<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberSum extends Model
{
    protected $table = 'member_sum';
    protected $primaryKey = 'mem_id';
    public $timestamps = false; // Since we use custom timestamp column

    protected $fillable = [
        'mem_add_id',
        'arrear_month',
        'arrear',
        'arrear_count',
        'arrears_interest',
        'last_salesinvoice',
        'last_paydate',
        'last_payamount',
        'user_id'
    ];

    // Relationship with MemberData
    public function memberData()
    {
        return $this->hasMany(MemberData::class, 'mem_id', 'mem_id');
    }

    // Relationship with CarSticker
    public function carStickers()
    {
        return $this->hasMany(CarSticker::class, 'mem_id', 'mem_id');
    }
}