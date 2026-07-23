<%@ Page Language="C#" AutoEventWireup="true" MaintainScrollPositionOnPostback="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Transaction Register" CodeBehind="Daybook.aspx.cs" Inherits="RetalineProAgent.Finance.WebForm1" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
     <a href="/Navigations/AccountBooks"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div class="d-flex align-items-center">
            <h6 class="slim-pagetitle">Transaction Register</h6>
            <div class="d-inline-block"> 
                <div class="btn-group ml-3" style="height: 25px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="line-height: 100%;font-size:16px;">
                        <i class="fa fa-sliders"></i>
                    </button>                                        
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item"  href="Daybook?et=2">Manual Vouchers</a></li>
                        <li><a class="dropdown-item" href="Daybook?et=1">System Vouchers</a></li>
                        <li><a class="dropdown-item" href="Daybook">All Vouchers</a></li>
                    </ul>
                    </div> 
            </div>            
        </div>
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="/Content/css/custom/Finance/custom.css">
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
 
        <div class="row row-sm">
            <div class="col-12">
                <div class="card">
                    <div class="card-header shadow_top">                      
                        <div class="row row-sm">
                           <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                                <div class="form-group d-flex row row-sm mb-0">
                                    <div class="input-group input-group col-6 col-sm-6 mb-2 mb-sm-0">
                                        <label for="FromDate" class="mb-0 w-100">From</label>
                                        <%--<input name="ctl00$cpMainContent$txtVoucherDate" type="date" id="fromdate" class="form-control " dataformatstring="{0:dd-MM-yyyy}">--%>
                                         <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date"/>
                                    </div>

                                    <div class="input-group input-group col-6 col-sm-6 mb-2 mb-md-0">
                                        <label for="ToDate" class="mb-0 w-100">To</label>
                                     <%--   <input name="ctl00$cpMainContent$txtVoucherDate" type="date" id="todate" class="form-control " dataformatstring="{0:dd-MM-yyyy}">
                                       --%>   <asp:TextBox ID="txtToDate" CssClass="form-control"  runat="server" TextMode="Date"/>
                                    </div>
                                    
                                </div>
                            </div>
                            <!--col-lg-7-->
                             <div class="col-12 col-lg-8 d-flex align-content-end">                                 
                                 <div class="row row-sm d-flex align-content-end w-100">                                    
                                     <div class="form-group col-10 d-flex col-sm-10 mb-3 mb-sm-0">
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                    
                                        <asp:Button ID="btnsearch" CssClass="btn btn-primary ml-2" runat="server" autocomplete="off" Text="GO" />
                                    </div>
                                     <div class="form-group col-2 col-sm-2 mb-0 pr-0 d-flex">
                                         <asp:LinkButton runat="server" ID="lnkExport1" CssClass="btn p-0 mr-2" OnClick="lnkExport1_Click" ToolTip="Export File">
                                          <i class="fa-light fa-arrow-down-to-bracket tx-20"></i>
                                         </asp:LinkButton>
                                         <asp:LinkButton runat="server" ID="lnbtndownload" OnClick="lnbtndownload_Click" CssClass="btn p-0" ToolTip="Download File">
                                         <i class="fa-light fa-file-arrow-down tx-20"></i>
                                         </asp:LinkButton>
                                     </div>
                                 </div>
                             </div>
                            <!--col-lg-4-->
                        </div>

                    </div>
                    <div class="card-body ">
                    <div class="table-responsive">
                        <table class="table table-bordered" cellspacing="0" rules="all"  id="cpMainContent_gvDataEntry">
                          <thead>
                            <tr class="border-top">                              
                              <th>Date</th>
                              <th>Trans ID</th>
                              <th>Voucher No.</th>
                              <th>Voucher Type</th>
                              <th>Led. ID</th>
                              <th>Ledger Name</th>
                              <th>Reference </th>
                              <th>Debit</th>
                              <th>Credit</th>
                              <th>Action</th>
                            </tr>
                          </thead>
                          <tbody> 
                            <asp:ListView ID="lvdatatable" OnSelectedIndexChanged="lvdatatable_SelectedIndexChanged" runat="server" DataSourceID="SDSDataEntry">                            
                            <ItemTemplate>
                            <tr>
                              <td style="vertical-align: middle; line-height: 100%; width:120px;"><%# ((DateTime)Eval("dateforshow")).ToString("dd/MMM/yyyy hh:mm tt")%></td>
                              <td style="vertical-align: middle; width:90px;">
                                <%# Eval("transid")%>                                
                              </td>
                              <td style="vertical-align: middle; word-break:break-all; width:100px;">
                                <%# Eval("voucherSlNoString")%>
                              </td>     
                              <td style="vertical-align: middle; width:100px;">
                                <%# Eval("Voucher")%>
                              </td>  
                              <td style="vertical-align: middle; width:90px;">
                                <%# Eval("ledger_id")%>
                              </td>  
                              <td style="vertical-align: middle; word-break:break-all; width:130px;">
                                <%# Eval("particulars")%>
                              </td>  
                              <td style="vertical-align: middle; width:200px;"">
                                <%# Eval("reference")%>
                              </td>  
                              <td style="vertical-align: middle; text-align: right;width:70px;">
                                <%# Eval("dr_amount", "{0:F2}") %>
                              </td>  
                              <td style="vertical-align: middle;text-align: right;width:70px;">
                                <%# Eval("cr_amount", "{0:F2}") %>
                              </td>  
                                <td align="right" style="vertical-align: middle;width:70px;">
                                <button type="button" id="btnDetails" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#personalModal" data-id='<%# Eval("id") %>' onclick="loadVoucherDetails(<%# Eval("id") %>)">Details</button>                                </td>
                              </td>
                            </tr>                                                                                            
                             </ItemTemplate>                               
                              </asp:ListView>
                               <SelectedRowStyle CssClass="selectrow" />                             
                               </table>
                         <asp:SqlDataSource runat="server" ID="SDSDataEntry" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                              SelectCommand="select t.id as transid,de.id,t.createdOn,de.createdOn as dateforshow,de.voucherSlNoString,t.ledger_id,entry_type,de.entity_id,vt.name AS Voucher,t.amount,t.isDebtor,
                                            t.particulars,t.reference,CASE WHEN [isDebtor] = 1 THEN  t.amount  END AS dr_amount,
                                            CASE WHEN [isDebtor] =0 THEN  t.amount  END AS cr_amount from transactions t
                                            inner join data_entry de  on t.data_entry_id =de.id LEFT JOIN 
                                            voucher_type vt ON de.voucher_type_id = vt.id   WHERE (TRIM(@search) LIKE ''
                                            OR de.voucherSlNoString LIKE CONCAT('%', @search, '%') OR t.particulars LIKE CONCAT('%', @search, '%')
                                            OR de.entity_id LIKE CONCAT('%', @search, '%') or vt.name LIKE CONCAT('%', @search, '%')) and (@fromDate is null or @fromDate = '' or 
                                CAST(de.createdOn AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate is null or @toDate = '' or 
                                CAST(de.createdOn AS DATE) <= CAST(@toDate AS DATE)) order by de.createdOn asc">
                                <SelectParameters>
                                    <asp:ControlParameter Name="search" ControlID="txtSearch"  ConvertEmptyStringToNull="false"  />                                                               
                            <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                            <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" />              
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
    </div>
      <!-- Modal -->
    <div class="modal fade" id="personalModal" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>


                    <div id="dvpopupvoucherdetails">
                    </div>

                </div>

            </div>
        </div>
    </div>
    <style>
        .mar_select {
                height: 30px!important;
                padding-top: 5px;
                padding-bottom: 5px;
        }
        .td_link::after {
            content:'';
            width:100%;
            height:100%;
            position:absolute;
            top:0;
            left:0;
        }
        tr.selectrow  {
            background-color:#e6eff9;
        }
         .table-responsive th > a {
            color:#343a40;
        }
    </style>

    <script type="text/javascript">
        function loadVoucherDetails(id) {
            $('#dvpopupvoucherdetails').html('<div>Loading .. </div>');
            $('#dvpopupvoucherdetails').load('/Finance/VouchuerDetails?id=' + id);
        }
    </script>
</asp:Content>

