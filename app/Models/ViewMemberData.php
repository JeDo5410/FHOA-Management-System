<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewMemberData extends Model
{
    protected $table = 'vw_member_data';
    public $timestamps = false;
    protected $primaryKey = 'mem_id';
    
    protected $guarded = [];
}