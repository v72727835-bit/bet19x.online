<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One game round reported by the AS Tech callback (bet and/or win). */
class AstechBetLog extends Model
{
    protected $connection = 'tenant';

    protected $table = 'astech_bet_logs';

    protected $guarded = [];

    protected $casts = ['rolled_back' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
