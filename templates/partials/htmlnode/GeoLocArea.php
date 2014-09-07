<div class="geoloc_area"
     id="<?= $id?"$id":""; ?>">
    <? if($label){ ?><label for="<?= $id?"$id":""; ?>_input"><?= $label; ?></label><? } ?>

    <span class="position">
        [<?= $longitude; ?>,<?= $latitude; ?>] <span class="position_name">
            <i class="fa fa-spinner fa-spin"></i>
        </span>
    </span>
</div>
<div class="geoloc_area"
     id="<?= $id?"$id":""; ?>_map">
    <label>&nbsp;</label>
    <div class="map" style="width: 450px;height: 300px;display: inline-block;"></div>
</div>
<script src="/maps/2.1.1/jsl.js" type="text/javascript" charset="utf-8"></script>
<script>
(function(area,area_map){
    var map = new nokia.maps.map.Display( area_map.find(".map").get(0), {
        components: [
            // Behavior collection
            new nokia.maps.map.component.Behavior(),
            new nokia.maps.map.component.ZoomBar(),
            new nokia.maps.map.component.Overview(),
            new nokia.maps.map.component.TypeSelector(),
            new nokia.maps.map.component.ScaleBar() ],
        'zoomLevel': 12, // Zoom level for the map
        'center': [<?= $longitude; ?>, <?= $latitude; ?>] // Center coordinates
    });
// Create a marker and add it to the map
    var marker = new nokia.maps.map.StandardMarker(
        [<?= $longitude; ?>, <?= $latitude; ?>], {
        text: "Hi!", // Small label
        draggable: false  // Make the marker draggable
    });
    map.objects.add(marker);

    var searchManager = new nokia.maps.search.Manager();
    searchManager.addObserver("state", function (observedManager, key, value) {
        // If the search  has finished we can process the results
        if (value == "finished") {
            if (observedManager.locations.length > 0) {
                var location = observedManager.locations[0];
                /*
                 city: "Shanghai"
                 country: "CHN"
                 countryName: "CHINA"
                 district: "Huangpu District"
                 street: "Miaojiang Rd"
                 */
                var position_name = "";
                if( location.label )
                    position_name += location.label+":";
                if( location.address ){
                    position_name += location.address.district+", ";
                    position_name += location.address.street+" - ";
                    position_name += location.address.city+" ";
                    position_name += location.address.countryName+"";
                }
                area.find(".position_name .fa-spin").animate({
                    opacity:0
                },250,function(){
                    area.find(".position_name").text(position_name);
                })
            }
        } else if (value == "failed") {
            area.find(".position_name").text("The search request failed.");
        }
    });

    var reverseGeocodeTerm = new nokia.maps.geo.Coordinate(<?= $longitude; ?>, <?= $latitude; ?>);
    searchManager.reverseGeocode(reverseGeocodeTerm);

})($("#<?= $id ?>"),$("#<?= $id ?>_map"));
</script>