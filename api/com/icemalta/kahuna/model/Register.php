<?php

namespace com\icemalta\kahuna\model;

require_once 'com/icemalta/kahuna/model/DBConnect.php';

use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class Register implements  JsonSerializable
{
    private static $db;
    private int $id = 0;
    private int $userId;
    private ?string $serialNo;
    private int|string $regDate = 0;

    public function __construct(int $userId, string $serialNo = '',int|string $regDate = 0, int|string $id = 0)
    {
        $this->userId = $userId;
        $this->serialNo = $serialNo;
        $this-> regDate= $regDate;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public static function save(Register $register): Register
    {
        // Check if the serial number exists in the Product table  (with some assistance of Chat GPT)
        $checkSql = "SELECT COUNT(*) FROM Product WHERE serialNo = :serialNo";
        $checkStmt = self::$db->prepare($checkSql);
        $checkStmt->bindValue('serialNo', $register->getSerialNo());
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

        if (!$exists) {
            sendResponse(null, 405,"The serial number does not exist in the Product table.");
            exit; 
        } 
        
        if ($register->getId() === 0) {
            // Insert the registered product in the DB
            $sql = "INSERT INTO Register(userId, serialNo) VALUES (:userId, :serialNo)";
            $sth = self::$db->prepare($sql);
            $sth->bindValue('userId', $register->getUserId());
            $sth->bindValue('serialNo', $register->getSerialNo());
            $sth->execute();

            
        } else {
            // Update the existing registered product in the DB
            $sql = "UPDATE Register SET serialNo = :serialNo WHERE id = :id";
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $register->getId());
            $sth->bindValue('serialNo', $register->getSerialNo());
            $sth->execute();
        }

        // Set the ID of a newly inserted Register to the ID given by the DB
        if ($sth->rowCount() > 0 && $register->getId() === 0) {
            $register->setId(self::$db->lastInsertId());
        }
        return $register;
    }


// Load the Registered Products
    public static function load(Register $register): array
{
    $sql = "SELECT r.userId,  p.productDesc, r.serialNo, r.regDate, p.warranty, r.id FROM Register r 
            JOIN Product p ON r.serialNo = p.serialNo WHERE r.userId = :userId ORDER BY r.id DESC";
    $sth = self::$db->prepare($sql);
    $sth->bindValue('userId', $register->getUserId());
    $sth->execute();

    $registers = [];
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        // Calculate remaining warranty in decimal years
        $remainingWarrantyDecimal = max(0, $row['warranty'] - (time() - strtotime($row['regDate'])) / (60 * 60 * 24 * 365));

        // Convert remaining warranty to a readable format
        $row['remainingWarranty'] = self::convertWarranty($remainingWarrantyDecimal);
        
        $registers[] = $row;
    }
    return $registers;
}

// Function to convert decimal years to a readable format
public static function convertWarranty(float $decimalYears): string {
    $years = floor($decimalYears); // Extract whole years
    $remainingMonthsDecimal = ($decimalYears - $years) * 12; 
    $months = floor($remainingMonthsDecimal); // Extract whole months
    $days = round(($remainingMonthsDecimal - $months) * 30.44); // Convert fractional months to days

    // Construct the result as a readable string
    $result = [];
    if ($years > 0) {
        $result[] = "$years year" . ($years > 1 ? "s" : "");
    }
    if ($months > 0) {
        $result[] = "$months month" . ($months > 1 ? "s" : "");
    }
    if ($days > 0) {
        $result[] = "$days day" . ($days > 1 ? "s" : "");
    }

    return implode(", ", $result);
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
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
    
    public function getRegDate(): ?int
    {
        return $this->regDate;
    }

    public function setRegDate(int $regDate): self
    {
        $this->regDate = $regDate;
        return $this;
    }

}