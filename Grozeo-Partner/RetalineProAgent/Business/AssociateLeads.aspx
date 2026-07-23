<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Leads" AutoEventWireup="true" CodeBehind="AssociateLeads.aspx.cs" Inherits="RetalineProAgent.AssociateLeads" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessCRM">CRM</a></li>
    <li class="breadcrumb-item active" aria-current="page">Leads</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Leads"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>--%> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row row-sm">
                        <div class="col-12 col-lg-8 mb-2 mb-sm-0">

                            <nav class="navbar float-non float-lg-left mb-2 mb-lg-0 navbar-expand-lg bg-transparent p-0 justify-content-start align-items-end">
                                <a class="navbar-brand d-lg-none tx-dark tx-14" href="#">Filter by</a>
                                <button class="navbar-toggler p-0 " type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                                        <i class="fa fa-sliders" aria-hidden="true"></i>
                                    </span>
                                </button>


                                <div class=" collapse navbar-collapse flex-wrap" id="navbarSupportedContent">
                                    <ul class="navbar-nav mr-auto pt-2 pt-lg-0">
                                        <li class="nav-item ml-0 mr-lg-1 my-1 my-lg-0">
                                            <asp:LinkButton ID="lbtnRetailer" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary active">Retailer <span class="sr-only">(current)</span></asp:LinkButton>
                                        </li>
                                        <li class="nav-item mx-0 mx-lg-1 my-1 my-lg-0">
                                            <asp:LinkButton ID="lbtnWholsaler" runat="server" typeid="2" OnClick="btnFilterType_Click" CssClass="btn btn-block btn-outline-primary">Wholsaler</asp:LinkButton>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <div class="">
                                <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <div class="d-flex pl-0 pl-lg-2">
                                <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name, number & state" CssClass="form-control" autocomplete="off"></asp:TextBox>
                                <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary d-inline-block w-auto ml-2" runat="server">Search</asp:LinkButton>
                            </div>
                            </div>
                        </div>
                        <div class="col-lg-4 d-flex justify-content-lg-end mt-2 mt-lg-0">
                            <a href="/Business/AsstLeadSettings" type="button" class="btn btn-primary"><i class="icon ion-plus-circled mr-2"></i>Create Leads</a>
                        </div>
                    </div>
            </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvLead" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvLead_DataBound" DataSourceID="SDSRetailerLeads">
                                    <Columns>
                                        <asp:BoundField HeaderText="Store Name" DataField="crle_orgName" SortExpression="crle_orgName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Contact Name" DataField="crle_indContactperson" SortExpression="crle_indContactperson" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Contact Number" DataField="crle_indMobile" SortExpression="crle_indMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Location" DataField="areaname" SortExpression="areaname" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Created From" DataField="createdFrom" SortExpression="createdFrom" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Assigned To" DataField="crle_Created" SortExpression="crle_Created" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="RO" DataField="assignedROName" SortExpression="assignedROName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <%--<asp:BoundField HeaderText="Created By" DataField="baName" SortExpression="baName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>--%>
                                        <asp:BoundField HeaderText="Status" DataField="leadStatus" SortExpression="leadStatus" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:TemplateField HeaderStyle-Width="50" HeaderText="Action" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="ion-android-menu"></i></a>
                                                <div class="dropdown-menu p-3" role="menu" style="">
                                                    <a href="javascript:void(0)" class=""  leadid='<%# Eval("id") %>' storeName='<%# Eval("crle_orgName") %>' areaname='<%# Eval("areaname") %>' selectedRO='<%# Eval("assignedRO") %>' onclick="loadDelegateLead(this)">Delegate Lead</a>
                                                    <div class="dropdown-divider"></div>
                                                    <asp:LinkButton runat="server" ID="lbtnUpgradeToProspect" CssClass="btn-no-border" Text="Upgrade to Prospect" OnClientClick='<%# "return confirmUpgrade(" + Eval("id") + ");" %>' OnClick="lbtnUpgradeToProspect_Click"/>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="javascript:void(0)" class="" leadid='<%# Eval("id") %>' onclick="loadShedule(this)">View/Set Schedule</a>
                                                    <div class="dropdown-divider"></div>
                                                    <asp:LinkButton ID="btnAction" leadid='<%# Eval("id") %>' OnClick="btnAction_Click" runat="server" Text="Communication"></asp:LinkButton>
                                                     <div class="dropdown-divider"></div>
                                                    <asp:LinkButton runat="server" ID="btnedit" leadid='<%# Eval("id") %>' OnClick="btnedit_Click" Text="Edit"></asp:LinkButton>
                                                    <%--<a href="javascript:void(0)" class="" leadid='<%# Eval("id") %>' onclick="communicationsection(this)">Communication</a>--%>
                                                </div>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <%--<asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Business/RLeadSettings" DataNavigateUrlFields="id" DataNavigateUrlFormatString="~/Business/RLeadSettings?id={0}" />--%>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No leads created.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSRetailerLeads" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT cl.id, ba.baName, cl.assignedRO, crle_orgName,crle_location, crle_indContactperson, crle_orgContactNo, crle_CreatedBy,
                                    cl.crmuId,IF((cl.crmuId > 0),(SELECT crmu_name FROM finascop_crm_status cs WHERE cs.crmu_id=cl.crmuId),'Lead')
                                    AS leadStatus, crle_gplace, crle_indMobile, cl.assignedRO,
                                    CASE WHEN cl.assignedRO > 0 THEN (SELECT roName FROM relationship_officer WHERE id = cl.assignedRO) ELSE '-' END AS assignedROName, 
                                    (SELECT areaName FROM area_entries WHERE id=cl.areaId) AS  areaname, CASE WHEN crle_CreatedFrom=1 THEN 'Admin' WHEN crle_CreatedFrom=2 THEN 'Associate' WHEN crle_CreatedFrom=3 THEN 'RO' END AS 
                                    createdFrom, CASE WHEN crle_CreatedFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = crle_CreatedBy) 
                                    WHEN crle_CreatedFrom =2 THEN (SELECT baName FROM business_associate WHERE id = crle_CreatedBy) 
                                    WHEN crle_CreatedFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = crle_CreatedBy) END AS crle_Created
                                    FROM  finascop_crm_lead cl
                                    LEFT JOIN finascop_crm_prospect ON leadId=cl.id
                                    INNER JOIN business_associate ba ON ba.id=cl.baId
                                    WHERE cl.crmuId NOT IN (3,7) AND ((@areaId > 0 and cl.areaId = @areaId) or cl.baId=@baId) AND (ifnull(@filterType, 0) = 0 
                                    or (@filterType = 1 and crle_type=1 and cl.crmuId NOT IN (3,7))  
                                    or (@filterType = 2 and crle_type=3 and cl.crmuId NOT IN (3,7)))
                                    AND (trim(ifnull(@searchKey, '')) like '' or crle_orgName like CONCAT('%', @searchKey, '%') or crle_gplace like CONCAT('%', @searchKey, '%') or crle_indMobile like CONCAT('%', @searchKey, '%')) ORDER BY crle_orgName ASC" OnSelecting="SDSLeads_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="baId" DefaultValue="0" />
                                <asp:Parameter Name="areaId" DefaultValue="0" />
                                <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
              </div>
            </div>
          </div>
    <asp:HiddenField ID="hidleadId" runat="server" />
    <asp:HiddenField ID="hidAreaName" runat="server" />
    <div id="modalSetDelegateLead" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title">Delegate</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="section-wrapper p-0 border-0">

                        <div class="row row-sm">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Lead</label>
                                    <asp:TextBox ID="txtLead" runat="server" Enabled="false" CssClass="form-control"></asp:TextBox>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Area</label>
                                    <asp:TextBox ID="txtArea" Enabled="false" runat="server" CssClass="form-control"></asp:TextBox>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group-sm">
                                    <label class="form-control-label">RO <span class="tx-danger">*</span></label>
                                    <asp:DropDownList ID="selRO" runat="server" AutoPostBack="false" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSRO" DataTextField="roName" DataValueField="id"></asp:DropDownList>
                                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSRO" ProviderName="MySql.Data.MySqlClient"
                                        SelectCommand="SELECT id,roName FROM relationship_officer WHERE roArea = @areaId ORDER BY roName"
                                        OnSelecting="SDSRO_Selecting">
                                        <SelectParameters>
                                            <asp:Parameter Name="areaId" />
                                        </SelectParameters>
                                    </asp:SqlDataSource>
                                    <asp:RequiredFieldValidator ValidationGroup="ListRO" ControlToValidate="selRO" ForeColor="Red" ErrorMessage="Select RO" runat="server"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                        </div>
                        <!--row-->

                    </div>
                    <!--section-wrapper-->

                    <div class="modal-btn mt-3">
                        <asp:Button runat="server" ID="btnDeligateLead" ValidationGroup="ListRO" CssClass="btn btn-primary mr-2 bd-0" Text="Save" OnClick="btnDeligateLead_Click"  formnovalidate />
                        <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>
                    </div>

                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->


    <div id="modalSetSchedule" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title">Schedule Lead Meetings</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="section-wrapper p-0 border-0">

                        <div class="row row-sm">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Select Date</label>
                                    <asp:TextBox ID="txtDate" runat="server" CssClass="form-control" TextMode="Date"/>
                                    <asp:RequiredFieldValidator ValidationGroup="InsertSchedule" ControlToValidate="txtDate" ForeColor="Red" ErrorMessage="Select Date" runat="server"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Time</label>
                                    <asp:DropDownList ID="ddlTime" runat="server" CssClass="form-control select2"></asp:DropDownList>
                                    <asp:RequiredFieldValidator ValidationGroup="InsertSchedule" ControlToValidate="ddlTime" ForeColor="Red" ErrorMessage="Select Time" runat="server"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group-sm">
                                    <label class="form-control-label">Remarks<span class="tx-danger">*</span></label>
                                    <asp:TextBox ID="txtRemarks" runat="server" CssClass="form-control"></asp:TextBox>
                                    <%--<asp:RequiredFieldValidator ValidationGroup="InsertSchedule" ControlToValidate="txtRemarks" ForeColor="Red" ErrorMessage="Remarks is required" runat="server"></asp:RequiredFieldValidator>--%>
                                </div>
                            </div>
                        </div>
                        <!--row-->

                    </div>
                    <!--section-wrapper-->

                    <div class="modal-btn mt-3">
                        <asp:Button runat="server" ID="btnSchedule" ValidationGroup="InsertSchedule" leadid='<%# Eval("id") %>' CssClass="btn btn-primary mr-2 bd-0" Text="Save" OnClick="btnSchedule_Click"  formnovalidate />
                        <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>
                    </div>

                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <div id="Communication" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
            <div class="modal-content bd-0 tx-14 ">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <div class="btnsec d-flex w-100">
                            <input type="button" name="" value="Create New" id="" class="btn btn-primary bd-0 mr-2" data-toggle="modal" data-target="#Create_communication">
                            <a href="javascript:void(0)" class="btn btn-outline-primary mx-2">Print</a>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="section-wrapper p-0 border-0">
                        <div class="table-responsive">
                            <table id="communicationTable" class="table table-bordered table-head-fixed" cellspacing="0" border="1">
                                <thead class="custom-header">
                                    <tr>
                                        <th style="padding:0.75rem; font-size: 14px; text-align:left; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;">Date</th>
                                        <th style="padding:0.75rem; font-size: 14px; text-align:left; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;">Time</th>
                                        <th style="padding:0.75rem; font-size: 14px; text-align:left; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <asp:Repeater ID="rptDetails" runat="server" DataSourceID="SDSListDetails">
                                    <ItemTemplate>
                                        <tr>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("cDate") %></td>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("cTime") %></td>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("crmc_remark") %></td>
                                        </tr>
                                    </ItemTemplate>
                                    <FooterTemplate>
                                        <tr>
                                            <td colspan="4" style="padding: 0.75rem; font-size: 14px; font-family:'Poppins', 'Helvetica Neue', Arial, sans-serif;">
                                                <asp:Label ID="lblEmptyData" runat="server" Visible='<%# (rptDetails).Items.Count == 0 %>' Text="No communication created." /></td>
                                        </tr>

                                    </FooterTemplate>
                                </asp:Repeater>
                            </tbody>
                            </table>
                        </div>
                    </div>
                    <!--section-wrapper-->
                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->
     <asp:HiddenField ID="hiddenLeadId" runat="server" />
    <asp:SqlDataSource runat="server" ID="SDSListDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT crle_id, DATE_FORMAT(crmc_Communication_Time, '%d %M %Y') AS cDate, TIME(crmc_Communication_Time) AS cTime, crmc_remark 
        FROM finascop_crm_communication WHERE crle_id = @leadId">
        <SelectParameters>
            <asp:ControlParameter ControlID="hidleadId" PropertyName="Value" Name="leadId" DefaultValue="0" />
    </SelectParameters>
    </asp:SqlDataSource>
    

    <!-- modal -->
    <div id="Create_communication" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title tx-dark">Create Lead Communication</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="section-wrapper p-0 border-0">
                       <div class="row">
                            <div class="col-12 col-sm-6 form-group mb-2">
                                <%--<label class="form-control-label mb-1 w-100 tx-dark" for="">Select Action</label>--%>
                                <label class="form-control-label mb-1 w-100 tx-dark">Select Action<span class="tx-danger">*</span></label>
                                <asp:DropDownList ID="selAction" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSAction" DataTextField="crma_name" AppendDataBoundItems="true" DataValueField="crma_id"><asp:ListItem Text="Select action" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSAction" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT crma_id,crma_name FROM finascop_crm_action">
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateLCommunication" ControlToValidate="selAction" ForeColor="Red" ErrorMessage="Select action" runat="server"></asp:RequiredFieldValidator>
                            </div>
                            <div class="col-12 col-sm-6 form-group mb-2">
                                <%--<label class="form-control-label mb-1 w-100 tx-dark" for="">Mode of Contact</label>--%>
                                <label class="form-control-label mb-1 w-100 tx-dark">Mode of Contact<span class="tx-danger">*</span></label>
                                <asp:DropDownList ID="selMode" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSMode" DataTextField="crmm_name" AppendDataBoundItems="true" DataValueField="crmm_id"><asp:ListItem Text="Select mode" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSMode" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT crmm_id,crmm_name FROM finascop_crm_action_mode">
                    </asp:SqlDataSource>
    <asp:RequiredFieldValidator ValidationGroup="CreateLCommunication" ControlToValidate="selMode" ForeColor="Red" ErrorMessage="Select mode" runat="server"></asp:RequiredFieldValidator>
                            </div>
                            <div class="col-12 form-group mb-3">
                                <label class="form-control-label mb-1 w-100 tx-dark">Remarks</label>
                                <asp:TextBox ID="txtCommRemarks" runat="server" CssClass="form-control" Height="50px" TextMode="MultiLine" />
                                <%--<textarea class="w-100" rows="3"></textarea>--%>
                            </div>
                            <div class="col-12 form-group">
                                <div class="border border-radius">
                                    <input type="file" class="form-control-file" id="">
                                </div>
                            </div>
                            <div class="col-12 ">
                                <div class="btnsec d-flex justify-content-center w-100">
                                    <%--<a href="/Business/AssociateLeads" class="btn btn-secondary">Cancel</a>--%>
                                    <button type="button" class="btn btn-secondary" aria-label="Cancel" data-dismiss="modal">Cancel</button>
                                    <asp:Button runat="server" ID="btnCreateLComm" OnClick="btnCreateLComm_Click" CssClass="btn btn-primary mx-2" Text="Save" ValidationGroup="CreateLCommunication"/>
                                    <%--<input type="submit" name="" value="Save" id="" class="btn btn-primary mx-2">--%>
                                </div>
                            </div>
                       </div>
                    </div>
                    <!--section-wrapper-->
                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <div id="modalLeadEmail" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title">Enter Email Id</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="section-wrapper p-0 border-0">

                        <div class="row row-sm">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Email Id</label>
                                    <%--<asp:TextBox ID="txtLeadEmail" runat="server" CssClass="form-control"/>--%>
                                    <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                    <asp:TextBox ID="txtLeadEmail" runat="server" CssClass="form-control" placeholder="Enter Email ID" TextMode="Email" autocomplete="nofill"/>
                                    <asp:RequiredFieldValidator ValidationGroup="InsertEmail" ControlToValidate="txtLeadEmail" ForeColor="Red" ErrorMessage="Select Date" runat="server"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                        </div>
                        <!--row-->

                    </div>
                    <!--section-wrapper-->

                    <div class="modal-btn mt-3">
                        <asp:Button runat="server" ID="btnLeadEmail" ValidationGroup="InsertEmail" leadid='<%# Eval("id") %>' CssClass="btn btn-primary mr-2 bd-0" Text="Save" OnClick="btnLeadEmail_Click"  formnovalidate />
                        <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>
                    </div>

                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->


    <div id="modaldemo5" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-danger mg-b-20"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success tx-semibold mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <script type="text/javascript">
        $("input[data-bootstrap-switch], tb[data-bootstrap-switch] input[type=checkbox]").each(function () {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('tb[data-bootstrap-switch] input[type=checkbox]').on('switchChange.bootstrapSwitch', function (e, state) {
            $(this).prop('checked', !state);
            $(this).trigger('click');
        });

        function loadDelegateLead(obj) {
            var lead = $(obj).attr('storeName');
            var area = $(obj).attr('areaname');
            var selectedRO = $(obj).attr('selectedRO');
            var leadId = $(obj).attr('leadid');
            $('#<%= hidleadId.ClientID %>').val(leadId);
            $('#<%= selRO.ClientID%>').val(selectedRO);
            $('#<%= txtLead.ClientID%>').val(lead);
            $('#<%= txtArea.ClientID%>').val(area);
            $('#<%= hidAreaName.ClientID %>').val(area);
            $('#modalSetDelegateLead').modal('show');
        }

        function confirmUpgrade(leadId) {
            if (confirm('Are you sure you want to upgrade this lead to Prospect?')) {
                var hiddenField = document.getElementById('<%= hidleadId.ClientID %>');
                hiddenField.value = leadId;
                $('#modalLeadEmail').modal('hide'); // Hide the popup
                return true; // Proceed with postback
            } else {
                alert('Upgrade to Prospect process cancelled.'); // Show a message for cancellation
                return false; // Cancel the postback
            }
        }

        function loadShedule(obj) {
            var leadId = $(obj).attr('leadid');
            $('#<%= hidleadId.ClientID %>').val(leadId);
            $('#modalSetSchedule').modal('show');
        }
       

        $(document).ready(function () {
            $('#Create_communication').on('hidden.bs.modal', function () {
                // Show the first popup
                $('#Communication').modal('show');
            });
        });
        function communicationsection(obj) {
            var leadId = $(obj).attr('leadid');
            $('#<%= hidleadId.ClientID %>').val(leadId);
            $('#Communication').modal('show');
        }

        function cancelSecondPopup() {
            $('#Create_communication').modal('hide');
        }

        function clearSecondPopupForm() {
            // Clear input values in the form fields
            $('#selAction').val('');
            $('#selMode').val('');
            $('#txtCommRemarks').val('');
        }

        function returnToFirstPopup() {
            $('#Create_communication').modal('hide');
            $('#Communication').modal('show');
        }
        function printCommunicationTable() {
            var popupContent = document.getElementById('communicationTable').outerHTML;
            var popupWindow = window.open('', '_blank');
            popupWindow.document.open();
            popupWindow.document.write('<html><head><title>Communication Table</title></head><body>' + popupContent + '</body></html>');
            popupWindow.document.close();
            popupWindow.print();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var printButton = document.querySelector('#Communication .btn.btn-outline-primary');
            if (printButton) {
                printButton.addEventListener('click', printCommunicationTable);
            }
        });

        function clearTextBox() {
            document.getElementById('<%= txtLeadEmail.ClientID %>').value = '';
        }
    </script>

    

    <style>
        .btn-no-border {
            background-color: transparent;
            border: none;
            padding: 0;
            margin: 0;
        }
       .modal-backdrop.show + .modal-backdrop.show,
        .modal-backdrop.show + div + .modal-backdrop.show{
            z-index: 1050;
        }
    </style>
</asp:Content>
