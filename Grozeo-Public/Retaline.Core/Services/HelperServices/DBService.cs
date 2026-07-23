using LazyCache;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Common;
using Retaline.Core.BusinessModel.Home;
using Retaline.Core.ViewModel.Tenant;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Data;
using System.Data.SqlClient;
using System.Linq;
using System.Security.Cryptography.X509Certificates;
using System.Text;
using System.Threading;
using System.Threading.Tasks;

namespace Retaline.Core.Services.HelperServices
{
    public class DBService: IDBService
    {
        private readonly IConfiguration _configuration;
        public DBService(IConfiguration configuration)
        {
            _configuration = configuration;
        }

        /// <summary>
        /// GetAllTenants from DB
        /// </summary>
        /// <returns>All active Tenants list</returns>
        public async Task<Collection<AppTenant>> GetAllTenants()
        {
            Collection<AppTenant> appTenants = new Collection<AppTenant>();
            //IAppCache cache = new CachingService();

            //appTenants = await Utilities.Common.Cache.GetOrAddAsync(Utilities.Common.TenantsCacheKey, cacheEntry =>
            //{
                //cacheEntry.SlidingExpiration = TimeSpan.FromSeconds(60*30);
                return await GetAllTenantsFromDB();

            //});

            return appTenants;
        }

        private async Task<Collection<AppTenant>> GetAllTenantsFromDB()
        {
            Collection<AppTenant> tenants = new Collection<AppTenant>();
            try
            {
                if (String.IsNullOrEmpty(_configuration["ConnectionStrings:DefaultConnection"]))
                    return null;

                using (var connection = new SqlConnection(_configuration["ConnectionStrings:DefaultConnection"]))
                {
                    await connection.OpenAsync();
                    string strSql = "SELECT a.*, Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.[Status] > 0 and a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts, s.StoreAddress, s.StoreEmail, s.StorePhone, s.SM_FB, s.SM_Twiter, s.SM_Insta, s.SM_WP, s.SM_Other, s.AppUrlAndroid, s.AppUrlIOS FROM AppTenant a left join Store s on a.Id=s.Tenantid ";
                    using (var command = new SqlCommand(strSql, connection)) // "SELECT * FROM dbo.AppTenant Where [Status] = 1"
                    {
                        var reader = await command.ExecuteReaderAsync();
                        //var lst = reader.AutoMap<ViewModel.Tenant.AppTenant>().ToList();
                        //if (reader.Read())
                        //{
                        //    result = reader[0].ToString();
                        //}
                        while (reader.Read())
                        {
                            try
                            {
                                AppTenant tenant = new AppTenant();
                                tenant.Id = int.Parse(reader["Id"].ToString());
                                tenant.Name = reader["Name"].ToString();
                                tenant.Theme = reader["Theme"].ToString();
                                tenant.APIUrl = reader["APIUrl"].ToString();

                                tenant.StoreId = reader["StoreId"].ToString();

                                string srHostnames = reader["hosts"].ToString();
                                if (!String.IsNullOrEmpty(srHostnames))
                                {
                                    tenant.Hostnames = srHostnames.Split(',').Select(h => h.Trim()).ToArray();
                                }
                                tenant.CanCheckout = (bool)reader["CanCheckout"];
                                tenant.OnlinePaymentEnabled = (bool)reader["OnlinePaymentEnabled"];

                                tenant.Status = Convert.ToInt32(reader["Status"]);
                                tenant.ShowPWA = bool.Parse(reader["ShowPWA"].ToString());
                                tenant.LogoImage = reader["LogoImage"].ToString();
                                tenant.CustomColor = reader["CustomColor"].ToString();
                                tenant.LogoSmall = reader["LogoSmall"].ToString();
                                tenant.FavIcoImage = reader["FavIcoImage"].ToString();
                                try { tenant.Stage = Convert.ToInt32(reader["Stage"]); } catch { }
                                try { tenant.OwnBannerOnly = Convert.ToBoolean(reader["OwnBannerOnly"]); } catch { tenant.OwnBannerOnly = false; }
                                tenant.EnableAnalytics = false; try { tenant.EnableAnalytics = Convert.ToBoolean(reader["EnableAnalytics"]); } catch { tenant.EnableAnalytics = false; }
                                try { tenant.Address = reader["StoreAddress"].ToString(); } catch { }
                                try { tenant.ContactEmail = reader["StoreEmail"].ToString(); } catch { }
                                try { tenant.ContactPhone = reader["StorePhone"].ToString(); } catch { }
                                try { tenant.AnalyticsId = reader["AnalyticsId"].ToString(); } catch { }
                                try { tenant.PODEnabled = (bool)reader["PODEnabled"]; } catch { }

                                // SM_FB, SM_Twiter, SM_Insta, SM_WP, SM_Other
                                try { tenant.SM_FB = reader["SM_FB"].ToString(); } catch { }
                                try { tenant.SM_Twiter = reader["SM_Twiter"].ToString();} catch { }
                                try { tenant.SM_Insta = reader["SM_Insta"].ToString();} catch { }
                                try { tenant.SM_WP = reader["SM_WP"].ToString();} catch { }
                                try { tenant.SM_Other = reader["SM_Other"].ToString(); } catch { }
                                try { tenant.PaymentGateway = reader["PaymentGateway"].ToString(); } catch { }

                                try { tenant.AppUrlAndroid = reader["AppUrlAndroid"].ToString(); } catch { }
                                try { tenant.AppUrlIOS = reader["AppUrlIOS"].ToString(); } catch { }

                                tenants.Add(tenant);
                            }
                            catch(Exception ex)
                            {
                                
                            }
                        }

                        if (!reader.IsClosed) await reader.CloseAsync();
                        if (connection.State != ConnectionState.Closed) await connection.CloseAsync();

                        //if(!tenants.Any(t => t.Name == "Grozeo"))
                        //{
                        //    var objc = _configuration.GetValue<MultitenancyOptions>("Multitenancy");
                        //    if (objc != null && objc.Tenants != null && objc.Tenants.Any(t=> t.Name == "Grozeo"))
                        //    {
                        //        tenants.Add(objc.Tenants.Where(t => t.Name == "Grozeo").FirstOrDefault());
                        //    }
                        //}
                        

                        return tenants;//FormatJsonData(result);
                    }
                }
            }
            catch (Exception ex)
            {
                return null;
            }

        }
        //private string FormatJsonData(string json)
        //{
        //    dynamic data = Newtonsoft.Json.JsonConvert.DeserializeObject(json);
        //    return Newtonsoft.Json.JsonConvert.SerializeObject(data, Newtonsoft.Json.Formatting.Indented);
        //}

