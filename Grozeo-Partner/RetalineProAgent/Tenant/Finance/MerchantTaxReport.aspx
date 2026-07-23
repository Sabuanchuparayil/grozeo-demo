<%@ Page Language="C#" AutoEventWireup="true"  Title="Merchant Tax Report"  MasterPageFile="~/Tenant/TenantMaster.master"
 Async="true" CodeBehind="MerchantTaxReport.aspx.cs" Inherits="RetalineProAgent.Tenant.Finance.MerchantTaxReport" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

 <asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Seller Tax Report"></asp:Literal>
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

        function validateDateRange() {
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
</script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex align-items-center row row-sm">

                <div class="col-sm-10 p-0 mb-2 mb-sm-0 flex-wrap flex-md-nowrap d-flex">
                    <div class="input-group mb-2 mb-lg-0 col-sm-4 p-0 px-2" style="display: none;>
                        <label for="ddlStoreGroupBranch" runat="server" class="tx-dark mb-1 w-100">Branch:</label>
                        <asp:DropDownList ID="ddlStoreGroupBranch" CssClass="form-control select2" runat="server" DataSourceID="SDSGroupBranches" DataTextField="store_group_name" DataValueField="storeRefId"></asp:DropDownList>
                        <asp:SqlDataSource runat="server" ID="SDSGroupBranches" ConnectionString="<%$ ConnectionStrings:mySqlConnection%>" ProviderName="MySql.Data.MySqlClient"
                            SelectCommand="SELECT `store_group_name`, `storeRefId` FROM `finascop_branch_group` WHERE store_group_id= @storegroup"
                            OnSelecting="SDSGroupBranches_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="storegroup" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                    <div class="input-group mb-2 mb-lg-0 col-sm-4 p-0 px-2">
                        <label for="ddlPeriods" runat="server" class="form-control-label mb-1 w-100 tx-dark">Period:</label>
                        <asp:DropDownList ID="ddlPeriods"  CssClass="form-control select2" runat="server"  OnSelectedIndexChanged = "ddlPeriods_SelectedIndexChanged" AutoPostBack="true">
                            <asp:ListItem Text="Financial Year" Value="FinancialYear"></asp:ListItem>
                            <asp:ListItem Text="Month" Value="Month"></asp:ListItem>
                            <asp:ListItem Text="Date Range (Max 31 Days)" Value="DateRange"></asp:ListItem>
                        </asp:DropDownList>
                    </div>
                    <div ID="divFinancialYear" class="input-group mb-2 mb-lg-0 col-sm-4 p-0 px-2" runat="server" >
                        <label for="ddlFinancialYears" runat="server" class="form-control-label mb-1 w-100 tx-dark">Financial Year:</label>
                            <asp:DropDownList ID="ddlFinancialYears" runat="server" AutoPostBack="true" CssClass="form-control select2" OnSelectedIndexChanged="ddlFinancialYears_SelectedIndexChanged">
                            </asp:DropDownList>
                    </div>
                    <div ID="divMonths" runat="server" class="input-group mb-2 mb-lg-0 col-sm-4 p-0 px-2">
                        <label for="ddlMonths" runat="server" class="form-control-label mb-1 w-100 tx-dark">Month:</label>
                            <asp:DropDownList ID="ddlMonths" CssClass="form-control select2" runat="server" OnSelectedIndexChanged="ddlMonths_SelectedIndexChanged" AutoPostBack="true" >
                                <asp:ListItem Text="April" Value="4"></asp:ListItem>
                                <asp:ListItem Text="May" Value="5"></asp:ListItem>
                                <asp:ListItem Text="June" Value="6"></asp:ListItem>
                                <asp:ListItem Text="July" Value="7"></asp:ListItem>
                                <asp:ListItem Text="August" Value="8"></asp:ListItem>
                                <asp:ListItem Text="September" Value="9"></asp:ListItem>
                                <asp:ListItem Text="October" Value="10"></asp:ListItem>
                                <asp:ListItem Text="November" Value="11"></asp:ListItem>
                                <asp:ListItem Text="December" Value="12"></asp:ListItem>
                                <asp:ListItem Text="January" Value="1"></asp:ListItem>
                                <asp:ListItem Text="February" Value="2"></asp:ListItem>
                                <asp:ListItem Text="March" Value="3"></asp:ListItem>
                            </asp:DropDownList>
                    </div>

                    <div ID="divDateFrom" class="input-group mb-2 mb-lg-0 col-sm-4 p-0 px-2" runat="server" >
                        <label for="txtDateFrom" runat="server" class="form-control-label mb-1 w-100 tx-dark">From:</label>
                        <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-inputformat="dd/mm/yyyy" ></asp:TextBox>
                    </div>
                    <div id="divDateTo"  class="input-group mb-2 mb-lg-0 col-sm-4 p-0 px-2" runat="server">
                        <label for="txtDateTo" runat="server" class="form-control-label mb-1 w-100 tx-dark">To:</label>
                        <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-inputformat="dd/mm/yyyy"></asp:TextBox>
                    </div>
                </div>

                <div class="col-sm-2">
                    <label class="d-none d-sm-block mb-0">&nbsp;</label>
                    <!-- Change LinkButton to Button -->
                    <asp:Button ID="btnSearch" CssClass="btn btn-primary w-lg-100 mt-0" runat="server" OnClientClick="return validateDateRange();" OnClick="lbtnSearch_Click" Text="Search" />
                </div>
            </div>
        </div><!-- card-header -->


                <div class="card-body">
                   <div class="table-responsive">

                     <asp:GridView AutoGenerateColumns="false" ID="gvTaxReport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                        AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10"  DataSourceID="SDSPassbookEntries" > 
                         <HeaderStyle HorizontalAlign="Center" />
                        <Columns>
                            <asp:BoundField HeaderText="Month" DataField="month">
                               <HeaderStyle HorizontalAlign="Left" />
							   <ItemStyle HorizontalAlign="Left" />
                            </asp:BoundField>
                            <asp:BoundField HeaderText="Sales" DataField="Sales">
							   <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField> 
                            <asp:BoundField HeaderText="CGST" DataField="CGST">
							   <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>                            
                            <asp:BoundField HeaderText="SGST/UTGST" DataField="SGST/UTGST">
							   <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>  
                            <asp:BoundField HeaderText="IGST" DataField="IGST">
                                <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>
							<asp:BoundField HeaderText="Cess" DataField="Cess">
							   <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField> 
                            <asp:BoundField HeaderText="TCS CGST" DataField="TCS CGST">
							   <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>                            
                            <asp:BoundField HeaderText="TCS SGST/UTGST" DataField="TCS SGST/UTGST">
							   <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>  
                            <asp:BoundField HeaderText="TCS IGST" DataField="TCS IGST">
                                <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>
                        </Columns>
                         <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                         <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                    </asp:GridView>

                    <asp:SqlDataSource runat="server" ID="SDSPassbookEntries" OnSelecting="SDSPassbookEntries_Selecting" ConnectionString="<%$ ConnectionStrings:FinascopConnection%>" 
                        SelectCommand = "SELECT 
                            concat(cr_month,' ', curr_year) as month,[Tenant Sales] as Sales,[Tenant CGST] as CGST,[Tenant SGST] AS [SGST/UTGST],[Tenant IGST] AS IGST,
	                        [Tenant Comp. Cess] as [Cess],[TCS CGST],[TCS SGST] AS [TCS SGST/UTGST],[TCS IGST]
                        FROM
                            (SELECT CONCAT(YEAR(tr.createdOn), RIGHT('0' + CAST(MONTH(tr.createdOn) AS VARCHAR(2)), 2)) AS num_month,
	                        DATENAME(month, tr.createdOn) AS cr_month,
	                        year(tr.createdOn) as curr_year,CASE WHEN tr.isDebtor = 0 THEN -tr.amount ELSE tr.amount END AS amount, tr.particulars
                                FROM [dbo].[transactions] tr
		                            INNER JOIN [data_entry] de ON tr.data_entry_id = de.id
	                            WHERE tr.createdOn >= CONVERT(datetime, @fromDate, 120) AND tr.createdOn <= CONVERT(datetime,@toDate, 120) AND
	                            de.[store_group_refId] = @storeRefId
	 
                                ) AS inner_table
	                            PIVOT (
                            SUM(amount)
                            FOR [particulars] IN ([Tenant Sales],[Tenant CGST],[Tenant SGST],[Tenant IGST],[Tenant Comp. Cess],
	                        [TCS CGST],[TCS SGST],[TCS IGST])
                        ) AS PVT ORDER BY num_month" >

                        <SelectParameters>
                            <asp:controlparameter controlid="txtdatefrom" name="fromDate" convertemptystringtonull="false" />
                            <asp:controlparameter controlid="txtdateto" name="toDate" convertemptystringtonull="false" />
                            <asp:ControlParameter ControlID="ddlStoreGroupBranch" Name="storeRefId" ConvertEmptyStringToNull="false" />
                        </SelectParameters>

                    </asp:SqlDataSource>
               </div>
            </div><!-- card-body -->
      </div>
</asp:Content>

