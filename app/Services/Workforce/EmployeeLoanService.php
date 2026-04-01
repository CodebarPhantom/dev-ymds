<?php

namespace App\Services\Workforce;

use App\Enums\LoanStatus;
use App\Services\MasterService;
use App\Models\Workforce\EmployeeLoan;
use App\Models\Workforce\EmployeeLoanDetail;
use Carbon\Carbon;


class EmployeeLoanService extends MasterService
{

    public function createLoan(array $data)
    {
        $data['installment_amount'] = $data['loan_amount'] / $data['installment_period']; // Calculate installment amount

        $employeeLoan = EmployeeLoan::create($data);

        $totalLoanAmount = intval($data['loan_amount']); // Ensure it's an integer
        $installmentPeriod = intval($data['installment_period']);

        $baseInstallment = intdiv($totalLoanAmount, $installmentPeriod); // Divide evenly
        $remainder = $totalLoanAmount % $installmentPeriod; // Get the remainder

        // Generate installment details
        $installments = [];
        $startDate = Carbon::parse($data['loan_date'])->startOfMonth()->addMonth(); //Carbon::now()->startOfMonth()->addMonth(); // Next month's first day
        for ($i = 0; $i < $data['installment_period']; $i++) {
            $amount = ($i === 0) ? $baseInstallment + $remainder : $baseInstallment; // Add remainder to first installment

            $installments[] = [
                'employee_loan_id' => $employeeLoan->id,
                'installment_date' => $startDate->copy()->addMonths($i), // Each installment on the 1st
                'installment_amount' => $data['installment_amount'],
                'installment_amount' => $amount, // Ensure it's an integer (no decimal)
                'is_paid' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert installment details in bulk
        EmployeeLoanDetail::insert($installments);

        $this->appLogService->logChange($employeeLoan, 'created');
        return;
    }

    public function showLoan($loanId)
    {
        $employeeLoan = EmployeeLoan::with('employee', 'details')->find($loanId);

        return $employeeLoan;
    }

    public function updateLoan(int $id, array $data)
    {
        $data['installment_amount'] = $data['loan_amount'] / $data['installment_period']; // Calculate installment amount

        $employeeLoan = EmployeeLoan::findOrFail($id);


        // Check if loan_amount has changed
        if ($data['loan_amount'] != $employeeLoan->loan_amount) {
            // Delete existing EmployeeLoanDetail records
            EmployeeLoanDetail::where('employee_loan_id', $id)->delete();

            // Recalculate installments
            $totalLoanAmount = intval($data['loan_amount']);
            $installmentPeriod = intval($data['installment_period']);

            $baseInstallment = intdiv($totalLoanAmount, $installmentPeriod); // Even division
            $remainder = $totalLoanAmount % $installmentPeriod; // Get remainder

            $installments = [];
            $startDate = Carbon::parse($data['loan_date'])->startOfMonth()->addMonth(); // Next month's first day

            for ($i = 0; $i < $installmentPeriod; $i++) {
                $amount = ($i === 0) ? $baseInstallment + $remainder : $baseInstallment; // Add remainder to first installment

                $installments[] = [
                    'employee_loan_id' => $employeeLoan->id,
                    'installment_date' => $startDate->copy()->addMonths($i),
                    'installment_amount' => $amount, // Ensure integer (no decimal)
                    'is_paid' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $employeeLoan->update($data);

            // Bulk insert new installment details
            EmployeeLoanDetail::insert($installments);
        }

        // Update employee loan record
        $employeeLoan->update($data);

        // Log the update
        $this->appLogService->logChange($employeeLoan, 'updated');
        return;
    }

    public function acceleratedRepaymentLoan(int $id)
    {
        // Find the employee loan
        $employeeLoan = EmployeeLoan::findOrFail($id);

        // Update all associated EmployeeLoanDetails to mark them as paid
        EmployeeLoanDetail::where('employee_loan_id', $employeeLoan->id)
            ->update([
                'is_paid' => true,
                'paid_date' => now(),
                'updated_at' => now(),
            ]);

        // Update the EmployeeLoan status to "LUNAS"
        $employeeLoan->update([
            'status' => LoanStatus::LUNAS->value,
            'updated_at' => now(),
        ]);

        return;
    }

    public function deleteLoan(int $id)
    {
        $employeeLoan = EmployeeLoan::findOrFail($id);
        EmployeeLoanDetail::where('employee_loan_id', $id)->delete();
        $employeeLoan->delete();

        // Log the deletion
        $this->appLogService->logChange($employeeLoan, 'deleted');
        return;
    }
}
