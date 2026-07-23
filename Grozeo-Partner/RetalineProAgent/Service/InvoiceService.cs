using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI.WebControls;


namespace RetalineProAgent.Service
{
    public class InvoiceService
    {
        public static class NumberTowords
        {
            public static string NumberToWords(string doubleNumber, string mainAmountType, string decimalAmountType)
            {
                int beforeFloatingPoint = Convert.ToInt32(Convert.ToString(doubleNumber).Split('.')[0]);
                string beforeFloatingPointWord = string.Format("{0} {1}", NumberToWords(beforeFloatingPoint), (beforeFloatingPoint > 1 ? mainAmountType : mainAmountType.TrimEnd(mainAmountType.Last())));

                string afterFloatingPointPart = Convert.ToString(doubleNumber).Contains('.') ? Convert.ToString(doubleNumber).Split('.')[1] : "0";

                // Ensure trailing zeroes are preserved for accurate conversion
                int afterFloatingPoint = Convert.ToInt32(afterFloatingPointPart);

                string afterFloatingPointWord = string.Format("{0} {1} Only.", SmallNumberToWord(afterFloatingPoint, ""), decimalAmountType);

                if (afterFloatingPoint > 0)
                {
                    return string.Format("{0} And {1}", beforeFloatingPointWord, afterFloatingPointWord);
                }
                else
                {
                    return string.Format("{0} Only", beforeFloatingPointWord);
                }
            }

            private static string NumberToWords(int number)
            {
                if (number == 0)
                    return "Zero";

                if (number < 0)
                    return "minus " + NumberToWords(Math.Abs(number));

                var words = "";

                if (number / 1000000000 > 0)
                {
                    words += NumberToWords(number / 1000000000) + " billion ";
                    number %= 1000000000;
                }

                if (number / 1000000 > 0)
                {
                    words += NumberToWords(number / 1000000) + " million ";
                    number %= 1000000;
                }

                if (number / 1000 > 0)
                {
                    words += NumberToWords(number / 1000) + " thousand ";
                    number %= 1000;
                }

                if (number / 100 > 0)
                {
                    words += NumberToWords(number / 100) + " hundred ";
                    number %= 100;
                }

                words = SmallNumberToWord(number, words);

                return words;
            }

