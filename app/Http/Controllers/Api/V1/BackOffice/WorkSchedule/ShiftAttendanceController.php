<?php

namespace App\Http\Controllers\Api\V1\BackOffice\WorkSchedule;

use App\Enums\ShiftAttendanceStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Services\WorkSchedule\ShiftAttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Workforce\EmployeeService;

class ShiftAttendanceController extends MasterController
{
    protected $shiftAttendanceService;
    protected $dateNow;
    protected $employeeId;
    protected $employeeName;
    protected $companyService;
    protected $employeeService;

    public function __construct(
        ShiftAttendanceService $shiftAttendanceService,
        CompanyService $companyService,
        EmployeeService $employeeService
    ) {
        parent::__construct();
        $this->shiftAttendanceService = $shiftAttendanceService;
        $this->dateNow = Carbon::now()->format('Y-m-d');
        $this->employeeId = Auth::user()->employee_id;
        $this->employeeName = Auth::user()->employee->name;
        $this->companyService = $companyService;
        $this->employeeService = $employeeService;
    }


    public function bypassAttendance(Request $request)
    {
        $func = function () use ($request) {

            // $data = $request->validate([
            //     'image' => 'required|string',
            //     'latitude' => 'required|numeric',
            //     'longitude' => 'required|numeric',
            //     'closest_company' => 'required|array'
            // ]);

            // // Extract the base64 data
            // $base64Image = $data['image'];
            // $imageData = explode(',', $base64Image);
            // if (count($imageData) === 2) {
            //     $imageContent = base64_decode($imageData[1]);
            // } else {
            //     return response()->json(['error' => 'Invalid image format'], 400);
            // }

            $employeeId = $request->employee_id;
            $bypassDate = $request->bypass_date;


            $timestamp = Carbon::parse($request->bypass_time);

            Log::info([$employeeId,$bypassDate, $timestamp]);

            // Check the attendance status
            $attendanceStatus = $this->shiftAttendanceService->getShiftAttendanceByDateEmployeeId(
                $bypassDate,
                $employeeId
            );

            // Log::info($attendanceStatus->date);
            // dd($attendanceStatus);

            $shiftStartTime = Carbon::createFromTimeString($attendanceStatus['shift']['start_time']);
            if ($attendanceStatus['shift']['is_night_shift']) {
                if ($timestamp->copy()->format('Y-m-d') > $attendanceStatus->date) {
                    $shiftEndTime = Carbon::createFromTimeString($attendanceStatus['shift']['end_time']);

                } else {
                    $shiftEndTime = Carbon::createFromTimeString($attendanceStatus['shift']['end_time'])->addDay();
                }

            } else {
                //$shiftEndTime = Carbon::createFromTimeString($attendanceStatus['shift']['end_time']);
                //Log::debug($attendanceStatus->date);
                $shiftEndTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$attendanceStatus->date} {$attendanceStatus['shift']['end_time']}");

            }

            $employeeClockIn = $attendanceStatus->clock_in;
            $employeeClockOut = $attendanceStatus->clock_out;


            $floorDifference = null;
            $boolLateOrEarly = false;
            $fileType = '';
            $updateKey = '';
            $isComplete = false;


            if (is_null($employeeClockIn)) {
                $fileType = 'clock_in_';
                $updateKey = 'clock_in';
                $latitude = 'lat_clock_in';
                $longitude = 'long_clock_in';
                $imagePath = 'img_clock_in';
                $status = ShiftAttendanceStatus::MASUK->value;
                $diffSecondKey = 'late_clock_in_time';
                $checkLateOrEarly = 'is_late';
                $distanceClockInOut = 'distance_clock_in';

                if ($timestamp->greaterThan($shiftStartTime)) {
                    $difference = (int) $shiftStartTime->diffInSeconds($timestamp);
                    $floorDifference = floor($difference);
                    $boolLateOrEarly = true;
                }

            } elseif (!is_null($employeeClockIn) && is_null($employeeClockOut)) {
                $fileType = 'clock_out_';
                $updateKey = 'clock_out';
                $latitude = 'lat_clock_out';
                $longitude = 'long_clock_out';
                $imagePath = 'img_clock_out';
                $status = ShiftAttendanceStatus::MASUK->value;
                $diffSecondKey = 'early_clock_out_time';
                $checkLateOrEarly = 'is_early_clock_out';
                $distanceClockInOut = 'distance_clock_out';
                if ($timestamp->lessThan($shiftEndTime)) {
                    $difference = (int) $timestamp->diffInSeconds($shiftEndTime);
                    $floorDifference = floor($difference);
                    $boolLateOrEarly = true;
                }

                //Log::debug([$timestamp,$shiftEndTime]);
                //dd('test');
            } else {
                $this->error = true;
                $this->messages = ['Attendance already completed'];
                $isComplete = true;
            }

            // Generate file name
            // $fileName = $fileType . Str::random(10) . '.jpg';
            // $filePath = 'attendance/' . Str::slug($this->employeeName) . '/' . $attendanceStatus->date . '/' . $fileName;

            // // Store the image
            // Storage::disk('uploads')->put($filePath, $imageContent);

            // Prepare attendance data

            if(!$isComplete)
            {
                $attendanceData = [
                    'date' => $attendanceStatus->date,
                    'employee_id' => $employeeId,
                    //$latitude => $data['latitude'],
                    //$longitude => $data['longitude'],
                    $updateKey => $timestamp, // Adds clock_in or clock_out timestamp
                    //$imagePath => 'uploads/'.$filePath,
                    $diffSecondKey =>  $floorDifference,
                    $checkLateOrEarly => $boolLateOrEarly,
                    //$distanceClockInOut => ($data['closest_company']['distance']*1000),
                    'status' => $status
                ];

                // Call the service to update attendance
                $attendance = $this->shiftAttendanceService->updateAttendanceByDateEmployeeId(
                    $attendanceData['date'],
                    $attendanceData['employee_id'],
                    $attendanceData
                );
                $this->data = compact('attendance');

            }



        };

        return $this->callFunction($func);
    }
}
