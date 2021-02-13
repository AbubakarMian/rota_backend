<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\models\Doctor;
use App\models\User;
use App\models\Doctor_type;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;

class DoctorController extends Controller
{
    public function index(Request $request)
    {


        $doctors = Doctor::with(['user','doctor_type'])->paginate(10);
        // $doctors = array_map('ucfirst', 'doctor_type');

        return view('admin.doctor.index', compact('doctors'));

    }

    public function create()
    {
        $control = 'create';
        $types = Doctor_type::pluck('name', 'id')->toArray();
        $types = array_map('ucfirst', $types);

        return \View::make(
            'admin.doctor.create',
            compact('control', 'types')
        );
    }


    public function save(Request $request)
    {
        $doctor = new Doctor();
        $user = new User();
        $user->email = $request->email;
        $this->add_or_update($request, $doctor, $user);

        return redirect('admin/doctor');
    }
    public function edit($id)
    {

        $control = 'edit';
        $doctor = Doctor::with('user')->find($id);
        $types = Doctor_type::pluck('name', 'id');
        return \View::make('admin.doctor.create', compact(
            'control',
            'doctor',
            'types'
        ));
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::find($id);
        $user = User::find($id);
        $type = Doctor_type::find($id);
        $this->add_or_update($request, $doctor, $user, $type);
        return Redirect('admin/doctor');
    }


    public function add_or_update(Request $request, $doctor, $user)
    {
        $user->name = $request->name;
        $user->fullname = $request->fullname;
        $user->save();

        $doctor->user_id = $user->id;
        $doctor->age = $request->age;
        $doctor->total_duties = $request->total_duties;

        $doctor->qualification = $request->qualification;
        $doctor->doctor_type_id = $request->doctor_type_id;
        $doctor->save();

        if ($request->hasFile('avatar')) {
            $avatar = $request->avatar;
            $root = $request->root();
            $user->avatar = $this->move_img_get_path($avatar, $root, 'image');
        } else if (strcmp($request->avatar_visible, "")  !== 0) {
            $user->avatar = $request->avatar_visible;
        }
        $user->save();
        $doctor->save();

        return redirect()->back();
    }



    public function search(Request $request)
    {
        $name = $request->name ?? '';

        $doctors = Doctor::whereHas('user', function ($query) use ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        })->with(['user' => function ($query) use ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }])->paginate(10);
        return view('admin.doctor.index', compact('doctors','name'));
    }

    public function destroy_undestroy($id)
    {

        $doctor = Doctor::find($id);
        if ($doctor) {
            Doctor::destroy($id);
            $new_value = 'Activate';
        } else {
            Doctor::withTrashed()->find($id)->restore();
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
