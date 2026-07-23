using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class mystores: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void chk_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chk = (CheckBox)sender;
            string strRowId = "", strFieldName = "";
            try
            {
                strRowId = chk.Attributes["rowId"];
                strFieldName = chk.Attributes["fieldname"];
                int chkChecked = chk.Checked ? 1 : 0;
                if (!String.IsNullOrEmpty(strRowId) && !String.IsNullOrEmpty(strFieldName))
                {
                    string strSql = $"UPDATE AppTenant SET {strFieldName} = {chkChecked} WHERE Id = {strRowId}";
                    SDSStores.UpdateCommand = strSql;
                    SDSStores.Update();
                    SDSStores.UpdateCommand = "";
                    SDSStores.Select(DataSourceSelectArguments.Empty);
                    lstStores.DataBind();
                }
            }
            catch { }
        }

        protected void AddEdit_Click(object sender, EventArgs e)
        {
            //Reset();
            //pnlAddForm.Visible = true;
            //pnlStoresList.Visible = false;
            //ltrAction.Text = "Add New Store";
            //btnAdd.Text = "Add Store";
            //lblCustomDomain.Text = "[title].site.com";
            //TenantId = -1;
            //StoreId = -1;
            //if (sender is LinkButton)
            //{
            //    LinkButton btn = (LinkButton)sender;
            //    if (!String.IsNullOrEmpty(btn.Attributes["rowId"]))
            //    {
            //        ltrAction.Text = "Edit Store";
            //        btnAdd.Text = "Update Store";
            //        TenantId = Convert.ToInt32(btn.Attributes["rowId"]);

            //        string strSql = SDSStores.SelectCommand + " where a.Id = " + btn.Attributes["rowId"];
            //        DataTable dt = DataService.GetDataTable(strSql, SDSStores.ConnectionString);
            //        if (dt.Rows.Count > 0)
            //        {
            //            txtStoreName.Text = dt.Rows[0]["Name"].ToString();
            //            lblCustomDomain.Text = $"{txtStoreName.Text.Replace(" ", "").ToLower()}.site.com";
            //            string strHosts = dt.Rows[0]["hosts"].ToString();
            //            foreach (string host in strHosts.Split(','))
            //            {
            //                if (host != lblCustomDomain.Text)
            //                    txtCustomDomain.Text += (String.IsNullOrEmpty(txtCustomDomain.Text) ? "" : ",") + host;
            //            }
            //            //txtCustomDomain.Text = dt.Rows[0]["hosts"].ToString();
            //            txtMinMargine.Text = dt.Rows[0]["MinMargin"].ToString();
            //            txtColor.Text = dt.Rows[0]["CustomColor"].ToString();
            //            selTheme.Text = dt.Rows[0]["Theme"].ToString();
            //            txtAPICode.Text = dt.Rows[0]["StoreId"].ToString();
            //            if (!String.IsNullOrEmpty(dt.Rows[0]["tStoreId"].ToString()))
            //                StoreId = Convert.ToInt32(dt.Rows[0]["tStoreId"]);// 
            //            txtConnectionString.Text = dt.Rows[0]["DBConnectionString"].ToString();
            //            txtSelectSql.Text = dt.Rows[0]["SelectSql"].ToString();

            //            string strBusinessType = dt.Rows[0]["BusinessType"].ToString();
            //            if (!String.IsNullOrEmpty(strBusinessType))
            //            {
            //                foreach (string strBType in strBusinessType.Split(','))
            //                {
            //                    foreach (ListItem item in chkBusinessTypes.Items)
            //                        if (item.Value == strBType.Trim())
            //                            item.Selected = true;
            //                }
            //            }

            //            if (!String.IsNullOrEmpty(dt.Rows[0]["Package"].ToString()))
            //                selPackage.Text = dt.Rows[0]["Package"].ToString();

            //            if (!String.IsNullOrEmpty(dt.Rows[0]["LogoImage"].ToString()))
            //            {
            //                imgLogo.ImageUrl = dt.Rows[0]["LogoImage"].ToString();
            //                imgLogo.Visible = true;
            //                chkDelImgLogo.Visible = true;
            //            }
            //            if (!String.IsNullOrEmpty(dt.Rows[0]["LogoSmall"].ToString()))
            //            {
            //                imgLogoWhite.ImageUrl = dt.Rows[0]["LogoSmall"].ToString();
            //                imgLogoWhite.Visible = true;
            //                chkDelImgLogoWhite.Visible = true;
            //            }

            //            try
            //            {
            //                chkStatus.Checked = (dt.Rows[0]["Status"].Equals(true));
            //                chkCheckout.Checked = (dt.Rows[0]["CanCheckout"].Equals(true));
            //                chkOnline.Checked = (dt.Rows[0]["OnlinePaymentEnabled"].Equals(true));
            //                chkPWA.Checked = (dt.Rows[0]["ShowPWA"].Equals(true));
            //            }
            //            catch
            //            {

            //            }
            //        }
            //    }
            //}

        }

        protected void SDSStores_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@usertype"].Value = (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") ? "0" : "1");
            e.Command.Parameters["@user"].Value = Page.User.Identity.Name;

        }
    }
}