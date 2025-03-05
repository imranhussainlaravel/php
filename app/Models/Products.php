<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products'; // Ensure correct table name

    protected $fillable = [
        'title',
        'title_2',
        'alt_name',
        'description',
        'description_2',
        'faqs',
        'content',
        'industry_id',
        'material_id',
        'style_id',
        'image_1',
        'image_2',
        'image_3',
        'image_4',
        'image_5',
        'status'
    ];
}
