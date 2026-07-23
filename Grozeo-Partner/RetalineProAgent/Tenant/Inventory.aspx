<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Inventory.aspx.cs" Async="true" Inherits="RetalineProAgent.Inventory" %>

<asp:Content ID="BodyContent" ContentPlaceHolderID="cpMainContent" runat="server">
<style>
    ul.catmenu {
        list-style-type: none !important;
        overflow: unset !important;
        padding-left: 16px !important;
        max-height: none !important;
    }

    ul.catmenu li {
        margin: 2px 0px;
        cursor: pointer;
        padding: 10px;
        border: solid 1px lightgray;
    }
        ul.catmenu li ul li{border: none;}

        span.Collapsable {
            width: 100%;
        }
    ul.catmenu li:hover {
        color: #303391;
    }

    span.Collapsable:hover {
        color: #303391;
    }

    ul.catmenu ul {
        display: none;
    }

    span.symbol {
        padding-right: 10px;
        width: 12px;
        display: inline-block;
    }

</style>
    <asp:HiddenField ID="hidStoreName" runat="server" /><asp:HiddenField ID="hidStoreMargine" runat="server" />

      <div class="row">
        <div class="col-md-3">
          <%--<a href="compose.html" class="btn btn-primary btn-block mb-3">Compose</a>--%>
          <!-- /.card -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Filters</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <asp:LinkButton runat="server" ID="lbtnBrands" OnClick="lbtnBrands_Click" CssClass="nav-link">
                  <%--<a href="#" class="nav-link">--%>
                    <i class="far fa-circle text-danger"></i>
                    Brands
                  <%--</a>--%>
                    </asp:LinkButton>
                </li>
                <li class="nav-item">
                    <asp:LinkButton runat="server" ID="lbtnCategories" OnClick="lbtnCategories_Click" CssClass="nav-link">
                  <%--<a href="#" class="nav-link">--%>
                    <i class="far fa-circle text-warning"></i> Categories
                  <%--</a>--%>
                        </asp:LinkButton>
                </li>
                <%--<li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle text-primary"></i>
                    Social
                  </a>
                </li>--%>
              </ul>
            </div>
            <!-- /.card-body -->
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Brands</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">

                  <asp:Repeater runat="server" ID="rpBrands" DataSourceID="OBJCategories">
                      <HeaderTemplate>
              <ul class="nav nav-pills flex-column">
                      </HeaderTemplate>
                      <ItemTemplate>
                          <li class="nav-item active">
                  <%--<a href="#" class="nav-link">
                    <i class="fa fa-inbox"></i> 
                    <span class="badge bg-primary float-right">(12)</span>
                  </a>--%>
                    <asp:LinkButton runat="server" CssClass="nav-link" Text='<%# Eval("BrandName")%>' brandCode='<%# Eval("BrandId")%>' ID="lnkBrandItem" OnClick="lnkBrandItem_Click">
                        <i class="fa fa-inbox"></i> <asp:Literal runat="server"  Text='<%# Eval("BrandName")%>'></asp:Literal>
                    <span class="badge bg-primary float-right">1</span>
                    </asp:LinkButton>

                </li>
                      </ItemTemplate>
                      <FooterTemplate>              </ul>

                      </FooterTemplate>
                  </asp:Repeater>

                <asp:ListView ID="lstCategories" DataSourceID="OBJCategories" runat="server" ItemPlaceholderID="plsProducts">
                <LayoutTemplate>
				<ul class="catmenu">
				<asp:PlaceHolder ID="plsProducts" runat="server"></asp:PlaceHolder>
				</ul>
				</LayoutTemplate>
                <ItemTemplate>
                    
                    <li id="cat_<%# Eval("CategoryId") %>">
                                    <%--<img src="<%# Eval("ImageUrl") %>" style="width: 50px;" />--%>
                                    <span class="Collapsable"><span class="symbol fright"> +</span><%# Eval("CategoryName") %></span>
                                    <ul>
                                        
                                        <asp:Repeater ID="rptPCategories" runat="server" DataSource='<%# Eval("Subcategories") %>'>
                                            <ItemTemplate>
                                        <li id="<%# String.Format("cat_{0}_{1}",
    DataBinder.Eval(((IDataItemContainer)((Control)Container).NamingContainer.NamingContainer).DataItem, "CategoryId"), //Eval("CategoryId"), 
    Eval("CategoryId")) %>">
                                            <%--<img src="<%# Eval("ImageUrl") %>" style="width: 50px;" />--%>
                                            <span class="Collapsable"><span class="symbol fright"> +</span><%# Eval("CategoryName") %></span>
                                            <ul>
                                                <asp:Repeater ID="rptsubcat" runat="server" DataSource='<%# Eval("Subcategory") %>'>
                                                    <ItemTemplate>
                                                <li 
                                                    id="<%# String.Format("cat_{0}_{1}_{2}", 
    DataBinder.Eval(((IDataItemContainer)((Control)Container).NamingContainer.NamingContainer).DataItem, "ParentCategory"), //Eval("CategoryId"), 
    DataBinder.Eval(((IDataItemContainer)((Control)Container).NamingContainer.NamingContainer).DataItem, "CategoryId"), //data.CategoryId, 
    Eval("SubCategoryId")) %>"
                                                    >
                                                    <%--<img src="<%# Eval("SubCategoryImage") %>" style="width: 50px;" />--%>
                                                    <asp:LinkButton ID="lbtnSubCat" runat="server" pcat='<%# DataBinder.Eval(((IDataItemContainer)((Control)Container).NamingContainer.NamingContainer).DataItem, "ParentCategory") %>'
                                                        catid='<%# DataBinder.Eval(((IDataItemContainer)((Control)Container).NamingContainer.NamingContainer).DataItem, "CategoryId") %>'
                                                        subcatid='<%# Eval("SubCategoryId") %>' Text='<%# Eval("SubCategoryName") %>'
                                                         OnClick="lbtnSubCat_Click" catlevel="3"
                                                        >
                                                    </asp:LinkButton>

