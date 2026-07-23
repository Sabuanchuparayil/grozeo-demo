<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="CostAllocation.aspx.cs" Inherits="RetalineProAgent.Finance.CostAllocation" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
  <a href="/Finance/costallocationrules"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
  <li class="breadcrumb-item active" aria-current="page">Cost Allocation Rules</li>
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
    <h6 class="slim-pagetitle">Cost Allocation Rules</h6>
    <p class="mb-0">You can see Cost Allocation Rules here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

    <div class="row row-sm">
        <div class="col-12 ">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row row-sm">
                        <div class="col-6 ">
                            <div class="form-group">
                                <label class="mb-1">Finance Function</label>
                                <asp:DropDownList ID="ddleventmaster" DataSourceID="SDSeventmaster" OnSelectedIndexChanged="ddleventmaster_SelectedIndexChanged" CssClass="form-control select2 py-0" DataTextField="name" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                    <asp:ListItem Text="Select Finance Posting Function" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ID="SDSeventmaster" runat="server" SelectCommand="SELECT id,NAME FROM finance_event_master" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                            </div>
                        </div>
                        <div class="col-6 ">
                            <div class="form-group">
                                <label class="mb-0 w-100 mb-1">Store Type</label>
                                <asp:DropDownList ID="ddlsalestype" CssClass="form-control select2 py-0 " OnSelectedIndexChanged="ddlsalestype_SelectedIndexChanged" AppendDataBoundItems="true" runat="server">
                                    <asp:ListItem Text="Select Store Type" Value="-1"></asp:ListItem>
                                    <asp:ListItem Text="Grozeo" Value="0"></asp:ListItem>
                                    <asp:ListItem Text="Tenant" Value="1"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:Label ID="lblentry" runat="server"></asp:Label>
                                <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Sales Type" ValidationGroup="Addentry" ControlToValidate="ddlsalestype" Display="Dynamic"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                    </div>
                    <!--row-->
                    <div class="row row-sm">
                        <div class="col-12 col-lg-3">
                            <label class="mb-0 w-100">Payment Type</label>
                            <asp:DropDownList ID="ddlpaymentmod" CssClass="form-control select2 py-0 " OnSelectedIndexChanged="ddlpaymentmod_SelectedIndexChanged" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Payment Type" Value="-1"></asp:ListItem>
                                <asp:ListItem Text="Prepaid" Value="0"></asp:ListItem>
                                <asp:ListItem Text="Pay on Delivery" Value="1"></asp:ListItem>
                                <asp:ListItem Text="Not Applicable" Value="2"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:Label ID="Label1" runat="server"></asp:Label>
                            <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Payment Type" ValidationGroup="Addentry" ControlToValidate="ddlpaymentmod" Display="Dynamic"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label class="mb-0 w-100">Delivery Type</label>
                            <asp:DropDownList ID="ddldeliverytype" CssClass="form-control select2 py-0 " OnSelectedIndexChanged="ddldeliverytype_SelectedIndexChanged" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Delivery Type" Value="-1"></asp:ListItem>
                                <asp:ListItem Text="Courier" Value="0"></asp:ListItem>
                                <asp:ListItem Text="Direct" Value="1"></asp:ListItem>
                                <asp:ListItem Text="Not Applicable" Value="2"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:Label ID="Label4" runat="server"></asp:Label>
                            <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Delivery Type" ValidationGroup="Addentry" ControlToValidate="ddlsalestype" Display="Dynamic"></asp:RequiredFieldValidator>
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="mb-0 w-100">Associate Type</label>
                            <asp:DropDownList ID="ddlAreaType" DataSourceID="SDSareatype" DataTextField="name" DataValueField="id" CssClass="form-control select2 py-0 " OnSelectedIndexChanged="ddlAreaType_SelectedIndexChanged" AppendDataBoundItems="true" runat="server">
                                <asp:ListItem Text="Select Associate Type" Value="-1"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:SqlDataSource ID="SDSareatype" runat="server" SelectCommand="SELECT id, NAME FROM finance_area_type UNION ALL SELECT 6, 'Not Applicable'"
                                ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                            <asp:Label ID="Label5" runat="server"></asp:Label>
                            <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Area Type" ValidationGroup="Addentry" ControlToValidate="ddlAreaType" Display="Dynamic"></asp:RequiredFieldValidator>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label class="mb-0 w-100">Account Value Head</label>
                            <asp:DropDownList ID="ddlitemvaluehead" DataSourceID="SDSledger" DataTextField="column_name" DataValueField="id" runat="server" CssClass="form-control select2 py-0" AppendDataBoundItems="true">
                                <asp:ListItem Text="Select Order Value Head" Value="-1"></asp:ListItem>
                            </asp:DropDownList>
                            <asp:SqlDataSource ID="SDSledger" runat="server" SelectCommand="SELECT  id,`column_name` FROM `finance_calculation_heads` WHERE costcentre_enabled=0  order by column_name"
                                ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                            <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Revenue Type" ValidationGroup="Addentry" ControlToValidate="ddlAreaType" Display="Dynamic"></asp:RequiredFieldValidator>
                        </div>
                    </div>
                    <!--row-->
                    <div class="px-3 py-2 border mt-3">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-3 mb-2 mb-lg-0">
                                <label class="mb-1">Cost Centre</label>
                                <asp:DropDownList ID="ddlcostcentre" DataSourceID="SDScostcentre" CssClass="form-control" DataTextField="name"
                                    DataValueField="combinedId" AppendDataBoundItems="true" runat="server">
                                    <asp:ListItem Text="Select Cost Centre" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please Select Cost Centre" ValidationGroup="ddlvaluehead" ControlToValidate="ddlcostcentre" Display="Dynamic"></asp:RequiredFieldValidator>
                                <asp:SqlDataSource ID="SDScostcentre" runat="server" SelectCommand="
                          select cost_centre.id, cost_centre.[name], 0 as [type], concat(id, '_', 0) as combinedId from cost_centre where IsAuto=0 
                                    union all select -1, 'Referal Merchant (group)', 1, '1_1'
                                    union all select -1, 'Source Merchant (group)', 1, '2_1'
                                    union all select -1, 'Business Associate(group)', 1, '3_1'
                                    union all select -1, 'Area Associate (group)', 1, '4_1'
									union all select -1, 'Grozeo Logistics Partners (group)',1,'5_1'
                                    union all select -1, 'Business Partners (group)',1,'6_1'
                                     order by [type] asc, [name]"
                                    ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                            </div>
                            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                                <label class="mb-1">Account Value Head</label>
                                <asp:DropDownList ID="ddlvaluehead" DataSourceID="SDSvaluehead" CssClass="form-control" AutoPostBack="true" DataTextField="name" DataValueField="id" AppendDataBoundItems="true" OnSelectedIndexChanged="ddlvaluehead_SelectedIndexChanged" runat="server">
                                    <asp:ListItem Text="Select Order Value Head" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ID="SDSvaluehead" runat="server" SelectCommand="SELECT id,NAME FROM  finance_calculation_heads WHERE costcentre_enabled=1 ORDER BY NAME" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                                <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please Select Order Value Head" ValidationGroup="Addentry" ControlToValidate="ddlvaluehead" Display="Dynamic"></asp:RequiredFieldValidator>
                            </div>
                            <%--<div class="col-12 col-lg-4">
                             <label class="mb-1"> Cost Centre</label>
                                            <asp:DropDownList ID="ddlcostcentre" DataSourceID="SDScostcentre" CssClass="form-control" DataTextField="name"
                                                DataValueField="id" AppendDataBoundItems="true" runat="server">
                                                <asp:ListItem Text="Select Cost Centre" Value="-1"></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select entry type" ValidationGroup="Addentry" ControlToValidate="ddlcostcentre" Display="Dynamic"></asp:RequiredFieldValidator>
                                            <asp:SqlDataSource ID="SDScostcentre" runat="server" SelectCommand="Select id,name from cost_centre" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                        </div>--%>
                            <div class="col-12 col-lg-3 mb-2 mb-lg-0">
                                <label class="mb-1">Allocation(%)</label>
                                <input type="text" style="display: none">
                                <input type="password" style="display: none">
                                <asp:TextBox ID="txtAllocation" CssClass="form-control v_active_two" runat="server" autocomplete="off" onchange="updateCreditField();"></asp:TextBox>
                                <asp:RequiredFieldValidator runat="server" ValidationGroup="Addentry" ControlToValidate="txtAllocation" ErrorMessage="Allocation is required" Display="Dynamic"></asp:RequiredFieldValidator>
                            </div>
                            <div class="col-12 col-lg-2 d-flex align-items-lg-end">
                                <asp:LinkButton ID="btnaddentry" runat="server" CssClass="btn btn-primary Voucher_entryBTN w-100 px-3" OnClick="lbAddEntry_Click" ValidationGroup="Addentry" Text="Add Entry" />
                            </div>
                        </div>
                    </div>

                    <div class="row row-sm">
                        <div class="col-12">
                            <div id="cpMainContent_cpNMainContent_Panel1" class="right-panel">
                                <div class="table-responsive p-0 mb-3" id="divLedger" style="max-height: 400px;">
                                    <table id="Table1" class="table table-bordered table-head-fixed mb-0">
                                        <thead>
                                            <tr class="TableHeader">
                                                <th class="border-top" width="125" style="text-transform: capitalize;">Order Value Head</th>
                                                <th class="border-top" align="right" width="125" style="text-transform: capitalize;">Cost Centre</th>
                                                <th class="border-top" align="right" width="125" style="text-transform: capitalize;">Allocation</th>
                                                <th class="border-top" align="right" width="125" style="text-transform: capitalize;">Action</th>
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
                                            <asp:ListView runat="server" ID="lvcostallocation" DataKeyNames="OrderValueHeadid" OnItemCommand="lvcostallocation_ItemCommand" OnItemUpdating="lvcostallocation_ItemUpdating" OnItemDeleting="lvcostallocation_ItemDeleting"
                                                ItemPlaceholderID="plcItems" OnItemEditing="lvcostallocation_ItemEditing" OnItemCanceling="lvcostallocation_ItemCanceling" OnDataBound="lvcostallocation_DataBound">
                                                <LayoutTemplate>
                                                    <asp:PlaceHolder ID="plcItems" runat="server"></asp:PlaceHolder>
                                                </LayoutTemplate>
                                                <ItemTemplate>
                                                    <tr class="collapsed">
                                                        <td>
                                                            <span class="w-100">
                                                                <asp:Label ID="lbCostPurpose" runat="server" Text='<%# Eval("Ordervaluehead")%>'>   
                                                                </asp:Label>
                                                            </span>
                                                        </td>
                                                        <td align="left">
                                                            <asp:Label ID="lbCostCategory" runat="server" Text='<%# 
                            (Eval("cost_category_id") != null && (int)Eval("cost_category_id") == 1 && !String.IsNullOrEmpty(Request.QueryString["Id"]) ? 
                                                                    GetGroupName((RetalineProAgent.Finance.CostCenterGroupType)Eval("cost_centre_id")) : 
                                                                    Eval("Costcentrename"))%>'>
                                                            </asp:Label>
                                                        </td>
                                                        <td align="right">
                                                            <asp:Label ID="lbCostCentre" runat="server" Text='<%# Eval("Allocation")%>'>
                                                            </asp:Label>
                                                        </td>
                                                        <td align="right">
                                                            <asp:Label ID="Label2" runat="server">
                                                                 <asp:LinkButton runat="server" CommandName="Delete" OnClientClick="return confirm('Are you sure you want to delete this record?');"><i class="fa fa-trash-o text-danger ml-2"></i></asp:LinkButton>/
                                                             <asp:LinkButton runat="server" CssClass="ml-1" CommandName="Edit">Edit</asp:LinkButton>

                                                            </asp:Label>
                                                        </td>
                                                    </tr>
                                                </ItemTemplate>
                                                <EditItemTemplate>
                                                    <tr class="">
                                                        <td colspan="4">
                                                            <div class="row row-sm">
                                                                <div class="col-12 col-lg-4">
                                                                    <div class="form-group mb-2 mb-lg-0">
                                                                        <label class="mb-1">Order Value Head</label>
                                                                        <asp:DropDownList ID="ddlvaluehead_update" DataSourceID="SDSvaluehead" SelectedValue='<%# Bind("OrderValueHeadid") %>' CssClass="form-control" AutoPostBack="true" DataTextField="name" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                                                            <asp:ListItem Text="Select Order Value Head" Value="-1"></asp:ListItem>
                                                                        </asp:DropDownList>
                                                                    </div>
                                                                </div>
                                                                <!--col-->
                                                                <div class="col-12 col-lg-3">
                                                                    <div class="form-group mb-2 mb-lg-0">
                                                                        <label class="mb-1">Cost Centre </label>
                                                                        <asp:DropDownList ID="ddlcostcentre_update" DataSourceID="SDScostcentre" SelectedValue='<%# Bind("Costcentrename_id") %>' CssClass="form-control" DataTextField="name"
                                                                            DataValueField="id" AppendDataBoundItems="true" runat="server">
                                                                            <asp:ListItem Text="Select Cost Centre" Value="-1"></asp:ListItem>
                                                                        </asp:DropDownList>
                                                                        <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Cost Centre" ValidationGroup="Addentry" ControlToValidate="ddlcostcentre_update" Display="Dynamic"></asp:RequiredFieldValidator>
                                                                    </div>
                                                                </div>
                                                                <!--col-->
                                                                <div class="col-12 col-lg-3">
                                                                    <div class="form-group mb-2 mb-lg-0">
                                                                        <label class="mb-1">Cost Allocation</label>
                                                                        <asp:TextBox ID="txtAllocation_update" CssClass="form-control v_active_two" Text='<%# Bind("Allocation") %>' runat="server" autocomplete="off" onchange="updateCreditField();"></asp:TextBox>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-lg-2 d-flex align-items-lg-end">
                                                                    <asp:LinkButton runat="server" CssClass="ml-1 ml-1 btn btn-primary py-1" CommandName="Update">Save</asp:LinkButton>
                                                                    <asp:LinkButton runat="server" CssClass="ml-1 mr-1 btn btn-secondary py-1" CommandName="Cancel">Cancel</asp:LinkButton>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </EditItemTemplate>
                                            </asp:ListView>
                                            <%--<tr>
                                                <th style="text-align: left;" align="left" id="thtot" runat="server">
                                                    <asp:Literal ID="Literal1" runat="server">Rev_Allocation_RevenueReserve </asp:Literal></th>
                                                <th style="text-align: left;" align="left" id="thDr" runat="server">
                                                    <asp:Literal ID="ltrDrTotal" runat="server">Reserve Fund</asp:Literal></th>

                                                <th style="text-align: right;" align="right" id="th1" runat="server">
                                                    <asp:Literal ID="txttotal" runat="server"></asp:Literal></th>
                                                <th style="text-align: right;" align="right" id="thCr" runat="server">
                                                    <asp:Literal ID="ltrCrTotal" runat="server"></asp:Literal></th>
                                            </tr>--%>
                                        </tbody>
                                        <tfoot>


                                            <tr>
                                                <th></th>
                                                <th style="text-align: left;" align="left">Total</th>
                                                <th style="text-align: right;" align="right">
                                                    <asp:Literal ID="ltrallocationtotal" runat="server"></asp:Literal>
                                                </th>
                                                <th></th>
                                            </tr>

                                        </tfoot>
                                    </table>

                                </div>
                                <!--table-responsive-->
                            </div>
                        </div>
                        <!--col-12-->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="mb-0" id="lblnarration">Description</label>
                                <asp:TextBox ID="txtNarration" CssClass="form-control" Style="height: 50px; max-width: 100%;" TextMode="MultiLine" Rows="3" runat="server"></asp:TextBox>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="mb-0" id="">Save As  Rule </label>
                            <div class="form-group mb-2 d-flex pb-2">
                                <asp:TextBox ID="txtrule" CssClass="form-control" Style="height: 50px; max-width: 100%;" TextMode="MultiLine" Rows="3" runat="server" ValidationGroup="saverule"></asp:TextBox>

                            </div>
                        </div>
                        <!--col-12-->
                        <div class="col-12 d-flex justify-content-lg-end">
                            <asp:LinkButton runat="server" ID="lbnsave" OnClick="btnSave_Click" CssClass="btn btn-primary Voucher_entryBTN px-3" ValidationGroup="Addentry" Text="Save Rule"></asp:LinkButton>
                            <asp:Label runat="server" CssClass="text-danger" ID="txterror"></asp:Label>
                        </div>
                    </div>
                    <!--card-body-->
                </div>
                <!--card-->
            </div>
            <!--col-lg-6-->
        </div>
    </div>
    <!--row-->
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

    </script>
    <style>
        body {
            overflow-x: hidden;
        }

        .table.table-head-fixed thead tr:nth-child(1) th {
            color: #FFF;
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

