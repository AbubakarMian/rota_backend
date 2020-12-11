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
   ////
   Route::get('rota/request/{id}', 'Admin\Rota_RequestController@create')->name('rota.request');
   Route::post('admin/rota/request/save', 'Admin\Rota_RequestController@store')->name('request.save');


    //////////ROTA
   Route::get('admin/rotadoctor', 'Admin\Rota_Controller@index')->name('rota.index');
   Route::get('admin/list/create', 'Admin\Rota_Controller@create')->name('doctor.list.create');
   Route::post('admin/list/save', 'Admin\Rota_Controller@save')->name('admin.doctorlist.save');
//////rota generate

Route::get('admin/rota/generate/{id}', 'Admin\Rota_Controller@generate')->name('rota.generate');

Route::get('admin/rota/check', 'Admin\Rota_Controller@check')->name('rota.check');
