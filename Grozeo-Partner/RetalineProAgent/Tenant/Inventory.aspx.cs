using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;

namespace RetalineProAgent
{
    public partial class Inventory: Base.BasePartnerPage
    {
        public List<Core.BussinessModel.Inventory.ItemMaster> InventoryMapping
        {
            get
            {
                return (List<Core.BussinessModel.Inventory.ItemMaster>)ViewState["INVENTORYMAPPING"];
            }
            set
            {
                ViewState["INVENTORYMAPPING"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            string strStoreId = Request.QueryString["storeid"];
            if (String.IsNullOrEmpty(strStoreId))
            {
                ClientScript.RegisterClientScriptBlock(typeof(string),"InvalidStoreId",
                    @"<script language='javascript'>alert('Invalid store'); window.location.href='/store'</script>");
                return;
            }

            if (!IsPostBack)
            {
                APIService.ClearCachedData();
                var dt = DataService.GetDataTable($"SELECT * FROM InventoryMapping WHERE StoreId={strStoreId}");
                //InventoryMapping = dt.Rows.Cast<Core.BussinessModel.Inventory.ItemMaster>().ToList();


                InventoryMapping = (from row in dt.AsEnumerable()
                                 select new Core.BussinessModel.Inventory.ItemMaster()
                                 {
                                     Id = row.Field<int>("Id"), 
                                     ErpId = row.Field<string>("ErpId")
                                     //RC = row.Field<string>(SubjectsImportStructure.RC),
                                 }).ToList();

                //IQueryable<IImportRow> data = importdata as IQueryable<IImportRow>;

            }

            rpBrands.Visible = rbtnTypes.SelectedValue == "2";
            lstCategories.Visible = rbtnTypes.SelectedValue == "1";
        }

        protected void lbtnSubCat_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string pcatid = lbtn.Attributes["pcat"];
            string catid = lbtn.Attributes["catid"];
            string scatid = lbtn.Attributes["subcatid"];
            string catlevel = lbtn.Attributes["catlevel"];
            hidCatId.Value = scatid;
            hidCatlevel.Value = catlevel;
        }

        protected void lnkBrandItem_Click(object sender, EventArgs e)
        {
            LinkButton button = (LinkButton)sender;
            string brandCode = button.Attributes["brandCode"];
            ltrSelBrand.Text = button.Text;
            hidBrandId.Value = brandCode;
            ODSProducts.Select();
            //lstProducts.DataSource = Service.Common.GetProducts(Convert.ToInt32(Request.QueryString["storeid"]), Convert.ToDouble(hidStoreMargine.Value), Convert.ToInt32(brandCode));
            lstProducts.DataBind();
        }

        protected void chkProductHItem_CheckedChanged(object sender, EventArgs e)
        {

        }

        protected void chkProductHItem_CheckedChanged1(object sender, EventArgs e)
        {
            DataTable dt = new DataTable();
            dt.Columns.Add("Id", typeof(int));
            dt.Columns.Add("Barcode", typeof(string));
            dt.Columns.Add("StoreErpId", typeof(string));
            dt.Columns.Add("StoreId", typeof(string));
            CheckBox chkHeader = (CheckBox)sender;
            if (chkHeader == null)
                return;
            string strStoreId = Request.QueryString["storeid"];

            foreach (ListViewDataItem item in lstProducts.Items)
            {
                Repeater rptProducts = (Repeater)item.FindControl("rptProducts");
                foreach(RepeaterItem product in rptProducts.Items)
                {
                    Repeater rptItem = (Repeater)product.FindControl("rptItem");
                    foreach(RepeaterItem productitem in rptItem.Items)
                    {
                        CheckBox chkProductHItem = (CheckBox)productitem.FindControl("chkProductHItem");
                        if(chkProductHItem != null)
                        {
                            if (!InventoryMapping.Any(i => i.Id == Convert.ToInt32(chkProductHItem.Attributes["itemid"])))
                                InventoryMapping.Add(new Core.BussinessModel.Inventory.ItemMaster() { Id = Convert.ToInt32(chkProductHItem.Attributes["itemid"]) });
                            if (!chkHeader.Checked)
                                InventoryMapping.Remove( InventoryMapping.FirstOrDefault(i=> i.Id== Convert.ToInt32(chkProductHItem.Attributes["itemid"])) );

                            DataRow dr = dt.NewRow();
                            dr["Id"] = chkProductHItem.Attributes["itemid"];
                            dr["StoreId"] = strStoreId;
                            dt.Rows.Add(dr);
                        }
                    }
                }
            }

            if(dt.Rows.Count > 0)
            {

                if (!chkHeader.Checked)
                {
                    dt.Columns.Remove("StoreId"); dt.Columns.Remove("Barcode"); dt.Columns.Remove("StoreErpId");
                    List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
                    parmeters.Add(new KeyValuePair<string, object>("StoreId", strStoreId));
                    parmeters.Add(new KeyValuePair<string, object>("IDs", dt));
                    DataService.ExecuteSP(sp: "DeleteInventoryMapping", parmeters: parmeters);
                }
                else
                {
                    DataService.InventoryMapingBulkInsert(dt);
                }
                lstProducts.DataBind();
            }

        }

        protected void lbtnBrands_Click(object sender, EventArgs e)
        {
            rbtnTypes.SelectedIndex = 0;
        }

        protected void lbtnCategories_Click(object sender, EventArgs e)
        {
            rbtnTypes.SelectedIndex = 1;

        }
    }
}