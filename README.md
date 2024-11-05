# MySuccess Website Developer Course Final Project

## Summary

This repository contains the final project deliverables of the MySuccess Website Developer Course by ICE Malta. It consists solely
of a back-end solution, since the front-end client was not required to be implemented.

## Contents

- **src**: Directory containing source code:
  - **api**: Back-end web API source code written in PHP.
  - **client**: Directory for front-end client. Currently empty.
- **docker**: Docker files for the PHP-FPM base image. The PHP version used is 8.3.
- **support**: Directory for support files:
  - **db.sql**: SQL DDL and DML statements used to create the Kahuna database.
  - **db.vuerd.json**: A physical Entity Relationship Diagram (ERD) documenting the Kahuna database schema.
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
    1. On Windows, type **.\run.cmd**.    
    2. On macOS or Linux, type: **./run.cmd**.
5. Run Postman. Select File -> Import, and when presented with a dialog box drag the following files.
    1. KahunaApp Tests: **support/KahunaApp.postman_collection.json**.
    2. KahunaAPP Environment Variables: **support/Test.postman_environment.json**.
6. You are now setup for testing the KahunaApp.

## Details

PHP has been setup with a MariaDB server. Details follow:

- **Host**: mariadb
- **Database Name:** kahuna
- **User**: root
- **Pass**: root

The services started include:
- API Server on [http://localhost:8000](https://localhost:8000).