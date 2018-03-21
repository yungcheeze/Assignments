//login stuff ----
$("#registerBtn").on("click", function()
{
    $.post("php/membership/register.php",
	{
        email: $("#email").val(),
        username: $("#username").val(),
        password: $("#password").val(),
        location: window.currentlatLng.lat() + ',' + window.currentlatLng.lng(),
        flags: createFlags()
    }, function(data)
    {
        window.currentlatLng = undefined;

        $("#registrationModal").modal("hide");
        $("#loginModal").modal("show");
        resetReg();
    }).fail(function(response)
    {
        alert('Error: ' + response.responseText);
    });
});

$(document).ready(function()
{
	initMap();
});

function createFlags()
{
    var selected = [];
    $("#dietary").find(":selected").each(function()
    {
        selected.push($(this).val());
    });

    $("#allergens").find(":selected").each(function()
    {
        selected.push($(this).val());
    });

    return selected;
}

$("#nextBtn").click(function()
{
    if ($("#regDiv1").css("display") !== "none")
	{
        $("#regDiv1").fadeOut(function()
		{
			$("#regModalLabel").html("Location");
            $("#nextBtn").prop("disabled", true);
            $("#regDiv2").fadeIn();
            reInitMap();
		});
	}
	else
	{
		$("#regDiv2").fadeOut(function()
		{
			$("#regModalLabel").html("Allergens");
            $("#registerBtn").show();
            $("#nextBtn").hide();
			$("#regDiv3").fadeIn();
		});
	}
});

function resetReg()
{
    $(".regDivs").hide();
    $("#regModalLabel").html("User Details");
    $("#regDiv1").show();
    $("#nextBtn").show();
    $("#nextBtn").prop("disabled", false);
	window.currentlatLng = undefined;
    $("#registerBtn").hide();
    $("#registrationModal .form-control").val("");
    reInitMap();
}

$("#regCancel").on("click", resetReg);

$("#loginBtn").on("click", function()
{
    $.post("php/membership/login.php",
    {
        email: $("#uemail").val(),
        password: $("#upass").val()
    }, function(data)
    {
	window.localStorage.setItem("loginResponse", JSON.stringify(data));
	window.location.replace("index.php");
    }).fail(function(response)
    {
        alert('Error: ' + response.responseText);
    });
});

$(document).ready(function () {
    var response =JSON.parse(localStorage.getItem("loginResponse"));
    if (response !== null) {
	if (response.success !== undefined) {
	    addNotification(response.success, "alert-success")
	    
	} else if (response.error !== undefined) {
	    addNotification("Login Failed" + ": " + response.error, "alert-danger")
	}
	localStorage.removeItem("loginResponse");
    }
    
});

$("#registrationBtn").click(function () {
    //set "ready" attribute to false (set to true when form input is valid)
    $("#regDiv1 input").each(function (index, obj) {
        $(this).data("ready", false);
    });
    $("#registrationModal .form-control-feedback").hide(); //hide validation text
    $("#nextBtn, registerBtn").prop("disabled", true);
});


$("#regDiv1 input").on("input", function() {
   var inputId = $(this).attr("id"); 
   var text = $(this).val();
   var formDiv = $(this).parent();
   var emailRE = /[\w\-\_]+\@[\w\-\_]+(\.\w{2,5})+/;
   var usernameRE = /\w{3,}/;
   switch (inputId) {
       case 'username':
	   if (!matchExact(usernameRE, text)) {
	       $(this).data("ready", false);
	       formDiv.addClass("has-danger");
	       formDiv.find(".form-control-feedback").show();
	   } else {
	       $(this).data("ready", true);
	       formDiv.removeClass("has-danger");
	       formDiv.find(".form-control-feedback").hide();
	   }
	   break;
       case 'email':
	   if (!matchExact(emailRE, text)) {
	       $(this).data("ready", false);
	       formDiv.addClass("has-danger");
	       formDiv.find(".form-control-feedback").show();
	   } else {
	       $(this).data("ready", true);
	       formDiv.removeClass("has-danger");
	       formDiv.find(".form-control-feedback").hide();
	   }
	   break;
       case 'password':
	   if (text.length < 6) {
	       $(this).data("ready", false);
	       formDiv.addClass("has-danger");
	       formDiv.find(".form-control-feedback").show();
	   } else {
	       $(this).data("ready", true);
	       formDiv.removeClass("has-danger");
	       formDiv.find(".form-control-feedback").hide();
	   }
	   break;
       case 'passwordConfirm':
	   if (text !== $("#password").val()) {
	       $(this).data("ready", false);
	       formDiv.addClass("has-danger");
	       formDiv.find(".form-control-feedback").show();
	   } else {
	       $(this).data("ready", true);
	       formDiv.removeClass("has-danger");
	       formDiv.find(".form-control-feedback").hide();
	   }
	       break;
   }

   var allReady = true;
   $("#regDiv1 input").each(function (index, obj) {
       allReady = $(this).data("ready") && allReady;
   });
   $("#nextBtn, registerBtn").prop("disabled", !allReady);
});

function matchExact(r, str) {
   var match = str.match(r);
   return match != null && str == match[0];
}
//enable submit on enter
$("#regDiv1 input").keypress(function(event) {
    if(!$("#nextBtn").prop("disabled") && event.keyCode == 13){
	$("#nextBtn").click();
    }
});
