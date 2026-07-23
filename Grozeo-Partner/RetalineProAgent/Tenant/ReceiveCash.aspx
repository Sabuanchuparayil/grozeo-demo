<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ReceiveCash.aspx.cs" Inherits="RetalineProAgent.ReceiveCash" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Receive Cash</li>--%>
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Receive Cash</h6>
        <p class="mb-0">Streamlined Cash Management</p>
    </div>
    
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-sm-6 col-md-3 col-lg-4 input-group mg-b-10 mg-sm-b-0">
                    <span class="tx-dark mb-1 w-100">
                        <asp:Literal ID="ltrBranch" runat="server">Store</asp:Literal>
                    </span>
                    <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                    <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                        <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID" runat="server">
                            <asp:ListItem Text="Select Branch" Value="-1"></asp:ListItem>
                        </asp:DropDownList>
                    </asp:PlaceHolder>
                    <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_PyramidLevel = 4 and br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters>
                            <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                            <asp:Parameter Name="branchid" DefaultValue="-1" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                </div>
                <div class="col-sm-6 col-md-3 form-group mb-2 mb-sm-0">
                <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Search by</label>
                    <input type="text" style="display:none" />
                    <input type="password" style="display:none" />
                    <asp:TextBox ID="txtFindCustomer" runat="server" placeholder="Search by name or phone number" CssClass="form-control" autocomplete="nofill"></asp:TextBox>
                  </div>

                <div class="col-sm-6 col-md-3 form-group mb-2 mb-sm-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearch">Driver</label>
                    <asp:DropDownList ID="ddlDrivers" AutoPostBack="true"  OnSelectedIndexChanged="ddlDrivers_SelectedIndexChanged" CssClass="form-control select2" AppendDataBoundItems="true" DataSourceID="SDSDrivers" DataTextField="d_Name" DataValueField="d_ID" runat="server">
                        <asp:ListItem Text="Select Driver" Value="-1"></asp:ListItem>
                    </asp:DropDownList>
                </div>

                <asp:SqlDataSource ID="SDSDrivers" runat="server"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                    SelectCommand="SELECT d_ID,d_Name FROM retaline_customer_order INNER JOIN finascop_branch fb ON order_branch_id = fb.br_ID
                                   INNER JOIN qugeo_order ON order_order_id = quor_RefNo INNER JOIN qugeo_driver ON d_ID = quor_DeliveryDriverId
                                   WHERE payment_mode IN (1, 4, 7) AND quor_Status = 38 AND order_branch_id = @branchid
                                   GROUP BY quor_DeliveryDriverId;"
                    ProviderName="MySql.Data.MySqlClient">
                    <SelectParameters>
                        <asp:ControlParameter Name="branchid" ControlID="selBranches" PropertyName="SelectedValue" Type="Int32" />
                    </SelectParameters>
                </asp:SqlDataSource>
                
            <div class="col-sm-6 col-md-2 d-flex align-items-sm-end">
                <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary mt-2 mt-lg-0 mr-2" runat="server">Search</asp:LinkButton>
              <%--  <asp:Button runat="server" ID="btnreset" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2"  PostBackUrl="~/Tenant/ReceiveCash.aspx" Text="Reset" />--%>

         <asp:LinkButton ID="lbtnReset" CssClass="btn btn-outline-primary mt-2 mt-lg-0" runat="server" OnClick="lbtnReset_Click">Reset</asp:LinkButton>
            </div>
            </div>
        </div><!-- card-header -->
        </div>
       <div class="card-body">
    <div class="table-responsive">
        <asp:GridView 
            AutoGenerateColumns="false" 
            ID="gvDeliverBoy" 
            runat="server" 
            CssClass="table table-bordered gridview_table" 
            GridLines="None" 
            BorderColor="#ECECEC"
            AllowPaging="true" 
            AllowSorting="true" 
            ShowFooter="false" 
            PagerSettings-Visible="true" 
            DataKeyNames="quor_id"
            PageSize="10" 
            OnDataBound="gvDeliveryBoy_DataBound"  OnRowDataBound="gvDeliverBoy_RowDataBound"
            DataSourceID="SDSDeliveryBoy">
            
            <Columns>
                <asp:TemplateField HeaderText="">
                    <HeaderTemplate>
                        <asp:CheckBox ID="chkSelectAll" runat="server" AutoPostBack="true"  />
                    </HeaderTemplate>
                    <ItemTemplate>
                        <asp:CheckBox
                            AutoPostBack="true"
                            ID="ckCCJobs"
                            OnCheckedChanged="ckCCJobs_CheckedChanged"
                            runat="server" />
                    </ItemTemplate>
                </asp:TemplateField>
                <asp:BoundField DataField="quor_id" Visible="false" />
                <asp:BoundField HeaderText="Order ID" DataField="order_order_id" />
                <asp:BoundField HeaderText="Order Date" DataField="order_confirm_date"  DataFormatString="{0:dd MMM yyyy HH:mm}" />
                <asp:BoundField HeaderText="Store" DataField="br_Name" />
                <asp:BoundField HeaderText="Packing Time" DataField="quor_CreatedOn"  DataFormatString="{0:dd MMM yyyy HH:mm}"/>
                <asp:BoundField HeaderText="Pickup Time" DataField="quor_PickedupTime" DataFormatString="{0:dd MMM yyyy HH:mm}" />
               <%-- <asp:BoundField HeaderText="Delivery Mode" DataField="order_method" />--%>
                <asp:BoundField HeaderText="Delivery Agent" DataField="d_Name" />
                <asp:BoundField HeaderText="Delivery Time" DataField="quor_DeliveredTime" DataFormatString="{0:dd MMM yyyy HH:mm}" />
                <asp:TemplateField HeaderText="Amount" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f" ItemStyle-HorizontalAlign="Right" ItemStyle-BackColor="White">
                    <ItemTemplate>
                        <asp:Literal ID="ltrAmount" runat="server" Text='<%# Eval("quor_AmountCollectible") %>'></asp:Literal>
                        <asp:Literal ID="ltrDriverId" runat="server" Text='<%# Eval("d_ID") %>' Visible="false" />
                    </ItemTemplate>
                </asp:TemplateField>

                <%--<asp:TemplateField HeaderStyle-Width="50" HeaderText="Action"></asp:TemplateField>--%>
                   
            </Columns>

            <EmptyDataTemplate>
                <div class="text-center">
                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg" />
                    <h6 class="mb-3">No records available.</h6>
                </div>
            </EmptyDataTemplate>

            <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
            <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
        </asp:GridView>
        
        <asp:SqlDataSource runat="server" ID="SDSDeliveryBoy" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
            OnSelecting="SDSDeliveryBoy_Selecting"
            SelectCommand="SELECT quor_id,order_id,order_order_id,order_confirm_date,br_Name,fb.br_ID,quor_CreatedOn,quor_PickedupTime,d_ID,d_Name,quor_DeliveredTime,quor_AmountCollectible FROM 
                           retaline_customer_order INNER JOIN finascop_branch fb ON order_branch_id = fb.br_ID INNER JOIN qugeo_order ON order_order_id = quor_RefNo INNER JOIN qugeo_driver ON d_ID = quor_DeliveryDriverId
                           WHERE payment_mode IN (1, 4, 7) AND quor_Status = 38 AND order_branch_id = @branchId AND (@DriverId <= 0 OR quor_DeliveryDriverId = @DriverId)
                AND (
                        TRIM(IFNULL(@searchKey, '')) = ''
                        OR order_order_id LIKE CONCAT('%', @searchKey, '%')
                        OR quor_DeliveryName LIKE CONCAT('%', @searchKey, '%')
                        OR quor_AmountCollectible LIKE CONCAT('%', @searchKey, '%')
                    )
                ORDER BY quor_DeliveredTime ASC,quor_PickedupTime ASC;">
            
            <SelectParameters>
                <asp:Parameter Name="storegroup" />
                <asp:Parameter Name="branchId" />
                <asp:Parameter Name="DriverId" Type="Int32" DefaultValue="0" />
               
                <asp:ControlParameter Name="searchKey" ControlID="txtFindCustomer" ConvertEmptyStringToNull="false" />
            </SelectParameters>
        </asp:SqlDataSource>
        <asp:HiddenField ID="hfDriverId" runat="server" />
         <asp:Label ID="lblTotalAmount" runat="server" style="display: none;"></asp:Label>
         <asp:Label ID="lblDriverName" runat="server" style="display: none;"></asp:Label>
    </div>
