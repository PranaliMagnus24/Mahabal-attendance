<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'attended_by',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_selfie',
        'check_out_selfie',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendedBy()
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
}
