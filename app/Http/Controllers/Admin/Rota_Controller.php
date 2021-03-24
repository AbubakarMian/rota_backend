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
use App\models\Rota_detail;
use App\models\Temp_monthly_rota;
use App\models\TempRota;
use App\models\Monthly_Rota_doctors;
use App\models\Rota_Generate_Pattern;
use App\models\Temp_Rota_Date_Details;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\libraries\GenerateRota;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;


class Rota_Controller extends Controller
{
    public function index()
    {
        $list = Monthly_rota::orderBy('created_at','desc')->paginate(10);
        return \View::make('admin.rotalist.index', compact('list'));
    }


    public function rota_detail($id){
        $rota_details = Rota_detail::with('doctor.user')->where('monthly_rota_id',$id)->paginate(10);
        return view('admin.temp_rota_detail.index', compact('rota_details'));
    }

    public function show_rota($monthly_rota_id){
        $monthly_rota = Monthly_rota::find($monthly_rota_id);
        $doctors = Doctor::with('user')->get();
        $doctors_by_id = [];
        foreach($doctors as $doctor){
            $doctors_by_id[$doctor->id] = $doctor->user->name;
        }
        $start_weekday = date('w', $monthly_rota->rota->rota_generate_pattern[0]->duty_date)+1; // since our week starts from sunday add 1
        $weekdays = Config::get('constants.weekdays_num');
        return \View::make('admin.rota.calender.index', compact('monthly_rota','start_weekday','weekdays', 'doctors','doctors_by_id'));
    }

    public function create()
    {
        $control = 'create';
        return \View::make('admin.rotalist.create', compact('control'));
    }

