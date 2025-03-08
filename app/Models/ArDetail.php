<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ar_details';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ar_transno',
        'payor_name',
        'payor_address',
        'mem_add_id',
        'acct_type_id',
        'ar_amount',
        'arrear_bal',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ar_amount' => 'decimal:2',
        'arrear_bal' => 'decimal:2',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the account receivable record that this detail belongs to.
     */
    public function accountReceivable()
    {
        return $this->belongsTo(AcctReceivable::class, 'ar_transno', 'ar_transno');
    }

    /**
     * Get the account type associated with this detail.
     */
    public function accountType()
    {
        return $this->belongsTo(ChartOfAccount::class, 'acct_type_id', 'acct_type_id');
    }
}