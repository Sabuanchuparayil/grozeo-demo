using System.Collections.Generic;

namespace Retaline.Web.Models.Info
{
    public class PluginModel
    {
        public IList<Retaline.Core.BusinessModel.Home.RetalinePlugin> TenantPlugins { get; set; }
        public bool isHeader {  get; set; }
    }

}
