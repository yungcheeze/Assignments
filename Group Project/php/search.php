<?php
require_once(__DIR__ . "/database.php");
require_once(__DIR__ . "/user.php");
cSessionStart();
if (!loginCheck())
{
    header("Location: post/mostRecent.php");
    exit();
}

$dbconnection = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
    $keywords = isset($_GET["keywords"]) ? explode(" ", $_GET["keywords"]) : array();
    $location = isset($_GET["location"]) ? $_GET["location"] : "";

    $flags = 0;
    if (isset($_GET["flags"]) && is_array($_GET["flags"]))
    {
        foreach ($_GET["flags"] as $value) $flags |= constant($value);
    }
	
    $location = mysqli_real_escape_string($dbconnection, $location);

    $sql = "SELECT title,description,location,flags,posttime,expiry,id,userid FROM PostsTable WHERE ";
    if (sizeof($keywords) != 0 && $keywords[0] != "")
    {
        $filtered = mysqli_real_escape_string($dbconnection, $keywords[0]);
        $sql .= "(description LIKE '%" . $filtered . "%' OR title LIKE '%" . $filtered . "%'";
        for ($i = 1; $i < sizeof($keywords); $i++)
        {
            $filtered = mysqli_real_escape_string($dbconnection, $keywords[$i]);
            if ($filtered == "") continue;
            $sql .= " OR description LIKE '%" . $filtered . "%' OR title LIKE '%" . $filtered . "%'";
        }
        $sql .= ") AND";
    }
    $sql .= " visible=1";
    $sql .= " AND userid != ". $_SESSION["user"]->getUserID();
    $result = $dbconnection->query($sql);

    $out = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
        $uLoc = $_SESSION["user"]->getLocation();
        if (areCompatible($flags, intval($row["flags"])))
        {
            $row["distance"] = $uLoc->distanceFrom(new Location($row["location"]));
            $posterInfo = $_SESSION["info"]->getBasicInfo($row["userid"]);
            foreach ($posterInfo as $k=>$v)
            {
                $str = "poster" . $k;
                $row[$str] = $v;
            }
            $out[] = $row;
        }
    }
	
    echo json_encode($out);
}
?>