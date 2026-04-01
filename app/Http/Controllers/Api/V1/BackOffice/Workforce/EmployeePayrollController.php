<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeePayroll;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\Workforce\Employee;
use Illuminate\Support\Facades\Log;


class EmployeePayrollController extends MasterController
{
    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', EmployeePayroll::class);

        // Parse the search query
        $search = $request->input('search', '');
        $filters = [];
        parse_str($search, $filters); // Parse query string format into an array

        $month = $filters['month'] ?? now()->month; // Default to current month
        $year = $filters['year'] ?? now()->year;    // Default to current year
        $employeeName = $filters['name'] ?? '';     // Extract employee name if in structured format

        // If the search term is not structured, assume it's the employee name
        if (empty($filters) && !empty($search)) {
            $employeeName = $search;
        }

        $pageSize = $request->input('size', 10);
        $sortField = $request->input('sortField', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');

        $allowedSortFields = ['name', 'created_at'];
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }

        $startDate = Carbon::create($year, $month, 1)->format('Y-m-d H:i:s');
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d H:i:s');

        $employeePayrolls = EmployeePayroll::with(['employee'])
            ->whereBetween('payday_date', [$startDate, $endDate])
            ->when(!empty($employeeName), function ($query) use ($employeeName) {
                $query->whereHas('employee', function ($query) use ($employeeName) {
                    $query->where('name', 'ILIKE', '%' . $employeeName . '%');
                });
            });

        if ($sortField === 'name') {
            $employeePayrolls = $employeePayrolls->join('employees', 'employee_payrolls.employee_id', '=', 'employees.id')
                ->orderBy('employees.name', $sortOrder);
        } else {
            $employeePayrolls = $employeePayrolls->orderBy($sortField, $sortOrder);
        }

        $employeePayrolls = $employeePayrolls->paginate($pageSize);

        return response()->json([
            'page' => $employeePayrolls->currentPage(),
            'pageCount' => $employeePayrolls->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $employeePayrolls->total(),
            'data' => $employeePayrolls->items(),
        ]);
    }
}


// $employeePayrolls = Employee::with([
        //     'latestPayroll',
        //     'user',
        //     'role',
        // ])
        // ->withSum(['combens as total_combens' => function ($query) use ($startDate, $endDate) {
        //     $query->whereBetween('date', [$startDate, $endDate]);
        // }], 'value')
        // ->withSum(['taxes as total_taxes' => function ($query) use ($startDate, $endDate) {
        //     $query->whereBetween('date', [$startDate, $endDate]);
        // }], 'value')
        // ->withSum(['deductions as total_deductions' => function ($query) use ($startDate, $endDate) {
        //     $query->whereBetween('date', [$startDate, $endDate]);
        // }], 'value')
        // ->withSum('packageRenumerations as total_remuneration' , 'value')
        // ->active()->where(function ($query) use ($search) {
        //     if (!empty($search)) {
        //         $query->whereRaw('name ILIKE ?', ['%' . $search . '%']);
        //     }
        // })
        // ->orderBy($sortField, $sortOrder)
        // ->paginate($pageSize);

        // $employeePayrolls->getCollection()->transform(function ($employee) {
        //     $employee->total_take_home_pay =
        //         ($employee->salary_value ?? 0) +
        //         ($employee->total_combens ?? 0) +
        //         ($employee->total_remuneration ?? 0) -
        //         ($employee->total_taxes ?? 0) -
        //         ($employee->total_deductions ?? 0);
        //     return $employee;
        // });
