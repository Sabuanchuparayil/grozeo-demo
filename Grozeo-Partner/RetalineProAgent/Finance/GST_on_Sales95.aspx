<%@ Page Language="C#" MaintainScrollPositionOnPostback="true" AutoEventWireup="true" Title="GST Report" CodeBehind="GST_on_Sales95.aspx.cs"
    MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.GST_on_Sales95" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Finance/Navigations/GSTReports"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>

 <asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Daily Report on GST on Sales u/s 9(5) by ECO"></asp:Literal>
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Financial Transparency</p>
    </div>

    
<style>
    table.table, table.table td {
 /*       border: 0px !important;
        padding: 5px;*/
         border: 1px solid #ddd; /* Adjust color as needed */
         border-collapse: collapse;
    }
</style>

<script>
    function isTxtDateFromEnabled() {
        var txtDateFrom = document.getElementById('<%= txtDateFrom.ClientID %>');
            if (txtDateFrom !== null) {
                return !txtDateFrom.disabled; // Returns true if enabled, false if disabled
            }
            return false; // Control not found
    }

    function validateDateRange() {
        if (!isTxtDateFromEnabled()) {
            return true;
        }
        var startDate = document.getElementById('<%= txtDateFrom.ClientID %>').value;
        var endDate = document.getElementById('<%= txtDateTo.ClientID %>').value;

        if (!startDate || !endDate) {
            alert("Please provide both start and end dates.");
        return false; // Stop execution
        }

        var start = new Date(startDate);
        var end = new Date(endDate);

        var differenceInDays = (end - start) / (1000 * 60 * 60 * 24);

        if (differenceInDays > 31) {
            alert("The date range must be less than or equal to 31 days.");
        return false; // Prevent the postback
        }

        return true; // Allow the postback
    }

    $(document).ready(function() {
        $('#<%= ddlPeriods.ClientID %>').change(function() {
            var selectedValue = $(this).val();
            // Perform your desired actions here
            //alert('Selected value: ' + selectedValue);
            if(selectedValue == 'DateRange'){
                enableTextBoxes();
            }else{
                
            var txtDateFrom = document.getElementById('<%= txtDateFrom.ClientID %>');
            var txtDateTo = document.getElementById('<%= txtDateTo.ClientID %>');

            txtDateFrom.disabled = false;
            txtDateTo.disabled = false;

            var fromDate, toDate;
            var today = new Date();

                switch (selectedValue) {
                    case "MonthTillDate":
                        fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        fromDate.setDate(fromDate.getDate() + 1);
                        toDate = today;
                        break;
                    case "Last Month":
                        fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        fromDate.setDate(fromDate.getDate() + 1);
                        toDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        //toDate.setDate(toDate.getDate() - 1);
                        break;
                    case "YearTillDate":
                        fromDate = new Date(today.getFullYear(), 0, 1);
                        fromDate.setDate(fromDate.getDate() + 1);
                        toDate = today;
                        break;
                    default:
                        fromDate = "";
                        toDate = "";
                        break;
                }

                txtDateFrom.value = fromDate ? fromDate.toISOString().split('T')[0] : "";
                txtDateTo.value = toDate ? toDate.toISOString().split('T')[0] : "";

            txtDateFrom.disabled = true;
            txtDateTo.disabled = true;
            }
            
        });
    });
	
	function enableTextBoxes() {
		document.getElementById('<%= txtDateFrom.ClientID %>').disabled = false;
		document.getElementById('<%= txtDateTo.ClientID %>').disabled = false;
	}

