<?php
$admin_common = session()->get('admin_common');
?>
@extends('layouts.default_header')

@section('css')
<link rel="stylesheet" href="{{ asset('theme/vendor/chosen_v1.4.0/chosen.min.css') }}">
<link rel="stylesheet" href="{{ asset('theme/vendor/datatables/media/css/jquery.dataTables.css') }}">
<link rel="stylesheet" href="{{ asset('theme/vendor/bootstrap-daterangepicker/daterangepicker-bs3.css') }}">
@stop


@section('Admin')
    Welcome {!! $admin_common->name !!}
@stop
 @section('content')  
 	           <ol class="breadcrumb">
                <li>
                  <a href="{{asset('index.php/admin/dashboard')}}"><i class="ti-home mr5"></i>Dashboard</a>
                </li>
                <li>
                  <a href=""><i class="ti-window mr5"></i>Reports</a>
                </li>
                <li class="active">@yield('report_name')</li>
              </ol>

            <h3>@yield('report_name')<br/>
			<small>
				@yield('report_description')
			</small>
			</h3>
            <br>
            <section class="panel panel-default">
              <header class="panel-heading">
                <div class="row">
                  @yield('form') 
                </div>   
				</header>
				<div class="panel-body">                                          
                <div class="table-responsive no-border">
                  <div class="demo-button margin-bottom">                      
                     @yield('excel')
                    </div>
                    <table class="table table-bordered table-striped mg-t editable-datatable">
                    
                    @yield('table')
                  </table>
                    @yield('pagination')
                </div>
              </div>
 	</section>
  
  <!-- build:js({.tmp,app}) scripts/app.min.js -->
  <script src="{{ asset('theme/vendor/jquery/dist/jquery.js') }}"></script>
  <!-- endbuild -->

  <!-- page level scripts -->
  <script src="{{ asset('theme/vendor/chosen_v1.4.0/chosen.jquery.min.js') }}"></script>
  <script src="{{ asset('theme/vendor/datatables/media/js/jquery.dataTables.js') }}"></script>
  <!-- /page level scripts -->

 <!-- page level scripts -->
  <script src="{{ asset('theme/vendor/moment/moment.js') }}"></script>
  <script src="{{ asset('theme/vendor/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script src="{{ asset('theme/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.js') }}"></script>
  <script src="{{ asset('theme/vendor/jt.timepicker/jquery.timepicker.min.js') }}"></script>
  <script src="{{ asset('theme/vendor/mjolnic-bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js') }}"></script>
  <script src="{{ asset('theme/vendor/bootstrap-colorpalette/js/bootstrap-colorpalette.js') }}"></script>
  <!-- /page level scripts -->

  <!-- page script -->
  <script src="{{ asset('theme/scripts/pickers.js') }}"></script>

  <!-- page script -->
  <script src="{{ asset('theme/scripts/bootstrap-datatables.js') }}"></script>
  <script src="{{ asset('theme/scripts/table-edit.js') }}"></script>
  <!-- /page script -->

@stop
