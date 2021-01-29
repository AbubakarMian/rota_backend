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
    protected $duty_arr=[];
    protected $doctors_arr=[];
    protected $rota_generate_pattern;
    protected $consective_days_allowed = 4;
    protected $doctors_duty_num_initial=[];

    public function __construct($monthly_rota)
    {

        $this->rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota->id)
                                                        ->orderBy('duty_date', 'asc')->get();
        $this->doctors_duty_num_initial = Doctor::select(DB::Raw('0 as duties '),'id')->pluck('duties','id')->toArray();
        $this->shifts = Config::get('constants.duty_type');
        $this->monthly_rota = $monthly_rota;
    }

    public function generate_rota_arr()
    {
        $doctors_arr = $this->get_doctors_rotareq_details();


        $duties_arr = $this->get_duties_info_and_assign_special_requests();
        dd('$doctors_arr');
        $generated_rota = $this->generate_rota($rota_generate_patterns, $doctors_arr, $duties_arr);
    }

    public function get_doctors_rotareq_details()
    {
        $days = $this->monthly_rota->total_days;

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

    public function get_initial_duties_arr(){
        $duty_arr = [
        'duty_date'=>'',
        'total_morning_doctors'=>[],
        'total_evening_doctors'=>[],
        'total_night_doctors'=>[],
        'annual_leaves'=>[],
        'regular_leaves'=>[],
        'all_leaves'=>[],
        'special_rota_morning_doctors_res'=>[],
        'special_rota_morning_doctors_reg'=>[],
        'special_rota_evening_doctors_res'=>[],
        'special_rota_evening_doctors_reg'=>[],
        'special_rota_night_doctors_res'=>[],
        'special_rota_night_doctors_reg'=>[],
        'special_rota_morning_request'=>[],
        'special_rota_evening_doctors'=>[],
        'special_rota_night_doctors'=>[],
        'special_rota_off_doctors'=>[],
        'assigned_morning_doctors_res'=> [],
        'assigned_morning_doctors_reg'=>[],
        'assigned_evening_doctors_res'=>[],
        'assigned_evening_doctors_reg'=>[],
        'assigned_night_doctors_res'=>[],
        'assigned_night_doctors_reg'=>[],
        'consecutive_doctors'=>[], // only one duty of yesterday assigned
        'consecutive_morning_doctors'=>[],
        'consecutive_evening_doctors'=>[],
        'consecutive_night_doctors'=>[],
        'doctors_duty_num_initial'=>$this->doctors_duty_num_initial,
        'qualified_doctors'=>[],
        'qualified_morning_doctors'=>[],
        'qualified_evening_doctors'=>[],
        'qualified_night_doctors'=>[],
        'diss_qualified_doctors'=>[],
        'dis_qualified_consecutive_doctors'=>[],
        ];
        return $duty_arr;
    }

    public function rota_by_date($check_date,$where_doctors=[]){

        $doctors_consective_duties = Rota::where('duty_date', '=', $check_date)
        ->orderBy('duty_date','asc');
        if($where_doctors){
            $doctors_consective_duties = $doctors_consective_duties->whereIn('doctor_id',$where_doctors)->get();
        }

        return $doctors_consective_duties;
    }

    public function get_pre_day_duties_arr($duty_date){
        $pre_date = strtotime('-1 day',$duty_date);
        $consective_days_allowed = $this->consective_days_allowed;

        $day_doctors = [];
        $day_doctors[] = rota_by_date($check_date);



        for($day = 2;$day<=$consective_days_allowed ; $day++){
            $check_date  = strtotime('-'.$day.' day',$duty_date);
            $pre_doctors = array_column($day_doctors[$day-1]->toArray(),'doctor_id');
            $day_doctors[] = rota_by_date($check_date,$pre_doctors);
        }

        for($day = 0;$day<$consective_days_allowed ; $day++){
            foreach($day_doctors as $d){
                $duty_arr['doctors_duty_num_initial'][$d->doctor_id]++;

                // check if $day_doctors last index is equal to first index set this doc to dis_qualified_doc arr
                $last_day_doctors =end($day_doctors);
                $first_day_doctors =current($day_doctors);
                if($last_day_doctors == $first_day_doctors){

              $duty_arr['dis_qualified_consecutive_doctors'][$d->doctor_id];
             }
            }

        }
        $duty_arr = $this->get_initial_duties_arr();
        $dis_qualified_consecutive_doctors = [];
        foreach($doctors_consective_duties_dayone as $dc){
            if($dc->total_duties >= $consective_days_allowed){
                $dis_qualified_consecutive_doctors[] = $dc;
            }
            elseif($dc->total_duties == 1 && $pre_date == $dc->duty_date){
                $this->duty_arr[$duty_date]['consecutive_doctors'][] = $dc->doctor_id;
                if($dc->shift == 'morning'){
                    $this->duty_arr[$duty_date]['consecutive_morning_doctors_arr'][] = $dc;
                }
                elseif($dc->shift == 'evening'){
                    $this->duty_arr[$duty_date]['consecutive_evening_doctors_arr'][] = $dc;
                }
                else{
                    $this->duty_arr[$duty_date]['consecutive_night_doctors_arr'][] = $dc;
                }
            }
            $duty_arr['doctors_duty_num_initial'][$dc->doctor_id]++;
        }
        $this->duties_arr[$pre_date] = $duty_arr;

    }
    public function get_duties_info_and_assign_special_requests()
    {

        // $doctors_consective_duties_arr_initial = Doctor::select(DB::Raw('0 as duties '),'id')->pluck('duties','id')->toArray();
        // $doctors_duty_num_initial = Doctor::select(DB::Raw('0 as duties '),'id')->pluck('duties','id')->toArray();
        // $duty_date = $this->rota_generate_patterns->duty_date;
        // dd('get_duties_info_and_assign_special_requests',$duty_date);
        foreach ($this->rota_generate_patterns as $rota_generate_pattern_key => $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;
            $this->duties_arr[$duty_date] = $this->get_initial_duties_arr();
            if($rota_generate_pattern_key == 0){
                $this->get_pre_day_duties_arr($duty_date);
            }

            $special_rota_request = Special_rota_request::where('duty_date', $duty_date);
            $special_rota_morning_request = $special_rota_request->where('want_duty', 1)->where('shift', 'morning')->get();
            $special_rota_evening_request = $special_rota_request->where('want_duty', 1)->where('shift', 'evening')->get();
            $special_rota_night_request = $special_rota_request->where('want_duty', 1)->where('shift', 'night')->get();
            $special_rota_want_off = $special_rota_request->where('want_off', 1)->pluck('doctor_id')->toArray();
            $annual_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 1)->pluck('id')->toArray();
            $regular_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 0)->pluck('id')->toArray();
            $all_leaves = array_merge($annual_leaves, $regular_leaves, $special_rota_want_off, );
            $all_diss_qualified_doctors = $all_leaves;

            $this->duties_arr[$duty_date]= [
                    'duty_date'=>$duty_date,
                    'total_morning_doctors'=>$rota_generate_pattern->total_morning_doctors,
                    'total_evening_doctors'=>$rota_generate_pattern->total_evening_doctors,
                    'total_night_doctors'=>$rota_generate_pattern->total_night_doctors,
                    'annual_leaves'=>$annual_leaves,
                    'regular_leaves'=>$regular_leaves,
                    'all_leaves'=>$all_leaves,
                    // 'special_rota_morning_doctors_res'=>[],
                    // 'special_rota_morning_doctors_reg'=>[],
                    // 'special_rota_evening_doctors_res'=>[],
                    // 'special_rota_evening_doctors_reg'=>[],
                    // 'special_rota_night_doctors_res'=>[],
                    // 'special_rota_night_doctors_reg'=>[],
                    'special_rota_morning_request'=>$special_rota_morning_request,
                    'special_rota_evening_doctors'=>$special_rota_evening_request,
                    'special_rota_night_doctors'=>$special_rota_night_request,
                    'special_rota_off_doctors'=>$special_rota_want_off,
                    // 'assigned_morning_doctors_res'=> [],
                    // 'assigned_morning_doctors_reg'=>[],
                    // 'assigned_evening_doctors_res'=>[],
                    // 'assigned_evening_doctors_reg'=>[],
                    // 'assigned_night_doctors_res'=>[],
                    // 'assigned_night_doctors_reg'=>[],
                    // 'consecutive_doctors'=>[], // only one duty of yesterday assigned
                    // 'consecutive_morning_doctors'=>[],
                    // 'consecutive_evening_doctors'=>[],
                    // 'consecutive_night_doctors'=>[],
                    // 'doctors_consective_duties_arr'=>$doctors_consective_duties,
                    'doctors_duty_num'=>$doctors_duty_num_initial,
                    // 'qualified_doctors'=>[],
                    // 'qualified_morning_doctors'=>[],
                    // 'qualified_evening_doctors'=>[],
                    // 'qualified_night_doctors'=>[],
                    'diss_qualified_doctors'=>$all_diss_qualified_doctors,
                    // 'dis_qualified_consecutive_doctors'=>[],
                ];
            $this->duties_arr = $duties_arr;
            $this->assign_duties_to_special_request_doctors($duty_date);
            $this->assign_duties_to_consecutive_doctors($duty_date);
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

    public function is_consective_duty_allowed($duty_date,$doctor_id){
        $duty_index = $this->duties_arr [$duty_date]['index'];
        if($duty_index == 0){

        }

    }

    public function assign_doctor($shift,$doctor,$duty_date){

        $duties_shift_type = Config::get('constants.duties_shift_type'.$shift);
        $next_date = strtotime('+1 day',$duty_date);
        $pre_date = strtotime('+1 day',$duty_date);
        $assigned_doctor = $doctor->type == 1 ? 'assigned_doctors_res' : 'assigned_doctors_reg';
        $assigned_doctor_type = $duties_shift_type[$assigned_doctor];
        $this->duties_arr [$duty_date][$assigned_doctor_type][] = $doctor->id;
        $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor->id] =
                        $this->duties_arr [$pre_date]['doctors_duty_num_initial'][$doctor->id]+1;
        $this->duties_arr [$duty_date]['doctors_consective_duties_arr'][$doctor->id]++;
        $this->doctors_arr [$doctor->id]['given']++;
        if($this->duties_arr[$pre_date]['doctors_duty_num_initial'][$doctor->id] == 0){
            $this->duties_arr [$next_date][$duties_shift_type['consecutive_doctors_arr']] = $doctor->id; // morning
            $this->duties_arr [$next_date]['consecutive_doctors'] = $doctor->id;
        }

        if(isset($this->duties_arr [$next_date])){
            if($this->consective_days_allowed >= $this->duties_arr [$pre_date]['doctors_duty_num_initial'][$doctor->id]){
                $this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'] = $doctor->id;
                return false;
            }
            $this->duties_arr [$next_date][$duties_shift_type['doctors_duty_num_initial']][$doctor->id]++;
        }
        return true;
    }

    public function assign_duty($duty_date, $doctor, $shift)
    {
        if(in_array($doctor->id,$this->duties_arr [$duty_date]['diss_qualified_doctors'])){
            return false;
        }
        if ($shift == $this->shifts['morning']) {
            if ($doctor->doctor_type == 1) {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_morning_doctors,
                    $this->duties_arr[$duty_date]['assigned_morning_doctors_reg']
                );
                if ($has_room) {
                    $this->assign_doctor($this->shifts['morning'],$doctor,$duty_date);
                    return true;
                }
            } else {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_morning_doctors,
                    $this->duties_arr[$duty_date]['assigned_morning_doctors_res']
                );

                if ($has_room) {
                    $this->assign_doctor($this->shifts['morning'],$doctor,$duty_date);
                    return true;
                }
            }
        } elseif ($shift == $this->shifts['evening']) {
            if ($special_req->doctor->doctor_type == 1) {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_reg']
                );
                if ($has_room) {
                    $this->assign_doctor($this->shifts['evening'],$doctor,$duty_date);
                    return true;
                }
            } else {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_res']
                );

                if ($has_room) {
                    $this->assign_doctor($this->shifts['evening'],$doctor,$duty_date);
                    return true;
                }
            }
        } elseif ($shift == $this->shifts['night']) {
            if ($special_req->doctor->doctor_type == 1) {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_reg']
                );
                if ($has_room) {
                    $this->assign_doctor($this->shifts['evening'],$doctor,$duty_date);
                    return true;
                }
            } else {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_res']
                );

                if ($has_room) {
                    $this->assign_doctor($this->shifts['evening'],$doctor,$duty_date);
                    return true;
                }
            }
        }

        return false;
    }

    public function assign_duties_to_special_request_doctors($duty_date)
    {
        $special_rota_morning_request = $this->duties_arr[$duty_date]['special_rota_morning_request'];
        $special_rota_evening_request = $this->duties_arr[$duty_date]['special_rota_evening_request'];
        $special_rota_night_request = $this->duties_arr[$duty_date]['special_rota_night_request'];
        if (isset($special_rota_morning_request)) {
            foreach ($special_rota_morning_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor, 'morning');
                if ($special_req->doctor->doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_evening_request)) {
            foreach ($special_rota_evening_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor, 'evening');
                if ($special_req->doctor->doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_night_request)) {
            foreach ($special_rota_night_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor, 'evening');
                if ($special_req->doctor->doctor_type == 1) {
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
            $this->assign_duty($duty_date, $doctor, 'night');
        }
        // evening consecutive doctors can be adjusted in night shift
        foreach ($consecutive_evening_doctors as $d) {
            $doctor_added = $this->assign_duty($duty_date, $doctor, 'evening');
            if(!$doctor_added){
                $this->assign_duty($duty_date, $doctor, 'night');
            }
        }
        // morning consecutive doctors can be adjusted in evening and night shift both
        foreach ($consecutive_morning_doctors as $d) {
            $doctor_added = $this->assign_duty($duty_date, $doctor, 'morning');
            if(!$doctor_added){
                $this->assign_duty($duty_date, $doctor, 'evening');
            }
            elseif(!$doctor_added){
                $this->assign_duty($duty_date, $doctor, 'night');
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
                    return $this->select_doctor_general_rota( $duty_date, $shift='evening');
                }
            } elseif ($shift == 'evening') {
                if ($duties_arr[$duty_date]['total_evening_doctors'] == sizeof($duties_arr[$duty_date]['assigned_evening_doctors_res'])) {
                    return $this->select_doctor_general_rota( $duty_date, $shift='night');
                }
            } else {
                return ;
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
                // if(check if it has room){  add 'duties_assigned_dates'=>[] fill disqualified doctors in assign doctor
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
