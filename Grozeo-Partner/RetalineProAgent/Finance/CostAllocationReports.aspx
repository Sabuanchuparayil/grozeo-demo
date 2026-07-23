<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="CostAllocationReports.aspx.cs" Inherits="RetalineProAgent.Finance.CostAllocationReports" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
       <a href="/Navigations/AccountBooks"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div class="">
        <h6 class="slim-pagetitle">Cost Centre</h6>
        <p class="mb-0">You can see Cost Centre Entry here</p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
        <div class="row row-sm">
            <div class="col-12">
                <div class="card">
                    <div class="card-header shadow_top">
                        <div class="d-flex flex-wrap filter_ledger row filter_ledger">

                            <div class="col-12 col-lg-6">
                                 <label for="selLedger" class="mb-0" runat="server">Cost Centre</label>
                                    <asp:DropDownList ID="selcostcentre" DataSourceID="SDSLedgerTypes" CssClass="form-control select2" DataTextField="name" AutoPostBack="true" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                        <asp:ListItem Text="Select Cost Centre" Value="-1"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:SqlDataSource ID="SDSLedgerTypes" runat="server" SelectCommand="select id, name from cost_centre" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                            </div>
                            <div class="col-md-6 mt-2 mt-lg-0 d-flex flex-lg-nowrap flex-wrap justify-content-between">
                                <div class="col-12 col-md-10 d-flex flex-lg-nowrap flex-wrap p-0">
                                    <div class="form-group mb-0 w-100 mr-lg-2">
                                    <label for="txtFromDate" class="mb-0" runat="server">From</label>
                                    <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" required TextMode="Date" />
                                </div>
                                <div class="form-group w-100 mb-0 mt-2 mt-lg-0 ml-lg-2">
                                    <label for="txtToDate" class="mb-0" runat="server">To:</label>
                                    <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" required TextMode="Date" />
                                </div>                                   
                                </div>
                                <div class="form-group col-12 mb-0 mt-2 mt-lg-0 col-md-2 d-flex align-items-end">
                                    <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                    <asp:LinkButton ID="lbtnSearch" dataid='<%# Eval("id") %>' CssClass="btn btn-block btn-primary" runat="server" autocomplete="off" ><i class="fa fa-search"></i></asp:LinkButton>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <span class="mr-2">
                                    <b>Opening Balance:</b>
                                    <asp:Literal runat="server" ID="ltrPageCurStart" Text=""></asp:Literal>
                                </span>
                                <span class="mr-2">
                                    <b>Closing Balance:</b>
                                    <asp:Literal runat="server" ID="ltrPageCurTotal" Text=""></asp:Literal>
                                </span>
                                <span class="mr-2">
                                    <b>Total Debit: </b>
                                    <asp:Literal runat="server" ID="ltrtotaldebit" Text=""></asp:Literal>
                                </span>
                                <span class="mr-2">
                                    <b>Total Credit: </b>
                                    <asp:Literal runat="server" ID="ltrtotalcredit" Text=""></asp:Literal>
                                </span>
                            </div>
                        </div>                   
            </div>
                <div class="card-body">
                      <div class="table-responsive ">
                          <asp:GridView ID="gvCostallocation" DataSourceID="SDSCostallocation" AllowPaging="true" PageSize="15" CssClass="table table-bordered gridview_table" AllowSorting="true" runat="server" AutoGenerateColumns="false">
                              <Columns>
                                  <asp:BoundField HeaderText="Date" ReadOnly="true" DataField="createdOn" SortExpression="createdOn" HeaderStyle-HorizontalAlign="Center" DataFormatString="{0:dd-MMM-yyyy}" ItemStyle-Width="15%"   />
<%--                                  <asp:BoundField HeaderText="Voucher Number" ReadOnly="true" DataField="docSerialNo" ItemStyle-Width="10%" HeaderStyle-CssClass="py-1" />--%>
                                  <asp:BoundField HeaderText="ID" DataField="id" Visible="false"/>
                                  <asp:BoundField HeaderText=" Ledger" ReadOnly="true" DataField="name" ItemStyle-Width="30%" HeaderStyle-HorizontalAlign="Center" ItemStyle-HorizontalAlign="left"   />
                                  <asp:TemplateField SortExpression="Dr" HeaderText="Dedit"  ItemStyle-Width="15%" HeaderStyle-HorizontalAlign="Center" ItemStyle-HorizontalAlign="Right" >
                                      <ItemTemplate>
                                          <%# (Eval("isDebtor").ToString() == "1" ? Eval("Dr","{0:n}") : "") %>
                                      </ItemTemplate>
                                  </asp:TemplateField>
                                  <asp:TemplateField SortExpression="Cr" HeaderText="Credit" ItemStyle-Width="15%" HeaderStyle-HorizontalAlign="Center" ItemStyle-HorizontalAlign="Right" >
                                      <ItemTemplate>
                                          <%# (Eval("isDebtor").ToString() == "0" ? Eval("Cr","{0:n}") : "") %>
                                      </ItemTemplate>
                                  </asp:TemplateField>
                                  <asp:TemplateField HeaderText="Action" ItemStyle-HorizontalAlign="Center" HeaderStyle-HorizontalAlign="Center" ItemStyle-Width="20%" >
                                      <ItemTemplate>
                                          <button type="button" id="btnDetails" class="btn btn-secondary py-1" data-toggle="modal" data-target="#personalModal" data-id='<%# Eval("id") %>' onclick="loadVoucherDetails(<%# Eval("id") %>)">Details</button>                                         
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
                        <asp:SqlDataSource ID="SDSCostallocation" runat="server" 
                        SelectCommand="SET dateformat dmy;select le.name,tr.id,tr.[createdOn],tr.amount,tr.isDebtor,(CASE WHEN tr.isDebtor = 1 THEN [tr].[amount] ELSE 0  END) AS Dr,
                      (CASE WHEN tr.isDebtor = 0 THEN [tr].[amount] ELSE 0  END) AS Cr from  ledger le inner join transactions tr on le.id=tr.ledger_id 
                      inner join cost_centre_entries ce on ce.transactions_id=tr.id WHERE [ce].[cost_centre_id] = @costcentretype 
                        AND CAST([tr].[createdOn] AS DATE)  >= @fromDate AND CAST([tr].[createdOn] AS DATE) <= @toDate group by tr.ledger_id,le.name,tr.amount,tr.isDebtor,tr.createdOn,tr.id"
                        ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">   
                       <SelectParameters>
                           <asp:ControlParameter ControlID="selcostcentre" PropertyName="Text" Type="Int32" Name="costcentretype" />
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

        .table th {
            text-align: center;
        }
        .table-responsive th > a {
            color: #FFF;
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
            $('#dvpopupvoucherdetails').load('/Finance/Costentrydetails?transactions_id=' + id);
        }
    </script>

</asp:Content>

