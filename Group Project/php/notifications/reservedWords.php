<?php
require_once "../database.php";
require_once "../user.php";

cSessionStart();
if (!loginCheck())
{
    header("Location: ../index.php");
    exit();
}

$_POST = array(); //workaround for broken PHPstorm
parse_str(file_get_contents('php://input'), $_POST);
$dbConnection = Database::getConnection();


if(isset($_POST["word"]))
{
    $cnx = Database::getConnection();
    $userid = $_SESSION["user"]->getUserID();
    if (isset($_POST["delete"]))
    {
        $cnx->query("DELETE FROM ReservedTable WHERE userid=" . intval($userid) . " AND word='" . mysqli_real_escape_string($cnx, strtoupper($_POST["word"])) . "'");
        exit();
    }
    else
    {
        $cnx->query("INSERT INTO ReservedTable VALUES (" . intval($userid) . ", '" . mysqli_real_escape_string($cnx, strtoupper($_POST["word"])) . "')");
        exit();
    }
}

else if ($_SERVER["REQUEST_METHOD"] == "GET")
{
    $cnx = Database::getConnection();
    $uid = $_SESSION["user"]->getUserID();
    $stmt = $cnx->prepare("SELECT word FROM ReservedTable WHERE userid=?");
    $stmt->bind_param("i", $uid);
    $stmt->bind_result($word);
    $stmt->execute();
    $ret = array();
    while ($stmt->fetch()) $ret[] = $word;
    echo json_encode($ret);
}