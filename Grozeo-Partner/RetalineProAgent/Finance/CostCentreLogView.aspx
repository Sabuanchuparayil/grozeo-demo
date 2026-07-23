<%@ Page Title="" Language="C#" MasterPageFile="~/Finance/FinanceMaster.master" AutoEventWireup="true" CodeBehind="CostCentreLogView.aspx.cs" Inherits="RetalineProAgent.Finance.CostCentreLogView" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Finance/Navigations/Accounting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Cost Centre Log</h6>
    <p class="mb-0">Cost Centre Log</p>
</asp:Content>
<asp:Content runat="server" Visible="false" ContentPlaceHolderID="cpNMainContent">   
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
               <div class="row row-sm justify-content-between">
            <div class="col-12 col-lg-4">
                <div class="row row-sm">
                    <div class="form-group col-12 col-md-6 mb-2 mb-lg-0 pr-md-1">
                        <label for="txtFromDate" class="tx-dark" runat="server">From</label>
                        <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                    <div class="form-group col-12 col-md-6 mb-2 mb-lg-0 pl-md-1">
                        <label for="txtToDate" class="tx-dark" runat="server">To</label>
                        <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                </div>
                
            </div>
            <div class="col-12  col-lg-8">
                <div class=" w-100 d-flex flex-wrap flex-lg-nowrap align-items-end">
                    <div class="form-group w-100 mb-2 mb-lg-0 col-12 col-md-6 pl-0 pr-0 pr-md-2">
                        <label for="seltype" class="tx-dark" runat="server">Type</label>
                        <asp:DropDownList ID="dlentrytpeupdae" CssClass="form-control py-0" AutoPostBack="true" runat="server">
                            <%--<asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                            <asp:ListItem Text="failed" Value="0"></asp:ListItem>
                            <asp:ListItem Text="success" Value="1"></asp:ListItem>
                            <asp:ListItem Text="corrected" Value="2"></asp:ListItem>
                            <asp:ListItem Text="Mannual Entry" Value="3"></asp:ListItem>
                                <asp:ListItem Text="Escalate" Value="4"></asp:ListItem>--%>                                            
                            <asp:ListItem Text="Failed" Value="0"></asp:ListItem>
                                <asp:ListItem Text="Success" Value="1"></asp:ListItem>
                            <asp:ListItem Text="Escalated" Value="4"></asp:ListItem>
                            <asp:ListItem Text="Failed & Escalated" Value="5"></asp:ListItem>
                        </asp:DropDownList>
                    </div>          
                    <div class="form-group d-flex mb-2 mb-lg-0 col-12 col-md-6 pr-0 pr-md-1 pl-0">
                           
                        <div class="input_search_box">
                            <input type="text" style="display:none" />
                            <input type="password" style="display:none" />

                            <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                            <asp:LinkButton runat="server" CssClass="input-group-append">                        
                            </asp:LinkButton>

                            <input type="text" style="display:none" />
                                <input type="password" style="display:none" />
                            <asp:LinkButton ID="lbtnSearch" dataid='<%# Eval("entity_id") %>' CssClass="btn bd bd-l-0 tx-gray-600 "  runat="server" autocomplete="off"><i class="fa fa-search mt-1"></i></asp:LinkButton>
                        </div>
                            
                        </div>
                </div>

        </div>
      </div>
                </div>

                <div class="card-body">

                    <div class="table-responsive mailbox-messages">
                <asp:GridView AutoGenerateColumns="false" ID="gvpending" runat="server" CssClass="table table-bordered gridview_table" BorderStyle="Solid" OnPageIndexChanged="gvpending_PageIndexChanged"
                    DataSourceID="SDSpendingentries" AllowPaging="true" PagerStyle-CssClass="pg_table" PageSize="10" DataKeyNames="Id" OnRowDataBound="gvpending_RowDataBound">
                    <Columns>
                        
                        <asp:BoundField HeaderText="Trans ID" ItemStyle-Width="10%" HeaderStyle-HorizontalAlign="Center" ItemStyle-HorizontalAlign="Center" DataField="id" SortExpression="id" HeaderStyle-CssClass="text-center" />
                        <asp:BoundField HeaderText="Date & Time " ItemStyle-Width="16%" ItemStyle-HorizontalAlign="left" DataField="createdon" DataFormatString="{0:MMM-dd-yyyy hh:mm tt}" SortExpression="createdon" ItemStyle-VerticalAlign="Middle" />
                        
                        <asp:BoundField HeaderText="Order" ItemStyle-Width="12%" HeaderStyle-HorizontalAlign="Center"  ItemStyle-HorizontalAlign="Center" DataField="ordername" SortExpression="ordername" ItemStyle-VerticalAlign="Middle" /> 
                        <asp:TemplateField HeaderText="Log Details" HeaderStyle-HorizontalAlign="left" ItemStyle-Width="42%" ItemStyle-CssClass="logd_details text-break" ItemStyle-VerticalAlign="Middle" >
                             <ItemTemplate>
                                 <asp:Literal ID="lbllog" runat="server"  Text='<%# RetalineProAgent.Service.Common.ShrinkText(Eval("costCentreLogData").ToString(), 75) %>'></asp:Literal><asp:LinkButton ID="btncomment" recid='<%# Eval("id") %>' runat="server" OnClick="btncomment_Click"  CssClass="ml-2">View</asp:LinkButton>                                
                             </ItemTemplate>
                        </asp:TemplateField>
                        <%--<asp:BoundField HeaderText="Log Details" ItemStyle-Width="42%" HeaderStyle-HorizontalAlign="Center"  ItemStyle-HorizontalAlign="Left" DataField="comments" SortExpression="comments" ItemStyle-CssClass="logd_details text-break" />--%>
                         <asp:BoundField HeaderText="Status" ItemStyle-Width="8%" ItemStyle-HorizontalAlign="left" DataField="Statusname" SortExpression="Statusname" />
                         <asp:TemplateField HeaderText="Action" HeaderStyle-HorizontalAlign="Center" ItemStyle-Width="12%" ItemStyle-CssClass="text-center" HeaderStyle-CssClass="text-center">
                            <ItemTemplate>
                           
                                <asp:LinkButton ID="btnaction" runat="server"   CssClass="btn btn-outline-primary btn-sm" Text="Check" recid='<%# Eval("id") %>' OnClick="btnaction_Click1" Visible='<%# ( (new int[]{1, 2,3}).Contains(Convert.ToInt32(Eval("costCentreEntryStatus"))) ? false: true) %>'  />
                                <asp:LinkButton  ID="btnimgactio" runat="server" CssClass="text-success" Font-Size="22px"   Visible='<%# ( (new int[]{0, 5,4}).Contains(Convert.ToInt32(Eval("costCentreEntryStatus"))) ? false: true) %>'><i class="fa fa-check" aria-hidden="true"></i></asp:LinkButton>
                            </ItemTemplate>
                        </asp:TemplateField>
                    </Columns>
                    <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No record available</h6>
                                </div>
                            </EmptyDataTemplate>
                     <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                      <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                </asp:GridView>
                <asp:SqlDataSource runat="server" ID="SDSpendingentries" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                    SelectCommand="SELECT id,CONVERT(datetime, SWITCHOFFSET(createdOn, DATEPART(TZOFFSET, 
                       createdOn AT TIME ZONE 'India Standard Time'))) as createdon,createdOn,order_order_id,entry_RefId,costCentreEntryStatus,
                    CONCAT(order_order_id,',',order_event) AS ordername, (case when costCentreEntryStatus=1 then 'Success' when costCentreEntryStatus=2 then 'Corrected' 
                    when costCentreEntryStatus=3 then 'Mannual Entry' when costCentreEntryStatus=4 then 'Escalated' when costCentreEntryStatus=5 then 'Failed & Escalated'  
                    else 'Failure' end ) as Statusname,costCentreLogData 
					FROM cost_centre_log  
					WHERE   (trim(@search) like '' or order_order_id like CONCAT('%', @search, '%'))  and 
                    (@fromDate is null or @fromDate = '' or CAST(createdOn AS DATE) >= CAST(@fromDate AS DATE)) AND 
                    (@toDate is null or @toDate = '' or CAST(createdOn AS DATE) <= CAST(@toDate AS DATE)) 
					AND costCentreEntryStatus=@status   ORDER BY id ASC ">
                    <SelectParameters>
                        <asp:ControlParameter Name="status" ControlID="dlentrytpeupdae" ConvertEmptyStringToNull="false" />
                        <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                         <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                            <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" /> 
                        <%--<asp:ControlParameter ControlID="selpending" Name="errors" PropertyName="Text" />--%>
                    </SelectParameters>
                </asp:SqlDataSource>
            </div>

                </div>
            </div>
            
        </div>
    </div>



    <div class="modal" id="PupEscalate" data-backdrop="static">
	<div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">       
          <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
        <div class="container"></div>
        <div class="modal-body">
         <h4>Do you want to Escalate?</h4>
        </div>
        <div class="modal-footer">
          <asp:LinkButton runat="server" OnClick="btnyes_Click" CssClass="btn btn-outline-success btn-sm py-1 px-3 mx-2" ID="btnyes"> Yes</asp:LinkButton>
          <asp:Button  data-dismiss="modal" ID="btnclose" runat="server" Text="Close" CssClass="btn btn-outline-dark btn-sm py-1 px-3 mx-2" />
        </div>
          </div>
      </div>
    </div>