            private static string SmallNumberToWord(int number, string words)
            {
                if (number <= 0) return words;
                if (words != "")
                    words += "";

                var unitsMap = new[] { "Zero", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen" };
                var tensMap = new[] { "Zero", "Ten", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety" };

                if (number < 20)
                    words += unitsMap[number];
                else
                {
                    words += tensMap[number / 10];
                    if ((number % 10) > 0)
                        words += " " + unitsMap[number % 10];
                }
                return words;
            }
        }
        public static string Generateinvoicetemplate(int order_id, int? type_id = null)
        {
            int TypeId = type_id ?? 0;
            string body = string.Empty;
            try
            {
                //  Fetch order data
                var orderData = FetchOrderData(order_id);
                if (orderData == null) return string.Empty;

                // Fetch item details
                var itemsData = FetchItemDetails(order_id);
                if (itemsData == null) return string.Empty;               
                // group order items (Restaurant and None restaurant items)
                // 1. Restaurant items
                List<DataRow> itemsWithRestaurantService = itemsData.AsEnumerable().Where(rows => Convert.ToInt32(rows["platform_tax_enabled"]) == 1 && Convert.ToInt32(rows["hasRestaurantService"]) == 1).ToList();
                // 2. Non Restaurant items
                List<DataRow> itemsWithoutService = itemsData.AsEnumerable().Where(rows => Convert.ToInt32(rows["platform_tax_enabled"]) != 1).ToList();
                // Generate Grozeo invoice if restaurant items.
                if (itemsWithRestaurantService != null && itemsWithRestaurantService.Count > 0)
                {   
                     body = GenerateInvoiceBody(itemsWithRestaurantService, itemsData, orderData,"1");
                }
                // Generate Merchant invoice if there are non-restaurant items  
                if (itemsWithoutService?.Count > 0)
                    body += GenerateInvoiceBody(itemsWithoutService, itemsData, orderData,"0");
                // Generate Grozeo to Customer invoice for Services  
                if (orderData.Rows[0]["delivery_rule_type"].ToString() == "1")
                    body += GenerateInvoiceBody(null, null, orderData, "2"); 
            }
            catch (Exception ex)
            {
                return "";
            }
            return body;
        }
        // Function to generate invoice body.
        private static string GenerateInvoiceBody(List<DataRow> itemsWithoutService, DataTable itemsData, DataTable orderData,string hasretaurent)
        {
            try
            {
                string body = string.Empty;
                int itemqty = 0;
                int itemcount = 0;
                if (itemsWithoutService != null && itemsWithoutService.Count > 0)
                {
                    itemqty = itemsData.AsEnumerable()
                       .Where(r => r["item_order_qty_scanned"] != DBNull.Value && !string.IsNullOrWhiteSpace(r["item_order_qty_scanned"].ToString()))
                       .Sum(r => Convert.ToInt32(r["item_order_qty_scanned"]));

                    itemcount = itemsWithoutService.Count;
                }
                StringBuilder sbItem = new StringBuilder();
                StringBuilder sbdeliverychargeItems = new StringBuilder();
                if (hasretaurent == "2")
                {
                    if(orderData.AsEnumerable().FirstOrDefault() is DataRow rows)
                    {
                        sbdeliverychargeItems.Append(GenerateItemDeliverychargeBody(rows));
                    }
                }
                else
                {
                    foreach (DataRow dritem in itemsWithoutService)
                    {
                        sbItem.Append(GenerateItemBody(dritem));
                    }
                }
                if (orderData.Rows[0]["delivery_rule_type"].ToString() == "3" && orderData.AsEnumerable().FirstOrDefault() is DataRow row)
                {
                    sbdeliverychargeItems.Append(GenerateItemDeliverychargeBody(row));
                }
                string partnerUrl = ConfigurationManager.AppSettings["partner.url"];
                string imageUrl = $"<img style=\"width: 150px; margin: 0;\" src=\"{Path.Combine(partnerUrl, "Content/template/images/logo.png")}\">";
                string datetime = DateTime.Now.ToString("ddMMyy");
                int order_id = Convert.ToInt32(orderData.Rows[0]["order_id"]);
                string invoiceno = ""; string date = "";
                string code = hasretaurent == "2" ? "C1" : itemsData.Rows[0]["hasRestaurantService"].ToString() == "1" ? orderData.Rows[0]["statecode"].ToString() : "B1";
                (invoiceno, date) = Getinvoicenumber(code, 1, order_id, datetime);
                string logo = orderData.Rows[0]["logo"].ToString();
                string logoReplace = string.IsNullOrEmpty(logo) ? ((hasretaurent == "1" || hasretaurent == "2") ? imageUrl : GetStoreLogo(orderData.Rows[0]["br_storeGroup"].ToString())) : $"<img style=\"width: 150px; margin: 0;\" src=\"{logo}\">";
                string storename = (hasretaurent == "1" || hasretaurent == "2") ? "Grozeo" : orderData.Rows[0]["storename"].ToString();
                string taxInvoice = (ConfigurationManager.AppSettings["CountryCode"] == "IN" && orderData.Rows[0]["taxType"].ToString() == "1") ? "TAX INVOICE" : (ConfigurationManager.AppSettings["CountryCode"] == "IN" ? "BILL OF SUPPLY" : "INVOICE");
                string taxNote = ConfigurationManager.AppSettings["CountryCode"] == "IN" ? (orderData.Rows[0]["taxType"].ToString() == "1" ? "Tax payable on reverse charge basis: No" : "Not Eligible to collect Tax on Supplies") : "";
                DateTime invoicestore = hasretaurent == "1" ? Convert.ToDateTime((date).ToString()) : Convert.ToDateTime(orderData.Rows[0]["order_confirm_date"].ToString());
                DateTime saleorder = Convert.ToDateTime(orderData.Rows[0]["order_confirm_date"].ToString());
                DateTime saledate = Convert.ToDateTime(orderData.Rows[0]["order_confirmed_on"].ToString());
                string grandtotal = orderData.Rows[0]["delivery_rule_type"]?.ToString() != "3" ? orderData.Rows[0]["subtotal"]?.ToString() ?? "0.00" : orderData.Rows[0]["total"]?.ToString() ?? "0.00";
                string igstValue = orderData.Rows[0]["delivery_rule_type"]?.ToString() == "3" ? (Convert.ToDecimal(orderData.Rows[0]["order_total_igst"] ?? 0) + Convert.ToDecimal(orderData.Rows[0]["order_delivery_charge_igst"] ?? 0)).ToString("0.00") : orderData.Rows[0]["order_total_igst"]?.ToString() ?? "0.00";
                string assablevalue = orderData.Rows[0]["delivery_rule_type"]?.ToString() != "3" ? orderData.Rows[0]["order_total_amount"]?.ToString() : (Convert.ToDecimal(orderData.Rows[0]["order_total_amount"] ?? 0) + Convert.ToDecimal(orderData.Rows[0]["order_delivery_charge_et"] ?? 0)).ToString();
                string totalCgst = orderData.Rows[0]["delivery_rule_type"]?.ToString() == "3" ? (Convert.ToDecimal(orderData.Rows[0]["order_total_cgst"] ?? 0) + Convert.ToDecimal(orderData.Rows[0]["order_delivery_charge_cgst"] ?? 0)).ToString("0.00") : orderData.Rows[0]["order_total_cgst"]?.ToString() ?? "0.00";
                string totalTax = (decimal.TryParse(orderData.Rows[0]["order_total_gst"]?.ToString(), out var gst) ? gst : 0 + (orderData.Rows[0]["delivery_rule_type"]?.ToString() == "3" && decimal.TryParse(orderData.Rows[0]["order_delivery_charge_gst"]?.ToString(), out var deliveryGst) ? deliveryGst : 0)).ToString("0.00");
                string totalSgst = orderData.Rows[0]["delivery_rule_type"]?.ToString() == "3" ? (Convert.ToDecimal(orderData.Rows[0]["order_total_cgst"] ?? 0) + Convert.ToDecimal(orderData.Rows[0]["order_delivery_charge_sgst"] ?? 0)).ToString("0.00") : orderData.Rows[0]["order_total_sgst"]?.ToString() ?? "0.00";
                string roundOff = Math.Abs(Convert.ToDecimal(orderData.Rows[0]["order_roundoff"] ?? 0)).ToString("0.00");
                string total = orderData.Rows[0]["delivery_rule_type"]?.ToString() != "3" ? orderData.Rows[0]["subtotal"]?.ToString() ?? "0.00" : orderData.Rows[0]["total"]?.ToString() ?? "0.00";
                string countryCode = ConfigurationManager.AppSettings["CountryCode"];
                var currencyMap = new Dictionary<string, (string Currency, string Subunit)>
                {
                    { "UK", ("Pounds", "Pence") },
                    { "IN", ("Rupees", "Paisa") }
                };
                var (currency, subunit) = currencyMap.TryGetValue(countryCode, out var val) ? val : (ConfigurationManager.AppSettings["CurrencySymbol"], " ");
                string totalAmount = (hasretaurent == "2"? orderData.Rows[0]["order_delivery_charge"]?.ToString(): (orderData.Rows[0]["delivery_rule_type"]?.ToString() != "3"? orderData.Rows[0]["subtotal"]?.ToString() ?? "0.00": orderData.Rows[0]["total"]?.ToString() ?? "0.00")); 
                string grandtotalwords = NumberTowords.NumberToWords(totalAmount, currency, subunit);

                string imageload = "", INVOICENO, TAXINVOICE = "", NOTELIGIBLETOCOLLECTTAXONSUPPLIES = "", INVOICEDATE = "", ORDERDATE = "", STORENAME = "",
                 INVOICEDTOSHIPPERTO = "", TOTALITEM = "", TOTALQUANTITY = "", GRANDTOTALROUND = "0.00", IGST = "", ASSESSABLEVALUE = "0.00", CGSTTOTAL = "0.00", SALEORDERDATE="",
                 GSTTOTAL = "0.00", SGSTTOTAL = "0.00", CESSTOTAL = "0.00", GRANDTOTAL = "0.00", INVOICETOTALINWORD = "0.00", ROUNDOFF = "0.00", ITEMREPEAT = "", ITEMDELIVERYCHARGE = "", VATTOTAL="0.00";

                var values = new
                {
                    imageload = logoReplace,
                    INVOICENO = invoiceno,
                    TAXINVOICE = taxInvoice,
                    NOTELIGIBLETOCOLLECTTAXONSUPPLIES = taxNote,
                    INVOICEDATE = invoicestore.ToString("dd MMM yyyy"),
                    ORDERDATE = saleorder.ToString("dd MMM yyyy"),
                    SALEORDERDATE = saledate.ToString("dd MMM yyyy:hh:mm:ss"),
                    INVOICEDTOSHIPPERTO = "Invoice to",
                    TOTALITEM = itemcount,
                    TOTALQUANTITY = itemqty ,
                    GRANDTOTALROUND = grandtotal,
                    IGST = igstValue,
                    ASSESSABLEVALUE = assablevalue,
                    CGSTTOTAL = totalCgst,
                    GSTTOTAL = totalTax,
                    VATTOTAL= totalTax,
                    SGSTTOTAL = totalSgst,
                    ROUNDOFF = roundOff,
                    GRANDTOTAL = total,
                    INVOICETOTALINWORD = grandtotalwords,
                    ITEMREPEAT = hasretaurent == "2"? sbdeliverychargeItems:sbItem,
                    ITEMDELIVERYCHARGE = sbdeliverychargeItems,
                    STORENAME = storename,
                };
                string template = EmailService.GetTemplateFile(hasretaurent == "1" ? EmailType.invoiceresturant : hasretaurent == "2" ? EmailType.invoiceservice : EmailType.invoice);
                StringBuilder sbItems = new StringBuilder();
                string templateFolderPath = "~/Content/template/";
                string fullTemplatePath = HttpContext.Current.Server.MapPath(templateFolderPath + template);
                string templateBody = File.ReadAllText(fullTemplatePath);
                string strItemBody = ReplacePlaceholders(templateBody, values);
                body = GetReplacePlaceholders(orderData.Rows[0], strItemBody);
                return body;
            }
            catch(Exception ex)
            {
                return "";
            }           
        }
        // generate invoice for Restaurant and services
        public static (string, string) Getinvoicenumber(string pmr_office_prefix, int pmr_invoice_type, int pmr_order_id, string pmr_date_format_prefix)
        {
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("pmr_order_id", pmr_order_id));
            prms.Add(new KeyValuePair<string, object>("pmr_office_prefix", pmr_office_prefix));
            prms.Add(new KeyValuePair<string, object>("pmr_invoice_type", pmr_invoice_type));
            prms.Add(new KeyValuePair<string, object>("pmr_date_format_prefix", pmr_date_format_prefix));
            DataTable invoice = DataServiceMySql.GetDataTable("getInvoiceNumber", UserService.GetAPIConnectionString(), prms, true);
            string invoicenumber = "";
            string date = "";
            if (invoice != null && invoice.Rows.Count > 0)
            {
                invoicenumber = invoice.Rows[0]["inv_number"].ToString();
                date = invoice.Rows[0]["created_at"].ToString();
            }
            return (invoicenumber, date);
        }       
        public static string ReplacePlaceholdersFromDataRow(DataRow row, string template)
        {
            return Regex.Replace(template, @"\[(.*?)\]", match =>
            {
                string key = match.Groups[1].Value;
                // Handle special keys
                switch (key)
                {                   
                    case "CC":
                        return ConfigurationManager.AppSettings["CountryCode"] != "IN"
                            ? " "
                            : $"&{row["hsnCess"]?.ToString()}%";
                    case "TAX":
                        var countryCode = ConfigurationManager.AppSettings["CountryCode"];
                        return countryCode == "AE" ? "5" :
                               countryCode == "IN" ? "18" : "20";
                }
                // Extract the column name from the key (after the first underscore)
                int firstUnderscore = key.IndexOf('_');
                string col = firstUnderscore >= 0 ? key.Substring(firstUnderscore + 1) : key;
                if (row.Table.Columns.Contains(col) && row[col] != DBNull.Value)
                {
                    var rawValue = row[col].ToString();

                    if (key.StartsWith("HSNSAC_", StringComparison.OrdinalIgnoreCase) || col.Equals("hsnGst", StringComparison.OrdinalIgnoreCase) ||col.Equals("hsnCess", StringComparison.OrdinalIgnoreCase))
                    {
                        return rawValue;
                    }


                    return decimal.TryParse(rawValue, out var num)
                        ? num.ToString("0.00")
                        : rawValue;
                }


                return ""; 
            });

        }
        private static  string GenerateItemBody(DataRow dritem)
        {
            StringBuilder sbItems = new StringBuilder();
            string template = EmailService.GetTemplateFile(EmailType.itemrepeat);
            string templateFolderPath = "~/Content/template/";  
            string fullTemplatePath = HttpContext.Current.Server.MapPath(templateFolderPath + template);
            string templateBody = File.ReadAllText(fullTemplatePath); 
            string strItemBody = ReplacePlaceholdersFromDataRow(dritem, templateBody);
            sbItems.Append(strItemBody);
            return sbItems.ToString();
        }
        //get delivery chage of item
        private static string GenerateItemDeliverychargeBody(DataRow drOrderInfo)
        {
            StringBuilder sbItems = new StringBuilder();
            string template = EmailService.GetTemplateFile(EmailType.itemdeliverycharge);
            string templateFolderPath = "~/Content/template/";
            string fullTemplatePath = HttpContext.Current.Server.MapPath(templateFolderPath + template);
            string templateBody = File.ReadAllText(fullTemplatePath);
            string strItemBody = ReplacePlaceholdersFromDataRow(drOrderInfo, templateBody);
            sbItems.Append(strItemBody);
            return sbItems.ToString();
        }
        // Get Order info
        private static DataTable FetchOrderData(int orderId)
        {
            try
            {
                List<KeyValuePair<string, object>> orderSqlprms = new List<KeyValuePair<string, object>>();
                orderSqlprms.Add(new KeyValuePair<string, object>("orderid", orderId));
                string sqlOrder = $"SELECT taxType,subtotal,branch_shortname,br_phone,delivery_rule_type,order_delivery_charge_gst,order_delivery_charge_et,order_delivery_charge,order_delivery_charge_cgst,order_delivery_charge_sgst,order_delivery_charge_utgst,order_delivery_charge_igst, IFNULL(re.order_invoiceno, 'N/A') AS order_invoiceno,order_total_igst,dst_Name,order_roundoff,fb.logo,br_storeGroup,(SELECT store_group_name FROM `finascop_branch_group` fg WHERE fg.store_group_id=fb.br_storeGroup LIMIT 1) as storename,order_roundoff,re.order_invoicedate," +
                    $"re.order_id,COALESCE(NULLIF(br_GST, ''), 'Not Available') AS br_GST,re.order_confirm_date, IFNULL(re.order_payment_gateway_refid,'Pay On Delivery') as order_payment_gateway_refid,CONCAT(fs.state_code,fb.branch_shortname)AS statecode,IFNULL(fs.st_name,' ') as st_name,order_order_id,re.total," +
                    $"br_City,br_address,order_confirmed_on,ro.order_customer_name,fs.state_code,fs.gst_state_code, " +
                    $"ro.order_contact_no,IFNULL(ro.order_city,' ') as order_city,ro.order_pin,ro.order_state,ro.order_country,br_name,storegroup_id,br_District,br_pincode,order_total_gst, " +
                    $"order_total_cgst,order_total_sgst,order_cess,order_total_amount,deli_delivery_pin,deli_city,order_state,deli_state,CONCAT(deli_district,' ') AS delivery_district , " +
                    $"CASE WHEN  taxType = 1 THEN 'Regular Tax Payer - Original for Recipient' WHEN taxType = 2 THEN 'Composite Tax Payer - Original for Recipient' WHEN taxType = 3 THEN 'Enrolled Tax Payer - Original for Recipient' " +
                    $" WHEN taxType = 4 THEN 'Unregistered Tax Payer - Original for Recipient' WHEN taxType = 4 THEN 'No GST - Original for Recipient'  WHEN taxType IS NULL THEN ' ' ELSE ' ' END AS taxnames,   " +
                    $"CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 'Online Payment' WHEN payment_mode = 3  THEN 'Wallet' WHEN payment_mode = 4 THEN 'COD With Wallet' WHEN payment_mode = 5 THEN 'Online With Wallet'" +
                    $" WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7  THEN 'Cash On Delivery' ELSE 'Payment is Not Avaliable' END AS payMode " +
                    $"FROM finascop_branch fb INNER JOIN retaline_customer_order re ON re.order_branch_id = fb.br_ID " +
                    $"left JOIN finascop_state fs  ON fs.st_ID = fb.br_State " +
                    $"INNER JOIN `retaline_customer_order_delivery_address` ro ON re.order_id = ro.customer_order_id " +
                    $" left JOIN retaline_customer_delivery_info rci ON rci.deli_id=ro.deli_id " +
                    $" left JOIN finascop_district fd ON fd.dst_Id=fb.br_District WHERE re.order_id = @orderid";
                DataTable dtOrderDetails = DataServiceMySql.GetDataTable(sqlOrder, Service.UserService.GetAPIConnectionString(), orderSqlprms);
                return dtOrderDetails;
            }
            catch(Exception ex)
            {

            }
            return null;
        }
        // Get order Items
        private static DataTable FetchItemDetails(int orderId)
        {
            try
            {
                List<KeyValuePair<string, object>> orderSqlprms = new List<KeyValuePair<string, object>>();
                orderSqlprms.Add(new KeyValuePair<string, object>("orderid", orderId));
                string itemdetails = $"SELECT customer_order_id,item_order_qty_scanned,platform_tax_enabled, hasRestaurantService,item_sales_price ,order_item_basket_price_et,IFNULL((SELECT fsi.fsipc_code FROM finascop_stock_itemmaster_product_codes fsi" +
                    $" WHERE fsi.fsipc_stit_id = fs.stit_ID  AND(fsi.fsipc_store = fb.br_ID OR fsipc_isCompany = 1) ORDER BY fsipc_store DESC LIMIT 1),'Not Applicable') " +
                    $" AS itemcode, order_item_mrp_et, stit_SKU, item_sales_price, order_item_mrp, IFNULL(item_order_qty_scanned, 0) AS item_order_qty_scanned, hsnGst, " +
                    $" hsnCess, item_price, order_item_seller_discount, stit_HSN_code  FROM retaline_customer_order re" +
                    $" INNER JOIN retaline_customer_order_items ro ON re.order_id = ro.customer_order_id" +
                    $" INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID" +
                    $" LEFT JOIN hsn_value hs ON hs.id = fs.taxValueId" +
                    $" INNER JOIN mypha_productsubcategory mp ON fs.product_category = mp.sub_category_id" +
                    $" INNER JOIN finascop_branch fb ON ro.order_branch_id = fb.br_ID" +
                    $" WHERE customer_order_id = @orderid and order_item_status in (1,2)";
                DataTable dtitems = DataServiceMySql.GetDataTable(itemdetails, Service.UserService.GetAPIConnectionString(), orderSqlprms);
                return dtitems;
            }
            catch(Exception ex)
            {

            }
          return null ;
        }
        //  method to fetch logo if not available in the order info
        private static string GetStoreLogo(string storeId)
        {
            List<KeyValuePair<string, object>> rms = new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storeid", storeId) };
            var images = "SELECT TOP 1 * FROM AppTenant WHERE StoreId = @storeid";
            DataTable dt = DataService.GetDataTable(images, parmeters: rms);
            if (dt.Rows.Count > 0 && !string.IsNullOrEmpty(dt.Rows[0]["LogoImage"]?.ToString()))
            {
                return $"<img style=\"width: 150px; margin: 0;\" src=\"{dt.Rows[0]["LogoImage"]}\">";
            }
            return string.Empty;
        }        
        // replacement of items  in services
        public static string ReplacePlaceholders(string template, object values)
        {
            return Regex.Replace(template, @"\[(.*?)\]", match =>
            {
                string key = match.Groups[1].Value;
                // Get the property from the values object
                var prop = values.GetType().GetProperty(key, BindingFlags.IgnoreCase | BindingFlags.Public | BindingFlags.Instance);
                // If property is found, return its value, otherwise return the original placeholder
                return prop != null ? prop.GetValue(values)?.ToString() ?? "" : match.Value;
            });
        }
        public static string GetReplacePlaceholders(DataRow row, string template)
        {
            return Regex.Replace(template, @"\[(.*?)\]", match =>
            {
                string key = match.Groups[1].Value;

                // Map placeholders to actual column names
                var columnMap = new Dictionary<string, string>(StringComparer.OrdinalIgnoreCase)
                {
                    { "ORDERNO_order_order_id", "order_order_id" },
                    { "INVOICENO_order_invoiceno", "order_invoiceno" },
                    {"PINSTORE_br_pincode","br_pincode" },
                    {"CODE_gst_state_code","gst_state_code" },
                    {"PIN3_deli_delivery_pin","deli_delivery_pin" },
                    {"CONTACTNUMBER_order_contact_no","order_contact_no" },                    
                };
               
                // Use mapped column name if available, else extract from key
                string col = columnMap.ContainsKey(key)
                    ? columnMap[key]
                    : (key.Contains("_") ? key.Substring(key.IndexOf('_') + 1) : key);
                if (row.Table.Columns.Contains(col) && row[col] != DBNull.Value)
                {
                    if (columnMap.ContainsKey(key))
                    {
                        return row[col].ToString();
                    }
                    // For others, format as decimal
                    return decimal.TryParse(row[col].ToString(), out var num)
                        ? num.ToString("0.00")
                        : row[col].ToString();
                }
                return "";
            });

        }

    }
}