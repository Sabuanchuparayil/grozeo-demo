<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Campaigns" AutoEventWireup="true" CodeBehind="Campaigns.aspx.cs" Inherits="RetalineProAgent.Campaigns" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
    <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Campaigns"></asp:Literal> 
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Effective Marketing Outreach</p>
    </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/crm">Leads & Customers</a></li>
    <li class="breadcrumb-item active" aria-current="page">Campaigns</li>--%>
   <%-- <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
     <a href="/Navigations/crm"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    
        <div class="row" >
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
            
               <div class="table-responsive mailbox-messages">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <%--<asp:GridView AutoGenerateColumns="false" ID="gvBusinessFAQ" runat="server" CssClass="table table-bordered" 
                                     AllowSorting="true" ShowFooter="false" OnDataBound="gvBusinessFAQ_DataBound" DataSourceID="ODSBusinessFAQ" OnRowDataBound="gvBusinessFAQ_RowDataBound">
                                    <Columns>
                                        <asp:BoundField HeaderText="Customer" DataField="typeName" SortExpression="typeName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Conversation" DataField="title" SortExpression="title" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Number"  DataField="message" SortExpression="message" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Waiting Since"  DataField="message" SortExpression="message" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                    </Columns><EmptyDataTemplate>No data available.</EmptyDataTemplate>
                                </asp:GridView>--%>
                   <div class="card">
              <div class="card-header" role="tab" id="home">
                <a class="tx-gray-800 transition collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                  HOME
                </a>
              </div>
              <div id="collapseOne" class="collapse" role="tabpanel" aria-labelledby="home" style="">
                <div class="card-body">
                  The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.
                </div>
              </div>
            </div>
                   <div class="card">
              <div class="card-header" role="tab" id="sales&return">
                <a class="tx-gray-800 transition collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  SALES & RETURN
                </a>
              </div>
              <div id="collapseTwo" class="collapse" role="tabpanel" aria-labelledby="return" style="">
                <div class="card-body">
                  The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.
                </div>
              </div>
            </div>
                   <div class="card">
              <div class="card-header" role="tab" id="fulfilment">
                <a class="tx-gray-800 transition collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                  FULFILMENT
                </a>
              </div>
              <div id="collapseThree" class="collapse" role="tabpanel" aria-labelledby="return" style="">
                <div class="card-body">
                  The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.
                </div>
              </div>
                       <div class="card">
              <div class="card-header" role="tab" id="packing">
                <a class="tx-gray-800 transition collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="false" aria-controls="collapse1">
                  PACKING
                </a>
              </div>
              <div id="collapse1" class="collapse" role="tabpanel" aria-labelledby="return" style="">
                <div class="card-body">
                  The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.
                </div>
              </div>
            </div>
            </div>
                   <div class="card">
              <div class="card-header" role="tab" id="accounts">
                <a class="tx-gray-800 transition collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                  ACCOUNTS & MIS
                </a>
              </div>
              <div id="collapseFour" class="collapse" role="tabpanel" aria-labelledby="return" style="">
                <div class="card-body">
                  The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.
                </div>
              </div>
            </div>
                   <div class="card">
              <div class="card-header" role="tab" id="support">
                <a class="tx-gray-800 transition collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                  SUPPORT
                </a>
              </div>
              <div id="collapseFive" class="collapse" role="tabpanel" aria-labelledby="return" style="">
                <div class="card-body">
                  The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.
                </div>
              </div>
            </div>
                   <div class="card">
              <div class="card-header" role="tab" id="settings">
                <a class="tx-gray-800 transition collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                  SETTINGS
                </a>
              </div>
              <div id="collapseSix" class="collapse" role="tabpanel" aria-labelledby="return" style="">
                <div class="card-body">
                  The module manages the packaging process of orders, which involves preparing products for shipment by placing them in appropriate containers, boxes, or packaging materials, and ensuring they are properly invoicing, labeled and secured for delivery.
                </div>
              </div>
            </div>
                   

                                <%--<asp:ObjectDataSource ID="ODSBusinessFAQ" runat="server" TypeName="RetalineProAgent.Core.Services.APIService"
       SelectMethod="BusinessFAQ" OnSelecting="OBJ_Selecting" >
        <SelectParameters><asp:Parameter Name="storeId" /></SelectParameters></asp:ObjectDataSource>--%>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