<div class="modal fade" id="priviewcostcentreentrypopup" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modaltitle">
                    <button type="button" class="close position-absolute mt-2 mr-1" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-2" style="box-shadow: none;">
                            <div class="card-header py-2 px-1 border-0">
                                <div class="row row-sm">
                                    <div class="col-12 col-lg-11">
                                        <div class="text-left"><b class="mr-1">Cost Centre Rule:</b><asp:Literal ID="lbCostCentreRule" runat="server"></asp:Literal></div>
                                        <div class="text-left"><b class="mr-1">Ledger :</b><asp:Literal ID="lbLedger" runat="server"></asp:Literal></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body rounded-0 p-0">
                                <div class="table-responsive p-0" style="max-height: 300px;">
                                    <asp:ListView ID="lvDataEny" runat="server" OnDataBound="lvDataEny_DataBound">
                                        <LayoutTemplate>
                                            <table id="Table1" runat="server" class="table table-bordered table-head-fixed mb-0">
                                                <tr id="Tr1" runat="server" class="TableHeader tablehead">
                                                    <th id="Td1" runat="server">Cost Centre</th>
                                                    <th id="Td2" runat="server">Amount</th>
                                                </tr>
                                                <tr id="ItemPlaceholder" runat="server">
                                                </tr>
                                                <tfoot>
                                                    <tr>
                                                        <th id="Td4" runat="server">Total</th>
                                                        <th align="right" style="text-align: right;">
                                                            <asp:Literal ID="ltCCAmount" runat="server"></asp:Literal></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </LayoutTemplate>
                                        <ItemTemplate>
                                            <tr class="TableData">
                                                <td>
                                                    <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("costCentreName")%>'></asp:Label>
                                                </td>
                                                <td align="right">
                                                    <asp:Label ID="lbamount" runat="server" Text='<%# Convert.ToDouble(Eval("amount")) != 0 ? Eval("amount", "{0:0.00}") : "" %>'></asp:Label>
                                                </td>
                                            </tr>
                                        </ItemTemplate>                                          
                                        <EmptyDataTemplate>
                                            <div class="text-center">
                                                <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                                <h6 class="mb-3">No record available</h6>
                                            </div>
                                        </EmptyDataTemplate>
                                    </asp:ListView>
                                </div>
                            </div>

                        </div>
                         <div class="d-flex mt-3 align-items-center justify-content-center">
                            <a data-toggle="modal" href="#PupEscalate" class="btn btn-outline-dark btn-sm py-1 px-3 mx-2">Skip</a>
                            <asp:Button ID="btnCorrect" runat="server" Text="Correct" onClick="btnCorrect_Click" CssClass="btn btn-outline-danger btn-sm py-1 px-3 mx-2" />                                                                   
                            <asp:Button ID="btnapprove" runat="server" Text="Approve"  CssClass="btn btn-outline-primary btn-sm py-1 px-3 mx-2" />
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>


