//Form submission handler
$("#submit").click(function()
	{
	    if($("#fpass").val() != $("#cfpass").val()) return; //todo error stuff and validation

	    var checked = [];
	    $("#allergyDiv").find("input:checked").each(function()
		    {
			checked.push($(this).attr("value"));
		    });

	    $.post("php/membership/changeDetails.php",
		    {
			uname: $("#fname").val(),
			email: $("#femail").val(),
			flags: checked,
			location: window.currentlatLng.toString(),
			pass: $("#fpass").val(),
			cpass: $("#chpass").val()
		    }, function(data) {
			console.log(data);
			window.currentlatLng = undefined;
			window.localStorage.setItem("postResult", JSON.stringify(data))
			    window.location.reload();
		    });
	});


$(document).ready(function() {
    //hide validation stuff
    $("#editDetails .form-control-feedback").hide(); //hide validation text

    //show pop-up if needed
    var response = JSON.parse(localStorage.getItem("postResult"));
    if (response !== null) {
	if (response.success !== undefined) {
	    addNotification(response.success, "alert-success")

	} else if (response.error !== undefined) {
	    addNotification("Error" + ": " + response.error, "alert-danger")
	}
	localStorage.removeItem("postResult");
    }
});

//Validate username
$("#fname").change(function() {
    var text = $(this).val();
    var formDiv = $(this).parent();
    var usernameRE = /\w{3,}/;
    if (!matchExact(usernameRE, text)) {
	formDiv.addClass("has-danger");
	formDiv.find(".form-control-feedback").show();
    } else {
	$(this).data("ready", true);
	formDiv.removeClass("has-danger");
	formDiv.find(".form-control-feedback").hide();
    }
});

//Validate email
$("#femail").change(function() {
    var text = $(this).val();
    var formDiv = $(this).parent();
    var emailRE = /[\w\-\_]+\@[\w\-\_]+(\.\w{2,5})+/;
    if (!matchExact(emailRE, text)) {
	formDiv.addClass("has-danger");
	formDiv.find(".form-control-feedback").show();
    } else {
	$(this).data("ready", true);
	formDiv.removeClass("has-danger");
	formDiv.find(".form-control-feedback").hide();
    }
});

//Validate new password
$("#cfpass").change(function() {
    var text = $(this).val();
    var formDiv = $(this).parent();
    if (text.length < 6) {
	formDiv.addClass("has-danger");
	formDiv.find(".form-control-feedback").show();
    } else {
	$(this).data("ready", true);
	formDiv.removeClass("has-danger");
	formDiv.find(".form-control-feedback").hide();
    }
});

//check that passwords match
$("#fpass").change(function() {
    var text = $(this).val();
    var formDiv = $(this).parent();
    if (text !== $("#cfpass").val()) {
	formDiv.addClass("has-danger");
	formDiv.find(".form-control-feedback").show();
    } else {
	$(this).data("ready", true);
	formDiv.removeClass("has-danger");
	formDiv.find(".form-control-feedback").hide();
    }
});

function matchExact(r, str) {
    var match = str.match(r);
    return match != null && str == match[0];
}
