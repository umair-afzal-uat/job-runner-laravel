<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackgroundJob extends Model
{
    use HasFactory;

    // Optional: Define the table name if it's different from the default plural form of the model name
    protected $table = 'background_jobs';

    // Define the fillable attributes (columns that are mass assignable)
    protected $fillable = ['class_name', 'method', 'status', 'params', 'created_at', 'updated_at'];
}
