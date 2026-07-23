using Antlr.Runtime;
using RetalineProAgent.Service;
using NPOI.POIFS.NIO;
using NPOI.POIFS.Properties;
using NPOI.SS.Formula.Functions;
using System;
using System.Collections;
using System.Collections.Generic;
using System.Data;
using System.Data.SqlClient;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Xml.Linq;
using static Org.BouncyCastle.Crypto.Digests.SkeinEngine;
using RetalineProAgent.Core.Services;
using System.Configuration;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.Services.ActiveLog;
using Amazon.Auth.AccessControlPolicy;

namespace RetalineProAgent.Finance
{
    public partial class GroupCreation: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                
            }

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (ddlentrype.Text == "1")
            {
                pnlMain.Visible = false;
                pnlSub.Visible = false;
                pnlPrimary.Visible = true;
            }
            if (ddlentrype.Text == "2")
            {
                pnlSub.Visible = false;
                pnlPrimary.Visible=false;
                pnlMain.Visible = true;
            }
            if(ddlentrype.Text == "3")
            {
                pnlMain.Visible = false;
                pnlPrimary.Visible = false;
                pnlSub.Visible = true;
            }
            if (dlentrypeudate.Text == "1")
            {
                pnlmainupdate.Visible = false;
                pnlsubgroup.Visible = false;
                pnlpimaryupdate.Visible = true;
            }
            if (dlentrypeudate.Text == "2")
            {
                pnlmainupdate.Visible = true;
                pnlsubgroup.Visible = false;
                pnlpimaryupdate.Visible = false;
            }
            if (dlentrypeudate.Text == "3")
            {
                pnlmainupdate.Visible = false;
                pnlsubgroup.Visible = true;
                pnlpimaryupdate.Visible = false;
            }

        }
        protected void btncreate_Click(object sender, EventArgs e)
        {

            ShowDiv.Visible=true;
            pnldetails.Visible=false;
            editdiv.Visible = false;
            pnledit.Visible = true;






        }
        protected void lvdetails_ItemEditing(object sender, ListViewEditEventArgs e)
        {

        }

        protected void lvdetails_ItemUpdating(object sender, ListViewUpdateEventArgs e)
        {

        }

        private void Loadinfo()
        {
            int Id = Convert.ToInt32(hidgroupId.Value);
            if (Id > 0)
            {
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("id", Id));
                string gropdetais = "Select g.id,g.parent_id, g.[name],account_types_id," +
                    $"(case when g.id in (select  g.id from groups g where g.parent_id in (select s.[id]  from[groups] s where parent_id = 0)) then(select s.name from groups s where s.id = g.parent_id) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0))" +
                    $")then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) end ) as primarygroup," +
                    $"(case when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id = 0 )) then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select[id] from[groups] where parent_id = 0))" +
                    $")then(select s.name from groups s where s.id = g.parent_id) when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) end ) as maingroup," +
                    $"(case when g.id in (select g.id from groups g where g.parent_id in(select[id] from[groups] where parent_id = 0 )) then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0))" +
                    $")then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in (select g.id from groups g where g.parent_id in(select g.[id] from[groups] g where g.parent_id in(select s.[id] from[groups] s where s.parent_id != 0))) then(select s.name from groups s where s.id = g.parent_id) end ) as subgroup," +
                    $"parent_id,(case when parent_id = 0 then 'Primary Group' when parent_id in(select[id] from[groups] where parent_id = 0 )" +
                    $"then 'Main Group' else 'Sub Group' end ) as GroupType, isSystem, ac.id as natureid, ac.nature from[groups] g " +
                    $"inner join[account_types] ac on g.account_types_id = ac.id where g.id =@Id order by g.parent_id";
                var groupid = DataService.GetDataTable(gropdetais, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
                if (groupid != null && groupid.Rows.Count > 0)
                {
                    var grou = groupid.Rows[0];
                    ltrnamegroup.Text = grou["name"].ToString();
                    ltrtypeofgroup.Text = grou["GroupType"].ToString();
                    ltrnatureofgroup.Text = grou["nature"].ToString();
                    ltrprimarygroup.Text = grou["primarygroup"].ToString();
                    ltrmaingroup.Text = grou["maingroup"].ToString();
                    ltrsubgroup.Text = grou["subgroup"].ToString();


                }




            }






        }





        protected void lvdetails_ItemCommand(object sender, ListViewCommandEventArgs e)
        {

        }

        protected void btnsave_Click(object sender, EventArgs e)
        {
            if (ddlentrype.Text == "3")
            {
                int id = 0;
                int nature_id = 0;
                int parent_id = 0;
                if (!String.IsNullOrEmpty(selsubgroup.Text))
                    id = Convert.ToInt32(selsubgroup.Text);
                nature_id = Convert.ToInt32(dlnature.Text);
                parent_id = Convert.ToInt32(selsubgroup.Text);
                List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
                sidparams.Add(new KeyValuePair<string, object>("name", txtGroupName.Text));
                sidparams.Add(new KeyValuePair<string, object>("id", id));
                sidparams.Add(new KeyValuePair<string, object>("naturid", nature_id));
                sidparams.Add(new KeyValuePair<string, object>("parentid", parent_id));
                string cnt = null;
                DataTable groupCount = DataService.GetDataTable($"select count(1) as count from groups where [name]= @name", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sidparams);

                if (groupCount != null && groupCount.Rows.Count > 0)
                {
                    DataRow da = groupCount.Rows[0];
                    cnt = da["count"].ToString();
                }
                int count = Convert.ToInt32(cnt);
                if (count > 0)
                {
                    lbgroupid.Text = "Subgroup already exists";
                }
                else
                {
                    if (parent_id != 0)
                    {
                        string sub = "insert into groups ([name],parent_id,isSystem,account_types_id)   values(@name,@parentid,0,@naturid)";
                        int suggroup = DataService.ExecuteSql(sub, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams);
                        lbgroupid.Text = "";
                        Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/GroupManagement");
                    }
                    else
                    {
                        lbprime.Text = "primary group can't edit";
                    }
                    
                }
            }
            if (ddlentrype.Text == "2")
            {
                int id = 0;
                int nature_id = 0;
                int parent_id = 0;
                if (!String.IsNullOrEmpty(ddlgroup.Text))
                    id = Convert.ToInt32(ddlgroup.Text);
                nature_id = Convert.ToInt32(dlnature.Text);
                parent_id = Convert.ToInt32(ddlgroup.Text);
                List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
                sidparams.Add(new KeyValuePair<string, object>("name", txtGroupName.Text));
                sidparams.Add(new KeyValuePair<string, object>("id", id));
                sidparams.Add(new KeyValuePair<string, object>("naturid", nature_id));
                sidparams.Add(new KeyValuePair<string, object>("parentid", parent_id));
                string cnt = null;
                DataTable groupCount = DataService.GetDataTable($"select count(1) as count from groups where [name]= @name", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sidparams);

                if (groupCount != null && groupCount.Rows.Count > 0)
                {
                    DataRow da = groupCount.Rows[0];
                    cnt = da["count"].ToString();
                }
                int count = Convert.ToInt32(cnt);
                if (count > 0)
                {
                    lbgroupid.Text = "Group is already exists";
                }
                else
                {

                    if (parent_id != 0)
                    {
                        string sub = "insert into groups ([name],parent_id,isSystem,account_types_id)   values(@name,@parentid,0,@naturid)";
                        int suggroup = DataService.ExecuteSql(sub, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams);
                        lbgroupid.Text = "";
                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = -1;
                        string User = "Finance Admin";
                        string name = txtGroupName.Text;
                        string Id = Convert.ToString(id);
                        string Naturid = Convert.ToString(nature_id);
                        string Parentid = Convert.ToString(parent_id);
                        string create = "Group Creation";
                        var items = new[]
                        {
                            new { Key = "Group Name", Value = name },
                            new { Key = "Group Id", Value = Id },
                            new { Key = "Natur Id", Value = Naturid },
                            new { Key = "Parent Id", Value = Parentid },
                            new { Key = "create", Value = create },
                        };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                        Common.ShowCustomAlert(this.Page, "Success", "saved successfully!", true, "/Finance/GroupManagement");
                    }
                    else
                    {
                        lbprime.Text = "primary group can't edit";
                    }
                    
                }

            }
            if (ddlentrype.Text == "1")
            {


                int id = 0;
                int nature_id = 0;
                int parent_id = 0;
                if (!String.IsNullOrEmpty(selGroup.Text))
                    id = Convert.ToInt32(selGroup.Text);
                nature_id = Convert.ToInt32(dlnature.Text);
                parent_id = Convert.ToInt32(selGroup.Text);
                List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
                sidparams.Add(new KeyValuePair<string, object>("name", txtGroupName.Text));
                sidparams.Add(new KeyValuePair<string, object>("id", id));
                sidparams.Add(new KeyValuePair<string, object>("naturid", nature_id));
                sidparams.Add(new KeyValuePair<string, object>("parentid", parent_id));
                string cnt = null;
                DataTable groupCount = DataService.GetDataTable($"select count(1) as count from groups where [name]= @name", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sidparams);

                if (groupCount != null && groupCount.Rows.Count > 0)
                {
                    DataRow da = groupCount.Rows[0];
                    cnt = da["count"].ToString();
                }
                int count = Convert.ToInt32(cnt);
                if (count > 0)
                {
                    lbgroupid.Text = "Group is already exists";
                }
                else
                {
                    if (parent_id != 0)
                    {
                        string sub = "insert into groups ([name],parent_id,isSystem,account_types_id)   values(@name,@parentid,0,@naturid)";
                        int suggroup = DataService.ExecuteSql(sub, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams);
                        lbgroupid.Text = "";
                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = -1;
                        string User = "Finance Admin";
                        string name = txtGroupName.Text;
                        string Id = Convert.ToString(id);
                        string Naturid = Convert.ToString(nature_id);
                        string Parentid = Convert.ToString(parent_id);
                        string create = "Group Creation";
                        var items = new[]
                        {
                            new { Key = "Group Name", Value = name },
                            new { Key = "Group Id", Value = Id },
                            new { Key = "Natur Id", Value = Naturid },
                            new { Key = "Parent Id", Value = Parentid },
                            new { Key = "create", Value = create },
                        };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                        Common.ShowCustomAlert(this.Page, "Success", "saved successfully!", true, "/Finance/GroupManagement");
                    }
                    else
                    {
                        lbprime.Text = "primary group cannot edit";
                    }
                    
                }


            }
            SDSGroupCreation.Select(DataSourceSelectArguments.Empty);
            lvdatatable.DataBind();
        }

        protected void lvdatatable_DataBinding(object sender, EventArgs e)
        {
           
        }
       
        protected void lvdetails_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void lvdetails_DataBound(object sender, EventArgs e)
        {
          
        }

        protected void lvdatatable_DataBound(object sender, EventArgs e)
        {

            if (lvdatatable.Items.Count > 0 && (String.IsNullOrEmpty(hidgroupId.Value) || hidgroupId.Value == "0"))
            {
                LinkButton lbtn = (LinkButton)lvdatatable.Items[0].FindControl("btnhide");
                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
                {
                    hidgroupId.Value = lbtn.Attributes["dataid"];
                    lvdatatable.SelectedIndex = 0;

                }
                Loadinfo();
            }




        }

        protected void btnhide_Click(object sender, EventArgs e)
        {

            ShowDiv.Visible = false;
            pnldetails.Visible = true;

            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
            {
                editdiv.Visible = false;
                pnledit.Visible = true;
                btnedit.Enabled=true;
                hidgroupId.Value = lbtn.Attributes["dataid"];
                int Id = Convert.ToInt32(hidgroupId.Value);
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("id", Id));
                string gropdetais = "Select g.id,g.parent_id, g.[name],account_types_id," +
                    $"(case when g.id in (select  g.id from groups g where g.parent_id in (select s.[id]  from[groups] s where parent_id = 0)) then(select s.name from groups s where s.id = g.parent_id) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0))"+
                    $")then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) end ) as primarygroup,"+
                    $"(case when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id = 0 )) then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select[id] from[groups] where parent_id = 0))"+
                    $")then(select s.name from groups s where s.id = g.parent_id) when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) end ) as maingroup,"+
                    $"(case when g.id in (select g.id from groups g where g.parent_id in(select[id] from[groups] where parent_id = 0 )) then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0))"+
                    $")then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in (select g.id from groups g where g.parent_id in(select g.[id] from[groups] g where g.parent_id in(select s.[id] from[groups] s where s.parent_id != 0))) then(select s.name from groups s where s.id = g.parent_id) end ) as subgroup,"+
                    $"parent_id,(case when parent_id = 0 then 'Primary Group' when parent_id in(select[id] from[groups] where parent_id = 0 )"+
                    $"then 'Main Group' else 'Sub Group' end ) as GroupType, isSystem, ac.id as natureid, ac.nature from[groups] g "+
                    $"inner join[account_types] ac on g.account_types_id = ac.id where g.id =@Id order by g.parent_id";
                var groupid = DataService.GetDataTable(gropdetais, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
                if (groupid != null && groupid.Rows.Count > 0)
                {
                    var grou = groupid.Rows[0];
                    ltrnamegroup.Text= grou["name"].ToString();
                    ltrtypeofgroup.Text= grou["GroupType"].ToString();
                    ltrnatureofgroup.Text= grou["nature"].ToString();
                    ltrprimarygroup.Text= grou["primarygroup"].ToString();
                    ltrmaingroup.Text= grou["maingroup"].ToString();
                    if(grou["isSystem"].ToString() == "1")
                    {
                        btnedit.Visible = false;
                    }
                    else
                    {
                        btnedit.Visible = true;
                    }
                    ltrsubgroup.Text= grou["subgroup"].ToString();
                    if (grou["parent_id"].ToString() == "0")
                    {
                        btnedit.Visible = false;
                    }
                    
                }

            }
            
           
        }

        protected void SDSGroupdetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            
        }

        protected void btnedit_Click(object sender, EventArgs e)
        {
            editdiv.Visible = true;
            pnledit.Visible = false;
            if (!(editdiv.Visible = true))
            {
                editdiv.Visible = true;
            }
            int Id = Convert.ToInt32(hidgroupId.Value);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("groupid", Id));
            var groupid= DataService.GetDataTable("GroupExpand", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId, isSP: true);
            
            if (groupid != null && groupid.Rows.Count > 0)
            {
                var grou = groupid.Rows[0];
                txtgroup.Text = grou["name"].ToString();
                if (dlnatureupdate.Items.FindByValue(grou["account_types_id"].ToString()) != null)
                {
                    dlnatureupdate.SelectedIndex = dlnatureupdate.Items.IndexOf(dlnatureupdate.Items.FindByValue(grou["account_types_id"].ToString()));
                    //dlnatureupate.Text = dlnatureupate.Items.FindByText(grouprow["nature"].ToString()).Value;
                    //dlnatureupate.Items.FindByText(grouprow["nature"].ToString()).Selected = true;
                   

                }
                if (ddlgroupupdate.Items.Count <= 1)
                    ddlgroupupdate.DataBind();



                //dlnatureupate.SelectedItem.Text = grouprow["nature"].ToString();
                //ddlgroupupdate.SelectedItem.Text = grouprow["maingroup"].ToString();
                if (ddlgroupupdate.Items.FindByValue(grou["maingroupid"].ToString()) != null)
                {
                    ddlgroupupdate.SelectedIndex = ddlgroupupdate.Items.IndexOf(ddlgroupupdate.Items.FindByText(grou["maingroupname"].ToString()));
                    //ddlgroupupdate.Items.FindByText(grouprow["maingroup"].ToString()).Selected = true;
                    //ddlgroupupdate.SelectedItem.Text = grou["maingroupname"].ToString();

                }
                if (selsubgroupupdate.Items.Count <= 1)
                    selsubgroupupdate.DataBind();
                //selsubgroupupdate.SelectedItem.Text = grouprow["subgroup"].ToString();
                if (selsubgroupupdate.Items.FindByValue(grou["subgroupid"].ToString()) != null)
                {
                    selsubgroupupdate.SelectedIndex = selsubgroupupdate.Items.IndexOf(selsubgroupupdate.Items.FindByText(grou["subgroupname"].ToString()));
                    //selsubgroupupdate.Items.FindByText(grouprow["subgroup"].ToString()).Selected = true;
                    //selsubgroupupdate.SelectedItem.Text = grou["subgroupname"].ToString();
                }
                if (selGroupupdate.Items.Count <= 1)
                    selGroupupdate.DataBind();
                //selGroupupdate.SelectedItem.Text = grouprow["primarygroup"].ToString();
                if (selGroupupdate.Items.FindByValue(grou["parentid"].ToString()) != null)
                {
                    selGroupupdate.SelectedIndex = selGroupupdate.Items.IndexOf(selGroupupdate.Items.FindByText(grou["parentname"].ToString()));
                    //selGroupupdate.Items.FindByText(grouprow["primarygroup"].ToString()).Selected = true;
                    //selGroupupdate.SelectedItem.Text = grou["parentname"].ToString();
                }

            }














        }

        protected void btnupdate_Click(object sender, EventArgs e)
        {
            int Id = Convert.ToInt32(hidgroupId.Value);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            if (dlentrypeudate.Text == "3")
            {
                
                int nature_id = Convert.ToInt32(dlnatureupdate.SelectedItem.Value);
                if (pnlsubgroup.Visible == true)
                {
                    int parent_id = Convert.ToInt32(selsubgroupupdate.SelectedItem.Value);
                    sqldaId.Add(new KeyValuePair<string, object>("name", txtgroup.Text));
                    sqldaId.Add(new KeyValuePair<string, object>("naturid", nature_id));
                    sqldaId.Add(new KeyValuePair<string, object>("parentid", parent_id));
                    if (parent_id != 0)
                    {

                        string group = "UPDATE groups SET name=@name,parent_id=@parentid,account_types_id=@naturid,isSystem=0 where id=@Id";
                        int result = DataService.ExecuteSql(group, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
                        lbgroupid.Text = "";
                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = -1;
                        string User = "Finance Admin";
                        string name = txtGroupName.Text;                        
                        string Naturid = Convert.ToString(nature_id);
                        string Parentid = Convert.ToString(parent_id);
                        string create = "Group Creation";
                        var items = new[]
                        {
                            new { Key = "Group Name", Value = name },                           
                            new { Key = "Natur Id", Value = Naturid },
                            new { Key = "Parent Id", Value = Parentid },
                            new { Key = "create", Value = create },
                        };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                        Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/GroupManagement");
                        lbprime.Text = "";
                    }
                    else
                    {
                        lbprime.Text = "primary group cannot edit";
                    }
                   
                }
               
            }
            if (dlentrypeudate.Text == "2")
            {
                if(pnlmainupdate.Visible == true)
                {
                    
                    int nature_id = Convert.ToInt32(dlnatureupdate.SelectedItem.Value);
                    int parent_id = Convert.ToInt32(ddlgroupupdate.SelectedItem.Value);
                    sqldaId.Add(new KeyValuePair<string, object>("name", txtgroup.Text));
                    sqldaId.Add(new KeyValuePair<string, object>("naturid", nature_id));
                    sqldaId.Add(new KeyValuePair<string, object>("parentid", parent_id));
                    if (parent_id != 0)
                    {
                        string group = "UPDATE groups SET name=@name,parent_id=@parentid,account_types_id=@naturid,isSystem=0 where id=@Id";
                        int result = DataService.ExecuteSql(group, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
                        lbgroupid.Text = "";
                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = -1;
                        string User = "Finance Admin";
                        string name = txtGroupName.Text;
                        string Naturid = Convert.ToString(nature_id);
                        string Parentid = Convert.ToString(parent_id);
                        string create = "Group Creation";
                        var items = new[]
                        {
                            new { Key = "Group Name", Value = name },
                            new { Key = "Natur Id", Value = Naturid },
                            new { Key = "Parent Id", Value = Parentid },
                            new { Key = "create", Value = create },
                        };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                        Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/GroupManagement");
                        lbprime.Text = "";
                    }
                    else
                    {
                        lbprime.Text = "primary group cannot edit";
                    }
                  


                }
               
            }
            if (dlentrypeudate.Text == "1")
            {
                if(pnlpimaryupdate.Visible == true)
                {
                    int nature_id = Convert.ToInt32(dlnatureupdate.SelectedItem.Value);
                    int parent_id = Convert.ToInt32(selGroupupdate.SelectedItem.Value);
                    sqldaId.Add(new KeyValuePair<string, object>("name", txtgroup.Text));
                    sqldaId.Add(new KeyValuePair<string, object>("naturid", nature_id));
                    sqldaId.Add(new KeyValuePair<string, object>("parentid", parent_id));
                    if(parent_id != 0)
                    {
                        string group = "UPDATE [groups] SET name=@name,parent_id=@parentid,account_types_id=@naturid,isSystem=0 where id=@Id";
                        int result = DataService.ExecuteSql(group, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
                        lbgroupid.Text = "";
                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = -1;
                        string User = "Finance Admin";
                        string name = txtGroupName.Text;
                        string Naturid = Convert.ToString(nature_id);
                        string Parentid = Convert.ToString(parent_id);
                        string create = "Group Creation";
                        var items = new[]
                        {
                            new { Key = "Group Name", Value = name },
                            new { Key = "Natur Id", Value = Naturid },
                            new { Key = "Parent Id", Value = Parentid },
                            new { Key = "create", Value = create },
                        };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                        Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/GroupManagement");
                        lbprime.Text = "";
                    }
                    else
                    {
                        lbprime.Text = "primary group cannot edit";
                    }
                    
                }
               
            }
            SDSGroupCreation.Select(DataSourceSelectArguments.Empty);
            lvdatatable.DataBind();
        }

        protected void btncancel_Click(object sender, EventArgs e)
        {
            editdiv.Visible = false;
            pnledit.Visible = true;
        }

        protected void dlentrypeudate_DataBound(object sender, EventArgs e)
        {
           

        }

        protected void btncanel_update_Click(object sender, EventArgs e)
        {
            editdiv.Visible = false;
            pnledit.Visible = true;
        }
    }
}
