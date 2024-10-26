# MySuccess Website Developer Course Final Project

## Summary

This repository contains the final project deliverables of the MySuccess Website Developer Course by ICE Malta. It consists solely
of a back-end solution, since the front-end client was not required to be implemented.

## Contents

- **api**: Directory containing back-end web API source code written in PHP.
- **client**: Directory for front-end client. Currently empty.
- **docker**: Docker files for the PHP-FPM base image. The PHP version used is 8.3.
- **support**: Directory for support files. Consists of:
  - **db.sql**: SQL statements used to create the Kahuna DB.
  - **db.vuerd.json**: A physical ERD documenting the Kahuna DB schema.
  - **KahunaApp.postman_collection.json**: Postman tests used for the Kahuna back-end API testing.
  - **Test.postman_collection.json**: Postman environment variables used for the Kahuna API tests.
  - **mswd-final-project.pdf**: Document describing the Kahuna final project assignment problem statement and requirements.
- **docker-compose.yml**: Docker compose file that creates the Kauna project containers consisting of a base PHP docker image,
  an Nginx server, a MariaDB server, and the client and API code used in this project.
- **run.cmd**: A simple script that runs `docker compose up` and prints informative status messages of the Kahuna docker images.

## Usage

1. Clone this repository.
2. Ensure Docker Desktop is running.
3. Open a terminal and change to the folder where you cloned this repository.
4. Run the run.cmd script.  
    4.1. On Windows, type **.\run.cmd**.    
    4.2. On macOS or Linux, type: **./run.cmd**.
5. Open [http://localhost:8001](https://localhost:8001) in your browser.

## Details

PHP has been setup with a MariaDB server. Details follow:

- **Host**: mariadb
- **Database Name:** kahuna
- **User**: root
- **Pass**: root

The services started include:
- API Server on [http://localhost:8000](https://localhost:8000).
- Client on [http://localhost:8001](https://localhost:8001).