    public function save(Request $request)
    {
        $monthly_rota = Monthly_rota::where('year', $request->year)->where('month', $request->month)->first();
        if(!$monthly_rota){
            $monthly_rota = new Monthly_rota();
        }
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

    public function create_temp_rota($monthly_rota,$exetime){
        $generated_rota = new GenerateRota($monthly_rota,$exetime);
        list($rota_generated_successfully,$generated_rota_arr, $doctors_duties_assigned) = $generated_rota->generate_rota_arr();
        // while(!$rota_generated_successfully){
        //     list($rota_generated_successfully,$generated_rota_arr, $doctors_duties_assigned) = $generated_rota->generate_rota_arr();
        // }
        $temp_rota_count = TempRota::withTrashed()->where('monthly_rota_id', $monthly_rota->id)->count('id');
        $temp_rota_count = $temp_rota_count+1;
        $temp_rota = new TempRota();
        $temp_rota->demo_num = $temp_rota_count;
        $temp_rota->monthly_rota_id = $monthly_rota->id;
        $temp_rota->save();

        $doctors = Doctor::with('user')->get()->pluck('user.name', 'id')->toArray();
        $rota_generate_patterns = Rota_Generate_Pattern::where('monthly_rota_id', $monthly_rota->id)
                                                    ->orderBy('duty_date', 'asc')->get();

        $temp_rota_id = $temp_rota->id;
        $temp_rota_details = [];
        $doctor_details = [];
        // foreach($doctors_duties_assigned as $doctor_duties){
        //     $total_given_duties = $doctor_duties['given_morning']+$doctor_duties['given_evening']+$doctor_duties['given_night'];

        //     $temp_rota_details[] = [
        //         'monthly_rota_id'=>$monthly_rota->id,
        //         'doctor_id'=>$doctor_duties['doctor_id'],
        //         'total_morning'=>$doctor_duties['given_morning'],
        //         'total_evening'=>$doctor_duties['given_evening'],
        //         'total_night'=>$doctor_duties['given_night'],
        //         'total_duties'=>$total_given_duties,
        //         'total_leaves'=>$monthly_rota->total_days - $total_given_duties,
        //         'temp_rota_id'=>$temp_rota_id,
        //     ];
        // }

        // Temp_Rota_detail::insert($temp_rota_details);
        $temp_monthly_rota = [];
        $temp_rota_date_details_arr = [];
        $temp_rota_date_details = [];
        $temp_rota_date_details['rota_id']=$monthly_rota->id;
        $temp_rota_date_details['temp_rota_id']=$temp_rota_id;
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
            $doctor_details = $this->get_temp_rota_detail( $doctor_details,$monthly_rota,$temp_rota_id,
                                                            $duty_date,
                                                            $rota_generate_pattern,
                                                            $rota_by_date,
                                                            $doctors_duties_assigned);

            // Temp_Rota_Date_Details for consecutive and annual leave doctor
            // $temp_rota_date_details =new Temp_Rota_Date_Details();
            $temp_rota_date_details['date']=$duty_date;
            // $temp_rota_date_details->rota_id= $monthly_rota->id ;
            // $temp_rota_date_details->temp_rota_id= $temp_rota_id ;
            // $temp_rota_date_details->date= $duty_date;


            if($rota_by_date['dis_qualified_consecutive_doctors']){
                $disqualified_doc_merge=[] ;

                foreach($rota_by_date['dis_qualified_consecutive_doctors'] as $dqc_id){

                    $disqualified_doc_merge[] = $doctors[$dqc_id] ;
                }
                $temp_rota_date_details['consecutive_doctor']=implode(',',$disqualified_doc_merge);

                // $temp_rota_date_details->consecutive_doctor= implode(',',$disqualified_doc_merge);
            }
            if($rota_by_date['annual_leaves']){
                $annual_leave_arr=[] ;
                    foreach($rota_by_date['annual_leaves'] as $al_id){
                    $annual_leave_arr[] = $doctors[$al_id] ;
                }
                $temp_rota_date_details['anual_leave_doctor']=implode(',',$annual_leave_arr);
            }
                $temp_rota_date_details['conditions']=json_encode($rota_by_date['conditions']);

                // $temp_rota_date_details->conditions= json_encode( $rota_by_date['conditions']);

            if($rota_by_date['special_rota_off_doctors']){
                $special_rota_off_arr=[] ;
                    foreach($rota_by_date['special_rota_off_doctors'] as $sroff_id){

                    $special_rota_off_arr[] = $doctors[$sroff_id] ;
                }
                $temp_rota_date_details['special_rota_off']=implode(',',$special_rota_off_arr);

                // $temp_rota_date_details->special_rota_off=  implode(',',$special_rota_off_arr);
            }
            $temp_rota_date_details_arr[] = $temp_rota_date_details;
        //    $temp_rota_date_details->save();
        }
        // dd($doctor_details);
        Temp_Rota_detail::insert($doctor_details);
        Temp_Rota_Date_Details::insert($temp_rota_date_details_arr);
        Temp_monthly_rota::insert($temp_monthly_rota);
        return [$temp_rota];
    }

    public function save_temp_rota($temp_rota_id){

    $temp_rota = TempRota::find($temp_rota_id);
    $total_month_days = $temp_rota->monthly_rota->total_days;
    $temp_rota->monthly_rota->temp_rota()->where('status','selected')->update(['status' => 'unselected']);
    $shifts = Config::get('constants.duty_type');

    $temp_rota->status = 'selected';
    $temp_rota->save();

    $temp_monthly_rota = Temp_monthly_rota::where('temp_rota_id',$temp_rota_id)->get();
    $monthly_rota_id = $temp_rota->monthly_rota_id;

    Rota::where('monthly_rota_id',$monthly_rota_id)->delete();
    Rota_detail::where('monthly_rota_id',$monthly_rota_id)->delete();
    $rota = [];
    $doctor_rota_detail = [];
    foreach($temp_monthly_rota as $monthly_rota_doc){

      $rota[] = [
          'doctor_id'=>$monthly_rota_doc->doctor_id,
          'shift'=>$monthly_rota_doc->shift,
          'duty_date'=>$monthly_rota_doc->duty_date,
          'doctor_type_id'=>$monthly_rota_doc->doctor_type_id,
          'is_ucc'=>$monthly_rota_doc->is_ucc,
          'monthly_rota_id'=>$monthly_rota_id,
      ];
      if(!isset($doctor_rota_detail[$monthly_rota_doc->doctor_id])){
        $doctor_rota_detail[$monthly_rota_doc->doctor_id] =  [
            'doctor_id'=>$monthly_rota_doc->doctor_id,
            'monthly_rota_id'=>$monthly_rota_id,
            'total_morning'=>0,
            'total_evening'=>0,
            'total_night'=>0,
            'total_duties'=>0,
            'total_leaves'=>0,
        ];
      }
      $morning_duty = $monthly_rota_doc->shift == $shifts['morning'] ?1:0;
      $evening_duty = $monthly_rota_doc->shift == $shifts['evening'] ?1:0;
      $night_duty = $monthly_rota_doc->shift == $shifts['night'] ?1:0;

        $doctor_rota_detail[$monthly_rota_doc->doctor_id] =  [
            'total_morning'=>$doctor_rota_detail[$monthly_rota_doc->doctor_id]['total_morning']+$morning_duty,
            'total_evening'=>$doctor_rota_detail[$monthly_rota_doc->doctor_id]['total_evening']+$evening_duty,
            'total_night'=>$doctor_rota_detail[$monthly_rota_doc->doctor_id]['total_night']+$night_duty,
            'total_duties'=>$doctor_rota_detail[$monthly_rota_doc->doctor_id]['total_duties']+1,
            'total_leaves'=>$total_month_days - $doctor_rota_detail[$monthly_rota_doc->doctor_id]['total_duties'],
        ];
    }

    Rota::insert($rota);
    Rota_detail::insert($doctor_rota_detail);
    return redirect('admin/rota');
    }

