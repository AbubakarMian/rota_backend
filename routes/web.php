<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

     Route::get('admin/login', 'Admin\AdminController@index');
     Route::post('admin/checklogin', 'Admin\AdminController@checklogin');
     Route::get('admin/logout', 'Admin\AdminController@logout')->name('logout');

    Route::get('admin/dashboard', 'Admin\AdminController@dashboard')->name('dashboard');

 ////doctor
    Route::get('admin/doctor', 'Admin\DoctorController@index')->name('doctor.index');
    Route::get('admin/doctor/create', 'Admin\DoctorController@create')->name('doctor.create');
    Route::post('admin/doctor/save', 'Admin\DoctorController@save')->name('doctor.save');

    Route::get('admin/doctor/edit/{id}', 'Admin\DoctorController@edit')->name('doctor.edit');
    Route::post('admin/doctor/update/{id}', 'Admin\DoctorController@update')->name('doctor.update');
    Route::post('admin/doctor/delete/{id}', 'Admin\DoctorController@destroy_undestroy')->name('doctor.delete');


    Route::post('admin/doctor/save','Admin\DoctorController@save')->name('admin.doctor.save');
    Route::get('admin/doctor/search','Admin\DoctorController@search')->name('doctor.search');

   ////rota request
   Route::get('admin/request', 'Admin\Rota_RequestController@index')->name('rota.doctor.index');
   /////leave request
   Route::get('rota/leave/{id}', 'Admin\Rota_RequestController@leave')->name('rota.leave');
   Route::post('admin/rota/leave/save', 'Admin\Rota_RequestController@save')->name('leave.save');
   Route::get('admin/rota/leave/detail/{id}', 'Admin\Rota_RequestController@detail')->name('admin.leave.detail');
   Route::get('admin/rota/request/detail/{id}', 'Admin\Rota_RequestController@request')->name('admin.request.detail');

   ////rota request page
   Route::get('rota/request/{id}', 'Admin\Rota_RequestController@create')->name('rota.request');
   Route::post('admin/rota/request/save/{doctor_id}', 'Admin\Rota_RequestController@store')->name('request.save');


    //////////ROTA
   Route::get('admin/rota', 'Admin\Rota_Controller@index')->name('rota.index');
   Route::get('admin/list/create', 'Admin\Rota_Controller@create')->name('doctor.list.create');
   Route::post('admin/list/save', 'Admin\Rota_Controller@save')->name('admin.doctorlist.save');
//////rota generate

Route::get('admin/rota/generate/{id}', 'Admin\Rota_Controller@generate')->name('rota.generate');

Route::get('admin/rota/check', 'Admin\Rota_Controller@check')->name('rota.check');

//////////Rota Generate Pattern
Route::get('admin/rota/generate/pattern/{id}', 'Admin\Rota_Generate_Pattern_Controller@index')->name('rota.pattern.index');
Route::get('admin/rota/generate/pattern/create', 'Admin\Rota_Controller@create')->name('rota.generate.pattern.create');
Route::post('admin/rota/generate/pattern/save', 'Admin\Rota_Controller@save')->name('admin.rota_generate_pattern.save');

///general rota request crud

Route::get('admin/general/rota', 'Admin\General_Rota_Request_Controller@index')->name('general.rota.index');
Route::post('admin/general/rota', 'Admin\General_Rota_Request_Controller@index')->name('general.rota.search');
Route::get('admin/general/rota/create', 'Admin\General_Rota_Request_Controller@create')->name('general.rota.create');
Route::post('admin/general/rota/save', 'Admin\General_Rota_Request_Controller@save')->name('admin.general_rota_request.save');
// Route::get('admin/general_rota_request/search','Admin\General_Rota_Request_Controller@search')->name('general_rota_request.search');

//////special rota request

Route::get('admin/special/rota', 'Admin\Special_Rota_Request_Controller@index')->name('special.rota.index');
Route::get('admin/special/rota/create', 'Admin\Special_Rota_Request_Controller@create')->name('special.rota.create');
Route::post('admin/special/rota/save', 'Admin\Special_Rota_Request_Controller@save')->name('admin.special_rota_request.save');
Route::post('admin/special/rota', 'Admin\Special_Rota_Request_Controller@index')->name('special.rota.search');
// Route::get('admin/special_rota_request/search','Admin\Special_Rota_Request_Controller@search')->name('special_rota_request.search');

///////Monthly rota details

// Route::get('admin/monthly/rota/detail/{id}', 'Admin\Monthly_Rota_Details_Controller@detail')->name('admin.monthly.rota.detail');

