using RetalineProAgent.Service;
using NPOI.POIFS.Properties;
using Org.BouncyCastle.Asn1.Ocsp;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Data;
using System.Data.SqlTypes;
using System.Diagnostics;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Xml.Linq;
using RetalineProAgent.Core.Services;
using System.Configuration;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.Services.ActiveLog;
using NPOI.SS.Formula.Functions;

namespace RetalineProAgent.Finance
{
    public partial class LedgerCreation: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void btncreatenew_Click(object sender, EventArgs e)
        {
            pnlnewledgers.Visible = true;
            pnlledgerdetailes.Visible    = false;
            pnlledger_updetes.Visible = false;
        }

        protected void btnhide_Click(object sender, EventArgs e)
        {
            
            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
            {
                btnedit.Enabled=true;
                hidledger.Value = lbtn.Attributes["dataid"];
                int LedId = Convert.ToInt32(hidledger.Value);
                List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
                sqlId.Add(new KeyValuePair<string, object>("Ledid", LedId));
               
                var ledgerid = DataService.GetDataTable("DetailsofLedger", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlId, isSP: true);

                if (ledgerid != null && ledgerid.Rows.Count > 0)
                {
                    var ledgername = ledgerid.Rows[0];
                    ltrnameledger.Text = ledgername["name"].ToString();
                    ltrgroup.Text = ledgername["groupname"].ToString();
                    ltrOpening.Text = String.Format("{0:0.00}", ledgername["opening"]).ToString();
                    if (ledgername["isSystem"].ToString() == "1")
                    {
                        btnedit.Visible = false;
                    }
                    else
                    {
                        btnedit.Visible = true;
                    }
                    if (ledgername["hasCostCentre"].ToString() == "True")
                    {
                        ltrcostcentre.Text = "Yes";
                    }
                    else
                    {
                        ltrcostcentre.Text = "No";
                    }

                    if (new string[] { "56", "167" }.Contains(ledgername["groups_id"].ToString()))
                    {

                        pnlcredit_update.Visible = true;
                        pnlde_update.Visible = true;
                        ltrname.Text = ledgername["legalname"].ToString();
                        ltradress.Text = ledgername["Address"].ToString();
                        ltrState.Text = ledgername["stateName"].ToString();
                        ltrPincode.Text = ledgername["Pincode"].ToString();
                        ltrCountry.Text = ledgername["place"].ToString();
                        ltrphoneno.Text = ledgername["Phone _No"].ToString();
                        ltrwebsite.Text = ledgername["Website"].ToString();
                        ltrcontactdetails.Text = ledgername["Contact_ Person"].ToString();
                        ltrmobileno.Text = ledgername["Contact_mobile_no"].ToString();
                        ltremail.Text = ledgername["Contact_email_1"].ToString();
                        ltrtransactiotype.Text = ledgername["TransactionType"].ToString();
                        ltraccountholder.Text = ledgername["Account _Holder"].ToString();
                        ltraccountnumber.Text = ledgername["Account _Number"].ToString();
                        ltrifsccode_update.Text = ledgername["IFSC _Code"].ToString();
                        ltrbankname.Text = ledgername["Bank _Name"].ToString();
                        ltrbranch.Text = ledgername["Branch"].ToString();
                        ltrpan.Text = ledgername["PAN_IT No"].ToString();
                        ltrgsttype.Text = ledgername["GSTRegistrationType"].ToString();
                        ltrgstin.Text = ledgername["GSTIN_UIN"].ToString();
                    }
                    else
                    {
                        pnlcredit_update.Visible = false;
                        pnlde_update.Visible = false;
                    }
                    if (ledgername["GST_applicable"].ToString() == "True")
                    {
                        ltrapplicable.Text = "yes";
                    }
                    else
                    {
                        ltrapplicable.Text = "no";
                    }

                    ltrtypeofsupply.Text = ledgername["supplytype"].ToString();

                    if (ledgername["TDS_applicable"].ToString() == "True")
                    {
                        ltrlapplicable.Text = "Yes";
                    }
                    else
                    {
                        ltrlapplicable.Text = "No";
                    }
                    ltrnatureofpayment.Text = "";
                    ltrdeducteetypr.Text = ledgername["Deducteetype"].ToString();
                    ltrTDSdeductible.Text = ledgername["TDS_applicable"].ToString();
                    if (ledgername["TDS_applicable"].ToString() == "True")
                    {
                        ltrTDSdeductible.Text = "Yes";
                    }
                    else
                    {
                        ltrTDSdeductible.Text = "No";
                    }
                    ltrdeducteetypr.Text = ledgername["Deducteetype"].ToString();

                    if (ledgername["Assessee _ Other _Territory"].ToString() == "True")
                    {
                        ltrassessofother.Text = "yes";
                    }
                    else
                    {
                        ltrassessofother.Text = "no";
                    }
                    if (ledgername["e_commerce_operator"].ToString() == "True")
                    {
                        ltrcommerce.Text = "yes";
                    }
                    else
                    {
                        ltrcommerce.Text = "no";
                    }
                    if (ledgername["deemed_exporter_purchases"].ToString() == "True")
                    {
                        ltrdeemed.Text = "yes";
                    }
                    else
                    {
                        ltrdeemed.Text = "No";
                    }
                    ltrpartype.Text = ledgername["partytype"].ToString();
                    if (ledgername["transporter"].ToString() == "True")
                    {
                        ltrwhethertranspoter.Text = "yes";
                    }
                    else
                    {
                        ltrwhethertranspoter.Text = "No";
                    }
                    ltrtranspoterid.Text = ledgername["Transporter _ID"].ToString();
                }
            }
        }

