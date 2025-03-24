<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class Categories extends Model
{
    use SoftDeletes; // Enable soft deletes

    protected $dates = ['deleted_at']; // Ensure deleted_at is treated as a date
}
