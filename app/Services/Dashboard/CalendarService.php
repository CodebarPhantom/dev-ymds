<?php

namespace App\Services\Dashboard;

use App\Models\WorkSchedule\ShiftAttendance;
use App\Services\MasterService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendarService extends MasterService
{

    public function getByAttendanceEmployeeMonth(int $id, $month, $year)
    {
        $startDate = Carbon::parse($year.'-'.$month.'-'.'01')->startOfDay();
        $endDate =  $startDate->copy()->endOfMonth()->addDays(11)->endOfDay();
        $test = ShiftAttendance::where('employee_id',$id)->where('date','>=', $startDate)->where('date','<=',$endDate)->get();
        return $test;
    }
}
