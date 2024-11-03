<?php
namespace com\icemalta\kahuna\api\model;

use \stdClass;
use \JsonSerializable;
use \PDO;
use \DateTime;
use \DateInterval;

class CustomerProduct implements JsonSerializable
{
    private static PDO $db;

    private int $id;
    private int $customerId;
    private ?string $productSerial;
    private ?DateTime $purchaseDate;

    public function __construct(int $customerId, ?string $productSerial = null, ?DateTime $purchaseDate = null, int $id = 0)
    {
        $this->customerId = $customerId;
        $this->productSerial = $productSerial;
        $this->purchaseDate = $purchaseDate;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function jsonSerialize(): array
    {
        $result['id'] = $this->getId();
        $result['productSerial'] = $this->getProductSerial();
        $product = Product::get($this->productSerial);
        if ($product) {
            $result['productName'] = $product->getName();
            $result['warrantyLeft'] =
                self::getWarrantyLeft($this->getPurchaseDate(), $product->getWarrantyLength());
        }
        return $result;
    }

    public static function save(CustomerProduct $customerProduct): ?CustomerProduct
    {
        $sql = 'INSERT INTO CustomerProduct(customerId, productSerial, purchaseDate) VALUES
        (:customerId, :productSerial, :purchaseDate)';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('customerId', $customerProduct->getCustomerId());
        $sth->bindValue('productSerial', $customerProduct->getProductSerial());
        $sth->bindValue('purchaseDate', $customerProduct->getPurchaseDate()->format('Y-m-d'));
        $sth->execute();

        if ($sth->rowCount() > 0) {
            $customerProduct->setId(self::$db->lastInsertId());
            return $customerProduct;
        }
        return null;
    }

    public static function get(CustomerProduct $customerProduct)
    {
        if ($customerProduct->getProductSerial()) {
            // Get a particular customer product
            $sql = <<<'SQL'
                SELECT customerId, productSerial, purchaseDate, id FROM CustomerProduct
                    WHERE customerId = :customerId AND productSerial = :productSerial;
            SQL;
            $sth = self::$db->prepare($sql);
            $sth->bindValue('productSerial', $customerProduct->getProductSerial());
        } else {
            // Get all customer products
            $sql = <<<'SQL'
                SELECT customerId, productSerial, purchaseDate, id FROM CustomerProduct
                    WHERE customerId = :customerId;
            SQL;
            $sth = self::$db->prepare($sql);
        }
        $sth->bindValue('customerId', $customerProduct->getCustomerId());
        $sth->execute();

        $result = $sth->fetchAll(
            PDO::FETCH_FUNC,
            fn($customerId, $productSerial, $purchaseDate, $id) =>
            new CustomerProduct(
                id: $id,
                customerId: $customerId,
                productSerial: $productSerial,
                purchaseDate: new DateTime($purchaseDate),
            )
        );

        if ($sth->rowCount() > 0) {
            return $sth->rowCount() === 1 ? $result[0] : $result;
        }
        return null;
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

        $result = 'expired';
        if ($remaining->invert === 0) {
            if ($remaining->y > 0) {
                $result = $remaining->format('%y year(s)');
            } else if ($remaining->m > 0) {
                $result = $remaining->format('%m month(s)');
            } else if ($remaining->d > 0) {
                $result = $remaining->format('%d day(s)');
            } else if ($remaining->h > 0) {
                $result = $remaining->format('%h hour(s)');
            }
        }
        return $result;
    }
}