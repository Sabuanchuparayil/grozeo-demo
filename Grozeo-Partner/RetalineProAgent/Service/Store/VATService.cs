using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using QRCoder.Extensions;
using RetalineProAgent.Core.BussinessModel.Adhar;
//using RetalineProAgent.Core.BussinessModel.GST;
using RetalineProAgent.Core.BussinessModel.VAT;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.GST;

namespace RetalineProAgent.Service.Store
{
    public enum VATType
    {
        VAT=1,
        GST=2,
        TestVAT = 3,
        TestGST=4,
        PAN =5,
        TestPAN=6,
        NoVAT=7,
        Adhar=8,
        TestAdhar=9,
        TRN=10,
        TestTRN=11
    }
    public enum VatAPIResultType
    {
        InvalidVAT = 1,
        DuplicateVAT = 2,
        APIError = 3,
        Success = 4
    }
    public class VatResult
    {
        public bool Success { get; set; }
        public VatAPIResultType ResultType { get; set; }
        public string VAT { get; set; }
        public string Description { get; set; }
        public VATType VatType { get; set; }
        public Core.BussinessModel.VAT.VATData VatData { get; set; }
        public GSTValidationResult GstData { get; set; }
        public Core.BussinessModel.PAN.PANInfo PanData { get; set; }
        public AdharVerificationData AdharData { get; set; }
        public AdharInfo AdharInfo { get; set; }
        public TRNData TRNData { get; set; }

    }

    public class VATService
    {
        public VatResult ValidateVAT(string vat)
        {
            //string strGSTPattern = "[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}";
            if (string.IsNullOrEmpty(vat) || vat.Length < 9) // || !Regex.Match(vat, strGSTPattern, RegexOptions.IgnoreCase).Success)
            {
                return new VatResult { Success = false, ResultType = VatAPIResultType.InvalidVAT, Description = "Invalid VAT", VatType = VATType.VAT };
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("vat", vat));

            Core.BussinessModel.VAT.VATData vatData = null;
            bool isTestVAT = false;
            try
            {
                var tblTestGST = DataService.GetDataTable("SELECT * FROM GSTLog WHERE gstin = @vat ORDER BY id desc", parmeters: prms);
                if (tblTestGST != null && tblTestGST.Rows.Count > 0)
                {
                    vatData = System.Text.Json.JsonSerializer.Deserialize<Core.BussinessModel.VAT.VATData>((string)tblTestGST.Rows[0]["gstdata"]);
                    isTestVAT = Convert.ToBoolean(tblTestGST.Rows[0]["is_test_gst"]);
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "VAT validated successfully", VatType = (isTestVAT ? VATType.TestVAT : VATType.VAT), VatData=vatData };
                }
            }
            catch (Exception exGST)
            {

            }

            if (vatData == null || !isTestVAT)
            {
                var tblDuplicateVAT = DataService.GetDataTable("SELECT * FROM StoreBranch WHERE GSTIN = @vat", parmeters: prms);
                if (tblDuplicateVAT != null && tblDuplicateVAT.Rows.Count > 0)
                {
                    return new VatResult { Success = false, ResultType = VatAPIResultType.DuplicateVAT, Description = "Duplicate VAT. The VAT is already used.", VatType = VATType.VAT };
                }
            }

