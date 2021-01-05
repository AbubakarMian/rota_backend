<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\models\Doctor;
use App\models\Leave_Request;
use App\models\User;
use App\models\Weekday;
use App\models\Monthly_rota;
use Illuminate\Support\Facades\Response;
use LeaveRequest;
use App\models\Rota_Generate_Pattern;
use Config;



class Rota_Generate_Pattern_Controller extends Controller
{
    public function index(Request $request, $id)
    {

        $list = Rota_Generate_Pattern::where('monthly_rota_id',$id)->orderBy('duty_date','asc')->get();
        $monthly_rota = Monthly_rota::find($id);

        if(!$list->count()){

            $days = cal_days_in_month(CAL_GREGORIAN,$monthly_rota->month,$monthly_rota->year);
            for($i=1; $i<($days+1); $i++){
                $duty_date = strtotime($i."-".$monthly_rota->month."-".$monthly_rota->year);

                $data[] =
                [
                    'duty_date' => $duty_date,
                    'monthly_rota_id' => $id,
                    'has_ucc' => 0,
                    'total_morning_doctors' => 4,
                    'total_evening_doctors' => 4,
                    'total_night_doctors' => 4,
                    'total_doctors' => 12
                ];

            }
            Rota_Generate_Pattern::insert($data);
            $list = Rota_Generate_Pattern::where('monthly_rota_id',$id)->orderBy('duty_date','asc')->get();
        }

        $duty_date = strtotime("1-".$monthly_rota->month."-".$monthly_rota->year);
        $start_weekday = date('w', $duty_date);
        $weekdays = Config::get('constants.weekdays_num');
        // dd($weekdays);
        // dd($start_weekday);
        return view('admin.rota_generate_pattern.index',compact('list','start_weekday','weekdays'));


    }

    public function create()
    {
        $control = 'create';
        $generate = Rota_Generate_Pattern::get();

        return \View::make(
            'admin.rota_generate_pattern.create',
            compact('control', 'generate')
        );
    }

    public function save(Request $request){

        $generate = new Rota_Generate_Pattern();
}


public function update(Request $request,$id){

    $generate = Rota_Generate_Pattern::find($id);
    $this->add_or_update($request,$generate);
    // return Redirect('admin/doctor');

}
    public function add_or_update(Request $request, $generate)

    {

        // $request->all();
        // $generate->duty_date = $request->duty_date;
        // $generate->monthly_rota_id = $request->$id;
        $generate->total_morning_doctors = $request->total_morning_doctors;
        $generate->total_evening_doctors = $request->total_evening_doctors;
        $generate->total_night_doctors = $request->total_night_doctors;
        $generate->has_morning_ucc = $request->has_morning_ucc;





        $generate->save();

        $res = new \stdClass();
        $res->status = true;
        // return json_encode($res);


}


}
