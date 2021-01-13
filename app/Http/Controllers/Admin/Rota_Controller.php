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
        return \View::make('admin.rotalist.create',compact('control'));
    }

    public function save(Request $request)
    {
        $doctorlist = new Monthly_rota();
        $this->add_or_update($request, $doctorlist);
        return redirect('admin/rotadoctor');
    }

    public function add_or_update(Request $request, $doctorlist)
    {
        $doctorlist->year = $request->year;
        $doctorlist->month = $request->month;
        $doctorlist->save();
        return redirect()->back();
    }



    public function generatemonthly($id)
    {
        $rota_month = Monthly_rota::find($id);
        $total_days = cal_days_in_month(CAL_GREGORIAN, $rota_month->month, $rota_month->year);
        $last_date = strtotime($rota_month->month . '/' . $total_days . '/' . $rota_month->year);

        $rota_request = Rota_Request::where('duty_date', '=>', $rota_month)->where('duty_date', '=<', $last_date)->get();
        $doctor_arr = Doctor::get(['id', 'doctor_type_id', DB::raw("0 as total_duties")])->toArray();

        // $doctor_arr->random();
        // $random = $collection->random(3);
        // dd($doctor_arr[0]->doctor_type_id );
        // $doctor_arr->put('price', 100);
        // dd($doctor_arr);

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
                    if($total_doctors == $doctor_index){
                        $doctor_arr = $this->sort_asc_array($doctor_arr,'total_duties');
                        $doctor_index = 0;
                    }

            }
        }
        // dd($doctor_arr);
        Rota::insert($data);
        dd('saved');
    }



    public function generate($id){

        $list = Rota::where('monthly_rota_id',$id)->orderBy('duty_date','asc')->get();
        $monthly_rota = Monthly_rota::find($id);

      if($list->count()){
        $days = cal_days_in_month(CAL_GREGORIAN,$monthly_rota->month,$monthly_rota->year);
        $rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id',$id)->orderBy('duty_date','asc')->get();
        $shifts = Config::get('constants.duty_type');
        $find_doctors_level = Config::get('constants.find_doctors_level');

        $doctors = Doctor::get();
        $doctors_arr = [];
        foreach($doctors as $d){
            $req_duties_mor = General_rota_request::where('doctor_id',$d->id)->where('shift',$shifts['morning']);
            $req_duties_eve = General_rota_request::where('doctor_id',$d->id)->where('shift',$shifts['evening']);
            $req_duties_night = General_rota_request::where('doctor_id',$d->id)->where('shift',$shifts['night']);
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
                'holiday_leaves'=>0
            ];
        }

    $duties_arr = [];
   foreach($rota_generate_patterns as $rota_generate_pattern){

    $duty_date = $rota_generate_pattern->duty_date;
    $special_rota_request = Special_rota_request::where('duty_date',$duty_date)->get();

    if(!isset($special_rota_request->duty_date)){
        $duties_arr [$special_rota_request->duty_date]= [
            'duty_date'=>$special_rota_request->duty_date,
            'special_rota_morning_doctors'=>[],
            'special_rota_evening_doctors'=>[],
            'special_rota_night_doctors'=>[],
            'special_rota_off_doctors'=>[],
            'assigned_morning_doctors_res'=>[],
            'assigned_evening_doctors_res'=>[],
            'assigned_night_doctors_res'=>[],
            'assigned_morning_doctors_reg'=>[],
            'assigned_evening_doctors_reg'=>[],
            'assigned_night_doctors_reg'=>[],
            'consecutive_doctors'=>[],
            'qualified_doctors_doctors'=>[],
            'non_qualified_doctors_doctors'=>[],
        ];
    }

    if($special_rota_request->wantoff ){
        $duties_arr [$special_rota_request->duty_date]['special_rota_off_doctors'][]=
            $special_rota_request->doctor_id;
    }

    else if($special_rota_request->shift == $shifts['morning']){
        $duties_arr [$special_rota_request->duty_date]['special_rota_morning_doctors'][]=
            $special_rota_request->doctor_id;
            if($doctors_arr[$special_rota_request->doctor_id]['doctor_type'] == 1){
                $duties_arr [$special_rota_request->duty_date]['assigned_morning_doctors_res'][]=
                $special_rota_request->doctor_id;
            }
            else{
                $duties_arr [$special_rota_request->duty_date]['assigned_morning_doctors_res'][]=
                $special_rota_request->doctor_id;
            }

    }

    else if($special_rota_request->shift == $shifts['evening']){
        $duties_arr [$special_rota_request->duty_date]['special_rota_evening_doctors'][]=
            $special_rota_request->doctor_id;

            if($doctors_arr[$special_rota_request->doctor_id]['doctor_type'] == 1){
                $duties_arr [$special_rota_request->duty_date]['assigned_evening_doctors_res'][]=
                $special_rota_request->doctor_id;
            }
            else{
                $duties_arr [$special_rota_request->duty_date]['assigned_evening_doctors_reg'][]=
                $special_rota_request->doctor_id;
            }
    }

    else if($special_rota_request->shift == $shifts['night']){
        $duties_arr [$special_rota_request->duty_date]['special_rota_night_doctors'][]=
            $special_rota_request->doctor_id;

            if($doctors_arr[$special_rota_request->doctor_id]['doctor_type'] == 1){
                $duties_arr [$special_rota_request->duty_date]['assigned_night_doctors_res'][]=
                $special_rota_request->doctor_id;
            }
            else{
                $duties_arr [$special_rota_request->duty_date]['assigned_night_doctors_reg'][]=
                $special_rota_request->doctor_id;
            }
    }

   }

       foreach($rota_generate_patterns as $rota_generate_pattern){

        $duty_date = $rota_generate_pattern->duty_date;

        $doctors_index = 0;
        $find_doctors_level_index = 1;
        [$doctors_arr,$doctors] = $this->get_suitable_doctor($doctors_arr,
                                                            $duty_date,$shifts['morning'],
                                                            $find_doctors_level[$find_doctors_level_index]);
        $rota_generate_pattern_total_morning_doctors = $rota_generate_pattern->total_morning_doctors+$rota_generate_pattern->has_morning_ucc;
        for($i=0;$i<$rota_generate_pattern_total_morning_doctors;$i++){

            $is_ucc = 0;
            if($i==0 && $rota_generate_pattern->has_morning_ucc){
                $is_ucc = 1;
            }

            while(!isset($doctors[$doctors_index])){
            $select_doctor = 0;
            $find_doctors_level_index = $find_doctors_level_index + 1;
            [$doctors_arr,$doctors] = $this->get_suitable_doctor($doctors_arr,
                                                                $duty_date,$shifts['morning']
                                                                ,$find_doctors_level[$find_doctors_level_index]);

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

        // foreach($shifts as $shift){
        //     [$doctors_arr,$doctor] = $this->get_suitable_doctor($doctors_arr,$duty_date,$shift);
        //     $data[] =
        //     [
        //         'duty_date' => $duty_date,
        //         'monthly_rota_id' => $id,
        //         'is_ucc' => 0,
        //         'shift' => $shift,
        //         'doctor_id' => $doctor->id

        //     ];
        // }

        }
        // dd($data);
       Rota::insert($data);

       $list = Rota::where('monthly_rota_id',$id)->orderBy('duty_date','asc')->get();
 }
        // $duty_date = strtotime("1-".$monthly_rota->month."-".$monthly_rota->year);
        $start_weekday = date('w', $list[0]->duty_date);
        $weekdays = Config::get('constants.weekdays_num');
        return view('admin.doctor_calender.index',compact('list','start_weekday','weekdays','doctors'));
    }
    function get_suitable_doctor($doctors_arr,$duty_date,$shift){
        $doctors_id = array_column($doctors_arr, 'doctor_id');
        $doctors_id = $this->filter_anualleaves($doctors_id,$duty_date);
        $doctor = $this->special_rota_request_wantduty($doctors_id,$duty_date,$shift);
        if($doctor){
            return $doctor;
        }
        $doctors_id = $this->special_rota_request_wantoff($doctors_id,$duty_date,$shift);

        $doctor = $this->select_doctor_general_rota($doctors_arr,$doctors_id,$duty_date,$shift);
        if($doctor){
            return $doctor;
        }
        $doctor = $this->select_doctor_rota($doctors_arr,$doctors_id,$duty_date,$shift);


        // dd( $doct );
        return $doct;
    }
    function special_rota_request_wantduty($doctors,$duty_date,$shift){
        $doctor = Special_rota_request::where('duty_date',$duty_date)
                                        ->where('want_duty',1)
                                        ->where('shift',$shift)
                                        ->first();
        return $doctor;
    }

    function special_rota_request_wantoff($doctors_id,$duty_date,$shift){
        $doctors_off = Special_rota_request::where('duty_date',$duty_date)
                                        ->where('want_duty',0)
                                        ->pluck('id')->toArray();
        $result=array_diff($doctors,$doctors_off);
        return $result;
    }


    function filter_anualleaves($doctors,$duty_date){
        $doct = Leave_Request::
                            where('start_date','<=',$duty_date)
                            ->where('end_date','>=',$duty_date)
                            ->where('annual_leave',1)
                            ->whereIn('id',$doctors)
                            ->pluck('id')->toArray();

        $result=array_diff($doctors,$doct);

        return $result;
    }



    function  select_doctor_general_rota($doctors_arr,$doctors_id,$duty_date,$shift){

        foreach($doctors_arr as $doctor){
            if($shift == 'morning'){

                if($doctor['req_morning']<$doctor['given_morning']){
                    return $doctor;
                }
            }
            elseif($shift == 'evening'){



                if($doctor['req_evening']<$doctor['given_evening']){
                    return $doctor;
                }
            }
            else{
                if($doctor['req_night']<$doctor['given_night']){
                    return $doctor;
                }
            }
        }
    }
    function select_doctor_rota($doctors_arr,$doctors_id,$duty_date,$shift){

    }

}
