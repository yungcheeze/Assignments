<?php
require_once(__DIR__ . "/../database.php");
$_POST = array(); //workaround for broken PHPstorm
parse_str(file_get_contents('php://input'), $_POST);

$errorMessage = "";
$dbconnection = Database::getConnection();

if (isset($_POST["email"], $_POST["username"], $_POST["password"]))
{
    //Fix when jetbrains get off their asses
    $username = $_POST["username"]; /*filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);*/
    $email = $_POST["email"];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        header("Location: ../../index.php?error=" . urlencode("The email address you entered is not valid!"));
        exit();
    }

    $username = mysqli_real_escape_string($dbconnection, $username);
    $email = mysqli_real_escape_string($dbconnection,$email);
 
    $prepstmt = "SELECT id FROM UsersTable WHERE email = ? LIMIT 1";
    $stmt = $dbconnection->prepare($prepstmt);
 
    if ($stmt) 
    {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows == 1) 
        {
            $errorMessage = "A user with this email address already exists!";
            $stmt->close();
            header("Location: ../../index.php?error=" . urlencode($errorMessage));
            exit();
        }
    } 
    else 
    {
        $errorMessage = "Database error!";
        header("Location: ../../index.php?error=" . urlencode($errorMessage));
        exit();
    }

    $prepstmt = "SELECT id FROM UsersTable WHERE username = ? LIMIT 1";
    $stmt = $dbconnection->prepare($prepstmt);
 
    if ($stmt) 
    {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows == 1) 
        {
            $errorMessage = "A user with this username already exists!";
            $stmt->close();
            header("Location: ../../index.php?error=" . urlencode($errorMessage));
            exit();
        }
    }
    else 
    {
        $errorMessage = "Database error!";
        header("Location: ../../index.php?error=" . urlencode($errorMessage));
        exit();
    }
 

    if (empty($errorMessage)) 
    {
        $password = hash("sha256", $_POST["password"]);

        $flags = 0;
		if (isset($_POST["flags"]))
        {
            foreach ($_POST["flags"] as $flag) $flags |= constant($flag);
        }
		$location = isset($_POST["location"]) ? $_POST["location"] : NULL;
        if ($insertstmt = $dbconnection->prepare("INSERT INTO UsersTable (username, email, password, flags, location) VALUES (?, ?, ?, ?, ?)"))
        {
            $insertstmt->bind_param("sssis", $username, $email, $password, $flags, $location);
            if (!$insertstmt->execute()) $errorMessage .= "Could not create a new user";
            else
            {
                header("Location: ../../index.php?success=1");
                exit();
            }
        }
    }
}
if (!$errorMessage) $errorMessage = "Unknown error";
header("Location: ../../index.php?error=" . urlencode($errorMessage));
exit();
?>