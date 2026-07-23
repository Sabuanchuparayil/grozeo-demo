<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="" AutoEventWireup="true" CodeBehind="ClientManagement.aspx.cs" Inherits="RetalineProAgent.ClientManagement" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessCRM">CRM</a></li>
    <li class="breadcrumb-item active" aria-current="page" id="breadcrumbType" runat="server"></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <script src="/content/js/custom/pdf.js"></script>
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <script src="../Content/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<%--    <script src="../Content/lib/jquery/js/jquery-ui.js"></script>
    <script src="../Content/lib/jquery/js/jquery.js"></script>--%>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
   <%-- <link rel="stylesheet" href="/Content/css/custom/custom.css"> --%>   
      <link rel="stylesheet" href="/Content/customadmin/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/Content/customadmin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <script>
        function handleImageError(QRImgPreview, QRPdfPreview) {
            let pdfUrl = $(QRImgPreview).attr('src');
            if (pdfUrl == "") {
                return;
            }


            pdfjsLib.getDocument(pdfUrl).promise.then(function (pdf) {
                var newDiv = $("<div></div>");
                $(QRPdfPreview).empty().append(newDiv);
                console.log("the pdf has", pdf.numPages, "page(s).");
                for (var i = 0; i < pdf.numPages; i++) {
                    (function (pageNum) {
                        pdf.getPage(i + 1).then(function (page) {
                            // you can now use *page* here
                            var viewport = page.getViewport(2.0);
                            var pageNumDiv = document.createElement("div");
                            pageNumDiv.className = "pageNumber";
                            pageNumDiv.innerHTML = "Page " + pageNum;
                            var canvas = document.createElement("canvas");
                            canvas.className = "page";
                            canvas.title = "Page " + pageNum;
                            $(QRPdfPreview).append(pageNumDiv);
                            $(QRPdfPreview).append(canvas);
                            $(QRPdfPreview).show();
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;


                            page.render({
                                canvasContext: canvas.getContext('2d'),
                                viewport: viewport
                            }).promise.then(function () {
                                console.log('Page rendered');
                            });
                            page.getTextContent().then(function (text) {
                                console.log(text);
                            });
                        });
                    })(i + 1);
                }

            });
            $(QRImgPreview).hide();
        }
        function handleImageError0(event) {
            handleImageError($('#QRImgPreview0'), $('#QRPdfPreview0'));
        }
    </script>
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle" runat="server" Text=""></asp:Literal> 
            </h6>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <!-- Contact -->
    <asp:PlaceHolder ID="phContact" runat="server" Visible="false">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-4">
                                <%--<label class="form-control-label w-100 mb-1">Search: </label>--%>
                                <input type="text" style="display: none" />
                                <input type="password" style="display: none" />
                                <div class="d-flex">
                                    <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name, number, email etc." CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" Style="height: 33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                                </div>
                            </div>


                            <div class="col-sm-8">
                                <div class="float-right"><a href="/Business/ContactSettings" type="button" class="btn btn-primary pb-1 pt-1"><i class="icon ion-plus-circled mr-2"></i>Create Contact</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <asp:GridView AutoGenerateColumns="false" ID="gvContacts" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDS_Contact">
                                <Columns>
                                    <asp:TemplateField HeaderText="Store Name" SortExpression="crco_orgName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                        <ItemTemplate>
                                            <a href="https://maps.google.com/?q=<%# Eval("glatitude") %>,<%# Eval("glongitude") %>" target="_blank"><i class="fa fa-map-marker"></i></a>&nbsp;
                                                <%# Eval("crco_orgName") %><br />
                                            <small><%# Eval("crco_location") %></small>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:TemplateField HeaderText="Contact" SortExpression="crco_indMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                        <ItemTemplate><%# Eval("crco_indMobile") %><br />
                                            <small><%# Eval("crco_indContactperson") %></small></ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:TemplateField HeaderText="Email Address" SortExpression="email" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                        <ItemTemplate><%# Eval("email") %><br />
                                            <small><%# Eval("businessCategory") %></small>-<small><%# Eval("activeServices") %></small></ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:BoundField HeaderText="Type" DataField="contactType" SortExpression="contactType" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Created" DataField="created_From" SortExpression="created_From" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Created By" DataField="createdBy" SortExpression="createdBy" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Created On" DataField="crco_CreatedOn" SortExpression="crco_CreatedOn" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:BoundField HeaderText="Status" DataField="contactStatus" SortExpression="contactStatus" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                                    <asp:TemplateField HeaderStyle-Width="150px" ItemStyle-Width="150px" HeaderStyle-BackColor="#DEE2E6" HeaderText="View Image" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                        <ItemTemplate>
                                            <asp:Image ID="imgDisplay" runat="server" Visible="false" />
                                            <asp:Label ID="lblMessage" runat="server" ForeColor="Red"></asp:Label>
                                            <asp:LinkButton ID="lbtnView" runat="server" Text='<%# string.IsNullOrEmpty(Eval("crco_image").ToString()) ? "No image uploaded" : "View Image" %>' Enabled='<%# (!string.IsNullOrEmpty(Eval("crco_image").ToString())) %>' contactImage='<%# Eval("crco_image") %>' CommandArgument='<%# Eval("id") %>' OnClick="lbtnView_Click"></asp:LinkButton>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Business/ContactSettings" DataNavigateUrlFields="id" DataNavigateUrlFormatString="~/Business/ContactSettings?id={0}" />
                                </Columns>
                                <EmptyDataTemplate>
                                    No contacts.
                                </EmptyDataTemplate>
                            </asp:GridView>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </asp:PlaceHolder>

    <!-- Lead / Prospect -->
    <asp:PlaceHolder ID="phLeadProspect" runat="server" Visible="false">
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
                                        <asp:TextBox ID="textBoxSearch" runat="server" placeholder="Search by name, number & state" CssClass="form-control" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton ID="lbtnSearchBox" CssClass="btn btn-primary d-inline-block w-auto ml-2" runat="server">Search</asp:LinkButton>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 d-flex justify-content-lg-end mt-2 mt-lg-0">
                                <a href="/Business/AsstLeadSettings" type="button" class="btn btn-primary"><i class="icon ion-plus-circled mr-2"></i>Create Leads</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="accordion" class="table-responsive">
                            <asp:HiddenField ID="hidFilterType" runat="server" />
                            <asp:GridView AutoGenerateColumns="false" ID="gvLeadProspect" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                          AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" 
                                          OnRowDataBound="gvLeadProspect_RowDataBound" DataSourceID="SDS_LeadProspect">
                                <Columns>
                                    <asp:TemplateField HeaderText="Store Name" SortExpression="orgName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black">
                                        <ItemTemplate>
                                            <%# Eval("orgName") %><br />
                                            <small><%# Eval("areaname") %></small>
                                                <asp:PlaceHolder runat="server" Visible='<%# !string.IsNullOrEmpty(GetImagePath(Eval("Type"), Eval("crle_image"), Eval("crpr_image"))) %>'>
                                                <a href="javascript:void(0)"
                                                    class="view-image-icon"
                                                    title="View Image"
                                                    onclick="showImageModal('<%# GetImagePath(Eval("Type"), Eval("crle_image"), Eval("crpr_image")) %>')">
                                                    <i class="fa fa-image"></i>
                                                </a>
                                            </asp:PlaceHolder>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:TemplateField HeaderText="Contact" SortExpression="indContactperson" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black">
                                        <ItemTemplate>
                                                <%# Eval("indContactperson") %><br />
                                            <small><%# Eval("indMobile") %></small>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:BoundField HeaderText="Created From" DataField="createdFrom" SortExpression="createdFrom" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                    <asp:BoundField HeaderText="Assigned To" DataField="createdByName" SortExpression="createdByName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                    <asp:BoundField HeaderText="RO" DataField="assignedROName" SortExpression="assignedROName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                    <asp:BoundField HeaderText="Type" DataField="Type" SortExpression="Type" Visible="false" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                    <asp:TemplateField HeaderText="Status" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black">
                                        <ItemTemplate>
                                            <%# Eval("Type").ToString() == "Lead" ? Eval("leadStatus") : Eval("prospectStatus") %><br />
                                            <asp:Label runat="server" Text='<%# "<small>" + Eval("invitationCode") + "</small>" %>'
                                                Visible='<%# Eval("invitationSent").ToString() == "1" && Eval("prospectStatus").ToString() == "Invitation Sent" %>' />
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:TemplateField HeaderStyle-Width="50" HeaderText="Action" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black">
                                        <ItemTemplate>
                                            <div class="action_arrow tx-center" data-toggle="collapse" data-target="<%# String.Format("#collapse{0}", Container.DataItemIndex) %>" aria-expanded="false" aria-controls="collapseOne"><i class="fa fa-chevron-down" aria-hidden="true"></i></div>
                                            <tr>
                                                <td colspan="7" class="hiddenRow">
                                                    <div id="<%# String.Format("collapse{0}", Container.DataItemIndex) %>" class="collapse tx-center" aria-labelledby="headingOne" data-parent="#accordion">
                                                        <a href="javascript:void(0)" class="open-activity btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" leadid='<%# Eval("leadId") %>' prospectid='<%# Eval("prospectId") %>' data-invitation-code='<%# Eval("invitationCode") %>' expireddate='<%# Eval("crpr_ExpiredOn") %>'>Add Activity</a>
                                                        <button type="button" id="btnView" class="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" leadid='<%# Eval("leadId") %>' prospectid='<%# Eval("prospectId") %>' crle_orgname='<%# Eval("orgName") %>' data-toggle="modal" data-target="#modalDetails" onclick="loadDetails('<%# Eval("leadId") %>', '<%# Eval("prospectId") %>', '<%# Eval("orgName") %>')">View Activity</button>
                                                        <asp:LinkButton runat="server" ID="lbtnUpgradeToProspect" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Upgrade to Prospect" Visible='<%# Eval("Type").ToString() == "Lead" %>' OnClientClick='<%# "return confirmUpgrade(" + Eval("leadId") + ");" %>' OnClick="lbtnUpgradeToProspect_Click"></asp:LinkButton>
                                                        <asp:LinkButton runat="server" CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" ID="btnEdit" Visible='<%# Eval("Type").ToString() == "Lead" || Eval("Type").ToString() == "Prospect" %>' clientType='<%# Eval("Type") %>' leadid='<%# Eval("leadId") %>' prospectid='<%# Eval("prospectId") %>' type='<%# Eval("Type") %>' OnClick="btnEdit_Click" Text='<%# Eval("Type").ToString() == "Lead" ? "Edit Lead" : "Edit Prospect" %>'></asp:LinkButton>
                                                        <a href="javascript:void(0)" class="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" leadid='<%# Eval("leadId") %>' storename='<%# Eval("orgName") %>' areaname='<%# Eval("areaname") %>' selectedro='<%# Eval("assignedRO") %>' clienttype='<%# Eval("Type") %>' onclick="loadDelegateLead(this)"><%# Eval("Type").ToString() == "Lead" ? "Delegate Lead" : "Delegate Prospect" %></a>
                                                        <asp:LinkButton runat="server" ID="btnSentInvitation" Visible='<%# Eval("prospectStatus").ToString() == "Prospect" || Eval("prospectStatus").ToString() == "Lead" %>'  prospectId='<%# Eval("prospectId") %>' orgName='<%# Eval("orgName") %>' email='<%# Eval("crpr_orgEmail") %>' code='<%# Eval("invitationCode") %>' CssClass="btn btn-primary ml-1 mr-1 mt-3 mb-3 p-1 px-3" Text="Sent Invitation" OnClientClick="return confirm('Are you sure you want to send an invitation code?');" OnClick="btnSentInvitation_Click" />
                                                    </div>
                                                </td>
                                            </tr>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                </Columns>
                                <EmptyDataTemplate>
                                    No leads / prospect created.
                                </EmptyDataTemplate>
                            </asp:GridView>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </asp:PlaceHolder>

    
                        <!-- Contacts -->
                        <asp:SqlDataSource runat="server" ID="SDS_Contact" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT fcc.id, crco_orgName,crco_type, NAME AS contactType,`crco_CreatedOn`,
                                    IF(`retailCategory_isOthers` =1,'Inactive','Active') AS activeServices, CASE WHEN crco_mode=1 
                                    THEN 'Enquiries from the Site or SM campaigns' 
                                    WHEN crco_mode=2 THEN 'Contacts created through CRM web form' WHEN crco_mode=3 
                                    THEN 'Contacts creation through CRM mobile app with current location and photo' 
                                    WHEN crco_mode=4 THEN 'Contacts created through CRM mobile app with Google address API' END AS contactMode, crco_indContactperson, crco_indMobile, 
                                    CASE WHEN crco_orgEmail IS NULL OR crco_orgEmail = '' THEN 'NIL' ELSE crco_orgEmail 
                                    END AS email,crmu_id,(SELECT crmu_name FROM finascop_crm_status crs WHERE crs.crmu_id=fcc.crmu_id) AS contactStatus,crco_isActive,crco_CreatedBy, glatitude, glongitude,
                                    (SELECT baName FROM business_associate WHERE id=crco_CreatedBy) AS baName, crco_location, 
                                    (SELECT business_category_name FROM retaline_business_category WHERE business_category_id = retailCategory) AS businessCategory,
                                    CASE WHEN crco_CreatedFrom=1 THEN 'Admin' WHEN crco_CreatedFrom=2 THEN 'Associate' WHEN crco_CreatedFrom=3 THEN 'RO' END AS 
                                    created_From, CASE WHEN crco_CreatedFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = crco_CreatedBy) 
                                    WHEN crco_CreatedFrom =2 THEN (SELECT baName FROM business_associate WHERE id = crco_CreatedBy) WHEN crco_CreatedFrom =3 
                                    THEN (SELECT roName FROM relationship_officer WHERE id = crco_CreatedBy) END AS createdBy,crco_CreatedFrom, crco_image
                                    FROM finascop_crm_contact fcc 
                                    INNER JOIN crm_contact_type cct ON cct.id = crco_type 
                                    WHERE ((@areaId > 0 AND fcc.id IN(SELECT contactId FROM finascop_crm_lead WHERE areaId = @areaId)) 
                                    or (crco_CreatedFrom = 2 AND crco_CreatedBy = @baId) OR (crco_CreatedFrom = 3 AND crco_CreatedBy 
                                    IN(SELECT GROUP_CONCAT(id) FROM relationship_officer WHERE roBusAssociate = @baId)))
                                    AND (trim(ifnull(@searchKey, '')) like '' or crco_orgName like CONCAT('%', @searchKey, '%') or crco_indContactperson like CONCAT('%', @searchKey, '%') or crco_orgEmail like CONCAT('%', @searchKey, '%') or crco_indMobile like CONCAT('%', @searchKey, '%') or crco_type like CONCAT('%', @searchKey, '%')) ORDER BY crco_orgName ASC" OnSelecting="SDSContacts_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="baId" DefaultValue="0" />
                                <asp:Parameter Name="areaId" DefaultValue="0" />
                                <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                            </SelectParameters>
                        </asp:SqlDataSource>

                        <!-- Leads / Prospect-->
                        <asp:SqlDataSource ID="SDS_LeadProspect" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
                            SelectCommand="SELECT cl.id AS leadId, ba.baName, crle_image, crpr_image, crpr_orgEmail,
                            COALESCE(cp.assignedRO, cl.assignedRO) AS assignedRO,
                            CASE WHEN cp.id IS NOT NULL THEN crpr_orgName ELSE crle_orgName END AS orgName,
                            CASE WHEN cp.id IS NOT NULL THEN crpr_indContactperson ELSE crle_indContactperson END AS indContactperson,
                            CASE WHEN cp.id IS NOT NULL THEN crpr_indMobile ELSE crle_indMobile END AS indMobile,
                            crle_location, crle_orgContactNo, crle_CreatedBy, cl.crmuId,
                            COALESCE((SELECT crmu_name FROM finascop_crm_status cs WHERE cs.crmu_id = cl.crmuId), 'Lead') AS leadStatus,
                            crle_gplace, cp.invitationCode, cp.crpr_ExpiredOn, COALESCE((SELECT roName FROM relationship_officer WHERE id = COALESCE(cp.assignedRO, cl.assignedRO)),
                            '-' ) AS assignedROName, (SELECT areaName FROM area_entries WHERE id = COALESCE(cp.areaId, cl.areaId)) AS areaName,
                            CASE crle_CreatedFrom
                                WHEN 1 THEN 'Admin'
                                WHEN 2 THEN 'Associate'
                                WHEN 3 THEN 'RO'
                            END AS createdFrom,
                            CASE crle_CreatedFrom
                                WHEN 1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = crle_CreatedBy)
                                WHEN 2 THEN (SELECT baName FROM business_associate WHERE id = crle_CreatedBy)
                                WHEN 3 THEN (SELECT roName FROM relationship_officer WHERE id = crle_CreatedBy)
                            END AS createdByName,cp.invitationSent,
                            CASE 
                           WHEN cp.invitationSent = 1 AND cp.crpr_ExpiredOn IS NOT NULL AND TIMESTAMPDIFF(MINUTE, cp.crpr_ExpiredOn, NOW()) > 30 
                               THEN 'Prospect' 
                           WHEN cp.invitationSent = 1 
                               THEN CONCAT('Invitation Sent') 
                           ELSE (SELECT crmu_name FROM finascop_crm_status cs WHERE cs.crmu_id = cp.crmuId) 
                            END AS prospectStatus,
                            cp.id AS prospectId,
                            CASE WHEN cp.id IS NOT NULL THEN 'Prospect' ELSE 'Lead' END AS Type
                            FROM finascop_crm_lead cl
                            LEFT JOIN finascop_crm_prospect cp ON cl.id = cp.leadId
                            INNER JOIN business_associate ba ON ba.id = cl.baId
                            WHERE 
                            cl.crmuId NOT IN (7)
                            AND (
                                (@areaId > 0 AND cl.areaId = @areaId) 
                                OR cl.baId = @baId
                            )
                            AND (
                                COALESCE(@filterType, 0) = 0 
                                OR (@filterType = 1 AND crle_type = 1)  
                                OR (@filterType = 2 AND crle_type = 3)
                            )
                            AND (
                                TRIM(COALESCE(@searchKey, '')) = '' 
                                OR crle_orgName LIKE CONCAT('%', @searchKey, '%') 
                                OR crle_gplace LIKE CONCAT('%', @searchKey, '%') 
                                OR crle_indMobile LIKE CONCAT('%', @searchKey, '%') 
                                OR crpr_orgName LIKE CONCAT('%', @searchKey, '%') 
                                OR crpr_gplace LIKE CONCAT('%', @searchKey, '%') 
                                OR crpr_indMobile LIKE CONCAT('%', @searchKey, '%')
                            )
                            ORDER BY 
                            crle_orgName ASC" OnSelecting="SDSLeads_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="baId" DefaultValue="0" />
                                <asp:Parameter Name="areaId" DefaultValue="0" />
                                <asp:ControlParameter Name="searchKey" ControlID="textBoxSearch" ConvertEmptyStringToNull="false" />
                                <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
                            </SelectParameters>
                            </asp:SqlDataSource>

    <asp:HiddenField ID="hidType" runat="server" />
    <asp:HiddenField ID="hidleadId" runat="server" />
    <asp:HiddenField ID="hidAreaName" runat="server" />
    <asp:HiddenField ID="hidProspectId" runat="server" />

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
                        <h5 class="modal-title">
                            <asp:Label ID="lblModalTitle" runat="server" Text="Schedule Lead Meetings"></asp:Label>
                        </h5>
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
                        <asp:Button runat="server" ID="btnSchedule" ValidationGroup="InsertSchedule" leadid='<%# Eval("leadId") %>' CssClass="btn btn-primary mr-2 bd-0" Text="Save" OnClick="btnSchedule_Click"  formnovalidate />
                        <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>
                    </div>

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
                        <asp:Button runat="server" ID="btnStatusUpt" ValidationGroup="StatusUpdate" prospectId='<%# Eval("prospectId") %>' CssClass="btn btn-primary mr-2 bd-0" Text="Save" OnClick="btnStatusUpt_Click"  formnovalidate />
                        <a href="javascript:void(0)" class="btn btn-secondary bd-0" data-dismiss="modal" aria-label="Close" style="width: 100px">Cancel</a>
                    </div>

                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog w-100" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Uploaded Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="imgboxwrap">
                    <img id="modalImage" src="" alt="Image" class="img-fluid" />
                </div>
            </div>
        </div>
    </div>
