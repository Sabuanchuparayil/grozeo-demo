using Amazon.DynamoDBv2.Model.Internal.MarshallTransformations;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using RetalineProAgent.UI.Login;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;
using static System.Windows.Forms.VisualStyles.VisualStyleElement.TreeView;

namespace RetalineProAgent.Tenant
{
    public partial class MarketingTools : Base.BasePartnerPage
    {
        int TenantID = 0;
        protected void Page_Load(object sender, EventArgs e)

        {
            TenantID = this.CurrentUser.StoreGroupId;
        }

        private void UpdatePluginVisibility()
        {
            List<int> tenantPluginIds = FindId();

            // Optionally reset all to false first
            I1.Visible = false;
            I2.Visible = false;
            I3.Visible = false;
            I4.Visible = false;
            I5.Visible = false;
            I6.Visible = false;
            I7.Visible = false;

            foreach (int pluginId in tenantPluginIds)
            {
                switch (pluginId)
                {
                    case 1: I1.Visible = true; break;
                    case 2: I2.Visible = true; break;
                    case 3: I3.Visible = true; break;
                    case 4: I4.Visible = true; break;
                    case 5: I5.Visible = true; break;
                    case 6: I6.Visible = true; break;
                    case 7: I7.Visible = true; break;
                }
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            UpdatePluginVisibility();
            //    List<int> tenantPluginIds = FindId();

            //    foreach (int pluginId in tenantPluginIds)
            //    {
            //        switch (pluginId)
            //        {
            //            case 1:
            //                I1.Visible = true;
            //                break;
            //            case 2:
            //                I2.Visible = true;
            //                break;
            //            case 3:
            //                I3.Visible = true;
            //                break;
            //            case 4:
            //                I4.Visible = true;
            //                break;
            //            case 5:
            //                I5.Visible = true;
            //                break;
            //            case 6:
            //                I6.Visible = true;
            //                break;
            //            case 7:
            //                I7.Visible = true;
            //                break;
            //        }
            //    }
            //}
        }

        protected List<int> FindId()
        {
            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
               new KeyValuePair<string, object>("TenantID", TenantID),
            };

            string LoadValueQuery = "select distinct(PluginId) from TenantPlugin where TenantId=@TenantID";
            DataTable result = DataService.GetDataTable(LoadValueQuery, parmeters: checkParams);

            List<int> tenantPluginIds = new List<int>();

            foreach (DataRow row in result.Rows)
            {
                // Extract the PluginId value and add it to the list
                int pluginId = Convert.ToInt32(row["PluginId"]);
                tenantPluginIds.Add(pluginId);
            }

            return tenantPluginIds;
        }

        //GOOGLE TAG
        protected void lnkGoogleTag_Click(object sender, EventArgs e)
        {

            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
              new KeyValuePair<string, object>("TenantID", TenantID),
            };

            string LoadValueQuery = "SELECT Value FROM TenantPlugin WHERE PluginId = 2 AND TenantID = @TenantID";

            var result = DataService.ExecuteScalar(LoadValueQuery, parmeters: checkParams);

            if (result != null && result != DBNull.Value)
            {
                hdGoogleTag.Value = result.ToString();
            }
            else
            {
                hdGoogleTag.Value = string.Empty;
            }

            txtGoogleTag.Text = hdGoogleTag.Value;
            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalGoogleTag').modal('toggle');</script>");

        }

        protected async void lbtnGTSave_Click(object sender, EventArgs e)
        {
            try
            {
                string googleTagValue = txtGoogleTag.Text;

                string strSqlCheck = "SELECT COUNT(*) FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId=2";
                List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("Value", googleTagValue)
        };

                int existingCount = (int)DataService.ExecuteScalar(strSqlCheck, parmeters: checkParams);

                if (existingCount > 0)
                {
                    string strSqlUpd = "UPDATE TenantPlugin SET Value = @Value WHERE TenantId = @TenantId AND PluginId = 2";
                    DataService.ExecuteSql(strSqlUpd, parmeters: checkParams);
                    Common.ShowCustomAlert(this.Page, "Success", "Updated Successfully", true);
                }
                else
                {
                    string strSqlIns = "INSERT INTO TenantPlugin(TenantId, PluginId, Name, Value) VALUES (@TenantId, 2, 'GOOGLETAGMGRID', @Value)";
                    int rowsAffected = DataService.ExecuteSql(strSqlIns, parmeters: checkParams);

                    if (rowsAffected < 1)
                    {
                        throw new Exception("Error inserting Google Tag Manager value");
                    }

                    Common.ShowCustomAlert(this.Page, "Success", "Google Tag Manager has been enabled.");
                }

                await ClearPluginCacheAsync(TenantID);
                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The value of the Google Tag Manager has been added/updated");

            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }

        protected async void lbtnGTDelete_Click(object sender, EventArgs e)
        {
            try
            {
                string strSqlCheck = "SELECT COUNT(*) FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId = 2";
                List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID)
        };

                int existingCount = (int)DataService.ExecuteScalar(strSqlCheck, parmeters: parameters);

                if (existingCount > 0)
                {
                    string strSqlDel = "DELETE FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId = 2";
                    int rowsAffected = DataService.ExecuteSql(strSqlDel, parmeters: parameters);

                    if (rowsAffected < 1)
                    {
                        throw new Exception("Error deleting Google Tag Manager value");
                    }

                    Common.ShowCustomAlert(this.Page, "Success", "Google Tag Manager has been deleted.");
                    UpdatePluginVisibility();

                    await ClearPluginCacheAsync(TenantID);
                    await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The Google Tag Manager configuration was deleted");
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Info", "No Google Tag Manager setting found to delete.", false);
                }
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }


        // GOOGLE ANALYTICS
        protected void lnkGoogleAnalytics_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
              new KeyValuePair<string, object>("TenantID", TenantID),
            };

            string LoadValueQuery = "SELECT Value FROM TenantPlugin WHERE PluginId = 5 AND TenantID = @TenantID";

            var result = DataService.ExecuteScalar(LoadValueQuery, parmeters: checkParams);

            if (result != null && result != DBNull.Value)
            {
                hdGoogleAnalytics.Value = result.ToString();
            }
            else
            {
                hdGoogleAnalytics.Value = string.Empty;
            }

            txtGoogleAnalytics.Text = hdGoogleAnalytics.Value;

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalGoogleAnalytics').modal('toggle');</script>");
        }

        protected async void lbtnGAnalyticsSave_Click(object sender, EventArgs e)
        {
            try
            {
                string googleAnalyticsValue = txtGoogleAnalytics.Text;

                string strSqlCheck = "SELECT COUNT(*) FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId=5";
                List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("Value", googleAnalyticsValue)
        };

                int existingCount = (int)DataService.ExecuteScalar(strSqlCheck, parmeters: checkParams);

                if (existingCount > 0)
                {
                    string strSqlUpd = "UPDATE TenantPlugin SET Value = @Value WHERE TenantId = @TenantId AND PluginId = 5";
                    DataService.ExecuteSql(strSqlUpd, parmeters: checkParams);
                    Common.ShowCustomAlert(this.Page, "Success", "Updated Successfully", true);
                }
                else
                {
                    string strSqlIns = "INSERT INTO TenantPlugin(TenantId, PluginId, Name, Value) VALUES (@TenantId,5, 'GOOGLEANALYTICSID',@Value)";
                    int rowsAffected = DataService.ExecuteSql(strSqlIns, parmeters: checkParams);

                    if (rowsAffected < 1)
                    {
                        throw new Exception("Error inserting Google Analytics value");
                    }

                    Common.ShowCustomAlert(this.Page, "Success", "Google Analytics has been enabled.");
                }

                await ClearPluginCacheAsync(TenantID);
                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The value of the Google Analytics has been added/updated");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }

        protected async void lbtnGAnalyticsDelete_Click(object sender, EventArgs e)
        {
            try
            {
                string strSqlDelete = "DELETE FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId = 5";
                List<KeyValuePair<string, object>> deleteParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID)
        };

                int rowsAffected = DataService.ExecuteSql(strSqlDelete, parmeters: deleteParams);

                if (rowsAffected > 0)
                {
                    await ClearPluginCacheAsync(TenantID);
                    Common.ShowCustomAlert(this.Page, "Success", "Google Analytics ID has been deleted.");
                    UpdatePluginVisibility();

                    await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The Google Analytics ID was deleted");
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Info", "No Google Analytics ID found to delete.");
                }
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }

        //MICROSOFT CLARITY
        protected void lnkMicrosoftClarity_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
              new KeyValuePair<string, object>("TenantID", TenantID),
            };

            string LoadValueQuery = "SELECT Value FROM TenantPlugin WHERE PluginId = 1 AND TenantID = @TenantID";

            var result = DataService.ExecuteScalar(LoadValueQuery, parmeters: checkParams);

            if (result != null && result != DBNull.Value)
            {
                hdMicrosoftClarity.Value = result.ToString();
            }
            else
            {
                hdMicrosoftClarity.Value = string.Empty;
            }

            txtMSClarity.Text = hdMicrosoftClarity.Value;
            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalMicrosoftClarity').modal('toggle');</script>");
        }

        protected async void lbtnMSClarity_Click(object sender, EventArgs e)
        {
            try
            {
                string msClarity = txtMSClarity.Text;

                string strSqlCheck = "SELECT COUNT(*) FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId = 1";
                List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("Value", msClarity)
        };

                int existingCount = (int)DataService.ExecuteScalar(strSqlCheck, parmeters: checkParams);

                if (existingCount > 0)
                {
                    string strSqlUpd = "UPDATE TenantPlugin SET Value = @Value WHERE TenantId = @TenantId AND PluginId = 1";
                    DataService.ExecuteSql(strSqlUpd, parmeters: checkParams);
                    Common.ShowCustomAlert(this.Page, "Success", "Updated Successfully", true);
                }
                else
                {
                    string strSqlIns = "INSERT INTO TenantPlugin(TenantId, PluginId, Name, Value) VALUES (@TenantId, 1, 'MSCLARITYID', @Value)";
                    int rowsAffected = DataService.ExecuteSql(strSqlIns, parmeters: checkParams);

                    if (rowsAffected < 1)
                    {
                        throw new Exception("Error inserting Microsoft Clarity value");
                    }

                    Common.ShowCustomAlert(this.Page, "Success", "Microsoft Clarity has been enabled.");
                }

                await ClearPluginCacheAsync(TenantID);
                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The value of the Microsoft Clarity has been added/updated");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }


        protected async void lbtnMSClarityDelete_Click(object sender, EventArgs e)
        {
            try
            {
                string strSqlDelete = "DELETE FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId = 1";
                List<KeyValuePair<string, object>> deleteParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID)
        };

                int rowsAffected = DataService.ExecuteSql(strSqlDelete, parmeters: deleteParams);

                if (rowsAffected > 0)
                {
                    await ClearPluginCacheAsync(TenantID);
                    Common.ShowCustomAlert(this.Page, "Success", "Microsoft Clarity ID has been deleted.");
                    UpdatePluginVisibility();
                    await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The Microsoft Clarity ID was deleted");
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Info", "No Microsoft Clarity ID found to delete.");
                }
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }

        //SEO Tools
        protected void lnkSEOTools_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
              new KeyValuePair<string, object>("TenantID", TenantID),
            };

            string LoadValuesQuery = "SELECT [Name], [Value] FROM TenantPlugin WHERE PluginId = 3 AND TenantID = @TenantID";
            txtSEOMetaTitle.Text = string.Empty;
            txtSEOMetaKeyword.Text = string.Empty;
            txtSEOMetaDesc.Text = string.Empty;

            using (var dataTable = DataService.GetDataTable(LoadValuesQuery, parmeters: checkParams))
            {
                foreach (DataRow dr in dataTable.Rows)
                {
                    string strKeyName = dr["Name"].ToString();
                    switch (strKeyName)
                    {
                        case "SEOMETATAG":
                            txtSEOMetaTitle.Text = dr["Value"].ToString();
                            break;
                        case "SEOMETAKEY":
                            txtSEOMetaKeyword.Text = dr["Value"].ToString();
                            break;
                        case "SEOMETADESC":
                            txtSEOMetaDesc.Text = dr["Value"].ToString();
                            break;
                    }
                }

            }

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalSEOTools').modal('toggle');</script>");
        }
        protected async void lbtSEOSave_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> insertParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("PluginId", 3)
        };

                string strInsertSql = "";
                if (!string.IsNullOrEmpty(txtSEOMetaTitle.Text))
                {
                    strInsertSql += " INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, @PluginId, 'SEOMETATAG', @SEOMETATAG); ";
                    insertParams.Add(new KeyValuePair<string, object>("SEOMETATAG", txtSEOMetaTitle.Text));
                }
                if (!string.IsNullOrEmpty(txtSEOMetaKeyword.Text))
                {
                    strInsertSql += " INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, @PluginId, 'SEOMETAKEY', @SEOMETAKEY); ";
                    insertParams.Add(new KeyValuePair<string, object>("SEOMETAKEY", txtSEOMetaKeyword.Text));
                }
                if (!string.IsNullOrEmpty(txtSEOMetaDesc.Text))
                {
                    strInsertSql += " INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, @PluginId, 'SEOMETADESC', @SEOMETADESC); ";
                    insertParams.Add(new KeyValuePair<string, object>("SEOMETADESC", txtSEOMetaDesc.Text));
                }

                string strSql = $"DELETE FROM TenantPlugin WHERE TenantId=@TenantId AND PluginId= @PluginId; {strInsertSql}";
                DataService.ExecuteSql(strSql, parmeters: insertParams);

                await ClearPluginCacheAsync(TenantID);
                Common.ShowToastifyMessage(this.Page, "SEO Tool has been enabled.");
                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The value of the SEO Tool has been added/updated");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, $"An error occurred: {ex.Message}", "danger");
            }
        }
        protected async void lbtSEODelete_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> deleteParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("PluginId", 3)
        };

                string deleteSql = @"
            DELETE FROM TenantPlugin 
            WHERE TenantId = @TenantId AND PluginId = @PluginId 
            AND Name IN ('SEOMETATAG', 'SEOMETAKEY', 'SEOMETADESC');";

                int rowsAffected = DataService.ExecuteSql(deleteSql, parmeters: deleteParams);

                await ClearPluginCacheAsync(TenantID);

                if (rowsAffected > 0)
                {
                    Common.ShowToastifyMessage(this.Page, "SEO Tool settings have been deleted.");
                    UpdatePluginVisibility();
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "No SEO Tool settings found to delete.", "info");
                }

                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The SEO Tool settings were deleted");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, $"An error occurred: {ex.Message}", "danger");
            }
        }


        // META PIXEL
        protected void lnkMetaPixal_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
              new KeyValuePair<string, object>("TenantID", TenantID),
            };

            string LoadValueQuery = "SELECT Value FROM TenantPlugin WHERE PluginId = 4 AND TenantID = @TenantID";

            var result = DataService.ExecuteScalar(LoadValueQuery, parmeters: checkParams);

            if (result != null && result != DBNull.Value)
            {
                hdMetaPixelValue.Value = result.ToString();
            }
            else
            {
                hdMetaPixelValue.Value = string.Empty;
            }

            txtMetaPixel.Text = hdMetaPixelValue.Value;

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalMetaPixel').modal('toggle');</script>");
        }
        protected async void lbtnMetaPixelSave_Click(object sender, EventArgs e)
        {
            try
            {
                string metaPixelValue = txtMetaPixel.Text;

                string strSqlCheck = "SELECT COUNT(*) FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId = 4";
                List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("Value", metaPixelValue)
        };

                int existingCount = (int)DataService.ExecuteScalar(strSqlCheck, parmeters: checkParams);

                if (existingCount > 0)
                {
                    string strSqlUpd = "UPDATE TenantPlugin SET Value = @Value WHERE TenantId = @TenantId AND PluginId = 4";
                    DataService.ExecuteSql(strSqlUpd, parmeters: checkParams);
                    Common.ShowCustomAlert(this.Page, "Success", "Updated Successfully", true);
                }
                else
                {
                    string strSqlIns = "INSERT INTO TenantPlugin(TenantId, PluginId, Name, Value) VALUES (@TenantId, 4, 'METAPIXELID', @Value)";
                    int rowsAffected = DataService.ExecuteSql(strSqlIns, parmeters: checkParams);

                    if (rowsAffected < 1)
                    {
                        throw new Exception("Error inserting Meta Pixel value");
                    }

                    Common.ShowCustomAlert(this.Page, "Success", "Meta Pixel has been enabled.");
                }

                await ClearPluginCacheAsync(TenantID);
                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The value of the Meta Pixel has been added/updated");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }

        protected async void lbtnMetaPixelDelete_Click(object sender, EventArgs e)
        {
            try
            {
                string strSqlDelete = "DELETE FROM TenantPlugin WHERE TenantId = @TenantId AND PluginId = 4";
                List<KeyValuePair<string, object>> deleteParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID)
        };

                int rowsAffected = DataService.ExecuteSql(strSqlDelete, parmeters: deleteParams);

                await ClearPluginCacheAsync(TenantID);

                if (rowsAffected > 0)
                {
                    Common.ShowCustomAlert(this.Page, "Success", "Meta Pixel has been deleted.");
                    UpdatePluginVisibility();
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Info", "No Meta Pixel setting found to delete.");
                }

                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(), "The Meta Pixel ID was deleted");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }


        //TAWK 
        protected void lnkTawkLive_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
              new KeyValuePair<string, object>("TenantID", TenantID),
            };
            txtTawkPropertyId.Text = txtTawkWidgetId.Text = "";
            string sql = "SELECT [Name], [Value] FROM TenantPlugin WHERE PluginId =6 AND TenantID = @TenantID";
            using (var dataTable = DataService.GetDataTable(sql, parmeters: checkParams))
            {
                foreach (DataRow dr in dataTable.Rows)
                {
                    string strKeyName = dr["Name"].ToString();
                    switch (strKeyName)
                    {
                        case "TWALKLIVEID":
                            txtTawkPropertyId.Text = dr["Value"].ToString();
                            break;
                        case "TWALKWIDGETID":
                            txtTawkWidgetId.Text = dr["Value"].ToString();
                            break;
                    }
                }

            }

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalTwakLive').modal('toggle');</script>");

        }
        protected async void lbtnTawkSave_Click(object sender, EventArgs e)
        {
            try
            {
                string strSqlIns = @"
            DELETE FROM TenantPlugin 
            WHERE TenantId = @TenantId AND PluginId = 6;

            INSERT INTO TenantPlugin(TenantId, PluginId, Name, Value) 
            VALUES 
            (@TenantId, 6, 'TWALKLIVEID', @TWALKLIVEID), 
            (@TenantId, 6, 'TWALKWIDGETID', @TWALKWIDGETID);";

                var insertParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("TWALKLIVEID", txtTawkPropertyId.Text),
            new KeyValuePair<string, object>("TWALKWIDGETID", txtTawkWidgetId.Text)
        };

                DataService.ExecuteSql(strSqlIns, parmeters: insertParams);

                await ClearPluginCacheAsync(TenantID);
                Common.ShowToastifyMessage(this.Page, "Tawk.to Live Chat has been enabled.");

                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(),
                    "The values of the Tawk.to Live Chat have been added/updated");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }

        protected async void lbtnTawkDelete_Click(object sender, EventArgs e)
        {
            try
            {
                string strSqlDelete = @"
            DELETE FROM TenantPlugin 
            WHERE TenantId = @TenantId 
            AND PluginId = 6 
            AND Name IN ('TWALKLIVEID', 'TWALKWIDGETID');";

                var deleteParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID)
        };

                int rowsAffected = DataService.ExecuteSql(strSqlDelete, parmeters: deleteParams);

                await ClearPluginCacheAsync(TenantID);

                if (rowsAffected > 0)
                {
                    Common.ShowToastifyMessage(this.Page, "Tawk.to Live Chat settings have been deleted.");
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "No Tawk.to settings found to delete.", "info");
                }

                await LogPluginActivityAsync(this.CurrentUser.APIStoreId, TenantID.ToString(),
                    "Tawk.to settings were deleted");
                UpdatePluginVisibility();
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }

        //SOCIAL MEDIA
        protected void lnkSocialMedia_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> checkParams = new List<KeyValuePair<string, object>>()
            {
              new KeyValuePair<string, object>("TenantID", TenantID),
            };

            txtFBUrl.Text = txtInstaUrl.Text = txtTwitterUrl.Text = txtLinkedIn.Text = txtTikTok.Text = txtYouTubeUrl.Text = "";
            string LoadValuesQuery = "SELECT [Name], [Value] FROM TenantPlugin WHERE PluginId = 7 AND TenantID = @TenantID";

            using (var dataTable = DataService.GetDataTable(LoadValuesQuery, parmeters: checkParams))
            {
                foreach(DataRow dr in dataTable.Rows)
                {
                    string strKeyName = dr["Name"].ToString();
                    switch (strKeyName)
                    {
                        case "FBUrl":
                            txtFBUrl.Text = dr["Value"].ToString();
                            break;
                        case "InstaUrl":
                            txtInstaUrl.Text = dr["Value"].ToString();
                            break;
                        case "TwitterUrl":
                            txtTwitterUrl.Text = dr["Value"].ToString();
                            break;
                        case "LinkedInUrl":
                            txtLinkedIn.Text = dr["Value"].ToString();
                            break;
                        case "TikTokUrl":
                            txtTikTok.Text = dr["Value"].ToString();
                            break;
                        case "YouTubeUrl":
                            txtYouTubeUrl.Text = dr["Value"].ToString();
                            break;


                    }
                }
            }


            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalSocialMediaURL').modal('toggle');</script>");
        }

        protected async void lbtSMSave_Click(object sender, EventArgs e)
        {
            try
            {
                var insertParams = new List<KeyValuePair<string, object>>()
        {
            new KeyValuePair<string, object>("TenantId", TenantID),
            new KeyValuePair<string, object>("PluginId", 7)
        };

                string strInsertSql = "";

                if (!string.IsNullOrEmpty(txtFBUrl.Text))
                {
                    strInsertSql += "INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, 7, 'FBUrl', @FBUrl); ";
                    insertParams.Add(new KeyValuePair<string, object>("FBUrl", txtFBUrl.Text));
                }
                if (!string.IsNullOrEmpty(txtInstaUrl.Text))
                {
                    strInsertSql += "INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, 7, 'InstaUrl', @InstaUrl); ";
                    insertParams.Add(new KeyValuePair<string, object>("InstaUrl", txtInstaUrl.Text));
                }
                if (!string.IsNullOrEmpty(txtTwitterUrl.Text))
                {
                    strInsertSql += "INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, 7, 'TwitterUrl', @TwitterUrl); ";
                    insertParams.Add(new KeyValuePair<string, object>("TwitterUrl", txtTwitterUrl.Text));
                }
                if (!string.IsNullOrEmpty(txtLinkedIn.Text))
                {
                    strInsertSql += "INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, 7, 'LinkedInUrl', @LinkedInUrl); ";
                    insertParams.Add(new KeyValuePair<string, object>("LinkedInUrl", txtLinkedIn.Text));
                }
                if (!string.IsNullOrEmpty(txtTikTok.Text))
                {
                    strInsertSql += "INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, 7, 'TikTokUrl', @TikTokUrl); ";
                    insertParams.Add(new KeyValuePair<string, object>("TikTokUrl", txtTikTok.Text));
                }
                if (!string.IsNullOrEmpty(txtYouTubeUrl.Text))
                {
                    strInsertSql += "INSERT INTO TenantPlugin (TenantId, PluginId, Name, Value) VALUES (@TenantId, 7, 'YouTubeUrl', @YouTubeUrl); ";
                    insertParams.Add(new KeyValuePair<string, object>("YouTubeUrl", txtYouTubeUrl.Text));
                }

                if (string.IsNullOrWhiteSpace(strInsertSql))
                {
                    Common.ShowToastifyMessage(this.Page, "Please enter at least one social media URL.", "info");
                    return;
                }

                string strSql = "DELETE FROM TenantPlugin WHERE TenantId=@TenantId AND PluginId=7; " + strInsertSql;
                DataService.ExecuteSql(strSql, parmeters: insertParams);

                await ClearPluginCacheAsync(TenantID);

                Common.ShowToastifyMessage(this.Page, "Social Media URLs saved successfully.");

                await LogPluginActivityAsync(this.CurrentUser.APIStoreId,TenantID.ToString(),"Social Media URLs have been added/updated");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", $"An error occurred: {ex.Message}", false);
            }
        }


        //Clear Cache
        private async Task ClearPluginCacheAsync(int tenantId)
        {
            var cacheService = new RedisCacheService();
            string cacheKey = $"Retl.AppTenant.PluginKeys1.{tenantId}";
            await cacheService.RemoveAsync(cacheKey);
        }
        
        //Activity Log
        private async Task LogPluginActivityAsync(int storeGroupId, string tenantId, string actionDescription)
        {
            string pathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            string baseUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(pathAndQuery, "/");
            string source = baseUrl;

            string remarks = $"{actionDescription} by {tenantId} at {DateTime.Now}";
            await Activitylog.ActivitylogAsync(storeGroupId, source, tenantId, remarks);
        }

    }
}
