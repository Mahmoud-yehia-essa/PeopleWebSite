@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-success font-weight-bold" style="border-left: 3px solid #d4af37; padding-left: 15px;">التقارير والمتابعة</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-warning"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">لوحة التقارير</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Welcome Header Banner -->
    <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #052c16 0%, #0f5132 100%); border-right: 5px solid #d4af37;">
        <div class="card-body p-4 text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2 font-weight-bold" style="color: #d4af37;">قسم التقارير وإحصائيات النظام</h4>
                    <p class="mb-0 text-white-50">اختر الفترة الزمنية المناسبة لاستخراج تقارير تفصيلية عن نشاط ومستجدات المنصة ببيانات مكتوبة ورسومية ممتازة.</p>
                </div>
                <div class="d-none d-lg-block">
                    <i class="bx bx-bar-chart-square" style="font-size: 4rem; color: rgba(212, 175, 55, 0.4);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms Grid -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">
        <!-- Daily Report Form -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm transition-hover" style="border-radius: 12px; border-top: 4px solid #198754 !important;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg bg-light-success text-success rounded-circle p-2 me-3">
                                <i class="bx bx-calendar-event fs-3 text-success"></i>
                            </div>
                            <h5 class="card-title mb-0 font-weight-bold text-dark">البحث باليوم</h5>
                        </div>
                        <p class="text-muted small">احصل على تقرير إحصائي مفصل لنشاط يوم محدد بالكامل.</p>
                        
                        <form method="post" action="{{ route('search-by-date') }}">
                            @csrf
                            <div class="mb-3 mt-4">
                                <label class="form-label text-secondary font-weight-bold">اختر التاريخ:</label>
                                <input type="date" name="date" class="form-control border-success-light" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%); border: none;">
                            <i class="bx bx-search-alt"></i> استخراج التقرير
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Monthly Report Form -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm transition-hover" style="border-radius: 12px; border-top: 4px solid #ffc107 !important;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg bg-light-warning text-warning rounded-circle p-2 me-3">
                                <i class="bx bx-calendar fs-3 text-warning"></i>
                            </div>
                            <h5 class="card-title mb-0 font-weight-bold text-dark">البحث بالشهر</h5>
                        </div>
                        <p class="text-muted small">احصل على تقرير إحصائي مفصل لنشاط شهر محدد بالكامل.</p>
                        
                        <form method="post" action="{{ route('search-by-month') }}">
                            @csrf
                            <div class="mb-3 mt-4">
                                <label class="form-label text-secondary font-weight-bold">اختر الشهر:</label>
                                <select name="month" class="form-select border-warning-light mb-3" required>
                                    <option value="non" disabled selected>-- اختر الشهر --</option>
                                    <option value="January" {{ old('month') == 'January' ? 'selected' : '' }}>يناير</option>
                                    <option value="February" {{ old('month') == 'February' ? 'selected' : '' }}>فبراير</option>
                                    <option value="March" {{ old('month') == 'March' ? 'selected' : '' }}>مارس</option>
                                    <option value="April" {{ old('month') == 'April' ? 'selected' : '' }}>أبريل</option>
                                    <option value="May" {{ old('month') == 'May' ? 'selected' : '' }}>مايو</option>
                                    <option value="June" {{ old('month') == 'June' ? 'selected' : '' }}>يونيو</option>
                                    <option value="July" {{ old('month') == 'July' ? 'selected' : '' }}>يوليو</option>
                                    <option value="August" {{ old('month') == 'August' ? 'selected' : '' }}>أغسطس</option>
                                    <option value="September" {{ old('month') == 'September' ? 'selected' : '' }}>سبتمبر</option>
                                    <option value="October" {{ old('month') == 'October' ? 'selected' : '' }}>أكتوبر</option>
                                    <option value="November" {{ old('month') == 'November' ? 'selected' : '' }}>نوفمبر</option>
                                    <option value="December" {{ old('month') == 'December' ? 'selected' : '' }}>ديسمبر</option>
                                </select>
                                @error('month')
                                    <div class="text-danger small mt-1 mb-2">{{ $message }}</div>
                                @enderror

                                <label class="form-label text-secondary font-weight-bold">اختر السنة:</label>
                                <select name="year_name" class="form-select border-warning-light" required>
                                    <option value="non" disabled selected>-- اختر السنة --</option>
                                    @for($y = date('Y'); $y >= 2024; $y--)
                                        <option value="{{ $y }}" {{ old('year_name') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('year_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning w-100 text-dark d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border: none; font-weight: bold;">
                            <i class="bx bx-search-alt"></i> استخراج التقرير
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Yearly Report Form -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm transition-hover" style="border-radius: 12px; border-top: 4px solid #0dcaf0 !important;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg bg-light-info text-info rounded-circle p-2 me-3">
                                <i class="bx bx-calendar-star fs-3 text-info"></i>
                            </div>
                            <h5 class="card-title mb-0 font-weight-bold text-dark">البحث بالسنة</h5>
                        </div>
                        <p class="text-muted small">احصل على تقرير إحصائي مفصل لنشاط سنة كاملة ومقارنة أدائها.</p>
                        
                        <form method="post" action="{{ route('search-by-year') }}">
                            @csrf
                            <div class="mb-3 mt-4">
                                <label class="form-label text-secondary font-weight-bold">اختر السنة:</label>
                                <select name="years" class="form-select border-info-light" required>
                                    <option value="non" disabled selected>-- اختر السنة --</option>
                                    @for($y = date('Y'); $y >= 2024; $y--)
                                        <option value="{{ $y }}" {{ old('years') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('years')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-info text-white w-100 d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #0dcaf0 0%, #0bacd0 100%); border: none;">
                            <i class="bx bx-search-alt"></i> استخراج التقرير
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Custom Date Range Report Form -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm transition-hover" style="border-radius: 12px; border-top: 4px solid #d4af37 !important;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg bg-light-gold rounded-circle p-2 me-3" style="background-color: #fdfaf2;">
                                <i class="bx bx-calendar-edit fs-3" style="color: #d4af37;"></i>
                            </div>
                            <h5 class="card-title mb-0 font-weight-bold text-dark">فترة مخصصة</h5>
                        </div>
                        <p class="text-muted small">احصل على تقرير مخصص بالكامل بنطاق التواريخ الذي تحدده بنفسك.</p>
                        
                        <form method="post" action="{{ route('search-by-range') }}">
                            @csrf
                            <div class="mb-3 mt-4">
                                <label class="form-label text-secondary font-weight-bold">تاريخ البداية (من):</label>
                                <input type="date" name="start_date" class="form-control border-gold-light mb-3" value="{{ old('start_date', date('Y-m-d', strtotime('-7 days'))) }}" required>
                                @error('start_date')
                                    <div class="text-danger small mt-1 mb-2">{{ $message }}</div>
                                @enderror

                                <label class="form-label text-secondary font-weight-bold">تاريخ النهاية (إلى):</label>
                                <input type="date" name="end_date" class="form-control border-gold-light" value="{{ old('end_date', date('Y-m-d')) }}" required>
                                @error('end_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn text-white w-100 d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #d4af37 0%, #aa8418 100%); border: none; font-weight: bold;">
                            <i class="bx bx-search-alt"></i> استخراج التقرير
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .border-success-light:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
    .border-warning-light:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    .border-info-light:focus {
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.25);
    }
    .border-gold-light {
        border: 1px solid #ced4da;
    }
    .border-gold-light:focus {
        border-color: #d4af37;
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
    }
    .avatar-lg {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-light-success {
        background-color: #e2f0d9;
    }
    .bg-light-warning {
        background-color: #fff2cc;
    }
    .bg-light-info {
        background-color: #e8f7fa;
    }
    .bg-light-gold {
        background-color: #fdfaf2;
    }
</style>

@endsection
