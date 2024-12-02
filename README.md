# PHP & MariaDB Development Enviroment Tester

## Purpose

This simple app will help setup a working enviroment for your final project. It will also check that PHP is working, and that a connection to the MariaDB database server can be established. 

## Usage

1. Clone this repository.
2. Ensure Docker Desktop is running.
3. Open a terminal and change to the folder where you cloned this repository.
4. Run the run.cmd script.  
    4.1. On Windows, type **.\run.cmd**.    
    4.2. On macOS or Linux, type: **./run.cmd**.
5. Open [http://localhost:8001](https://localhost:8001) in your browser.

## Details

PHP has been setup as usual. A MariaDB server has also been created. Details follow:

- **Host**: mariadb
- **Database Name:** kahuna
- **User**: root
- **Pass**: root

The services started include:
- API Server on [http://localhost:8000](https://localhost:8000).
- Client on [http://localhost:8001](https://localhost:8001).

## Next Steps

You can now start working on your final project.

1. It is safe to delete the contents of the **client** folder. 

__________________________________________________________________________________________________________________________________________________________

## Final Project - Kahuna Control.

## Overview 

This Project, Kahuna Control, is a product registration system that allows a user/client to register a product and view their warranty. Also, admins can add, modify or delete a product in the database. 

## Tools Used 

- **VSCode**: Integrated Development Environment (IDE) for coding.
- **MariaDB**: Relational database to store users, products, and registration data.
- **Docker**: Enabled me to build, deploy, run, update and manage containers
- **PHP**: Backend language to create RESTful API endpoints.
- **Postman**: API development and testing tool.
- **GitHub**: Version control platform for managing the project codebase.

## 1: Setting Up the Project Environment

1. **Install Required Tools**:
    - MariaDB  https://mariadb.org/download/?t=mariadb&p=mariadb&r=11.6.2
    - Postman for API testing   https://www.postman.com/downloads/
    - Docker or a web server of your liking  https://www.docker.com/products/docker-desktop/
    - Git for version control  https://github.com/

2. **Set Up Project Directory**:
    - Create a new directory for the project.
    - Initialize a Git repository.


## 2: Clone Repositry

1. Clone this repository.
2. Ensure Docker Desktop is running.
3. Open a terminal and change to the folder where you cloned this repository.
4. Run the run.cmd script.  
    4.1. On Windows, type **.\run.cmd**.    
    4.2. On macOS or Linux, type: **./run.cmd**.
5. Open [http://localhost:8001](https://localhost:8001) in your browser and you should see "Welcome to Kahuna API".

## 3: Extract Postman Workspace

1. Create a new Blank workspace and name it Kahuna
2. Click Import 
3. Go to the root folder Postman in sc-bed-finalproject-matthew.mallia and press open
4. Select All files and Import them 
5. Select the Arrow next to "No Environment" to "Test"

## 4: Postman Function Tests

1. In the Basic Tests Collection, click on Connectivity Test and Press 'Send"
- Make sure that in the Body it displays  "data": "Welcome to Kahuna API!"
2. Then, Click 404 Test and Press "Send"
- It should display a 404 Not Found error and in the Body it displays "error": "Endpoint bogus not found."
3. Then, Click Unsupported Method and Press "Send"
- It should display "error": "Method not allowed."

## 5: Working With Postman / User

**Creating a User**
1. In the User Managment Collection click on Crete User 
2. Press the Body Tag and insert your details and Press "Send"
3. You have now created a User!

**Log In**
1. In the Autentication Collection click on Login.
2. Press the Body Tag and insert the same details you typed in to Create User and Press "Send"
- Make sure that the log in details are correct, if not an error "Login failed." will appear.

**Registering a Product**
1. Go to the Registering a Product Collection and select Register a Product.
2. In the Body Tab, insert a Serial Number of a product that was provided in the Final Project PDF file on page 6 and Press "Send"
- You should see in the Result below that the data was inserted in the Database.

**Viewing Registered Products**
1. Go to the Registering a Product Collection and select Get Registered Products.
2. Press "Send" and here you can see all your Registered Products that you inserted in the Database.
3. If you go to the Get All Products Tab and press "Send", here you will see all the available products in the Kahuna database and their detals. 

**Log Out**
1. In the User Managment Collection click on Create User and in the Body Tab insert your details.
2. Go to the Authentication Collection and Press Logout. 
- A message will appear saying "You have been logged out."

You can repeat all the process above to creating other users with different e-mails and registering more products!

## 6: Working With Postman / Admin

**Creating a User**
1. In the User Managment Collection click on Crete Admin 
2. Press the Body Tag and insert your details.
3. Important that the accessLevel Key's Value is set to "admin" and Press "Send"
4. You have now created an Admin!

**Log In**
1. In the Autentication Collection click on Login Admin.
2. Press the Body Tag and insert the same details you typed in to Create User and Press "Send"
- Make sure that the log in details are correct, if not an error "Login failed." will appear.

**Adding, Modifying & Deleting a Product**
1. In the Add/Modify/Delete Product Collection click on the Get All Products and Press "Send". 
- Here you can see all products inserted in the SQL 
2. In the same Collection, press on Add a Product. 
- Here you can Add a new product to your Database that a User can Register too!
3. In the same Collection, press on Modify Product.
- Here you can Modify a product that maybe has been updated from the company etc..
- <IMPORTANT> Enter the value of the id key of the product you want to update!
4. In the same Collection, press on Delete a Product.
- Here you can Delete a product from the Database.
- <IMPORTANT> Enter the value of the id of the product you want to update next to the {{BASE_URI}}product/# eg. {{BASE_URI}}product/3  

**Registering a Product**
1. Like a User, here you can Registr a product.
2. To do so, go to the Registering a Product Collection and select Register a Product.
3. In the Body Tab, insert a Serial Number of a product that was provided in the Final Project PDF file on page 6 and Press "Send"
- You should see in the Result below that the data was inserted in the Database.

**Viewing Registered Products**
1. Go to the Registering a Product Collection and select Get Registered Products.
2. Press "Send" and here you can see all your Registered Products that you inserted in the Database.
3. If you go to the Get All Products Tab and Press "Send", here you will see all the available products in the Kahuna database and their detals. 

**Log Out**
1. In the User Managment Collection click on Create User and in the Body Tab insert your details.
2. Go to the Authentication Collection and press Logout. 
- A message will appear saying "You have been logged out."
