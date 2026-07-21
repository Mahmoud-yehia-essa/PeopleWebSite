<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'is_active'])]
class PrivacyLevel extends Model
{
    use HasFactory;

    protected $table = 'privacy_level';
}