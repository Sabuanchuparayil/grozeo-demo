using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Globalization;
using System.Threading;

namespace RetalineProAgent.Infrastructure
{
    public partial class PartnerContext
    {

        #region Constants
        private const string CONST_PARTNERSESSION = "Partner.PartnerSession";
        private const string CONST_PARTNERSESSIONCOOKIE = "Partner.PartnerSessionGUIDCookie";
        #endregion

        #region Fields
        private Service.User _currentPartner;
        private bool _isCurrentPartnerImpersonated;
        private Service.User _originalPartner;
        private bool? _isAdmin;
        private readonly HttpContext _context = HttpContext.Current;
        //private Language _workingLanguage;
        //private Currency _workingCurrency;
        private bool? _localizedEntityPropertiesEnabled;
        //private TaxDisplayTypeEnum? _taxDisplayType;
        #endregion

        #region Ctor
        /// <summary>
        /// Creates a new instance of the PartnerContext class
        /// </summary>
        private PartnerContext()
        {
        }
        #endregion

        #region Methods

        /// <summary>
        /// Save Partner session to data source
        /// </summary>
        /// <returns>Saved Partner ssion</returns>
        //private PartnerSession SaveSessionToDatabase()
        //{
        //    var sessionId = Guid.NewGuid();
        //    //while (IoC.Resolve<IPartnerService>().GetPartnerSessionByGuid(sessionId) != null)
        //    //    sessionId = Guid.NewGuid();
        //    var session = new PartnerSession();
        //    int partnerId = 0;
        //    if (this.User != null)
        //    {
        //        partnerId = this.User.Id;
        //    }
        //    session.PartnerSessionGuid = sessionId;
        //    session.PartnerId = partnerId;
        //    session.LastAccessed = DateTime.UtcNow;
        //    session.IsExpired = false;
        //    //session = IoC.Resolve<IPartnerService>().SavePartnerSession(session.PartnerSessionGuid, session.PartnerId, session.LastAccessed, session.IsExpired);
        //    return session;
        //}

        /// <summary>
        /// Gets Partner session
        /// </summary>
        /// <param name="createInDatabase">Create session in database if no one exists</param>
        /// <returns>Partner session</returns>
        public PartnerSession GetSession(bool createInDatabase)
        {
            return this.GetSession(createInDatabase, null);
        }

        /// <summary>
        /// Gets Partner session
        /// </summary>
        /// <param name="createInDatabase">Create session in database if no one exists</param>
        /// <param name="sessionId">Session identifier</param>
        /// <returns>Partner session</returns>
        public PartnerSession GetSession(bool createInDatabase, Guid? sessionId)
        {
            PartnerSession byId = null;
            object obj2 = Current[CONST_PARTNERSESSION];
            if (obj2 != null)
                byId = (PartnerSession)obj2;
            if ((byId == null) && (sessionId.HasValue))
            {
                //byId = IoC.Resolve<IPartnerService>().GetPartnerSessionByGuid(sessionId.Value);
                return byId;
            }
            //if (byId == null && createInDatabase)
            //{
            //    byId = SaveSessionToDatabase();
            //}
            string PartnerSessionCookieValue = string.Empty;
            if ((HttpContext.Current.Request.Cookies[CONST_PARTNERSESSIONCOOKIE] != null) && (HttpContext.Current.Request.Cookies[CONST_PARTNERSESSIONCOOKIE].Value != null))
                PartnerSessionCookieValue = HttpContext.Current.Request.Cookies[CONST_PARTNERSESSIONCOOKIE].Value;
            if ((byId) == null && (!string.IsNullOrEmpty(PartnerSessionCookieValue)))
            {
                //var dbPartnerSession = IoC.Resolve<IPartnerService>().GetPartnerSessionByGuid(new Guid(PartnerSessionCookieValue));
                //byId = dbPartnerSession;
            }
            Current[CONST_PARTNERSESSION] = byId;
            return byId;
        }

        /// <summary>
        /// Saves current session to client
        /// </summary>
        public void SessionSaveToClient()
        {
            if (HttpContext.Current != null && this.Session != null)
                SetCookie(HttpContext.Current.ApplicationInstance, CONST_PARTNERSESSIONCOOKIE, this.Session.PartnerSessionGuid.ToString());
        }

        /// <summary>
        /// Reset Partner session
        /// </summary>
        public void ResetSession()
        {
            if (HttpContext.Current != null)
                SetCookie(HttpContext.Current.ApplicationInstance, CONST_PARTNERSESSIONCOOKIE, string.Empty);
            this.Session = null;
            this.User = null;
            this["Partner.SessionReseted"] = true;
        }

