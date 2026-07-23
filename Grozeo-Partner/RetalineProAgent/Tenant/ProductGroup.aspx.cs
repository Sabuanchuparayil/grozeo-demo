using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class ProductGroup : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void SDSBrands_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 
        }

        protected void SDSGroups_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 

        }

        protected void ProductGroupAdd_Click(object sender, EventArgs e)
        {

            int groupid = 0; string strResult = "Group added successfully";
            if(!String.IsNullOrEmpty(hidGpId.Value))
                try { groupid = Convert.ToInt32(hidGpId.Value); } catch { groupid = 0; }

            List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>() {
                        new KeyValuePair<string, object>("name", txtGroupName.Text),
                        new KeyValuePair<string, object>("user", this.CurrentUser.Id), new KeyValuePair<string, object>("store", this.CurrentUser.APIStoreId),
                        new KeyValuePair<string, object>("brandId", selGroupBrand.Text)
              };
            
            string strSql = "insert into product_group(`Name`, `StoreGroupId`, `CreatedBy`, brandId) values(@name, @store, @user, @brandId);";
            if(groupid > 0) {
                strSql = "UPDATE product_group SET `Name` = @name WHERE Id = @id and StoreGroupId = @store";
                sqlParams.Add(new KeyValuePair<string, object>("id", groupid));
                strResult = "Group edited successfully";
            }
            int count = DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(),parmeters:  sqlParams);
            if(count <= 0)
            {

            }
            Common.ShowCustomAlert(this.Page, "Success", strResult, OnCloseRedirectUrl: "/tenant/productgroup?ref=" + String.Format("{0}2", Request.QueryString["ref"]));

        }


    }
}