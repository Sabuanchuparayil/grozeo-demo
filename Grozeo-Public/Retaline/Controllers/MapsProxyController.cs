using Microsoft.AspNetCore.Mvc;
using System.Net.Http;
using System.Threading.Tasks;
using Microsoft.Extensions.Configuration;
using System;
using System.Linq; // To access app settings or Key Vault values


namespace Retaline.Web.Controllers
{
    [ApiController]
    [Route("api/maps")]
    public class MapsProxyController : ControllerBase
    {
        private readonly HttpClient _httpClient;
        private readonly IConfiguration _configuration;
        private readonly string _googleMapsApiKey;
        private readonly Core.ViewModel.Tenant.AppTenant tenant;

        public MapsProxyController(HttpClient httpClient, IConfiguration configuration, 
            SaasKit.Multitenancy.ITenant<Core.ViewModel.Tenant.AppTenant> tenant)
        {
            _httpClient = httpClient;
            _configuration = configuration;
            this.tenant = tenant?.Value;

            //this.tenant.Hostnames.Contains(Request.)

            // In Azure App Service, app settings are treated as environment variables
            // and can pull values directly from Key Vault if configured via "Reference".
            _googleMapsApiKey = _configuration["GoogleAPI"];
        }

        // Proxy for Maps JavaScript API loading
        [HttpGet("js")]
        public async Task<IActionResult> GetMapsJs([FromQuery] string v = "weekly", [FromQuery] string region = "US", [FromQuery] string libraries = "")
        {
            if (string.IsNullOrEmpty(_googleMapsApiKey))
            {
                return StatusCode(500, "Google Maps API Key not configured.");
            }

            //string googleApiUrl=$"https://maps.googleapis.com/maps/api/js?key={_googleMapsApiKey}&v={v}&libraries=places&callback=mapdummyfn
            // Construct the Google Maps JavaScript API URL
            string googleApiUrl = $"https://maps.googleapis.com/maps/api/js?key={_googleMapsApiKey}&v={v}&libraries=places&callback=mapdummyfn"; // &region={region}
            googleApiUrl = $"https://maps.googleapis.com/maps/api/js?key={_googleMapsApiKey}&libraries=places&v=weekly&callback=mapdummyfn";

            if (!string.IsNullOrEmpty(libraries))
            {
                googleApiUrl += $"&libraries={libraries}";
            }

            // Forward the request to Google Maps
            var response = await _httpClient.GetAsync(googleApiUrl);

            // Read the content as a string
            var content = await response.Content.ReadAsStringAsync();

            // Important: Replace any hardcoded URLs or API keys in the JS content if necessary
            // (though Google's JS usually handles relative paths well)
            // This is a basic example; for full-fledged proxying, consider more robust content manipulation
            // especially if you want to replace map tiles URLs as well. For the JS API itself, this is usually enough.

            // Return the content with the correct Content-Type
            return Content(content, "application/javascript");
        }

        // Example proxy for Geocoding API
        [HttpGet("geocode")]
        public async Task<IActionResult> Geocode([FromQuery] string address)
        {
            if (string.IsNullOrEmpty(_googleMapsApiKey))
            {
                return StatusCode(500, "Google Maps API Key not configured.");
            }
            if (string.IsNullOrEmpty(address))
            {
                return BadRequest("Address parameter is required.");
            }

            string googleGeocodeUrl = $"https://maps.googleapis.com/maps/api/geocode/json?address={Uri.EscapeDataString(address)}&key={_googleMapsApiKey}";

            var response = await _httpClient.GetAsync(googleGeocodeUrl);
            response.EnsureSuccessStatusCode(); // Throws if not a success status code

            var content = await response.Content.ReadAsStringAsync();
            return Content(content, "application/json");
        }

        // You would add similar methods for Places Autocomplete, Directions, etc.
    }
}
