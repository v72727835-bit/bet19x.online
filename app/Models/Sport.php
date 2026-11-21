<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'is_active'];

    public function matchEvents()
    {
        return $this->hasMany(MatchEvent::class);
    }
}
