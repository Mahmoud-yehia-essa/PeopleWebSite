@extends('admin.dashboard')
@section('admin')

<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">الطلبات خلال شهر</div>
					<div class="ps-3"> 
						<nav aria-label="breadcrumb">
					
						</nav>
					</div>
					<div class="ms-auto">
						<div class="btn-group">

						</div>
					</div>
				</div>
				<!--end breadcrumb-->
				 <h3>البحث في تاريخ : {{ $month }} - {{ $year }}</h3>
				<hr/>
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table id="example" class="table table-striped table-bordered" style="width:100%">
								<thead>
			<tr>
				<th>الرقم</th>
				<th>التاريخ </th>
				<th>الفاتورة </th>
				<th>القيمة </th>
				<th>طريقة الدفع </th>
				<th>الحالة </th>
				<th>الاجراء</th> 
			</tr>
		</thead>
		<tbody>
	@foreach($orders as $key => $item)		
			<tr>
				<td> {{ $key+1 }} </td>
				<td>{{ $item->order_date }}</td>
				<td>{{ $item->invoice_no }}</td>
				<td>${{ $item->amount }}</td>
				<td>{{ $item->payment_method }}</td>
                <td> <span class="badge rounded-pill bg-success">
								
									@if ($item->status == 'pending')
                            
									الطلب قيد المعالجة
									@elseif($item->status == 'confirm')
										 تم تأكيد الطلب
										 @elseif($item->status == 'processing')
										 جاري تحضير الطلب
										 @else
										 تم تسليم الطلب
								 @endif
								
								
								</span></td> 

				<td>
<a href="{{ route('admin.order.details',$item->id) }}" class="btn btn-info" title="Details"><i class="fa fa-eye"></i> </a>

<a href="{{ route('admin.invoice.download',$item->id) }}" class="btn btn-danger" title="Invoice Pdf"><i class="fa fa-download"></i> </a>


				</td> 
			</tr>
			@endforeach


		</tbody>
		<tfoot>
			<tr>
				<th>الرقم</th>
				<th>التاريخ </th>
				<th>الفاتورة </th>
				<th>القيمة </th>
				<th>طريقة الدفع </th>
				<th>الحالة </th>
				<th>الاجراء</th> 
			</tr>
		</tfoot>
	</table>
						</div>
					</div>
				</div>



			</div>




@endsection