        /// <summary>
        /// Sets cookie
        /// </summary>
        /// <param name="application">Application</param>
        /// <param name="key">Key</param>
        /// <param name="val">Value</param>
        private static void SetCookie(HttpApplication application, string key, string val)
        {
            HttpCookie cookie = new HttpCookie(key);
            cookie.Value = val;
            if (string.IsNullOrEmpty(val))
            {
                cookie.Expires = DateTime.Now.AddMonths(-1);
            }
            else
            {
                cookie.Expires = DateTime.Now.AddHours(128);
            }
            application.Response.Cookies.Remove(key);
            application.Response.Cookies.Add(cookie);
        }

        #endregion

        #region Properties

        /// <summary>
        /// Gets an instance of the PartnerContext, which can be used to retrieve information about current context.
        /// </summary>
        public static PartnerContext Current
        {
            get
            {
                if (HttpContext.Current == null)
                {
                    object data = Thread.GetData(Thread.GetNamedDataSlot("PartnerContext"));
                    if (data != null)
                    {
                        return (PartnerContext)data;
                    }
                    PartnerContext context = new PartnerContext();
                    Thread.SetData(Thread.GetNamedDataSlot("PartnerContext"), context);
                    return context;
                }
                if (HttpContext.Current.Items["PartnerContext"] == null)
                {
                    PartnerContext context = new PartnerContext();
                    HttpContext.Current.Items.Add("PartnerContext", context);
                    return context;
                }
                return (PartnerContext)HttpContext.Current.Items["PartnerContext"];
            }
        }

        /// <summary>
        /// Gets or sets a value indicating whether the context is running in admin-mode
        /// </summary>
        public bool IsAdmin
        {
            get
            {
                if (!_isAdmin.HasValue)
                {
                    _isAdmin = false; //CommonHelper.IsAdmin();
                }
                return _isAdmin.Value;
            }
            set
            {
                _isAdmin = value;
            }
        }

        /// <summary>
        /// Gets or sets an object item in the context by the specified key.
        /// </summary>
        /// <param name="key">The key of the value to get.</param>
        /// <returns>The value associated with the specified key.</returns>
        public object this[string key]
        {
            get
            {
                if (this._context == null)
                {
                    return null;
                }

                if (this._context.Items[key] != null)
                {
                    return this._context.Items[key];
                }
                return null;
            }
            set
            {
                if (this._context != null)
                {
                    this._context.Items.Remove(key);
                    this._context.Items.Add(key, value);

                }
            }
        }

        /// <summary>
        /// Gets or sets the current session
        /// </summary>
        public PartnerSession Session
        {
            get
            {
                return this.GetSession(false);
            }
            set
            {
                Current[CONST_PARTNERSESSION] = value;
            }
        }

        /// <summary>
        /// Gets or sets the current user
        /// </summary>
        public Service.User User
        {
            get
            {
                return this._currentPartner;
            }
            set
            {
                this._currentPartner = value;
            }
        }

        /// <summary>
        /// Gets or sets the value indicating whether current user is impersonated
        /// </summary>
        public bool IsCurrentPartnerImpersonated
        {
            get
            {
                return this._isCurrentPartnerImpersonated;
            }
            set
            {
                this._isCurrentPartnerImpersonated = value;
            }
        }

        /// <summary>
        /// Gets or sets the current user (in case th current user is impersonated)
        /// </summary>
        public Service.User OriginalUser
        {
            get
            {
                return this._originalPartner;
            }
            set
            {
                this._originalPartner = value;
            }
        }

        /// <summary>
        /// Gets an user host address
        /// </summary>
        public string UserHostAddress
        {
            get
            {
                if (HttpContext.Current != null &&
                    HttpContext.Current.Request != null &&
                    HttpContext.Current.Request.UserHostAddress != null)
                    return HttpContext.Current.Request.UserHostAddress;
                else
                    return string.Empty;
            }
        }

        /// <summary>
        /// Get a value indicating whether we have localized entity properties
        /// </summary>
        public bool LocalizedEntityPropertiesEnabled
        {
            get
            {
                if (!_localizedEntityPropertiesEnabled.HasValue)
                {
                    bool showHidden = this.IsAdmin;
                    //var languages = IoC.Resolve<ILanguageService>().GetAllLanguages(showHidden);

                    this._localizedEntityPropertiesEnabled = false; //languages.Count > 1;
                }
                return this._localizedEntityPropertiesEnabled.Value;
            }
        }

        /// <summary>
        /// Sets the CultureInfo 
        /// </summary>
        /// <param name="culture">Culture</param>
        public void SetCulture(CultureInfo culture)
        {
            Thread.CurrentThread.CurrentCulture = culture;
            Thread.CurrentThread.CurrentUICulture = Thread.CurrentThread.CurrentCulture;
        }

        #endregion

    }
}