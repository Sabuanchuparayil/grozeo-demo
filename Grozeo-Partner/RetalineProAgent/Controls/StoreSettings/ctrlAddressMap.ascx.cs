using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Configuration;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlAddressMap: Base.BasePartnerUserControl
    {
        public string ParentLocationClientId { get; set; }
        public string ParentLatClientId { get; set; }
        public string ParentLongClientId { get; set; }

        public string ParentLocationNameClientId { get; set; }
        public string ParentPinClientId { get; set; }
        public string ParentAddrClientId { get; set; }
        public string ParentAddr2ClientId {  get; set; }
        public string ParentAddr3ClientId {  get; set; }
        public string ParentDistrictClientId { get; set; }
        public string ParentStateClientId { get; set; }
        public string ParentLocalityClientId { get; set; }
        public string ParentPlaceClientId { get; set; }
        public string ParentCountryClientId { get; set; }


        public string LocationTxtClientId
        {
            get
            {
                return txtLocation.ClientID;
            }
        }

        private string _lat = ConfigurationManager.AppSettings.Get("DefaultLAT")??"9.5947087", _lng= ConfigurationManager.AppSettings.Get("DefaultLNG") ??"76.4855729";

        public string Lat
        {
            get
            {
                if (String.IsNullOrEmpty((string)ViewState["MAPLATITUDE"]))
                    ViewState["MAPLATITUDE"] = _lat;
                return (string)ViewState["MAPLATITUDE"];
            }
            set
            {
                ViewState["MAPLATITUDE"] = value;
            }
        }
        public string Lng
        {
            get
            {
                if (String.IsNullOrEmpty((string)ViewState["MAPLONGITUDE"]))
                    ViewState["MAPLONGITUDE"] = _lng;
                return (string)ViewState["MAPLONGITUDE"];
            }
            set
            {
                ViewState["MAPLONGITUDE"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {

        }
    }
}