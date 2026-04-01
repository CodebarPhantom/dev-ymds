<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EmployeeAttendanceExport implements FromView
{
    protected $data;
    protected $daysInMonth;
    protected $period;


    public function __construct($data, $daysInMonth,$period)
    {
        $this->data = $data;
        $this->daysInMonth = $daysInMonth;
        $this->period = $period;
    }

    public function view(): View
    {
        return view('exports.employee-attendance', [
            'reports' => $this->data,
            'daysInMonth' => $this->daysInMonth,
            'period' =>  $this->period
        ]);
    }
}