<%--                                                    <a href="<%# String.Format("/pc/{0}/{1}/{2}/{3}", 
    DataBinder.Eval(((IDataItemContainer)((Control)Container).NamingContainer.NamingContainer).DataItem, "ParentCategory"), //Eval("CategoryId"), 
    DataBinder.Eval(((IDataItemContainer)((Control)Container).NamingContainer.NamingContainer).DataItem, "CategoryId"), //data.CategoryId, 
    Eval("SubCategoryId"), Eval("SubCategoryName")) %>"><%# Eval("SubCategoryName") %></a>--%>
                                                </li>

                                                    </ItemTemplate>
                                                </asp:Repeater>
                                                <%--<% foreach(var subcat in data.Subcategory) { %>
                                                <% } %>--%>
            
                                            </ul>
                                        </li>
                                            </ItemTemplate>
                                        </asp:Repeater>

                                        <%--<% foreach(RetalineProAgent.Core.BussinessModel.Catalog.SubcategoryMaster data in ((List<RetalineProAgent.Core.BussinessModel.Catalog.SubcategoryMaster>)Eval("Subcategories"))) { %>
                                        

                                        <% } %>--%>

                                        
                                    </ul>
                                </li>

                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>

                
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
        <div class="col-md-9">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h3 class="card-title">Products</h3>

              <div class="card-tools">
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" placeholder="Search products">
                  <div class="input-group-append">
                    <div class="btn btn-primary">
                      <i class="fa fa-search"></i>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-tools -->
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
              <%--<div class="mailbox-controls">
                <!-- Check all button -->
                <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="far fa-trash-alt"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fa fa-reply"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fa fa-share"></i>
                  </button>
                </div>
                <!-- /.btn-group -->
                <button type="button" class="btn btn-default btn-sm">
                  <i class="fa fa-sync-alt"></i>
                </button>
                <div class="float-right">
                  0-0/0
                  <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm">
                      <i class="fa fa-chevron-right"></i>
                    </button>
                  </div>
                  <!-- /.btn-group -->
                </div>
                <!-- /.float-right -->
              </div>--%>
              <div class="table-responsive mailbox-messages">


                  <asp:ListView ID="lstProducts" DataSourceID="ODSProducts" runat="server" ItemPlaceholderID="plsProducts">
                <LayoutTemplate>
				<table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th><asp:CheckBox ID="chkProductHItem" AutoPostBack="true" OnCheckedChanged="chkProductHItem_CheckedChanged1" runat="server" /></th>
                                            <th>Name</th>
                                            <th>MRP</th>
											<th>Stock</th>
											<th>Margin %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									
				<asp:PlaceHolder ID="plsProducts" runat="server"></asp:PlaceHolder>
				</tbody>
                                </table>
				</LayoutTemplate>
                <ItemTemplate>
                    <asp:Repeater ID="rptProducts" runat="server" DataSource='<%# Eval("Products") %>'>
                        <ItemTemplate>

                            <asp:Repeater ID="rptItem" runat="server" DataSource='<%# Eval("Item") %>'>
                                <ItemTemplate>
                            <tr>
                                <td><asp:CheckBox ID="chkProductHItem" itemid='<%# Eval("StitId") %>' Checked='<%# InventoryMapping.Any(i=> i.Id.Equals(Eval("StitId"))) %>' runat="server" /></td>
						        <td><%# Eval("ItemName")%></td>
						        <td><%# Eval("MRP")%></td>
						        <td><asp:TextBox ID="txtPStock" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>
						        <td> <asp:TextBox ID="txtPCustomMargine" TextMode="Number" Width="50" runat="server"></asp:TextBox></td>
					        </tr>


                                </ItemTemplate>
                            </asp:Repeater>

                        </ItemTemplate>
                    </asp:Repeater>

                    
                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>

                <!-- /.table -->
              </div>
              <!-- /.mail-box-messages -->
            </div>
            <!-- /.card-body -->
            <%--<div class="card-footer p-0">
              <div class="mailbox-controls">
                <!-- Check all button -->
                <button type="button" class="btn btn-default btn-sm checkbox-toggle">
                  <i class="far fa-square"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="far fa-trash-alt"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fa fa-reply"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fa fa-share"></i>
                  </button>
                </div>
                <!-- /.btn-group -->
                <button type="button" class="btn btn-default btn-sm">
                  <i class="fa fa-sync-alt"></i>
                </button>
                <div class="float-right">
                  0-0/0
                  <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm">
                      <i class="fa fa-chevron-right"></i>
                    </button>
                  </div>
                  <!-- /.btn-group -->
                </div>
                <!-- /.float-right -->
              </div>
            </div>--%>
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>

    <asp:ObjectDataSource ID="OBJCategories" runat="server" TypeName="RetalineProAgent.Service.Common"
       SelectMethod="GetFilterItems" >
        <SelectParameters>
            <asp:QueryStringParameter QueryStringField="storeid" Name="storeId" />
            <asp:ControlParameter ControlID="rbtnTypes" Name="typeid" Type="Int32" />
        </SelectParameters>
    </asp:ObjectDataSource>
    <asp:HiddenField runat="server" ID="hidBrandId" />
	<asp:HiddenField runat="server" ID="hidCatId" />
	<asp:HiddenField runat="server" ID="hidCatlevel" />							
                                
			<asp:ObjectDataSource ID="ODSProducts" runat="server" TypeName="RetalineProAgent.Service.Common"
       SelectMethod="GetFilteredProducts" >
        <SelectParameters>
            <asp:QueryStringParameter QueryStringField="storeid" Name="storeId" />
            <asp:ControlParameter ControlID="rbtnTypes" Name="typeid" Type="Int32" />
            <asp:ControlParameter ControlID="hidBrandId" Name="brandid" Type="Int32" />
            <asp:ControlParameter ControlID="hidCatId" Name="catid" Type="Int32" />
            <asp:ControlParameter ControlID="hidCatlevel" Name="catlevelid" Type="Int32" />

        </SelectParameters>
    </asp:ObjectDataSource>





