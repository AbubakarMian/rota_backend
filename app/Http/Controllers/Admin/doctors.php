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


TRUNCATE temp_rota;
TRUNCATE rota_generate_pattern;
TRUNCATE temp_monthly_rota_doctors;
TRUNCATE temp_rota_detail;
TRUNCATE rota_request;
// TRUNCATE leave_request;
// TRUNCATE general_rota_request;
// TRUNCATE special_rota_request;
TRUNCATE doctor;


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

