@extends('layouts.main')

@push('head')
    <style>
        #map {
            width: 100%;
            /* Ensure it takes up full width */
            height: 300px;
            /* Set a fixed height for mobile screens */
            min-height: 200px;
            /* Minimum height for very small devices */
            max-height: 100vh;
            /* Prevent it from overflowing */
        }

        /* Full-screen backdrop */
    </style>
@endpush

@section('content')
    <div id="loading" style="display: none;">
        <div class="backdrop"></div>
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Sending data, please wait...</p>
        </div>
    </div>

    <!-- Container -->
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
                {{-- <div class="flex items-center gap-2 text-sm font-normal text-gray-700">
                    Central Hub for Personal Customization
                </div> --}}
            </div>
        </div>
    </div>

    @include('partials.attention')

    @if ($data['shiftAttendance'])
        <div class="container-fixed">
            <div class="grid gap-6 pb-6">
                <!-- Shift Information -->
                <div
                    class="bg-white rounded-md border border-gray-300 p-4 flex flex-col md:flex-row md:items-center md:space-x-4">
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-xl font-semibold text-gray-800">Informasi Kehadiran</h2>
                        <p class="text-red-500 mt-2">
                            <span class="font-medium">Kehadiran Tanggal:
                                {{ $data['shiftAttendance']['formatted_date'] }}</span> |
                            <span class="font-medium">{{ $data['shiftAttendance']['shift']['name'] }}
                                ({{ $data['shiftAttendance']['shift']['formatted_start_time'] }} -
                                {{ $data['shiftAttendance']['shift']['formatted_end_time'] }})</span>
                        </p>
                        <div id="serverTime" class="text-blue-500 font-bold text-2xl my-4">Loading Server Time...</div>

                        <div class="flex justify-center md:justify-start gap-2 pb-6">
                            <button id="view-location"
                                class="bg-blue-500 text-white px-3 py-1.5 rounded-md font-medium hover:bg-blue-600 focus:outline-none">
                                Lihat Lokasi
                            </button>
                            @if ($data['shiftAttendance']['status']->value !== 9)
                                @if (is_null($data['shiftAttendance']['clock_in']) && is_null($data['shiftAttendance']['clock_out']))
                                    {{-- If neither clock_in nor clock_out is set --}}
                                    <button id="capture"
                                        class="bg-amber-500 text-white px-3 py-1.5 rounded-md font-medium hover:bg-amber-600 focus:outline-none">
                                        Masuk
                                    </button>
                                @elseif (!is_null($data['shiftAttendance']['clock_in']) && is_null($data['shiftAttendance']['clock_out']))
                                    {{-- If clock_in is set but clock_out is not --}}
                                    <button id="capture"
                                        class="bg-rose-500 text-white px-3 py-1.5 rounded-md font-medium hover:bg-rose-600 focus:outline-none">
                                        Pulang
                                    </button>
                                @else
                                    <button hidden id="capture" disabled>
                                    </button>
                                @endif
                            @endif



                        </div>
                    </div>
                    <div class="flex-1 flex justify-center">
                        <video id="webcam" autoplay playsinline
                            class="w-64 h-48 border border-gray-300 rounded-md"></video>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Left Column: Stacked Cards -->
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Clock In Card -->
                        <div
                            class="bg-gradient-to-r from-amber-100 to-amber-50 rounded-lg p-4 flex items-center space-x-4 relative">
                            <!-- Image Section -->
                            <div class="text-center">
                                <img src="{{ $data['shiftAttendance']['img_clock_in'] !== null ? url('/' . $data['shiftAttendance']['img_clock_in']) : asset('assets/media/avatars/blank.png') }}"
                                    alt="User Image" class="w-20 h-20 rounded-full object-cover mt-2">
                            </div>
                            <!-- Text Section -->
                            <div class="text-left flex-1">
                                <h3 class="text-sm font-semibold text-amber-800 uppercase tracking-wide">
                                    Masuk
                                    @if ($data['shiftAttendance']['is_late'])
                                        - Telat
                                    @endif
                                </h3>
                                <div class="text-xs text-gray-600 mt-1">
                                    <span>Aktual:</span>
                                    <span class="font-semibold">
                                        {{ $data['shiftAttendance']['clock_in'] !== null ? $data['shiftAttendance']['formatted_clock_in'] : '-' }}
                                    </span> |
                                    <span>Jam Masuk:</span>
                                    <span class="font-semibold">
                                        {{ $data['shiftAttendance']['shift']['formatted_start_time'] }}
                                    </span>
                                </div>
                            </div>
                            <!-- Button Section -->
                            <button
                                onclick="window.open('https://www.google.com/maps?q={{ $data['shiftAttendance']['lat_clock_in'] }},{{ $data['shiftAttendance']['long_clock_in'] }}', '_blank')"
                                class="absolute bottom-2 right-2 bg-amber-500 text-white text-xs px-3 py-1 rounded-lg shadow-sm hover:bg-amber-600 focus:ring-2 focus:ring-amber-300">
                                Lokasi
                            </button>
                        </div>

                        <!-- Clock Out Card -->
                        <div
                            class="bg-gradient-to-r from-rose-100 to-rose-50 rounded-lg p-4 flex items-center space-x-4 relative">
                            <!-- Image Section -->
                            <div class="text-center">
                                <img src="{{ $data['shiftAttendance']['img_clock_out'] !== null ? url('/' . $data['shiftAttendance']['img_clock_out']) : asset('assets/media/avatars/blank.png') }}"
                                    alt="User Image" class="w-20 h-20 rounded-full object-cover mt-2">
                            </div>
                            <!-- Text Section -->
                            <div class="text-left flex-1">
                                <h3 class="text-sm font-semibold text-rose-800 uppercase tracking-wide">
                                    Pulang
                                    @if ($data['shiftAttendance']['is_early_clock_out'])
                                        - Pulang Lebih Awal
                                    @endif
                                </h3>
                                <div class="text-xs text-gray-600 mt-1">
                                    <span>Aktual:</span>
                                    <span class="font-semibold">
                                        {{ $data['shiftAttendance']['clock_out'] !== null ? $data['shiftAttendance']['formatted_clock_out'] : '-' }}
                                    </span> |
                                    <span>Jam Pulang:</span>
                                    <span class="font-semibold">
                                        {{ $data['shiftAttendance']['shift']['formatted_end_time'] }}
                                    </span>
                                </div>
                            </div>
                            <!-- Button Section -->
                            <button
                                onclick="window.open('https://www.google.com/maps?q={{ $data['shiftAttendance']['lat_clock_out'] }},{{ $data['shiftAttendance']['long_clock_out'] }}', '_blank')"
                                class="absolute bottom-2 right-2 bg-rose-500 text-white text-xs px-3 py-1 rounded-lg shadow-sm hover:bg-rose-600 focus:ring-2 focus:ring-rose-300">
                                Lokasi
                            </button>
                        </div>
                    </div>

                    <!-- Right Column: Map -->
                    <div class="lg:col-span-2 bg-white rounded-lg border border-gray-300 p-4 shadow-sm">
                        <div id="map" style="height: 100%;"></div>
                    </div>
                </div>


                <!-- Attendance Section -->


            </div>
        </div>
    @else
        <div class="container-fixed flex flex-col items-center justify-center h-1/2 mt-2">
            <img src="{{ asset('assets/media/illustrations/22.svg') }}" alt="Tidak ada data absen" class="w-52 h-52">
            <h3 class="text-lg font-semibold text-gray-900 mt-4">
                Tidak ada data absen segera hubungin divisi HR
            </h3>
        </div>
    @endif



