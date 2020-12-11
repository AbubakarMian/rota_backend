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

class Rota_RequestController extends Controller
{

    public function index()
    {


        $doctors = Doctor::with(['user'])->paginate(13);
        return view('admin.rota_request.index', compact('doctors'));
    }


    public function search(Request $request)
    {


        $doctors = Doctor::whereHas('user', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->name . '%');
        })->with(['user' => function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }])->paginate(13);
        return view('admin.rota_request.index', compact('doctors'));
    }

    public function leave($id)
    {

        $doctor = Doctor::find($id);
        return \View::make('admin.rota.leave.create', compact(
            'doctor'
        ));
    }


    public function save(Request $request)
    {


        $rota = new Leave_Request();

        $rota->start_date = strtotime($request->startdate);
        $rota->end_date = strtotime($request->enddate);
        $rota->doctor_id = $request->doctor_id;
        $rota->save();

        return redirect('admin/request');
    }


    public function create($id)
    {

        $doctor = Doctor::find($id);
        $weekday = Weekday::get();

        return \View::make('admin.rota.request.create', compact(
            'doctor',
            'weekday'
        ));
    }


    public function store(Request $request)
    {
        //    return $request;
        // dd($request->dutydate);

        $evening = new Rota_Request();
        $evening->doctor_id = $request->doctor_id;
        $evening->duty_date = strtotime($request->dutydate);
        $evening->week_day_id = $request->weekday_id;


        if ($request->shiftday == 'general') {
            $evening->is_general = 1;
        } elseif ($request->shiftday == 'morning') {
            $evening->is_morning = 1;
        } elseif ($request->shiftday == 'night') {
            $evening->is_night = 1;
        } elseif ($request->shiftday == 'cc') {
            $evening->is_cc= 1;
        }

        if ($request->duty == 'wantduty') {
            $evening->want_duty = 1;
        }
        elseif ($request->duty == 'wantoff') {
            $evening->want_off = 1;

        }

        $evening->save();




        return redirect('admin/request');
    }
}
