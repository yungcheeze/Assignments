<?php
include_once "../database.php";
include_once "../user.php";

$_POST = array(); //workaround for broken PHPstorm
parse_str(file_get_contents('php://input'), $_POST);
cSessionStart();

header('Content-Type: application/json');

$response = array();

if (isset($_POST["email"], $_POST["password"]))
{
    $email = mysqli_real_escape_string(Database::getConnection(), $_POST["email"]);

    try
    {
        $_SESSION["user"] = new User($email, $_POST["password"]);
        $_SESSION["info"] = new UserInfo();
	$response["success"] = "Login Successful";
	echo json_encode($response);
        exit();
    }
    catch (Exception $e)
    {
	$response["error"] = $e->getMessage();
	echo json_encode($response);
        exit();
    }
} 
else echo json_encode($response);

?>
