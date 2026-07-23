<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="API_connector.aspx.cs" Inherits="RetalineProAgent.API_connector" %>

<%@ Register Src="~/Controls/StoreSettings/ctrlAddressMap.ascx" TagPrefix="uc1" TagName="ctrlAddressMap" %>
<asp:Content ContentPlaceHolderID="head" runat="server">
        <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>
   <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
   <link href="/Content/lib/jt.timepicker/css/jquery.timepicker.css" rel="stylesheet">
       <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
    <script src="/Content/lib/jt.timepicker/js/jquery.timepicker.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Products">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">API/Connectors</li>--%>
    <a href="/Navigations/Others"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">API/Connectors</h6>
        <p class="mb-0">Seamless Integrations</p>
    </div>

</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body">
            <asp:PlaceHolder ID="plcStoreList" runat="server">

          <div class="table-responsive">
                  <asp:Repeater runat="server" ID="rptBranches" OnItemDataBound="rptBranches_ItemDataBound" DataSourceID="ODSStore">
                      <HeaderTemplate><table class="table table-bordered">
                          <thead>
                            <tr><th>Store Name</th><th>Location</th>
                                <%--<th width="86px">Is Online</th>--%>
                                <th width="110px"></th></tr>
                          </thead><tbody>
                      </HeaderTemplate>
                      <ItemTemplate>
                          <tr>
                              <td>
                                  <%# Eval("BranchName") %><br /><small><%= GSTLabel %>: <%# Eval("GSTIN") %></small></td>
                              <td><a href="https://maps.google.com/?q=<%# Eval("Lat") %>,<%# Eval("Lng") %>" target="_blank"><i class="fa fa-map-marker"></i></a>&nbsp;<%# Eval("Address") %>
                                  <br /><small>Bank: <%# Eval("Bank") %></small>, <i class="ion-ios-timer"></i>&nbsp;<small>
                                    <asp:Repeater ID="rptTiming" runat="server"><ItemTemplate><%# Eval("OnTime") %> - <%# Eval("OffTime") %></ItemTemplate><SeparatorTemplate>, </SeparatorTemplate></asp:Repeater>
                                      <asp:Literal ID="ltrNoTiming" Visible="false" runat="server" Text="All time"></asp:Literal>
                                  </small>
                              </td>
                              <%--<td data-bootstrap-switch>--%>
<%--                                  <div class="toggle-wrapper"><div class="toggle toggle-light success" data-toggle-on="<%# Eval("Status").Equals(1) ? "true" : "false" %>"></div></div>--%>
                                 <%-- <asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" style="display: none;" AutoPostBack="true" runat="server" brid='<%# Eval("BranchId") %>' Checked='<%# Eval("Status").Equals(1) %>'/>--%>
                                <%--</td>--%>
                              <td runat="server" visible="true" class="tx-center">
<%--                                  <asp:LinkButton ID="lbtnEditStore" runat="server" brid='<%# Eval("DBBranchid") %>' OnClick="lbtnEditStore_Click" CssClass="btn btn-primary btn-sm"><i class="fa fa-pencil-alt"></i> Edit</asp:LinkButton>--%>
                                  <%--<asp:HyperLink runat="server" ID="lnkEdit" CssClass="btn btn-primary btn-sm" Text="Edit" NavigateUrl='<%# String.Format("/BranchSettings?brid={0}&id={1}", Eval("BranchId"), DBStoreId((int)Eval("BranchId"))) %>'>
                                <i class="fa fa-pencil-alt"></i> Edit
                            </asp:HyperLink>--%>
                                  <%--<a href="javascript:void(0)" onclick="showAPI('<%# Eval("APIKey") %>', '<%# Eval("BranchName") %>')" Class="btn btn-outline-primary btn-sm">API</a>--%>
                                  <a href="javascript:void(0)" onclick="showAPI('<%# Eval("APIKey") %>', '<%# Eval("BranchName") %>', '<%# Eval("BranchId") %>')" class="btn btn-outline-primary btn-sm">API</a>
                                 <%--<asp:LinkButton ID="lblDelivRule" runat="server" brid='<%# Eval("BranchId") %>' OnClick="lblDelivRule_Click" CssClass="btn btn-primary btn-sm"><i class="fa fa-pencil-alt"></i>Set Delivery Rule</asp:LinkButton>--%>
                            </td>
                          </tr>
                      </ItemTemplate>
                      <FooterTemplate></tbody></table></FooterTemplate>
                  </asp:Repeater>
                  <asp:ObjectDataSource ID="ODSStore" runat="server" OnSelecting="ODSStore_Selecting" TypeName="RetalineProAgent.Services.StoreService" SelectMethod="GetStores">
                      <SelectParameters>
                          <asp:Parameter Name="storegroupid" />
                          <asp:Parameter Name="apistoregroupid" />
                          <asp:Parameter Name="all" DefaultValue="true" Type="Boolean" />
                      </SelectParameters>
                  </asp:ObjectDataSource>

              <%--<label id="lblTest" runat="server"></label>--%>

          </div><!-- table-responsive -->                 

    </asp:PlaceHolder>

            <asp:PlaceHolder ID="plcStoreSettings" runat="server">
          <div class="form-layout">            

              <div class="col-lg-3">
                <div class="form-group">
