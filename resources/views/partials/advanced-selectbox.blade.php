{{--
    This Blade template provides an advanced select box component with support for both single and multiple selections.
    It uses Axios for fetching options from a specified API endpoint and allows for fallback options if the API is not provided.

    Attributes:
    - data-api: The API endpoint to fetch options from.
    - data-collection: The key in the API response where the options are stored.
    - data-options: A JSON string of predefined options to use if no API is provided.
    - data-multiple: A boolean indicating if multiple selections are allowed.
    - data-params: A JSON string of additional parameters to send with the API request.

    Elements:
    - .combobox: The main container for the select box.
    - .search-box: The input element for searching and displaying the selected option(s).
    - .dropdown-menu: The container for the dropdown options.
    - .options-container: The container where the options are rendered.
    - .pill-container: The container for selected options (pills) in multi-select mode.
    - .selected-data: A hidden input element to store the selected option(s) in JSON format.

    Functionality:
    - Fetches options from the API or uses fallback options.
    - Renders options dynamically based on the search input.
    - Supports both single and multiple selections.
    - Prefills selected options based on the hidden input value.
    - Updates the hidden input value when selections change.
    - Syncs checkboxes with selected options in multi-select mode.
    - Shows and hides the dropdown menu based on user interactions.

    Event Listeners:
    - Focus on the search box to show the dropdown.
    - Input in the search box to filter options.
    - Click outside the combobox to hide the dropdown.
    - Click on options to select/deselect

    Example Usage:
    <div class="combobox"
         data-api="/api/options"
         data-collection="items"
         data-options='[{"id":1,"label":"Option 1"},{"id":2,"label":"Option 2"}]'
         data-multiple="true"
         data-params='{"key":"value"}'>
        <input type="text" class="search-box" placeholder="Select options...">
        <div class="dropdown-menu hidden">
            <div class="options-container"></div>
        </div>
        <div class="pill-container"></div>
        <input type="hidden" class="selected-data" value='[]'>
    </div>
--}}

