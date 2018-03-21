##How the database works
The database is a singleton class that creates a connection and/or the actual database if it needs to. Make sure that
the constants at the top of php/database.php is set up with the correct credentials for your MySQL setup - soon we'll 
have it set up with AWS.

##Accessing session variables
Just using session_start() doesn't protect against session hijacking. I've written a function called cSessionStart() in
php/membership/userfunctions.php which you should use instead. loginCheck() should be called every now and then to check 
the users session id.

##User class
The user class is stored in $_SESSION["user"] (call cSessionStart() and loginCheck() before trying to access this).
It's constructed on login.