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
    private string $productSerial;
    private DateTime $purchaseDate;

    public function __construct(int $customerId, string $productSerial, DateTime $purchaseDate, int $id = 0)
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $this->customerId = $customerId;
        $this->productSerial = $productSerial;
        $this->purchaseDate = $purchaseDate;
        $this->id = $id;
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

    public static function get(int $customerId, string $productSerial)
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql = 'SELECT * FROM CustomerProduct WHERE customerId = :customerId AND productSerial = :productSerial';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('customerId', $customerId);
        $sth->bindValue('productSerial', $productSerial);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return new CustomerProduct(
                id: $result->id,
                customerId: $result->customerId,
                productSerial: $result->productSerial,
                purchaseDate: $result->purchaseDate
            );
        }
        return null;
    }

    public static function getAll(int $customerId): array
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql = 'SELECT customerId, productSerial, purchaseDate, id FROM CustomerProduct WHERE customerId = :customerId';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('customerId', $customerId);
        $sth->execute();
        return $sth->fetchAll(
            PDO::FETCH_FUNC,
            fn($customerId, $productSerial, $purchaseDate, $id)
                => new CustomerProduct($customerId, $productSerial, new DateTime($purchaseDate), $id)
        );
    }

    public function jsonSerialize(): array
    {
        $result['id'] = $this->getId();
        $result['customerId'] = $this->getCustomerId();
        $result['purchaseDate'] = $this->getPurchaseDate()->format('Y-m-d');
        $result['product'] = $this->getProductInfo();
        return $result;
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

    public function getProductSerial(): string
    {
        return $this->productSerial;
    }

    public function getPurchaseDate(): DateTime
    {
        return $this->purchaseDate;
    }

    public function getProductInfo(): ?stdclass
    {
        $product = Product::get($this->getProductSerial());
        if ($product) {
            $result = new stdClass();
            $result->serial = $product->getSerial();
            $result->productName = $product->getName();
            $result->warrantyLength = $product->getWarrantyLength()->format('%y year(s)');

            // Calculate remaining warranty
            $today = new DateTime();
            $warrantyEndDate = $this->getPurchaseDate()->add($product->getWarrantyLength());
            $warrantyRemaining = $warrantyEndDate->diff($today);
            if ($warrantyRemaining->days > 0) {
                $result->warrantyRemaining = $warrantyRemaining->format('%y year(s), %m month(s), and %d day(s)');
            }

            return $result;
        }
        return null;
    }
}