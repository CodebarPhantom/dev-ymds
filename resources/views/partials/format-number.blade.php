<script>
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
</script>
