<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = ['name', 'short_name', 'logo'];

    public function playerProfiles()
    {
        return $this->hasMany(PlayerProfile::class);
    }
}
