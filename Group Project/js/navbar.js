/**
 * Created by Ucizi on 29/10/16.
 */
$(document).ready(function () {
    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("drawer-open");
    });
});
