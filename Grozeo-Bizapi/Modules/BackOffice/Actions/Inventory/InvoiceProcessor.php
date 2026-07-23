<?php

namespace BackOffice\Actions\Inventory;

use App\Models\Order;
use GuzzleHttp\Client;
use App\Models\OrderItem;
use BackOffice\Models\Branch;
use App\Models\StockItemMaster;
use App\Models\OrderItemBarcodes;
use BackOffice\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvoiceProcessor {

    protected $qugeo;
    protected $branch;
    protected $client;

    const CASH_ON_DELIVERY = 1;
    const ONLINE = 2;

    public function __construct() {
        $this->invoice = new Invoice();
    }

    public function save($order) {

        $qugeoOrder = $this->invoice->create([
            'orderType' => $order->orderType,
            'bci_fsto_id' => $order->bci_fsto_id,
            'bci_fstr_id' => $order->bci_fstr_id,
            'bci_bcso_id' => $order->bci_bcso_id,
            'bci_Customer_ID' => $order->bci_Customer_ID,
            'invoiceNumber' => $order->invoiceNumber,
            'invoiceDate' => $order->invoiceDate,
            'invoiceValue' => $order->invoiceValue,
            'HandlingCharges' => $order->HandlingCharges,
            'InvValBtax' => $order->InvValBtax,
            'InvValAtax' => $order->InvValAtax,
            //'CGSTVal' => $order->CGSTVal,
            //'SGSTVal' => $order->SGSTVal,
            'AmountCollectible' => $order->AmountCollectible,
            'InitialPaymode' => $order->InitialPaymode,
            'bci_br_ID' => $order->bci_br_ID,
            'createdon' => $order->createdon,
            'updatedon' => $order->updatedon,
            'roundoff' => $order->roundoff
        ]);

        
        return true;
    }

}
