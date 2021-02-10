

<head>

    <meta charset="utf-8">
    <meta name="description" content="Flat, Clean, Responsive, application admin template built with bootstrap 3">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">


    <title>Calender</title>


    <link rel="stylesheet" href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css">
    <link rel="stylesheet" href="{{ asset('theme/vendor/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/styles/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/styles/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/styles/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/styles/sublime.css') }}">
    <link rel="stylesheet" href="{{ asset('cssjs/myapp.css') }}">
    <link rel="stylesheet" href="{{ asset('cssjs/jquery.timeentry.css') }}">
    <script src="{{ asset('theme/vendor/jquery.easing/jquery.easing.js') }}"></script>

    @yield('css')
    @yield('extra_css')

    <script src="{{ asset('theme/vendor/modernizr.js') }}"></script>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

</head>


<body>
    @yield('main_calender')



    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.2.6/jquery.inputmask.bundle.min.js"></script>

    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    @yield('app_jquery')

    <!-- page script -->
    <!-- /page script -->

    <!-- template scripts -->
    <script src="{{ asset('theme/scripts/main.js') }}"></script>
    <script src="{{ asset('theme/scripts/offscreen.js') }}"></script>
    @include('layouts.myapp_js')

    <!-- /template scripts -->

</body>
<!-- /body -->
