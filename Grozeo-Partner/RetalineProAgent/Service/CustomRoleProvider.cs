using System;
using System.Collections.Generic;
using System.Web;
using System.Web.Security;
using System.Data;
using System.Configuration;
using System.Linq;
using RetalineProAgent.Core.Services;

namespace RetalineProAgent.Service
{
    /// <summary>
    /// CustomRoleProvider
    /// </summary>
    public class CustomRoleProvider : RoleProvider
    {
        public override bool IsUserInRole(string username, string roleName)
        {
            string[] roles = GetRolesForUser(username);
            return roles.Any(r => r == roleName);
            //foreach (var role in roles)
            //{
            //    if (role.Equals(roleName))
            //    {
            //        return true;
            //    }
            //}
            //return false;
        }

        /// <summary>
        /// Overwride method - GetRolesForUser
        /// </summary>
        /// <param name="username">User name</param>
        /// <returns>Roles array</returns>
        public override string[] GetRolesForUser(string username)
        {
            List<string> roles = new List<string>();
            List<KeyValuePair<string, object>> param = new List<KeyValuePair<string, object>>();
            param.Add(new KeyValuePair<string, object>("user", username));
            DataTable dt = DataService.GetDataTable("exec GetUserRoles @user", parmeters: param);
            foreach (DataRow dr in dt.Rows)
            {
                if (dr["RoleName"] != null && !String.IsNullOrEmpty(dr["RoleName"].ToString()))
                    roles.Add(dr["RoleName"].ToString());

            }

            //     if (ConfigurationManager.AppSettings.Get("AdminUser").ToLower().Equals(username.ToLower()) && !roles.Contains("admin"))
            //roles.Add("admin");
            //if (roles.Count < 1)
            //{
            //    string inactiveRole = ConfigurationManager.AppSettings.Get("InactiveRole");
            //    roles.Add(String.IsNullOrEmpty(inactiveRole) ? "inactive" : inactiveRole);
            //}
            return roles.ToArray();
        }

        public override void CreateRole(string roleName)
        {
            throw new System.NotImplementedException();
        }
        public override bool DeleteRole(string roleName, bool throwOnPopulatedRole)
        {
            throw new System.NotImplementedException();
        }
        public override bool RoleExists(string roleName)
        {
            var roles = GetAllRoles();
            return roles.Any(r=> r==roleName);
            //foreach (string role in roles)
            //{
            //    if (role.Equals(roleName))
            //    {
            //        return true;
            //    }
            //}
            //return false;
        }
        public override void AddUsersToRoles(string[] usernames, string[] roleNames)
        {
            throw new System.NotImplementedException();
        }
        public override void RemoveUsersFromRoles(string[] usernames, string[] roleNames)
        {
            throw new System.NotImplementedException();
        }
        public override string[] GetUsersInRole(string roleName)
        {
            throw new System.NotImplementedException();
        }
        public override string[] GetAllRoles()
        {
            List<string> roles = new List<string>();
            DataTable dt = DataService.GetDataTable("exec GetRoles");
            foreach (DataRow dr in dt.Rows)
                roles.Add(dr["RoleName"].ToString());

            return roles.ToArray();
        }
        public override string[] FindUsersInRole(string roleName, string usernameToMatch)
        {
            throw new System.NotImplementedException();
        }
        public override string ApplicationName { get; set; }

    }

}