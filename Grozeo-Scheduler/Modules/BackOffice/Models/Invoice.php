<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\InvoiceNumbering;

class Invoice extends Model {

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'B2CInvoice';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

     /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createdon';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updatedon';

    public function invoicedetails() {
        return $this->hasMany(InvoiceDetails::class, 'bci_id');
    }

    public static function nextInvoiceNo($brid) {
        $branches = Branch::select('branch_shortname')
                ->where('br_id', $brid)
                ->first();
        // //DB::enableQueryLog();
        /* $lastOrderNo = Invoice::selectraw('right(invoiceNumber,3)*1 as invoiceNumber ')
                ->where('bci_br_ID', $brid)
                ->orderBy('id', 'desc')
                ->first();
        $lastOrderNo = $lastOrderNo->invoiceNumber??0; */
        $numbering = InvoiceNumbering::create();
        $lastNo = $numbering ? $numbering->id : 1;
        return $branches->branch_shortname . '/INV/' . now()->format('ym') . '/' .
                str_pad($lastNo, 3, '0', STR_PAD_LEFT);
    }

}
