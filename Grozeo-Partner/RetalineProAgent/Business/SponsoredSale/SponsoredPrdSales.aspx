<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="SponsoredPrdSales.aspx.cs" Inherits="RetalineProAgent.Business.SponsoredPrdSales" %>

<div class="card card-table">
    <div class="table-responsive">
            <table class="table table-bordered table-head-fixed" cellspacing="0" border="1">
                <thead class="custom-header">
                    <tr>
                        <th class="text-nowrap column">Order ID</th>
                        <th class="text-nowrap column">Order Date</th>
                        <th class="text-nowrap column">Order Method</th>
                        <th class="text-nowrap column">Customer</th>
                        <th class="text-nowrap column">Branch</th>
                        <th class="text-nowrap column">Total</th>
                        <th class="text-nowrap column">Status</th>
                        <th class="text-nowrap column">Payment Mode</th>
                        <th class="text-nowrap">Item Count</th>
                    </tr>
                </thead>
                <asp:Repeater ID="rptDetails" runat="server" DataSourceID="SDSListDetails">
                    <itemtemplate>
                        <tbody>
                            <tr>
                                <td><%# Eval("order_order_id") %></td>
                                <td><%# Eval("createdDate") %></td>
                                <td><%# Eval("order_method") %></td>
                                <td><%# Eval("customerName") %></td>
                                <td><%# Eval("branchName") %></td>
                                <td class="text-end"><%# Eval("total") %></td>
                                <td><%# Eval("order_status") %></td>
                                <td><%#RetalineProAgent.PendingOrders.GetPaymentModeName(Convert.ToInt32(Eval("payment_mode"))) %></td>
                                <td><%# Eval("items_count") %></td>
                            </tr>
                        </tbody>
                    </itemtemplate>
                    <FooterTemplate>
                                        <tr>
                                            <td colspan="5" style="padding: 0.75rem; font-size: 14px; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;">
                                                <asp:Label ID="lblEmptyData" runat="server" Visible='<%# (rptDetails).Items.Count == 0 %>' Text="No sponsored sales available to list." /></td>
                                        </tr>

                                    </FooterTemplate>
                </asp:Repeater>
            </table>
    </div>

    <asp:SqlDataSource runat="server" ID="SDSListDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT co.order_order_id,co.storegroup_id,DATE_FORMAT(co.created_at,'%d %b %Y  %H:%i') AS createdDate,CASE
        WHEN order_method = 1 THEN 'Drive Delivery'  WHEN order_method = 2 THEN 'Customer Collect' WHEN order_method = 3 THEN 'Courier Delivery'
        END AS order_method,(SELECT cust_customer_name FROM retaline_customer WHERE cust_id=order_customer_id) AS customerName,co.total,co.order_branch_id,co.status_id,admin_description AS order_status,co.payment_mode,
        (SELECT COUNT(*) FROM finascop_stock_transfer_order_details roi WHERE roi.fsto_uid = sto.fsto_uid) AS items_count,
        fb.br_name,(SELECT br_name FROM finascop_branch WHERE br_storeGroup = co.storegroup_id LIMIT 1) AS branchName,fb.br_storegroup FROM retaline_customer_order co 
        INNER JOIN finascop_stock_transfer_order sto ON sto.fstr_id = co.order_id
        INNER JOIN finascop_branch fb ON fb.br_ID = co.order_branch_id
        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = co.status_id 
        WHERE co.order_branch_id = @branchId 
        AND co.storegroup_id != @storegroupId 
        AND co.status_id >= 4 AND co.status_id NOT IN (19, 24)"
        OnSelecting="SDSListDetails_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="storegroupId" DefaultValue="0" />
                                <asp:Parameter Name="branchId" DefaultValue="0" />
                            </SelectParameters>
                        </asp:SqlDataSource>
    <style>
        .custom-header {
            background-color: #2a6436;
            font-weight: 700;
            font-size: 12px;
        }

        .table-bordered > thead > tr th, .table-bordered > thead > tr td {
            color: #fff;
            border-color: #13977f;
            background-color: #13977f !important;
        }

        .card-table {
            max-height: 400px;
            overflow-y: auto;
        }

        .text-end {
            text-align: right;
        }

        .column {
            min-width: 150px; 
            width: auto;
        }
    </style>

</div>

