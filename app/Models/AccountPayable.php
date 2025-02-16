<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    protected $table = 'acct_payable';
    protected $primaryKey = 'ap_transno';
    public $timestamps = false;

    protected $fillable = [
        'ap_voucherno',
        'ap_date',
        'ap_payee',
        'ap_paytype',
        'paytype_reference',
        'ap_total',
        'remarks',
        'user_id'
    ];

    protected $casts = [
        'ap_date' => 'date',
        'ap_total' => 'decimal:2',
        'timestamp' => 'datetime'
    ];

    public function details()
    {
        return $this->hasMany(ApDetail::class, 'ap_transno', 'ap_transno');
    }
}