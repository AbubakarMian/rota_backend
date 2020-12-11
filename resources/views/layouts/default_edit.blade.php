  <?php
      $admin_common = session()->get('admin_common');
  ?>

@extends('layouts.default_header')

@section('content')
@section('Admin')
    Welcome {!! $admin_common->name !!}
@stop
</head>
<!-- body -->
<body>
  <div class="app">
    <section class="layout">
      <!-- main content -->
      <section class="main-content">
        <!-- content wrapper -->
        <div class="content-wrap">
          <!-- inner content wrapper -->
          <div class="wrapper">
            <section class="panel">
              <header class="panel-heading no-b">
                <h2>@yield('heading')</h2>
              </header>
              <div class="panel-body">


                  <div class="row">

                  <!-- left side -->
                    <?php
                    if(empty($col_size)){
                      $col_size = '6';
                    }
                    ?>
                    <div class="col-md-{!! $col_size !!}">
                    	@yield('leftsideform')
                    </div>



                  <!-- Lef side end -->


                  <!-- Right side start -->
                    <div class="col-md-6">
 						@yield('rightsideform')
                    </div>
                           <div class="col-md-5 pull-left">
                      <div class="form-group text-center">
                        <label></label>
                        <div>
                        	@yield('save')
                        </div>
                      </div>
                    </div>
                     <div class="col-md-5 pull-left">
                      <div class="form-group text-center">
                        <label></label>
                        <div>
                        	@yield('cancel')
                        </div>
                      </div>
                    </div>
                    <!-- Right side end -->
                  </div>
              </div>
            </section>
          </div>
          <!-- /inner content wrapper -->

        </div>
        <!-- /content wrapper -->
        <a class="exit-offscreen"></a>
      </section>
      <!-- /main content -->
    </section>
  </div>
<div class="form-group">

</div>
  <!-- build:js({.tmp,app}) scripts/app.min.js -->
  <script src="{{ asset('theme/vendor/jquery/dist/jquery.js') }}"></script>
  <!-- endbuild -->
	<script src="{{ asset('theme/vendor/parsleyjs/dist/parsley.min.js') }}"></script>
@stop


