<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/AgentMaster.Master" CodeBehind="StoreCompletion.aspx.cs" Inherits="RetalineProAgent.StoreCompletion" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <style>
            .site_iframe iframe{
      width:100%!important; border:0px!important; height:100vh!important;
    }

    </style>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
        <div class="processingsect ">
            <ul class="processingwrap">
              <li class="active">
                <div class="processing-title">Create Store</div>
              </li>
                <% if (this.CurrentUser.TenantType != 2)
                    { %>
              <li class="active">
                <div class="processing-title">Select Products</div>
              </li>
              <li class="active">
                <div class="processing-title">Manage Stock</div>
              </li>
                <% } %>
              <li class="active">
                <div class="processing-title">Sponsored Products</div>
              </li>
              <li class="active">
                <div class="processing-title">Publish Store</div>
              </li>
            </ul>
          </div><!--processingsect-->
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server"></asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

        <div class="card card-body p-0">
          <div class="wizard_wrap p-3" style="max-height: none;">
            
  
            <div class="wizard_body">
  
              <div class=" wizard-cont-wrap">

                <div class="site_iframe">
                  <iframe id="frmPublicStore" runat="server" title="grozeo"></iframe> 
                </div>

              </div> <!--table-responsive-->
            </div> <!--wizard_body-->
          </div><!--wizard_wrap-->
          <div class="d-sm-flex p-3 wiz_btnsect justify-content-center">
                            <asp:LinkButton ID="lbtnConfirmCompletion" runat="server" Text="Completed" CssClass="btn btn-success btn-block m-0 mx-2 wd-sm-auto-force px-4" OnClick="lbtnConfirmCompletion_Click"></asp:LinkButton>
              <%--<a href="/" class="btn btn-success btn-block m-0 mx-2 wd-sm-auto-force px-4">Completed</a>--%>
          </div>
        </div><!--card-->

</asp:Content>