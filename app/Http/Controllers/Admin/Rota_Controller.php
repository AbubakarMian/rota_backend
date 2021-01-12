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
        $shifts = Config::get('constants.duty_type');

        $doctors = Doctor::get();
        $doctors_arr = [];
        foreach($doctors as $d){
            $req_duties_mor = General_rota_request::where('doctor_id',$d->id)->where('shift',$shifts['morning']);
            $req_duties_eve = General_rota_request::where('doctor_id',$d->id)->where('shift',$shifts['evening']);
            $req_duties_night = General_rota_request::where('doctor_id',$d->id)->where('shift',$shifts['night']);
            $doctors_arr[] = [
                'doctor_id'=>$d->id,
                'total_duties'=>$d->total_duties,
                'extra'=>0,
                'given_morning'=>0,
                'given_evening'=>0,
                'given_night'=>0,
                'extra'=>0,
                'req_morning'=>$req_duties_mor,
                'req_evening'=>$req_duties_eve,
                'req_night'=>$req_duties_night,

            ];
        }


       for($i=1; $i<($days+1);$i++){
           $doctors = Doctor::pluck('id')->toArray();

        $duty_date = strtotime($i."-".$monthly_rota->month."-".$monthly_rota->year);
        // dd($duty_date);


        foreach($shifts as $shift){
            $doct = $this->get_suitable_doctor($shift);
            $data[] =
            [
                'duty_date' => $duty_date,
                'monthly_rota_id' => $id,
                'is_ucc' => 0,
                'shift' => $shift,
                'doctor_id' => $doctors

            ];
        }

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
    function get_suitable_doctor($shift){
        $doctors = $this->filter_anualleaves($doctors,$duty_date);
        $doctor = $this->special_rota_request($doctors,$duty_date,$shift);
        if($doctor){
            return $doctor;
        }
        $doctor = $this->general_rota_request($doctors,$duty_date,$shift);

        dd( $doct );
        return $doct;
    }
    function special_rota_request($doctors,$duty_date,$shift){
        $doctor = Special_rota_request::where('duty_date',$duty_date)
                                        ->where('want_duty',1)
                                        ->where('shift',$shift)
                                        ->first();
        return $doctor;
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

}
