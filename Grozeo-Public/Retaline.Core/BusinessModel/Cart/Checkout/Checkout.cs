using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Cart.Checkout
{
    public class Checkout
    {

        [JsonPropertyName("item_count")]
        public double ItemCount { get; set; }
        [JsonPropertyName("pricedetails")]
        public List<CouponLabel> PriceLabels { get; set; }
        [JsonPropertyName("nearest_retailer")]
        public int NearestRetailer { get; set; }
        [JsonPropertyName("shipping_method")]
        public int ShippingMethod { get; set; }
        [JsonPropertyName("prescription_id")]
        public object PrescriptionIds { get; set; }


        [JsonPropertyName("stock_available")]
        public bool StockAvailable { get; set; }

        [JsonPropertyName("sufficient_available")]
        public bool SufficientAvailable { get; set; }
        [JsonPropertyName("message")]
        public string Message { get; set; }
        //[JsonPropertyName("customer")]
        public Customer customer { get; set; }
        [JsonPropertyName("orders")]
        public List<CheckoutOrder> Orders { get; set; }
        //[JsonPropertyName("style")]
        public CheckoutStyle[] style { get; set; }
        //[JsonPropertyName("item")]
        public CartItemMaster[] Item { get; set; }
        [JsonPropertyName("wallet_balance")]
        public double WalletBalance { get; set; }
        [JsonPropertyName("delivery_details")]
        public Order.ShippingAddress shippingAddress { get; set; }
    }
}
