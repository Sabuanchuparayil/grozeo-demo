<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" AutoEventWireup="true" CodeBehind="Prospects.aspx.cs" Inherits="RetalineProAgent.Business.Prospects" %>


<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessCRM">CRM</a></li>
    <li class="breadcrumb-item active" aria-current="page">Prospects</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Prospects"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>--%> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                                    <div class="col-lg-6">
                                          <label class="form-control-label w-100 mb-1">Search: </label>
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <div class="d-flex">
                                            <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by store name, contact number & state" CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" style="height:33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                                        </div>
                                      </div>
                        

                        <%--<div class="col-sm-8">
                            <div class="float-right mt-4"><a href="/Business/WLeadSettings" type="button" class="btn btn-info pb-1 pt-1"><i class="icon ion-plus-circled mr-2"></i>Create Wholesaler Leads</a></div>
                        </div>--%>
                    </div>
            </div>
                <div class="card-body">
                    <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvRetailers" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDSRetailers">
                                    <Columns>
                                        <asp:BoundField HeaderText="Store Name" DataField="crpr_orgName" SortExpression="crpr_orgName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Contact Name" DataField="crpr_indContactperson" SortExpression="crpr_indContactperson" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Contact Number" DataField="crpr_indMobile" SortExpression="crpr_indMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Area" DataField="areaName" SortExpression="areaName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Created On" DataField="created_at" SortExpression="created_at" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Assigned To" DataField="crpr_Created" SortExpression="crpr_Created" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Created By" DataField="baName" SortExpression="baName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="RO" DataField="assignedROName" SortExpression="assignedROName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Prospect Status" DataField="prospectStatus" SortExpression="prospectStatus" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:TemplateField HeaderStyle-Width="50" HeaderText="Action" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="ion-android-menu"></i></a>
                                                <div class="dropdown-menu p-3" role="menu" style="">
                                                    <a href="javascript:void(0)" class=""  leadid='<%# Eval("leadId") %>' storeName='<%# Eval("crpr_orgName") %>' area='<%# Eval("areaName") %>'  selectedRO='<%# Eval("assignedRO") %>' onclick="loadDelegateLead(this)">Delegate Prospect</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="javascript:void(0)" class="" leadid='<%# Eval("leadId") %>' onclick="loadShedule(this)">View/Set Schedule</a>
                                                    <div class="dropdown-divider"></div>
                                                    <asp:LinkButton runat="server" ID="btnSentInvitation" prospectId='<%# Eval("id") %>' orgName='<%# Eval("crpr_orgName") %>' email='<%# Eval("crpr_orgEmail") %>' code='<%# Eval("invitationCode") %>' CssClass="btn-no-border" Text="Sent Invitation" OnClientClick="return confirm('Are you sure you want to send an invitation code?');" OnClick="btnSentInvitation_Click"/>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="javascript:void(0)" class="" leadid='<%# Eval("leadId") %>' prospectId='<%# Eval("id") %>' onclick="updateStages(this)">Update Stages</a>
                                                    <div class="dropdown-divider"></div>
                                                    <asp:LinkButton ID="btnAction" prospectid='<%# Eval("id") %>' OnClick="btnAction_Click" runat="server" Text="Communication"></asp:LinkButton>
                                                </div>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <%--<asp:BoundField HeaderText="Status" DataField="leadStatus" SortExpression="leadStatus" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>--%>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No retailers to list.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSRetailers" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT cp.id, crpr_orgName, cp.areaName, cp.crpr_orgEmail, cp.invitationCode, cp.leadId, assignedRO, crpr_indContactperson, crpr_indMobile, crpr_location, crpr_CreatedBy, ba.baName, crpr_gplace, crpr_CreatedOn, DATE_FORMAT(crpr_CreatedOn,'%d %b %Y') AS created_at, storeGroupId,
                                    cp.assignedRO, CASE WHEN cp.assignedRO > 0 THEN (SELECT roName FROM relationship_officer WHERE id = cp.assignedRO) ELSE '-' END AS assignedROName, 
                                    CASE WHEN crpr_CreatedFrom=1 THEN 'Admin' WHEN crpr_CreatedFrom=2 THEN 'Associate' WHEN crpr_CreatedFrom=3 THEN 'RO' END AS 
                                    crpr_Created, IF((invitationSent = 1),'Invitation Sent',(SELECT crmu_name FROM finascop_crm_status cs WHERE cs.crmu_id=cp.crmuId)) AS prospectStatus
                                    FROM  finascop_crm_prospect cp INNER JOIN business_associate ba ON ba.id=cp.baId WHERE ((@areaId > 0 and areaId = @areaId) or baId=@baId) 
                                    AND ifnull(storeGroupId, 0) <= 0 
                                    AND (trim(ifnull(@searchKey, '')) like '' or crpr_orgName like CONCAT('%', @searchKey, '%') or crpr_indMobile like CONCAT('%', @searchKey, '%') or crpr_gplace like CONCAT('%', @searchKey, '%')) ORDER BY crpr_orgName ASC" OnSelecting="SDSRetailerLeads_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="baId" DefaultValue="0" />
                                <asp:Parameter Name="areaId" DefaultValue="0" />
                                <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
              </div>
            </div>
          </div>
    <asp:HiddenField ID="hidleadId" runat="server" />
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
                        <asp:Button runat="server" ID="btnDeligateLead" ValidationGroup="ListRO" areaName='<%# Eval("areaname") %>' CssClass="btn btn-primary mr-2 bd-0" Text="Save" OnClick="btnDeligateLead_Click"  formnovalidate />
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
                        <h5 class="modal-title">Schedule Prospect Meetings</h5>
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

    <asp:HiddenField ID="hidProspectId" runat="server" />
    <div id="modalUpdateStage" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title">Update Prospect Stages</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="section-wrapper p-0 border-0">

                        <div class="row row-sm">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Status</label>
                                    <asp:DropDownList ID="selStatus" runat="server" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSStatus" DataTextField="NAME" AppendDataBoundItems="true" DataValueField="id"><asp:ListItem Text="Select status" Value=""></asp:ListItem></asp:DropDownList>
                                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSStatus" ProviderName="MySql.Data.MySqlClient"
                                    SelectCommand="SELECT id,NAME FROM prospect_stages  ORDER BY NAME">
                                    </asp:SqlDataSource>
                                    <asp:RequiredFieldValidator ValidationGroup="StatusUpdate" ControlToValidate="selStatus" ForeColor="Red" ErrorMessage="Select status" runat="server"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label w-100">Select Date</label>
                                    <asp:TextBox ID="txtDatePicker" runat="server" CssClass="form-control" TextMode="Date"/>
                                    <asp:RequiredFieldValidator ValidationGroup="StatusUpdate" ControlToValidate="txtDatePicker" ForeColor="Red" ErrorMessage="Select Date" runat="server"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group-sm">
                                    <label class="form-control-label">Remarks<span class="tx-danger">*</span></label>
                                    <asp:TextBox ID="txtRemrk" runat="server" CssClass="form-control"></asp:TextBox>
                                    <%--<asp:RequiredFieldValidator ValidationGroup="StatusUpdate" ControlToValidate="txtRemarks" ForeColor="Red" ErrorMessage="Remarks is required" runat="server"></asp:RequiredFieldValidator>--%>
                                </div>
                            </div>
                        </div>
                        <!--row-->

                    </div>
                    <!--section-wrapper-->

                    <div class="modal-btn">
                        <asp:Button runat="server" ID="btnStatusUpt" ValidationGroup="StatusUpdate" prospectId='<%# Eval("id") %>' CssClass="btn btn-primary mr-2 bd-0" Text="Save" OnClick="btnStatusUpt_Click"  formnovalidate />
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
     
    <asp:SqlDataSource runat="server" ID="SDSListDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT prospectId, DATE(crmc_Communication_Time) AS cDate, TIME(crmc_Communication_Time) AS cTime, crmc_remark 