@endsection

@push('javascript')
    {{-- <script src="https://cdn.jsdelivr.net/npm/pica@8.1.2/dist/pica.min.js"></script> --}}

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', () => {
            const videoElement = document.getElementById('webcam');
            const captureButton = document.getElementById('capture');
            const loadingIndicator = document.getElementById('loading'); // The loading indicator element

            let closestCompany = null; // Placeholder for closest company data

            // Start webcam
            async function startWebcam() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: true
                    });
                    videoElement.srcObject = stream;
                } catch (error) {
                    console.error('Error accessing webcam:', error);
                }
            }

            // Capture image from video feed
            async function captureFromVideo() {
                // Dynamically create a canvas
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                // Set canvas size to match video
                canvas.width = videoElement.videoWidth;
                canvas.height = videoElement.videoHeight;

                // Draw current video frame onto the canvas
                context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

                // Show loading indicator
                loadingIndicator.style.display = 'flex';
                // Get base64 image data
                const imageData = canvas.toDataURL('image/jpeg');

                // Optionally, get user location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                                const payload = {
                                    image: imageData,
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude,
                                    timestamp: new Date().toISOString(),
                                    closest_company: {
                                        name: closestCompany?.name || 'Unknown',
                                        distance: closestCompany ? (closestCompany.distance / 1000)
                                            .toFixed(2) : null, // Distance in km
                                    },
                                };



                                // Send data to API
                                try {
                                    const response = await axios.post(
                                        '{{ route('api.v1.my-attendance.attendance') }}', payload);

                                    // Hide loading indicator
                                    loadingIndicator.style.display = 'none';

                                    if (response.status === 200) {
                                        alert('Data Absen Terkirim..');
                                        window.location.reload();
                                    } else {
                                        alert('Failed to send data.');
                                    }
                                } catch (error) {
                                    // Hide loading indicator
                                    loadingIndicator.style.display = 'none';

                                    console.error('Error sending data to API:', error);
                                    alert('An error occurred while sending the data.');
                                }
                            },
                            (error) => {
                                console.error('Error getting location:', error);
                                alert('Could not fetch location.');
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0,
                            }
                    );
                } else {
                    alert('Geolocation not supported by this browser.');
                }
            }

            // Attach event listener to the capture button
            captureButton.addEventListener('click', captureFromVideo);

            // Start the webcam
            startWebcam();

            // Expose a function to update closest company data
            window.updateClosestCompany = function(company) {
                closestCompany = company;
            };
        });

        document.getElementById('view-location').addEventListener('click', () => {
            if (navigator.geolocation) {
                // Get the user's current location
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const {
                            latitude,
                            longitude
                        } = position.coords;

                        // // Destination coordinates
                        // const destinationLatitude = 40.748817; // Example: Latitude of Times Square
                        // const destinationLongitude = -73.985428; // Example: Longitude of Times Square

                        // Google Maps URL scheme
                        //https://www.google.com/maps/dir/?api=1&origin=${latitude},${longitude}&destination=${destinationLatitude},${destinationLongitude}&travelmode=driving
                        const googleMapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}`;

                        // Open the URL in a new tab
                        window.open(googleMapsUrl, '_blank');
                    },
                    error => {
                        console.error('Error fetching location:', error);
                        alert('Gagal mendapatkan lokasi kamu. Pastikan GPS menyala.');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                alert('Geolocation tidak didukung oleh browser ini!');
            }
        });
    </script>



    <script type="text/javascript">
        async function fetchServerTime() {
            try {
                const response = await fetch('{{ route('api.v1.server-time') }}'); // Adjust route if necessary
                const data = await response.json();
                return data.time; // Expecting the server response to include { time: "23:45:27" }
            } catch (error) {
                console.error('Error fetching server time:', error);
                return null;
            }
        }

        function startLiveTime() {
            const timeElement = document.getElementById('serverTime');

            // Fetch initial time from the server
            fetchServerTime().then(serverTime => {
                if (serverTime) {
                    updateLiveTime(serverTime);
                } else {
                    timeElement.textContent = "Error fetching time";
                }
            });

            // Update every second
            setInterval(() => {
                const currentText = timeElement.textContent.split(':');
                if (currentText.length === 3) {
                    let [hours, minutes, seconds] = currentText.map(Number);
                    seconds++;
                    if (seconds === 60) {
                        seconds = 0;
                        minutes++;
                    }
                    if (minutes === 60) {
                        minutes = 0;
                        hours++;
                    }
                    if (hours === 24) {
                        hours = 0;
                    }

                    timeElement.textContent =
                        `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                }
            }, 1000);
        }

        // Function to update time with a new server time
        function updateLiveTime(serverTime) {
            const timeElement = document.getElementById('serverTime');
            timeElement.textContent = serverTime;
        }

        // Start live time
        startLiveTime();
    </script>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const companies = @json($data['companies']);

            // Initialize the map
            const map = L.map('map').setView([0, 0], 12);

            // Add OpenStreetMap layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Function to calculate distance in meters between two coordinates
            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371000; // Earth radius in meters
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c; // Distance in meters
            }

            // Function to find the closest company
            function findClosestCompany(userLat, userLon) {
                let closestCompany = null;
                let shortestDistance = Infinity;

                companies.forEach(company => {
                    const distance = calculateDistance(userLat, userLon, company.latitude, company
                        .longitude);
                    if (distance < shortestDistance) {
                        shortestDistance = distance;
                        closestCompany = {
                            ...company,
                            distance
                        };
                    }
                });

                return closestCompany;
            }

            // Function to update map with user's live location and all companies
            function setUserAndCompanies(userLat, userLon) {
                let locations = []; // Initialize an empty array for locations
                let latLngArr = []; // Array to store lat-lon pairs for fitting the map

                // Add user's location to the locations array
                locations.push(["Lokasi Kamu", userLat, userLon]);

                // Loop through all companies and add them to the locations array
                companies.forEach(company => {
                    locations.push([company.name, company.latitude, company.longitude]);
                });

                // Add markers and popups for all locations (user and companies)
                for (let i = 0; i < locations.length; i++) {
                    const pinIcon = L.icon({
                        iconUrl: i === 0 ? 'path_to_user_icon.png' :
                        'path_to_company_icon.png', // Different icon for user and companies
                        iconSize: [32, 32],
                    });

                    // Add a marker for each location
                    const marker = L.marker([locations[i][1], locations[i][2]], pinIcon)
                        .bindPopup(locations[i][0], {
                            autoClose: false
                        })
                        .addTo(map)
                        .openPopup(); // Open the popup automatically

                    // Store the lat-lon pair for map bounds adjustment
                    latLngArr.push([locations[i][1], locations[i][2]]);
                }

                // Find the closest company
                const closestCompany = findClosestCompany(userLat, userLon);

                if (closestCompany) {
                    // Add marker for the closest company
                    window.updateClosestCompany(closestCompany);
                    const companyMarker = L.marker([closestCompany.latitude, closestCompany.longitude])
                        .addTo(map)
                        .bindPopup(`
                    <b>${closestCompany.name}</b><br>
                    ${closestCompany.address}<br>
                    <b>Jarak:</b> ${(closestCompany.distance / 1000).toFixed(2)} km
                `)
                        .openPopup(); // Open popup automatically for the closest company

                    // Draw a line between the user's location and the closest company
                    L.polyline([
                        [userLat, userLon],
                        [closestCompany.latitude, closestCompany.longitude]
                    ], {
                        color: 'blue',
                        weight: 2,
                    }).addTo(map);

                    // Add radius circle around the company
                    L.circle([closestCompany.latitude, closestCompany.longitude], {
                        color: 'green',
                        fillColor: '#add8e6',
                        fillOpacity: 0.5,
                        radius: closestCompany.radius, // Use company's radius value
                    }).addTo(map);
                }

                // Adjust map view to fit all markers
                map.fitBounds(latLngArr, {
                    padding: [50, 50]
                });
            }

            // Get current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const {
                            latitude,
                            longitude
                        } = position.coords;
                        setUserAndCompanies(latitude, longitude);
                    },
                    error => {
                        console.error("Error fetching user location: ", error.message);
                        alert("Gagal mendapatkan lokasi kamu. Pastikan GPS menyala.");
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0,
                    }
                );
            } else {
                alert("Geolocation tidak didukung oleh browser ini!");
            }
        });
    </script>
@endpush
