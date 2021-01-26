<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Log;
use Response;
use stdClass;
use Illuminate\Http\Request;
use App\models\Rota_Generate_Pattern;
use App\models\Special_rota_request;
use App\models\General_rota_request;
use App\models\Monthly_rota;
use App\models\Leave_Request;
use App\models\Doctor;
use App\models\Rota;
use App\models\Rota_Request;
use App\models\Doctor_type;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class GenerateRota
{
    protected $rota_generate_patterns;
    protected $shifts;
    protected $monthly_rota;
    protected $doctors_arr;
    protected $rota_generate_pattern;

    public function __constructor($monthly_rota)
    {
        $this->rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota_id)
                                                        ->orderBy('duty_date', 'asc')->get();
        $this->shifts = Config::get('constants.duty_type');
        $this->monthly_rota = $monthly_rota;
    }

    public function generate_rota_arr()
    {
        $doctors_arr = $this->get_doctors_rotareq_details();
        
        $duties_arr = $this->get_duties_info_and_assign_special_requests();

        $generated_rota = $this->generate_rota($rota_generate_patterns, $doctors_arr, $duties_arr);
    }

    public function get_doctors_rotareq_details()
    {
        $days = $this->monthly_rota->total_days;
        $monthly_rota_id = $this->monthly_rota->id;
        $rota_generate_patterns = $this->rota_generate_patterns;

        $doctors = Doctor::get();
        $doctors_arr = [];
        foreach ($doctors as $d) {
            $req_duties_mor = General_rota_request::where('doctor_id', $d->id)->where('shift', $this->shifts['morning']);
            $req_duties_eve = General_rota_request::where('doctor_id', $d->id)->where('shift', $this->shifts['evening']);
            $req_duties_night = General_rota_request::where('doctor_id', $d->id)->where('shift', $this->shifts['night']);
            $doctors_arr[$d->id] = [
            'doctor_id'=>$d->id,
            'doctor'=>$d,
            'doctor_type'=>$d->type,
            'total_duties'=>$d->total_duties,
            'extra'=>0,
            'given_morning'=>0,
            'given_evening'=>0,
            'given_night'=>0,
            'req_morning'=>$req_duties_mor,
            'req_evening'=>$req_duties_eve,
            'req_night'=>$req_duties_night,
            'total_leaves'=>($days - $d->total_duties),
            'duties_assigned_dates  '=>[],
            'assigned_leaves'=>0,
            'holiday_leaves'=>0 // if he already got sat sun off
            ];
        }
        $this->doctors_arr;
    }
    public function get_duties_info_and_assign_special_requests()
    {
        // $duties_arr = [];
        foreach ($this->rota_generate_patterns as $rota_generate_pattern_key => $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;
            $consecutive_doctors_arr = [];
            if ($rota_generate_pattern_key == 0) {
                $consecutive_doctors = Doctor::join('rota', function ($j) use ($duty_date) {
                    return $j->where('doctor.id', 'rota.doctor_id')
                            ->where('duty_date', '!=', strtotime('-2 day', $duty_date));
                })->with('doctor')->where('duty_date', strtotime('-1 day', $duty_date));

                $dis_qualified_consecutive_doctors = Doctor::join('rota', function ($j) use ($duty_date) {
                    return $j->where('doctor.id', 'rota.doctor_id')
                            ->where('duty_date', '<=', strtotime('-4 day', $duty_date));
                })->with('doctor')->where('duty_date', '>=', strtotime('-1 day', $duty_date))->get()->toArray();
        
                $consecutive_morning_doctors_arr = $consecutive_doctors->get()->toArray();
                $consecutive_evening_doctors_arr = $consecutive_doctors->get()->toArray();
                $consecutive_night_doctors_arr = $consecutive_doctors->get()->toArray();
                $consecutive_doctors_arr = array_merge(
                    $consecutive_morning_doctors_arr,
                    $consecutive_evening_doctors_arr,
                    $consecutive_night_doctors_arr
                );
            }
            $duty_date = $rota_generate_pattern->duty_date;
            $special_rota_request = Special_rota_request::where('duty_date', $duty_date);
            $special_rota_morning_request = $special_rota_request->where('want_duty', 1)->where('shift', 'morning')->get();
            $special_rota_evening_request = $special_rota_request->where('want_duty', 1)->where('shift', 'evening')->get();
            $special_rota_night_request = $special_rota_request->where('want_duty', 1)->where('shift', 'night')->get();
            $special_rota_want_off = $special_rota_request->where('want_off', 1)->pluck('doctor_id')->toArray();
            $annual_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 1)->pluck('id')->toArray();
            $regular_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 0)->pluck('id')->toArray();
            $all_leaves = array_merge($annual_leaves, $regular_leaves, $special_rota_want_off, $dis_qualified_consecutive_doctors);
            $all_diss_qualified_doctors = $all_leaves;
            $this->duties_arr [$duty_date]= [
                    'index'=>$rota_generate_pattern_key,
                    'doctors_consective_duties_num'=>[],
                    'duty_date'=>$duty_date,
                    'total_morning_doctors'=>$rota_generate_pattern->total_morning_doctors,
                    'total_evening_doctors'=>$rota_generate_pattern->total_evening_doctors,
                    'total_night_doctors'=>$rota_generate_pattern->total_night_doctors,
                    'annual_leave'=>$annual_leaves,
                    'regular_leaves'=>$regular_leaves,
                    'all_leaves'=>$all_leaves,
                    'special_rota_morning_doctors_res'=>[],
                    'special_rota_morning_doctors_reg'=>[],
                    'special_rota_evening_doctors_res'=>[],
                    'special_rota_evening_doctors_reg'=>[],
                    'special_rota_night_doctors_res'=>[],
                    'special_rota_night_doctors_reg'=>[],
                    'special_rota_morning_request'=>$special_rota_morning_request,
                    'special_rota_evening_doctors'=>$special_rota_evening_request,
                    'special_rota_night_doctors'=>$special_rota_night_request,
                    'special_rota_off_doctors'=>$special_rota_want_off,
                    'assigned_morning_doctors_res'=> [],
                    'assigned_morning_doctors_reg'=>[],
                    'assigned_evening_doctors_res'=>[],
                    'assigned_evening_doctors_reg'=>[],
                    'assigned_night_doctors_res'=>[],
                    'assigned_night_doctors_reg'=>[],
                    'consecutive_doctors'=>$consecutive_doctors_arr, // only one duty of yesterday assigned
                    'consecutive_morning_doctors'=>$consecutive_morning_doctors_arr,
                    'consecutive_evening_doctors'=>$consecutive_evening_doctors_arr,
                    'consecutive_night_doctors'=>$consecutive_night_doctors_arr,
                    'qualified_doctors'=>[],
                    'qualified_morning_doctors'=>[],
                    'qualified_evening_doctors'=>[],
                    'qualified_night_doctors'=>[],
                    'diss_qualified_doctors'=>$all_diss_qualified_doctors,
                    'dis_qualified_consecutive_doctors'=>$dis_qualified_consecutive_doctors,
                ];
            $this->duties_arr = $this->assign_duties_to_special_request_doctors($duty_date);
            $this->duties_arr = $this->assign_duties_to_consecutive_doctors($duty_date);
        }
    }

    public function has_room_for_doctors($total_doctors, $dependent_arr)
    {
        $room_for_doctors = $total_doctors;
        if (sizeof($dependent_arr)) {
            $room_for_doctors = $room_for_doctors - sizeof($dependent_arr);
        } else {
            $room_for_doctors = $room_for_doctors - 1;
        }
        return $room_for_doctors;
    }

    public function assign_duty($duty_date, $doctor, $shift)
    {
        if ($shift == $this->shifts['morning']) {
            if ($doctor->doctor_type == 1) {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_morning_doctors,
                    $this->duties_arr[$duty_date]['assigned_morning_doctors_reg']
                );
                if ($has_room) {
                    $this->duties_arr [$duty_date]['assigned_morning_doctors_res'][] = $doctor->id;
                }
            } else {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_morning_doctors,
                    $this->duties_arr[$duty_date]['assigned_morning_doctors_res']
                );
    
                if ($has_room) {
                    $this->duties_arr [$duty_date]['assigned_morning_doctors_reg'][] = $doctor->id;
                }
            }
        } elseif ($shift == $this->shifts['evening']) {
            if ($special_req->doctor->doctor_type == 1) {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_reg']
                );
                if ($has_room) {
                    $this->duties_arr [$duty_date]['assigned_evening_doctors_res'][] = $special_req->doctor_id;
                }
            } else {
                $this->duties_arr [$duty_date]['special_rota_evening_doctors_reg'][] = $special_req->doctor_id;
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_res']
                );
    
                if ($has_room) {
                    $this->duties_arr [$duty_date]['assigned_evening_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }

        return $doctor->doctor_type;
    }

    public function assign_duties_to_special_request_doctors($duty_date)
    {
        $special_rota_morning_request = $this->duties_arr[$duty_date]['special_rota_morning_request'];
        $special_rota_evening_request = $this->duties_arr[$duty_date]['special_rota_evening_request'];
        $special_rota_night_request = $this->duties_arr[$duty_date]['special_rota_night_request'];
        if (isset($special_rota_morning_request)) {
            foreach ($special_rota_morning_request as $special_req) {
                $doctor_type = $this->assign_duty($duty_date, $special_req->doctor, 'morning');
                if ($doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_evening_request)) {
            foreach ($special_rota_evening_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor, 'evening');
                if ($doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_night_request)) {
            foreach ($special_rota_night_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor, 'evening');
                if ($doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_night_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_night_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
    }
   
    public function assign_duties_to_consecutive_doctors($duty_date)
    {
        $consecutive_night_doctors = $this->duties_arr[$duty_date]['consecutive_night_doctors_arr'];
        $consecutive_evening_doctors = $this->duties_arr[$duty_date]['consecutive_evening_doctors_arr'];
        $consecutive_morning_doctors = $this->duties_arr[$duty_date]['consecutive_morning_doctors_arr'];
        
        foreach ($consecutive_night_doctors as $d) {
            if ($d['type']==1 && $this->has_room_for_doctors(
                $this->rota_generate_pattern->total_night_doctors,
                $this->duties_arr[$duty_date]['assigned_night_doctors_reg']
            )) {
                $this->duties_arr['assigned_night_doctors_res'][] = $d['id'];
            } elseif ($d['type']==2 && has_room_for_doctors(
                $this->rota_generate_pattern->total_night_doctors,
                $duties_arr[$duty_date]['assigned_night_doctors_res']
            )) {
                $this->duties_arr['assigned_night_doctors_reg'][] = $d['id'];
            }
        }
        // evening consecutive doctors can be adjusted in night shift
        foreach ($consecutive_evening_doctors as $d) {
            if ($d['type']==1) {
                if ($this->has_room_for_doctors(
                    $this->rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_reg']
                )) {
                    $this->duties_arr['assigned_evening_doctors_res'][] = $d['id'];
                } elseif ($this->has_room_for_doctors(
                    $rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_reg']
                )) {
                    $this->duties_arr['assigned_night_doctors_res'][] = $d['id'];
                }
            } elseif ($d['type']==2) {
                if ($this->has_room_for_doctors(
                    $rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_res']
                )) {
                    $duties_arr['assigned_evening_doctors_reg'][] = $d['id'];
                } elseif ($this->has_room_for_doctors(
                    $rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_res']
                )) {
                    $this->duties_arr['assigned_night_doctors_reg'][] = $d['id'];
                }
            }
        }
        // morning consecutive doctors can be adjusted in evening and night shift both
        foreach ($consecutive_morning_doctors as $d) {
            if ($d['type']==1) {
                if ($this->has_room_for_doctors(
                    $this->rota_generate_pattern->total_morning_doctors,
                    $this->duties_arr[$duty_date]['assigned_morning_doctors_reg']
                )) {
                    $this->duties_arr['assigned_morning_doctors_res'][] = $d['id'];
                } elseif ($this->has_room_for_doctors(
                    $this->rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_reg']
                )) {
                    $this->duties_arr['assigned_morning_doctors_res'][] = $d['id'];
                } elseif ($this->has_room_for_doctors(
                    $this->rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_reg']
                )) {
                    $this->duties_arr['assigned_morning_doctors_res'][] = $d['id'];
                }
            } elseif ($d['type']==2) {
                if (has_room_for_doctors(
                    $this->rota_generate_pattern->total_moring_doctors,
                    $duties_arr[$duty_date]['assigned_moring_doctors_res']
                )) {
                    $this->duties_arr['assigned_moring_doctors_reg'][] = $d['id'];
                } elseif (has_room_for_doctors(
                    $this->rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_res']
                )) {
                    $this->duties_arr['assigned_evening_doctors_reg'][] = $d['id'];
                } elseif (has_room_for_doctors(
                    $this->rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_res']
                )) {
                    $this->duties_arr['assigned_night_doctors_reg'][] = $d['id'];
                }
            }
        }
    }

    
    public function generate_rota()
    {
        $find_doctors_level_index = 1;
             
        foreach ($this->rota_generate_patterns as $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;

            $doctors_index = 0;
            [$doctors_arr,$doctors] = $this->get_suitable_doctors(
                $doctors_arr,
                $duties_arr,
                $duty_date,
                $find_doctors_level[$find_doctors_level_index]
            );
            $rota_generate_pattern_total_morning_doctors = $rota_generate_pattern->total_morning_doctors+$rota_generate_pattern->has_morning_ucc;
            for ($i=0;$i<$rota_generate_pattern_total_morning_doctors;$i++) {
                $is_ucc = 0;
                if ($i==0 && $rota_generate_pattern->has_morning_ucc) {
                    $is_ucc = 1;
                }

                while (!isset($doctors[$doctors_index])) {
                    $select_doctor = 0;
                    $find_doctors_level_index = $find_doctors_level_index + 1;
                    [$doctors_arr,$doctors] = $this->get_suitable_doctors(
                        $doctors_arr,
                        $duties_arr,
                        $duty_date,
                        $shifts['morning'],
                        $find_doctors_level[$find_doctors_level_index]
                    );
                }
                $data[] =
                [
                    'duty_date' => $duty_date,
                    'monthly_rota_id' => $id,
                    'is_ucc' => $is_ucc,
                    'shift' => $shifts['morning'],
                    'doctor_id' => $doctor->id
                ];
            }
        }
    }
    
    public function get_suitable_doctors($duty_date, $level)
    {
        $doctor = $this->select_doctor_general_rota($duty_date, $shift);
        if ($doctor) {
            return $doctor;
        }
        $doctor = $this->select_doctor_rota($doctors_id, $duty_date, $shift);

        return $doct;
    }
    
    public function select_doctor_general_rota($duty_date, $shift='morning')
    { // to be continued working
        foreach ($doctors_arr as $doctor) {
            if ($shift == 'morning') {
                if ($duties_arr[$duty_date]['total_morning_doctors'] == sizeof($duties_arr[$duty_date]['assigned_morning_doctors_res'])) {
                    return $this->select_doctor_general_rota($doctors_arr, $duties_arr, $duty_date, $shift='evening');
                }
            } elseif ($shift == 'evening') {
                if ($duties_arr[$duty_date]['total_evening_doctors'] == sizeof($duties_arr[$duty_date]['assigned_evening_doctors_res'])) {
                    return $this->select_doctor_general_rota($doctors_arr, $duties_arr, $duty_date, $shift='night');
                }
            } else {
                return [$doctors_arr, $duties_arr];
            }
        
            if (in_array($doctor->id, $duties_arr[$duty_date]['diss_qualified_doctors'])) {
                continue;
            }
            if (in_array($doctor->id, $duties_arr[$duty_date]['dis_qualified_consecutive_doctors'])) {
                continue;
            }
            if (in_array($doctor->id, $duties_arr[$duty_date]['all_assigned_doctors'])) {
                continue;
            }
            if ($shift == 'morning') {
                // if(check if it has room){  add 'duties_assigned_dates'=>[] fill disqualified doctors
                if ($doctor['req_morning']<$doctor['given_morning']) {
                    $doctor['given_morning'] = $doctor['given_morning'] + 1;
                    $doctor['given_morning'] = $doctor['given_morning'] + 1;
                }
                // }
            } elseif ($shift == 'evening') {
                if ($doctor['req_evening']<$doctor['given_evening']) {
                    $doctor['given_evening'] = $doctor['given_evening'] + 1;
                }
            } else {
                if ($doctor['req_night']<$doctor['given_night']) {
                    $doctor['given_night'] = $doctor['given_evening'] + 1;
                }
            }
        }
    }

    public function select_doctor_rota($doctors_id, $duty_date, $shift)
    {
    }
}
