<?php
require_once(__DIR__ . "/../database.php");
require_once(__DIR__ . "/../user.php");
require_once(__DIR__ . "/../location.php");
require_once(__DIR__ . "/../notifications/notifyUser.php");
cSessionStart();
if (!loginCheck())
{
    header("Location: ../index.php?error=" . urlencode("You must be logged in to do that."));
    exit();
}

$_POST = array(); //workaround for broken PHPstorm
parse_str(file_get_contents('php://input'), $_POST);
$dbConnection = Database::getConnection();

/*
 * If the poster is finalizing "rating" must be set
 * If he is reserving it for someone "otherID" must be set
 * In all cases "postID" must be set
 */
if(isset($_POST["postID"]))
{
	$userid = intval($_SESSION["user"]->getUserID());

	$stmt = $dbConnection->prepare("SELECT userid, visible FROM PostsTable WHERE id=?");
	$stmt->bind_param("i", intval($_POST["postID"]));
	$stmt->bind_result($posterid, $visible);
	$stmt->execute();
	$stmt->store_result();
	$stmt->fetch();

	if ($stmt->num_rows <= 0)
	{
	    $stmt = $dbConnection->prepare("SELECT posterID FROM FinishedPostsTable WHERE id=?");
	    $stmt->bind_param("i", intval($_POST["postID"]));
	    $stmt->bind_result($posterid);
	    $stmt->execute();
	    $stmt->store_result();
	    $stmt->fetch();
	    $visible = is_null($posterid) ? true : false;
	}

    if(isset($_POST["cancel"]))
    {
        mysqli_report(MYSQLI_REPORT_ALL);
        $stmt = $dbConnection->prepare("SELECT posterID, recipientID, posterDone, recipientDone FROM FinishedPostsTable WHERE id = ?");

        $stmt->bind_param("i", intval($_POST["postID"]));
        $stmt->bind_result($posterid, $recepid, $pdone, $rdone);
        $stmt->execute();
        $stmt->fetch();
        $stmt->store_result();
        $stmt->close();
        if ($pdone || $rdone || ($userid !== $posterid && $userid !== $recepid)) die("Can't cancel this post");
        $dbConnection->query("UPDATE PostsTable SET visible=1 WHERE id=" . intval($_POST["postID"]));
        $dbConnection->query("DELETE FROM FinishedPostsTable WHERE id=" . intval($_POST["postID"]));

        $otherID = ($posterid == $userid) ? $recepid : $posterid;
        notifyUser("Your reservation for <a href='" . WEBROOT . "/listing.php?id=" . intval($_POST["postID"]) .
                                            "'>this post</a> was cancelled by the other party.", $otherID);
        exit();
    }

    else if(isset($_POST["delete"]))
    {
        $stmt = $dbConnection->prepare("SELECT userid, visible FROM PostsTable WHERE id=?");
        $stmt->bind_param("i", $_POST["postID"]);
        $stmt->bind_result($pid, $isVisible);
        $stmt->execute();
        $stmt->fetch();
        $stmt->store_result();
        $stmt->close();
        if ($isVisible == 0) die("Please cancel this post first!");
        if ($pid != $userid) die("That isn't yours!");
        $dbConnection->query("DELETE FROM PostsTable WHERE id=" . intval($_POST["postID"]));
        exit();
    }

	if (!$visible) //post is removed from poststable when poster reserves it
    {
        finalise(intval($_POST["postID"]));
        exit();
    }

	if ($userid !== $posterid)
	{
		$stmt->close();
		die("Only the poster can deal with this post!");
	}

    if (isset($_POST["otherUser"]))
    {
        $stmt = $dbConnection->prepare("SELECT title, expiry FROM PostsTable WHERE id=?");
        $stmt->bind_param("i", intval($_POST["postID"]));
        $stmt->bind_result($title, $expiry);
        $stmt->execute();
        $stmt->store_result();
        $stmt->fetch();

        $otherID = $_SESSION["info"]->nameToID($_POST["otherUser"]);
        $stmt = $dbConnection->prepare("INSERT INTO FinishedPostsTable (id, title, posterID, recipientID, expiry) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiis", intval($_POST["postID"]), $title, $userid, intval($otherID), $expiry);
        $stmt->execute();
		
		notifyUser("<a href='" . WEBROOT . "/listing.php?id=" . intval($_POST["postID"]) . "'>A post</a> has been reserved for you.", $otherID);

        $stmt = $dbConnection->prepare("UPDATE PostsTable SET visible=0 WHERE id=?");
        $stmt->bind_param("i", intval($_POST["postID"]));
        $stmt->execute();
    }
}

