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
        'mem_transno',
        'or_number',
        'ar_date',
        'ar_total',
        'ar_remarks',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ar_date' => 'date',
        'ar_total' => 'decimal:2',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the AR details associated with this receivable.
     */
    public function details()
    {
        return $this->hasMany(ArDetail::class, 'ar_transno', 'ar_transno');
    }

    /**
     * Get the user that created this record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the member associated with this receivable.
     * Note: This assumes you have a Member or MemberData model with mem_transno field.
     */
    public function member()
    {
        return $this->belongsTo(MemberData::class, 'mem_transno', 'mem_transno');
    }
}