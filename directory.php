<?php
/*
Template Name: Directory
*/

$locations = map_get_locations();
$per_column = 12;
$total = count($locations);

// pf many changes in this file

?>
<section id="directory">
  <div class="background">
    <div class="extent">
      <div class="left-column">
        <h2><?php the_title(); ?></h2>
        <div id="location_type" class="filter">
          <a href="#type-all" data-type="all" class="type-all selected first">All categories</a>
        </div>
      </div>
      <div class="right-column">
        <div id="location-count"></div>
        <div class="pagination">
        </div>
        <div class="locations">
          <div class="slider">
            <div id="locations-all" class="holder selected">
                (loading locations...)
              <div class="clear"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="clear"></div>
    </div>
  </div>
  <script>
  
  function showMapLocation(slug) {
    var link = $('directory').getElement('a.location-link-' + slug);
    if (link) {
      var id = link.get('data-id');
      if (id && directory) {
        var location = directory['location' + id];
        if (location) {
          location.openMarker();
        }
      }
    }
  }

  function showCategory(type) {
      var link = $('directory').getElement('a.type-' + (type||'all'));
      console.log(link);
      $$('#location_type a.selected').removeClass('selected');
      link.addClass('selected');
      $$('#directory .slider .holder.selected').removeClass('selected');
      var holder = $('locations-' + type);
      if (!holder) {
          holder = new Element('div', {
              id: 'locations-' + type,
              'class': 'holder'
          });
          holder.inject($('directory').getElement('.slider'));
          var list = new Element('ul');
          list.inject(holder);
          $$('#locations-all li').each(function(item) {
              if (item.hasClass(type)) {
                  var clone = item.clone();
                  clone.inject(list);
                  if (list.getElements('li').length == 12) {
                      list = new Element('ul');
                      list.inject(holder);
                  }
              }
          });
      }
      holder.addClass('selected');
      var locationCount = holder.getElements('li').length;
      var pages = Math.ceil(locationCount / 36);
      $$('#directory .pagination a').each(function(link, index) {
          if (index == 0) {
              link.addClass('selected');
          } else {
              link.removeClass('selected');
          }
          if (index < pages && !(index == 0 && pages == 1)) {
              link.removeClass('hidden');
          } else {
              link.addClass('hidden');
          }
      });
      $('location-count').set('html', locationCount + ' locations');
      $('directory').getElement('.slider').setStyle('left', 0);
      updateMarkers();
      closeMarker();
      resizeLocations();
  }

  function showTag(tag_id) {
    var tag_name = id_to_tag[tag_id];
    var active_locations = tag_to_locations[tag_name];
    
    $$('#location_type a.selected').removeClass('selected');
    $$('#directory .slider .holder.selected').removeClass('selected');
    
    var holder = $('tagged-locations-' + tag_id);
    if (!holder) {
      holder = new Element('div', {
        id: 'tagged-locations-' + tag_id,
        'class': 'holder'
      });
      holder.inject($('directory').getElement('.slider'));
      var list = new Element('ul');
      list.inject(holder);
      $$('#locations-all li').each(function(item) {
	var a = item.getElement('a');
	if (a) {
	  var id = parseInt(a.get('data-id'));
          if (active_locations[id]) {
            var clone = item.clone();
            clone.inject(list);
            if (list.getElements('li').length == 12) {
              list = new Element('ul');
              list.inject(holder);
            }
          }
	}
      });
    }
    holder.addClass('selected');
    var locationCount = holder.getElements('li').length;
    var pages = Math.ceil(locationCount / 36);
    $$('#directory .pagination a').each(function(link, index) {
      if (index == 0) {
        link.addClass('selected');
      } else {
        link.removeClass('selected');
      }
      if (index < pages && !(index == 0 && pages == 1)) {
        link.removeClass('hidden');
      } else {
        link.addClass('hidden');
      }
    });
    $('location-count').set('html', locationCount + ' locations');
    $('directory').getElement('.slider').setStyle('left', 0);
    updateMarkers(function(location) {
      return active_locations[location.id] ? true : false;
    });
    closeMarker();

    var filter_name = "tag-select-" + tag_id;
    var filter = $(filter_name);
    if (!filter) {
      filter = new Element('a', {
	href: "#/tag/" + tag_id,
	"data-id": tag_id,
	id: filter_name,
	html: tag_name
      });
      $("location_type").adopt(filter);
    }
    filter.addClass('selected');
    
    resizeLocations();
  }

  function resizeLocations() {
    var locations = $('directory').getElement('.locations');
    locations.setStyle('height', $('location_type').getSize().y);
    var totalWidth = 0;
    $$('#directory .locations ul').each(function(list) {
      if (list.getSize().y > locations.getSize().y) {
        locations.setStyle('height', list.getSize().y);
      }
      totalWidth += list.getSize().x;
    });
    $('locations-all').setStyle('width', totalWidth);
  }

  initializeDirectoryPages = function() {
  
    resizeLocations();
  
    var slider = $('directory').getElement('.locations .slider');
    slider.set('tween', {
      duration: 750,
      transition: Fx.Transitions.Quart.easeOut
    });
    $$('#directory .pagination a').each(function(link) {
      link.addEvent('click', function(e) {
        e.stop();
        $$('#directory .pagination a.selected').removeClass('selected');
        var page = link.get('href').match(/directory-page(\d)/);
        if (page) {
          var i = page[1].toInt() - 1;
          slider.tween('left', i * -780);
          link.addClass('selected');
        }
      });
    });
    
    $$('#location_type a').each(function(link) {
      link.addEvent('click', function(e) {
        e.stop();
        var type = link.get('data-type');
        showCategory(type);
      });
    });
  }

  </script>
</section>
