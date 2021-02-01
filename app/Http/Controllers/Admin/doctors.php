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
            'doctors_duty_num_initial'=>$doctors_duty_num_initial,[[5=>1,7=>3]],// doctor_id=>duties_count
            'dis_qualified_consecutive_doctors'=>[],// doctor_id=>duties_count
            'disqualified_morning_doctors'=>[],
            'disqualified_evening_doctors'=>[],
            'disqualified_night_doctors'=>[],
            'diss_qualified_doctors'=>$all_diss_qualified_doctors,
            'check_general_request'=>true,
];

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



// get all doctors
// is doctor valid
// if doctor is already give duty on that day


$has_room = $this->has_room_for_doctors(
    $rota_generate_pattern->total_morning_doctors,
    $this->duties_arr[$duty_date]['assigned_morning_doctors_reg']
);



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
