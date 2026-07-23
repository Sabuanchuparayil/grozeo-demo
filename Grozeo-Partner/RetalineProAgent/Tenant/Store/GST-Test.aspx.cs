using RetalineProAgent.Core.Services;
using RetalineProAgent.Service.Store;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant.Store
{
    public partial class GST_Test : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            lblResult.Text = "";
        }

        protected void btnAddGST_Click(object sender, EventArgs e)
        {

            //txtGST.Text;


            string strGSTPattern = "[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}";
            if (string.IsNullOrEmpty(txtGST.Text) || !Regex.Match(txtGST.Text, strGSTPattern, RegexOptions.IgnoreCase).Success)
            {
                lblResult.Text = "Invalid GST format";
                Common.ShowToastifyMessage(this.Page, $"Invalid GST format!! Please enter valid GST format and try again.", "danger");
                return;
            }
            ValidateGSTChecksum(txtGST.Text);
        }


        private void ValidateGSTChecksum(string gstNumber)
        {
            // Check if the GST number has at least 15 characters
            if (gstNumber.Length < 15)
            {
                lblResult.Text = "Invalid GST Format. GST should be 15 letters";
                Common.ShowCustomAlert(this.Page, "Invalid GST Format", "GST should be 15 letters", false);
                return;
                //throw new ArgumentException("GST Number is too short");
            }

            // Extract the first 14 digits of the GST number
            string first14Digits = gstNumber.Substring(0, 14);

            // Calculate the checksum
            int total = 0;
            int[] weights = { 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2 };

            for (int i = 0; i < 14; i++)
            {
                char currentChar = first14Digits[i];
                int digit = -1;
                if (!char.IsDigit(currentChar))
                {
                    int position = (int)currentChar - (int)'A' + 1;
                    digit = position + 9;
                }
                else
                {
                    digit = int.Parse(currentChar.ToString());
                }

                if (digit >= 0)
                {
                    int weightedDigit = digit * weights[i];
                    int quotientval = Math.Abs(weightedDigit / 36);
                    int modval = weightedDigit % 36;

                    int calcval = quotientval + modval;

                    total += calcval;
                }
                else
                {
                    throw new ArgumentException("Invalid character in GST Number");
                }
            }

            char[] strChecksum = "000000000ABCDEFGHIJKLMNOPQRSTUVWXYZ0".ToCharArray();
            int chksumModval = total % 36;
            int finalR = 36 - chksumModval;

            if (finalR <= 0 || finalR > 36)
            {
                lblResult.Text = "Invalid checksum value: "+finalR;
                Common.ShowToastifyMessage(this.Page, "Invalid checksum value: " + finalR, "danger");
                return;
            }
            // Calculate the checksum character
            char retVal = (finalR <= 9 ? finalR.ToString().ToCharArray()[0] : strChecksum[finalR - 1]);
            if (gstNumber.EndsWith(retVal.ToString()))
            {
                lblResult.Text = "GSTIN validated successfully!! Checksum: "+retVal;
                Common.ShowToastifyMessage(this.Page, "GSTIN validated successfully!! Checksum: " + retVal);
            }
            else
            {
                lblResult.Text = "Invalid GSTIN!! Checksum does not match: " + retVal;
                Common.ShowToastifyMessage(this.Page, "Invalid GSTIN!! Checksum does not match: " + retVal, "danger");

            }

        }

    }
}