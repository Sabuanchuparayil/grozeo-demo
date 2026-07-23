using System;
using System.Net;
using Azure.Storage.Blobs;
using Microsoft.AspNetCore.DataProtection;
using Microsoft.AspNetCore.Hosting;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;

namespace Retaline.Web.Service
{
    /// <summary>
    /// Represents extensions of IServiceCollection
    /// </summary>
    public static class ServiceCollectionExtensions
    {
        public static string AzureDataProtectionKeyFile => "DataProtectionKeys.xml";
        public static string DataProtectionKeysPath => "~/DataProtectionKeys";

        /// <summary>
        /// Add services to the application and configure service provider
        /// </summary>
        /// <param name="services">Collection of service descriptors</param>
        /// <param name="configuration">Configuration of the application</param>
        /// <param name="webHostEnvironment">Hosting environment</param>
        /// <returns>Configured engine and app settings</returns>
        public static void ConfigureApplicationServices(this IServiceCollection services,
            IConfiguration configuration, IWebHostEnvironment webHostEnvironment)
        {
            //let the operating system decide what TLS protocol version to use
            //see https://docs.microsoft.com/dotnet/framework/network-programming/tls
            ServicePointManager.SecurityProtocol = SecurityProtocolType.SystemDefault;
            services.AddRetalineDataProtection(configuration);

        }

        /// <summary>
        /// Adds data protection services
        /// </summary>
        /// <param name="services">Collection of service descriptors</param>
        public static void AddRetalineDataProtection(this IServiceCollection services, IConfiguration configuration)
        {
            //var appSettings = Singleton<AppSettings>.Instance;
            string strBlobConnection = configuration["Azure:Blob:ConnectionString"];
            string strDataProtectionKeysContainerName = configuration["Azure:Blob:DataProtectionKeysContainerName"];
            // DataProtectionKeysContainerName
            if (!String.IsNullOrEmpty(strBlobConnection) && !String.IsNullOrEmpty(strDataProtectionKeysContainerName))
            {
                var blobServiceClient = new BlobServiceClient(strBlobConnection);
                var blobContainerClient = blobServiceClient.GetBlobContainerClient(strDataProtectionKeysContainerName);
                var blobClient = blobContainerClient.GetBlobClient(AzureDataProtectionKeyFile);

                var dataProtectionBuilder = services.AddDataProtection().PersistKeysToAzureBlobStorage(blobClient);

                //if (!appSettings.AzureBlobConfig.DataProtectionKeysEncryptWithVault)
                //    return;

                //var keyIdentifier = appSettings.AzureBlobConfig.DataProtectionKeysVaultId;
                //var credentialOptions = new DefaultAzureCredentialOptions();
                //var tokenCredential = new DefaultAzureCredential(credentialOptions);

                //dataProtectionBuilder.ProtectKeysWithAzureKeyVault(new Uri(keyIdentifier), tokenCredential);
            }
            else
            {
                //var dataProtectionKeysPath = CommonHelper.DefaultFileProvider.MapPath(RetalineDataProtectionDefaults.DataProtectionKeysPath);
                var dataProtectionKeysFolder = new System.IO.DirectoryInfo(DataProtectionKeysPath);

                //configure the data protection system to persist keys to the specified directory
                services.AddDataProtection().PersistKeysToFileSystem(dataProtectionKeysFolder);
            }
        }
        
    }
}