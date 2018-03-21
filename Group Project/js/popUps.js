function addNotification(notificationText, typeClass) {
   notification = $("#notificationsDiv .notification.prototype").clone(); 
   notification.removeClass("prototype");
   notification.find(".text").text(notificationText);
   notification.addClass(typeClass);
   $("#notificationsDiv").append(notification);
   window.setTimeout(function() {
       notification.alert().alert("close");
   }, 
   4000);
}

