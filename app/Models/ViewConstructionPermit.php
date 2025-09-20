<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewConstructionPermit extends Model
{
    use HasFactory;

    protected $table = 'vw_construction_permit';
    protected $primaryKey = 'permit_no';
    public $incrementing = false;
    public $timestamps = false;
}