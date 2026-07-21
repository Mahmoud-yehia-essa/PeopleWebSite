@extends('admin.master_admin')
@section('admin')

<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">الكوبونات المضافة</div>
					<div class="ps-3">

					</div>
					<div class="ms-auto">

					</div>
				</div>
				<!--end breadcrumb-->

				<hr/>
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table id="example" class="table table-striped table-bordered" style="width:100%">
								<thead>
			<tr>
				<th>الرقم</th>
				<th>اسم الكوبون </th>
				<th>الخصم</th>
				<th>تاريخ الانتهاء</th>
				<th>حالة الكوبون</th>
				<th>الاجراء</th>
			</tr>
		</thead>
		<tbody>
	@foreach($coupon as $key => $item)
			<tr>
				<td> {{ $key+1 }} </td>
				<td> {{ $item->coupon_name }}</td>
				<td> {{ $item->coupon_discount }}%  </td>
				<td> {{ Carbon\Carbon::parse($item->coupon_validity)->format('D, d F Y') }}  </td>


				<td>
@if($item->coupon_validity >= Carbon\Carbon::now()->format('Y-m-d'))
<span class="badge rounded-pill bg-success">صالح للاستخدام</span>
@else
<span class="badge rounded-pill bg-danger">غير صالح للاستخدام</span>
@endif

				  </td>

				<td>
					<a href="{{ route('edit.coupon',$item->id) }}" class="btn btn-info">تعديل</a>
					<a href="{{ route('delete.coupon',$item->id) }}" class="btn btn-danger" id="delete" >حذف</a>
				</td>
			</tr>
			@endforeach


		</tbody>
		<tfoot>
			<tr>
					<th>الرقم</th>
				<th>اسم الكوبون </th>
				<th>الخصم</th>
				<th>تاريخ الانتهاء</th>
				<th>حالة الكوبون</th>
				<th>الاجراء</th>
			</tr>
		</tfoot>
	</table>
						</div>
					</div>
				</div>



			</div>




@endsection