        public async Task<IList<GenericAttribute>> GetGenericAttributeFromDB(int entityId, string keyGroup)
        {
            IList<GenericAttribute> genAttributes = new List<GenericAttribute>();
            try
            {
                if (String.IsNullOrEmpty(_configuration["ConnectionStrings:DefaultConnection"]))
                    return null;

                using (var connection = new SqlConnection(_configuration["ConnectionStrings:DefaultConnection"]))
                {
                    await connection.OpenAsync();
                    string strSql = "select * from GenericAttribute where EntityId = @entityId AND KeyGroup = @keyGroup";
                    using (var command = new SqlCommand(strSql, connection)) // "SELECT * FROM dbo.AppTenant Where [Status] = 1"
                    {
                        command.Parameters.Add(new SqlParameter("@entityId", entityId));
                        command.Parameters.Add(new SqlParameter("@keyGroup", keyGroup));

                        var reader = await command.ExecuteReaderAsync();
                        //var lst = reader.AutoMap<ViewModel.Tenant.AppTenant>().ToList();
                        //if (reader.Read())
                        //{
                        //    result = reader[0].ToString();
                        //}
                        while (reader.Read())
                        {
                            try
                            {
                                GenericAttribute atr = new GenericAttribute();
                                atr.Id = int.Parse(reader["Id"].ToString());
                                atr.EntityId = int.Parse(reader["EntityId"].ToString());
                                atr.KeyGroup = reader["KeyGroup"].ToString();
                                atr.Key = reader["Key"].ToString();
                                atr.Value = reader["Value"].ToString();
                                atr.StoreId = int.Parse(reader["StoreId"].ToString());

                                try { atr.CreatedOrUpdatedDateUTC = Convert.ToDateTime(reader["CreatedOrUpdatedDateUTC"]); } catch { }

                                genAttributes.Add(atr);
                            }
                            catch (Exception ex)
                            {

                            }
                        }

                        if (!reader.IsClosed) await reader.CloseAsync();
                        if (connection.State != ConnectionState.Closed) await connection.CloseAsync();

                        return genAttributes;
                    }
                }
            }
            catch (Exception ex)
            {
                return null;
            }

        }
        public async Task<int> SaveGenericAttributeInDB(GenericAttribute attribute)
        {
            try
            {
                if (String.IsNullOrEmpty(_configuration["ConnectionStrings:DefaultConnection"]))
                    return default;

                using (var connection = new SqlConnection(_configuration["ConnectionStrings:DefaultConnection"]))
                {
                    await connection.OpenAsync();
                    string strSql = @"if exists(select * from GenericAttribute where EntityId = @entityId AND KeyGroup = @keyGroup) 
                    begin delete GenericAttribute  where EntityId = @entityId AND KeyGroup = @keyGroup end; 
                    insert into GenericAttribute([KeyGroup],[Key],[Value],[EntityId],[StoreId]) values(@keyGroup,@key,@value,@entityId,@storeId)";
                    using (var command = new SqlCommand(strSql, connection)) // "SELECT * FROM dbo.AppTenant Where [Status] = 1"
                    {
                        command.Parameters.Add(new SqlParameter("@entityId", attribute.EntityId));
                        command.Parameters.Add(new SqlParameter("@keyGroup", attribute.KeyGroup));
                        command.Parameters.Add(new SqlParameter("@key", attribute.Key));
                        command.Parameters.Add(new SqlParameter("@value", attribute.Value));
                        command.Parameters.Add(new SqlParameter("@storeId", attribute.StoreId));

                        var result = await command.ExecuteNonQueryAsync();
                        return result;
                    }
                }
            }
            catch (Exception ex)
            {
                return default;
            }

        }


