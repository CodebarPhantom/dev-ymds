@push('javascript')
    <!-- Work History & Education History DELETE-->
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {

            // Select all delete buttons
            const deleteButtonWorkHitories = document.querySelectorAll('.delete-work-history');
            deleteButtonWorkHitories.forEach(function(button) {
                button.addEventListener('click', function() {
                    const workHistoryId = this.getAttribute(
                        'data-id'); // Get the ID from data attribute
                    const rowElement = this.closest(
                        '.work-history-row'); // Get the row element to remove
                    const deleteRoute =
                        "{{ route('api.v1.workforce.employee.work-history.delete', ':id') }}"; // Pass route pattern to JS
                    const deleteUrl = deleteRoute.replace(':id', workHistoryId);

                    if (confirm('Are you sure you want to delete this work history?')) {
                        // Send delete request via API
                        axios.post(deleteUrl, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                },
                                _method: 'DELETE',
                                id: workHistoryId,
                            })
                            .then(response => {
                                if (!response.data.error) {
                                    // Remove the row from the DOM if deletion is successful
                                    rowElement.remove();
                                } else {
                                    alert('Failed to delete work history.');
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                alert('Error occurred while deleting work history.');
                            });
                    }
                });
            });

            const deleteButtonEducationHistories = document.querySelectorAll('.delete-education-history');
            deleteButtonEducationHistories.forEach(function(button) {
                button.addEventListener('click', function() {
                    const educationId = this.getAttribute(
                        'data-id'); // Get the ID from data attribute
                    const rowElement = this.closest(
                        '.education-history-row'); // Get the row element to remove
                    const deleteRoute =
                        "{{ route('api.v1.workforce.employee.education-history.delete', ':id') }}"; // Pass route pattern to JS
                    const deleteUrl = deleteRoute.replace(':id', educationId);

                    if (confirm('Are you sure you want to delete this education history?')) {
                        // Send delete request via API
                        axios.post(deleteUrl, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                },
                                _method: 'DELETE',
                                id: educationId,
                            })
                            .then(response => {
                                if (!response.data.error) {
                                    // Remove the row from the DOM if deletion is successful
                                    rowElement.remove();
                                } else {
                                    alert('Failed to delete work history.');
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                alert('Error occurred while deleting work history.');
                            });
                    }
                });
            });

            const deleteButtonEmployeeAgreement = document.querySelectorAll('.delete-employee-agreement');
            deleteButtonEmployeeAgreement.forEach(function(button) {
                button.addEventListener('click', function() {
                    const employeeAgreementID = this.getAttribute(
                        'data-id'); // Get the ID from data attribute
                    const rowElement = this.closest(
                        '.employee-agreement-row'); // Get the row element to remove
                    const deleteRoute =
                        "{{ route('api.v1.workforce.employee.employee-agreement.delete', ':id') }}"; // Pass route pattern to JS
                    const deleteUrl = deleteRoute.replace(':id', employeeAgreementID);

                    if (confirm('Are you sure you want to delete this employee agreement?')) {
                        // Send delete request via API
                        axios.post(deleteUrl, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                },
                                _method: 'DELETE',
                                id: employeeAgreementID,
                            })
                            .then(response => {
                                if (!response.data.error) {
                                    // Remove the row from the DOM if deletion is successful
                                    rowElement.remove();
                                } else {
                                    alert('Failed to delete employee agreement.');
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                alert('Error occurred while deleting employee agreement.');
                            });
                    }
                });
            });
        });
    </script>

    <!-- Work History-->
    <script type="text/javascript">
        // Select the button and the container where we will append the new rows
        const btnWorkHistory = document.getElementById('btn-work-history');
        const workHistoryContainer = document.getElementById('work-history-container');

        // Function to create a new work history row
        function createWorkHistoryRow() {
            event.preventDefault();
            // Create a new div to hold the form row
            const rowDiv = document.createElement('div');
            rowDiv.classList.add('flex', 'gap-3', 'items-center');

            // Create input elements for company_name, role_name, start_date, end_date, reason, salary
            const companyName = createInput('text', 'company_name', 'Perusahaan');
            const roleName = createInput('text', 'role_name', 'Nama Jabatan');
            const startDate = createInput('date', 'start_date', 'Tanggal Mulai');
            const endDate = createInput('date', 'end_date', 'Tanggal Selesai');
            const reason = createInput('text', 'reason', 'Alasan Berhenti');
            const salary = createInput('number', 'salary', 'Gaji');

            // Create a delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('btn', 'btn-icon', 'btn-clear', 'btn-danger');
            deleteBtn.innerHTML = '<i class="ki-filled ki-trash"></i>'; // Add the trash icon inside the button
            deleteBtn.addEventListener('click', function() {
                rowDiv.remove(); // Remove the row when the button is clicked
            });

            // Append all inputs and the delete button to the row
            rowDiv.appendChild(companyName);
            rowDiv.appendChild(roleName);
            rowDiv.appendChild(startDate);
            rowDiv.appendChild(endDate);
            rowDiv.appendChild(reason);
            rowDiv.appendChild(salary);
            rowDiv.appendChild(deleteBtn);

            // Append the row to the work history container
            workHistoryContainer.appendChild(rowDiv);
        }

        // Utility function to create an input element
        function createInput(type, name, placeholder) {
            const input = document.createElement('input');
            input.setAttribute('type', type);
            input.setAttribute('name', `${name}[]`);
            input.setAttribute('placeholder', placeholder);
            input.classList.add('input', 'p-2');
            return input;
        }

        // Event listener for the add work history button
        btnWorkHistory.addEventListener('click', createWorkHistoryRow);
    </script>

    <!-- Education History-->
    <script type="text/javascript">
        // Select the button and the container where we will append the new rows
        const btnEducationHistory = document.getElementById('btn-education-history');
        const educationHistoryContainer = document.getElementById('education-history-container');

        // Function to create a new education history row
        function createEducationHistoryRow() {
            event.preventDefault();

            // Create a new div to hold the form row
            const rowDiv = document.createElement('div');
            rowDiv.classList.add('flex', 'gap-3', 'items-center');

            // Create input elements for education_name, city, start_year, end_year, major
            const educationName = createInput('text', 'education_name', 'Nama Institusi');
            const city = createInput('text', 'city', 'Kota');
            const startYear = createInput('number', 'start_year', 'Tahun Mulai');
            const endYear = createInput('number', 'end_year', 'Tahun Selesai');
            const major = createInput('text', 'major', 'Jurusan');

            // Create a delete button with an icon
            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('btn', 'btn-icon', 'btn-clear', 'btn-danger');
            deleteBtn.innerHTML = '<i class="ki-filled ki-trash"></i>'; // Add the trash icon inside the button

            // Event listener to delete the row when the delete button is clicked
            deleteBtn.addEventListener('click', function() {
                rowDiv.remove(); // Remove the row when the button is clicked
            });

            // Append all inputs and the delete button to the row
            rowDiv.appendChild(educationName);
            rowDiv.appendChild(city);
            rowDiv.appendChild(startYear);
            rowDiv.appendChild(endYear);
            rowDiv.appendChild(major);
            rowDiv.appendChild(deleteBtn);

            // Append the row to the education history container
            educationHistoryContainer.appendChild(rowDiv);
        }

        // Utility function to create an input element
        function createInput(type, name, placeholder) {
            const input = document.createElement('input');
            input.setAttribute('type', type);
            input.setAttribute('name', `${name}[]`);
            input.setAttribute('placeholder', placeholder);
            input.classList.add('input', 'w-full', 'border', 'border-gray-300', 'p-2');
            return input;
        }

        // Event listener for the add education history button
        btnEducationHistory.addEventListener('click', createEducationHistoryRow);
    </script>

    <!-- Employee Agreement-->
    <script type="text/javascript">
        // Select the button and the container where we will append the new rows
        const btnEmployeeAgreement = document.getElementById('btn-employee-agreement');
        const employeeAgreementContainer = document.getElementById('employee-agreement-container');

        // Function to create a new education history row
        function createEmployeeAgreementRow() {
            event.preventDefault();

            // Create a new div to hold the form row
            const rowDiv = document.createElement('div');
            rowDiv.classList.add('flex', 'gap-3', 'items-center');

            // Create input elements for education_name, city, start_year, end_year, major
            const agreementName = createInput('text', 'agreement_name', 'Nama Perjanjian Kerja');
            const startDate = createInput('date', 'start_date', 'Tanggal Mulai');
            const endDate = createInput('date', 'end_date', 'Tanggal Selesai');
            // const isActive = createInput('checkbox', 'is_active');

            // Create a delete button with an icon
            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('btn', 'btn-icon', 'btn-clear', 'btn-danger');
            deleteBtn.innerHTML = '<i class="ki-filled ki-trash"></i>'; // Add the trash icon inside the button

            // Event listener to delete the row when the delete button is clicked
            deleteBtn.addEventListener('click', function() {
                rowDiv.remove(); // Remove the row when the button is clicked
            });

            // Append all inputs and the delete button to the row
            rowDiv.appendChild(agreementName);
            rowDiv.appendChild(startDate);
            rowDiv.appendChild(endDate);
            //rowDiv.appendChild(isActive);
            rowDiv.appendChild(deleteBtn);

            // Append the row to the education history container
            employeeAgreementContainer.appendChild(rowDiv);
        }

        // Utility function to create an input element
        function createInput(type, name, placeholder = '') {
            const input = document.createElement('input');
            input.setAttribute('type', type);
            input.setAttribute('name', `${name}[]`);

            // Apply placeholder only if it is not a checkbox
            if (type !== 'checkbox') {
                input.setAttribute('placeholder', placeholder);
            }

            // Apply specific classes based on the input type
            if (type === 'checkbox') {
                // Classes for checkbox input
                input.classList.add('checkbox', 'h-5', 'w-5', 'border', 'border-gray-300');
            } else {
                // Default classes for other input types (text, date, etc.)
                input.classList.add('input', 'w-full', 'border', 'border-gray-300', 'p-2');
            }

            return input;
        }

        // Event listener for the add education history button
        btnEmployeeAgreement.addEventListener('click', createEmployeeAgreementRow);
    </script>

    <!--Suppot File-->
    <script type="text/javascript">
        document.getElementById('btn-support-files').addEventListener('click', function() {
            event.preventDefault();

            document.getElementById('support-files-input').click();
        });

        document.getElementById('support-files-input').addEventListener('change', function(event) {
            const files = event.target.files;
            if (files.length > 0) {
                const formData = new FormData();
                for (let i = 0; i < files.length; i++) {
                    formData.append('files[]', files[i]);
                }
                const employeeId = '{{ $data['employee']['id'] ?? null }}';
                formData.append('employee_id', employeeId);
                // Use Axios to send the request
                axios.post('{{ route('api.v1.workforce.employee.files.upload') }}', formData, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'multipart/form-data' // Optional: Axios sets this automatically for FormData
                        }
                    })
                    .then(response => {
                        // Handle the response
                        const data = response.data.data;
                        if (data.uploadedFiles) {
                            let fileIds = [];

                            data.uploadedFiles.forEach(file => {
                                fileIds.push(file.id);
                                addFileRow(file);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error uploading files:', error);
                    });
            }
        });


        // Function to format the file size in MB or KB
        function formatFileSize(size) {
            let sizeInMB = size / (1024 * 1024);
            if (sizeInMB < 1) {
                return (size / 1024).toFixed(2) + ' KB';
            } else {
                return sizeInMB.toFixed(2) + ' MB';
            }
        }

        // Function to format the date in your desired format
        function formatDate(date) {
            let options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric'
            };
            return date.toLocaleDateString('en-GB', options); // Adjust locale based on desired format
        }

        // Function to add the file row
        function addFileRow(file) {

            const container = document.getElementById('support-files-container');
            // Get file details
            const fileName = file.slug;
            const filePath = file.path;
            const fileSize = formatFileSize(file.size); // Get file size
            const uploadDate = formatDate(new Date()); // Get the current date

            // Get the file extension and determine the icon
            const extension = fileName.split('.').pop().toLowerCase();
            let iconPath;
            switch (extension) {
                case 'pdf':
                    iconPath = '{{ asset('assets/media/file-types/pdf.svg') }}';
                    break;
                case 'docx':
                case 'doc':
                    iconPath = '{{ asset('assets/media/file-types/word.svg') }}';
                    break;
                case 'xlsx':
                case 'xls':
                    iconPath = '{{ asset('assets/media/file-types/xls.svg') }}';
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    iconPath = '{{ asset('assets/media/file-types/image.svg') }}';
                    break;
                default:
                    iconPath = '{{ asset('assets/media/file-types/mail.svg') }}';
                    break;
            }

            // Create the HTML for the new row
            const fileRow = `
                <div class="flex items-center gap-3">
                    <div class="flex items-center grow gap-2.5">
                        <img src="${iconPath}">
                        <div class="flex flex-col">
                            <a href="${filePath}" class="text-sm font-medium text-gray-900 cursor-pointer hover:text-primary mb-px" target="_blank">
                                ${fileName}
                            </a>
                            <span class="text-xs text-gray-700">
                                ${fileSize} ${uploadDate}
                            </span>
                        </div>
                    </div>
                    <button class="btn btn-icon btn-clear btn-danger" onclick="deleteFileRow(this, ${file.id})">
                        <i class="ki-filled ki-trash"></i>
                    </button>
                </div>
            `;

            // Append the new row to the container
            container.insertAdjacentHTML('beforeend', fileRow);

            // Add file ID to hidden input
            const hiddenInput = document.getElementById('uploaded-file-ids-employee');
            let fileIds = hiddenInput.value ? hiddenInput.value.split(',') : []; // Get existing IDs and split into array
            fileIds.push(file.id); // Add new file ID

            // Update hidden input with joined values
            hiddenInput.value = fileIds.join(','); // Join with commas and set the value
        }

        function deleteFileRow(element, fileId) {
            const deleteRoute = "{{ route('api.v1.workforce.employee.files.delete', ':id') }}"; // Pass route pattern to JS
            const deleteUrl = deleteRoute.replace(':id', fileId);
            event.preventDefault();


            if (!confirm('Are you sure you want to delete this file?')) return;

            // Send a DELETE request to the server to delete the file
            axios.delete(deleteUrl, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                })
                .then(response => {
                    // console.log(response.data);

                    // Remove the row from the DOM
                    const row = element.closest('.flex');
                    row.remove();

                    // Update the hidden input field by removing the deleted file's ID
                    let fileIds = document.getElementById('uploaded-file-ids-employee').value.split(',');

                    // Filter out the deleted file's ID
                    fileIds = fileIds.filter(id => id !== String(fileId));

                    // Update the hidden input with the new file IDs
                    document.getElementById('uploaded-file-ids-employee').value = fileIds.join(',');
                })
                .catch(error => {
                    console.error('Error deleting file:', error);
                });
        }
    </script>

    <!-- Post Data-->
    <script type="text/javascript">
        document.getElementById('submit-button').addEventListener('click', function(event) {
            event.preventDefault();

            const form = document.getElementById('employeeForm');

            // Create a new FormData object
            const formData = new FormData(form);

            // Add Work History dynamically
            const workHistoryContainer = document.getElementById('work-history-container');
            const workHistoryRows = workHistoryContainer.querySelectorAll('div.flex.gap-3.items-center');

            workHistoryRows.forEach((row, index) => {
                const companyName = row.querySelector('input[name="company_name[]"]').value;
                const roleName = row.querySelector('input[name="role_name[]"]').value;
                const startDate = row.querySelector('input[name="start_date[]"]').value;
                const endDate = row.querySelector('input[name="end_date[]"]').value;
                const reason = row.querySelector('input[name="reason[]"]').value;
                const salary = row.querySelector('input[name="salary[]"]').value;

                formData.append(`work_history[${index}][company_name]`, companyName);
                formData.append(`work_history[${index}][role_name]`, roleName);
                formData.append(`work_history[${index}][start_date]`, startDate);
                formData.append(`work_history[${index}][end_date]`, endDate);
                formData.append(`work_history[${index}][reason]`, reason);
                formData.append(`work_history[${index}][salary]`, salary);
            });

            const educationHistoryContainer = document.getElementById('education-history-container');
            const educationHistoryRows = educationHistoryContainer.querySelectorAll('div.flex.gap-3.items-center');
            educationHistoryRows.forEach((row, index) => {
                const educationName = row.querySelector('input[name="education_name[]"]').value;
                const city = row.querySelector('input[name="city[]"]').value;
                const startYear = row.querySelector('input[name="start_year[]"]').value;
                const endYear = row.querySelector('input[name="end_year[]"]').value;
                const major = row.querySelector('input[name="major[]"]').value;

                formData.append(`education_history[${index}][education_name]`, educationName);
                formData.append(`education_history[${index}][city]`, city);
                formData.append(`education_history[${index}][start_year]`, startYear);
                formData.append(`education_history[${index}][end_year]`, endYear);
                formData.append(`education_history[${index}][major]`, major);
            });

            const employeeAgreementContainer = document.getElementById('employee-agreement-container');
            const employeeAgreementRows = employeeAgreementContainer.querySelectorAll(
            'div.flex.gap-3.items-center');
            employeeAgreementRows.forEach((row, index) => {
                const agreementName = row.querySelector('input[name="agreement_name[]"]').value;
                const startDate = row.querySelector('input[name="start_date[]"]').value;
                const endDate = row.querySelector('input[name="end_date[]"]').value;
                // const isActive = row.querySelector('input[name="is_active[]"]').value;

                formData.append(`employee_agreement[${index}][agreement_name]`, agreementName);
                formData.append(`employee_agreement[${index}][start_date]`, startDate);
                formData.append(`employee_agreement[${index}][end_date]`, endDate);
                //formData.append(`employee_agreement[${index}][is_active]`, isActive);
            });

            // Check if we are in update mode (PUT request) or create mode (POST request)
            const employeeId = form.getAttribute(
            'data-employee-id'); // Assume that the employee ID is stored in the form as a data attribute


            let method = 'post';
            let url = '{{ route('api.v1.workforce.employee.store') }}'; // Default URL for creating a new employee

            if (employeeId) {
                const updateRoute =
                    "{{ route('api.v1.workforce.employee.update', ':id') }}"; // Pass route pattern to JS
                url = updateRoute.replace(':id', {{ $data['employee']->id ?? '' }});
                formData.append('_method', 'PUT'); // Simulate PUT request by adding the _method field
            }

            // for (let [key, value] of formData.entries()) {
            //     console.log(`${key}: ${value}`);
            // }

            // Send request with Axios
            axios({
                    method: 'post',
                    url: url,
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                })
                .then(response => {
                    if (response.status === 200 || response.status === 201) {
                        window.location.href =
                            '{{ route('workforce.employee.index') }}'; // Redirect to the employee index
                    }
                })
                .catch(error => {
                    if (error.response && error.response.status === 422) {
                        // Display validation errors
                        const errors = error.response.data.messages ||
                    []; // Assuming the validation errors are a flat array
                        const errorContainer = document.getElementById('error-container');
                        errorContainer.innerHTML = '';

                        errors.forEach((error) => {
                            const errorParagraph = document.createElement('p');
                            errorParagraph.textContent = error;
                            errorParagraph.classList.add('text-gray-700', 'text-2sm', 'font-normal');
                            errorContainer.appendChild(errorParagraph);
                        });

                        // Scroll to the top where the error messages are displayed
                        window.scrollTo(0, 0);

                        const errorApiAttention = document.getElementById('error-api-attention');
                        errorApiAttention.classList.remove('hidden');
                    }
                });
        });
    </script>
@endpush
