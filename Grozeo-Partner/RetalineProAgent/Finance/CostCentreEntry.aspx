<%@ Page Language="C#"  MasterPageFile="~/Finance/FinanceMaster.master" Title="Voucher Entry"  AutoEventWireup="true" CodeBehind ="CostCentreEntry.aspx.cs" Inherits="RetalineProAgent.Finance.CostCentreEntry" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
      <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <script src="../Content/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<%--    <script src="../Content/lib/jquery/js/jquery-ui.js"></script>
    <script src="../Content/lib/jquery/js/jquery.js"></script>--%>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
   <%-- <link rel="stylesheet" href="/Content/css/custom/custom.css"> --%>   
      <link rel="stylesheet" href="/Content/customadmin/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/Content/customadmin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <script src="/Content/customadmin/plugins/select2/js/select2.min.js"></script>

<style>
    tr.selectrow  {
        background-color:#e6eff9;
    }
</style>

<script>

    function selectAndScrollToRow(rowNumber) {
        var selectedRow = $('#CostCentreTable tr:eq(' + (rowNumber+1) + ')');
        selectedRow.addClass('selectrow');
        scrollToRow(rowNumber);
    }

    function scrollToRow(rowNumber) {

        var selectedRow = $('#CostCentreTable tr:eq(' + rowNumber + ')');

         if (selectedRow.length > 0) {
            selectedRow[0].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        } else {
            console.log('Row not found');
        }
    }
