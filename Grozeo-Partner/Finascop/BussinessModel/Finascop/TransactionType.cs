using System;

namespace Finascop.BussinessModel
{
    [Serializable]
    public enum TransactionType
    {
        CashReceipt = 1,
        CashPayment = 2,
        BankReceipt = 3,
        BankPayment = 4,
        Journal = 5,
        Contra = 6,
        Sale = 7,
        Purchase = 8,
        DebitNote = 9,
        CreditNote = 10
    }
}
