<?php
namespace com\icemalta\kahuna\api\model;

use \DateInterval;
use \JsonSerializable;
use \PDO;

class Product implements JsonSerializable
{
    private static PDO $db;

    private string $serial;
    private string $name;
    private DateInterval $warrantyLength;

    public function __construct(string $serial, string $name, DateInterval $warrantyLength)
    {
        self::$db = DBConnect::getInstance()->getConnection();

        $this->serial = $serial;
        $this->name = $name;
        $this->warrantyLength = $warrantyLength;
    }

    public function jsonSerialize(): array
    {
        return [
            'serial' => $this->getSerial(),
            'name' => $this->getName(),
            'warrantyLength' => $this->warrantyLength?->format('%y year(s)')
        ];
    }

    /**
     * Performs an "upsert" of a Product.
     * @param \com\icemalta\kahuna\api\model\Product $product The product to insert or update to the DB.
     * @return Product|null Returns the product if the operation was successful, or null on failure.
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
            $sql = <<<'SQL'
                UPDATE Product
                    SET serial = :serial, name = :name, warrantyLength = :warrantyLength
                    WHERE serial = :serial;
            SQL;
        } else {
            // Insert
            $sql = 'INSERT INTO Product(serial, name, warrantyLength) VALUES (:serial, :name, :warrantyLength)';
        }
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $product->getSerial());
        $sth->bindValue('name', $product->getName());
        $sth->bindValue('warrantyLength', $product->getWarrantyLength()->format('P%yY'));
        $sth->execute();

        return $sth->rowCount() > 0 ? $product : null;
    }

    /**
     * Retrieves a product from the DB given a serial no.
     * @param string $serial The serial of the product to search for.
     * @return Product|null Returns the Product if successful, or null on failure.
     */
    public static function get(string $serial): ?Product
    {
        $sql = 'SELECT serial, name, warrantyLength FROM Product WHERE serial = :serial';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $serial);
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
     * Retrieves all products from the DB.
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