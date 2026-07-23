<%@ Page Language="C#" AutoEventWireup="true"  Title="Passbook"  MasterPageFile="~/Tenant/TenantMaster.master" Async="true" CodeBehind="MerchantPassbook.aspx.cs" Inherits="RetalineProAgent.Passbook" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Passbook</li>--%>
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Passbook"></asp:Literal>
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Financial Transparency</p>
    </div>

     <div class="modal fade" id="personalModal" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>


                    <div id="dvpopupvoucherdetails">
                    </div>

                </div>

            </div>
        </div>
    </div>
    
<style>
    table.table table, table.table table td {
        border: 0px !important;
        padding: 5px;
    }
</style>

<script type="text/javascript">
    function loadVoucherDetails(id) {
        $('#dvpopupvoucherdetails').html('<div>Loading .. </div>');
        console.log("ID :" + id);
        $('#dvpopupvoucherdetails').load('./VoucherDetails?id=' + id);
        console.log("Page Loaded..");
    }
</script>

</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="d-flex align-items-center row row-sm">

                <div class="col-sm-10 p-0 mb-2 mb-sm-0 flex-wrap flex-md-nowrap d-flex">
                    <div class="input-group mb-2 mb-sm-0 col-sm-4 p-0 px-2" style="display: none;">
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
                    <div class="input-group mb-2 mb-sm-0 col-sm-4 p-0 px-2">
                        <label for="txtDateFrom" runat="server" class="form-control-label mb-1 w-100 tx-dark">From:</label>
                    <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-inputformat="dd/mm/yyyy"></asp:TextBox>
                    </div>
                    <div class="input-group mb-2 mb-sm-0 col-sm-4 p-0 px-2">
                        <label for="txtDateTo" runat="server" class="form-control-label mb-1 w-100 tx-dark">To:</label>
                    <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-inputformat="dd/mm/yyyy"></asp:TextBox>
                    </div>
                     <div class="input-group input-group col-12 col-md-1 align-items-end pl-md-1 mb-2 mb-md-0">
                        <input type="submit" name="" value="GO" id="" class="btn btn-primary">
                      </div>
                    <div class="form-group col-12 col-md-5 align-items-end mb-0 d-flex">
                          <div class="input_search_box">
                            <input type="text" style="display:none" />
                            <input type="password" style="display:none" />

                            <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                            <asp:LinkButton runat="server" CssClass="input-group-append">                        
                            </asp:LinkButton>

                            <input type="text" style="display:none" />
                                <input type="password" style="display:none" />
                            <asp:LinkButton ID="lbtnSearch"  CssClass="btn bd bd-l-0 tx-gray-600 "  runat="server" autocomplete="off"><i class="fa fa-search mt-1"></i></asp:LinkButton>
                        </div>
                    </div>

                    <div class="form-group align-items-end mb-0 d-flex">
                    <asp:LinkButton runat="server" ID="btndownload" CssClass="btn p-0 mr-2" OnClick="btndownload_Click" ToolTip="Dowload File">
                       <i class="fa-light fa-arrow-down-to-bracket tx-20"></i>
                    </asp:LinkButton>
                </div>
                </div>
                <div class="float-left w-100 mt-2">
                    <span class="mr-2">
                        <b>Opening Balance:</b> <asp:Literal runat="server" ID="ltrPageCurStart" Text=""></asp:Literal>
                    </span>
                    <span class="mr-2">
                        <b> Closing Balance:</b> <asp:Literal runat="server" ID="ltrPageCurTotal" Text=""></asp:Literal>
                    </span>
                    <span class="mr-2">
                        <b>Total Debit: </b> <asp:Literal runat="server" ID="ltrtotaldebit" Text=""></asp:Literal>
                    </span>
                    <span class="mr-2">
                        <b> Total Credit: </b> <asp:Literal runat="server" ID="ltrtotalcredit" Text=""></asp:Literal>
                    </span>
                </div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">

                     <asp:GridView AutoGenerateColumns="false" ID="gvPassbook" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                        AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10"  DataSourceID="SDSPassbookEntries"
                          OnDataBound ="gvPassbook_DataBound" >  
                        <Columns>
                            <asp:BoundField HeaderText="Date"  DataField="createdOn" SortExpression="createdOn" DataFormatString="{0:dd/MMM/yyyy}" ><HeaderStyle HorizontalAlign="Center" />
                           </asp:BoundField>
                            <asp:BoundField HeaderText="Particulars" DataField="particulars">
                               <HeaderStyle HorizontalAlign="Center" />
                            </asp:BoundField>                            
                            <asp:BoundField HeaderText="Reference" DataField="refernce">
                               <HeaderStyle HorizontalAlign="Center" />
                            </asp:BoundField>  
                            <asp:BoundField HeaderText="Debit" DataField="dr_amount"  DataFormatString="{0:F2}">
                               <HeaderStyle HorizontalAlign="Center" />
                                <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>
                            <asp:BoundField HeaderText="Credit" DataField="cr_amount"  DataFormatString="{0:F2}">
                               <HeaderStyle HorizontalAlign="Center" />
                                <ItemStyle HorizontalAlign="Right" />
                            </asp:BoundField>                           
                            <asp:TemplateField HeaderText="Action">
                                <ItemTemplate>
                                    <%# 
            Eval("voucher_id") != DBNull.Value && Eval("voucher_id") != null 
            ? $"<button type='button' id='btnDetails' class='btn btn-outline-primary btn-sm' data-toggle='modal' data-target='#personalModal' data-id='{Eval("voucher_id")}' onclick='loadVoucherDetails({Eval("voucher_id")})'>Voucher</button>"
            : "" 
                                    %>
                                </ItemTemplate>
                            </asp:TemplateField>
                        </Columns>
                         <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No record available</h6>
                                        </div>
                         </EmptyDataTemplate>
                          <PagerStyle CssClass="cssPager" />
                          <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                    </asp:GridView>

                    <asp:SqlDataSource runat="server" ID="SDSPassbookEntries" OnSelected = "SDSPassbookEntries_Selected" ConnectionString="<%$ ConnectionStrings:FinascopConnection%>" 
                                        SelectCommand = "DECLARE @ledgerId INT = (
                    SELECT TOP 1 [id] 
                    FROM [ledger] 
                    WHERE refId = @storeBrID
                );

                WITH x AS (
                    SELECT de.entity_id, tr.data_entry_id, refId, de.event, de.id 
                    FROM transactions tr 
                    INNER JOIN data_entry de ON tr.data_entry_id = de.id 
                    INNER JOIN [ledger] l ON l.id = tr.ledger_id 
                    WHERE refId = @storeBrID
                ),
                max_id_cte AS (
                    SELECT ISNULL(MAX(id), 0) AS max_id FROM transactions
                ),
               opening_balance AS (
    SELECT 
        0 AS id, 
        @storeBrID AS storeRefId, 
        @datefrom AS createdOn, 
        'Opening Balance' AS particulars,
        NULL AS docNumber, 
        NULL AS event,
        NULL AS voucher_id,
        NULL AS refernce,
        CASE 
         WHEN ob.balance < 0 THEN FORMAT(ABS(ob.balance), '0.00')  
                            ELSE NULL 
                        END AS dr_amount,
                        CASE 
                            WHEN ob.balance >= 0 THEN FORMAT(ob.balance, '0.00')  -- Credit
                            ELSE NULL 
                        END AS cr_amount,
                        NULL AS closingBalance,
                        '' AS href
                    FROM (
                        SELECT CAST(ISNULL((
                            SELECT TOP 1 closingBalance 
                            FROM transactions 
                            WHERE ledger_id = @ledgerId 
                              AND CONVERT(DATE, createdOn) < @datefrom 
                            ORDER BY id DESC
                        ), 0) AS DECIMAL(18, 2)) AS balance
                    ) ob
                ),
                closing_balance AS (
                SELECT 
                    (SELECT max_id + 2 FROM max_id_cte) AS id, 
                    @storeBrID AS storeRefId, 
                    @dateto AS createdOn, 
                    'Closing Balance' AS particulars,
                    NULL AS docNumber, 
                    NULL AS event,
                    NULL AS voucher_id,
                    NULL AS refernce,
                    CASE 
                        WHEN cb.balance < 0 THEN FORMAT(ABS(cb.balance), '0.00') 
                        ELSE NULL 
                    END AS dr_amount,
                    CASE 
                        WHEN cb.balance >= 0 THEN FORMAT(cb.balance, '0.00')  -- Credit
                        ELSE NULL 
                    END AS cr_amount,
                    NULL AS closingBalance,
                    '' AS href
                FROM (
                    SELECT CAST(ISNULL((
                        SELECT TOP 1 closingBalance 
                        FROM transactions 
                        WHERE ledger_id = @ledgerId 
                          AND CONVERT(DATE, createdOn) <= @dateto 
                        ORDER BY id DESC
                    ), 0) AS DECIMAL(18, 2)) AS balance
                ) cb
            )
                SELECT * FROM opening_balance
                UNION ALL
                SELECT 
                    ROW_NUMBER() OVER (ORDER BY tr.id) + 1 AS id,
                    @storeBrID AS storeRefId,
                    de.createdOn,
                    tr.particulars AS particulars,
                    de.docSerialNo AS docNumber, 
                    x.event AS event,
                    x.id AS voucher_id,
                    x.entity_id AS refernce,    
                    CASE WHEN tr.isDebtor = 1 THEN tr.amount END AS dr_amount,
                    CASE WHEN tr.isDebtor = 0 THEN tr.amount END AS cr_amount,
                    NULL AS closingBalance,
                    '' AS href
                FROM transactions tr
                INNER JOIN data_entry de ON tr.data_entry_id = de.id 
                INNER JOIN x ON x.data_entry_id = tr.data_entry_id 
                INNER JOIN ledger l ON l.id = tr.ledger_id 
                WHERE l.refId <> @storeBrID 
                  AND x.refId = @storeBrID 
                  AND CONVERT(DATE, tr.createdOn) BETWEEN @datefrom AND @dateto
                  AND (TRIM(@search) LIKE '' OR tr.particulars LIKE CONCAT('%', @search, '%') OR x.entity_id LIKE CONCAT('%', @search, '%') OR x.id LIKE CONCAT('%', @search, '%') or tr.amount LIKE CONCAT('%', @search, '%')) 
                   GROUP BY x.id,tr.id, de.createdOn, tr.particulars,de.docSerialNo, x.event,x.entity_id,tr.isDebtor,tr.amount
                UNION ALL
                SELECT * FROM closing_balance
                ORDER BY id;">
                        <SelectParameters>
                             <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                            <asp:controlparameter controlid="txtdatefrom" name="datefrom" convertemptystringtonull="false" />
                            <asp:controlparameter controlid="txtdateto" name="dateto" convertemptystringtonull="false" />
                            <asp:ControlParameter ControlID="ddlStoreGroupBranch" Name="storeBrID" ConvertEmptyStringToNull="false" />
                        </SelectParameters>

                    </asp:SqlDataSource>
                   </div>
        </div><!-- card-body -->
       <PagerStyle CssClass="cssPager" />
        <!-- card-footer -->
    </div><!-- card -->

        <div class="modal" id="Pupaction" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">               
                 <div class="modal-content">
                <div class="modal-body">
                    <div class="modaltitle">
                        <button type="button" class="close position-absolute mt-2 mr-1" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>                  
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-2" style="box-shadow: none;">                                
                                <div class="card-body rounded-0 p-0">
                                    <div class="table-responsive p-0" style="max-height: 300px;">
                                        <asp:ListView ID="lvsettlement" DataSourceID="SDSsettlement" OnDataBound="lvsettlement_DataBound"  runat="server" >
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table gridview_table table-bordered table-head-fixed m-0">
                                                    <tr id="Tr1" runat="server" class="TableHeader">
                                                        <th id="Td1" runat="server">Order No</th>
                                                        <th style="width:90px" id="Td2" runat="server">Order Date</th>
                                                        <th id="Td3" runat="server">Particulars</th>
                                                        <th id="Th1" runat="server">Earnings</th>
                                                        <th id="Th3" runat="server">Deductions</th>                                                        
                                                    </tr>
                                                    <tr id="ItemPlaceholder" runat="server">
                                                    </tr>
                                                    <tfoot>
                                                        <tr>
                                                            <td id="Td4" runat="server"><b>Total</b></td>
                                                            <td align="right" style="text-align: right;">
                                                                <asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></td>                                                                                                                      
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltttotalamount" runat="server"></asp:Literal></strong></td>
                                                             <td align="right" style="text-align: right;">
                                                              <strong><asp:Literal ID="ltrdeduction" runat="server"></asp:Literal></strong></td>
                                                            <td align="right" style="text-align: right;">
                                                               <strong><asp:Literal ID="ltrsettleamount" runat="server"></asp:Literal></strong></td>                                                             
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <tr class="TableData">
                                                    <td>
                                                        <asp:Label ID="lbOrderNo" runat="server" Text='<%# Eval("entity_id")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbOrderDate" runat="server" Text='<%# Eval("createdOn","{0:dd/MMM}")%>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbConfoirmedDate" runat="server" Text='<%# Eval("DisplayLabel")%>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lbdelivery" runat="server" Text='<%# Eval("dr_amount","{0:n}")%>'></asp:Label>
                                                    </td>                                                   
                                                     <td align="left">
                                                        <asp:Label ID="lbSettlementDate" runat="server" Text='<%# Eval("cr_amount","{0:n}")%>'></asp:Label>
                                                    </td>                                                   
                                                </tr>
                                            </ItemTemplate>                                          
                                            <EmptyDataTemplate>
                                                <div class="text-center">
                                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                                    <h6 class="mb-3">No record available</h6>
                                                </div>     
                                            </EmptyDataTemplate>
                                        </asp:ListView>
                                    </div>
                                     <asp:HiddenField ID="hidValueHeadOrderId" runat="server" />
                                    <asp:HiddenField ID="hidValueHeadStorRef" runat="server" />
                                    <asp:SqlDataSource ID="SDSsettlement" runat="server" ConnectionString="<%$ connectionStrings:FinascopConnection %>" 
                                        SelectCommand="with x as(
                                            SELECT de.entity_id,data_entry_id,l.refId,l.name,l.id,l.DisplayLabel FROM transactions tr 
                                            INNER JOIN  data_entry de ON tr.data_entry_id =de.id 
                                            inner join [ledger] l on l.id=tr.ledger_id where de.[entity_id] =@Id and refId =@ref
                                            )select de.entity_id, tr.data_entry_id,de.createdOn,de.docSerialNo,(select vt.name from voucher_type vt where de.voucher_type_id=vt.id) as Voucher,de.event, tr.particulars, tr.ledger_id as ledgerId,l.DisplayLabel,CASE WHEN [isDebtor] = 1 THEN  tr.amount  END AS dr_amount,CASE WHEN [isDebtor] =0 THEN  tr.amount 
                                            END AS cr_amount from transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id inner join x on x.data_entry_id = tr.data_entry_id inner join [ledger] l on l.id=tr.ledger_id  where x.id!=tr.ledger_id">
                                            <SelectParameters>                                            
                                            <asp:ControlParameter ControlID="hidValueHeadOrderId" PropertyName="Value" Name="Id" DefaultValue="0" />
                                            <asp:ControlParameter ControlID="hidValueHeadStorRef" PropertyName="Value" Name="ref" DefaultValue="0" />
                                        </SelectParameters>
                                    </asp:SqlDataSource>
                                </div>
                            </div>                            
                        </div>
                    </div>
                </div>

            </div>
            </div>
        </div>
    </div>
                                    
</asp:Content>