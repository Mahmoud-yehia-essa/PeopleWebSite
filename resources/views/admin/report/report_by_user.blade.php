@extends('admin.dashboard')
@section('admin')

<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">طلب تقرير حول طلبات مستخدم معين</div>
					<div class="ps-3"> 
						<nav aria-label="breadcrumb">
						
						</nav>
					</div>

				</div>
				<!--end breadcrumb-->

				<hr/>




<div class="row row-cols-1 row-cols-md-1 row-cols-lg-3 row-cols-xl-3">


 <form method="post" action="{{ route('search-by-user')}}">
		@csrf
		<div class="col">
			<div class="card">

				<div class="card-body">
					<h5 class="card-title">البحث بالمستخدم</h5>


	  <label class="form-label">اسم المستخدم:</label>
		<select name="user" class="form-select mb-3" aria-label="Default select example">
		<option selected="">اختر اسم المستخدم</option>
		@foreach($users as $user)
		<option value="{{ $user->id }}">{{ $user->username }}</option>
		 @endforeach
	</select>


		<br>
		<input type="submit" class="btn btn-rounded btn-primary" value="بحث">
				</div>


			</div>
		</div>
	</form>



				</div> 


			</div>




@endsection