/*
 * stillUp - array of your posts that are still listed
 * bothDone - finished posts
 * reserved - posts you have reserved for someone but neither have finalized (cancel here)
 * orders - posts reserved for you that you haven't finalized
 * needsConfirming - posts you have reserved that the other guy has confirmed
 * youveDone - you have rated the other guy, goes in your history
 */
else if ($_SERVER["REQUEST_METHOD"] == "GET")
{
    global $dbConnection;
    $userid = intval($_SESSION["user"]->getUserID());

    //get posts still up
    $stillGoing = array();
    if ($result = $dbConnection->query("SELECT * FROM PostsTable WHERE visible=1 AND userid=" . $userid))
    {
        while ($row = $result->fetch_assoc()) $stillGoing[] = $row;
    }

    //get the rest
    $reserved = array();
    $orders = array();
    $waitingForYou = array();
    $waitingForThem = array();
    $bothDone = array();
    if ($result = $dbConnection->query("SELECT * FROM FinishedPostsTable WHERE posterID=" . $userid . " OR recipientID=" . $userid))
    {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
        {
            $row["posterName"] = $_SESSION["info"]->idToName(intval($row["posterID"]));
            if (isset($row["location"]) && ($loc = $_SESSION["user"]->getLocation()))
            {
                $row["distance"] = $loc->distanceFrom(new Location($row["location"]));
            } //ugh

            $rdone = intval($row["recipientDone"]);
            $rid = intval($row["recipientID"]);
            $pdone = intval($row["posterDone"]);
            $pid = intval($row["posterID"]);

            if ($rdone && $pdone) $bothDone[] = $row;
            else if (!$pdone && !$rdone)
            {
                if ($userid == $pid) $reserved[] = $row;
                else $orders[] = $row;
            }
            else if (!$rdone && $userid == $pid || !$pdone && $userid == $rid) $waitingForThem[] = $row;
            else $waitingForYou[] = $row;
        }
    }

    $fin = array("stillUp" => $stillGoing, "orders"=>$orders, "waitingForYou"=> $waitingForYou, "bothDone"=>$bothDone, "reserved"=>$reserved, "waitingForThem"=> $waitingForThem);
    echo json_encode($fin);
}


function finalise($postID)
{
    global $dbConnection;
    $userid = $_SESSION["user"]->getUserID();
    $stmt = $dbConnection->prepare("SELECT recipientID, recipientDone, posterID, posterDone FROM FinishedPostsTable WHERE id=?");
    $stmt->bind_param("i", intval($postID));
    $stmt->execute();
    $stmt->bind_result($recepID, $recepDone, $posterID, $posterDone);
    $stmt->store_result();
    $stmt->fetch();
    if ($recepID == $userid)
    {
        if ($recepDone) die("Post already finalized.");
        $dbConnection->query("UPDATE FinishedPostsTable SET recipientDone=1 WHERE id=" . intval($postID));
        $dbConnection->query("UPDATE UsersTable SET score=score+1 WHERE id=". intval($userid));
        $otherid = $posterID;
    }
    else if ($posterID == $userid)
    {
        if ($posterDone) die("Post already finalized.");
        $dbConnection->query("UPDATE FinishedPostsTable SET posterDone=1 WHERE id=" . intval($postID));
        $dbConnection->query("UPDATE UsersTable SET score=score+5 WHERE id=". intval($userid));
        $otherid = $recepID;
        /*
        $dbConnection->query("DELETE FROM PostsTable WHERE id=" .intval($postID));
        $dbConnection->query("DELETE FROM MessagesTable WHERE postid=" .intval($postID));*/
    }
    else die("Wrong user ID.");

    $_SESSION["user"]->reload();

    $stmt = $dbConnection->prepare("SELECT number, rating FROM UsersTable WHERE id=?");
    $stmt->bind_param("i", $otherid);
    $stmt->bind_result($number, $rating);
    $stmt->execute();
    $stmt->store_result();
    $stmt->fetch();

    if ($number == 0) $newrating = $_POST["rating"];
    else $newrating = (($rating * $number + floatval($_POST["rating"])) / ($number + 1));
    $dbConnection->query("UPDATE UsersTable SET number=number+1, rating=" . floatval($newrating) . " WHERE id=" . intval($otherid));
    $uname = $_SESSION["user"]->getUserName();
    notifyUser($uname . " rated you " . $_POST["rating"] . " stars.", $otherid);
}
?>
