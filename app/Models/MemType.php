<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemType extends Model
{
    protected $table = 'mem_type';
    protected $primaryKey = 'mem_typecode';
    public $timestamps = false;

    protected $fillable = [
        'mem_typedescription',
        'mem_monthlydues'
    ];

    // Relationship with MemberData
    public function memberData()
    {
        return $this->hasMany(MemberData::class, 'mem_typecode', 'mem_typecode');
    }
}