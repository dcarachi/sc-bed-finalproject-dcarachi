CREATE DATABASE IF NOT EXISTS kahuna;

USE kahuna;

CREATE TABLE IF NOT EXISTS User
(
    id          INT                     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255)            NOT NULL,
    password    VARCHAR(255)            NOT NULL,
    firstName   VARCHAR(20)             NOT NULL,
    lastName    VARCHAR(35)             NOT NULL,
    accessLevel ENUM('admin', 'client') NOT NULL DEFAULT 'client'
);

CREATE TABLE IF NOT EXISTS AccessToken
(
    id          INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token       VARCHAR(255)    NOT NULL,
    birth       TIMESTAMP       NOT NULL,
    userId      INT             NOT NULL,
    CONSTRAINT  FK_User_TO_AccessToken
        FOREIGN KEY (userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Product
(
    id              INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    serial          VARCHAR(50)     NOT NULL,
    name            VARCHAR(100)    NOT NULL,
    warrantyLength  INT             NOT NULL
);

CREATE TABLE IF NOT EXISTS UserProduct
(
    id          INT     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userId      INT     NOT NULL,
    productId   INT     NOT NULL,
    CONSTRAINT  FK_User_TO_UserProduct
        FOREIGN KEY (userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT  FK_Product_TO_UserProduct
        FOREIGN KEY (productId) REFERENCES Product(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Initial product data

INSERT INTO Product(serial, name, warrantyLength) VALUES
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