using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;
using MySql.Data.MySqlClient;

namespace DataEntry
{
    public static class DataServiceMySql
    {
        private static string ConnectionString
        {
            get
            {
                return Environment.GetEnvironmentVariable("dbconnection");
                //return ConfigurationManager.ConnectionStrings["mySqlConnection"].ConnectionString;
            }
        }

        public static DataTable GetDataTable(string sql, string sqlconnection = "", List<KeyValuePair<String, Object>> parmeters = null, bool isSP=false)
        {

            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            DataTable dt = new DataTable();

            using (MySqlConnection connection = new MySqlConnection(APIConnectionString(sqlconnection)))
            {
                connection.Open();
                using (MySqlCommand cmd = new MySqlCommand())
                {
                    cmd.Connection = connection;
                    //cmd.Transaction = tran;
                    if(isSP)
                        cmd.CommandType = CommandType.StoredProcedure;
                    try
                    {
                        if (parmeters != null)
                            FillParams(parmeters, cmd.Parameters);

                        cmd.CommandText = sql;
                        dt.Load(cmd.ExecuteReader());
                    }
                    catch (Exception ex)
                    {
                        throw ex;
                    }
                    finally
                    {
                        connection.Close();
                    }
                }


            }

            return dt;
        }

        public static int ExecuteSql(string sql, string sqlconnection, List<KeyValuePair<String, Object>> parmeters = null, bool isSP=false)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            int count = -1;

            using (MySqlConnection connection = new MySqlConnection(APIConnectionString(sqlconnection)))
            {
                connection.Open();
                using (MySqlCommand cmd = new MySqlCommand())
                {
                    cmd.Connection = connection;
                    //cmd.Transaction = tran;
                    if (isSP)
                        cmd.CommandType = CommandType.StoredProcedure;
                    try
                    {
                        if (parmeters != null)
                            FillParams(parmeters, cmd.Parameters);

                        cmd.CommandText = sql;
                        count = cmd.ExecuteNonQuery();
                    }
                    catch (Exception ex)
                    {
                        throw ex;
                    }
                    finally
                    {
                        connection.Close();
                    }
                }
            }

            return count;
        }

        internal static object GetDataTable(string sql, object aPIConnection)
        {
            throw new NotImplementedException();
        }

        public static int ExecuteSP(string sp, string sqlconnection, List<KeyValuePair<String, Object>> parmeters = null)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            int count = -1;

            using (MySqlConnection connection = new MySqlConnection(APIConnectionString(sqlconnection)))
            {
                connection.Open();
                using (MySqlCommand cmd = new MySqlCommand(sp, connection))
                {
                    try
                    {
                        cmd.CommandType = CommandType.StoredProcedure;
                        if (parmeters != null)
                            FillParams(parmeters, cmd.Parameters);

                        count = cmd.ExecuteNonQuery();
                    }
                    catch (Exception ex)
                    {
                        throw ex;
                    }
                    finally
                    {
                        connection.Close();
                    }
                }
            }

            return count;
        }

        public static object ExecuteScalar(string sql, string sqlconnection, List<KeyValuePair<String, Object>> parmeters = null, bool isSP=false)
        {
            if (string.IsNullOrEmpty(sqlconnection))
                sqlconnection = ConnectionString;

            object result = null;

            using (MySqlConnection connection = new MySqlConnection(APIConnectionString(sqlconnection)))
            {
                connection.Open();
                using (MySqlCommand cmd = new MySqlCommand(sql, connection))
                {
                    try
                    {
                        if (isSP)
                            cmd.CommandType = CommandType.StoredProcedure; 
                        if (parmeters != null)
                            FillParams(parmeters, cmd.Parameters);

                        result = cmd.ExecuteScalar();
                    }
                    catch (Exception ex)
                    {
                        throw ex;
                    }
                    finally
                    {
                        connection.Close();
                    }
                }
            }

            return result;
        }

        public static void FillParams(List<KeyValuePair<String, Object>> parmeters, MySqlParameterCollection prms)
        {
            if (parmeters != null)
            {
                foreach (KeyValuePair<String, Object> strparams in parmeters)
                {
                    MySqlParameter param = new MySqlParameter();
                    param.ParameterName = String.Format("@{0}", strparams.Key);
                    param.Value = strparams.Value;
                    prms.Add(param);
                }
            }
        }


        /// <summary>
        /// APIConnectionString - return the mysql connection string to access API database.
        /// The function get the database name as input parameter and get the remaining connection string from config file and return the resolved full string by combine both.
        /// </summary>
        /// <param name="APIDB">Encrypted Database name or connection string. If it is a full connection string then just return it.</param>
        /// <returns>Mysql DB connection string</returns>
        public static string APIConnectionString(string APIDB)
        {
            // If the input parameter APIDB is a complete connection string then return it.
            if (APIDB.Replace(" ", "").ToLower().Contains("server="))
                return APIDB;

            //string mySqlCon = ConfigurationManager.ConnectionStrings["mySqlConnection"].ConnectionString;
            string mySqlCon = Environment.GetEnvironmentVariable("dbconnection");

            return String.Format(mySqlCon, EncryptionService.DecryptText(APIDB));

        }

    }
}