<asp:RadioButtonList ID="rbtnTypes" AutoPostBack="true" runat="server" RepeatDirection="Vertical" RepeatLayout="Flow" Font-Size="10px">
                                <asp:ListItem Text="Brand" Selected="True" Value="1"></asp:ListItem>
                                <asp:ListItem Text="Category" Value="2"></asp:ListItem>
                            </asp:RadioButtonList>






    <asp:PlaceHolder runat="server" Visible="false">

<div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bar-chart-o fa-fw"></i> Set Margin - <label>Store: </label><asp:Label ID="lblStore" runat="server"></asp:Label> 
<div class="form-group" style="float: right; padding: 0px; margin-top: -5px;">
                                            <label style="float:left; padding:10px;">Store Margin: </label>
                <asp:Label ID="lblStoreMargine" runat="server"></asp:Label>% &nbsp;
                                            <asp:Button runat="server" ID="btnAdd" CssClass="btn btn-default" Text="Save Changes" ValidationGroup="AddStore"/>
                                        </div>                        </div>
                        <!-- /.panel-heading -->
                        
                    </div>
</div>
			


<div class="col-lg-12">
    <div class="row">
        <div class="col-lg-4">
<div class="panel panel-default">
                        <div class="panel-heading" style="padding-right: 3px;">
                            
                            <asp:Literal ID="ltrBrandsCount" runat="server"></asp:Literal>: 
