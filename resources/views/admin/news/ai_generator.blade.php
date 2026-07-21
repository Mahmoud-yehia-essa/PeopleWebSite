@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">إدارة الأخبار</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('all.news') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">توليد الأخبار بالذكاء الاصطناعي</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Generator Form Card -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-top border-0 border-4 border-primary">
                <div class="card-body p-5">
                    <div class="card-title d-flex align-items-center">
                        <div><i class="bx bx-chip me-1 font-22 text-primary"></i></div>
                        <h5 class="mb-0 text-primary">توليد الأخبار الذكي (الاصدار التجريبي)</h5>
                    </div>
                    <hr>
                    
                    <form id="ai-generator-form" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">اختر قسم/تصنيف الخبر</label>
                            <select class="form-select" id="news_category_id" required>
                                <option value="" selected disabled>اختر التصنيف المراد توليد الأخبار له...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">عدد الأخبار المراد توليدها</label>
                            <select class="form-select" id="count" required>
                                <option value="1">خبر واحد</option>
                                <option value="2">خبرين</option>
                                <option value="3" selected>3 أخبار</option>
                                <option value="5">5 أخبار</option>
                                <option value="8">8 أخبار</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label font-weight-bold">توجيهات أو ملاحظات إضافية للذكاء الاصطناعي (اختياري)</label>
                            <textarea class="form-control" id="custom_prompt" rows="3" placeholder="مثال: ركز على أسعار النفط الكويتي، أو اكتب عن البورصة الكويتية اليوم، أو صغ الأخبار بأسلوب عاجل ومختصر..."></textarea>
                        </div>

                        <div class="col-12 mt-4 text-start">
                            <button type="submit" id="submit-btn" class="btn btn-primary px-5 d-inline-flex align-items-center gap-2">
                                <i class="bx bx-magic-wand"></i>
                                توليد الأخبار الآن
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Area -->
    <div id="loading-area" class="row d-none">
        <div class="col-lg-12">
            <div class="card text-center p-5">
                <div class="card-body">
                    <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-4 font-weight-bold text-primary">جاري استشارة الذكاء الاصطناعي وتوليد الأخبار...</h5>
                    <p class="text-secondary text-sm">قد يستغرق ذلك بضع ثوانٍ للحصول على صياغة ممتازة ومترجمة.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Area -->
    <div id="error-area" class="alert alert-danger d-none" role="alert">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class="bx bx-x-circle"></i></div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">خطأ أثناء التوليد</h6>
                <div id="error-message" class="text-white">حدث خطأ ما أثناء توليد الأخبار. الرجاء المحاولة مجدداً.</div>
            </div>
        </div>
    </div>

    <!-- Results Area -->
    <div id="results-area" class="d-none">
        <h4 class="mb-3 font-weight-bold text-slate-800">الأخبار المقترحة والمولدة:</h4>
        <div id="articles-container" class="row">
            <!-- Dynamically populated via JS -->
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editArticleFieldModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold text-white" id="editModalTitle">تعديل المحتوى</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start" style="direction: rtl;">
                    <input type="hidden" id="edit-article-index">
                    <input type="hidden" id="edit-article-field">
                    
                    <div class="mb-3" id="single-line-container">
                        <label class="form-label font-weight-bold text-secondary">المحتوى الجديد</label>
                        <input type="text" class="form-control" id="edit-field-input">
                    </div>
                    
                    <div class="mb-3 d-none" id="multi-line-container">
                        <label class="form-label font-weight-bold text-secondary">المحتوى الجديد</label>
                        <textarea class="form-control" id="edit-field-textarea" rows="8"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="save-field-changes">حفظ التغييرات</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // تخزين الأخبار المولدة عالمياً لتسهيل التعديل
    window.generatedArticles = [];

    $('#ai-generator-form').on('submit', function(e) {
        e.preventDefault();
        
        const catId = $('#news_category_id').value || $('#news_category_id').val();
        const count = $('#count').val();
        
        if (!catId) {
            Swal.fire({
                icon: 'warning',
                title: 'تنبيه',
                text: 'الرجاء اختيار تصنيف الأخبار أولاً.',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // إخفاء النتائج والأخطاء وإظهار التحميل
        $('#results-area').addClass('d-none');
        $('#error-area').addClass('d-none');
        $('#loading-area').removeClass('d-none');
        $('#submit-btn').prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.ai_news.generate') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                news_category_id: catId,
                count: count,
                custom_prompt: $('#custom_prompt').val()
            },
            success: function(response) {
                $('#loading-area').addClass('d-none');
                $('#submit-btn').prop('disabled', false);

                if (response.success && response.articles && response.articles.length > 0) {
                    window.generatedArticles = response.articles;
                    renderArticles(window.generatedArticles, catId);
                    $('#results-area').removeClass('d-none');
                } else {
                    $('#error-message').text('فشل جلب الأخبار بشكل صحيح. يرجى إعادة المحاولة.');
                    $('#error-area').removeClass('d-none');
                }
            },
            error: function(xhr) {
                $('#loading-area').addClass('d-none');
                $('#submit-btn').prop('disabled', false);
                
                const errJson = xhr.responseJSON;
                const errMsg = errJson && errJson.message ? errJson.message : 'حدث خطأ في الاتصال بالسيرفر أثناء توليد الأخبار.';
                
                $('#error-message').text(errMsg);
                $('#error-area').removeClass('d-none');
            }
        });
    });

    function renderArticles(articles, catId) {
        const container = $('#articles-container');
        container.empty();

        articles.forEach((art, index) => {
            const cardHtml = `
                <div class="col-12 mb-4" id="article-card-${index}">
                    <div class="card shadow-sm border-start border-4 border-success">
                        <div class="card-body">
                            <div class="row">
                                <!-- Image Upload Column -->
                                <div class="col-md-3 border-end">
                                    <div class="mb-3 text-center">
                                        <label class="form-label font-weight-bold text-secondary d-block">صورة الخبر</label>
                                        <div class="image-preview-container border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative mx-auto" style="width: 100%; height: 160px;">
                                            <img class="article-preview-img w-100 h-100 object-cover d-none" id="preview-img-${index}" src="" alt="Preview">
                                            <div class="img-placeholder text-muted" id="placeholder-${index}">
                                                <i class="bx bx-image font-35 d-block mb-1"></i>
                                                <span class="small d-block">لم يتم اختيار صورة</span>
                                            </div>
                                        </div>
                                        <input type="file" class="form-control form-control-sm mt-2 article-image-file" data-index="${index}" accept="image/*">
                                        <small class="text-muted d-block mt-1" style="font-size: 10px;">صيغ مدعومة: JPG, PNG, JPEG</small>
                                    </div>
                                </div>

                                <!-- News Content Column -->
                                <div class="col-md-9">
                                    <div class="py-2">
                                        <h4 class="text-slate-800 font-bold mb-2 d-flex align-items-center gap-2">
                                            <span class="article-title">${escapeHtml(art.title) || 'بدون عنوان'}</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 edit-field-btn p-1" data-field="title" data-index="${index}" title="تعديل العنوان">
                                                <i class="bx bx-edit font-16"></i>
                                            </button>
                                        </h4>
                                        <p class="text-secondary font-weight-bold mb-3 d-flex align-items-center gap-2">
                                            <span class="article-des">${escapeHtml(art.des) || 'بدون ملخص'}</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 edit-field-btn p-1" data-field="des" data-index="${index}" title="تعديل الملخص">
                                                <i class="bx bx-edit font-14"></i>
                                            </button>
                                        </p>
                                        <div class="bg-light p-3 rounded text-slate-700 position-relative" style="line-height: 1.8rem;">
                                            <div class="article-more-des" style="white-space: pre-line;">${escapeHtml(art.more_des) || 'بدون تفاصيل'}</div>
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 edit-field-btn position-absolute top-0 end-0 m-2" data-field="more_des" data-index="${index}" title="تعديل المحتوى التفصيلي">
                                                <i class="bx bx-edit font-16"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Action Button -->
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><i class="bx bx-check-double"></i> جاهز للنشر الفوري بعد المراجعة والتعديل</span>
                                <button type="button" class="btn btn-success px-4 publish-btn d-inline-flex align-items-center gap-1" 
                                    data-index="${index}"
                                    data-title="${escapeHtml(art.title)}"
                                    data-des="${escapeHtml(art.des)}"
                                    data-more-des="${escapeHtml(art.more_des)}"
                                    data-cat-id="${catId}">
                                    <i class="bx bx-paper-plane"></i>
                                    نشر هذا الخبر مباشرة بالموقع
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(cardHtml);
        });

        // ربط حدث النقر لزر النشر
        $('.publish-btn').off('click').on('click', function() {
            const btn = $(this);
            const index = btn.data('index');
            
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري النشر...');

            // بناء FormData لإرسال الصورة والبيانات
            const formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('news_category_id', btn.data('cat-id'));
            formData.append('title', btn.data('title'));
            formData.append('des', btn.data('des'));
            formData.append('more_des', btn.data('more-des'));

            // إرفاق الصورة إذا تم اختيارها
            const fileInput = btn.closest('.card-body').find('.article-image-file')[0];
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                formData.append('photo', fileInput.files[0]);
            }

            $.ajax({
                url: "{{ route('admin.ai_news.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'نجاح النشر!',
                            text: res.message,
                            confirmButtonText: 'رائع'
                        });
                        
                        // استبدال الجزء السفلي بحالة النجاح
                        btn.parent().html('<span class="badge bg-success font-14 py-2 px-3"><i class="bx bx-check-shield"></i> تم النشر بنجاح على الموقع</span>');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل النشر',
                            text: res.message,
                            confirmButtonText: 'حسناً'
                        });
                        btn.prop('disabled', false);
                        btn.html('<i class="bx bx-paper-plane"></i> نشر هذا الخبر مباشرة بالموقع');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'حدث خطأ في الخادم أثناء نشر الخبر.',
                        confirmButtonText: 'حسناً'
                    });
                    btn.prop('disabled', false);
                    btn.html('<i class="bx bx-paper-plane"></i> نشر هذا الخبر مباشرة بالموقع');
                }
            });
        });
    }

    // معالجة اختيار الصورة وعرض معاينة فورية لها
    $(document).on('change', '.article-image-file', function(e) {
        const index = $(this).data('index');
        const file = e.target.files[0];
        const previewImg = $(`#preview-img-${index}`);
        const placeholder = $(`#placeholder-${index}`);

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.attr('src', e.target.result);
                previewImg.removeClass('d-none');
                placeholder.addClass('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            previewImg.attr('src', '');
            previewImg.addClass('d-none');
            placeholder.removeClass('d-none');
        }
    });

    // فتح الـ Modal لتعديل الحقل المختار
    $(document).on('click', '.edit-field-btn', function() {
        const index = $(this).data('index');
        const field = $(this).data('field');
        const article = window.generatedArticles[index];
        const val = article[field] || '';

        $('#edit-article-index').val(index);
        $('#edit-article-field').val(field);

        if (field === 'title') {
            $('#editModalTitle').text('تعديل عنوان الخبر');
            $('#single-line-container').removeClass('d-none');
            $('#multi-line-container').addClass('d-none');
            $('#edit-field-input').val(val);
        } else if (field === 'des') {
            $('#editModalTitle').text('تعديل التفاصيل القصيرة (الملخص)');
            $('#single-line-container').addClass('d-none');
            $('#multi-line-container').removeClass('d-none');
            $('#edit-field-textarea').val(val).attr('rows', 4);
        } else if (field === 'more_des') {
            $('#editModalTitle').text('تعديل التفاصيل الطويلة (محتوى الخبر كاملاً)');
            $('#single-line-container').addClass('d-none');
            $('#multi-line-container').removeClass('d-none');
            $('#edit-field-textarea').val(val).attr('rows', 8);
        }

        const modal = new bootstrap.Modal(document.getElementById('editArticleFieldModal'));
        modal.show();
    });

    // حفظ التعديلات من الـ Modal
    $('#save-field-changes').on('click', function() {
        const index = $('#edit-article-index').val();
        const field = $('#edit-article-field').val();
        let newVal = '';

        if (field === 'title') {
            newVal = $('#edit-field-input').val();
        } else {
            newVal = $('#edit-field-textarea').val();
        }

        // تحديث البيانات عالمياً
        window.generatedArticles[index][field] = newVal;

        // تحديث النص المعروض في البطاقة
        if (field === 'title') {
            $(`#article-card-${index} .article-title`).text(newVal);
        } else if (field === 'des') {
            $(`#article-card-${index} .article-des`).text(newVal);
        } else if (field === 'more_des') {
            $(`#article-card-${index} .article-more-des`).text(newVal);
        }

        // تحديث قيم الـ data-attributes في زر النشر
        const publishBtn = $(`#article-card-${index} .publish-btn`);
        if (field === 'title') {
            publishBtn.data('title', newVal);
            publishBtn.attr('data-title', newVal);
        } else if (field === 'des') {
            publishBtn.data('des', newVal);
            publishBtn.attr('data-des', newVal);
        } else if (field === 'more_des') {
            publishBtn.data('more-des', newVal);
            publishBtn.attr('data-more-des', newVal);
        }

        // إغلاق النافذة
        const modalEl = document.getElementById('editArticleFieldModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
</script>
@endsection
