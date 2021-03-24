<?php
$doctors_arr[$d->id] = [
            'doctor_id'=>$d->id,
            'doctor'=>$d,
            'doctor_type'=>$d->type,
            'total_duties'=>$d->total_duties,
            'extra'=>0,
            // 'consecutive_duties'=>0,
            'given_morning'=>0,
            'given_evening'=>0,
            'given_night'=>0,
            'given_general'=>0,
            'req_morning'=>$req_duties_mor,
            'req_evening'=>$req_duties_eve,
            'req_night'=>$req_duties_night,
            'req_general'=>($d->total_duties - ($req_duties_mor+$req_duties_eve+$req_duties_night)),
            'total_leaves'=>($days - $d->total_duties),
            'assigned_leaves'=>0,
            'yearly_leaves'=>0,
            'regular_leaves'=>0,
            'total_requested_leaves'=>0,
            'total_assigned_duties'=>0,
            'holiday_leaves'=>0, // if he already got sat sun off,
            'duties_assigned_dates'=>[]
            ];
            $this->general_request_allowed($doctor_id,$shift)
            assign_general_duties
            assign_doctors_by_general_requests($doctors_arr,$duty_date)
$duties_arr [$duty_date]= [
            'duty_date'=>$duty_date,
            'total_morning_doctors'=>$rota_generate_pattern->total_morning_doctors,
            'total_evening_doctors'=>$rota_generate_pattern->total_evening_doctors,
            'total_night_doctors'=>$rota_generate_pattern->total_night_doctors,
            'has_morning_ucc'=>$rota_generate_pattern->has_morning_ucc,
            'has_evening_ucc'=>$rota_generate_pattern->has_evening_ucc,
            'has_night_ucc'=>$rota_generate_pattern->has_night_ucc,
            'annual_leaves'=>$annual_leaves,
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
            'all_assigned_doctors'=> [],
            'assigned_morning_doctors_res'=> [],
            'assigned_morning_doctors_reg'=>[],
            'assigned_evening_doctors_res'=>[],
            'assigned_evening_doctors_reg'=>[],
            'assigned_night_doctors_res'=>[],
            'assigned_night_doctors_reg'=>[],
            'consecutive_doctors'=>$consecutive_doctors_arr, // only one duty of yesterday assigned
            'consecutive_morning_doctors'=>$consecutive_morning_doctors_arr,
            'consecutive_evening_doctors'=>$consecutive_evening_doctors_arr,
            'consecutive_night_doctors_arr'=>$consecutive_night_doctors_arr,
            'doctors_duty_num_initial'=>$doctors_duty_num_initial,[[5=>1,7=>3]],// doctor_id=>duties_count
            'dis_qualified_consecutive_doctors'=>[],// doctor_id=>duties_count
            'disqualified_morning_doctors'=>[],
            'disqualified_evening_doctors'=>[],
            'disqualified_night_doctors'=>[],
            'diss_qualified_doctors'=>$all_diss_qualified_doctors,
            'check_general_request'=>true,
            'disqualified_has_room_for_doctors'=>[],
            'disqualified_shift_allowed'=>[],
            'disqualified_regular_leaves'=>[],
            'disqualified_annual_leaves'=>[],
            'disqualified_already_assigned'=>[],
            'disqualified_consecutive_doctors'=>[],
];

// doctor_duty_allowed

// problem date 1615248000,1615161600

cons leave
0 => 2
1 => 3
2 => 4
7 => 13
8 => 14
13 => 21
15 => 24

if($duty_date == 1615161600 ){
    dd($duty_date);
}


if($duty_date == 1614902400 && !in_array($doctor_id,[3,4,13,14,2,21])){
    dd($doctor_id);
}


// update diss_qualified_doctors on assign doctor
// update doctors_consective_duties_arr on assign doctor
// update duties_assigned_dates
// count consective duties
// update requsetd gereral rota given_morning on assign doctor
// add check for gereral rota  given_shifts
// make reset $duties_arr [$duty_date]
// move above arra index 0
// add pre date
// get duties_arr from function


// $2y$10$5khvtNrxHhxHXrySnw5UheJiAmy63iREf1HpFohUUfou0BjMUFfJ.

DELETE FROM `rota_generate_pattern` WHERE `rota_generate_pattern`.`id` != 0;
DELETE FROM `temp_monthly_rota_doctors` WHERE `temp_monthly_rota_doctors`.`id` != 0;



TRUNCATE temp_rota;
TRUNCATE rota_generate_pattern;
TRUNCATE temp_monthly_rota_doctors;
TRUNCATE temp_rota_detail;
TRUNCATE rota_request;
TRUNCATE rota;
TRUNCATE monthly_rota;
// TRUNCATE leave_request;
// TRUNCATE general_rota_request;
// TRUNCATE special_rota_request;


