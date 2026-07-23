using RetalineProAgent.Core.BussinessModel.Catalog;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.ComponentModel.Design;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlBrands : Base.BasePartnerUserControl
    {
        public delegate void ParentAddProductHandler(int status);
        public delegate void ParentAddBrandHandler(int status);
        public delegate void ParentMessageHandler(string title, string msg, int type);

        public event ParentAddProductHandler ParentAddProductBinding;
        public event ParentAddBrandHandler ParentAddBrandBinding;
        public event ParentAddProductHandler ParentCancelAddProductBinding;

        public event ParentMessageHandler ParentMessageBinding;
        protected void Page_Load(object sender, EventArgs e)
        {
            if (gvMyBrand.HeaderRow != null)
                gvMyBrand.HeaderRow.TableSection = TableRowSection.TableHeader;
        }



        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            gvMyBrand.PageIndex = 0;
        }
        protected void SDSMyBrands_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
                e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }


        protected void btnAddBrand_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtEditBrand.Text))
            {
                ltrAddBrandResult.Text = "Please enter brand name and manufacturer.";
                //ShowAddBrandPopup();
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(0);
                else
                    Common.ShowToastifyMessage(this.Page, "Please enter brand name.");
                return;
            }


            DataTable dtManufacturer = DataServiceMySql.GetDataTable($"SELECT manufacture_id, manufacture_name FROM mypha_productmanufacture WHERE manufacture_name='Multiple'", Service.UserService.GetAPIConnectionString());
            int manufactureId = 0;
            if (dtManufacturer != null && dtManufacturer.Rows.Count > 0)
            {
                DataRow dr = dtManufacturer.Rows[0];
                manufactureId = Convert.ToInt32(dr["manufacture_id"]);

            }


            var brandParams = new List<KeyValuePair<string, object>>();
            brandParams.Add(new KeyValuePair<string, object>("brandname", txtEditBrand.Text));
            brandParams.Add(new KeyValuePair<string, object>("manufacture", manufactureId));
            brandParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));


            try
            {
                List<KeyValuePair<string, object>> brandparams = new List<KeyValuePair<string, object>>();
                brandparams.Add(new KeyValuePair<string, object>("brandId", hidbrandId.Value));
                brandparams.Add(new KeyValuePair<string, object>("brandname", txtEditBrand.Text));
                DataTable dtBrand = DataServiceMySql.GetDataTable($"SELECT brand_name, COUNT(*) AS BrandCount FROM mypha_productbrands WHERE brand_name = @brandname GROUP BY brand_name HAVING COUNT(brand_name) > 1", Service.UserService.GetAPIConnectionString(), brandparams);
                if (dtBrand != null && dtBrand.Rows.Count <= 0)
                {
                    int brandid = Convert.ToInt32(hidbrandId.Value);
                    if (brandid > 0)
                    {
                        string brandName = txtEditBrand.Text;
                        int manufacturerId = manufactureId;
                        int storegroupId = this.CurrentUser.APIStoreId;
                        var result = Core.Services.APIService.ProductBrand(brandName, manufacturerId, storegroupId);
                        if(result != null)
                        {
                            brandparams.Add(new KeyValuePair<string, object>("mappingId", result.brand_id));
                            string strUpdateSql = $"UPDATE mypha_productbrands SET brand_name=@brandname, mapping_id=@mappingId WHERE brand_id=@brandId";
                            DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), brandparams);
                            Response.Redirect($"PrivateInventory.aspx?brandId={brandid}");
                        }
                        //else
                        //{
                        //    string brandId = Convert.ToString(brandid);
                        //    Response.Redirect($"PrivateInventory.aspx?brandId={brandid}");
                        //}
                    }

                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                    string brandname = txtEditBrand.Text;
                    int Brandid = Convert.ToInt32(brandid);
                    int ManufactureId = manufactureId;
                    int tenantId = this.CurrentUser.APIStoreId;
                    var items = new[]
                    {
                      new { Key = "Brandname", Value = brandname },
                      new { Key = "Manufacture Id", Value =Convert.ToString(ManufactureId) },
                      new { Key = "TenantId", Value =Convert.ToString(tenantId) },
                      new { Key = "Brandid", Value =Convert.ToString(Brandid) },
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                }
                else
                {
                    Common.ShowToastifyMessage(Page, "The brand name already exists or there is a technical problem on creating brand.", "danger");
                }
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(1);
            }
            catch
            {
                Common.ShowToastifyMessage(Page, "The brand name already exists or there is a technical problem on creating brand.", "danger");
            }
            
        }

        protected void gvMyBrand_DataBound(object sender, EventArgs e)
        {

            int startRowOnPage = (gvMyBrand.PageIndex * gvMyBrand.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvMyBrand.Rows.Count - 1;
        }

        private void ShowSuccess(string title, string content)
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show');</script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void lbtnedit_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            hidbrandId.Value = (lbtn.Attributes["brandId"]);
            string brandId = hidbrandId.Value;
            txtEditBrand.Text = GetBrandNameById(brandId);
            string script = "$('#modalBrand').modal('show');";
            ScriptManager.RegisterStartupScript(this, this.GetType(), "ShowModalScript", script, true);
        }

        private string GetBrandNameById(string brandId)
        {
            string brandname = "";
            if (brandId != null)
            {
                List<KeyValuePair<string, object>> brandparams = new List<KeyValuePair<string, object>>();
                brandparams.Add(new KeyValuePair<string, object>("brandId", brandId));
                DataTable dtbrand = DataServiceMySql.GetDataTable($"SELECT brand_name FROM mypha_productbrands WHERE brand_id=@brandId", Service.UserService.GetAPIConnectionString(), brandparams);
                if(dtbrand != null && dtbrand.Rows.Count > 0)
                {
                    DataRow dr = dtbrand.Rows[0];
                    brandname = dr["brand_name"].ToString();
                }
            }
            return brandname;
        }
    }
}