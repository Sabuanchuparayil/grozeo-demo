using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Finascop.BussinessModel
{
    [Serializable]
    public enum LedgerType
    {
        PODCollection = 15,
        Tenant = 16,
        BANKGGOA = 17,
        BANKGOCA = 18,
        BANKGTPA = 19,
        BANKGCCA = 20,
        IGSTInput = 21,
        CGSTInput = 22,
        SGSTInput = 23,
        UTGSTInput = 24,
        PODCollectionOther = 25,
        TenantSecurityDeposit = 26,
        CourierGLP = 27,
        TradeDepositsSTL = 28,
        SalesOrder = 29,
        TenantSalesOrder = 30,
        TenantSales = 31,
        TenantDelivery = 32,
        TenantSalesRoundOff = 33,
        TenantIGST = 34,
        TenantCGST = 35,
        TenantSGST = 36,
        IGST = 37,
        CGST = 38,
        SGST = 39,
        TenantUTGST = 40,
        TCSIGST = 41,
        TCSCGST = 42,
        TCSSGST = 43,
        TCSUTGST = 44,
        TDSoncontractorsSubContractorsAY22_23 = 45,
        SuspenseAccount = 46,
        DeliveryCharges = 47,
        RoundOff = 48,
        BankChargesTDR = 49,
        GrozeoMonthlyPlan = 50,
        GrozeoAnnualPlan = 51,
        DeliveryChargesIncome = 52,
        GrozeoSupportServices = 53,
        TDROnlineTransactions = 54,
        OrderProcessingCharges = 55,
        CustomerWallet = 56,
        TDSonE_commercetransactionsAY22_23 = 57,
        RefId = 0,
        TenantSalesOrderRoundOff = 33,
    }
}
