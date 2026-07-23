<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Manage Group" CodeBehind="GroupManagement.aspx.cs" Inherits="RetalineProAgent.Finance.GroupCreation" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Manage Group</h6>
     <p class="mb-0">You can see Manage Group here</p>
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
                                        <asp:LinkButton ID="lbnSearch" runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600" >
                                          <i class="fa fa-search"></i>
                                        </div>
                                        </asp:LinkButton>
                                        <%--</a>--%>
                                    </div>

                                    <script type="text/javascript">
                                        document.addEventListener("DOMContentLoaded", function () {
                                            document.getElementById('<%= txtSearch.ClientID %>').addEventListener("keydown", function (event) {
                                                if (event.key === "Enter") {
                                                    event.preventDefault(); // Prevent form submission
                                                    __doPostBack('<%= lbnSearch.UniqueID %>', '');
                                                }
                                            });
                                        });
                                    </script>

                                    <div class="btn-group ml-3">
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false" >
                                            <!-- Flilter -->
                                            <i class="fa fa-sliders"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="GroupManagement?ntid=1">Asset</a></li>
                                            <li><a class="dropdown-item" href="GroupManagement?ntid=2">Liability</a></li>
                                            <li><a class="dropdown-item" href="GroupManagement?ntid=3">Income</a></li>
                                            <li><a class="dropdown-item" href="GroupManagement?ntid=4">Expenditure</a></li>
                                            <li><a class="dropdown-item" href="GroupManagement?ntid=5">Branch/Divisions</a></li>
                                            <li><a class="dropdown-item" href="GroupManagement?ntid">All Items</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered gridview_table" rules="all" id="cpMainContent_gvGroup"
                                    style="border-style: Solid; border-collapse: collapse;" cellspacing="0" border="1">
                                    <tbody>
                                        <tr class="border-top">
                                            <th width="52%">Group Name</th>
                                            <th width="17%">Group Type</th>
                                            <th width="43%">parent Group</th>
                                        </tr>
                                        <asp:ListView ID="lvdatatable" runat="server" DataSourceID="SDSGroupCreation" OnDataBinding="lvdatatable_DataBinding" OnDataBound="lvdatatable_DataBound">
                                            <ItemTemplate>
                                                <tr>
                                                    <td align="left">
                                                        <asp:LinkButton ID="btnhide" dataid='<%# Eval("id") %>' OnClick="btnhide_Click" Text='<%# Eval("name")%>' runat="server"></asp:LinkButton></td>
                                                    <td align="left"><%# Eval("GroupType")%></td>
                                                    <td><%# Eval("parentname")%></td>

                                                </tr>
                                            </ItemTemplate>
                                        </asp:ListView>                                       
                                    </tbody>
                                    </table>                                   
                                <asp:SqlDataSource runat="server" ID="SDSGroupCreation" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                                    SelectCommand="Select g.id, [name], account_types_id,(select s.name from groups s where s.id= g.parent_id) as parentname, parent_id,(case when parent_id=0 then 'Primary Group' when parent_id in(select [id] from  [groups] where parent_id =0 ) then 'Main Group ' else 'Sub Group' end ) as GroupType, isSystem, ac.id as natureid, ac.nature from [groups] g inner join [account_types] ac on g.account_types_id = ac.id where   (trim(@search) like '' or g.name like CONCAT('%', @search, '%')) AND (@nature <= 0 or account_types_id=@nature) order by name  ">
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
                                <h5 class="mb-0">Create new group</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-group mb-2">
                                    <label class="mb-0">Group Name</label>
                                     <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                    <asp:TextBox runat="server" CssClass="form-control" ID="txtGroupName" autocomplete="off"></asp:TextBox>
                                     <asp:RequiredFieldValidator runat="server" ValidationGroup="Savegroup" ControlToValidate="txtGroupName" ForeColor="Red" ErrorMessage="Please give a group Name"></asp:RequiredFieldValidator>
                                </div>

                                <div class="form-group mb-2">
                                    <label class="mb-0">Nature of Group</label>
                                    <asp:DropDownList ID="dlnature" CssClass="form-control select" runat="server" AutoPostBack="true">
                                        <asp:ListItem Enabled="true" Text="Select Nature of Group" Value=""></asp:ListItem>
                                        <asp:ListItem Text="Assets" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Liability" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Income" Value="3"></asp:ListItem>
                                        <asp:ListItem Text="Expenditure" Value="4"></asp:ListItem>
                                        <asp:ListItem Text="Branch/Divisions" Value="5"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" ValidationGroup="Savegroup" ControlToValidate="dlnature" ForeColor="Red" ErrorMessage="Please select Nature of Group"></asp:RequiredFieldValidator>

                                </div>

                                <div class="form-group mb-2">
                                    <label class="mb-0">Type of Group</label>
                                    <asp:DropDownList ID="ddlentrype" CssClass="form-control select" runat="server" AutoPostBack="true">
                                        <asp:ListItem Enabled="true" Text="Select group type" Value=""></asp:ListItem>
                                        <asp:ListItem Text="Main Group" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Group Under Main Group" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="Group Under Sub Group" Value="3"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" ValidationGroup="Savegroup" ControlToValidate="ddlentrype" ForeColor="Red" ErrorMessage="Please select type of group"></asp:RequiredFieldValidator>

                                </div>
                                <asp:Panel runat="server" Visible="true" ID="pnlPrimary">
                                    <div class="form-group mb-2">
                                        <label class="mb-0">Primary Group</label>
                                        <asp:DropDownList ID="selGroup" DataSourceID="SDSNature" CssClass="form-control select2" DataTextField="name" AutoPostBack="true" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text=" Select Primary Group" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ID="SDSNature" runat="server" SelectCommand="select id, name from groups where parent_id=0" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </asp:Panel>
                                <asp:Panel runat="server" Visible="true" ID="pnlMain">
                                    <div class="form-group  mb-2">
                                        <label class="mb-0">Main Group</label>
                                        <asp:DropDownList ID="ddlgroup" DataSourceID="SDSgroup" CssClass="form-control select2" DataTextField="name" AutoPostBack="true" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text=" select Main Group" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ID="SDSgroup" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </asp:Panel>
                                <asp:Panel runat="server" Visible="true" ID="pnlSub">
                                    <div class="form-group mb-3">
                                        <label class="mb-0">Sub Group</label>
                                        <asp:DropDownList ID="selsubgroup" DataSourceID="SDSubgroup" CssClass="form-control select2" DataTextField="name" AutoPostBack="true" DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text=" select Subgroup" Value="-1"></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ID="SDSubgroup" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id! =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </asp:Panel>
                                <div class="form-group float-right">
                                    <%-- <a class="btn btn-success " href="">Save</a>--%>

                                    <asp:LinkButton runat="server" ID="btnsave" CssClass="btn btn-primary mr-2" ValidationGroup="Savegroup" OnClick="btnsave_Click">Save</asp:LinkButton>
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

                            <div class="card-header shadow_top d-flex align-items-center" style="height: 69px;">
                                <h5 class="mb-0">Group Details</h5>
                            </div>

                            <div class="card-body">

                                <div class="table-responsive">
                                    <asp:Panel runat="server" Visible="true" ID="pnledit" CssClass="border-top">
                                        <asp:HiddenField ID="hidgroupId" ClientIDMode="Static" Value="0" runat="server" />
                                        <table class="table table-bordered" id="tbldetails">
                                            <tr>
                                                <td width="140px" class="font-weight-bold">Group Name</td>
                                                <td>
                                                    <asp:Literal ID="ltrnamegroup" runat="server"></asp:Literal></td>
                                            </tr>
                                            <tr>
                                                <td width="140px" class="font-weight-bold">Type of Group</td>
                                                <td>
                                                    <asp:Literal ID="ltrtypeofgroup" runat="server"></asp:Literal></td>
                                            </tr>
                                            <tr>
                                                <td width="140px" class="font-weight-bold">Nature of Group</td>
                                                <td>
                                                    <asp:Literal ID="ltrnatureofgroup" runat="server"></asp:Literal></td>
                                            </tr>
                                            <tr>
                                                <td width="140px" class="font-weight-bold">Primary Group</td>
                                                <td>
                                                    <asp:Literal ID="ltrprimarygroup" runat="server"></asp:Literal></td>
                                            </tr>
                                            <tr>
                                                <td width="140px" class="font-weight-bold">Main Group</td>
                                                <td>
                                                    <asp:Literal ID="ltrmaingroup" runat="server"></asp:Literal></td>
                                            </tr>
                                            <tr>
                                                <td width="140px" class="font-weight-bold">Sub Group</td>
                                                <td>
                                                    <asp:Literal ID="ltrsubgroup" runat="server"></asp:Literal></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="form-group mb-0 float-right">
                                                        <asp:LinkButton runat="server" ID="btnedit" CssClass="btn btn-primary py-1 text-white" Visible="true" Enabled="false" OnClick="btnedit_Click" CommandName="Edit">Edit</asp:LinkButton>
                                                    </div>
                                                </td>

                                            </tr>
                                        </table>
                                    </asp:Panel>
                                    <asp:Panel runat="server" Visible="false" ID="editdiv">
                                        <div class="form-group">
                                            <label class="mb-0">Group Name</label>
                                            <asp:TextBox runat="server" CssClass="form-control" ID="txtgroup"></asp:TextBox>
                                        </div>

                                        <div class="form-group">
                                            <label class="mb-0">Nature of Group</label>
                                            <asp:DropDownList ID="dlnatureupdate" CssClass="form-control select" runat="server" AutoPostBack="true">
                                                <asp:ListItem Enabled="true" Text="Select Nature of Group" Value="-1"></asp:ListItem>
                                                <asp:ListItem Text="Assets" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Liability" Value="2"></asp:ListItem>
                                                <asp:ListItem Text="Income" Value="3"></asp:ListItem>
                                                <asp:ListItem Text="Expenditure" Value="4"></asp:ListItem>
                                                <asp:ListItem Text="Branch/Divisions" Value="5"></asp:ListItem>
                                            </asp:DropDownList>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-0">Type of Group</label>
                                            <asp:DropDownList ID="dlentrypeudate" CssClass="form-control " runat="server" AutoPostBack="true">
                                                <asp:ListItem Text="Select group type" Value=""></asp:ListItem>
                                                <asp:ListItem Text="Main Group" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Group Under Main Group" Value="2"></asp:ListItem>
                                                <asp:ListItem Text="Group Under Sub Group" Value="3"></asp:ListItem>
                                            </asp:DropDownList>
                                        </div>
                                        <div class="form-group">
                                            <asp:Panel runat="server" Visible="true" ID="pnlpimaryupdate">
                                                <label class="mb-0">Primary Group</label>
                                                <asp:DropDownList ID="selGroupupdate" DataSourceID="SDSprimary" CssClass="form-control" DataTextField="name" DataValueField="id" AutoPostBack="true" AppendDataBoundItems="true" runat="server">
                                                    <asp:ListItem Text=" Select Primary Group" Value=""></asp:ListItem>
                                                </asp:DropDownList>
                                                <asp:SqlDataSource ID="SDSprimary" runat="server" SelectCommand="select id, name from groups where parent_id=0" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                            </asp:Panel>
                                        </div>
                                        <div class="form-group">
                                            <asp:Panel runat="server" Visible="true" ID="pnlmainupdate">
                                                <label class="mb-0">Main Group</label>
                                                <asp:DropDownList ID="ddlgroupupdate" DataSourceID="SDSgroupmain" CssClass="form-control" DataTextField="name" DataValueField="id" AutoPostBack="true" AppendDataBoundItems="true" runat="server">
                                                    <asp:ListItem Text=" select Main Group" Value=""></asp:ListItem>
                                                </asp:DropDownList>
                                                <asp:SqlDataSource ID="SDSgroupmain" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                            </asp:Panel>
                                        </div>

                                        <div class="form-group">
                                            <asp:Panel runat="server" Visible="true" ID="pnlsubgroup">
                                                <label class="mb-0">Sub Group</label>
                                                <asp:DropDownList ID="selsubgroupupdate" DataSourceID="SDSubgroupsub" CssClass="form-control " DataTextField="name" DataValueField="id" AutoPostBack="true" AppendDataBoundItems="true" runat="server">
                                                    <asp:ListItem Text=" select Subgroup" Value=""></asp:ListItem>
                                                </asp:DropDownList>
                                                <asp:SqlDataSource ID="SDSubgroupsub" runat="server" SelectCommand="select [id],[name],parent_id from  [groups] where parent_id in (select [id] from  [groups] where parent_id! =0 ) order by name" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                            </asp:Panel>
                                        </div>
                                        <div class="form-group mb-0 float-right">
                                            <asp:LinkButton runat="server" ID="btncancel" CssClass="btn btn-danger" OnClick="btncancel_Click">cancel</asp:LinkButton>
                                            <asp:LinkButton runat="server" ID="btnupdate" CssClass="btn btn-success" OnClick="btnupdate_Click">Save</asp:LinkButton>
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


