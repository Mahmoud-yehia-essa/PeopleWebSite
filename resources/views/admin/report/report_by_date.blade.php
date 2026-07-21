@extends('admin.master_admin')
@section('admin')

<script type="text/javascript">
  google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(drawChart);
  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['البند', 'العدد'],
      ['مشاهدات الأخبار',     {{$newsViews}}],
      ['الأخبار المضافة',     {{$news->count()}}],
      ['أخبار أنت عين الخبر',  {{$newsEye->count()}}],
      ['المجموعات النقاشية', {{$groups->count()}}],
      ['طلبات حماك الخير',   {{$helpRequests->count()}}],
      ['المكتبة الصوتية',    {{$soundLibrary->count()}}],
      ['المستخدمين الجدد',   {{$users->count()}}],
      ['الألعاب المنشأة',     {{$games->count()}}],
      ['الأسئلة المضافة',     {{$questions->count()}}],
    ]);

    var options = {
      title: 'توزيع النشاطات خلال الفترة: {{ $formatDate }}',
      is3D: true,
      colors: ['#0ea5e9', '#ef4444', '#10b981', '#fbbf24', '#a855f7', '#6366f1', '#f43f5e', '#ec4899', '#14b8a6', '#06b6d4']
    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
    chart.draw(data, options);
  }
</script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">التقارير والمتابعة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">نتائج التقرير للفترة المحددة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="d-flex align-items-center justify-content-between mb-4">
    <h3>نتائج تقرير المتابعة للفترة: <span class="text-primary font-bold">{{ $formatDate }}</span></h3>
    <a href="{{ route('report.view') }}" class="btn btn-primary d-flex align-items-center gap-1">
        <i class="bx bx-arrow-back"></i>
        <span>تغيير الفترة الزمنية</span>
    </a>
</div>

<hr/>

@php
    $totalActivity = $newsViews + $news->count() + $newsEye->count() + $groups->count() + $helpRequests->count() + $soundLibrary->count() + $users->count() + $games->count() + $questions->count();
@endphp

@if ($totalActivity == 0)
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body text-center py-5">
            <div class="text-danger mb-3" style="font-size: 3rem;">
                <i class="bx bx-info-circle"></i>
            </div>
            <h4 class="text-danger">لا توجد بيانات متاحة أو نشاطات مسجلة خلال الفترة المحددة ({{ $formatDate }})</h4>
            <p class="text-muted">الرجاء اختيار فترة زمنية أخرى أو التحقق من وجود نشاطات في التواريخ المحددة.</p>
        </div>
    </div>
@else
    <!-- First Row: Core Requested Metrics -->
    <h5 class="mb-3 text-secondary">التقارير والمتابعة الأساسية</h5>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
        <!-- 1. News Views -->
        <div class="col">
            <div class="card radius-10 bg-gradient-ohhappiness">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 text-white">{{ number_format($newsViews) }}</h4>
                        <div class="ms-auto text-white fs-3">
                            <i class='bx bx-show'></i>
                        </div>
                    </div>
                    <div class="progress my-2 bg-opacity-25 bg-white" style="height:4px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="d-flex align-items-center text-white">
                        <p class="mb-0">عدد مشاهدات الأخبار</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. News Added -->
        <div class="col">
            <div class="card radius-10 bg-gradient-deepblue">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 text-white">{{ $news->count() }}</h4>
                        <div class="ms-auto text-white fs-3">
                            <i class='bx bx-news'></i>
                        </div>
                    </div>
                    <div class="progress my-2 bg-opacity-25 bg-white" style="height:4px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="d-flex align-items-center text-white">
                        <p class="mb-0">عدد الأخبار المضافة</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. News Eye Posts -->
        <div class="col">
            <div class="card radius-10 bg-gradient-ibiza">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 text-white">{{ $newsEye->count() }}</h4>
                        <div class="ms-auto text-white fs-3">
                            <i class='bx bx-camera'></i>
                        </div>
                    </div>
                    <div class="progress my-2 bg-opacity-25 bg-white" style="height:4px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="d-flex align-items-center text-white">
                        <p class="mb-0">عدد أخبار أنت عين الخبر</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Discussion Groups -->
        <div class="col">
            <div class="card radius-10 bg-gradient-moonlit">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 text-white">{{ $groups->count() }}</h4>
                        <div class="ms-auto text-white fs-3">
                            <i class='bx bx-group'></i>
                        </div>
                    </div>
                    <div class="progress my-2 bg-opacity-25 bg-white" style="height:4px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="d-flex align-items-center text-white">
                        <p class="mb-0">عدد المجموعات المنشأة</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Hmak Al-Khair Help Requests -->
        <div class="col">
            <div class="card radius-10 bg-gradient-blooker">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 text-white">{{ $helpRequests->count() }}</h4>
                        <div class="ms-auto text-white fs-3">
                            <i class='bx bx-donate-heart'></i>
                        </div>
                    </div>
                    <div class="progress my-2 bg-opacity-25 bg-white" style="height:4px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="d-flex align-items-center text-white">
                        <p class="mb-0">عدد طلبات حماك الخير</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Sound Library Items -->
        <div class="col">
            <div class="card radius-10 bg-gradient-scooter">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 text-white">{{ $soundLibrary->count() }}</h4>
                        <div class="ms-auto text-white fs-3">
                            <i class='bx bx-music'></i>
                        </div>
                    </div>
                    <div class="progress my-2 bg-opacity-25 bg-white" style="height:4px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="d-flex align-items-center text-white">
                        <p class="mb-0">عدد مواد المكتبة الصوتية</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Secondary General Metrics -->
    <h5 class="mb-3 mt-4 text-secondary">إحصائيات عامة إضافية</h5>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
        <!-- Users registered -->
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">المستخدمين المسجلين</p>
                            <h4 class="my-1 text-info">{{ $users->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                            <i class='bx bxs-user-plus'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Games -->
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">الألعاب المنشأة</p>
                            <h4 class="my-1 text-danger">{{ $games->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                            <i class='bx bx-game'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">الأسئلة المضافة</p>
                            <h4 class="my-1 text-success">{{ $questions->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                            <i class='bx bx-question-mark'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">تصنيفات الأسئلة</p>
                            <h4 class="my-1 text-warning">{{ $category->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                            <i class='bx bx-list-ul'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div id="piechart_3d" style="width: 100%; height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
