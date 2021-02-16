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
use App\models\Rota_Request;
use Illuminate\Support\Facades\Response;
use LeaveRequest;
use App\models\General_rota_request;

class General_Rota_Request_Controller extends Controller
{
    public function index(Request $request)
    {
    //    $list = General_rota_request::with('doctor')->get();

    $name = $request->name ?? '';
    $list =  General_rota_request::whereHas('doctor', function ($query) use ($name) {
    $query->whereHas('user',function($u)use($name){
    $u->where('name', 'like', '%' . $name . '%');
    });
    })->paginate(10);
     return \View::make('admin.general_rota_request.index', compact('list','name'));
    }


    public function create()
    {
        $control = 'create';
        $general = General_rota_request::get();
        $doctor = Doctor::with(['user'])->get();

        return \View::make(
            'admin.general_rota_request.create',
            compact('control', 'general', 'doctor')
        );
    }

    public function save(Request $request)
    {
        $generate = new General_rota_request();
        $generate ->total_duties=$request->total_duties;
        $generate ->shift=$request->shift;
        $generate ->doctor_id=$request->doctor_id;
        $generate ->save();
        return Redirect('admin/general/rota');
    }


public function search(Request $request)
{

    return view('admin/general/rota');
    }


    public function destroy_undestroy($id)
    {

        $general = General_rota_request::find($id);
        if ($general) {
            General_rota_request::destroy($id);
            $new_value = 'Activate';
        } else {
            General_rota_request::withTrashed()->find($id)->restore();
            $new_value = 'Delete';
        }
        $response = Response::json([
            "status" => true,
            'action' => Config::get('constants.ajax_action.delete'),
            'new_value' => $new_value
        ]);
        return $response;
    }

}