            if (vatData == null)
            {
                vatData = Core.Services.APIService.ValidateVAT(vat);
                if (vatData != null)
                {
                    try
                    {

                        List<KeyValuePair<string, object>> prmsInsertGSTLog = new List<KeyValuePair<string, object>>();
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gstin", vatData.vat_number));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("email", ""));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("mobile", ""));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("organization", vatData.company_name));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("address", vatData.company_address));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("is_test_gst", 0));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gstdata", System.Text.Json.JsonSerializer.Serialize(vatData)));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("type", 1));
                        DataService.ExecuteSql("INSERT INTO GSTLog (gstin, email, mobile, organization, address, is_test_gst, gstdata, [type]) VALUES(@gstin, @email, @mobile, @organization, @address, @is_test_gst, @gstdata, @type)", parmeters: prmsInsertGSTLog);

                    }
                    catch { }
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "VAT validated successfully", VatType = VATType.VAT, VatData= vatData };
                }
            }
            return new VatResult { Success = false, ResultType = VatAPIResultType.APIError, Description = "Invalid VAT or VAT master data access is not available.", VatType = VATType.VAT };

        }

        public VatResult ValidateGST(string gstin)
        {
            //string strGSTPattern = "[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}";
            //if (string.IsNullOrEmpty(gstin) || !Regex.Match(gstin, strGSTPattern, RegexOptions.IgnoreCase).Success)
            //{
            //    return new VatResult { Success = false, ResultType = VatAPIResultType.InvalidVAT, Description = "Invalid GSTIN", VatType = VATType.GST };
            //}
            if (!ValidateGSTChecksum(gstin))
            {
                return new VatResult { Success = false, ResultType = VatAPIResultType.InvalidVAT, Description = "Invalid GSTIN account", VatType = VATType.GST };
            }
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("gst", gstin));

            GSTValidationResult gstResult = null;
            bool isTestGST = false;
            try
            {
                var tblTestGST = DataService.GetDataTable("SELECT * FROM GSTLog WHERE gstin = @gst ORDER BY id desc", parmeters: prms);
                if (tblTestGST != null && tblTestGST.Rows.Count > 0)
                {
					gstResult = new GSTValidationResult();
					gstResult.GSTIN = tblTestGST.Rows[0]["gstin"].ToString();
					gstResult.TradeName = tblTestGST.Rows[0]["organization"].ToString();
					gstResult.Address = tblTestGST.Rows[0]["address"].ToString();
					gstResult.Email = tblTestGST.Rows[0]["email"].ToString();
					gstResult.Mobile = tblTestGST.Rows[0]["mobile"].ToString();
					try { gstResult.RawResponse = tblTestGST.Rows[0]["gstdata"].ToString(); } catch { }

                    try { isTestGST = Convert.ToBoolean(tblTestGST.Rows[0]["is_test_gst"]); } catch { isTestGST = false; }
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "GSTIN validated successfully", VatType = (isTestGST? VATType.TestGST : VATType.GST), GstData= gstResult };
                }
            }
            catch (Exception exGST)
            {

            }

            if (gstResult == null || !isTestGST)
            {
                var tblDuplicateGST = DataService.GetDataTable("SELECT * FROM StoreBranch WHERE GSTIN = @gst", parmeters: prms);
                if (tblDuplicateGST != null && tblDuplicateGST.Rows.Count > 0)
                {
                    return new VatResult { Success = false, ResultType = VatAPIResultType.DuplicateVAT, Description = "Duplicate GST. The GSTIN is already used.", VatType = VATType.GST };
                }
            }

            if (gstResult == null)
            {
                gstResult = (new GSTValidatorService()).ValidateGST(gstin);

                if (gstResult != null && !string.IsNullOrEmpty(gstResult.GSTIN))
                {
                    try
                    {
                        List<KeyValuePair<string, object>> prmsInsertGSTLog = new List<KeyValuePair<string, object>>();
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gstin", gstResult.GSTIN));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("email", gstResult.Email));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("mobile", gstResult.Mobile));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("organization", gstResult.TradeName));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("address", gstResult.Address));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("is_test_gst", 0));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gdata", gstResult.RawResponse.ToString()));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("type", 1));
                        DataService.ExecuteSql("INSERT INTO GSTLog (gstin, email, mobile, organization, address, is_test_gst, gstdata, [type]) VALUES(@gstin, @email, @mobile, @organization, @address, @is_test_gst, @gdata, @type)", parmeters: prmsInsertGSTLog);

                    }
                    catch { }
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "GSTIN validated successfully", VatType = VATType.GST, GstData=gstResult };
                }
            }
            return new VatResult { Success = false, ResultType = VatAPIResultType.APIError, Description = "Invalid GST number or GST master data access is not available.", VatType = VATType.GST };


        }

        public VatResult ValidatePAN(string pan)
        {
            // [A-Z]{5}\d{4}[A-Z]{1} 
            //[A-Z]{5}[0-9]{4}[A-Z]{1} 
            string strGSTPattern = "[A-Z]{5}[0-9]{4}[A-Z]{1}";
            if (string.IsNullOrEmpty(pan) || !Regex.Match(pan, strGSTPattern, RegexOptions.IgnoreCase).Success)
            {
                return new VatResult { Success = false, ResultType = VatAPIResultType.InvalidVAT, Description = "Invalid PAN", VatType = VATType.PAN };
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("pan", pan));

            Core.BussinessModel.PAN.PANInfo panData = null;
            bool isTestPAN = false;
            try
            {
                var tblTestGST = DataService.GetDataTable("SELECT * FROM PANLog WHERE pan = @pan ORDER BY id desc", parmeters: prms);
                if (tblTestGST != null && tblTestGST.Rows.Count > 0)
                {
                    panData = System.Text.Json.JsonSerializer.Deserialize<Core.BussinessModel.PAN.PANInfo>((string)tblTestGST.Rows[0]["pandata"]);
                    isTestPAN = Convert.ToBoolean(tblTestGST.Rows[0]["is_test_pan"]);
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "PAN validated successfully", VatType = (isTestPAN ? VATType.TestPAN : VATType.PAN), PanData = panData };
                }
            }
            catch (Exception exGST)
            {

            }

            if (panData == null || !isTestPAN)
            {
                var tblDuplicatePAN = DataService.GetDataTable("SELECT * FROM Store WHERE PAN = @pan", parmeters: prms);
                if (tblDuplicatePAN != null && tblDuplicatePAN.Rows.Count > 0)
                {
                    return new VatResult { Success = false, ResultType = VatAPIResultType.DuplicateVAT, Description = "Duplicate PAN. The PAN is already used.", VatType = VATType.PAN };
                }
            }

            if (panData == null)
            {
                panData = Core.Services.APIService.GetPANDetails(pan);
                if (panData != null)
                {
                    try
                    {
                        List<KeyValuePair<string, object>> prmsInsertGSTLog = new List<KeyValuePair<string, object>>();
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("pan", panData.result.essentials.number));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("name", panData.result.result.name));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("is_test_pan", 0));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("pandata", System.Text.Json.JsonSerializer.Serialize(panData)));
                        DataService.ExecuteSql("INSERT INTO PANLog (pan, [name], is_test_pan, pandata) VALUES(@pan, @name, @is_test_pan, @pandata)", parmeters: prmsInsertGSTLog);

                    }
                    catch { }
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "PAN validated successfully", VatType = VATType.PAN, PanData = panData };
                }
            }
            return new VatResult { Success = false, ResultType = VatAPIResultType.APIError, Description = "Invalid PAN or PAN master data access is not available.", VatType = VATType.PAN };

        }

        public bool ValidateGSTChecksum(string gstNumber)
        {
            // Ensure that the GST number is in the correct format
            //if (!IsValidGSTFormat(gstNumber))
            //{
            //    throw new ArgumentException("Invalid GST Number Format");
            //}

            // Check if the GST number has at least 15 characters
            if (gstNumber.Length < 15)
            {
                return false;
                //throw new ArgumentException("GST Number is too short");
            }

            // Extract the first 14 digits of the GST number
            string first14Digits = gstNumber.Substring(0, 14);

            // Calculate the checksum
            int total = 0;
            int[] weights = { 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2 };

            for (int i = 0; i < 14; i++)
            {
                char currentChar = first14Digits[i];
                int digit = -1;
                if (!char.IsDigit(currentChar))
                {
                    int position = (int)currentChar - (int)'A' + 1;
                    digit = position + 9;
                }
                else
                {
                    digit = int.Parse(currentChar.ToString());
                }

                if (digit >= 0)
                {
                    int weightedDigit = digit * weights[i];
                    int quotientval = Math.Abs(weightedDigit / 36);
                    int modval = weightedDigit % 36;

                    int calcval = quotientval + modval;

                    total += calcval;
                }
                else
                {
                    throw new ArgumentException("Invalid character in GST Number");
                }
            }

            char[] strChecksum = "000000000ABCDEFGHIJKLMNOPQRSTUVWXYZ0".ToCharArray();
            int chksumModval = total % 36;
            int finalR = 36 - chksumModval;

            if (finalR <= 0 || finalR > 36)
                return false;

            // Calculate the checksum character
            char retVal = (finalR <= 9 ? finalR.ToString().ToCharArray()[0] : strChecksum[finalR - 1]);

            return gstNumber.EndsWith(retVal.ToString());
            //return retVal;

        }

        public VatResult ValidateAdhar(string adhar)
        {
            // [A-Z]{5}\d{4}[A-Z]{1} 
            //[A-Z]{5}[0-9]{4}[A-Z]{1} 
            string strGSTPattern = "^[2-9]{1}[0-9]{11}$";//"[A-Z]{5}[0-9]{4}[A-Z]{1}";
            if (string.IsNullOrEmpty(adhar) || !Regex.Match(adhar, strGSTPattern, RegexOptions.IgnoreCase).Success)
            {
                return new VatResult { Success = false, ResultType = VatAPIResultType.InvalidVAT, Description = "Invalid Aadhaar", VatType = VATType.Adhar };
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("adhar", adhar));

            Core.BussinessModel.Adhar.AdharVerificationData adharData = null;
            try
            {
                var tblTestGST = DataService.GetDataTable("SELECT * FROM AdharLog WHERE adhar = @adhar ORDER BY id desc", parmeters: prms);
                if (tblTestGST != null && tblTestGST.Rows.Count > 0)
                {
                    return new VatResult { Success = false, ResultType = VatAPIResultType.DuplicateVAT, Description = "Duplicate Aadhaar. The Aadhaar is already used.", VatType = VATType.Adhar };
                }
            }
            catch (Exception exGST)
            {

            }

            if (adharData == null)
            {
                var adharResult = Core.Services.APIService.GetAdharDetails(adhar);

                if (adharResult != null && adharResult.data != null)
                {                    
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "Aadhaar validated successfully", VatType = VATType.Adhar, AdharData = adharResult.data };
                }
            }
            return new VatResult { Success = false, ResultType = VatAPIResultType.APIError, Description = "Invalid Aadhaar or Aadhaar data access is not available.", VatType = VATType.PAN };

        }

        public VatResult VerifyAdhar(string adhar, string clientid, string otp)
        {
            string strGSTPattern = "^[2-9]{1}[0-9]{11}$";//"[A-Z]{5}[0-9]{4}[A-Z]{1}";
            if (string.IsNullOrEmpty(clientid) || string.IsNullOrEmpty(otp))
            {
                return new VatResult { Success = false, ResultType = VatAPIResultType.InvalidVAT, Description = "Invalid Verification", VatType = VATType.Adhar };
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("adhar", adhar));

            Core.BussinessModel.Adhar.AdharInfo adharData = null;
            bool isTestAdhar = false;
            try
            {
                var tblTestGST = DataService.GetDataTable("SELECT * FROM AdharLog WHERE adhar = @adhar ORDER BY id desc", parmeters: prms);
                if (tblTestGST != null && tblTestGST.Rows.Count > 0)
                {
                    return new VatResult { Success = false, ResultType = VatAPIResultType.DuplicateVAT, Description = "Duplicate Aadhaar. The Aadhaar is already used.", VatType = VATType.Adhar };
                }
            }
            catch (Exception exGST)
            {

            }

            if (adharData == null)
            {
                var adharResult = Core.Services.APIService.VerifyAdhar(clientid, otp);

                if (adharResult != null && adharResult.data != null)
                {
                    adharData = adharResult.data;
                    try
                    {
                        List<KeyValuePair<string, object>> prmsInsertGSTLog = new List<KeyValuePair<string, object>>();
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("adhar", adhar));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("name", adharData.full_name));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("adhardata", System.Text.Json.JsonSerializer.Serialize(adharData)));
                        object result = DataService.ExecuteScalar("INSERT INTO AdharLog (adhar, [name], adhardata) VALUES(@adhar, @name, @adhardata); select scope_identity();", parmeters: prmsInsertGSTLog);
                        if (result != null)
                            adharData.id = Convert.ToInt32(result);
                    }
                    catch { }
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "Aadhaar validated successfully", VatType = VATType.Adhar, AdharInfo = adharData };
                }
            }
            return new VatResult { Success = false, ResultType = VatAPIResultType.APIError, Description = "Invalid Aadhaar or Aadhaar data access is not available.", VatType = VATType.Adhar };

        }

        public static VatResult ValidateTRN(string trn)
        {
            //string strGSTPattern = "[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}";
            if (string.IsNullOrEmpty(trn) || trn.Length < 15 || !trn.StartsWith("100")) // || !Regex.Match(vat, strGSTPattern, RegexOptions.IgnoreCase).Success)
            {
                return new VatResult { Success = false, ResultType = VatAPIResultType.InvalidVAT, Description = "Invalid VAT", VatType = VATType.VAT };
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("trn", trn));

            Core.BussinessModel.VAT.TRNData trnData = null;
            bool isTestTRN = false;
            try
            {
                var tblTestTRN = DataService.GetDataTable("SELECT * FROM GSTLog WHERE gstin = @trn ORDER BY id desc", parmeters: prms);
                if (tblTestTRN != null && tblTestTRN.Rows.Count > 0)
                {
                    trnData = System.Text.Json.JsonSerializer.Deserialize<Core.BussinessModel.VAT.TRNData>((string)tblTestTRN.Rows[0]["gstdata"]);
                    isTestTRN = Convert.ToBoolean(tblTestTRN.Rows[0]["is_test_gst"]);
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "VAT validated successfully", VatType = (isTestTRN ? VATType.TestTRN : VATType.TRN), TRNData = trnData };
                }
            }
            catch (Exception exGST)
            {

            }

            if (trnData == null || !isTestTRN)
            {
                var tblDuplicateTRN = DataService.GetDataTable("SELECT * FROM StoreBranch WHERE GSTIN = @trn", parmeters: prms);
                if (tblDuplicateTRN != null && tblDuplicateTRN.Rows.Count > 0)
                {
                    return new VatResult { Success = false, ResultType = VatAPIResultType.DuplicateVAT, Description = "Duplicate TRN. The TRN is already used.", VatType = VATType.TRN };
                }
            }

            if (trnData == null)
            {
                trnData = Core.Services.APIService.ValidateTRN(trn);
                if (trnData != null && trnData.trn_status == true)
                {
                    try
                    {

                        List<KeyValuePair<string, object>> prmsInsertGSTLog = new List<KeyValuePair<string, object>>();
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gstin", trnData.trn_number));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("email", ""));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("mobile", ""));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("organization", trnData.legal_name));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("address", ""));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("is_test_gst", 0));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gdata", System.Text.Json.JsonSerializer.Serialize(trnData)));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("type", 10));
                        DataService.ExecuteSql("INSERT INTO GSTLog (gstin, email, mobile, organization, address, is_test_gst, gstdata, [type]) VALUES(@gstin, @email, @mobile, @organization, @address, @is_test_gst, @gdata, @type)", parmeters: prmsInsertGSTLog);

                    }
                    catch { }
                    return new VatResult { Success = true, ResultType = VatAPIResultType.Success, Description = "TRN validated successfully", VatType = VATType.TRN, TRNData = trnData };
                }
            }
            return new VatResult { Success = false, ResultType = VatAPIResultType.APIError, Description = "Invalid TRN or TRN master data access is not available.", VatType = VATType.TRN };

        }

        public static string VATLabel
        {
            get
            {
                VATType vatType = (VATType)(String.IsNullOrEmpty(ConfigurationManager.AppSettings.Get("VATType")) ? 0 : Convert.ToInt32(ConfigurationManager.AppSettings.Get("VATType")));
                return Enum.GetName(typeof(VATType), vatType);
            }
        }
    }
}