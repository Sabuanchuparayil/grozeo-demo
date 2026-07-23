<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="Testcost.aspx.cs" Inherits="RetalineProAgent.Finance.Testcost" %>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="row row-sm">
        <div class="col-12 col-lg-5">
            <div class="card" style="height: calc(100% - 15px);">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="mb-0">Parent Ledger</label>
                                <asp:DropDownList ID="ddlledger" DataSourceID="SDSledger" CssClass="form-control" DataTextField="name"
                                    DataValueField="id" AppendDataBoundItems="true" runat="server">
                                    <asp:ListItem Text="Select Parent Ledger" Value="-1"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:Label ID="Label3" runat="server"></asp:Label>
                                <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select entry type" ValidationGroup="Addentry" ControlToValidate="ddlledger" Display="Dynamic"></asp:RequiredFieldValidator>
                                <asp:SqlDataSource ID="SDSledger" runat="server" SelectCommand="select id,name from ledger where isSystem=0 and hasCostCentre=1" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                            </div>
                        </div>
                        <!--col-->
                        <!--col-->
                        <div class="col-12">
                            <div class="border row m-0 w-100 p-2">
                                <div class="col-12 p-0">
                                    <div class="form-group">
                                        <label class="mb-0 w-100">Cost Purpose</label>
                                        <asp:DropDownList ID="ddlCostpurpose" DataSourceID="SDSCostpurpose" CssClass="form-control" DataTextField="cost_purpose"
                                            DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text="Select Cost Purpose" Value="-1"></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:Label ID="lblentry" runat="server"></asp:Label>
                                        <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Cost Purpose" ValidationGroup="Addentry" ControlToValidate="ddlCostpurpose" Display="Dynamic"></asp:RequiredFieldValidator>
                                        <asp:SqlDataSource ID="SDSCostpurpose" runat="server" SelectCommand="select id,cost_purpose from cost_purpose" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </div>
                                <!--col-->
                                <div class="col-sm-12 p-0">
                                    <div class="form-group">
                                        <label class="mb-0">Cost Category </label>
                                        <asp:DropDownList ID="ddlCostCategory" DataSourceID="SDSCostCategory" CssClass="form-control"  DataTextField="name" AutoPostBack="true"
                                            DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text="Select Cost Category" Value="-1"></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select entry type" ValidationGroup="Addentry" ControlToValidate="ddlCostCategory" Display="Dynamic"></asp:RequiredFieldValidator>
                                        <asp:SqlDataSource ID="SDSCostCategory" runat="server" SelectCommand="select id,name from cost_category" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </div>
                                <div class="col-sm-3 p-0">
                                    <div class="form-group">
                                        <label class="mb-0" id="txtmode">Mode</label>
                                        <asp:DropDownList ID="Ddlmode" CssClass="form-control v_active" runat="server">
                                            <asp:ListItem Enabled="true" Text="Mode" Value="-1"></asp:ListItem>
                                            <asp:ListItem Text="System" Value="1"></asp:ListItem>
                                            <asp:ListItem Text="Mannual" Value="2"></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Mode" ValidationGroup="Addentry" ControlToValidate="Ddlmode" Display="Dynamic"></asp:RequiredFieldValidator>
                                    </div>
                                </div>
                                <div class="col-sm-6 px-0 px-sm-2">
                                    <div class="form-group">
                                        <label class="mb-0" id="txtcostcentre">Cost Centre</label>
                                        <asp:DropDownList ID="ddlCostCentre" DataSourceID="SDSCostCentre" CssClass="form-control" DataTextField="name" AutoPostBack="true"
                                            DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text="Select Cost Centre" Value="-1"></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:Label ID="Label2" runat="server"></asp:Label>
                                        <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Cost Centre" ValidationGroup="Addentry" ControlToValidate="ddlCostCentre" Display="Dynamic"></asp:RequiredFieldValidator>
                                        <asp:SqlDataSource ID="SDSCostCentre" runat="server" SelectCommand="select id,name,cost_category_id from cost_centre" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </div>
                                <!--col-->
                                <div class="col-sm-3 p-0">
                                    <div class="form-group">
                                        <label class="mb-0">Allocation</label>
                                        <input type="text" style="display: none">
                                        <input type="password" style="display: none">
                                        <asp:TextBox ID="txtAllocation" CssClass="form-control v_active_two" runat="server" autocomplete="off" onchange="updateCreditField();"></asp:TextBox>
                                        <asp:RequiredFieldValidator runat="server" ValidationGroup="Addentry" ControlToValidate="txtAllocation" ErrorMessage="Allocation is required" Display="Dynamic"></asp:RequiredFieldValidator>
                                        <asp:Label ID="lblcostcategory" ForeColor="#ff3300" runat="server"></asp:Label>
                                    </div>
                                </div>
                                <div class="col-sm-12 p-0">
                                    <div class="form-group text-right">
                                        <label class="mb-0"></label>
                                        <asp:LinkButton runat="server" ID="cancel" OnClick="cancel_Click"  CssClass="btn btn-danger AddVoucherBTN">Cancel</asp:LinkButton>
                                        <asp:LinkButton runat="server" ID="lbAddEntry" OnClick="lbAddEntry_Click"  CssClass="btn btn-primary AddVoucherBTN" ValidationGroup="Addentry"><i class="fa fa-plus mr-2"></i>Add Entry</asp:LinkButton>
                                        <div class="form-group">
                                          
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 p-0">
                        <div class="form-group text-right">
                              
                        </div>
                        </div>
                    </div>
                    <!--row -->
                </div>
                <!--card-body -->
            </div>
            <!--card-->
        </div>
        <!--col-lg-6-->
              
        <div class="col-12 col-lg-7">
            <div class="card" style="height: calc(100% - 15px);">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div id="cpMainContent_cpNMainContent_Panel1" class="right-panel">
                                <div class="table-responsive p-0 mb-3" id="divLedger" style="max-height: 400px;">
                                    <div id="cpMainContent_cpNMainContent_ShowDiv" class="d-inline-block w-100">
                                    </div>
                                    <table id="Table1" class="table table-bordered table-head-fixed mb-0">
                                        <thead>
                                            <tr class="TableHeader">
                                                <th class="border-top" width="125">Cost Purpose</th>
                                                <th class="border-top" align="right" width="125">Cost Category</th>
                                                <th class="border-top" align="right" width="125">Cost Centre</th>
                                                <th class="border-top" align="right" width="125">Allocation%</th>
                                            </tr>
                                        </thead>
                                        <tbody>                                            
                                            <asp:ListView runat="server" ID="lvcostallocation" DataKeyNames="ledgerId" OnItemCanceling="lvcostallocation_ItemCanceling" 
                                                OnItemDeleting="lvcostallocation_ItemDeleting" ItemPlaceholderID="plcItems"  OnItemEditing="lvcostallocation_ItemEditing">
                                                <LayoutTemplate>
                                                        <asp:PlaceHolder ID="plcItems" runat="server"></asp:PlaceHolder>
                                                    </LayoutTemplate>
                                                 <ItemTemplate>
                                                       <tr class="collapsed" >
                                                           <td>
                                                            <span class="w-100">
                                                                <asp:Label ID="lbCostPurpose" runat="server" Text='<%# Eval("CostPurpose")%>'>   
                                                                </asp:Label>
                                                            </span>
                                                            <asp:LinkButton runat="server" CommandName="Delete" OnClientClick="return confirm('Are you sure you want to delete this record?');"><i class="fa fa-trash-o text-danger ml-2"></i></asp:LinkButton>
                                                            <asp:LinkButton runat="server" CssClass="ml-1 collapsed" CommandName="Edit" data-toggle="collapse" data-target="#collapse0" aria-expanded="false" aria-controls="collapseOne">Edit</asp:LinkButton>

                                                        </td>
                                                             <td align="right">
                                                             <asp:Label ID="lbCostCategory" runat="server" Text='<%# Eval("CostCategory")%>'>
                                                            </asp:Label>
                                                             </td>
                                                            <td align="right">
                                                             <asp:Label ID="lbCostCentre" runat="server" Text='<%# Eval("CostCentre")%>'>
                                                            </asp:Label>
                                                            </td>
                                                           <td align="right">
                                                            <asp:Label ID="lballocations" runat="server" Text='<%# Eval("Allocation")%>'>
                                                            </asp:Label>
                                                           </td>
                                                       </tr>                                                        
                                                     <tr>
                                                         <td colspan="4" class="hiddenRow">
                                                             <div id="collapse0" class="collapse p-3" aria-labelledby="headingOne" data-parent="#accordion" style="">

                                                                 <edititemtemplate>
                                                                     <div class="row row-sm">
                                                                         <div class="col-md-6">
                                                                             <div class="form-group">
                                                                                 <label class="mb-0 w-100">Cost Purpose</label>
                                                                                 <asp:DropDownList ID="ddlCostpurpose" DataSourceID="SDSCostpurpose" SelectedValue='<%# Bind("CostPurposeid") %>' CssClass="form-control" DataTextField="cost_purpose"
                                                                                     DataValueField="id" AppendDataBoundItems="true" runat="server">
                                                                                     <asp:ListItem Text="Select Cost Purpose" Value="-1"></asp:ListItem>
                                                                                 </asp:DropDownList>
                                                                                 <asp:Label ID="lblentry" runat="server"></asp:Label>
                                                                                 <asp:SqlDataSource ID="SDSCostpurpose" runat="server" SelectCommand="select id,cost_purpose from cost_purpose" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                                                             </div>
                                                                         </div>
                                                                         <div class="col-md-6">
                                                                             <div class="form-group">
                                                                                 <label class="mb-0">Cost Category</label>
                                                                                 <asp:DropDownList ID="ddlCostCategory" DataSourceID="SDSCostCategory" SelectedValue='<%# Bind("CostCategoryid") %>' CssClass="form-control" DataTextField="name" AutoPostBack="true"
                                                                                     DataValueField="id" AppendDataBoundItems="true" runat="server">
                                                                                     <asp:ListItem Text="Select Cost Category" Value="-1"></asp:ListItem>
                                                                                 </asp:DropDownList>
                                                                                 <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select entry type" ValidationGroup="Addentry" ControlToValidate="ddlCostCategory" Display="Dynamic"></asp:RequiredFieldValidator>
                                                                                 <asp:SqlDataSource ID="SDSCostCategory" runat="server" SelectCommand="select id,name from cost_category" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                                                             </div>
                                                                         </div>

                                                                         <div class="col-md-6">
                                                                             <div class="form-group">
                                                                                 <label class="mb-0" id="txtcostcentretest">Cost Centre</label>
                                                                                 <asp:DropDownList ID="DropDownList1" DataSourceID="SDSCostCentre" SelectedValue='<%# Bind("CostCentreid") %>' CssClass="form-control" DataTextField="name" AutoPostBack="true"
                                                                                     DataValueField="id" AppendDataBoundItems="true" runat="server">
                                                                                     <asp:ListItem Text="Select Cost Centre" Value="-1"></asp:ListItem>
                                                                                 </asp:DropDownList>
                                                                                 <asp:Label ID="Label1" runat="server"></asp:Label>
                                                                                 <asp:SqlDataSource ID="SqlDataSource1" runat="server" SelectCommand="select id,name,cost_category_id from cost_centre" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                                                             </div>

                                                                         </div>
                                                                         <div class="col-md-6">
                                                                             <div class="form-group">
                                                                                 <label class="mb-0">Allocation %</label>
                                                                                 <asp:TextBox runat="server" CssClass="form-control" Text='<%# Bind("Allocation")%>' ID="TextBox2"></asp:TextBox>
                                                                             </div>
                                                                         </div>
                                                                         <div class="col-12">
                                                                             <asp:LinkButton runat="server" CssClass="ml-1 mr-1 btn btn-danger py-1" CommandName="Cancel">Cancel</asp:LinkButton>
                                                                             <asp:LinkButton runat="server" CssClass="ml-1 ml-1 btn btn-success py-1" CommandName="Update">Save</asp:LinkButton>
                                                                             <%--<a href="javscript:void(0)" class="ml-1  mr-1 text-danger">Cancel</a>
                                                                        <a href="javscript:void(0)" class="ml-1  ml-1">Save</a>--%>
                                                                         </div>
                                                                     </div>
                                                                 </edititemtemplate>


                                                             </div>
                                                         </td>
                                                     </tr>
                                                        
                                                 </ItemTemplate>
                                                  
                                            </asp:ListView>
                                        </tbody>
                                        <tfoot>
                                            <tr id="cpMainContent_cpNMainContent_tot">
                                                <th id="cpMainContent_cpNMainContent_thtot" style="text-align: right;" align="right"></th>
                                                <th id="cpMainContent_cpNMainContent_thDr" style="text-align: right;" align="right"></th>
                                                <th id="cpMainContent_cpNMainContent_thCr" style="text-align: right;" align="right"></th>
                                                <th id="cpMainContent_cpNMainContent_thCrE" style="text-align: right;" align="right"></th>
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
                                <label class="mb-0" id="lblnarration">Narration</label>
                                <%--<textarea name="ctl00$ctl00$cpMainContent$cpNMainContent$txtNarration" rows="3" cols="20" id="txtNarration" class="form-control" style="width:100%;"></textarea>--%>
                                <asp:TextBox ID="txtNarration" CssClass="form-control" Style="height: 150px; max-width: 100%;" TextMode="MultiLine" Rows="3" runat="server"></asp:TextBox>
                            </div>
                        </div>
                        <!--col-12-->
                        <div class="col-12">
                            <label class="mb-0" id="">Save  Rule As</label>
                            <div class="form-group mb-2 d-flex">
                                <asp:TextBox runat="server" CssClass="form-control mr-3" ID="txtsaverule"></asp:TextBox>
                                <%-- <input type="text" class="form-control mr-3" id="">--%>
                                <%-- <input type="submit" name="ctl00$ctl00$cpMainContent$cpNMainContent$btnSave" value="Save" id="cpMainContent_cpNMainContent_btnSave" class="btn btn-success Voucher_entryBTN"> --%>
                                <asp:Button ID="btnSave" runat="server" CssClass="btn btn-success Voucher_entryBTN" OnClick="btnSave_Click" Text="Save" />
                            </div>
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

    <style>
        body {
            overflow-x: hidden;
        }

        .table.table-head-fixed thead tr:nth-child(1) th {
            background-color: #f8f9fa;
        }

        .table.table-head-fixed tfoot tr:nth-child(1) th {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background-color: #f8f9fa;
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
    

</asp:Content>

