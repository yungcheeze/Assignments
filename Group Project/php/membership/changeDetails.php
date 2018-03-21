<?php
require_once "../user.php";
require_once "../database.php";
cSessionStart();
if (!loginCheck())
{
    header("Location: ../../index.php?error=" . urlencode("You must be logged in to do that."));
    exit();
}

header('Content-Type: application/json');

$_POST = array(); //workaround for broken PHPstorm
parse_str(file_get_contents('php://input'), $_POST);
$dbConnection = Database::getConnection();

$response = array();

if (isset($_POST["uname"]))
{
    $cnx = Database::getConnection();

    $hash = hash("sha256", $_POST["cpass"]);
    $id = $_SESSION["user"]->getUserID();

    $stmt = $cnx->prepare("SELECT password FROM UsersTable WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->bind_result($dbPassword);
    $stmt->execute();
    $stmt->store_result();
    $stmt->fetch();

    if (!($stmt->num_rows == 1 && $hash === $dbPassword))
    {
	$response["error"] = "incorrect password";
	die(json_encode($response));

    }
    $flags = 0;
    if (isset($_POST["flags"]))
    {
        foreach ($_POST["flags"] as $flag) $flags |= constant($flag);
    }
    $location = isset($_POST["location"]) ? $_POST["location"] : NULL;

    if ($stmt = $cnx->prepare("UPDATE UsersTable SET username=?, email=?, password=?, flags=?, location=? WHERE id=?"))
    {
        $pass = (!isset($_POST["pass"]) || strlen($_POST["pass"]) <= 0) ? $dbPassword : hash("sha256", $_POST["pass"]);
        $stmt->bind_param("sssisi", $_POST["uname"], $_POST["email"], $pass, $flags, $location, $id);
        $stmt->execute();
        $_SESSION["user"]->reload();
	$response["success"] = "changes successfully made";
    }

} else {
    $response["error"] = "username not set";
}

echo json_encode($response);
?>
