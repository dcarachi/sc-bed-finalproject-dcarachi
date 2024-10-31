<?php
namespace com\icemalta\kahuna\api\model;

use \PDO;
use \JsonSerializable;

class Product implements JsonSerializable
{
    private static PDO $db;

    private int $id;
    private ?string $serial;
    private ?string $name;
    private ?int $warrantyLength;

    public function __construct(?string $serial = null, ?string $name = null, ?int $warrantyLength = null, int $id = 0)
    {
        $this->serial = $serial;
        $this->name = $name;
        $this->warrantyLength = $warrantyLength;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    /**
     * Performs an "upsert" of a Product.
     * @param \com\icemalta\kahuna\api\model\Product $product The product object to insert or update to the database.
     * @return Product|null Returns the product with the updated Id if successful, or null on failure.
     */
    public static function save(Product $product): ?Product
    {
        if ($product->getId() === 0) {
            // Insert
            $sql = 'INSERT INTO Product(serial, name, warrantyLength) VALUES (:serial, :name, :warrantyLength)';
            $sth = self::$db->prepare($sql);
        } else {
            // Update
            $sql = 'UPDATE Product SET serial = :serial, name = :name, warrantyLength = :warrantyLength WHERE id = :id';
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $product->getId());
        }
        $sth->bindValue('serial', $product->getSerial());
        $sth->bindValue('name', $product->getName());
        $sth->bindValue('warrantyLength', $product->getWarrantyLength());
        $sth->execute();

        if ($sth->rowCount() > 0) {
            if ($product->getId() === 0) {
                $product->setId(self::$db->lastInsertId());
            }
            return $product;
        }
        return null;
    }

    /**
     * Retrieves a product from the database given a particular Id.
     * @param \com\icemalta\kahuna\api\model\Product $product The product object containing the desired id.
     * @return Product|null Returns a Product with populated fields if successful, or null on failure.
     */
    public static function get(Product $product): ?Product
    {
        $sql = 'SELECT * FROM Product WHERE id = :id';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('id', $product->getId());
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return new Product(
                id: $result->id,
                serial: $result->serial,
                name: $result->name,
                warrantyLength: $result->warrantyLength
            );
        }
        return null;
    }

    /**
     * Retrieves a product from the database given a particular serial no.
     * @param \com\icemalta\kahuna\api\model\Product $product The product object containing the desired serial no.
     * @return Product|null Returns a Product with populated fields if successful, or null on failure.
     */
    public static function getBySerial(string $serial): ?Product
    {
        self::$db = DBConnect::getInstance()->getConnection();
        
        $sql = 'SELECT * FROM Product WHERE serial = :serial';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $serial);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return new Product(
                id: $result->id,
                serial: $result->serial,
                name: $result->name,
                warrantyLength: $result->warrantyLength
            );
        }
        return null;
    }

    /**
     * Get all products from the database.
     * @return array An array containing Product objects with populated fields.
     */
    public static function getAll(): array
    {
        self::$db = DBConnect::getInstance()->getConnection();

        $sql = 'SELECT serial, name, warrantyLength, id FROM Product';
        $sth = self::$db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(
            PDO::FETCH_FUNC,
            fn(...$fields): Product => new Product(...$fields)
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
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

    public function getSerial(): ?string
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

    public function getWarrantyLength(): ?int
    {
        return $this->warrantyLength;
    }

    public function setWarrantyLength(int $warrantyLength): self
    {
        $this->warrantyLength = $warrantyLength;
        return $this;
    }
}