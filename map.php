<?php
/*
Template Name: Map
*/
?>
<section id="map">
  <div id="map_canvas"></div>
  <div id="map_intro">
    <div id="logo">
		  <h1><span>SolidarityNYC</span></h1>
    </div>
    <p><strong>The solidarity economy</strong> meets human needs through economic activities–like the production and exchange of goods and services–that reinforce values of justice, ecological sustainability, cooperation, and democracy.

<br /><a href="#about" class="read">Read more</a></p>
    <a href="#map" class="explore">Explore the map</a>
  </div>
  <a href="#map_size" id="map_size" data-open-label="Smaller map">Bigger map</a>
  <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
  <script>

  var mapStyles = [
    {
      featureType: "landscape",
      elementType: "geometry",
      stylers: [
        { visibility: "on" },
        { lightness: 98 },
        { gamma: 9.99 }
      ]
    }, {
      featureType: "road.arterial",
      stylers: [
        { visibility: "simplified" },
        { saturation: -95 },
        { lightness: 21 }
      ]
    }, {
      featureType: "transit",
      stylers: [
        { visibility: "off" }
      ]
    }, {
      featureType: "poi",
      stylers: [
        { visibility: "off" }
      ]
    }, {
      featureType: "road.local",
      stylers: [
        { visibility: "on" },
        { lightness: 10 }
      ]
    }, {
      featureType: "road.highway",
      stylers: [
        { visibility: "off" }
      ]
    }, {
      featureType: "administrative.neighborhood",
      stylers: [
        { visibility: "on" }
      ]
    }, {
      featureType: "water",
      stylers: [
        { visibility: "simplified" },
        { saturation: -100 },
        { lightness: 57 }
      ]
    }
  ];
  
  var streetsType = new google.maps.StyledMapType(mapStyles, {
    name: "Streets"
  });
  
  var zoom = 13;
  var center = new google.maps.LatLng(40.7149, -73.9740);
  var options = {
    zoom: zoom,
    center: center,
    mapTypeControlOptions: {
      mapTypeIds: [google.maps.MapTypeId.SATELLITE, 'Streets']
    },
    scrollwheel: false
  };
  var map = new google.maps.Map($("map_canvas"), options);
  map.mapTypes.set('Streets', streetsType);
  map.setMapTypeId('Streets');
  var overlay = new google.maps.OverlayView();
  overlay.draw = function() {};
  overlay.setMap(map);
  
  // pf+ add directory status, to be loaded from external file
  var initializeDirectoryPages = null;
  var locations = [];
  var categories = [];
  var tag_to_locations = {};
  var tag_count = 0;
  var tag_to_id = {};
  var id_to_tag = {};
  
  var location_types = {};
  var search_data = [];
  // pf-
  
  var infowindow = new google.maps.InfoWindow();
  function closeMarker() {
    infowindow.close();
    $$('#directory li.selected').removeClass('selected');
  }
  google.maps.event.addListener(infowindow, 'closeclick', function() {
    hashListener.updateHash('#/map');
    $$('#directory li.selected').removeClass('selected');
  });
  var directory = {};
  var iconSize = new google.maps.Size(32, 32);
  var iconOrigin = new google.maps.Point(0, 0);
  var iconAnchor = new google.maps.Point(15, 14);
  // pf +- locations.each processing removed from here, since we don't have locations yet

  var mapIntroHidden = false;
  var logoInterval;
  function hideMapIntro() {
    if (!mapIntroHidden) {
      mapIntroHidden = true;
      $('map_intro').fade('out').retrieve('tween').chain(function() {
        $('map_intro').setStyle('display', 'none');
      });
      clearInterval(logoInterval);
    }
  }
  
  function updateMarkers(filter) {
    if (!filter) {
      var link = $('location_type').getElement('a.selected');
      var type = link ? link.get('data-type') : 'unknown'; // pf+ type may be unknown
      if (type==null) type = 'unknown';
      hash = "//type/" + type; // pf- mark hash as changed to given type
      filter = function(location) {
        return (type == 'all' || location.type == type);
      }
      if (type=='unknown') return;
    }
    var bounds = new google.maps.LatLngBounds();
    Object.each(directory, function(location) {
      var visible = filter(location);
      location.marker.setVisible(visible);
      if (visible) {
        bounds.extend(location.marker.getPosition());
      }
    });
      /*
    map.setCenter(bounds.getCenter());
    map.fitBounds(bounds);
    map.setZoom(map.getZoom()-1); 
    if(map.getZoom()>15) map.setZoom(15);
    if(map.getZoom()<12) map.setZoom(12);
*/
  }
  
  $('map_intro').getElement('a.explore').addEvent('click', function(e) {
    e.stop();
    hideMapIntro();
  });

  // pf+ add tag support
  function getTags(entry) {
      var raw = entry.Tags;
      if (raw==null) return [];
      var lst = raw.trim().split(',');
      for (var i=0; i<lst.length; i++) {
	  lst[i] = lst[i].trim();
      }
      return lst;
  }
  // pf-

  // pf+ callback
  function initializeLocations() {

    locations.each(function(location) {
      try {
        var latlng = location.latlng.match(/^(.+),(.+)$/);
        var type = location.type;
	if(!type) {
	  type = "all";
	}
        var tags = getTags(location);
	for (var i=0; i<tags.length; i++) {
	  var tag = tags[i];
	  if (!tag_to_locations[tag]) {
	    tag_to_locations[tag] = {};
	  }
	  tag_to_locations[tag][location.id] = true;
	  if (!tag_to_id[tag]) {
	    tag_count++;
	    tag_to_id[tag] = tag_count;
            search_data.push({id: search_data.length, text: tag,
                              kind: 'tag', obj: tag_count});
	    id_to_tag[tag_count] = tag;
	  }
	}
        var marker = new google.maps.Marker({
          position: new google.maps.LatLng(parseFloat(latlng[1]), parseFloat(latlng[2])),
          map: map,
          icon: new google.maps.MarkerImage(location_types[type],
                                            iconSize, iconOrigin, iconAnchor
                                           )
        });
        var content = '<div class="infowindow">' +
          '<h3>' + location.Name + '</h3>' +
          location.Description;
	if (location.Url) {
	  content += "<br /><a href='" + location.Url + "'>" + location.Url + "</a>";
	}
	if (location.Email) {
	  content += "<br /><tt>" + location.Email + "</tt>";
	}
	if (location.Tags) {
	  content += "<br />tagged as: ";
	  for (var i=0; i<tags.length; i++) {
	    var tag = tags[i];
	    var id = tag_to_id[tag];
	    content += "<a href=\"#/tag/" + id + "\" data-id=\"" + id + "\">" + tag + "</a></li>\n";
	  }
	}
        content += '</div>';
        var openMarker = function() {
          closeMarker();
          hideMapIntro();
          hideAddLocation();
          infowindow.setPosition(marker.getPosition());
          infowindow.setContent(content);
          infowindow.open(map, marker);
          //map.panTo(marker.getPosition());
          var directory = $$('#directory .holder.selected .directory' + location.id);
          if (directory) {
            directory[0].addClass('selected');
          }
        };
        google.maps.event.addListener(marker, 'click', function () {
          hashListener.updateHash('#/map/' + location.slug);
        });
        location.marker = marker;
        location.openMarker = openMarker;
        directory['location' + location.id] = location;
      } catch (e) {
	console.log("Problem with location");
	console.log(location);
	console.log(e);
      }
    });

    var fn = (function() {
      var logo = $$('#logo h1 span')[0];
      var logos = [logo];
      for (var i = 1; i < 12; i++) {
        var clone = logo.clone();
        var num = i + 1;
        clone.setStyle('background-image', 'url("wp-content/themes/map/logo/logo' + num + '.png")');
        clone.fade('hide');
        clone.inject($('logo').getElement('h1'));
        logos.push(clone);
      }
      var index = 0;
      var z = 1;
      logoInterval = setInterval(function() {
        var prevIndex = index;
        index = (index + 1) % logos.length;
        z++;
        logos[index].fade('hide');
        logos[index].setStyle('z-index', z);
        logos[index].fade('in').retrieve('tween').chain(function() {
          logos[prevIndex].fade('hide');
        });
      }, 10000);
      
      
      var smallSize = $('map_canvas').getSize().y;
      var origLabel = $('map_size').get('html');
      
      $('map_size').addEvent('click', function(e) {
        e.stop();
        var largeSize = window.getSize().y - 42;
        if (largeSize < 600) {
          largeSize = 600;
        }
        $('map_size').toggleClass('open');
        if ($('map_size').hasClass('open')) {
          $('map_canvas').setStyle('height', largeSize);
          $('map_size').set('html', $('map_size').get('data-open-label'));
        } else {
          $('map_canvas').setStyle('height', smallSize);
          $('map_size').set('html', origLabel);
        }
        google.maps.event.trigger(map, "resize");
      });
    });
    
    fn();
  }

  function initializeDirectory() {
    // code translated directory from original php
    // locations will be set before call - may need to sort though
    var per_column = 12;
    var total = locations.length;
    var pages = Math.ceil(total / (per_column * 3));
    var txt = "";
    for (var i = 0; i < pages; i++) {
      var n = i + 1;
      var selected = (i == 0) ? ' class="selected"' : '';
      txt += "<a href=\"#directory-page" + n + "\"" +selected+ ">" + n + "</a>";
    }
    var pagination_html = txt;
    txt = "<ul>\n";
    for (var index=0; index<locations.length; index++) {
      var location = locations[index];
      if ((index % per_column == 0) && index > 0) {
        txt += "    </ul>\n    <ul>\n";
      }
      txt += "      <li class=\"directory" + location.id + " " + location.type + "\"><a href=\"#/map/" + location.slug + "\" class=\"location-link-" + location.slug + "\" data-id=\"" + location.id + "\">" + location.Name + "</a></li>\n";
    }
    txt += "    </ul>\n";
    var directory_html = txt;
    $("location-count").set('html',total + " locations");
    $$(".pagination").set('html',pagination_html);
    $("locations-all").set('html',directory_html);
  }

  window.addEvent('domready', function() {
    var request = new Request.JSON({
      url: '/wp-content/themes/map/data/solidnyc.json',
      onSuccess: function(data) {
	locations = data.Map.rows;
	categories = data.Categories.rows;
	var cat2short = {};
	for (var i=0; i<categories.length; i++) {
	  var c = categories[i];
	  cat2short[c.Category] = c.Shortcode;
          location_types[c.Shortcode] = "/wp-content/themes/map/icons2015/" + c.Shortcode + ".png";
	  var filter = new Element('a', {
            "class": "type-" + c.Shortcode,
	    href: "#type-" + c.Shortcode,
	    "data-type": c.Shortcode,
	    style: "background-image: url(" + location_types[c.Shortcode] + ");",
	    html: c.Category
	  });
	  $("location_type").adopt(filter);
	  var opt = new Element('option', {
	    value: c.Category,
	    html: c.Category
	  });
	  $("add_category").adopt(opt);
          search_data.push({id: search_data.length, text: c.Category,
                            icon: c.Shortcode, kind: 'category', obj: c});
	}
	function compare(a,b) {
	  if (a.Name < b.Name)
	    return -1;
	  if (a.Name > b.Name)
	    return 1;
	  return 0;
	}
	locations.sort(compare);
        for (var i=0; i < locations.length; i++) {
	  var location = locations[i];
	  location.slug = "listing_" + i;
	  location.id = i;
	  location.type = cat2short[location.Category];
          search_data.push({id: search_data.length, text: location.Name,
                            all: location.Description,
                            icon: location.type,
                            kind: 'location', obj: location});
        }
	initializeDirectory();
	initializeLocations();
	initializeDirectoryPages();
        $j(".search-our-city").select2({
            placeholder: "Search our city",
            "data": search_data,
            "templateResult": function(state) {
                if (!state.id) { return state.text; }
                if (!state.icon) { return state.text; }
                return $j(
                    '<span><div style="background-image:url(\'' + location_types[state.icon] + '\')" class="search-icon" /> ' + state.text + '</span>'
                );
                return $state;
            }
        });
        $j(".search-our-city").on("change",
                                  function (e) {
                                      var id = $j(e.target).val();
                                      console.log(id);
                                      var v = search_data[id];
                                      console.log(v);
                                      hideMapIntro();
                                      if (v.kind=='category') {
                                          var category = v.obj;
                                          showCategory(category.Shortcode);
                                      } else if (v.kind=='tag') {
                                          var tag = v.obj;
                                          showTag(tag);
                                      } else {
                                          var location = v.obj;
                                          showCategory(location.type);
                                          showMapLocation(location.slug);
                                      }
                                  });
      },
      onError: function(txt,err) {
	console.log(err);
      }
    }).get();
  });
  
  </script>
  <div class="extent">
    <?php get_template_part('add_location'); ?>
  </div>
</section>