FROM finascop_crm_communication WHERE prospectId = @prospectId">
                            <SelectParameters>
                                <asp:ControlParameter ControlID="hidProspectId" PropertyName="Value" Name="prospectId" />
                            </SelectParameters>
                        </asp:SqlDataSource>
     

    <div id="Create_communication" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title tx-dark">Create Prospect Communication</h5>
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
                                    <a href="/Business/Prospects" class="btn btn-secondary">Cancel</a>
                                    <asp:Button runat="server" ID="btnCreatePComm" OnClick="btnCreatePComm_Click" CssClass="btn btn-primary mx-2" Text="Save" ValidationGroup="CreateLCommunication"/>
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
            var area = $(obj).attr('area');
            var selectedRO = $(obj).attr('selectedRO');
            var leadId = $(obj).attr('leadid');
            $('#<%= hidleadId.ClientID %>').val(leadId);
            $('#<%= selRO.ClientID%>').val(selectedRO);
            $('#<%= txtLead.ClientID%>').val(lead);
            $('#<%= txtArea.ClientID%>').val(area);
            $('#modalSetDelegateLead').modal('show');
        }


        function loadShedule(obj) {
            var leadId = $(obj).attr('leadid');
            $('#<%= hidleadId.ClientID %>').val(leadId);
            $('#modalSetSchedule').modal('show');
        }

        function updateStages(obj) {
            var prospectId = $(obj).attr('prospectId');
            $('#<%= hidProspectId.ClientID %>').val(prospectId);
            $('#modalUpdateStage').modal('show');
        }

        $(document).ready(function () {
            $('#Create_communication').on('hidden.bs.modal', function () {
                // Show the first popup
                $('#Communication').modal('show');
            });
        });
        function communicationsection(obj) {
            var prospectId = $(obj).attr('leadid');
            $('#<%= hidProspectId.ClientID %>').val(prospectId);
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
