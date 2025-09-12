<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitType extends Model
{
    protected $table = 'permit_type';
    protected $primaryKey = 'typecode';
    public $timestamps = false;

    protected $fillable = [
        'typecode',
        'typedescription'
    ];

    // Relationship with ConstructionPermit
    public function constructionPermits()
    {
        return $this->hasMany(ConstructionPermit::class, 'permit_type', 'typecode');
    }
}