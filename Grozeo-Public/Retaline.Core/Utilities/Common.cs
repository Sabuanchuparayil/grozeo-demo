using LazyCache;
using Retaline.Core.BusinessModel.Home;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Security.Cryptography;
using System.Text;
using System.Threading.Tasks;

namespace Retaline.Core.Utilities
{
    
    public static class Common
    {
        public static string TenantsCacheKey => "RETALINE-Active-Tenants";
        private static IAppCache _cache = new CachingService();
        public static IAppCache Cache { 
            get
            {
                if(_cache == null)
                    _cache = new CachingService();
                return _cache;
            } 
        }

        /// <summary>
        /// Clear cache
        /// </summary>
        public static void ClearCache()
        {
            Cache.Remove(TenantsCacheKey);
        }

        public static string GetProductImage(ItemMaster itemMaster, bool isThumb=false)
        {
            string strDefaultImageUrl = "/images/p-no-image.png";

            if (itemMaster != null && itemMaster.MainImage != null && itemMaster.MainImage.Count > 0)
            {
                if (!isThumb && !String.IsNullOrEmpty(itemMaster.MainImage[0].ImageUrl))
                    return itemMaster.MainImage[0].ImageUrl;
                else if(isThumb && !String.IsNullOrEmpty(itemMaster.MainImage[0].ImageThumbUrl))
                    return itemMaster.MainImage[0].ImageThumbUrl;
            }
            return strDefaultImageUrl;
        }

        public static string GetProductImage(List<ItemMaster> itemMasters, bool isThumb = false)
        {
            string strDefaultImageUrl = "/images/p-no-image.png";

            if (itemMasters != null && itemMasters.Count > 0)
            {
                return GetProductImage(itemMasters[0], isThumb);
            }
            return strDefaultImageUrl;
        }

        // TODO: This is NOT real encryption — it is Base64 encoding only. Replace with actual AES encryption before relying on this for confidentiality.
        public static string EncodeBase64String(string plainText)//, byte[] key, byte[] iv)
        {
            byte[] plainBytes = Encoding.ASCII.GetBytes(plainText);
            return Convert.ToBase64String(plainBytes);

            //byte[] iv2 = Encoding.ASCII.GetBytes("5fgf5HJ5g27");
            byte[] iv = new byte[16] { 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0 };
            SHA256 mySHA256 = SHA256Managed.Create();
            byte[] key = mySHA256.ComputeHash(Encoding.ASCII.GetBytes("5fgf5HJ5g27"));//("5ADA4F9AB91A24ABB5756D1B6B2FC"));

            // Instantiate a new Aes object to perform string symmetric encryption
            Aes encryptor = Aes.Create();

            encryptor.Mode = CipherMode.CBC;

            // Set key and IV
            encryptor.Key = key;
            encryptor.IV = iv;

            // Instantiate a new MemoryStream object to contain the encrypted bytes
            MemoryStream memoryStream = new MemoryStream();

            // Instantiate a new encryptor from our Aes object
            ICryptoTransform aesEncryptor = encryptor.CreateEncryptor();

            // Instantiate a new CryptoStream object to process the data and write it to the 
            // memory stream
            CryptoStream cryptoStream = new CryptoStream(memoryStream, aesEncryptor, CryptoStreamMode.Write);

            // Convert the plainText string into a byte array
            //byte[] plainBytes = Encoding.ASCII.GetBytes(plainText);

            // Encrypt the input plaintext string
            cryptoStream.Write(plainBytes, 0, plainBytes.Length);

            // Complete the encryption process
            cryptoStream.FlushFinalBlock();

            // Convert the encrypted data from a MemoryStream to a byte array
            byte[] cipherBytes = memoryStream.ToArray();

            // Close both the MemoryStream and the CryptoStream
            memoryStream.Close();
            cryptoStream.Close();

            // Convert the encrypted byte array to a base64 encoded string
            string cipherText = Convert.ToBase64String(cipherBytes, 0, cipherBytes.Length);

            // Return the encrypted data as a string
            return cipherText;
        }

        //public static List<ItemMaster> GetDistinctItemMaster(List<HomeValue> homeValues)
        //{
        //    var list = (from parent in homeValues
        //                select parent into p
        //                from child in p.ItemMaster
        //                select child).Distinct().ToList();
        //    return list;
        //}

        /// <summary>
        /// Ensure that a string doesn't exceed maximum allowed length
        /// </summary>
        /// <param name="str">Input string</param>
        /// <param name="maxLength">Maximum length</param>
        /// <param name="postfix">A string to add to the end if the original string was shorten</param>
        /// <returns>Input string if its length is OK; otherwise, truncated input string</returns>
        public static string EnsureMaximumLength(string str, int maxLength, string postfix = null)
        {
            if (string.IsNullOrEmpty(str))
                return str;

            if (str.Length <= maxLength)
                return str;

            var pLen = postfix?.Length ?? 0;

            var result = str[0..(maxLength - pLen)];
            if (!string.IsNullOrEmpty(postfix))
            {
                result += postfix;
            }

            return result;
        }

    }
}
