@extends('admin.master_admin')
@section('admin')

<style>
    td.question-column {
        white-space: normal !important;
        word-break: break-word !important;
        vertical-align: top;
    }

    .text-display-box {
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        font-size: 14px;
        font-family: inherit;
        line-height: 1.6;
    }

    /* اختياري: إذا كنت تستخدم DataTables */
    table.dataTable td {
        overflow: visible !important;
    }
</style>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">الأسئلة المولدة باستخدام AI</div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{route('all.question.ai')}}">
                <button type="button" class="btn btn-primary">إنشاء اسئلة جديدة</button>
            </a>
        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr />

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">موضوع الأسئلة: <strong>{{ $categoryName }}</strong></h5>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>السؤال</th>
                        <th>نوع السؤال</th>
                        <th>الفئة</th>
                        <th>الإجابة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questions as $index => $item)
                    <tr>
                        <form action="{{ route('add.question.to.game.ai') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="category_id" value="{{ $categoryId }}">
                            <input type="hidden" name="qu_title" value="{{ trim($item['question']) }}">
                            <input type="hidden" name="answer_title" value="{{ trim($item['answer']) }}">

                            <td>{{ $index + 1 }}</td>
                            <td class="question-column">
                                <div class="text-display-box">{{ trim($item['question']) }}</div>
                            </td>
                            <td>نصي</td>
                            <td>{{ $categoryName }}</td>
                            <td class="question-column">
                                <div class="text-display-box">{{ trim($item['answer']) }}</div>
                            </td>
                            <td>
                                <input type="submit" class="btn btn-primary px-4" value="إضافة السؤال إلى اللعبة" />
                            </td>
                        </form>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>الرقم</th>
                        <th>السؤال</th>
                        <th>نوع السؤال</th>
                        <th>الفئة</th>
                        <th>الإجابة</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
