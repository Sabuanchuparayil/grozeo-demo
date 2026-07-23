<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="AutoPostingRules.aspx.cs" Inherits="RetalineProAgent.Finance.AutoPostingRules" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
      <a href="/Finance/Autopostingsettings"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div class="">
        <div class="d-flex align-items-center">
            <h6 class="slim-pagetitle">Finance Auto Posting Rules</h6>
            <div class="d-inline-block">
                <div class="d-inline-block ml-3">                  
                </div>
            </div>
        </div>
    </div>
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="/Content/css/custom/Finance/custom.css">
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="row row-sm">
        <div class="col-12 ">
            <div class="card">
                <div class="card-body p-2">
                    <div class="row row-sm">
                        <div class="col-lg-6 ">
                            <div class="form-group">
                                <label class="mb-1">Finance Function</label>
                                <asp:DropDownList ID="ddleventmaster" DataSourceID="SDSeventmaster"  OnSelectedIndexChanged="ddleventmaster_SelectedIndexChanged" CssClass="form-control select2 py-0" DataTextField="name"  DataValueField="id" AppendDataBoundItems="true" runat="server">
                                    <asp:ListItem Text="Select Finance Posting Function" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ID="SDSeventmaster" runat="server" SelectCommand="SELECT id,NAME FROM finance_event_master order by name" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                            </div>
                        </div>

                        <div class="col-lg-6 ">
                            <div class="form-group">
                                <label class="mb-1">Voucher Type</label>
                                <asp:DropDownList ID="ddlvoucher" DataSourceID="SDSvouchertype"  OnSelectedIndexChanged="ddlvoucher_SelectedIndexChanged" CssClass="form-control select2 py-0" DataTextField="name"  DataValueField="id" AppendDataBoundItems="true" runat="server">
                                    <asp:ListItem Text="Select Voucher Type" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ID="SDSvouchertype" runat="server" SelectCommand="select id,name from voucher_type order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                            </div>
                        </div>

                    </div>
                    <!--row-->
                    <div class="p-3 border">
                    <div class="row row-sm">
                        <div class="col-12 col-lg-3 mb-3 mb-lg-0">
                            <label class="mb-1">  Posting Location</label>
                             <asp:DropDownList ID="ddlpostingloaction" CssClass="form-control select2 py-0" AutoPostBack="true" OnSelectedIndexChanged="ddlpostingloaction_SelectedIndexChanged" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Posting Location" Value="-1"></asp:ListItem>
                                <asp:ListItem Text="Ledger" Value="0"></asp:ListItem>
                                <asp:ListItem  Text="Cost Centre"  Value="1"></asp:ListItem>
                            </asp:DropDownList>                           
                        </div>                      
                        <div class="col-12 col-lg-3 mb-3 mb-lg-0 ">
                            <label class="mb-1">Ledger/Cost Centre</label>
                            <asp:DropDownList ID="ddlselect"  CssClass="form-control"  DataTextField="cost_purpose"
                                DataValueField="id" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Ledger/Cost Centre" Value="-1"></asp:ListItem>                                
                            </asp:DropDownList>
                            <asp:DropDownList ID="ddlCostcentre" DataSourceID="SDSCostcentre" Visible="false" CssClass="form-control" DataTextField="name"
                                DataValueField="combinedId"  runat="server">
                                <asp:ListItem Text="Select Cost Centre" Value="-1"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Cost centre" ValidationGroup="Addentry" ControlToValidate="ddlCostcentre" Display="Dynamic"></asp:RequiredFieldValidator>
                            <asp:SqlDataSource ID="SDSCostcentre" runat="server" SelectCommand="select cost_centre.id, cost_centre.[name], 0 as [type], concat(id, '_', 0) as combinedId from cost_centre where IsAuto=0 
                                    union all select -1, 'Referal Merchant (group)', 2, '1_2'
                                    union all select -1, 'Source Merchant (group)', 2, '2_2'
                                    union all select -1, 'Business Associate(group)', 2, '3_2'
                                    union all select -1, 'Area Associate (group)', 2, '4_2'
									union all select -1, 'Business Partners (group)',2,'5_2'" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>                            
                            <asp:DropDownList ID="ddlLedger" DataSourceID="SDSledger" Visible="false"  DataTextField="name" DataValueField="combinedId" runat="server" CssClass="form-control select2 py-0" AppendDataBoundItems="true">
                                <asp:ListItem Text="Select Ledger" Value="-1"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:SqlDataSource ID="SDSledger" runat="server" SelectCommand="select [ledger].id, [ledger].[name], 0 as [type], concat(id, '_', 0) as combinedId from [ledger] where IsAuto=0 UNION ALL
	                            SELECT etlg.id, etlg.ledger_group AS [name],etlg.[type], 
                                concat(0, '_', etlg.[type]) AS combinedId FROM entryTypeLedgerGroup etlg ORDER BY [type] ASC"
                                ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                        </div>

                         <div class="col-12 col-lg-3  mb-3 mb-lg-0">
                            <label class="mb-1">Account Value Head</label>
                            <asp:DropDownList ID="ddlvaluehead" DataSourceID="SDSvaluehead" AutoPostBack="true" CssClass="form-control select2 py-0" OnSelectedIndexChanged="ddlvaluehead_SelectedIndexChanged" DataTextField="name"  DataValueField="id" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Order Value Head" Value="-1"></asp:ListItem>                               
                            </asp:DropDownList>
                            <asp:SqlDataSource ID="SDSvaluehead" runat="server" SelectCommand="SELECT id,NAME FROM  finance_calculation_heads WHERE costcentre_enabled=0 ORDER BY NAME" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                             <asp:DropDownList ID="ddlcostcentrevaluehead" Visible="false" DataSourceID="SDScostcentrevaluehead" AutoPostBack="true" CssClass="form-control select2 py-0" OnSelectedIndexChanged="ddlvaluehead_SelectedIndexChanged" DataTextField="name"  DataValueField="id" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Order Value Head" Value="-1"></asp:ListItem>                               
                            </asp:DropDownList>
                            <asp:SqlDataSource ID="SDScostcentrevaluehead" runat="server" SelectCommand="SELECT id,NAME FROM  finance_calculation_heads WHERE costcentre_enabled=1 order by name" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                        </div>
                        <div class="col-12 col-lg-3 mb-3 mb-lg-0 ">
                            <label class="mb-1">Entry Type</label>
                            <asp:DropDownList ID="ddlentrytype" Enabled="true" CssClass="form-control select2 py-0" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Entry Type" Value="-1"></asp:ListItem>
                                <asp:ListItem Text="Debit" Value="1"></asp:ListItem>
                                <asp:ListItem Text="Credit" Value="0"></asp:ListItem>
                            </asp:DropDownList>
                        </div>

                        <div class="col-12 align-items-end d-flex justify-content-lg-end mt-2">                           
                            <asp:LinkButton runat="server" CssClass="btn btn-primary Voucher_entryBTN " ID="lbaddentry" OnClick="lbAddEntry_Click" ValidationGroup="Addentry" Text="Add Entry"></asp:LinkButton>
                        </div>
                    </div> <!--row-->
                    </div>
                    <div class="row row-sm">
                        <div class="col-12">
                            <div id="cpMainContent_cpNMainContent_Panel1" class="right-panel">
                                <div class="table-responsive p-0 mb-3" id="divLedger" style="max-height: 400px;">
                                    <table id="Table1" class="table table-bordered table-head-fixed mb-0">
                                        <thead>
                                            <tr class="TableHeader">
                                                <th class="border-top"  width="125" style="text-transform: capitalize;">Value Head</th>
                                                <th class="border-top" align="right"  width="125" style="text-transform: capitalize;">Ledger/Cost Centre</th>
                                                <th class="border-top" align="right" width="125" style="text-transform: capitalize;">Entry Type</th>
                                                <th class="border-top" align="right"   width="125" style="text-transform: capitalize;">Connection</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <asp:Panel runat="server" Visible="true" CssClass="d-inline-block w-100" ID="ShowDiv">
                                                <tr>
                                                    <td>
                                                        <div class="wireframe"></div>
                                                    </td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="wireframe"></div>
                                                    </td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                </tr>

                                                <tr>
                                                    <td>
                                                        <div class="wireframe"></div>
                                                    </td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="wireframe"></div>
                                                    </td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="wireframe"></div>
                                                    </td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                    <td align="right"></td>
                                                </tr>
                                            </asp:Panel>
                                            <asp:HiddenField ID="hidSelectedLedgerIds" runat="server" />
                                                            <asp:SqlDataSource ID="SDSledgercost" runat="server" SelectCommand="select id,name from ledger where hascostcentre=1 and id in(select value from string_split(@ledgerids, ','))" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"><SelectParameters><asp:ControlParameter ControlID="hidSelectedLedgerIds" PropertyName="Value" Name="ledgerids" /></SelectParameters></asp:SqlDataSource>

                                            <asp:ListView runat="server"   ID="lvfinanceposting" OnItemDataBound="lvfinanceposting_ItemDataBound"  OnItemCommand="lvfinanceposting_ItemCommand" OnItemCanceling="lvfinanceposting_ItemCanceling" DataKeyNames="Name_id"
                                                ItemPlaceholderID="plcItems" OnItemDeleting="lvfinanceposting_ItemDeleting" OnItemEditing="lvfinanceposting_ItemEditing" OnItemUpdating="lvfinanceposting_ItemUpdating">
                                                <LayoutTemplate>
                                                    <asp:PlaceHolder ID="plcItems" runat="server"></asp:PlaceHolder>
                                                </LayoutTemplate>
                                                <ItemTemplate>
                                                    <tr class="collapsed">
                                                        <td>
                                                            <span class="w-100">
                                                                <asp:Label ID="lbCostPurpose" runat="server" Text='<%# Eval("ValueHeadere_name")%>'>   
                                                                </asp:Label>
                                                            </span>


                                                        </td>
                                                        <td align="right">
                                                            <asp:Label ID="lbledger"  runat="server" Text='<%# Eval("Name")%>'></asp:Label> 
                                                                                                                     
                                                        </td>
                                                        <td align="right">
                                                            <asp:Label ID="lbentrytype" runat="server" Text='<%# Eval("Entry_Type_name")%>'>
                                                            </asp:Label>
                                                        </td>
                                                        <td align="right" >  
                                                            <asp:DropDownList ID="ddlLedgercost" DataSourceID="SDSledgercost"  Visible="false"   DataTextField="name" DataValueField="id" runat="server" CssClass="form-control select2 py-0 ht-35-force" AppendDataBoundItems="true">
                                                                <asp:ListItem Text="Select Ledger" Value="-1"></asp:ListItem>
                                                            </asp:DropDownList>
                                                          <asp:RequiredFieldValidator runat="server" ValidationGroup="Submit" ControlToValidate="ddlLedgercost" Display="Dynamic"></asp:RequiredFieldValidator>                                                            
                                                            <asp:PlaceHolder runat="server" Visible='<%# Eval("hasCostCenterLinked").Equals(true) %>'>
                                                             <button type="button" id="btnDetails"  class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#personalModal" data-id='<%# Eval("Name_id") %>' onclick="loadVoucherDetails(<%# Eval("Name_id") %>)">View</button> 
                                                            </asp:PlaceHolder>
                                                            <%--<button id="btnDetails" runat="server" type="button" visible='<%# Convert.ToBoolean(Eval("hasCostcenterLinked")) == true ? true : false %>'></button>--%>
                                                            <asp:LinkButton runat="server" ID="btndelete" CssClass="mr-2" CommandName="Delete" OnClientClick="return confirm('Are you sure you want to delete this record?');"><i class="fa fa-trash-o text-danger ml-2"></i></asp:LinkButton>
                                                            <asp:LinkButton runat="server" ID="btnedit" CssClass="ml-1" CommandName="Edit">Edit</asp:LinkButton>
                                                          </td>
                                                    </tr>
                                                </ItemTemplate>
                                                <EditItemTemplate>
                                                    <tr class="">
                                                        <td colspan="4">
                                                            <div class="row row-sm">
                                                                <div class="col-12 p-0">
                                                                    <div class="form-group">
                                                                        <label class="mb-1">Order Value Head</label>
                                                                        <asp:DropDownList ID="ddlvaluehead_update" DataSourceID="SDSvaluehead"  CssClass="form-control select2 py-0 ht-35-force" SelectedValue='<%# Bind("ValueHeader_ID") %>'  DataTextField="name"  DataValueField="id"  AppendDataBoundItems="true" runat="server">
                                                                            <asp:ListItem Text="Select Order Value Head" Value="-1"></asp:ListItem>                                                                            
                                                                        </asp:DropDownList>
                                                                    </div>
                                                                </div>
                                                                <!--col-->
                                                                <div class="col-12 col-lg-3">
                                                                    <label class="mb-1">Ledger</label>
                                                                    <asp:DropDownList ID="ddlLedger_update" DataSourceID="SDSledger" Visible="true" DataTextField="name"  DataValueField="id" OnDataBound="ddlLedger_update_DataBound" runat="server" CssClass="form-control select2 py-0 ht-35-force" AppendDataBoundItems="true">
                                                                        <asp:ListItem Text="Select Ledger" Value="-1"></asp:ListItem>
                                                                    </asp:DropDownList>
                                                                </div>
                                                                <div class="col-sm-12 p-0">
                                                                    <div class="form-group">
                                                                        <label class="mb-1">Entry Type</label>
                                                                        <asp:DropDownList ID="ddlentrytype_update" CssClass="form-control select2 py-0 ht-35-force" SelectedValue='<%# Bind("Entry_Type_ID") %>'  AppendDataBoundItems="true" runat="server">
                                                                            <asp:ListItem Text="Select Entry Type" Value="-1"></asp:ListItem>
                                                                            <asp:ListItem Text="Debit" Value="1"></asp:ListItem>
                                                                            <asp:ListItem Text="Credit" Value="0"></asp:ListItem>
                                                                        </asp:DropDownList>
                                                                    </div>
                                                                </div>                                                               
                                                                <!--col-->                                                               
                                                                <div class="col-12">
                                                                    <asp:LinkButton runat="server" CssClass="ml-1 mr-1 btn btn-danger py-1" CommandName="Cancel">Cancel</asp:LinkButton>
                                                                    <asp:LinkButton runat="server" CssClass="ml-1 ml-1 btn-primary py-1" CommandName="Update">Save</asp:LinkButton>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </EditItemTemplate>
                                            </asp:ListView>
                                        </tbody>
                                    </table>
                                </div>
                                <!--table-responsive-->
                            </div>
                        </div>
                        <!--col-12-->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="mb-0" id="lblnarration">Voucher Narration</label>
                                <asp:TextBox ID="txtNarration" CssClass="form-control" Style="height: 50px; max-width: 100%;" TextMode="MultiLine" Rows="3" runat="server"></asp:TextBox>
                                <asp:PlaceHolder runat="server">
                                 <p class="m-0 tx-9">Net_Amount_Payable, Customer_Name, Sale_Order_Number, Sale_Order_Date, Store_Name, Mode_Of_Payment, Bank_Reference_ID, Date_of_Cancellation, Customer_Wallet, Customer_Card, Customer_Bank_Account, Sale_Order_Cancellation_Number, Tenant_Invoice_Number, Tenant_Invoice_Date, Grozeo_Invoice_Number, Grozeo_Invoice_Date, Grozeo_Invoice_for_Restaurant_Service_Number, Grozeo_Invoice_for_Restaurant_Service_Date, Delivery_Partner_Name</p>
                                </asp:PlaceHolder>
                            </div>
                        </div>
                        <!--col-12-->
                        <div class="col-12">
                            <label class="mb-0" id="">Description</label>
                            <div class="form-group mb-2 d-flex pb-2">
                                <asp:TextBox ID="txtdescription" CssClass="form-control" Style="height: 50px; max-width: 100%;" TextMode="MultiLine" Rows="3" runat="server"></asp:TextBox>
                            </div>
                        </div>
                         <div class="col-12">
                            <label class="mb-0" id="">Save As Rule</label>
                            <div class="form-group mb-2 d-flex pb-2">
                                <asp:TextBox ID="txtrule" CssClass="form-control" Style="height: 50px; max-width: 100%;" TextMode="MultiLine" Rows="3" runat="server"></asp:TextBox>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-lg-end">
                            <asp:LinkButton  ID="lbnsave"  runat="server" CssClass=" btn btn-primary Voucher_entryBTN px-3" OnClick="lbnsave_Click" ValidationGroup="Addentry" Text="Save Rule"></asp:LinkButton>
                        </div>
                    </div>
                    <!--card-body-->
                </div>
                <!--card-->
            </div>
            <!--col-lg-6-->
        </div>
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
    <script type="text/javascript">     
        $(function () {

            $('.dlt_docmt').click(function () {
                $(this).closest('li').hide();
            });

            $('.objdiv').click(function () {
                $(this).closest('div').addClass('processing_loader');
                setTimeout(function () {
                    $('.objdiv').removeClass('processing_loader');
                }, 7000);
            });


        });
function loadVoucherDetails(id) {
            $('#dvpopupvoucherdetails').html('<div>Loading .. </div>');
            $('#dvpopupvoucherdetails').load('/Finance/Costcentredetails?Name_id=' + id);
        }

    </script>
     <style>
        body {
            overflow-x: hidden;
        }

        .table.table-head-fixed thead tr:nth-child(1) th {
            color:#FFF;
        }

        .table.table-head-fixed tfoot tr:nth-child(1) th {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background-color: #f8f9fa;
            border-top: 0;
            box-shadow: inset 0 1px 0 #dee2e6,inset 0 -1px 0 #dee2e6;
        }
         .table-bordered > thead > tr th, .table-bordered > thead > tr td {
            color: #33603f;
            border-color: #13977f;
        }
         textarea {
          resize: none;
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
</asp:Content>
