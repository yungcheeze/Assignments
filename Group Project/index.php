<?php
include_once "php/user.php";
cSessionStart();
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
    <!--Bootstap-Select Stylesheet-->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css">

    <!-- Material-Design core stylesheet -->
    <link rel="stylesheet" href="bootstrap-material-design/css/mdb.min.css">

    <!-- My Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
  <div id="notificationsDiv">
    <div class='alert alert-dismissable fade in notification prototype' role='alert'>
      <a href='#' class='close' data-dismiss='alert' aria-label='Close'>&times;</a>
      <div class="text"></div>
    </div>
        <?php
        if (isset($_GET["error"]))
        {
            echo
            (
                "<div class='alert alert-danger alert-dismissable fade in' role='alert'>
                <a href='#' class='close' data-dismiss='alert' aria-label='Close'>&times;</a>"
                . $_GET["error"] . "</div>"
            );
        }
        ?>
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
                    <a class="nav-link" href="inbox.php">Messages <?php if (loginCheck() && $_SESSION["user"]->hasNewMessages()) echo ("<i class='fa fa-circle msgCircle'></i>");?></a>
                    <div class="mask"></div>
                </li>
            </div>
            <div class="view overlay hm-white-slight">
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">Notifications and alerts <?php if (loginCheck() && $_SESSION["user"]->hasNewNot()) echo ("<i class='fa fa-circle notCircle'></i>");?></a>
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

<!--login modal-->
<div class="modal fade" id="loginModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <!--Content-->
        <div class="modal-content">
            <!--Header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100" id="myModalLabel">Login</h4>
            </div>
            <!--Body-->
            <div class="modal-body submittable" trigger-btn="#loginBtn" tabindex="0">
                <div class="md-form">
                    <i class="fa fa-envelope prefix"></i>
                    <input type="text" id="uemail" class="form-control">
                    <label for="uemail">Your email</label>
                </div>

                <div class="md-form">
                    <i class="fa fa-lock prefix"></i>
                    <input type="password" id="upass" class="form-control">
                    <label for="upass">Your password</label>
                </div>
            </div>
            <!--Footer-->
            <div class="modal-footer">
                <button type="button" id="registrationBtn" class="btn btn-secondary" data-dismiss="modal"
                        data-toggle="modal" data-target="#registrationModal">Register
                </button>
                <button type="button" class="btn btn-primary" id="loginBtn">Login</button>
            </div>
        </div>
        <!--/.Content-->
    </div>
</div>
<!--/.login modal-->
<!--registration modal-->
<div class="modal fade" id="registrationModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <!--Content-->
        <div class="modal-content">
            <!--Header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100" id="regModalLabel">User Details</h4>
            </div>
            <!--Body-->
            <div class="modal-body" id="regBody">
                <div id="regDiv1">
                    <div class="md-form">
                        <input type="text" id="username" class="form-control">
                        <label for="form2">Username</label>
			<small class="form-control-feedback">
			  usernames must be alphanumeric and at least 3
			  characters long.
			</small>
                    </div>

                    <div class="md-form">
                        <input type="email" id="email" class="form-control">
                        <label for="form4">Email</label>
			<small class="form-control-feedback">
			  emails must be of the form user@host.con
			</small>
                    </div>

                    <div class="md-form">
                        <input type="password" id="password" class="form-control">
                        <label for="form4">Password</label>
			<small class="form-control-feedback">
			  passwords must be at least 6 characters long.
			</small>
                    </div>

                    <div class="md-form">
                        <input type="password" id="passwordConfirm" class="form-control">
                        <label for="form4">Confirm Password</label>
			<small class="form-control-feedback">
			  passwords do not match
			</small>
                    </div>
                </div>

                <div id="regDiv2" class="regDivs">
                    <input id="addressInput" class="controls" type="text" placeholder="Search...">
                    <div id="inputMap"></div>
                    <p class="text-md-center">Please click/search to set your location.</p>
                </div>

                <div id="regDiv3" class="regDivs">
                    <div class="form-group">
                        <h4>Dietary Preferences</h4>
                        <select id="dietary" class="selectpicker" multiple>
                            <option value="HALAL">Halal</option>
                            <option value="KOSHER">Kosher</option>
                            <option value="VEGETARIAN">Vegeterian</option>
                            <option value="VEGAN">Vegan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <h4>Allergies</h4>
                        <select id="allergens" class="selectpicker" multiple>
                            <option value="NUTS">Nuts</option>
                            <option value="GLUTEN">Gluten</option>
                            <option value="SOY">Soy</option>
                            <option value="LACTOSE">Lactose</option>
                        </select>
                    </div>
                </div>

            </div>
            <!--Footer-->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="regCancel" data-dismiss="modal">Cancel</button>
                <button type="button" id="nextBtn" class="btn btn-primary">Next</button>
                <button type="button" class="btn btn-primary" id="registerBtn">Submit</button>
            </div>
        </div>
        <!--/.Content-->
    </div>
