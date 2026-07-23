using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Security;

namespace RetalineProAgent.Service
{
    public static class FormsAuthenticationService
    {

        #region Utilities

        /// <summary>
        /// Get authenticated customer
        /// </summary>
        /// <param name="ticket">Ticket</param>
        /// <returns>Customer</returns>
        public static User GetAuthenticatedCustomerFromTicket(FormsAuthenticationTicket ticket)
        {
            if (ticket == null)
                throw new ArgumentNullException("ticket");

            var user = JsonConvert.DeserializeObject<User>(ticket.UserData);//ticket.UserData;
            return user;
        }

        //public static void UpdateCookie(User selcustomer, bool checkAuthenticated = true, bool isPersistent = false)
        //{
        //    if (HttpContext.Current == null ||
        //        HttpContext.Current.Request == null ||
        //        (checkAuthenticated && !HttpContext.Current.Request.IsAuthenticated)
        //        )
        //    {
        //        return;
        //    }

        //    if (!(HttpContext.Current.User.Identity is FormsIdentity))
        //        return;
            
        //    if (checkAuthenticated)
        //    {
        //        try
        //        {
        //            var formsIdentity = (FormsIdentity)HttpContext.Current.User.Identity;
        //            isPersistent = formsIdentity.Ticket.IsPersistent;
        //        }
        //        catch { isPersistent = false; }
        //    }
        //    //var customer = GetAuthenticatedCustomerFromTicket(formsIdentity.Ticket);

            
        //    User customer = selcustomer;

        //    var now = DateTime.UtcNow.ToLocalTime();


        //    var ticket = new FormsAuthenticationTicket(
        //        1 /*version*/,
        //        customer.Email,
        //        now,
        //        now.Add(FormsAuthentication.Timeout),
        //        isPersistent,
        //        JsonConvert.SerializeObject(new User
        //        {
        //            FullName = customer.FullName,
        //            Phone = customer.Phone,
        //            StoreGroupId = customer.StoreGroupId,
        //            StoreGroupName = customer.StoreGroupName
        //        }),
        //        FormsAuthentication.FormsCookiePath);

        //    var encryptedTicket = FormsAuthentication.Encrypt(ticket);

        //    var cookie = new HttpCookie(FormsAuthentication.FormsCookieName, encryptedTicket);
        //    cookie.HttpOnly = true;
        //    if (ticket.IsPersistent)
        //    {
        //        cookie.Expires = ticket.Expiration;
        //    }
        //    cookie.Secure = FormsAuthentication.RequireSSL;
        //    cookie.Path = FormsAuthentication.FormsCookiePath;
        //    if (FormsAuthentication.CookieDomain != null)
        //    {
        //        cookie.Domain = FormsAuthentication.CookieDomain;
        //    }
        //    HttpCookie ck = HttpContext.Current.Response.Cookies.Get(FormsAuthentication.FormsCookieName);
            
        //    //HttpContext.Current.Response.Cookies.Remove(FormsAuthentication.FormsCookieName);
        //    //HttpContext.Current.Response.Cookies.Add(cookie);
        //    HttpContext.Current.Response.SetCookie(cookie);
        //    //_cachedCustomer = customer;
        //}

        #endregion

        #region Methods

        /// <summary>
        /// Sign in
        /// </summary>
        /// <param name="customer">Customer</param>
        /// <param name="createPersistentCookie">A value indicating whether to create a persistent cookie</param>
        public static void SignIn(User customer, bool createPersistentCookie)
        {
            var now = DateTime.UtcNow.ToLocalTime();

            var ticket = new FormsAuthenticationTicket(
                1 /*version*/,
                customer.Email,
                now,
                now.Add(FormsAuthentication.Timeout),
                createPersistentCookie,
                JsonConvert.SerializeObject(new User { FullName=customer.FullName, Phone = customer.Phone, 
                    StoreGroupId=customer.StoreGroupId, StoreGroupName=customer.StoreGroupName }),
                FormsAuthentication.FormsCookiePath);

            var encryptedTicket = FormsAuthentication.Encrypt(ticket);

            var cookie = new HttpCookie(FormsAuthentication.FormsCookieName, encryptedTicket);
            cookie.HttpOnly = true;
            if (ticket.IsPersistent)
            {
                cookie.Expires = ticket.Expiration;
            }
            cookie.Secure = FormsAuthentication.RequireSSL;
            cookie.Path = FormsAuthentication.FormsCookiePath;
            if (FormsAuthentication.CookieDomain != null)
            {
                cookie.Domain = FormsAuthentication.CookieDomain;
            }

            HttpContext.Current.Response.Cookies.Add(cookie);
            //_cachedCustomer = customer;
        }

        /// <summary>
        /// Sign out
        /// </summary>
        public static void SignOut()
        {
            //_cachedCustomer = null;
            FormsAuthentication.SignOut();
        }

        /// <summary>
        /// Get authenticated customer
        /// </summary>
        /// <returns>Customer</returns>
        public static User GetAuthenticatedCustomer()
        {

            if (HttpContext.Current == null ||
                HttpContext.Current.Request == null ||
                !HttpContext.Current.Request.IsAuthenticated ||
                !(HttpContext.Current.User.Identity is FormsIdentity))
            {
                return null;
            }

            var formsIdentity = (FormsIdentity)HttpContext.Current.User.Identity;
            var customer = GetAuthenticatedCustomerFromTicket(formsIdentity.Ticket);
            if (customer != null)
                return customer;
            return null; //_cachedCustomer;
        }

        #endregion

    }
}