<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Message Center" AutoEventWireup="true" CodeBehind="MessageCenter.aspx.cs" Inherits="RetalineProAgent.MessageCenter" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
    <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Message Center"></asp:Literal> 
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Centralized Communication Hub</p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Support">Support</a></li>
    <li class="breadcrumb-item active" aria-current="page">Message Center</li>--%>
    <a href="/Navigations/Support"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                      <div class="d-inline-block w-100 tx-center py-4">
                          <asp:Image runat="server" ID="imgId" CssClass="img-fluid" style="opacity: 0.9; max-width:450px; width: 100%;" ImageUrl="/content/images/nodata.png"/>
                      </div>
                    </div><!--card body-->
              </div>
            </div>
          <div class="col-12" runat="server" visible="false">
            <div class="card">
                <div class="card-header">
                  <div class="row">
    <div class="col-lg-3 d-flex flex-wrap input-group-sm">
                                          <label for="txtBranch" runat="server" class="tx-dark mb-1 w-10">Branch:</label>
                                          <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">
                                          <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                   <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" OnDataBound="selBranches_DataBound" AutoPostBack="true" CssClass="bd p-2 wd-100p-force" DataSourceID="SDSBranches" DataTextField="br_Name" DataValueField="br_ID" runat="server"><asp:ListItem Text="Select Branch" Value="-1"></asp:ListItem></asp:DropDownList>
                    <%--<asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator>--%>
                </asp:PlaceHolder>
                                          <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                ProviderName="MySql.Data.MySqlClient"
                ><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" /><asp:Parameter Name="branchid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>
                                      </div>
                                    <%--<div class="col-lg-5">
                                          <label class="form-control-label w-100 mb-1">Search: </label>
                                          <input type="text" style="display:none" />
                                          <input type="password" style="display:none" />
                                        <div class="d-flex">
                                            <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name and phone number" CssClass="p-1 form-control" autocomplete="nofill"></asp:TextBox>
                                            <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" style="height:32px; line-height: 24px;" runat="server">Search</asp:LinkButton>
                                          </div>
                                      </div>--%>

                        </div>
                    </div>
                <div class="card-body">
                    <div class="row row-sm">
            
               <%--<div class="table-responsive mailbox-messages">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvMessageCenter" runat="server" CssClass="table table-bordered" 
                                     AllowSorting="true" ShowFooter="false" OnDataBound="gvMessageCenter_DataBound" DataSourceID="ODSMessageCenter" OnRowDataBound="gvMessageCenter_RowDataBound">
                                    <Columns>
                                        <asp:BoundField HeaderText="Customer" DataField="typeName" SortExpression="typeName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Conversation" DataField="title" SortExpression="title" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Number"  DataField="message" SortExpression="message" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Waiting Since"  DataField="message" SortExpression="message" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                    </Columns><EmptyDataTemplate>No data available.</EmptyDataTemplate>
                                </asp:GridView>
                                <asp:ObjectDataSource ID="ODSMessageCenter" runat="server" TypeName="RetalineProAgent.Core.Services.APIService"
       SelectMethod="MessageCenter" OnSelecting="OBJ_Selecting" >
        <SelectParameters><asp:Parameter Name="storeId" /></SelectParameters></asp:ObjectDataSource>
               </div>--%>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
