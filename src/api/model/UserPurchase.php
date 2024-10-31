<?php
namespace com\icemalta\kahuna\api\model;

use \JsonSerializable;
use \PDO;
use \DateTime;
use \DateInterval;

class UserPurchase implements JsonSerializable
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

    public static function save(UserPurchase $userPurchase): ?UserPurchase
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
                return new UserPurchase($userId, $productId, new DateTime($purchaseDate), $id);
            }
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'product' => $this->getProductInfo()
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

    private function getProductInfo(): array
    {
        $result = [];
        $product = new Product(id: $this->getId());
        $product = Product::get($product);
        if ($product) {
            // Calculate warranty remaining
            $purchaseDate = clone($this->getPurchaseDate());
            $warrantyEnd = $purchaseDate->add(new DateInterval('P' . $product->getWarrantyLength() . 'Y'));
            $warrantyLeft = $warrantyEnd->diff(new DateTime("now"));

            $result = [
                'serial' => $product->getSerial(),
                'name' => $product->getName(),
                'purchaseDate' => $this->getPurchaseDate()->format('Y-m-d'),
                'warrantyLength' => $product->getWarrantyLength(),
                'remainingWarranty' => $warrantyLeft->format('%y year(s), %m month(s), %d day(s)')
            ];
        }
        return $result;
    }
}