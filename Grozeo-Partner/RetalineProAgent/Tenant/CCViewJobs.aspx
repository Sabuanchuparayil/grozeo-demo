<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Cash Collection Jobs" AutoEventWireup="true" CodeBehind="CCViewJobs.aspx.cs" Inherits="RetalineProAgent.CCViewJobs" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" ><a href="/Tenant/ReceiveCash">Receive Cash</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cash Collection Jobs</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Cash Collection Jobs</h6>
        <p class="mb-0">Cash collection jobs</p>
    </div>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body">
               <div class="table-responsive">
                   <%--<asp:HiddenField ID="hidFilterType" runat="server" />--%>
                                <asp:GridView AutoGenerateColumns="false" ID="gvCCJobs" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="false" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="false" PageSize="10" OnDataBound="gvCCJobs_DataBound" DataSourceID="SDSCCJobs">
                                    <Columns>
                                        <asp:TemplateField HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f">
                                            <ItemTemplate>
                                                <asp:CheckBox AutoPostBack="true" OnCheckedChanged="ckCCJobs_CheckedChanged"
                                                    ID="ckCCJobs" quor_id='<%# Eval("quor_id") %>' data-amount='<%# Eval("quor_AmountCollectible") %>' runat="server" />
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Order No" DataField="quor_RefNo" SortExpression="quor_RefNo" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f"/>
                                        <asp:BoundField HeaderText="Type" DataField="quor_TypeName" SortExpression="quor_TypeName" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f"/>
                                        <asp:BoundField HeaderText="Status" DataField="dls_DelStatus" SortExpression="dls_DelStatus" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f"/>
                                        <asp:BoundField HeaderText="Customer" DataField="quor_DeliveryName" SortExpression="quor_DeliveryName" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f"/>
                                        <asp:BoundField HeaderText="Customer Contact" DataField="quor_DeliveryPhone" SortExpression="quor_DeliveryPhone" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f"/>
                                        <asp:BoundField HeaderText="Location" DataField="dLocation" SortExpression="dLocation" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f"/>
                                        <%--<asp:BoundField HeaderText="Amount" DataField="quor_AmountCollectible" SortExpression="quor_AmountCollectible" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-HorizontalAlign="Right" ItemStyle-BackColor="White"/>--%>
                                        <asp:TemplateField HeaderText="Amount" HeaderStyle-BackColor="#13977f" HeaderStyle-ForeColor="#FFFFFF" HeaderStyle-BorderColor="#13977f" ItemStyle-HorizontalAlign="Right" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <asp:Literal ID="ltrAmount" runat="server" Text='<%# Eval("quor_AmountCollectible") %>'></asp:Literal>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                       
                                    </Columns>
                                    
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No jobs available</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSCCJobs" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT quor_id,dls_DelStatus,quor_RefNo,quor_DeliveryName,quor_DeliveryPhone,CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = quor_Deliverybr_id) as dLocation,quor_AmountCollectible FROM qugeo_order  
                                    INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status INNER JOIN finascop_stock_transfer_order ON quor_TransferOrder_id = fsto_id AND fsto_ordertype = 1
                INNER JOIN retaline_customer_order ON order_id = fstr_id AND payment_mode IN (1,4,7) WHERE  quor_AmountCollectible> 0 AND quor_TransferOrder_Type = 1 AND quor_Status = 38 and quor_DeliveryDriverId = @drivrId "
        OnSelecting="SDSCCJobs_Selecting">
        <SelectParameters>
            <asp:Parameter Name="storegroup" />
             <asp:QueryStringParameter Name="drivrId" QueryStringField="d_ID" />
            <%--<asp:ControlParameter ControlID="selBranches" PropertyName="Text" Name="branchId" />--%>
            <%--<asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
            <asp:ControlParameter ControlID="txtSearch" Name="orderid" ConvertEmptyStringToNull="false" />--%>
        </SelectParameters>
    </asp:SqlDataSource>
                   <asp:HiddenField ID="hidSelectedJobs" runat="server" />
                   <asp:Label ID="lblTotalAmount" runat="server" style="display: none;"></asp:Label>
                   <asp:Label ID="lblDriverName" runat="server" style="display: none;"></asp:Label>
               </div>
            <div class="float-right">
            
            
            
            </div>
    
      </div>
        <div class="card-footer d-flex flex-wrap justify-content-lg-between">
            <div class="col-12 p-0 d-flex justify-content-center justify-content-lg-start align-items-end flex-wrap flex-md-nowraps">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <label class="form-control-label mr-2">Cash to be collected</label>
                    <asp:TextBox runat="server" ID="txtCashInHand" CssClass="form-control mr-2" Enabled="false" Style="width: 120px;" Visible="false"></asp:TextBox>
                </div>
                <div class="d-flex">
                    <asp:TextBox runat="server" ID="txtDeliDate" CssClass="form-control" placeholder="Date" TextMode="Date" Style="width: 150px;" Visible="false"></asp:TextBox>
                    <asp:Button runat="server" ID="btnDeliverOrders" CssClass="btn btn-primary btn-inline-block mx-2" Text="Cash Received" OnClick="ccDeliverOrders" Visible="false" OnClientClick="return confirmForCCJobs();" />
                    <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />
                </div>
                
            </div>
            </div><!-- card-footer -->
  </div>

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
            var gridView = document.getElementById('<%= gvCCJobs.ClientID %>');
            var checkboxes = gridView.getElementsByTagName("input");
            var totalAmount = parseFloat(document.getElementById('<%= lblTotalAmount.ClientID %>').innerText) || 0;
            var selectedDeliveries = 0;
            var userName = document.getElementById('<%= lblDriverName.ClientID %>').innerHTML.trim();

            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type === "checkbox" && checkboxes[i].id.indexOf("ckCCJobs") !== -1) {
                    // Check if the current checkbox is checked
                    if (checkboxes[i].checked) {
                        // Accumulate the total amount from each checked checkbox
                        totalAmount = totalAmount;

                        // Increment the count of selected deliveries
                        selectedDeliveries++;
                    }
                }
            }

            // Check if any checkbox is checked
            if (selectedDeliveries > 0) {

                var additionalMessage = 'Are you sure that you received '  + totalAmount.toFixed(2) +  '.';
                var isAdditionalConfirmed = confirm(additionalMessage);

                if (isAdditionalConfirmed)
                {
                    var message = 'Total Rs. ' + totalAmount.toFixed(2) + ' from ' + selectedDeliveries + ' deliveries now marked as received by ' + userName + '.';
                    var isConfirmed = confirm(message);

                    // Return false if the user clicks Cancel
                    if (!isConfirmed) {
                        return false;
                    }

                }
                else {
                    // Return false if the user clicks Cancel on the first confirmation dialog
                    return false;
                }
            }

            // Return true if no checkbox is checked or the user clicks OK for all checkboxes
            return true;
        }
    </script>
    </asp:Content>