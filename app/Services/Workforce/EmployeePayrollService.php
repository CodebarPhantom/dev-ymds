<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Employee;
use App\Models\Workforce\EmployeeOvertime;
use App\Models\Workforce\EmployeePayroll;
use App\Services\MasterService;
use Carbon\Carbon;
use App\Enums\EmployeePayrollStatus;
use App\Enums\OvertimeStatus;
use App\Enums\ShiftAttendanceStatus;
use App\Models\Workforce\EmployeePayrollComben;
use App\Models\Workforce\EmployeePayrollDeduction;
use App\Models\Workforce\EmployeePayrollSchedule;
use App\Models\Workforce\EmployeePayrollTax;
use App\Models\WorkSchedule\ShiftAttendance;
use App\Enums\GlobalParam;

class EmployeePayrollService extends MasterService
{

    public function getAllEmployeeWithPayroll()
    {
        Employee::with(['latestPayroll', 'user'])->active()->get();
    }

    public function getEmployeePayrollById($employeePayrollId)
    {
        return EmployeePayroll::with([
            'employee',
            'combens',
            'taxes',
            'deductions',
            'bank',
            'role',
            'ptkp',
            'entity',
            'company'
        ])->find($employeePayrollId);
    }
    // public function generatePayroll($month, $year)
    // {
    //     $startDate = Carbon::parse($year . '-' . $month . '-01')->startOfMonth()->format('Y-m-d');
    //     $endDate = Carbon::parse($year . '-' . $month . '-01')->endOfMonth()->format('Y-m-d');
    //     $currentPeriod = Carbon::parse($year . '-' . $month . '-01')->format('Ym');
    //     $currentPeriodFormatted = Carbon::parse($year . '-' . $month . '-01')->format('F Y');

    //     $checkPeriod = EmployeePayrollSchedule::where('date', $startDate)->exists();

    //     if ($checkPeriod) {
    //         return;
    //     }

    //     $payrollSchedule = EmployeePayrollSchedule::create([
    //         'date' => $startDate
    //     ]);

    //     $employeeOvertimes = EmployeeOvertime::whereBetween('start_date', [$startDate, $endDate])
    //         ->where('is_paid', false)
    //         ->get();

    //     // Group by employee_id and process
    //     $groupedOvertimes = $employeeOvertimes->groupBy('employee_id');

    //     foreach ($groupedOvertimes as $employeeId => $overtime) {
    //         // Calculate the sum of overtime_pay for this employee
    //         $totalOvertimePay = $overtime->sum('overtime_pay');

    //         // Update the is_paid column to true for all records of this employee
    //         EmployeeOvertime::where('employee_id', $employeeId)
    //             ->whereIn('id', $overtime->pluck('id')) // Update only fetched records
    //             ->update(['is_paid' => true]);

    //         // Retrieve the employee's name for the note (assuming Employee model has the name field)
    //         $employeeName = Employee::find($employeeId)->name;

    //         // Insert a new record into EmployeePayrollComben
    //         EmployeePayrollComben::create([
    //             'employee_id' => $employeeId,
    //             'note' => "Lembur untuk $employeeName periode $currentPeriodFormatted",
    //             'value' => $totalOvertimePay,
    //             'date' => $startDate,
    //         ]);
    //     }

    //     $employees = employee::active()->get();

    //     foreach ($employees as $employee) {
    //         $totalWorkdays = ShiftAttendance::whereBetween('date', [$startDate, $endDate])->where('employee_id', $employee->id)->get()->count();
    //         $absentDays = ShiftAttendance::whereBetween('date', [$startDate, $endDate])
    //             ->where('status', ShiftAttendanceStatus::TIDAK_MASUK->value)
    //             ->where('employee_id', $employee->id)
    //             ->get()
    //             ->count();

    //         if ($totalWorkdays > 0 && $absentDays > 0) {
    //             $dailySalary = ($employee->salary_value + ($employee->total_remuneration ?? 0)) / $totalWorkdays; // Prorate salary per day
    //             $totalCutAbsent = $dailySalary * $absentDays; // Total deduction for absences
    //             EmployeePayrollDeduction::create([
    //                 'employee_id' => $employee->id,
    //                 'note' => "Potongan Tidak Hadir $currentPeriodFormatted ",
    //                 'value' => $totalCutAbsent,
    //                 'date' => $startDate,
    //             ]);
    //         }
    //     }






    //     $employeeWithDetails = Employee::with([
    //         'latestPayroll'
    //     ])
    //         ->withSum(['combens as total_combens' => function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('date', [$startDate, $endDate]);
    //         }], 'value')
    //         ->withSum(['taxes as total_taxes' => function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('date', [$startDate, $endDate]);
    //         }], 'value')
    //         ->withSum(['deductions as total_deductions' => function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('date', [$startDate, $endDate]);
    //         }], 'value')
    //         ->withSum('packageRenumerations as total_remuneration', 'value')
    //         ->active()->get();

