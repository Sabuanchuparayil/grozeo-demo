<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Relationship Officer" AutoEventWireup="true" CodeBehind="RelationshipOfficer.aspx.cs" Inherits="RetalineProAgent.RelationshipOfficer" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/Resources">Resources</a></li>
    <li class="breadcrumb-item active" aria-current="page">Relationship Officer</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Relationship Officer"></asp:Literal>
        <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>--%> 
    </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">


                        <%--<li class="nav-item dropdown mx-1">
                      <a class="nav-link dropdown-toggle btn btn-block btn-outline-primary btn-sm p-1 px-2 <%= (new int[] { 5,6,7,8,9}).Contains(FilterType)?"active":"" %>" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Other Filters
                      </a>
                      <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <asp:LinkButton ID="lbtnPendingPacking" runat="server" typeid="5" OnClick="btnFilterType_Click" CssClass="dropdown-item">Pending for Packing</asp:LinkButton>
                        <asp:LinkButton ID="lbtnPaymentFailed" runat="server" typeid="6" OnClick="btnFilterType_Click" CssClass="dropdown-item">Payment Failed</asp:LinkButton>
                        <asp:LinkButton ID="lbtnPickupFailed" runat="server" typeid="7" OnClick="btnFilterType_Click" CssClass="dropdown-item">Pickup Failed</asp:LinkButton>
                        <asp:LinkButton ID="lbtnDeliveryFailed" runat="server" typeid="8" OnClick="btnFilterType_Click" CssClass="dropdown-item">Delivery Failed</asp:LinkButton>
                        <asp:HyperLink runat="server" CssClass="dropdown-item" NavigateUrl="/SpotReturn">Returns</asp:HyperLink>
                      <div class="dropdown-divider"></div>
                        <asp:LinkButton ID="lbtnCancelled" runat="server" typeid="9" OnClick="btnFilterType_Click" CssClass="dropdown-item">Cancelled Orders</asp:LinkButton>

                      </div>
                    </li>--%>


                        <div class="col-lg-4">
                            <label class="form-control-label w-100 mb-1">Search: </label>
                            <input type="text" style="display: none" />
                            <input type="password" style="display: none" />
                            <div class="d-flex">
                                <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name & number" CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" Style="height: 33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                            </div>
                        </div>


                        <div class="col-sm-8">
                            <div class="float-right mt-4"><a href="/Business/ROSettings" type="button" class="btn btn-primary pb-1 pt-1"><i class="icon ion-plus-circled mr-2"></i>Create Relationship Officer</a></div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <asp:GridView AutoGenerateColumns="false" ID="gvRelationshipOfficer" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvRelationshipOfficer_DataBound" DataSourceID="SDSRelationshipOfficer">
                            <Columns>
                                <asp:BoundField HeaderText="Officer Name" DataField="roName" SortExpression="roName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Contact" DataField="roMobile" SortExpression="roMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Home District" DataField="district" SortExpression="district" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="State" DataField="state" SortExpression="state" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                <asp:BoundField HeaderText="Status" DataField="name" SortExpression="name" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" HeaderStyle-Width="100px" ItemStyle-Width="100px" />
                                <asp:TemplateField HeaderStyle-Width="150px" ItemStyle-Width="150px" HeaderText="Action" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                    <ItemTemplate>
                                        <asp:LinkButton ID="lbtnView" runat="server" Text=" View" Visible='<%# ((new int[]{9, 10}).Contains(Convert.ToInt32(Eval("roStatus"))) && !string.IsNullOrEmpty(Eval("roAppointmentOrder").ToString())) %>' CommandArgument='<%# Eval("id") %>' currentRoStatus='<%# Eval("roStatus") %>' OnClick="lbtnView_Click"></asp:LinkButton>
                                        <asp:LinkButton ID="lbtnUpload" runat="server" Text="Upload" Visible='<%# ((new int[]{6,9}).Contains(Convert.ToInt32(Eval("roStatus")))) %>' roid='<%# Eval("id") %>' roStatus='<%# Eval("roStatus") %>' OnClientClick="openUploadModal(this); return false;"></asp:LinkButton>
                                        <asp:Label ID="lblNoAction" runat="server" Text="No Action" Visible='<%# !((new int[]{9, 10,6}).Contains(Convert.ToInt32(Eval("roStatus"))) || (string.IsNullOrEmpty(Eval("roAppointmentOrder").ToString()) && !string.IsNullOrEmpty(Eval("roAppointmentOrder").ToString()))) %>'></asp:Label>
                                        </div>
                                    </ItemTemplate>
                                </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                No ROs created.
                            </EmptyDataTemplate>
                        </asp:GridView>

                        <asp:SqlDataSource runat="server" ID="SDSRelationshipOfficer" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT ro.id, ro.roName, ro.roMobile, ro.roAddress, ro.rodst_Id, ro.roStatus,ros.name,
                            (SELECT dst_Name FROM finascop_district WHERE dst_Id=rodst_Id) AS district, 
                            (SELECT st_name FROM finascop_state WHERE st_Id=rost_id) AS state, roBloodGroup,
                            (SELECT roAppointmentOrder FROM relational_officer_log WHERE ro.id=roId AND roAppointmentOrder IS NOT NULL LIMIT 1) AS roAppointmentOrder  FROM relationship_officer ro 
                            INNER JOIN `relationship_officer_status` ros ON ros.id = ro.roStatus WHERE type=1 AND ((@areaId > 0 and roArea = @areaId) or roBusAssociate=@baId) 
                            AND (trim(ifnull(@searchKey, '')) like '' or roName like CONCAT('%', @searchKey, '%') or roMobile like CONCAT('%', @searchKey, '%')) GROUP BY ro.id ORDER BY id DESC"
                            OnSelecting="SDSRelationshipOfficer_Selecting">
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

    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Appointment Letter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <asp:HiddenField ID="hiddenRoid" runat="server" />
                <asp:HiddenField ID="hiddenRoStatus" runat="server" />
                <div class="form-group">
                    <label for="fileAppointmentLetter">Choose Appointment Letter (.pdf only): <span class="tx-danger">*</span></label>
                    <input type="file" id="fileAppointmentLetter" runat="server" accept="application/pdf" class="form-control-file" required/>
                    <span id="appointmentLetterError" class="error_msg_wrap" style="color: red; display: none;">Please select an appointment letter.</span>
                </div>
            </div>
            <div class="modal-footer">
                <asp:Button ID="btnUploadAppointment" runat="server" CssClass="btn btn-primary" OnClick="btnUploadAppointment_Click" Text="Upload" OnClientClick="return uploadAppointmentLetter();" />
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

     <!-- Modal -->
    <div class="modal fade" id="appointmentLetterModal" tabindex="-1" role="dialog" aria-labelledby="appointmentLetterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentLetterModalLabel">Appointment Letter</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="appointmentLetterContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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

    <script>
        function openUploadModal(button) {
            var roid = button.getAttribute('roid');
            var roStatus = parseInt(button.getAttribute('roStatus'));
            if (roStatus === 9 || roStatus === 6) {
                document.getElementById('<%= hiddenRoid.ClientID %>').value = roid;
            document.getElementById('<%= hiddenRoStatus.ClientID %>').value = roStatus;
                $('#uploadModal').modal('show');
            } else {
                alert("Upload is only allowed when RO is waiting for appointment or RO appointed by associate.");
            }
        }
