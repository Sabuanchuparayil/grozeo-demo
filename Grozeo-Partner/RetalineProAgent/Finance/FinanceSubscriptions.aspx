<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="FinanceSubscriptions.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.FinanceSubscriptions" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
     <a href="/Navigations/Accounting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div class="d-flex align-items-center">
            <h6 class="slim-pagetitle">Subscriptions</h6>                        
        </div>
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="/Content/css/custom/Finance/custom.css">
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="row row-sm justify-content-between">
                        <div class="col-12 col-lg-4">
                            <div class="row row-sm">
                                <div class="form-group col-6 mb-2 mb-lg-0 pr-md-1">
                                    <label for="txtFromDate" class="tx-dark" runat="server">From</label>
                                    <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                                </div>
                                <div class="form-group col-6 mb-2 mb-lg-0 pl-md-1">
                                    <label for="txtToDate" class="tx-dark" runat="server">To</label>
                                    <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                                </div>
                            </div>

                        </div>
                        <div class="col-12  col-lg-8">
                            <div class=" d-flex flex-wrap flex-lg-nowrap align-items-end">
                                <div class="form-group w-100 mb-2 mb-lg-0  col-8 col-sm-6 pl-0">
                                    <label for="seltype" class="tx-dark" runat="server"> Subscription Type</label>
                                    <asp:DropDownList ID="dlsubscriptiontype" CssClass="form-control py-0" AutoPostBack="true" runat="server">
                                        <asp:ListItem Text="select the Subscription Type" Value="0"></asp:ListItem>
                                        <asp:ListItem Text="Scale" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Shine" Value="3"></asp:ListItem>
                                        <asp:ListItem Text="PWA" Value="4"></asp:ListItem>
                                        <asp:ListItem Text="Andriod App" Value="5"></asp:ListItem>
                                         <asp:ListItem Text="iOS App" Value="6"></asp:ListItem>
                                         </asp:DropDownList>
                                </div>
                               <div class="input-group input-group col-auto w-auto align-items-end mt-2 p-0 mt-md-0 mb-2 mb-lg-0">
                                    <asp:Button ID="btnsearch" CssClass="btn btn-primary" runat="server" Text="GO"/>                            
                                </div>                           
                                <div class="form-group d-flex mb-2 mb-lg-0 col-12 col-sm-4 pr-0 pr-md-1 pl-0 pl-sm-2">
                                    <div class="input_search_box" style="width: 100%">                                       
                                        <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton runat="server" CssClass="input-group-append">                        
                                        </asp:LinkButton>                                       
                                        <asp:LinkButton ID="lbtnSearch"  CssClass="btn bd bd-l-0 tx-gray-600" OnClick="lbtnSearch_Click" runat="server" autocomplete="off"><i class="fa fa-search mt-1"></i></asp:LinkButton>
                                    </div>
                                </div>
                                <div class="form-group col-2 col-sm-2 mb-0 pr-0 d-flex">
                                    <asp:LinkButton runat="server" ID="lnkExport1" OnClick="lnkExport1_Click" CssClass="btn btn-sm btn-outline-primary ml-2" ToolTip="Export File">
                                          <i class="fa-light fa-arrow-down-to-bracket tx-16"></i>
                                    </asp:LinkButton>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="card-body">

                    <div class="table-responsive mailbox-messages">
                        <asp:GridView ID="gvSubscription" runat="server" DataKeyNames="PGSubscriptionId,name,createdOn,planname,billingcycle,Pricepercycle,Discount,groupid" GridLines="None" DataSourceID="SDSsubscription" BorderColor="#ECECEC" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                            <Columns>
                                <asp:BoundField HeaderText="createdOn" DataFormatString="{0:dd/MMM/yyyy}" DataField="createdOn" NullDisplayText="Not Submitted" />
                                <asp:BoundField HeaderText="Store Group" DataField="name" />
                                <asp:TemplateField HeaderText="Subscription Item">
                                    <ItemTemplate>
                                        <%# Eval("planname") + " " + Eval("billingcycle") %>
                                    </ItemTemplate>
                                </asp:TemplateField>
                                <asp:BoundField HeaderText="Subscription ID" DataField="PGSubscriptionId" NullDisplayText="NA" />
                                <asp:BoundField HeaderText="Rate" DataField="Pricepercycle" ItemStyle-HorizontalAlign="Right" NullDisplayText="NA" />
                                <asp:BoundField HeaderText="Discount" DataField="Discount" ItemStyle-HorizontalAlign="Right" NullDisplayText="NA" />
                                <asp:BoundField HeaderText="Amount" DataField="Pricepercycle" ItemStyle-HorizontalAlign="Right" NullDisplayText="NA" />
                                <asp:TemplateField HeaderText="Referrer">
                                    <ItemTemplate>NA </ItemTemplate>
                                </asp:TemplateField>
                                <asp:TemplateField HeaderText="Status">
                                    <ItemTemplate>
                                        <%# Eval("paymentStatus").ToString().ToLower() == "pending" ? "Subscription Initialized" : Eval("paymentStatus").ToString().ToLower() == "paid" ? "Subscribed" : Eval("paymentStatus").ToString().ToLower() == "failed" ? "Subscription Failed" : "Unknown Status" %>
                                    </ItemTemplate>
                                </asp:TemplateField>
                                 <asp:TemplateField HeaderText="Action">
                                    <ItemTemplate>
                                        <asp:LinkButton runat="server" ID="btnsubscrib"  OnClick="btnsubscrib_Click" subId='<%# Eval("PGSubscriptionId") %>' CssClass="btn btn-block btn-primary" Text="Post Now"></asp:LinkButton>
                                        <asp:LinkButton runat="server" Visible="false" CssClass="btn btn-block btn-primary" Text="View Post"></asp:LinkButton>
                                    </ItemTemplate>
                                </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No account added</h6>
                                </div>
                            </EmptyDataTemplate>
                            <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                            <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                        </asp:GridView>
                        <asp:HiddenField runat="server" ID="hdnstoregroupid" />
                        <asp:SqlDataSource ID="SDSsubscription" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
                            SelectCommand="select ss.id,s.groupid, ss.planname,s.name,m.PGSubscriptionId,m.paymentStatus,m.StartDate,m.ExpiryDate,m.createdOn,sub.BillingCycle,sub.Pricepercycle,sub.Discount
                                          from s_merchantSubscriptions m inner join store s on s.tenantId=m.merchantId inner join s_planpricing sub on sub.planpricingId=m.priceid inner join  S_SubscriptionPlans ss on ss.id=m.planid WHERE ((TRIM(@search) = '' OR s.name LIKE CONCAT('%', @search, '%') OR m.PGSubscriptionId LIKE CONCAT('%', @search, '%')))
                                            AND (@fromDate IS NULL OR @fromDate = '' OR CAST(m.createdOn AS DATE) >= CAST(@fromDate AS DATE))
                                            AND (@toDate IS NULL OR @toDate = '' OR CAST(m.createdOn AS DATE) <= CAST(@toDate AS DATE))
                                          AND (@status IS NULL OR @status = '' OR @status = 0 OR ss.id = @status)">
                            <SelectParameters>
                             <asp:ControlParameter Name="search" ControlID="txtSearch"  ConvertEmptyStringToNull="false"  />                                                               
                            <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                            <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" />  
                              <asp:ControlParameter ControlID="dlsubscriptiontype" Name="status" Type="Int32" />  
                                </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
            </div>
        </div>
    </div>   
    <div class="modal" id="razorpayaccountDetails" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <h5 class="modal-title">Details of Subscription Received</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body sub_tran">
                    <div class="row row-sm">
                             <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="text-left tx-dark">Store Group:</label><asp:Label runat="server" ID="ltrstoregroup"></asp:Label>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="text-left tx-dark">Collected:</label><asp:TextBox ID="txtcollect" runat="server" CssClass="form-control"  autocomplete="nofill" />                               
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="text-left tx-dark">Subscription: </label><asp:Label runat="server" ID="ltrsubscription"></asp:Label>                                
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="text-left tx-dark">Amout:</label><asp:TextBox ID="txtamount" runat="server" CssClass="form-control"  autocomplete="nofill" />                              
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="form-control-label mb-1 tx-dark">Start date:</label><asp:Label runat="server" ID="ltrcrateddate"></asp:Label>                                                                
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-2">
                                <label class="text-left tx-dark">GST:</label><asp:TextBox ID="txtgst" runat="server" CssClass="form-control"  autocomplete="nofill" />                               
                            </div>
                        </div>
                        <!-- col-4 -->

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="text-left tx-dark">Rate:</label><asp:Label runat="server" ID="ltrrate"></asp:Label>                               
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="text-left tx-dark">MDR:</label><asp:TextBox ID="txtMDR" runat="server" CssClass="form-control"  autocomplete="nofill" />                                
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="text-left tx-dark">Discount:</label><asp:Label runat="server" ID="ltrdiscount"></asp:Label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="text-left tx-dark">GST MDR:</label><asp:TextBox ID="txtgstmdr" runat="server" CssClass="form-control"  autocomplete="nofill" />
                            </div>
                        </div>
                        <div class="col-sm-6">                           
                            <div class="form-group">
                                <label class="text-left tx-dark">SJ voucher:</label><asp:TextBox ID="txtsjvoucher" runat="server" CssClass="form-control"  autocomplete="nofill" />
                            </div>
                        </div>
                        <div class="col-sm-6">                           
                            <div class="form-group">
                                <label class="text-left tx-dark">BR voucher:</label><asp:TextBox ID="txtbrvocher" runat="server" CssClass="form-control"  autocomplete="nofill" />
                            </div>
                        </div>
                         <div class="col-sm-6">                           
                            <div class="form-group">
                                <label class="text-left tx-dark">Receipt No:</label><asp:TextBox ID="txtreceiptno" runat="server" CssClass="form-control"  autocomplete="nofill" />
                            </div>
                        </div>
                          <div class="col-sm-6">                           
                            <div class="form-group">
                                <label class="text-left tx-dark">invoice No:</label><asp:TextBox ID="txtinvoiceno" runat="server" CssClass="form-control"  autocomplete="nofill" />
                            </div>
                        </div>                        
                    </div>
                    <div class="d-flex mt-3 align-items-center justify-content-center">
                            <asp:LinkButton ID="btnviewBRvoucher" runat="server" OnClick="btnviewBRvoucher_Click"  CssClass="btn btn-primary btn-sm py-1 px-3 mx-2" Text="View BR Voucher"></asp:LinkButton>
                            <asp:LinkButton ID="btnviewsalevoucher" runat="server" OnClick="btnviewsalevoucher_Click"  CssClass="btn btn-primary btn-sm py-1 px-3 mx-2" Text="View SR Voucher"></asp:LinkButton>
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
                    <asp:HiddenField runat="server" ID="hdnbankrecipt" />
                    <asp:HiddenField runat="server" ID="hdnsalerecipt" />
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-2" style="box-shadow: none;">
                                <div class="card-header py-2 px-1 border-0">
                                    <div class="row row-sm">
                                        <div class="col-12 col-lg-11">
                                            <div class="text-left"><b class="mr-1 tx-dark">Voucher Number:</b><asp:Literal ID="lbstoregroup" runat="server"></asp:Literal></div>
                                        </div>
                                    </div>
                                </div>
                               <div class="card-body rounded-0 p-0">
                                    <div class="table-responsive p-0" style="max-height: 300px;">
                                        <asp:ListView ID="lvsubscrionposting" runat="server">
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
                    </div>

                </div>

            </div>
        </div>
    </div>
     <style>
        .sub_tran .form-group {
            display: flex;
            gap: 5px;
            align-items:center;
        }
        .sub_tran .form-group label {
            width: 100%;
            max-width: 80px;
            margin:0px;
        }
        @media (min-width: 992px) {
             #priviewledgerpopup .modal-dialog {
                 max-width: 1106px;
                 width: 70%;
             }
         }
    </style>
</asp:Content>
