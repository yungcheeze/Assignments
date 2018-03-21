$(document).ready(function()
{
  // add cards to the index page, and add their correct images
  $.get("php/search.php", function (data)
  {
    var posts = JSON.parse(data);
    // sort the cards by distance from the user
    posts.sort(function(a,b){return a.distance - b.distance});
    // load card.html to use as the base of each card
    $.get("ajax/card.html", function (data)
    {
      // perfrom beforeSend first to make sure data-itemid has been correctly added so itemimage.js can use it, then call fixImgs to add the images based on their data-itemid
      $.ajax({url: "js/itemimage.js",
      dataType: "script",
      success: function(){
        fixImgs();
      },
      beforeSend: function()
      {
        tmp = $("<out>").append(data);
        $(".item-card").each(function (i, obj)
        {
          if (i < posts.length)
          {
            // modify the card.html template to add the info for the specific listing
            name = posts[i]["title"];
            name = name ? posts[i]["title"] : "Untitled";
            tmp.find("#title").html(name);
            var distance = posts[i].hasOwnProperty('distance')? posts[i].distance.toFixed(1) + " miles away" : "--";
            tmp.find("#distance").html(distance);
            tmp.find("#link").attr("href", "listing.php?id=" + posts[i].id);
            tmp.find("#card_image").attr("data-itemid", posts[i].id);
          }
          else
          {
            // if there are no listings to add, but more cards need to be filled, add empty cards
            tmp.find("#title").html("--");
            tmp.find("#distance").html("--");
            // use default image
            tmp.find("#itemimg").attr("src","img/vege-card.jpg");
          }
          $(obj).html(tmp.html());
          $(obj).attr("id", i.toString());
        });
      }});
    });
  });
});