<div class="form-group" style="float: right; width: 245px; margin-top: -7px; padding: 0px; margin-bottom: 0px;">
                                            <asp:TextBox ID="txtBrand" style="float: left; width: 70%;" runat="server" CssClass="form-control" placeholder="" ValidationGroup="BrandSearch"/>&nbsp;
 										    <asp:Button runat="server" style="padding: 6px 3px;" ID="btnCatSearch" CssClass="btn btn-default" Text="Search" ValidationGroup="BrandSearch"/>
                                       </div>
                        </div>
                        <div class="panel-body" style="max-height: 500px; width:100%; overflow-y: auto;">
<div class="table-responsive categories">							

    

    

    

			
                            </div>

                            
                            
                            
                        </div>
                    </div>
        </div>
<div class="col-lg-8">
<div class="panel panel-default">
                        <div class="panel-heading">
                            Products under brand: <asp:Literal ID="ltrSelBrand" runat="server"></asp:Literal>
<div class="form-group" style="float: right; width: 270px; margin-top: -7px; padding: 0px; margin-bottom: 0px;">
                                            <span style="float:left;"> Brand Margin:</span> <asp:TextBox ID="txtBMargine" Width="50" style="float:left; max-width: 70%;" runat="server" CssClass="form-control" placeholder="0.0" TextMode="Number" ValidationGroup="ProductSearch"/>&nbsp;                                            
										    <asp:Button runat="server" ID="btnProdSearch" CssClass="btn btn-default" Text="Update" ValidationGroup="ProductSearch"/>
                                        </div>
                        </div>
                        <div class="panel-body" style="max-height: 500px; overflow: auto;">
<div class="table-responsive">

                            </div>
                            
                        </div>
                       
                    </div>
    </div>

    </div>

                </div>


    </asp:PlaceHolder>


            <br />

    <script>
        //$("table.table tr").click(function () {
        //    $(this).addClass("selected").siblings().removeClass("selected");
        //});​

        $("ul.catmenu .Collapsable").click(function () {

            $(this).parent().children().toggle();
            $(this).toggle();
            if ($(this).parent().children('ul').is(':hidden')) {
                $(this).children('span').text('+');
            }
            else {
                $(this).children('span').text('-');
            }
        });

    </script>

    <%--<asp:SqlDataSource ID="SDSStore" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
        SelectCommand="Select * from Store where Id=@id">
        <SelectParameters>
            <asp:QueryStringParameter QueryStringField="storeid" Name="id" />
        </SelectParameters>
    </asp:SqlDataSource>--%>

</asp:Content>
