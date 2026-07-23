<%@ Page Language="C#" AutoEventWireup="true" Title="Business Portal" MasterPageFile="~/Business/BusinessMaster.master" CodeBehind="Default.aspx.cs" Inherits="RetalineProAgent.Business.Default" %>

<%@ Import Namespace="System.Configuration" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">
<script src="/Content/js/custom/associate.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server"></asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
            <h6 class="slim-pagetitle">My Business</h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">



    <div class="row row-sm">
        <div class="col-lg-3 mb-3 mb-lg-4">
            <div class="card h-100">
                <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets ">
                    <div class="d-flex align-items-center dash_title mb-4">
                        <%--<i class="fa-thin fa-bags-shopping mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>--%>
                        <i class="icon ion-ios-cart-outline tx-purple mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Contacts</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                        <h3 id="ltrContacts" class="homeloading" runat="server">
                            <div class="lodingbusy">
                                <div class="dot"></div>
                                <div class="dot"></div>
                                <div class="dot"></div>
                            </div>
                        </h3>
                        <span class="dsb_card_dataerrormsg">No contacts.</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                        <a class="btn dash-btn" href="/Business/ClientManagement?type=contact">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                </div>
            </div>
            <!--card-->
        </div>
        <!--col-lg-3-->

        <div class="col-lg-3 mb-3 mb-lg-4">
            <div class="card h-100">
                <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                    <div class="d-flex align-items-center dash_title mb-4">
                       <i class="fa-thin fa-comments mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Leads</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                        <h3 id="ltrLeads" class="homeloading" runat="server">
                            <div class="lodingbusy">
                                <div class="dot"></div>
                                <div class="dot"></div>
                                <div class="dot"></div>
                            </div>
                        </h3>
                        <span class="dsb_card_dataerrormsg">No Leads.</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                        <a class="btn dash-btn" href="/Business/ClientManagement?type=lead">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                </div>
            </div>
            <!--card-->
        </div>
        <!--col-lg-3-->

        <div class="col-lg-3 mb-3 mb-lg-4">
            <div class="card h-100">
                <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                    <div class="d-flex align-items-center dash_title mb-4">
                        <i class="icon ion-ios-pricetag-outline tx-teal mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Prospects</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                        <h3 id="ltrProspects" class="homeloading" runat="server">
                            <div class="lodingbusy">
                                <div class="dot"></div>
                                <div class="dot"></div>
                                <div class="dot"></div>
                            </div>
                        </h3>
                        <span class="dsb_card_dataerrormsg">No prospects.</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                        <a class="btn dash-btn" href="/Business/ClientManagement?type=lead">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                </div>
            </div>
            <!--card-->
        </div>
        <!--col-lg-3-->

        <div class="col-lg-3 mb-3 mb-lg-4">
            <div class="card h-100">
                <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                    <div class="d-flex align-items-center dash_title mb-4">
                       <i class="fa-thin fa-store mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Retailers</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                        <h3 id="ltrRetailers" class="homeloading" runat="server">
                            <div class="lodingbusy">
                                <div class="dot"></div>
                                <div class="dot"></div>
                                <div class="dot"></div>
                            </div>
                        </h3>
                        <span class="dsb_card_dataerrormsg">No retailers.</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                        <a class="btn dash-btn" href="/Business/CRMRetailers">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                </div>
            </div>
            <!--card-->
        </div>
        <!--col-lg-3-->
    </div>

    <div class="row row-sm">
    
   <div class="col-lg-6">
    <h6 class="slim-pagetitle mt-1 mt-lg-0 mb-2 pt-2 pb-2">Leads</h6>

    <div class="card mb-4">
              <div class="card-body">
              <div class="table-responsive">
                <table class="table mg-b-0 tx-13">
                    <thead>
                        <tr class="tx-10">
                            <th class="pd-y-5">Store Name</th>
                            <th class="pd-y-5">Contact Number</th>
                            <th class="pd-y-5">Contact Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <asp:SqlDataSource ID="SDSLeads" runat="server" OnSelecting="SDSLeads_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand=" SELECT fcl.id,crle_orgName,crle_orgContactNo,fcl.crmuId,cct.name AS 'Contact Type' 
                                            FROM finascop_crm_lead fcl INNER JOIN crm_contact_type cct 
                                            ON fcl.crle_type=cct.id WHERE fcl.baId=@baId AND fcl.crmuId NOT IN (3,7) AND cct.id IN(1,3) ORDER BY fcl.id DESC LIMIT 5"
                            ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters>
                                <asp:Parameter Name="baId" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                        <asp:Repeater ID="rptLeads" runat="server" DataSourceID="SDSLeads">
                            <ItemTemplate>
                                <tr>
                                    <td><%# Eval("crle_orgName") %></td>
                                    <td><%# Eval("crle_orgContactNo") %></td>
                                    <td><%# Eval("Contact Type") %></td>
                                </tr>


                            </ItemTemplate>
                            <FooterTemplate>
                                <tr>
                                    <td colspan="3" class="py-0">
                                        <asp:Label ID="lblLeadsCount" runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>' Text="No Leads available" /></td>
                                </tr>

                            </FooterTemplate>
                        </asp:Repeater>

                    </tbody>
                </table>
            </div>
            <!-- table-responsive -->
            <div class="card-footer tx-12 pd-y-15 bg-transparent">
                <a href="/Business/ClientManagement?type=lead"><i class="fa fa-angle-down mg-r-5"></i>View All</a>
            </div>
            <!-- card-footer -->
        </div>

    </div>
