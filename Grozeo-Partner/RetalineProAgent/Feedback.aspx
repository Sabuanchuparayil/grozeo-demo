<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Customer Messages" AutoEventWireup="true" CodeBehind="Feedback.aspx.cs" Inherits="RetalineProAgent.Feedback" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Support">Support</a></li>
    <li class="breadcrumb-item active" aria-current="page">Customer Communications</li>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
          <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                  <div class="float-right">
                      <div class="card-tools">
                <div class="input-group input-group-sm">
                    &nbsp;<asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
                    <asp:LinkButton runat="server" CssClass="input-group-append">
                        <%--<div class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </div>--%>
                    </asp:LinkButton>
                    &nbsp;
<%--<div class="float-right">
                  <asp:Literal runat="server" ID="ltrPageCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPageCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPageTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                      <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                      </asp:LinkButton>
                      <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm">
                          <i class="fa fa-chevron-right"></i>
                      </asp:LinkButton>
                    
                  </div>
                  <!-- /.btn-group -->
                </div>--%>
                    
                </div>
                  
              </div> 
                </div><br />
                    </div>
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvFeedback" runat="server" CssClass="table table-bordered" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" 
                                    OnDataBound="gvFeedback_DataBound" DataSourceID="SDSFeedback">
                                    <Columns>
                                        <asp:HyperLinkField DataTextField="fb_mobile" DataNavigateUrlFields="fb_id" DataNavigateUrlFormatString="~/FeedbackDetails.aspx?id={0}"
            HeaderText="Mobile" ItemStyle-Width = "150" SortExpression="fb_mobile" />
                                        <%--<asp:BoundField HeaderText="Mobile" DataField="fb_mobile" SortExpression="fb_mobile" />--%>
                                        <asp:BoundField HeaderText="Email" DataField="fb_email" SortExpression="fb_email" />
                                        <asp:BoundField HeaderText="Comments" DataField="fb_comments" SortExpression="fb_comments" />
                                        <asp:BoundField HeaderText="Date" DataFormatString="{0:dd/MM/yyyy hh:mm tt}" ItemStyle-Wrap="false" DataField="fb_createdOn" SortExpression="fb_createdOn" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSFeedback" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT fb_id,fb_mobile,fb_email,fb_comments, fb_createdOn FROM app_feedback WHERE storegroup_id=@storegroup order by fb_createdOn desc"
                                 OnSelecting="SDSFeedback_Selecting">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroup" />
                                </SelectParameters>
                            </asp:SqlDataSource>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>
</asp:Content>
