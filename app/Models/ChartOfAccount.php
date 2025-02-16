<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'charts_of_account';
    protected $primaryKey = 'acct_type_id';
    public $timestamps = false;

    protected $fillable = [
        'acct_type_id',
        'acct_type',
        'acct_name',
        'acct_description'
    ];

    protected $casts = [
        'timestamp' => 'datetime'
    ];

    public function apDetails()
    {
        return $this->hasMany(ApDetail::class, 'acct_type_id', 'acct_type_id');
    }
}