        protected void btnedit_Click(object sender, EventArgs e)
        {

            pnlledger_updetes.Visible = true;
            pnlledgerdetailes.Visible = false;
            int Id = Convert.ToInt32(hidledger.Value);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("Ledid", Id));           
            var ledgername = DataService.GetDataTable("DetailsofLedger", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId, isSP: true);
            if (ledgername != null && ledgername.Rows.Count > 0)
            {
                var led = ledgername.Rows[0];
                txtledgerupdate.Text = led["name"].ToString();
                if (selGroup.Items.Count <= 1)
                    selGroup.DataBind();
                if (selGroup.Items.FindByValue(led["groups_id"].ToString()) != null)
                {
                    selGroup.SelectedIndex = selGroup.Items.IndexOf(selGroup.Items.FindByValue(led["groups_id"].ToString()));

                }
                txtAmountUpdate.Text = String.Format("{0:0.00}", led["opening"]).ToString();

                if (new string[] { "56", "167" }.Contains(led["groups_id"].ToString()))
                {
                    //pnlcredit_update.Visible = true;
                    //pnlde_update.Visible = true;

                    pnlvisib_update.Visible = true;
                    pnlvisible_up.Visible = true;
                }
                else
                {
                    pnlvisib_update.Visible = false;
                    pnlvisible_up.Visible = false;
                }
                if (ddltypeofsupply.Items.Count <= 1)
                    ddltypeofsupply.DataBind();
                if (ddltypeofsupply.Items.FindByValue(led["Supply_type"].ToString()) != null)
                {
                    ddltypeofsupply.SelectedIndex = ddltypeofsupply.Items.IndexOf(ddltypeofsupply.Items.FindByValue(led["Supply_type"].ToString()));
                }
                if (ddlducteetype.Items.Count <= 1)
                    ddlducteetype.DataBind();
                if (ddlducteetype.Items.FindByValue(led["Deductee_type"].ToString()) != null)
                {
                    ddlducteetype.SelectedIndex = ddlducteetype.Items.IndexOf(ddlducteetype.Items.FindByValue(led["Deductee_type"].ToString()));
                }
                txtlegalname_update.Text = led["legalname"].ToString();
                txtaddress_update.Text = led["Address"].ToString();
                if (ddlstete_update.Items.Count <= 1)
                    ddlstete_update.DataBind();
                if (ddlstete_update.Items.FindByValue(led["State"].ToString()) != null)
                {
                    ddlstete_update.SelectedIndex = ddlstete_update.Items.IndexOf(ddlstete_update.Items.FindByValue(led["State"].ToString()));
                }
                if (ddlcountry_update.Items.Count <= 1)
                    ddlcountry_update.DataBind();
                if (ddlcountry_update.Items.FindByValue(led["Country"].ToString()) != null)
                {
                    ddlcountry_update.SelectedIndex = ddlcountry_update.Items.IndexOf(ddlcountry_update.Items.FindByValue(led["Country"].ToString()));
                }
                txtpincode_update.Text = led["Pincode"].ToString();
                txtwebsite_update.Text = led["Website"].ToString();
                txtphonenumber_update.Text = led["Phone _No"].ToString();
                txtcontactperson_update.Text = led["Contact_ Person"].ToString();
                txtmobileno_update.Text = led["Contact_mobile_no"].ToString();
                txtemail_update.Text = led["Contact_email_1"].ToString();
                if (ddltransactiontype.Items.Count <= 1)
                    ddltransactiontype.DataBind();
                if (ddltransactiontype.Items.FindByValue(led["Transaction _Type"].ToString()) != null)
                {
                    ddltransactiontype.SelectedIndex = ddltransactiontype.Items.IndexOf(ddltransactiontype.Items.FindByValue(led["Transaction _Type"].ToString()));
                }
                txtaccountholder_update.Text = led["Account _Holder"].ToString();
                txtaccountnumber_update.Text = led["Account _Number"].ToString();
                txtifsc_update.Text = led["IFSC _Code"].ToString();
                txtbankname_update.Text = led["Bank _Name"].ToString();
                txtbranch_update.Text = led["Branch"].ToString();
                txtpan_update.Text = led["PAN_IT No"].ToString();
                if (ddlGstregistrationtype_update.Items.Count <= 1)
                    ddlGstregistrationtype_update.DataBind();
                if (ddlGstregistrationtype_update.Items.FindByValue(led["GST _Registration _Type"].ToString()) != null)
                {
                    ddlGstregistrationtype_update.SelectedIndex = ddlGstregistrationtype_update.Items.IndexOf(ddlGstregistrationtype_update.Items.FindByValue(led["GST _Registration _Type"].ToString()));
                }
                txtgstin_update.Text = led["GSTIN_UIN"].ToString();
                if (ddlpartytype_update.Items.Count <= 1)
                    ddlpartytype_update.DataBind();
                if (ddlpartytype_update.Items.FindByValue(led["party_type"].ToString()) != null)
                {
                    ddlpartytype_update.SelectedIndex = ddlpartytype_update.Items.IndexOf(ddlpartytype_update.Items.FindByValue(led["party_type"].ToString()));
                }
                txttransporterid_update.Text = led["Transporter _ID"].ToString();
                if (led["hasCostCentre"].ToString() == "True")
                {
                    costcentreyes_update.Checked = true;
                    costcentreno_update.Checked=false;
                }
                else
                {
                    costcentreyes_update.Checked = false;
                    costcentreno_update.Checked = true;
                }
                if (led["GST_applicable"].ToString() == "True")
                {
                    gstyes_upadete.Checked = true;
                    gstno_updete.Checked = false;
                }
                else
                {
                    gstyes_upadete.Checked = false;
                    gstno_updete.Checked = true;
                }

                if (led["TDS_applicable"].ToString() == "True")
                {
                    TDSyes_update.Checked = true;
                    TDSno_update.Checked = false;
                }
                else
                {
                    TDSyes_update.Checked = false;
                    TDSno_update.Checked = true;
                }
                if (led["TDS_deductible"].ToString() == "True")
                {
                    TDSDeductibleyes_update.Checked = true;
                    TDSDeductibleno_update.Checked = false;
                }
                else
                {
                    TDSDeductibleyes_update.Checked = false;
                    TDSDeductibleno_update.Checked = true;
                }
                if (led["Assessee _ Other _Territory"].ToString() == "True")
                {
                    Territoryyes_update.Checked = true;
                    Territoryno_update.Checked = false;


                }
                else
                {
                    Territoryyes_update.Checked = false;
                    Territoryno_update.Checked = true;
                }
                if (led["e_commerce_operator"].ToString() == "True")
                {
                    Ecommerceyes_update.Checked = true;
                    Ecommerceno_update.Checked = false;
                }
                else
                {
                    Ecommerceyes_update.Checked = false;
                    Ecommerceno_update.Checked = true;
                }
                if (led["deemed_exporter_purchases"].ToString() == "True")
                {
                    purchasesyes_update.Checked = true;
                    purchasesno_update.Checked = false;
                }
                else
                {
                    purchasesyes_update.Checked = false;
                    purchasesno_update.Checked = true;
                }
                if (led["transporter"].ToString() == "True")
                {
                    Transporteryes_update.Checked = true;
                    Transportereno_update.Checked = false;
                }
                else
                {
                    Transporteryes_update.Checked = false;
                    Transportereno_update.Checked = true;
                }

            }
            if (selGroup.SelectedItem.Text != null)
            {
               
                int ledgerid = 0;
                ledgerid = Convert.ToInt32(selGroup.SelectedItem.Value);
                List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
                sqlId.Add(new KeyValuePair<string, object>("Ledid", ledgerid));
                string group = $"Select g.id, g.[name], account_types_id,(case when g.id in (select  g.id from groups g where g.parent_id in (select s.[id]  from[groups] s where parent_id = 0)) then" +
                    $"(select s.name from groups s where s.id = g.parent_id) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0)))then" +
                    $"(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then" +
                    $"(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) end ) as primarygroup,(case when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id = 0 )) then" +
                    $"(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select[id] from[groups] where parent_id = 0)))then(select s.name from groups s where s.id = g.parent_id) when g.id in " +
                    $"(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) end ) as maingroup,(case when g.id in (select g.id from groups g where g.parent_id in(select[id] from[groups] where parent_id = 0 )) " +
                    $"then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0)))then" +
                    $"(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in (select g.id from groups g where g.parent_id in(select g.[id] from[groups] g where g.parent_id in(select s.[id] from[groups] s where s.parent_id != 0))) then" +
                    $"(select s.name from groups s where s.id = g.parent_id) end ) as subgroup,parent_id,(case when parent_id = 0 then 'Primary Group' when parent_id in(select[id] from[groups] where parent_id = 0 ) then 'Main Group' else 'Sub Group' end ) as GroupType, isSystem, ac.id as natureid, ac.nature from[groups] g " +
                    $"inner join[account_types] ac on g.account_types_id = ac.id where g.id=@Ledid order by parent_id";
                DataTable groupname = DataService.GetDataTable(group,ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlId);
                if (groupname != null && groupname.Rows.Count > 0)
                {
                    var name = groupname.Rows[0];
                    ltrnature.Text = name["nature"].ToString();
                    ltrprimary.Text = name["primarygroup"].ToString();
                    ltrmain.Text = name["maingroup"].ToString();
                    ltrsubgroup.Text = name["subgroup"].ToString();


                }
            }



        }

        protected void btnsave_Click(object sender, EventArgs e)
        {
            int groupid = 0;
            int Typeid = 0;
            int Natureofpaymentrid = 0;
            int Deducteeid = 0;
            int GSTRegistrationid = 0;
            int PartyTypeid = 0;

            int transcationtypeid = 0;
            Typeid = Convert.ToInt32(dplsupplyservice.Text);
            // Natureofpaymentrid = Convert.ToInt32(ddlnature_update.Text);
            Deducteeid = Convert.ToInt32(dpldeductee.Text);

           
            if (!String.IsNullOrEmpty(ddlgroup.Text))
                groupid = Convert.ToInt32(ddlgroup.Text);
            int nature = Convert.ToInt32(dlentrytpeupdate.SelectedItem.Value);
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();           
            sidparams.Add(new KeyValuePair<string, object>("name", txtledgerName.Text));
            if (new string[] { "56", "167" }.Contains(groupid.ToString()))
            {
                pnldebitor_update.Visible = true;
                plccreditor.Visible = true;
                PartyTypeid = Convert.ToInt32(ddlpartytype.Text);
                transcationtypeid = Convert.ToInt32(ddltranscationtype.Text);
                GSTRegistrationid = Convert.ToInt32(ddlgstregistrationtype.Text);
            }
            else
            {
                pnldebitor_update.Visible = false;
                plccreditor.Visible = false;
            }

            sidparams.Add(new KeyValuePair<string, object>("Legalname", txtlegalname.Text));
            sidparams.Add(new KeyValuePair<string, object>("Address", txtaddress.Text));
            sidparams.Add(new KeyValuePair<string, object>("Pincode", txtpincode.Text));
            sidparams.Add(new KeyValuePair<string, object>("Website", txtwebsite.Text));
            sidparams.Add(new KeyValuePair<string, object>("Contactperson", txtcontactperson.Text));
            sidparams.Add(new KeyValuePair<string, object>("MobileNo", txtmobile_no.Text));
            sidparams.Add(new KeyValuePair<string, object>("Email", txtEmail.Text));
            sidparams.Add(new KeyValuePair<string, object>("Accountholder", txtaccountholder.Text));
            sidparams.Add(new KeyValuePair<string, object>("Accountnumber", txtaccountnumber.Text));
            sidparams.Add(new KeyValuePair<string, object>("IFSC", txtIFSC.Text));
            sidparams.Add(new KeyValuePair<string, object>("Bankname", txtbankname.Text));
            sidparams.Add(new KeyValuePair<string, object>("Branch", txtbranch.Text));
            sidparams.Add(new KeyValuePair<string, object>("PAN", txtpan_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("GSTIN", txtGSTIN.Text));
            sidparams.Add(new KeyValuePair<string, object>("Typeid", Typeid));
            sidparams.Add(new KeyValuePair<string, object>("Natureofpaymentrid", Natureofpaymentrid));
            if (costcentreyes.Checked && costcentreno.Checked == false)
            {
                sidparams.Add(new KeyValuePair<string, object>("costcentre", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("costcentre", 0));
            }
            if (TDSyes.Checked && TDSno.Checked == false)
            {
                sidparams.Add(new KeyValuePair<string, object>("IT_TDS_Applicable", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("IT_TDS_Applicable", 0));
            }
            if (TDSDeductibleyes.Checked && TDSDeductibleno.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("TDS_Deductible", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("TDS_Deductible", 0));
            }
            if (Territoryyes.Checked && Territoryno.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("Assessee_Other_Territory", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("Assessee_Other_Territory", 0));
            }
            if (Ecommerceyes.Checked && Ecommerceno.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("e_Commerce_Operator", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("e_Commerce_Operator", 0));
            }
            if (purchasesyes.Checked && purchasesno.Checked == false )
            {
                sidparams.Add(new KeyValuePair<string, object>("Deemed_exporter_purchases", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("Deemed_exporter_purchases", 0));
            }
            if (Transporteryes.Checked && Transportereno.Checked == false)
            {
                sidparams.Add(new KeyValuePair<string, object>("Whether_Transporter", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("Whether_Transporter", 0));
            }

            sidparams.Add(new KeyValuePair<string, object>("groupid", groupid));


            sidparams.Add(new KeyValuePair<string, object>("Deducteeid", Deducteeid));
            sidparams.Add(new KeyValuePair<string, object>("GSTRegistrationid", GSTRegistrationid));
            sidparams.Add(new KeyValuePair<string, object>("PartyTypeid", PartyTypeid));
            sidparams.Add(new KeyValuePair<string, object>("state", ddlstate.Text));
            sidparams.Add(new KeyValuePair<string, object>("transcationtypeid", transcationtypeid));
            sidparams.Add(new KeyValuePair<string, object>("country", ddlcountry.Text));
            sidparams.Add(new KeyValuePair<string, object>("transpoter_id", txttranspoter_id.Text));
            sidparams.Add(new KeyValuePair<string, object>("phonenumber", txtPhoneNo.Text));
            if (nature == 1)
            {
                sidparams.Add(new KeyValuePair<string, object>("debitamount", Convert.ToDouble(txtamount.Text)));
                sidparams.Add(new KeyValuePair<string, object>("creditamonut", 0.00));
            }
            else
            {
                sidparams.Add(new KeyValuePair<string, object>("creditamonut", Convert.ToDouble(txtamount.Text)));
                sidparams.Add(new KeyValuePair<string, object>("debitamount", 0.00));
            }
            if (gstyes.Checked && gstno.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("GST_Applicable", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("GST_Applicable", 0));
            }

            string cnt = null;
            DataTable ledgercount = DataService.GetDataTable($"select count(1) as count from ledger where [name]=@name", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("name", txtledgerName.Text) });
            if (ledgercount != null && ledgercount.Rows.Count > 0)
            {
                DataRow da = ledgercount.Rows[0];
                cnt = da["count"].ToString();
            }
            int count = Convert.ToInt32(cnt);
            if (count > 0)
            {
                lbledgermgmt.Text = "ledger is already exist";

            }
            else
            {



                //string led = "insert into ledger ([name],groups_id,isSystem,isEnabled,groups_refId,company_id,company_refId,branch_id,branch_refId,isApiCreated) values(@name, @groupid,0,1,(select  refId from groups where id = @groupid),10,(select TOP 1   refId  from company ),1,(select TOP 1   refId  from company branch where isActive=1),0)";
                int ledger = DataService.ExecuteSql("Ledgercreation", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams, isSP: true);               
                    lbledgermgmt.Text = "";
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = -1;
                string User = "Finance Admin";
                string Legalname = txtlegalname_update.Text;
                string name = txtledgerName.Text;
                string natures = dlentrytpeupdate.SelectedItem.Value;
                string group_id = Convert.ToString(ddlgroup.Text);
                string Address = txtpincode.Text;
                string Website = txtwebsite.Text;
                string Contactperson = txtcontactperson.Text;
                string MobileNo = txtmobile_no.Text;
                string Email = txtEmail.Text;
                string Accountholder = txtaccountholder.Text;
                string Accountnumber = txtaccountnumber.Text;
                string IFSC = txtIFSC.Text;
                string Bankname = txtbankname.Text;
                string Branch = txtbranch.Text;
                string PAN = txtPAN.Text;
                string GSTIN = txtGSTIN.Text;
                string Type_id = Convert.ToString(Typeid);
                string GSTRegistration_id = Convert.ToString(GSTRegistrationid);
                string PartyType_id = Convert.ToString(PartyTypeid);
                string state = Convert.ToString(ddlstete_update.Text);
                string transcationtype_id = Convert.ToString(transcationtypeid);
                string create = "Ledger";

                var items = new[]
                    {
                    new { Key = "Legalname", Value = Legalname },
                    new { Key = "name", Value = name },
                    new { Key = "group_id", Value = group_id },
                    new { Key = "Address", Value = Address },
                    new { Key = "Website", Value = Website },
                    new { Key = "Contactperson", Value = Contactperson },
                    new { Key = "MobileNo", Value = MobileNo },
                    new { Key = "MobileNo", Value = MobileNo },
                    new { Key = "Email", Value = Email },
                    new { Key = "Accountholder", Value = Accountholder },
                    new { Key = "Accountnumber", Value = Accountnumber },
                    new { Key = "IFSC", Value = IFSC },
                    new { Key = "Bankname", Value = Bankname },
                    new { Key = "Branch", Value = Branch },
                    new { Key = "PAN", Value = PAN },
                    new { Key = "GSTIN", Value = GSTIN },
                    new { Key = "Type_id", Value = Type_id },
                    new { Key = "GSTRegistration_id", Value = GSTRegistration_id },
                    new { Key = "PartyType_id", Value = PartyType_id },
                    new { Key = "state", Value = state },
                    new { Key = "transcationtype_id", Value = transcationtype_id },
                    new { Key = "create", Value = create },
                };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/LedgerManagement");

            }
        }

        

        protected void ddlgroup_SelectedIndexChanged(object sender, EventArgs e)
        {
            plcshow.Visible = true;
            int group = 0;
            if (!String.IsNullOrEmpty(ddlgroup.Text))
                group = Convert.ToInt32(ddlgroup.Text);
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            sidparams.Add(new KeyValuePair<string, object>("groupid", group));
            DataTable groupname = DataService.GetDataTable($"Select g.id, g.[name], account_types_id,(case when g.id in (select  g.id from groups g where g.parent_id in (select s.[id]  from[groups] s where parent_id = 0)) then(select s.name from groups s where s.id = g.parent_id) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0)))then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) end ) as primarygroup,(case when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id = 0 )) then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select[id] from[groups] where parent_id = 0)))then(select s.name from groups s where s.id = g.parent_id) when g.id in (select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id != 0))) then(select p.name from groups p where p.id = (select s.parent_id from groups s where s.id = g.parent_id)) end ) as maingroup,(case when g.id in (select g.id from groups g where g.parent_id in(select[id] from[groups] where parent_id = 0 )) then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in(select g.id from groups g where g.parent_id in(select s.[id] from[groups] s where s.parent_id in(select g.[id] from[groups] g where g.parent_id = 0)))then(select s.name from groups s where s.id = (select s.id from groups s where s.id = (select s.parent_id from groups s where s.id = (select s.parent_id from groups s where s.id = g.parent_id) ))) when g.id in (select g.id from groups g where g.parent_id in(select g.[id] from[groups] g where g.parent_id in(select s.[id] from[groups] s where s.parent_id != 0))) then(select s.name from groups s where s.id = g.parent_id) end ) as subgroup,parent_id,(case when parent_id = 0 then 'Primary Group' when parent_id in(select[id] from[groups] where parent_id = 0 ) then 'Main Group' else 'Sub Group' end ) as GroupType, isSystem, ac.id as natureid, ac.nature from[groups] g inner join[account_types] ac on g.account_types_id = ac.id where g.id =@groupid order by parent_id", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sidparams);
            if (groupname != null && groupname.Rows.Count > 0)
            {
                var name = groupname.Rows[0];
                ltlnatureofgroup.Text = name["nature"].ToString();
                ltlprimarygroup.Text = name["primarygroup"].ToString();
                ltlmaingroup.Text = name["maingroup"].ToString();
                ltlsub.Text = name["subgroup"].ToString();
                if (new string[] { "56", "167" }.Contains(name["id"].ToString()))
                {
                    pnldebitor_update.Visible = true;
                    plccreditor.Visible = true;
                }
                else
                {
                    pnldebitor_update.Visible = false;
                    plccreditor.Visible = false;
                }


            }
        }

        protected void selGroup_SelectedIndexChanged(object sender, EventArgs e)
        {
        }

        protected void btnupdate_Click(object sender, EventArgs e)
        {


            int Id = Convert.ToInt32(hidledger.Value);
            int groupid = Convert.ToInt32(selGroup.SelectedItem.Value);
            int Typeid = Convert.ToInt32(ddltypeofsupply.Text);
            int Natureofpaymentrid = Convert.ToInt32(ddlducteetype.Text);
            int Deducteeid = Convert.ToInt32(ddlducteetype.Text);
            int GSTRegistrationid = 0;
            int PartyTypeid = 0;
            int transcationtypeid = 0;           
            //int nature = Convert.ToInt32(ddlnaturetupeupdate.SelectedItem.Value);
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            sidparams.Add(new KeyValuePair<string, object>("id", Id));
            sidparams.Add(new KeyValuePair<string, object>("groupid", groupid));
            if (new string[] { "56", "167" }.Contains(groupid.ToString()))
            {
                GSTRegistrationid = Convert.ToInt32(ddlGstregistrationtype_update.Text);
                PartyTypeid = Convert.ToInt32(ddlpartytype_update.Text);
                transcationtypeid = Convert.ToInt32(ddlpartytype_update.Text);

            }
            sidparams.Add(new KeyValuePair<string, object>("Legalname", txtlegalname_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Address", txtaddress_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Pincode", txtpincode_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Website", txtwebsite_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Contactperson", txtcontactperson_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("MobileNo", txtmobileno_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Email", txtemail_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Accountholder", txtaccountholder_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Accountnumber", txtaccountnumber_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("IFSC", txtifsc_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Bankname", txtbankname_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Branch", txtbranch_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("PAN", txtPAN.Text));
            sidparams.Add(new KeyValuePair<string, object>("GSTIN", txtgstin_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("Typeid", Typeid));
            sidparams.Add(new KeyValuePair<string, object>("Natureofpaymentrid", Natureofpaymentrid));
            sidparams.Add(new KeyValuePair<string, object>("GSTRegistrationid", GSTRegistrationid));
            sidparams.Add(new KeyValuePair<string, object>("PartyTypeid", PartyTypeid));
            sidparams.Add(new KeyValuePair<string, object>("state", ddlstete_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("transcationtypeid", transcationtypeid));

            sidparams.Add(new KeyValuePair<string, object>("country", ddlcountry_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("transpoter_id", txttransporterid_update.Text));
            sidparams.Add(new KeyValuePair<string, object>("phonenumber", txtphonenumber_update.Text));
            if (costcentreyes_update.Checked && costcentreno_update.Checked == false)
            {
                sidparams.Add(new KeyValuePair<string, object>("costcentre", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("costcentre", 0));
            }
            if (TDSDeductibleyes_update.Checked && TDSDeductibleno_update.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("TDS_Deductible", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("TDS_Deductible", 0));
            }
            if (Territoryyes_update.Checked && Territoryno_update.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("Assessee_Other_Territory", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("Assessee_Other_Territory", 0));
            }
            if (Ecommerceyes_update.Checked && Ecommerceno_update.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("e_Commerce_Operator", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("e_Commerce_Operator", 0));
            }
            if (purchasesyes_update.Checked && purchasesno_update.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("Deemed_exporter_purchases", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("Deemed_exporter_purchases", 0));
            }
            if (Transporteryes_update.Checked && Transportereno_update.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("Whether_Transporter", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("Whether_Transporter", 0));
            }
            sidparams.Add(new KeyValuePair<string, object>("name", txtledgerupdate.Text));

            sidparams.Add(new KeyValuePair<string, object>("Deducteeid", Deducteeid));


            if (gstyes_upadete.Checked && gstno_updete.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("GST_Applicable", 1));
            }
            else
            {
                sidparams.Add(new KeyValuePair<string, object>("GST_Applicable", 0));
            }
            if (TDSyes_update.Checked && TDSno_update.Checked==false)
            {
                sidparams.Add(new KeyValuePair<string, object>("IT_TDS_Applicable", 1));
            }
            else 
            {
                sidparams.Add(new KeyValuePair<string, object>("IT_TDS_Applicable", 0));
            }


            int ledger = DataService.ExecuteSql("LedgerUpdate", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams,isSP:true);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = -1;
            string User = "Finance Admin";
            string Legalname = txtlegalname_update.Text;
            string name = txtledgerupdate.Text;
            string group_id = Convert.ToString(selGroup.SelectedItem.Value);
            string Address = txtpincode_update.Text;
            string Website = txtwebsite_update.Text;
            string Contactperson = txtcontactperson_update.Text;
            string MobileNo = txtmobileno_update.Text;
            string Email = txtemail_update.Text;
            string Accountholder = txtaccountholder_update.Text;
            string Accountnumber = txtaccountnumber_update.Text;
            string IFSC = txtifsc_update.Text;
            string Bankname = txtbankname_update.Text;
            string Branch = txtbranch_update.Text;
            string PAN = txtPAN.Text;
            string GSTIN = txtgstin_update.Text;
            string Type_id =Convert.ToString(Typeid);
            string GSTRegistration_id = Convert.ToString(GSTRegistrationid);
            string PartyType_id = Convert.ToString(PartyTypeid);
            string state = Convert.ToString(ddlstete_update.Text);
            string transcationtype_id = Convert.ToString(transcationtypeid);
            string create = "Ledger";

            var items = new[]
                {
                    new { Key = "Legalname", Value = Legalname },
                    new { Key = "name", Value = name },
                    new { Key = "group_id", Value = group_id },
                    new { Key = "Address", Value = Address },
                    new { Key = "Website", Value = Website },
                    new { Key = "Contactperson", Value = Contactperson },
                    new { Key = "MobileNo", Value = MobileNo },
                    new { Key = "MobileNo", Value = MobileNo },
                    new { Key = "Email", Value = Email },
                    new { Key = "Accountholder", Value = Accountholder },
                    new { Key = "Accountnumber", Value = Accountnumber },
                    new { Key = "IFSC", Value = IFSC },
                    new { Key = "Bankname", Value = Bankname },
                    new { Key = "Branch", Value = Branch },
                    new { Key = "PAN", Value = PAN },
                    new { Key = "GSTIN", Value = GSTIN },
                    new { Key = "Type_id", Value = Type_id },
                    new { Key = "GSTRegistration_id", Value = GSTRegistration_id },
                    new { Key = "PartyType_id", Value = PartyType_id },
                    new { Key = "state", Value = state },
                    new { Key = "transcationtype_id", Value = transcationtype_id },
                    new { Key = "create", Value = create },
                };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
            Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/LedgerManagement");

        }

        protected void btncanel_update_Click(object sender, EventArgs e)
        {
            pnlnewledgers.Visible = false;
            pnlledger_updetes.Visible = false;
            pnlledgerdetailes.Visible = true;
        }

        protected void btncancel_Click(object sender, EventArgs e)
        {
            pnlledger_updetes.Visible = false;
            pnlledgerdetailes.Visible = true;
        }

        protected void lvledger_DataBound(object sender, EventArgs e)
        {
            if (lvledger.Items.Count > 0 && (String.IsNullOrEmpty(hidledger.Value) || hidledger.Value == "0"))
            {
                LinkButton lbtn = (LinkButton)lvledger.Items[0].FindControl("btnhide");
                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
                {
                    hidledger.Value = lbtn.Attributes["dataid"];
                    lvledger.SelectedIndex = 0;

                }
                Loadinfo();
            }
      
        }



        private void Loadinfo()
        {

           
            int Id = Convert.ToInt32(hidledger.Value);
            if (Id > 0)
            {
                List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
                sqlId.Add(new KeyValuePair<string, object>("Ledid", Id));

                var ledgerid = DataService.GetDataTable("DetailsofLedger", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlId, isSP: true);

                if (ledgerid != null && ledgerid.Rows.Count > 0)
                {
                    var ledgername = ledgerid.Rows[0];
                    ltrnameledger.Text = ledgername["name"].ToString();
                    ltrgroup.Text = ledgername["groupname"].ToString();
                    ltrOpening.Text = String.Format("{0:0.00}", ledgername["opening"]).ToString();
                    if (ledgername["isSystem"].ToString() == "1")
                    {
                        btnedit.Visible = false;
                    }
                    else
                    {
                        btnedit.Visible = true;
                    }
                    if (ledgername["hasCostCentre"].ToString() == "True")
                    {
                        ltrcostcentre.Text = "Yes";
                    }
                    else
                    {
                        ltrcostcentre.Text = "No";
                    }

                    if (new string[] { "56", "167" }.Contains(ledgername["groups_id"].ToString()))
                    {

                        pnlcredit_update.Visible = true;
                        pnlde_update.Visible = true;
                        ltrname.Text = ledgername["legalname"].ToString();
                        ltradress.Text = ledgername["Address"].ToString();
                        ltrState.Text = ledgername["stateName"].ToString();
                        ltrPincode.Text = ledgername["Pincode"].ToString();
                        ltrCountry.Text = ledgername["place"].ToString();
                        ltrphoneno.Text = ledgername["Phone _No"].ToString();
                        ltrwebsite.Text = ledgername["Website"].ToString();
                        ltrcontactdetails.Text = ledgername["Contact_ Person"].ToString();
                        ltrmobileno.Text = ledgername["Contact_mobile_no"].ToString();
                        ltremail.Text = ledgername["Contact_email_1"].ToString();
                        ltrtransactiotype.Text = ledgername["TransactionType"].ToString();
                        ltraccountholder.Text = ledgername["Account _Holder"].ToString();
                        ltraccountnumber.Text = ledgername["Account _Number"].ToString();
                        ltrifsccode_update.Text = ledgername["IFSC _Code"].ToString();
                        ltrbankname.Text = ledgername["Bank _Name"].ToString();
                        ltrbranch.Text = ledgername["Branch"].ToString();
                        ltrpan.Text = ledgername["PAN_IT No"].ToString();
                        ltrgsttype.Text = ledgername["GSTRegistrationType"].ToString();
                        ltrgstin.Text = ledgername["GSTIN_UIN"].ToString();
                    }
                    else
                    {
                        pnlcredit_update.Visible = false;
                        pnlde_update.Visible = false;
                    }
                    if (ledgername["GST_applicable"].ToString() == "True")
                    {
                        ltrapplicable.Text = "yes";
                    }
                    else
                    {
                        ltrapplicable.Text = "no";
                    }

                    ltrtypeofsupply.Text = ledgername["supplytype"].ToString();

                    if (ledgername["TDS_applicable"].ToString() == "True")
                    {
                        ltrlapplicable.Text = "Yes";
                    }
                    else
                    {
                        ltrlapplicable.Text = "No";
                    }
                    ltrnatureofpayment.Text = "";
                    ltrdeducteetypr.Text = ledgername["Deducteetype"].ToString();
                    ltrTDSdeductible.Text = ledgername["TDS_applicable"].ToString();
                    if (ledgername["TDS_applicable"].ToString() == "True")
                    {
                        ltrTDSdeductible.Text = "Yes";
                    }
                    else
                    {
                        ltrTDSdeductible.Text = "No";
                    }
                    ltrdeducteetypr.Text = ledgername["Deducteetype"].ToString();

                    if (ledgername["Assessee _ Other _Territory"].ToString() == "True")
                    {
                        ltrassessofother.Text = "yes";
                    }
                    else
                    {
                        ltrassessofother.Text = "no";
                    }
                    if (ledgername["e_commerce_operator"].ToString() == "True")
                    {
                        ltrcommerce.Text = "yes";
                    }
                    else
                    {
                        ltrcommerce.Text = "no";
                    }
                    if (ledgername["deemed_exporter_purchases"].ToString() == "True")
                    {
                        ltrdeemed.Text = "yes";
                    }
                    else
                    {
                        ltrdeemed.Text = "No";
                    }
                    ltrpartype.Text = ledgername["partytype"].ToString();
                    if (ledgername["transporter"].ToString() == "True")
                    {
                        ltrwhethertranspoter.Text = "yes";
                    }
                    else
                    {
                        ltrwhethertranspoter.Text = "No";
                    }
                    ltrtranspoterid.Text = ledgername["Transporter _ID"].ToString();
                }
            }
        }
    }
}
