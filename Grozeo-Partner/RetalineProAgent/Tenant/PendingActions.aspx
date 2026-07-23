<%@ Page Language="C#" Async="true" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Partner - Pending Actions" CodeBehind="PendingActions.aspx.cs" Inherits="RetalineProAgent.PendingActions" %>
<%@ Register Src="~/Controls/ctrlLanguages.ascx" TagPrefix="uc1" TagName="ctrlLanguages" %>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
            <h6 class="slim-pagetitle"><%= this.CurrentUser.StoreGroupName %></h6>
    <p class="mb-0">Pending Actions & Pending Jobs</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="row row-sm">
        <div class="col-12 col-lg-6 mb-3 mb-lg-0">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mg-b-0 tx-13" id="tblpendingActions">
                            <thead>
                                <tr>
                                    <th width="85%">Pending Actions</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <asp:Repeater ID="rptPendingActions" runat="server">
                                    <ItemTemplate>
                                        <tr class="<%# Eval("Name") %>"><td class="align-middle"><div class="d-flex align-items-center">
                                            <i class="fa <%# GetContent(Eval("Name").ToString(), 1) %> mr-2 pendicon"></i>
                                            <p class="m-0" style="line-height: 100%;"><%# Eval("Description") %></p></div></td>
                                            <td class="align-middle"><a href="<%# GetContent(Eval("Name").ToString(), 2) %>" class="text-uppercase font-weight-bold"><%# GetContent(Eval("Name").ToString(), 3) %></a></td></tr>
                                    </ItemTemplate>
                                    <FooterTemplate>
                                        <asp:PlaceHolder runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>'>
                                            <tr><td class= "align-middle" align = "center" colspan="2"><img style="max-height: 250px; max-width: 100%;" src="/content/images/no_pending_actions.png"></td></tr>
                                        </asp:PlaceHolder>
                                    </FooterTemplate>
                                </asp:Repeater>

                                <%--<tr>
                                    <td colspan="2" class="processing_loader">Loading..</td>
                                </tr>--%>
                            </tbody>
                        </table>
                    </div>
                    <!-- table-responsive -->
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mg-b-0 tx-13" id="tblPendingjobs">
                            <thead>
                                <tr>
                                    <th width="85%">Pending Jobs</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                 <asp:Repeater ID="rptPendingJobs" runat="server">
                                    <ItemTemplate>
                                        <tr class="<%# Eval("Name") %>"><td class="align-middle"><div class="d-flex align-items-center">
                                            <i class="fa <%# GetContent(Eval("Name").ToString(), 1) %> mr-2 pendicon"></i>
                                            <p class="m-0" style="line-height: 100%;"><%# Eval("Description") %></p></div></td>
                                            <td class="align-middle"><a href="<%# GetContent(Eval("Name").ToString(), 2) %>" class="text-uppercase font-weight-bold"><%# GetContent(Eval("Name").ToString(), 3) %></a></td></tr>
                                    </ItemTemplate>
                                    <FooterTemplate>
                                        <asp:PlaceHolder runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>'>
                                            <tr><td class= "align-middle" align = "center" colspan="2"><img style="max-height: 250px; max-width: 100%;" src="/content/images/no_pending_jobs.png"></td></tr>                                         </asp:PlaceHolder>
                                    </FooterTemplate>
                                </asp:Repeater>

                                <%--<tr>
                                    <td colspan="2" class="processing_loader">Loading..</td>
                                </tr>--%>
                            </tbody>
                        </table>
                    </div>
                    <!-- table-responsive -->
                </div>
            </div>
        </div>
        <!--col-6-->

    </div>

    <uc1:ctrlLanguages runat="server" ID="ctrlLanguages1" />

    <%--<script type="text/javascript">
        $(document).ready(function () {

            onSuccess = function (data) {
                //$('.homeloading').removeClass('processing_loader');
                $('#tblpendingActions,#tblPendingjobs').find('tbody').html('');
                if (data && data.status === 'Success' && data.data && data.data.data) {
                    if (data.data.data.branches)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-sitemap mr-2" style="font-size: 16px; color:#005aff; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Branch is not created</p></div></td><td class="align-middle"><a href="/Tenant/branches" class="text-uppercase font-weight-bold">Add</a></td></tr>');
                    if (data.data.data.bankAccounts)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-university mr-2" style="font-size: 16px; color:red; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Bank account is not added</p></div></td><td class="align-middle"><a href="/Tenant/Store/BankAccount-Add" class="text-uppercase font-weight-bold">Add</a></td></tr>');
                    //if (data.data.data.storesWithoutBank > 0)
                    //    $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-university mr-2" style="font-size: 16px; color:red; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Store is missing bank account</p></div></td><td class="align-middle"><a href="/Tenant/Branches" class="text-uppercase font-weight-bold">Add</a></td></tr>');
                    if (data.data.data.bankLinkedToStore)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i style="font-size: 16px; color:#ffae00; width: 20px; height: 20px; text-align: center;" aria-hidden="true" class="fa-regular fa-money-bill-transfer mr-2"></i><p class="m-0" style="line-height: 100%;">Bank account is not linked to store</p></div></td><td class="align-middle"><a href="/Tenant/Store/BankAccount" class="text-uppercase font-weight-bold">Add</a></td></tr>');
                    //if (data.data.storesWithoutBank > 0)
                    //    $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-briefcase mr-2" style="font-size: 16px; color:#ffae00; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i> <p class="m-0" style="line-height: 100%;">Store is missing bank account</p></div></td><td class="align-middle"><a href="/Tenant/Store/BankAccount" class="text-uppercase font-weight-bold">Create</a></td></tr>');
                    if (data.data.data.bankNoLinkedToStore)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-code-fork mr-2" style="font-size: 20px; color:#ff00ff; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">One or more of the bank account added is not yet assigned to a store</p></div></td><td class="align-middle"><a href="/Tenant/Store/BankAccount" class="text-uppercase font-weight-bold">Manage</a></td></tr>');

                    if (data.data.data.gstscount)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"> <i class="fa fa-calculator mr-2" style="font-size: 16px; color:#02704f; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">No GST/VAT added</p></div></td><td class="align-middle"><a href="/Tenant/store/GST-Add" class="text-uppercase font-weight-bold">Add</a></td></tr>');
                    if (data.data.data.gstNotLinkedToStore)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-share-alt mr-2" style="font-size: 20px; color:#e7d000; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i> <p class="m-0" style="line-height: 100%;">One or more GST/VAT added is not yet assigned to a store</p></div></td><td class="align-middle"><a href="/Tenant/store/gst" class="text-uppercase font-weight-bold">Manage</a></td></tr>');
                    if (data.data.data.totalStores)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"> <i class="fa fa-shopping-cart mr-2" style="font-size: 20px; color:#9a9fab; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">No store is active</p></div></td><td class="align-middle"><a href="/Tenant/branches" class="text-uppercase font-weight-bold">Manage</a></td></tr>');
                    if (data.data.data.gstnNotVerified)
                        $('#tblpendingActions').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"> <i class="fa fa-file-text-o mr-2" style="font-size: 20px; color:#00b6bf; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">One or more GST/VAT added is yet to be verified</p></div></td><td  class="align-middle"><a href="/Tenant/store/gst" class="text-uppercase font-weight-bold">Manage</a></td></tr>');

                    if (data.data.data.orderPickers)
                        $('#tblPendingjobs').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-id-badge mr-2" style="font-size: 20px; color:#619b29; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Order picker is not created</p></div></td><td class="align-middle"><a href="/Tenant/orderpicker" class="text-uppercase font-weight-bold">Create</a></td></tr>');
                    if (data.data.data.orderPickersOnline)
                        $('#tblPendingjobs').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"> <i class="fa fa-puzzle-piece mr-2" style="font-size: 20px; color:#f9ba08; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Order pickers are offline</p></div></td><td class="align-middle"><a href="/Tenant/orderpicker" class="text-uppercase font-weight-bold">Manage</a></td></tr>');
                    if (data.data.data.drivers)
                        $('#tblPendingjobs').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"> <i class="fa fa-user mr-2" style="font-size: 18px; color:#666666; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Driver is not created</p></div></td><td class="align-middle"><a href="/Tenant/DeliveryStaffs" class="text-uppercase font-weight-bold">Create</a></td></tr>');
                    if (data.data.data.products)
                        $('#tblPendingjobs').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-exclamation-circle mr-2" style="font-size: 20px; color:#ff0000; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Products are not added to the store</p></td><td><a href="/Tenant/MyProducts" class="text-uppercase font-weight-bold">Add</a></td></tr>');

                    if (data.data.data.totalStores && data.data.data.totalStores > 0 && data.data.data.onlineStores <= 0)
                        $('#tblPendingjobs').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"> <i class="fa fa-globe mr-2" style="font-size: 16px;color: #ff6f6f;width: 20px;height: 20px;text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">No store is online</p></div></td><td class="align-middle"><a href="/Tenant/StockPrice" class="text-uppercase font-weight-bold">Manage</a></td></tr>');

                    //if (data.data.data.products > 0 && data.data.data.products == data.data.data.outOfStock)
                    //    $('#tblPendingjobs').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"> <i class="fa fa-shopping-basket mr-2" style="font-size: 16px; color:#0037ff; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">All proudcts added are out of stock</p></div></td><td class="align-middle"><a href="/Tenant/StockPrice" class="text-uppercase font-weight-bold">Manage</a></td></tr>');

                    if (data.data.data.emailverified)
                        $('#tblPendingjobs').find('tbody').append('<tr><td class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-envelope mr-2" style="font-size: 16px; color:#b308f9; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Verify Email</p></td><td class="align-middle"><a href="javascript:void(0)" onclick="$(\'#modalverifyemail\').modal(\'show\');" class="text-uppercase font-weight-bold">Verify</a></td></tr>');
                }

                else if (data && data.status !== 'Success') {
                    $('#tblpendingActions').find('tbody').append('<tr><td colspan="2" class="align-middle"><div class="d-flex align-items-center"><i class="fa fa-exclamation-triangle mr-2" style="font-size: 20px; color:#ff0000; width: 20px; height: 20px; text-align: center;" aria-hidden="true"></i><p class="m-0" style="line-height: 100%;">Loading failed due to a technical error..</p></div></td></tr>');
                }
                if ($('#tblpendingActions').find('tbody tr').length < 1) {
                    $('#tblpendingActions thead th:last-child').remove();
                    $('#tblpendingActions').find('tbody').append('<tr><td class= "align-middle" align = "center"><img style="max-height: 250px; max-width: 100%;" src="/content/images/no_pending_actions.png"></td></tr> ');
                } 

                if ($('#tblPendingjobs').find('tbody tr').length < 1) {
                    $('#tblPendingjobs thead th:last-child').remove();
                    $('#tblPendingjobs').find('tbody').append('<tr><td class= "align-middle" align = "center"><img style="max-height: 250px; max-width: 100%;" src="/content/images/no_pending_jobs.png"></td></tr> ');
                }  
            };

            onError = function (data) {
                console.log(data);
            };
            retMaster.ajax.JSONRequest('/api/home/GetPendingTasks/1', 'GET', {}, onSuccess, onError);

        });

    </script>--%>

</asp:Content>