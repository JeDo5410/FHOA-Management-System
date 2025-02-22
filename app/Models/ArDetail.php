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
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ar_transno';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        'mem_id',
        'acct_type_id',
        'ar_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ar_amount' => 'decimal:2',
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
     * Get the member associated with this detail.
     */
    public function member()
    {
        return $this->belongsTo(MemberSum::class, 'mem_id', 'mem_id');
    }

    /**
     * Get the account type associated with this detail.
     * Note: This assumes you have an AccountType model.
     */
    public function accountType()
    {
        return $this->belongsTo(ChartOfAccount::class, 'acct_type_id');
    }
}