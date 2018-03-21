<?php
require_once (__DIR__ . "/../database.php");
require_once (__DIR__ . "/../user.php");

cSessionStart();
if (!loginCheck())
{
    header("Location: ../../index.php");
    exit();
}

function notifyUser($notification, $userid)
{
    $cnx = Database::getConnection();
    $stmt = $cnx->prepare("INSERT INTO NotificationTable(toid, text) VALUES (?,?)");
    $stmt->bind_param("is", $userid, $notification);
    $stmt->execute();
    $cnx->query("UPDATE UsersTable SET newNot=1 WHERE id=" . $userid);

    //check if we have more than 20 notifications - delete oldest one
    $res = $cnx->query("SELECT * FROM NotificationTable WHERE toid=" . intval($userid));
    if ($res->num_rows > 20) $cnx->query("DELETE FROM NotificationTable WHERE notificationtime IS NOT NULL AND toid=" . intval($userid) .
                                            "ORDER BY notificationtime ASC LIMIT 1");
}

if (isset($_GET["notifs"]))
{
    $cnx = Database::getConnection();
    $uid = $_SESSION["user"]->getUserID();
    $stmt = $cnx->prepare("SELECT text, notificationtime FROM NotificationTable WHERE toid=? ORDER BY notificationtime DESC");
    $stmt->bind_param("i", $uid);
    $stmt->bind_result($text, $time);
    $stmt->execute();
    $ret = array();
    if (isset($_GET["withTime"])) {
	while ($stmt->fetch()) $ret[] = array("text" => $text, "time" =>$time);
    } else {
	while ($stmt->fetch()) $ret[] = $text;
    }
    echo json_encode($ret);
}
?>
