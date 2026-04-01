@extends('layouts.main')

@section('content')
    <!-- Container -->
    <form action="{{ route('workforce.employee-role-remuneration-package.update', $data['role']->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="container-fixed">
            <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-bold leading-none text-gray-900">
                        {{ $data['pageTitle'] }}
                    </h1>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center gap-2.5">

                        <a class="btn text-center btn-sm btn-primary"
                            href="{{ route('workforce.employee-role-remuneration-package.index') }}">
                            <i class="ki-filled ki-left"></i></i>Kembali
                        </a>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="submit" class="btn btn-sm text-center btn-success">
                            <i class="ki-filled ki-check"></i>{{ $data['pageTitle'] }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        @include('partials.attention')

        <div class="container-fixed">
            <div class="grid gap-5  mx-auto">
                <div class="card pb-2.5">
                    <div class="card-body grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="form-label max-w-56">
                                Jabatan
                            </label>
                            <input class="input" readonly="true" value="{{ $data['role']['name'] }}"
                                placeholder="Nama Jabatan" id="role-name" type="input" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 mt-5 mx-auto">
                <div class="card pb-2.5">
                    <div class="card-header flex flex-wrap items-center lg:items-end justify-between gap-5 ">
                        <h3 class="card-title ">
                            Paket Remunerasi
                        </h3>
                        <button id="btn-remuneration-package" class="btn btn-sm text-center bg-[#4b5563] text-white">
                            <i class="ki-filled ki-plus"></i>Tambah Paket Remunerasi
                        </button>
                    </div>
                    <div class="card-body grid gap-5" id="remuneration-package-container">
                        <div class="flex gap-2 items-center">
                            <label class="form-label text-center">
                                Nama Remunerasi
                            </label>
                            <label class="form-label text-center">
                                Nilai
                            </label>
                        </div>
                        @foreach ($data['role']['remunerationPackages'] as $remunerationPackage)
                            <div class="flex gap-3 items-center remuneration-package-row"
                                data-id="{{ $remunerationPackage->id }}">
                                <input type="hidden" name="id[]" value="{{ $remunerationPackage->id }}">
                                <input value="{{ $remunerationPackage->note }}" type="text" name="note[]"
                                    placeholder="Keterangan Remunerasi" class="input w-full border border-gray-300 p-2">
                                <input value="{{ number_format($remunerationPackage->value, 2, '.', ',') }}" type="text"
                                    placeholder="Nilai" name="value[]"
                                    class="input w-full border border-gray-300 p-2 text-right format-number">
                                <!-- Other fields here -->
                                <button type="button" class="btn btn-icon btn-clear btn-danger delete-remuneration-package"
                                    data-id="{{ $remunerationPackage->id }}">
                                    <i class="ki-filled ki-trash"></i>
                                </button>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>


    </form>

    <!-- End of Container -->
@endsection

@push('javascript')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            formatNumber();
            deleteRemunerationPackage();
        });

        function formatNumber() {
            const inputs = document.querySelectorAll('.format-number');

            inputs.forEach(input => {
                // Format input value on focusout
                input.addEventListener('blur', (e) => {
                    let value = parseFloat(input.value.replace(/,/g, '')) ||
                        0; // Remove separators and parse as float
                    input.value = value.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }); // Format with separator
                });

                // Convert formatted value back to a plain decimal number before submitting
                input.addEventListener('input', (e) => {
                    let rawValue = e.target.value.replace(/,/g, ''); // Remove separators on input
                    e.target.value = rawValue; // Set the raw value in the input field
                });
            });

            // Ensure the correct value is sent in the form
            document.querySelector('form').addEventListener('submit', function() {
                inputs.forEach(input => {
                    input.value = parseFloat(input.value.replace(/,/g, '')) ||
                        0; // Remove separators and keep only the decimal value
                });
            });
        };

        function deleteRemunerationPackage() {
            const deleteButtonRemunartionPackage = document.querySelectorAll('.delete-remuneration-package');
            deleteButtonRemunartionPackage.forEach(function(button) {
                button.addEventListener('click', function() {
                    const remunerationId = this.getAttribute(
                        'data-id'); // Get the ID from data attribute
                    const rowElement = this.closest(
                        '.remuneration-package-row'); // Get the row element to remove
                    const deleteRoute =
                        "{{ route('api.v1.workforce.employee-role-remuneration-package.destroy', ':id') }}"; // Pass route pattern to JS
                    const deleteUrl = deleteRoute.replace(':id', remunerationId);

                    if (confirm('Are you sure you want to delete this remuneration?')) {
                        // Send delete request via API
                        axios.post(deleteUrl, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                },
                                _method: 'DELETE',
                                id: remunerationId,
                            })
                            .then(response => {
                                if (!response.data.error) {
                                    // Remove the row from the DOM if deletion is successful
                                    rowElement.remove();
                                } else {
                                    alert('Failed to delete remuneration.');
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                alert('Error occurred while deleting remuneration.');
                            });
                    }
                });
            });
        }
    </script>
    <script type="text/javascript">
        const btnRemunerationPackage = document.getElementById('btn-remuneration-package');
        const remunerationPackageContainer = document.getElementById('remuneration-package-container');

        function createRemunerationPackageRow(event) {
            event.preventDefault();

            // Create a new div to hold the form row
            const rowDiv = document.createElement('div');
            rowDiv.classList.add('flex', 'gap-3', 'items-center');

            // Create input elements for note and value fields
            const noteField = createInput('text', 'note', 'Keterangan Remunerasi');
            const valueField = createInput('text', 'value', 'Nilai', ['format-number', 'text-right']);

            // Create a delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('btn', 'btn-icon', 'btn-clear', 'btn-danger');
            deleteBtn.innerHTML = '<i class="ki-filled ki-trash"></i>'; // Add the trash icon inside the button
            deleteBtn.addEventListener('click', function() {
                rowDiv.remove(); // Remove the row when the button is clicked
            });

            // Append all inputs and the delete button to the row
            rowDiv.appendChild(noteField);
            rowDiv.appendChild(valueField);
            rowDiv.appendChild(deleteBtn);

            // Append the row to the remuneration package container
            remunerationPackageContainer.appendChild(rowDiv);

            // Reapply the formatNumber function to include the new input
            formatNumber();
        }
        // Utility function to create an input element
        function createInput(type, name, placeholder, additionalClasses = []) {
            const input = document.createElement('input');
            input.setAttribute('type', type);
            input.setAttribute('name', `${name}[]`);
            input.setAttribute('placeholder', placeholder);
            input.classList.add('input', 'p-2', ...additionalClasses);
            return input;
        }


        btnRemunerationPackage.addEventListener('click', createRemunerationPackageRow);
    </script>
@endpush
