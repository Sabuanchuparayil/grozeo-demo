<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="SourcedPrds.aspx.cs" Inherits="RetalineProAgent.Business.SourcedPrds" %>

<div class="card card-table">
    <div class="table-responsive">
            <table class="table table-bordered table-head-fixed" cellspacing="0" border="1">
                <thead class="custom-header">
                    <tr>
                        <th class="text-nowrap column">Product</th>
                        <th>MRP</th>
                        <th class="text-nowrap column">Selling Price</th>
                        <th class="text-nowrap column">Discount Selling Price</th>
                        <th>Margin</th>
                    </tr>
                </thead>
                <asp:Repeater ID="rptDetails" runat="server" DataSourceID="SDSListDetails">
                    <itemtemplate>
                        <tbody>
                            <tr>
                                <td><%# Eval("stit_SKU") %></td>
                                <td class="text-end"><%# Eval("mrp") %></td>
                                <td class="text-end"><%# Eval("selling_price") %></td>
                                <td class="text-end"><%# Eval("discount_selling_price") %></td>
                                <td class="text-end"><%# Eval("grozeo_margin") %></td>
                            </tr>
                        </tbody>
                    </itemtemplate>
                    <FooterTemplate>
                                        <tr>
                                            <td colspan="5" style="padding: 0.75rem; font-size: 14px; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;">
                                                <asp:Label ID="lblEmptyData" runat="server" Visible='<%# (rptDetails).Items.Count == 0 %>' Text="No sourced products are available to list." /></td>
                                        </tr>

                                    </FooterTemplate>
                </asp:Repeater>
            </table>
    </div>

    <asp:SqlDataSource runat="server" ID="SDSListDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT sit.stit_SKU,sbi.mrp,sbi.selling_price,sbi.grozeo_margin,sbi.discount_selling_price FROM finascop_stock_branch_inventory sbi 
INNER JOIN finascop_stock_itemmaster sit ON sit.stit_ID=sbi.stit_id
INNER JOIN finascop_branch b ON sbi.branch_id = b.br_ID AND br_storegroup = @storegroupId
WHERE discount_selling_price > 0"
        OnSelecting="SDSListDetails_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="storegroupId" DefaultValue="0" />
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

