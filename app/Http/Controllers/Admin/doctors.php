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
            'req_morning'=>$req_duties_mor,
            'req_evening'=>$req_duties_eve,
            'req_night'=>$req_duties_night,
            'total_leaves'=>($days - $d->total_duties),
            'assigned_leaves'=>0,
            'holiday_leaves'=>0, // if he already got sat sun off,
            // 'duties_assigned_dates'=>[]
            ];
$duties_arr [$duty_date]= [
            'duty_date'=>$duty_date,
            'doctors_consective_duties_num'=>[[5=>1,7=>3]],// doctor_id=>duties_count
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
            'all_assigned_doctors'=> [],
            'assigned_morning_doctors_res'=> [],
            'assigned_morning_doctors_reg'=>[],
            'assigned_evening_doctors_res'=>[],
            'assigned_evening_doctors_reg'=>[],
            'assigned_night_doctors_res'=>[],
            'assigned_night_doctors_reg'=>[],
            'consecutive_doctors'=>$consecutive_doctors_arr, // only one duty of yesterday assigned
            'consecutive_morning_doctors_arr'=>$consecutive_morning_doctors_arr,
            'consecutive_evening_doctors_arr'=>$consecutive_evening_doctors_arr,
            'consecutive_night_doctors_arr'=>$consecutive_night_doctors_arr,
            'qualified_doctors'=>[],
            'qualified_morning_doctors'=>[],
            'qualified_evening_doctors'=>[],
            'qualified_night_doctors'=>[],
            'diss_qualified_doctors'=>$all_diss_qualified_doctors,
];

// update diss_qualified_doctors on assign doctor
// update doctors_consective_duties_num on assign doctor
// update duties_assigned_dates
// count consective duties
// update requsetd gereral rota given_morning on assign doctor
// add check for gereral rota  given_shifts
// make reset $duties_arr [$duty_date] 
