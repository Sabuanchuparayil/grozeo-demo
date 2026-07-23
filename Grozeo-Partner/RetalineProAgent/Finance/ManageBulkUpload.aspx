<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ManageBulkUpload.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.MangeBulkUpload" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Navigations/AccountBooks"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Bulk Bank Transfer</h6>
    <p class="mb-0">Manage Bulk Transfer</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="row row-sm justify-content-between">
                        <div class="col-12 col-lg-4">
                            <div class="row row-sm">
                                <div class="form-group col-12 col-md-6 mb-2 mb-lg-0 pr-md-1">
                                    <label for="txtFromDate" class="tx-dark" runat="server">From</label>
                                    <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                                </div>
                                <div class="form-group col-12 col-md-6 mb-2 mb-lg-0 pl-md-1">
                                    <label for="txtToDate" class="tx-dark" runat="server">To</label>
                                    <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                                </div>
                            </div>
                        </div>
                        <div class="col-12  col-lg-8">
                            <div class=" w-100 d-flex flex-wrap flex-lg-nowrap align-items-end">
                                <div class="form-group w-100 mb-2 mb-lg-0 col-12 col-md-6 pl-0 pr-0 pr-md-2">
                                    <label for="seltype" class="tx-dark" runat="server">Type</label>
                                    <asp:DropDownList ID="dlentrytpeupdae" CssClass="form-control py-0" AutoPostBack="true" runat="server">
                                         <asp:ListItem Text="Select the File Type" Value="0"></asp:ListItem>
                                        <asp:ListItem Text="File Created" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="File Uploaded" Value="2"></asp:ListItem>  
                                        <asp:ListItem Text="File Closed" Value="3"></asp:ListItem>  
                                    </asp:DropDownList>
                                </div>
                                <div class="form-group d-flex mb-2 mb-lg-0 col-12 col-md-6 pr-0 pr-md-1 pl-0">
                                    <div class="input_search_box">
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton runat="server" CssClass="input-group-append">                        
                                        </asp:LinkButton>
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:LinkButton ID="lbtnSearch"  CssClass="btn bd bd-l-0 tx-gray-600 " runat="server" autocomplete="off"><i class="fa fa-search mt-1"></i></asp:LinkButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive mailbox-messages">
                <asp:GridView AutoGenerateColumns="false" ID="gvpending"  runat="server" CssClass="table table-bordered gridview_table" BorderStyle="Solid" 
                    DataSourceID="SDSpendingentries" AllowPaging="true" PagerStyle-CssClass="pg_table" PageSize="10" DataKeyNames="Id">
                    <Columns>                        
                        <asp:BoundField HeaderText="Sl.No" ItemStyle-Width="50px" HeaderStyle-HorizontalAlign="Center" ItemStyle-HorizontalAlign="Center" DataField="id" SortExpression="id" HeaderStyle-CssClass="text-center" />
                        <asp:BoundField HeaderText="Date" ItemStyle-Width="115px" ItemStyle-HorizontalAlign="left" DataField="CreatedOn" DataFormatString="{0:dd-MMM-yyyy}" SortExpression="CreatedOn" ItemStyle-VerticalAlign="Middle" />                        
                        <asp:BoundField HeaderText="Count" HeaderStyle-HorizontalAlign="Center"  ItemStyle-HorizontalAlign="Center" DataField="SettlementCount" SortExpression="SettlementCount" ItemStyle-VerticalAlign="Middle" /> 
                          <asp:BoundField HeaderText="Amount" ItemStyle-Width="" HeaderStyle-HorizontalAlign="Center" DataFormatString="{0:n}"  ItemStyle-HorizontalAlign="Right" DataField="Amount" SortExpression="Amount" ItemStyle-VerticalAlign="Middle" /> 
                        <asp:TemplateField HeaderText="File Name" HeaderStyle-HorizontalAlign="left" ItemStyle-Width="" ItemStyle-CssClass="logd_details text-break" ItemStyle-VerticalAlign="Middle" >
                             <ItemTemplate>
                                 <a href='<%# ConfigurationManager.AppSettings.Get("finance.url") + Eval("FileName").ToString() %>' target="_blank"><%# Eval("FileName") %></a>                                
                             </ItemTemplate>
                        </asp:TemplateField>
                        <%--<asp:BoundField HeaderText="Log Details" ItemStyle-Width="42%" HeaderStyle-HorizontalAlign="Center"  ItemStyle-HorizontalAlign="Left" DataField="comments" SortExpression="comments" ItemStyle-CssClass="logd_details text-break" />--%>
                         <asp:BoundField HeaderText="Bank" ItemStyle-Width="" ItemStyle-HorizontalAlign="left" DataField="Bank" SortExpression="Bank" />
                         <asp:BoundField HeaderText="Trans No" ItemStyle-Width="" ItemStyle-HorizontalAlign="left" DataField="TransactionNumbers" SortExpression="TransactionNumbers" />
                         <asp:BoundField HeaderText="Status" ItemStyle-Width="" ItemStyle-HorizontalAlign="left" DataField="status_update" SortExpression="status_update" />
                         <asp:TemplateField HeaderText="Action" HeaderStyle-HorizontalAlign="Center" ItemStyle-Width="100px" ItemStyle-CssClass="text-center" HeaderStyle-CssClass="text-center">
                            <ItemTemplate>
                                <asp:LinkButton ID="btnaction" runat="server" Visible='<%# Eval("status_id").Equals(1)%>' CssClass="btn btn-outline-primary btn-sm" Text="Trans Id" OnClick="btnaction_Click" recid='<%# Eval("id") %>' />
                                <asp:LinkButton ID="btnupdatepayment" Visible='<%# Eval("status_id").Equals(2)%>' OnClick="btnupdatepayment_Click" runat="server" CssClass="btn btn-outline-primary btn-sm" Text="UTR" getfile='<%# Eval("FileName") %>'></asp:LinkButton>
                                <asp:LinkButton ID="btnimgactio" runat="server" Visible='<%# Eval("status_id").Equals(3)%>' CssClass="btn btn-outline-primary btn-sm" OnClick="btnimgactio_Click"  getid='<%# Eval("TransactionRef_id") %>'><i class="fa-thin fa-eye"></i></asp:LinkButton>
                                <asp:LinkButton ID="btnView" runat="server"  Visible='<%# Eval("status_id").Equals(3)%>' CssClass="btn btn-outline-primary btn-sm"  getrefid='<%# Eval("TransactionRef_id") %>' OnClick="btnView_Click"><i class="fa-thin fa-eye"></i></asp:LinkButton>
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
                       <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />                </asp:GridView>
                <asp:SqlDataSource runat="server" ID="SDSpendingentries" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                    SelectCommand="SELECT id,CreatedOn,FileName,FilePath,Amount,IFNULL(TransactionNumber,'Not Updated') AS TransactionNumbers,SettlementCount,'IDFC' as Bank,TransactionRef_id,status_id,
                         CASE WHEN status_id=1 THEN 'File Created' WHEN status_id=2 THEN 'File Uploaded' WHEN status_id=3 THEN 'File Closed' END AS status_update,(SELECT COUNT(*) FROM finance_transaction WHERE FileId=BankFileDetails.id AND status_id=6) AS failedCount  FROM BankFileDetails WHERE (trim(@search) like '' or FileName like CONCAT('%', @search, '%')) and (@fromDate IS NULL OR @fromDate = '' OR CAST(CreatedOn AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate IS NULL OR @toDate = '' OR CAST(CreatedOn AS DATE) <= CAST(@toDate AS DATE)) AND (( (@status = 0 AND status_id IN (1, 2)) OR(@status = 1 AND status_id=1) OR (@status = 2 AND status_id=2) OR (@status = 3 AND status_id=3))) ">
                   <SelectParameters>
                        <asp:ControlParameter Name="status" ControlID="dlentrytpeupdae" ConvertEmptyStringToNull="false" />
                        <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                         <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                           <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" /> 
                    </SelectParameters>
                </asp:SqlDataSource>
            </div>
                </div>              
            </div>
        </div>
    </div>
    <div class="modal fade" id="priviewledgerpopup" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
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
                                <div class="card-header py-2 px-1 border-0">
                                    <div class="row row-sm">
                                        <div class="col-12 col-lg-11">
                                            <div class="text-left"><b class="mr-1 tx-dark">File Name:</b><asp:Literal ID="lbstoregroup" runat="server"></asp:Literal></div>
                                        </div>
                                    </div>
                                </div>

                               <div class="card-body rounded-0 p-0">
                                    <div class="table-responsive p-0" style="max-height: 300px;">
                                        <asp:ListView ID="lvsettlement" runat="server">
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table gridview_table table-bordered table-head-fixed m-0">
                                                    <tr id="Tr1" runat="server">
                                                        <th id="Td1" style="font-weight:bold" runat="server">Head of Account</th>
                                                        <th id="Td2" style="font-weight:bold" runat="server">Debit</th>
                                                        <th id="Td3" style="font-weight:bold" runat="server">Credit</th>
                                                    </tr>
                                                    <tr id="ItemPlaceholder" runat="server">
                                                    </tr>
                                                    <tfoot>
                                                        <tr>
                                                            <th id="Td4" style="font-weight:bold" runat="server">Total</th>
                                                            <th align="right" style="text-align: right;">
                                                                <asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></th>
                                                            <th align="right" style="text-align: right;">
                                                                <asp:Literal ID="ltrCRTotal" runat="server"></asp:Literal></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <tr class="TableData">
                                                    <td>
                                                        <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("particulars")%>'></asp:Label>
                                                    </td>
                                                    <td align="right">
                                                        <asp:Label ID="lbDramount" runat="server" Text='<%#Eval("debit", "{0:0.00}") %>'></asp:Label>
                                                    </td>
                                                    <td align="right">
                                                        <asp:Label ID="lbCramount" runat="server" Text='<%# (Eval("credit", "{0:0.00}")) %>'></asp:Label>
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
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="row row-sm">
                                <div class="col-12 col-md-6 d-flex form-group align-items-center w-100 justify-content-center">
                                    <label for="txtToDate" class="tx-dark" style="width: 150px;" runat="server">Transaction Date</label>
                                    <asp:TextBox ID="txtdate" runat="server" CssClass="form-control" DataFormatString="{0:dd-MM-yyyy}" TextMode ="Date"></asp:TextBox>
                                    <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please Add Transaction date " ValidationGroup="AddTransaction" ControlToValidate="txtdate" Display="Dynamic"></asp:RequiredFieldValidator>

                                </div>
                                <div class="col-12 d-flex form-group col-md-6 align-items-center w-100 justify-content-center">
                                    <label for="txtToDate" class="tx-dark" style="width: 130px;" runat="server">Transaction No</label>
                                    <asp:TextBox ID="txttransationno" CssClass="form-control" runat="server"></asp:TextBox>
                                    <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please Add Transaction Number " ValidationGroup="AddTransaction" ControlToValidate="txttransationno" Display="Dynamic"></asp:RequiredFieldValidator>
                                </div>
                            </div>

                            <div class="d-flex mt-3 align-items-center justify-content-center">
                                <a data-toggle="modal" href="#priviewledgerpopup" class="btn btn-outline-dark btn-sm py-1 px-3 mx-2">Close</a>
                                <%--<asp:Button data-toggle="modal" href="#myModal2" ID="btnskip" runat="server" Text="Skip" CssClass="btn btn-outline-dark btn-sm py-1 px-3 mx-2" />--%>
                                <asp:Button ID="btnapprove" ValidationGroup="AddTransaction" runat="server" OnClick="btnapprove_Click" Text="Update" CssClass="btn btn-outline-primary btn-sm py-1 px-3 mx-2" />
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <div class="modal" id="Puppaymentdetails" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered w-100">
            <div class="modal-content">
                <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>                
                <div class="modal-body">
                      <div class="row">
                        <div class="col-12">
                            <div class="card mb-2" style="box-shadow: none;">
                                <div class="card-header py-2 px-1 border-0">
                                    <div class="row row-sm">
                                        <div class="col-12">
                                            <div class="text-left"><b class="mr-1">Update UTR</b></div>
                                            <div class="d-flex mt-2">
                                                <div class="custom-control pl-0 custom-radio flex-wrap d-flex align-items-center mr-3">
                                                    <div class="d-flex align-item-center w-100 mb-2">
                                                        <asp:Label runat="server" CssClass="mr-1" Text="Upload Bank Response file for "></asp:Label>
                                                        <asp:Label runat="server" ID="lblfileupload"></asp:Label>
                                                    </div>
                                                    
<%--                                                    <asp:RadioButton ID="rbtnupload" runat="server" Text="Upload Bank Document" Checked="true" GroupName="Payment" AutoPostBack="true" OnCheckedChanged="rbtnupload_CheckedChanged" />--%>
                                                    <asp:FileUpload ID="FileUpload1" onchange="initiatePostBack()" AutoPostBack="true"   runat="server" />
                                                    <%--<asp:Button ID="btn" runat="server" Text="Upload" OnClick="btn_Click" CssClass="btn btn-primary btn-sm py-1 px-3 mx-2" />--%>

                                                    <asp:LinkButton ID="btnFileUploadHidClick" runat="server"  style="display: none;" OnClick="btnUpload_OnClick"></asp:LinkButton>
                                                </div>
                                                <%--<div class="custom-control pl-0 custom-radio d-flex align-items-center">                                                   
                                                    <asp:RadioButton ID="rbtnpayment" runat="server" Text="Upload Bank Document Mannually" Checked="true" AutoPostBack="true" OnCheckedChanged="rbtnpayment_CheckedChanged"  GroupName="Payment" />  
                                                </div>--%>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body rounded-0 p-0">                                 
                                       <asp:Panel Visible="true" runat="server" ID="pnlupload">
                                           <div class="table-responsive">
                                               <asp:GridView ID="gvupload" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                                   AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                                               </asp:GridView>
                                           </div>
                                       </asp:Panel>
                                    <div class="d-flex mt-3  align-items-center w-100 justify-content-center">
                                        <%--<asp:Label runat="server">Bank Ref : </asp:Label>
                                        <asp:TextBox ID="txtbankref" runat="server"></asp:TextBox>--%>
                                        <asp:Button ID="btnSave" runat="server" Text="Save" OnClick="btnSave_Click" CssClass="btn btn-primary btn-sm py-1 px-3 mx-2" />
                                        <asp:Button ID="btnClose" runat="server" Text="Back" CssClass="btn btn-primary btn-sm py-1 px-3 mx-2" />
                                    </div>
                                   <%-- <div class="d-flex mt-3 align-items-left w-100 justify-content-end">
                                        <asp:Button ID="btnClose" runat="server" Text="Close" CssClass="btn btn-primary btn-sm py-1 px-3 mx-2" />
                                    </div> --%>                                  
                                </div>
                            </div>                                                
                        </div>
                    </div>

                </div>                
            </div>
        </div>
    </div>
    <script type="text/javascript">
    function initiatePostBack() {
        <%= Page.ClientScript.GetPostBackEventReference(btnFileUploadHidClick, string.Empty) %>
        }
    </script>
     <style>
           .table.table-head-fixed tr:nth-child(1) th 
           {
            background-color:#f8f9fa;
            border-bottom: 0;
            box-shadow: inset 0 1px 0 #dee2e6, inset 0 -1px 0 #dee2e6;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
           }
        .table.table-head-fixed tr:last-child th {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background-color: #f8f9fa;
            border-top: 0;
            box-shadow: inset 0 1px 0 #dee2e6, inset 0 -1px 0 #dee2e6;
        }
         @media (min-width: 992px) {
             #priviewledgerpopup .modal-dialog {
                 max-width: 1106px;
                 width: 70%;
             }
         }
     </style>
</asp:Content>

