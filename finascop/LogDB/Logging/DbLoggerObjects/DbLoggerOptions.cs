using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace LogDB.Logging.DbLoggerObjects
{
    public class DbLoggerOptions
    {
        public string ConnectionString { get; set; }

        public string[] LogFields { get; set; }

        public string LogTable { get; set; }

        public DbLoggerOptions()
        {
        }
    }
}
