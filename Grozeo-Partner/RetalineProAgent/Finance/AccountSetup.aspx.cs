using Microsoft.Ajax.Utilities;
using NPOI.POIFS.Properties;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class AccountSetup: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                LoadTree();
            }
        }

        private void LoadTree()
        {
            DataView view = (DataView)SDSGroups.Select(DataSourceSelectArguments.Empty);
            DataTable dtGroupsLedgers = view.ToTable();

            DataView viewAcTypes = (DataView)SDSAccountTypes.Select(DataSourceSelectArguments.Empty);
            DataTable dtAcTypes = viewAcTypes.ToTable();

            if (dtGroupsLedgers != null && dtGroupsLedgers.Rows.Count > 0 && dtAcTypes != null && dtAcTypes.Rows.Count > 0)
            {
                foreach (var dr in dtAcTypes.AsEnumerable().Select(c => c))
                {
                    int natureid = 0; try { natureid = Convert.ToInt32(dr["id"]); } catch { natureid = 0; }
                    if (natureid <= 0)
                        continue;

                    TreeNode actChildNode = new TreeNode(String.Format("<span gid='0_" + natureid + "' class='text-warning "+(!String.IsNullOrEmpty(txtSearch.Text) && dr["nature"].ToString().ToLower().Contains(txtSearch.Text.ToLower())  ? "mark" : "")+"'>{0}</span>", dr["nature"]), "0");
                    actChildNode.SelectAction = TreeNodeSelectAction.None;
                    TreeView1.Nodes.Add(actChildNode);
                    if (!String.IsNullOrEmpty(txtSearch.Text) && dr["nature"].ToString() == txtSearch.Text)                        
                    Expandtree(actChildNode);                    

                    foreach (var drg in dtGroupsLedgers.AsEnumerable().Where(c => Convert.ToInt32(c["typeid"]) == natureid && Convert.ToInt32(c["parent_id"]) == 0).Select(c => c))
                    {
                        int id = Convert.ToInt32(drg["id"]);
                        int type = Convert.ToInt32(drg["actype"]);
                        string css = (type == 2 ? "text-success" : "text-info");

                        TreeNode childNode = new TreeNode(String.Format("<span gid='1_" + id + "' class='{0} "+(!String.IsNullOrEmpty(txtSearch.Text) && drg["name"].ToString().ToLower().Contains(txtSearch.Text.ToLower())   ? "mark" : "")+"'>{1}</span>", css, drg["name"]), id.ToString());
                        //if(type == 0)
                        //    childNode.
                        
                        childNode.SelectAction = TreeNodeSelectAction.None;
                        actChildNode.ChildNodes.Add(childNode);
                        if (!String.IsNullOrEmpty(txtSearch.Text) && drg["name"].ToString() == txtSearch.Text)                            
                          Expandtree(childNode);
                        BindTree(dtGroupsLedgers, id, childNode);

                    }
                }

                //int parentId = Convert.ToInt32(dtGroupsLedgers.Rows[0]["parent_id"]);
                //BindTree(dtGroupsLedgers, parentId);
            }
        }

        private void BindTree(DataTable dt, int parentId, TreeNode tn = null)
        {
            foreach (var dr in dt.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == parentId).Select(c => c))
            {
                int id = Convert.ToInt32(dr["id"]);
                int type = Convert.ToInt32(dr["actype"]);
                string css = (type == 0 ? "text-warning" : ( type== 2 ? "text-success" : "text-info"));
                string gid = String.Format("{0}_{1}", type, id);
                TreeNode childNode = new TreeNode(String.Format("<span gid='"+ gid + "' class='{0} "+(!String.IsNullOrEmpty(txtSearch.Text) && dr["name"].ToString().ToLower().Contains(txtSearch.Text.ToLower())   ? "mark" : "")+"'>{1}</span>", css,dr["name"]), id.ToString());
                //if(type == 0)
                //    childNode.
                
                childNode.SelectAction = TreeNodeSelectAction.None;
                if (tn == null)
                {
                    TreeView1.Nodes.Add(childNode);
                }
                else
                {
                    tn.ChildNodes.Add(childNode);
                }
                if (!String.IsNullOrEmpty(txtSearch.Text) && dr["name"].ToString().ToLower().Contains(txtSearch.Text.ToLower()))
                    Expandtree(childNode);
                    //childNode.Parent.Expand(); //.Expanded = true;

                BindTree(dt, id, childNode);
            }
        }
        private void Expandtree(TreeNode childNode)
        {
            if (childNode.Parent != null)
            {
                childNode.Parent.Expand();
                Expandtree(childNode.Parent);
            }
        }
        protected void btnsearch_Click(object sender, EventArgs e)
        {
            TreeView1.Nodes.Clear();
            LoadTree();
            //if (!string.IsNullOrEmpty(txtSearch.Text))
            //{
            //    TreeNode searchedNode = null;
            //    foreach (TreeNode node in TreeView1.Nodes)
            //    {
            //        searchedNode = SearchNode(node, txtSearch.Text);
            //        if (searchedNode == null)
            //        {
            //            foreach (TreeNode childNode in node.ChildNodes)
            //            {
            //                searchedNode = SearchNode(childNode, txtSearch.Text);
            //                if (searchedNode != null)
            //                    goto Here;
            //            }
            //        }
            //        else
            //        {
            //            break;
            //        }
            //    }
            //Here:
            //    if (searchedNode != null)
            //    {
            //        searchedNode.Select();
            //        TreeView1.ExpandAll();
            //        txtSearch.Text = "";
            //    }
            //    else
            //    {
            //        this.ClientScript.RegisterStartupScript(this.GetType(), "Not Found", "alert('Node " + txtSearch.Text + " not found');", true);
            //    }
            //}
            //TreeNode SearchNode(TreeNode node, string searchText = null)
            //{
            //    if (node.Text == searchText) return node;

            //    TreeNode tn = null;
            //    foreach (TreeNode childNode in node.ChildNodes)
            //    {
            //        tn = SearchNode(childNode);
            //        if (tn != null) break;
            //    }

            //    if (tn != null) node.Expand();
            //    return tn;
            //}
        }

        
    }
    
}
