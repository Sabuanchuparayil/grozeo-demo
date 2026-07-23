using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Amazon;
using Amazon.S3;
using Amazon.S3.Model;
using Amazon.S3.Transfer;
using System.Configuration;
using System.IO;
using System.Net;
using System.Xml.Linq;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services
{
    public static class FileService
    {
        //        AWS_ACCESS_KEY_ID=AKIAWIS5ROLTVHNOZ5AM
        //AWS_SECRET_ACCESS_KEY=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
        //AWS_DEFAULT_REGION=ap-southeast-1
        //AWS_BUCKET=odomedsdev
        //AWS_BUCKET_UPLOADS=odomedsdevuploads

        private static string bucketName = ConfigurationManager.AppSettings.Get("AWS_S3_BucketName");//"your-amazon-s3-bucket";
        private static string accessKeyId = ConfigurationManager.AppSettings.Get("AWS_Key_ID");
        private static string accessSecret = ConfigurationManager.AppSettings.Get("AWS_Secret");
        private static string region = ConfigurationManager.AppSettings.Get("AWS_Region"); // "us-west-1"
        private static string bucketNameforProducts = ConfigurationManager.AppSettings.Get("AWS_S3_BucketProducts");
        private static string bucketNameforAndroid = ConfigurationManager.AppSettings.Get("AWS_S3_BucketAndroid");
        private static string bucketNameforTenant = ConfigurationManager.AppSettings.Get("AWS_S3_BucketTenant");
        private static string appregion = ConfigurationManager.AppSettings.Get("AWS_AppRegion");

        public static string UploadImage(System.IO.Stream fileStream, string fileName)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            string rndFileName = Guid.NewGuid().ToString() + Path.GetExtension(fileName);
            rndFileName = rndFileName.Replace("..", ".").Replace("-", "");
            try
            {
                //MetadataCollection meta = new MetadataCollection();
                //meta.Add();
                PutObjectRequest putRequest = new PutObjectRequest
                {
                    BucketName = bucketName,
                    Key = rndFileName,
                    InputStream = fileStream,
                    CannedACL = S3CannedACL.PublicRead
                    //FilePath = "product/",
                    //ContentType = "text/plain"
                };
                putRequest.Metadata.Add("bucket", bucketNameforProducts);
                putRequest.Metadata.Add("mediaType", "image");
                putRequest.Metadata.Add("filepath", "products/");

                PutObjectResponse response = client.PutObject(putRequest);

                var result = response.HttpStatusCode;
            }
            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                    ||
                    amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }

            return rndFileName;
        }

        public static string DeleteImage(string fileName)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            string rndFileName = Guid.NewGuid().ToString() + Path.GetExtension(fileName);
            rndFileName = rndFileName.Replace("..", ".").Replace("-", "");
            try
            {
                //MetadataCollection meta = new MetadataCollection();
                //meta.Add();
                DeleteObjectRequest delRequest = new DeleteObjectRequest
                {
                    BucketName = bucketName,
                    Key = rndFileName,
                    //FilePath = "product/",
                    //ContentType = "text/plain"
                };

                var response = client.DeleteObject(delRequest);

                var result = response.HttpStatusCode;
            }
            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                    ||
                    amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }

            return rndFileName;
        }



        public static string UploadFileToS3( string region,System.IO.Stream fileStream, string fileName, string strBucketName, string strPath = "", string strMediaType = "image", bool keepFile = false)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            string rndFileName = keepFile ? fileName : Guid.NewGuid().ToString() + Path.GetExtension(fileName);
            rndFileName = rndFileName.Replace("..", ".").Replace("-", "");
            try
            {
                PutObjectRequest putRequest = new PutObjectRequest
                {
                    BucketName = strBucketName,
                    Key = strPath + rndFileName,
                    InputStream = fileStream,
                    CannedACL = S3CannedACL.PublicRead
                };
                putRequest.Metadata.Add("bucket", strBucketName);
                putRequest.Metadata.Add("mediaType", strMediaType);
                //putRequest.Metadata.Add("filepath", strPath);

                PutObjectResponse response = client.PutObject(putRequest);

                var result = response.HttpStatusCode;
            }
            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                    ||
                    amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }

            return rndFileName;
        }

        public class S3UploadResult
        {
            public string BodyContent { get; set; }
            public string FileName { get; set; }
        }

        public static S3UploadResult UploadFileToS3(string bodyContent, string fileName, string strPath = "", string strMediaType = "image")
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion));
            //string rndFileName = Guid.NewGuid().ToString() + Path.GetExtension(fileName);
            string rndFileName = fileName;
            rndFileName = rndFileName.Replace("..", ".").Replace("-", "");

            try
            {
                PutObjectRequest putRequest = new PutObjectRequest
                {
                    BucketName = bucketNameforTenant,
                    Key = strPath + rndFileName,
                    ContentBody = bodyContent,
                    CannedACL = S3CannedACL.PublicRead,
                    ContentType = "txt/xml"
                };
                putRequest.Metadata.Add("bucket", bucketNameforTenant);

                PutObjectResponse response = client.PutObject(putRequest);

                var result = response.HttpStatusCode;

                // Return an instance of S3UploadResult with both values
                return new S3UploadResult
                {
                    BodyContent = bodyContent,
                    FileName = rndFileName
                };
            }
            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                    ||
                    amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }
        }


        public static Dictionary<string, string> ReadAllS3Files(string getthemes, string storetheme, string extensionFilter)
        {
            if (string.IsNullOrWhiteSpace(getthemes) || string.IsNullOrWhiteSpace(storetheme))
            {
                throw new ArgumentException("getthemes, storetheme, and extensionFilter cannot be null or empty.");
            }

            string bucketName = ConfigurationManager.AppSettings.Get("AWS_S3_BucketProducts");
            if (string.IsNullOrWhiteSpace(bucketName))
            {
                throw new ConfigurationErrorsException("The 'ThemeLocation' AppSetting is missing or empty in your configuration file.");
            }

            Dictionary<string, string> fileContents = new Dictionary<string, string>();

            using (var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region)))
            {
                string prefix = $"{getthemes}/{storetheme}";

                string continuationToken = null;
                do
                {
                    ListObjectsV2Request request = new ListObjectsV2Request
                    {
                        BucketName = bucketName, 
                        Prefix = prefix,
                        ContinuationToken = continuationToken,
                    };

                    try
                    {
                        ListObjectsV2Response response = client.ListObjectsV2(request);
                        foreach (S3Object s3Object in response.S3Objects)
                        {
                            if (s3Object.Key.EndsWith("/") || (s3Object.Size == 0 && s3Object.Key == prefix))
                            {
                                continue;
                            }
                            try
                            {
                                using (GetObjectResponse getObjectResponse = client.GetObject(bucketName, s3Object.Key)) // Use the consistent bucketName variable
                                using (StreamReader reader = new StreamReader(getObjectResponse.ResponseStream))
                                {
                                    string fileContent = reader.ReadToEnd();
                                    fileContents[s3Object.Key] = fileContent;
                                }
                            }
                            catch (AmazonS3Exception s3Ex)
                            {
                                Console.WriteLine($"Error getting object {s3Object.Key} from S3: {s3Ex.Message}");
                            }
                        }

                        continuationToken = response.NextContinuationToken;
                    }
                    catch (AmazonS3Exception ex)
                    {
                        Console.WriteLine("Amazon S3 Exception during listing: " + ex.Message);
                        throw;
                    }
                    catch (Exception ex)
                    {
                        Console.WriteLine("General Exception during listing: " + ex.Message);
                        throw;
                    }

                } while (!string.IsNullOrEmpty(continuationToken));
            }

            return fileContents;
        }
        public static int GetApkCount(int storeGroupId)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion));
            string bucketName = bucketNameforTenant;
            string prefix = "APK-Files/" + storeGroupId;

            ListObjectsV2Request request = new ListObjectsV2Request
            {
                BucketName = bucketName,
                Prefix = prefix
            };

            try
            {
                ListObjectsV2Response response = client.ListObjectsV2(request);

                int apkCount = response.S3Objects.Count(obj => obj.Key.EndsWith(".apk", StringComparison.OrdinalIgnoreCase));

                return apkCount;
            }
            catch (AmazonS3Exception ex)
            {
                // Handle Amazon S3 specific exceptions
                Console.WriteLine("Amazon S3 Exception: " + ex.Message);
            }
            catch (Exception ex)
            {
                // Handle other exceptions
                Console.WriteLine("Exception: " + ex.Message);
            }

            return 0; // Return 0 if there was an exception or no APK files found
        }

        public static int GetXmlFileCount(int storeGroupId, string uploadResult, string strPath = "")
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion));
            string rndFileName = uploadResult;
            string bucketName = bucketNameforTenant;
            string xmlFilename = rndFileName; // Build the XML file name
            strPath = "xml-files/" + "preview-";

            try
            {
                GetObjectMetadataRequest request = new GetObjectMetadataRequest
                {
                    BucketName = bucketName,
                    Key = strPath + xmlFilename,
                };

                // If the object metadata request succeeds, the XML file exists
                GetObjectMetadataResponse response = client.GetObjectMetadata(request);
                return 1;
            }
            catch (AmazonS3Exception ex)
            {
                // Handle Amazon S3 specific exceptions
                if (ex.StatusCode == HttpStatusCode.NotFound)
                {
                    // Object not found, return 0
                    return 0;
                }
                Console.WriteLine("Amazon S3 Exception: " + ex.Message);
            }
            catch (WebException wex)
            {
                // Handle web exceptions
                if (wex.Response is HttpWebResponse response && response.StatusCode == HttpStatusCode.NotFound)
                {
                    // Object not found, return 0
                    return 0;
                }
                Console.WriteLine("WebException: " + wex.Message);
            }
            catch (Exception ex)
            {
                // Handle other exceptions
                Console.WriteLine("Exception: " + ex.Message);
            }

            // Return 0 for other exceptions
            return 0;
        }

        public static int GetJsonFileCount(int storeGroupId, string uploadResult, string strPath = "")
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion));
            string rndFileName = uploadResult;
            string bucketName = bucketNameforTenant;
            string xmlFilename = rndFileName; // Build the XML file name
            strPath = "Store-Data/" + storeGroupId;

            try
            {
                GetObjectMetadataRequest request = new GetObjectMetadataRequest
                {
                    BucketName = bucketName,
                    Key = strPath + "/" + xmlFilename,
                };

                // If the object metadata request succeeds, the XML file exists
                GetObjectMetadataResponse response = client.GetObjectMetadata(request);
                return 1;
            }
            catch (AmazonS3Exception ex)
            {
                // Handle Amazon S3 specific exceptions
                if (ex.StatusCode == HttpStatusCode.NotFound)
                {
                    // Object not found, return 0
                    return 0;
                }
                Console.WriteLine("Amazon S3 Exception: " + ex.Message);
            }
            catch (WebException wex)
            {
                // Handle web exceptions
                if (wex.Response is HttpWebResponse response && response.StatusCode == HttpStatusCode.NotFound)
                {
                    // Object not found, return 0
                    return 0;
                }
                Console.WriteLine("WebException: " + wex.Message);
            }
            catch (Exception ex)
            {
                // Handle other exceptions
                Console.WriteLine("Exception: " + ex.Message);
            }

            // Return 0 for other exceptions
            return 0;
        }

        public static string DownloadXmlFromS3(string objectKey)
        {
            using (var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion)))
            {
                GetObjectRequest request = new GetObjectRequest
                {
                    BucketName = bucketNameforTenant,
                    Key = objectKey
                };

                using (GetObjectResponse response = client.GetObject(request))
                using (Stream responseStream = response.ResponseStream)
                using (StreamReader reader = new StreamReader(responseStream))
                {
                    return reader.ReadToEnd();
                }
            }
        }

        public static string ReadJsonFromS3(string key)
        {
            try
            {
                using (var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion)))
                {
                    var request = new GetObjectRequest
                    {
                        BucketName = bucketNameforTenant,
                        Key = key
                    };

                    using (GetObjectResponse response = client.GetObject(request))
                    using (Stream responseStream = response.ResponseStream)
                    using (StreamReader reader = new StreamReader(responseStream))
                    {
                        return reader.ReadToEnd();
                    }
                }
            }
            catch (AmazonS3Exception ex)
            {
                // Check if the error is due to the file not being found
                if (ex.StatusCode == System.Net.HttpStatusCode.NotFound)
                {
                    Console.WriteLine($"File not found in S3: {ex.Message}");
                    return null;
                }

                Console.WriteLine($"Error reading JSON from S3: {ex.Message}");
                return null;
            }
        }

        public static string DownloadLogoFromS3(string bucketName)
        {
            using (var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion)))
            {
                GetObjectRequest request = new GetObjectRequest
                {
                    BucketName = bucketName,
                };

                using (GetObjectResponse response = client.GetObject(request))
                using (Stream responseStream = response.ResponseStream)
                using (StreamReader reader = new StreamReader(responseStream))
                {
                    return reader.ReadToEnd();
                }
            }
        }

        public static void ProcessXmlContent(string xmlContent)
        {
            try
            {
                // Load the XML content into an XDocument for parsing.
                XDocument xmlDoc = XDocument.Parse(xmlContent);

                // Extract data from the XML using LINQ to XML.
                string appName = xmlDoc.Root
                    .Elements("string")
                    .Where(e => (string)e.Attribute("name") == "app_name")
                    .Select(e => (string)e)
                    .FirstOrDefault();

                string appPackage = xmlDoc.Root
                    .Elements("string")
                    .Where(e => (string)e.Attribute("name") == "app_package")
                    .Select(e => (string)e)
                    .FirstOrDefault();

                string storeGroupId = xmlDoc.Root
                    .Elements("string")
                    .Where(e => (string)e.Attribute("name") == "storegroupid")
                    .Select(e => (string)e)
                    .FirstOrDefault();

                string headLine = xmlDoc.Root
                    .Elements("string")
                    .Where(e => (string)e.Attribute("name") == "headLine")
                    .Select(e => (string)e)
                    .FirstOrDefault();

                string description = xmlDoc.Root
                    .Elements("string")
                    .Where(e => (string)e.Attribute("name") == "description")
                    .Select(e => (string)e)
                    .FirstOrDefault();
            }
            catch (Exception ex)
            {
                Console.WriteLine("Exception: " + ex.Message);
            }

        }

        public static string UploadGraphics(System.IO.Stream fileStream, string fileName, int storeId, string graphicsFilename)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            string rndFileName = Guid.NewGuid().ToString() + Path.GetExtension(fileName);
            rndFileName = rndFileName.Replace("..", ".").Replace("-", "");
            string fullPath = "customImages" + "/" + storeId + "/" + graphicsFilename;
            try
            {
                PutObjectRequest putRequest = new PutObjectRequest
                {
                    BucketName = bucketName,
                    Key = fullPath,
                    InputStream = fileStream,
                    CannedACL = S3CannedACL.PublicRead
                };

                putRequest.Metadata.Add("bucket", bucketName);
                putRequest.Metadata.Add("mediaType", "image");

                PutObjectResponse response = client.PutObject(putRequest);

                var result = response.HttpStatusCode;
            }

            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                    ||
                    amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }

            return ConfigurationManager.AppSettings["uploads.url"] + fullPath;
        }


        public static (string fileName, string fileUrl) AttachFileToS3(System.IO.Stream fileStream, string fileName)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            string rndFileName = Guid.NewGuid().ToString() + Path.GetExtension(fileName);
            rndFileName = rndFileName.Replace("..", ".").Replace("-", "");

            try
            {
                PutObjectRequest putRequest = new PutObjectRequest
                {
                    BucketName = bucketName,
                    Key = "partnersupport/" + rndFileName, // Include the desired folder path in the object key
                    InputStream = fileStream,
                    CannedACL = S3CannedACL.PublicRead
                };
                putRequest.Metadata.Add("bucket", bucketName);
                putRequest.Metadata.Add("mediaType", "image");
                putRequest.Metadata.Add("filepath", "partnersupport/");

                PutObjectResponse response = client.PutObject(putRequest);

                var result = response.HttpStatusCode;

                // Construct and return the full S3 URL along with the generated file name
                string s3Url = $"https://{bucketName}.s3.{Amazon.RegionEndpoint.GetBySystemName(region).SystemName}.amazonaws.com/partnersupport/{rndFileName}";
                return (rndFileName, s3Url);
            }
            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                     ||
                     amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }
        }


        public static bool MoveFileAndXmlPreviewToFolder(string xmlPreviewData, string sourcePrefix, string destinationPrefix, string destinationXmlPrefix)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(appregion));

            try
            {
                // Move XML preview file to the destination XML folder
                string xmlPreviewSourceKey = xmlPreviewData;
                string xmlPreviewDestinationKey = destinationXmlPrefix;

                CopyObjectRequest xmlPreviewCopyRequest = new CopyObjectRequest
                {
                    SourceBucket = bucketNameforTenant,
                    SourceKey = xmlPreviewSourceKey,
                    DestinationBucket = bucketNameforTenant,
                    DestinationKey = xmlPreviewDestinationKey
                };

                client.CopyObject(xmlPreviewCopyRequest);

                DeleteObjectRequest xmlPreviewDeleteRequest = new DeleteObjectRequest
                {
                    BucketName = bucketNameforTenant,
                    Key = xmlPreviewSourceKey
                };

                client.DeleteObject(xmlPreviewDeleteRequest);

                // Move each object from the source folder to the destination folder
                ListObjectsV2Request listRequest = new ListObjectsV2Request
                {
                    BucketName = bucketNameforTenant,
                    Prefix = sourcePrefix
                };

                ListObjectsV2Response listResponse;
                do
                {
                    // List objects in the source folder
                    listResponse = client.ListObjectsV2(listRequest);

                    // Move each object to the destination folder
                    foreach (var s3Object in listResponse.S3Objects)
                    {
                        var sourceKey = s3Object.Key;
                        var destinationKey = destinationPrefix;

                        CopyObjectRequest copyRequest = new CopyObjectRequest
                        {
                            SourceBucket = bucketNameforTenant,
                            SourceKey = sourceKey,
                            DestinationBucket = bucketNameforTenant,
                            DestinationKey = destinationKey
                        };

                        client.CopyObject(copyRequest);

                        DeleteObjectRequest deleteRequest = new DeleteObjectRequest
                        {
                            BucketName = bucketNameforTenant,
                            Key = sourceKey
                        };

                        client.DeleteObject(deleteRequest);
                    }

                    // Continue listing if there are more objects to retrieve
                    listRequest.ContinuationToken = listResponse.NextContinuationToken;
                } while (listResponse.IsTruncated);

                return true; // Indicate success
            }
            catch (AmazonS3Exception ex)
            {
                // Log exceptions or handle errors
                Console.WriteLine("S3 Error: " + ex.Message);
                return false; // Indicate failure
            }
        }

        public static string UploadROPhotoCV(System.IO.Stream fileStream, string fileName, string filePath)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            string rndFileName = Guid.NewGuid().ToString() + Path.GetExtension(fileName);
            rndFileName = rndFileName.Replace("..", ".").Replace("-", "");
            try
            {
                PutObjectRequest putRequest = new PutObjectRequest
                {
                    BucketName = bucketNameforProducts,
                    Key = filePath + rndFileName,
                    InputStream = fileStream,
                    CannedACL = S3CannedACL.PublicRead
                };
                putRequest.Metadata.Add("bucket", bucketNameforProducts);
                putRequest.Metadata.Add("mediaType", filePath.Contains("photo") ? "image" : "document");
                putRequest.Metadata.Add("filepath", filePath);

                PutObjectResponse response = client.PutObject(putRequest);

                if (response.HttpStatusCode != System.Net.HttpStatusCode.OK)
                {
                    throw new Exception("File upload failed.");
                }

                string relativeUrl = filePath + rndFileName;

                string imageLocation = ConfigurationManager.AppSettings["ImageLocation"];

                
                if (imageLocation.EndsWith("products/"))
                {
                    imageLocation = imageLocation.Replace("products/", "");
                }

                string s3Url = imageLocation + filePath + rndFileName;

                return s3Url;
            }
            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                    ||
                    amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }
        }

        public static void DeleteROPhotoCV(string filePath)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            try
            {
                // Create the delete request
                DeleteObjectRequest deleteRequest = new DeleteObjectRequest
                {
                    BucketName = bucketNameforProducts,
                    Key = filePath
                };

                // Execute the delete request
                DeleteObjectResponse response = client.DeleteObject(deleteRequest);

                // Check the response status
                if (response.HttpStatusCode != System.Net.HttpStatusCode.NoContent)
                {
                    throw new Exception("File deletion failed.");
                }
            }
            catch (AmazonS3Exception amazonS3Exception)
            {
                if (amazonS3Exception.ErrorCode != null &&
                    (amazonS3Exception.ErrorCode.Equals("InvalidAccessKeyId")
                    ||
                    amazonS3Exception.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + amazonS3Exception.Message);
                }
            }
            catch (Exception ex)
            {
                throw new Exception("An unexpected error occurred during file deletion: " + ex.Message);
            }
        }

        public static string UploadMerchantImage(Stream fileStream, string fileName, string filePath)
        {
            var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
            string rndFileName = Guid.NewGuid().ToString().Replace("-", "") + Path.GetExtension(fileName);
            string s3Key = $"{filePath.TrimEnd('/')}/{rndFileName}";

            try
            {
                var putRequest = new PutObjectRequest
                {
                    BucketName = bucketName,
                    Key = s3Key,
                    InputStream = fileStream,
                    CannedACL = S3CannedACL.PublicRead
                };

                putRequest.Metadata.Add("mediaType", "image");
                putRequest.Metadata.Add("filepath", filePath);

                var response = client.PutObject(putRequest);
                if (response.HttpStatusCode != System.Net.HttpStatusCode.OK)
                {
                    throw new Exception("Upload failed. Status: " + response.HttpStatusCode);
                }
                //string cdnBaseUrl = ConfigurationManager.AppSettings["CDNBaseUrl"];
                //return $"{cdnBaseUrl.TrimEnd('/')}/{s3Key}";
                string s3Url = $"https://{bucketName}.s3.{region}.amazonaws.com/{s3Key}";
                return s3Url;
            }
            catch (AmazonS3Exception ex)
            {
                if (ex.ErrorCode != null &&
                    (ex.ErrorCode.Equals("InvalidAccessKeyId") || ex.ErrorCode.Equals("InvalidSecurity")))
                {
                    throw new Exception("Check the provided AWS Credentials.");
                }
                else
                {
                    throw new Exception("Error occurred: " + ex.Message);
                }
            }
            finally
            {
                fileStream?.Close();
            }
        }


        public static async Task DeleteS3ImageAsync(string imageUrl)
        {
            try
            {
                Uri uri = new Uri(imageUrl);
                string s3Key = uri.AbsolutePath.TrimStart('/');

                var client = new AmazonS3Client(accessKeyId, accessSecret, Amazon.RegionEndpoint.GetBySystemName(region));
                var deleteRequest = new DeleteObjectRequest
                {
                    BucketName = bucketName,
                    Key = s3Key
                };

                await client.DeleteObjectAsync(deleteRequest);
            }
            catch (AmazonS3Exception ex)
            {
                throw new Exception("Failed to delete image from S3: " + ex.Message);
            }
        }

    }

}
