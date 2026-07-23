using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Data.SqlClient;
using RetalineProAgent.Core.Services;
using static RetalineProAgent.Controls.StoreSettings.ctrlCreateProduct;
using RetalineProAgent.Controls.StoreSettings;

namespace RetalineProAgent
{
    public partial class PrivateInventory: Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            //if (this.CurrentUser.TenantType == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            //{
            //    Response.Redirect("/Tenant/SponsoredProducts");
            //    return;
            //}
            if (Page.User.IsInRole("StoreManager"))
            {
                Response.Redirect("/Tenant");
                return;
            }

            ctrlCreateProduct.ParentAddProductBinding += new Controls.StoreSettings.ctrlCreateProduct.ParentAddProductHandler(AddProductPostEvent);
            ctrlCreateProduct.ParentCancelAddProductBinding += new Controls.StoreSettings.ctrlCreateProduct.ParentAddProductHandler(CancelAddProductPostEvent);
            ctrlCreateProduct.ParentMessageBinding += new Controls.StoreSettings.ctrlCreateProduct.ParentMessageHandler(ShowResult);
            if (!string.IsNullOrEmpty(Request.QueryString["type"]))
            {
                int type;
                if (int.TryParse(Request.QueryString["type"], out type))
                {
                    if (Enum.IsDefined(typeof(ViewMode), type))
                    {
                        if (type == (int)ViewMode.Edit || type == (int)ViewMode.Duplicate)
                        {
                            ctrlCreateProduct.ViewType = (ViewMode)type;
                            if (!string.IsNullOrEmpty(Request.QueryString["id"]))
                            {
                                ctrlCreateProduct.EditProdId = Convert.ToInt32(Request.QueryString["id"]);
                            }
                        }
                    }
                }
            }
        }

        private void AddProductPostEvent(int type,int productid)
        {
            if(type == 1)
            {
                if(productid != 0)
                {
                    DataTable dtattribute = DataServiceMySql.GetDataTable($"SELECT * FROM `finascop_stock_itemmaster` fs INNER JOIN  attributeSubcategoryMap ap ON fs.product_category=subCategoryId INNER JOIN attributeValue av ON av.attributeId=ap.attributeId WHERE stit_ID={productid} ", Service.UserService.GetAPIConnectionString());
                    if (dtattribute != null && dtattribute.Rows.Count > 0)
                    {
                        hdnproductid.Value = productid.ToString();
                        string product_id = hdnproductid.Value;
                        if (!string.IsNullOrEmpty(product_id))
                        {
                            DataTable dtattribut = DataServiceMySql.GetDataTable($"SELECT subCategoryId,stit_ID FROM `finascop_stock_itemmaster` fs INNER JOIN  attributeSubcategoryMap ap ON fs.product_category=subCategoryId WHERE stit_ID={product_id} ", Service.UserService.GetAPIConnectionString());
                            if (dtattribut != null && dtattribut.Rows.Count > 0)
                            {
                                string subCategoryId = dtattribute.Rows[0]["subCategoryId"].ToString();
                                HiddenSubCategoryId.Value = subCategoryId;

                            }
                        }
                        string strAlertSCript = "$('#Popupattribute').modal('show');";
                        strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
                        System.Type cstype = this.GetType();
                        String csname1 = "ShowConfirmPopup";
                        ClientScriptManager cs = Page.ClientScript;
                        StringBuilder cstext1 = new StringBuilder();
                        cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
                        cstext1.Append("script>");
                        cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
                        // Response.Redirect("/Tenant/Productattribute.aspx?productid=" + productid);

                    }
                    else
                    {
                        ctrlMessagebox.ShowResult("Success", "Product Created Successfully", 1, "/Tenant/MyProducts");
                    }
                }
                
                //Response.Redirect("/Products");
            }
        }
        private void CancelAddProductPostEvent(int type,int productid)
        {
            //if (type == 1)
            //{
                Response.Redirect("/Tenant/MyProducts");
            //}
        }

        private void ShowResult(string title, string content, int type)
        {
            if(type == 3)
                ctrlMessagebox.ShowResult(title, content, 2, "/Tenant/MyProducts");
            else if(type == 2)
                ctrlMessagebox.ShowResult(title, content, 2);
            else
                ctrlMessagebox.ShowResult(title, content, type, "/Tenant/MyProducts");
        }

        protected void rptattribute_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
            {
                var dataItem = e.Item.DataItem as DataRowView;
                if (dataItem != null)
                {

                    string product_id = hdnproductid.Value;
                    int valuemod = Convert.ToInt32(dataItem["valueMode"]);
                    ListBox ddl = e.Item.FindControl("selattributevalue") as ListBox;
                    int atrributeid = Convert.ToInt32(dataItem["attributeId"]);
                    string selectedValues = dataItem["selectedValues"].ToString();
                    HiddenField hfAttributeId = e.Item.FindControl("hfAttributeId") as HiddenField;
                    hfAttributeId.Value = atrributeid.ToString();
                    ddl.Visible = (valuemod == 1);
                    if (ddl.Visible)
                    {
                        var attribute = new List<KeyValuePair<string, object>>();
                        attribute.Add(new KeyValuePair<string, object>("atrributeid", atrributeid));
                        DataTable dtattribute = DataServiceMySql.GetDataTable($"SELECT id,attributeId,valueName FROM attributeValue WHERE attributeId = @atrributeid ORDER BY valueName", Service.UserService.GetAPIConnectionString(), parmeters: attribute);
                        ddl.DataSource = dtattribute;
                        ddl.DataBind();
                        foreach (string val in selectedValues.Split(','))
                        {
                            var ddlVal = ddl.Items.FindByValue(val);
                            if (ddlVal != null)
                                ddlVal.Selected = true;
                        }
                    }
                }
            }
        }
        protected void btnattributesave_Click(object sender, EventArgs e)
        {

            string sqlvalues = ""; int indx = 0;
            string product_id = hdnproductid.Value;
            List<KeyValuePair<string, object>> attrsparams = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("product_id", product_id)
            };

            foreach (RepeaterItem item in rptattribute.Items)
            {
                if (item.ItemType == ListItemType.Item || item.ItemType == ListItemType.AlternatingItem)
                {
                    try
                    {
                        HiddenField hfAttributeId = item.FindControl("hfAttributeId") as HiddenField;
                        ListBox ddl = item.FindControl("selattributevalue") as ListBox;
                        if (ddl != null && hfAttributeId != null)
                        {
                            int attributeId = Convert.ToInt32(hfAttributeId.Value);
                            List<string> selectedValues = new List<string>();
                            foreach (ListItem listItem in ddl.Items)
                            {
                                if (listItem.Selected)
                                {
                                    // Build the insert values part of the sql for each selected attribute value. Increment index after the last item.
                                    attrsparams.Add(new KeyValuePair<string, object>($"{indx}_attributeId", attributeId));
                                    attrsparams.Add(new KeyValuePair<string, object>($"{indx}_attributevalue", listItem.Value));
                                    sqlvalues += (String.IsNullOrEmpty(sqlvalues) ? "" : ", ") + $"(@product_id, @{indx}_attributeId, @{indx++}_attributevalue)";
                                }
                            }
                        }
                    }
                    catch (Exception ex)
                    {
                        Common.ShowToastifyMessage(Page, "The Attribute Mapping is valid or there is a technical problem on Attribute Mapping.", "danger");
                    }
                }
            }

            if (!String.IsNullOrEmpty(sqlvalues))
            {
                // Delete existing records and insert the new selection. Execute within a transaction to roll back (deletion or insertion) if any failure.
                string insertQry = $"DELETE FROM attributeProductMap WHERE stitId=@product_id; INSERT INTO attributeProductMap(stitId, attributeId, attributeValueId) VALUES" + sqlvalues;
                var result = DataServiceMySql.ExecuteSqlWithTransaction(insertQry, Service.UserService.GetAPIConnectionString(), attrsparams);
            }

            Common.ShowCustomAlert(this.Page, "Success", "Product Attribute Successfully", true, "/Tenant/MyProducts");
        }
    }
}



