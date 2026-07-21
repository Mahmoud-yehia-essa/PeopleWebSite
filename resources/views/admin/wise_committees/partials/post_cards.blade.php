@forelse($posts as $post)
    <div class="card post-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 pb-3 border-bottom gap-3">
                <!-- User Info -->
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ (!empty($post->user->photo) && $post->user->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$post->user->photo : url('upload/no_image.jpg') }}" class="rounded-circle shadow-sm" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid #e9ecef;" loading="lazy">
                    <div>
                        <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 15px;">{{ $post->user->fname }} {{ $post->user->lname }}</h6>
                        <span class="text-muted" style="font-size: 12px;">نشر: {{ $post->created_at ? $post->created_at->diffForHumans() : 'غير محدد' }}</span>
                    </div>
                </div>

                <!-- Current Wise Rating Badge -->
                <div>
                    @if($post->wise_rating)
                        @php
                            $ratingClass = 'rating-medium';
                            if($post->wise_rating >= 8.0) $ratingClass = 'rating-high';
                            elseif($post->wise_rating < 5.0) $ratingClass = 'rating-low';
                        @endphp
                        <div class="rating-badge {{ $ratingClass }}">
                            <i class="fa-solid fa-star"></i>
                            <span>تقييم لجنة الحكماء: {{ number_format($post->wise_rating, 2) }} / 10</span>
                        </div>
                    @else
                        <div class="rating-badge rating-none">
                            <i class="fa-regular fa-star"></i>
                            <span>لم يتم التقييم بعد</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Post Content -->
            <div class="post-content mb-3 text-dark" style="font-size: 14.5px; line-height: 1.6;">
                {!! nl2br(e($post->content)) !!}
            </div>

            <!-- Post Media (Image/Video) -->
            @if($post->image)
                <div class="mb-3">
                    <img src="{{ 'http://localhost:8888/new_wiselook/uploads/'.$post->image }}" class="img-fluid rounded shadow-sm" style="max-height: 400px; object-fit: cover;" onclick="showImageModal(this.src)" loading="lazy">
                </div>
            @endif

            @if($post->video)
                <div class="mb-3">
                    <video src="{{ 'http://localhost:8888/new_wiselook/uploads/'.$post->video }}" controls class="img-fluid rounded shadow-sm" style="max-height: 400px; width: 100%;"></video>
                </div>
            @endif

            <!-- Collapsible: Previous Ratings & Assessment Form -->
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-sm btn-outline-secondary px-3" type="button" data-bs-toggle="collapse" data-bs-target="#ratingsCollapse-{{ $post->id }}" aria-expanded="false" aria-controls="ratingsCollapse-{{ $post->id }}">
                        <i class="fa-solid fa-list-check me-1"></i> تقييمات الحكماء التفصيلية ({{ $post->wiseRatings->count() }})
                    </button>

                    @if($isWiseMember)
                        @php
                            $hasRated = isset($myRatings[$post->id]);
                            $isAuthor = $post->user_id == Auth::id();
                        @endphp
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary px-3" type="button" data-bs-toggle="modal" data-bs-target="#rateModal-{{ $post->id }}">
                                <i class="fa-solid fa-star-half-stroke me-1"></i> {{ $hasRated ? 'تعديل تقييمي' : 'قيم هذا الموضوع' }}
                            </button>
                            @if(!$isAuthor)
                                <button class="btn btn-sm btn-success px-3" type="button" data-bs-toggle="modal" data-bs-target="#awardPointsModal-{{ $post->id }}">
                                    <i class="fa-solid fa-award me-1"></i> منح نقاط للعضو
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Ratings List -->
                <div class="collapse" id="ratingsCollapse-{{ $post->id }}">
                    <div class="card card-body bg-light-50 p-3 mb-0">
                        @forelse($post->wiseRatings as $rating)
                            <div class="wise-history-item shadow-sm">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ (!empty($rating->user->photo) && $rating->user->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$rating->user->photo : url('upload/no_image.jpg') }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" loading="lazy">
                                        <div>
                                            <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 13px;">{{ $rating->user->fname }} {{ $rating->user->lname }}</h6>
                                            <span class="text-muted" style="font-size: 10px;">{{ $rating->created_at ? $rating->created_at->diffForHumans() : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="badge bg-light-primary text-primary border border-primary font-weight-bold px-2 py-1" style="font-size: 12px;">
                                        التقييم: {{ number_format($rating->rating, 1) }} / 10
                                    </div>
                                </div>
                                @if($rating->reason)
                                    <p class="mb-0 text-secondary" style="font-size: 13px; line-height: 1.5;">
                                        <strong>المبرر/الرأي:</strong> {{ $rating->reason }}
                                    </p>
                                @else
                                    <p class="mb-0 text-muted" style="font-size: 13px; font-style: italic;">لم يكتب مبرراً للتقييم.</p>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">لم يتم تسجيل أي تقييم تفصيلي لهذا الموضوع بعد.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for rating (specific to this post) -->
    @if($isWiseMember)
        @php
            $existingRating = $post->wiseRatings->where('user_id', Auth::id())->first();
            $currentValue = $existingRating ? $existingRating->rating : 5.0;
            $currentReason = $existingRating ? $existingRating->reason : '';
        @endphp
        <div class="modal fade" id="rateModal-{{ $post->id }}" tabindex="-1" aria-labelledby="rateModalLabel-{{ $post->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="rateModalLabel-{{ $post->id }}">تقييم موضوع: {{ Str::limit(strip_tags($post->content), 30) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.wise_committees.store_rating') }}" method="POST">
                        @csrf
                        <input type="hidden" name="post_id" value="{{ $post->id }}">
                        
                        <div class="modal-body">
                            <div class="mb-4">
                                <label for="rating-{{ $post->id }}" class="form-label font-weight-bold text-dark">التقييم (من 1 إلى 10)</label>
                                <select name="rating" id="rating-{{ $post->id }}" class="form-select form-select-lg text-primary font-weight-bold" required>
                                    @for($r = 10.0; $r >= 1.0; $r -= 0.5)
                                        <option value="{{ number_format($r, 1) }}" {{ number_format($currentValue, 1) == number_format($r, 1) ? 'selected' : '' }}>
                                            {{ number_format($r, 1) }} / 10
                                        </option>
                                    @endfor
                                </select>
                                <small class="text-muted d-block mt-1">اختر القيمة المناسبة للموضوع حيث 10 هي الأعلى و 1 هي الأقل.</small>
                            </div>

                            <div class="mb-3">
                                <label for="reason-{{ $post->id }}" class="form-label font-weight-bold text-dark">مبرر أو رأي الحكيم (اختياري)</label>
                                <textarea name="reason" id="reason-{{ $post->id }}" class="form-control" rows="4" placeholder="اكتب رأيك الفكري ومبررات هذا التقييم للموضوع لنقاشه مع الإدارة واللجنة...">{{ $currentReason }}</textarea>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary px-4">حفظ التقييم <i class="fa-solid fa-star ms-1"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal for awarding points to member -->
        @if($post->user_id != Auth::id())
        <div class="modal fade" id="awardPointsModal-{{ $post->id }}" tabindex="-1" aria-labelledby="awardPointsModalLabel-{{ $post->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="awardPointsModalLabel-{{ $post->id }}">منح نقاط للعضو: {{ $post->user->fname }} {{ $post->user->lname }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.wise_committees.store_member_rating') }}" method="POST">
                        @csrf
                        <input type="hidden" name="post_id" value="{{ $post->id }}">
                        
                        <div class="modal-body">
                            <div class="alert alert-info border-0 bg-light-info py-2" style="font-size: 13px;">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                رصيد نقاط العضو الحالي: <strong>{{ $post->user->points ?? 0 }} نقطة</strong>
                            </div>

                            <div class="mb-3">
                                <label for="points_given-{{ $post->id }}" class="form-label font-weight-bold text-dark">عدد النقاط الممنوحة</label>
                                <select name="points_given" id="points_given-{{ $post->id }}" class="form-select form-select-lg text-success font-weight-bold" required>
                                    <option value="5">5 نقاط</option>
                                    <option value="10" selected>10 نقاط</option>
                                    <option value="15">15 نقطة</option>
                                    <option value="20">20 نقطة</option>
                                    <option value="25">25 نقطة</option>
                                    <option value="50">50 نقطة</option>
                                    <option value="100">100 نقطة</option>
                                </select>
                                <small class="text-muted d-block mt-1">اختر عدد النقاط المراد مكافأة العضو بها على هذا المنشور.</small>
                            </div>

                            <div class="mb-3">
                                <label for="note-{{ $post->id }}" class="form-label font-weight-bold text-dark">السبب أو الملاحظة (اختياري)</label>
                                <textarea name="note" id="note-{{ $post->id }}" class="form-control" rows="3" placeholder="مثال: أسلوب طرح متميز، فكرة مبتكرة ومفيدة..."></textarea>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-success px-4">تأكيد منح النقاط <i class="fa-solid fa-gift ms-1"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endif
@empty
    <div class="card p-5 text-center text-muted" id="emptyStatePlaceholder" style="width: 100%;">
        <i class="bx bx-detail fs-1 mb-2"></i>
        <h5>لا توجد مواضيع للأعضاء منشورة حالياً لتسجيل تقييمها.</h5>
    </div>
@endforelse
