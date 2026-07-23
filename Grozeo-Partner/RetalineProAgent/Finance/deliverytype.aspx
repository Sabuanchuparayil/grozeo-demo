<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="deliverytype.aspx.cs" MasterPageFile="~/Finance/FinanceMaster.master" Inherits="RetalineProAgent.Finance.deliverytype" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
 <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery  Type</h6>
     <p class="mb-0">You can see Delivery  Type here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">
        <div class="row">
            <div class="col-12 pb-3">
                <div class="card m-0 h-100">
                    <div class="card-header shadow_top">
                        <div class="row row-sm">                            
                            <div class="col-12 col-lg-7 d-flex align-items-end">
                                <div class="input-group input_search_box">
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                    <asp:LinkButton runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600">
                                          <i class="fa fa-search"></i>
                              </div>
                                    </asp:LinkButton>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive" >
                             <table class="table table-bordered" cellspacing="0" border="1">
                                <thead>
                                    <tr>
                                        <th style="width:100px;">No</th>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Courier</td>
                                    </tr>
                                     <tr>
                                        <td>2</td>
                                        <td>Local</td>
                                    </tr>                                    
                                </tbody>
                            </table>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>        
    </section>  
</asp:Content>


