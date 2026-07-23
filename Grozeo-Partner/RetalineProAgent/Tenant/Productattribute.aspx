<%@ Page Language="C#" AutoEventWireup="true" Title="Manage Attribute" CodeBehind="Productattribute.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.Productattribute" %>
<%@ Register Src="~/Controls/StoreSettings/ctrlMessagebox.ascx" TagPrefix="uc1" TagName="ctrlMessagebox" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/PendingOrders">Order Packing</a></li>
    <li class="breadcrumb-item active" aria-current="page">Assign Order Picker</li>--%>
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Manage Attribute</h6>
        <p class="mb-0"> Manage Attribute</p>
    </div>   
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
     <div class="row">
         <div class="col-12">
              <div class="card">
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
                               <uc1:ctrlMessagebox runat="server" id="ctrlMessagebox" />
                           <asp:HiddenField ID="HiddenSubCategoryId" runat="server" />
                        <asp:SqlDataSource runat="server" ID="SDSattribute" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
                        SelectCommand=" SELECT asm.attributeId,NAME,valueMode,stitId,GROUP_CONCAT(am.attributeValueId) AS selectedValues FROM attributeSubcategoryMap asm 
                                        INNER JOIN attribute ON id = asm.attributeId AND attribute.status=1
                                        LEFT JOIN  attributeProductMap am ON am.attributeId=asm.attributeId AND stitId = @productid WHERE subCategoryId = @subcategory_id 
                                        GROUP BY attributeId ORDER BY valueMode ASC;">   
                            <SelectParameters>
                                  <asp:ControlParameter ControlID="HiddenSubCategoryId" Name="subcategory_id" PropertyName="Value" />
<%--                                <asp:QueryStringParameter QueryStringField="product_category" Name="subcategory_id" />--%>
                                 <asp:QueryStringParameter QueryStringField="productid" Name="productid" />
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
    <script>
        $(document).ready(function () {
            $('.select2').select2();           
            $('#selattributevalue').multiselect();
            });
    </script>

</asp:Content>