</script>

    <script>
        function validateFileSelection() {
            var fileInput = document.getElementById('<%= fileAppointmentLetter.ClientID %>');
        
        if (fileInput.files.length === 0) {
            Common.ShowToastifyMessage('<%= Page.ClientID %>', "File is not selected.", "danger");
                return false; 
            }

            return true; 
        }
</script>

   <%--<script type="text/javascript">
       function showAppointmentLetterModal(url) {
           var iframe = document.getElementById('appointmentLetterIframe');
           iframe.src = url;

           // Show the modal
           $('#appointmentLetterModal').modal('show');
       }
</script>--%>

    <%--<script type="text/javascript">
        function showAppointmentLetterModal(url, type) {
            var embedhtml = '';

            switch (type) {
                case 'pdf':
                    url = "https://mozilla.github.io/pdf.js/web/viewer.html?file=" + url;
                    embedhtml = '<iframe src="' + url + '" width="100%" height="100%" style="border: none;"></iframe>';
                    break;
                // Add other cases if needed
            }

            // Assuming you have a div with id 'appointmentLetterContent' to hold the embedded HTML
            document.getElementById('appointmentLetterContent').innerHTML = embedhtml;

            // Show the modal
            $('#appointmentLetterModal').modal('show');
        }
</script>--%>

    <script type="text/javascript">
        function showAppointmentLetterModal(url, type) {
            var embedhtml = '';

            switch (type) {
                case 'pdf':
                    url = "https://mozilla.github.io/pdf.js/web/viewer.html?file=" + encodeURIComponent(url);
                    embedhtml = '<iframe src="' + url + '" width="700px" height="500px" style="border: none;"></iframe>';
                    break;
            }

            var contentDiv = document.getElementById('appointmentLetterContent');
            if (contentDiv) {
                contentDiv.innerHTML = embedhtml;

                // Show the modal
                $('#appointmentLetterModal').modal('show');
            } else {
                console.error('Element with id "appointmentLetterContent" not found.');
            }
        }
</script>

    <script type="text/javascript">


        $("input[data-bootstrap-switch], tb[data-bootstrap-switch] input[type=checkbox]").each(function () {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('tb[data-bootstrap-switch] input[type=checkbox]').on('switchChange.bootstrapSwitch', function (e, state) {
            $(this).prop('checked', !state);
            $(this).trigger('click');
        });

    </script>
</asp:Content>
