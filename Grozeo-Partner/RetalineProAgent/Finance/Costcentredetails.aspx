<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Costcentredetails.aspx.cs" Inherits="RetalineProAgent.Finance.Costcentredetails" %>

<div class="card card-table">
    <div class="table-responsive">
          <div class="table-responsive">
                            <table class="table table-bordered" cellspacing="0" border="1">
                                <thead>
                                    <tr>
                                        <th>Rule Name</th>
                                        <th>Cost Centre</th>
                                    </tr>
                                </thead>
                                 <asp:Repeater ID="rptEditCostCenter" runat="server">
                                    <ItemTemplate>
                                   <tbody>                                    
                                    <tr>
                                        <td><asp:Literal runat="server" ID="ltrrulename" Text='<%# Eval("RuleName") %>'></asp:Literal></td>
                                        <td><asp:Literal runat="server" ID="ltrcostcentre" Text='<%# Eval("CostCentreName") %>'></asp:Literal></td>
                                    </tr>                                     
                                </tbody>
                                    </ItemTemplate>
                                     </asp:Repeater>                               
                            </table>
                        </div>
    </div>

</div>