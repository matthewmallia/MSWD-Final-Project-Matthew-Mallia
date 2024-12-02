CREATE DATABASE IF NOT EXISTS Kahuna;

USE Kahuna;

CREATE TABLE User(
    id              INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email           VARCHAR(255) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    firstName       VARCHAR(50) NOT NULL,
    lastName        VARCHAR(50) NOT NULL,
    accessLevel     CHAR(10) NOT NULL DEFAULT 'user'
);

CREATE TABLE AccessToken(
    id              INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    userId          INT NOT NULL,
    token           VARCHAR(255) NOT NULL,
    birth           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT c_accesstoken_user
        FOREIGN KEY(userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE Register(
    id          INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    userId      INT NOT NULL,
    serialNo    VARCHAR(255) NOT NULL,
    regDate     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT c_registration_user 
        FOREIGN KEY (userId) REFERENCES User(id)
        ON UPDATE CASCADE 
        ON DELETE CASCADE
);

CREATE TABLE Product(
    id            INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    serialNo      VARCHAR(255) NOT NULL,
    productDesc   TEXT,
    warranty      INT(50) NOT NULL,
    birth         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO Product(serialNo, productDesc, warranty) 
VALUES
    ('KHWM8199911', 'CombiSpin Washing Machine', 2),
    ('KHWM8199912', 'CombiSpin + Dry Washing Machine', 2),
    ('KHMW789991', 'CombiGrill Microwave', 1),
    ('KHWP890001', 'K5 Water Pump', 5),
    ('KHWP890002', 'K5 Heated Water Pump', 5),
    ('KHSS988881', 'Smart Switch Lite', 2),
    ('KHSS988882', 'Smart Switch Pro', 2),
    ('KHSS988883', 'Smart Switch Pro V2', 2),
    ('KHHM89762', 'Smart Heated Mug', 1),
    ('KHSB0001', 'Smart Bulb 001', 1);