</div>

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
    </div>
    <!-- modal -->
    
    <!-- Activity Modal -->
    <div id="modalActivities" class="modal fade" data-backdrop="static">
        <div class="modal-dialog w-100 modal-dialog-vertical-center modal-lg" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <!-- Modal Header -->
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <h5 class="modal-title">Add Activity</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Section Wrapper -->
                    <div class="section-wrapper p-0 border-0">
                        <div id="activityInfo" class="alert alert-info" style="display: none;">
                            <span id="invitationCodeMessage"></span>
                        </div>
                        <!-- Activity Selector -->
                        <div class="form-group">
                            <label for="ddlActivity" class="form-control-label">Select Activity</label>
                            <asp:DropDownList ID="ddlActivity" runat="server" CssClass="form-control select2" AppendDataBoundItems="true" ForeColor="GrayText" DataSourceID="SDSActivity" DataTextField="crma_name" DataValueField="crma_id"><asp:ListItem Text="Select activity" Value=""></asp:ListItem></asp:DropDownList>
                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSActivity" ProviderName="MySql.Data.MySqlClient"
                                SelectCommand="SELECT crma_id, crma_name, type FROM finascop_crm_action WHERE crma_id > 2"></asp:SqlDataSource>
                            <asp:RequiredFieldValidator ValidationGroup="AddActivity" ControlToValidate="ddlActivity" ForeColor="Red" ErrorMessage="Select activity" runat="server"></asp:RequiredFieldValidator>
                        </div>

                        <!-- Conditional Sections -->

                        <div id="dvNotes" class="form-group" style="display: none;">
                            <label for="txtNotes" class="form-control-label">Notes <span id="spnNotesRequired" class="tx-danger" style="display: none;">*</span></label>
                            <asp:TextBox ID="txtNotes" runat="server" CssClass="form-control" TextMode="MultiLine" Rows="4" MaxLength="500" oninput="updateNoteCharacterCount()"></asp:TextBox>
                            <span id="charCountNote" class="character-count"></span>
                        </div>

                        <div id="dvAttachment" class="form-group" style="display: none;">
                            <label for="fuAttachment" class="form-control-label">Attachment <span id="spnAttachmentRequired" class="tx-danger" style="display: none;">*</span></label>
                            <asp:FileUpload ID="fuAttachment" runat="server" CssClass="form-control" accept="*/*" />
                        </div>

                        <div id="dvCalendar" class="form-group" style="display: none;">
                            <label for="calendar" class="form-control-label">Select Date<span class="tx-danger">*</span></label>
                            <asp:TextBox ID="calendar" runat="server" CssClass="form-control" TextMode="Date" />
                            <label id="lblTime" for="ddlSTime" style="display: none; margin-top: 15px;">Select Time<span class="tx-danger">*</span></label>
                            <asp:DropDownList ID="ddlSTime" runat="server" CssClass="form-control select2"></asp:DropDownList>
                        </div>

                        <div id="dvInvitation" class="form-group" style="display: none;">
                            <label for="txtInvitationCode" class="form-control-label">Invitation Code</label>
                            <asp:TextBox ID="txtInvitationCode" runat="server" CssClass="form-control" />
                        </div>
                    </div>
                    <!-- Section Wrapper End -->

                    <!-- Modal Footer -->
                    <div class="modal-btn mt-3">
                        <asp:Button runat="server" ID="btnSubmitActivity" CssClass="btn btn-primary mr-2 bd-0" Text="Submit" OnClick="btnSubmitActivity_Click" ValidationGroup="AddActivity"/>
                        <button type="button" class="btn btn-secondary bd-0" data-dismiss="modal" style="width: 100px;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Activity Modal -->
    <div id="modalCustomActivity" class="modal fade" data-backdrop="static">
    <div class="modal-dialog w-100 modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
            <div class="modal-body">
                <!-- Modal Header -->
                <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                    <h5 class="modal-title">Custom Activity</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Section Wrapper -->
                <div class="section-wrapper p-0 border-0">
                    <div id="dvCNotes" class="form-group"">
                            <label for="txtCustomActivity" class="form-control-label">Add Activity</label>
                            <asp:TextBox ID="txtCustomActivity" runat="server" CssClass="form-control"></asp:TextBox>
                        </div>
                    <div id="dvNoteVisibility" class="form-group">
                            <label class="form-control-label">Visibility</label><br />
                            <asp:RadioButton ID="rbPrivate" runat="server" GroupName="noteVisibility" CssClass="visibility_radio" Text="Private" Value="Private" />
                            <asp:RadioButton ID="rbPublic" runat="server" GroupName="noteVisibility" CssClass="visibility_radio" Text="Public" Value="Public" Checked="true" />
                        </div>

                        <div id="dvCustomNotes" class="form-group"">
                            <label for="txtAdditionalNotes" class="form-control-label">Notes</label>
                            <asp:TextBox ID="txtAdditionalNotes" runat="server" CssClass="form-control" MaxLength="500" oninput="updateNoteCharacterCount()" TextMode="MultiLine" Rows="4"></asp:TextBox>
                            <span id="charCountNotes" class="character-count"></span>
                        </div>
                   
                    <div id="dvCustomAttachment" class="form-group">
                        <label class="form-control-label">Attachment</label>
                        <asp:Button ID="btnChange" runat="server" CssClass="btn btn-link" Text="Change File" Visible="false" OnClick="btnChange_Click"
                                Style="float: right; font-weight: normal; text-decoration: underline; color: #797867; cursor: pointer;" OnClientClick="return confirm('Are you sure you want to change this file?');"/>
                        <asp:TextBox ID="txtFileName" runat="server" CssClass="form-control" Visible="false"></asp:TextBox>
                        <div class="upload_qrqcode_wrap" id="documentUpload" runat="server">
                            <div class="upload_btnicon" data-toggle="modal" data-target="#DocumentUploadpopup">
                                <img src="/content/images/upladnew_img.png">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Section Wrapper End -->

                <!-- Modal Footer -->
                <div class="modal-btn mt-3">
                    <asp:Button ID="btnSubmitAdditionalFeatures" runat="server" CssClass="btn btn-primary mr-2 bd-0" Text="Submit" OnClick="btnSubmitAdditionalFeatures_Click" />
                    <button type="button" class="btn btn-secondary bd-0" data-dismiss="modal" style="width: 100px;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Modal -->

    <style>
        .visibility_radio label{
            margin-left:5px;
            margin-bottom:0px;
        }
        .visibility_radio {
            margin-right:1rem;
        }
      #modalCustomActivity{
        z-index: 1051;
      }
      .modal-backdrop + .modal-backdrop {
        z-index: 1050;
      }
      #DocumentUploadpopup{
        z-index: 1052;
      }
      .modal-backdrop + .modal-backdrop + .modal-backdrop {
        z-index: 1051;
      }
    </style>

    <div class="modal fade" id="DocumentUploadpopup" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="DocumentUploadpopupLabel" style="height: 100%; width: 100%; overflow: visible; display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
            <div class="modal-content doument-upload-dialog">
                <div class="modal-body">
                    <div class="section-wrapper p-0 border-0">
                       <div class="row justify-content-center">
                        <div class="col-12 mb-0 form-group">
                            <div id="UploadFile0" class="Uploadbox enabled d-flex justify-content-center align-items-center">
                                <div class="upload_qrqcode_wrap m-2 repeater_block-class" style="height: 150px; width: 150px;">
                                    <asp:HiddenField ID="hfdBlobURL" Value="" runat="server" />
                                    <asp:HiddenField ID="hfdKey" Value="" runat="server" />
                                    <asp:HiddenField ID="folder" Value = "" runat="server" ClientIDMode="Static"  />
                                    <asp:HiddenField ID="DocumentID0" Value="DOC0" runat="server" ClientIDMode="Static" />
                                    <asp:HiddenField ID="DocumentName0" Value="Proof Document 1" runat="server" ClientIDMode="Static" />
                                    <asp:HiddenField ID="DocumentURL0" Value="" runat="server" ClientIDMode="Static" />
                                    <asp:HiddenField ID="blobFileURL0" Value="" runat="server" ClientIDMode="Static" />
                                    <asp:HiddenField ID="blobFileName0" Value="" runat="server" ClientIDMode="Static" />
                                    <asp:HiddenField ID="hfBlobFileName" runat="server" />
                                    <asp:HiddenField ID="hfBlobFileURL" runat="server" />

                                    <div id="docUpload_wap0" class="uploadfile_wrap d-flex align-items-center justify-content-between w-100 h-100 position-relative" style="background-color: #ececec;">
                                        <div id="actions0" class="upload_btnicon m-1 upload_interface">
                                            <div id="documentupload_input0" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100">
                                                <a id="pdfUpload0" class="d-inline-block text-center w-100 addtext">
                                                    <img style="max-width: 55px;" src="/content/images/loc_update.png">
                                                </a>
                                                <asp:FileUpload ID="fuCustomAttachment" runat="server" class="fup_block-class position-absolute w-100 fup_pdf_upload" Style="opacity: 0; height: 38px;" accept="*/*" onchange="UploadFile(this)" />
                                                <asp:HiddenField ID="hfAttachmentPath" runat="server" />
                                            </div>
                                        </div>

                                        <div id="qrcode-section0" class="qrqcode_sec m-1">
                                            <img style="max-width: 55px;" src="/content/images/Qr_code.png">
                                        </div>

                                        <div id="QRImgPreview_wap0" class="qrimg_preview_block-class text-center align-items-center h-100  w-100" style="display: none; min-height: 100px; overflow: auto;">
                                            <img id="QRImgPreview0" src="" class="" style="max-width: 100%;">
                                            <div id="QRPdfPreview0" class="qrpdf_preview_block-class align-items-center w-100 h-100 " style="display: none; min-height: 100px; overflow: auto;"></div>
                                        </div>
                                        <div id="docPreview_wap0" class="doc_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;"></div>
                                        <div id="ImgPreview_wap0" class="img_preview_block-class text-center w-100 h-100 position-relative" style="display: none; min-height: 100px; overflow: auto;">
                                            <img id="ImgPreview0" src="" class="preview_img" style="max-width: 100%; min-height: 100px;">
                                        </div>
                                        <div class="qrqcode_btnicon w-100 p-2" style="display: none;">
                                            <button id="close_btn0" type="button" class="btn-close close btn-link" aria-label="" style="width: 18px; height: 20px; border: 0px; background: #c1c1c1; opacity: 1; color: black; line-height: 100%; top: -13px; right: -18px;">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <img id="imgUploadQrcode0" src="" class="img-upload-qrcode-class" style="max-width: 100%; max-height: 100%;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center py-0">
                                <asp:Button ID="btnCancel" runat="server" Text="Cancel" OnClick="btnCancel_Click" CssClass="btn_same btn btn-secondary mr-2" Visible="true" />
                                <asp:Button ID="btnupload" CssClass="btn_same btn btn-primary" runat="server" Text="Upload" OnClick="btnupload_Click" OnClientClick="return confirm('Are you sure you want to upload this file?');" />
                            </div>
                               
                        </div>
                    </div>
                    </div>
                </div>
                <!--modal-body-->
            </div>
            <!--modal-content-->
        </div>
        <!--modal-dialog-->
    </div>
    <!--modal-->

    <script>
        function showCustomActivityModal() {
            // Hide the txtFileName field
            $('#<%= txtFileName.ClientID %>').show();

            // Close the DocumentUploadpopup modal
            $('#DocumentUploadpopup').modal('hide');

            // Show the modalCustomActivity modal
            $('#modalCustomActivity').modal('show');
        }

    </script>

    <script>
        $(document).ready(function () {
            function checkFileSelected() {
                var fileInput = document.getElementById('<%= fuCustomAttachment.ClientID %>');
                var blobFile = $("#blobFileName0").val().trim();
                var qrPreviewVisible = $("#QRImgPreview_wap0").css("display") === "block"; 

                // Check if a file is selected (either manual upload, QR code upload, or QR preview is visible)
                var hasFile = (fileInput && fileInput.files.length > 0) ||  qrPreviewVisible;

                // Enable or disable the upload button based on selection
                $("#<%= btnupload.ClientID %>").prop("disabled", !hasFile);
            }

            // Initially disable the upload button
            $("#<%= btnupload.ClientID %>").prop("disabled", true);

            // Delay to ensure event listeners are added after DOM is ready
            setTimeout(function () {
                var fileInput = document.getElementById('<%= fuCustomAttachment.ClientID %>');
                if (fileInput) {
                    fileInput.addEventListener("change", checkFileSelected);
                }
            }, 500);

            // QR Code Upload - Detect changes in blob input
            $("#blobFileName0").on("input change", checkFileSelected);

            // MutationObserver for dynamic changes in QR Code field
            var target = document.getElementById("blobFileName0");
            if (target) {
                new MutationObserver(checkFileSelected).observe(target, {
                    attributes: true,
                    childList: true,
                    subtree: true
                });
            }

            // Observe changes in QR Preview visibility (QRImgPreview_wap0)
            var qrPreview = document.getElementById("QRImgPreview_wap0");
            if (qrPreview) {
                new MutationObserver(checkFileSelected).observe(qrPreview, { attributes: true, attributeFilter: ["style"] });
            }

            // Disable Upload when "Change File" is clicked
            $("#<%= btnChange.ClientID %>").click(function () {
                // Disable upload button
                $("#<%= btnupload.ClientID %>").prop("disabled", true);

                // Clear filename fields
                $("#txtFileName").val(""); 
                $("#blobFileName0").val("").trigger("change");

                // Clear file input (ensure it's properly cleared)
                var fileInput = document.getElementById('<%= fuCustomAttachment.ClientID %>');
                if (fileInput) {
                    $(fileInput).val(""); // Reset file input field properly
                }

                // Delay check to ensure UI updates before re-validating
                setTimeout(checkFileSelected, 20);
            });
        });
    </script>

    <%--<script>
        $("#blobFileName0").on("input change", function () {
            $("#<%= hfBlobFileName.ClientID %>").val($(this).val()); // Store in hidden field
        });
        $("#blobFileURL0").on("input change", function () {
            $("#<%= hfBlobFileURL.ClientID %>").val($(this).val()); // Store in hidden field
});
    </script>--%>

    <script>
        function handleQRCodeUpload(fileName, fileUrl) {
            if (!fileName || !fileUrl) {
                // If no file is uploaded, clear all fields
                $("#<%= blobFileName0.ClientID %>").val("");
        $("#<%= blobFileURL0.ClientID %>").val("");
        $("#<%= hfAttachmentPath.ClientID %>").val("");
        $("#<%= txtFileName.ClientID %>").val("").hide();
        return;
    }

    // Set values in hidden fields
    $("#<%= blobFileName0.ClientID %>").val(fileName);
    $("#<%= blobFileURL0.ClientID %>").val(fileUrl);
    $("#<%= hfAttachmentPath.ClientID %>").val(fileUrl);

    // Show the file name in the textbox
            $("#<%= txtFileName.ClientID %>").val(fileName).show();
        }
    </script>

    <script>
        let $current_upload_qrqcode_wrap = null;

        $('#QRImgPreview0').on("error", handleImageError0);

        $(document).ready(function () {
            $(".remove_preview_wrap").click(function () {
                $upload_qrqcode_wrap = $(this).closest('.upload_qrqcode_wrap');

                var fileInput = $upload_qrqcode_wrap.find(".fup_block-class");
                fileInput.val('');

                $upload_qrqcode_wrap.find('.doc_preview_block-class').html('');
                $upload_qrqcode_wrap.find('.img_preview_block-class  img').attr("src", "");

                $upload_qrqcode_wrap.find('.doc_preview_block-class').hide();
                $upload_qrqcode_wrap.find('.img_preview_block-class').hide();
                $upload_qrqcode_wrap.find(".qrimg_preview_block-class").hide();
                $upload_qrqcode_wrap.find('.uploadfile_wrap').addClass('w-100');
                $upload_qrqcode_wrap.find('.text-center').addClass('w-100');

                $upload_qrqcode_wrap.find('.qrqcode_sec').show();
                $upload_qrqcode_wrap.find('.upload_btnicon').show();
            });
        });

        $(document).ready(function () {
            $(".repeater_block-class").on("click", function () {
                $(this).closest(".repeater_block-class").addClass("repeater-item-focus");
            });
        });

        $(document).ready(function () {
            if ($('#folder').val() == "") {
                $('#folder').val(folder);
            } else {
                folder = $('#folder').val();
            }
            var blobFileURL = "";
            if ($('#blobFileURL0').val() == "") {
                blobFileURL = generateFileUrl($('#UploadFile0').find('.qrqcode_btnicon'));
                $('#blobFileURL0').val(blobFileURL);
            }
            var fileuploadurl = cururl;
            var filename = $('#blobFileName0').val();
            fileuploadurl += '/Finance/UploadFile?key=' + folder + '&file=' + filename;
            console.log(fileuploadurl);
            console.log($('#blobFileURL0').val());
            fileuploadurl = encodeURIComponent(fileuploadurl);
            $('#imgUploadQrcode0').attr('src', 'https://qrcode.tec-it.com/API/QRCode?data=' + fileuploadurl + '&dim=325');
        });

        function UploadFile(input) {
            if (input.files && input.files[0]) {
                var $uploadWrap = $(input).closest('.upload_qrqcode_wrap');
                $uploadWrap.find('.uploadfile_wrap').removeClass('w-100');
                $uploadWrap.find('.text-center').removeClass('w-100');
                $uploadWrap.find('.upload_btnicon').hide();
                $uploadWrap.find('.qrqcode_sec').hide();

                var reader = new FileReader();

                reader.onload = function (e) {
                    $uploadWrap.find('.preview_img').attr('src', e.target.result);
                    $uploadWrap.find('.text-center').show();
                }

                reader.readAsDataURL(input.files[0]);
                var nextTargetDiv = $uploadWrap.closest('.Uploadbox').first().nextAll(".Uploadbox").first();
                nextTargetDiv.removeClass('Uploadbox disabled');
                nextTargetDiv.addClass('Uploadbox enabled');
            }

        };

        $(".upload_qrqcode_wrap").click(function (e) {
            if (ftimer != null) {
                clearInterval(ftimer);
                ftimer = null;
            }
            var repeaterItems = $(".upload_qrqcode_wrap");
            repeaterItems.each(function (index, item) {
                if (!$(item).is(e.target.closest(".repeater_block-class"))) {
                    $(item).removeClass("repeater-item-focus");
                    $qr_code_icon = $(item).closest('.upload_qrqcode_wrap').find('.qrqcode_btnicon');
                    $qr_code_icon.hide();
                    $qr_code_icon.removeClass("repeater-item-focus");
                } else {
                    $current_upload_qrqcode_wrap = $(item);

                }
            });
        });

        $(".qrqcode_sec").click(function (e) {
            if (ftimer != null) {
                clearInterval(ftimer);
                ftimer = null;
            }
            $upload_qrqcode_wrap = $(this).closest('.upload_qrqcode_wrap');
            $upload_qrqcode_wrap.find('.qrqcode_btnicon').show();
            $upload_qrqcode_wrap.find('.qrqcode_btnicon').addClass("repeater-item-focus");

            var blobFileURL = "";
            if ($upload_qrqcode_wrap.find('#blobFileURL0').length != 0) { blobFileURL = $upload_qrqcode_wrap.find('#blobFileURL0').val(); }
            ftimer = setInterval(loadFile(blobFileURL, $(this).closest('.upload_qrqcode_wrap')), 10 * 1000);
        });

        $(".btn-close").click(function (e) {
            $(this).closest('.img_preview_block-class, .qrqcode_btnicon').hide();

            $qr_code_icon = $(this).closest('.upload_qrqcode_wrap').find('.qrqcode_btnicon');
            $qr_code_icon.hide();
            $qr_code_icon.removeClass("repeater-item-focus");
            $uploadWrap.find('.qrcode_sec').show();
            if (ftimer != null) {
                clearInterval(ftimer);
                ftimer = null;
            }
        });

        function fupPdfFileUploadChange() {
            if (this.files.length > 0) {
                var $uploadWrap = $(this).closest('.upload_qrqcode_wrap');

                $uploadWrap.find('.uploadfile_wrap').removeClass('w-100');

                $uploadWrap.find('.upload_btnicon').hide();
                $uploadWrap.find('.qrqcode_sec').hide();

                var nextTargetDiv = $uploadWrap.closest('.Uploadbox').first().nextAll(".Uploadbox").first();
                nextTargetDiv.removeClass('Uploadbox disabled');
                nextTargetDiv.addClass('Uploadbox enabled');
            }
        }

        $('#UploadFile0').find('.fup_pdf_upload').change(fupPdfFileUploadChange);
    </script>

    <style>
        .class-blobfileurl {
        }

        .doument-upload-dialog {
        }

        .document-name {
        }

        .fup_block-class {
        }

        .repeater-item-focus {
            background-color: #c6def7;
        }

        .repeater_block-class {
        }

        .doc_preview_block-class {
            display: block;
        }

        .btn-link:hover {
            opacity: 1;
        }

        .btn-close {
            box-sizing: border-box;
            padding: 0em 0em;
            color: #000;
            border: 0;
            border-radius: 0rem;
            opacity: 0.2;
        }

        .btn_same {
            width: 70px;
            margin-top: 10px;
        }

        .remove_preview_wrap {
            bottom: -25px;
            right: 30px;
        }

        .qrqcode_btnicon {
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
            padding: 5px;
            border-radius: 10px;
        }

        .modal-btn {
            text-align: left;
        }

        .Uploadbox.disabled {
            pointer-events: none;
            opacity: 0.4;
        }

        .Uploadbox.enabled {
            pointer-events: auto;
            opacity: 1.0;
        }
    </style>
    
    <script>
        function loadFile(fileurl, upload_qrqcode_wrap) {
            $.ajax({
                url: fileurl,
                type: 'GET',
                headers: {
                    'Access-Control-Allow-Credentials': true,
                    'Access-Control-Allow-Origin': fileurl,
                },
                error: function (xhr, status, error) {
                    //file not exists
                    upload_qrqcode_wrap.find(".qrimg_preview_block-class").hide();
                    clearInterval(ftimer);
                    ftimer = null;
                    ftimer = setTimeout(function () {
                        loadFile(fileurl, upload_qrqcode_wrap); // repeat
                    }, 10 * 1000);

                },
                success: function (data) {
                    //file exists
                    if (ftimer != null) {
                        clearInterval(ftimer);
                        ftimer = null;
                    }
                    upload_qrqcode_wrap.find('.uploadfile_wrap').removeClass('w-100');

                    upload_qrqcode_wrap.find('.upload_btnicon').hide();
                    upload_qrqcode_wrap.find('.qrqcode_sec').hide(); 
                    if (upload_qrqcode_wrap.find('#docPreview_wap0').length != 0) {
                        $('#docPreview_wap0').hide();
                        $('#docPreview_wap0').html('');
                        $('#docPreview_wap0').innerHTML = "";
                    }

                    if (upload_qrqcode_wrap.find('#ImgPreview_wap0').length != 0) {
                        $('#ImgPreview_wap0').hide();
                        $('#ImgPreview_wap0 img').attr("src", "");
                    }


                    upload_qrqcode_wrap.find('.qrqcode_btnicon').hide();

                    if (upload_qrqcode_wrap.find('#QRImgPreview_wap0').length != 0) {
                        $('#DocumentURL0').val(fileurl);
                        $("#QRImgPreview_wap0").show();
                        $('#QRImgPreview0').attr("src", fileurl);
                        $("#QRImgPreview0").show();
                    }
                    var nextTargetDiv = upload_qrqcode_wrap.closest('.Uploadbox').first().nextAll(".Uploadbox").first();
                    nextTargetDiv.removeClass('Uploadbox disabled');
                    nextTargetDiv.addClass('Uploadbox enabled');
                }
            });
        }

        pdfjsLib.GlobalWorkerOptions.workerSrc = '/content/js/custom/pdf.worker.js';

        function addChangeEventListener(e) {
            var $docPreview_wap = $(this).closest('.upload_qrqcode_wrap').find('.doc_preview_block-class');
            $docPreview_wap.innerHTML = "";
            var fileReader = new FileReader();

            fileReader.onload = function () {
                var typedarray = new Uint8Array(this.result);

                pdfjsLib.getDocument(typedarray).promise.then(function (pdf) {
                    // you can now use *pdf* here
                    console.log("the pdf has", pdf.numPages, "page(s).");
                    for (var i = 0; i < pdf.numPages; i++) {
                        (function (pageNum) {
                            pdf.getPage(i + 1).then(function (page) {
                                // you can now use *page* here
                                var viewport = page.getViewport(2.0);
                                var pageNumDiv = document.createElement("div");
                                pageNumDiv.className = "pageNumber";
                                pageNumDiv.innerHTML = "Page " + pageNum;
                                var canvas = document.createElement("canvas");
                                canvas.className = "page";
                                canvas.title = "Page " + pageNum;
                                $docPreview_wap.append(pageNumDiv);
                                $docPreview_wap.append(canvas);
                                $docPreview_wap.show();
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;


                                page.render({
                                    canvasContext: canvas.getContext('2d'),
                                    viewport: viewport
                                }).promise.then(function () {
                                    console.log('Page rendered');
                                });
                                page.getTextContent().then(function (text) {
                                    console.log(text);
                                });
                            });
                        })(i + 1);
                    }

                });
            };

            fileReader.readAsArrayBuffer(file);
        }
        $('#UploadFile0').find('.fup_pdf_upload').on("change", addChangeEventListener);

        function readURL(input, imgControlName) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $(imgControlName).attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }


        function Generator() { };

        Generator.prototype.rand = Math.floor(Math.random() * 26) + Date.now();

        Generator.prototype.getId = function () {
            return this.rand++;
        };
        var idGen = new Generator();
        var folder;// = '<%= Guid.NewGuid().ToString() %>';

        $('#modalDialogShow').val('false');

        var key = document.getElementById('<%= hfdKey.ClientID %>').value;
        document.getElementById('<%= hfdKey.ClientID %>').value = (key == "") ? folder : key;
        var cururl = '<%= GetCurrentUrl()%>'
        let ftimer = null;

        var bloburl = document.getElementById('<%= hfdBlobURL.ClientID %>').value;

        function generateFileUrl(qr_code_icon) {

            var filename = idGen.getId();
            var blobFileURL = bloburl + '/finascopupload/' + folder + '/' + filename;
            if ($(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName0').length != 0) { $(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName0').val(filename); }
            return blobFileURL;
        }

        function deleteBlobFile(blobFileUrl, hfdDocumentURL) {
            $.ajax({
                type: "POST",
                url: '/Finance/UploadFile.aspx/deleteBlobFile',
                data: JSON.stringify({ "blobFileURL": blobFileUrl }),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (response) {
                    hfdDocumentURL.val("");
                },
                error: function (err) {

                }
            });
        }

        $(document).on('show.bs.modal', '#DocumentUploadpopup', function () {


            if ($('#DocumentURL0').val() != "") {
                $('#UploadFile0').find('.uploadfile_wrap').removeClass('w-100');
                $('#UploadFile0').find('.upload_btnicon').show();
                $('#UploadFile0').find('.qrqcode_sec').show();
                $('#docPreview_wap0').hide();
                $('#docPreview_wap0').html('');
                $('#docPreview_wap0').innerHTML = "";
                $('#ImgPreview_wap0').hide();
                $('#ImgPreview_wap0 img').attr("src", "");
                $('#UploadFile0').find('.qrqcode_btnicon').hide();
                $('#QRImgPreview_wap0').hide();
                $('#QRImgPreview0').attr("src", $('#DocumentURL0').val());
                $('#QRImgPreview0').show();
                $('#QRImgPreview0').on("error", handleImageError0);
                $('#UploadFile0').removeClass('Uploadbox disabled');
                $('#UploadFile0').addClass('Uploadbox enabled');
            } else {
                $('#UploadFile0').find(".repeater_block-class").removeClass("repeater-item-focus");
                $qr_code_icon = $('#UploadFile0').find('.qrqcode_btnicon');
                $qr_code_icon.hide();
                $qr_code_icon.removeClass("repeater-item-focus");
            }
            $('#UploadFile0').find('.upload_qrqcode_wrap').click();
            $('#modalDialogShow').val('true');

        })


        $(document).on('hidden.bs.modal', '#DocumentUploadpopup', function () {
            if (ftimer != null) {
                // close timer.
                clearInterval(ftimer);
                ftimer = null;
            }
            $('#modalDialogShow').val('false');
        })

    </script>

    <script>
        function handleFileUpload(input) {
            const fileDetailsDiv = document.getElementById('fileDetails');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileName = file.name;
                const fileExtension = fileName.split('.').pop();
                fileDetailsDiv.innerHTML = `File Name: <strong>${fileName}</strong> <br> File Extension: <strong>${fileExtension}</strong>`;
            } else {
                fileDetailsDiv.innerHTML = 'No file selected.';
            }
        }
    </script>

    <!-- View Activity -->
    <div class="modal fade" id="modalDetails" tabindex="-1" role="dialog" aria-labelledby="modalDetailsLabel" aria-hidden="true">
        <div class="modal-dialog w-100">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div id="dvpopupdetails">

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Marketing/ClientManagement?type=lead";
            });
        });

    </script>

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
            var clientType = $(obj).attr('clientType');
            $('#<%= hidleadId.ClientID %>').val(leadId);
                $('#<%= selRO.ClientID%>').val(selectedRO);
                $('#<%= txtLead.ClientID%>').val(lead);
                $('#<%= txtArea.ClientID%>').val(area);
                $('#<%= hidAreaName.ClientID %>').val(area);
                $('#<%= hidType.ClientID %>').val(clientType);
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

    <script type="text/javascript">
        function showImageModal(imageUrl) {
            $("#modalImage").attr("src", imageUrl);
            // Show the modal
            $('#imageModal').modal('show');
        }
    </script>
    

    <script type="text/javascript">
        $(document).ready(function () {
            // Open modal on button click
            $(document).on('click', '.open-activity', function () {
                var leadId = $(this).attr('leadid');
                var prospectId = $(this).attr('prospectid') || "0"; // Set to "0" if empty
                var invitationCode = $(this).data('invitation-code');
                var expiredDate = $(this).attr('expiredDate');

                // Set the hidden field with the leadId & prospectId value
                $('#<%= hidleadId.ClientID %>').val(leadId);
                $('#<%= hidProspectId.ClientID %>').val(prospectId);

            // Format expiration date to dd-MM-YYYY H:i:s
            var dateObject = new Date(expiredDate);
            var formattedDate = dateObject.toLocaleDateString('en-GB') + ' ' +
                dateObject.toLocaleTimeString('en-GB', { hour12: false });

                // Check if crpr_ExpiredOn is NULL or expired
                if (!expiredDate || new Date() > new Date(expiredDate)) {
                    // Hide the activity info and clear any invitation code
                    $('#activityInfo').hide();
                    $('#<%= txtInvitationCode.ClientID %>').val('').prop('readonly', false).hide();
                    } else {
                        // Format the expiration date
                        var dateObject = new Date(expiredDate);
                        var formattedDate = dateObject.toLocaleDateString('en-GB') + ' ' +
                            dateObject.toLocaleTimeString('en-GB', { hour12: false });

                        // Display invitation code and expiration info
                        if (invitationCode) {
                            $('#invitationCodeMessage').text(`Invitation Code: ${invitationCode} (Valid until ${formattedDate})`);
                            $('#activityInfo').show();
                            $('#<%= txtInvitationCode.ClientID %>').val(invitationCode).prop('readonly', true).show();
                        } else {
                            // Hide activity info if no invitation code is present
                            $('#activityInfo').hide();
                            $('#<%= txtInvitationCode.ClientID %>').val('').prop('readonly', false).hide();
                        }
                    }

            // Show the modal
            $('#modalActivities').modal('show');
        });

        // Activity dropdown change event
        $('#<%= ddlActivity.ClientID %>').change(function () {
            var selectedActivity = $(this).val();

            // Hide all panels by default
            $('#dvNotes, #dvAttachment, #dvCalendar, #dvInvitation, #dvCustomActivity').hide();
            $('#spnNotesRequired, #spnAttachmentRequired, #lblTime, #<%= ddlSTime.ClientID %>').hide();

            switch (selectedActivity) {
                case "3":
                case "4":
                case "9":
                case "10":
                case "11":
                case "12":
                    $('#dvNotes, #spnNotesRequired').show();
                    break;
                case "5":
                case "6":
                case "7":
                case "8":
                    $('#dvNotes, #dvAttachment, #spnNotesRequired, #spnAttachmentRequired').show();
                    break;
                case "13":
                    $('#<%= calendar.ClientID %>').val('');
                    $('#dvCalendar, #<%= ddlSTime.ClientID %>, #lblTime').show();
                    break;
                case "15":
                    $('#<%= calendar.ClientID %>').val('');
                    $('#dvCalendar').show();
                    break;
                case "14":
                    var date = new Date();
                    date.setDate(date.getDate() + 30);
                    var year = date.getFullYear();
                    var month = String(date.getMonth() + 1).padStart(2, '0');
                    var day = String(date.getDate()).padStart(2, '0');
                    $('#<%= calendar.ClientID %>').val(`${year}-${month}-${day}`);
                    $('#dvCalendar').show();
                    break;
                case "16":
                    $('#modalCustomActivity').modal('show');
                    break;
                default:
                    console.warn("Unhandled activity: ", selectedActivity);
            }
        });

        // Reset all fields when modal is closed
        $('#modalActivities').on('hidden.bs.modal', function () {
            $('#<%= ddlActivity.ClientID %>').val('');
            $('#txtNotes, #txtCustomActivity, #calendar, #txtTime').val('');
            $('#fuAttachment').val('');
            $('#<%= ddlSTime.ClientID %>').val('');
            $('#dvNotes, #dvAttachment, #dvCalendar, #dvInvitation, #dvCustomActivity, #activityInfo').hide();
        });

        // Close modal and clear fields on cancel button click
        $('#modalActivities button[data-dismiss="modal"]').on('click', function () {
            $('#<%= ddlActivity.ClientID %>').val('');
            $('#txtNotes, #txtCustomActivity, #calendar, #txtTime').val('');
            $('#fuAttachment').val('');
            $('#dvNotes, #dvAttachment, #dvCalendar, #dvInvitation, #dvCustomActivity').hide();
        });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            // Open modal on button click (assuming 'open-activity' class is being used)
            $(document).on('click', '.open-custom-activity', function () {
                var leadId = $(this).attr('leadid');
                // Set the hidden field with the leadId value
                $('#<%= hidleadId.ClientID %>').val(leadId); // Set the hidden field value

                // Show the modal
                $('#modalCustomActivity').modal('show');
            });

            // Reset all fields when modal is closed (cancel button or when modal is hidden)
            $('#modalCustomActivity').on('hidden.bs.modal', function () {
                // Clear all text inputs, textareas, and file uploads
                $('#txtAdditionalNotes').val('');
                $().val('#fuCustomAttachment');
                $('#txtCustomActivity').val('');

            });

            // Close the modal and clear fields when cancel button is clicked
            $('#modalCustomActivity button[data-dismiss="modal"]').on('click', function () {
                // Reset fields
                $('#txtAdditionalNotes').val('');
                $('#fuCustomAttachment').val('');
                $('#txtCustomActivity').val('');
            });
        });

        function clearFileInputOnSubmit() {
            var fileInput = document.getElementById('fuCustomAttachment');
            if (fileInput) {
                fileInput.value = ''; // Reset the file input
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            // Function to update the character count for a given textbox and counter
            function updateCharacterCount($textBox, $counter) {
                var maxLength = $textBox.prop('maxLength');

                // Calculate remaining characters
                var remainingChars = maxLength - $textBox.val().length;

                // Update the character count
                $counter.text(remainingChars + '/' + maxLength);

                // Provide visual feedback when reaching the limit
                if (remainingChars < 0) {
                    $counter.css('color', 'red'); // Change color to red when exceeding the limit
                } else {
                    $counter.css('color', ''); // Reset color
                }
            }

            // Attach event handlers to update the character count on input
            $('#<%= txtAdditionalNotes.ClientID %>').on('input', function () {
            updateCharacterCount($(this), $('#charCountNotes'));
        });

        $('#<%= txtNotes.ClientID %>').on('input', function () {
            updateCharacterCount($(this), $('#charCountNote'));
        });

        // Initialize the character count on page load
        updateCharacterCount($('#<%= txtAdditionalNotes.ClientID %>'), $('#charCountNotes'));
        updateCharacterCount($('#<%= txtNotes.ClientID %>'), $('#charCountNote'));
    });