@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        function initializeComboboxes(container = document) {
            container.querySelectorAll('.combobox').forEach((combobox, index) => {
                const uniqueId = `combobox-${index}-${Date.now()}`; // Generate a unique ID
                combobox.setAttribute("data-id", uniqueId);
                const apiEndpoint = combobox.getAttribute('data-api'); // Get unique API endpoint
                const searchBox = combobox.querySelector('.search-box');
                const dropdownMenu = combobox.querySelector('.dropdown-menu');
                const optionsContainer = combobox.querySelector('.options-container');
                const pillContainer = combobox.querySelector('.pill-container');
                const selectedDataInput = combobox.querySelector('.selected-data');
                const dataCollection = combobox.getAttribute('data-collection');
                const isReadonly = searchBox.hasAttribute('readonly');
                const isDisabled = searchBox.hasAttribute('disabled');
                const isMultiple = combobox.getAttribute('data-multiple') ===
                    'true'; // Toggle single/multiple select

                let dataParams = combobox.getAttribute('data-params') ? JSON.parse(combobox.getAttribute(
                    'data-params')) : {};
                let selectedOptions = new Map();
                let options = [];
                let optionsLoaded = false;

                // Fallback data if API is not provided
                const predefinedOptions = combobox.getAttribute('data-options');
                const fallbackOptions = predefinedOptions ? JSON.parse(predefinedOptions) : [];

                // Assign unique ID if necessary
                searchBox.id = `${uniqueId}-search`;
                selectedDataInput.id = `${uniqueId}-selected-data`;
                combobox.querySelectorAll('input[type="checkbox"]').forEach((checkbox, idx) => {
                    checkbox.id = `${uniqueId}-option-${idx}`;
                    checkbox.nextElementSibling.setAttribute("for", checkbox.id);
                });


                // Fetch options for this combobox
                async function fetchOptions() {
                    if (!apiEndpoint) {
                        // Load from fallback options
                        options = fallbackOptions;
                        renderOptions(); // Render options immediately
                        optionsLoaded = true;
                        prefillPills(); // Ensure prefilled pills match checkboxes
                        return;
                    }

                    try {
                        const response = await axios.get(apiEndpoint, {
                            params: dataParams
                        });
                        console.log(response);
                        options = response.data.data[dataCollection];
                        renderOptions(); // Render options after fetching
                        optionsLoaded = true;
                        prefillPills(); // Ensure prefilled pills match checkboxes
                    } catch (error) {
                        console.error('Error fetching options:', error);
                        optionsContainer.innerHTML =
                            `<div class="px-3 py-2 text-sm text-red-500">Failed to load options</div>`;
                    }
                }

                // Pre-fill pills or single value if data exists in the hidden input
                function prefillPills() {
                    const predefinedData = JSON.parse(selectedDataInput.value || (isMultiple ? '[]' : 'null'));
                    if (isMultiple && Array.isArray(predefinedData)) {
                        predefinedData.forEach((id) => {
                            const option = options.find(opt => opt.id === id);
                            if (option) {
                                addPill(option.id, option.label,
                                    false); // Add pills without triggering updates
                            }
                        });
                    } else if (!isMultiple && predefinedData) {
                        const option = options.find(opt => opt.id === predefinedData);
                        if (option) {
                            selectOption(option.id, option.label, false); // Prefill single-select
                        }
                    }
                    updateCheckboxes(); // Sync checkboxes to prefilled pills
                }

                // Render options dynamically
                function renderOptions(filter = '') {
                    if (isReadonly || isDisabled) return; // Skip rendering for non-editable combobox

                    const filteredOptions = options.filter(option =>
                        option.label.toLowerCase().includes(filter.toLowerCase())
                    );

                    optionsContainer.innerHTML = ''; // Clear old options

                    if (filteredOptions.length === 0) {
                        optionsContainer.innerHTML =
                            `<div class="px-3 py-2 text-sm text-gray-500">Data not found</div>`;
                        return;
                    }

                    filteredOptions.forEach((option, index) => {
                        const uniqueComboboxId = combobox.getAttribute(
                            'data-id'); // Get combobox-specific ID
                        const optionId = `${uniqueComboboxId}-option-${option.id}`; // Ensure unique ID
                        const labelId = `${uniqueComboboxId}-label-${option.id}`;

                        const optionElement = document.createElement('div');
                        optionElement.className =
                            'flex items-center px-3 py-2 hover:bg-gray-100 cursor-pointer';

                        if (isMultiple) {
                            // Multi-select option with unique IDs
                            optionElement.innerHTML = `
                                <input
                                    type="checkbox"
                                    id="${optionId}"
                                    value="${option.id}"
                                    class="mr-2"
                                    ${selectedOptions.has(option.id) ? 'checked' : ''}
                                />
                                <label for="${optionId}" id="${labelId}" class="text-sm">${option.label}</label>
                            `;
                            optionElement.querySelector('input').addEventListener('change', (e) => {
                                if (e.target.checked) {
                                    addPill(option.id, option.label);
                                } else {
                                    removePill(option.id);
                                }
                            });
                        } else {
                            // Single-select option
                            optionElement.setAttribute('data-id', option.id);
                            optionElement.innerHTML = `<span class="text-sm">${option.label}</span>`;
                            optionElement.addEventListener('click', () => {
                                selectOption(option.id, option.label);
                            });
                        }

                        optionsContainer.appendChild(optionElement);
                    });

                    updateCheckboxes(); // Sync checkboxes to pills after rendering
                }

                // Add a pill (for multi-select)
                function addPill(id, label, updateInput = true) {
                    if (selectedOptions.has(id)) return;

                    selectedOptions.set(id, label);

                    const pill = document.createElement('div');
                    pill.className =
                        'flex items-center px-2 py-1 m-1 text-sm text-white bg-blue-500 rounded-full';
                    pill.innerHTML = `
                <span>${label}</span>
                <button
                class="ml-2 text-xs font-bold text-white bg-transparent focus:outline-none"
                data-id="${id}"
                >
                &times;
                </button>
            `;

                    if (!isReadonly && !isDisabled) {
                        pill.querySelector('button').addEventListener('click', () => {
                            removePill(id);
                            renderOptions(searchBox.value);
                        });
                    }

                    pillContainer.insertBefore(pill, searchBox);
                    if (updateInput) updateHiddenInput();
                }

                // Select an option (for single-select)
                function selectOption(id, label, updateInput = true) {
                    selectedOptions.clear();
                    selectedOptions.set(id, label);
                    searchBox.value = label;
                    if (updateInput) updateHiddenInput();
                    hideDropdown();
                }

                // Remove a pill (for multi-select)
                function removePill(id) {
                    if (isReadonly || isDisabled) return;
                    selectedOptions.delete(id);
                    const pill = pillContainer.querySelector(`button[data-id="${id}"]`).parentNode;
                    pill.remove();
                    updateHiddenInput();
                    updateCheckboxes();
                }

                // Update the hidden input
                function updateHiddenInput() {
                    if (isMultiple) {
                        const selectedValues = Array.from(selectedOptions.keys());
                        selectedDataInput.value = JSON.stringify(selectedValues);
                    } else {
                        const [selectedValue] = selectedOptions.keys();
                        selectedDataInput.value = selectedValue || '';
                    }
                }

                // Sync checkboxes with the pills (for multi-select)
                function updateCheckboxes() {
                    const checkboxes = optionsContainer.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = selectedOptions.has(parseInt(checkbox.value));
                    });
                }

                // Show dropdown
                function showDropdown() {
                    dropdownMenu.classList.remove('hidden');
                }

                // Hide dropdown
                function hideDropdown() {
                    dropdownMenu.classList.add('hidden');
                }

                // Event Listeners
                if (!isReadonly && !isDisabled) {
                    searchBox.addEventListener('focus', showDropdown);
                    searchBox.addEventListener('input', (e) => renderOptions(e.target.value));
                }

                document.addEventListener('mousedown', (e) => {
                    if (!combobox.contains(e.target)) {
                        hideDropdown();
                    }
                });

                dropdownMenu.addEventListener('mousedown', (e) => e.stopPropagation());
                pillContainer.addEventListener('mousedown', (e) => e.stopPropagation());

                // Initialize the component
                async function initialize() {
                    await fetchOptions(); // Fetch options on page load or load fallback data
                }

                // Reinitialize with new dataParams
                combobox.updateParams = async function(newParams) {
                    dataParams = newParams;
                    optionsLoaded = false; // Mark options as not loaded
                    await fetchOptions(); // Fetch new options
                };

                initialize(); // Initial setup
            });
        }

        const setupLinkedCombobox = (triggerComboboxSelector, targetComboboxSelector, paramKey) => {
            const triggerCombobox = document.querySelector(triggerComboboxSelector);
            const targetCombobox = document.querySelector(targetComboboxSelector);
            if (!triggerCombobox || !targetCombobox) return;

            const triggerHiddenInput = triggerCombobox.querySelector('.selected-data');

            if (!triggerHiddenInput) {
                console.error("Error: No hidden input found in trigger combobox.");
                return;
            }

            // Function to update department combobox params
            const updateDepartmentParams = () => {
                const selectedDivisionId = triggerHiddenInput.value || "";
                console.log(`Updating department filter: ${paramKey} = ${selectedDivisionId}`); // Debugging log
                targetCombobox.setAttribute('data-params', JSON.stringify({
                    [paramKey]: selectedDivisionId
                }));
                targetCombobox.updateParams?.({
                    [paramKey]: selectedDivisionId
                }); // Ensure API reloads
            };

            // Listen for changes on the hidden input inside the combobox
            const observer = new MutationObserver(() => updateDepartmentParams());
            observer.observe(triggerHiddenInput, {
                attributes: true,
                attributeFilter: ['value']
            });

            // Run on page load in case of pre-selected value
            updateDepartmentParams();
        };


        // const setupCombobox = (comboboxSelector) => {
        //     document.querySelectorAll(comboboxSelector).forEach(combobox => {
        //         const optionsContainer = combobox.querySelector('.options-container');
        //         const hiddenInput = combobox.querySelector('.selected-data');

        //         optionsContainer?.addEventListener('click', (e) => {
        //             const selectedOption = e.target.closest('div[data-id]');
        //             if (selectedOption && hiddenInput) {
        //                 console.log(
        //                     `User selected option: ${selectedOption.textContent.trim()} (ID: ${selectedOption.dataset.id})`
        //                 );
        //                 hiddenInput.value = selectedOption.dataset.id;
        //                 hiddenInput.dispatchEvent(new Event('change'));
        //             }
        //         });
        //     });
        // };

        initializeComboboxes();



        // const updateComboboxParams = (comboboxElement, relatedElementId) => {
        //     const relatedElement = document.getElementById(relatedElementId);

        //     const updateParams = () => {
        //         const relatedId = relatedElement.value;
        //         const dataParams = JSON.parse(comboboxElement.getAttribute('data-params') || '{}');
        //         dataParams[relatedElementId] = relatedId; // Associate the related element's ID
        //         comboboxElement.setAttribute('data-params', JSON.stringify(dataParams));

        //         if (typeof comboboxElement.updateParams === 'function') {
        //             comboboxElement.updateParams(dataParams);
        //         }
        //     };

        //     // Update params when the related element's value changes
        //     relatedElement.addEventListener('change', updateParams);

        //     // Handle option selection within the combobox, without changing the relatedElement value
        //     comboboxElement.querySelector('.options-container')?.addEventListener('click', (e) => {
        //         const selectedOption = e.target.closest('div[data-id]');
        //         if (selectedOption) {
        //             const selectedOptionId = selectedOption.dataset.id;
        //             const selectedOptionLabel = selectedOption.textContent.trim();

        //             console.log(
        //                 `Selected Option in combobox: ID=${selectedOptionId}, Label=${selectedOptionLabel}`);

        //             // Dispatch updateParams without modifying relatedElement
        //             comboboxElement.dispatchEvent(new CustomEvent('selectionChange', {
        //                 detail: {
        //                     id: selectedOptionId,
        //                     label: selectedOptionLabel
        //                 }
        //             }));
        //         }
        //     });
        // };
        // document.addEventListener('DOMContentLoaded', () => {
        //     const divisionIdElement = document.getElementById('division_id');
        //     const departmentCombobox = document.getElementById('department_combobox');

        //     if (divisionIdElement && departmentCombobox) {
        //         updateComboboxParams(departmentCombobox, 'division_id');
        //     }
        // });
    </script>
@endpush