		public async Task<IList<RetalinePlugin>> GetTenantPlugins(int tenantId)
		{
			try
			{
				if (String.IsNullOrEmpty(_configuration["ConnectionStrings:DefaultConnection"]))
					return default;

				IList<RetalinePlugin> retalinePlugins = new List<RetalinePlugin>();
				using (var connection = new SqlConnection(_configuration["ConnectionStrings:DefaultConnection"]))
				{
					await connection.OpenAsync();
					string strSql = @"SELECT tp.*, p.[Name] as pluginName, p.[Type], p.AllPages, p.[Key] FROM Plugin p inner join TenantPlugin tp on tp.PluginId=p.Id WHERE tp.TenantId=@storeId;";
					using (var command = new SqlCommand(strSql, connection))
					{
						command.Parameters.Add(new SqlParameter("@storeId", tenantId));

						var reader = await command.ExecuteReaderAsync();
						while (reader.Read())
						{
							try
							{

								var plugin = new RetalinePlugin { TenantPlugins=new List<TenantPlugin>() };
                                int pluginId = Convert.ToInt32(reader["PluginId"]);
                                //plugin.TenantPlugins = new List<TenantPlugin>();

                                if (retalinePlugins.Any(p => p.Id == pluginId))
                                {
                                    plugin = retalinePlugins.Where(p => p.Id == pluginId).FirstOrDefault();
                                }
                                else { 
                                    plugin.Name = reader["pluginName"].ToString();
                                    plugin.TypeId = Convert.ToInt32(reader["Type"].ToString()); 
                                    //plugin.Description = reader["Description"].ToString();
                                    plugin.AllPages = Convert.ToBoolean(reader["AllPages"]);
                                    plugin.Key = reader["Key"].ToString();
                                    retalinePlugins.Add(plugin);
                                    plugin.Id = pluginId;
								}
                                var tenantPlugin = new TenantPlugin();
                                tenantPlugin.TenantId = Convert.ToInt32(reader["TenantId"]);
                                tenantPlugin.PluginId = Convert.ToInt32(reader["PluginId"]);
                                tenantPlugin.Name = reader["Name"].ToString();
                                tenantPlugin.Value = reader["Value"].ToString();
                                plugin.TenantPlugins.Add(tenantPlugin);
							}
							catch (Exception ex)
							{

							}
						}

						if (!reader.IsClosed) await reader.CloseAsync();
						if (connection.State != ConnectionState.Closed) await connection.CloseAsync();

						return retalinePlugins;

					}
				}
			}
			catch (Exception ex)
			{
				return default;
			}

		}


	}
}