</script>
</asp:Content>
<asp:Content ContentPlaceHolderID = "cpNMainContent" runat="server">
     <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex align-items-center row row-sm">
                <div class="col-lg-10 p-0 mb-2 mb-lg-0 flex-wrap flex-md-nowrap d-flex">
                    <div class="input-group mb-2 mb-lg-0 col-lg-3 p-0 px-2">
                        <label for="ddlPeriods" runat="server" class="form-control-label mb-1 w-100 tx-dark">Period:</label>
                        <asp:DropDownList ID="ddlPeriods"  CssClass="form-control select2" runat="server" AutoPostBack="false" OnSelectedIndexChanged="ddlPeriods_SelectedIndexChanged">
                            <asp:ListItem Text="Date Range (Max 31 Days)" Value="DateRange"></asp:ListItem>
                            <asp:ListItem Text="Month Till Date" Value="MonthTillDate"></asp:ListItem>
                            <asp:ListItem Text="Last Month" Value="Last Month"></asp:ListItem>
                            <asp:ListItem Text="Year Till Date" Value="YearTillDate"></asp:ListItem>
                        </asp:DropDownList>
                    </div>
                    <div id="datesDiv" class="input-group mb-3 mb-lg-0 col-lg-3 p-0 px-2" >
                        <div ID="divDateFrom" class="input-group mb-2 mb-lg-0 col-lg-6 p-0 px-2" runat="server">
                            <label for="txtDateFrom" runat="server" class="form-control-label mb-1 w-100 tx-dark">From:</label>
                            <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-inputformat="dd/mm/yyyy" ></asp:TextBox>
                        </div>
                        <div id="divDateTo"  class="input-group mb-2 mb-lg-0 col-lg-6 p-0 px-2" runat="server">
                            <label for="txtDateTo" runat="server" class="form-control-label mb-1 w-100 tx-dark">To:</label>
                            <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-inputformat="dd/mm/yyyy"></asp:TextBox>
                        </div>
                    </div>

                    <div class="input-group mb-2 mb-lg-0 col-lg-2 p-0 px-2">
                        <label for="ddlInvoiceGroup" runat="server" class="form-control-label mb-1 w-100 tx-dark">Invoice Group:</label>
                        <asp:DropDownList ID="ddlInvoiceGroup"  CssClass="form-control select2" runat="server" OnSelectedIndexChanged ="ddlInvoiceGroup_SelectedIndexChanged" AutoPostBack="true">
                            <asp:ListItem Text="All Invoices" Value="AllInvoices"></asp:ListItem>
                            <asp:ListItem Text="State Wise" Value="StateWiseInvoices"></asp:ListItem>
                        </asp:DropDownList>
                    </div>
                    <div ID="states_div" class="col-lg-2 input-group mb-2 mb-lg-0 p-0 px-2" runat="server">
                        <label for="ddlStates" runat="server" class="form-control-label mb-1 w-100 tx-dark">States:</label>
                        <asp:DropDownList ID="ddlStates" CssClass="form-control select2" AutoPostBack="true"  runat="server" DataValueField="state_code" OnSelectedIndexChanged="ddlStates_SelectedIndexChanged" DataTextField="st_name"></asp:DropDownList>
                    </div>
                    <div ID="divsearchbtn" class="col-lg-1 input-group mb-2 mb-lg-0 p-0 px-2" runat="server">
                        <label for="btnSearch" runat="server"  class="form-control-label mb-1 w-100 tx-dark">&nbsp;</label>
                        <asp:Button ID="btnSearch" CssClass="btn btn-primary w-100 mt-2 mt-lg-0" OnClick="btnSearch_Click"  OnClientClick="return validateDateRange();" runat="server" Text="Search" />
                    </div>
                </div>

            </div>
        </div>

                       <div class="card-body">
                 <div class="table-responsive">

                   <asp:GridView AutoGenerateColumns="false" ID="gvTaxReport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                      AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" DataSourceID="sdsGSTSalesRestaurant" > 
                       <HeaderStyle HorizontalAlign="Center" />
                      <Columns>
                          <asp:BoundField HeaderText="Date" DataField="order_date" DataFormatString="{0:dd-MM-yyyy}">
					         <ItemStyle HorizontalAlign="Center" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="No. of Invoices" DataField="no_of_invoices">
					         <ItemStyle HorizontalAlign="Center" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="Gross Sales" DataField="gross_sales" DataFormatString="{0:#,##0.00}">
					         <ItemStyle HorizontalAlign="Right" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="IGST" DataField="igst" DataFormatString="{0:#,##0.00}">
					         <ItemStyle HorizontalAlign="Right" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="CGST" DataField="cgst" DataFormatString="{0:#,##0.00}">
					         <ItemStyle HorizontalAlign="Right" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="SGST/UTGST" DataField="sgst_or_utgst" DataFormatString="{0:#,##0.00}">
					         <ItemStyle HorizontalAlign="Right" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="Compensation Cess" DataField="compensation_cess" DataFormatString="{0:#,##0.00}">
					         <ItemStyle HorizontalAlign="Right" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="Total GST and Cess" DataField="total_gst_cess" DataFormatString="{0:#,##0.00}">
					         <ItemStyle HorizontalAlign="Right" />
                          </asp:BoundField>
                          <asp:BoundField HeaderText="Invoice Total" DataField="invoice_total" DataFormatString="{0:#,##,##0.00}">
					         <ItemStyle HorizontalAlign="Right" />
                          </asp:BoundField>
                      </Columns>
                        <PagerStyle CssClass="cssPager" />
                        <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                  </asp:GridView>

                   <asp:SqlDataSource runat="server" ID="sdsGSTSalesRestaurant" ConnectionString="<%$connectionStrings:mySqlConnection%>"  ProviderName="MySql.Data.MySqlClient"
                        SelectCommand = "WITH InvoiceCalculations AS 
                            (
                                SELECT 
                                    DATE(inv.created_at) AS order_date, 
                                    COUNT(DISTINCT customer_order_id) AS no_of_invoices, 
                                    ROUND(SUM(item_price), 2) AS invoice_total, 
                                    ROUND(SUM(order_item_basket_price_et), 2) AS order_item_basket_price_et, 
                                    ROUND(SUM(order_item_igst), 2) AS igst,
                                    ROUND(SUM(order_item_cgst), 2) AS cgst,
                                    ROUND(SUM(order_item_ugst + order_item_sgst), 2) AS sgst_or_utgst, 
                                    ROUND(SUM(order_item_cess), 2) AS compensation_cess,    
                                    ROUND(SUM(order_item_igst + order_item_cgst + order_item_ugst + order_item_sgst + order_item_cess), 2) AS total_gst_cess 
                                FROM 
                                    retaline_customer_order re 
                                INNER JOIN 
                                    retaline_customer_order_items ro ON re.order_id = ro.customer_order_id 
                                INNER JOIN 
                                    finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID 
                                LEFT JOIN 
                                    hsn_value hs ON hs.id = fs.stit_hsnId 
                                INNER JOIN 
                                    mypha_productsubcategory mp ON fs.product_category = mp.sub_category_id
                                INNER JOIN 
                                    finascop_branch fb ON ro.order_branch_id = fb.br_ID 
                                INNER JOIN 
                                    invoice_number inv ON re.order_id = inv.order_id
                                WHERE 
                                    hasRestaurantService = 1 
                                    AND item_order_qty >= 1 
                                    AND invoice_type = 1  
                                    AND DATE(inv.created_at) >= @fromDate 
                                    AND DATE(inv.created_at) <= @toDate 
                                GROUP BY 
                                    DATE(inv.created_at) ORDER BY inv.created_at
                            )
                            SELECT 
                                order_date, 
                                no_of_invoices, 
                                order_item_basket_price_et AS gross_sales, 
                                igst, 
                                cgst, 
                                sgst_or_utgst, 
                                compensation_cess, 
                                total_gst_cess, 
                                ROUND(invoice_total, 2) AS invoice_total 
                            FROM 
                                InvoiceCalculations;" >
                        <SelectParameters>
                            <asp:controlparameter controlid="txtdatefrom" name="fromDate" convertemptystringtonull="false" />
                            <asp:controlparameter controlid="txtdateto" name="toDate" convertemptystringtonull="false" />
<%--                            <asp:ControlParameter ControlID="ddlStoreGroupBranch" Name="storeRefId" ConvertEmptyStringToNull="false" />--%>
                        </SelectParameters>
                    </asp:SqlDataSource>

             </div>
          </div><!-- card-body -->
    </div>
</asp:Content>