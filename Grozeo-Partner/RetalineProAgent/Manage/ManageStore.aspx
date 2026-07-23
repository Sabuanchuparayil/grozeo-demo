<%@ Page Language="C#" AutoEventWireup="true" Title="Add Store" MasterPageFile="~/Manage/AdminMaster.master" CodeBehind="ManageStore.aspx.cs" Inherits="RetalineProAgent.ManageStore" %>

<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">

<div class="panel panel-default">
                        <div class="panel-heading" runat="server" visible="false">
                            <i class="fa fa-bar-chart-o fa-fw"></i> 
                            <asp:Literal ID="ltrAction" runat="server" Text="Add Store"></asp:Literal>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="row">
							
							
							<div class="col-lg-6">

                                <div class="card card-primary">
                                    <div class="card-header">
              <h3 class="card-title">Store Info</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                  <i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
                                    <div class="card-body" style="display: block;">


                                        <div class="form-group">
                                            <label>Store Name</label><asp:RequiredFieldValidator ValidationGroup="AddStore" ForeColor="Red" Font-Bold="true" runat="server"
                                ControlToValidate="txtStoreName" Text="*" ErrorMessage="Enter name"></asp:RequiredFieldValidator>
                                            <asp:TextBox ID="txtStoreName" runat="server" CssClass="form-control" onchange="document.getElementById('lblCustomDomain').innerHTML= this.value.toLowerCase().replace(/ /g,'')+'.site.com';" placeholder="Enter name"/>
                                            <asp:Label ClientIDMode="Static" id="lblCustomDomain" runat="server">[title].site.com</asp:Label>
                                        </div>
										<div class="form-group">
                                            <label>Store Id</label>
											<asp:TextBox ID="txtAPICode" runat="server" CssClass="form-control" placeholder="Enter API Code"/>
                                        </div>
                                
                                <div class="form-group">
                                            <label>Theme</label>
											<asp:DropDownList ID="selTheme" CssClass="form-control" runat="server">
                                                <asp:ListItem Text="Retaline Cart" Value="Retaline"></asp:ListItem>
                                                <asp:ListItem Text="Consumerfed Online Store" Value="Consumerfed"></asp:ListItem>
                                                <asp:ListItem Text="Dhanya Supermarket" Value="DhanyaNew"></asp:ListItem>
                                                <asp:ListItem Text="916 Cart" Value="916Cart"></asp:ListItem>
                                                <asp:ListItem Text="Jewel" Value="Jewel"></asp:ListItem>
											</asp:DropDownList>
                                        </div>

                                <div class="form-group">
                                            <label>Package</label>
											<asp:DropDownList ID="selPackage" CssClass="form-control" runat="server">
                                                <asp:ListItem Text="Basic (Free)" Value="basic"></asp:ListItem>
                                                <asp:ListItem Text="Standard" Value="standard"></asp:ListItem>
                                                <asp:ListItem Text="Premium" Value="premium"></asp:ListItem>
											</asp:DropDownList>
                                        </div>

                                <div class="form-group">
                                            <label>Min Margin%</label><asp:RequiredFieldValidator ValidationGroup="AddStore" ForeColor="Red" Font-Bold="true" runat="server"
                                ControlToValidate="txtMinMargine" Text="*" ErrorMessage="Enter min margin"></asp:RequiredFieldValidator>
											<asp:TextBox ID="txtMinMargine" TextMode="Number" runat="server" CssClass="form-control" placeholder="Enter min margin"/>
                                        </div>
                                

                                    </div>
                                </div>
                                    
                                
									
							</div>
							<div class="col-lg-6">
                                <div class="card card-secondary">
                                    <div class="card-header">
              <h3 class="card-title">Settings</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                  <i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
                                    <div class="card-body">

										<div class="form-group">
                                            <label>Business Type</label><br />
                                            <asp:CheckBoxList runat="server" RepeatLayout="Flow" Font-Size="13px" RepeatColumns="2" Width="200" CellPadding="10" CellSpacing="10" ID="chkBusinessTypes">
                                                <asp:ListItem style="padding: 10px;" Text="Grocery" Value="Grocery"></asp:ListItem>
                                                <asp:ListItem style="padding: 10px;" Text="Jewellery" Value="Jewellery"></asp:ListItem>
                                                <asp:ListItem style="padding: 10px;" Text="Restaurant" Value="Restaurant"></asp:ListItem>
                                                <asp:ListItem style="padding: 10px;" Text="Textile" Value="Textile"></asp:ListItem>
                                            </asp:CheckBoxList>
                                        </div>
                                <div class="form-group">
                                            <label>Logo</label>
											<asp:FileUpload ID="uploadLogo" CssClass="form-control" runat="server" />
                                    <asp:Image ID="imgLogo" runat="server" style="max-width: 40px; max-height: 40px; width: auto; height: auto;border: solid 1px lightgray;" Visible="false" />
                                    <asp:CheckBox ID="chkDelImgLogo" runat="server" Visible="false" Text="Delete?" />
                                        </div>
                                <div class="form-group">
                                            <label>Logo White</label>
											<asp:FileUpload ID="uploadLogoWhite" CssClass="form-control" runat="server" />
                                    <asp:Image ID="imgLogoWhite" runat="server" style="max-width: 40px; max-height: 40px; width: auto; height: auto;border: solid 1px lightgray;" Visible="false" />
                                    <asp:CheckBox ID="chkDelImgLogoWhite" runat="server" Visible="false" Text="Delete?" />
                                        </div>

                                <div class="form-group">
                                            <label>Custom colour</label>
											<asp:TextBox ID="txtColor" runat="server" CssClass="form-control" placeholder="Enter color (eg. #ece5e5)"/>
                                        </div>

