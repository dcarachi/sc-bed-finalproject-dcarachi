<?php
namespace com\icemalta\kahuna\api\model;

use \JsonSerializable;
use \PDO;
use \DateTime;
use \DateInterval;

class Purchase implements JsonSerializable
{
    private static PDO $db;

    private int $id;
    private ?int $userId;
    private ?int $productId;
    private ?DateTime $purchaseDate;

    public function __construct(?int $userId = null, ?int $productId = null, ?DateTime $purchaseDate = null, int $id = 0)
    {
        $this->userId = $userId;
        $this->productId = $productId;
        $this->purchaseDate = $purchaseDate;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public static function save(Purchase $userPurchase): ?Purchase
    {
        $sql = 'INSERT INTO UserPurchase(userId, productId, purchaseDate) VALUES (:userId, :productId, :purchaseDate)';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('userId', $userPurchase->getUserId());
        $sth->bindValue('productId', $userPurchase->getProductId());
        $sth->bindValue('purchaseDate', $userPurchase->getPurchaseDate()->format('Y-m-d'));
        $sth->execute();

        if ($sth->rowCount() > 0) {
            $userPurchase->setId(self::$db->lastInsertId());
            return $userPurchase;
        }
        return null;
    }

    public static function getAll(): array
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql = 'SELECT userId, productId, purchaseDate, id FROM UserPurchase';
        $sth = self::$db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(
            PDO::FETCH_FUNC,
            function (int $userId, int $productId, string $purchaseDate, int $id): UserPurchase {
                return new Purchase($userId, $productId, new DateTime($purchaseDate), $id);
            }
        );
    }

    public function jsonSerialize(): array
    {
        $product = $this->getProductInfo();
        return [
            'id' => $this->getId(),
            'productSerial' => $product->getSerial(),
            'productName' => $product->getName(),
            'purchaseDate' => $this->getPurchaseDate()->format('Y-m-d'),
            'warrantyLength' => $product->getWarrantyLength()->format('%y year(s)')
        ];
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function getPurchaseDate(): ?DateTime
    {
        return $this->purchaseDate;
    }

    public function getProductInfo(): ?Product
    {
        $product = new Product(id: $this->getProductId());
        $product = Product::get($product);
        return $product;
    }

    private static function calculateWarranty(DateInterval $warrantyLength)
    {
    }
}