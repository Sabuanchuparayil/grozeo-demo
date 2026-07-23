<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="CostCentre.aspx.cs" Inherits="RetalineProAgent.Finance.CostCentre" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
              <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Manage Cost Centre</h6>
      <p class="mb-0">You can see Manage Cost Centre here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">
            <div class="row row-sm">
               <div class="col-12 col-lg-7 pb-3">
                   <div class="card m-0 h-100">
                        <div class="card-header shadow_top">
                            <div class="row row-sm">
                                <div class="col-12 col-lg-5">
                                    <div class="d-inline-block mb-2 mb-lg-0 mr-lg-3">
                                        <asp:Button runat="server" ID="btncreatenew" OnClick="btncreatenew_Click" CssClass="btn btn-primary AddVoucherBTN" Text="Create New"></asp:Button>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-7 d-flex align-items-end">
                                    <div class="input-group input_search_box">
                                        <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                       <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" Style="height: 31px;" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600">
                                          <i class="fa fa-search"></i>
                              </div>
                                          </asp:LinkButton>
                                    </div>                                   
                                </div>
                            </div>
                        </div>

                        <div class="card-body ">                            
                            <div class="table-responsive">
                                <table class="table table-bordered table table-bordered gridview_table" rules="all" id="cpMainContent_gvGroup"
                                    style="border-style: Solid; border-collapse: collapse;" cellspacing="0" border="1">
                                    <tbody>
                                        <tr class="border-top">
                                            <th width="50%"  align="left">Cost Centre</th>
                                            <th width="25%"  align="left">Cost Category</th>
                                            <th width="25%"  align="left">Action</th>
                                        </tr>
                                        <asp:ListView ID="lvledger" runat="server" DataSourceID="SDSledgerCreation" OnDataBound="lvledger_DataBound">
                                            <ItemTemplate>
                                                <tr>
                                                    <td align="left"><%# Eval("name")%>
                                                       </td>
                                                    <td align="left"><%# Eval("costcategoryname")%></td>
                                                    <td align="right"> <asp:LinkButton ID="btnselect" dataid='<%# Eval("id") %>' Text="View/Edit" OnClick="btnselect_Click"  runat="server"></asp:LinkButton></td>
                                                </tr>
                                            </ItemTemplate>
                                        </asp:ListView>                                       
                                    </tbody>
                                </table>
                                <asp:SqlDataSource runat="server" ID="SDSledgerCreation" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"
                                    SelectCommand="select id, name,cost_category_id,(select name from cost_category cg where cc.cost_category_id=cg.id) as costcategoryname from cost_centre cc
                                        where  (trim(@search) like '' or cc.name like CONCAT('%', @search, '%'))  order by name">
                                   <SelectParameters>
                                        <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                        <asp:QueryStringParameter QueryStringField="ntid" Name="nature" DefaultValue="0" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                            </div>
                            <div class="pagenation_listview p-3">
                                <asp:DataPager ID="DataPager1" runat="server" PageSize="10"
                                    PagedControlID="lvledger">
                                    <Fields>
                                        <asp:NextPreviousPagerField PreviousPageText="<" FirstPageText="<<" ShowPreviousPageButton="false"
                                            ShowFirstPageButton="false" ShowNextPageButton="false" ShowLastPageButton="false"
                                            ButtonCssClass="btn btn-default" RenderNonBreakingSpacesBetweenControls="false" RenderDisabledButtonsAsLabels="false" />
                                        <asp:NumericPagerField ButtonType="Link" CurrentPageLabelCssClass="btn btn-primary disabled" RenderNonBreakingSpacesBetweenControls="false"
                                            NumericButtonCssClass="btn btn-default" ButtonCount="5" NextPageText="..." NextPreviousButtonCssClass="btn btn-default" />
                                        <asp:NextPreviousPagerField NextPageText=">" LastPageText=">>" ShowNextPageButton="false"
                                            ShowLastPageButton="false" ShowPreviousPageButton="false" ShowFirstPageButton="false"
                                            ButtonCssClass="btn btn-default" RenderNonBreakingSpacesBetweenControls="false" RenderDisabledButtonsAsLabels="false" />
                                    </Fields>
                                </asp:DataPager>
                            </div>
                        </div>
                    </div>
                </div>            

                <div class="col-12 col-lg-5 pb-3">
             <div class="card m-0 h-100 overflow-hidden" style="overflow:hidden;">
                   <asp:PlaceHolder runat="server" Visible="false" ID="pnlnewledgers">
                        <div class="card-header shadow_top border-0 d-flex align-items-center" style="min-height: 69px;">
                                <h5 class="mb-0">Create New Cost Centre</h5>
                            </div>
                        <div class="card-body p-3" style="overflow:hidden;">
                            <div class="tab-content" id="ledger-tabContent">
                                <div class="form-group mb-0">
                                            <label class="mb-0">Name of Cost Centre</label>
                                             <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                             <asp:TextBox runat="server" CssClass="form-control" ID="txtName" autocomplete="off"></asp:TextBox>
                                        <asp:RequiredFieldValidator runat="server" ControlToValidate="txtName" ForeColor="Red" ErrorMessage="Please give a ledger Name"></asp:RequiredFieldValidator>

                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="mb-0 w-100">Select Cost Category </label>
                                             <asp:DropDownList ID="ddlgroup" DataSourceID="SDSgroupnew" CssClass="form-control select2" DataTextField="name" DataValueField="id" OnSelectedIndexChanged="ddlgroup_SelectedIndexChanged" AutoPostBack="true"  AppendDataBoundItems="true" runat="server">
                                                <asp:ListItem Text=" Select Cost Category" Value=""></asp:ListItem>
                                            </asp:DropDownList>
                                            <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlgroup" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>
                                            <asp:SqlDataSource ID="SDSgroupnew" runat="server" SelectCommand="select id,name from cost_category" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                        </div>  
                                <div class="card-footer bg-white p-0">
                                <div class="form-group mb-0 float-right">
                                   <%-- <a class="btn btn-success " href="">Save</a>--%>
                                    <asp:LinkButton runat="server" ID="btnsave" CssClass="btn btn-primary mx-1 py-1" OnClick="btnsave_Click" >Save</asp:LinkButton>
                                     <asp:LinkButton runat="server" ID="btncanel_update" CssClass="btn btn-secondary mx-1 py-1" CausesValidation="false" OnClick="btncanel_update_Click">Cancel</asp:LinkButton>
                                    
                                </div>
                                 <asp:Label ID="lbledgermgmt" runat="server"></asp:Label>
                            </div>
                            </div>
                        </div>
                   </asp:PlaceHolder>
                 <asp:PlaceHolder runat="server" Visible="true" ID="pnlledgerdetailes">
                      <div class="card-header shadow_top border-0 d-flex align-items-center ht-55">
                                <h5 class="mb-0">Create New Cost Centre</h5>
                            </div>
                      <div class="card-body">
                           <asp:HiddenField ID="hidledger" ClientIDMode="Static" Value="0" runat="server" />
                             <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr class="border-top" >
                                                        <td width="140px" class="font-weight-bold">Name</td>
                                                        <td><asp:Literal ID="ltrnameledger" runat="server"></asp:Literal></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="140px" class="font-weight-bold">Cost Category</td>
                                                        <td><asp:Literal ID="ltrgroup" runat="server"></asp:Literal></td>
                                                    </tr>                                                  
                                                </tbody>
                                            </table>
                                        </div>
                          <div class="form-group mt-3 mb-0 float-right px-3">
                                   <asp:LinkButton runat="server" ID="btnedit" Enabled="false"  OnClick="btnedit_Click" CssClass="btn btn-primary text-white py-2 px-3" CommandName="Edit">Edit</asp:LinkButton>

                                </div>
                      </div>
                      
                 </asp:PlaceHolder>
                  <asp:PlaceHolder runat="server" Visible="false" ID="pnlledger_updetes">
                       <div class="card-header shadow_top border-0 d-flex align-items-center">
                                <h5 class="mb-0">Create New </h5>
                            </div>
                        <div class="card-body pt-0" >
                              <div class="form-group">
                                            <label class="mb-0">Name of Cost Centre</label>
                                          <asp:TextBox runat="server" CssClass="form-control" ID="txtnameupdate"></asp:TextBox>
                                           <asp:RequiredFieldValidator runat="server" ControlToValidate="txtnameupdate" ForeColor="Red" ErrorMessage="Please give a ledger name"></asp:RequiredFieldValidator>

                                        </div>
                            <div class="form-group">
                                            <label class="mb-0 w-100">Select  Cost Category </label>                                           
                                            <asp:DropDownList ID="selGroup" DataSourceID="SDSgroup" CssClass="form-control select2" DataTextField="name" DataValueField="id" OnSelectedIndexChanged="selGroup_SelectedIndexChanged"  AppendDataBoundItems="true" runat="server">
                                                <asp:ListItem Text=" Select Cost Category " Value=""></asp:ListItem>
                                            </asp:DropDownList>
                                           <asp:RequiredFieldValidator runat="server" ControlToValidate="selGroup" ForeColor="Red" ErrorMessage="Please select group"></asp:RequiredFieldValidator>

                                            <asp:SqlDataSource ID="SDSgroup" runat="server" SelectCommand="select id,name from cost_category" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                        </div> 
                        </div>
                         <div class="card-footer bg-white">
                                <div class="form-group mb-0 float-right">
                                    <%--<a class="btn btn-success " href="">Save</a>--%>
                                 <asp:LinkButton runat="server" ID="btnupdate" OnClick="btnupdate_Click" CssClass="btn btn-primary mx-1 py-1">Save</asp:LinkButton>
                                <asp:LinkButton runat="server" ID="btncancel" CssClass="btn btn-secondary mx-1 py-1" CausesValidation="false" OnClick="btncancel_Click">Cancel</asp:LinkButton>
                                </div>
                            </div>
                  </asp:PlaceHolder>
             </div>
                </div>
            </div>           
    </section>
    <style>
        .ledger-tab::after {
            content: '';
            width: calc(100vw + 20vw);
            height: 1px;
            background: #dee2e6;
            left: -20px;
            bottom: 0px;
            position: absolute;
        }

        .ledger-tab li > a.nav-link {
            position: relative;
            font-size: 14px;
            color: #495057;
        }

        .ledger-tab li > a.active.nav-link {
            color: #0069d9;
        }

            .ledger-tab li > a.active.nav-link::after {
                content: '';
                width: 100%;
                height: 4px;
                background: #0069d9;
                left: 0px;
                bottom: -6px;
                position: absolute;
            }

        .tab-content .card-header {
            padding-left: .75rem;
            padding-right: .75rem;
        }

        .tab-content h6 {
            text-decoration: underline;
            font-weight: 600;
        }

        .form-group .breadcrumb-item + .breadcrumb-item::before {
            content: "\f10b";
            font-weight: 900;
            line-height: 22px;
            font-size: 11px;
        }
    </style>  
</asp:Content>

