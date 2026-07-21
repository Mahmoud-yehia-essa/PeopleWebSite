<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ReportType;

#[Fillable([
    'name',
    'name_ar',
    'description',
    'description_ar',
    'report_type_id'
])]
class ReportReason extends Model
{
    use HasFactory;

    protected $table = 'report_reasons';

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class, 'report_type_id');
    }
}