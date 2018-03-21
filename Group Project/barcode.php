<?php
require_once(__DIR__ . "/php/database.php");
require_once(__DIR__ . "/php/user.php");
cSessionStart();
if (!loginCheck())
{
  header("Location: index.php?error=" . urlencode("You must be logged in to do that."));
  exit();
}

$user = $_SESSION["user"];
$allergens = array();


  for ($i = 1; $i <= 128; $i *= 2) $allergens[$i] = $user->checkFlag($i);

  echo ("<script>window.currentlatLng ='" . $user->getLocation()->getLatLong() . "';</script>");
?>

<!DOCTYPE html>
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="dates/bootstrap-material-datetimepicker.css" />

  <!--snap stuff-->
  <meta http-equiv="x-ua-compatible" content="IE=edge"/>
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-touch-fullscreen" content="yes">
  <link rel="stylesheet" type="text/css" href="snap/snap.css"/>

  <!-- fontawesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

  <!-- Material-Design icon library -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!-- Bootstrap Core Stylesheet -->
  <link rel="stylesheet" href="bootstrap-material-design/css/bootstrap.min.css">

  <!-- Material-Design core stylesheet -->
  <link rel="stylesheet" href="bootstrap-material-design/css/mdb.min.css">

  <!-- My Stylesheet -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/newpost.css">

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
            else if ($_SESSION["user"]->hasNewNot()) echo ("<i class='fa fa-circle notCircle'></i>");?></a>
          </li>
        </ul>
      </nav>
      <!--/.navbar -->
    </header>

    <main>

      <div class = "container">
        <h2>Enter a barcode</h2>
        <div class = "row">

          <div class = "col-md-6">
            <div class="card">
              <div class="card-block">
                <div class="card-title"><h4>Item Details</h4></div>
                <div class="md-form">
                  <input type="text" id="barcode" >
                  <label for="uemail">Barcode</label>
                </div>

                <div class="md-form">
                  <input type="text" id="description" <?php if(isset($description)) echo ("value='" . $description . "'");?>>
                  <label for="uemail">Description</label>
                </div>

                <div class="md-form">
                  <p class="text-muted">Flags (Check all that apply):</p>
                  <div id="allergyDiv">
                    <input type="checkbox" value="VEGAN" <?php if ($allergens[VEGAN]) { echo("checked"); }?>> Vegan <br/>
                    <input type="checkbox" value="VEGETARIAN" <?php if ($allergens[VEGETARIAN]) { echo("checked"); }?>> Vegetarian <br/>
                    <input type="checkbox" value="PEANUT" <?php if ($allergens[PEANUT]) { echo("checked"); }?>> Peanuts <br/>
                    <input type="checkbox" value="SOY" <?php if ($allergens[SOY]) { echo("checked"); }?>> Soy <br/>
                    <input type="checkbox" value="GLUTEN" <?php if ($allergens[GLUTEN]) { echo("checked"); }?>> Gluten <br/>
                    <input type="checkbox" value="LACTOSE" <?php if ($allergens[LACTOSE]) { echo("checked"); }?>> Lactose <br/>
                    <input type="checkbox" value="HALAL" <?php if ($allergens[HALAL]) { echo("checked"); }?>> Halal <br/>
                    <input type="checkbox" value="KOSHER" <?php if ($allergens[KOSHER]) { echo("checked"); }?>> Kosher <br/>
                  </div>
                </div>

                <div class="md-form">
                  <input type="text" id="date" class="form-control floating-label">
                  <label for="uemail">Expiry Date</label>
                </div>

              </div>
            </div>


          </div>

          <div class="col-md-6">
            <div class="card">
              <div class="card-block card-title"><h4>Location</h4></div>
              <div class="card-block">
                <input id="addressInput" class="controls" type="text" placeholder="Search...">
                <div id="inputMap" style="width: 100%; height: 20em;"></div>
              </div>
            </div>
            <script src="bootstrap-material-design/js/jquery-3.1.1.min.js"></script>
            <script src="js/enterLocation.js"></script>
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAIMtO0_uKM_0og7IjdV7nBDjH4dtUmVoY&callback=initMap&libraries=places" async defer></script>
          </div>
        </div>

        <button id="tSubmit" class="btn btn-primary z-depth-2" type="submit">Post</button>
      </div>
    </main>

    <footer></footer>
  </div>

  <!--Scripts-->
  <script src="bootstrap-material-design/js/jquery-3.1.1.min.js"></script>
  <script src="bootstrap-material-design/js/tether.min.js"></script>
  <script src="bootstrap-material-design/js/bootstrap.min.js"></script>
  <script src="bootstrap-material-design/js/mdb.min.js"></script>
  <script src="dates/moments.js"></script>
  <script src="dates/bootstrap-material-datetimepicker.js"></script>

  <script>
  //todo make expiry field load into date
  $("#date").bootstrapMaterialDatePicker({format:"DD/MM/YYYY", weekStart : 0, time: false,  minDate : new Date()});

  $("#tSubmit").on("click", function()
  {
    var checked = [];
    $("#allergyDiv").find("input:checked").each(function()
    {
      checked.push($(this).attr("value"));
    });

    // on submission, make a call to the barcode api to look up the product
    $.get("https://pod.opendatasoft.com/api/records/1.0/search/?dataset=pod_gtin&q=" + $("#barcode").val(), function(itemdata)
    {
      var itemfields = itemdata["records"][0]["fields"];
      // itemfields has lots of data - we only need itemfields.gtin_nm for the product name and temfields.gtin_img for the product image url

      var data =
      {
        title: itemfields.gtin_nm,
        description: $("#description").val(),
        flags: checked,
        location: window.currentlatLng.toString(),
        expiry: $("#date").val()
      };

      if (window.editing) data.id = window.editing;

      $.post("php/newitempost.php", data, function(data)
      {
        var res = JSON.parse(data);

        if(res.hasOwnProperty("error"))
        {
          alert(res["error"]);
        }
        else
        {
          var postid = res["postid"];
          // use the postid of the newly created item (return from the post) to post to imagefromurl.php, to add the image url to the listing
          $.post("php/imagefromurl.php?postid=" + postid + "&url=" + encodeURIComponent(itemfields.gtin_img));
          // async redirect to the new listing
          window.location.href = "listing.php?id=" + postid;
        }
      });
    });
  });

  //when the user uploads photo(s), update the preview div to show them
  document.getElementById("photo").onchange = function ()
  {
    $("#preview").html("");
    $("#preview").show();
    for(var i = 0; i < this.files.length; i++)
    {
      var reader = new FileReader();
      reader.onload = function (e) {
        console.log(e);
        $("#preview").append("<img src='" + e.target.result + "'>");
      };
      reader.readAsDataURL(this.files[i]);
    }
  };

  // on loading, load and show the images for the current listing
  $.get("php/images.php?postid=" + window.editing, function(data){
    var images = JSON.parse(data);
    for(var i = 0; i < images.length; i++)
    {
      $("#allphotos").append("<img src='" + images[i] + "'>")
    }
  });
  </script>

  <script type="text/javascript" src="snap/snap.min.js"></script>
  <script type="text/javascript" src="js/sidebar.js"></script>
  <!--/.Scripts-->

</body>
