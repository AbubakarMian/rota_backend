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
    protected $duties_arr=[];
    protected $doctors_arr=[];
    protected $rota_generate_pattern;
    protected $consective_days_allowed = 4;
    protected $doctors_duty_num_initial=[];
    protected $level='';
    protected $extra_duties_allowed=0;

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
        $this->get_doctors_rotareq_details();

        $this->set_min_extra_duties_required();

        $this->get_duties_info_and_assign_special_requests();

        $duty_date = $this->rota_generate_patterns[0]->duty_date;
        $last_duty_date = $this->rota_generate_patterns[sizeof($this->rota_generate_patterns)-1]->duty_date;
        $mkey = 0;

        // foreach ($this->rota_generate_patterns as $rota_generate_pattern_key => $rota_generate_pattern) {
        while ($duty_date <= $last_duty_date) {
            $mkey = $mkey+1;
                $all_assigned = $this->assign_duties_to_consecutive_doctors($duty_date);

           if(!$all_assigned){
            $all_assigned = $this->assign_duties_to_on_going_doc($duty_date);
           }

            $all_assigned = $this->assign_general_doctor_duties($duty_date);

            if (!$all_assigned) {
                $all_assigned = $this->assign_doctor_duties($duty_date);
            }

            if(!$all_assigned){
                $try_num = 0 ;
                $find_doctor_for = 0;
                while(!$all_assigned){
                    $find_suitable_doctor_key = 0;
                    while($find_suitable_doctor_key<1000 && !$all_assigned){
                        $all_assigned = $this->find_suitable_doctor($duty_date);
                        $find_suitable_doctor_key++;
                        $try_num++;
                    }
                    if(!$all_assigned){
                        // dd($duty_date);
                        $this->reset_duties_by_date($duty_date);
                        $duty_date = strtotime('-1 day',$duty_date);
                        $this->reset_duties_by_date($duty_date);
                    }
                    if($try_num> 70000){
                        dd($duty_date);
                    }
                }
            }

            $duty_date = strtotime('+1 day',$duty_date);
        }
        return [$this->duties_arr,$this->doctors_arr];
    }

    public function set_min_extra_duties_required(){
        $total_doctor_duties = 0;
        $total_duties_required = 0;
        foreach($this->doctors_arr as $doctor){
            $total_doctor_duties = $total_doctor_duties +($doctor['total_duties']-$doctor['total_requested_leaves']);
        }
        foreach( $this->rota_generate_patterns as $rgp){
            $total_duties_required = $total_duties_required + $rgp->total_doctors;
        }
        if($total_duties_required > $total_doctor_duties){
            $duties_diff = $total_duties_required - $total_doctor_duties;
            $this->extra_duties_allowed = ceil($duties_diff/(sizeof($this->doctors_arr)));
        }
        else{
            $this->extra_duties_allowed = 0;
        }
    }

    public function find_suitable_doctor($duty_date){
        $all_doctors = $this->doctors_arr; // sort them by duties assigned asc
        $all_doctors = array_column($all_doctors, 'doctor_id');
        shuffle($all_doctors);

        return $this->assign_duties_to_doctors($duty_date,$all_doctors);
    }

    public function get_subset_of_doctor($array ,$minLength = 1){
        $doctor_arr = Doctor::get('id')->toArray();
        $subset = array();
        $results = array(array());
        foreach ($array as $element){
            foreach ($results as $combination){
                $result = array_merge(array($element), $combination);
                array_push($results, $result);

                if(count($result) == $minLength){
                    $subset[] = $result;
                }
            }
        }

        return $subset;

    }

    public function assign_duties_to_consecutive_doctors($duty_date)
    {
        $consecutive_night_doctors = $this->duties_arr[$duty_date]['consecutive_night_doctors'];
        $consecutive_evening_doctors = $this->duties_arr[$duty_date]['consecutive_evening_doctors'];
        $consecutive_morning_doctors = $this->duties_arr[$duty_date]['consecutive_morning_doctors'];

        foreach ($consecutive_night_doctors as $doctor_id) {
            $this->assign_duty($duty_date, $doctor_id, 'night');
        }
        // evening consecutive doctors can be adjusted in night shift
        foreach ($consecutive_evening_doctors as $doctor_id) {
            $doctor_added = $this->assign_duty($duty_date, $doctor_id, 'evening');
            if (!$doctor_added) {
                $this->assign_duty($duty_date, $doctor_id, 'night');
            }
        }
        // morning consecutive doctors can be adjusted in evening and night shift both
        foreach ($consecutive_morning_doctors as $doctor_id) {
            $doctor_added = $this->assign_duty($duty_date, $doctor_id, 'morning');
            if (!$doctor_added) {
                $this->assign_duty($duty_date, $doctor_id, 'evening');
            } elseif (!$doctor_added) {
                $this->assign_duty($duty_date, $doctor_id, 'night');
            }
        }
        return $this->if_all_duties_assigned($duty_date);
    }

    public function assign_duties_to_doctors($duty_date,$all_doctors){
        foreach ($all_doctors as $doctor_id) {
            $assigned = $this->assign_duty($duty_date, $doctor_id, 'morning');
            if (!$assigned) {
                $assigned = $this->assign_duty($duty_date, $doctor_id, 'evening');

            }
            if (!$assigned){
                $assigned = $this->assign_duty($duty_date, $doctor_id, 'night');
            }
        }
        return $this->if_all_duties_assigned($duty_date);
    }

    public function assign_doctor_duties($duty_date)
    {
        $all_doctors = $this->doctors_arr; // sort them by duties assigned asc
        $sort_on_column = array_column($all_doctors, 'assigned_duties');
        array_multisort($sort_on_column, SORT_ASC, $all_doctors);
        $all_doctors = array_column($all_doctors, 'doctor_id');
        return $this->assign_duties_to_doctors($duty_date,$all_doctors);
    }

    public function get_general_rota_request_doctor_detail($doctor_id)
    {

        $req_duties_doctor = General_rota_request::where('doctor_id', $doctor_id)
                                              ->get(['total_duties', 'shift']);

        $req_duties_mor = 0;
        $req_duties_eve = 0;
        $req_duties_night = 0;
        foreach ($req_duties_doctor as $req) {
            if ($req->shift == $this->shifts['morning']) {
                $req_duties_mor=$req->total_duties;
            } elseif ($req->shift == $this->shifts['evening']) {
                $req_duties_eve=$req->total_duties;
            } else {//if($req->shift == $this->shifts['night']
                $req_duties_night=$req->total_duties;
            }
        }
        return [$req_duties_mor,$req_duties_eve,$req_duties_night];
    }

    public function get_doctors_rotareq_details()
    {
        $days = $this->monthly_rota->total_days;

        $doctors = Doctor::get();
        $doctors = $doctors->shuffle();
        $doctors_arr = [];

        foreach ($doctors as $d) {
            list($req_duties_mor, $req_duties_eve, $req_duties_night) = $this->get_general_rota_request_doctor_detail($d->id);

            $this->doctors_arr[$d->id] = [
            'doctor_id'=>$d->id,
            'doctor'=>$d,
            'doctor_type'=>$d->doctor_type_id,
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
            'yearly_leaves'=>0,
            'regular_leaves'=>0,
            'total_requested_leaves'=>0,
            'duties_assigned_dates'=>[],
            'assigned_duties'=>0,
            'assigned_leaves'=>0,
            'holiday_leaves'=>0 // if he already got sat sun off
            ];
        }
        $this->doctors_arr;
    }

    public function assign_general_doctor_duties($duty_date)
    {

        $this->assign_general_duties($duty_date);
        return $this->if_all_duties_assigned($duty_date);
    }

    public function assign_general_duties($duty_date)
    {
        $all_doctors = $this->doctors_arr; // sort them by duties assigned asc
        $sort_on_column = array_column($all_doctors, 'assigned_duties');
        array_multisort($sort_on_column, SORT_ASC, $all_doctors);
        foreach ($all_doctors as $all_doctor) {
            $assigned= '' ;
            if ($this->doctors_arr[$all_doctor['doctor_id']]['req_morning']>
                    $this->doctors_arr[$all_doctor['doctor_id']]['given_morning']) {
                $assigned = $this->assign_duty($duty_date, $all_doctor['doctor_id'], 'morning');
            }
            if (!$assigned && $this->doctors_arr[$all_doctor['doctor_id']]['req_evening']>$this->doctors_arr[$all_doctor['doctor_id']]['given_evening']) {
                $assigned = $this->assign_duty($duty_date, $all_doctor['doctor_id'], 'evening');
            } elseif (!$assigned && $this->doctors_arr[$all_doctor['doctor_id']]['req_night']>
                                    $this->doctors_arr[$all_doctor['doctor_id']]['given_night']) {
                $assigned = $this->assign_duty($duty_date, $all_doctor['doctor_id'], 'night');
            }
        }
    }

    public function if_shift_doctors_completed($duty_date,$shift)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);
        $total_shift_doctors= $this->duties_arr[$duty_date][$duties_shift_type['total_doctors']];
        $total_assigned_morning_doctors = sizeof($this->duties_arr[$duty_date][$duties_shift_type['assigned_doctors_res']])+
                                        sizeof($this->duties_arr[$duty_date][$duties_shift_type['assigned_doctors_reg']]);
        if ($total_shift_doctors<= $total_assigned_morning_doctors) {
            return true;
        }
        return false;
    }

    public function assign_duties_to_on_going_doc($duty_date)
    {
        $pre_date = strtotime('-1 day', $duty_date);

        $previous_doctors = array_merge(
            $this->duties_arr[$pre_date]['assigned_morning_doctors_res'],
            $this->duties_arr[$pre_date]['assigned_morning_doctors_reg'],
        );
        $unassigned_pre_doctors = $previous_doctors;

        foreach ($previous_doctors as $key=>$pre_doctor_id) {
            $assigned = false;
            if ($this->doctors_arr[$pre_doctor_id]['given_morning']<$this->doctors_arr[$pre_doctor_id]['req_morning']) {
                $assigned = $this->assign_duty($duty_date, $pre_doctor_id, 'morning');
            }
            if(!$assigned){
                if ($this->doctors_arr[$pre_doctor_id]['given_general']<$this->doctors_arr[$pre_doctor_id]['req_general']) {
                    $this->assign_duty($duty_date, $pre_doctor_id, 'morning');
                }
            }
        }
        $previous_doctors = array_merge(
            $this->duties_arr[$pre_date]['assigned_evening_doctors_res'],
            $this->duties_arr[$pre_date]['assigned_evening_doctors_reg'],
        );
        $unassigned_pre_doctors = array_merge($unassigned_pre_doctors,$previous_doctors);

        foreach ($previous_doctors as $pre_doctor_id) {
            $assigned = false;
            if ($this->doctors_arr[$pre_doctor_id]['given_evening']<$this->doctors_arr[$pre_doctor_id]['req_evening']) {
                $assigned = $this->assign_duty($duty_date, $pre_doctor_id, 'evening');
            }
            if(!$assigned){
                if ($this->doctors_arr[$pre_doctor_id]['given_general']<$this->doctors_arr[$pre_doctor_id]['req_general']) {
                    $this->assign_duty($duty_date, $pre_doctor_id, 'evening');
                }
            }
        }

        $previous_doctors = array_merge(
            $this->duties_arr[$pre_date]['assigned_night_doctors_res'],
            $this->duties_arr[$pre_date]['assigned_night_doctors_reg'],
        );

        foreach ($previous_doctors as $pre_doctor_id) {
            $assigned = false;
            if ($this->doctors_arr[$pre_doctor_id]['given_night']<$this->doctors_arr[$pre_doctor_id]['req_night']) {
                $assigned = $this->assign_duty($duty_date, $pre_doctor_id, 'night');
            }
            if(!$assigned){
                if ($this->doctors_arr[$pre_doctor_id]['given_general']<$this->doctors_arr[$pre_doctor_id]['req_general']) {
                    $this->assign_duty($duty_date, $pre_doctor_id, 'night');
                }
            }
        }
        return $this->if_all_duties_assigned($duty_date);
    }

    public function if_all_duties_assigned($duty_date)
    {
        return $this->if_shift_doctors_completed($duty_date,'morning') &&$this->if_shift_doctors_completed($duty_date,'evening') &&
        $this->if_shift_doctors_completed($duty_date,'night');
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
        'special_rota_morning_doctors_res'=>[], //set
        'special_rota_morning_doctors_reg'=>[], //set
        'special_rota_evening_doctors_res'=>[], //set
        'special_rota_evening_doctors_reg'=>[],//set
        'special_rota_night_doctors_res'=>[], //set
        'special_rota_night_doctors_reg'=>[], //set
        'special_rota_morning_request'=>[], //set
        'special_rota_evening_request'=>[], //set
        'special_rota_night_request'=>[],
        'special_rota_off_doctors'=>[],
        'assigned_morning_doctors_res'=> [],
        'assigned_morning_doctors_reg'=>[],
        'assigned_evening_doctors_res'=>[],
        'assigned_evening_doctors_reg'=>[],
        'assigned_night_doctors_res'=>[],
        'assigned_night_doctors_reg'=>[],
        'has_morning_ucc'=>0,
        'has_evening_ucc'=>0,
        'has_night_ucc'=>0,
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
            $doctors_consective_duties = $doctors_consective_duties->whereIn('doctor_id', $where_doctors);
        }
        $doctors_consective_duties = $doctors_consective_duties->get();

        return $doctors_consective_duties;
    }

    public function get_pre_day_duties_arr($duty_date)
    {
        $pre_date = strtotime('-1 day', $duty_date);
        $this->duties_arr[$pre_date] = $this->get_initial_duties_arr();
        $consective_days_allowed = $this->consective_days_allowed;

        $day_doctors = [];
        $date_rota = $this->rota_by_date($pre_date);
        if (count($date_rota)) {
            $day_doctors[] = $date_rota;
        }

        for ($day = 2;$day<=$consective_days_allowed ; $day++) {
            $check_date  = strtotime('-'.$day.' day', $pre_date);
            if (isset($day_doctors[$day-1])) {
                $pre_doctors = array_column($day_doctors[$day-1]->toArray(), 'doctor_id');
                $date_rota = $this->rota_by_date($pre_date, $pre_doctors); // get only those doctors whic exist in pre day
                if (count($date_rota)) {
                    $day_doctors[] = $date_rota;
                }
            }
        }
        if (isset($day_doctors[$consective_days_allowed])) {
            $last_day_doctors = $day_doctors[$consective_days_allowed];
            $this->duties_arr[$duty_date]['dis_qualified_consecutive_doctors'] = $last_day_doctors;
        }
        else{
            $this->duties_arr[$duty_date]['dis_qualified_consecutive_doctors'] = [];
        }

        for ($day = 0;$day<$consective_days_allowed ; $day++) {
            foreach ($day_doctors as $d) {
                $this->duties_arr[$pre_date]['doctors_duty_num_initial'][$d->doctor_id]++;
            }
        }

        if(isset($day_doctors[1])){
            $consecutive_doctors = array_diff($day_doctors[0], $day_doctors[1]);
            $this->duties_arr[$duty_date]['consecutive_doctors'] = $consecutive_doctors;
        }
        elseif(isset($day_doctors[0])){
            $this->duties_arr[$duty_date]['consecutive_doctors'] = $day_doctors[0];
        }
        else{
            $this->duties_arr[$duty_date]['consecutive_doctors'] = [];
        }

        foreach ($day_doctors as $day_doctor) {
            if ($day_doctor->duty_date == $pre_date && in_array($day_doctor->doctor_id, $consecutive_doctors)) {
                if ($day_doctor->shift == $this->shift['morning']) {
                    $this->duties_arr[$duty_date]['consecutive_morning_doctors'] = $day_doctor->doctor_id;
                } elseif ($day_doctor->shift == $this->shift['evening']) {
                    $this->duties_arr[$duty_date]['consecutive_evening_doctors'] = $day_doctor->doctor_id;
                    $this->duties_arr[$duty_date]['disqualified_morning_doctors'] = $day_doctor->doctor_id;
                } else {
                    $this->duties_arr[$duty_date]['consecutive_night_doctors'] = $day_doctor->doctor_id;
                    $this->duties_arr[$duty_date]['disqualified_morning_doctors'] = $day_doctor->doctor_id;
                    $this->duties_arr[$duty_date]['disqualified_evening_doctors'] = $day_doctor->doctor_id;
                }
            }
        }
    }

    public function special_rota_request_details($duty_date)
    {
        $special_rota_request = Special_rota_request::where('duty_date', $duty_date);
        $special_rota_morning_request = $special_rota_request->where('want_duty', 1)->where('shift', 'morning')->pluck('doctor_id')->toArray();
        $special_rota_evening_request = $special_rota_request->where('want_duty', 1)->where('shift', 'evening')->pluck('doctor_id')->toArray();
        $special_rota_night_request = $special_rota_request->where('want_duty', 1)->where('shift', 'night')->pluck('doctor_id')->toArray();
        $special_rota_want_off = $special_rota_request->where('want_off', 1)->pluck('doctor_id')->toArray();
        return array(
        'special_rota_morning_request'=>$special_rota_morning_request,
        'special_rota_evening_request'=>$special_rota_evening_request,
        'special_rota_night_request'=>$special_rota_night_request,
        'special_rota_want_off'=>$special_rota_want_off);
    }

    public function leave_request($duty_date)
    {
        $leave_request = Leave_Request::where('start_date','<=', $duty_date)->where('end_date','>=', $duty_date)->get();
        $regular_leaves = array();
        $annual_leaves = array();
        $special_rota_want_off = [];
        foreach ($leave_request as $lr) {
            if ($lr->annual_leave == 1) {
                $annual_leaves[] = $lr->id ;
                $this->doctors_arr[$lr->id]['yearly_leaves'] = $lr->id ;

            } else {
                $regular_leaves[] = $lr->id ;
                $this->doctors_arr[$lr->id]['regular_leaves'] = $lr->id ;
            }
            $this->doctors_arr[$lr->id]['total_requested_leaves'] = $lr->id ;
        }

        $all_leaves = array_merge($annual_leaves, $regular_leaves);
        return array(
           'annual_leaves'=> $annual_leaves,
           'regular_leaves'=> $regular_leaves,
            'all_leaves'=>$all_leaves
        );
    }
    public function get_duties_info_and_assign_special_requests()
    {
        foreach ($this->rota_generate_patterns as $rota_generate_pattern_key => $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;
            if ($rota_generate_pattern_key == 0) {
                $this->get_pre_day_duties_arr($duty_date);
            }
            $this->duties_arr[$duty_date] = $this->get_initial_duties_arr();
            //special_rota_want_off query function
            $special_rota_request = $this->special_rota_request_details($duty_date);

            // Leave_Request query function
            $leave_request = $this->leave_request($duty_date);

            $this->duties_arr[$duty_date]['duty_date'] = $duty_date;
            $this->duties_arr[$duty_date]['total_morning_doctors'] = $rota_generate_pattern->total_morning_doctors;
            $this->duties_arr[$duty_date]['total_evening_doctors'] = $rota_generate_pattern->total_evening_doctors;
            $this->duties_arr[$duty_date]['total_night_doctors'] = $rota_generate_pattern->total_night_doctors;
            $this->duties_arr[$duty_date]['has_morning_ucc'] = $rota_generate_pattern->has_morning_ucc;
            $this->duties_arr[$duty_date]['has_evening_ucc'] = $rota_generate_pattern->has_evening_ucc;
            $this->duties_arr[$duty_date]['has_night_ucc'] = $rota_generate_pattern->has_night_ucc;
            $this->duties_arr[$duty_date]['annual_leaves'] = $leave_request['annual_leaves'];
            $this->duties_arr[$duty_date]['regular_leaves'] = $leave_request['regular_leaves'];
            $this->duties_arr[$duty_date]['all_leaves'] = $leave_request['all_leaves'];
            $this->duties_arr[$duty_date]['special_rota_morning_request'] = $special_rota_request['special_rota_morning_request'];
            $this->duties_arr[$duty_date]['special_rota_evening_request'] = $special_rota_request['special_rota_evening_request'];
            $this->duties_arr[$duty_date]['special_rota_night_request'] = $special_rota_request['special_rota_night_request'];
            $this->duties_arr[$duty_date]['special_rota_off_request'] = $special_rota_request['special_rota_want_off'];
            // $this->duties_arr[$duty_date]['disqualified_doctors'] = [];

            $this->assign_duties_to_special_request_doctors($duty_date);
        }

    }

    public function has_room_for_doctors($duty_date, $doctor_id, $shift)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);
        $total_doctors = $this->duties_arr[$duty_date][$duties_shift_type['total_doctors']];
        $has_ucc = $this->duties_arr[$duty_date][$duties_shift_type['has_ucc']];
        $min_res = $has_ucc + 1;
        $min_reg = 1;
        $total_assigned_doctors_reg = sizeof($this->duties_arr[$duty_date][$duties_shift_type['assigned_doctors_reg']]);
        $total_assigned_doctors_res = sizeof($this->duties_arr[$duty_date][$duties_shift_type['assigned_doctors_res']]);
        if (($total_assigned_doctors_reg+$total_assigned_doctors_res) >= $total_doctors) {
            return false;
        }

        if ($this->doctors_arr[$doctor_id]['doctor_type'] == 1) {

            if ($min_res>$total_assigned_doctors_res) {
                return true;
            } elseif (($total_doctors - $total_assigned_doctors_res - ($min_reg-$total_assigned_doctors_reg)) > 1) {
                return true;
            }
        } else {// ($this->doctors_arr[$doctor_id]->type == 2)

            if ($min_reg>$total_assigned_doctors_reg) {
                return true;
            } elseif (($total_doctors - $total_assigned_doctors_reg - ($min_res-$total_assigned_doctors_res)) > 1) {
                return true;
            }
        }
        return false;
    }

    public function chage_duty_arr_conditions($duty_date, $level_conditions)
    {
        $level_conditions = $this->level;

        foreach ($level_conditions as $level_condition) {
            if (!$level_condition['annual_leave']) {
                $this->duties_arr[$duty_date]['annual_leave'] = [];
            }
            if (!$level_condition['regular_leaves']) {
                $this->duties_arr[$duty_date]['regular_leaves'] = [];
            }
            if (!$level_condition['special_off_requests']) {
                $this->duties_arr[$duty_date]['special_rota_off_doctors'] = [];
            }
            if (!$level_condition['special_shift_requests']) {
                $this->duties_arr[$duty_date]['special_rota_morning_doctors_res'] = [];
                $this->duties_arr[$duty_date]['special_rota_evening_doctors_res'] = [];
                $this->duties_arr[$duty_date]['special_rota_night_doctors_res'] = [];
                $this->duties_arr[$duty_date]['special_rota_morning_doctors_reg'] = [];
                $this->duties_arr[$duty_date]['special_rota_evening_doctors_reg'] = [];
                $this->duties_arr[$duty_date]['special_rota_night_doctors_reg'] = [];
            }
            if (!$level_condition['general_shift_requests']) {
                $this->duties_arr[$duty_date]['check_general_request'] = false;
            }
            $this->extra_duties_allowed = $level_condition['extra_duties'];
        }
    }

    function debug($shift='no shift',$compare='no',$data='hi'){
        if($shift == $compare){
            dd($data);
        }
        if($shift == $compare){
            dd($shift .'     '.$data);
        }
        if($shift == $compare){
            dd($shift .'     '.$data);
        }
    }

    public function shift_allowed($shift, $doctor_id, $duty_date){
        $pre_date = strtotime('-1 day',$duty_date);
        if(!isset($this->duties_arr[$pre_date])){
            return true;
        }
        $previous_shift = $this->duties_arr[$pre_date];
        $morning_doctors = array_merge($previous_shift['assigned_morning_doctors_res'],$previous_shift['assigned_morning_doctors_reg']);
        $evening_doctors = array_merge($previous_shift['assigned_evening_doctors_res'],$previous_shift['assigned_evening_doctors_reg']);
        $night_doctors = array_merge($previous_shift['assigned_night_doctors_res'],$previous_shift['assigned_night_doctors_reg']);
        if($shift == $this->shifts['morning']){
            if(in_array($doctor_id,$evening_doctors)===true){
                return false;
            }
            if(in_array($doctor_id,$night_doctors)===true){
                return false;
            }
        }
        if($shift == $this->shifts['evening']){
            if(in_array($doctor_id,$night_doctors)===true){
                return false;
            }
        }
        return true;
    }

    public function doctor_duty_allowed($shift, $doctor_id, $duty_date)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);
        $pre_date = strtotime('-1 day',$duty_date);
        $is_disqualified = in_array($doctor_id, $this->duties_arr [$duty_date]['disqualified_doctors']);
        $dis_qualified_consecutive_doctors = in_array($doctor_id, $this->duties_arr [$duty_date]['dis_qualified_consecutive_doctors']);
        $doctor_already_assigned = in_array($doctor_id, $this->duties_arr[$duty_date]['assigned_doctors']);


        if ($is_disqualified === true) {
            return false;
        }
        if ($dis_qualified_consecutive_doctors === true) {
            return false;
        }
        if(!$this->shift_allowed($shift, $doctor_id, $duty_date)){
            return false;
        }
        if (!$this->has_room_for_doctors($duty_date, $doctor_id, $shift)) {
            return false;
        }

        if ($doctor_already_assigned === true) {
            return false;
        }

        if (in_array($doctor_id, $this->duties_arr[$duty_date]['annual_leaves']) === true) {
            return false;
        }
        if (in_array($doctor_id, $this->duties_arr[$duty_date]['special_rota_off_doctors']) === true) {
            return false;
        }


        // if ($this->duties_arr[$duty_date]['check_general_request']) {
            if ($this->doctors_arr [$doctor_id][$duties_shift_type['required_shift']]<=
                        $this->doctors_arr [$doctor_id][$duties_shift_type['given']]  &&
                        $this->doctors_arr [$doctor_id]['req_general']<=
                        $this->doctors_arr [$doctor_id]['given_general']
                ) {
                return false;
            }

        $duties_allowed = $this->doctors_arr [$doctor_id]['total_duties'] + $this->extra_duties_allowed;

        if ($duties_allowed <= $this->doctors_arr [$doctor_id]['assigned_duties']) {

            return false ;
        }
        return true;
    }

    public function assign_doctor($shift, $doctor_id, $duty_date)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);

        $next_date = strtotime('+1 day', $duty_date);
        $pre_date = strtotime('-1 day', $duty_date);

        if (!$this->doctor_duty_allowed($shift, $doctor_id, $duty_date)) {
            return false;
        }

        // if ($shift == 'morning') {
        //     $this->doctors_arr [$doctor_id]['given_morning'] = $this->doctors_arr [$doctor_id]['given_morning'] +1  ;
        // } elseif ($shift == 'evening') {
        //     $this->doctors_arr [$doctor_id]['given_evening'] = $this->doctors_arr [$doctor_id]['given_evening'] +1  ;
        // } else { //shift = night
        //     $this->doctors_arr [$doctor_id]['given_night'] = $this->doctors_arr [$doctor_id]['given_night'] +1  ;
        // }
        $this->doctors_arr [$doctor_id]['duties_assigned_dates'][] = $duty_date ;
        $this->doctors_arr [$doctor_id]['assigned_duties'] = $this->doctors_arr [$doctor_id]['assigned_duties'] + 1;
        $assigned_doctor = $this->doctors_arr [$doctor_id]['doctor_type'] == 1 ? 'assigned_doctors_res' : 'assigned_doctors_reg';
        $this->duties_arr [$duty_date][$duties_shift_type[$assigned_doctor]][] = $doctor_id;

        $this->duties_arr [$duty_date]['assigned_doctors'][] = $doctor_id;

        $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor_id] =
                                        $this->duties_arr [$pre_date]['doctors_duty_num_initial'][$doctor_id]+1;

        if($this->doctors_arr [$doctor_id][ $duties_shift_type['given']] >=
                $this->doctors_arr [$doctor_id][ $duties_shift_type['required_shift']]){
            $this->doctors_arr [$doctor_id]['given_general']++;
        }
        // else{
            $this->doctors_arr [$doctor_id][ $duties_shift_type['given']]++;
        // }

        if ($this->duties_arr[$duty_date]['doctors_duty_num_initial'][$doctor_id] == 1) {
            $this->duties_arr [$next_date][$duties_shift_type['consecutive_doctors']][] = $doctor_id; // morning
            $this->duties_arr [$next_date]['consecutive_doctors'][] = $doctor_id;
        }

        if (isset($this->duties_arr [$next_date])) {

            if ($this->consective_days_allowed <= $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor_id]) {
                $this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'][] = $doctor_id;
                // if($duty_date == 1614902400){
                //     dd($this->duties_arr [$next_date]['dis_qualified_consecutive_doctors']);
                // }
                // $this->duties_arr [$next_date]['disqualified_doctors'][] = $doctor_id;
            }
        }
        return true;
    }

    public function assign_duty($duty_date, $doctor_id, $shift)
    {
        $assigned = false;
        if ($shift == $this->shifts['morning']) {
                $assigned = $this->assign_doctor($this->shifts['morning'], $doctor_id, $duty_date);
        } elseif ($shift == $this->shifts['evening']) {
                $assigned = $this->assign_doctor($this->shifts['evening'], $doctor_id, $duty_date);
        } else { //   $shift == $this->shifts['night']
                $assigned = $this->assign_doctor($this->shifts['night'], $doctor_id, $duty_date);
        }

        return $assigned;
    }

    public function assign_duties_to_special_request_doctors($duty_date)
    {
        $special_rota_morning_request = $this->duties_arr[$duty_date]['special_rota_morning_request'];
        $special_rota_evening_request = $this->duties_arr[$duty_date]['special_rota_evening_request'];
        $special_rota_night_request = $this->duties_arr[$duty_date]['special_rota_night_request'];
        if (isset($special_rota_morning_request)) {
            foreach ($special_rota_morning_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor->id, $this->shift['morning']);
                if ($special_req->doctor->doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_evening_request)) {
            foreach ($special_rota_evening_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor->id, $this->shift['evening']);
                if ($special_req->doctor->doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
        if (isset($special_rota_night_request)) {
            foreach ($special_rota_night_request as $special_req) {
                $this->assign_duty($duty_date, $special_req->doctor->id, $this->shift['night']);
                if ($special_req->doctor->doctor_type == 1) {
                    $this->duties_arr [$duty_date]['special_rota_night_doctors_res'][] = $special_req->doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_night_doctors_reg'][] = $special_req->doctor_id;
                }
            }
        }
    }

    public function reset_assigned_doctors($duty_date){

        $shifts = Config::get('constants.duties_shift_type');

        foreach($shifts as $shift){

            // if(!isset($this->duties_arr[$duty_date][$shift['assigned_doctors_res']])){
            //     dd($this->duties_arr[$duty_date]);
            // }

            $assigned_doctors = array_merge($this->duties_arr[$duty_date][$shift['assigned_doctors_res']],
                                            $this->duties_arr[$duty_date][$shift['assigned_doctors_reg']]);

        foreach($assigned_doctors as $ad){
            if($this->doctors_arr[$ad]['extra']){
                $this->doctors_arr[$ad]['extra'] = $this->doctors_arr[$ad]['extra'] - 1;
            }

            if($this->doctors_arr[$ad][$shift['given']] < 1){
                $this->doctors_arr[$ad]['given_general'] = $this->doctors_arr[$ad]['given_general'] -1;
            }
            else{
                $this->doctors_arr[$ad][$shift['given']] = $this->doctors_arr[$ad][$shift['given']] -1;
            }

            $this->doctors_arr[$ad]['assigned_duties'] = $this->doctors_arr[$ad]['assigned_duties'] -1;

            $new_dates = [];
            foreach($this->doctors_arr[$ad]['duties_assigned_dates'] as $dad){
                if($dad == $duty_date){
                    continue;
                }
                else{
                    $new_dates[] = $dad;
                }
            }
            $this->doctors_arr[$ad]['duties_assigned_dates'] = $new_dates;

        }

        }
    }

    public function reset_assigned_duty($duty_date){

        $next_date = strtotime('+1 day',$duty_date);
        // $this->duties_arr[$duty_date]['special_rota_morning_doctors_res'] = [];
        // $this->duties_arr[$duty_date]['special_rota_morning_doctors_reg'] = [];
        // $this->duties_arr[$duty_date]['special_rota_evening_doctors_res'] = [];
        // $this->duties_arr[$duty_date]['special_rota_evening_doctors_reg'] = [];
        // $this->duties_arr[$duty_date]['special_rota_night_doctors_res'] = [];
        // $this->duties_arr[$duty_date]['special_rota_night_doctors_reg'] = [];
        $this->duties_arr[$duty_date]['all_assigned_doctors'] = [];
        $this->duties_arr[$duty_date]['assigned_morning_doctors_res'] = [];
        $this->duties_arr[$duty_date]['assigned_morning_doctors_reg'] = [];
        $this->duties_arr[$duty_date]['assigned_evening_doctors_res'] = [];
        $this->duties_arr[$duty_date]['assigned_evening_doctors_reg'] = [];
        $this->duties_arr[$duty_date]['assigned_night_doctors_res'] = [];
        $this->duties_arr[$duty_date]['assigned_night_doctors_reg'] = [];
        $this->duties_arr[$duty_date]['assigned_doctors'] = [];
        // $this->duties_arr[$duty_date]['disqualified_doctors'] = [];
        // $this->duties_arr[$next_date]['dis_qualified_consecutive_doctors'] = [];
    }

    public function reset_duties_by_date($duty_date){
        // $this->duties_arr[$duty_date]['doctors_duty_num_initial'] = $this->doctors_duty_num_initial;
        $this->reset_assigned_doctors($duty_date);
        $this->reset_assigned_duty($duty_date);
        $this->assign_duties_to_special_request_doctors($duty_date);
    }


}
