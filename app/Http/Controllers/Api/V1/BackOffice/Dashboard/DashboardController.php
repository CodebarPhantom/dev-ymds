<?php
namespace App\Http\Controllers\Api\V1\BackOffice\Dashboard;

use App\Http\Controllers\MasterController;
use App\Services\Dashboard\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends MasterController
{
    protected $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        parent::__construct();
        $this->calendarService = $calendarService;
    }

    public function attendanceMonth(Request $request)
    {


        $func = function () use ($request) {

            $calendarsData = $this->calendarService->getByAttendanceEmployeeMonth(Auth::user()->employee_id, $request->month,$request->year);
            $calendars = $calendarsData->map(function($calendarData) {
                return [
                    'start_date' => $calendarData->date,
                    'end_date' => $calendarData->date,
                    'note'=> '|'.$calendarData->shift->formatted_start_time . ' - ' .$calendarData->shift->formatted_end_time . '| '.' |'.$calendarData->status_label.'| |'.$calendarData->shift->name.'|',
                    'color'=> $calendarData->status_color
                ];
            });

            $this->data = compact('calendars');

        };

        return $this->callFunction($func);
    }
}
