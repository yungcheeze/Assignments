$(document).ready(function() {
    //fill notifications
    $.get("php/notifications/notifyUser.php?withTime&notifs", function(data) {
	var notifications = JSON.parse(data);
	$.each(notifications, function(index, notification) {
	    var card = $(".notification-prototype").clone();
	    card.removeClass("notification-prototype");
	    card.find(".notification").html(notification.text);
	    card.find(".timestamp").text(notification.time);
	    $("#notifications").append(card);
	});
    });
    //fillkeywords
    fillKeywords();
    //add keyword
    //TODO validate input
    $("#newKwdBtn").click(function()
	{
		$.post("php/notifications/reservedWords.php", {word: $("#wordInput").val()},
			function (data) {
				fillKeywords();
			});
	});
    //delete keyword
    $(document).on("click", '.list-group-item a', function(event) { 
	event.preventDefault();
	var str = $(this).prev().text();
	$.post("php/notifications/reservedWords.php",{word: str, delete: true},
		function (data) {
		    fillKeywords();
		});
    });
});

function fillKeywords() {
    $.get("php/notifications/reservedWords.php", function(data) {
	$("#keywords.list-group").html("");
	var keywords = JSON.parse(data);
	$.each(keywords, function (index, keyword) {
	    var listItem = $(".keyword-prototype").clone();
	    listItem.removeClass("keyword-prototype");
	    listItem.find(".text").text(keyword.toLowerCase());
	    $("#keywords.list-group").append(listItem);
	});
	if (keywords.length == 0) {
	    var listItem = $(".keyword-prototype").clone();
	    listItem.removeClass("keyword-prototype");
	    listItem.find(".text").text("NO KEYWORDS");
	    listItem.find(".text").addClass("text-muted");
	    listItem.find("a").remove();
	    $("#keywords.list-group").append(listItem);
	}
    });
}
