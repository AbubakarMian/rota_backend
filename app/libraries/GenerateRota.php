<?php
// remove extra loop recursions
// try to get doctors from
// disqualified_shift_allowed
namespace App\Libraries;

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
use Illuminate\Support\Facades\Log;

class GenerateRota
{
    protected $rota_generate_patterns;
    protected $shifts;
    protected $monthly_rota;
    protected $duties_arr=[];
    protected $doctors_arr=[];
    protected $rota_generate_pattern;
    protected $consective_days_allowed = 5;
    protected $exetime = 1;
    protected $consective_nights_allowed = 4;
    protected $doctors_duty_num_initial=[];
    protected $extra_duties_allowed_basic = 0;
    protected $all_doctors=[];
    protected $level='';
    protected $extra_duties_allowed=0;
    protected $least_doctors_in_shift=4;
    protected $conditions=[];
    protected $debug_index = 0;
    protected $print_log = false;

    public function __construct($monthly_rota,$exetime=1)
    {
        // set_time_limit(0);
        $this->rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota->id)
                                                        ->orderBy('duty_date', 'asc')->get();
        $this->doctors_duty_num_initial = Doctor::select(DB::Raw('0 as duties '), 'id')->pluck('duties', 'id')->toArray();
        $this->all_doctors = array_keys($this->doctors_duty_num_initial);
        $this->shifts = Config::get('constants.duty_type');
        $this->monthly_rota = $monthly_rota;
        $this->exetime = $exetime ?? 0;
        $this->conditions = Config::get('constants.conditions');
    }

    public function generate_rota_arr()
    {
        Log::info('--------------- Generate Rota Start --------------------');
        $this->get_doctors_rotareq_details();
        $this->get_duties_info_and_assign_special_requests();
        // $this->set_min_extra_duties_required();

        Log::info('--------------- Extra duties allowed Min set : '.$this->extra_duties_allowed.' --------------------');
        $duty_date = $this->rota_generate_patterns[0]->duty_date;
        $last_duty_date = $this->rota_generate_patterns[sizeof($this->rota_generate_patterns)-1]->duty_date;
        $date_num = 1;
        $problem_date = 0;
        $condition_key = array_keys ( $this->conditions );
        $condition_key_num = 0;
        $div_condition_key_num = 0;
        $div_extra_duties_allowed = 0;
        $setduties_num = 0;
        $back_track_date = 1;
        $back_track_index = 0;


        while ($duty_date <= $last_duty_date) {
            $problem_date = $duty_date;
            $date_num = $date_num+1;
                $all_assigned = $this->assign_duties_to_consecutive_leave_doctors($duty_date);
                $all_assigned = $this->assign_duties_to_consecutive_doctors($duty_date);

        //    if(!$all_assigned){
                // $all_assigned = $this->assign_duties_to_on_going_doc($duty_date);
        //    }
            $all_assigned = $this->assign_general_doctor_duties($duty_date);

            if (!$all_assigned) {
                $all_assigned = $this->assign_doctor_duties($duty_date);
            }
            if (!$all_assigned) {

                $all_assigned = $this->search_doctor_in_disqualified_list($duty_date);
            }
            if(!$all_assigned){
                $this->set_total_duties_to_applicable_number($duty_date);
                $all_assigned = $this->search_doctor_in_disqualified_list($duty_date);
            }

            $all_assigned = $this->if_all_duties_assigned($duty_date);

                while(!$all_assigned){


                    $div_condition_key_num = $div_condition_key_num+1;
                    $div_extra_duties_allowed = $div_extra_duties_allowed+1;

                    $find_suitable_doctor_key = 0;
                    $back_track_index++;

                    if(!$all_assigned && $back_track_index > 3&& false){
                        $back_track_index = 0;
                        Log::info('--------------- Start Reset Duty : '.$duty_date.' --------------------');
                        for($i=1;$i<=$back_track_date;$i++){
                            $this->reset_duties_by_date($duty_date);
                            $new_duty_date = strtotime('-'.$i.' day',$duty_date);
                            if(!isset($this->duties_arr[$new_duty_date])){
                                break;
                            }
                            $duty_date = $new_duty_date;

                        }
                        $this->reset_duties_by_date_assign_consective_special_request_duties($duty_date);
                        $back_track_date++;
                        Log::info('--------------- Next Reset Duty : '.$duty_date.' --------------------');
                    }

                    if( $div_condition_key_num > 2 && !$all_assigned ){
                        $div_condition_key_num = 0;
                        if($setduties_num == 0){
                            $setduties_num = 1;
                            $this->reset_duties_by_date_assign_consective_special_request_duties($duty_date);
                        }
                    }
                    if($div_extra_duties_allowed > 2 == 0 && !$all_assigned){

                        $div_extra_duties_allowed = 0;
                        $this->extra_duties_allowed = $this->extra_duties_allowed+1;
                        Log::info('--------------- Extra duties allowed increment : '.$this->extra_duties_allowed.' --------------------');
                    }
                    if(isset($condition_key[$condition_key_num ]) && !$all_assigned){
                        $this->conditions[$condition_key[$condition_key_num ]] = false;
                        $condition_key_num = $condition_key_num + 1;
                        $setduties_num = 0;
                    }
                    while($find_suitable_doctor_key < 3  && !$all_assigned){

                        $all_assigned = $this->find_suitable_doctor($duty_date);
                        if (!$all_assigned) {
                            $all_assigned = $this->search_doctor_in_disqualified_list($duty_date);
                        }
                        if (!$all_assigned) {
                            // $this->reset_duties_by_date_assign_consective_special_request_duties($duty_date);
                        }

                        $find_suitable_doctor_key++;

                    }
                    $this->debug_index = $this->debug_index+1;
                    $all_assigned = $this->if_all_duties_assigned($duty_date);
                }
                if($problem_date == $duty_date && $all_assigned){
                    Log::info('--------------- End Problem Date : '.$problem_date.' --------------------');
                    $this->duties_arr[$duty_date]['conditions'] = $this->conditions;
                    $this->debug_index = 0;
                    $back_track_date = 1;
                    foreach($this->conditions as $my_key=>$condition){
                        $this->conditions[$my_key] = true;
                    }
                    $condition_key_num = 0;
                    $this->extra_duties_allowed = $this->extra_duties_allowed_basic;
                    Log::info('--------------- Extra duties allowed reset : '.$this->extra_duties_allowed.' --------------------');
                }

            $duty_date = strtotime('+1 day',$duty_date);
        }
        Log::info('--------------- Generate Rota End --------------------');

        return [$this->duties_arr,$this->doctors_arr];
    }

    public function set_total_duties_to_applicable_number($duty_date){
        $check_if_total_doctors_count_applicaple  = $this->total_doctor_count_applicable($duty_date);
        while(!$check_if_total_doctors_count_applicaple){
            $this->minimize_total_duties($duty_date);
            $check_if_total_doctors_count_applicaple = $this->total_doctor_count_applicable($duty_date);
        }
    }

    public function minimize_total_duties($duty_date){
        $greatest = $this->duties_arr[$duty_date]['total_morning_doctors'];
        $greatest_index = 'total_morning_doctors';
        if($greatest < $this->duties_arr[$duty_date]['total_evening_doctors'] ){
            $greatest = $this->duties_arr[$duty_date]['total_evening_doctors'];
            $greatest_index = 'total_evening_doctors';
        }
        if($greatest < $this->duties_arr[$duty_date]['total_night_doctors']){
            $greatest = $this->duties_arr[$duty_date]['total_night_doctors'];
            $greatest_index = 'total_night_doctors';
        }
        $this->duties_arr[$duty_date][$greatest_index] = $this->duties_arr[$duty_date][$greatest_index] - 1;
    }

    public function total_doctor_count_applicable($duty_date){
        $min_total_doctors_required = $this->duties_arr[$duty_date]['total_morning_doctors']
                                        + $this->duties_arr[$duty_date]['total_evening_doctors']
                                        + $this->duties_arr[$duty_date]['total_night_doctors'];

        $doctors_cannot_get_duties = sizeof($this->duties_arr[$duty_date]['annual_leaves'])
                                    + sizeof($this->duties_arr[$duty_date]['dis_qualified_consecutive_doctors']);

        $total_doctors_can_get_duties_avalible = sizeof($this->doctors_arr) - $doctors_cannot_get_duties;

        if($min_total_doctors_required <= $total_doctors_can_get_duties_avalible ){
            return true;
        }

        return false;
    }

    public function search_doctor_in_disqualified_list($duty_date){

        for($d = 1;$d<=$this->consective_nights_allowed;$d++ ){
            $all_assigned = $this->find_doctor_from_disqualified_list($duty_date,$d);
            if(!$all_assigned){
                $all_assigned = $this->find_doctors_from_shift_not_allowed($duty_date);


            }
        }
        return  $this->if_all_duties_assigned($duty_date);
    }
    public function find_doctors_from_shift_not_allowed($duty_date){

        $all_shifts = ['night','evening','morning']; // since sequice matters in shif assignment

        $morning_shift_assigned = $this->if_shift_doctors_completed($duty_date,'morning');
        $evening_shift_assigned = $this->if_shift_doctors_completed($duty_date,'evening');
        $night_shift_assigned = $this->if_shift_doctors_completed($duty_date,'night');

        if(!$morning_shift_assigned){
            $this->assign_doctors_from_shift_not_allowed($this->shifts['morning'],$duty_date);
        }
        if(!$evening_shift_assigned){
            $this->assign_doctors_from_shift_not_allowed($this->shifts['evening'],$duty_date);
        }
        if(!$night_shift_assigned){
            $this->assign_doctors_from_shift_not_allowed($this->shifts['night'],$duty_date);
        }

        return  $this->if_all_duties_assigned($duty_date);
    }

    function assign_doctors_from_shift_not_allowed($incomplete_shift,$duty_date){

        $duties_not_assigned_doctors = array_diff($this->all_doctors,$this->duties_arr[$duty_date]['assigned_doctors']);
        $duties_shift_type = Config::get('constants.duties_shift_type');
        $all_assigned_doctors = [
            $this->shifts['morning']=>[],
            $this->shifts['evening']=>[],
            $this->shifts['night']=>[],
        ];

        foreach($all_assigned_doctors as $key_shift=>$shift_doctors){
            $all_assigned_doctors[$key_shift] = array_merge($this->duties_arr[$duty_date][$duties_shift_type[$key_shift]['assigned_doctors_res']],
            $this->duties_arr[$duty_date][$duties_shift_type[$key_shift]['assigned_doctors_reg']]);
        }

        foreach($duties_not_assigned_doctors as $not_assigned_doctor_id){
            foreach($this->shifts as $shift){
                $other_shifts = array_diff($this->shifts,[$shift]);
                if($this->shift_allowed($shift, $not_assigned_doctor_id, $duty_date)){
                    $doctor_assigned_to_other_shift = false;
                    foreach($all_assigned_doctors[$shift] as $all_assigned_doctor_id){
                        foreach($other_shifts as $other_shift_key){
                            if($this->shift_allowed($other_shift_key, $all_assigned_doctor_id, $duty_date)){
                                $this->remove_assign_doctor ($duty_date, $all_assigned_doctor_id );
                                $assigned = $this->assign_duty_to_any_avalible_shift( $duty_date,$all_assigned_doctor_id);
                                if($assigned){
                                    $assigned = $this->assign_duty_to_any_avalible_shift($duty_date,$not_assigned_doctor_id);
                                    if($assigned){
                                        $doctor_assigned_to_other_shift = true;
                                    }
                                }
                                else{
                                    $assigned = $this->assign_duty_to_any_avalible_shift($duty_date,$all_assigned_doctor_id);
                                }
                                break;
                            }
                        }
                    }
                    if($doctor_assigned_to_other_shift){
                        break;
                    }
                }
            }
        }
    }

    public function if_doctor_duty_will_be_allowed($shift, $doctor_id, $duty_date)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);
        $pre_date = strtotime('-1 day',$duty_date);
        $is_disqualified = in_array($doctor_id, $this->duties_arr [$duty_date]['disqualified_doctors']);
        $annual_leaves = in_array($doctor_id, $this->duties_arr [$duty_date]['annual_leaves']);
        $regular_leaves = in_array($doctor_id, $this->duties_arr [$duty_date]['regular_leaves']);
        $dis_qualified_consecutive_doctors = in_array($doctor_id, $this->duties_arr [$duty_date]['dis_qualified_consecutive_doctors']);
        $special_rota_off_doctors = in_array($doctor_id, $this->duties_arr[$duty_date]['special_rota_off_doctors']);

        // if ($is_disqualified === true) {
        //     // echo "<br/> is_disqualified : ".$duty_date;
        //     return false;
        // }
        // if ( $dis_qualified_consecutive_doctors === true) {//$this->conditions['dis_qualified_consecutive_doctors'] &&$shift != $this->shifts['night'] &&
            // echo "<br/> dis_qualified_consecutive_doctors : ".$duty_date;
        //     return false;
        // }
        // if(!$this->shift_allowed($shift, $doctor_id, $duty_date)){
        //     // echo "<br/> shift_allowed : ".$duty_date;
        //     return false;
        // }

        if ( $annual_leaves === true) {//$this->conditions['annual_leaves'] &&
            // echo "<br/> annual_leaves : ".$duty_date;
            return false;
        }
        if ($this->conditions['regular_leaves'] && $regular_leaves === true) {
            // echo "<br/> regular_leaves : ".$duty_date;
            return false;
        }

        if ($this->conditions['special_rota_off'] &&  $special_rota_off_doctors === true) {
            // echo "<br/> special_rota_off : ".$duty_date;
            return false;
        }

        if ($this->conditions['check_general_request']) {
            if ($this->doctors_arr [$doctor_id][$duties_shift_type['required_shift']]<=
                        $this->doctors_arr [$doctor_id][$duties_shift_type['given']]  &&
                        $this->doctors_arr [$doctor_id]['req_general']<=
                        $this->doctors_arr [$doctor_id]['given_general']
                ) {
                    // echo "<br/> check_general_request : ".$duty_date;
                return false;
            }
        }

        $duties_allowed = $this->doctors_arr [$doctor_id]['total_duties'] + $this->extra_duties_allowed;

        if ( $duties_allowed <= $this->doctors_arr [$doctor_id]['assigned_duties']) { //$this->conditions['duties_equilibrium'] &&
            return false ;
        }
        return true;
    }

    public function remove_assign_doctor ($duty_date, $doctor_id)
    {
        $assigned_doctor = $this->doctors_arr [$doctor_id]['doctor_type'] == 1 ? 'assigned_doctors_res' : 'assigned_doctors_reg';
        $duties_shift_types = Config::get('constants.duties_shift_type');
        $found = false;
        foreach($duties_shift_types as $shift_key=> $s_type){
            if(in_array($doctor_id,$this->duties_arr [$duty_date][$s_type[$assigned_doctor]])){
                $shift = $shift_key;
                $found = true;
            }
        }

        if(!$found){
            return false;
        }
        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);

        $next_date = strtotime('+1 day', $duty_date);

        $this->doctors_arr [$doctor_id]['duties_assigned_dates'] = array_diff($this->doctors_arr [$doctor_id]['duties_assigned_dates'],[$duty_date]);
        $this->doctors_arr [$doctor_id]['assigned_duties'] = $this->doctors_arr [$doctor_id]['assigned_duties'] - 1;

        $this->duties_arr [$duty_date][$duties_shift_type[$assigned_doctor]] =
                                    array_diff($this->duties_arr [$duty_date][$duties_shift_type[$assigned_doctor]],[$doctor_id]);

        $this->duties_arr [$duty_date]['assigned_doctors'] = array_diff($this->duties_arr [$duty_date]['assigned_doctors'],[$doctor_id]);

        $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor_id] =
                                        $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor_id]-1;

        $doctor_found = true;
        while($doctor_found){
            $num_initial_date = strtotime('+1 day',$duty_date);
            if(!isset($this->duties_arr [$num_initial_date])){
                break;
            }
            if($this->duties_arr [$num_initial_date]['doctors_duty_num_initial'][$doctor_id]!=0){
                $this->duties_arr [$num_initial_date]['doctors_duty_num_initial'][$doctor_id] =
                                $this->duties_arr [$num_initial_date]['doctors_duty_num_initial'][$doctor_id]-1;
            }
            else{
                break;
            }
        }

        // if($this->doctors_arr [$doctor_id][ $duties_shift_type['given']] >=
        //         $this->doctors_arr [$doctor_id][ $duties_shift_type['required_shift']] && $this->doctors_arr [$doctor_id]['given_general'] > 1){
        if($this->doctors_arr [$doctor_id][ $duties_shift_type['given']] < 1){
            $this->doctors_arr [$doctor_id]['given_general']--;
        }
        $this->doctors_arr [$doctor_id][ $duties_shift_type['given']]--;

        if (isset($this->duties_arr [$next_date])) {
            $this->duties_arr [$next_date]['consecutive_doctors'] = array_diff($this->duties_arr [$next_date]['consecutive_doctors'],[$doctor_id]);
            $this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'] = array_diff($this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'],[$doctor_id]);
        }
        return true;

    }
    public function assign_duty_to_any_avalible_shift($duty_date,$doctor_id){
        $assigned = $this->assign_duty($duty_date, $doctor_id, $this->shifts['morning']);
        if(!$assigned){
            $assigned = $this->assign_duty($duty_date, $doctor_id, $this->shifts['evening']);
        }
        if(!$assigned){
            $assigned = $this->assign_duty($duty_date, $doctor_id, $this->shifts['night']);
        }

        if($assigned){
            $temp_duty_date = $duty_date;
            $doctor_found = true;
            while($doctor_found){
                $num_initial_date = strtotime('+1 day',$temp_duty_date);
                if(!isset($this->duties_arr [$num_initial_date])){
                    break;
                }
                if($this->duties_arr [$num_initial_date]['doctors_duty_num_initial'][$doctor_id]!=0){
                    $this->duties_arr [$num_initial_date]['doctors_duty_num_initial'][$doctor_id] =
                                    $this->duties_arr [$temp_duty_date]['doctors_duty_num_initial'][$doctor_id]+1;
                }
                else{
                    $doctor_found = false;
                }
                $temp_duty_date = $num_initial_date;
            }
        }
        return $assigned;
    }


    public function swap_pervious_disqualified_doctor($shift,$dis_qualified_doctor_id, $duty_date,$pre_date){

        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);
        $doctor_will_be_allowed = $this->if_doctor_duty_will_be_allowed($shift, $dis_qualified_doctor_id, $pre_date);


        if($doctor_will_be_allowed){
            $avalible_doctors = array_merge($this->duties_arr[$pre_date][$duties_shift_type['assigned_doctors_res']],
                                                    $this->duties_arr[$pre_date][$duties_shift_type['assigned_doctors_reg']]);

            foreach($avalible_doctors as$u=> $d_id){

                $doctor_will_be_allowed = $this->if_doctor_duty_will_be_allowed($shift, $d_id, $duty_date);
                $already_assigned_pre_date = in_array($dis_qualified_doctor_id,$this->duties_arr[$pre_date]['assigned_doctors']);
                if($doctor_will_be_allowed && !$already_assigned_pre_date){
                    $this->remove_assign_doctor($pre_date, $d_id );
                    $assigned = $this->assign_duty_to_any_avalible_shift($pre_date, $dis_qualified_doctor_id);
                    if(!$assigned){
                        $assigned = $this->assign_duty_to_any_avalible_shift($pre_date, $d_id);
                        return false;
                    }
                    if($assigned){
                        $assigned = $this->assign_duty_to_any_avalible_shift($duty_date, $d_id);
                    }
                    return $assigned;
                }
            }
        }
        return false;
    }

    public function find_doctor_from_disqualified_list($duty_date,$d){
        $pre_date = strtotime('-'.$d.' day',$duty_date);

        if(!isset($this->duties_arr[$pre_date])){
            return true;
        }

        $disqualified_doctors_saved = array_merge(
        $this->duties_arr[$duty_date]['all_leaves'],
        $this->duties_arr[$duty_date]['special_rota_off_doctors'],
        $this->duties_arr[$duty_date]['dis_qualified_consecutive_doctors']);
        $disqualified_doctors_saved = array_diff($this->all_doctors,$this->duties_arr[$duty_date]['assigned_doctors']);//$disqualified_doctors_saved

        $disqualified_doctors_arr = $disqualified_doctors_saved;

        $assigned = false;
        foreach($disqualified_doctors_arr as $k=>$dis_qualified_doctor_id){

            if(!$this->if_shift_doctors_completed($duty_date,$this->shifts['morning'])){

                $assigned = $this->swap_pervious_disqualified_doctor($this->shifts['morning'],$dis_qualified_doctor_id, $duty_date,$pre_date);
            }
            if(!$assigned && !$this->if_shift_doctors_completed($duty_date,$this->shifts['evening'])){
                $assigned = $this->swap_pervious_disqualified_doctor($this->shifts['evening'],$dis_qualified_doctor_id, $duty_date,$pre_date);
            }
            if(!$assigned && !$this->if_shift_doctors_completed($duty_date,$this->shifts['night'])){
                $assigned = $this->swap_pervious_disqualified_doctor($this->shifts['night'],$dis_qualified_doctor_id, $duty_date,$pre_date);
            }

        }
        return false;
    }


    public function doctors_with_assigned_duties_left($duty_date){
            $check_ava_doc = [];
            foreach($this->doctors_arr as $dr){
                if($dr['total_duties'] > $dr['assigned_duties']){
                    $check_ava_doc [] = $dr;
                }

            }
        return $check_ava_doc;
    }

    public function set_min_extra_duties_required(){
        $total_doctor_duties = 0;
        $total_duties_required = 0;

        foreach($this->doctors_arr as $doctor){
            $total_doctor_duties = $total_doctor_duties +($doctor['total_duties']-$doctor['total_requested_leaves']);
        }
        foreach( $this->duties_arr as $duty){
            $total_duties_required = $total_duties_required + $duty['total_morning_doctors']+ $duty['total_evening_doctors']+ $duty['total_night_doctors'];
        }

        if($total_duties_required > $total_doctor_duties){
            $duties_diff = $total_duties_required - $total_doctor_duties;
            $this->extra_duties_allowed = ceil($duties_diff/(sizeof($this->doctors_arr)));
        }
        else{
            $this->extra_duties_allowed = 0;
        }
        $this->extra_duties_allowed_basic = $this->extra_duties_allowed;

    }

    public function find_suitable_doctor($duty_date){
        $all_doctors = $this->all_doctors;
        shuffle($all_doctors);
        // $all_doctors = $this->sort_doctors();
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

    public function assign_duties_to_consecutive_leave_doctors($duty_date)
    {
        if(!$this->conditions['consecutive_leave_doctors']){
            return;
        }
        $consecutive_leave_doctors = $this->duties_arr[$duty_date]['consecutive_leave_doctors'];

        foreach ($consecutive_leave_doctors as $doctor_id) {

            $assigned = $this->assign_duty($duty_date, $doctor_id, $this->shifts['night']);
            if(!$assigned){
                $assigned = $this->assign_duty($duty_date, $doctor_id, $this->shifts['evening']);
            }
            if(!$assigned){
                $assigned = $this->assign_duty($duty_date, $doctor_id, $this->shifts['morning']);
            }
        }
        return $this->if_all_duties_assigned($duty_date);
    }
    public function assign_duties_to_consecutive_doctors($duty_date)
    {
        // if(!$this->conditions['consecutive_duties']){
        //     return;
        // }
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

            $assigned = $this->assign_duty($duty_date, $doctor_id, 'night');
            if (!$assigned) {
                $assigned = $this->assign_duty($duty_date, $doctor_id, 'evening');
            }
            if (!$assigned){
                $assigned = $this->assign_duty($duty_date, $doctor_id, 'morning');

            }
        }
        return $this->if_all_duties_assigned($duty_date);
    }

    public function sort_doctors($duty_date=null){
        $all_doctors = $this->doctors_arr;
        $doctors_arr_sort =[];
        $doctors_arr_sorted =[];
        $doctors_arr_down =[];
        foreach($all_doctors as $key_id=>$d){
            $doctors_arr_sort[]=[
                'sort'=>$d['total_duties'] - $d['assigned_duties'],
                'total_duties'=>$d['total_duties'],
                'assigned_duties'=> $d['assigned_duties'],
                'doctor_id'=>$key_id,
                // 'doctor'=>$d,
            ];
        }

        $sort_on_column = array_column($doctors_arr_sort, 'sort');//assigned_duties
        array_multisort($sort_on_column, SORT_DESC, $doctors_arr_sort);//SORT_ASC
        $doctors_arr_sorted = array_column($doctors_arr_sort, 'doctor_id');
        return $doctors_arr_sorted;
    }

    public function assign_doctor_duties($duty_date)
    {
        // $all_doctors = $this->doctors_arr; // sort them by duties assigned asc
        // $sort_on_column = array_column($all_doctors, 'total_duties');//assigned_duties
        // array_multisort($sort_on_column, SORT_DESC, $all_doctors);//SORT_ASC
        // $all_doctors = array_column($all_doctors, 'doctor_id');
        $all_doctors = $this->sort_doctors($duty_date);
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
            'total_duties'=>$d->total_duties + $d->extra_duties,
            'extra'=>0,
            'given_morning'=>0,
            'given_evening'=>0,
            'given_night'=>0,
            'given_general'=>0,
            'req_morning'=>$req_duties_mor,
            'req_evening'=>$req_duties_eve,
            'req_night'=>$req_duties_night,
            'req_general'=>($d->total_duties + $this->extra_duties_allowed - ($req_duties_mor+$req_duties_eve+$req_duties_night)),
            'total_leaves'=>($days - ($d->total_duties+$d->extra_duties)),
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
        $all_doctors = $this->sort_doctors();
        foreach ($all_doctors as $doctor_id) {
            $assigned= false ;
            if ($this->doctors_arr[$doctor_id]['req_night']>
                                    $this->doctors_arr[$doctor_id]['given_night']) {
                $assigned = $this->assign_duty($duty_date, $doctor_id, 'night');
            }
            if (!$assigned && $this->doctors_arr[$doctor_id]['req_evening']>$this->doctors_arr[$doctor_id]['given_evening']) {
                $assigned = $this->assign_duty($duty_date, $doctor_id, 'evening');
            }
            if (!$assigned && $this->doctors_arr[$doctor_id]['req_morning']>
                    $this->doctors_arr[$doctor_id]['given_morning']) {
                $assigned = $this->assign_duty($duty_date, $doctor_id, 'morning');
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
        }
        return $this->if_all_duties_assigned($duty_date);
    }

    public function if_all_duties_assigned($duty_date)
    {
        $no_consective_leave_doctor_left = true;
        if ($this->conditions['consecutive_leave_doctors'] ) {
            // echo "no_consective_leave_doctor_left : true";
            $all_avalible_doctors = array_diff($this->all_doctors,array_merge(
                $this->duties_arr[$duty_date]['annual_leaves'],
                $this->duties_arr[$duty_date]['regular_leaves'],
                $this->duties_arr[$duty_date]['special_rota_off_doctors'],
            ));
            $consecutive_leave_doctors_assigend = array_diff($this->duties_arr[$duty_date]['assigned_doctors'],$all_avalible_doctors);

            // $no_consective_leave_doctor_left = sizeof($consecutive_leave_doctors_assigend) == 0;
        }
        else{
            // echo "<br/>no_consective_leave_doctor_left : false";
        }
        //$duty_date == 1614988800   &&
            if( !$no_consective_leave_doctor_left){ // sizeof($this->duties_arr[$duty_date]['assigned_doctors'])>11

            }

        // $this->duties_arr[$duty_date]['dis_qualified_consecutive_doctors'] =
        //                     array_unique($this->duties_arr[$duty_date]['dis_qualified_consecutive_doctors']);


        return $no_consective_leave_doctor_left &&
                $this->if_shift_doctors_completed($duty_date,'morning') &&
                $this->if_shift_doctors_completed($duty_date,'evening') &&
                $this->if_shift_doctors_completed($duty_date,'night');
    }

    public function get_initial_duties_arr()
    {
        $duty_arr = [
        'duty_date'=>'',
        'total_morning_doctors'=>0,
        'total_evening_doctors'=>0,
        'total_night_doctors'=>0,
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
        'special_rota_evening_request'=>[],
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
        'consecutive_leave_doctors'=>[],
        'disqualified_morning_doctors'=>[],
        'disqualified_evening_doctors'=>[],
        'disqualified_night_doctors'=>[],
        'dis_qualified_consecutive_doctors'=>[],
        'disqualified_doctors'=>[],
        'qualified_doctors'=>[],
        'check_general_request'=>true,
        'dis_qualified_check_general_request'=>[],
        'disqualified_has_room_for_doctors'=>[],
        'disqualified_shift_allowed'=>[],
        'disqualified_regular_leaves'=>[],
        'disqualified_annual_leaves'=>[],
        'disqualified_already_assigned'=>[],
        'disqualified_consecutive_doctors'=>[],
        'disqualified_special_rota_off'=>[],
        'conditions'=>[],
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
        // consective_days_allowed since night are lesser thant consective days it would be safer
        $consective_days_allowed = $this->consective_nights_allowed;

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
            $all_doctors = $this->all_doctors;
            $consecutive_doctors = array_diff($day_doctors[0], $day_doctors[1]); //31dec doctor - 30dec doctor
            $this->duties_arr[$duty_date]['consecutive_doctors'] = $consecutive_doctors;
            $this->duties_arr[$duty_date]['consecutive_leave_doctors'] = array_diff($all_doctors,array_merge($day_doctors[0], $day_doctors[1]));
        }
        elseif(isset($day_doctors[0])){
            $this->duties_arr[$duty_date]['consecutive_doctors'] = $day_doctors[0];
        }
        else{
            $this->duties_arr[$duty_date]['consecutive_doctors'] = [];
        }
        foreach ($day_doctors as $day_doctor) {
            if ($day_doctor->duty_date == $pre_date && in_array($day_doctor->doctor_id, $consecutive_doctors)) {
                if ($day_doctor->shift == $this->shifts['morning']) {
                    $this->duties_arr[$duty_date]['consecutive_morning_doctors'] = $day_doctor->doctor_id;
                } elseif ($day_doctor->shift == $this->shifts['evening']) {
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
        $special_rota_request = Special_rota_request::where('duty_date', $duty_date)->with(array('doctor' => function($query)
        {
             $query->whereNull('doctor.deleted_at');
        }));
        $special_rota_morning_request = Special_rota_request::where('duty_date', $duty_date)->whereHas('doctor' , function($query){
                                                            $query->whereNull('doctor.deleted_at');
                                                    })->where('want_duty', 1)->where('shift', 'morning')->pluck('doctor_id')->toArray();
        $special_rota_evening_request = Special_rota_request::where('duty_date', $duty_date)->whereHas('doctor' ,function($query){
                                                            $query->whereNull('doctor.deleted_at');
                                                    })->where('want_duty', 1)->where('shift', 'evening')->pluck('doctor_id')->toArray();
        $special_rota_night_request = Special_rota_request::where('duty_date', $duty_date)->whereHas('doctor' ,function($query){
                                                            $query->whereNull('doctor.deleted_at');
                                                    })->where('want_duty', 1)->where('shift', 'night')->pluck('doctor_id')->toArray();
        $special_rota_off_doctors = Special_rota_request::where('duty_date', $duty_date)->whereHas('doctor' , function($query){
             $query->whereNull('doctor.deleted_at');
        })->where('want_off', 1)->pluck('doctor_id')->toArray();

        return array(
        'special_rota_morning_request'=>$special_rota_morning_request,
        'special_rota_evening_request'=>$special_rota_evening_request,
        'special_rota_night_request'=>$special_rota_night_request,
        'special_rota_off_doctors'=>$special_rota_off_doctors);
    }

    public function leave_request($duty_date)
    {
        $leave_request = Leave_Request::where('start_date','<=', $duty_date)->where('end_date','>=', $duty_date)->get();
        $regular_leaves = array();
        $annual_leaves = array();
        // $special_rota_off_doctors = [];
        foreach ($leave_request as $lr) {
            if(!isset($this->doctors_arr[$lr->doctor_id])){
                continue;
            }
            if ($lr->annual_leave == 1) {
                $annual_leaves[] = $lr->doctor_id ;
                $this->doctors_arr[$lr->doctor_id]['yearly_leaves'] =  $this->doctors_arr[$lr->doctor_id]['yearly_leaves']+1;

            } else {
                $regular_leaves[] = $lr->doctor_id ;

                $this->doctors_arr[$lr->doctor_id]['regular_leaves'] = $this->doctors_arr[$lr->doctor_id]['regular_leaves']+1 ;

            }
            $this->doctors_arr[$lr->doctor_id]['total_requested_leaves'] = $this->doctors_arr[$lr->doctor_id]['total_requested_leaves']+1;
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
            $special_rota_request = $this->special_rota_request_details($duty_date);
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
            $this->duties_arr[$duty_date]['special_rota_off_doctors'] = $special_rota_request['special_rota_off_doctors'];
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
        if(!$this->conditions['reg_and_res']){
            $this->duties['disqualified_has_room_for_doctors'] = [];
            return true;
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
            if (!$level_condition['annual_leaves']) {
                $this->duties_arr[$duty_date]['annual_leaves'] = [];
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
        $next_date = strtotime('+1 day',$duty_date);
        $duty_allowed = true;
        if(isset($this->duties_arr[$pre_date])){
            $previous_shift = $this->duties_arr[$pre_date];
            $morning_doctors = array_merge($previous_shift['assigned_morning_doctors_res'],$previous_shift['assigned_morning_doctors_reg']);
            $evening_doctors = array_merge($previous_shift['assigned_evening_doctors_res'],$previous_shift['assigned_evening_doctors_reg']);
            $night_doctors = array_merge($previous_shift['assigned_night_doctors_res'],$previous_shift['assigned_night_doctors_reg']);
            if($shift == $this->shifts['morning']){
                if(in_array($doctor_id,$evening_doctors)===true){
                    $duty_allowed = false;
                }
                if(in_array($doctor_id,$night_doctors)===true){
                    $duty_allowed = false;
                }
            }
            if($shift == $this->shifts['evening']){
                if(in_array($doctor_id,$night_doctors)===true){
                    $duty_allowed = false;
                }
            }
        }

        if(isset($this->duties_arr[$next_date])){
            $next_shift = $this->duties_arr[$next_date];
            $morning_doctors = array_merge($next_shift['assigned_morning_doctors_res'],$next_shift['assigned_morning_doctors_reg']);
            $evening_doctors = array_merge($next_shift['assigned_evening_doctors_res'],$next_shift['assigned_evening_doctors_reg']);
            $night_doctors = array_merge($next_shift['assigned_night_doctors_res'],$next_shift['assigned_night_doctors_reg']);

            if($shift == $this->shifts['night']){
                if(in_array($doctor_id,$morning_doctors)===true){
                    $duty_allowed = false;
                }
                if(in_array($doctor_id,$evening_doctors)===true){
                    $duty_allowed = false;
                }
            }
            if($shift == $this->shifts['evening']){
                if(in_array($doctor_id,$morning_doctors)===true){
                    $duty_allowed = false;
                }
            }
        }

        return $duty_allowed;
    }

    public function doctor_duty_allowed($shift, $doctor_id, $duty_date)
    {
        $duties_shift_type = Config::get('constants.duties_shift_type.'.$shift);
        $pre_date = strtotime('-1 day',$duty_date);
        $is_disqualified = in_array($doctor_id, $this->duties_arr [$duty_date]['disqualified_doctors']);
        $annual_leaves = in_array($doctor_id, $this->duties_arr [$duty_date]['annual_leaves']);
        $regular_leaves = in_array($doctor_id, $this->duties_arr [$duty_date]['regular_leaves']);
        $dis_qualified_consecutive_doctors = in_array($doctor_id, $this->duties_arr [$duty_date]['dis_qualified_consecutive_doctors']);
        $special_rota_off_doctors = in_array($doctor_id, $this->duties_arr[$duty_date]['special_rota_off_doctors']);
        $doctor_already_assigned = in_array($doctor_id, $this->duties_arr[$duty_date]['assigned_doctors']);

        $this->print_log = false;
        if($duty_date == 1614988800){
            // $this->print_log = true;
        }
        if ($is_disqualified === true) {
            if($this->print_log){
                echo "<br/> is_disqualified : ".$duty_date ." doctor_id : ".$doctor_id;
            }
            return false;
        }
        if ( $dis_qualified_consecutive_doctors === true) {//$this->conditions['dis_qualified_consecutive_doctors'] &&$shift != $this->shifts['night'] &&
             if($this->print_log){
                echo "<br/> dis_qualified_consecutive_doctors : ".$duty_date." doctor_id : ".$doctor_id;
            }
            return false;
        }
        if(!$this->shift_allowed($shift, $doctor_id, $duty_date)){
            if($this->print_log){
                echo "<br/> shift_allowed : ".$duty_date." doctor_id : ".$doctor_id;
            }
            if(!in_array($doctor_id,$this->duties_arr[$duty_date]['disqualified_shift_allowed'])){
                $this->duties_arr[$duty_date]['disqualified_shift_allowed'][]= $doctor_id;
            }
            return false;
        }
        if ($doctor_already_assigned === true) {
            if($this->print_log){
                echo "<br/> doctor_already_assigned : ".$duty_date."doctor_id : ".$doctor_id;
            }
            return false;
        }

        if ( $annual_leaves === true) {//$this->conditions['annual_leaves'] &&
            if($this->print_log){
                echo "<br/> annual_leaves : ".$duty_date." doctor_id : ".$doctor_id;
            }
            if(!in_array($doctor_id,$this->duties_arr[$duty_date]['disqualified_annual_leaves'])){
                $this->duties_arr[$duty_date]['disqualified_annual_leaves'][]= $doctor_id;
            }
            return false;
        }
        if ($this->conditions['regular_leaves'] && $regular_leaves === true) {
            if($this->print_log){
                echo "<br/> regular_leaves : ".$duty_date." doctor_id : ".$doctor_id;
            }
            if(!in_array($doctor_id,$this->duties_arr[$duty_date]['disqualified_regular_leaves'])){
                $this->duties_arr[$duty_date]['disqualified_regular_leaves'][]= $doctor_id;
            }
            return false;
        }
        else{
            if(!$regular_leaves){
                $this->duties_arr[$duty_date]['disqualified_regular_leaves']=[];

            }
        }

        if ($this->conditions['special_rota_off'] &&  $special_rota_off_doctors === true) {
            if($this->print_log){
                echo "<br/> special_rota_off : ".$duty_date." doctor_id : ".$doctor_id;
            }
            if(!in_array($doctor_id,$this->duties_arr[$duty_date]['disqualified_special_rota_off'])){
                $this->duties_arr[$duty_date]['disqualified_special_rota_off'][]= $doctor_id;
            }
            // elseif(!$special_rota_off_doctors){
            //     $this->duties_arr[$duty_date]['disqualified_special_rota_off']=[];
            // }
            return false;
        }

        if ($this->conditions['check_general_request']) {
            if ($this->doctors_arr [$doctor_id][$duties_shift_type['required_shift']]<=
                        $this->doctors_arr [$doctor_id][$duties_shift_type['given']]  &&
                        $this->doctors_arr [$doctor_id]['req_general']<=
                        $this->doctors_arr [$doctor_id]['given_general']
                ) {
                    if($this->print_log){
                         echo "<br/> check_general_request : ".$duty_date." doctor_id : ".$doctor_id;
                    }
                    if(!in_array($doctor_id,$this->duties_arr[$duty_date]['dis_qualified_check_general_request'])){
                        $this->duties_arr[$duty_date]['dis_qualified_check_general_request'][]= $doctor_id;
                    }
                return false;
            }
        }
        else{
            $this->duties_arr[$duty_date]['dis_qualified_check_general_request']=[];
        }

        $duties_allowed = $this->doctors_arr [$doctor_id]['total_duties'] + $this->extra_duties_allowed;

        if ( $duties_allowed <= $this->doctors_arr [$doctor_id]['assigned_duties']) { //$this->conditions['duties_equilibrium'] &&
            if($this->print_log){
                 echo "<br/> total duties doctor exceedes : ".$duty_date."doctor_id : ".$doctor_id;
            }
            return false ;
        }


        if (!$this->has_room_for_doctors($duty_date, $doctor_id, $shift)) {
            if($this->print_log){
                echo "<br/> has_room_for_doctors : ".$duty_date." doctor_id : ".$doctor_id;
            }
            if(!in_array($doctor_id,$this->duties_arr[$duty_date]['disqualified_has_room_for_doctors'])){
                $this->duties_arr[$duty_date]['disqualified_has_room_for_doctors'][]= $doctor_id;
            }
            return false;
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
        $this->doctors_arr [$doctor_id][ $duties_shift_type['given']]++;

        if (isset($this->duties_arr [$next_date])) {

            if ($this->duties_arr[$duty_date]['doctors_duty_num_initial'][$doctor_id] == 1) {
                $this->duties_arr [$next_date][$duties_shift_type['consecutive_doctors']][] = $doctor_id; // morning
                $this->duties_arr [$next_date]['consecutive_doctors'][] = $doctor_id;
            }

            if($this->shifts['night'] == $shift && !in_array($doctor_id,$this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'])){
                if ($this->consective_nights_allowed <= $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor_id]) {
                    $this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'][] = $doctor_id;
                }
            }
            elseif ($this->consective_days_allowed <= $this->duties_arr [$duty_date]['doctors_duty_num_initial'][$doctor_id]
                    && !in_array($doctor_id,$this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'])) {
                $this->duties_arr [$next_date]['dis_qualified_consecutive_doctors'][] = $doctor_id;
            }
            $this->duties_arr[$next_date]['consecutive_leave_doctors'] =
                                            array_diff($this->all_doctors,array_merge($this->duties_arr [$pre_date]['assigned_doctors'],
                                            $this->duties_arr [$duty_date]['assigned_doctors']));
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
        if(!$this->conditions['special_rota_duties']){
            return;
        }
        $special_rota_morning_request = $this->duties_arr[$duty_date]['special_rota_morning_request'];
        $special_rota_evening_request = $this->duties_arr[$duty_date]['special_rota_evening_request'];
        $special_rota_night_request = $this->duties_arr[$duty_date]['special_rota_night_request'];

        if (isset($special_rota_night_request)) {
            foreach ($special_rota_night_request as $special_req_doctor_id) {
                $this->assign_duty($duty_date, $special_req_doctor_id, $this->shifts['night']);
                if ($this->doctors_arr[$special_req_doctor_id]['doctor_type'] == 1) {
                    $this->duties_arr [$duty_date]['special_rota_night_doctors_res'][] = $special_req_doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_night_doctors_reg'][] = $special_req_doctor_id;
                }
            }
        }
        if (isset($special_rota_evening_request)) {
            foreach ($special_rota_evening_request as $special_req_doctor_id) {
                $this->assign_duty($duty_date, $special_req_doctor_id, $this->shifts['evening']);
                if ($this->doctors_arr[$special_req_doctor_id]['doctor_type'] == 1) {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_res'][] = $special_req_doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_evening_doctors_reg'][] = $special_req_doctor_id;
                }
            }
        }
        if (isset($special_rota_morning_request)) {
            foreach ($special_rota_morning_request as $special_req_doctor_id) {
                $this->assign_duty($duty_date, $special_req_doctor_id, $this->shifts['morning']);
                if ($this->doctors_arr[$special_req_doctor_id]['doctor_type'] == 1) {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_res'][] = $special_req_doctor_id;
                } else {
                    $this->duties_arr [$duty_date]['special_rota_morning_doctors_reg'][] = $special_req_doctor_id;
                }
            }
        }
    }

    public function reset_assigned_doctors($duty_date){

        $shifts = Config::get('constants.duties_shift_type');

        foreach($shifts as $shift){
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
        $this->reset_assigned_doctors($duty_date);
        $this->reset_assigned_duty($duty_date);
    }

    public function reset_duties_by_date_assign_consective_special_request_duties($duty_date){
        $this->reset_duties_by_date($duty_date);
        $this->assign_duties_to_consecutive_doctors($duty_date);
        $this->assign_duties_to_consecutive_leave_doctors($duty_date);
        $this->assign_duties_to_special_request_doctors($duty_date);
    }
}
