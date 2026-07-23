<%@ Page Language="C#" AutoEventWireup="true" ValidateRequest="false" MaintainScrollPositionOnPostback="true" Title="Select Inventory" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="InventoryMapping.aspx.cs" Inherits="RetalineProAgent.InventoryMapping" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlInventorySetup.ascx" TagPrefix="uc1" TagName="ctrlInventorySetup" %>
<asp:Content runat="server" ContentPlaceHolderID="head">
    <script type="text/javascript">
        function on() {
            document.getElementById("overlay").style.display = "flex";
        }
    </script>
    <script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
    <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
   <link href="/Content/lib/jt.timepicker/css/jquery.timepicker.css" rel="stylesheet">
       <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
    <script src="/Content/lib/jt.timepicker/js/jquery.timepicker.js"></script>
    <%--<script src="/content/lib/medium-editor/js/medium-editor.js"></script>--%>
    <style>
        .spinner {
  height: 60px;
  width: 60px;
  margin: auto;
  display: flex;
  position: absolute;
  -webkit-animation: rotation .6s infinite linear;
  -moz-animation: rotation .6s infinite linear;
  -o-animation: rotation .6s infinite linear;
  animation: rotation .6s infinite linear;
  border-left: 6px solid rgba(0, 174, 239, .15);
  border-right: 6px solid rgba(0, 174, 239, .15);
  border-bottom: 6px solid rgba(0, 174, 239, .15);
  border-top: 6px solid rgba(0, 174, 239, .8);
  border-radius: 100%;
}
        .pagenation .btn-group > span{
                      display: flex;
                    }
                    .pagenation > .text-left{
                      top: -7px;
                      position: relative;
                    }
                    .create_new_product .modal-dialog{
                      max-width: 1110px;
                    }
                    @media (max-width: 1200px) {
                      .create_new_product .modal-dialog{
                        max-width: 90%;
                      }
                    }

@-webkit-keyframes rotation {
  from {
    -webkit-transform: rotate(0deg);
  }
  to {
    -webkit-transform: rotate(359deg);
  }
}

@-moz-keyframes rotation {
  from {
    -moz-transform: rotate(0deg);
  }
  to {
    -moz-transform: rotate(359deg);
  }
}

@-o-keyframes rotation {
  from {
    -o-transform: rotate(0deg);
  }
  to {
    -o-transform: rotate(359deg);
  }
}

@keyframes rotation {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(359deg);
  }
}

#overlay {
  position: absolute;
  display: none;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 2;
  cursor: pointer;
}

    #addbrand {
        z-index: 1052 !important;
    }
    .modal-backdrop.show + .modal-backdrop.show {
        z-index: 1051 !important;
    }
        .addbrandpopup {
  font-weight: normal;
  text-decoration: underline;
  color: #797867;
  cursor: pointer;
      float: right;
}
    </style>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <asp:PlaceHolder ID="plcWizard" Visible="false" runat="server">
        <div class="processingsect ">
            <ul class="processingwrap">
              <li class="active">
                <div class="processing-title">Create Store</div>
              </li>
              <li class="active">
                <div class="processing-title">Select Products</div>
              </li>
              <li class="">
                <div class="processing-title">Manage Stock</div>
              </li>
              <li class="">
                <div class="processing-title">Sponsored Products</div>
              </li>
              <li class="">
                <div class="processing-title">Publish Store</div>
              </li>
            </ul>
          </div><!--processingsect-->
    </asp:PlaceHolder>
    <asp:PlaceHolder ID="plcNoneWizard" Visible="false" runat="server">
     <%--<h6 class="slim-pagetitle">Select Items for Sale</h6>--%>
        <!--<small> Total Items selected: <asp:Literal ID="ltrTitleCount" runat="server"></asp:Literal></small>-->
        <div>
        <h6 class="slim-pagetitle m-0">Select Items for Sale</h6>
        <p class="mb-0">Select Products from Brand Gallery</p>
    </div>
    </asp:PlaceHolder>

</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
<asp:PlaceHolder ID="plcWizardBrudcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <%--<li class="breadcrumb-item"><a href="/ItemsForSale">Manage Stock</a></li>--%>
    <%--<li class="breadcrumb-item"><a href="/ItemsForSale">Add Item For Sale</a></li>--%>
    <li class="breadcrumb-item active" aria-current="page">Select items for sale</li>
</asp:PlaceHolder>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
<div class="card">
        <uc1:ctrlInventorySetup runat="server" ID="ctrlInventorySetup1" />                
</div>
</asp:Content>