</script>

    <script type="text/javascript">
        function loadDetails(leadId, prospectId, orgName) {
            // Default to 0 if leadId or prospectId is null
            leadId = leadId || 0;
            prospectId = prospectId || 0;

            var crle_orgName = orgName;
            console.log(crle_orgName);
            // Show a loading message in the modal content area
            $('#dvpopupdetails').html('<div>Loading .. </div>');

            // Construct the URL with the parameters
            var url = `/Business/ViewCRMActivities/ViewActivity?leadId=${leadId}&prospectId=${prospectId}&crle_orgName=${encodeURIComponent(crle_orgName)}`;

            // Load the content dynamically into the modal
            $('#dvpopupdetails').load(url);

            // Set the title for the modal using crle_orgName passed in the query string
            $('#lblTitle').text(crle_orgName + ' - CRM Activities');
        }
    </script>
    

    <style>
        .btn-no-border {
            background-color: transparent;
            border: none;
            padding: 0;
            margin: 0;
        }

        /*.modal-backdrop.show + .modal-backdrop.show,
        .modal-backdrop.show + div + .modal-backdrop.show{
            z-index: 1050;
        }*/


        #modalImage {
            width: 400px;
            height: 400px;
            object-fit: contain;
        }

        .time-gap {
            margin-top: 10px;
        }

        button.btn-no-border {
            cursor: pointer;
        }

        .hiddenRow {
            padding: 0 !important;
        }

        tr[data-toggle="collapse"] {
            cursor: pointer;
        }

        tr[aria-expanded="true"] > td .action_arrow .fa-chevron-down::before {
            content: "\f077";
        }

        .upload_btnicon, .qrqcode_btnicon {
            width: 130px;
            background: #ececec;
        }

        #txtFileName {
            margin-top: 10px;
            display: block;
            font-weight: bold;
        }

        .upload_qrqcode_wrap {
            margin-top: 10px;
        }

        #ImgPreview_wap0 {
            position: relative; /* Ensure positioning for the close button */
        }

        #closeImgPreview {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #fff;
            border: none;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
        }

            #closeImgPreview:hover {
                color: black;
            }
    </style>

</asp:Content>
