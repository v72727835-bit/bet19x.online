<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['title', 'slug', 'content', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
