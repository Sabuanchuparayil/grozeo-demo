<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Marketing/MarketingMaster.master" CodeBehind="Contact.aspx.cs" Inherits="RetalineProAgent.Marketing.Contact" %>


<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessCRM">CRM</a></li>
    <li class="breadcrumb-item active" aria-current="page">Contacts</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Contacts"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>--%> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                                    <div class="col-lg-4">
                                          <%--<label class="form-control-label w-100 mb-1">Search: </label>--%>
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <div class="d-flex">
                                            <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name, number, email etc." CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" style="height:33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
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
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvContacts_DataBound" DataSourceID="SDSContacts">
                                    <Columns>
                                        <asp:TemplateField HeaderText="Store Name" SortExpression="crco_orgName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate>
                                                <a href="https://maps.google.com/?q=<%# Eval("glatitude") %>,<%# Eval("glongitude") %>" target="_blank"><i class="fa fa-map-marker"></i></a>&nbsp;
                                                <%# Eval("crco_orgName") %><br /><small><%# Eval("crco_location") %></small></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Contact" SortExpression="crco_indMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate><%# Eval("crco_indMobile") %><br /><small><%# Eval("crco_indContactperson") %></small></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Email Address" SortExpression="email" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                            <ItemTemplate><%# Eval("email") %><br /><small><%# Eval("businessCategory") %></small>-<small><%# Eval("activeServices") %></small></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Type" DataField="contactType" SortExpression="contactType" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <%--<asp:BoundField HeaderText="Contact Mode" DataField="contactMode" SortExpression="contactMode" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>--%>
                                        <asp:BoundField HeaderText="Created" DataField="created_From" SortExpression="created_From" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Created By" DataField="createdBy" SortExpression="createdBy" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>                                                                              
                                         <asp:BoundField HeaderText="Created On" DataField="crco_CreatedOn" SortExpression="crco_CreatedOn" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>                                                                               
                                        <asp:BoundField HeaderText="Status" DataField="contactStatus" SortExpression="contactStatus" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Business/ContactSettings" DataNavigateUrlFields="id" DataNavigateUrlFormatString="~/Business/ContactSettings?id={0}" />
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No contacts.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSContacts" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "  SELECT fcc.id, crco_orgName,crco_type, NAME AS contactType,`crco_CreatedOn`,
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
                                    THEN (SELECT roName FROM relationship_officer WHERE id = crco_CreatedBy) END AS createdBy,crco_CreatedFrom
                                    FROM finascop_crm_contact fcc 
                                    INNER JOIN crm_contact_type cct ON cct.id = crco_type 
                                    WHERE ((@areaId > 0 AND fcc.id IN(SELECT contactId FROM finascop_crm_lead WHERE areaId = @areaId)) or (crco_CreatedFrom = 2 AND crco_CreatedBy = @baId) OR (crco_CreatedFrom = 3 AND crco_CreatedBy IN(SELECT GROUP_CONCAT(id) FROM relationship_officer WHERE roBusAssociate = @baId)))
                                    AND (trim(ifnull(@searchKey, '')) like '' or crco_orgName like CONCAT('%', @searchKey, '%') or crco_indContactperson like CONCAT('%', @searchKey, '%') or crco_orgEmail like CONCAT('%', @searchKey, '%') or crco_indMobile like CONCAT('%', @searchKey, '%') or crco_type like CONCAT('%', @searchKey, '%')) ORDER BY crco_orgName ASC" OnSelecting="SDSContacts_Selecting">
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