TRUNCATE `users`;
TRUNCATE `doctor`;
TRUNCATE `doctor_type`;
TRUNCATE `rota`;
TRUNCATE `rota_detail`;
TRUNCATE `rota_generate_pattern`;
TRUNCATE `rota_request`;
TRUNCATE `special_rota_request`;
TRUNCATE `temp_monthly_rota_doctors`;
TRUNCATE `temp_rota`;
TRUNCATE `temp_rota_detail`;
TRUNCATE `duties`;
TRUNCATE `failed_jobs`;
TRUNCATE `general_rota_request`;
TRUNCATE `leave_request`;
TRUNCATE `migrations`;
TRUNCATE `monthly_rota`;
TRUNCATE `monthly_rota_doctors`;
TRUNCATE `password_resets`;
TRUNCATE `weekday`;

drop TABLE `doctor`;
drop TABLE `doctor_type`;
drop TABLE `duties`;
drop TABLE `failed_jobs`;
drop TABLE `general_rota_request`;
drop TABLE `leave_request`;
drop  TABLE `migrations`;
drop  TABLE `monthly_rota`;
drop  TABLE `monthly_rota_doctors`;
drop  TABLE `password_resets`;
drop  TABLE `rota`;
drop  TABLE `rota_detail`;
drop TABLE  `rota_generate_pattern`;
drop  TABLE `rota_request`;
drop TABLE  `special_rota_request`;
drop  TABLE `temp_monthly_rota_doctors`;
drop TABLE  `temp_rota`;
drop  TABLE `temp_rota_detail`;
drop  TABLE `users`;
drop TABLE  `weekday`;


            // if($duty_date == 1614643200  ){
            //     dd($shift);
            // }


            if($duty_date == 1614988800 && sizeof($this->duties_arr[1614988800]['assigned_doctors'])>9 ){
                dd($this->duties_arr);
            }


consective duties max 4
leave request

dev : $2y$10$Znz.iBnscgguOMl6K49pJOQth14Z0YrePxGLbRe77IoE3k0NI8Hj.
local : $2y$10$5khvtNrxHhxHXrySnw5UheJiAmy63iREf1HpFohUUfou0BjMUFfJ.

1614816000

when selected from previous month select general duty

