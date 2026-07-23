using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class BusinessProfile: Base.BasePartnerPage
    {
       
        protected void Page_Load(object sender, EventArgs e)
        {
            User user = UserService.GetCustomerByEmail(User.Identity.Name);
            //ltrPhone.Text = user.Phone;
            ltrFullName.Text = user.FullName;
            ltrAddr.Text = user.Address;
            ltrCity.Text = user.City;
            ltrState.Text = user.State;
            ltrCountry.Text = user.Country;

            ltrRole.Text = (User.IsInRole("SuperAdmin") ? "Super Admin" :
                                                          (User.IsInRole("RetalineProAgent") ? "Admin" :
                                                          (User.IsInRole("StoreAdmin") ? "Store Admin" :
                                                          (User.IsInRole("StoreManager") ? "Store Manager" :
                                                          (User.IsInRole("Agent") ? "Sales" :
                                                          (User.IsInRole("BranchManager") ? "Branch Manager" : ""))))));
            //ltrRole2.Text = ltrRole.Text;

        }

        
    }
}