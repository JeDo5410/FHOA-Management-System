<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberData extends Model
{
    protected $table = 'member_data';
    protected $primaryKey = 'mem_transno';
    public $timestamps = false;

    protected $fillable = [
        'mem_id',
        'mem_typecode',
        'mem_name',
        'mem_mobile',
        'mem_date',
        'mem_email',
        'mem_SPA_Tenant',
        'mem_Resident1',
        'mem_Resident2',
        'mem_Resident3',
        'mem_Resident4',
        'mem_Resident5',
        'mem_Resident6',
        'mem_Resident7',
        'mem_Resident8',
        'mem_Resident9',
        'mem_Resident10',
        'mem_Relationship1',
        'mem_Relationship2',
        'mem_Relationship3',
        'mem_Relationship4',
        'mem_Relationship5',
        'mem_Relationship6',
        'mem_Relationship7',
        'mem_Relationship8',
        'mem_Relationship9',
        'mem_Relationship10',
        'mem_remarks',
        'user_id'
    ];

    // Relationship with MemberSum
    public function memberSum()
    {
        return $this->belongsTo(MemberSum::class, 'mem_id', 'mem_id');
    }

    // Relationship with MemType
    public function memberType()
    {
        return $this->belongsTo(MemType::class, 'mem_typecode', 'mem_typecode');
    }
}