    public function calender_view_temp_rota($temp_rota_id){
        $temp_rota = TempRota::with('rota_generate_pattern','rota_Date_Detail')->find($temp_rota_id);
        $doctors = Doctor::withTrashed()->with('user')->get();
        $doctors_by_id = [];
        foreach($doctors as $key=>$doctor){
            $doctors_by_id[$doctor->id] = $doctor->user->name;
        }
        $doctors = Doctor::with('user')->get();
        $start_weekday = date('w', $temp_rota->rota_generate_pattern[0]->duty_date)+1; // since our week starts from sunday add 1
        $weekdays = Config::get('constants.weekdays_num');

        $conditions_key_values = Config::get('constants.conditions_key_values');

        return view('admin.doctor_calender.index', compact('temp_rota', 'conditions_key_values','start_weekday', 'weekdays', 'doctors','doctors_by_id'));
    }

    public function get_temp_rota($temp_rota_id){
        $temp_rota = TempRota::find($temp_rota_id);
        return $this->calender_view_temp_rota($temp_rota_id);
    }
    public function temp_rota($monthly_rota_id){
        $temp_rota = TempRota::where('monthly_rota_id',$monthly_rota_id)->orderBy('created_at','desc')->paginate(10);
        return view('admin.temprota.index', compact('monthly_rota_id','temp_rota'));
    }
    public function generate(Request $request,$monthly_rota_id) // add new temp rota
    {
        $monthly_rota = Monthly_rota::find($monthly_rota_id);
        list($temp_rota) = $this->create_temp_rota($monthly_rota,$request->exetime);
        return $this->calender_view_temp_rota($temp_rota->id);
    }

