<!doctype html>
<html class="error-page no-js" lang="">
<head>
  <!-- meta -->
  <meta charset="utf-8">
  <meta name="description" content="Flat, Clean, Responsive, application admin template built with bootstrap 3">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
  <!-- /meta -->
  <title>404 Error</title>
  <!-- build:css({.tmp,app}) styles/app.min.css -->
  <link rel="stylesheet" href="{!! asset('theme/vendor/bootstrap/dist/css/bootstrap.min.css') !!}">
  <link rel="stylesheet" href="{!! asset('theme/styles/font-awesome.css') !!}">
  <link rel="stylesheet" href="{!! asset('theme/styles/themify-icons.css') !!}">
  <link rel="stylesheet" href="{!! asset('theme/styles/animate.css') !!}">
  <link rel="stylesheet" href="{!! asset('theme/styles/sublime.css') !!}">
  <!-- endbuild -->
  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  <!-- load modernizer -->
  <script src="{!! asset('theme/vendor/modernizr.js') !!}"></script>
</head>
<!-- body -->
<body class="bg-primary">

  <!-- error wrapper -->
  <div class="center-wrapper">

    <div class="center-content text-center">

      <div class="error-number animated bounceIn">404</div>

      <div class="mb25">PAGE NOT FOUND</div>

      <p>Sorry, but the page you were trying to view does not exist.</p>
    </div>
  </div>
  <!-- /error wrapper -->
  <script type="text/javascript">
    var el = document.getElementById("year"),
      year = (new Date().getFullYear());
    el.innerHTML = year;
  </script>
</body>
<!-- /body -->
</html>
