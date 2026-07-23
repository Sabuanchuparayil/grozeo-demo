using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Margin: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //SDSStore.DataSourceMode = SqlDataSourceMode.DataSet;
            var storeInfo = (DataView)SDSStore.Select(DataSourceSelectArguments.Empty);
            if(storeInfo != null && storeInfo.Table.Rows.Count >0)
            {
                hidStoreMargine.Value= storeInfo.Table.Rows[0]["MinMargin"].ToString();
                lblStoreMargine.Text = hidStoreMargine.Value;
            }
        }

        protected void btnProdSearch_Click(object sender, EventArgs e)
        {
            //lstProducts.DataSource= Service.Common.GetProducts(-1, txtProduct.Text);
            //lstProducts.DataBind();
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            
        }
        public string DefaultMargine {
            get
            {
                return lblStoreMargine.Text;
            }
        }
        protected void btnBrandSearch_Click(object sender, EventArgs e)
        {
            //var data=Service.Common.GetBrands(txtBrand.Text);
            //ltrBrandsCount.Text = String.Format(" ({0})", data.Rows.Count);
            //gvBrands.DataSource = data;
            //gvBrands.DataBind();
        }

        protected void lnkBrandItem_Click(object sender, EventArgs e)
        {
            LinkButton button = (LinkButton)sender;
            string brandCode = button.Attributes["brandCode"];
            ltrSelBrand.Text = button.Text;
            lstProducts.DataSource = Service.Common.GetProducts(Convert.ToInt32(Request.QueryString["storeid"]), Convert.ToDouble(hidStoreMargine.Value), Convert.ToInt32(brandCode));
            lstProducts.DataBind();
        }
    }
}