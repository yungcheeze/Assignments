<?php

// get a list of images for the postid paramater
if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
	$root = "../itemphotos/";

	$absroot = "itemphotos/";

	if (isset($_REQUEST["postid"]))
	{
		$postid = $_REQUEST["postid"];

		// the image folder for the post
		$dir = $root . $postid;
		$dir_contents = scandir($dir);
		$array = array();

		// check all the files in the post's image folder:
		foreach ($dir_contents as $file)
		{
			$fileinfo = pathinfo($file);
            if (substr($file, -1) != '.') $array[] = $absroot . $postid . '/' . $file;
		}

		echo json_encode($array, 64);
	}
	else
	{
		echo "{error : no post id}";
	}
}

// add new images to the postid parameter from $_FILES (uploaded files)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	if (isset($_POST["postid"]))
	{
		$postid = $_REQUEST["postid"];
		postImages($postid, $_POST["new"]);
	}
	else
	{
		echo "{error : no post id}";
	}
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

function postImages($postid, $new)
{
	//path to post's images
	$root = "../itemphotos/";
	$dir = $root . $postid;

	//if a new listing, create image folder - if the folder already exists then delete it to clear out the old images and recreate it
	if($new)
	{
		if(file_exists($dir)) deleteDirectory($dir);
		mkdir($dir, 0777, true);
	}
	// if not a new listing (adding more images to an existing listing), only create the folder if it doesnt exist
	else if(!file_exists($dir))
	{
		mkdir($dir, 0777, true);
    }

	// check photos are available
	if (isset($_FILES['photo']) && $_FILES["photo"]["name"][0] !== "")
	{
			$filesuploaded = $_FILES['photo'];
			// iterate through all the files uploaded
			for ($i = 0; $i < count($filesuploaded['tmp_name']); $i++)
			{
                $currentfile = $filesuploaded['tmp_name'][$i];
				$path = $dir . "/" . $i . ".jpg";
				// delete the file if it already exists
				unlink($path);
				// copy the image from the temporary file to the local file
				move_uploaded_file($currentfile, $path);
			}
	}

	// after completed, redirect back
	echo '<script> window.location.href="../listing.php?id=' . $postid . '"; </script>';
}

?>
