// SECURITY: This page executes raw SQL - protect behind admin role + IP whitelist or remove in production
﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Configuration;
using System.Data;
using System.IO;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using System.Data.SqlClient;

namespace RetalineProAgent
{
    public partial class Command: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void btnConnect_Click(object sender, EventArgs e)
        {
            string strConnectionstring = "";
            if (!String.IsNullOrEmpty(txtSql.Text))
            {
                if (!String.IsNullOrEmpty(txtConnection.Text))
                {
                    strConnectionstring = txtConnection.Text;
                }
                else
                {
                    strConnectionstring = ConfigurationManager.ConnectionStrings["conn"].ConnectionString;
                }

                if (chkToXL.Checked)
                {
                    SqlConnection con = new SqlConnection(strConnectionstring);
                    SqlCommand cmd = new SqlCommand(txtSql.Text, con);
                    con.Open();
                    DataTable dt = new DataTable();
                    dt.Load(cmd.ExecuteReader());
                    con.Close();
                    if (!Directory.Exists(Server.MapPath("~/tmp")))
                        Directory.CreateDirectory(Server.MapPath("~/tmp"));
                    string strDate = DateTime.Now.ToString("dd_MM_yyyy_hh_mm_ss_tt").Replace(" ", "_");
                    string strPath = Server.MapPath("~/tmp/myresult"+ strDate + ".xlsx");
                    TransferDataTabletoXL(dt, strPath);
                }
                else
                {
                    sdsConnect.ConnectionString = strConnectionstring;
                    sdsConnect.SelectCommand = txtSql.Text;
                    var dsource = sdsConnect.Select(DataSourceSelectArguments.Empty);
                    gvTables.DataSource = dsource;
                    gvTables.DataBind();
                }
            }
        }



        public static void TransferDataTabletoXL(DataTable dt, string path)
        {
            //DataTable dt = new DataTable();
            //dt.Columns.Add("City", typeof(string));
            //dt.Columns.Add("State", typeof(string));
            //dt.Columns.Add("Zip", typeof(string));

            //using (FileStream stream = new FileStream(OpenFile(), FileMode.Open, FileAccess.Read))
            //{
            //    IWorkbook wb = new XSSFWorkbook(stream);
            //    ISheet sheet = wb.GetSheet("Sheet1");
            //    string holder;
            //    int i = 0;
            //    do
            //    {
            //        DataRow dr = dt.NewRow();
            //        IRow row = sheet.GetRow(i);
            //        try
            //        {
            //            holder = row.GetCell(0, MissingCellPolicy.CREATE_NULL_AS_BLANK).ToString();
            //        }
            //        catch (Exception)
            //        {
            //            break;
            //        }

            //        string city = holder.Substring(0, holder.IndexOf(','));
            //        string state = holder.Substring(holder.IndexOf(',') + 2, 2);
            //        string zip = holder.Substring(holder.IndexOf(',') + 5, 5);
            //        dr[0] = city;
            //        dr[1] = state;
            //        dr[2] = zip;
            //        dt.Rows.Add(dr);
            //        i++;
            //    } while (!String.IsNullOrEmpty(holder));
            //}
            
            using (FileStream stream = new FileStream(path, FileMode.Create, FileAccess.Write))//@"C:\Working\FieldedAddresses.xlsx"
            {
                IWorkbook wb = new XSSFWorkbook();
                ISheet sheet = wb.CreateSheet("Sheet1");
                ICreationHelper cH = wb.GetCreationHelper();
                int rows = 0;
                IRow rowH = sheet.CreateRow(rows++);
                foreach (DataColumn dc in dt.Columns)
                {
                    ICell cell = rowH.CreateCell(rowH.Cells.Count);
                    cell.SetCellValue(cH.CreateRichTextString(dc.ColumnName));
                }
                //for (int i = 0; i < dt.Rows.Count; i++)
                foreach(DataRow dr in dt.Rows)
                {
                    IRow row = sheet.CreateRow(rows++);
                    for (int j = 0; j < dt.Columns.Count; j++)
                    {
                        ICell cell = row.CreateCell(j);
                        cell.SetCellValue(cH.CreateRichTextString(dr[j].ToString()));
                    }
                }
                wb.Write(stream);
            }
        }

    }
}