    public function update_temp_rota(Request $request){
        Log::info('all request ');
        Log::info($request->all());

        $temp_rota_id = $request->temp_rota_id;
        $shift = $request->shift;
        $is_ucc = $request->is_ucc;
        $duty_date = $request->duty_date;
        $temp_monthly_rota = Temp_monthly_rota::where('temp_rota_id',$temp_rota_id)
            ->where('shift',$shift)
            ->where('is_ucc',$is_ucc)
            ->where('duty_date',$duty_date)
            ->delete();

        $temp_monthly_rota_arr = [];
        $all_doctors = '';
        $doctors = explode(',',$request->doctors);
        foreach($doctors as $d){
            $doctor = Doctor::find($d);
            $temp_monthly_rota_arr[] = [
                'doctor_id'=>$d,
                'temp_rota_id'=>$temp_rota_id,
                'shift'=>$shift,
                'duty_date'=>$duty_date,
                'doctor_type_id'=>$doctor->doctor_type_id,
                'is_ucc'=>$is_ucc,
            ];

            $style='';
            if($is_ucc){
                $style = 'color:#cd2ad8';

            }
            elseif($doctor->doctor_type_id == 2 ){
                $style = 'color:#2e4eec';
            }
            $all_doctors .= ',<div style="'.$style.'" data-id="'.$doctor->id.'" class=" doc did_'.$doctor->id.'">'.$doctor->user->name.'</div>';
        }
        $all_doctors = ltrim($all_doctors,',') ;

        Temp_monthly_rota::insert($temp_monthly_rota_arr);
        $res = new \stdClass();
        $res->status = true;
        $res->response = $all_doctors;

        return json_encode($res);
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
                $is_ucc = 0;//$rota_by_date,
                $temp_duties[] = $this->get_temp_duty($temp_rota_id, $duty_date, $doctors[$d_id], $shift_key, $is_ucc);
            }
        }
        return $temp_duties;
    }


    public function get_temp_rota_detail($doctor_details,$monthly_rota,$temp_rota_id, $duty_date, $rota_generate_pattern, $rota_by_date, $doctors)
    {
        $shift_type = Config::get('constants.duties_shift_type');
        // $doctor_details = [];
        foreach ($shift_type as $shift_key=>$shift) {
            $selected_shift_res = $shift['assigned_doctors_res'];
            $selected_shift_reg = $shift['assigned_doctors_reg'];
            $all_doctors = array_merge($rota_by_date[$selected_shift_res],$rota_by_date[$selected_shift_reg]);

            foreach ($all_doctors as $key=>$d_id) {

                if(!isset($doctor_details[$d_id])){

                    $doctor_details[$d_id] = [
                        'monthly_rota_id'=>$monthly_rota->id,
                        'total_morning'=>0,
                        'total_evening'=>0,
                        'total_night'=>0,
                        'total_duties'=>0,
                        'total_leaves'=>$monthly_rota->total_days,
                        'temp_rota_id'=>$temp_rota_id,
                        'doctor_id'=>$d_id
                    ];
                }
                $doctor_details[$d_id]['total_morning'] = ($shift_key == 'morning'? 1:0) + $doctor_details[$d_id]['total_morning'];
                $doctor_details[$d_id]['total_evening'] = ($shift_key == 'evening'? 1:0) + $doctor_details[$d_id]['total_evening'];
                $doctor_details[$d_id]['total_night'] = ($shift_key == 'night'? 1:0) + $doctor_details[$d_id]['total_night'];
                $doctor_details[$d_id]['total_duties'] = $doctor_details[$d_id]['total_duties'] + 1;
                $doctor_details[$d_id]['total_leaves'] = $doctor_details[$d_id]['total_leaves'] - 1;

                // $doctor_details[$d_id] = [
                //     'total_morning'=>($shift_key == 'morning'? 1:0) + $doctor_details[$d_id]['total_morning'],
                //     'total_evening'=>($shift_key == 'evening'? 1:0) + $doctor_details[$d_id]['total_evening'],
                //     'total_night'=>($shift_key == 'night'? 1:0) + $doctor_details[$d_id]['total_night'],
                //     'total_duties'=>$doctor_details[$d_id]['total_duties'] + 1,
                //     'total_leaves'=>$doctor_details[$d_id]['total_leaves'] - 1,
                // ];
            }
        }
        return $doctor_details;
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

    public function status($id){

   $status_check = TempRota::find($id);

    if ($status_check->status)
      {
    $status_check->status = 'selected';
    $new_value = 'unselected';

   }

   else{
      $status_check->status = 'unselected';
      $new_value = 'selected';
   }

   $status_check->save();
   $response = Response::json([
    "status" => true,
    'action' => Config::get('constants.ajax_action.update'),
    'new_value' => $new_value

    ]);
    return $response;
    }



    public function destroy_undestroy($id)
    {
        $rota =  Monthly_rota::find($id);
        if ($rota) {
            Monthly_rota::destroy($id);
            $new_value = 'Activate';
        }
        $response = Response::json([
            "status" => true,
            'action' => Config::get('constants.ajax_action.delete'),
            'new_value' => $new_value
        ]);
        return $response;
    }

    public function temprota_destroy($id)
    {

        $rota =  TempRota::destroy($id);

        $response = Response::json([
            "status" => true,
            'action' => Config::get('constants.ajax_action.delete'),
        ]);
        return $response;
    }
}