public function rearrange_duties_according_to_general_requests($duty_date){
    $all_doctors_assigned = $this->duties_arr[$duty_date]['assigned_doctors'];
    $this->conditions['check_general_request'] = true;
    $all_duties_not_assigned_doctors = $all_doctors_assigned ;
    $doctors_shift_allowed = [];
    $one_shift = [];
    $two_shift = [];
    $three_shift = [];
    $total_shift_doctors_left = [
        'morning'=>0,
        'evening'=>0,
        'night'=>0,
    ];

    foreach($all_doctors_assigned as $doctor_id){
        $this->remove_assign_doctor($duty_date, $doctor_id );
    }

    foreach($all_doctors_assigned as $doctor_id){
        $shift_marked = false;
        foreach($this->shifts as $shift){
            if($this->doctor_duty_allowed($shift, $doctor_id, $duty_date)){
                $doctors_shift_allowed[$doctor_id][] = $shift;
                $total_shift_doctors_left[$shift] = $total_shift_doctors_left[$shift]++;
                $shift_marked = true;
            }
        }
        if(!$shift_marked){
            $doctors_shift_allowed[$doctor_id][] = $this->shifts['morning'];
            $doctors_shift_allowed[$doctor_id][] = $this->shifts['evening'];
            $doctors_shift_allowed[$doctor_id][] = $this->shifts['night'];
            $total_shift_doctors_left['morning'] = $total_shift_doctors_left['morning']++;
            $total_shift_doctors_left['evening'] = $total_shift_doctors_left['evening']++;
            $total_shift_doctors_left['night'] = $total_shift_doctors_left['night']++;
        }
    }
    foreach($doctors_shift_allowed as $doctor_id=>$shifts_arr){
        if(sizeof($shifts_arr) == 1){
            $one_shift[] = $doctor_id;
        }
        elseif(sizeof($shifts_arr) == 2){
            $two_shift[] = $doctor_id;
        }
        else{// size is 3
            $three_shift[] = $doctor_id;
        }
    }
    // $this->print_log = true;
     // if general request is not possible
    foreach($one_shift as $one_shift_doctor_id){
        $assigned = $this->assign_duty($duty_date, $one_shift_doctor_id, $doctors_shift_allowed[$one_shift_doctor_id][0]);
        $total_shift_doctors_left[$doctors_shift_allowed[$one_shift_doctor_id][0]]--;
    }

    if($this->conditions['reg_and_res']){
        $doctor_types = [1,2];
    }
    else{
        $doctor_types = [];
    }
    $doctor_types = [1,2];
    $shifts['night'] = $this->shifts['night'];
    $shifts['evening'] = $this->shifts['evening'];
    $shifts['morning'] = $this->shifts['morning'];
    foreach($shifts as $shift){
        foreach($doctor_types as $doctor_type){
            $has_least_number_of_doctors = $this->if_shift_has_least_doctor_type($duty_date, $shift,$doctor_type);
            if(!$has_least_number_of_doctors){
                foreach($two_shift as $two_shift_key=>$two_shift_doctor_id){
                    if(in_array($shift,$doctors_shift_allowed[$two_shift_doctor_id])){
                        $assigned = $this->assign_doctor($shift, $two_shift_doctor_id, $duty_date);
                        if($assigned){
                            unset($two_shift[$two_shift_key]);
                        }
                        $has_least_number_of_doctors = $this->if_shift_has_least_doctor_type($duty_date, $shift,$doctor_type);
                        if($has_least_number_of_doctors){
                            break;
                        }
                    }
                }
            }
            $has_least_number_of_doctors = $this->if_shift_has_least_doctor_type($duty_date, $shift,$doctor_type);
                if(!$has_least_number_of_doctors){
                    foreach($three_shift as $three_shift_key=>$three_shift_doctor_id){
                        if(in_array($shift,$doctors_shift_allowed[$three_shift_doctor_id])){
                            $assigned = $this->assign_doctor($shift, $three_shift_doctor_id, $duty_date);
                            if($assigned){
                                unset($three_shift[$three_shift_key]);
                            }
                            $has_least_number_of_doctors = $this->if_shift_has_least_doctor_type($duty_date, $shift,$doctor_type);
                            if($has_least_number_of_doctors){
                                break;
                            }
                        }
                    }
                }
        }
    }
    if($this->conditions['reg_and_res']){
        // dd('least doctor shift',$this->duties_arr,$this->duties_arr[$duty_date],$doctors_shift_allowed,$all_doctors_assigned,$two_shift);
    }
    $this->assign_doctors_by_general_requests($two_shift,$duty_date);

        foreach($two_shift as $two_shift_key=>$two_shift_doctor_id){
            // $this->assign_doctors_by_general_requests($doctors_arr,$duty_date);

            // if(in_array('night',$doctors_shift_allowed[$two_shift_doctor_id])){
            //     $assigned = $this->assign_doctor('night', $two_shift_doctor_id, $duty_date);
            // }

            // if(!$assigned && in_array('evening',$doctors_shift_allowed[$two_shift_doctor_id])){
            //     $assigned = $this->assign_doctor('evening', $two_shift_doctor_id, $duty_date);
            // }

            // if(!$assigned && in_array('morning',$doctors_shift_allowed[$two_shift_doctor_id])){
            //     $assigned = $this->assign_doctor('morning', $two_shift_doctor_id, $duty_date);
            // }

            if(!$assigned){
                $assigned = $this->assign_duty_to_any_avalible_shift( $duty_date,$two_shift_doctor_id);
            }
            if(!$assigned){
                // dd('error',$this->duties_arr,$this->duties_arr[$duty_date],$doctors_shift_allowed,$all_doctors_assigned,$two_shift);
            }

                // $this->assign_duty_to_any_avalible_shift( $duty_date,$two_shift_doctor_id);
                unset($two_shift[$two_shift_key]);
        }
        // $this->print_log = false;
        $this->assign_doctors_by_general_requests($three_shift,$duty_date);
        foreach($three_shift as $three_shift_key=>$three_shift_doctor_id){

            // if(in_array('night',$doctors_shift_allowed[$three_shift_doctor_id])){
            //     $assigned = $this->assign_doctor('night', $three_shift_doctor_id, $duty_date);
            // }

            // if(!$assigned && in_array('evening',$doctors_shift_allowed[$three_shift_doctor_id])){
            //     $assigned = $this->assign_doctor('evening', $three_shift_doctor_id, $duty_date);
            // }

            // if(!$assigned && in_array('morning',$doctors_shift_allowed[$three_shift_doctor_id])){
            //     $assigned = $this->assign_doctor('morning', $three_shift_doctor_id, $duty_date);
            // }

                // $this->assign_duty_to_any_avalible_shift( $duty_date,$three_shift_doctor_id);
                unset($three_shift[$three_shift_key]);
        }
        $this->conditions['check_general_request'] = false;
    $this->assign_doctors_from_shift_not_allowed($duty_date);
    if(!$this->if_all_duties_assigned($duty_date)){
        // dd($this->if_all_duties_assigned($duty_date),$this->duties_arr[$duty_date],$doctors_shift_allowed,$all_doctors_assigned,$one_shift,$two_shift,$three_shift);
    }
    return $this->if_all_duties_assigned($duty_date);
}
