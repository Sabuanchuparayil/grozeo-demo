<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" ValidateRequest="false" CodeBehind="FAQ.aspx.cs" Inherits="RetalineProAgent.FAQ" %>
<asp:Content ContentPlaceHolderID="head" runat="server">
  <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
    <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Appearance">Appearance</a></li>
    <li class="breadcrumb-item"><a href="/navigations/ContentsPages">Content Pages</a></li>
    <li class="breadcrumb-item active" aria-current="page">FAQ & Support</li>--%>
    <a href="/Navigations/ContentsPages"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">FAQs & Support</h6>
        <p class="mb-0">FAQs & Support</p>
    </div>
</asp:Content>


<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <%--<div class="card-header">
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
              </nav>

            </div>
                  
              </div> 
                </div><br />
                    </div>--%>
                <div class="card-body">
                    <div class="row row-sm">
            
               <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvFAQ" runat="server" CssClass="table table-bordered gridview_table" 
                                     AllowSorting="true" ShowFooter="false" OnDataBound="gvFAQ_DataBound" DataSourceID="SDSFAQ">
                                    <Columns>
                                        <asp:BoundField HeaderText="Question" DataField="faq_title" SortExpression="faq_title"/>
                                        <asp:BoundField HeaderText="Answer" DataField="faq_description" SortExpression="faq_description"/>
                                        <%--<asp:TemplateField HeaderText="Status" ItemStyle-CssClass="action" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black">
                                        <ItemTemplate>
                                            <div class="toggle-wrapper"><div class="toggle toggle-light success" data-toggle-on="<%# Eval("faq_status").ToString().Equals("1") ? "true":"false" %>" ></div></div>
                                            <asp:CheckBox ID="chkStatus" OnCheckedChanged="chkStatus_CheckedChanged" style="display: none;" runat="server" AutoPostBack="true" faqId='<%# Eval("faq_id") %>' activeId='<%# Eval("faq_status") %>' Checked='<%# Eval("faq_status").Equals(1) %>'/>
                                        </ItemTemplate>
                                    </asp:TemplateField>--%>
                                    </Columns>
                                     <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No record available</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                </asp:GridView>
                   

                                <asp:SqlDataSource runat="server" ID="SDSFAQ" ProviderName="MySql.Data.MySqlClient" ConnectionString ="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT faq_id,faq_title,faq_description,faq_status,IF((faq_status=1),'Active','Inactive') AS faqstatus FROM app_faqs ORDER BY faq_id DESC">
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