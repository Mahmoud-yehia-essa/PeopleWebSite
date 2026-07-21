@extends('admin.master_admin')
@section('admin')

<!-- Custom Styling for Ratings Dashboard -->
<style>
    .post-card {
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .post-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }
    .rating-badge {
        font-size: 16px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .rating-high {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.2);
    }
    .rating-medium {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    .rating-low {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }
    .rating-none {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.2);
    }
    .wise-history-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px 15px;
        border-right: 4px solid #008cff;
        margin-bottom: 8px;
    }
</style>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #008cff; padding-left: 10px; font-weight: bold;">تقييم المواضيع</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.wise_committees.index') }}">لجنة الحكماء</a></li>
                <li class="breadcrumb-item active" aria-current="page">تقييم مواضيع الأعضاء</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<!-- Header Alert/Status -->
@if(!$isWiseMember)
<div class="alert alert-warning border-0 bg-light-warning alert-dismissible fade show py-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center">
        <div class="font-35 text-warning"><i class="bx bx-info-circle"></i></div>
        <div class="ms-3">
            <h6 class="mb-1 text-dark font-weight-bold" style="font-size: 15px;">تنبيه: وضع المشاهدة فقط</h6>
            <div class="text-secondary" style="font-size: 13px;">حسابك الحالي ليس عضواً نشطاً في لجنة الحكماء. يمكنك استعراض تقييمات الحكماء للمواضيع ولكن لا يمكنك تسجيل تقييمات جديدة.</div>
        </div>
    </div>
</div>
@else
<div class="alert alert-success border-0 bg-light-success alert-dismissible fade show py-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center">
        <div class="font-35 text-success"><i class="bx bx-check-circle"></i></div>
        <div class="ms-3">
            <h6 class="mb-1 text-dark font-weight-bold" style="font-size: 15px;">حالة العضوية: حكيم معتمد ومفعل</h6>
            <div class="text-secondary" style="font-size: 13px;">يمكنك تقييم مواضيع الأعضاء أدناه وتغيير معدل الحكماء العام للموضوع وكتابة مبررات تقييمك.</div>
        </div>
    </div>
</div>
@endif

<!-- Filters Card -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.wise_committees.ratings') }}" class="row g-3 align-items-end">
            <!-- 1. Text Search -->
            <div class="col-md-4">
                <label for="search" class="form-label font-weight-bold text-dark" style="font-size: 13.5px;">البحث عن موضوع</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" id="search" class="form-control" placeholder="ابحث في محتوى الموضوع..." value="{{ request('search') }}">
                </div>
            </div>
            
            <!-- 2. Select Publisher (User) -->
            <div class="col-md-3">
                <label for="user_id" class="form-label font-weight-bold text-dark" style="font-size: 13.5px;">ناشر الموضوع</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">كل المستخدمين</option>
                    @foreach($publishers as $pub)
                        <option value="{{ $pub->id }}" {{ request('user_id') == $pub->id ? 'selected' : '' }}>
                            {{ $pub->fname }} {{ $pub->lname }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- 3. Select Wise Member -->
            <div class="col-md-3">
                <label for="wise_member_id" class="form-label font-weight-bold text-dark" style="font-size: 13.5px;">فلترة بحكيم مقيّم</label>
                <select name="wise_member_id" id="wise_member_id" class="form-select">
                    <option value="">كل الحكماء</option>
                    @foreach($wiseMembers as $wm)
                        <option value="{{ $wm->user_id }}" {{ request('wise_member_id') == $wm->user_id ? 'selected' : '' }}>
                            {{ $wm->user->fname }} {{ $wm->user->lname }} ({{ $wm->specialty ?: 'عام' }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- 4. Actions -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-3 w-50" title="تصفية البحث"><i class="bx bx-filter-alt"></i> تصفية</button>
                <a href="{{ route('admin.wise_committees.ratings') }}" class="btn btn-secondary px-3 w-50" title="إعادة ضبط"><i class="bx bx-refresh"></i> إلغاء</a>
            </div>
        </form>
    </div>
</div>

<!-- List of Posts -->
<div class="row">
    <div class="col-12" id="postsContainer">
        @include('admin.wise_committees.partials.post_cards')
    </div>
</div>

<!-- Sentinel for Infinite Scroll -->
<div id="sentinel" style="height: 15px; margin-top: 10px;"></div>

<!-- Loading Indicator -->
<div id="loadingIndicator" class="text-center my-4" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">جاري التحميل...</span>
    </div>
    <p class="text-muted mt-2" style="font-size: 13px;">جاري تحميل المزيد من المواضيع...</p>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content position-relative bg-transparent border-0">
        <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 15px; right: 15px; background-color: black; font-size: 30px; padding: 1px 10px; border-radius: 8px; z-index: 1055;">&times;</button>
        <img id="modalImage" src="" class="img-fluid rounded shadow-lg mx-auto d-block" alt="preview">
      </div>
    </div>
</div>

<!-- Script for Image Modal & Lazy Loading -->
<script>
    function showImageModal(src) {
        document.getElementById('modalImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
        myModal.show();
    }

    // منطق التحميل الكسول (Lazy Loading / Infinite Scroll) باستخدام IntersectionObserver للمزيد من الكفاءة والأداء
    let page = 1;
    let hasMore = {{ $posts->hasMorePages() ? 'true' : 'false' }};
    let loading = false;

    // إعداد مراقب التقاطع (Intersection Observer)
    const sentinel = document.getElementById('sentinel');
    const observer = new IntersectionObserver((entries) => {
        const entry = entries[0];
        if (entry.isIntersecting && !loading && hasMore) {
            loadMorePosts();
        }
    }, {
        rootMargin: '200px' // تحميل المواضيع مسبقاً قبل وصول المستخدم الفعلي بـ 200 بكسل لتجربة تصفح سلسة
    });

    if (sentinel) {
        observer.observe(sentinel);
    }

    function loadMorePosts() {
        loading = true;
        document.getElementById('loadingIndicator').style.display = 'block';
        page++;

        // استخراج فلاتر البحث الحالية لتمريرها مع الطلب
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', page);

        fetch('{{ route("admin.wise_committees.ratings") }}?' + urlParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            document.getElementById('loadingIndicator').style.display = 'none';
            
            if (data.html && data.html.trim() !== '') {
                const container = document.getElementById('postsContainer');
                container.insertAdjacentHTML('beforeend', data.html);
                hasMore = data.hasMore;
            } else {
                hasMore = false;
            }
            loading = false;

            // إذا لم يعد هناك صفحات، نوقف مراقبة الـ sentinel
            if (!hasMore && sentinel) {
                observer.unobserve(sentinel);
            }
        })
        .catch(error => {
            console.error('حدث خطأ أثناء تحميل المواضيع:', error);
            loading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
        });
    }
</script>

@endsection
