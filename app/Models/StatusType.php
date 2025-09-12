<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusType extends Model
{
    protected $table = 'status_type';
    protected $primaryKey = 'statuscode';
    public $timestamps = false;

    protected $fillable = [
        'statuscode',
        'statusdescription'
    ];

    // Relationship with ConstructionPermit
    public function constructionPermits()
    {
        return $this->hasMany(ConstructionPermit::class, 'status_type', 'statuscode');
    }
}