using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ManageStore: Base.BasePartnerPage
    {
        private int? TenantId
        {
            get
            {
                return (int?)ViewState["TENANTID"];
            }
            set
            {
                ViewState["TENANTID"] = value;
            }
        }
        private int? StoreId
        {
            get
            {
                return (int?)ViewState["STOREID"];
            }
            set
            {
                ViewState["STOREID"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {

            if (!IsPostBack)
            {
                lblCustomDomain.Text = System.Configuration.ConfigurationManager.AppSettings.Get("newsitedomain");
                txtStoreName.Attributes.Remove("onchange");
                txtStoreName.Attributes.Add("onchange", "document.getElementById('lblCustomDomain').innerHTML= this.value.toLowerCase().replace(/ /g,'')+'" + lblCustomDomain.Text.Replace("[title]", "") + "';");
            }

            lblMessage.Text = "";

            if (IsPostBack)
                return;

            //Reset();
            ltrAction.Text = "Add New Store";
            btnAdd.Text = "Add Store";
            lblCustomDomain.Text = System.Configuration.ConfigurationManager.AppSettings.Get("newsitedomain");
            TenantId = -1;
            StoreId = -1;

            string strEditId = Request.QueryString["sid"];
            if (!String.IsNullOrEmpty(strEditId))
            {
                Title = "Edit Store";
                ltrAction.Text = "Edit Store";
                btnAdd.Text = "Update Store";
                TenantId = Convert.ToInt32(strEditId);

                string strSql = SDSStores.SelectCommand + " where a.Id = " + strEditId;
                DataTable dt = DataService.GetDataTable(strSql, SDSStores.ConnectionString);
                if (dt.Rows.Count > 0)
                {
                    txtStoreName.Text = dt.Rows[0]["Name"].ToString();
                    lblCustomDomain.Text = $"{txtStoreName.Text.Replace(" ", "").ToLower()}.System.Configuration.ConfigurationManager.AppSettings.Get('newsitedomain')";
                    string strHosts = dt.Rows[0]["hosts"].ToString();
                    foreach (string host in strHosts.Split(','))
                    {
                        if (host != lblCustomDomain.Text)
                            txtCustomDomain.Text += (String.IsNullOrEmpty(txtCustomDomain.Text) ? "" : ",") + host;
                    }
                    //txtCustomDomain.Text = dt.Rows[0]["hosts"].ToString();
                    txtMinMargine.Text = dt.Rows[0]["MinMargin"].ToString();
                    txtColor.Text = dt.Rows[0]["CustomColor"].ToString();
                    selTheme.Text = dt.Rows[0]["Theme"].ToString();
                    txtAPICode.Text = dt.Rows[0]["StoreId"].ToString();
                    if (!String.IsNullOrEmpty(dt.Rows[0]["tStoreId"].ToString()))
                        StoreId = Convert.ToInt32(dt.Rows[0]["tStoreId"]);// 
                    txtConnectionString.Text = dt.Rows[0]["DBConnectionString"].ToString();
                    txtSelectSql.Text = dt.Rows[0]["SelectSql"].ToString();

                    string strBusinessType = dt.Rows[0]["BusinessType"].ToString();
                    if (!String.IsNullOrEmpty(strBusinessType))
                    {
                        foreach (string strBType in strBusinessType.Split(','))
                        {
                            foreach (ListItem item in chkBusinessTypes.Items)
                                if (item.Value == strBType.Trim())
                                    item.Selected = true;
                        }
                    }

                    if (!String.IsNullOrEmpty(dt.Rows[0]["Package"].ToString()))
                        selPackage.Text = dt.Rows[0]["Package"].ToString();

                    if (!String.IsNullOrEmpty(dt.Rows[0]["LogoImage"].ToString()))
                    {
                        imgLogo.ImageUrl = dt.Rows[0]["LogoImage"].ToString();
                        imgLogo.Visible = true;
                        chkDelImgLogo.Visible = true;
                    }
                    if (!String.IsNullOrEmpty(dt.Rows[0]["LogoSmall"].ToString()))
                    {
                        imgLogoWhite.ImageUrl = dt.Rows[0]["LogoSmall"].ToString();
                        imgLogoWhite.Visible = true;
                        chkDelImgLogoWhite.Visible = true;
                    }

                    try
                    {
                        chkStatus.Checked = (dt.Rows[0]["Status"].Equals(true));
                        chkCheckout.Checked = (dt.Rows[0]["CanCheckout"].Equals(true));
                        chkOnline.Checked = (dt.Rows[0]["OnlinePaymentEnabled"].Equals(true));
                        chkPWA.Checked = (dt.Rows[0]["ShowPWA"].Equals(true));
                    }
                    catch
                    {

                    }
                }
            }




        }


        protected async void btnAdd_Click(object sender, EventArgs e)
        {
            if (IsValid)
            {
                string strBusinessTypes = "";
                foreach (ListItem item in chkBusinessTypes.Items)
                    if (item.Selected)
                        strBusinessTypes += (String.IsNullOrWhiteSpace(strBusinessTypes) ? "" : ",") + item.Value;

                List<string> strHosts = new List<string>();
                strHosts.Add(lblCustomDomain.Text.Replace("[title]", txtStoreName.Text.Replace(" ", "").Trim().ToLower()));
                if (!String.IsNullOrEmpty(txtCustomDomain.Text))
                    foreach (string strCustDomain in txtCustomDomain.Text.Split(','))
                        strHosts.Add(strCustDomain.Trim());

                string strSqlCheck = String.Join("','", strHosts);
                if (!String.IsNullOrEmpty(strSqlCheck))
                {
                    strSqlCheck = "select HostAddress from host where HostAddress in ('" + strSqlCheck + "') " + (TenantId > 0 ? $"and TenantId <> {TenantId}" : "");
                    DataTable dtHosts = DataService.GetDataTable(strSqlCheck, SDSStores.ConnectionString);
                    if (dtHosts != null && dtHosts.Rows.Count > 0)
                    {
                        lblMessage.Text = "domain is already assigned to another tenant: ";
                        foreach (DataRow dr in dtHosts.Rows)
                            lblMessage.Text += " " + dr["HostAddress"].ToString();
                        return;
                    }
                }

                string insertDoamin = " DELETE Host WHERE TenantId=@TenantId; ";
                List<KeyValuePair<String, Object>> headerParams = new List<KeyValuePair<string, object>>();
                int hcount = 0;
                foreach (string strHost in strHosts)
                {
                    if (!String.IsNullOrEmpty(strHost.Trim()))
                    {
                        hcount++;
                        headerParams.Add(new KeyValuePair<string, object>($"host{hcount}", strHost.Trim().ToLower()));
                        insertDoamin += $" INSERT INTO Host(TenantId, HostAddress) VALUES(@TenantId, @host{hcount}); ";
                    }
                }
                string insertStore = $" DELETE FROM Store WHERE TenantId=@TenantId;" +
                    $" INSERT INTO Store(Name, GroupId, TenantId, MinMargin, Status, Package, BusinessType, DBConnectionString, SelectSql, CreatedBy)" +
                    $" VALUES(@Name, @StoreGroupId, @TenantId, @MinMargin, @Status, @Package, @BusinessType, @DBConnectionString, @SelectSql, @User); " +
                    $" SET @StrId=scope_identity(); {insertDoamin} ";

                string sqlUpdateStore = $" IF EXISTS(SELECT * FROM Store WHERE Id=@StrId) BEGIN UPDATE Store SET Name=@Name, " +
                    $"GroupId=@StoreGroupId, TenantId=@TenantId, MinMargin=@MinMargin, Status=@Status, Package=@Package, BusinessType=@BusinessType, " +
                    $"DBConnectionString=@DBConnectionString, SelectSql=@SelectSql, UpdatedBy=@User WHERE Id=@StrId AND TenantId=@TenantId; END " +
                    $" ELSE BEGIN {insertStore} END {insertDoamin} ";

                string sqlInsertTenant = $"IF NOT EXISTS(SELECT * FROM AppTenant WHERE Name like '{txtStoreName.Text}') " +
                    $"BEGIN INSERT INTO AppTenant(Name, Theme, APIUrl, CustomColor, CanCheckout, OnlinePaymentEnabled, StoreId, Status, ShowPWA, LogoImage, LogoSmall) " +
                    $"VALUES(@Name, @Theme, @APIUrl, @CustomColor, @CanCheckout, @OnlinePaymentEnabled, @StoreGroupId, @Status, @ShowPWA, @LogoImage, @LogoSmall); " +
                    $" SET @TenantId=scope_identity(); {insertStore} " +
                    $" SELECT @TenantId; END ELSE BEGIN SELECT -2; END";

                string sqlUpdateTenant = $"UPDATE AppTenant SET Name=@Name, Theme=@Theme, APIUrl=@APIUrl, CustomColor=@CustomColor, CanCheckout=@CanCheckout, OnlinePaymentEnabled=@OnlinePaymentEnabled, " +
                    $"StoreId=@StoreGroupId, Status=@Status, {(uploadLogo.HasFile || (chkDelImgLogo.Visible && chkDelImgLogo.Checked) ? "LogoImage=@LogoImage," : "")} " +
                    $" {(uploadLogoWhite.HasFile || (chkDelImgLogoWhite.Visible && chkDelImgLogoWhite.Checked) ? "LogoSmall=@LogoSmall," : "")} ShowPWA=@ShowPWA " +
                    $" WHERE Id=@TenantId; {sqlUpdateStore} ";


                List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();

                parmeters.Add(new KeyValuePair<string, object>("Name", txtStoreName.Text));
                parmeters.Add(new KeyValuePair<string, object>("Theme", selTheme.Text));
                parmeters.Add(new KeyValuePair<string, object>("APIUrl", System.Configuration.ConfigurationManager.AppSettings.Get("api.url")));
                parmeters.Add(new KeyValuePair<string, object>("CanCheckout", chkCheckout.Checked));
                parmeters.Add(new KeyValuePair<string, object>("OnlinePaymentEnabled", chkOnline.Checked));
                parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", txtAPICode.Text));
                parmeters.Add(new KeyValuePair<string, object>("Status", chkStatus.Checked));
                parmeters.Add(new KeyValuePair<string, object>("ShowPWA", chkPWA.Checked));
                string strLogo = Guid.NewGuid().ToString();
                if (uploadLogo.HasFile)
                {
                    string strExtention = System.IO.Path.GetExtension(uploadLogo.PostedFile.FileName);
                    string resultLogo = await Service.Common.CreateBlob(uploadLogo.PostedFile.InputStream, strLogo + $"_logo{strExtention}");
                    if (!string.IsNullOrEmpty(resultLogo))
                        parmeters.Add(new KeyValuePair<string, object>("LogoImage", resultLogo));
                }
                if (uploadLogoWhite.HasFile)
                {
                    string strExtention = System.IO.Path.GetExtension(uploadLogoWhite.PostedFile.FileName);
                    string resultLogo = await Service.Common.CreateBlob(uploadLogoWhite.PostedFile.InputStream, strLogo + $"_logo_white{strExtention}");
                    if (!string.IsNullOrEmpty(resultLogo))
                        parmeters.Add(new KeyValuePair<string, object>("LogoSmall", resultLogo));
                }

                if (!parmeters.Any(k => k.Key == "LogoImage"))
                    parmeters.Add(new KeyValuePair<string, object>("LogoImage", ""));
                if (!parmeters.Any(k => k.Key == "LogoSmall"))
                    parmeters.Add(new KeyValuePair<string, object>("LogoSmall", ""));

                parmeters.Add(new KeyValuePair<string, object>("CustomColor", txtColor.Text));
                //parmeters.Add(new KeyValuePair<string, object>("GroupId", txtAPICode.Text));
                parmeters.Add(new KeyValuePair<string, object>("TenantId", TenantId));
                parmeters.Add(new KeyValuePair<string, object>("StrId", StoreId));

                parmeters.Add(new KeyValuePair<string, object>("MinMargin", txtMinMargine.Text));
                //parmeters.Add(new KeyValuePair<string, object>("Status", chkStatus.Checked));
                parmeters.Add(new KeyValuePair<string, object>("Package", selPackage.Text));
                parmeters.Add(new KeyValuePair<string, object>("BusinessType", strBusinessTypes));
                parmeters.Add(new KeyValuePair<string, object>("DBConnectionString", txtConnectionString.Text));
                parmeters.Add(new KeyValuePair<string, object>("SelectSql", txtSelectSql.Text));
                parmeters.Add(new KeyValuePair<string, object>("User", User.Identity.Name));
                parmeters.AddRange(headerParams);

                if (TenantId > 0)
                {
                    string sql = sqlUpdateTenant;
                    int result = DataService.ExecuteSql(sql, SDSStores.ConnectionString, parmeters);
                    if (result < 1)
                        lblMessage.Text = "Failure.";
                    else
                        lblMessage.Text = "Updated successfully!!";

                    //int count = SDSStores.Insert();
                    //if (count > 0)
                    //    lblMessage.Text = "Store added successfully";
                    //else
                    //    lblMessage.Text = "Failed!! Store name already exists.";

                    //if (count > 0)
                    //    Reset();
                }
                else
                {
                    string sql = sqlInsertTenant;
                    object result = DataService.ExecuteScalar(sql, SDSStores.ConnectionString, parmeters);
                    if (result is int && Convert.ToInt32(result) == -2)
                        lblMessage.Text = "Error!! Store name already exists.";
                    else
                        lblMessage.Text = "Store added successfully!!";
                }

            }
        }

        private void Reset()
        {
            txtStoreName.Text = "";
            txtAPICode.Text = "";
            txtConnectionString.Text = "";
            txtMinMargine.Text = "";
            txtColor.Text = "";
            txtSelectSql.Text = "";
            chkStatus.Checked = false;
            lblMessage.Text = "";
            txtCustomDomain.Text = "";

            chkCheckout.Checked = false;
            chkOnline.Checked = false;
            chkPWA.Checked = false;

            imgLogo.Visible = false;
            imgLogoWhite.Visible = false;
            chkDelImgLogo.Visible = false;
            chkDelImgLogoWhite.Visible = false;

        }

        protected void btnReset_Click(object sender, EventArgs e)
        {
            Reset();
            //pnlAddForm.Visible = false;
            //pnlStoresList.Visible = true;
        }


    }
}