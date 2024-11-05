<?php
namespace com\icemalta\kahuna\api\model;

use com\icemalta\kahuna\api\helper\DateHelper;
use com\icemalta\kahuna\api\helper\DateIntervalHelper;
use \DateInterval;
use \JsonSerializable;
use \PDO;

class Product implements JsonSerializable
{
    private static PDO $db;

    private string $serial;
    private ?string $name;
    private ?DateInterval $warrantyLength;

    public function __construct(string $serial, ?string $name = null, ?DateInterval $warrantyLength = null)
    {
        $this->serial = $serial;
        $this->name = $name;
        $this->warrantyLength = $warrantyLength;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function jsonSerialize(): array
    {
        return [
            'serial' => $this->getSerial(),
            'name' => $this->getName(),
            'warrantyLength' => $this->getWarrantyLength() ?
                DateIntervalHelper::formatString($this->getWarrantyLength()) : null
        ];
    }

    /**
     * Performs an "upsert" of a Product.
     * @param \com\icemalta\kahuna\api\model\Product $product The Product object to persist to the database.
     * @return Product|null Returns the product with updated fields, if the operation was successful, or `null` on failure.
     */
    public static function save(Product $product): ?Product
    {
        $sql = 'SELECT COUNT(*) AS itemCount FROM Product WHERE serial = :serial';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $product->getSerial());
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result->itemCount > 0) {
            // Update
            $sql = 'UPDATE Product SET serial = :serial, name = :name, warrantyLength = :warrantyLength WHERE serial = :serial';
        } else {
            // Insert
            $sql = 'INSERT INTO Product(serial, name, warrantyLength) VALUES (:serial, :name, :warrantyLength)';
        }
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $product->getSerial());
        $sth->bindValue('name', $product->getName());
        $sth->bindValue('warrantyLength', DateIntervalHelper::formatISO($product->getWarrantyLength()));
        $sth->execute();

        return $sth->rowCount() > 0 ? $product : null;
    }

    /**
     * Check if a product with a given serial number exists.
     * @param \com\icemalta\kahuna\api\model\Product $product The Product object with the serial to check for.
     * @return bool Returns `true` if a product exists, `false` otherwise.
     */
    public static function exists(Product $product): bool
    {
        $sql = 'SELECT COUNT(*) AS prodCount FROM Product WHERE serial = :serial';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $product->getSerial());
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_OBJ);
        return $result->prodCount > 0;
    }

    /**
     * Retrieves a product from the DB with a given serial no.
     * @param \com\icemalta\kahuna\api\model\Product $product The product containing the serial number to search for.
     * @return Product|null Returns the product found, or `false` on failure.
     */
    public static function get(Product $product): ?Product
    {
        $sql = 'SELECT serial, name, warrantyLength FROM Product WHERE serial = :serial';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $product->getSerial());
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return new Product(
                serial: $result->serial,
                name: $result->name,
                warrantyLength: new DateInterval($result->warrantyLength)
            );
        }
        return null;
    }

    /**
     * Retrieves all products from the database.
     * @return array An array containing Product objects.
     */
    public static function getAll(): array
    {
        self::$db = DBConnect::getInstance()->getConnection();

        $sql = 'SELECT serial, name, warrantyLength FROM Product';
        $sth = self::$db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(
            PDO::FETCH_FUNC,
            fn($serial, $name, $warrantyLength) => new Product($serial, $name, new DateInterval($warrantyLength))
        );
    }

    public function getSerial(): string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getWarrantyLength(): ?DateInterval
    {
        return $this->warrantyLength;
    }

    public function setWarrantyLength(DateInterval $warrantyLength): self
    {
        $this->warrantyLength = $warrantyLength;
        return $this;
    }
}