    //     foreach ($employeeWithDetails as $employeeWithDetail) {

    //         $lastPayroll = EmployeePayroll::where('code', 'LIKE', "$currentPeriod-%")
    //             ->orderBy('code', 'desc')
    //             ->first();

    //         // Generate the next number
    //         $nextNumber = $lastPayroll
    //             ? intval(substr($lastPayroll->code, -5)) + 1 // Extract the last 5 digits and increment
    //             : 1; // Start from 1 if no payrolls exist for this period

    //         // Format the new payroll code
    //         $newCode = sprintf('%s-%05d', $currentPeriod, $nextNumber);
    //         $checkIsEmployeeDailyPay = $employeeWithDetail->employee_status;
    //         $multiplyPay = 1;

    //         if ($checkIsEmployeeDailyPay === "Harian") {
    //             $multiplyPay = ShiftAttendance::where('employee_id', $employeeWithDetail->id)->where('status', ShiftAttendanceStatus::MASUK->value)
    //                 ->whereBetween('date', [$startDate, $endDate])->get()->count();
    //         }


    //         $employeePayroll = EmployeePayroll::create([
    //             'employee_id' => $employeeWithDetail->id,
    //             'code' => $newCode,
    //             'employee_payroll_schedule_id' => $payrollSchedule->id,
    //             'status' => EmployeePayrollStatus::ALREADY_GENERATED->value,
    //             'payday_date' => $endDate,
    //             'salary_base' => $employeeWithDetail->salary_value * $multiplyPay,
    //             'salary_remuneration' => $employeeWithDetail->total_remuneration ?? 0,
    //             'salary_comben' => $employeeWithDetail->total_combens ?? 0,
    //             'salary_gross' => $employeeWithDetail->salary_value + ($employeeWithDetail->total_remuneration ?? 0) + ($employeeWithDetail->total_combens ?? 0),
    //             'salary_deduction' => $employeeWithDetail->total_deductions ?? 0,
    //             'salary_tax' => $employeeWithDetail->total_taxes ?? 0,
    //             'salary_thp' => $employeeWithDetail->salary_value + ($employeeWithDetail->total_remuneration ?? 0) + ($employeeWithDetail->total_combens ?? 0) - ($employeeWithDetail->total_taxes ?? 0) - ($employeeWithDetail->total_deductions ?? 0)
    //         ]);

    //         foreach ($employeeWithDetail->combens as $comben) {
    //             EmployeePayrollComben::where('id', $comben->id)->update(
    //                 [
    //                     'is_paid' => EmployeePayrollStatus::ALREADY_GENERATED->value,
    //                     'employee_payroll_id' => $employeePayroll->id
    //                 ]
    //             );
    //         }

    //         foreach ($employeeWithDetail->taxes as $tax) {
    //             EmployeePayrollTax::where('id', $tax->id)->update(
    //                 [
    //                     'is_paid' => EmployeePayrollStatus::ALREADY_GENERATED->value,
    //                     'employee_payroll_id' => $employeePayroll->id
    //                 ]
    //             );
    //         }

    //         foreach ($employeeWithDetail->deductions as $deduction) {
    //             EmployeePayrollDeduction::where('id', $deduction->id)->update(
    //                 [
    //                     'is_paid' => EmployeePayrollStatus::ALREADY_GENERATED->value,
    //                     'employee_payroll_id' => $employeePayroll->id
    //                 ]
    //             );
    //         }
    //     }


    //     return;
    // }

    // public function generatePayroll($month, $year)
    // {
    //     // Define dates and formats
    //     $startDate = Carbon::parse("$year-$month-01")->startOfMonth()->format('Y-m-d');
    //     $endDate = Carbon::parse("$year-$month-01")->endOfMonth()->format('Y-m-d');
    //     $currentPeriod = Carbon::parse("$year-$month-01")->format('Ym');
    //     $currentPeriodFormatted = Carbon::parse("$year-$month-01")->format('F Y');

    //     // Create payroll schedule
    //     $payrollSchedule = EmployeePayrollSchedule::create(['date' => $startDate]);

    //     // Handle overtime payments
    //     $this->processOvertime($startDate, $endDate, $currentPeriodFormatted);

