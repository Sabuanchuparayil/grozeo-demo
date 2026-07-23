<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Manage/AdminMaster.master" CodeBehind="Margin.aspx.cs" Inherits="RetalineProAgent.Margin" %>

<asp:Content ID="BodyContent" ContentPlaceHolderID="cpNMainContent" runat="server">
<style>
    .selected{
        background: none repeat scroll 0 0 #FFCF8B;
        color: #000000;
    }
    table.table tr:hover, table.table tr::selection, table.table tr:active{
        background: none repeat scroll 0 0 #FFCF8B;
        color: #000000;
    }
    table.table tr th, table.table tr td{
        padding: 8px 3px;

    }
</style>
    <asp:HiddenField ID="hidStoreName" runat="server" /><asp:HiddenField ID="hidStoreMargine" runat="server" />
    
<div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bar-chart-o fa-fw"></i> Set Margin - <label>Store: </label><asp:Label ID="lblStore" runat="server"></asp:Label> 
<div class="form-group" style="float: right; padding: 0px; margin-top: -5px;">
                                            <label style="float:left; padding:10px;">Store Margin: </label>
                <asp:Label ID="lblStoreMargine" runat="server"></asp:Label>% &nbsp;
                                            <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-default" Text="Save Changes" ValidationGroup="AddStore"/>
                                        </div>                        </div>
                        <!-- /.panel-heading -->
                        
                    </div>
</div>
			


<div class="col-lg-12">
    <div class="row">
        <div class="col-lg-4">
<div class="panel panel-default">
                        <div class="panel-heading" style="padding-right: 3px;">
                            Brands<asp:Literal ID="ltrBrandsCount" runat="server"></asp:Literal>: 
<div class="form-group" style="float: right; width: 245px; margin-top: -7px; padding: 0px; margin-bottom: 0px;">
                                            <asp:TextBox ID="txtBrand" style="float: left; width: 70%;" runat="server" CssClass="form-control" placeholder="Brand name part" ValidationGroup="BrandSearch"/>&nbsp;
 										    <asp:Button runat="server" style="padding: 6px 3px;" OnClick="btnBrandSearch_Click" ID="btnBrandSearch" CssClass="btn btn-default" Text="Search" ValidationGroup="BrandSearch"/>
                                       </div>
                        </div>
                        <div class="panel-body" style="max-height: 500px; width:100%; overflow-y: auto;">
<div class="table-responsive">
							<asp:GridView CssClass="table" DataSourceID="OBJBrands" Font-Size="10px" ID="gvBrands" GridLines="None" 
                                AllowSorting="true" runat="server" AutoGenerateColumns="false">
                                <Columns>
                                    
                                    <asp:TemplateField>
                                        <HeaderTemplate>
                                            <asp:CheckBox ID="chkHeadBrands" runat="server" />
                                        </HeaderTemplate>
                                        <ItemTemplate>
                                        <asp:CheckBox ID="chkBrandItem" runat="server" />
                                                       </ItemTemplate></asp:TemplateField>
                                    <asp:TemplateField HeaderText="Brand Name" SortExpression="MIH_ITEM_MFR_CODE">
                                        <ItemTemplate>
                                            <asp:LinkButton runat="server" brandCode='<%# Eval("MIH_ITEM_MFR_CODE")%>' Text='<%# String.Format("{0}", Eval("MMM_MFR_NAME"))%>' ID="lnkBrandItem" OnClick="lnkBrandItem_Click"></asp:LinkButton>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:BoundField DataField="Count" SortExpression="Count" HeaderText="Count" />
                                    <asp:BoundField DataField="Margine" SortExpression="Margine" HeaderText="Margin" />
                                </Columns>
                                <EmptyDataTemplate>No data available</EmptyDataTemplate>
							</asp:GridView>

    <asp:ObjectDataSource ID="OBJBrands" runat="server" TypeName="RetalineProAgent.Service.Common"
       SelectMethod="GetBrands" >
        <SelectParameters>
            <asp:ControlParameter ControlID="txtBrand" Name="searchKey" />
            <asp:QueryStringParameter QueryStringField="storeid" Name="storeId" />
            <asp:ControlParameter ControlID="hidStoreMargine" Name="defaultMargine" />
        </SelectParameters>
    </asp:ObjectDataSource>

							<%--<asp:ListView ID="lstBrands" runat="server" ItemPlaceholderID="plsBrands" DataKeyNames="MIH_ITEM_MFR_CODE">
                <LayoutTemplate>
				<table class="table" style="font-size: 10px;">
                                    <thead>
                                        <tr>
                                            <th><asp:CheckBox ID="chkHeadBrands" runat="server" /></th>
                                            <th>Brand Name</th>
                                            <th style="width: 60px;">Count</th>
											<th style="width: 67px;">Margine %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									
				<asp:PlaceHolder ID="plsBrands" runat="server"></asp:PlaceHolder>
				</tbody>
                                </table>
				</LayoutTemplate>
                <ItemTemplate>
                    <tr>
                        <td><asp:CheckBox ID="chkBrandItem" runat="server" /></td>
						<td><asp:LinkButton runat="server" brandCode='<%# Eval("MIH_ITEM_MFR_CODE")%>' Text='<%# String.Format("{0}", Eval("MMM_MFR_NAME"))%>' ID="lnkBrandItem" OnClick="lnkBrandItem_Click"></asp:LinkButton></td>
                        <td><%# Eval("Count") %></td>
						<td> <asp:TextBox ID="txtBCustomMargine" Text="5" Width="50" TextMode="Number" runat="server"></asp:TextBox></td>
					</tr>
                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>--%>
                                
			
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
										    <asp:Button runat="server" ID="btnProdSearch" OnClick="btnProdSearch_Click" CssClass="btn btn-default" Text="Update" ValidationGroup="ProductSearch"/>
                                        </div>
                        </div>
                        <div class="panel-body" style="max-height: 500px; overflow: auto;">
<div class="table-responsive">
							
							<asp:ListView ID="lstProducts" runat="server" ItemPlaceholderID="plsProducts">
                <LayoutTemplate>
				<table class="table">
                                    <thead>
                                        <tr>
                                            <th><asp:CheckBox ID="chkProductHItem" runat="server" /></th>
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
                    <tr>
                        <td><asp:CheckBox ID="chkProductHItem" runat="server" /></td>
						<td><%# Eval("MIH_ITEM_NAME")%></td>
						<td><%# Eval("MID_MRP")%></td>
						<td><asp:TextBox ID="txtPStock" Text='<%# Eval("MAIN_lOC_STOCK") %>' TextMode="Number" Width="50" runat="server"></asp:TextBox></td>
						<td> <asp:TextBox ID="txtPCustomMargine" Text='<%# Eval("Margine") %>' TextMode="Number" Width="50" runat="server"></asp:TextBox></td>
					</tr>
                </ItemTemplate>
                <EmptyItemTemplate>No data available</EmptyItemTemplate>
            </asp:ListView>
                                
			
                            </div>
                            
                        </div>
                       
                    </div>
    </div>

    </div>

                </div>
            <br />

    <script>
        $("table.table tr").click(function () {
            $(this).addClass("selected").siblings().removeClass("selected");
        });​
    </script>

    <asp:SqlDataSource ID="SDSStore" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
        SelectCommand="Select * from Store where Id=@id">
        <SelectParameters>
            <asp:QueryStringParameter QueryStringField="storeid" Name="id" />
        </SelectParameters>
    </asp:SqlDataSource>

</asp:Content>