</div>



   <div class="col-lg-6">
   <h6 class="slim-pagetitle mt-1 mt-lg-0 mb-2 pt-2 pb-2">Prospects</h6>

    <div class="card mb-4">
              <div class="card-body">
              <div class="table-responsive">
                <table class="table mg-b-0 tx-13">
                    <thead>
                        <tr class="tx-10">
                            <th class="pd-y-5">Store Name</th>
                            <th class="pd-y-5">Contact Number</th>
                            <th class="pd-y-5">State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <asp:SqlDataSource ID="SDSProspect" runat="server" OnSelecting="SDSProspect_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT id, crpr_orgName,fb.store_group_name, crpr_indMobile, crpr_gplace, storeGroupId FROM  finascop_crm_prospect fc
                                           LEFT JOIN finascop_branch_group fb ON fc.storeGroupId=fb.store_group_id WHERE areaId=@areaId and baId=@baId AND IFNULL(storeGroupId, 0) <= 0 ORDER BY id DESC 
                                LIMIT 5"
                            ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters>
                                <asp:Parameter Name="baId" />
                                <asp:Parameter Name="areaId" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                        <asp:Repeater ID="rptProspects" runat="server" DataSourceID="SDSProspect">
                            <ItemTemplate>
                                <tr>
                                    <td><%# Eval("crpr_orgName") %></td>
                                    <td><%# Eval("crpr_indMobile") %></td>
                                    <td><%# Eval("crpr_gplace") %></td>
                                </tr>


                            </ItemTemplate>
                            <FooterTemplate>
                                <tr>
                                    <td colspan="3" class="py-0">
                                        <asp:Label ID="lblProspectCount" runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>' Text="No prospects available" /></td>
                                </tr>

                            </FooterTemplate>
                        </asp:Repeater>

                    </tbody>
                </table>
            </div>
            <!-- table-responsive -->
            <div class="card-footer tx-12 pd-y-15 bg-transparent">
                <a href="/Business/ClientManagement?type=lead"><i class="fa fa-angle-down mg-r-5"></i>View All</a>
            </div>
            <!-- card-footer -->
        </div>

    </div>
</div>

  </div>


    <script src="/content/lib/chart.js/js/Chart.js"></script>

<script type="text/javascript">
    associate.url.getDashboard = '/api/home/AssociateDashboardValues';
    associate.controls.newContacts = '<%= ltrContacts.ClientID %>';
    associate.controls.newLeads = '<%= ltrLeads.ClientID %>';
    associate.controls.newProspects = '<%= ltrProspects.ClientID %>';
    associate.controls.newRetailers = '<%= ltrRetailers.ClientID %>';
</script>



</asp:Content>