    //     // Retrieve employee details
    //     $employees = Employee::with([
    //         'latestPayroll',
    //         'combens' => fn($query) => $query->whereBetween('date', [$startDate, $endDate]),
    //         'taxes' => fn($query) => $query->whereBetween('date', [$startDate, $endDate]),
    //         'deductions' => fn($query) => $query->whereBetween('date', [$startDate, $endDate])
    //     ])
    //         ->withSum('combens as total_combens', 'value')
    //         ->withSum('taxes as total_taxes', 'value')
    //         ->withSum('deductions as total_deductions', 'value')
    //         ->withSum('packageRenumerations as total_remuneration', 'value')
    //         ->active()->get();

    //     foreach ($employees as $employee) {
    //         $this->processEmployeePayroll($employee, $startDate, $endDate, $currentPeriod, $currentPeriodFormatted, $payrollSchedule->id);
    //     }
    // }

    public function generatePayroll($month, $year)
    {
        // Initialize dates and period information
        $baseDate = Carbon::parse("$year-$month-01");
        $startDate = $baseDate->startOfMonth()->format('Y-m-d');
        $endDate = $baseDate->endOfMonth()->format('Y-m-d');
        $formattedPeriod = $baseDate->format('F Y');
        $currentPeriod = $baseDate->format('Ym');

        // Check if payroll for the period already exists
        if (EmployeePayrollSchedule::where('date', $startDate)->exists()) {
            return;
        }

        // Create payroll schedule
        $payrollSchedule = EmployeePayrollSchedule::create(['date' => $startDate]);

        // Process overtime payments
        $this->processOvertime($startDate, $endDate, $formattedPeriod);

        // Process employee deductions for absences
        $this->processDeductions($startDate, $endDate, $formattedPeriod);

        // Generate payroll for each employee
        $this->generateEmployeePayrolls($startDate, $endDate, $currentPeriod, $formattedPeriod, $payrollSchedule);
    }

    private function processOvertime($startDate, $endDate, $formattedPeriod)
    {
        $employeeOvertimes = EmployeeOvertime::whereBetween('start_date', [$startDate, $endDate])
            ->where('is_paid', false)
            ->where('status', OvertimeStatus::DISETUJUI->value)
            ->get()
            ->groupBy('employee_id');

        foreach ($employeeOvertimes as $employeeId => $overtime) {
            $totalOvertimePay = $overtime->sum('overtime_pay');

            EmployeeOvertime::where('employee_id', $employeeId)
                ->whereIn('id', $overtime->pluck('id'))
                ->update(['is_paid' => true]);

            EmployeePayrollComben::create([
                'employee_id' => $employeeId,
                'note' => "Lembur periode $formattedPeriod",
                'value' => $totalOvertimePay,
                'date' => $startDate,
            ]);
        }
    }

    private function processDeductions($startDate, $endDate, $formattedPeriod)
    {
        $employees = Employee::active()
            ->withSum('packageRenumerations as total_package_renumerations', 'value')
            ->get();


        foreach ($employees as $employee) {
            if ($employee->employee_status !== "Harian") { //jika bukan harian
                $totalWorkdays = ShiftAttendance::whereBetween('date', [$startDate, $endDate])
                    ->where('employee_id', $employee->id)
                    ->whereNotIn('status', [
                        ShiftAttendanceStatus::LIBUR_KERJA->value,
                        ShiftAttendanceStatus::LIBUR_NASIONAL->value,
                        // ShiftAttendanceStatus::CUTI->value,
                        // ShiftAttendanceStatus::SAKIT->value,
                    ])
                    ->count();

                $absentDays = ShiftAttendance::whereBetween('date', [$startDate, $endDate])
                    ->where('employee_id', $employee->id)
                    ->whereIn('status', [
                        ShiftAttendanceStatus::TIDAK_MASUK->value,
                        ShiftAttendanceStatus::UNPAID_LEAVE->value
                    ])
                    ->count();

                if ($totalWorkdays > 0 && $absentDays > 0) {
                    $dailySalary = ($employee->salary_value + ($employee->total_package_renumerations ?? 0)) / $totalWorkdays;
                    $totalCutAbsent = $dailySalary * $absentDays;

                    EmployeePayrollDeduction::create([
                        'employee_id' => $employee->id,
                        'note' => "Potongan Tidak Hadir $formattedPeriod",
                        'value' => $totalCutAbsent,
                        'date' => $startDate,
                    ]);
                }
            } else { ////jika harian
                $totalWorkdays = (int)GlobalParam::TOTAL_WORK_DAYS_DAILY_WORKER->value;

                $absentDays = ShiftAttendance::whereBetween('date', [$startDate, $endDate])
                    ->where('employee_id', $employee->id)
                    ->whereIn('status', [
                        ShiftAttendanceStatus::TIDAK_MASUK->value,
                        ShiftAttendanceStatus::UNPAID_LEAVE->value
                    ])
                    ->count();

                if ($totalWorkdays > 0 && $absentDays > 0) {
                    $renumeration = (($employee->total_package_renumerations ?? 0)) / $totalWorkdays;
                    $totalCutAbsent = $renumeration * $absentDays;

                    EmployeePayrollDeduction::create([
                        'employee_id' => $employee->id,
                        'note' => "Potongan Tidak Hadir $formattedPeriod",
                        'value' => $totalCutAbsent,
                        'date' => $startDate,
                    ]);
                }
            }

            foreach ($employee->loans->whereBetween('installment_date', [$startDate, $endDate]) as $loanDetail) {
                EmployeePayrollDeduction::create([
                    'employee_id' => $employee->id,
                    'note' => "Potongan Pinjaman $formattedPeriod",
                    'value' => $loanDetail->installment_amount,
                    'date' => $loanDetail->installment_date,
                ]);
            }

        }
    }

