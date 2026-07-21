<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'title_ar',
    'content',
    'content_ar',
    'status'
])]
class Page extends Model
{
    use HasFactory;

    protected $table = 'pages';
}