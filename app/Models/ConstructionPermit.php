<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstructionPermit extends Model
{
    protected $table = 'construction_permit';
    protected $primaryKey = 'permit_no';
    public $timestamps = false;

    protected $fillable = [
        'mem_id',
        'application_date',
        'applicant_name',
        'applicant_contact',
        'permit_type',
        'permit_sin',
        'permit_arn',
        'permit_fee',
        'permit_bond',
        'permit_fee_date',
        'permit_bond_date',
        'permit_start_date',
        'permit_end_date',
        'status_type',
        'inspection_date',
        'Inspector',
        'inspector_note',
        'bond_release_type',
        'bond_receiver',
        'bond_release_date',
        'remarks',
        'user_id'
    ];

    protected $casts = [
        'application_date' => 'date',
        'permit_fee_date' => 'date',
        'permit_bond_date' => 'date',
        'permit_start_date' => 'date',
        'permit_end_date' => 'date',
        'inspection_date' => 'date',
        'bond_release_date' => 'date',
        'permit_fee' => 'decimal:2',
        'permit_bond' => 'decimal:2'
    ];

    // Relationship with MemberData
    public function member()
    {
        return $this->belongsTo(MemberData::class, 'mem_id', 'mem_id');
    }

    // Relationship with PermitType
    public function permitType()
    {
        return $this->belongsTo(PermitType::class, 'permit_type', 'typecode');
    }

    // Relationship with StatusType
    public function statusType()
    {
        return $this->belongsTo(StatusType::class, 'status_type', 'statuscode');
    }
}