using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.IO;
using System.Data;

using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using MySql.Data.MySqlClient;

namespace RetalineProAgent
{
    public partial class MasterDataImport: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            plsDefaultDB.Visible = chkDefaultDBServer.Checked;
            plcNotDefaultDB.Visible = !plcNotDefaultDB.Visible;
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            ReadExcel();

        }

        private void ReadExcel()
        {
            if (!fupload.HasFile)
            {
                lblResult.Text = "Please select excel";
                return;
            }

            if (!Directory.Exists(Server.MapPath("/Uploads")))
                Directory.CreateDirectory(Server.MapPath("/Uploads"));

            string strExcel = Server.MapPath($"/Uploads/{Guid.NewGuid()}{Path.GetExtension(fupload.PostedFile.FileName)}");
            fupload.PostedFile.SaveAs(strExcel);

            //string strExcel = "D:\\Downloads\\Grozeo dummy data.xlsx"; //fupload.FileName;
            //HSSFWorkbook hssfwb;
            XSSFWorkbook xssfwb;
            using (FileStream file = new FileStream(strExcel, FileMode.Open, FileAccess.Read))
            {
                //hssfwb = new HSSFWorkbook(file);
                xssfwb = new XSSFWorkbook(file);
            }
            //ISheet sheet = hssfwb.GetSheet("Arkusz1");

            for (int sheetNum = 0; sheetNum < xssfwb.NumberOfSheets; sheetNum++)
            {
                ISheet sheet = xssfwb.GetSheetAt(sheetNum); //.GetSheet("Sheet1");

                if (sheet.LastRowNum < 1)
                    continue;

                ReadSheet(sheet);

            }

        }

        private void ReadSheet(ISheet sheet)
        {
            lblResult.Text = $"Total rows: {sheet.LastRowNum}";

            DataTable dt = new DataTable();
            foreach (ICell cell in sheet.GetRow(0).Cells)
            {
                if (!dt.Columns.Contains(cell.StringCellValue))
                    dt.Columns.Add(cell.StringCellValue.Replace("{", "").Replace("}", ""));
            }

            for (int row = 1; row <= sheet.LastRowNum; row++)
            {
                IRow excelRow = sheet.GetRow(row);
                if (excelRow != null) //null is when the row only contains empty cells 
                {
                    bool containsData = false;
                    DataRow dr = dt.NewRow();
                    for (int i = 0; i < dt.Columns.Count; i++)
                    {
                        ICell cell = excelRow.GetCell(i, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                        if (cell == null)
                            continue;
                        string val = (cell.CellType == CellType.Numeric ? cell.NumericCellValue.ToString() : cell.StringCellValue.Replace("{", "").Replace("}", ""));
                        dr[i] = val;
                        containsData = true;
                    }
                    if(containsData)
                        dt.Rows.Add(dr);

                }
            }

            lblResult.Text += "<br/>Generated data table";
            string strTableName = (String.IsNullOrEmpty(txtTableName.Text) ? "tmp_master_pro2" : txtTableName.Text);
            BulkInsertMySQL(dt, strTableName); // "tmp_master_pro2");

        }


        public void BulkInsertMySQL(DataTable table, string tableName)
        {
            string strDBName = "grozeo_tmp";
            if (chkDefaultDBServer.Checked && !String.IsNullOrEmpty(txtDB.Text))
                strDBName = txtDB.Text;

            // SECURITY: Validate table name to prevent SQL injection
            if (!System.Text.RegularExpressions.Regex.IsMatch(tableName, @"^[a-zA-Z_][a-zA-Z0-9_]{0,63}$"))
            {
                lblResult.Text = "Invalid table name.";
                return;
            }
            string strTable = tableName;
            // FIXED: Use environment variables - never hardcode credentials
            string dbHost = System.Environment.GetEnvironmentVariable("IMPORT_DB_HOST") ?? "localhost";
            string dbUser = System.Environment.GetEnvironmentVariable("IMPORT_DB_USER") ?? "";
            string dbPass = System.Environment.GetEnvironmentVariable("IMPORT_DB_PASS") ?? "";
            string connectionString = $"Server={dbHost};Database={strDBName};Uid={dbUser};Pwd={dbPass};SslMode=Preferred;";
            if (!chkDefaultDBServer.Checked && !String.IsNullOrEmpty(txtCon.Text))
                connectionString = txtCon.Text;

            using (MySqlConnection connection = new MySqlConnection(connectionString))
            {
                connection.Open();

                lblResult.Text += "<br/>Connected DB.";
                using (MySqlTransaction tran = connection.BeginTransaction(IsolationLevel.Serializable))
                {
                    if (chkCreateTable.Checked)
                    {
                        //strTable = txtName.Text;
                        string strFields = "";
                        foreach (DataColumn dc in table.Columns)
                        {
                            strFields += (String.IsNullOrEmpty(strFields) ? "" : ", ") + $"{dc.ColumnName} varchar(200) null";
                        }
                        using (MySqlCommand cmd = new MySqlCommand())
                        {
                            cmd.Connection = connection;
                            cmd.Transaction = tran;
                            cmd.CommandText = $"CREATE TABLE " + tableName + "( " + strFields + " )";
                            cmd.ExecuteNonQuery();
                        }
                        lblResult.Text += "<br/>Created table.";
                    }
                    if (chckClearRecords.Checked)
                    {
                        using (MySqlCommand cmd = new MySqlCommand())
                        {
                            cmd.Connection = connection;
                            cmd.Transaction = tran;
                            cmd.CommandText = $"TRUNCATE TABLE {tableName}";
                            cmd.ExecuteNonQuery();
                        }
                        lblResult.Text += "<br/>Cleared data in table." + tableName;

                    }
                    using (MySqlCommand cmd = new MySqlCommand())
                    {
                        string strSql = $"SELECT * FROM " + tableName + " limit 0";
                        cmd.Connection = connection;
                        cmd.Transaction = tran;
                        cmd.CommandText = $"SELECT * FROM " + tableName + " limit 0";

                        using (MySqlDataAdapter adapter = new MySqlDataAdapter(cmd))
                        {
                            adapter.UpdateBatchSize = 10000;
                            using (MySqlCommandBuilder cb = new MySqlCommandBuilder(adapter))
                            {
                                cb.SetAllValues = true;
                                adapter.Update(table);
                                tran.Commit();
                            }
                        };
                    }
                    lblResult.Text += "<br/>Exported data.";
                }
            }
        }


    }
}