<div class="form-group">
                                            <label>Custom domain</label>
											<asp:TextBox ID="txtCustomDomain" runat="server" CssClass="form-control" placeholder="Enter domain if available (comma seperated)"/>
                                        </div>
<asp:PlaceHolder runat="server" Visible="false">
										<div class="form-group">
                                            <label>DB Connection String</label>
											<asp:TextBox ID="txtConnectionString" runat="server" CssClass="form-control" placeholder="Enter connection string"/>
                                        </div>
                                <div class="form-group">
                                            <label>Select Sql</label>
											<asp:TextBox ID="txtSelectSql" runat="server" CssClass="form-control" placeholder="Enter Sql"/>
                                        </div>
</asp:PlaceHolder>										
										
										
										<div class="checkbox" style="font-size: 12px;">
                                                <label><asp:CheckBox ID="chkStatus" runat="server" style="padding: 20px;" Checked="True" Text="Status"/>
                                                    &nbsp;<asp:CheckBox ID="chkCheckout" runat="server" style="padding: 20px;" Checked="True" Text="Checkout"/>
                                                    &nbsp;<asp:CheckBox ID="chkOnline" runat="server" style="padding: 20px;" Checked="True" Text="Online Payment"/>
                                                    &nbsp;<asp:CheckBox ID="chkPWA" runat="server" style="padding: 20px;" Checked="True" Text="PWA"/>
                                                </label>
                                           </div>
                                        <%--<button type="reset" class="btn btn-default">Reset</button>--%>
                                        
                                    </div>

                                </div>



										
							</div>
							
							</div>

                            <div class="row">
        <div class="col-12">
          <%--<a href="#" class="btn btn-secondary">Cancel</a>--%>
            <asp:Button ID="btnReset" runat="server" OnClick="btnReset_Click" Text="Cancel" CssClass="btn btn-secondary" />
          <%--<input type="submit" value="Create new Porject" class="btn btn-success float-right">--%>
            <asp:Button runat="server" ID="btnAdd" OnClick="btnAdd_Click" CssClass="btn btn-success float-right" Text="Add" ValidationGroup="AddStore"/>
            <br /><asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>
        </div>
      </div>



                        </div>
                        <!-- /.panel-body -->
                    </div>
       <br /><br />
		<asp:SqlDataSource ID="SDSStores" runat="server" ConnectionString="<%$ ConnectionStrings:conn %>"
        SelectCommand="SELECT a.*, Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts 
,s.MinMargin, s.DBConnectionString, s.SelectSql, s.APICode, s.GroupId, s.BusinessType, s.Package, s.Id as tStoreId
FROM AppTenant a left join Store s on s.TenantId=a.Id " 
            >
		
    </asp:SqlDataSource>
    
</asp:Content>
