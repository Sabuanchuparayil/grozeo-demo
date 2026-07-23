using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class ctrlMenuNavigation : System.Web.UI.UserControl
    {
        public string StartingNode
        {
            get
            {
                return SMDataSource.StartingNodeUrl;
            }
            set
            {
                SMDataSource.StartingNodeUrl = value;
            }
        }
        public string StartingNodeTitle
        {
            get;
            set;
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            Repeater1.Visible = StartingNode != "~/";
        }

        protected void Repeater1_PreRender(object sender, EventArgs e)
        {
            if (Repeater1.Items.Count > 0 && !string.IsNullOrEmpty(StartingNodeTitle))
            {
                ltrMenuTitle.Text = StartingNodeTitle;
            }
        }

        public bool ShowParentNode(object url, object title)
        {
            //string strUrl = (string)url;
            bool isVisible = !(new string[] { StartingNode.Replace("~", ""), "/default" }).Contains(Eval("url").ToString());

            if (!isVisible && string.IsNullOrEmpty(StartingNodeTitle) && Eval("url").ToString() == StartingNode.TrimStart(new char[] { '~' }))
                StartingNodeTitle = title.ToString();

            return isVisible; //!(new string[] { StartingNode.Replace("~", ""), "/default" }).Contains(Eval("url").ToString());
        }

    }
}