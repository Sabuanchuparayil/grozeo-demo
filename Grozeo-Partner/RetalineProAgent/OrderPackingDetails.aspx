<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="View Details" MasterPageFile="~/AgentMaster.Master" CodeBehind="OrderPackingDetails.aspx.cs" Inherits="RetalineProAgent.OrderPackingDetails" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlOrderPackingDetails.ascx" TagPrefix="uc2" TagName="ctrlOrderPackingDetails" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
        <%--<script src="/Content/custom/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="/Content/custom/plugins/toastr/toastr.min.js"></script>
    <script src="/Content/custom/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>--%>
    <script type="text/javascript">

        //var Toast = Swal.mixin({
        //        toast: true,
        //        position: 'top-end',
        //        showConfirmButton: false,
        //        timer: 3000
        //    });

        //function showSuccess(msg) {
        //    $(document).Toasts('create', {
        //        class: 'bg-success',
        //        title: 'Success',
        //        subtitle: 'Store Created',
        //        body: msg
        //    })
        //}

        //function showError(msg) {
        //    $(document).Toasts('create', {
        //        class: 'bg-danger',
        //        title: 'Error ',
        //        subtitle: ' Operation failed ',
        //        body: msg
        //    })
        //}

    </script>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <uc2:ctrlOrderPackingDetails runat="server" id="ctrlOrderPackingDetails1" />
</asp:Content>
