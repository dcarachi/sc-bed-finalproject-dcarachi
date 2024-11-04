CREATE DATABASE IF NOT EXISTS kahuna;

USE kahuna;

CREATE TABLE IF NOT EXISTS User(
    id          INT                     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    firstName   VARCHAR(100)            NOT NULL,
    lastName    VARCHAR(100)            NOT NULL,
    email       VARCHAR(100)            NOT NULL,
    password    VARCHAR(255)            NOT NULL,
    accessLevel ENUM('admin', 'client') NOT NULL DEFAULT 'client'
);

CREATE TABLE IF NOT EXISTS AccessToken(
    id          INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token       VARCHAR(255)    NOT NULL,
    birth       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    userId      INT             NOT NULL,
    CONSTRAINT c_accesstoken_user
        FOREIGN KEY (userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Product (
    serial          VARCHAR(50)     NOT NULL PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL,
    warrantyLength  CHAR(5)         NOT NULL
);

CREATE TABLE IF NOT EXISTS CustomerProduct (
    id            INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    customerId    INT           NOT NULL,
    productSerial VARCHAR(50)   NOT NULL,
    purchaseDate  DATE          NOT NULL,
    CONSTRAINT c_customerproduct_user
        FOREIGN KEY (customerId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT c_customerproduct_product
        FOREIGN KEY (productSerial) REFERENCES Product(serial)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Initial product data

INSERT INTO Product(serial, name, warrantyLength) VALUES
    ('KHWM8199911', 'CombiSpin Washing Machine', 'P2Y'),
    ('KHWM8199912', 'CombiSpin + Dry Washing Machine', 'P2Y'),
    ('KHMW789991', 'CombiGrill Microwave', 'P2Y'),
    ('KHWP890001', 'K5 Water Pump', 'P5Y'),
    ('KHWP890002', 'K5 Heated Water Pump', 'P5Y'),
    ('KHSS988881', 'Smart Switch Lite', 'P2Y'),
    ('KHSS988882', 'Smart Switch Pro', 'P2Y'),
    ('KHSS988883', 'Smart Switch Pro V2', 'P2Y'),
    ('KHHM89762', 'Smart Heated Mug', 'P1Y'),
    ('KHSB0001', 'Smart Bulb 001', 'P1Y');