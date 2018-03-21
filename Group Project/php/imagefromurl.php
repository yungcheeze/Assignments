<?php

// post with url parameters: postid (post to add the image to), url (url to the image to add)
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	if (isset($_REQUEST["postid"]))
	{
		$postid = $_REQUEST["postid"];
		postImages($postid, $_REQUEST["url"]);
	}
	else
	{
		echo "{error : no post id}";
	}
}

function postImages($postid, $url)
{
	//path to post's images
	$root = "../itemphotos/";
	$dir = $root . $postid;

	//create image folder - if the folder already exists then delete it to clear out the old images and recreate it
	if(file_exists($dir))
	{
		rmdir($dir);
	}
	mkdir($dir);

	// make a new file with a random, unique name to store the image in
	$newname = tempnam($dir, "photo_");
	// delete the file if it already exists
	unlink($newname);
	$newname = $newname. ".jpg";
	// copy the image from the url to the local file
	copy($url, $newname);
}

?>
