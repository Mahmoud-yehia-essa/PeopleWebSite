@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">التسويق بالعمولة (Affiliate)</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('all.affiliates') }}">روابط التسويق بالعمولة</a></li>
                <li class="breadcrumb-item active" aria-current="page">إعدادات نقاط المكافأة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card border-top border-0 border-4 border-primary">
            <div class="card-body p-4">
                <div class="card-title d-flex align-items-center mb-4">
                    <div><i class="bx bx-cog me-1 font-22 text-primary"></i></div>
                    <h5 class="mb-0 text-primary fw-bold">إعدادات نظام المكافآت ونقاط الإحالة</h5>
                </div>
                <hr>
                
                <form action="{{ route('update.affiliate_settings') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="reward_points_per_referral" class="form-label fw-bold">
                            <i class="bx bx-star text-warning"></i> نقاط المكافأة لكل تسجيل ناجح:
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bx bx-coin-stack"></i></span>
                            <input type="number" name="reward_points_per_referral" id="reward_points_per_referral" 
                                   class="form-control @error('reward_points_per_referral') is-invalid @enderror" 
                                   value="{{ old('reward_points_per_referral', $settings->reward_points_per_referral) }}" 
                                   min="0" required>
                        </div>
                        <div class="form-text text-muted">
                            عدد النقاط التي يحصل عليها المسوق في حسابه فور تسجيل عضو جديد بنجاح من خلال رابط الإحالة الخاص به.
                        </div>
                        @error('reward_points_per_referral')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_points_silver_rank" class="form-label fw-bold">
                                <i class="bx bx-medal text-secondary"></i> الحد الأدنى للدعوات لرتبة (سفير فضي):
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-user-plus"></i></span>
                                <input type="number" name="min_points_silver_rank" id="min_points_silver_rank" 
                                       class="form-control" 
                                       value="{{ old('min_points_silver_rank', $settings->min_points_silver_rank) }}" 
                                       min="1" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="min_points_gold_rank" class="form-label fw-bold">
                                <i class="bx bx-trophy text-warning"></i> الحد الأدنى للدعوات لرتبة (سفير ذهبي):
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-user-check"></i></span>
                                <input type="number" name="min_points_gold_rank" id="min_points_gold_rank" 
                                       class="form-control" 
                                       value="{{ old('min_points_gold_rank', $settings->min_points_gold_rank) }}" 
                                       min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_affiliate_enabled" name="is_affiliate_enabled" 
                                   {{ $settings->is_affiliate_enabled ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_affiliate_enabled">
                                تفعيل نظام التسويق بالعمولة وسفراء المنصة
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save"></i> حفظ الإعدادات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
