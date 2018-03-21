<?php
require_once "php/user.php";
require_once "php/database.php";
cSessionStart();
if (!loginCheck())
{
    header("Location: index.php?error=" . urlencode("You must be logged in to do that."));
    exit();
}
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
    <link rel="stylesheet" href="css/search.css">

</head>

<body>
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
            <a href = "index.php">
          <img src="img/Cupboard.png" alt="logo" style="width:100px;height:50px;">
        </a>
            <ul class="nav navbar-nav pull-right">
                <!--<li class="nav-item">-->
                <!--<a class="nav-link">Login</a>-->
                <!--</li>-->
                <li class="nav-item">
                    <a href="#" id="open-right" class="nav-link"><i class="material-icons">account_circle</i> <?php if ($_SESSION["user"]->hasNewMessages()) echo ("<i class='fa fa-circle msgCircle'></i>");
                        else if ($_SESSION["user"]->hasNewNot()) echo ("<i class='fa fa-circle notCircle'></i>");?></a>                </li>
            </ul>
        </nav>
        <!--/.navbar -->
    </header>


    <main>

        <div class="container search-container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <form class="z-depth-1" action="javascript:populateSearchResults();">
                        <div class="input-group md-form">
                            <span id="showFilters" class="input-group-addon waves-effect"><i class="material-icons">menu</i></span>
                            <input class="form-control" type="text" id="searchBox" placeholder="Search">
                            <span id="searchBtn" class="input-group-addon waves-effect"><i class="material-icons">search</i></span>
                        </div>
                    </form>
                    <div class="search-options-container card light-blue lighten-4 z-depth-0">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Dietary Requirements</h4>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" value="HALAL" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(HALAL)) { echo("checked"); }?>> Halal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" value="KOSHER" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(KOSHER)) { echo("checked"); }?>> Kosher
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox"  value="VEGETARIAN" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(VEGETARIAN)) { echo("checked"); }?>> Vegeterian
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" value="VEGAN" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(VEGAN)) { echo("checked"); }?>> Vegan
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4>Allergies</h4>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" value="PEANUT" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(PEANUT)) { echo("checked"); }?>> Peanuts
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" value="GLUTEN" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(GLUTEN)) { echo("checked"); }?>> Gluten
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" value="SOY" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(SOY)) { echo("checked"); }?>> Soy
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" value="LACTOSE" name="allergyCheck"
                                            <?php if ($_SESSION["user"]->checkFlag(LACTOSE)) { echo("checked"); }?>> Lactose
                                    </label>
                                </div>
                            </div>

                        </div>
			<div class="row">
			  <div class="col-xs-6">
                  <label for="sortBy">Sort by: </label>
			    <div class="dropdown" id="sortBy">
			      <a aria-expanded="false" aria-haspopup="true" role="button" data-toggle="dropdown" class="dropdown-toggle" href="#">
				<span id="selected">Distance</span><span class="caret"></span></a>
			      <ul class="dropdown-menu">
				<li><a href="#">Distance</a></li>
				<li><a href="#">Soonest Expiry</a></li>
				<li><a href="#">Most Recent</a></li>
				<li><a href="#">User Rating</a></li>
				<li><a href="#">User Score</a></li>
			      </ul>
			    </div>
			  </div>
			  <div class="col-xs-6">
			    <div id="refineBtn">
			      <button type="button" class="btn btn-primary">Apply Filters</button>
			    </div>
			  </div>
			</div>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>

	<div class="container">
	  <div class="row">
	    <nav class="nav">
	      <ul class="nav nav-tabs justify-content-center">
		<li class="nav-item">
		  <a class="nav-link active" href="#list" data-toggle="tab">List</a>
		</li>
		<li class="nav-item">
		  <a id="mapShow" class="nav-link" href="#map" data-toggle="tab">Map</a>
		</li>
	      </ul>
	    </nav>

	    <div class="tab-content">
	      <div class="tab-pane active" id="list" role="tabpanel">
		<div class="" id="results">
		</div>
	      </div>
	      <div class="tab-pane" id="map" role="tabpanel">
		<div id="resultsMap"></div>
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
<script src="js/search.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAIMtO0_uKM_0og7IjdV7nBDjH4dtUmVoY&libraries=places" async defer></script>

<script type="text/javascript" src="snap/snap.min.js"></script>
<script type="text/javascript" src="js/sidebar.js"></script>
<!--/.Scripts-->
</body>
