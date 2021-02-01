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
    protected $level='';

    public function __construct($monthly_rota)
    {
        $this->rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota->id)
                                                        ->orderBy('duty_date', 'asc')->get();
        $this->doctors_duty_num_initial = Doctor::select(DB::Raw('0 as duties '), 'id')->pluck('duties', 'id')->toArray();
        $this->shifts = Config::get('constants.duty_type');
        $this->monthly_rota = $monthly_rota;
    }

    public function generate_rota_arr()
    {
        $doctors_arr = $this->get_doctors_rotareq_details();


        $duties_arr = $this->get_duties_info_and_assign_special_requests();


        foreach ($this->rota_generate_patterns as $rota_generate_pattern_key => $rota_generate_pattern) {

            $this->assign_general_doctor_duties($duty_date);


        }


        // check if all morning,eve,night req and given are full then dont call normal doct duties

        //    $doctors_arr = $this->duty_arr[$duty_date]['dis_qualified_consecutive_doctors'] = $last_day_doctors;



        // assign normal doctor duties
        $duties_arr = $this->assign_doctor_duties();

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
            'given_general'=>0,
            'req_morning'=>$req_duties_mor,
            'req_evening'=>$req_duties_eve,
            'req_night'=>$req_duties_night,
            'req_general'=>($d->total_duties - ($req_duties_mor+$req_duties_eve+$req_duties_night)),
            'total_leaves'=>($days - $d->total_duties),
            'duties_assigned_dates  '=>[],
            'assigned_leaves'=>0,
            'holiday_leaves'=>0 // if he already got sat sun off
            ];
        }
        $this->doctors_arr;
    }


    public function assign_general_doctor_duties($duty_date)
    {
        // if doctor is already give duty on that day

        // foreach($this->doctors_duty_num_initial as $doctors_id => $doctors_duty_num )

        $pre_date = strtotime('-1 day', $duty_date);

        // if ($shift == $this->shifts['morning']) {
            $previous_doctors = array_merge(
                $this->duty_arr[$pre_date]['assigned_morning_doctors_res'],
                $this->duty_arr[$pre_date]['assigned_morning_doctors_reg'],
            );

            foreach ($previous_doctors as $pre_doctor) {
                if ($this->doctors_arr[$pre_doctor]['given_morning']<$this->doctors_arr[$pre_doctor]['req_morning']) {
                    $this->assign_duty($pre_doctor, $duty_date, $shift);
                }
            }
        // }
        // elseif ($shift == $this->shifts['evening']) {
            $previous_doctors = array_merge(
                $this->duty_arr[$pre_date]['assigned_evening_doctors_res'],
                $this->duty_arr[$pre_date]['assigned_evening_doctors_reg'],
            );

            foreach ($previous_doctors as $pre_doctor) {
                if ($this->doctors_arr[$pre_doctor]['given_evening']<$this->doctors_arr[$pre_doctor]['req_evening']) {
                    $this->assign_duty($pre_doctor, $duty_date, $shift);
                }
                if ($this->doctors_arr[$pre_doctor]['given_evening']<$this->doctors_arr[$pre_doctor]['req_evening']) {
                }
            }
        // }
        // else { //if ($shift == $this->shifts['night'])
            $previous_doctors = array_merge(
                $this->duty_arr[$pre_date]['assigned_night_doctors_res'],
                $this->duty_arr[$pre_date]['assigned_night_doctors_reg'],
            );

            foreach ($previous_doctors as $pre_doctor) {
                if ($this->doctors_arr[$pre_doctor]['given_night']<$this->doctors_arr[$pre_doctor]['req_night']) {
                    $this->assign_duty($pre_doctor, $duty_date, $shift);
                }
                if ($this->doctors_arr[$pre_doctor]['given_night']<$this->doctors_arr[$pre_doctor]['req_night']) {
                }
            }
        // }

        return false;
    }

    public function assign_doctor_duties()
    {
    }

    public function get_initial_duties_arr()
    {
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
        'assigned_doctors'=>[],
        'consecutive_doctors'=>[], // only one duty of yesterday assigned
        'consecutive_morning_doctors'=>[],
        'consecutive_evening_doctors'=>[],
        'consecutive_night_doctors'=>[],
        'doctors_duty_num_initial'=>$this->doctors_duty_num_initial,
        'disqualified_morning_doctors'=>[],
        'disqualified_evening_doctors'=>[],
        'disqualified_night_doctors'=>[],
        'dis_qualified_consecutive_doctors'=>[],
        'disqualified_doctors'=>[],
        'qualified_doctors'=>[],
        'check_general_request'=>true,
        ];
        return $duty_arr;
    }

    // get only those doctors which exist in pre day
    public function rota_by_date($check_date, $where_doctors=[])
    {
        $doctors_consective_duties = Rota::where('duty_date', '=', $check_date)
        ->orderBy('duty_date', 'desc');
        if ($where_doctors) {
            $doctors_consective_duties = $doctors_consective_duties->whereIn('doctor_id', $where_doctors)->get();
        }

        return $doctors_consective_duties;
    }

    public function get_pre_day_duties_arr($duty_date)
    {
        $pre_date = strtotime('-1 day', $duty_date);
        $consective_days_allowed = $this->consective_days_allowed;

        $day_doctors = [];
        $day_doctors[] = rota_by_date($pre_date);

        for ($day = 2;$day<=$consective_days_allowed ; $day++) {
            $check_date  = strtotime('-'.$day.' day', $pre_date);
            $pre_doctors = array_column($day_doctors[$day-1]->toArray(), 'doctor_id');
            $day_doctors[] = rota_by_date($check_date, $pre_doctors);   // get only those doctors whic exist in pre day
        }
        $last_day_doctors = end($day_doctors);
        $this->duty_arr[$duty_date]['dis_qualified_consecutive_doctors'] = $last_day_doctors;

        for ($day = 0;$day<$consective_days_allowed ; $day++) {
            foreach ($day_doctors as $d) {
                $this->duty_arr[$pre_date]['doctors_duty_num_initial'][$d->doctor_id]++;
            }
        }

        $consecutive_doctors = array_diff($day_doctors[0], $day_doctors[1]);
        $this->duty_arr[$duty_date]['consecutive_doctors'] = $consecutive_doctors;

        foreach ($day_doctors as $day_doctor) {
            if ($day_doctor->duty_date == $pre_date && in_array($day_doctor->doctor_id, $consecutive_doctors)) {
                if ($day_doctor->shift == $this->shift['morning']) {
                    $this->duty_arr[$duty_date]['consecutive_morning_doctors_arr'] = $day_doctor->doctor_id;
                } elseif ($day_doctor->shift == $this->shift['evening']) {
                    $this->duty_arr[$duty_date]['consecutive_evening_doctors_arr'] = $day_doctor->doctor_id;
                    $this->duty_arr[$duty_date]['disqualified_morning_doctors'] = $day_doctor->doctor_id;
                } else {
                    $this->duty_arr[$duty_date]['consecutive_night_doctors_arr'] = $day_doctor->doctor_id;
                    $this->duty_arr[$duty_date]['disqualified_morning_doctors'] = $day_doctor->doctor_id;
                    $this->duty_arr[$duty_date]['disqualified_evening_doctors'] = $day_doctor->doctor_id;
                }
            }
        }
    }
    public function get_duties_info_and_assign_special_requests()
    {
        foreach ($this->rota_generate_patterns as $rota_generate_pattern_key => $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;
            $this->duties_arr[$duty_date] = $this->get_initial_duties_arr();
            if ($rota_generate_pattern_key == 0) {
                $pre_date = strtotime('-1 day', $duty_date);
                $this->duty_arr[$pre_date] = $this->get_initial_duties_arr();
                $this->get_pre_day_duties_arr($duty_date);
            }

            $special_rota_request = Special_rota_request::where('duty_date', $duty_date);
            $special_rota_morning_request = $special_rota_request->where('want_duty', 1)->where('shift', 'morning')->get();
            $special_rota_evening_request = $special_rota_request->where('want_duty', 1)->where('shift', 'evening')->get();
            $special_rota_night_request = $special_rota_request->where('want_duty', 1)->where('shift', 'night')->get();
            $special_rota_want_off = $special_rota_request->where('want_off', 1)->pluck('doctor_id')->toArray();
            $annual_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 1)->pluck('id')->toArray();
            $regular_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 0)->pluck('id')->toArray();
            $all_leaves = array_merge($annual_leaves, $regular_leaves, $special_rota_want_off);

            $this->duties_arr[$duty_date]= [
                    'duty_date'=>$duty_date,
                    'total_morning_doctors'=>$rota_generate_pattern->total_morning_doctors,
                    'total_evening_doctors'=>$rota_generate_pattern->total_evening_doctors,
                    'total_night_doctors'=>$rota_generate_pattern->total_night_doctors,
                    'annual_leaves'=>$annual_leaves,
                    'regular_leaves'=>$regular_leaves,
                    'all_leaves'=>$all_leaves,
                    'special_rota_morning_request'=>$special_rota_morning_request,
                    'special_rota_evening_doctors'=>$special_rota_evening_request,
                    'special_rota_night_doctors'=>$special_rota_night_request,
                    'special_rota_off_doctors'=>$special_rota_want_off,
                ];
            $this->duties_arr = $duties_arr;
            $this->assign_duties_to_special_request_doctors($duty_date);
        }
    }

    public function has_room_for_doctors($duty_date, $doctor_id,$shift)
    {
        $rota_generate_pattern = $this->rota_generate_patterns->where('duty_date', $duty_date)->first();
        $has_ucc = $rota_generate_pattern->has_ucc;
        $min_res = $has_ucc + 1;
        $min_reg = 1;
        $duties_shift_type = Config::get('constants.duties_shift_type'.$shift);
        if ($this->doctor_arr[$doctor_id]->type == 1) {
            if($min_reg <= (sizeof($this->duties_arr[$duty_date][$duties_shift_type['assigned_doctors_reg']]))){
                return true;
            }
            $room_for_doctors = $room_for_doctors - sizeof($dependent_arr);
        }else {// ($this->doctor_arr[$doctor_id]->type == 1)
            if($min_res <= (sizeof($this->duties_arr[$duty_date][$duties_shift_type['assigned_doctors_res']]))){
                return true;
            }
        }
        return false;
    }

    public function is_consective_duty_allowed($duty_date, $doctor_id)
    {
        $duty_index = $this->duties_arr [$duty_date]['index'];
        if ($duty_index == 0) {
        }
    }

    public function chage_duty_arr_conditions($duty_date, $level_conditions)
    {
        $level_conditions = $this->level;

        foreach ($level_conditions as $level_condition) {
            if (!$level_condition['annual_leave']) {
                $this->duty_arr[$duty_date]['annual_leave'] = [];
            }
            if (!$level_condition['regular_leaves']) {
                $this->duty_arr[$duty_date]['regular_leaves'] = [];
            }
            if (!$level_condition['special_off_requests']) {
                $this->duty_arr[$duty_date]['special_rota_off_doctors'] = [];
            }
            if (!$level_condition['special_shift_requests']) {
                $this->duty_arr[$duty_date]['special_rota_morning_doctors_res'] = [];
                $this->duty_arr[$duty_date]['special_rota_evening_doctors_res'] = [];
                $this->duty_arr[$duty_date]['special_rota_night_doctors_res'] = [];
                $this->duty_arr[$duty_date]['special_rota_morning_doctors_reg'] = [];
                $this->duty_arr[$duty_date]['special_rota_evening_doctors_reg'] = [];
                $this->duty_arr[$duty_date]['special_rota_night_doctors_reg'] = [];
            }
            if (!$level_condition['general_shift_requests']) {
                $this->duty_arr[$duty_date]['check_general_request'] = false;
            }
            $this->extra_duties_allowed = $level_condition['extra_duties'];
        }
    }

    public function doctor_duty_allowed($shift, $doctor, $duty_date)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type'.$shift);

        if(in_array($doctors_id,$this->duty_arr[$duty_date]['assigned_doctors']) ){
            return false;
        }
        if (in_array($doctor->id, $this->duties_arr[$duty_date]['annual_leave'])) {
            return false;
        } elseif (in_array($doctor->id, $this->duty_arr[$duty_date]['special_rota_off_doctors'])) {
            return false;
        } elseif ($this->duty_arr[$duty_date]['check_general_request']) {
            if ($this->doctors_arr [$doctor->id][$duties_shift_type[$shift]['required_shift']]<=
                        $this->doctors_arr [$doctor->id][$duties_shift_type[$shift]['given']]  ||
                        $this->doctors_arr [$doctor->id]['req_general']<=
                        $this->doctors_arr [$doctor->id]['given_general']
                ) {
                return false;
            }
        } else {
            $duties_allowed = $this->doctors_arr [$doctor->id]['total_duties'] + $this->extra_duties_allowed;
            if ($duties_allowed >= $this->doctors_arr [$doctor->id]['total_assigned_duties']) {
                return false ;
            }
        }
        $has_room = $this->has_room_for_doctors(
            $rota_generate_pattern->total_morning_doctors,
            $this->duties_arr[$duty_date]['assigned_morning_doctors_reg']
        );
        return true;
    }

    public function assign_doctor($shift, $doctor, $duty_date)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type'.$shift);
        $next_date = strtotime('+1 day', $duty_date);
        $pre_date = strtotime('-1 day', $duty_date);
        if (!$this->doctor_duty_allowed($shift, $doctor, $duty_date)) {
            return false;
        }
        if ($shift == 'morning') {
            $this->doctors_arr [$doctor->id]['given_morning'] = $this->doctors_arr [$doctor->id]['given_morning'] +1  ;
        } elseif ($shift == 'evening') {
            $this->doctors_arr [$doctor->id]['given_evening'] = $this->doctors_arr [$doctor->id]['given_evening'] +1  ;
        } else { //shift = night
            $this->doctors_arr [$doctor->id]['given_night'] = $this->doctors_arr [$doctor->id]['given_night'] +1  ;
        }
        $this->doctors_arr [$doctor->id]['duties_assigned_dates'] = $duty_date ;
        $assigned_doctor = $doctor->type == 1 ? 'assigned_doctors_res' : 'assigned_doctors_reg';
        $this->duties_arr [$duty_date][$duties_shift_type[$assigned_doctor]][] = $doctor->id;
        $this->duties_arr [$duty_date]['assigned_doctors'][] = $doctor->id;

        $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor->id] =
                                        $this->duties_arr [$pre_date]['doctors_duty_num_initial'][$doctor->id]+1;

        // $this->duties_arr [$duty_date]['doctors_consective_duties_arr'][$doctor->id]++;
        $this->doctors_arr [$doctor->id][ $duties_shift_type['given']]++;
        if ($this->duties_arr[$duty_date]['doctors_duty_num_initial'][$doctor->id] == 1) {
            $this->duties_arr [$next_date][$duties_shift_type['consecutive_doctors_arr']] = $doctor->id; // morning
            $this->duties_arr [$next_date]['consecutive_doctors'] = $doctor->id;
        }

        if (isset($this->duties_arr [$next_date])) {
            if ($this->consective_days_allowed <= $this->duties_arr [$pre_date]['doctors_duty_num_initial'][$doctor->id]) {
                $this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'] = $doctor->id;
            }
        }
        return true;
    }

    public function assign_duty($duty_date, $doctor, $shift)
    {
        // if(in_array($doctor->id,$this->duties_arr [$duty_date]['diss_qualified_doctors'])){
        //     return false;
        // }
        if ($shift == $this->shifts['morning']) {
            if ($doctor->doctor_type == 1) {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_morning_doctors,
                    $this->duties_arr[$duty_date]['assigned_morning_doctors_reg']
                );
                if ($has_room) {
                    $this->assign_doctor($this->shifts['morning'], $doctor, $duty_date);
                    return true;
                }
            } else {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_morning_doctors,
                    $this->duties_arr[$duty_date]['assigned_morning_doctors_res']
                );

                if ($has_room) {
                    $this->assign_doctor($this->shifts['morning'], $doctor, $duty_date);
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
                    $this->assign_doctor($this->shifts['evening'], $doctor, $duty_date);
                    return true;
                }
            } else {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_evening_doctors,
                    $this->duties_arr[$duty_date]['assigned_evening_doctors_res']
                );

                if ($has_room) {
                    $this->assign_doctor($this->shifts['evening'], $doctor, $duty_date);
                    return true;
                }
            }
        } else { //   $shift == $this->shifts['night']
            if ($special_req->doctor->doctor_type == 1) {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_reg']
                );
                if ($has_room) {
                    $this->assign_doctor($this->shifts['night'], $doctor, $duty_date);
                    return true;
                }
            } else {
                $has_room = $this->has_room_for_doctors(
                    $rota_generate_pattern->total_night_doctors,
                    $this->duties_arr[$duty_date]['assigned_night_doctors_res']
                );

                if ($has_room) {
                    $this->assign_doctor($this->shifts['night'], $doctor, $duty_date);
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
                $this->assign_duty($duty_date, $special_req->doctor, $this->shift['morning']);
                if ($special_req->doctor->doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_evening_request)) {
            foreach ($special_rota_evening_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor, $this->shift['evening']);
                if ($special_req->doctor->doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_night_request)) {
            foreach ($special_rota_night_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor, $this->shift['night']);
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
            if (!$doctor_added) {
                $this->assign_duty($duty_date, $doctor, 'night');
            }
        }
        // morning consecutive doctors can be adjusted in evening and night shift both
        foreach ($consecutive_morning_doctors as $d) {
            $doctor_added = $this->assign_duty($duty_date, $doctor, 'morning');
            if (!$doctor_added) {
                $this->assign_duty($duty_date, $doctor, 'evening');
            } elseif (!$doctor_added) {
                $this->assign_duty($duty_date, $doctor, 'night');
            }
        }
    }


    public function generate_rota()
    {
        $rules = Config::get('constants.rules');
        $this->level = $rules['level-1'];
        // $find_doctors_level_index = $rules['level-1'];

        foreach ($this->rota_generate_patterns as $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;
            $this->assign_duties_to_consecutive_doctors($duty_date);

            $doctors_index = 0;
            [$doctors_arr,$doctors] = $this->get_suitable_doctors(
                $doctors_arr,
                $duties_arr,
                $duty_date
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
                    return $this->select_doctor_general_rota($duty_date, $shift='evening');
                }
            } elseif ($shift == 'evening') {
                if ($duties_arr[$duty_date]['total_evening_doctors'] == sizeof($duties_arr[$duty_date]['assigned_evening_doctors_res'])) {
                    return $this->select_doctor_general_rota($duty_date, $shift='night');
                }
            } else {
                return ;
            }

            // if (in_array($doctor->id, $duties_arr[$duty_date]['diss_qualified_doctors'])) {
            //     continue;
            // }
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
