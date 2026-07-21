<!DOCTYPE html>
<html lang="en" class="semi-dark" dir="rtl">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	{{-- <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/png" /> --}}
<!-- loader-->
<link href="{{ asset('backend/assets/css/pace.min.css') }}" rel="stylesheet" />
<script src="{{ asset('backend/assets/js/pace.min.js') }}"></script>
<!-- Bootstrap CSS -->
<link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<link href="{{ asset('backend/assets/css/app.css') }}" rel="stylesheet">
<link href="{{ asset('backend/assets/css/icons.css') }}" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

<title>صحيفة حماك الإلكترونية</title>
<style>

		body
		{
			font-family: "Cairo", sans-serif;
		}



	</style>
</head>

<body>
	<!-- wrapper -->
	<div class="wrapper">

		<div class="error-404 d-flex align-items-center justify-content-center">
			<div class="card shadow-none bg-transparent">
				<div class="card-body text-center">
                    <img src="{{ asset('backend/assets/images/logo-hmak.png') }}" class="img-fluid" width="200" alt=""/>

                    <h2>مرحبا</h2>
                    <h3>{{auth()->user()->fname}}</h3>
                    <hr>

					<h2 class="display-7 mt-2 text-danger">انت غير مصرح لك الدخول الى لوحة التحكم
                    </h2>
                    <hr>

					<h3>يجب عليك التسجيل الدخول كمدير او تواصل مع الادارة لحل المشكلة.</h3>
                    <hr>

					<div class="row">
						<div class="col-12 col-lg-12 mx-auto">

                            <a href="{{route('admin.logout')}}" >
							<button type="button" class="btn btn-danger px-5">تسجيل الخروج</button>
                        </a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="bg-white p-3 fixed-bottom border-top bg-body">
			<div class="d-flex align-items-center justify-content-between flex-wrap">
				<ul class="list-inline mb-0">
					<li class="list-inline-item">تواصل معنا</li>
					<li class="list-inline-item"><a href="javascript:;"><i class='bx bxl-facebook me-1'></i>Facebook</a>
					</li>
					</li>
					<li class="list-inline-item"><a href="javascript:;"><i class='bx bxl-youtube me-1'></i>Youtube</a>
					</li>
                    <li class="list-inline-item"><a href="javascript:;"><i class='bx bxl-twitter me-1'></i>X</a>
					</li>
				</ul>
                <p class="mb-0">صحيفة حماك © 2025 جميع الحقوق محفوظة</p>
			</div>
		</div>
	</div>
	<!-- end wrapper -->
	<!-- Bootstrap JS -->
    <script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
