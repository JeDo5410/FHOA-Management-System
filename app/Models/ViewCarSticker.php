<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewCarSticker extends Model
{
    protected $table = 'vw_car_sticker';
    public $timestamps = false;
    protected $primaryKey = 'mem_id';
    
    protected $guarded = [];
}
