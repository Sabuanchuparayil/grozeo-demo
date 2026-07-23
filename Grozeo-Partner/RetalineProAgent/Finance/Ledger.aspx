<%@ Page Language="C#" MasterPageFile="~/Finance/FinanceMaster.master" Title="Ledger" AutoEventWireup="true" CodeBehind="Ledger.aspx.cs" Inherits="RetalineProAgent.Finance.Ledger" %>
<asp:Content ContentPlaceHolderID="cpNhead" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Navigations/AccountBooks"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <h6 class="slim-pagetitle">Ledger</h6>
    <p class="mb-0">Ledger</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
        <div class="row row-sm">
            <div class="col-12">
                <div class="card">
                    <div class="card-header shadow_top">
                        <div class="d-flex flex-wrap filter_ledger row filter_ledger">

                            <div class="col-12 col-lg-5">
                                 <label for="selLedger" class="mb-1 tx-dark" runat="server">Ledger:</label>
                                    <asp:DropDownList ID="selLedger" DataSourceID="SDSLedgerTypes" CssClass="form-control select2" DataTextField="name"  DataValueField="refId" AppendDataBoundItems="true" runat="server">
                                        <asp:ListItem Text="Select Ledger" Value="0"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:SqlDataSource ID="SDSLedgerTypes" runat="server" SelectCommand="select id,refId, name from [ledger]" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                            </div>
                            <div class="col-md-6 mt-2 mt-lg-0 d-flex flex-lg-nowrap flex-wrap justify-content-between">
                                <div class="col-12 col-md-10 d-flex flex-lg-nowrap flex-wrap p-0">
                                    <div class="form-group mb-0 w-100 mr-lg-2">
                                    <label for="txtFromDate" class="mb-1 tx-dark" runat="server">From:</label>
                                    <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" required TextMode="Date" />
                                </div>
                                <div class="form-group w-100 mb-0 mt-2 mt-lg-0 ml-lg-2">
                                    <label for="txtToDate" class="mb-1 tx-dark" runat="server">To:</label>
                                    <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" required TextMode="Date" />
                                </div>                                   
                                </div>
                                <div class="form-group col-12 mb-0 mt-2 mt-lg-0 col-md-2 d-flex align-items-end pl-0 pl-lg-3 pr-0">
                                    <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                    <asp:LinkButton ID="lbtnSearch" dataid='<%# Eval("id") %>' CssClass="btn btn-block btn-primary"  runat="server" autocomplete="off" ><i class="fa fa-search"></i></asp:LinkButton>
                                </div>
                                <div class="form-group col-12 mb-0 mt-2 mt-lg-0 col-md-2 d-flex align-items-end pl-0 pl-lg-3 pr-0">
                                    <asp:LinkButton ID="lbtnDownload" CssClass="btn btn-block btn-outline-secondary"  OnClick="lbtnDownload_Click" runat="server"><i class="fa fa-download"></i></asp:LinkButton>
                                </div>
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
                <div class="card-body">
                      <div class="table-responsive mailbox-messages">
                          <asp:GridView ID="gvLedger" DataSourceID="SDSLedger" AllowPaging="true" AllowSorting="false" PageSize="15" CssClass="table table-bordered gridview_table" BorderColor="#ECECEC"  runat="server" AutoGenerateColumns="false">
                              <Columns>
                                  <asp:BoundField HeaderText="Date" HeaderStyle-Font-Bold="true" ReadOnly="true" DataField="createdOn" SortExpression="createdOn" DataFormatString="{0:dd-MMM-yyyy}" ItemStyle-Width="15%"   />
                                  <asp:BoundField HeaderText="Voucher Number" HeaderStyle-Font-Bold="true" ReadOnly="true" DataField="voucherSlNostring" ItemStyle-Width="10%"  />
                                  <asp:BoundField HeaderText="ID" DataField="id" Visible="false"/>
                                  <asp:BoundField HeaderText="Particulars" HeaderStyle-Font-Bold="true" ReadOnly="true" DataField="opposite_ledgers_with_amounts" ItemStyle-Width="30%" ItemStyle-HorizontalAlign="left" HeaderStyle-HorizontalAlign="Center"  />
                                  <asp:TemplateField SortExpression="Dr" HeaderStyle-Font-Bold="true" HeaderText="Debit"  ItemStyle-Width="15%" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="py-1">
                                      <ItemTemplate>
                                          <asp:Label runat="server" Text='<%#(Eval("selected_ledger_isDebtor").ToString() == "1" ? Eval("selected_ledger_amount","{0:n}") : "") %>'></asp:Label>                                         
                                      </ItemTemplate>
                                  </asp:TemplateField>
                                  <asp:TemplateField SortExpression="Cr" HeaderStyle-Font-Bold="true" HeaderText="Credit" ItemStyle-Width="15%" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="py-1">
                                      <ItemTemplate>
                                          <%# (Eval("selected_ledger_isDebtor").ToString() == "0" ? Eval("selected_ledger_amount","{0:n}") : "") %>
                                      </ItemTemplate>
                                  </asp:TemplateField>
                                  <asp:TemplateField HeaderText="Balance" HeaderStyle-Font-Bold="true" ItemStyle-Width="30%" ItemStyle-HorizontalAlign="right" HeaderStyle-HorizontalAlign="Center">
                                      <ItemTemplate>
                                          <asp:Label ID="lblRunningBalance" runat="server"
                                              Text='<%# Eval("selected_ledger_closingbalance", "{0:n}") == null ? "" : Math.Abs(Convert.ToDecimal(Eval("selected_ledger_closingbalance"))).ToString("n") %>'>
                                          </asp:Label>
                                      </ItemTemplate>
                                  </asp:TemplateField>
                                   <asp:TemplateField HeaderText="Dr/Cr" ItemStyle-Width="30%" HeaderStyle-Font-Bold="true" ItemStyle-HorizontalAlign="center" HeaderStyle-HorizontalAlign="Center">
                                      <ItemTemplate>
                                          <asp:Label ID="lblRunningBalance" runat="server"
                                              Text='<%# Eval("selected_ledger_closingbalance") == DBNull.Value || Eval("selected_ledger_closingbalance") == null ? "" :
                                                (Convert.ToDecimal(Eval("selected_ledger_closingbalance")) < 0 ? "Cr" : "Dr") %>'>
                                          </asp:Label>
                                      </ItemTemplate>
                                  </asp:TemplateField>
                                  <asp:TemplateField HeaderText="Details" HeaderStyle-Font-Bold="true" ItemStyle-HorizontalAlign="Center" ItemStyle-Width="20%">
                                      <ItemTemplate>
                                          <button type="button" id="btnDetails" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#personalModal" data-id='<%# Eval("data_entry_id") %>' onclick="loadVoucherDetails(<%# Eval("data_entry_id") %>)">View</button>                                         
                                      </ItemTemplate>                                      
                                  </asp:TemplateField>
                              </Columns>
                              <EmptyDataTemplate>
                                  <div class="text-center">
                                      <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                      <h6 class="mb-3">No record available</h6>
                                  </div>
                              </EmptyDataTemplate>
                                <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                               <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                          </asp:GridView>
                      </div> 
                        <asp:SqlDataSource ID="SDSLedger" runat="server" 
                        SelectCommand="WITH selected_ledger AS (SELECT id, refId, name  FROM ledger  WHERE refId = @ledgertype),base_transactions AS (SELECT 
                                       de.id AS data_entry_id,de.entity_id,de.voucherSlNostring,de.createdOn,de.docSerialNo,tr.particulars,tr.id,
                                       tr.isDebtor AS selected_ledger_isDebtor, tr.amount AS selected_ledger_amount,tr.closingbalance AS selected_ledger_closingbalance,sl.name AS selected_ledger_name
                                       FROM transactions tr INNER JOIN data_entry de ON tr.data_entry_id = de.id INNER JOIN selected_ledger sl ON tr.ledger_id = sl.id),
                                       opposite_transactions AS (SELECT tr.data_entry_id,l.name AS ledger_name, tr.amount,tr.isDebtor,tr.particulars,tr.closingbalance AS selected_ledger_closingbalance
                                       FROM transactions tr INNER JOIN ledger l ON l.id = tr.ledger_id WHERE tr.data_entry_id IN (SELECT data_entry_id FROM base_transactions) AND tr.ledger_id <> (SELECT id FROM selected_ledger))
                                       SELECT b.data_entry_id ,b.entity_id,b.id,b.voucherSlNostring,b.createdOn,b.selected_ledger_isDebtor,b.docSerialNo,b.selected_ledger_name,b.selected_ledger_amount,b.selected_ledger_closingbalance,CASE b.selected_ledger_isDebtor 
                                       WHEN 1 THEN 'DR' WHEN 0 THEN 'CR' END AS selected_ledger_side,    STRING_AGG( CASE  WHEN (b.selected_ledger_isDebtor = 1 AND o.isDebtor = 0) OR (b.selected_ledger_isDebtor = 0 AND o.isDebtor = 1) THEN o.particulars END,  ', ') AS opposite_ledgers_with_amounts,    
                                          SUM(CASE WHEN (b.selected_ledger_isDebtor = 1 AND o.isDebtor = 0)  OR (b.selected_ledger_isDebtor = 0 AND o.isDebtor = 1)  THEN o.amount ELSE 0 END) AS total_opposite_amount
                                       FROM base_transactions b LEFT JOIN opposite_transactions o ON b.data_entry_id = o.data_entry_id where CAST(b.createdOn AS DATE) >= @fromDate AND CAST(b.createdOn AS DATE) <= @toDate  GROUP BY b.data_entry_id,
                                       b.entity_id,b.voucherSlNostring,b.createdOn,b.docSerialNo,b.selected_ledger_name,b.selected_ledger_amount,b.selected_ledger_closingbalance,b.id,b.selected_ledger_isDebtor ORDER BY b.id ASC"                        
                                        ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">   
                       <SelectParameters>
                           <asp:ControlParameter ControlID="selLedger" PropertyName="Text" Name="ledgertype" />
                           <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" Name="fromDate" />
                           <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" />
                       </SelectParameters>
                   </asp:SqlDataSource>
                </div>
                </div>
            </div>
        </div>
    <!-- Modal -->
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
        .table th, .table td {
          vertical-align: middle;
        }
        .table-responsive th > a {
            color:#343a40;
        }
        #vouchertype {
            width: 100%;
            max-height: 500px !important;
            height: auto;
            overflow: hidden !important;
            border: 0;
            min-height: 250px !important;
        }

        .popupdate {
            width: 150px;
        }

        #personalModal .close {
            position: absolute;
            z-index: 1;
            right: 10px;
            top: 10px;
        }

        @media (min-width: 576px) {
            #personalModal .modal-dialog {
                max-width: 660px;
            }
        }
    </style>    
    <script type="text/javascript">
        function loadVoucherDetails(id) {
            $('#dvpopupvoucherdetails').html('<div>Loading .. </div>');
            $('#dvpopupvoucherdetails').load('/Finance/VouchuerDetails?id=' + id);
        }
    </script>
     <script>
        $(document).ready(function () {
            $(document).ready(function () {
                $('.select2').select2();

                //Bootstrap Duallistbox
                $('.duallistbox').bootstrapDualListbox();
            });
        });
     </script>

</asp:Content>
