<%@ Page Language="C#" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Products" ValidateRequest="false" AutoEventWireup="true" CodeBehind="PrivateInventory.aspx.cs" Inherits="RetalineProAgent.PrivateInventory" %>

<%@ Register Src="~/Controls/StoreSettings/ctrlCreateProduct.ascx" TagPrefix="uc1" TagName="ctrlCreateProduct" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlMessagebox.ascx" TagPrefix="uc1" TagName="ctrlMessagebox" %>



<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/MyProducts">My Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Product</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><%: Request.QueryString["type"] == "1" ? "Edit Product" : "Create Product" %></h6>
    <p class="mb-0"><%: Request.QueryString["type"] == "1" ? "Update an existing product" : "Add a New Product" %></p>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
     <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body p-3 shadow_top">
          <%--<label class="section-title">Create New Product</label>--%>
        <uc1:ctrlCreateProduct runat="server" ID="ctrlCreateProduct" />
        </div>
    </div>
    <uc1:ctrlMessagebox runat="server" id="ctrlMessagebox" />
    <div class="modal" id="Popupattribute">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="modaldemo5Label">Manage Attribute</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
                <div class="modal-body">                   
                     <div class="card-body p-3">
                       <div class="row row-sm">
                              <asp:Repeater runat="server" ID="rptattribute" DataSourceID="SDSattribute" OnItemDataBound="rptattribute_ItemDataBound" >
                     <ItemTemplate>
                         
                          <div class="form-group col-12 col-sm-6">
                              <label><asp:Literal runat="server" Text='<%# Eval("name")%>' ID="ltratrribute"></asp:Literal></label>
                              <asp:ListBox ID="selattributevalue" ClientIDMode="Static" SelectionMode="Multiple" runat="server"  
                                  CssClass="form-control select2" DataTextField="valueName" DataValueField="id"></asp:ListBox>
                                   <asp:HiddenField ID="hfAttributeId" runat="server" Value='<%# Eval("attributeId") %>' />
                                 <asp:TextBox runat="server" ID="txtattribute" Visible='<%#((Eval("valueMode")).ToString() == "2" ? true : false) %>'  TextMode="SingleLine"></asp:TextBox>
                                   <asp:TextBox runat="server" ID="txtattibutevalue" Visible='<%#((Eval("valueMode")).ToString() == "3" ? true : false) %>'  TextMode="MultiLine"></asp:TextBox>

                          </div>
                     </ItemTemplate>                   
                 </asp:Repeater>
                                 <asp:HiddenField ID="hdnproductid" runat="server" />
                           <asp:HiddenField ID="HiddenSubCategoryId" runat="server" />
                        <asp:SqlDataSource runat="server" ID="SDSattribute" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
                        SelectCommand=" SELECT asm.attributeId,NAME,valueMode,stitId,GROUP_CONCAT(am.attributeValueId) AS selectedValues FROM attributeSubcategoryMap asm 
                                        INNER JOIN attribute ON id = asm.attributeId AND attribute.status=1   INNER JOIN  `attributeValue` av ON av.`attributeId`= asm.`attributeId` 
                                        LEFT JOIN  attributeProductMap am ON am.attributeId=asm.attributeId AND stitId = @Productid WHERE subCategoryId = @subcategory_id 
                                        GROUP BY attributeId ORDER BY valueMode ASC;">   
                            <SelectParameters>
                                  <asp:ControlParameter ControlID="HiddenSubCategoryId" Name="subcategory_id" PropertyName="Value" />
<%--                                <asp:QueryStringParameter QueryStringField="product_category" Name="subcategory_id" />--%>
                                  <asp:ControlParameter ControlID="hdnproductid" Name="Productid" PropertyName="Value" />
                            </SelectParameters>
                     </asp:SqlDataSource>                
                           <div class="col-12 justify-content-end">
                           <asp:LinkButton runat="server" Text="Save" ID="btnattributesave" OnClick="btnattributesave_Click" CssClass="btn btn-primary mr-1"></asp:LinkButton>
<%--                           <asp:LinkButton runat="server" Text="cancel" ID="btncanceattribute" CssClass="btn btn-secondary"></asp:LinkButton>--%>
                               <a href="/Tenant/MyProducts"  class="btn btn-secondary">Cancel</a>
                           </div>
                       </div>                    
                   </div>
                </div>                
            </div>
        </div>
    </div>

   <script type="text/javascript">
    $(document).ready(function () {
        $('#Popupattribute').on('shown.bs.modal', function () {
            // Initialize select2 with dropdownParent inside modal
            $('.select2').select2({
                dropdownParent: $('#Popupattribute') 
            });

            // Initialize multiselect properly
            $('#selattributevalue').multiselect('destroy'); // Destroy if previously initialized
            $('#selattributevalue').multiselect(); // Reinitialize
        });
    });
   </script>

<style>        
    .modal {
        overflow: visible; /* Ensures dropdowns appear */
    }  
</style>

</asp:Content>