    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle text-green-500 text-xl"></i>
        <span id="toastMessage" class="font-medium"></span>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function selectCategory(id, name) {
            localStorage.setItem('selectedCategory', JSON.stringify({
                id: id,
                name: name
            }));
            showToast(`✓ Blok ${name} dipilih`);
        }

        function copyToClipboard(accountNumber, bankName) {
            // Get the button element more reliably
            const button = event.currentTarget;
            const originalHTML = button.innerHTML;

            // Show loading state
            button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> <span>Menyalin...</span>';
            button.disabled = true;

            navigator.clipboard.writeText(accountNumber).then(() => {
                // Success feedback
                button.innerHTML = '<i class="bi bi-check-lg"></i> <span>Tersalin</span>';
                button.classList.add('bg-green-600');
                button.classList.remove('bg-white', 'text-blue-600', 'text-orange-600', 'text-green-600');

                showToast(`✓ No. Rekening ${bankName} disalin!`);

                // Reset button after delay
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    button.classList.remove('bg-green-600');

                    // Restore original button classes based on bank
                    if (bankName === 'Sea Bank') {
                        button.classList.add('bg-white', 'text-blue-600');
                    } else if (bankName === 'BNI') {
                        button.classList.add('bg-white', 'text-orange-600');
                    } else if (bankName === 'Bank Muamalat') {
                        button.classList.add('bg-white', 'text-green-600');
                    }
                }, 2000);
            }).catch((err) => {
                console.error('Clipboard error:', err);

                // Fallback: try using document.execCommand
                const textArea = document.createElement('textarea');
                textArea.value = accountNumber;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    const successful = document.execCommand('copy');
                    document.body.removeChild(textArea);

                    if (successful) {
                        button.innerHTML = '<i class="bi bi-check-lg"></i> <span>Tersalin</span>';
                        button.classList.add('bg-green-600');
                        button.classList.remove('bg-white', 'text-blue-600', 'text-orange-600', 'text-green-600');

                        showToast(`✓ No. Rekening ${bankName} disalin!`);

                        setTimeout(() => {
                            button.innerHTML = originalHTML;
                            button.disabled = false;
                            button.classList.remove('bg-green-600');

                            // Restore original button classes
                            if (bankName === 'Sea Bank') {
                                button.classList.add('bg-white', 'text-blue-600');
                            } else if (bankName === 'BNI') {
                                button.classList.add('bg-white', 'text-orange-600');
                            } else if (bankName === 'Bank Muamalat') {
                                button.classList.add('bg-white', 'text-green-600');
                            }
                        }, 2000);
                    } else {
                        throw new Error('execCommand failed');
                    }
                } catch (fallbackErr) {
                    // Reset button on error
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    showToast('✗ Gagal menyalin. Silakan salin manual.');
                }
            });
        }

        // Format number with comma separator
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Parse number from formatted string
        function parseNumber(str) {
            return str.replace(/,/g, '');
        }

        // Show Toast
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');

            toastMessage.textContent = message;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Initialize tab indicator position
        document.addEventListener('DOMContentLoaded', () => {
            const tabIndicator = document.getElementById('tabIndicator');
            if (tabIndicator) {
                tabIndicator.style.width = '50%';
                tabIndicator.style.left = '0';
            }

            // Amount input formatting (for form pages)
            const amountInput = document.getElementById('amount');
            if (amountInput) {
                amountInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/,/g, '');
                    if (!isNaN(value) && value !== '') {
                        e.target.value = formatNumber(value);
                    }
                });
            }

            // Block selection for non-block categories (for form pages)
            const blockSelect = document.getElementById('blockSelect');
            if (blockSelect) {
                blockSelect.addEventListener('change', function() {
                    const blockId = this.value;
                    const locationSelect = document.getElementById('locationSelect');

                    if (blockId) {
                        fetch(`/api/v1/locations/block/${blockId}`)
                            .then(response => response.json())
                            .then(response => {
                                // console.log('API Response:', response);
                                locationSelect.innerHTML = '<option value="">-- Pilih Lokasi --</option>';

                                // Handle both old and new response formats
                                const locations = response.data || response;
                                if (Array.isArray(locations)) {
                                    locations.forEach(location => {
                                        locationSelect.innerHTML += `<option value="${location.id}">${location.name}</option>`;
                                    });
                                }
                                locationSelect.disabled = false;
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showToast('Gagal memuat lokasi');
                            });
                    } else {
                        locationSelect.innerHTML = '<option value="">-- Pilih Lokasi --</option>';
                        locationSelect.disabled = true;
                    }
                });
            }

            // Submit form (for form pages)
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    const form = document.getElementById('paymentForm');
                    const formData = new FormData(form);

                    // Get form values for validation
                    const locationId = document.getElementById('locationSelect').value;
                    const payerName = document.getElementById('payerName').value;
                    const month = document.getElementById('monthSelect').value;
                    const year = document.getElementById('yearSelect').value;
                    const amount = parseNumber(document.getElementById('amount').value || '0');
                    const proofFile = document.getElementById('proofFile').files[0];

                    // Validation
                    if (!locationId || !payerName || !month || !year || !amount || !proofFile) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Form Belum Lengkap',
                            text: 'Mohon lengkapi semua field yang wajib diisi termasuk bukti transfer',
                            confirmButtonColor: '#3b82f6'
                        });
                        return;
                    }

                    // Show loading
                    Swal.fire({
                        title: 'Mengirim Konfirmasi...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Prepare form data
                    const submitData = new FormData();
                    submitData.append('location_id', locationId);
                    submitData.append('location_category_id', document.querySelector('[data-category-id]')?.getAttribute('data-category-id') || '');
                    submitData.append('payer_name', payerName);
                    submitData.append('month', month);
                    submitData.append('year', year);
                    submitData.append('amount', amount);
                    submitData.append('notes', document.getElementById('notes').value);

                    if (proofFile) {
                        submitData.append('proof_file', proofFile);
                    }

                    // Submit to server
                    fetch('/payment/submit', {
                        method: 'POST',
                        body: submitData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();

                        if (data.success) {
                            // Redirect to summary page
                            window.location.href = data.redirect_url;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Mengirim',
                                text: data.message || 'Terjadi kesalahan saat mengirim konfirmasi',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Gagal mengirim konfirmasi. Silakan coba lagi.',
                            confirmButtonColor: '#ef4444'
                        });
                    });
                });
            }
        });

        // Add click handlers to category cards only (not all cards)
        document.querySelectorAll('a[href*="landing.filter"] .card').forEach(card => {
            card.addEventListener('click', function() {
                const blockName = this.querySelector('h3')?.textContent;
                if (blockName) {
                    showToast(`✓ ${blockName} dipilih`);
                }
            });
        });

        // Tab Switching
        function switchTab(tab) {
            const bendaharaContent = document.getElementById('bendaharaContent');
            const masjidContent = document.getElementById('masjidContent');
            const tabIndicator = document.getElementById('tabIndicator');
            const tabButtons = document.querySelectorAll('.tab-button');

            if (bendaharaContent && masjidContent && tabIndicator && tabButtons.length > 0) {
                if (tab === 'bendahara') {
                    bendaharaContent.classList.remove('hidden');
                    masjidContent.classList.add('hidden');
                    tabIndicator.style.width = '50%';
                    tabIndicator.style.left = '0';
                    tabButtons[0].classList.add('active');
                    tabButtons[1].classList.remove('active');
                } else {
                    bendaharaContent.classList.add('hidden');
                    masjidContent.classList.remove('hidden');
                    tabIndicator.style.width = '50%';
                    tabIndicator.style.left = '50%';
                    tabButtons[1].classList.add('active');
                    tabButtons[0].classList.remove('active');
                }
            }
        }
    </script>