    private function generateEmployeePayrolls($startDate, $endDate, $currentPeriod, $formattedPeriod, $payrollSchedule)
    {
        $employees = Employee::with([
            'combens' => fn($query) => $query->whereBetween('date', [$startDate, $endDate]),
            'taxes' => fn($query) => $query->whereBetween('date', [$startDate, $endDate]),
            'deductions' => fn($query) => $query->whereBetween('date', [$startDate, $endDate]),
            'loans' => fn($query) => $query->whereBetween('installment_date', [$startDate, $endDate]),
        ])
            ->withSum(['combens as total_combens' => fn($query) => $query->whereBetween('date', [$startDate, $endDate])], 'value')
            ->withSum(['taxes as total_taxes' => fn($query) => $query->whereBetween('date', [$startDate, $endDate])], 'value')
            ->withSum(['deductions as total_deductions' => fn($query) => $query->whereBetween('date', [$startDate, $endDate])], 'value')
            ->withSum(['loans as total_loans' => fn($query) => $query->whereBetween('installment_date', [$startDate, $endDate])], 'installment_amount')
            ->withSum('packageRenumerations as total_package_renumerations', 'value')
            ->active()
            ->get();

        foreach ($employees as $employee) {
            $newCode = $this->generatePayrollCode($currentPeriod);

            $dailyMultiplier = $employee->employee_status === 'Harian'
                ? ShiftAttendance::where('employee_id', $employee->id)
                ->where('status', ShiftAttendanceStatus::MASUK->value)
                ->whereBetween('date', [$startDate, $endDate])
                ->count()
                : 1;

            $salaryBase = $employee->salary_value * $dailyMultiplier;

            $employeePayroll = EmployeePayroll::create([
                'employee_id' => $employee->id,
                'code' => $newCode,
                'employee_payroll_schedule_id' => $payrollSchedule->id,
                'status' => EmployeePayrollStatus::ALREADY_GENERATED->value,
                'payday_date' => $endDate,
                'salary_base' => $salaryBase,
                'salary_remuneration' => $employee->total_package_renumerations ?? 0,
                'salary_comben' => $employee->total_combens ?? 0,
                'salary_gross' => $salaryBase + ($employee->total_combens ?? 0) + ($employee->total_package_renumerations ?? 0),
                'salary_deduction' => ($employee->total_deductions ?? 0) + ($employee->total_loans ?? 0),
                'salary_tax' => $employee->total_taxes ?? 0,
                'salary_thp' => $salaryBase + ($employee->total_combens ?? 0) + ($employee->total_package_renumerations ?? 0)
                    - ($employee->total_deductions ?? 0)  - ($employee->total_loans ?? 0) - ($employee->total_taxes ?? 0),
            ]);

            $this->markAsPaid($employee, $employeePayroll);
        }
    }

    private function generatePayrollCode($currentPeriod)
    {
        $lastPayroll = EmployeePayroll::where('code', 'LIKE', "$currentPeriod-%")
            ->orderBy('code', 'desc')
            ->first();

        $nextNumber = $lastPayroll ? intval(substr($lastPayroll->code, -5)) + 1 : 1;
        return sprintf('%s-%05d', $currentPeriod, $nextNumber);
    }

    private function markAsPaid($employee, $employeePayroll)
    {
        foreach (['combens', 'taxes', 'deductions', 'loans'] as $relation) {
            foreach ($employee->$relation as $item) {
                $item->update([
                    'is_paid' => EmployeePayrollStatus::ALREADY_GENERATED->value,
                    'employee_payroll_id' => $employeePayroll->id,
                    'paid_date' => Carbon::now()
                ]);
            }
        }

        //Update EmployeeLoan status based on installment payments
        foreach ($employee->loans as $loanDetail) {
            $employeeLoan = $loanDetail->loan; // Get the parent EmployeeLoan
            if ($employeeLoan) {
                $employeeLoan->updatePaymentStatus();
            }
        }
    }
}
