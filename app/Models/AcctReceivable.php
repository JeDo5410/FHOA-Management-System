<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctReceivable extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acct_receivable';

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
    public $incrementing = true;

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
        'mem_id',
        'or_number',
        'ar_date',
        'ar_amount',
        'arrear_bal',
        'acct_type_id',
        'payor_name',
        'payor_address',
        'payment_type',
        'payment_Ref',
        'receive_by',
        'ar_remarks',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ar_date' => 'date',
        'ar_amount' => 'decimal:2',
        'arrear_bal' => 'decimal:2',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the user that created this record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the member associated with this receivable.
     */
    public function member()
    {
        return $this->belongsTo(MemberData::class, 'mem_id', 'mem_id');
    }
}