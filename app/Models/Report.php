<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\ReportType;
use App\Models\ReportReason;

#[Fillable([
    'reported_by_id',
    'report_type_id',
    'target_id',
    'report_reasons_id'
])]
class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReportType::class, 'report_type_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReportReason::class, 'report_reasons_id');
    }
}