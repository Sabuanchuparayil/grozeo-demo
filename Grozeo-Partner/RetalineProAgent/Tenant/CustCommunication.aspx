<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Communications" AutoEventWireup="true" CodeBehind="CustCommunication.aspx.cs" Inherits="RetalineProAgent.CustCommunication" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
    <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Support">Support</a></li>
    <li class="breadcrumb-item active" aria-current="page">Customer Communications</li>--%>
    <a href="/Navigations/Support"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Customer Communications"></asp:Literal> 
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> 
            </h6>
        <p class="mb-0">Communicate and stay connected</p>
    </div>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                      <div class="d-inline-block w-100 tx-center py-4">
                          <asp:Image runat="server" ID="imgId" CssClass="img-fluid" style="opacity: 0.9; max-width:450px; width: 100%;" ImageUrl="/content/images/nodata.png"/>
                      </div>
                    </div><!--card body-->
              </div>
            </div>
          <div class="col-12">
            <div class="card" runat="server" visible="false">
                <div class="card-header">
                  <div class="float-left">
                      <div class="card-tools">
                <div class="col-8 col-lg-10">
                <nav class="navbar navbar-expand-lg bg-transparent p-0 justify-content-start">
                <a class="navbar-brand d-lg-none tx-dark tx-14" href="#">Filter by</a>
                <button class="navbar-toggler p-0 " type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon bg-darck d-flex align-items-center">
                    <i class="fa fa-sliders" aria-hidden="true"></i>
                  </span>
                </button>
              
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <ul class="navbar-nav mr-auto">
                      <li class="nav-item active mx-1">
                        <asp:LinkButton ID="lbtnSMS" runat="server" typeid="1" OnClick="btnFilterType_Click" CssClass="nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 active">SMS <span class="sr-only">(current)</span></asp:LinkButton>
                    </li>
                    <li class="nav-item mx-1">
                        <asp:LinkButton ID="lbtnEmail" runat="server" typeid="2" OnClick="btnFilterType_Click" CssClass="nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2">Email</asp:LinkButton>
                    </li>
                      <li class="nav-item mx-1">
                        <asp:LinkButton ID="lbtnWhatsapp" runat="server" typeid="3" OnClick="btnFilterType_Click" CssClass="nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2">Whatsapp</asp:LinkButton>
                    </li>
                  </ul>
                </div>
              </nav>

            </div>
                  
              </div> 
                </div><br />
                    </div>
                <div class="card-body">
                    <div class="row row-sm">
            
               <div class="table-responsive mailbox-messages">
                   <asp:HiddenField ID="hidFilterType" runat="server" />
                                <asp:GridView AutoGenerateColumns="false" ID="gvCustCommunication" runat="server" CssClass="table table-bordered" 
                                     AllowSorting="true" ShowFooter="false" OnDataBound="gvCustCommunication_DataBound" DataSourceID="SDSCustCommunication" OnRowDataBound="gvCustCommunication_RowDataBound">
                                    <Columns>
                                        <asp:BoundField HeaderText="Type" DataField="typeName" SortExpression="typeName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Title" DataField="title" SortExpression="title" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:BoundField HeaderText="Content"  DataField="message" SortExpression="message" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" />
                                        <asp:TemplateField HeaderText="Action"  ItemStyle-CssClass="action" HeaderStyle-BackColor="#DEE2E6">
                                        <ItemTemplate>
                                            <div class="toggle-wrapper"><div class="toggle toggle-light success <%# Eval("isRequired").ToString().Equals("1") ? "":"toggleDisabled" %>" data-toggle-on="<%# Eval("newisRequired").ToString().Equals("1") ? "false":"true" %>" ></div></div>
                                            <asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" style="display: none;" runat="server" AutoPostBack="true" commId='<%# Eval("id") %>' activeId='<%# Eval("isRequired") %>' Checked='<%# Eval("newisRequired").Equals(1) %>'/>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    </Columns>
                                </asp:GridView>
                   

                                <asp:SqlDataSource runat="server" ID="SDSCustCommunication" ProviderName="MySql.Data.MySqlClient" ConnectionString ="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT id,isActive,TYPE,CASE WHEN TYPE = 1 THEN 'SMS' WHEN TYPE = 2 THEN 'Email' WHEN TYPE = 3 THEN 'WatsApp' END AS typeName,
                                    isRequired,typeId,IF(isRequired=1,IF((SELECT COUNT(*) FROM  `communication_entry_map` WHERE `ceId`= communication_entry.id AND 
                                    `storeGroupId` = @storegroup) > 0,0,1),0) AS newisRequired,IF(isRequired = 1,'Yes','No') AS isRequiredStatus,
                                    CASE WHEN TYPE = 1 THEN (SELECT templateName FROM sms_templates WHERE sms_templates.id = typeId)  
                                    WHEN TYPE = 2 THEN '-'   WHEN TYPE = 3 THEN '-' END AS title,CASE WHEN TYPE = 1 THEN (SELECT templateContent FROM 
                                    sms_templates WHERE sms_templates.id = typeId) WHEN TYPE = 2 THEN '-'  WHEN TYPE = 3 THEN '-' END AS message  
                                    FROM  communication_entry WHERE isActive=1 AND (ifnull(@filterType, 0) = 0 or (@filterType = 1 and type IN(1))  
                                    or (@filterType = 2 and type IN(2))  
                                    or (@filterType = 3 and type IN(3))) ORDER BY id ASC" OnSelecting="SDSCustCommunication_Selecting">
                                    <SelectParameters>
                                        <asp:Parameter Name="storegroup" />
                                        <asp:ControlParameter ControlID="hidFilterType" Name="filterType" DefaultValue="0" DbType="Int32" PropertyName="Value" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
               </div>
                </div>
               </div>
                </div>
            </div>
          </div>

       <script type="text/javascript">
        $('.toggle').toggles(
            {
                //on: true,
              height: 26
            },
            //checkbox:
           );
           $('.toggleDisabled').each(function () {
           //if ($('.toggle toggle-light').hasClass('toggleDisabled')) {
               //console.log('Hello');
               $(this).parent().closest('td').addClass('disabled');
              // }
           })
        $('.toggle').on('toggle', function (e, active) {
            $(this).closest('td').find('input[type=checkbox]').trigger('click');
            $(this).addClass('processing_loader');
        });
           
       </script>

              <style>
                  td.action.disabled * {
                      pointer-events:none!important;
                  }
              </style>
</asp:Content>
