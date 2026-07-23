using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

using Azure.Identity;
using Azure.Security.KeyVault.Secrets;

using Microsoft.Azure.Management.WebSites;
using Microsoft.Azure.Management.WebSites.Models;
using Microsoft.Rest.Azure.Authentication;
using System.Configuration;
using Amazon.DynamoDBv2;
using System.Web.Http.Controllers;
using NPOI.SS.Formula.Functions;
using Microsoft.Ajax.Utilities;
using System.Text.Json.Serialization;
using Newtonsoft.Json;
using RetalineProAgent.Manage;
using System.Globalization;
using System.Text.RegularExpressions;
using Amazon.Runtime.Internal.Util;
using DnsClient;
using Org.BouncyCastle.Crypto.Tls;

namespace RetalineProAgent
{
    public partial class DomainControl : Base.BasePartnerPage
    {
        private static readonly LookupClient dnsClient = new LookupClient();

        protected void Page_Load(object sender, EventArgs e)
        {
            lblStatus.Text = string.Empty;
            if (!IsPostBack)
            {
                plcValidateDomain.Visible = true;
                plcDomainProgress.Visible = false;
                plcDomainSettings.Visible = true;
                plcSuccess.Visible = false;

                string sql = "select *, getutcdate() as curdate from CustomDomain where Tenantid = @storegroupid";
                DataTable dt = DataService.GetDataTable(sql, parmeters: new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId) });
                if (dt != null && dt.Rows.Count > 0)
                {
                    string strDomainName = (dt.Rows[0]["Domain"]).ToString();
                    string strAllocatedIP = (dt.Rows[0]["AllocatedIP"]).ToString();
                    string strTxt = (dt.Rows[0]["TXTRecord"]).ToString();
                    DateTime createdOn = (DateTime)dt.Rows[0]["CreatedOn"];
                    DateTime expiry = (DateTime)dt.Rows[0]["ExpiryDate"];
                    DateTime curDate = (DateTime)dt.Rows[0]["curdate"];

                    int status = 0; try { status = Convert.ToInt32(dt.Rows[0]["Status"]); } catch { status = 0; }
                    hidExpiryDate.Value = expiry.ToString("o");
                    hidCurDate.Value = curDate.ToString("o");

                    // 1: domain mapping completed with SSL, 2: in-progress because of auto mapping failed, 3: SSL is pending
                    plcValidateDomain.Visible = (!(new int[] {1, 3 }).Contains(status) && DateTime.UtcNow >= expiry);
                    plcDomainProgress.Visible = !plcValidateDomain.Visible;

                    if (plcValidateDomain.Visible)
                    {
                        txtDomain.Text = strDomainName;
                        lblStatus.Text = "Domain mapping was expired. Please validate again with the same domain or try with a different domain";
                    }
                    else if (plcDomainProgress.Visible)
                    {
                        ltrDomainName.Text = strDomainName;
                        ltrIP.Text = strAllocatedIP;
                        ltrTXTRecord.Text = strTxt;
                        //plcSuccess.Visible = (new int[] { 1, 3 }).Contains(status);

                        pnlCompleted.Visible = status == 1;
                        pnlSSLPending.Visible = status == 3;

                        ltrDomainStatus.Text = status == 1 ? "Status: Completed" : status == 3 ? "Status: Partially Completed. SSL is pending" : "Status: In progress";
                        plcDomainSettings.Visible = ltrDomainStatus.Visible ;

                        lblMappingProgress.Visible = status == 2;
                        btnValidate.Visible = lblVerify.Visible = status != 2;
                        lblStatus.Text = "";
                    }
                    plcProgressCount.Visible = plcDomainSettings.Visible=!(new int[] { 1, 3 }).Contains(status);
                    btnValidate.Visible = !(new int[] { 1, 3,2 }).Contains(status);
                }
            }

        }

        protected void Unnamed_ServerValidate(object source, ServerValidateEventArgs args)
        {
            args.IsValid = (!String.IsNullOrEmpty(txtDomain.Text) && IsValidDomain(txtDomain.Text)); //Uri.CheckHostName(txtDomain.Text) != UriHostNameType.Unknown);
        }

        //public string GetAsciiDomain(string domainName)
        //{
        //    IdnMapping idn = new IdnMapping();
        //    return idn.GetAscii(domainName);
        //}

        public bool IsValidDomain(string domain)
        {
            string domainPattern = @"^((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,6}$";
            return Regex.IsMatch(domain, domainPattern);
        }
        protected async void btnValidate_Click(object sender, EventArgs e)
        {
            bool hasDNSUpdated = await IsDnsUpdatedAsync(ltrDomainName.Text, ltrIP.Text);
            if(!hasDNSUpdated)
            {
                Common.ShowCustomAlert(this.Page, "Validation failure", "DNS updates not found. Please ensure the required DNS records are set up correctly.", false);
                return;
            }

            bool hasTXTUpdated = await ValidateTxtRecordAsync(ltrDomainName.Text, ltrTXTRecord.Text);
            if (!hasTXTUpdated)
            {
                Common.ShowCustomAlert(this.Page, "Validation failure", "TXT record not found. Please ensure the required TXT record set up correctly.", false);
                return;
            }

            int domainMapped = 0;

            Service.User user = this.CurrentUser;
            string sql = $"update CustomDomain set [Status]= 2 where Tenantid=@storegroupid; ";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId) };
            DataService.ExecuteSql(sql, parmeters: prms);

            try
            {
                domainMapped = await MapDomain(ltrDomainName.Text);
            }
            catch(Exception ex)
            {
                this.LogError($"domainControl - btnValidate - MapDomain Error: {ex.Message}");
                //domainMapped = 2;
                //Core.Services.APIService.Support(4, user.Phone, user.Email, user.FullName, "Custom domain mapping", $"Assign domain '{ltrDomainName.Text}' to store: {user.StoreGroupName} ", user.APIStoreId, 13, "", "");
            }



            //if (domainMapped == 1 || domainMapped ==3)
            //{
            //    sql += "INSERT INTO Host(TenantId, StoreId, HostAddress, [Status]) values(@tenantId, (select top 1 Id from Store where TenantId= @tenantId), @hostAddress, 1)";
            //    prms.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
            //    prms.Add(new KeyValuePair<string, object>("hostAddress", ltrDomainName.Text));
            //}
            //// Create ticket.
            //if(domainMapped == 3)
            //    Core.Services.APIService.Support(4, user.Phone, user.Email, user.FullName, "SSL for custom domain mapping", $"Assign SSL to '{ltrDomainName.Text}' for store: {user.StoreGroupName} ", user.APIStoreId, 13, "", "");

            Response.Redirect(Request.RawUrl);
        }

        private async Task<bool> ValidateTxtRecordAsync(string domain, string expectedTxtValue)
        {
            var result = await dnsClient.QueryAsync(domain, QueryType.TXT);

            var txtRecords = result.Answers.TxtRecords()
                                    .SelectMany(record => record.Text)
                                    .ToList();

            // Check if any TXT record matches the expected value
            return txtRecords.Any(txt => txt.Contains(expectedTxtValue));
        }


        private async Task<bool> IsDnsUpdatedAsync(string domain, string expectedIpAddress)
        {
            try
            {
                IPHostEntry hostEntry = await Dns.GetHostEntryAsync(domain);

                foreach (IPAddress ip in hostEntry.AddressList)
                {
                    if (ip.ToString() == expectedIpAddress)
                    {
                        return true;
                    }
                }
            }
            catch (Exception)
            {
                // Handle exceptions
            }

            return false;
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            if (IsValid)
            {
                string strCustomIP = "", strCustomTXT = "";
                DataTable dt = DataServiceMySql.GetDataTable("SELECT * FROM sys_configuration WHERE cfg_Name LIKE 'CUSTOM-DOMAIN-IP' OR cfg_Name LIKE 'CUSTOM-DOMAIN-TXT' limit 2", Service.UserService.GetAPIConnectionString());
                if (dt != null && dt.Rows.Count >= 2)
                {
                    strCustomIP = dt.Rows[0]["cfg_Value"].ToString();
                    strCustomTXT = dt.Rows[1]["cfg_Value"].ToString();
                }

                if (String.IsNullOrEmpty(strCustomIP) || String.IsNullOrEmpty(strCustomTXT))
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "There is a technical error happened. Please contact admin for more details or submit ticket with details on your domain", false);
                    return;
                }
                DataTable dtExistingRecords = DataService.GetDataTable($"select * from CustomDomain WHERE Domain like @domain and Tenantid <> @storegroupid", parmeters: new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("domain", txtDomain.Text), new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId) });
                if (dtExistingRecords != null && dtExistingRecords.Rows.Count > 0)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "The domain name is already existing for another store. Please try with a different domain or contact admin for more details.", false);
                    return;
                }
                string strInsertSql = "delete CustomDomain WHERE Tenantid = @storegroupid; insert into CustomDomain(Tenantid, Domain, AllocatedIP, TXTRecord, ExpiryDate, [Status]) " +
                    "values(@storegroupid, @domain, @allocatedIP, @txtRecord, dateadd(hour, 48, getutcdate()), 0)";
                int result = DataService.ExecuteSql(strInsertSql, parmeters: new List<KeyValuePair<string, object>> {
                    new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId),
                    new KeyValuePair<string, object>("domain", txtDomain.Text),
                    new KeyValuePair<string, object>("allocatedIP", strCustomIP),
                    new KeyValuePair<string, object>("txtRecord", strCustomTXT)
                });

                Response.Redirect(Request.RawUrl);
                //Common.ShowCustomAlert(this.Page, "Success", "Domain has been added for mapping. Please complete the DNS updates for A and TXT records as listed here, for the domain. The data will be expired after 48 hours. Please complete the DNS updates before expiry and submit for activation", true, "/tenant/domaincontrol");

            }
        }

        protected void lbtnEmail_Click(object sender, EventArgs e)
        {
            Service.User user = this.CurrentUser;
            string toEmail = txtEmail.Text;
            if (String.IsNullOrEmpty(toEmail) || !Common.IsValidEmail(toEmail))
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Destination email is blank or invalid. Please make sure that you have entered the correct email id", false);
                return;
            }
            try
            {
                string subject = "DNS A Records and Instructions for Custom Domain Setup";
                string emailBody = GenerateEmailBody(ltrDomainName.Text, ltrIP.Text, ltrTXTRecord.Text, user.StoreGroupName, user.FullName, (String.IsNullOrEmpty(user.Email) ? user.Phone : user.Email));
                var result = Core.Services.APIService.SendEmail(toEmail, subject, emailBody, "", true);
                Common.ShowCustomAlert(this.Page, "Success", "Email send successfully!!");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Email sending failed due to a technical error. Please contact support for more detals.", false);
            }
        }

        protected void lbtnDownload_Click(object sender, EventArgs e)
        {
            // Create the CSV content
            StringBuilder csvContent = new StringBuilder();
            csvContent.AppendLine("Title,Value");
            csvContent.AppendLine("Domain name," + ltrDomainName.Text);
            csvContent.AppendLine("IP," + ltrIP.Text);
            csvContent.AppendLine("TXT," + ltrTXTRecord.Text);

            // Convert the content to a byte array
            byte[] byteArray = Encoding.UTF8.GetBytes(csvContent.ToString());

            // Clear the response
            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.Cache.SetCacheability(System.Web.HttpCacheability.NoCache);
            Response.ContentType = "text/csv";
            Response.AppendHeader("Content-Disposition", "attachment; filename=DNSRecords.csv");
            Response.BinaryWrite(byteArray);
            Response.Flush();
            Response.End();
        }

        private string GenerateEmailBody(string domain, string ipAddress, string txtRecord, string storeName, string user, string email)
        {
            return $@"
            <p>Dear Technical Team,</p>
            <p>Please update the DNS records for the domain <strong>{domain}</strong> as follows:</p>
            <table border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <th>Record Type</th>
                    <th>Host</th>
                    <th>Value</th>
                    <th>TTL</th>
                </tr>
                <tr>
                    <td>A</td>
                    <td>@</td>
                    <td>{ipAddress}</td>
                    <td>3600</td>
                </tr>
                <tr>
                    <td>TXT</td>
                    <td>@</td>
                    <td>{txtRecord}</td>
                    <td>3600</td>
                </tr>
            </table>
            <p>Ensure that these records are correctly set up to enable the custom domain for {storeName}.</p>
            <p>Best regards,</p>
            <p>{user}</p>
            <p>{email}</p>
        ";
        }

        private async Task<int> MapDomain(string domain)
        {
            int status = 2;
            try
            {
                string keyvaultname = ConfigurationManager.AppSettings.Get("KeyVaultName");
                if (string.IsNullOrEmpty(keyvaultname)) { this.LogError($"DomainControl/MapDomain - KeyVaultName is missing in configuration"); return status; }

                var keyVaultUri = $"https://{keyvaultname}.vault.azure.net/";
                SecretClient secretClient = null;
                try{secretClient = new SecretClient(new Uri(keyVaultUri), new DefaultAzureCredential());}catch(Exception ex1) { this.LogError($"DomainControl/MapDomain - SecretClient (url: {keyVaultUri}) error: {ex1.Message}"); return status; }

                this.LogError("MapDomain - Successfully connected KeyVault client");

                string clientId,clientSecret,tenantId,subscriptionId,resourceGroupName,appName;

                try{clientId = secretClient.GetSecret("GrozeoAppClientId").Value.Value;}catch(Exception ex1) { this.LogError($"DomainControl/MapDomain - SecretClient - GrozeoAppClientId error: {ex1.Message}"); return status; }
                try{clientSecret = secretClient.GetSecret("GrozeoAppClientSecret").Value.Value;}catch(Exception ex1) { this.LogError($"DomainControl/MapDomain - SecretClient - GrozeoAppClientSecret error: {ex1.Message}"); return status; }
                try{tenantId = secretClient.GetSecret("GrozeoAppTenantId").Value.Value;}catch(Exception ex1) { this.LogError($"DomainControl/MapDomain - SecretClient - GrozeoAppTenantid error: {ex1.Message}"); return status; }
                try{subscriptionId = secretClient.GetSecret("GrozeoAppSubscriptionId").Value.Value;}catch(Exception ex1) { this.LogError($"DomainControl/MapDomain - SecretClient - GrozeoAppSubscriptionId error: {ex1.Message}"); return status; }
                try{resourceGroupName = secretClient.GetSecret("GrozeoAppResourceGroupName").Value.Value;}catch(Exception ex1) { this.LogError($"DomainControl/MapDomain - SecretClient - GrozeoAppResourceGroupName error: {ex1.Message}"); return status; }
                try{appName = secretClient.GetSecret("GrozeoAppName").Value.Value; } catch (Exception ex1) { this.LogError($"DomainControl/MapDomain - SecretClient - GrozeoAppName error: {ex1.Message}"); return status; }

                if (String.IsNullOrEmpty(clientId)) { this.LogError($"DomainControl/MapDomain - SecretClient - Missing GrozeoAppClientId in KeyVault or access is not available"); return status; }
                if (String.IsNullOrEmpty(clientSecret)) { this.LogError($"DomainControl/MapDomain - SecretClient - Missing GrozeoAppClientSecret in KeyVault or access is not available"); return status; }
                if (String.IsNullOrEmpty(tenantId)) { this.LogError($"DomainControl/MapDomain - SecretClient - Missing GrozeoAppTenantId in KeyVault or access is not available"); return status; }
                if (String.IsNullOrEmpty(subscriptionId)) { this.LogError($"DomainControl/MapDomain - SecretClient - Missing GrozeoAppSubscriptionId in KeyVault or access is not available"); return status; }
                if (String.IsNullOrEmpty(resourceGroupName)) { this.LogError($"DomainControl/MapDomain - SecretClient - Missing GrozeoAppResourceGroupName in KeyVault or access is not available"); return status; }
                if (String.IsNullOrEmpty(appName)) { this.LogError($"DomainControl/MapDomain - SecretClient - Missing appName in KeyVault or access is not available"); return status; }

                this.LogError($"Successfully loaded secrets from keyVaule: {appName}, {resourceGroupName}, {subscriptionId}, {tenantId}, {clientId}");
                // Step 3: Authenticate with Azure using the retrieved credentials
                var serviceClientCredentials = await ApplicationTokenProvider.LoginSilentAsync(tenantId, clientId, clientSecret);
                if(serviceClientCredentials == null){ this.LogError($"DomainControl/MapDomain - LoginSilentAsync - serviceClientCredentials is null"); return status; }

                this.LogError($"LoginSilentAsync Successfully executed");
                WebSiteManagementClient webSiteManagementClient = null;
                try{webSiteManagementClient = new WebSiteManagementClient(serviceClientCredentials) { SubscriptionId = subscriptionId }; } catch (Exception ex1) { this.LogError($"DomainControl/MapDomain - WebSiteManagementClient error: {ex1.Message}"); return status; }

                // Step 4: Add the custom domain to Azure App Service
                var domainBinding = new HostNameBinding
                {
                    HostNameType = HostNameType.Verified,
                    SslState = SslState.Disabled
                };

                Service.User user = this.CurrentUser;
                try
                {
                    var hostnamebindings = await webSiteManagementClient.WebApps.CreateOrUpdateHostNameBindingAsync(resourceGroupName, appName, domain, domainBinding);
                    try { string strResult = JsonConvert.SerializeObject(hostnamebindings, Formatting.Indented); this.LogError($"Domain mapped. Result: {strResult}"); } catch { }
                    status = 3;
                }
                catch (Exception ex1) 
                { 
                    this.LogError($"DomainControl/MapDomain - webSiteManagementClient.WebApps.CreateOrUpdateHostNameBindingAsync error: {ex1.Message}");
                    Core.Services.APIService.Support(4, user.Phone, user.Email, user.FullName, "Custom domain mapping", $"Assign domain '{ltrDomainName.Text}' to store: {user.StoreGroupName} ", user.APIStoreId, 13, "", "");
                    return status; 
                }

                string sql = $"update CustomDomain set [Status]= 3 where Tenantid=@storegroupid; ";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId) };

                sql += " INSERT INTO Host(TenantId, StoreId, HostAddress, [Status]) values(@tenantId, (select top 1 Id from Store where TenantId= @tenantId), @hostAddress, 1)";
                prms.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                prms.Add(new KeyValuePair<string, object>("hostAddress", ltrDomainName.Text));
                DataService.ExecuteSql(sql, parmeters: prms);

                this.LogError($"Successfully assigned custom domain: {domain}. Executing SSL part");

                // Add SSL
                bool sslAdded = false; try { sslAdded = await BindManagedCertificateAsync(webSiteManagementClient, resourceGroupName, appName, domain); }
                catch(Exception ex2) { this.LogError($"SSL failed for the custom domain: {domain}, Error: {ex2.Message}"); sslAdded = false; }

                if (sslAdded)
                {
                    string sql2 = $"update CustomDomain set [Status]= 1 where Tenantid=@storegroupid; ";
                    List<KeyValuePair<string, object>> prms2 = new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId) };
                    DataService.ExecuteSql(sql, parmeters: prms);

                    this.LogError($"SSL added Successfully for the custom domain: {domain}.");
                    status = 1;
                }
                else
                {
                    // Create ticket
                    APIService.Support(4, user.Phone, user.Email, user.FullName, "SSL for custom domain mapping", $"Assign SSL to '{ltrDomainName.Text}' for store: {user.StoreGroupName} ", user.APIStoreId, 13, "", "");
                    this.LogError($"SSL failed for the custom domain: {domain}.");
                }

            }
            catch(Exception ex)
            {
                this.LogError($"DomainControl/MapDomain - Function failed: {ex.Message}"); 
                //return status;
            }
            return status;
        }

        public async Task<bool> BindManagedCertificateAsync(WebSiteManagementClient webSiteManagementClient, string resourceGroupName, string appServiceName, string domainName)
        {
            try
            {
                var existingBindings = await webSiteManagementClient.WebApps.ListHostNameBindingsAsync(resourceGroupName, appServiceName);
                bool domainExists = existingBindings.Any(b => b.Name.Equals(domainName, StringComparison.OrdinalIgnoreCase));

                if (!domainExists)
                {
                    this.LogError($"SSL failed for the custom domain: {domainName}. Looks like the domain mapping is not yet completed. Skipping SSL part");
                    return false;
                }

                // Request and bind the App Service Managed Certificate
                var sslBinding = new HostNameBinding
                {
                    SslState = SslState.SniEnabled, // Use SNI SSL
                    HostNameType = HostNameType.Managed, // Managed SSL certificate
                    Thumbprint = null, // Thumbprint is null because Azure will automatically create and manage the certificate
                    //CustomHostNameDnsRecordType = CustomHostNameDnsRecordType.CName                    
                };

                var hostnamebinding = await webSiteManagementClient.WebApps.CreateOrUpdateHostNameBindingAsync(
                    resourceGroupName,
                    appServiceName,
                    domainName,
                    sslBinding
                );

                return hostnamebinding.SslState == SslState.SniEnabled;
            }
            catch(Exception ex)
            {
                this.LogError($"SSL failed for the custom domain: {domainName}, Error: {ex.Message}");
                return false;
            }

            // return true;
        }

        protected void btndomainedit_Click(object sender, EventArgs e)
        {
            txtDomainEdit.Text = ltrDomainName.Text;
            txtDomainEdit.Visible = btndomainsave.Visible = btnsavenewdomain.Visible= true;
            ltrDomainName.Visible = domainDisplay.Visible = btndomainedit.Visible= btnnewdomain.Visible = false;
        }

        protected void btndomainsave_Click(object sender, EventArgs e)
        {
            try
            {
                DataTable dtExistingRecords = DataService.GetDataTable($"select * from CustomDomain WHERE Domain like @domain and Tenantid <> @storegroupid", parmeters: new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("domain", txtDomain.Text), new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId) });
                if (dtExistingRecords != null && dtExistingRecords.Rows.Count > 0)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "The domain name is already existing for another store. Please try with a different domain or contact admin for more details.", false);
                    return;
                }
                string sql = $"update CustomDomain set Domain= @Domainname,[Status]=0,ExpiryDate= DATEADD(HOUR, 48, GETUTCDATE()) where Tenantid=@storegroupid; ";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId),
                    new KeyValuePair<string, object>("Domainname", txtDomainEdit.Text)
                };
                DataService.ExecuteSql(sql, parmeters: prms);
            }
            catch (Exception ex)
            {

            }
            txtDomainEdit.Visible = btndomainsave.Visible = btnsavenewdomain.Visible= false;
            ltrDomainName.Visible = domainDisplay.Visible = btndomainedit.Visible = btnnewdomain.Visible = true;
            Common.ShowCustomAlert(this.Page, "Success", "Domain has been udated for mapping.", true, "/tenant/domaincontrol");

        }
    }
}