@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل الاشعارات</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">

        </nav>
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
<th>العنوان</th>
<th> الوصف</th>
<th>الاجراء</th>
</tr>
</thead>
<tbody>
{{-- @foreach($category as $key => $item)
<tr>
<td> {{ $key+1 }} </td>
<td>{{ $item->category_name }}</td>
<td> <img src="{{ asset($item->category_photo) }}" style="width: 70px; height:40px;" >  </td>

<td>
<a href="{{route('edit.category',$item->id)}}" class="btn btn-info">تعديل</a>
<a href="{{ route('delete.category',$item->id) }}" class="btn btn-danger" id="delete" >حذف</a>

</td>
</tr>
@endforeach --}}


</tbody>
<tfoot>
<tr>
  <th>الرقم</th>
<th>العنوان</th>
<th> الوصف</th>
<th>الاجراء</th>
</tr>
</tfoot>
</table>
        </div>
    </div>
</div>



@endsection
