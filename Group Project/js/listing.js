// when the deletebutton of a listing is clicked:
$("#deleteBtn").on("click", function()
{
  // post that you are deleting it to postTools.php
  $.post("php/post/postTools.php",
  {postID: $(this).data("pid"), delete: true} ,
  function(data){
    console.log(data);
    localStorage.setItem("notification", "Item Deleted");
    window.location.href="orders.php";
  }
  );
});
