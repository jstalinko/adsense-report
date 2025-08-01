<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable =[
        'user_id',
        'domain',
        'modal',
        'revenue',
        'tax',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
