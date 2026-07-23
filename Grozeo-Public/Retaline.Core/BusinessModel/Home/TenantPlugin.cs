using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Home
{
	public class RetalinePlugin
	{
		public int Id { get; set; }

		public string Name { get; set; }
		public string Description { get; set; }

		public int TypeId { get; set; }

		public bool AllPages { get; set; }

		public string Key { get; set; }
		public List<TenantPlugin> TenantPlugins { get; set; }

	}

	public class TenantPlugin
	{
		public int TenantId { get; set; }
		public int PluginId { get; set; }
		public string Name { get; set; }
		public string Value { get; set; }
	}
}
