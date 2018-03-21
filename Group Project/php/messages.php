<?php
require_once "user.php";
require_once "database.php";
cSessionStart();
if (!loginCheck())
{
    header("Location: ../index.php");
    exit();
}

$dbConnection = Database::getConnection();

$_POST = array(); //workaround for broken PHPstorm
parse_str(file_get_contents('php://input'), $_POST);

if (isset($_POST["toUser"]))
{
    $dbconnection = Database::getConnection();
    $userid = $_SESSION["user"]->getUserID();
    $otherid = $_SESSION["info"]->nameToID($_POST["toUser"]);
    $stmt = $dbconnection->prepare("INSERT INTO MessagesTable (fromid, toid, postid, text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $userid, $otherid, $_POST["pid"], $_POST["message"]);
    if ($stmt->execute())
    {
        if (!$stmt->affected_rows === 1) echo("No message sent!");
        $dbConnection->query("UPDATE UsersTable SET newMsg=1 WHERE id=" . $otherid);
    }
    else echo("Could not execute statement!");
}

else if(isset($_GET["othername"])) //get list of messages with one person
{
    $userid = intval($_SESSION["user"]->getUserID());
    $otherid = $_SESSION["info"]->nameToID($_GET["othername"]);
    $stmt = $dbConnection->prepare("SELECT fromid, toid, text, messagetime FROM MessagesTable 
                                  WHERE (fromid = ? AND toid = ? OR fromid = ? AND toid = ?) AND postid = ?
                                  ORDER BY messageTime DESC");
    $stmt->bind_param("iiiii", $otherid, $userid, $userid, $otherid, $_GET["pid"]);
    $stmt->bind_result($fromid, $toid, $text, $time);

    if ($stmt->execute())
    {
        $stmt->store_result();
        $messages = array();
        while ($stmt->fetch())
        {
            $row = array();
            $row["fromname"] = $_SESSION["info"]->idToName($fromid);
            $row["toname"] = $_SESSION["info"]->idToName($toid);
            $row["text"] = $text;
            $row["time"] = $time;
            $messages[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($messages);
    }

    else header("Location: ../index.php?error=" . urlencode("Error preparing message thread statement!"));
}

else //no other user specified, return all users and the first message
{

    $stmt = $dbConnection->prepare("SELECT least(fromid, toid) as fromid, greatest(fromid, toid) as toid, postid FROM MessagesTable
                                  WHERE fromid = ? OR toid = ?
                                  GROUP BY least(fromid, toid), greatest(fromid, toid), postid
                                  ORDER BY messageTime DESC");

    $userid = $_SESSION["user"]->getUserID();
    $stmt->bind_param("ii", $userid, $userid);
	$stmt->bind_result($first, $second, $pid);
    if ($stmt->execute())
    {
        $stmt->store_result();
        $messages = array();
        while ($stmt->fetch())
        {
            $fromid = $first;
            $toid = $second;

            $row = array();
            $row["fromname"] = $fromid === $userid ? $_SESSION["info"]->idToName($toid) : $_SESSION["info"]->idToName($fromid);

            $stmt2 = $dbConnection->prepare("SELECT text, UNIX_TIMESTAMP(messagetime) AS time FROM MessagesTable 
                                  WHERE (fromid = ? AND toid = ? OR toid = ? AND fromid = ?) AND postid = ?
                                  ORDER BY time DESC LIMIT 1");
            $stmt2->bind_param("iiiii", $fromid, $toid, $fromid, $toid, $pid);
            $stmt2->bind_result($text, $time);
            $stmt2->execute();
            $stmt2->store_result();
            $stmt2->fetch();
            $row["text"] = $text;
            $row["time"] = $time;
            $stmt2->close();

            $nameSt = $dbConnection->prepare("SELECT title FROM PostsTable WHERE id=?");
            $nameSt->bind_param("i", $pid);
            $nameSt->bind_result($title);
            $nameSt->execute();
            $nameSt->store_result();
            $nameSt->fetch();
            $row["ptitle"] = $title;
            $row["pid"] = $pid;
            $nameSt->close();

            $messages[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($messages);
        exit();
    }

    else header("Location: ../index.php?error=" . urlencode("Error preparing message list statement!"));

}