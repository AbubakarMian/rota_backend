<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\models\Temp_monthly_rota;
use App\models\Temp_Rota_detail;
use App\models\TempRota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB ;

class Temp_Rota_Controller extends Controller
{

    public function index($id){
        $temp_monthly_rota_arr = Temp_monthly_rota::where('temp_rota_id',$id)->get();
        $temp_monthly_rota = TempRota::with('monthly_rota')->find($id);
        $monthly_rota = $temp_monthly_rota->monthly_rota;

        $doctors = [];

        foreach($temp_monthly_rota_arr as $temp_monthly_rota){
            $doctor_id = $temp_monthly_rota->doctor_id;
            if(!isset($doctors[$temp_monthly_rota->doctor_id])){
                $doctors[$doctor_id] = [
                    'monthly_rota_id'=>$monthly_rota->id,
                    'total_morning'=>0,
                    'total_evening'=>0,
                    'total_night'=>0,
                    'total_duties'=>0,
                    'total_leaves'=>$monthly_rota->total_days,
                    'temp_rota_id'=>$id,
                    'doctor_id'=>$doctor_id
                ];
            }
            $doctors[$doctor_id]['total_morning'] = ($temp_monthly_rota->shift == 'morning'? 1:0) + $doctors[$doctor_id]['total_morning'];
            $doctors[$doctor_id]['total_evening'] = ($temp_monthly_rota->shift == 'evening'? 1:0) + $doctors[$doctor_id]['total_evening'];
            $doctors[$doctor_id]['total_night'] = ($temp_monthly_rota->shift == 'night'? 1:0) + $doctors[$doctor_id]['total_night'];
            $doctors[$doctor_id]['total_duties'] = $doctors[$doctor_id]['total_duties'] + 1;
            $doctors[$doctor_id]['total_leaves'] = $doctors[$doctor_id]['total_leaves'] - 1;
        }

        foreach($doctors as $doctor_id=>$doctor){
            $temp_rota_detail = Temp_Rota_detail::where('temp_rota_id',$id)->where('doctor_id',$doctor_id)->first();
            $temp_rota_detail->total_morning = $doctor['total_morning'];
            $temp_rota_detail->total_evening = $doctor['total_evening'];
            $temp_rota_detail->total_night = $doctor['total_night'];
            $temp_rota_detail->total_duties = $doctor['total_duties'];
            $temp_rota_detail->total_leaves = $doctor['total_leaves'];
            $temp_rota_detail->save();
        }
        $rota_details = Temp_Rota_detail::with('doctor.user')->where('temp_rota_id',$id)->paginate(10);
        return view('admin.temp_rota_detail.index', compact('rota_details'));
    }
}
