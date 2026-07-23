using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class API_connector: Base.BasePartnerPage
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
        public string GSTLabel
        {
            get
            {
                return (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT");
            }
        }

        public List<Store> _myStores = null;
        public DataTable dtMystores = null;

        public DataTable TblMyStores
        {
            get
            {
                if (dtMystores == null)
                {
                    //var dv = (DataView)SDSBranches.Select(DataSourceSelectArguments.Empty); //DataService.GetDataTable($"SELECT * FROM StoreBranch WHERE StoreId={UserService.CachedDefaultUser.StoreGroupId}");
                    //dtMystores = dv.ToTable();
                }
                return dtMystores;
            }
        }
        //public List<Store> MyStores {
        //    get
        //    {
        //        if(_myStores == null)
        //        {
        //            _myStores = new List<Store>();
        //            //var dv = (DataView)SDSBranches.Select(DataSourceSelectArguments.Empty); //DataService.GetDataTable($"SELECT * FROM StoreBranch WHERE StoreId={UserService.CachedDefaultUser.StoreGroupId}");
        //            DataTable dt = TblMyStores;
        //            if(dt != null && dt.Rows.Count > 0)
        //            {
        //                foreach(DataRow dr in dt.Rows)
        //                    _myStores.Add(new Store() { DBBranchid = (int)dr["Id"], BranchId = (int)dr["APIBranchId"] });
        //            }
        //        }
        //        return _myStores;
        //    }
        //}
        private int EditStoreId
        {
            get
            {
                return (int)ViewState["EDITBRID"];
            }
            set
            {
                ViewState["EDITBRID"] = value;
            }
        }
        private int EditAPIStoreId
        {
            get
            {
                return (int)ViewState["EDITAPIBRID"];
            }
            set
            {
                ViewState["EDITAPIBRID"] = value;
            }
        }
        private string CurViewType
        {
            get
            {
                return (string)ViewState["CURVIEWTYPE"];
            }
            set
            {
                ViewState["CURVIEWTYPE"] = value;
            }
        }

        
        public string GSTIN(int storeid)
        {
            DataTable dt = TblMyStores;
            if (dt != null && dt.Rows.Count > 0)
            {
                var rows = dt.Select("Id= " + storeid);
                if (rows != null && rows.Count() > 0)
                    return rows[0]["gstin"].ToString();
            }
            return "Empty";
        }
        public string BankAccount(int storeid)
        {
            DataTable dt = TblMyStores;
            if (dt != null && dt.Rows.Count > 0)
            {
                var rows = dt.Select("Id= " + storeid);
                if (rows != null && rows.Count() > 0)
                    return String.Format("{0} {1}", rows[0]["BankName"], rows[0]["Branch"]);
            }
            return "Empty";
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                EditStoreId = -1;
                EditAPIStoreId = -1;
            }
            if (ConfigurationManager.AppSettings.Get("VATType") == "2")
                selGST.Attributes.Add("required", "required");


        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            
        }

        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.StoreGroupId;
            e.InputParameters["apistoregroupid"] = this.CurrentUser.APIStoreId;
           
        }

        protected void Unnamed_RowDataBound(object sender, GridViewRowEventArgs e)
            {

            }

            protected void rptBranches_ItemDataBound(object sender, RepeaterItemEventArgs e)
            {
                if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
                {
                    RadioButton rdDefaultBrnach = (RadioButton)e.Item.FindControl("rdDefaultBrnach1");
                    Repeater rptTiming = (Repeater)e.Item.FindControl("rptTiming");
                    if (rptTiming != null)
                    {
                        rptTiming.DataSource = (StoreTime[])DataBinder.Eval(e.Item.DataItem, "OnOffTime");
                        rptTiming.DataBind();

                        Literal ltrNoTime = (Literal)e.Item.FindControl("ltrNoTiming");
                        if (ltrNoTime != null)
                            ltrNoTime.Visible = (rptTiming.Items.Count <= 0);
                    }
                    System.Web.UI.HtmlControls.HtmlGenericControl lblDefaultBrnach = (System.Web.UI.HtmlControls.HtmlGenericControl)e.Item.FindControl("lblDefaultBrnach1");
                    if (lblDefaultBrnach != null && rdDefaultBrnach != null)
                    {
                        lblDefaultBrnach.Attributes.Add("for", rdDefaultBrnach.ClientID);

                    }
                }
            }

        protected void SDSStore_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("@storeId"))
                e.Command.Parameters["@storeId"].Value = this.CurrentUser.StoreGroupId;

        }

        protected void selState_DataBound(object sender, EventArgs e)
        {
            if (selState.Items.Count > 0)
            {
                string strKey = selState.Attributes["DefaultState"];
                if (!String.IsNullOrEmpty(strKey) && selState.Items.FindByText(strKey) != null)
                    selState.Text = (selState.Items.FindByText(strKey).Value);
            }
        }
        protected void selDistrict_DataBound(object sender, EventArgs e)
        {
            if (selDistrict.Items.Count > 0)
            {
                string strKey = selDistrict.Attributes["DefaultDistrict"];
                if (!String.IsNullOrEmpty(strKey) && selDistrict.Items.FindByText(strKey) != null)
                    selDistrict.Text = (selDistrict.Items.FindByText(strKey).Value);
            }
            selDistrict.Items.Insert(0, new ListItem("Select District", ""));
            if (selDistrict.Items.Count > 1 && !String.IsNullOrEmpty(hidDistrict.Value) && selDistrict.Text != hidDistrict.Value && selDistrict.Items.FindByText(hidDistrict.Value) != null)
                selDistrict.SelectedValue = selDistrict.Items.FindByText(hidDistrict.Value).Value; //selState.Items.FindByText(strState).Value;
        }

        
    }
}
