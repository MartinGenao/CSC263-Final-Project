# CSC263-Final Project
 Web Application to handle a Dog Sitter Case Study

 # Disclaimer
- This is a local project, any changes will only be seen locally so it is crucial to set up your own database exactly how we've provided

# Instructions (Part 1)
- Download XAMPP
- Open XAMPP Control Panel
- Go into 'Config' under actions for the 'Apache' Module then open the 'config.inc.php' file in notepad --> Edit password
- Start the Servers
- use commands 'mysql -u root -p' and enter your password
- Once in mariaDB, create the tables provided in "CSC263_part1.sql" 
- Insert the Data provided in "CSC263_part1.sql"

# Instructions (Part 2)
- In XAMPP Control Panel select explorer --> Traverse to the htdocs file and create a file with your name
- Create a php file for your desired page
- Test page is functional by entering "http://localhost/Your_name/Your_file.php" into the browser

# Instructions (Part 3)
- Populate the database with users and assign them roles
- Review CSC263_final.sql to see how to structure the SQL statement
- Populate the database with sample data
- Once accounts are created run Hashing script

# Hashing Passwords

- Place populate_passwords.php in your project directory.
- Open a browser and navigate to http://localhost/<your_project_directory>/populate_pwds.php.
- The script will update the Password column in your Responders table and display the results in your browser.

# After Running the Script

- Verify the Responders table to ensure that the Password column is populated with hashed values:

- SELECT ResponderID, Password FROM Responders;

