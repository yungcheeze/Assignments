// USE THIS SCRIPT TO SHOW THE IMAGE FOR THE ITEM ID SPECIFIED IN data-itemid
// FILL THE PAGE WITH IMGS OF THE CLASS itemimage, SET THIER data-itemid AND THEN RUN THIS SCRIPT TO ADD THE SAVED IMAGE / DEFAULT IMAGE TO THEM

// call to add the primary image of the specific listing to all imgs of class .itemimage in the document
function fixImgs()
{
  $.each($(".itemimage"), function(){
    fix($(this));
  });
}

// call to add the primary image of the specific listing to all imgs of class .itemimage in the specified element (paramter)
function fixImg(card)
{
  fix(card.find(".itemimage"));
}

// call to add not just the primary image of the specific listing, but all images of a listing to all divs of class .allitemimages in the document
function addAllImgs()
{
  $.each($(".allitemimages"), function(){
    addAllImg($(this));
  });
}

// call to add not just the primary image of the specific listing, but all images of a listing to all divs of class .allitemimages in the specified element (paramter)
function addAllImg(div)
{
  // data-itemid of the div stores the id of the listing to show the image of
  var id = div.attr("data-itemid");
  // get the list of image urls from images.php
  $.get("php/images.php?postid=" + id, function(data)
  {
    try
    {
      var images = JSON.parse(data);
      console.log(images);
      div.append("<div class ='carousel-item active'><div class='view hm-grey-slight'><img src='" + images[0] + "' alt='IMAGE'></div></div>");
      for(var i = 1; i < images.length; i++)
      {
        // add an img with the current url to the div
        div.append("<div class ='carousel-item'><div class='view hm-grey-slight'><img src='" + images[i] + "' alt='IMAGE'></div></div>");
      }

      // if no images are returned, or there is an error, add only the default image
      if(images.length == 0)
      {
	  div.html("<div class ='carousel-item active'><div class='view hm-grey-slight'><img src='" +  "img/vege-card.jpg" + "' alt='IMAGE'></div></div>");
      }
    }
    catch(e)
    {
      div.html("<div class ='carousel-item active'><div class='view hm-grey-slight'><img src='" +  "img/vege-card.jpg" + "' alt='IMAGE'></div></div>");
    }
  });
}

// add the listings primary image to the img (parameter)
function fix(imgview)
{
  // data-itemid of the imgview stores the id of the listing to show the image of
  var id = imgview.attr("data-itemid");
  // get the list of image urls from images.php
  $.get("./php/images.php", {postid: id}, function (data)
  {
    // default src is the default image
    var imgSrc = "img/vege-card.jpg";
    try
    {
      var urls = JSON.parse(data);
      // use the image last in the folder
      for (var i = 0; i < urls.length; i++)
      {
        var photo = urls[i];
        photo.replace('\\', '');
        imgSrc = photo;
      }
    }
    catch(e)
    {
      imgSrc = "img/vege-card.jpg"
    }

    // set the src of the imgview to be whatever it was found to be
    imgview.attr("src", imgSrc);
  });
}
