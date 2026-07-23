using Amazon.Auth.AccessControlPolicy;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class StockPrice : Base.BasePartnerPage
    {
        List<Store> _myBranches = null;
        List<Store> MyBranches
        {
            get
            {

                if (_myBranches == null)
                {
                    _myBranches = Core.Services.APIService.GetStores(this.CurrentUser.APIStoreId, false);
                }
                return _myBranches;
            }
            set { _myBranches = value; }
        }

        bool allWithStockAndPrice
        {
            get
            {
                if (ViewState["ALLWITHSTOCKANDPRICE"] == null)
                    return true;
                return (bool)ViewState["ALLWITHSTOCKANDPRICE"];
            }
            set
            {
                ViewState["ALLWITHSTOCKANDPRICE"] = value;
            }
        }

        bool sellingPriceMoreThanMrp
        {
            get
            {
                if (ViewState["SELLINGPRICEMORETHANMRP"] == null)
                    return false;
                return (bool)ViewState["SELLINGPRICEMORETHANMRP"];
            }
            set
            {
                ViewState["SELLINGPRICEMORETHANMRP"] = value;
            }
        }

        bool hasAnyStock
        {
            get
            {
                if (ViewState["HASANYSTOCK"] == null)
                    return false;
                return (bool)ViewState["HASANYSTOCK"];
            }
            set
            {
                ViewState["HASANYSTOCK"] = value;
            }
        }



        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                int storegroupid = this.CurrentUser.APIStoreId;
                var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name, ifnull(tradeRestriction, 0) as tradeRestriction FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                string branchID = "";
                if (dtBranches != null && dtBranches.Rows.Count > 0)
                {
                    DataRow dr = dtBranches.Rows[0];
                    string branchName = dr["br_name"].ToString();
                    Int64 tradeRestriction = Convert.ToInt64(dr["tradeRestriction"]);
                    if(tradeRestriction > 0)
                        plcMsgRestrictionmode.Visible= true;
                    
                    var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                    if (btStoreGrp != null && btStoreGrp.Rows.Count > 0)
                    {
                        DataRow dc = btStoreGrp.Rows[0];
                        string storeGroup = dc["cnt"].ToString();
                        if (Convert.ToInt32(storeGroup) == 1)
                        {
                            branchname.Visible = true;
                            branchname.Value = dr["br_name"].ToString();
                            branchID = dr["br_ID"].ToString();
                            // RegisterStartupScript to execute JavaScript on page load
                            ClientScript.RegisterStartupScript(this.GetType(), "SetVisibilityScript", $@"
                                var oneStore = document.getElementById('oneStore');
                                var moreThanOneStr = document.getElementById('moreThanOneStr');

                                // Execute JavaScript code
                                oneStore.style.display = 'block';
                                moreThanOneStr.style.display = 'none';
                            ", true);
                        }
                        else
                        {
                            branchname.Visible = false;
                            branchID = selBranches.Text;
                            // RegisterStartupScript to execute JavaScript on page load
                            ClientScript.RegisterStartupScript(this.GetType(), "SetVisibilityScript", $@"
                            var oneStore = document.getElementById('oneStore');
                            var moreThanOneStr = document.getElementById('moreThanOneStr');

                            // Execute JavaScript code
                            oneStore.style.display = 'none';
                            moreThanOneStr.style.display = 'block';
                        ", true);
                        }
                    }
                }

                var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, bg.store_group_grosmartMerchant FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup = bg.store_group_id WHERE bg.store_group_id  = {storegroupid}", UserService.GetAPIConnectionString());
                int grosmartStore = 0;
                if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
                {
                    DataRow da = dtStoreGroup.Rows[0];
                    string grosmart = da["store_group_grosmartMerchant"].ToString();
                    grosmartStore = Convert.ToInt32(grosmart);

                    if (grosmartStore == 1)
                    {
                        gvProducts.Columns[5].Visible = true;
                    }
                    else
                    {
                        gvProducts.Columns[5].Visible = false;
                    }
                }
            }

            ScriptManager.RegisterStartupScript(this, GetType(), "ReopenModal", @"$(function() {if ($('#" + hdnModalOpen.ClientID + @"').val() === '1') {$('#modalVerify').modal('show');}});", true);
            //if (IsPostBack)
            //{
            //    SaveChanges();
            //    gvProducts.DataBind();
            //}

        }
        protected void SDSInventory_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storeId"].Value = this.CurrentUser.APIStoreId;
            e.Command.Parameters["@startIndex"].Value = (gvProducts.PageIndex * gvProducts.PageSize);

            //e.Command.Parameters["@user"].Value = Page.User.Identity.Name;
            if (selBranches.Items.Count <= 1)
                selBranches.DataBind();
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["@BranchId"].Value = brid;
            }
            else
            {
                if (selBranches.Items.Count == 2)
                    e.Command.Parameters["@BranchId"].Value = selBranches.Items[1].Value;
                else if (selBranches.Items.Count > 0 && !String.IsNullOrEmpty(selBranches.Text))
                    e.Command.Parameters["@BranchId"].Value = selBranches.Text;

                if (CurrentUser.TenantType == 2) // && (selBranches.Items.Count == 2 || selBranches.Items.Count > 0 && !String.IsNullOrEmpty(selBranches.Text)))
                {
                    e.Command.Parameters["retailerType"].Value = 1;
                }
                //else if (CurrentUser.TenantStatus != 2 && (selBranches.Items.Count == 2 || selBranches.Items.Count > 0 && !String.IsNullOrEmpty(selBranches.Text)))
                //{
                //    e.Command.Parameters["retailerType"].Value = 0;
                //}
            }

        }

        protected void SDSInventory_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            ////plcPrice.Visible = e.AffectedRows > 0;
            ////ltrStrPriceDefaultText.Visible = plcPrice.Visible;
            ////ctrlInventorySetup1.SelectedItemsCount = 
            ////ltrTotalItemsSelected.Text= 
            ////lblSelectedCount.Text = e.AffectedRows.ToString();

            //// paging controls
            //int startRowOnPage = (gvProducts.PageIndex * gvProducts.PageSize) + 1;
            //int lastRowOnPage = startRowOnPage + gvProducts.Rows.Count - 1;
            //int totalRows = e.AffectedRows;

            ////ltrPagingCurStart.Text = startRowOnPage.ToString();
            ////ltrPagingCurTotal.Text = lastRowOnPage.ToString();
            ////ltrPagingTotal.Text = totalRows.ToString();
            ////count2.Text = "Showing " + startRowOnPage.ToString() +
            ////              " - " + lastRowOnPage + " of " + totalRows;

            int totalRecords = (int)e.Command.Parameters["totalRecords"].Value;
            gvProducts.VirtualItemCount = totalRecords;

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvProducts.PageIndex > 0)
                gvProducts.PageIndex = gvProducts.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvProducts.PageIndex < gvProducts.PageCount - 1)
                gvProducts.PageIndex = gvProducts.PageIndex + 1;
        }

        protected void gvProducts_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvProducts.PageIndex * gvProducts.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvProducts.Rows.Count - 1;
            //ltrPagingCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSInventory.Select(DataSourceSelectArguments.Empty);
            var drs = dv.ToTable().Select("MRP is null or MRP < 1");
            if (drs != null && drs.Length > 0)
            {
                ltrComment.Text = $"{drs.Length} out of {dv.Count} records are missing {GetLabelPrice()} or Quantity. Please enter value to these records also. Otherwise these items will not be published to site.";
            }

        }

        private Store CurBrnach()
        {
            List<Store> myBranches = MyBranches.Where(b => plcSelectBranchModel.Visible == false || selBranches.Text == b.BranchId.ToString()).ToList();
            if (myBranches == null || myBranches.Count < 1)
            {
                return null;
            }
            lblResult.Text = "";

            Store myBranch = null;
            if (myBranches.Count == 1)
            {
                myBranch = myBranches[0];
            }
            else
            {
                if (selBranches.Items.Count > 1 && myBranches.Any(b => b.BranchId.ToString() == selBranches.Text))
                    myBranch = myBranches.FirstOrDefault(b => b.BranchId.ToString() == selBranches.Text);
                else
                    myBranch = myBranches[0];
            }
            return myBranch;
        }

        protected void chkProductItem_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chkProductItem = (CheckBox)sender;
            if (chkProductItem == null)
                return;

            string strnotinbrnach = chkProductItem.Attributes["notinbrnach"];
            string stritemid = chkProductItem.Attributes["itemid"];
            int itemid = Convert.ToInt32(stritemid);

            EnableDisableItemForBranch(strnotinbrnach, itemid, chkProductItem.Checked);

            SDSInventory.Select(DataSourceSelectArguments.Empty);
            gvProducts.DataBind();
            //ctrlInventorySetup1.ResetInventory();
        }

        private void EnableDisableItemForBranch(string strnotinbrnach, int itemid, bool enable)
        {
            Store curBranch = CurBrnach();
            if (curBranch == null)
            {
                if (selBranches.Items.Count > 1)
                    lblResult.Text = "Please select branch.";
                else
                    lblResult.Text = "Not active branch.";
                lblResult.ForeColor = System.Drawing.Color.Red;
                return;
            }

            List<int> notinbrnaches = new List<int>();
            try
            {

                if (!string.IsNullOrEmpty(strnotinbrnach))
                {
                    foreach (string strBranchId in strnotinbrnach.Split(','))
                    {
                        try
                        {
                            int branchId = Convert.ToInt32(strBranchId);
                            if (!notinbrnaches.Contains(branchId))
                                notinbrnaches.Add(branchId);
                        }
                        catch { }
                    }
                }
                if (enable)
                {
                    if (notinbrnaches.Contains(curBranch.BranchId))
                        notinbrnaches.Remove(curBranch.BranchId);
                }
                else
                {
                    if (!notinbrnaches.Contains(curBranch.BranchId))
                        notinbrnaches.Add(curBranch.BranchId);
                }
            }
            catch
            {

            }

            string strnotinbrnachNew = String.Join(",", notinbrnaches.ToArray());
            DataService.ExecuteSql($"UPDATE InventoryMapping set NotInBranch = '{strnotinbrnachNew}' WHERE Id={itemid}");

        }

        public bool IsDisabledBranch(string strnotinbranches)
        {
            Store curBranch = CurBrnach();
            if (curBranch == null)
                return false;

            if (string.IsNullOrEmpty(strnotinbranches))
                return false;

            foreach (string strBranchId in strnotinbranches.Split(','))
            {
                try
                {
                    int branchId = Convert.ToInt32(strBranchId);
                    if (curBranch.BranchId == branchId)
                        return true;
                }
                catch { }
            }

            return false;
        }

        //protected void btnStockSaveChanges_Click(object sender, EventArgs e)
        //{
        //    SaveChanges();
        //}
        //private void SaveChanges()
        //{
        //    string strSelectedBranchId = selBranches.Text;
        //    if (String.IsNullOrEmpty(strSelectedBranchId) && selBranches.Items.Count == 2)
        //        strSelectedBranchId = selBranches.Items[1].Value;

        //    if (String.IsNullOrEmpty(strSelectedBranchId))
        //    {
        //        Common.ShowCustomAlert(this.Page, "Failure", "Invalid store selected", false);
        //        return;
        //    }

        //    foreach (GridViewRow row in gvProducts.Rows)
        //    {
        //        SaveRowChanges(row);
        //    }


        //    hidInvChanges.Value = "0";

        //    if (hasAnyStock && this.CurrentUser.TenantStage == 7)
        //    {
        //        List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
        //        tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
        //        DataService.ExecuteSql("UPDATE AppTenant SET Stage = 1 WHERE Stage = 7 AND Id=@tenantId", parmeters: tenantParmeters);
        //        Service.UserService.CachedDefaultUser = null;
        //    }

        //}

        protected string GetOnInputScript(object dataItem)
        {
            bool hasRestaurantService = Convert.ToBoolean(DataBinder.Eval(dataItem, "hasRestaurantService"));
            int platformTaxEnabled = Convert.ToInt32(DataBinder.Eval(dataItem, "platform_tax_enabled"));
            if (platformTaxEnabled==1 && hasRestaurantService)
            {
                // If hasRestaurantService is 1, disable decimal input and show warning
                return "this.value = this.value.replace(/[^0-9]/g, ''); showDecimalWarning(this);";
            }
            else
            {
                // If hasRestaurantService is 0, allow decimal input and hide warning
                return @"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); hideDecimalWarning();";
            }
        }

        protected string GetOnBlurScript(object dataItem)
        {
            // Hide warning message on blur
            return "hideDecimalWarning(this);";
        }

        private void SaveRowChanges(GridViewRow row)
        {
            string strSelectedBranchId = selBranches.Text;
            if (String.IsNullOrEmpty(strSelectedBranchId) && selBranches.Items.Count == 2)
                strSelectedBranchId = selBranches.Items[1].Value;

            if (String.IsNullOrEmpty(strSelectedBranchId))
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid store selected", false);
                return;
            }

            TextBox txtMrp = (TextBox)row.FindControl("txtMRP");
            TextBox txtSellingPrice = (TextBox)row.FindControl("txtSellingPrice");
            TextBox txtDiscountSP = (TextBox)row.FindControl("txtDiscountSP");
            TextBox txtPStock = (TextBox)row.FindControl("txtPStock");
            HiddenField hidStitid = (HiddenField)row.FindControl("hidStitID");
            double discSP = 0; try { discSP = Convert.ToDouble(txtDiscountSP.Text); } catch { discSP = 0; }
            double textMrp = 0; try { textMrp = Convert.ToDouble(txtMrp.Text); } catch { textMrp = 0; }
            int stock = 0; try { stock = Convert.ToInt32(txtPStock.Text); } catch { stock = 0; }
            double textSellingPrice = 0; try { textSellingPrice = Convert.ToDouble(txtSellingPrice.Text); } catch { textSellingPrice = 0; }

            //if (stock == 0)
            //{
            //    Common.ShowToastifyMessage(this.Page, "Stock cannot be 0.", "danger");
            //}
            //if (textMrp == 0 || textSellingPrice == 0)
            //{
            //    Common.ShowToastifyMessage(this.Page, "MRP and Selling price cannot be 0.", "danger");
            //}
            //else if (textSellingPrice <= textMrp && discSP < textSellingPrice)
            //{
            //    sellingPriceMoreThanMrp = true;
            //}
             if (textSellingPrice > textMrp)
                {
                   
                    Common.ShowCustomAlert(this.Page, "Failed", "Selling Price should be less than or equal to" + " " + GetLabelPrice(), false);
                }
            else if (discSP > textSellingPrice)
                {
                    Common.ShowCustomAlert(this.Page, "Failed", "Discount price is not meeting the required criteria.", false);
                }

            else
            {
                string oldvalStock = txtPStock.Attributes["oldval"], oldvalMrp = txtMrp.Attributes["oldval"], oldvalSellingPrice = txtSellingPrice.Attributes["oldval"], oldvalDiscountSP = txtDiscountSP.Attributes["oldiscountval"];

                //SPFFactor calculation
                //=============================================================================================================================

                //List<KeyValuePair<string, object>> spfParams = new List<KeyValuePair<string, object>>();
                //spfParams.Add(new KeyValuePair<string, object>("stitid", hidStitid.Value));
                //spfParams.Add(new KeyValuePair<string, object>("brandId", txtDiscountSP.Attributes["prdBrand"]));
                //spfParams.Add(new KeyValuePair<string, object>("categoryId", txtDiscountSP.Attributes["prdCategory"]));
                //spfParams.Add(new KeyValuePair<string, object>("itemId", txtDiscountSP.Attributes["itemId"]));

                //var spfTbl1 = DataServiceMySql.GetDataTable($"SELECT spf_factor FROM selling_price_factor WHERE spf_type = 1 AND spf_detail  = @stitid", UserService.GetAPIConnectionString(), spfParams);

                //string spfFact = "";
                //double spfFactor1 = 0, spfFat = 0, spfFactor2 = 0, spfFactor3 = 0, spfFactor4 = 0;
                //if (spfTbl1 != null && spfTbl1.Rows.Count > 0)
                //{
                //    spfFact = spfTbl1.Rows[0]["spf_factor"].ToString();
                //    spfFactor1 = Convert.ToDouble(spfFact);
                //    if (spfFactor1 > 1)
                //    {
                //        spfFat = spfFactor1;
                //    }
                //}
                //else
                //{
                //    var spfTbl2 = DataServiceMySql.GetDataTable($"SELECT spf_factor FROM selling_price_factor WHERE spf_type = 2 AND spf_detail = @brandId", UserService.GetAPIConnectionString(), spfParams);
                //    if (spfTbl2 != null && spfTbl2.Rows.Count > 0)
                //    {
                //        spfFact = spfTbl2.Rows[0]["spf_factor"].ToString();
                //        spfFactor2 = Convert.ToDouble(spfFact);
                //        if (spfFactor2 > 1)
                //        {
                //            spfFat = spfFactor2;
                //        }
                //    }
                //    else
                //    {
                //        var spfTbl3 = DataServiceMySql.GetDataTable($"SELECT spf_factor FROM selling_price_factor WHERE spf_type = 3 AND spf_detail = @itemId", UserService.GetAPIConnectionString(), spfParams);
                //        if (spfTbl3 != null && spfTbl3.Rows.Count > 0)
                //        {
                //            spfFact = spfTbl3.Rows[0]["spf_factor"].ToString();
                //            spfFactor3 = Convert.ToDouble(spfFact);
                //            if (spfFactor3 > 1)
                //            {
                //                spfFat = spfFactor3;
                //            }
                //        }
                //        else
                //        {
                //            var spfTbl4 = DataServiceMySql.GetDataTable($"SELECT spf_factor FROM selling_price_factor WHERE spf_type = 4 AND spf_detail = @categoryId", UserService.GetAPIConnectionString(), spfParams);

                //            if (spfTbl4 != null && spfTbl4.Rows.Count > 0)
                //            {
                //                spfFact = spfTbl4.Rows[0]["spf_factor"].ToString();
                //                spfFactor4 = Convert.ToDouble(spfFact);
                //                if (spfFactor4 > 1)
                //                {
                //                    spfFat = spfFactor4;
                //                }
                //            }
                //        }
                //    }
                //}
                //double spfFactorCalc = 0, spfFatCal = 0;
                //string spfFactcal = "";
                //if (spfFat > 1)
                //{
                //    spfFactorCalc = spfFat;
                //}
                //else
                //{
                //    var sysConfigTbl = DataServiceMySql.GetDataTable($"SELECT cfg_Name, cfg_Value, cfg_Type, cfg_Enabled FROM sys_configuration WHERE cfg_Name='DEFAULT_SPF'", UserService.GetAPIConnectionString());
                //    if (sysConfigTbl != null && sysConfigTbl.Rows.Count > 0)
                //    {
                //        spfFactcal = sysConfigTbl.Rows[0]["cfg_Value"].ToString();
                //        spfFatCal = Convert.ToDouble(spfFactcal);
                //        //if (spfFatCal > 1)
                //        //{
                //        spfFactorCalc = spfFatCal;
                //        //}

                //    }

                //}

                //=============================================================================================================================




                List<KeyValuePair<string, object>> mmfFactorParams = new List<KeyValuePair<string, object>>();
                mmfFactorParams.Add(new KeyValuePair<string, object>("stitid", hidStitid.Value));
                mmfFactorParams.Add(new KeyValuePair<string, object>("brandId", txtDiscountSP.Attributes["prdBrand"]));
                mmfFactorParams.Add(new KeyValuePair<string, object>("categoryId", txtDiscountSP.Attributes["prdCategory"]));
                mmfFactorParams.Add(new KeyValuePair<string, object>("itemId", txtDiscountSP.Attributes["itemId"]));

                var mmfFactorSKU = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 1 AND mm_detail =@stitid", UserService.GetAPIConnectionString(), mmfFactorParams);


                string mmf_factorSKU = "", spffactorBrand = "", spfFactorItem = "", spfFactorCS = "";
                double mmFactor = 0, mmf_Factor = 0, spf_factorBrand = 0, mmfFactor = 0, factorItem = 0, factorSC = 0;

                if (mmfFactorSKU != null && mmfFactorSKU.Rows.Count > 0)
                {
                    mmf_factorSKU = mmfFactorSKU.Rows[0]["mm_factor"].ToString();
                    mmFactor = Convert.ToDouble(mmf_factorSKU);
                    if (mmFactor > 0)
                    {
                        mmfFactor = mmFactor;
                    }
                }
                else
                {
                    var mmfFactorBrand = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 2 AND mm_detail = @brandId", UserService.GetAPIConnectionString(), mmfFactorParams);
                    if (mmfFactorBrand != null && mmfFactorBrand.Rows.Count > 0)
                    {
                        spffactorBrand = mmfFactorBrand.Rows[0]["mm_factor"].ToString();
                        spf_factorBrand = Convert.ToDouble(spffactorBrand);
                        if (spf_factorBrand > 0)
                        {
                            mmfFactor = spf_factorBrand;
                        }
                    }

                    else
                    {
                        var mmf_factorItem = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 3 AND mm_detail = @itemId", UserService.GetAPIConnectionString(), mmfFactorParams);
                        if (mmf_factorItem != null && mmf_factorItem.Rows.Count > 0)
                        {
                            spfFactorItem = mmf_factorItem.Rows[0]["mm_factor"].ToString();
                            factorItem = Convert.ToDouble(spfFactorItem);
                            if (factorItem > 0)
                            {
                                mmfFactor = factorItem;
                            }
                        }
                        else
                        {
                            var mmf_factorSC = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 4 AND mm_detail = @categoryId", UserService.GetAPIConnectionString(), mmfFactorParams);
                            if (mmf_factorSC != null && mmf_factorSC.Rows.Count > 0)
                            {
                                spfFactorCS = mmf_factorSC.Rows[0]["mm_factor"].ToString();
                                factorSC = Convert.ToDouble(spfFactorCS);
                                if (factorSC > 0)
                                {
                                    mmfFactor = factorSC;
                                }
                            }
                        }
                    }
                }

                double mmfFactorCalc = 0, mmfFatCal = 0;
                string mmfFactcal = "";

                if (mmfFactor > 1)
                {
                    mmfFactorCalc = mmfFactor;
                }
                else
                {
                    var sysConfigTbll = DataServiceMySql.GetDataTable($"SELECT cfg_Name, cfg_Value, cfg_Type, cfg_Enabled FROM sys_configuration WHERE cfg_Name='DEFAULT_MM'", UserService.GetAPIConnectionString());
                    if (sysConfigTbll != null && sysConfigTbll.Rows.Count > 0)
                    {
                        mmfFactcal = sysConfigTbll.Rows[0]["cfg_Value"].ToString();
                        mmfFatCal = Convert.ToDouble(mmfFactcal);
                        mmfFactorCalc = mmfFatCal;
                    }
                }


                int storegroupid = this.CurrentUser.APIStoreId;
                var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                string brachID = "";
                string brId = "";
                if (dtBranches != null && dtBranches.Rows.Count > 0)
                {
                    DataRow da = dtBranches.Rows[0];
                    brachID = da["br_ID"].ToString();
                }
                if (branchname.Visible == true)
                {
                    brId = brachID;
                }
                else
                {
                    brId = selBranches.SelectedValue;
                }

                List<KeyValuePair<string, object>> countParams = new List<KeyValuePair<string, object>>();
                countParams.Add(new KeyValuePair<string, object>("itemId", hidStitid.Value));
                countParams.Add(new KeyValuePair<string, object>("branchId", brId));
                countParams.Add(new KeyValuePair<string, object>("stitid", hidStitid.Value));
                var fsbiCount = DataServiceMySql.GetDataTable($"SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = @itemId  AND branch_id = @branchId ", UserService.GetAPIConnectionString(), countParams);

                var gst = DataServiceMySql.GetDataTable($"SELECT IFNULL(stit_GST, 0) AS stit_GST FROM finascop_stock_itemmaster where stit_ID = @stitid", UserService.GetAPIConnectionString(), countParams);


                double itemLandingCost = 0, itemMMG = 0, desiredMargin = 0, discount_selling_price = 0, calculatedSP = 0, grozeoMargin = 0;
                if (gvProducts.Columns[5].Visible == true && !String.IsNullOrEmpty(txtDiscountSP.Text))
                {

                    if (discSP > 0)
                    {
                        itemLandingCost = discSP;
                        itemMMG = (Math.Round(textSellingPrice, 2)) - (Math.Round(itemLandingCost, 2)); //margin
                        desiredMargin = textSellingPrice * mmfFactorCalc / 100; //MRP*MM%
                        if (itemMMG >= desiredMargin)
                        {
                            discount_selling_price = discSP;
                            calculatedSP = textSellingPrice; //textMrp - (itemMMG * spfFactorCalc / 100); //MRP - (MARGIN*SellingPriceFactor%)
                            grozeoMargin = calculatedSP - itemLandingCost; //(calculatedSP - landingCost)
                        }
                        else
                        {
                            //discount_selling_price = 0;
                            //calculatedSP = 0;
                            //grozeoMargin = 0;
                            //ShowSuccess("Warning!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Please check discount selling price.</a></h5>");
                            Common.ShowToastifyMessage(this.Page, "Please check discount selling price.", "danger");
                        }
                    }
                }

                else
                {
                    itemLandingCost = textSellingPrice;
                    itemMMG = Math.Round(textMrp - (itemLandingCost), 2);
                    discount_selling_price = 0;
                    calculatedSP = 0;
                    grozeoMargin = 0;
                }


                double mrp = 0; try { mrp = textMrp; } catch { mrp = 0; }
                double pStock = Convert.ToDouble(txtPStock.Text);

                //if (pStock <= 0)
                //    allWithStockAndPrice = false;

                string fpod_poMMGleastSKU = Convert.ToString(itemMMG);
                double fpod_spHmDel = 0, fpod_spPikup = 0;


                double fcpod_spHmDel = Math.Round(calculatedSP, 2);
                double fcpod_spCouDel = Math.Round(calculatedSP, 2);
                double fcpod_spPikup = Math.Round(calculatedSP, 2);


                double fpod_spetHmDel = (fcpod_spHmDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                double fcpod_spetHmDel = Math.Round(fpod_spetHmDel, 2);
                double fpod_spetCouDel = (fcpod_spCouDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                double fcpod_spetCouDel = Math.Round(fpod_spetCouDel, 2);
                double fpod_spetPikup = (fcpod_spPikup * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                double fcpod_spetPikup = Math.Round(fpod_spetPikup, 2);

                double margin = 0;
                try
                {
                    if (mrp > 0 && itemLandingCost > 0 && mrp > itemLandingCost)
                        margin = (100 - ((itemLandingCost * 100) / mrp));
                }
                catch { margin = 0; }

                List<KeyValuePair<string, object>> updateparam = new List<KeyValuePair<string, object>>();
                updateparam.Add(new KeyValuePair<string, object>("sellingprice", textSellingPrice)); // txtSellingPrice.Text));
                updateparam.Add(new KeyValuePair<string, object>("discountSP", discount_selling_price));
                updateparam.Add(new KeyValuePair<string, object>("mrpval", mrp));
                updateparam.Add(new KeyValuePair<string, object>("stock", pStock));
                updateparam.Add(new KeyValuePair<string, object>("expressval", fcpod_spHmDel));
                updateparam.Add(new KeyValuePair<string, object>("courierval", fcpod_spCouDel));
                updateparam.Add(new KeyValuePair<string, object>("pickupval", fcpod_spPikup));
                updateparam.Add(new KeyValuePair<string, object>("leastSKU", fpod_poMMGleastSKU));
                updateparam.Add(new KeyValuePair<string, object>("margin", margin));
                updateparam.Add(new KeyValuePair<string, object>("stitid", hidStitid.Value));
                updateparam.Add(new KeyValuePair<string, object>("branchid", strSelectedBranchId));
                updateparam.Add(new KeyValuePair<string, object>("grozeoMargin", grozeoMargin));

                string strUpdateSql = $"insert into finascop_stock_branch_inventory(stit_id, branch_id, selling_price, mrp, item_count, fpod_customerRateHmDel, " +
                $"fpod_customerRateCouDel, fpod_customerRatePikup, fpod_leastSKUmrp,fpod_poLandingCostleastSKU, fpod_poMMGleastSKU, issponsered, current_margin, discount_selling_price, grozeo_margin) " +
                $"values(@stitid, @branchid, @sellingprice, @mrpval, @stock, @expressval, @courierval, @pickupval, @mrpval, @sellingprice, @leastSKU, @discountSP, @grozeoMargin, " +
                    $"(case when issponsered = 0 then 0 when ifnull(sponsered_margin, 0) > @margin then 2 else issponsered  end), (case when issponsered > 0 then @margin else 0 end)) " +
                $"ON DUPLICATE KEY UPDATE selling_price = VALUES(selling_price), mrp = VALUES(mrp), item_count = VALUES(item_count), " +
                    $"fpod_customerRateHmDel = VALUES(fpod_customerRateHmDel), fpod_customerRateCouDel = VALUES(fpod_customerRateCouDel), " +
                    $"fpod_customerRatePikup = VALUES(fpod_customerRatePikup), fpod_leastSKUmrp = VALUES(fpod_leastSKUmrp), " +
                    $"fpod_poLandingCostleastSKU = VALUES(fpod_poLandingCostleastSKU), fpod_poMMGleastSKU = VALUES(fpod_poMMGleastSKU), " +
                    //$"issponsered = (case when issponsered = 0 then 0 when issponsered = 1 and ifnull(sponsered_margin, 0) > @margin then 2 else issponsered  end), " +
                    $"issponsered = (case when @discountSP > 0 then 1 else 0 end)," +
                    $"current_margin = (case when issponsered > 0 then @margin else current_margin end), discount_selling_price=@discountSP, grozeo_margin=@grozeoMargin";
                DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), updateparam);
                int itemId = Convert.ToInt32(hidStitid.Value);
                int branchID = Convert.ToInt32(strSelectedBranchId);
                double selPrice = itemLandingCost;
                double itemCnt = pStock;
                string type = "Product update when stock";
                string action = "Stock update";
                string inventoryResult = Core.Services.APIService.InventoryLog(itemId, branchID, selPrice, itemCnt, type, action);
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                string User = this.CurrentUser.Email;
                string textSellingPriceStr = textSellingPrice.ToString();
                string discountSellingPriceStr = discount_selling_price.ToString();
                string mrpStr = mrp.ToString();
                string pStockStr = pStock.ToString();
                string itemIdStr = itemId.ToString();
                string Stock = pStock.ToString();
                string Expressval = fcpod_spHmDel.ToString();
                string courierval = fcpod_spCouDel.ToString();
                string pickupval = fcpod_spPikup.ToString();
                string leastSKU = fpod_poMMGleastSKU.ToString();
                string Margin = margin.ToString();
                string stitid = hidStitid.Value.ToString();
                string branchid = strSelectedBranchId.ToString();
                var items = new[]
                {
                    new { Key = "selling price", Value = textSellingPriceStr },
                    new { Key = "discount selling price", Value = discountSellingPriceStr },
                    new { Key = "mrp", Value = mrpStr },
                    new { Key = "stock", Value = pStockStr },
                    new { Key = "Item Id", Value = itemIdStr },
                    new { Key = "Stock", Value = Stock },
                    new { Key = "Expressval", Value = Expressval },
                    new { Key = "Courierval", Value = courierval },
                    new { Key = "Pickupval", Value = pickupval },
                    new { Key = "LeastSKU", Value = leastSKU },
                    new { Key = "Margin", Value = Margin },
                    new { Key = "Stitid", Value = stitid },
                    new { Key = "branchid", Value = branchid },
                };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);


                Common.ShowToastifyMessage(Page, "Saved successfully", "/Tenant/StockPrice");
                gvProducts.DataBind();
                          }
        }
       

        protected void btnStockPublishItems_Click(object sender, EventArgs e)
        {
            //List<Store> myBranches = MyBranches.Where(b => plcSelectBranchModel.Visible = false || selBranches.Text == "-1" || selBranches.Text == b.BranchId.ToString()).ToList();
            //if (myBranches == null || myBranches.Count < 1)
            //{
            //    lblResult.Text = "Failure! Your store location is not active.";
            //    lblResult.ForeColor = System.Drawing.Color.Red;
            //    return;
            //}
            //lblResult.Text = "";

            //Store myBranch = null;
            //if (myBranches.Count == 1)
            //{
            //    myBranch = myBranches[0];
            //}
            //else
            //{
            //    if (selBranches.Items.Count > 1 && myBranches.Any(b => b.BranchId.ToString() == selBranches.Text))
            //        myBranch = myBranches.FirstOrDefault(b => b.BranchId.ToString() == selBranches.Text);
            //    else
            //        myBranch = myBranches[0];
            //}


            //// UploadInventory
            //List<InventoryAPI> inventory = new List<InventoryAPI>();

            //string sql = $"SELECT * FROM BranchCurrentStock WHERE BranchId={myBranch.BranchId} and StoreId="+ this.CurrentUser.StoreGroupId;
            //DataTable dt = DataService.GetDataTable(sql);
            //int missingMRP=0, missingQty=0;
            //foreach(DataRow dr in dt.Rows)
            //{
            //    string erpId = dr["InventoryId"].ToString();
            //    if(string.IsNullOrEmpty(erpId))
            //        continue;

            //    if(dr["Qty"] is DBNull)
            //    {
            //        missingQty++;
            //        continue;
            //    }

            //    double qty = (double)dr["Qty"];
            //    if(qty <= 0)
            //    {
            //        missingQty++;
            //        continue;
            //    }
            //    if (dr["MRP"] is DBNull)
            //    {
            //        missingMRP++;
            //        continue;
            //    }
            //    double mrp = (double)dr["MRP"];
            //    if (mrp <= 0)
            //    {
            //        missingMRP++;
            //        continue;
            //    }
            //    double margin = 0; 
            //    if (dr["Margin"] is DBNull)
            //        margin = (double)dr["Margin"];

            //    if (margin < 5)
            //        margin = 5;

            //    double sellingPrice = mrp - ((mrp * margin) / 100);
            //    InventoryAPI stock = new InventoryAPI();
            //    stock.ErpId = erpId;
            //    stock.SellingPrice = sellingPrice;
            //    stock.Qty = qty;
            //    stock.MRP = mrp;
            //    inventory.Add(stock);
            //}

            ////foreach (GridViewRow gr in gvProducts.Rows)
            ////{
            ////    TextBox txtMrp = (TextBox)gr.FindControl("txtMRP");
            ////    TextBox txtSellingPrice = (TextBox)gr.FindControl("txtSellingPrice");
            ////    TextBox txtPStock = (TextBox)gr.FindControl("txtPStock");
            ////    TextBox txtPCustomMargin = (TextBox)gr.FindControl("txtPCustomMargine");
            ////    int mrp = Convert.ToInt32(txtMrp.Text);
            ////    int sellingPrice = Convert.ToInt32(txtSellingPrice.Text);
            ////    int pStock = Convert.ToInt32(txtPStock.Text);
            ////    int pCustomMargin = Convert.ToInt32(txtPCustomMargin.Text);
            ////    if (pCustomMargin < 5)
            ////        pCustomMargin = 5;

            ////    InventoryAPI stock = new InventoryAPI();
            ////    stock.ErpId = gvProducts.DataKeys[gr.RowIndex].Values[0].ToString();
            ////    stock.SellingPrice = sellingPrice;
            ////    stock.Qty = pStock;
            ////    stock.MRP = mrp;
            ////    inventory.Add(stock);
            ////}

            //if (inventory.Count > 0)
            //{
            //    Core.Services.APIService.UploadInventory(myBranch.APIKey, inventory);
            //    lblResult.Text += $" Published {inventory.Count} items to {myBranch.BranchName}.";
            //    //foreach (var item in myBranches)
            //    //{

            //    //}
            //    lblResult.ForeColor = System.Drawing.Color.Green;

            //}
            //else
            //{
            //    lblResult.Text = "No item published.";
            //    lblResult.ForeColor = System.Drawing.Color.Red;
            //}
            //if (missingMRP > 0)
            //    lblResult.Text += $" Items missing MRP: {missingMRP}.";
            //if (missingQty > 0)
            //    lblResult.Text += $" Items missing Quantity: {missingQty}.";

        }

        protected void ODSStore_Selected(object sender, ObjectDataSourceStatusEventArgs e)
        {
            MyBranches = (List<Store>)e.ReturnValue;
            if (MyBranches != null)
            {
                //ltrBranchName.Visible = MyBranches.Count == 1;
                //ltrBranchName.Text = MyBranches[0].BranchName;
                plcSelectBranchModel.Visible = MyBranches.Count > 1;

            }
            //if( MyBranches.Count > 1)
            //{

            //    plcSelectBranchModel.Visible = true;
            //    btnStockPublishItems.Attributes.Add("data-toggle", "modal");
            //    btnStockPublishItems.Attributes.Add("target", "#modal-select-branch");
            //    btnStockPublishItems.Visible = false;
            //    plcMultipleBranchButton.Visible = true;
            //}
        }

        protected void gvProducts_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            try
            {
                if (e.Row.RowType == DataControlRowType.Header)
                {
                    BoundField myBoundField = (BoundField)((DataControlFieldCell)e.Row.Cells[2]).ContainingField;

                    if (myBoundField != null)
                    {

                        myBoundField.HeaderText = GetLabelPrice();

                    }
                }

                if (DataBinder.Eval(e.Row.DataItem, GetLabelPrice()) == null)
                    return;                                  
                Label lblPCustomMarginVal = (Label)e.Row.FindControl("lblPCustomMarginVal");
                Label lblSellingPrice = (Label)e.Row.FindControl("lblSellingPrice");

            }
            catch { }

            if (e.Row.RowType != DataControlRowType.DataRow)
                return;

            //e.Row.Attributes.Add("data-toggle", "collapse");
            //e.Row.Attributes.Add("data-target", String.Format("#collapse{0}", e.Row.DataItemIndex));
            //e.Row.Attributes.Add("aria-expanded", "false");
            //e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.DataItemIndex));

            e.Row.Attributes.Add("data-toggle", "collapse");
            e.Row.Attributes.Add("data-target", String.Format(".collapse{0}", e.Row.DataItemIndex));
            e.Row.Attributes.Add("aria-expanded", "false");
            e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.DataItemIndex));
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvProducts.PageIndex = 0;
            gvProducts.DataBind();
        }

        protected void DeleteItem_Click(object sender, EventArgs e)
        {
            LinkButton delProductItem = (LinkButton)sender;
            if (delProductItem == null)
                return;

            int storegroupid = this.CurrentUser.APIStoreId;
            //DataTable dt = new DataTable();
            //dt.Columns.Add("Id", typeof(int));

            //DataRow dr = dt.NewRow();
            //dr["Id"] = delProductItem.Attributes["itemid"];
            //dt.Rows.Add(dr);
            //List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            //parmeters.Add(new KeyValuePair<string, object>("StoreId", storegroupid));
            //parmeters.Add(new KeyValuePair<string, object>("IDs", dt));

            string strSql = $"DELETE FROM finascop_stock_branch_inventory WHERE id={delProductItem.Attributes["itemid"]} AND EXISTS(SELECT * FROM finascop_branch WHERE br_ID = finascop_stock_branch_inventory.branch_id AND br_storeGroup={storegroupid})";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString());

            //DataService.ExecuteSP(sp: "DeleteInventoryMapping", parmeters: parmeters);

            SDSInventory.Select(DataSourceSelectArguments.Empty);
            gvProducts.DataBind();
            //ctrlInventorySetup1.ResetInventory();
        }

        protected void lbtnSelectAll_Click(object sender, EventArgs e)
        {
            LinkButton lbtntItem = (LinkButton)sender;
            if (lbtntItem == null)
                return;

            foreach (GridViewRow gr in gvProducts.Rows)
            {
                CheckBox chkItem = (CheckBox)gr.FindControl("chkProductItem");
                if (chkItem == null)
                    continue;
                string strnotinbrnach = chkItem.Attributes["notinbrnach"];
                int itemid = (int)gvProducts.DataKeys[gr.RowIndex].Values[0];
                //EnableDisableItemForBranch(strnotinbrnach, itemid, true);
            }

            //SDSInventory.Select(DataSourceSelectArguments.Empty);
            //gvProducts.DataBind();
        }

        protected void lbtnUncheckAll_Click(object sender, EventArgs e)
        {
            LinkButton lbtntItem = (LinkButton)sender;
            if (lbtntItem == null)
                return;

            foreach (GridViewRow gr in gvProducts.Rows)
            {
                CheckBox chkItem = (CheckBox)gr.FindControl("chkProductItem");
                if (chkItem == null)
                    continue;
                string strnotinbrnach = chkItem.Attributes["notinbrnach"];
                int itemid = (int)gvProducts.DataKeys[gr.RowIndex].Values[0];
                //EnableDisableItemForBranch(strnotinbrnach, itemid, false);
            }

            //SDSInventory.Select(DataSourceSelectArguments.Empty);
            //gvProducts.DataBind();
        }

        protected void lbtnRemoveAll_Click(object sender, EventArgs e)
        {
            //int storegroupid = this.CurrentUser.StoreGroupId;
            //DataTable dt = new DataTable();
            //dt.Columns.Add("Id", typeof(int));
            //foreach (GridViewRow gr in gvProducts.Rows)
            //{
            //    DataRow dr = dt.NewRow();
            //    dr["Id"] = (int)gvProducts.DataKeys[gr.RowIndex].Values[0];
            //    dt.Rows.Add(dr);
            //}

            //List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            //parmeters.Add(new KeyValuePair<string, object>("StoreId", storegroupid));
            //parmeters.Add(new KeyValuePair<string, object>("IDs", dt));
            //DataService.ExecuteSP(sp: "DeleteInventoryMapping", parmeters: parmeters);

            //SDSInventory.Select(DataSourceSelectArguments.Empty);
            //gvProducts.DataBind();
        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager") && e.Command.Parameters.Contains("branchid"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {
            //MyBranches = (List<Store>)e.ReturnValue;
            if (selBranches.Items.Count > 1)
            {
                //ltrBranchName.Visible = selBranches.Items.Count == 2;
                //ltrBranchName.Text = selBranches.Items[1].Text;
                plcSelectBranchModel.Visible = selBranches.Items.Count > 2;

            }
            //if( MyBranches.Count > 1)
            //{

            //    plcSelectBranchModel.Visible = true;
            //    btnStockPublishItems.Attributes.Add("data-toggle", "modal");
            //    btnStockPublishItems.Attributes.Add("target", "#modal-select-branch");
            //    btnStockPublishItems.Visible = false;
            //    plcMultipleBranchButton.Visible = true;
            //}

        }


        private void ExportGridToExcel()
        {
            //Products with Product Barcode/ERP ID is downloaded
            int brid = -1;
            if (selBranches.Items.Count <= 1)
                selBranches.DataBind();
            if (Page.User.IsInRole("BranchManager"))
                brid = UserService.UserRoleBranchId;
            else if (selBranches.Items.Count == 2)
                brid = Convert.ToInt32(selBranches.Items[1].Value);
            else if (selBranches.Items.Count > 0 && !String.IsNullOrEmpty(selBranches.Text))
                brid = Convert.ToInt32(selBranches.Text);

            List<KeyValuePair<string, object>> dataparams = new List<KeyValuePair<string, object>>();
            dataparams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
            dataparams.Add(new KeyValuePair<string, object>("BranchId", brid));
            dataparams.Add(new KeyValuePair<string, object>("searchKey", txtSearchProduct.Text));
            dataparams.Add(new KeyValuePair<string, object>("brand", selBrand.Text));
            dataparams.Add(new KeyValuePair<string, object>("catid", selCat.Text));


            string strSql = @"SELECT i.stit_id, i.stit_sku, bi.item_count, bi.mrp, bi.selling_price, bi.discount_selling_price,
             (SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id=bi.stit_id LIMIT 1) AS storeCode, CASE WHEN bi.taxValueId IS NOT NULL AND bi.taxValueId > 0 THEN (SELECT hsn_code FROM finascop_hsn WHERE hsn_id = (SELECT hsnId FROM hsn_value WHERE id = bi.taxValueId)) ELSE stit_HSN_code END AS hsncode, CASE WHEN bi.taxValueId IS NOT NULL THEN (SELECT hsnGst FROM hsn_value WHERE id = bi.taxValueId) ELSE stit_GST END AS taxPercent FROM finascop_stock_branch_inventory bi
             INNER JOIN finascop_stock_itemmaster i ON i.stit_id=bi.stit_id AND bi.branch_id=@BranchId 
             INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id AND b.br_storegroup=@storeId ORDER BY item_count DESC,
  stit_SKU";
            DataTable dt = DataServiceMySql.GetDataTable(strSql, parmeters: dataparams);

            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Inventory");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);
            string[] strFieldLabels = Array.Empty<string>();

            var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, bg.store_group_grosmartMerchant FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup = bg.store_group_id WHERE bg.store_group_id  = @storeId", UserService.GetAPIConnectionString(), dataparams);
            int grosmartStore = 0;
            string exportbranchName = selBranches.SelectedItem.Text;
            if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
            {
                DataRow da = dtStoreGroup.Rows[0];
                string grosmart = da["store_group_grosmartMerchant"].ToString();
                grosmartStore = Convert.ToInt32(grosmart);

                if (grosmartStore == 1)
                {
                    strFieldLabels = "stit_id,Product ID|stit_sku,Name|item_count,Stock|mrp,MRP|selling_price,Selling Price|discount_selling_price,Discount Selling Price|storeCode,Product Barcode/ERP ID|hsncode,Tax Code|taxPercent,Tax %".Split('|');
                }
                else
                {
                     strFieldLabels = "stit_id,Product ID|stit_sku,Name|item_count,Stock|mrp,MRP|selling_price,Selling Price|storeCode,Product Barcode/ERP ID|hsncode,Tax Code|taxPercent,Tax %".Split('|');
                }
            }

            string priceLabel = ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "SKU MRP" : "SKU RRP";

            // Find the index of "Product Barcode/ERP ID"
            int barcodeIndex = Array.FindIndex(strFieldLabels, label => label.Contains("Product Barcode/ERP ID"));

            // Insert the priceLabel after the barcodeIndex
            Array.Resize(ref strFieldLabels, strFieldLabels.Length + 1);
            Array.Copy(strFieldLabels, barcodeIndex + 1, strFieldLabels, barcodeIndex + 2, strFieldLabels.Length - barcodeIndex - 2);
            strFieldLabels[barcodeIndex + 1] = "mrp," + priceLabel;

            foreach (string dc in strFieldLabels)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.Split(',')[1]));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                for (int j = 0; j < strFieldLabels.Length; j++)
                {
                    ICell cell = row.CreateCell(j);
                    string strField = strFieldLabels[j].Split(',')[0];
                    cell.SetCellValue(cH.CreateRichTextString(dr[strField].ToString()));
                }
            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", $"attachment;filename={exportbranchName}_inventory.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }

        protected void lbtnDownloadExcel_Click(object sender, EventArgs e)
        {
            ExportGridToExcel();
        }
        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            gvProducts.PageIndex = 0;
            LinkButton btn = (LinkButton)sender;
            if (btn != null && !String.IsNullOrEmpty(btn.Attributes["typeid"]))
            {
                int btypeid = Convert.ToInt32(btn.Attributes["typeid"]);
                FilterType = btypeid;
                hidFilterType.Value = btypeid.ToString();
                //ltrTitle.Text = btn.Text + " Orders";
            }
        }
        private int FilterType
        {
            get
            {
                if (ViewState["ORDFILTERTYPE"] == null)
                    return 0;
                else
                    return (int)ViewState["ORDFILTERTYPE"];
            }
            set
            {
                ViewState["ORDFILTERTYPE"] = value;
            }
        }


        private void ShowSuccess(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            //    cs.RegisterClientScriptBlock(cstype, csname1, @"<script type='text/javascript'>$('#modaldemo4').on('hidden.bs.modal', function (e) {
            //      window.location.href='/bankaccount';
            //});</script>");
        }


        private void ShowFailure(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo5').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

        protected void btnSetErpID_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(hidERP_stitid.Value))
            {
                // showModal('Set ERP Failed', 'Invalid product id', false);
                // show error;
                return;
            }

            int storegroupid = this.CurrentUser.APIStoreId;
            string brId = "";
            if (selBranches.Items.Count == 2)
            {
                brId = selBranches.Items[1].Value;
            }
            else
            {
                brId = selBranches.Text;
            }

            List<KeyValuePair<String, Object>> erpParams = new List<KeyValuePair<string, object>>();
            erpParams.Add(new KeyValuePair<string, object>("itemId", hidERP_stitid.Value));
            erpParams.Add(new KeyValuePair<string, object>("branch", brId));
            erpParams.Add(new KeyValuePair<string, object>("storeGroup", this.CurrentUser.APIStoreId));
            erpParams.Add(new KeyValuePair<string, object>("code", txtCode.Text));
            bool codeExists = false;
            int companyId = 0;
            if (Convert.ToInt32(selERPType.SelectedItem.Value) == 0)
            {
                companyId = 0;
            }
            else
            {
                companyId = 1;
            }
            //DataTable dtCodeExists = DataServiceMySql.GetDataTable("select * from finascop_stock_itemmaster_product_codes where fsipc_stit_id=@itemId and fsipc_store=@branch and fsipc_storeGroup=@storeGroup", UserService.GetAPIConnectionString(), erpParams);
            DataTable dtCodeExists = DataServiceMySql.GetDataTable("SELECT * FROM finascop_stock_itemmaster_product_codes WHERE fsipc_storeGroup = @storeGroup AND ((fsipc_code = @code AND fsipc_store=@branch) OR (fsipc_stit_id=@itemId AND fsipc_store=@branch))", UserService.GetAPIConnectionString(), erpParams);
            if (dtCodeExists != null && dtCodeExists.Rows.Count > 0)
            {
                if (dtCodeExists.AsEnumerable().Any(r => r["fsipc_code"].ToString() == txtCode.Text && r["fsipc_store"].ToString() == brId.ToString()))
                {
                    Common.ShowToastifyMessage(this.Page, "Duplicate code. The code is already used for another product in same store.", "danger");
                    return;
                }
                else if (dtCodeExists.AsEnumerable().Any(r => r["fsipc_stit_id"].ToString() == hidERP_stitid.Value && r["fsipc_store"].ToString() == brId.ToString()))
                {
                    codeExists = true;
                }
            }

            erpParams.Add(new KeyValuePair<string, object>("codeType", selERPType.SelectedItem.Text));
            erpParams.Add(new KeyValuePair<string, object>("company", companyId));
            erpParams.Add(new KeyValuePair<string, object>("individual", 0));
            string strSql = $"INSERT INTO finascop_stock_itemmaster_product_codes(fsipc_stit_id, fsipc_code, fsipc_codeType, fsipc_isCompany, fsipc_storeGroup, " +
                $"fsipc_store, fsipc_isIndividual) " +
                $"VALUES(@itemId, @code, @codeType, @company, @storeGroup, @branch, @individual)";
            if (codeExists)
                strSql = "UPDATE finascop_stock_itemmaster_product_codes SET fsipc_code=@code, fsipc_codeType=@codeType, fsipc_isCompany=@company  where fsipc_stit_id=@itemId and fsipc_store=@branch";
            try
            {
                int result = DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), erpParams);
                SDSInventory.Select(DataSourceSelectArguments.Empty);
                gvProducts.DataBind();
                selERPType.DataBind();
                Common.ShowToastifyMessage(Page, "Executed successfully");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");

            }
        }


        protected void btnReturnDays_Click(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            string brId = "";
            if (selBranches.Items.Count == 2)
            {
                brId = selBranches.Items[1].Value;
            }
            else
            {
                brId = selBranches.Text;
            }

            List<KeyValuePair<String, Object>> erpParams = new List<KeyValuePair<string, object>>();
            erpParams.Add(new KeyValuePair<string, object>("itemId", hidERP_stitid.Value));
            erpParams.Add(new KeyValuePair<string, object>("branch", brId));
            erpParams.Add(new KeyValuePair<string, object>("storeGroup", this.CurrentUser.APIStoreId));
            erpParams.Add(new KeyValuePair<string, object>("returnTime", txtReturnDays.Text));
            erpParams.Add(new KeyValuePair<string, object>("spotReturn", (chkSpotReturn.Checked ? 1 : 0)));


            DataTable dtReturnDays = DataServiceMySql.GetDataTable("SELECT stit_id, hasSpotReturn, returnTime FROM finascop_stock_branch_inventory WHERE stit_id = @itemId AND branch_id = @branch", UserService.GetAPIConnectionString(), erpParams);

            //DataRow dr = dtReturnDays.Rows[0];
            try
            {
                if (dtReturnDays.Rows.Count == 0)
                {
                    string insertQry = $"INSERT INTO finascop_stock_branch_inventory(stit_id, branch_id, hasSpotReturn, returnTime) " +
                    $"VALUES(@itemId, @branch, @spotReturn, @returnTime)";
                    DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), erpParams);
                    Common.ShowToastifyMessage(Page, "Data created successfully");
                    gvProducts.DataBind();
                }
                else
                {
                    string updateSql = "UPDATE finascop_stock_branch_inventory SET hasSpotReturn=@spotReturn, returnTime=@returnTime where stit_id = @itemId AND branch_id = @branch";
                    DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), erpParams);
                    Common.ShowToastifyMessage(Page, "Data updated successfully");
                    gvProducts.DataBind();
                }
                //gvProducts.DataBind();
            }

            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");

            }

        }

        protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chbtn = (CheckBox)sender;
            //int storegroupid = this.CurrentUser.APIStoreId;
            //var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            int brId = 0;
            if (selBranches.Items.Count == 2)
            {
                brId = Convert.ToInt32(selBranches.Items[1].Value);
            }
            else
            {
                brId = Convert.ToInt32(selBranches.Text);
            }

            if (chbtn != null && brId > 0)
            {
                int stitid = Convert.ToInt32(chbtn.Attributes["stitid"]);
                double mrp = Convert.ToDouble(chbtn.Attributes["mrp"]);
                int onlineStaus = (chbtn.Checked ? 1 : 0);
                List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
                sqlparams.Add(new KeyValuePair<string, object>("stitid", stitid));
                sqlparams.Add(new KeyValuePair<string, object>("brid", brId));
                sqlparams.Add(new KeyValuePair<string, object>("mrp", mrp));
                sqlparams.Add(new KeyValuePair<string, object>("onlineStaus", onlineStaus));
                sqlparams.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));

                //string strSql = "UPDATE finascop_stock_branch_inventory SET isAvailable=@onlineStaus, item_count = 0 WHERE stit_id = @stitid and branch_id=@brid and branch_id in(SELECT br_ID FROM `finascop_branch` WHERE br_storeGroup=@storegroup)";
                //DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), sqlparams);
                string strUpdateSql = $"insert into finascop_stock_branch_inventory(stit_id, branch_id, selling_price, mrp, item_count, fpod_customerRateHmDel,fpod_customerRateCouDel, fpod_customerRatePikup, fpod_leastSKUmrp) " +
                $"values(@stitid, @brid, 0, @mrp, 0, 0, 0, 0, 0) " +
                $"ON DUPLICATE KEY UPDATE isAvailable=@onlineStaus, item_count = 0";
                DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), sqlparams);
            }

            gvProducts.DataBind();
        }

        protected void chkOndemand_CheckedChanged(object sender, EventArgs e)
        {

            CheckBox chbtn = (CheckBox)sender;
            decimal mrp = Convert.ToDecimal(chbtn.Attributes["mrp"]);
            decimal sellingprice = Convert.ToDecimal(chbtn.Attributes["sellingprice"]);

            if(mrp == 0 || sellingprice == 0)
            {
                string label = ConfigurationManager.AppSettings["CountryCode"] == "IN" ? "MRP" : "RRP";
                Common.ShowCustomAlert(this.Page, "Failure!", $"Please add {label} and Selling price to enable Ondemand for this product.", false, "/Tenant/StockPrice");
                return;
            }

            if (chbtn != null && !String.IsNullOrEmpty(chbtn.Attributes["brid"]))
            {
                int brid = Convert.ToInt32(chbtn.Attributes["brid"]);
                int stitid = Convert.ToInt32(chbtn.Attributes["stitid"]);
                int onDemandStaus = (chbtn.Checked ? 1 : 0);
                List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
                sqlparams.Add(new KeyValuePair<string, object>("stitid", stitid));
                sqlparams.Add(new KeyValuePair<string, object>("brid", brid));
                sqlparams.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
                sqlparams.Add(new KeyValuePair<string, object>("onDemand", onDemandStaus));

                if (onDemandStaus == 1)
                {
                    string strSql = "UPDATE finascop_stock_branch_inventory SET isOnDemand=@onDemand, item_count = 1000 WHERE stit_id = @stitid and branch_id=@brid and branch_id in(SELECT br_ID FROM `finascop_branch` WHERE br_storeGroup=@storegroup)";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), sqlparams);
                }
                else
                {
                    string strSql = "UPDATE finascop_stock_branch_inventory SET isOnDemand=@onDemand, item_count = 0 WHERE stit_id = @stitid and branch_id=@brid and branch_id in(SELECT br_ID FROM `finascop_branch` WHERE br_storeGroup=@storegroup)";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), sqlparams);
                }
            }

            gvProducts.DataBind();
        }

        protected void gvProducts_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            int index = 0;
            GridViewRow row;
            GridView grid = sender as GridView;

            switch (e.CommandName)
            {
                case "Save":
                    index = Convert.ToInt32(e.CommandArgument);
                    row = grid.Rows[index];

                    //use row to find the selected controls you need for edit, update, delete here
                    // e.g. HiddenField value = row.FindControl("hiddenVal") as HiddenField;
                    HiddenField stitId = row.FindControl("hidStitID") as HiddenField;

                    SaveRowChanges(row);
                    break;
            }
        }

        protected void selBrand_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvProducts.PageIndex = 0;
        }

        protected void selCat_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvProducts.PageIndex = 0;
        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            gvProducts.PageIndex = 0;
        }

        //protected void lbtnBulkImport_Click(object sender, EventArgs e)
        //{
        //    Response.Redirect("~/Tenant/BulkImport.aspx");
        //}
        private string GetLabelPrice()
        {
            return ConfigurationManager.AppSettings.Get("CountryCode") != "IN" ? "RRP" : "MRP";
        }

        protected void btnHSNVerify_Click(object sender, EventArgs e)
        {
            try
            {
                int storegroupid = this.CurrentUser.APIStoreId;
                string itemId = hidERP_stitid.Value;
                string hsn = string.IsNullOrWhiteSpace(txtHSNCode.Text) ? "0" : txtHSNCode.Text.Trim();
                string gst = string.IsNullOrWhiteSpace(txtTax.Text) ? "0" : txtTax.Text.Trim();
                string cess = string.IsNullOrWhiteSpace(txtCess.Text) ? "0" : txtCess.Text.Trim();
                int brId = 0;
                if (selBranches.Items.Count == 2)
                {
                    brId = Convert.ToInt32(selBranches.Items[1].Value);
                }
                else
                {
                    brId = Convert.ToInt32(selBranches.Text);
                }
                

                List<string> branchIdParams = new List<string>();
                List<KeyValuePair<string, object>> updateParams = new List<KeyValuePair<string, object>>
                {
                   new KeyValuePair<string, object>("hsn", hsn),
                        new KeyValuePair<string, object>("gst", gst),
                        new KeyValuePair<string, object>("cess", cess),
                    new KeyValuePair<string, object>("itemId", itemId),
                    new KeyValuePair<string, object>("brId", brId)
                };

                int index = 0;
                string inClause = string.Join(",", branchIdParams);
                string updateSql = $@"UPDATE finascop_stock_branch_inventory SET hsnCode = @hsn, taxValue= @gst, cessValue = @cess WHERE stit_id=@itemId AND branch_id=@brId";
                DataServiceMySql.ExecuteSql(updateSql, UserService.GetAPIConnectionString(), updateParams);
                Common.ShowToastifyMessage(Page, "HSN Verified Successfully!!");
                txtHSNCode.Text = "";
                txtTax.Text = "";
                txtCess.Text = "";
                txtCess.Text = string.Empty;
                txtTax.Text = string.Empty;
                hdnModalOpen.Value = "0";
                int currentPage = gvProducts.PageIndex;
                gvProducts.PageIndex = currentPage;
                gvProducts.DataBind();
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
            }
        }


        //protected void selHSN_SelectedIndexChanged(object sender, EventArgs e)
        //{
        //    hdnModalOpen.Value = "1";
        //    selType.Items.Clear();
        //    txtTax.Text = "";
        //    txtCess.Text = "";

        //    string hsnId = selHSN.SelectedValue;
        //    if (string.IsNullOrEmpty(hsnId)) return;

        //    var param = new List<KeyValuePair<string, object>>
        //    {
        //        new KeyValuePair<string, object>("hsnId", hsnId)
        //    };

        //    DataTable dt = DataServiceMySql.GetDataTable("SELECT id, hsnGst, hsnCess FROM hsn_value WHERE hsnId = @hsnId ORDER BY id", Service.UserService.GetAPIConnectionString(), param);

        //    if (dt == null || dt.Rows.Count == 0)
        //    {
        //        selType.Visible = false;
        //        txtTax.Visible = false;
        //        return;
        //    }

        //    if (dt.Rows.Count == 1)
        //    {
        //        string gst = dt.Rows[0]["hsnGst"].ToString();
        //        string cess = dt.Rows[0]["hsnCess"].ToString();
        //        string id = dt.Rows[0]["id"].ToString();

        //        selType.Items.Add(new ListItem(gst, id));
        //        selType.SelectedIndex = 0;

        //        txtCess.Text = cess;
        //        txtTax.Text = gst;

        //        selType.Visible = false;
        //        txtTax.Visible = true;
        //        txtTax.Enabled = false;
        //    }
        //    else
        //    {
        //        selType.DataSource = dt;
        //        selType.DataTextField = "hsnGst";
        //        selType.DataValueField = "id";
        //        selType.DataBind();

        //        selType.Visible = true;
        //        txtTax.Visible = false;
        //    }
        //}

        //protected void selType_SelectedIndexChanged(object sender, EventArgs e)
        //{
        //    hdnModalOpen.Value = "1";
        //    txtCess.Text = "";

        //    string selectedId = selType.SelectedValue;
        //    if (string.IsNullOrEmpty(selectedId)) return;

        //    var param = new List<KeyValuePair<string, object>>
        //    {
        //        new KeyValuePair<string, object>("id", selectedId)
        //    };

        //    DataTable dt = DataServiceMySql.GetDataTable("SELECT hsnCess FROM hsn_value WHERE id = @id", Service.UserService.GetAPIConnectionString(), param);

        //    if (dt != null && dt.Rows.Count > 0)
        //    {
        //        txtCess.Text = dt.Rows[0]["hsnCess"].ToString();
        //    }
        //}

        //protected void selType_DataBound(object sender, EventArgs e)
        //{
        //    selType.Items.Insert(0, new ListItem("Select Tax", ""));
        //}

        protected string GetVerifyText(object taxValueId)
        {
            if (taxValueId != null && taxValueId != DBNull.Value && Convert.ToInt32(taxValueId) > 0)
                return "VERIFIED";
            return "VERIFY";
        }

        protected string GetVerifyClass(object taxValueId)
        {
            if (taxValueId != null && taxValueId != DBNull.Value && Convert.ToInt32(taxValueId) > 0)
                return "hcn-verify-btn bg-success disabled";
            return "hcn-verify-btn bg-danger";
        }
    }
}