@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">تعديل كلمة المرور</div>

					<div class="ms-auto">

					</div>
				</div>
				<!--end breadcrumb-->
				<div class="container">
					<div class="main-body">
						<div class="row">

<div class="col-lg-12">
	<div class="card">
		<div class="card-body">
			<form method="post" action="{{ route('update.password') }}"  >
			@csrf

		 @if (session('status'))
		 <div class="alert alert-success" role="alert">
		 		{{session('status')}}
		 </div>
		 @elseif(session('error'))
		 <div class="alert alert-danger" role="alert">
		 	{{session('error')}}
		 </div>
		 @endif
			<div class="row mb-3">
				<div class="col-sm-3">
					<h6 class="mb-0">كلمة المرور القديمة</h6>
				</div>
				<div class="col-sm-9 text-secondary">
					<input type="password" name="old_password" class="form-control @error('old_password') is-invalid @enderror" id="current_password"   placeholder="كلمة المرور القديمة" />
					@error('old_password')
					<span class="text-danger">{{ $message }}</span>
					@enderror
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-sm-3">
					<h6 class="mb-0">كلمة المرور الجديدة</h6>
				</div>
				<div class="col-sm-9 text-secondary">
					<input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" id="new_password"   placeholder="كلمة المرور الجديدة" />
					@error('new_password')
					<span class="text-danger">{{ $message }}</span>
					@enderror
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-sm-3">
					<h6 class="mb-0">تأكيد كلمة المرور الجديدة</h6>
				</div>
				<div class="col-sm-9 text-secondary">
					<input type="password" name="new_password_confirmation" class="form-control" id="new_password_confirmation"   placeholder="تأكيد كلمة المرور الجديدة" />
				</div>
			</div>

			<div class="row">
				<div class="col-sm-3"></div>
				<div class="col-sm-9 text-secondary">
					<input type="submit" class="btn btn-primary px-4" value="حفظ التعديل" />
				</div>
			</div>
		</div>
		</form>
	</div>

							</div>
						</div>
					</div>
				</div>
			</div>

@endsection
