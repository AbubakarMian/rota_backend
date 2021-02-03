<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\Special_rota_request;
use App\models\General_rota_request;
use App\models\Temp_Rota_detail;
use App\models\Monthly_rota;
use App\models\Leave_Request;
use App\models\Doctor;
use App\models\Rota;
use App\models\Rota_Request;
use App\models\Doctor_type;
use App\models\Temp_monthly_rota;
use App\models\TempRota;
use App\models\Rota_Generate_Pattern;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Libraries\GenerateRota;

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


    public function generate($id)
    {
        $rota = Rota::where('monthly_rota_id', $id)->orderBy('duty_date', 'asc')->get();
        $monthly_rota = Monthly_rota::find($id);

        // dd($rota);
        if (true) {//if(!$list->count()){
            $find_doctors_level = Config::get('constants.find_doctors_level');

            $generated_rota = new GenerateRota($monthly_rota);
            list($generated_rota_arr, $doctors_duties_assigned) = $generated_rota->generate_rota_arr();





            $rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota->id)
                                                        ->orderBy('duty_date', 'asc')->get();

            $temp_rota_count = TempRota::where('monthly_rota_id', $id)->count('id');
            $temp_rota_count = $temp_rota_count+1;
            $temp_rota = new TempRota();
            $temp_rota->demo_num = $temp_rota_count;
            $temp_rota->monthly_rota_id = $id;
            $temp_rota->save();
            $temp_rota_id = $temp_rota->id;

            $temp_rota_details = [];
            foreach($doctors_duties_assigned as $doctor_duties){
                              $temp_rota_detail =  new Temp_Rota_detail();

                              $temp_rota_details[] = [
                                  'doctor_id'=>$doctor_duties['doctor_id'],
                                  'total_morning'=>$doctor_duties['given_morning'],
                                  'total_evening'=>$doctor_duties['given_evening'],
                                  'total_night'=>$doctor_duties['given_night'],
                                  'total_duties'=>$doctor_duties['total_duties'],
                                  'total_leaves'=>$doctor_duties['total_leaves'],
                                  'temp_rota_id'=>$temp_rota_id,
                              ];
                           }
                           Temp_Rota_detail::insert($temp_rota_details);
            $temp_monthly_rota = [];
            foreach ($rota_generate_patterns as $rota_generate_pattern) {
                $duty_date = $rota_generate_pattern->duty_date;
                $rota_by_date = $generated_rota_arr[$duty_date];
                $temp_date_rota = $this->get_temp_duties(
                    $temp_rota_id,
                    $duty_date,
                    $rota_generate_pattern,
                    $rota_by_date,
                    $doctors_duties_assigned
                );

                $temp_monthly_rota = array_merge($temp_monthly_rota, $temp_date_rota);
            }
        }
        // dd($temp_monthly_rota);
        Temp_monthly_rota::insert($temp_monthly_rota);

        $list = $rota_generate_patterns;
        // $list = Temp_monthly_rota::where('temp_rota_id',$temp_rota_id)->get();
        // dd($temp_monthly_rota);
        $doctors = Doctor::get();
        $start_weekday = date('w', $rota[0]->duty_date);
        $weekdays = Config::get('constants.weekdays_num');
        return view('admin.doctor_calender.index', compact('list', 'start_weekday', 'weekdays', 'doctors'));
    }

    public function get_temp_duties($temp_rota_id, $duty_date, $rota_generate_pattern, $rota_by_date, $doctors)
    {
        $temp_duties = [];
        $shift_type = Config::get('constants.duties_shift_type');
        foreach ($shift_type as $shift_key=>$shift) {
            $selected_shift_res = $shift['assigned_doctors_res'];
            $selected_shift_reg = $shift['assigned_doctors_reg'];

            foreach ($rota_by_date[$selected_shift_res] as $key=>$d_id) {
                $is_ucc = 0;
                if ($shift['has_ucc'] && $key==0) {
                    $is_ucc = 1;
                }
                $temp_duties[] = $this->get_temp_duty($temp_rota_id, $duty_date, $doctors[$d_id], $shift_key, $is_ucc);
            }
            foreach ($rota_by_date[$selected_shift_res] as $key=>$d_id) {
                $is_ucc = 0;
                $temp_duties[] = $this->get_temp_duty($temp_rota_id, $duty_date, $doctors[$d_id], $shift_key, $is_ucc);
            }
        }
        return $temp_duties;
    }

    public function get_temp_duty($temp_rota_id, $duty_date, $doctor, $shift, $is_ucc)
    {
        $temp_duty = [
            'doctor_id'=>$doctor['doctor_id'],
            'temp_rota_id'=>$temp_rota_id,
            'shift'=>$shift,
            'duty_date'=>$duty_date,
            'doctor_type_id'=>$doctor['doctor_type'],
            'is_ucc'=>$is_ucc,
        ];
        return $temp_duty;
    }
}
