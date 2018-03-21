<?php
/*
define("DBSERVER", "mysql.dur.ac.uk");
define("DBUSERNAME", "dcs8s08");
define("DBPASSWORD", "swansea2");
define("WEBROOT", "https://community.dur.ac.uk/cs.seg08/gp8");
*/

define("DBSERVER", "localhost");
define("DBUSERNAME", "root");
define("DBPASSWORD", "root");
//define("WEBROOT", "http://localhost/~abdul/Cupboard");


define("VEGAN", 1);
define("VEGETARIAN", 2);
define("PEANUT", 4);
define("SOY", 8);
define("GLUTEN", 16);
define("LACTOSE", 32);
define("HALAL", 64);
define("KOSHER", 128);

class Database
{
    private static $db;
    private $dbconnection;

    private function __construct()
    {
        $this->dbconnection = new mysqli(DBSERVER, DBUSERNAME, DBPASSWORD);
        if ($this->dbconnection->connect_error) die("Couldn't connect to database: " . $this->dbconnection->connect_error);

        if (!$this->dbconnection->select_db("Pdcs8s08_CupboardDB")) $this->createDB();
    }

    function __destruct()
    {
        $this->dbconnection->close();
    }

    public static function getConnection()
    {
        if (self::$db == null) self::$db = new Database();
        return self::$db->dbconnection;
    }

    public function createDB()
    {
        $createDBConnection = new mysqli(DBSERVER, DBUSERNAME, DBPASSWORD);
        if ($createDBConnection->connect_error) die("Couldn't connect to database: " . $createDBConnection->connect_error);

        if (!$createDBConnection->query("CREATE DATABASE IF NOT EXISTS Pdcs8s08_CupboardDB")) die("Failed to create DB!");

        $createDBConnection->select_db("Pdcs8s08_CupboardDB");

        $query = "
        CREATE TABLE IF NOT EXISTS UsersTable
        (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          username VARCHAR(16) UNIQUE NOT NULL,
          email VARCHAR(64) UNIQUE NOT NULL,
          password VARCHAR(256) NOT NULL,
          flags TINYINT UNSIGNED default 0,
          newMsg BOOL DEFAULT 0,
          newNot BOOL DEFAULT 0,
          location VARCHAR(52) NOT NULL,
          rating FLOAT default 3,
          score INT default 0,
          number INT default 0
          )";
        if (!$createDBConnection->query($query)) die("Failed to create user table:" . $createDBConnection->error);

        $query =
        "CREATE TABLE IF NOT EXISTS PostsTable
        (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(32) NOT NULL,
        description VARCHAR(256),
        location VARCHAR(52),
        flags TINYINT UNSIGNED default 0,
        userid INT NOT NULL,
        posttime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        visible BOOL DEFAULT 1,
        expiry DATE
        )";
        if (!$createDBConnection->query($query)) die("Failed to create posts table:" . $createDBConnection->error);

        $query =
        "CREATE TABLE IF NOT EXISTS FinishedPostsTable
        (
        id INT UNSIGNED PRIMARY KEY,
        title VARCHAR(32) NOT NULL,
        posterID INT NOT NULL,
        posterDone BOOL DEFAULT 0,
        recipientID INT NOT NULL,
        recipientDone BOOL DEFAULT 0,
        fintime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expiry DATE
        )";
        if (!$createDBConnection->query($query)) die("Failed to create finished posts table:" . $createDBConnection->error);

        $query = "
        CREATE TABLE IF NOT EXISTS MessagesTable
        (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fromid INT NOT NULL,
        toid INT NOT NULL,
        postid INT NOT NULL,
        text VARCHAR(256) NOT NULL,
        messagetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if (!$createDBConnection->query($query)) die("Failed to create messages table:" . $createDBConnection->error);

        $query = "
        CREATE TABLE IF NOT EXISTS NotificationTable
        (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        toid INT NOT NULL,
        text VARCHAR(512) NOT NULL,
        notificationtime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if (!$createDBConnection->query($query)) die("Failed to create notification table:" . $createDBConnection->error);

        $query = "
        CREATE TABLE IF NOT EXISTS ReservedTable
        (
        userid INT NOT NULL,
        word VARCHAR(16) NOT NULL,
        PRIMARY KEY (userid, word)
        )";
        if (!$createDBConnection->query($query)) die("Failed to create reserved table:" . $createDBConnection->error);

        $this->dbconnection = $createDBConnection;
    }
}
