@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-success font-weight-bold" style="border-left: 3px solid #d4af37; padding-left: 15px;">نتائج التقارير والمتابعة</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-warning"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('report.view') }}">لوحة التقارير</a></li>
                    <li class="breadcrumb-item active" aria-current="page">نتائج التقرير</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('report.view') }}" class="btn btn-warning px-4 d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #d4af37 0%, #aa8418 100%); border: none; font-weight: bold; color: #fff;">
                <i class="bx bx-arrow-back"></i> الرجوع للبحث
            </a>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Header Banner -->
    <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #052c16 0%, #0f5132 100%); border-right: 5px solid #d4af37;">
        <div class="card-body p-4 text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2 font-weight-bold" style="color: #d4af37;">{{ $periodTitle }}</h4>
                    <p class="mb-0 text-white-50">إحصائيات شاملة ومؤشرات أداء متكاملة للفترة المحددة لنشاط منصة حكماء العالم.</p>
                </div>
                <div class="d-none d-lg-block">
                    <i class="bx bx-pie-chart-alt-2" style="font-size: 4rem; color: rgba(212, 175, 55, 0.4);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Numerical Metrics Cards (KPIs) -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-3 mb-4">
        <!-- New Users Card -->
        <div class="col">
            <div class="card radius-10 border-0 border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary font-weight-bold">المستخدمين الجدد</p>
                            <h3 class="mb-0 text-success font-weight-bold counter-value">{{ number_format($usersCount) }}</h3>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-success text-success p-2">
                            <i class="bx bxs-user-plus fs-3"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Posts Card -->
        <div class="col">
            <div class="card radius-10 border-0 border-start border-info border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary font-weight-bold">المواضيع الجديدة</p>
                            <h3 class="mb-0 text-info font-weight-bold counter-value">{{ number_format($postsCount) }}</h3>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-info text-info p-2">
                            <i class="bx bx-detail fs-3"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved Friendships Card -->
        <div class="col">
            <div class="card radius-10 border-0 border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary font-weight-bold">علاقات الصداقة المقبولة</p>
                            <h3 class="mb-0 text-danger font-weight-bold counter-value">{{ number_format($friendshipsCount) }}</h3>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger p-2">
                            <i class="bx bx-group fs-3"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Groups Created Card -->
        <div class="col">
            <div class="card radius-10 border-0 border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary font-weight-bold">المجموعات المنشأة</p>
                            <h3 class="mb-0 text-warning font-weight-bold counter-value">{{ number_format($groupsCount) }}</h3>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning p-2">
                            <i class="bx bx-hive fs-3"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stories Uploaded Card -->
        <div class="col">
            <div class="card radius-10 border-0 border-start border-purple border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary font-weight-bold">القصص المرفوعة</p>
                            <h3 class="mb-0 text-purple font-weight-bold counter-value">{{ number_format($storiesCount) }}</h3>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-purple text-purple p-2">
                            <i class="bx bx-images fs-3"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-purple" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Most Active User Row -->
    <div class="row mb-4">
        <!-- 3D Pie Chart Card -->
        <div class="col-12 col-lg-7 d-flex">
            <div class="card shadow-sm border-0 w-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="card-title font-weight-bold text-dark mb-0">تمثيل بياني تفاعلي لتوزيع الأنشطة</h5>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 350px;">
                    @if(($usersCount + $postsCount + $friendshipsCount + $groupsCount + $storiesCount) > 0)
                        <div id="piechart_3d" style="width: 100%; height: 350px;"></div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-pie-chart fs-1 text-secondary opacity-50 mb-3"></i>
                            <h6 class="text-muted">لا توجد بيانات أنشطة كافية في هذه الفترة لرسم المخطط البياني.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Most Active User Card -->
        <div class="col-12 col-lg-5 d-flex">
            <div class="card shadow-sm border-0 w-100 overflow-hidden" style="border-radius: 12px; background: #fff;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="card-title font-weight-bold text-dark mb-0">العضو الأكثر نشاطاً في النشر</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center text-center">
                    @if($mostActiveUser)
                        @php
                            $userPhoto = (!empty($mostActiveUser->profile_picture) && $mostActiveUser->profile_picture != 'non') 
                                ? (filter_var($mostActiveUser->profile_picture, FILTER_VALIDATE_URL) ? $mostActiveUser->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$mostActiveUser->profile_picture) 
                                : url('upload/no_image.jpg');
                        @endphp
                        
                        <!-- Avatar with custom gold border decoration -->
                        <div class="position-relative mb-3">
                            <img src="{{ $userPhoto }}" alt="{{ $mostActiveUser->fname }}" class="rounded-circle p-1" style="width: 110px; height: 110px; object-fit: cover; border: 3px solid #d4af37; box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);">
                            <span class="position-absolute bottom-0 start-50 translate-middle-x badge rounded-pill bg-warning text-dark font-weight-bold px-3 py-1 shadow-sm">الأكثر نشاطاً</span>
                        </div>

                        <h5 class="font-weight-bold text-dark mb-1">{{ $mostActiveUser->fname }} {{ $mostActiveUser->lname }}</h5>
                        <p class="text-muted mb-2 small">{{ $mostActiveUser->email }}</p>

                        <!-- Points & Posts Statistics Grid -->
                        <div class="row w-100 g-2 mt-2">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 shadow-none border-0">
                                    <h5 class="font-weight-bold text-success mb-1">{{ $mostActiveUser->posts_count }}</h5>
                                    <span class="text-secondary small">منشور بالمنصة</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 shadow-none border-0">
                                    <h5 class="font-weight-bold text-warning mb-1">{{ $mostActiveUser->points ?? 0 }}</h5>
                                    <span class="text-secondary small">نقطة في الرصيد</span>
                                </div>
                            </div>
                        </div>

                        <!-- Extra details badge -->
                        <div class="mt-3 w-100">
                            <span class="badge w-100 py-2 fs-6 text-success" style="background-color: #e2f0d9; border: 1px solid rgba(25, 135, 84, 0.15);">
                                تاريخ التسجيل: {{ $mostActiveUser->created_at->format('Y-m-d') }}
                            </span>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-user-voice fs-1 text-secondary opacity-50 mb-3"></i>
                            <h6 class="text-muted">لم يقم أي عضو بنشر مواضيع خلال هذه الفترة.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="row">
        <!-- Most Commented Posts Table -->
        <div class="col-12 col-xl-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                    <h5 class="card-title font-weight-bold text-dark mb-0">أكثر 5 مواضيع تعليقاً</h5>
                    <span class="badge bg-success-light text-success font-weight-bold px-3 py-1">الأعلى تفاعلاً</span>
                </div>
                <div class="card-body p-4">
                    @if($topCommentedPosts && $topCommentedPosts->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>صاحب الموضوع</th>
                                        <th>محتوى المنشور (معاينة)</th>
                                        <th class="text-center">عدد التعليقات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topCommentedPosts as $post)
                                        @php
                                            $postOwnerPhoto = (!empty($post->user->profile_picture) && $post->user->profile_picture != 'non') 
                                                ? (filter_var($post->user->profile_picture, FILTER_VALIDATE_URL) ? $post->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$post->user->profile_picture) 
                                                : url('upload/no_image.jpg');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $postOwnerPhoto }}" alt="owner" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #ddd;">
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $post->user->fname }} {{ $post->user->lname }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-secondary small text-truncate d-inline-block" style="max-width: 250px;">
                                                    {{ Str::limit(strip_tags($post->content), 80) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success font-weight-bold fs-6 px-3 py-1" style="background-color: #198754 !important;">
                                                    {{ $post->comments_count_period }} تعليق
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-message-detail fs-1 text-secondary opacity-50 mb-3"></i>
                            <h6 class="text-muted">لا توجد تعليقات على المواضيع خلال هذه الفترة.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Most Reacted/Liked Posts Table -->
        <div class="col-12 col-xl-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                    <h5 class="card-title font-weight-bold text-dark mb-0">أكثر 5 مواضيع تفاعلاً وإعجاباً</h5>
                    <span class="badge bg-warning-light text-warning font-weight-bold px-3 py-1" style="background-color: #fff2cc; color: #ffc107 !important;">الأعلى إعجاباً</span>
                </div>
                <div class="card-body p-4">
                    @if($topReactedPosts && $topReactedPosts->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>صاحب الموضوع</th>
                                        <th>محتوى المنشور (معاينة)</th>
                                        <th class="text-center">عدد التفاعلات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topReactedPosts as $post)
                                        @php
                                            $postOwnerPhoto = (!empty($post->user->profile_picture) && $post->user->profile_picture != 'non') 
                                                ? (filter_var($post->user->profile_picture, FILTER_VALIDATE_URL) ? $post->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$post->user->profile_picture) 
                                                : url('upload/no_image.jpg');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $postOwnerPhoto }}" alt="owner" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #ddd;">
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $post->user->fname }} {{ $post->user->lname }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-secondary small text-truncate d-inline-block" style="max-width: 250px;">
                                                    {{ Str::limit(strip_tags($post->content), 80) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning text-dark font-weight-bold fs-6 px-3 py-1" style="background-color: #ffc107 !important;">
                                                    {{ $post->reactions_count_period }} تفاعل
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-like fs-1 text-secondary opacity-50 mb-3"></i>
                            <h6 class="text-muted">لا توجد تفاعلات أو إعجابات على المواضيع خلال هذه الفترة.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for Chart rendering and KPI card counters -->
@if(($usersCount + $postsCount + $friendshipsCount + $groupsCount + $storiesCount) > 0)
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['النشاط', 'العدد'],
                ['المستخدمين الجدد',     {{ $usersCount }}],
                ['المواضيع الجديدة',      {{ $postsCount }}],
                ['طلبات الصداقة المقبولة',  {{ $friendshipsCount }}],
                ['المجموعات المنشأة',  {{ $groupsCount }}],
                ['القصص المرفوعة', {{ $storiesCount }}]
            ]);

            var options = {
                is3D: true,
                backgroundColor: 'transparent',
                chartArea: {width: '100%', height: '80%', left: 0, right: 0},
                colors: ['#198754', '#0dcaf0', '#dc3545', '#ffc107', '#6f42c1'],
                legend: {
                    position: 'bottom', 
                    alignment: 'center',
                    textStyle: {color: '#495057', fontSize: 12, fontName: 'Cairo'}
                },
                tooltip: {textStyle: {fontName: 'Cairo', fontSize: 12}}
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
            chart.draw(data, options);
        }

        // Make chart responsive on window resize
        window.addEventListener('resize', function() {
            drawChart();
        });
    </script>
@endif

<style>
    .widgets-icons-2 {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-light-success {
        background-color: #e2f0d9;
    }
    .bg-light-info {
        background-color: #e8f7fa;
    }
    .bg-light-danger {
        background-color: #fce8e6;
    }
    .bg-light-warning {
        background-color: #fff2cc;
    }
    .bg-light-purple {
        background-color: #ebe3f5;
    }
    .text-purple {
        color: #6f42c1;
    }
    .bg-purple {
        background-color: #6f42c1;
    }
    .bg-success-light {
        background-color: #e2f0d9;
    }
    .bg-warning-light {
        background-color: #fff2cc;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(25, 135, 84, 0.03);
    }
    th {
        font-weight: 700;
        color: #495057;
    }
</style>

@endsection
