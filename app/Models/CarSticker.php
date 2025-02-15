<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarSticker extends Model
{
    protected $table = 'car_sticker';
    public $timestamps = false;

    protected $fillable = [
        'mem_id',
        'mem_code',
        'vehicle_maker',
        'vehicle_type',
        'vehicle_color',
        'vehicle_OR',
        'vehicle_CR',
        'vehicle_plate',
        'car_sticker',
        'vehicle_active',
        'remarks',
        'user_id'
    ];

    // Relationship with MemberSum
    public function memberSum()
    {
        return $this->belongsTo(MemberSum::class, 'mem_id', 'mem_id');
    }
}