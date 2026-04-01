@extends('layouts.main')

@push('head')
@endpush

@section('content')
    <div id="modal_2" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[1000] justify-center items-center">
        <div class="modal-content bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
            <div class="modal-header flex justify-between items-center">
                <h3 id="modalTitle" class="text-lg font-semibold">Event Details</h3>
                <button onclick="closeModal()" class="text-gray-600">
                    <i class="ki-outline ki-cross"></i>
                </button>
            </div>
            <div id="modalContent" class="modal-body mt-4">
                <!-- Content will be dynamically added -->
            </div>
            <div class="modal-footer mt-4 text-right flex justify-end space-x-2">
                <!-- Save button -->
                <button onclick="closeModal()" class="btn btn-warning  ">Cancel</button>
                <button onclick="saveEventDetails()" class="btn btn-success ">Save</button>
                <!-- Cancel button -->
            </div>
        </div>

    </div>

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
            </div>
            <div class="flex items-center gap-2.5 w-1/2">
                <select id="shift-select" class="select flex-1" name="shift">
                    <option>--- Silahkan Pilih Shift---</option>
                    <option value="0">Semua Shift</option>

                    @foreach ($data['shifts'] as $shift)
                        <option value="{{ $shift['id'] }}">
                            {{ $shift['name'] . ' | ' . $shift['formatted_start_time'] . ' - ' . $shift['formatted_end_time'] }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        @include('partials.attention')

        <!-- Placeholder Container (Initially Visible) -->
        <div id="placeholder-container" class="container-fixed flex flex-col items-center justify-center h-1/2 mt-2">
            <img src="{{ asset('assets/media/illustrations/22.svg') }}" alt="Tidak ada data" class="w-52 h-52">
            <h3 class="text-lg font-semibold text-gray-900 mt-4">
                Pilih Shift Terlebih Dahulu
            </h3>
        </div>


        <!-- Report Container (Initially Hidden) -->
        <div id="report-container" class="container-fixed hidden">
            <!-- Calendar Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-4 space-y-2 sm:space-y-0">
                <button id="prevWeek" class="p-2 btn btn-primary flex-shrink-0">
                    <i class="ki-filled ki-double-left-arrow"></i> Minggu Sebelumnya
                </button>
                <h2 id="weekRange" class="text-lg sm:text-xl font-bold text-center">
                    Dec 29, 2024 - Jan 4, 2025
                </h2>
                <button id="nextWeek" class="p-2 btn btn-primary flex-shrink-0">
                    Minggu Selanjutnya <i class="ki-filled ki-double-right-arrow"></i>
                </button>
            </div>

            <!-- Calendar Grid with Horizontal Scroll -->
            <div class="overflow-x-auto w-full">
                <div id="calendarContainer" class="grid gap-2 auto-rows-auto text-sm min-w-[700px]">
                    <!-- Header Row -->
                    <div class="grid grid-cols-8 font-bold border-b">
                        <div class="p-2">Nama Karyawan</div>
                        <div id="daysHeader" class="col-span-7 grid grid-cols-7 gap-1">
                            <!-- Days of the week will be added dynamically -->
                        </div>
                    </div>
                    <div id="not-found-container"
                        class="container-fixed flex flex-col items-center justify-center h-1/2 mt-2 hidden">
                        <img src="{{ asset('assets/media/illustrations/29.svg') }}" alt="Tidak ada data" class="w-52 h-52">
                        <h3 class="text-lg text-center font-semibold text-gray-900 mt-4">
                            Laporan shift karyawan tidak ditemukan. <br> Apakah kamu sudah menginputnya pada menu shift?
                        </h3>
                    </div>
                    <!-- Employee Rows -->
                    <div id="employeeRows" class="grid gap-1">
                        <!-- Employee data will be dynamically added -->
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.9.6/plugin/isBetween.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        dayjs.extend(window.dayjs_plugin_isBetween);

        let selectedEventData = {}; // To store the selected event data for the modal
        let currentWeek = dayjs().startOf("week");
        const loadingIndicator = document.getElementById("loading");
        const placeholderContainer = document.getElementById("placeholder-container");
        const reportContainer = document.getElementById("report-container");
        const notFoundContainer = document.getElementById("not-found-container");
        const shiftSelect = document.getElementById("shift-select");

        // Show the loading indicator
        function showLoading() {
            loadingIndicator.style.display = "flex";
        }

        // Hide the loading indicator
        function hideLoading() {
            loadingIndicator.style.display = "none";
        }

        function showModal(eventData) {
            const modal = document.getElementById("modal_2");

            if (!modal) {
                console.error("Modal element not found.");
                return;
            }

            const modalTitle = document.getElementById("modalTitle");
            const modalContent = document.getElementById("modalContent");

            modalTitle.innerText = `Jadwal Kerja`;
            modalContent.innerHTML = `
                <form id="modalForm">
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Nama</label>
                        <input type="hidden" name="employee_id" value="${eventData.employee_id}" />
                        <input class="input" name="employee_name" type="text" readonly="true" value="${eventData.employee_name}" />
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Shift</label>
                        <input class="input" name="shift_name" type="text" readonly="true" value="${eventData.note}" />
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Tanggal</label>
                        <input class="input" name="bypass_date" type="text" readonly="true" value="${eventData.shift_date}" />
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Status</label>
                        <input class="input" name="status" type="text" readonly="true" value="${eventData.status}" />
                    </div>


                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Jam Masuk</label>
                        <input class="input" name="clock_in" type="text" readonly="true" value="${eventData.clock_in}" />
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Jam Keluar</label>
                        <input class="input" name="clock_out" type="text" readonly="true" value="${eventData.clock_out}" />
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="form-label max-w-56">Bypass Jam Masuk / Keluar</label>
                        <input class="input" name="bypass_time" type="time" value="" />
                    </div>
                </form>
            `;

            // Toggle modal visibility
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        }



        function closeModal() {
            const modal = document.getElementById("modal_2");
            modal.classList.remove("flex");
            modal.classList.add("hidden");
        }

        async function saveEventDetails() {
            try {
                showLoading();

                // Collect form data from the modal
                const form = document.getElementById("modalForm");
                const formData = new FormData(form);

                // Convert FormData to JSON
                const data = Object.fromEntries(formData.entries());

                // Send POST request
                const response = await axios.post(
                    "{{ route('api.v1.work-schedule.shift-attendance.bypass-attendance') }}", data);

                if (!response.data.error) {
                    alert(`Data Kehadiran telah diperbarui`);
                    await updateCalendar(currentWeek);

                } else {
                    alert(response.data.messages);
                }
            } catch (error) {
                console.error("Error saving event:", error);
                alert("An error occurred while saving the event.");
            } finally {
                hideLoading();
                closeModal();
            }
        }

        // Fetch events data
        async function fetchEvents(weekStart, weekEnd) {
            try {
                const selectedShift = shiftSelect.value;

                if (!selectedShift || selectedShift === "") {
                    console.warn("Please select a shift.");
                    return [];
                }

                showLoading();

                const response = await axios.get('{{ route('api.v1.report.employee.report-shift') }}', {
                    params: {
                        start_date: weekStart.format("YYYY-MM-DD"),
                        end_date: weekEnd.format("YYYY-MM-DD"),
                        shift_id: selectedShift,
                    },
                });

                const reports = response.data.data.reports || [];
                // if (reports.length === 0) {
                //     alert("Data shift karyawan tidak ditemukan. Apakah kamu sudah menginputnya pada menu shift?");
                // }
                return reports;
            } catch (error) {
                console.error("Error fetching events:", error);
                return [];
            } finally {
                hideLoading();
            }
        }

        // Update the calendar with new data
        async function updateCalendar(weekStart) {
            const weekRange = document.getElementById("weekRange");
            const daysHeader = document.getElementById("daysHeader");
            const employeeRows = document.getElementById("employeeRows");

            const weekEnd = weekStart.add(6, "day");
            weekRange.innerText = `${weekStart.format("MMM D, YYYY")} - ${weekEnd.format("MMM D, YYYY")}`;

            daysHeader.innerHTML = "";
            employeeRows.innerHTML = "";

            const employees = await fetchEvents(weekStart, weekEnd);

            if (employees.length === 0) {
                notFoundContainer.classList.remove("hidden");
                employeeRows.classList.add("hidden");
                return;
            }

            notFoundContainer.classList.add("hidden");
            employeeRows.classList.remove("hidden");

            // Create day headers
            for (let i = 0; i < 7; i++) {
                const day = weekStart.add(i, "day");
                const dayHeader = document.createElement("div");
                dayHeader.className = "font-bold border p-2";
                dayHeader.textContent = `${day.format("ddd")} ${day.format("D")}`;
                daysHeader.appendChild(dayHeader);
            }

            // Create rows for employees and events
            employees.forEach((employee) => {
                const employeeRow = document.createElement("div");
                employeeRow.className = "grid grid-cols-8 border-b";

                const employeeName = document.createElement("div");
                employeeName.className = "p-2 font-bold text-left border-r";
                employeeName.textContent = employee.name;
                employeeRow.appendChild(employeeName);

                for (let i = 0; i < 7; i++) {
                    const day = weekStart.add(i, "day");
                    const dayCell = document.createElement("div");
                    dayCell.className =
                        "p-2 bg-gray-50 min-h-[60px] flex flex-col border hover:bg-gray-100 cursor-pointer";
                    //dayCell.className = "p-2 bg-gray-50 min-h-[60px] flex flex-col border";


                    const events = employee.events.filter((event) =>
                        day.isBetween(dayjs(event.start_date), dayjs(event.end_date), "day", "[]")
                    );

                    const dateLabel = document.createElement("div");
                    dateLabel.className = "text-xs text-gray-500 mb-1";
                    dateLabel.textContent = day.format("D");

                    dayCell.appendChild(dateLabel);

                    events.forEach((event) => {
                        const eventPill = document.createElement("div");
                        eventPill.className =
                            `mt-1 text-xs text-white rounded px-2 py-1 bg-${event.color} cursor-pointer`;

                        eventPill.textContent = `${event.note}`;

                        // Add click event to show the modal
                        eventPill.addEventListener("click", () => {
                            showModal({
                                employee_id: event.employee_id,
                                employee_name: event.employee_name,
                                shift_name: event.shift_name,
                                shift_date: event.shift_date,
                                start_date: event.start_date,
                                end_date: event.end_date,
                                note: event.note,
                                status: event.status,
                                clock_in: event.clock_in,
                                clock_out: event.clock_out,

                            });
                        });

                        dayCell.appendChild(eventPill);
                    });

                    employeeRow.appendChild(dayCell);
                }

                employeeRows.appendChild(employeeRow);
            });
        }

        // Event listener for shift selection
        shiftSelect.addEventListener("change", async () => {
            const selectedShift = shiftSelect.value;

            if (selectedShift && selectedShift !== "") {
                placeholderContainer.classList.add("hidden");
                reportContainer.classList.remove("hidden");
                await updateCalendar(currentWeek);
            } else {
                placeholderContainer.classList.remove("hidden");
                reportContainer.classList.add("hidden");
            }
        });

        // Event listeners for week navigation
        document.getElementById("prevWeek").addEventListener("click", async () => {
            currentWeek = currentWeek.subtract(7, "day");
            await updateCalendar(currentWeek);
        });

        document.getElementById("nextWeek").addEventListener("click", async () => {
            currentWeek = currentWeek.add(7, "day");
            await updateCalendar(currentWeek);
        });

        // Initial Setup: Only show placeholder initially
        placeholderContainer.classList.remove("hidden");
        reportContainer.classList.add("hidden");
        notFoundContainer.classList.add("hidden");
    </script>
@endpush
