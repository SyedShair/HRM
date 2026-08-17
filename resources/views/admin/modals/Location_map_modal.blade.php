{{-- ================= SHARED LOCATION MAP MODAL ================= --}}
{{-- Include this once per page (already added to layouts.default) --}}
{{-- Trigger it from anywhere with: showLocationMap(lat, lng, "Label text") --}}

<div id="locationMapModal" class="ui modal">
    <i class="close icon"></i>
    <div class="header" id="locationMapTitle">Clock Location</div>
    <div class="content">
        <div id="locationMapCanvas" style="width:100%; height:400px; border-radius:6px;"></div>
        <p id="locationMapCoords" style="margin-top:10px; font-size:12px; color:#6b7280;"></p>
    </div>
</div>

<script>
let _locationMap = null;
let _locationMarker = null;

function showLocationMap(lat, lng, label) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);

    if (isNaN(lat) || isNaN(lng)) {
        alert('No location data available for this record.');
        return;
    }

    $('#locationMapTitle').text(label || 'Clock Location');
    $('#locationMapCoords').text('Lat: ' + lat.toFixed(6) + ', Lng: ' + lng.toFixed(6));

    $('#locationMapModal').modal({
        onShow: function () {
            // Google Maps needs the container visible before it can size itself correctly
            setTimeout(function () {
                const position = { lat: lat, lng: lng };

                if (!_locationMap) {
                    _locationMap = new google.maps.Map(document.getElementById('locationMapCanvas'), {
                        center: position,
                        zoom: 16,
                    });
                    _locationMarker = new google.maps.Marker({
                        position: position,
                        map: _locationMap,
                    });
                } else {
                    _locationMap.setCenter(position);
                    _locationMarker.setPosition(position);
                }

                google.maps.event.trigger(_locationMap, 'resize');
                _locationMap.setCenter(position);
            }, 150);
        }
    }).modal('show');
}
</script>