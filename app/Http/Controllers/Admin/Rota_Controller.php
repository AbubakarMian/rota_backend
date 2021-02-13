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
        $list = Monthly_rota::orderBy('created_at','desc')->paginate(10);
        return \View::make('admin.rotalist.index', compact('list'));
    }

    public function create()
    {
        $control = 'create';
        return \View::make('admin.rotalist.create', compact('control'));
    }

    public function save(Request $request)
    {
        $monthly_rota = new Monthly_rota();
        $this->add_or_update($request, $monthly_rota);
        return redirect('admin/rota/generate/pattern/'.$monthly_rota->id);
    }

    public function add_or_update(Request $request, $monthly_rota)
    {
        $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
        $monthly_rota->year = $request->year;
        $monthly_rota->month = $request->month;
        $monthly_rota->total_days = $days;
        $monthly_rota->save();
        return redirect()->back();
    }

    public function create_temp_rota($monthly_rota){
        $temp_rota_count = TempRota::where('monthly_rota_id', $monthly_rota->id)->count('id');
        $temp_rota_count = $temp_rota_count+1;
        $temp_rota = new TempRota();
        $temp_rota->demo_num = $temp_rota_count;
        $temp_rota->monthly_rota_id = $monthly_rota->id;
        $temp_rota->save();


        $generated_rota = new GenerateRota($monthly_rota);
        list($generated_rota_arr, $doctors_duties_assigned) = $generated_rota->generate_rota_arr();
// dd($doctors_duties_assigned);
        $rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota->id)
                                                    ->orderBy('duty_date', 'asc')->get();

        $temp_rota_id = $temp_rota->id;

        $temp_rota_details = [];
        foreach($doctors_duties_assigned as $doctor_duties){
            $total_given_duties = $doctor_duties['given_morning']+$doctor_duties['given_evening']+$doctor_duties['given_night'];
            $temp_rota_detail =  new Temp_Rota_detail();

            $temp_rota_details[] = [
                'doctor_id'=>$doctor_duties['doctor_id'],
                'total_morning'=>$doctor_duties['given_morning'],
                'total_evening'=>$doctor_duties['given_evening'],
                'total_night'=>$doctor_duties['given_night'],
                'total_duties'=>$total_given_duties,
                'total_leaves'=>$temp_rota->monthly_rota->total_days - $total_given_duties,
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
        Temp_monthly_rota::insert($temp_monthly_rota);
        return [$temp_rota];
    }

    public function calender_view_temp_rota($temp_rota_id){
        $temp_rota = TempRota::with('rota_generate_pattern')->find($temp_rota_id);
        $doctors = Doctor::with('user')->get();
        $doctors_by_id = [];
        foreach($doctors as $doctor){
            $doctors_by_id[$doctor->id] = $doctor->user->name;
        }
        $start_weekday = date('w', $temp_rota->rota_generate_pattern[0]->duty_date)+1; // since our week starts from sunday add 1
        $weekdays = Config::get('constants.weekdays_num');
        return view('admin.doctor_calender.index', compact('temp_rota', 'start_weekday', 'weekdays', 'doctors','doctors_by_id'));
    }
    // admin.doctor_calender.index

    public function get_temp_rota($temp_rota_id){
        $temp_rota = TempRota::find($temp_rota_id);
        // $monthly_rota = Monthly_rota::find($temp_rota->monthly_rota_id);
        return $this->calender_view_temp_rota($temp_rota_id);
    }
    public function temp_rota($monthly_rota_id){
        $temp_rota = TempRota::where('monthly_rota_id',$monthly_rota_id)->orderBy('created_at','desc')->paginate(10);
        return view('admin.temprota.index', compact('monthly_rota_id','temp_rota'));
    }
    public function generate(Request $request,$monthly_rota_id) // add new temp rota
    {
        $monthly_rota = Monthly_rota::find($monthly_rota_id);
        list($temp_rota) = $this->create_temp_rota($monthly_rota);
        return $this->calender_view_temp_rota($temp_rota->id);
        // $find_doctors_level = Config::get('constants.find_doctors_level');
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
                if ($rota_generate_pattern[$shift['has_ucc']] && $key==0) { // since ucc can only be resident
                    $is_ucc = 1;
                }
                $temp_duties[] = $this->get_temp_duty($temp_rota_id, $duty_date, $doctors[$d_id], $shift_key, $is_ucc);
            }
            foreach ($rota_by_date[$selected_shift_reg] as $key=>$d_id) {
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
