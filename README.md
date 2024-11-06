# MySuccess Website Developer Course Final Project

## Summary

This repository contains the final project deliverables of the MySuccess Website Developer Course by ICE Malta. It consists solely
of a back-end solution, since the front-end client was not required to be implemented.

## Contents

The following is a list of the repository contents.

- **src**: Directory containing source code.
  - **api**: Back-end web API source code written in PHP.
  - **client**: Directory for front-end client. Currently empty.
- **docker**: Docker files for the PHP-FPM base image. The PHP version used is 8.3.
- **support**: Directory for support files.
  - **db.sql**: SQL DDL and DML statements used to create the Kahuna database.
  - **db.vuerd.json**: A physical Entity Relationship Diagram (ERD) documenting the Kahuna database schema.
  - **KahunaApp.postman_collection.json**: Postman tests used for the Kahuna back-end API testing.
  - **Test.postman_collection.json**: Postman environment variables used for the Kahuna API tests.
  - **mswd-final-project.pdf**: Document describing the Kahuna final project assignment problem statement and requirements.
- **docker-compose.yml**: Docker compose file that creates the Kauna project containers consisting of a base PHP docker image,
  an Nginx server, a MariaDB server, and the client and API code used in this project.
- **run.cmd**: A simple script that runs `docker compose up` and prints informative status messages of the Kahuna docker images.

## Usage

1. Clone this repository by using [this link](https://github.com/dcarachi/sc-bed-finalproject-dcarachi.git).
2. Ensure Docker Desktop is running.
3. Open a terminal and change to the folder where you cloned this repository.
4. In **src/api** create a text file named **.env** and populate it with the following key-value pairs:

    > DB_USER=**User**  
    > DB_PASS=**Pass**
    >
    > where **User** and **Pass** refer to the MariaDB username and password (see **Details** section for more information).
    
5. Run the run.cmd script.
    1. On Windows, type **.\run.cmd**.    
    2. On macOS or Linux, type: **./run.cmd**.
6. Run Postman. Select File -> Import, and import the following files.
    1. KahunaApp Tests: **support/KahunaApp.postman_collection.json**.
    2. KahunaAPP Environment Variables: **support/Test.postman_environment.json**.
7. You are now setup for testing the KahunaApp.

## Details

PHP has been setup with a MariaDB server. Details follow:

- **Host**: mariadb
- **Database Name:** kahuna
- **User**: root
- **Pass**: root

The services started include:
- API Server on [http://localhost:8000](https://localhost:8000).
- Client on [http://localhost:8001](https://localhost:8001).