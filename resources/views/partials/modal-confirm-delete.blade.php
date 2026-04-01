<div class="modal" data-modal="true" id="modal_confirm_delete">
    <div class="modal-content modal-center-y max-w-[600px]">
        <div class="modal-header">
            <h3 class="modal-title">
                {{ $mainTitle }}
            </h3>
            <button class="btn btn-xs btn-icon btn-light" data-modal-dismiss="true">
                <i class="ki-outline ki-cross"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>{{ $mainContent }}</p>
            <div class="mt-4">
                <label for="confirmDelete" class="form-label">Ketik "delete" untuk konfirmasi penghapusan data:</label>
                <input type="text" id="confirmDelete" class="input w-full" placeholder="delete">
            </div>
        </div>
        <div class="modal-footer flex justify-end gap-2">
            <button class="btn btn-sm btn-light" data-modal-dismiss="true">Cancel</button>
            <button id="confirmDeleteBtn" class="btn btn-sm btn-danger">Delete</button>
        </div>
    </div>
</div>

@push('javascript')
    <script type="text/javascript">
        function openDeleteModal(itemId, modalId) {

            const modal = document.querySelector('#modal_confirm_delete');

            const confirmDeleteBtn = document.querySelector('#confirmDeleteBtn');
            const inputField = document.querySelector('#confirmDelete');

            // Clear input field on modal open
            inputField.value = '';

            // Add event listener to the delete button inside the modal
            confirmDeleteBtn.onclick = () => {
                if (inputField.value.toLowerCase() === 'delete') {
                    // Replace ":id" with the actual itemId in the delete URL
                    const finalDeleteUrl = deleteUrl.replace(':id', itemId);
                    // Proceed with deletion
                    deleteItem(finalDeleteUrl);
                    closeModal(modalId);
                } else {
                    alert('ketik "delete" untuk mengkonfirmasi.');
                }
            };
        }

        function closeModal(modalId) {
            KTModal.init()
            const modalEl = document.querySelector('#modal_confirm_delete');
            const modal = KTModal.getInstance(modalEl);
            modal.hide();
        }

        function deleteItem(finalDeleteUrl) {
            // Example: Making a fetch request to the backend for deletion
            fetch(finalDeleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (!data.error) {
                        alert('Item deleted successfully');
                        // Optionally, reload the dataTable or page
                        dataTable.showSpinner();
                        dataTable.reload();
                    } else {
                        alert('Failed to delete item');
                    }
                })
                .catch(error => console.error('Error deleting item:', error));
        }
    </script>
@endpush
