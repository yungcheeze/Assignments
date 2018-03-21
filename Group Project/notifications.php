<?php
require_once "php/user.php";
require_once "php/database.php";
cSessionStart();
if (!loginCheck())
{
    header("Location: index.php?error=" . urlencode("You must be logged in to do that."));
    exit();
}
$userid = $_SESSION["user"]->getUserID();
Database::getConnection()->query("UPDATE UsersTable SET newNot=0 WHERE id=" . $userid);
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
  <link rel="stylesheet" href="css/notifications.css">

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
          <a class="nav-link" href="inbox.php">Messages <?php if ($_SESSION["user"]->hasNewMessages()) echo ("<i class='fa fa-circle msgCircle'></i>");?></a>
          <div class="mask"></div>
        </li>
      </div>
      <div class="view overlay hm-white-slight">
        <li class="nav-item">
          <a class="nav-link" href="#">Notifications</a>
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
                                                                                                        if ($_SESSION["user"]->hasNewNot()) echo ("<i class='fa fa-circle notCircle'></i>");?></a>
        </li>
      </ul>
    </nav>
    <!--/.navbar -->
  </header>


  <main>


    <div id="ordersWrapper" class="container">

      <div id="header">
	<h2>Notifications</h2>
      </div>
      <div class="row">

	<!--nav-links-->
	<div class="nav">
	  <ul class="nav nav-tabs">
	    <li class="nav-item">
	      <a class="nav-link active" href="#notifications" data-toggle="tab">Notifications</a>
	    </li>
	    <li class="nav-item">
	      <a class="nav-link" href="#watchlist" data-toggle="tab">Item Watchlist</a>
	    </li>
	  </ul>
	</div>
	<!--./nav-links-->

	<!--card-prototypes-->
	    <div class="card notification-prototype">
	      <div class="card-block">
	        <div class="card-text notification">Your watched item is available</div>
	        <div class="card-text text-muted timestamp">12/10/17 15:59</div>
	      </div>
	    </div>
	    <li class="list-group-item keyword-prototype">
	      <span class="text">FOOD</span>
	      <a href="#"><i class="material-icons delete-icon">close</i></a>
	    </li>
	<!--./card-prototypes-->

	<!--tab-content-->
	<div class="tab-content">
	  <div class="tab-pane active" id="notifications" role="tabpanel">
	  </div>
	  <div id="watchlist" class="tab-pane" role="tabpanel">
	    <div class="col-md-6">
	      <h3>Add Keywords</h3>
	      <br>
	      <form action="#">
	      </form>
	      <div class="md-form">
		<i class="fa fa-pencil prefix"></i>
		<input type="text" id="wordInput" id="form2" class="form-control">
		<label for="wordInput">Keyword</label>
	      </div>
	      <div class="md-form">
		<button id="newKwdBtn" class="btn btn-primary">Add</button>
	      </div>
	      </form>
	    </div>
	    <div class="col-md-6">
	      <h3>Current Keywords</h3>
	      <br>
	      <ul class="list-group" id="keywords">
	      </ul>
	    </div>
	  </div>
	</div>


      </div>


      <!--./tab-content-->

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
<script src="js/notifications.js"></script>

<script type="text/javascript" src="snap/snap.min.js"></script>
<script type="text/javascript" src="js/sidebar.js"> </script>
<!--/.Scripts-->

</body>
