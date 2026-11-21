<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TopWinner extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = ['name', 'avatar_url', 'contest_name', 'amount_won', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'amount_won' => 'decimal:2',
        ];
    }

    public function getAvatarAttribute(): string
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=FF4F9A&color=fff&bold=true';
    }
}
