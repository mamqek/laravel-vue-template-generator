<?php 

// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Language extends DirectusModel
{
    protected $table = 'languages';
    public $timestamps = false;

    protected $guarded = ['id'];

}
