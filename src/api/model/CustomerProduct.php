<?php
namespace com\icemalta\kahuna\api\model;

use \JsonSerializable;
use \PDO;
use \DateTime;
use \DateInterval;
use com\icemalta\kahuna\api\helper\DateIntervalHelper;

class CustomerProduct implements JsonSerializable
{
    private static PDO $db;

    private int $id;
    private int $customerId;
    private ?string $productSerial;
    private ?DateTime $purchaseDate;
    private ?Product $productInfo = null;

    public function __construct(int $customerId, ?string $productSerial = null, ?string $purchaseDate = null, int $id = 0)
    {
        $this->customerId = $customerId;
        $this->productSerial = $productSerial;
        $this->purchaseDate = $purchaseDate ? new DateTime($purchaseDate) : null;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function jsonSerialize(): array
    {
        $result = [
            'id' => $this->getId(),
            'customerId' => $this->getCustomerId(),
            'productSerial' => $this->getProductSerial(),
            'purchaseDate' => $this->getPurchaseDate()?->format('Y-m-d')
        ];
        if ($this->productInfo) {
            $result['productName'] = $this->productInfo->getName();
            $result['warrantyLeft'] =
                self::getWarrantyLeft($this->getPurchaseDate(), $this->productInfo->getWarrantyLength());
        }
        return $result;
    }

    /**
     * Performs an "upsert" on the CustomerProduct table.
     * @param \com\icemalta\kahuna\api\model\CustomerProduct $custProduct The CustomerProduct object to persist to the database.
     * @return CustomerProduct|null Returns the CustomerProduct with updated fields if successful, or `null` on failure.
     */
    public static function save(CustomerProduct $custProduct): ?CustomerProduct
    {
        if ($custProduct->getId() === 0) {
            // Insert
            $sql = <<<'SQL'
                INSERT INTO CustomerProduct(customerId, productSerial, purchaseDate) VALUES
                    (:customerId, :productSerial, :purchaseDate);
            SQL;
            $sth = self::$db->prepare($sql);
        } else {
            // Update
            $sql = <<<'SQL'
                UPDATE CustomerProduct
                    SET customerId = :customerId, productSerial = :productSerial, purchaseDate = :purchaseDate
                    WHERE id = :id;
            SQL;
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $custProduct->getId());
        }
        $sth->bindValue('customerId', $custProduct->getCustomerId());
        $sth->bindValue('productSerial', $custProduct->getProductSerial());
        $sth->bindValue('purchaseDate', $custProduct->getPurchaseDate()->format('Y-m-d'));
        $sth->execute();

        if ($sth->rowCount() > 0) {
            if ($custProduct->getId() === 0) {
                $custProduct->setId(self::$db->lastInsertId());
            }
            return $custProduct;
        }
        return null;
    }

    /**
     * Gets the Product details of a customer's registered product.
     * @param \com\icemalta\kahuna\api\model\CustomerProduct $custProduct A CustomerProduct object with the customerId and productSerial fields to search for.
     * @return CustomerProduct|null Returns the CustomerProduct with populated fields if found, or null if not found.
     */
    public static function getProductInfo(CustomerProduct $custProduct): ?CustomerProduct
    {
        $sql = <<<'SQL'
            SELECT
                customerId, productSerial, purchaseDate, id
            FROM
                CustomerProduct
            WHERE customerId = :customerId AND productSerial = :productSerial;
        SQL;
        $sth = self::$db->prepare($sql);
        $sth->bindValue('customerId', $custProduct->getCustomerId());
        $sth->bindValue('productSerial', $custProduct->getProductSerial());
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result) {
            $result = new CustomerProduct(
                id: $result->id,
                customerId: $result->customerId,
                productSerial: $result->productSerial,
                purchaseDate: $result->purchaseDate
            );
            $result->productInfo = Product::get(new Product($result->productSerial));
            return $result;
        }
        return null;
    }

    /**
     * Gets a list of customer registered products.
     * @param \com\icemalta\kahuna\api\model\CustomerProduct $custProduct A CustomerProduct object with the customerId to search for.
     * @return array Returns an enumerative array containing `CustomerProduct` objects that link a customer with a particular product registered.
     * If there are no products registered, the array is empty.
     */
    public static function getProducts(CustomerProduct $custProduct): array
    {
        $sql = <<<'SQL'
            SELECT
                customerId, productSerial, purchaseDate, id
            FROM
                CustomerProduct
            WHERE customerId = :customerId;
        SQL;
        $sth = self::$db->prepare($sql);
        $sth->bindValue('customerId', $custProduct->getCustomerId());
        $sth->execute();

        return $sth->fetchAll(
            PDO::FETCH_FUNC,
            fn(...$fields) => new CustomerProduct(...$fields)
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getProductSerial(): ?string
    {
        return $this->productSerial;
    }

    public function getPurchaseDate(): ?DateTime
    {
        return $this->purchaseDate;
    }

    private static function getWarrantyLeft(DateTime $purchaseDate, DateInterval $warrantyLength): string
    {
        $expiryDate = $purchaseDate->add($warrantyLength);
        $today = new DateTime();
        $remaining = $today->diff($expiryDate);

        return DateIntervalHelper::formatString($remaining);
    }
}