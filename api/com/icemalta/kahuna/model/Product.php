<?php
namespace com\icemalta\kahuna\model;

use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class Product implements JsonSerializable
{
    private static $db;
    private int|string $id = 0;
    private ?string $serialNo;
    private ?string $productDesc;
    private int $warranty;
    private int|string $birth = 0;

    public function __construct(string $serialNo = '', string $productDesc = '', int $warranty = 0, int|string $birth = 0, int|string $id = 0)
    {
        $this->serialNo = $serialNo;
        $this->productDesc = $productDesc;
        $this->warranty = $warranty;
        $this->birth = $birth;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public static function save(Product $product): Product
    {
        if ($product->getId() === 0) {
            // Insert the Product in the DB
            $sql = "INSERT INTO Product(serialNo, productDesc, warranty) VALUES (:serialNo, :productDesc, :warranty)";
            $sth = self::$db->prepare($sql);
        } else {
            // Update the existing product in the DB
            $sql = "UPDATE Product SET serialNo = :serialNo, productDesc = :productDesc, warranty = :warranty WHERE id = :id";
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $product->getId());
        }
        $sth->bindValue('serialNo', $product->getSerialNo());
        $sth->bindValue('productDesc', $product->getProductDesc());
        $sth->bindValue('warranty', $product->getWarranty());
        $sth->execute();

        // Sets the ID of a newly inserted Product to the id given by the DB
        if ($sth->rowCount() > 0 && $product->getId() === 0) {
            $product->setId(self::$db->lastInsertId());
        }
        return $product;
    }
    // Added data by past examples, Modified by Chat GPT 
    public static function load(?Product $product = null): array
    {
        $sql = "SELECT serialNo, productDesc, warranty, birth, id FROM Product";
        if ($product && $product->getId() !== 0) {
            $sql .= " WHERE id = :id";
        }
        $sql .= " ORDER BY id DESC"; // Orders list by last in, first shows in list
        $sth = self::$db->prepare($sql);
        if ($product && $product->getId() !== 0) {
            $sth->bindValue('id', $product->getId());
        }
        $sth->execute();
        $products = $sth->fetchAll(PDO::FETCH_FUNC, fn(...$fields) => new Product(...$fields));
        return $products;
    }

    public static function delete(Product $product): bool 
    {
        $sql = "DELETE FROM Product WHERE id = :id";
        $sth = self::$db->prepare($sql);
        $sth->bindValue('id', $product->getId());
        $sth->execute();
        return $sth->rowCount() > 0;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this); // Return all of my fields!
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

    public function getSerialNo(): string
    {
        return $this->serialNo;
    }

    public function setSerialNo(string $serialNo): self
    {
        $this->serialNo = $serialNo;
        return $this;
    }

    public function getProductDesc(): ?string
    {
        return $this->productDesc;
    }

    public function setProductDesc(string $productDesc): self
    {
        $this->productDesc = $productDesc;
        return $this;
    }

    public function getWarranty(): ?int
    {
        return $this->warranty;
    }

    public function setWarranty(int $warranty): self
    {
        $this->warranty = $warranty;
        return $this;
    }
    
    public function getBirth(): ?int
    {
        return $this->birth;
    }

    public function setBirth(int $birth): self
    {
        $this->birth = $birth;
        return $this;
    }
}
