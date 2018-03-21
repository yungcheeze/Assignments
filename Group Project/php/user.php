<?php
require_once "database.php";
require_once "location.php";

function cSessionStart()
{
    if(session_id() !== '') return true;
    session_start();
    return true;
}

function loginCheck()
{
    if (isset($_SESSION) && isset($_SESSION["user"])) return true;
    if (session_id() !== '') session_destroy();
    return false;
}

function areCompatible($fromFlags, $toFlags)
{
    return !($fromFlags & ~$toFlags);
    //if this breaks return the thing below
    //return ($fromFlags & ~$toFlags == 0) ? true : false; //php
}

if (isset($_GET["id"]))
{
    if (loginCheck()) echo $_SESSION["info"]->idToName($_GET["id"]);
}

class UserInfo
{
    public $allergens = array(1 => "Vegan", 2 => "Vegetarian", 4 => "Peanut", 8 => "Soy", 16 => "Gluten", 32 => "Lactose", 64 => "Halal", 128 => "Kosher");

    public function idToName($userid)
    {
        $userid = intval($userid);
        $dbconnection = Database::getConnection();

        $stmt = $dbconnection->prepare("SELECT username FROM UsersTable WHERE id = ?");
        $stmt->bind_param("i", $userid);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1)
        {
            $stmt->bind_result($name);
            $stmt->fetch();
            return $name;
        }
        return null;
    }

    public function nameToID($username)
    {
        $dbconnection = Database::getConnection();

        $stmt = $dbconnection->prepare("SELECT id FROM UsersTable WHERE username = ?");
        $stmt->bind_param("s", $username);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1)
        {
            $stmt->bind_result($id);
            $stmt->fetch();
            return $id;
        }
        return null;
    }


    //returns name, score, rating (and id)
    public function getBasicInfo($userid)
    {
        $userid = intval($userid);
        $dbconnection = Database::getConnection();

        $stmt = $dbconnection->prepare("SELECT username, score, rating FROM UsersTable WHERE id = ?");
        $stmt->bind_param("i", $userid);

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1)
        {
            $stmt->bind_result($name, $score, $rating);
            $stmt->fetch();
            $ret = array();
            $ret["name"] = $name;
            $ret["score"] = $score;
            $ret["rating"] = $rating;
            $ret["id"] = $userid;
            return $ret;
        }
        return null;
    }
}

class User
{
    private $username;
    private $userid;
    private $email;
    private $flags;
    private $score;
    private $rating;
    private $location;
    private $number;

    public function __construct($email, $password)
    {
        $dbconnection = Database::getConnection();
        if ($stmt = $dbconnection->prepare
        (
            "SELECT id, username, password, flags,
             location, rating, score, number
             FROM UsersTable
             WHERE email = ?
             LIMIT 1"))
        {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($userID, $username, $dbPassword, $this->flags, $llStr, $this->rating, $this->score, $this->number);
            $stmt->fetch();

            $hash = hash("sha256", $password);

            if ($stmt->num_rows == 1 && $hash === $dbPassword)
            {
                $this->userid = intval(preg_replace("/[^0-9]+/", "", $userID));
                $this->username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
                $this->email = $email;
                if(!is_null($llStr)) $this->location = new Location($llStr);
            }
            else throw new Exception("Invalid email or password, please try again.");
        } else throw new Exception("There was a database error.");
    }
	
	public function reload()
	{
		$dbconnection = Database::getConnection();
        if ($stmt = $dbconnection->prepare
        (
            "SELECT username, password, flags,
             location, rating, score, email, number
             FROM UsersTable
             WHERE id = ?
             LIMIT 1"))
        {
            $stmt->bind_param("i", $this->userid);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($username, $dbPassword, $this->flags, $llStr, $this->rating, $this->score, $this->email, $this->number);
            $stmt->fetch();

            $this->userid = intval(preg_replace("/[^0-9]+/", "", $this->userid));
            $this->username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
            if(!is_null($llStr)) $this->location = new Location($llStr);
		}
        else throw new Exception("There was an error preparing a statement.");
	}

	public function getScore()
    {
        return $this->score;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function getNumber()
    {
        return $this->number;
    }
	
    public function getUserName()
    {
        return $this->username;
    }
    public function setUserName($username)
    {
        $this->username = htmlspecialchars($username);
    }

    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) $this->email = $email;
    }

    public function getUserID()
    {
        return $this->userid;
    }

    public function getLocation()
    {
        return $this->location;
    }
    public function setLocation($newLoc)
    {
        if (is_null($this->location)) $this->location = new Location($newLoc);
        else $this->location->setLatLong($newLoc);
    }

    public function checkFlag($flag)
    {
        return ($this->flags & $flag) != 0;
    }

    public function setFlag($flag)
    {
        $this->flags = $this->flags | $flag;
    }

    public function clearFlags()
    {
        $this->flags = 0;
    }

    public function hasNewMessages()
    {
        $dbconnection = Database::getConnection();
        $stmt = $dbconnection->prepare("SELECT newMsg FROM UsersTable WHERE id=?");
        $stmt->bind_param("i", $this->userid);
        $stmt->bind_result($res);
        $stmt->execute();
        $stmt->store_result();
        $stmt->fetch();
        return ($res == 1);
    }

    public function hasNewNot()
    {
        $dbconnection = Database::getConnection();
        $stmt = $dbconnection->prepare("SELECT newNot FROM UsersTable WHERE id=?");
        $stmt->bind_param("i", $this->userid);
        $stmt->bind_result($res);
        $stmt->execute();
        $stmt->store_result();
        $stmt->fetch();
        return ($res == 1);
    }

    public function getFlags()
    {
        return $this->flags;
    }
}
