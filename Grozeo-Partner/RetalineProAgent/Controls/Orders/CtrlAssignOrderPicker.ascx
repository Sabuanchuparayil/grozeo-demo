<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="CtrlAssignOrderPicker.ascx.cs" Inherits="RetalineProAgent.Controls.Orders.CtrlAssignOrderPicker" %>
<div id="modalAssignorderpicker" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center w-100 modal-dialog-scrollable" role="document">
        <div class="modal-content bd-0 tx-14">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="card">
                <div class="card-header shadow_top">
                </div>
                <div class="card-body p-3 pt-0">
                    <div class="table-responsive">
                        <asp:GridView AutoGenerateColumns="false" ID="gvOrderPicker" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataSourceID="SDSOrderPickers">
                            <Columns>
                                <asp:BoundField HeaderText="Name" DataField="name" SortExpression="name" />
                                <asp:BoundField HeaderText="Phone" DataField="phone" SortExpression="phone" />
                                <asp:BoundField HeaderText="Status" DataField="liveStatus" SortExpression="liveStatus" />
                                <asp:TemplateField>
                                    <ItemTemplate>
                                        <asp:Button runat="server" ID="btnAdd" Enabled='<%# (Convert.ToInt32(Eval("is_offline")) == 1 ? false : true) %>' orderpickerid='<%# Eval("id") %>' branchid='<%# Eval("branch_id") %>' OnClick="btnAdd_Click" CssClass="btn btn-primary float-right" Text="Assign" />&nbsp;
                                    </ItemTemplate>
                                </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No order picker available. Please add order picker for your store</h6>
                                </div>
                            </EmptyDataTemplate>
                            <PagerStyle CssClass="cssPager" HorizontalAlign="Center" />
                            <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                        </asp:GridView>
                        <asp:HiddenField runat="server" ID="hdnfstoid" />
                        <asp:HiddenField runat="server" ID="hdnorderorderid" />
                        <asp:HiddenField runat="server" ID="hdntoid" />
                        <asp:HiddenField runat="server" ID="hdnorderid" />
                        <asp:SqlDataSource runat="server" ID="SDSOrderPickers" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT boy.id,boy.name,boy.has_open_orders,boy.phone,boy.is_offline,IF(boy.is_offline = 1,'Offline','Online') AS liveStatus, 
                    boy.branch_id FROM retaline_godown_boy boy INNER JOIN finascop_branch b ON b.br_ID=boy.branch_id
                    WHERE b.br_storeGroup = @storegroupid AND boy.status=1 AND branch_id = 
                    (SELECT order_branch_id FROM retaline_customer_order WHERE order_id =@orderid  LIMIT 1) ORDER BY is_offline=1"
                            OnSelecting="SDSOrderPickers_Selecting">
                            <SelectParameters>
                                <asp:ControlParameter ControlID="hdnorderid" Name="orderid" DefaultValue="0" />
                                <asp:Parameter Name="storegroupid" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    @media (min-width: 992px) {
            #modalAssignorderpicker .modal-dialog{
                max-width: 900px;
            }
          }
</style>