<%--                  <label class="form-control-label"><%= GSTLabel %>: <span class="tx-danger"><%= ConfigurationManager.AppSettings.Get("VATType") == "2" ? "*" : "" %></span></label>--%>
                    <asp:DropDownList ID="selGST" Visible="false" runat="server" DataSourceID="SDSGst" AppendDataBoundItems="true" DataTextField="gstin" DataValueField="id" CssClass="form-control"><asp:ListItem Text="Select GST/VAT" Value=""></asp:ListItem></asp:DropDownList>
                </div>
              </div><!-- col-4 -->
              <div class="col-lg-3">
                <div class="form-group">
                 <%-- <label class="form-control-label">Bank Account: <span class="tx-danger">*</span></label>
                    <a href="/BankAccount-add" onclick="return confirm('The current page will be redirected to a new page. Any modifications made before to saving will be lost. Are you sure?');" style="float: right;">Add Account</a>--%>
                  <div class="input-group">
                    <asp:DropDownList ID="selBankAccount" Visible="false" required DataSourceID="SDSBank" AppendDataBoundItems="true" DataTextField="combinedName" DataValueField="Id" runat="server" CssClass="form-control"><asp:ListItem Text="Select Bank Account" Value=""></asp:ListItem></asp:DropDownList>
                  </div>
                </div>
              </div><!-- col-4 -->          
              <div class="col-lg-3">
                <div class="form-group mg-b-10-force">
                  <%--<label class="form-control-label" >State: <span class="tx-danger" >*</span></label>--%>
                  <asp:DropDownList ID="selState" OnDataBound="selState_DataBound" AutoPostBack="true" Visible="false" runat="server" required DataSourceID="SDSState" DataTextField="name" DataValueField="st_ID"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="true" ><asp:ListItem Text="Select State" Value=""></asp:ListItem></asp:DropDownList>
                </div><asp:HiddenField ID="hidState" runat="server" />
              </div><!-- col-4 -->

              <div class="col-lg-3">
                <div class="form-group">
<%--                  <label class="form-control-label">District: <span class="tx-danger">*</span></label>--%>
                  <asp:DropDownList ID="selDistrict" Visible="false" OnDataBound="selDistrict_DataBound" runat="server" required DataSourceID="SDSDistrict" DataTextField="NAME" DataValueField="id"
                          CssClass="form-control" style="width: 100%;" AppendDataBoundItems="false" ><asp:ListItem Text="Select District" Value=""></asp:ListItem></asp:DropDownList>
                </div><asp:HiddenField ID="hidDistrict" runat="server" />
              </div><!-- col-4 -->
                <uc1:ctrladdressmap runat="server" id="ctrlAddressMap1" Visible="false" />
                <div class="col-lg-12"><small><asp:Literal ID="ltrIFSCSearch" runat="server"></asp:Literal></small></div>
            </div><!-- row -->

            <%--<asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>--%>     

<asp:SqlDataSource ID="SDSGst" OnSelecting="SDSStore_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
        SelectCommand="select gstin, id from GST where tenantid = @storeId"><SelectParameters><asp:Parameter Name="storeId" /></SelectParameters></asp:SqlDataSource>
<asp:SqlDataSource ID="SDSBank" OnSelecting="SDSStore_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
        SelectCommand="select Id, BankName, AccountNumber, AccountName, Branch, AccountNumber + ' - ' + BankName + ' - ' + Branch as combinedName from BankAccount where TenantId = @storeId"><SelectParameters><asp:Parameter Name="storeId" /></SelectParameters></asp:SqlDataSource>


