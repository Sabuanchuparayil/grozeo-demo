<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="grozeo">
        <meta name="keywords" content="grozeo">
        <meta name="author" content="grozeo">
        <link rel="shortcut icon" href="{{url('homepage/images/favicon.ico')}}" type="image/x-icon"/>
        <title>Grozeo</title>

        <!--Google font-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> 


        <!-- Bootstrap css -->
        <link rel="stylesheet" type="text/css" href="{{url('homepage/css/bootstrap.css')}}">

        <!-- Custom css -->
        <link rel="stylesheet" id="color" type="text/css" href="{{url('homepage/css/custom.css')}}">
    </head>
    <body>
        
        <header class="d-flex justify-content-center align-items-end py-3">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex justify-content-center">
                        <a target="_blank" href="https://grozeo.com/">
                            <img src="{{url('homepage/images/grozeo_logo.svg')}}">
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <section class="d-flex justify-content-center align-items-center py-4 p-md-0">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-6 col-lg-4 text-center">
                        <img class="mb-3 mb-lg-0" src="{{url('homepage/images/urlerror_img.png')}}">
                    </div><!--col-lg-4-->
                    <div class="col-12 col-md-6 col-lg-4">
                        <h1 class="error_tx mb-3">oops!</h1>
                        <p class="lh-sm m-0">The URL you try to reach is not available with Grozeo.</p>
                        <p class="lh-sm m-0">Please check and enter the correct URL.</p>
                        <a target="_blank" href="https://grozeo.com/" class="btn btn-outline-secondary rounded-pill px-4 mt-4">Back To Home</a>
                    </div><!--col-lg-4-->
                </div>
            </div>
        </section>

        <!-- latest jquery-->
        <script src="{{url('homepage/js/jquery-3.3.1.min.js')}}" ></script>
        <!-- Bootstrap js-->
        <script src="{{url('homepage/js/bootstrap.bundle.min.js')}}" ></script>

    </body>
</html>
