<?php
require_once(__DIR__ . "/user.php");
cSessionStart();
echo($_SESSION["user"]->getUserID());
?>
