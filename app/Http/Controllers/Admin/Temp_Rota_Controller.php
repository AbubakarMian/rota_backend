<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\models\Temp_monthly_rota;
use App\models\Temp_Rota_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB ;

class Temp_Rota_Controller extends Controller
{

    public function index($id){
        

        $temp_monthly_rota = Temp_monthly_rota::where('temp_rota_id',$id)
        ->select(DB::Raw('count(id) as total_duties_assigned'))
        ->where('doctor_id',20)
        ->get();
        
        $rota_details = Temp_Rota_detail::with('doctor.user')->where('temp_rota_id',$id)->paginate(10);
        dd($temp_monthly_rota);
      
        return view('admin.temp_rota_detail.index', compact('rota_details'));



    }
}
