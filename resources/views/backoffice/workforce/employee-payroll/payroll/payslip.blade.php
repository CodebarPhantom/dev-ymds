<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .payslip-container {
                box-shadow: none;
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-8 payslip-container">
        <!-- Company Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="mr-24">
                <img src="{{ url('/assets/media/app/vtnet-150.png') }}" alt="Company Logo" class="h-16">
            </div>
            <div class="text-right"> <!-- Adjust max-width as needed -->
                <h1 class="text-2xl font-bold">{{ $data['employeePayroll']['entity']['name'] ?? 'Belum Ditentukan' }}
                </h1>
                <p class="text-gray-600 break-words">
                    {{ $data['employeePayroll']['company']['address'] ?? 'Belum Ditentukan' }}</p>
            </div>
        </div>

        <!-- Payslip Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold">PAY SLIP</h2>
            <p class="text-gray-600">{{ $data['employeePayroll']['formatted_payday_date_period'] }}</p>
        </div>

        <!-- Employee Details -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="grid grid-cols-2 gap-4">
                <p class="font-medium break-words">Name</p>
                <p class="text-right">{{ $data['employeePayroll']['employee']['name'] }}</p>

                <p class="font-medium break-words">Employee No</p>
                <p class="text-right">{{ $data['employeePayroll']['employee']['employee_code'] }}</p>

                <p class="font-medium break-words">Position</p>
                <p class="text-right">{{ $data['employeePayroll']['role']['name'] }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <p class="font-medium break-words">Payday Date</p>
                <p class="text-right">{{ $data['employeePayroll']['formatted_payday_date_period'] }}</p>

                <p class="font-medium break-words">Tax Status</p>
                <p class="text-right">{{ $data['employeePayroll']['ptkp']['tax_status'] ?? 'Belum Ditentukan' }}</p>

                <p class="font-medium break-words">Bank Account</p>
                <p class="text-right">{{ $data['employeePayroll']['employee']['bank_account_number'] }}</p>
            </div>
        </div>


        <!-- Earnings and Deductions -->
        <div class="grid grid-cols-2 gap-6">
            <!-- Earnings -->
            <div>
                <h3 class="text-lg font-bold mb-2">Earnings</h3>
                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                    <p class="font-medium break-words">Base Salary</p>
                    <p class="text-right">IDR {{ $data['employeePayroll']['formatted_salary_base'] }}</p>

                    <p class="font-medium break-words">Tunjangan</p>
                    <p class="text-right">IDR {{ $data['employeePayroll']['formatted_salary_remuneration'] }}</p>

                    @foreach ($data['employeePayroll']['combens'] as $comben)
                        <p class="font-medium break-words">{{ $comben['note'] }}</p>
                        <p class="text-right">IDR {{ number_format($comben['value'], 0) }}</p>
                    @endforeach

                    <p class="font-bold break-words border-t pt-1">Total Earnings</p>
                    <p class="text-right font-bold border-t pt-1">IDR
                        {{ $data['employeePayroll']['formatted_salary_gross'] }}</p>
                </div>
            </div>

            <!-- Deductions -->
            <div>
                <h3 class="text-lg font-bold mb-2">Deductions</h3>
                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                    @foreach ($data['employeePayroll']['deductions'] as $deduction)
                        <p class="font-medium break-words">{{ $deduction['note'] }}</p>
                        <p class="text-right">IDR {{ number_format($deduction['value'], 0) }}</p>
                    @endforeach

                    @foreach ($data['employeePayroll']['taxes'] as $tax)
                        <p class="font-medium break-words">{{ $tax['note'] }}</p>
                        <p class="text-right">IDR {{ number_format($tax['value'], 0) }}</p>
                    @endforeach

                    <p class="font-bold break-words border-t pt-1">Total Deductions</p>
                    <p class="text-right font-bold border-t pt-1">
                        IDR
                        {{ number_format($data['employeePayroll']['salary_deduction'] + $data['employeePayroll']['salary_tax'], 0) }}
                    </p>
                </div>
            </div>
        </div>


        <!-- Net Pay -->
        <div class="mt-8 text-center">
            <h3 class="text-xl font-bold">Net Pay: IDR {{ $data['employeePayroll']['formatted_salary_thp'] }}</h3>
            <p class="text-gray-600">{{ $data['salaryInWords'] }} Rupiah</p>
        </div>

        <!-- Signature Section -->
        <div class="mt-12 flex justify-end">
            <div class="text-center">
                <p class="mb-1">Acknowledged By,</p>
                <br>
                <br>
                <div class="mt-8 mb-2 border-t-2 border-black w-32 mx-auto"></div>
                <p class="font-bold">{{ $data['acknowledgeName'] }}</p>
                <p class="text-sm">{{ $data['acknowledgeRole'] }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-600">
            <p>Transferred to: {{ $data['employeePayroll']['bank']['name'] }} -
                {{ $data['employeePayroll']['employee']['bank_account_number'] }} -
                {{ $data['employeePayroll']['employee']['name'] }}</p>
            <p>Thank you for your hard work!</p>
        </div>
    </div>
</body>

</html>
