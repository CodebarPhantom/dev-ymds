@push('javascript')
    <script type="text/javascript">
        let isDraggable = @json(request()->routeIs('location.create') || request()->routeIs('location.edit'));

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the map with default location (Jakarta)
            let map = L.map('map').setView(
                [
                    {{ $data['location']['latitude'] ?? -6.175563639696246 }},
                    {{ $data['location']['longitude'] ?? 106.82717621326447 }}
                ],
                19
            );

            // Base layer (OpenStreetMap)
            let osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            });

            // Satellite layer (Esri)
            let satelliteLayer = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                });

            // Add default layer to map
            osmLayer.addTo(map);

            // Layer control
            L.control.layers({
                'OpenStreetMap': osmLayer,
                'Satellite': satelliteLayer
            }).addTo(map);

            // Create the marker and set its draggable state based on the route
            let marker = L.marker(
                [
                    {{ $data['location']['latitude'] ?? -6.175563639696246 }},
                    {{ $data['location']['longitude'] ?? 106.82717621326447 }}
                ], {
                    draggable: isDraggable
                }
            ).addTo(map).bindPopup(isDraggable ? "Silahkan Geser Pin" :
                "{{ $data['location']['name'] ?? 'Pin tidak bisa digeser' }}").openPopup();

            function updateInputFields(lat, lng) {
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            }

            // Update input fields when the marker is dragged (only if draggable)
            if (isDraggable) {
                marker.on('dragend', function(e) {
                    let newLatLng = marker.getLatLng();
                    document.getElementById('latitude').value = newLatLng.lat;
                    document.getElementById('longitude').value = newLatLng.lng;
                });

                map.on('click', function(e) {
                    let latLng = e.latlng; // Get the latitude and longitude from the click event
                    marker.setLatLng(latLng); // Move the marker to the clicked location
                    updateInputFields(latLng.lat, latLng.lng); // Update the input fields
                    marker.openPopup(); // Optional: Open popup when the marker is moved
                });

                // Get the current location and move the marker
                document.querySelector('.btn.btn-sm.text-center.btn-info').addEventListener('click', function(
                    event) {
                    event.preventDefault();
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                // Update map and marker position with precise coordinates
                                let lat = position.coords.latitude;
                                let lng = position.coords.longitude;
                                map.setView([lat, lng], 19);
                                marker.setLatLng([lat, lng]).openPopup();
                                document.getElementById('latitude').value = lat;
                                document.getElementById('longitude').value = lng;
                            },
                            function(error) {
                                console.log("Geolocation error code: ", error.code);
                                if (error.code === 1) {
                                    alert(
                                        "Location access denied by user. Please enable location access in your browser settings.");
                                } else if (error.code === 2) {
                                    alert(
                                        "Location unavailable. Check your device's location settings and try again.");
                                } else if (error.code === 3) {
                                    alert("Location request timed out. Try again in a few seconds.");
                                } else {
                                    alert("An unknown error occurred while retrieving location.");
                                }
                            }, {
                                timeout: 30000, // Increase timeout to allow for better accuracy
                                maximumAge: 0, // Ensure fresh location data
                                enableHighAccuracy: true // Request high-accuracy location
                            }
                        );
                    } else {
                        alert("Geolocation is not supported by this browser.");
                    }
                });
            }
        });
    </script>
@endpush
