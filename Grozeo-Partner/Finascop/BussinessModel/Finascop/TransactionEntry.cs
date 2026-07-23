using System;
using System.Collections.Generic;

namespace Finascop.BussinessModel
{
    [Serializable]
    public class TransactionEntry
    {
        public TransactionType TransactionTypeId { get; set; }
        public string docSerialPrefix { get; set; }
        public int docSerialNo { get; set; }

        public string narration { get; set; }
        public TransactionType docTypeID { get; set; }
        public List<TransactionData> Account { get; set; }
        public List<TransactionData> Particulars { get; set; }
        public string StoreGroupName { get; set; }
        public int storeGroupId { get; set; }
        public string storeGroupRefId { get; set; }
        public string br_Name_store_group { get; set; }
        public int br_ID_store_group { get; set; }
        public int entry_type { get; set; }
        public string reference { get; set; }
        public string entry_RefId { get; set; }
        public string order_order_id { get; set; }
        public DateTime voucherDate { get; set; }
        public string order_event { get; set; }

        public int finascopBrID { get; set; }

    }
}
