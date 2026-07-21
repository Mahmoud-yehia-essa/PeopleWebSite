@extends('admin.master_admin')
@section('admin')

<div class="card radius-10 bg-linkedin">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div>
                <p class="mb-0 text-white"> تاريخ انشاء اللعبة </p>
                <h5 class="my-1 text-white"> {{ $game->created_at->diffForHumans() }}</h5>
            </div>

        </div>
    </div>
</div>

<div class="col">

    <div class="card radius-10 bg-gradient-kyoto">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div>
                    <p class="mb-0 text-dark">اسم اللعبة</p>
                    <h4 class="text-dark my-1">{{$game->game_name}}</h4>
                </div>
                {{-- <div class="text-dark ms-auto font-35"><i class="bx bx-user-pin"></i>
                </div> --}}
            </div>
        </div>
    </div>
</div>

<div class="col">
    <div class="card radius-10 bg-gradient-burning">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div>
                    <p class="mb-0 text-white">الفريق الفائز</p>

                    <h4 class="my-1 text-white">
                        @php
                            // Get the highest result value
                            $maxResult = $teams->max('result');

                            // Filter teams that have the highest result
                            $winningTeams = $teams->where('result', $maxResult);
                        @endphp

                        @if ($winningTeams->count() > 1)
                            <p>تعادل</p>
                        @else
                            @foreach ($winningTeams as $team)
                                <p>{{ $team->team_name }}</p>
                                {{-- <p>{{ $team->result }}</p> --}}
                            @endforeach
                        @endif
                    </h4>
                </div>
                {{-- <div class="text-white ms-auto font-35"><i class="bx bx-dollar"></i>
                </div> --}}
            </div>
        </div>
    </div>
</div>


<div class="col">
    <div class="card radius-10 bg-gradient-moonlit">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div>
                    <p class="mb-0 text-white">تم إنشاء اللعبة من خلال العضو</p>
                    <a href="{{route('edit.user',$game->users->id)}}">
                    <h4 class="my-1 text-white">{{$game->users->fname}}</h4>
                </a>
                </div>
                {{-- <div class="text-white ms-auto font-35"><i class="bx bx-comment-detail"></i>
                </div> --}}
            </div>
        </div>
    </div>
</div>
<div class="col">
    <div class="card radius-10 overflow-hidden bg-gradient-cosmic">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="mb-2 text-black ">فئات اللعبة</p>
                    <h5 class="mb-0 text-white">

                        @foreach ($game->gamesCategories as $index => $gamesCategorie)
                        <a href="{{route('edit.category',$gamesCategorie->category->id)}}">
                        <span class="badge bg-dark">{{ $gamesCategorie->category->category_name }}</span>
                    </a>
                        @if (($loop->index + 1) % 3 == 0)
                            <br>
                        @endif
                    @endforeach
                    </h5>
                </div>
                {{-- <div class="text-white"><i class="bx bx-wallet font-30"></i>
                </div> --}}
            </div>
        </div>
    </div>
</div>



<hr>
<h5 class="mb-3 text-uppercase"> الفرق المشاركة والنقاط</h5>
<div class="card">
    <div class="card-body">
        <table class="table mb-0 table-hover">
            <thead>


                <tr>
                    <th scope="col">#</th>
                    <th scope="col">اسم الفريق</th>
                    <th scope="col">عدد الاعبين</th>
                    <th scope="col">النقاط</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($teams as $index => $item)

                <tr>
                    <th scope="row">{{$index+1}}</th>
                    <td>{{$item->team_name}}</td>
                    <td>{{$item->number_members}}</td>
                    <td>{{$item->result}}</td>
                </tr>

                @endforeach



            </tbody>
        </table>


    </div>

</div>
<hr>
<h5 class="mb-3 text-uppercase">أسئلة المسابقة</h5>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
<tr>
<th>الرقم</th>
<th>السؤال</th>
<th>منو جاوب</th>


</tr>
</thead>
<tbody>
@foreach($questionsRegister as $key => $item)
<tr>
<td> {{ $key+1 }} </td>
<td><a href="{{route('edit.question',$item->question_id)}}">{{ $item->question->qu_title}}</a></td>


<td>{{ $item->team->team_name ?? "ولا أحد"}}</td>

</tr>
@endforeach


</tbody>
<tfoot>
<tr>
    <th>الرقم</th>
    <th>السؤال</th>
    <th>منو جاوب</th>
</tr>
</tfoot>
</table>
        </div>
    </div>
</div>

@endsection
