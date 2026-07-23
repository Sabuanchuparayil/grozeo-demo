using Amazon.DynamoDBv2;
using NPOI.POIFS.Properties;
using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Finance;
using RetalineProAgent.Navigations;
using RetalineProAgent.Service;
using StackExchange.Redis;
using System;
using System.Collections.Generic;
using System.Data;
using System.Data.SqlTypes;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static QRCoder.Base64QRCode;
using static System.Windows.Forms.VisualStyles.VisualStyleElement.TreeView;

namespace RetalineProAgent.Tenant
{
    public partial class Comboproduct : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        { 
           
          
        }

        protected void selBranch_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void SDSBranch_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
        }
        protected void selproduct_DataBound(object sender, EventArgs e)
        {
            selproduct.Items.Insert(0, new ListItem("Select Product", ""));
        }

        protected void SDScomboproduct_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
        }
        protected void btnaddcombo_Click(object sender, EventArgs e)
        {
            var costType = ddlComboCostType.SelectedItem.Value;
            var disableFields = costType == "1" || costType == "3";
            rfvinclusion.Enabled = !disableFields;
            rqfdiscount.Enabled = rfvvalue.Enabled = costType != "2" && !disableFields;

            try
            {
                string comboparentproduct = string.IsNullOrEmpty(hdfmasterproduct.Value) ? selproduct.SelectedItem.Value : hdfmasterproduct.Value;
                var prms = new List<KeyValuePair<string, object>>
                {
                  new KeyValuePair<string, object>("Parentid", comboparentproduct),
                  new KeyValuePair<string, object>("BranchId", selBranch.SelectedItem.Value)
                };
                string dtcombo= "SELECT COUNT(*) FROM finascop_stock_branch_inventory bv WHERE bv.hascombo=1 AND bv.stit_id=@Parentid and branch_id=@BranchId";
                int dt = Convert.ToInt32(DataServiceMySql.ExecuteScalar(dtcombo, Service.UserService.GetAPIConnectionString(), prms));
                if (dt<=0)
                {
                    string comboproduct = "UPDATE finascop_stock_branch_inventory SET hascombo=1 WHERE stit_id=@Parentid and branch_id=@BranchId";
                    var masterproduct = DataServiceMySql.ExecuteScalar(comboproduct, Service.UserService.GetAPIConnectionString(), prms);
                }
                var sqldaId = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("Comboparentproduct", comboparentproduct),
                    new KeyValuePair<string, object>("Comboproduct", selcomboproduct.SelectedItem.Value),
                    new KeyValuePair<string, object>("ComboCostType", ddlComboCostType.SelectedItem.Value),
                    new KeyValuePair<string, object>("DiscountInclusion", Convert.ToInt32(
                    ddlComboCostType.SelectedItem.Value == "1" ? ddlfreewithparents.SelectedItem.Value :
                    ddlComboCostType.SelectedItem.Value == "2" ? ddldiscountwithparent.SelectedItem.Value : "0")),
                    new KeyValuePair<string, object>("discounttype", string.IsNullOrEmpty(txtdiscounttype.Text) ? "0" : txtdiscounttype.Text),
                    new KeyValuePair<string, object>("quantity", Convert.ToInt32(txtquantity.Text)),
                    new KeyValuePair<string, object>("CreatedBY", this.CurrentUser.FullName),
                    new KeyValuePair<string, object>("BranchId", selBranch.SelectedItem.Value),
                    new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.APIStoreId)
                };
                string combo = "INSERT INTO Comboproduct (productId, ComboProductId, CostType, DiscountId, DiscountType, qutatity, CreatedBY, CreatedFrom, BranchId, StoreGroupId) " +
                               "VALUES (@Comboparentproduct, @Comboproduct, @ComboCostType, @DiscountInclusion, @discounttype, @quantity, @CreatedBY, 1, @BranchId, @StoreGroupId)";
                var product = DataServiceMySql.ExecuteScalar(combo, Service.UserService.GetAPIConnectionString(), sqldaId);
                comboproductload(Convert.ToInt32(string.IsNullOrEmpty(hdfmasterproduct.Value) ? selproduct.SelectedItem.Value : hdfmasterproduct.Value), Convert.ToInt32(selBranch.SelectedItem.Value));
                selcomboproduct.SelectedIndex = 0;
                ddlComboCostType.SelectedIndex = 0;
                ddlDiscountInclusion.SelectedIndex = 0;
                ddlfreewithparents.SelectedIndex = 0;
                ddldiscountwithparent.SelectedIndex = 0;
                txtdiscounttype.Text = string.Empty;
                txtquantity.Text = string.Empty;
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", "Error!", false, "/Tenant/Comboproduct");
            }
        }

        protected void rptrcomboproduct_ItemCommand(object source, RepeaterCommandEventArgs e)
        {
            if (e.CommandName == "DeleteItem")
            {
                string[] args = e.CommandArgument.ToString().Split(',');
                int comboProductId = Convert.ToInt32(args[0]);
                int comboparentproduct = Convert.ToInt32(args[1]);
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("comboproductid", comboProductId));
                string deletecombo = "DELETE FROM Comboproduct WHERE ComboProductId=@comboproductid";
                DataServiceMySql.ExecuteSql(deletecombo, UserService.GetAPIConnectionString(), prms);
                comboproductload(comboparentproduct, Convert.ToInt32(selBranch.SelectedItem.Value));
            }
        }
        private void comboproductload(int parentid, int branchid)
        {
            var sqldaId = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("Comboparentproduct", parentid),
                new KeyValuePair<string, object>("brid", branchid)
            };
            string getproduct = "SELECT cp.productId,cp.ComboProductId,cp.BranchId,(SELECT image_url FROM finascop_stock_item_images WHERE product_id = cp.ComboProductId ORDER BY image_type DESC LIMIT 1) AS image_url, CONCAT(fsi.stit_SKU, ' - ', CASE WHEN cp.CostType = 1 THEN 'Free' WHEN cp.CostType = 2 THEN 'Discounted'  WHEN cp.CostType = 3 THEN 'Suggested'  ELSE '' END, CASE WHEN cp.CostType = 1 THEN CONCAT(', - ', CASE  WHEN cp.DiscountId = 1 THEN 'Mandatory'  WHEN cp.DiscountId = 2 THEN 'Optional'  ELSE '' END) ELSE '' END, CASE WHEN cp.CostType = 2 THEN CONCAT(', - ', CASE WHEN cp.DiscountId = 1 THEN cp.DiscountType WHEN cp.DiscountId = 2 THEN CONCAT(cp.DiscountType, '%')   ELSE ''  END) ELSE '' END, ', Quantity: ', cp.qutatity ) AS ProductDetails, fsi.stit_SKU FROM Comboproduct cp  INNER JOIN finascop_stock_itemmaster fsi ON fsi.stit_ID = cp.ComboProductId WHERE cp.productId = @Comboparentproduct and cp.BranchId=@brid GROUP BY  fsi.stit_SKU, cp.CostType,  cp.DiscountId, cp.DiscountType,cp.qutatity;";
            DataTable comboproduct = DataServiceMySql.GetDataTable(getproduct, parmeters: sqldaId);
            if (comboproduct != null)
            {
                rptrcomboproduct.DataSource = comboproduct;
                rptrcomboproduct.DataBind();
                combopoup();
            }
        }
        protected void SDScomboproductshow_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            e.Command.Parameters["branchid"].Value = Page.User.IsInRole("BranchManager") ? UserService.UserRoleBranchId : (object)DBNull.Value;
        }
        protected void btncomboedit_Click(object sender, EventArgs e)
        {
            var lbtn = (LinkButton)sender;
            int productId = Convert.ToInt32(lbtn.Attributes["comboid"]);
            int brid = Convert.ToInt32(lbtn.Attributes["brid"]);

            var sqlPrdParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("product_stit_id", productId),
                new KeyValuePair<string, object>("brid", brid)
            };
            string masterproduct = "SELECT i.`stit_ID`,cp.`BranchId`,i.stit_SKU,cp.`BranchId`,cp.productId,(SELECT image_url FROM finascop_stock_item_images WHERE product_id=i.stit_id ORDER BY image_type DESC LIMIT 1 ) AS image_url FROM finascop_stock_itemmaster i INNER JOIN Comboproduct cp ON i.stit_ID=cp.productId  WHERE cp.productId=@product_stit_id and cp.`BranchId`=@brid ";
            var dt = DataServiceMySql.GetDataTable(masterproduct, parmeters: sqlPrdParams);
            if (dt != null && dt.Rows.Count > 0)
            {
                selBranch.SelectedItem.Value = dt.Rows[0]["BranchId"].ToString();
                hdfmasterproduct.Value = productId.ToString();
                lblproduct.Text = dt.Rows[0]["stit_SKU"].ToString();
                imgproduct.Src = RetalineProAgent.Service.Common.ImageUrl(dt.Rows[0]["image_url"].ToString());
                comboproductload(productId, Convert.ToInt32(selBranch.SelectedItem.Value));
            }
            combopoup();
        }
        protected void btnsavecombo_Click(object sender, EventArgs e)
        {
            rptrcomboproduct.DataSource = null;
            rptrcomboproduct.DataBind();
            Common.ShowCustomAlert(this.Page, "Success", "Saved successfully!", true, "/Tenant/Comboproduct");
        }

        protected void btncreatecombo_Click(object sender, EventArgs e)
        {
            if (selproduct.SelectedIndex <= 0) return;
            var sqlPrdParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("product_stit_id", selproduct.SelectedItem.Value),
                new KeyValuePair<string, object>("brid", selBranch.SelectedValue)
            };           
            string masterproduct = "SELECT i.`stit_ID`, i.stit_SKU ,(SELECT image_url FROM finascop_stock_item_images WHERE product_id=i.stit_id ORDER BY image_type DESC LIMIT 1 ) AS image_url FROM finascop_stock_itemmaster i  WHERE i.`stit_ID`=@product_stit_id";
            var dt = DataServiceMySql.GetDataTable(masterproduct, parmeters: sqlPrdParams);
            if (dt != null && dt.Rows.Count > 0)
            {
                lblproduct.Text = dt.Rows[0]["stit_SKU"].ToString();
                imgproduct.Src = RetalineProAgent.Service.Common.ImageUrl(dt.Rows[0]["image_url"].ToString());
            }
            combopoup();


        }
        private void combopoup()
        {
            string strAlertSCript = "$('#CreateComboProducts').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void selcomboproduct_DataBound(object sender, EventArgs e)
        {
            selcomboproduct.Items.Insert(0, new ListItem("Select Product", ""));
        }

        protected void lstshowcombo_ItemCommand(object sender, ListViewCommandEventArgs e)
        {
                                   
        }

        protected void btncomboDelete_Click(object sender, EventArgs e)
        {
            var lbtn = (LinkButton)sender;
            int productId = Convert.ToInt32(lbtn.Attributes["comboid"]);
            int brid = Convert.ToInt32(lbtn.Attributes["brid"]);
            var sqlcombo = new List<KeyValuePair<string, object>>
            {
                  new KeyValuePair<string, object>("Comboparentproduct", productId),
                  new KeyValuePair<string, object>("brid", brid)
            };
            string deletecombo = "DELETE FROM Comboproduct WHERE ProductId=@Comboparentproduct and BranchId=@brid ;";
            deletecombo+= "UPDATE finascop_stock_branch_inventory SET hascombo=0 WHERE stit_id=@Comboparentproduct and branch_id=@brid";
            DataServiceMySql.ExecuteSql(deletecombo, UserService.GetAPIConnectionString(), sqlcombo);
            Common.ShowCustomAlert(this.Page, "Success", "Item Deleted successfully!", true, "/Tenant/Comboproduct");
        }
    }
   
}