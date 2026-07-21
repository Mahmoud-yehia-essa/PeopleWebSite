<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'en',
    'ar'
])]
class Dictionary extends Model
{
    use HasFactory;

    // لارافيل يضيف s تلقائياً لتصبح dictionaries، 
    // ولكن نثبتها هنا لضمان مطابقة الاسم الصارم (dictionary) بمشروعك
    protected $table = 'dictionary';
}