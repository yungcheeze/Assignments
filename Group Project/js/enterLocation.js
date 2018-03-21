"use strict";

$.ajaxSetup({cache: false});

var marker;

function strToLatLng(l)
{
    var latlng = l.replace(/[^0-9,.-]/g, "").split(",");
    return new google.maps.LatLng(parseFloat(latlng[0]), parseFloat(latlng[1]));
}

function initMap()
{
    var map = new google.maps.Map(document.getElementById("inputMap"),
    {
        center: {lat: 53.303287539568494, lng: -1.478261947631836},
        zoom: 8,
        mapTypeId: 'roadmap'
    });

    window.map = map;

    marker = new google.maps.Marker({map: map});
    if (window.currentlatLng)
    {
        var ll = strToLatLng(window.currentlatLng);
        marker.setPosition(ll);
        map.panTo(ll);
    }

    var input = document.getElementById("addressInput");
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    map.addListener('bounds_changed', function ()
    {
        searchBox.setBounds(map.getBounds());
    });

    searchBox.addListener('places_changed', function ()
    {
        var places = searchBox.getPlaces();
        if (places.length == 0) return;

        var bounds = new google.maps.LatLngBounds();
        var place = places[0];

        if (!place.geometry)
        {
            console.log("Returned place contains no geometry");
            return;
        }

        marker.setPosition(place.geometry.location);
        window.currentlatLng = place.geometry.location;

        if (place.geometry.viewport) bounds.union(place.geometry.viewport);
        else bounds.extend(place.geometry.location);
        map.fitBounds(bounds);
    });

    google.maps.event.addListener(map, 'click', function (e)
    {
        marker.setPosition(e.latLng);
        window.currentlatLng = e.latLng;
        $("#nextBtn").prop("disabled", false);
    });

    google.maps.event.trigger(map, "resize");
}

function reInitMap()
{
    google.maps.event.trigger(window.map, "resize");
}