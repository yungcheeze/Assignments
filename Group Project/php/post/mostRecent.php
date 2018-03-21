<?php
require_once "../database.php";

if ($_SERVER["REQUEST_METHOD"] === "GET")
{
    $cnx = Database::getConnection();
    $result = $cnx->query("SELECT id, title, description, posttime FROM PostsTable WHERE visible=1 ORDER BY posttime LIMIT 9");
    $ret = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) $ret[] = $row;
    echo json_encode($ret);
}
?>