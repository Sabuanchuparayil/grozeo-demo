using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Retaline.Core.ViewModel.Home
{
    public class CookieConsentViewModel
    {
        [Required]
        public bool Necessary { get; set; }

        public bool Preferences { get; set; }

        public bool Statistics { get; set; }

        public bool Marketing { get; set; }
    }
}
