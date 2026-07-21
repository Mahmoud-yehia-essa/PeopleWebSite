@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل الألعاب </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">

        </nav>
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
<th>اسم اللعبة</th>
<th>منشء اللعبة</th>


<th>الفئات المختارة في اللعبة</th>
<th> التاريخ</th>

<th>الاجراء</th>
</tr>
</thead>
<tbody>
@foreach($games as $key => $item)
<tr>
<td> {{ $key+1 }} </td>
<td>{{ $item->game_name}}</td>
<td>{{ $item->users->fname }}</td>


<td>
    @foreach ($item->gamesCategories as $index => $gamesCategorie)
        <span class="badge bg-danger">{{ $gamesCategorie->category->category_name }}</span>

        @if (($loop->index + 1) % 3 == 0)
            <br>
        @endif
    @endforeach
</td>




<td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</td>

<td>
<a href="{{route('details.games',$item->id)}}" class="btn btn-info">تفاصيل</a>
<a href="{{ route('delete.games',$item->id) }}" class="btn btn-danger" id="delete" >حذف</a>

</td>
</tr>
@endforeach


</tbody>
<tfoot>
<tr>
    <th>الرقم</th>
    <th>اسم اللعبة</th>
    <th>منشء اللعبة</th>


    <th>الفئات المختارة في اللعبة</th>
    <th> التاريخ</th>

    <th>الاجراء</th>
</tr>
</tfoot>
</table>
        </div>
    </div>
</div>



@endsection
