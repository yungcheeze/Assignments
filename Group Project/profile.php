<?php
require_once "php/user.php";
require_once "php/database.php";
cSessionStart();
if (!loginCheck())
{
    header("Location: index.php?error=" . urlencode("You must be logged in to do that."));
    exit();
}

$user = $_SESSION["user"];
$allergens = array();
for ($i = 1; $i <= 128; $i *= 2) $allergens[$i] = $user->checkFlag($i);
echo("<script>window.currentlatLng ='" . $user->getLocation()->getLatLong() . "'</script>");

$res = Database::getConnection()->query("SELECT * FROM PostsTable WHERE visible=1 AND userid=" . $user->getUserID());
$current = $res->num_rows;
?>

    <!DOCTYPE html>
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--snap stuff-->
        <meta http-equiv="x-ua-compatible" content="IE=edge"/>
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-touch-fullscreen" content="yes">
        <link rel="stylesheet" type="text/css" href="snap/snap.css"/>

        <!-- fontawesome -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">

        <!-- Material-Design icon library -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <!-- Bootstrap Core Stylesheet -->
        <link rel="stylesheet" href="bootstrap-material-design/css/bootstrap.min.css">

        <!-- Material-Design core stylesheet -->
        <link rel="stylesheet" href="bootstrap-material-design/css/mdb.min.css">

        <!-- My Stylesheet -->
        <link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" type="text/css" href="css/profile.css"/>

    </head>

<body>
  <div id="notificationsDiv">
    <div class='alert alert-dismissable fade in notification prototype' role='alert'>
      <a href='#' class='close' data-dismiss='alert' aria-label='Close'>&times;</a>
      <div class="text"></div>
    </div>
  </div>
<div class="snap-drawers">
    <div class="snap-drawer snap-drawer-right elegant-color-dark">
        <ul class="nav flex-column">
	  <li class="nav-item">
	    <h2 class="nav-title">User Dashboard</h2>
	  </li>
	  <div class="view overlay hm-white-slight">
	      <li class="nav-item">
		  <a class="nav-link" href="search.php">Item Catalogue</a>
		  <div class="mask"></div>
	      </li>
	  </div>
            <div class="view overlay hm-white-slight">
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">Orders and Listings</a>
                    <div class="mask"></div>
                </li>
            </div>
            <div class="view overlay hm-white-slight">
                <li class="nav-item">
                    <a class="nav-link" href="inbox.php">Messages <?php if ($_SESSION["user"]->hasNewMessages()) echo ("<i class='fa fa-circle'></i>");?></a>
                    <div class="mask"></div>
                </li>
            </div>
            <div class="view overlay hm-white-slight">
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">Notifications and alerts <?php if ($_SESSION["user"]->hasNewNot()) echo ("<i class='fa fa-circle notCircle'></i>");?></a>
                    <div class="mask"></div>
                </li>
            </div>
            <div class="view overlay hm-white-slight">
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Account</a>
                    <div class="mask"></div>
                </li>
            </div>
            <div class="view overlay hm-white-slight">
                <li class="nav-item">
                    <a class="nav-link" href="php/membership/logout.php">Logout</a>
                    <div class="mask"></div>
                </li>
            </div>
        </ul>
    </div>
