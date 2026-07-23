<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlpendingaction.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlpendingaction" %>
<div id="modalStoresetup" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Complete your Store Setup</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered mg-b-0 tx-13" id="tblPendingjobs">
                        <thead>
                            <tr>
                                <th width="85%">Pending Actions</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                         <asp:Repeater ID="rptPendingActions" runat="server">
                             <ItemTemplate>
                                <tr class="<%# Eval("Name") %>">
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <i class="fa <%#GetContent(Eval("Name").ToString(), 1) %> mr-2"
                                            style="font-size: 20px; width: 20px; height: 20px; text-align: center;"
                                            aria-hidden="true"></i>
                                        <p class="m-0" style="line-height: 100%;"><%# Eval("Description") %></p>
                                    </div>
                                </td>
                                <td class="align-middle"><a href="<%# GetContent(Eval("Name").ToString(), 2) %>"
                                    class="text-uppercase font-weight-bold"><%# GetContent(Eval("Name").ToString(), 3) %></a></td>
                            </tr>
                             </ItemTemplate>
                         </asp:Repeater>                                                      
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- modal-dialog -->
</div>
<!-- modal -->
