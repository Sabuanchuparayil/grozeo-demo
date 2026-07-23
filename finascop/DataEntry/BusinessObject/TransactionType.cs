using System;
using System.Collections.Generic;
using System.Text;

namespace DataEntry.BusinessObject
{
    public enum TransactionType
    {
        CashReceipt = 1,
        CashPayment = 2,
        BankReceipt = 3,
        BankPayment = 4,
        Journal = 5,
        ContraEntry = 6,
        SalesOrder = 7,
        SalesInvoice = 8,
        PurchaseOrder = 9,
        PurchaseReceipt = 10,
        DebitNote = 11,
        CreditNote = 12
    }
}