</div>
<div id="content" class="snap-content">
    <div class="mask"></div>
    <header>
        <!-- navbar -->
        <nav class="navbar navbar-dark navbar-fixed-top elegant-color-dark">
            <a href="index.php">
                <img src="img/Cupboard.png" alt="logo" style="width:100px;height:50px;">
            </a>
            <ul class="nav navbar-nav pull-right">
                <!--<li class="nav-item">-->
                <!--<a class="nav-link">Login</a>-->
                <!--</li>-->
                <li class="nav-item">
                    <a href="#" id="open-right" class="nav-link"><i class="material-icons">account_circle</i> <?php if ($_SESSION["user"]->hasNewMessages()) echo ("<i class='fa fa-circle msgCircle'></i>");
                        else if ($_SESSION["user"]->hasNewNot()) echo ("<i class='fa fa-circle notCircle'></i>");?></a>
                </li>
            </ul>
        </nav>
        <!--/.navbar -->
    </header>


    <main>

        <div class="container">
            <div class="row">
                <div class="col-md-6">
		  <div class="card-colums">
                    <div class="card">
                        <div class="card-block">

                            <h3>Hello</h3>
                            <h2><?php echo($user->getUserName()); ?></h2>
                            <div id="ratings-div">
                                <h1>Rating: <?php echo($user->getRating()); ?>/5</h1>
                            </div>
                            <div id="details-div">
                                <div id="score">Your score is <?php echo($user->getScore()); ?>!</div>
                                <div id="listed-items">You have <?php echo $current ?> items currently listed!</div>
                                <div id="exchanged-items">You have exchanged <?php echo $user->getNumber() ?> items!
                                </div>
                            </div>

                        </div>
                    </div>
		    <div class="card">
		      <div class="card-block">
		      <a class="help-icon" href="#" data-toggle="tooltip" 
				      data-placement="bottom"
				      title="Edit details as desired. Click
				      'Submit changes' button to confirm change">
			<i class="material-icons">help</i>
		      </a>
			<h3 class="card-title">
			  Personal Details
			</h3>
		      </div>
		      <div id="editDetails" class="card-block">
			<div class="md-form">
			  <i class="fa fa-pencil prefix"></i>
			  <input type="text" id="fname" class="form-control" value="<?php echo $user->getUserName() ?>">
			  <label for="fname">Username</label>
			  <small class="form-control-feedback">
			    usernames must be alphanumeric and at least 3
			    characters long.
			  </small>
			</div>

			<div class="md-form">
			  <i class="fa fa-pencil prefix"></i>
			  <input type="text" id="femail" class="form-control" value="<?php echo $user->getEmail() ?>">
			  <label for="femail">Email</label>
			  <small class="form-control-feedback">
			    emails must be of the form user@host.con
			  </small>
			</div>

			<div class="md-form">
			  <span class="prefix">
			    <a href="#"
				 data-toggle="collapse"
				 data-target="#passwordCollapse"
				 aria-expanded="false"
				 aria-controls="passwordCollapse">
			      <small style="font-size: 10px">change</small>
			      <i class="material-icons">arrow_drop_down</i>
			    </a>
			  </span>
			  <input type="password" id="chpass"
			  class="form-control" placeholder="unchanged" >
			  <label for="chpass" >
			    Current Password
			  </label>
			</div>

			<div id="passwordCollapse" class="collapse">
			  <div class="md-form">
			    <i class="fa fa-pencil prefix"></i>
			    <input type="password" id="cfpass" class="form-control">
			    <label for="cfpass">New Password</label>
			    <small class="form-control-feedback">
			      passwords must be at least 6 characters long.
			    </small>
			  </div>

			  <div class="md-form">
			    <i class="fa fa-pencil prefix"></i>
			    <input type="password" id="fpass" class="form-control">
			    <label for="fpass">Confirm New Password</label>
			    <small class="form-control-feedback">
			      passwords do not match
			    </small>
			  </div>
			</div>

			<label>Allergies and other needs</label>
			<div id="allergyDiv">
			    <input type="checkbox" value="VEGAN" <?php if ($allergens[VEGAN])
			    {
				echo("checked");
			    } ?>> Vegan <br/>
			    <input type="checkbox" value="VEGETARIAN" <?php if ($allergens[VEGETARIAN])
			    {
				echo("checked");
			    } ?>> Vegetarian <br/>
			    <input type="checkbox" value="PEANUT" <?php if ($allergens[PEANUT])
			    {
				echo("checked");
			    } ?>> Peanuts <br/>
			    <input type="checkbox" value="SOY" <?php if ($allergens[SOY])
			    {
				echo("checked");
			    } ?>> Soy <br/>
			    <input type="checkbox" value="GLUTEN" <?php if ($allergens[GLUTEN])
			    {
				echo("checked");
			    } ?>> Gluten <br/>
			    <input type="checkbox" value="LACTOSE" <?php if ($allergens[LACTOSE])
			    {
				echo("checked");
			    } ?>> Lactose <br/>
			    <input type="checkbox" value="HALAL" <?php if ($allergens[HALAL])
			    {
				echo("checked");
			    } ?>> Halal <br/>
			    <input type="checkbox" value="KOSHER" <?php if ($allergens[KOSHER])
			    {
				echo("checked");
			    } ?>> Kosher <br/>
			</div>

		    <button class="btn btn-primary" id="submit">Submit changes</button>

		      </div>
		    </div>
		  </div>
                </div>
                <div class="col-md-6">
		  <div class="card">
		    <div class="card-block">
		      <a class="help-icon" href="#" data-toggle="tooltip" 
				      data-placement="bottom"
				      title="Place marker on new location (by
				      clicking) to change your address. Click
				      'Submit changes' button to confirm change">
			<i class="material-icons">help</i>
		      </a>
		      <h3 class="card-title">Location</h3>
		    </div>
		    <div class="card-block">
		      <label>Address</label>
		      <input id="addressInput" class="controls" type="text" placeholder="Search...">
		      <div id="inputMap" style="width=100%; height: 500px;"></div>
		    </div>
		  </div>
            </div>
        </div>
</div>

</main>


<footer>

</footer>
</div>

<!--Scripts-->
<script src="bootstrap-material-design/js/jquery-3.1.1.min.js"></script>
<script src="bootstrap-material-design/js/tether.min.js"></script>
<script src="bootstrap-material-design/js/bootstrap.min.js"></script>
<script src="bootstrap-material-design/js/mdb.min.js"></script>
<script src="js/enterLocation.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAIMtO0_uKM_0og7IjdV7nBDjH4dtUmVoY&callback=initMap&libraries=places"
        async defer></script>

<script type="text/javascript" src="snap/snap.min.js"></script>
<script type="text/javascript" src="js/sidebar.js"></script>

<script type="text/javascript" src="js/popUps.js"></script>
<script type="text/javascript" src="js/profile.js"></script>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
});
</script>
<!--/.Scripts-->
</body>