<div class="modal" id="Pupcomment" data-backdrop="static">
<div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">       
          <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="top: 4px; right: 10px; z-index: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
        <div class="container"></div>
        <div class="modal-body">
        <asp:Label runat="server" ID="ltrcomment" style="word-wrap: break-word;"></asp:Label>
        </div>
        
          </div>
      </div>
    </div>
    <style>

        .table.table-head-fixed tr:nth-child(1) th {
            background-color:#13977f;
            border-bottom: 0;
            box-shadow: inset 0 1px 0 #dee2e6, inset 0 -1px 0 #dee2e6;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .tablehead{
            background-color: #13977F;
        }
        .table.table-head-fixed tr:last-child th {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background-color: #f8f9fa;
            border-top: 0;
            box-shadow: inset 0 1px 0 #dee2e6, inset 0 -1px 0 #dee2e6;
        }

        .text-break {
            word-wrap: break-word!important;
            word-break: break-word!important;
        }
        .pg_table table td {
            border-top:0px!important;
        }
        @media (min-width: 576px){
            #PupEscalate .modal-dialog {
                max-width: 360px;
               
            }
             #Pupcomment .modal-dialog {
                max-width: 1030px;
               
            }
        }
        .search_btn {
            top: -1px;
            position: relative;
        }
         .table th, .table td {
          vertical-align: middle;
        }

    </style>
      <script type="text/javascript">
          $(function refernce() {
              $("#btnaction").click(function () {
                  var etry = $(this).attr('data-id');
                  $("#modal-body").val(etry);
                  $('#priviewledgerpopup').modal('show');

              });
          });
          $(function popup() {
              $("#btncomment").click(function () {
                  var etry = $(this).attr('data-id');
                  $("#modal-body").val(etry);
                  $('#Pupcomment').modal('show');

              });
          });
      </script>
</asp:Content>


