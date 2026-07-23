using RetalineProAgent.Core.BussinessModel.Finance;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel
{
    public static class Transaction
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
        public class TransactionData
        {
            /// <summary>
            /// Ledger ID
            /// </summary>
            public int ledgerId { get; set; }
            /// <summary>
            /// Ledger Reference ID
            /// </summary>
            public string ledgerRefId { get; set; }
            /// <summary>
            /// Transaction amount
            /// </summary>
            public double amount { get; set; }
            /// <summary>
            /// Particulars
            /// </summary>
            public string particulars { get; set; }

            /// <summary>
            /// Is Debit
            /// </summary>
            public int isDebtor { get; set; }
            /// <summary>
            /// Reference
            /// </summary>
            public string reference { get; set; }

            public List<Costcentre> costCentreEntries { get; set; }

        }
        //public class costcentre
        //{
        //    /// <summary>
        //    /// CostCentreName
        //    /// </summary>
        //    public string CostCentreName { get; set; }
        //    /// <summary>
        //    /// CostAmount
        //    /// </summary>
        //    public double CostAmount { get; set; }
        //    /// <summary>
        //    /// ledgerId
        //    /// </summary>
        //    public int ledgerId { get; set; }
        //    /// <summary>
        //    /// CostCentreId
        //    /// </summary>
        //    public int CostCentreId { get; set; }
        //    /// <summary>
        //    /// If true then Dr, else Cr.
        //    /// </summary>
        //    public int IsDebit { get; set; }

        //}
        public class TransactionEntry
        {
            public TransactionType TransactionTypeId { get; set; }
            public int docTypeID { get; set; }
            public string docSerialPrefix { get; set; }
            public int docSerialNo { get; set; }
            public string Narration { get; set; }
            public int? storeGroupId { get; set; }
            public string storeGroupRefId { get; set; }
            public List<TransactionData> Account { get; set; }
            public List<TransactionData> Particulars { get; set; }

            public string StoreGroupName { get; set; }
            public string br_Name_store_group { get; set; }
            public int br_ID_store_group { get; set; }
            public int entry_type { get; set; }
            public string reference { get; set; }
            public string entry_RefId { get; set; }
            public string order_order_id { get; set; }
            public string order_event { get; set; }
            public string blob_storage_folder { get; set; }
            public DateTime voucherDate { get; set; }
            public int finascopBrID { get; set; }

        }
    }
}