</div>
<!--/.login modal-->

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

                <?php
                if (loginCheck())
                {
                    echo '<li class="nav-item"><a href="#" id="open-right" class="nav-link"><i class="material-icons">account_circle</i>';
                    if ($_SESSION["user"]->hasNewMessages()) echo ("<i class='fa fa-circle msgCircle'></i>");
                    else if ($_SESSION["user"]->hasNewNot()) echo ("<i class='fa fa-circle notCircle'></i>");
                    echo '</a></li>';
                } else
                {
                    echo '
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-toggle="modal" data-target="#loginModal"><i class="material-icons">lock</i></a>
                    </li>';
                }
                ?>
            </ul>
        </nav>
        <!--/.navbar -->
    </header>


    <!-- background image -->
    <div class="view hm-black-strong search-jumbotron">
        <div class="full-bg-img flex-center">
            <!--Search Bar-->
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <form action="search.php">
                    <input class="form-control" name="searchText" type="text" placeholder="Search">
                </form>
            </div>
            <div class="col-md-2"></div>
            <!--/.Search Bar-->
        </div>
    </div>
    <!--/.background image -->

    <main>
        <!--Item-Carousel-->
        <div class="container">
            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-10">
                    <div id="ItemCarousel" class="carousel slide" data-ride="carousel">
                        <!--Indicators-->
                        <ol class="carousel-indicators">
                            <li data-target="#ItemCarousel" data-slide-to="0" class="active"></li>
                            <li data-target="#ItemCarousel" data-slide-to="1"></li>
                            <li data-target="#ItemCarousel" data-slide-to="2"></li>
                        </ol>
                        <!--/.Indicators-->

                        <!--Slides Wrapper-->
                        <div class="carousel-inner " role="listbox">
                            <div class="carousel-item active">
                                <div class="row">
                                    <div class="col-xs-4 item-card"></div>
                                    <div class="col-xs-4 item-card"></div>
                                    <div class="col-xs-4 item-card"></div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="row">
                                    <div class="col-xs-4 item-card"></div>
                                    <div class="col-xs-4 item-card"></div>
                                    <div class="col-xs-4 item-card"></div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="row">
                                    <div class="col-xs-4 item-card"></div>
                                    <div class="col-xs-4 item-card"></div>
                                    <div class="col-xs-4 item-card"></div>
                                </div>
                            </div>
                        </div>
                        <!--/.Slides Wrapper-->
                    </div>
                </div>
                <div class="col-md-1"></div>
            </div>
        </div>
        <!--/.Item-Carousel-->
    </main>

    <footer>
    </footer>
</div>

<!--Scripts-->
<script src="bootstrap-material-design/js/jquery-3.1.1.min.js"></script>
<script src="bootstrap-material-design/js/tether.min.js"></script>
<script src="bootstrap-material-design/js/bootstrap.min.js"></script>
<script src="bootstrap-material-design/js/mdb.min.js"></script>
<script src="js/cards.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAIMtO0_uKM_0og7IjdV7nBDjH4dtUmVoY&libraries=places"
        async defer></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>

<script type="text/javascript" src="snap/snap.min.js"></script>
<script type="text/javascript" src="js/sidebar.js"></script>
<?php
    if (!loginCheck())
    {
      echo ' <script>snapper.disable();</script>';
    }
?>
    <script type="text/javascript">
    //setup userAllergens selector
    $('.selectpicker').selectpicker();
</script>
<script src="js/enterLocation.js"></script>
<script src="js/popUps.js"></script>
<script src="js/index.js"></script>
<!--/.Scripts-->
</body>
