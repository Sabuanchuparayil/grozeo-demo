using Amazon.DynamoDBv2.DocumentModel;
using Amazon.DynamoDBv2.Model;
using NPOI.POIFS.Properties;
using Org.BouncyCastle.Asn1.Cms;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class Productattribute : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string product_id = Request.QueryString["productid"].ToString();
            if (product_id != null)
            {
                DataTable dtattribute = DataServiceMySql.GetDataTable($"SELECT subCategoryId,stit_ID FROM `finascop_stock_itemmaster` fs INNER JOIN  attributeSubcategoryMap ap ON fs.product_category=subCategoryId WHERE stit_ID={product_id} ", Service.UserService.GetAPIConnectionString());
                if (dtattribute != null && dtattribute.Rows.Count > 0)
                {
                    string subCategoryId = dtattribute.Rows[0]["subCategoryId"].ToString();
                    HiddenSubCategoryId.Value = subCategoryId;

                }
            }

        }

        protected void rptattribute_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
            {
                var dataItem = e.Item.DataItem as DataRowView;
                if(dataItem != null)
                {   

                    string product_id = Request.QueryString["productid"].ToString();
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
                        DataTable dtattribute =  DataServiceMySql.GetDataTable($"SELECT id,attributeId,valueName FROM attributeValue WHERE attributeId = @atrributeid ORDER BY valueName", Service.UserService.GetAPIConnectionString(),parmeters: attribute);
                        ddl.DataSource = dtattribute;                        
                        ddl.DataBind();                       
                       foreach(string val in selectedValues.Split(','))
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
            string product_id = Request.QueryString["productid"].ToString();
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
                    catch(Exception ex)
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

            ctrlMessagebox.ShowResult("Success", "Product Attribute Successfully", 1, "/Tenant/MyProducts");
        }
    }
}