<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    
    protected $fillable = [
        'title',  // Add this
        'image',
        'content',
        'sorting',
        'status'
    ];
}
