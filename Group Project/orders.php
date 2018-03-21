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
  <link rel="stylesheet" href="css/orders.css">

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
      <a href = "index.php">
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


    <div id="ordersWrapper" class="container">

      <div id="header">
  <button type="button" class="btn btn-primary" onclick="location.href='newpost.php'">NEW LISTING</button>
  <button type="button" class="btn btn-primary" onclick="location.href='barcode.php'">SCAN BARCODE</button>

	<h2>Your Orders</h2>
      </div>
      <div class="row">

	<!--nav-links-->
	<div class="nav">
	  <ul class="nav nav-pills">
	    <li class="nav-item">
	      <a class="nav-link active" href="#orders_current" data-toggle="pill">Current Orders</a>
	    </li>
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Current Listings</a>
	      <div class="dropdown-menu">
		<a class="dropdown-item" href="#listings_stillUp" data-toggle="pill">Still Up</a>
		<a class="dropdown-item" href="#listings_reserved" data-toggle="pill">Reserved</a>
	      </div>
	    </li>
	    <li class="nav-item">
	      <a class="nav-link" href="#orders_history" data-toggle="pill">Order History</a>
	    </li>
	    <li class="nav-item">
	      <a class="nav-link" href="#listings_history" data-toggle="pill">Listing History</a>
	    </li>
	  </ul>
	</div>
	<!--./nav-links-->

	<!--card-prototypes-->
	    <div class="card current-prototype">
	      <div class="card-block order-img">
		        <img class = "itemimage" id="card_image"/>
	      </div>
	      <div class="card-block">
              <h4> <a href="#" class="card-title">Food orders current </a></h4>
              <p class="text-info currentmsg">The poster has rated this exchange.</p>
		<button type="button" class="btn btn-primary" data-orderid="" data-toggle="modal" data-target ="#recievedModal">Rate</button>
		<button type="button" class="btn btn-danger cancelButton" data-orderid="">Cancel</button>
	      </div>
	    </div>

	    <div class="card history-prototype">
	      <a href="#"></a>
	      <div class="card-block order-img">
		<img class = "itemimage" id="card_image"/>
	      </div>
	      <div class="card-block">
		<h4 class="card-title">Food order history</h4>
		<p class="card-text text-muted timestamp">yesterday</p>
	      </div>
	    </div>
	<!--./card-prototypes-->

	<!--tab-content-->
	<div class="tab-content">
	  <div class="tab-pane active" id="orders_current" role="tabpanel">Current Orders</div>
	  <div class="tab-pane" id="orders_history" role="tabpanel">Order History</div>
	  <div class="tab-pane" id="listings_stillUp" role="tabpanel">Still Up</div>
	  <div class="tab-pane" id="listings_reserved" role="tabpanel">Reserved</div>
	  <div class="tab-pane" id="listings_history" role="tabpanel"> Listing History</div>
	</div>


      </div>


      <!--./tab-content-->

    </div>


  </main>

  <footer>

  </footer>
</div>

<!-- Modal -->
<div class="modal fade" id="recievedModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
  <div class="modal-dialog" role="document">
    <!--Content-->
    <div class="modal-content">
      <!--Header-->
      <div class="modal-header">
        <h4 class="modal-title w-100" id="myModalLabel">Please rate this item and your experience</h4>
      </div>
      <!--Body-->
      <div class="modal-body">
        <form>
          <input type="radio" name="rating" value="1" checked> 1 *<br>
          <input type="radio" name="rating" value="2" checked> 2 *<br>
          <input type="radio" name="rating" value="3" checked> 3 *<br>
          <input type="radio" name="rating" value="4" checked> 4 *<br>
          <input type="radio" name="rating" value="5" checked> 5 *<br>
        </form>
      </div>
      <!--Footer-->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">SUBMIT</button>
      </div>
    </div>
    <!--/.Content-->
  </div>
</div>


<!--Scripts-->
<script src="bootstrap-material-design/js/jquery-3.1.1.min.js"></script>
<script src="bootstrap-material-design/js/tether.min.js"></script>
<script src="bootstrap-material-design/js/bootstrap.min.js"></script>
<script src="bootstrap-material-design/js/mdb.min.js"></script>
<script src="js/popUps.js"></script>
<script src="js/mylistings.js"></script>

<script type="text/javascript" src="snap/snap.min.js"></script>
<script type="text/javascript" src="js/sidebar.js"> </script>
<!--/.Scripts-->

</body>
