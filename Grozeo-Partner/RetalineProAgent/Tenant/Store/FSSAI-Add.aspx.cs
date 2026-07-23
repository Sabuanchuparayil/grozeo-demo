using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services.Cache;

namespace RetalineProAgent.Tenant.Store
{
    public partial class FSSAI_Add : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            lblResult.Text = "";
        }

        protected async void btnAddFSSAI_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtAccountNumber.Text))
            {
                Common.ShowCustomAlert(this.Page, "Verification failed", "Account number is a required field. Please enter Account number");
                return;
            }

            //Core.BussinessModel.Store.FSSAI fssaiInfo = null;
            Core.BussinessModel.Store.FSSAINew fssaiInfo = null;
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("accountNumber", txtAccountNumber.Text));
            prms.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId));

            var dtAccount = DataService.GetDataTable("select  top 1 *, (case when TenantId = @storegroupid then 1 else 0 end) as existsSameTenant from FSSAI where AccountNumber = @accountNumber order by existsSameTenant desc", "", prms);
            if (dtAccount != null && dtAccount.Rows.Count > 0)
            {
                if (Convert.ToInt32(dtAccount.Rows[0]["existsSameTenant"]) == 1)
                {
                    Common.ShowCustomAlert(this.Page, "Verification failed", "Account is already existing. Please add another account", false);
                    return;
                }
                var dr = dtAccount.Rows[0];
                fssaiInfo = new Core.BussinessModel.Store.FSSAINew 
                {
                    result = new FSresult
                    {
                        result = new FSSAIResult
                        {
                            licenseNumber = dr["AccountNumber"].ToString(),
                            entityName = dr["FSSAIName"].ToString(),
                            status = "ACTIVE",
                            //valid = true,
                            products = new object[0]
                        }
                    }
                    
                };

                //fssaiInfo = new Core.BussinessModel.Store.FSSAINew { entityName = dr["AccountName"].ToString(), valid = true, status = "ACTIVE",  };
            }

            string fssainame = "", accouname = "", accountnumber="";
            if (fssaiInfo == null)
            {
                fssaiInfo = APIService.VerifyFSSAI(txtAccountNumber.Text);
                if (fssaiInfo != null && (fssaiInfo.result != null))
                {
                    fssainame = fssaiInfo.result.result.entityName;
                    accouname = fssainame;
                    accountnumber = fssaiInfo.result.result.licenseNumber;
                }
                if (fssaiInfo == null /*|| !fssaiInfo.result.result.valid*/ || fssaiInfo.result ==null)
                {
                    Common.ShowCustomAlert(this.Page, "Verification failed", "The FSSAI Number is invalid or not active. Please correct your input.", false);
                    return;
                }
            }
            else
            {
                fssainame = fssaiInfo.result.result.entityName;
                accouname = fssainame;
                accountnumber = fssaiInfo.result.result.licenseNumber;
            }

            string sql = "INSERT INTO FSSAI(TenantId, FSSAIName, AccountNumber, AccountName, Verified,Createdby) VALUES(@storegroupid, @FSSAIName, @accountNumber, @accountName, 1,@createdby)";
            prms.Add(new KeyValuePair<string, object>("FSSAIName", fssainame));
            prms.Add(new KeyValuePair<string, object>("accountName", accouname));
            prms.Add(new KeyValuePair<string, object>("createdby", string.IsNullOrEmpty(this.CurrentUser.Email) ? this.CurrentUser.Phone : this.CurrentUser.Email));
            DataService.ExecuteSql(sql, parmeters: prms);

            lblResult.Text = $"{txtAccountNumber.Text}";

            // Remove Redis cache entry
            var cacheService = new RedisCacheService();
            string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
            await cacheService.RemoveAsync(cachekey);

            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId; ;
            string User = this.CurrentUser.Email;
            string FSSAIname = fssainame;
            string accountName = accouname;
            string accountNumber = fssaiInfo.result.result.licenseNumber;
            var items = new[]
                {
                    new { Key = "FSSAI Name", Value = FSSAIname },
                    new { Key = "Account Name", Value = accountName },
                    new { Key = "Account Number", Value = accountNumber },
                    };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);

            string strcontent = $"<p class=\"mg-b-5\">Account number: {txtAccountNumber.Text}</p>";
            Common.ShowCustomAlert(this.Page, "FSSAI Verification Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your FSSAI account has been validated and added successfully!</a></h5>" + strcontent, true, "/Tenant/store/FSSAI");
        }


    }
}