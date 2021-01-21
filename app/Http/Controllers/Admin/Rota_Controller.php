<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

class Rota_Controller extends Controller
{
    public function index()
    {
        $list = Monthly_rota::get();
        return \View::make('admin.rotalist.index', compact('list'));
    }

    public function create()
    {
        $control = 'create';
        return \View::make('admin.rotalist.create', compact('control'));
    }

    public function save(Request $request)
    {
        $doctorlist = new Monthly_rota();
        $this->add_or_update($request, $doctorlist);
        return redirect('admin/rotadoctor');
    }

    public function add_or_update(Request $request, $doctorlist)
    {
        $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
        $doctorlist->year = $request->year;
        $doctorlist->month = $request->month;
        $doctorlist->total_days = $days;
        $doctorlist->save();
        return redirect()->back();
    }

    public function generatemonthly($id)
    {
        $rota_month = Monthly_rota::find($id);
        $total_days = $rota_month->total_days;
        $last_date = strtotime($rota_month->month . '/' . $total_days . '/' . $rota_month->year);

        $rota_request = Rota_Request::where('duty_date', '=>', $rota_month)->where('duty_date', '=<', $last_date)->get();
        $doctor_arr = Doctor::get(['id', 'doctor_type_id', DB::raw("0 as total_duties")])->toArray();
        $total_doctors = sizeof($doctor_arr);
        $duty_type = Config::get('constants.duty_type');

        $doctor_index = 0;
        $data = [];
        for ($date = 1; $date <= $total_days; $date++) {
            $rota_date = strtotime($rota_month->month . '/' . $date . '/' . $rota_month->year);

            foreach ($duty_type as $dt) {
                $doctor = $doctor_arr[$doctor_index];
                // $doctor = $doctor_arr[0];

                $data[] =
                    [
                        'doctor_id' => $doctor['id'],
                        'duty_date' => $rota_date,
                        'shift' => $dt,
                        'monthly_rota_id' => $rota_month->id,
                        'doctor_type_id' => $doctor['doctor_type_id']
                    ];
                $doctor_arr[$doctor_index]['total_duties'] = $doctor_arr[$doctor_index]['total_duties'] + 1;
                $doctor_index++;
                if ($total_doctors == $doctor_index) {
                    $doctor_arr = $this->sort_asc_array($doctor_arr, 'total_duties');
                    $doctor_index = 0;
                }
            }
        }
        Rota::insert($data);
        dd('saved');
    }
    public function get_duties_info($rota_generate_patterns)
    {
        $duties_arr = [];
        foreach ($rota_generate_patterns as $rota_generate_pattern_key => $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;
            $consecutive_doctors_arr = [];
            if ($rota_generate_pattern_key == 0) {
                $consecutive_doctors = Doctor::join('rota', function ($j) use ($duty_date) {
                    return $j->where('doctor.id', 'rota.doctor_id')
                            ->where('duty_date', '!=', strtotime('-2 day', $duty_date));
                })->where('duty_date', strtotime('-1 day', $duty_date));
        
                $consecutive_morning_doctors_arr = $consecutive_doctors->pluck('doctor_id')->toArray();
                $consecutive_evening_doctors_arr = $consecutive_doctors->pluck('doctor_id')->toArray();
                $consecutive_night_doctors_arr = $consecutive_doctors->pluck('doctor_id')->toArray();
                $consecutive_doctors_arr = array_merge(
                    $consecutive_morning_doctors_arr,
                    $consecutive_evening_doctors_arr,
                    $consecutive_night_doctors_arr
                );
            }
            $duty_date = $rota_generate_pattern->duty_date;
            $special_rota_request = Special_rota_request::where('duty_date', $duty_date);
            // $special_rota_morning_request = $special_rota_request->where('want_duty', 1)->where('shift', 'morning')->pluck('doctor_id');
            $special_rota_morning_request = $special_rota_request->where('want_duty', 1)->where('shift', 'morning')->get();
            $special_rota_evening_request = $special_rota_request->where('want_duty', 1)->where('shift', 'evening')->get();
            $special_rota_night_request = $special_rota_request->where('want_duty', 1)->where('shift', 'night')->get();
            $special_rota_want_off = $special_rota_request->where('want_off', 1)->pluck('doctor_id')->toArray();
            $annual_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 1)->pluck('id')->toArray();
            $regular_leaves = Leave_Request::where('duty_date', $duty_date)->where('annual_leave', 0)->pluck('id')->toArray();
            $all_leaves = array_merge($annual_leaves, $regular_leaves, $special_rota_want_off);
            $all_diss_qualified_doctors = $all_leaves;
            $duties_arr [$duty_date]= [
                    'duty_date'=>$duty_date,
                    'total_morning_doctors'=>$rota_generate_pattern->total_morning_doctors,
                    'total_evening_doctors'=>$rota_generate_pattern->total_evening_doctors,
                    'total_night_doctors'=>$rota_generate_pattern->total_night_doctors,
                    'annual_leave'=>$annual_leaves,
                    'regular_leaves'=>$regular_leaves,
                    'all_leaves'=>$all_leaves,
                    'special_rota_morning_doctors_res'=>[],
                    'special_rota_morning_doctors_reg'=>[],
                    'special_rota_evening_doctors'=>$special_rota_evening_request,
                    'special_rota_night_doctors'=>$special_rota_night_request,
                    'special_rota_off_doctors'=>$special_rota_want_off,
                    'assigned_morning_doctors_res'=> [],
                    'assigned_evening_doctors_res'=>[],
                    'assigned_night_doctors_res'=>[],
                    'assigned_morning_doctors_reg'=>[],
                    'assigned_evening_doctors_reg'=>[],
                    'assigned_night_doctors_reg'=>[],
                    'consecutive_doctors'=>$consecutive_doctors_arr,
                    'consecutive_morning_doctors_arr'=>$consecutive_morning_doctors_arr,
                    'consecutive_evening_doctors_arr'=>$consecutive_evening_doctors_arr,
                    'consecutive_night_doctors_arr'=>$consecutive_night_doctors_arr,
                    'qualified_doctors'=>[],
                    'qualified_morning_doctors'=>[],
                    'qualified_evening_doctors'=>[],
                    'qualified_night_doctors'=>[],
                    'diss_qualified_doctors'=>$all_diss_qualified_doctors,
                ];
                if(isset($special_rota_morning_request)){
                    foreach($special_rota_morning_request as $special_req){
                        if($special_req->doctor->doctor_type == 1){
                            $duties_arr [$duty_date]['special_rota_morning_doctors_res'][] = $special_req->doctor_id;
                            if(sizeof($duties_arr [$duty_date]['assigned_morning_doctors_res'] <    // room for special_rota_morning_doctors_reg
                                    $rota_generate_pattern->total_morning_doctors )){   
                                $duties_arr [$duty_date]['assigned_morning_doctors_res'][] = $special_req->doctor_id;
                            }                            
                        }
                        else{
                            $duties_arr [$duty_date]['special_rota_morning_doctors_reg'][] = $special_req->doctor_id;
                            if(sizeof($duties_arr [$duty_date]['assigned_morning_doctors_reg'] <    // room for assigned_morning_doctors_res
                                    $rota_generate_pattern->total_morning_doctors )){   
                                $duties_arr [$duty_date]['assigned_morning_doctors_reg'][] = $special_req->doctor_id;
                            }  
                        }
                    }
                }
                
                   
                    // 'assigned_evening_doctors_res'=>[],
                    // 'assigned_night_doctors_res'=>[],
                    // 'assigned_morning_doctors_reg'=>[],
                    // 'assigned_evening_doctors_reg'=>[],
                    // 'assigned_night_doctors_reg'=>[],
                    // 'consecutive_doctors'=>$consecutive_doctors_arr,
                    // 'consecutive_morning_doctors_arr'=>$consecutive_morning_doctors_arr,
                    // 'consecutive_evening_doctors_arr'=>$consecutive_evening_doctors_arr,
                    // 'consecutive_night_doctors_arr'=>$consecutive_night_doctors_arr,
                    // 'qualified_doctors'=>[],
                    // 'qualified_morning_doctors'=>[],
                    // 'qualified_evening_doctors'=>[],
                    // 'qualified_night_doctors'=>[],
                    // 'diss_qualified_doctors'=>$all_diss_qualified_doctors,
                


        }
        return $duties_arr;
    }


    public function generate($id)
    {
        $rota = Rota::where('monthly_rota_id', $id)->orderBy('duty_date', 'asc')->get();
        $monthly_rota = Monthly_rota::find($id);

        if (true) {//if(!$list->count()){
            $find_doctors_level = Config::get('constants.find_doctors_level');
            $rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota_id)
                                                        ->orderBy('duty_date', 'asc')->get();
            $doctors_arr = $this->get_doctors_rotareq_details($monthly_rota, $rota_generate_patterns);
        
            $duties_arr = $this->get_duties_info($rota_generate_patterns);

            $generated_rota = $this->generate_rota($rota_generate_patterns, $doctors_arr, $duties_arr);

            
            Rota::insert($data);
        }
        // $duty_date = strtotime("1-".$monthly_rota->month."-".$monthly_rota->year);
        $start_weekday = date('w', $rota[0]->duty_date);
        $weekdays = Config::get('constants.weekdays_num');
        return view('admin.doctor_calender.index', compact('list', 'start_weekday', 'weekdays', 'doctors'));
    }


    public function get_doctors_rotareq_details($monthly_rota, $rota_generate_patterns)
    {
        $days = $monthly_rota->total_days;
        $monthly_rota_id = $monthly_rota->id;
        
        $shifts = Config::get('constants.duty_type');

        $doctors = Doctor::get();
        $doctors_arr = [];
        foreach ($doctors as $d) {
            $req_duties_mor = General_rota_request::where('doctor_id', $d->id)->where('shift', $shifts['morning']);
            $req_duties_eve = General_rota_request::where('doctor_id', $d->id)->where('shift', $shifts['evening']);
            $req_duties_night = General_rota_request::where('doctor_id', $d->id)->where('shift', $shifts['night']);
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
            'holiday_leaves'=>0 // if he already got sat sun off
            ];
        }
        return $doctors_arr;
    }

    public function generate_rota($rota_generate_patterns, $doctors_arr, $duties_arr)
    {
        $find_doctors_level_index = 1;
             
        foreach ($rota_generate_patterns as $rota_generate_pattern) {
            $duty_date = $rota_generate_pattern->duty_date;

            $doctors_index = 0;
            [$doctors_arr,$doctors] = $this->get_suitable_doctor(
                $doctors_arr,
                $duties_arr,
                $duty_date,
                $shifts['morning'],
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
                    [$doctors_arr,$doctors] = $this->get_suitable_doctor(
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


    public function get_suitable_doctor($doctors_arr, $duties_arr, $duty_date, $shift, $level)
    {
        $doctor = $this->select_doctor_general_rota($doctors_arr, $doctors_id, $duty_date, $shift);
        if ($doctor) {
            return $doctor;
        }
        $doctor = $this->select_doctor_rota($doctors_arr, $doctors_id, $duty_date, $shift);


        // dd( $doct );
        return $doct;
    }
   

    public function select_doctor_general_rota($doctors_arr, $doctors_id, $duty_date, $shift)
    {
        foreach ($doctors_arr as $doctor) {
            if ($shift == 'morning') {
                if ($doctor['req_morning']<$doctor['given_morning']) {
                    return $doctor;
                }
            } elseif ($shift == 'evening') {
                if ($doctor['req_evening']<$doctor['given_evening']) {
                    return $doctor;
                }
            } else {
                if ($doctor['req_night']<$doctor['given_night']) {
                    return $doctor;
                }
            }
        }
    }
    public function select_doctor_rota($doctors_arr, $doctors_id, $duty_date, $shift)
    {
    }
}
