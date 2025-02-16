<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApDetail extends Model
{
    protected $table = 'ap_details';
    public $timestamps = false;

    protected $fillable = [
        'ap_transno',
        'ap_particular',
        'ap_amount',
        'acct_type_id'
    ];

    protected $casts = [
        'ap_amount' => 'decimal:2',
        'timestamp' => 'datetime'
    ];

    public function accountPayable()
    {
        return $this->belongsTo(AccountPayable::class, 'ap_transno', 'ap_transno');
    }

    public function accountType()
    {
        return $this->belongsTo(ChartOfAccount::class, 'acct_type_id', 'acct_type_id');
    }
}