<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ReportReason;
use App\Models\Report;

#[Fillable(['type'])]
class ReportType extends Model
{
    use HasFactory;

    protected $table = 'report_type';

    public function reasons(): HasMany
    {
        return $this->hasMany(ReportReason::class, 'report_type_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'report_type_id');
    }
}