<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="CostCategory.aspx.cs" Inherits="RetalineProAgent.Finance.CostCategory" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
           <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Manage Cost Category</h6>
      <p class="mb-0">You can see Manage Cost Category here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">       
            <div class="row row-sm">
                <div class="col-12 col-lg-7 pb-3">
                    <div class="card m-0 h-100">
                        <div class="card-header shadow_top">
                            <div class="row row-sm">
                                <div class="col-12 col-lg-5">
                                    <div class="d-inline-block mb-2 mb-lg-0 mr-lg-3 ">
                                        <asp:Button runat="server" ID="btncreate" OnClick="btncreate_Click" CssClass="btn btn-primary AddVoucherBTN" Text="Create New"></asp:Button>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-7 d-flex align-items-end">
                                    <div class="input-group input_search_box">
                                        <%--<input name="" type="text" id="" class="form-control" placeholder="Search">
                          <a class="input-group-append" href="">
                              <div class="btn btn-primary" style="line-height: 24px;">
                                <i class="fas fa-search"></i>--%>
                                        <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                        <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600" >
                                          <i class="fa fa-search"></i>
                              </div>
                                        </asp:LinkButton>
                                        <%--</a>--%>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>

                        <div class="card-body ">
                            <div class="table-responsive">
                                <table class="table table-bordered gridview_table" rules="all" id="cpMainContent_gvGroup"
                                    style="border-style: Solid; border-collapse: collapse;" cellspacing="0" border="1">
                                    <tbody>
                                        <tr class="border-top">
                                            <th width="52%"  align="left">Cost Category Name</th>
                                          <%--  <th width="17%" class="bg-light" align="left">Number of Cost Centre</th> --%>
                                             <th width="17%"  align="left">Action</th>    
                                        </tr>
                                        <asp:ListView ID="lvdatatable" runat="server" DataSourceID="SDSGroupCreation" OnDataBinding="lvdatatable_DataBinding" OnDataBound="lvdatatable_DataBound">
                                            <ItemTemplate>
                                                <tr>
                                                    <td align="left"><%# Eval("name")%>
                                                       </td>
                                                   <%-- <td align="left"><%# Eval("countno") %></td>--%>
                                                    <td> <asp:LinkButton ID="btnselect" dataid='<%# Eval("id") %>' OnClick="btnhide_Click" Text="View/Edit" runat="server"></asp:LinkButton></td>

                                                </tr>
                                            </ItemTemplate>
                                        </asp:ListView>                                       
                                    </tbody>
                                    </table>                                   
                                <asp:SqlDataSource runat="server" ID="SDSGroupCreation" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                                    SelectCommand="select cg.id,cg.name,Count(cc.cost_category_id) as countno from cost_category cg left join cost_centre cc on cc.cost_category_id=cg.id  where (trim(@search) like '' or cg.name like CONCAT('%', @search, '%')) group by cost_category_id,cg.name,cg.id order by cg.name  ">
                                    <SelectParameters>
                                        <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                        <asp:QueryStringParameter QueryStringField="ntid" Name="nature" DefaultValue="0" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                            </div>
                             <div class="pagenation_listview p-3">
                        <asp:DataPager ID="DataPager1" runat="server" PageSize="10"
                            PagedControlID="lvdatatable">
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
                <div class="col-12 col-lg-5 pb-3 ">
                    <asp:Panel runat="server" Visible="false" CssClass="h-100" ID="ShowDiv">
                        <div class="card m-0 h-100">

                            <div class="card-header shadow_top d-flex align-items-center" style="min-height: 69px;">
                                <h5 class="mb-0">Create new Cost Category </h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-group mb-2">
                                    <label class="mb-0">Cost Category Name</label>
                                     <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                    <asp:TextBox runat="server" CssClass="form-control" ID="txtGroupName" autocomplete="off"></asp:TextBox>
                                     <asp:RequiredFieldValidator runat="server" ControlToValidate="txtGroupName" ForeColor="Red" ErrorMessage="Please give a group Name"></asp:RequiredFieldValidator>
                                </div>                               
                               <div class="form-group mb-2">
                                    <label class="mb-0">Type</label>
                                    <asp:DropDownList ID="ddlentrype" CssClass="form-control select" runat="server" AutoPostBack="true">
                                        <asp:ListItem Enabled="true" Text="Select Type" Value=""></asp:ListItem>
                                        <asp:ListItem Text="External" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Internal" Value="2"></asp:ListItem>                                       
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" ControlToValidate="ddlentrype" ForeColor="Red" ErrorMessage="Please select type of group"></asp:RequiredFieldValidator>

                                </div>
                               <%-- <asp:Panel runat="server" Visible="true" ID="pnlPrimary">
                                    <div class="form-group">
                                        <label class="mb-0">Primary Cost Category</label>
                                        <asp:DropDownList ID="selGroup" DataSourceID="SDSNature" CssClass="form-control select2" DataTextField="name" AutoPostBack="true" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text=" Select Primary Group" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ID="SDSNature" runat="server" SelectCommand="select id, name from groups where parent_id=0" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </asp:Panel>
                                <asp:Panel runat="server" Visible="true" ID="pnlMain">
                                    <div class="form-group">
                                        <label class="mb-0">Main Group</label>
                                        <asp:DropDownList ID="ddlgroup" DataSourceID="SDSgroup" CssClass="form-control select2" DataTextField="name" AutoPostBack="true" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text=" select Main Group" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ID="SDSgroup" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </asp:Panel>
                                <asp:Panel runat="server" Visible="true" ID="pnlSub">
                                    <div class="form-group">
                                        <label class="mb-0">Sub Group</label>
                                        <asp:DropDownList ID="selsubgroup" DataSourceID="SDSubgroup" CssClass="form-control select2" DataTextField="name" AutoPostBack="true" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text=" select Subgroup" Value="-1"></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ID="SDSubgroup" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id! =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </asp:Panel>--%>
                                <div class="form-group mb-0 float-right">
                                    <%-- <a class="btn btn-success " href="">Save</a>--%>

                                    <asp:LinkButton runat="server" ID="btnsave" CssClass="btn btn-primary mr-2" OnClick="btnsave_Click">Save</asp:LinkButton>
                                    <asp:LinkButton runat="server" ID="btncanel_update" CssClass="btn btn-secondary" OnClick="btncanel_update_Click">Cancel</asp:LinkButton>
                                </div>
                                <asp:Label ID="lbgroupid" runat="server"></asp:Label>
                                <asp:Label ID="lbprime" runat="server"></asp:Label>
                            </div>
                            <!--card body-->
                            <!-- <div class="card-footer bg-white border-top">
                    <div class="form-group mb-0 float-right">
                      <a class="btn btn-success " href="">Save</a>                           
                    </div>
                  </div> -->

                        </div>
                    </asp:Panel>
                    <asp:Panel runat="server" CssClass="h-100" Visible="true" ID="pnldetails">
                        <div class="card m-0 h-100">

                            <div class="card-header shadow_top d-flex align-items-center" style="min-height: 69px;">
                                <h5 class="mb-0">Cost Category Details</h5>
                            </div>

                            <div class="card-body">

                                <div class="table-responsive">
                                    <asp:Panel runat="server" Visible="true" ID="pnledit" CssClass="border-top">
                                        <asp:HiddenField ID="hidgroupId" ClientIDMode="Static" Value="0" runat="server" />
                                        <table class="table table-bordered" id="tbldetails">
                                            <tr>
                                                <td width="140px" class="font-weight-bold">Cost Category Name</td>
                                                <td>
                                                    <asp:Literal ID="ltrnamegroup" runat="server"></asp:Literal></td>
                                            </tr>
                                           <%-- <tr>
                                                <td width="140px" class="font-weight-bold">Number cost of centre</td>
                                                <td>
                                                    <asp:Literal ID="ltrtypeofgroup" runat="server"></asp:Literal></td>
                                            </tr>--%>                                                                                        
                                            <tr>
                                                <td colspan="2">
                                                    <div class="form-group mb-0 float-right">
                                                        <asp:LinkButton runat="server" ID="btnedit" CssClass="btn btn-primary py-1 text-white" Visible="true"  OnClick="btnedit_Click" CommandName="Edit">Edit</asp:LinkButton>
                                                    </div>
                                                </td>

                                            </tr>
                                        </table>
                                    </asp:Panel>
                                    <asp:Panel runat="server" Visible="false" ID="editdiv">
                                        <div class="form-group">
                                            <label class="mb-0">Cost Category Name</label>
                                            <asp:TextBox runat="server" CssClass="form-control" ID="txtgroup"></asp:TextBox>
                                        </div>                                       
                                        <div class="form-group">
                                            <label class="mb-0">Type</label>
                                            <asp:DropDownList ID="dlentrypeudate" CssClass="form-control" runat="server" AutoPostBack="true">
                                                <asp:ListItem Text="Select type" Value=""></asp:ListItem>
                                                <asp:ListItem Text="External" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Internal" Value="2"></asp:ListItem>
                                                <asp:ListItem Text="Custom" Value="3"></asp:ListItem>
                                            </asp:DropDownList>
                                        </div>
                                        <%--<div class="form-group">
                                            <asp:Panel runat="server" Visible="true" ID="pnlpimaryupdate">
                                                <label class="mb-0">Primary Cost Category</label>
                                                <asp:DropDownList ID="selGroupupdate" DataSourceID="SDSprimary" CssClass="form-control" DataTextField="name" DataValueField="id" AutoPostBack="true" AppendDataBoundItems="true" runat="server">
                                                    <asp:ListItem Text=" Select Primary Group" Value=""></asp:ListItem>
                                                </asp:DropDownList>
                                                <asp:SqlDataSource ID="SDSprimary" runat="server" SelectCommand="select id, name from groups where parent_id=0" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                            </asp:Panel>
                                        </div>
                                        <div class="form-group">
                                            <asp:Panel runat="server" Visible="true" ID="pnlmainupdate">
                                                <label class="mb-0">Main Cost Category</label>
                                                <asp:DropDownList ID="ddlgroupupdate" DataSourceID="SDSgroupmain" CssClass="form-control" DataTextField="name" DataValueField="id" AutoPostBack="true" AppendDataBoundItems="true" runat="server">
                                                    <asp:ListItem Text=" select Main Group" Value=""></asp:ListItem>
                                                </asp:DropDownList>
                                                <asp:SqlDataSource ID="SDSgroupmain" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                            </asp:Panel>
                                        </div>

                                        <div class="form-group">
                                            <asp:Panel runat="server" Visible="true" ID="pnlsubgroup">
                                                <label class="mb-0">Sub Cost Category</label>
                                                <asp:DropDownList ID="selsubgroupupdate" DataSourceID="SDSubgroupsub" CssClass="form-control " DataTextField="name" DataValueField="id" AutoPostBack="true" AppendDataBoundItems="true" runat="server">
                                                    <asp:ListItem Text=" select Subgroup" Value=""></asp:ListItem>
                                                </asp:DropDownList>
                                                <asp:SqlDataSource ID="SDSubgroupsub" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id! =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                            </asp:Panel>
                                        </div>--%>
                                        <div class="form-group mb-0 float-right">
                                            <asp:LinkButton runat="server" ID="btnupdate" CssClass="btn btn-primary" OnClick="btnupdate_Click">Save</asp:LinkButton>
                                            <asp:LinkButton runat="server" ID="btncancel" CssClass="btn btn-secondary" OnClick="btncancel_Click">cancel</asp:LinkButton>
                                            
                                        </div>
                                    </asp:Panel>
                                </div>
                            </div>
                        </div>
                    </asp:Panel>
                </div>
            </div>
      
    </section>
    <style>
        .btn-group.ml-3 .dropdown-toggle::after {
            display: none;
        }

        .btn-group.ml-3 .dropdown-toggle .fa-sliders-h {
            font-size: 20px;
        }
    </style>
</asp:Content>


