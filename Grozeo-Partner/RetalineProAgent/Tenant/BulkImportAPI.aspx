<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="BulkImportAPI.aspx.cs" Inherits="RetalineProAgent.BulkImportAPI" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
        
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Tenant/API_connector"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">API/Connectors</h6>
        <p class="mb-0">Seamless Integrations</p>
    </div>
</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <%--<div class="container-fluid">--%>
    <div class="row row-sm">
        <!-- First Column -->
        <div class="col-lg-7 mb-3 mb-lg-0">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <div>
                            <asp:GridView AutoGenerateColumns="false" ID="gvImportProcessData" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" DataSourceID="SDSBulkImport">
                            <Columns>
                                <asp:BoundField HeaderText="Date" DataField="updatedDate" SortExpression="updatedDate" />
                                <asp:BoundField HeaderText="Time" DataField="updatedTime" SortExpression="updatedTime" />
                                <asp:BoundField HeaderText="Total Records" DataField="totalCount" SortExpression="totalCount" />
                                <asp:BoundField HeaderText="Success" DataField="totsuccessCount" SortExpression="totsuccessCount" />
                                <asp:BoundField HeaderText="Failure" DataField="totmissedCount" SortExpression="totmissedCount" />
                                <asp:TemplateField HeaderStyle-Width="50" HeaderText="Action">
                                    <ItemTemplate>
                                        <asp:Button Enabled='<%#Convert.ToInt32(Eval("totmissedCount")) > 0? true : false %>' CssClass="btn no-border" ID="btnAction" missingERPIds='<%# Eval("fbiu_missingerpids") %>' fbiu_id='<%# Eval("fbiu_id") %>' branchId='<%# Eval("fbiu_branch") %>' totalcount='<%# Eval("totalCount") %>' successcount='<%# Eval("totsuccessCount") %>' failedcount='<%# Eval("totmissedCount") %>' dateTime='<%# Eval("updatedDateTime") %>' filename='<%# Eval("filename") %>' OnClick="btnAction_Click" runat="server" Text="View"></asp:Button>
                                    </ItemTemplate>
                                </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No data uploaded</h6>
                                </div>
                            </EmptyDataTemplate>
                        </asp:GridView>
                        </div>
                    </div>
                </div>
                <!-- card-body -->
            </div>
        </div>

            <!-- Second Column -->
            <div class="col-lg-5">
                <div class="card-body h-100 p-3">
                    <div class="content">
                        <div class="modaltitle d-flex w-100 justify-content-between">
                            <h5 class="modal-title">API - Inventory Upload&nbsp;-&nbsp;
                                <label id="lblApiBranchName" runat="server"></label>
                            </h5>
                        </div>
                        <small>If you have an API submit facility in place then you can use the following API information to submit your inventory for live inventory update.
                            <br />
                            The ERP id (or barcode) should match with the same in the master data.</small><br />
                        <br />
                        <div class="form-group">
                            <div class="col-md-12">
                                <small>Url: <b><%= String.Format("{0}api/back-office/branch_inventory", ConfigurationManager.AppSettings.Get("api.url")) %></b></small>
                                <br>
                                <small>Header:
                                    <br />
                                    Authorization:&nbsp;<b><label id="lblApiKey" runat="server">[API Key]</label></b>
                                    <br />
                                    Content-Type:&nbsp;<b>application/json</b>
                                </small>
                                <br>
                                <small>Body (e.g):
                                    <br />
                                    <b>&nbsp;{<br />
                                        &nbsp;&nbsp;"data": [<br />
                                        &nbsp;&nbsp;&nbsp;{<br />
                                        &nbsp;&nbsp;&nbsp;&nbsp;"Qty": [Number],<br />
                                        &nbsp;&nbsp;&nbsp;&nbsp;"MRP": [Number],<br />
                                        &nbsp;&nbsp;&nbsp;&nbsp;"selling_price": [Number],<br />
                                        <% if (hasDiscountSellingPrice == true)
                                        { %>
        &nbsp;&nbsp;&nbsp;&nbsp;"discount_selling_price": [Number],<br />
                                        <!-- Add this row based on condition -->
                                        <% } %>
                                        &nbsp;&nbsp;&nbsp;&nbsp;"erpId": "[String]"<br />
                                        &nbsp;&nbsp;&nbsp;},
                                        <br />
                                        &nbsp;&nbsp;&nbsp;{<br />
                                        &nbsp;&nbsp;&nbsp;&nbsp;"Qty": [Number],<br />
                                        &nbsp;&nbsp;&nbsp;&nbsp;"MRP": [Number],<br />
                                        &nbsp;&nbsp;&nbsp;&nbsp;"selling_price": [Number],<br />
                                        <% if (hasDiscountSellingPrice == true)
                                        { %>
        &nbsp;&nbsp;&nbsp;&nbsp;"discount_selling_price": [Number],<br />
                                        <!-- Add this row based on condition -->
                                        <% } %>
                                        &nbsp;&nbsp;&nbsp;&nbsp;"erpId": "[String]"<br />
                                        &nbsp;&nbsp;&nbsp;},<br />
                                        &nbsp;&nbsp;&nbsp;....<br />
                                        &nbsp;&nbsp;]<br />
                                        &nbsp;}<br />
                                    </b>
                                    <br>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <%--</div>--%>
    <asp:SqlDataSource runat="server" ID="SDSBulkImport" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT DISTINCT iu.fbiu_id,iu.fbiu_branch,iu.fbiu_missingerpids,(IF(iu.fbiu_uploadedbyapi = 1,IFNULL(LENGTH(IFNULL(iu.fbiu_missingerpids, '')) - LENGTH(REPLACE(IFNULL(iu.fbiu_missingerpids, ''), ',', '')) + 
CASE WHEN iu.fbiu_missingerpids IS NOT NULL AND iu.fbiu_missingerpids != '' THEN 1 ELSE 0 END, 0),iu.missedCount) + IF(iu.fbiu_uploadedbyapi = 1,
(SELECT COUNT(*) FROM finascop_stock_branch_inventory_upload_detail d WHERE d.fbiu_id = iu.fbiu_id),iu.successCount)) AS totalCount,
IF(iu.fbiu_uploadedbyapi = 1,IFNULL(LENGTH(IFNULL(iu.fbiu_missingerpids, '')) - LENGTH(REPLACE(IFNULL(iu.fbiu_missingerpids, ''), ',', '')) +
CASE WHEN iu.fbiu_missingerpids IS NOT NULL AND iu.fbiu_missingerpids != '' THEN 1 ELSE 0 END, 0),iu.missedCount) AS totmissedCount,
IF(iu.fbiu_uploadedbyapi = 1,(SELECT COUNT(*) FROM finascop_stock_branch_inventory_upload_detail d WHERE d.fbiu_id = iu.fbiu_id),iu.successCount) AS totsuccessCount,
DATE_FORMAT(iu.fbiu_updatedOn, '%d %b %Y') AS updatedDate,TIME(iu.fbiu_updatedOn) AS updatedTime,CONCAT(DATE_FORMAT(iu.fbiu_updatedOn, '%d %b %Y'), ' ', TIME(iu.fbiu_updatedOn)) AS updatedDateTime,filename 
FROM finascop_stock_branch_inventory_upload iu WHERE iu.fbiu_branch=@branchid AND fbiu_uploadedbyapi = 1 ORDER BY fbiu_id DESC LIMIT 10"
        OnSelecting="SDSBulkImport_Selecting">
        <SelectParameters>
            <asp:Parameter Name="branchid" />
        </SelectParameters>
    </asp:SqlDataSource>

    <asp:HiddenField ID="hidId" runat="server" />
    <div id="ErrorDetails" class="modal" data-backdrop="static">
        <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
            <div class="modal-content bd-0 tx-14 ">
                <div class="modal-header">
                <h4 class="modal-title" style="font-size: 16px; color: #333;">Upload Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
                <div class="modal-body" style="height: 400px; overflow: auto;">
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal" style="font-size: 14px; color: #555;">
                            Date:
   
                            <asp:Literal ID="ltrDate" runat="server" Text=""></asp:Literal>
                        </div>
                        <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal" style="font-size: 14px; color: #555;">
                            Total Records:
                            <asp:Literal ID="ltrTtlRecords" runat="server" Text=""></asp:Literal>
                        </div>
                        <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal" style="font-size: 14px; color: #555;">
                            Success:
                            <asp:Literal ID="ltrSuccess" runat="server" Text=""></asp:Literal>
                        </div>
                        <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal" style="font-size: 14px; color: #555;">
                            Failed:
                            <asp:Literal ID="ltrFailed" runat="server" Text=""></asp:Literal>
                        </div>
                    </div>
                    <div class="modaltitle mb-2 d-flex w-100 justify-content-between">
                        <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal" style="font-size: 14px; color: #555;">
                            Store:
                            <asp:Literal ID="ltrStoreName" runat="server" Text=""></asp:Literal>
                        </div>
                        <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal" style="font-size: 14px; color: #555;">
                            File Name:
                            <asp:Literal ID="ltrFileName" runat="server" Text=""></asp:Literal>
                        </div>
                    </div>
                    <div class="section-wrapper p-0 border-0" id="failedRecTable" runat="server" visible="false">
                        <label class="tx-14 w-100">Failed Records</label>
                        <div class="table-responsive" style="max-height:300px;">
                            <table id="errorDetailsTable" class="table table-bordered table-head-fixed" cellspacing="0">
                                <thead class="custom-header">
                                    <tr>
                                        <th style="padding: 0.75rem; font-size: 14px; text-align: left; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; width: 150px;">Item ID</th>
                                        <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                                            { %>
                                        <th style="padding: 0.75rem; font-size: 14px; text-align: left; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; width: 150px;">MRP</th>
                                        <% }
                                        else
                                        { %>
                                        <th style="padding: 0.75rem; font-size: 14px; text-align: left; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; width: 150px;">RRP</th>
                                        <% } %>
                                        <th style="padding: 0.75rem; font-size: 14px; text-align: left; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;">Error Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <asp:Repeater ID="rptDetails" runat="server" DataSourceID="SDSListDetails">
                                    <ItemTemplate>
                                        <tr>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("stit_id") %></td>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; text-align: right;"><%# Eval("mrp") %></td>
                                            <td style="padding: 0.75rem; font-size: 14px; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;"><%# Eval("comment") %></td>
                                        </tr>
                                    </ItemTemplate>
                                </asp:Repeater>
                            </tbody>
                            </table>
                        </div>
                    </div>
                    <!--section-wrapper-->

                    <div id="apiFailedRecTable" runat="server" visible="false" class="section-wrapper p-0 border-0">
                        <label class="tx-14 w-100">Missing ERP IDs</label>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-bordered table-head-fixed" cellspacing="0">
                                <thead class="custom-header">
                                    <tr>
                                        <th style="padding: 0.75rem; font-size: 14px; text-align: left;">ERP ID</th>
                                        <th style="padding: 0.75rem; font-size: 14px; text-align: left;">Error Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <asp:Repeater ID="rptApiDetails" runat="server">
                                        <ItemTemplate>
                                            <tr>
                                                <td style="padding: 0.75rem; font-size: 14px;"><%# Eval("erpid") %></td>
                                                <td style="padding: 0.75rem; font-size: 14px;"><%# Eval("comment") %></td>
                                            </tr>
                                        </ItemTemplate>
                                    </asp:Repeater>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <!--modal-body-->
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->

    <asp:SqlDataSource runat="server" ID="SDSListDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT fbiu_id, stit_id, branch_id, mrp, comment FROM inventory_upload_error_log WHERE fbiu_id = @fbiu_id">
        <SelectParameters>
            <asp:ControlParameter ControlID="hidId" PropertyName="Value" Name="fbiu_id" DefaultValue="0" />
    </SelectParameters>
    </asp:SqlDataSource>

    <script type="text/javascript">
        function communicationsection(obj) {
            var fbiu_id = $(obj).attr('fbiu_id');
            $('#<%= hidId.ClientID %>').val(fbiu_id);
            $('#ErrorDetails').modal('show');
        }
    </script>

    <style>
        .btn.no-border {
            background: none;
            border: none;
            color: green;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
        }

            .btn.no-border:disabled {
                color: gray;
                text-decoration: none;
                cursor: default;
            }
    </style>
</asp:Content>


