<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewAcctReceivable extends Model
{
    use HasFactory;

    protected $table = 'vw_acct_receivable';
    protected $primaryKey = 'ar_transno';
    public $incrementing = true;
    public $timestamps = false;

    protected $casts = [
        'ar_transno' => 'integer',
        'or_number' => 'string',
        'ar_date' => 'date',
        'ar_amount' => 'decimal:2',
        'arrear_bal' => 'decimal:2',
        'timestamp' => 'datetime'
    ];

    /**
     * Scope to filter by member ID from the underlying acct_receivable table
     * Note: mem_id is not in the view, so we need to join back to acct_receivable
     */
    public function scopeForMember($query, $memberId)
    {
        return $query->whereRaw('ar_transno IN (
            SELECT ar.ar_transno
            FROM acct_receivable ar
            JOIN charts_of_account coa ON ar.acct_type_id = coa.acct_type_id
            WHERE ar.mem_id = ?
            AND coa.acct_type = ?
            AND coa.acct_name = ?
        )', [$memberId, 'Association Receipts', 'Association Dues']);
    }
}