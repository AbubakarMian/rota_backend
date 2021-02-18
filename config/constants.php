<?php

return [

	'status' => [
		'OK' => 200
	],

	'app-type' => [
		'android' => "rota-app-mobile",
    ],

	'social_login' => [
		'facebook'=>'facebook',
		'twitter'=>'twitter',
		'gmail'=>'gmail',
	],

    'ajax_action'=>[
        'create'=>'create',
        'update'=>'update',
        'delete'=>'delete',
        'error'=>'error',
        'success'=>'success',
    ],
    'duty_type'=>[
        'morning'=>'morning',
        'evening'=>'evening',
        'night'=>'night'
    ],
    'duty_type_request'=>[
        'morning'=>'Morning',
        'evening'=>'Evening',
        'night'=>'Night',
        // 'general'=>'General',
    ],
    'weekdays'=>[
        'sunday'=>'Sunday',
        'monday'=>'Monday',
        'tuesday'=>'Tuesday',
        'wednesday'=>'Wednesday',
        'thursday'=>'Thursday',
        'friday'=>'Friday',
        'saturday'=>'Saturday'
    ],
    'weekdays_num'=>[
        '1'=>'Sunday',
        '2'=>'Monday',
        '3'=>'Tuesday',
        '4'=>'Wednesday',
        '5'=>'Thursday',
        '6'=>'Friday',
        '7'=>'Saturday',

    ],
    'find_doctors_level'=>[
        1=>'1',
        2=>'2',
        3=>'3',
    ],
    'doctor_type'=>[
        '1'=>'resident',
        '2'=>'registrar',
    ],
    'duties_shift_type'=>[
        'morning'=>[
            'total_doctors'=>'total_morning_doctors',
            'special_rota_doctors_res'=>'special_rota_morning_doctors_res',
            'special_rota_doctors_reg'=>'special_rota_morning_doctors_reg',
            'special_rota_request'=>'special_rota_morning_request',
            'assigned_doctors_res'=>'assigned_morning_doctors_res',
            'assigned_doctors_reg'=>'assigned_morning_doctors_reg',
            'consecutive_doctors'=>'consecutive_morning_doctors',
            'qualified_doctors'=>'qualified_morning_doctors',
            'given'=>'given_morning',
            'required_shift'=>'req_morning',
            'has_ucc'=>'has_morning_ucc',
        ],
        'evening'=>[
            'total_doctors'=>'total_evening_doctors',
            'special_rota_doctors_res'=>'special_rota_evening_doctors_res',
            'special_rota_doctors_reg'=>'special_rota_evening_doctors_reg',
            'special_rota_request'=>'special_rota_evening_request',
            'assigned_doctors_res'=>'assigned_evening_doctors_res',
            'assigned_doctors_reg'=>'assigned_evening_doctors_reg',
            'consecutive_doctors'=>'consecutive_evening_doctors',
            'qualified_doctors'=>'qualified_evening_doctors',
            'given'=>'given_evening',
            'required_shift'=>'req_evening',
            'has_ucc'=>'has_evening_ucc',
        ],
        'night'=>[
            'total_doctors'=>'total_night_doctors',
            'special_rota_doctors_res'=>'special_rota_night_doctors_res',
            'special_rota_doctors_reg'=>'special_rota_night_doctors_reg',
            'special_rota_request'=>'special_rota_night_request',
            'assigned_doctors_res'=>'assigned_night_doctors_res',
            'assigned_doctors_reg'=>'assigned_night_doctors_reg',
            'consecutive_doctors'=>'consecutive_night_doctors',
            'qualified_doctors'=>'qualified_night_doctors',
            'given'=>'given_night',
            'required_shift'=>'req_night',
            'has_ucc'=>'has_night_ucc',
        ],
    ],
    'rules'=>[
        'level-1'=>[
            'annual_leave'=> true,
            'regular_leaves'=> true,
            'special_off_requests'=> true,
            'special_shift_requests'=> true,
            'consecutive_doctors'=> true,
            'extra_duties'=> 3,
            'general_shift_requests'=> true,
        ],

        'level-2'=>[
            'annual_leave'=> true,
            'regular_leaves'=> true,
            'special_off_requests'=> false,
            'special_shift_requests'=> false,
            'consecutive_doctors'=> true,
            'extra_duties'=> 5,
            'general_shift_requests'=> false,
        ],

        'level-3'=>[
            'annual_leave'=> true,
            'regular_leaves'=> false,
            'special_off_requests'=> false,
            'special_shift_requests'=> false,
            'consecutive_doctors'=> true,
            'extra_duties'=> 7,
            'general_shift_requests'=> false,
        ]
    ],
    'conditions'=>[
        'check_general_request'=>true,
        'special_rota_off'=>true,
        'consecutive_leave_doctors'=>true,
        'special_rota_duties'=>true,
        'regular_leaves'=>true,
        'duties_equilibrium'=>true,
        'dis_qualified_consecutive_doctors'=>true,
        'annual_leaves'=>true,
    ]
];
