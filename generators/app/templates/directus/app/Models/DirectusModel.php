<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class DirectusModel extends Model
{
    // By default, Directus uses different names for common columns 
    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';
}