<asp:SqlDataSource ID="SDSState" runat="server" SelectCommand="SELECT st_ID, st_name AS name FROM finascop_state" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
<asp:SqlDataSource ID="SDSDistrict" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
    SelectCommand="SELECT d.dst_Id AS id, d.dst_Name AS NAME, d.st_Id, s.st_ID, s.st_name AS NAME FROM finascop_district d INNER JOIN finascop_state s ON d.st_Id = s.st_ID WHERE d.st_Id = @st_ID">
        <SelectParameters><asp:ControlParameter ControlID="selState" Name="st_ID" Type="Int32" /></SelectParameters></asp:SqlDataSource>


    </asp:PlaceHolder>
        </div><!-- card-body -->
    </div><!-- card -->

    

    
<div class="modal fade" id="modal-branch-api">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
                <div class="modaltitle d-flex w-100 justify-content-between">
                    <h5 class="modal-title">API - Inventory Upload&nbsp;-&nbsp;
                    <label id="lblApiBranchName"></label></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                </div>
                <small>If you have an API submit facility in place then you can use the following API information to submit your inventory for live inventory update. <br />The ERP id (or barcode) should match with the same in the master data.</small><br /><br />
                  <div class="form-group">
                      <div class="col-md-12">
                <small>Url: <b><%= String.Format("{0}api/back-office/branch_inventory", ConfigurationManager.AppSettings.Get("api.url")) %></b></small>
              <br>
                    <small>Header: <br />
                        Authorization:&nbsp;<b><label id="lblApiKey">[API Key]</label></b>
                        <br />
                        Content-Type:&nbsp;<b>application/json</b>
                    </small>
              <br>
              <small>Body (e.g): <br />
                  <b>
                      &nbsp;{<br />
                        &nbsp;&nbsp;"data": [<br />
                          &nbsp;&nbsp;&nbsp;{<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"Qty": [Number],<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"MRP": [Number],<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"selling_price": [Number],<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"erpId": "[String]"<br />
                          &nbsp;&nbsp;&nbsp;}, <br />
                          &nbsp;&nbsp;&nbsp;{<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"Qty": [Number],<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"MRP": [Number],<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"selling_price": [Number],<br />
                            &nbsp;&nbsp;&nbsp;&nbsp;"erpId": "[String]"<br />
                          &nbsp;&nbsp;&nbsp;},<br />
                          &nbsp;&nbsp;&nbsp;....<br />  
                        &nbsp;&nbsp;]<br />
                      &nbsp;}<br />
                  </b>

<br>
              </small>
                
<br>
              </div>
                  </div>                                    
            </div>            
          </div>
        </div>
        <!-- /.modal-dialog -->
      </div>

    <script type="text/javascript">
        $('.toggle').toggles(
            {
                //on: true,
              height: 26
            },
            //checkbox:
        );
        $('.toggle').on('toggle', function (e, active) {
            $(this).closest('td').find('input[type=checkbox]').trigger('click');
            $(this).addClass('processing_loader');
            //if (active) {
            //    console.log('Toggle is now ON!');
            //} else {
            //    console.log('Toggle is now OFF!');
            //}
        });

        function showAPI(strkey, branch) {
            $('#lblApiKey').text(strkey);
            $('#lblApiBranchName').text(branch);
            $('#modal-branch-api').modal('show');
        }

        //$("input[data-bootstrap-switch], td[data-bootstrap-switch] input[type=checkbox]").each(function () {
        //    $(this).bootstrapSwitch('state', $(this).prop('checked'));
        //});

        //$('td[data-bootstrap-switch] input[type=checkbox]').on('switchChange.bootstrapSwitch', function (e, state) {
        //    $(this).prop('checked', !state);
        //    $(this).trigger('click');
        //});
      function selectstate(stateval) {
      var optionval = $('#<%= selState.ClientID %>').find("option:contains('" + stateval + "')").val();
     if (optionval && optionval != '') {
         $("#<%= selState.ClientID %>").val(optionval);
         $('#<%= selState.ClientID %>').change();
     }
 }
    </script>

    <script type="text/javascript">
        function showAPI(apiKey, branchName, id) {
            var url = '<%= ResolveUrl("~/Tenant/BulkImportAPI") %>' + '?id=' + id + '&branchName=' + encodeURIComponent(branchName) + '&apiKey=' + apiKey;
            window.location.href = url;
        }
    </script>
</asp:Content>