</div>
          <div class="card-footer d-flex flex-wrap justify-content-lg-between">
          <div class="col-12 p-0 d-flex justify-content-center justify-content-lg-start align-items-end flex-wrap flex-md-nowraps">
              <div class="d-flex align-items-center mb-2 mb-md-0">
                  <label class="form-control-label mr-2">Cash to be collected</label>
                  <asp:TextBox runat="server" ID="txtCashInHand" CssClass="form-control mr-2" Enabled="false" Style="width: 120px;" Visible="true"></asp:TextBox>
              </div>
              <div class="d-flex">
                  <asp:TextBox runat="server" ID="txtDeliDate" CssClass="form-control" placeholder="Date" TextMode="Date" Style="width: 150px;" ></asp:TextBox>
                  <asp:Button runat="server" ID="btnDeliverOrders" CssClass="btn btn-primary btn-inline-block mx-2" Text="Cash Received" OnClick="btnDeliverOrders_Click" OnClientClick="return confirmForCCJobs();" />
             </div>
              
          </div>
          </div><!-- card-footer -->
<%--</div>--%>

    <div id="modaldemo5" class="modal fade">
    <div class="modal-dialog" role="document">
      <div class="modal-content tx-size-sm">
        <div class="modal-body tx-center pd-y-20 pd-x-20">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
          <h4 class="tx-danger mg-b-20"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
          <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
          <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
        </div><!-- modal-body -->
      </div><!-- modal-content -->
    </div><!-- modal-dialog -->
  </div><!-- modal -->
  <!-- MODAL ALERT MESSAGE -->
  <div id="modaldemo4" class="modal fade">
    <div class="modal-dialog" role="document">
      <div class="modal-content tx-size-sm">
        <div class="modal-body tx-center pd-y-20 pd-x-20">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
          <h4 class="tx-success tx-semibold mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
          <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

          <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
        </div><!-- modal-body -->
      </div><!-- modal-content -->
    </div><!-- modal-dialog -->
  </div><!-- modal -->
   

  <script type="text/javascript">
      function confirmForCCJobs() {
          var gridView = document.getElementById('<%=gvDeliverBoy.ClientID %>');
          var checkboxes = gridView.getElementsByTagName("input");
          var totalAmount = parseFloat(document.getElementById('<%= lblTotalAmount.ClientID %>').innerText) || 0;
          var selectedDeliveries = 0;
          var userName = document.getElementById('<%= lblDriverName.ClientID %>').innerHTML.trim();

          for (var i = 0; i < checkboxes.length; i++) {
              if (checkboxes[i].type === "checkbox" && checkboxes[i].id.indexOf("ckCCJobs") !== -1) {

                  if (checkboxes[i].checked) {
                      totalAmount = totalAmount;
                      selectedDeliveries++;
                  }
              }
          }

          // Check if any checkbox is checked
          if (selectedDeliveries > 0) {

              var additionalMessage = 'Are you sure that you received ' + totalAmount.toFixed(2) + '.';
              var isAdditionalConfirmed = confirm(additionalMessage);

              if (isAdditionalConfirmed) {
                  var message = 'Total Rs. ' + totalAmount.toFixed(2) + ' from ' + selectedDeliveries + ' deliveries now marked as received by ' + userName + '.';
                  var isConfirmed = confirm(message);

                  // Return false if the user clicks Cancel
                  if (!isConfirmed) {
                      return false;
                  }

              }
              else
              {
                  return false;
              }
          }
          return true;
      }
  </script>

</asp:Content>

 