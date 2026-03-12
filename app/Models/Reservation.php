<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'status',
        'user_id',
        'station_id',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