</script>

    <h6 class="slim-pagetitle">Cost Centre Entry</h6>
    <p class="mb-0">Cost Centre Entry</p>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent"> 
        <div class="row row-sm">

            <style>
                body {
                    overflow-x: hidden;
                }

                .table.table-head-fixed thead tr:nth-child(1) th {
                }

                .table.table-head-fixed tfoot tr:nth-child(1) th {
                    position: sticky;
                    bottom: 0;
                    z-index: 10;
                    border-top: 0;
                    box-shadow: inset 0 1px 0 #dee2e6,inset 0 -1px 0 #dee2e6;
                }

                @keyframes placeHolderShimmer {
                    0% {
                        background-position: -800px 0
                    }

                    100% {
                        background-position: 800px 0
                    }
                }

                .wireframe {
                    height: 8px;
                    width: 100%;
                    max-width: 75%;
                    background: #e8e8e8;
                    border-radius: 10px;
                    margin-top: 5px;
                    animation-duration: 2s;
                    animation-fill-mode: forwards;
                    animation-iteration-count: infinite;
                    animation-name: placeHolderShimmer;
                    animation-timing-function: linear;
                    background-color: #f6f7f8;
                    background: linear-gradient(to right, #eee 8%, #e4e4e4 18%, #eee 33%);
                    background-size: 800px 104px;
                }
            </style>

            <div class="col-12 col-lg-12">
                <div class="card" style="height: calc(100% - 15px);">


                    <div class="card-body shadow_top">

                        <div class="row row-sm">

                            <div class="col-12">
                               <div class="card-header py-2 px-1 border-0">
                                    <div class="row row-sm">
                                        <div class="col-12 col-lg-11">
                                            <div class="text-left"><b class="mr-1">Cost Centre Rule:</b><asp:Literal ID="lbCostCentreRule" runat="server"></asp:Literal></div>
                                            <div class="text-left"><b class="mr-1">Ledger :</b><asp:Literal ID="lbLedger" runat="server"></asp:Literal></div>
                                        </div>
                                    </div>
                                </div>
                                <asp:Panel ID="Panel1" runat="server" CssClass="right-panel">
                                    <div class="table-responsive p-0 mb-3" id="divLedger" style="max-height: 400px;">
                                        <table id="CostCentreTable" class="table table-bordered table-head-fixed border-top mb-0">
                                            <thead>
                                                <tr class="TableHeader">
                                                    <th>Sl. No.</th>
                                                    <th>Cost Centre</th>
                                                    <th align="right" width="125">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                               <asp:Panel runat="server" Visible="true" CssClass="w-100 d-none" ID="ShowDiv">
                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                    </tr>

                                                </asp:Panel>
                                                <asp:ListView ID="lvCostCentreEntry" OnDataBound="lvCostCentreEntry_DataBound" runat="server" DataKeyNames="costCentreId" OnItemDataBound="lvCostCentreEntry_ItemDataBound"
                                                    OnItemCommand="lvCostCentreEntry_ItemCommand" OnItemDeleting="lvCostCentreEntry_ItemDeleting" OnItemCanceling="lvCostCentreEntry_ItemCanceling" 
                                                    OnItemEditing="lvCostCentreEntry_ItemEditing" OnItemUpdating="lvCostCentreEntry_ItemUpdating" OnSelectedIndexChanging="lvCostCentreEntry_SelectedIndexChanging" 
                                                    ItemPlaceholderID="plcItems" EnableViewState="true">
                                                    <LayoutTemplate>
                                                        <asp:PlaceHolder ID="plcItems" runat="server"></asp:PlaceHolder>
                                                    </LayoutTemplate>
                                                    <ItemTemplate>
                                                        <tr class="TableData">
                                                            <td align="right">
                                                                <asp:Label ID="Label1" CssClass="editingRow" runat="server" Text='<%# Container.DataItemIndex+1 %>'></asp:Label>
                                                            </td>
                                                            <td align="right" style="display:none">
                                                                <asp:Label ID="lbCostCentreID" CssClass="editingRow"  Visible="false" runat="server" Text='<%# Eval("costCentreId") %>'></asp:Label>
                                                            </td>
                                                            <td>
                                                                <span class="w-100">
                                                                    <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("costCentreName")%>'>   
                                                                    </asp:Label>
                                                                </span>
                                                                <asp:LinkButton runat="server" CommandName="Delete" OnClientClick="return confirm('Are you sure you want to delete this record?');"><i class="fa fa-trash-o text-danger ml-2"></i></asp:LinkButton>
                                                                <asp:LinkButton runat="server" CssClass="ml-1" CommandName="Edit">Edit</asp:LinkButton>
                                                                <asp:LinkButton runat="server" CssClass="ml-1" CommandName="AddRowAbove">
                                                                    &#9650;
                                                                </asp:LinkButton>
                                                                <asp:LinkButton runat="server" CssClass="ml-1" CommandName="AddRowBelow">
                                                                    &#9660;
                                                                </asp:LinkButton>
                                                            </td>
                                                            <td align="right">
                                                                <asp:Label ID="lbamount" runat="server" Text='<%# Convert.ToDouble(Eval("amount")) != 0 ? Eval("amount", "{0:0.00}") : "" %>'></asp:Label>
                                                            </td>
                                                        </tr>
                                                    </ItemTemplate>
                                                    <EditItemTemplate>
                                                        <tr class="" style="height:100%;">
                                                            <td colspan="3">
                                                                <div class="row row-sm">
                                                                    <div class="col-6">
                                                                        <div class="form-group">
                                                                            <label class="mb-0 w-100">Cost Centre</label>
                                                                            <asp:DropDownList ID="ddlCostCentre" DataSourceID="SDSCostCenters" CssClass="form-control select2" DataTextField="costCentreName" AutoPostBack="false"
                                                                                DataValueField="costCentreId"  SelectedValue='<%# Eval("costCentreId") %>'  AppendDataBoundItems="true" runat="server" OnSelectedIndexChanged="ddlCostCentre_SelectedIndexChanged">
                                                                                <asp:ListItem Text="Select Cost Centre" Value="0"></asp:ListItem>
                                                                            </asp:DropDownList>
                                                                            <asp:Label ID="lblCostCentreShow" runat="server"></asp:Label>
                                                                            <asp:SqlDataSource ID="SDSCostCenters" runat="server" SelectCommand="select id as costCentreId, name as costCentreName from [cost_centre]" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                                                        </div>
                                                                     </div>
                                                                    <div class="col-3" style="text-align: right;">
                                                                        <div class="form-group">
                                                                            <label class="mb-0">Amount</label>
                                                                            <asp:TextBox ID="txbAmount" Text='<%# Eval("amount") %>' CssClass="form-control"  Style="text-align: right;" runat="server" onchange="updateAmount();"></asp:TextBox>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-3" style="padding-top : 16px">
                                                                        <div class="form-group mb-2 text-center"> <!-- Updated class to text-center -->
                                                                            <asp:LinkButton runat="server" CssClass="ml-1 mr-1 btn btn-danger py-1" Style="width: 85px;" CommandName="Cancel">Cancel</asp:LinkButton>
                                                                            <asp:LinkButton runat="server" CssClass="ml-1 ml-1 btn btn-primary py-1" Style="width: 85px;" CommandName="Update">Save</asp:LinkButton>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </EditItemTemplate>
                                                </asp:ListView>
                                            </tbody>
                                            <tfoot>
                                                <tr id="tot" runat="server">
                                                    <th style="text-align: right;" align="right" id="thtot" runat="server">
                                                        <asp:Literal ID="Literal1" runat="server"></asp:Literal></th>
                                                    <th style="text-align: right;" align="right" id="th1" runat="server">
                                                        <asp:Literal ID="Literal2" runat="server">Total:</asp:Literal></th>
                                                    <th style="text-align: right;" align="right" id="thDr" runat="server">
                                                        <asp:Literal ID="ltrTotal" runat="server"></asp:Literal></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <!--table-responsive-->
                                </asp:Panel>
                            </div>
                            <!--col-12-->
                            <div class="col-12 px-4">
                                <div class="col-5"></div>
                                <div class="form-group mb-2 text-center"> <!-- Updated class to text-center -->
                                    <asp:Button ID="btnSave" runat="server" Enabled="true" CssClass="btn btn-primary mb-3 Voucher_entryBTN mx-auto" OnClick="btnsave_Click" Text="Save" />
                                </div>
                                <div class="col-5"></div>
                            </div>


                        </div>
                        <!--row-->

                    </div>
                    <!--card-body-->

                </div>
                <!--card-->
            </div>
            <!--col-lg-6-->
        </div>
        <!--row-->

            <div class="modal fade" id="priviewledgerpopup" data-bs-backdrop="static" data-bs-keyboard="false"
                      tabindex="-1" aria-labelledby="DocumentUploadpopupLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                    <h6 class="modal-title lh-1 font-weight-bold" id="priviewledgerpopupLabel">
                        <asp:Literal ID="ltrTitle" runat="server"></asp:Literal>
                        <asp:Label ID="ltrdate" CssClass="voucherofdate w-100 d-inline-block font-weight-normal text-sm" runat="server"></asp:Label>
                    </h6>
                    <!-- <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                            
                    </div>
                    <div class="modal-body">

                    <div class="row">

                        <div class="col-12">
                            <div class="table-responsive p-0" style="max-height: 400px;">
                                <asp:GridView ID="gvpopup" OnDataBound="gvpopup_DataBound" CssClass="table table-bordered table-head-fixed mb-0" AutoGenerateColumns="false" ShowFooter="true" runat="server">
                                    <Columns>
                                        <asp:BoundField HeaderText="Header of Account" HeaderStyle-BackColor="#DEE2E6"   DataField="particulars" SortExpression="particulars"  ItemStyle-Width="50%" />                              
                                        <asp:BoundField HeaderText="Debit" DataField="debit" SortExpression="debit" HeaderStyle-BackColor="#DEE2E6"  DataFormatString="{0:n}" ItemStyle-HorizontalAlign="Right" ItemStyle-Width="25%"  />                                                              
                                        <asp:BoundField HeaderText="Credit" DataField="credit" SortExpression="credit" HeaderStyle-BackColor="#DEE2E6"  DataFormatString="{0:n}" ItemStyle-HorizontalAlign="Right" ItemStyle-Width="25%" />
                                    </Columns>                                                          
                                </asp:GridView>                                                                          
                            </div><!--table-responsive-->
                                
                            </div><!--col-12-->

      
                        </div><!--row-->

                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <div class="btn_sec d-inline-block">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <asp:LinkButton ID="savemod"  CssClass="btn btn-primary ml-0 ml-sm-2 Voucher_entryBTN objdiv" OnClick="savemod_Click"  runat="server"  Text="Confirm & Save"></asp:LinkButton>  
                            </div>
                        </div>
                    </div><!--modal-content-->
                </div><!--modal-dialog-->
            </div><!--modal-->
  <style>
      .modal-body table.table tbody > tr:last-child > td{background:#DEE2E6; font-weight:bold; text-align:right;}
  </style>


</asp:Content>