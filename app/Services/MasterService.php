<?php

namespace App\Services;

//use App\Services\AppLogService;
use App\Services\Base\BaseNotificationService;
use App\Services\WorkSchedule\ShiftAttendanceService;


class MasterService
{
    //protected $appLogService;
    protected $baseNotificationService;
    protected $shiftAttendanceService;


    public function __construct(
        //AppLogService $appLogService,
        BaseNotificationService $baseNotificationService,
        ShiftAttendanceService $shiftAttendanceService
        )
    {
        //$this->appLogService = $appLogService;
        $this->baseNotificationService = $baseNotificationService;
        $this->shiftAttendanceService = $shiftAttendanceService;
    }
}
