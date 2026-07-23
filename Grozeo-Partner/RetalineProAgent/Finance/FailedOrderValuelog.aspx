<%@ Page Language="C#" AutoEventWireup="true"  MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="FailedOrderValuelog.aspx.cs" Inherits="RetalineProAgent.Finance.FailedOrderValuelog" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
        <a href="/Finance/Navigations/Accounting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a> 
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Item Heads Tenant Sale Order</h6>
    <p class="mb-0">Item Heads Tenant Sale Order</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">   
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
               <div class="row row-sm justify-content-between">
            <div class="col-12">
                <div class="row row-sm">
                    <div class="form-group col-12 col-md-3 mb-2 mb-lg-0 pr-md-1">
                        <label for="txtFromDate" class="tx-dark" runat="server">From</label>
                        <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                    <div class="form-group col-12 col-md-3 mb-2 mb-md-0 pl-md-1">
                        <label for="txtToDate" class="tx-dark" runat="server">To</label>
                        <asp:TextBox ID="txtToDate" CssClass="form-control" runat="server" TextMode="Date" />
                    </div>
                     <div class="input-group input-group col-12 col-md-1 align-items-end pl-md-1 mb-2 mb-md-0">
                        <input type="submit" name="" value="GO" id="" class="btn btn-primary">
                      </div>
                    <div class="form-group col-12 col-md-5 align-items-end mb-0 d-flex">
                          <div class="input_search_box">
                            <input type="text" style="display:none" />
                            <input type="password" style="display:none" />

                            <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                            <asp:LinkButton runat="server" CssClass="input-group-append">                        
                            </asp:LinkButton>

                            <input type="text" style="display:none" />
                                <input type="password" style="display:none" />
                            <asp:LinkButton ID="lbtnSearch"  CssClass="btn bd bd-l-0 tx-gray-600 "  runat="server" autocomplete="off"><i class="fa fa-search mt-1"></i></asp:LinkButton>
                        </div>
                    </div>
                </div>
                
            </div>  
      </div>
                </div>

                <div class="card-body">

                    <div class="table-responsive mailbox-messages">
                <asp:GridView AutoGenerateColumns="false" ID="gvpending"  runat="server" CssClass="table table-bordered gridview_table" BorderStyle="Solid" 
                    DataSourceID="SDSpendingentries" AllowPaging="true" PagerStyle-CssClass="pg_table" PageSize="10" DataKeyNames="Id">
                    <Columns>
                        
                        <asp:BoundField HeaderText=" Log ID" ItemStyle-Width="10%" HeaderStyle-HorizontalAlign="left" ItemStyle-HorizontalAlign="left" DataField="id" SortExpression="id" />
                        <asp:BoundField HeaderText="Order No" ItemStyle-Width="20%" HeaderStyle-HorizontalAlign="left" ItemStyle-HorizontalAlign="left" DataField="order_order_id" SortExpression="order_order_id" />
                        <asp:BoundField HeaderText="Date" ItemStyle-Width="25%" ItemStyle-HorizontalAlign="left" DataField="order_confirmed_on" DataFormatString="{0:dd-MMM-yyy}" SortExpression="order_confirmed_on" ItemStyle-VerticalAlign="Middle" />                        
                        <asp:BoundField HeaderText="Time" ItemStyle-Width="10%" ItemStyle-HorizontalAlign="left" DataField="order_confirmed_on" DataFormatString="{0:HH:mm:ss}" SortExpression="order_confirmed_on" ItemStyle-VerticalAlign="Middle" />                        
                        <asp:BoundField HeaderText="Store Group Name" ItemStyle-Width="20%" HeaderStyle-HorizontalAlign="left"  ItemStyle-HorizontalAlign="left" DataField="storegroupname" SortExpression="storegroupname" />
                         <asp:BoundField HeaderText="Order Value" ItemStyle-Width="15%" HeaderStyle-HorizontalAlign="left" DataFormatString="{0:n}"  ItemStyle-HorizontalAlign="right" DataField="total" SortExpression="total" />
                         <asp:TemplateField HeaderText="Action" HeaderStyle-HorizontalAlign="Center" ItemStyle-Width="20%" ItemStyle-CssClass="text-right" HeaderStyle-CssClass="text-center">
                            <ItemTemplate>                           
                                <asp:LinkButton ID="btnaction" runat="server"  OnClick="btnaction_Click"   CssClass="btn btn-outline-primary btn-sm" Text="View" recid='<%# Eval("order_id") %>'  />                               
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
                <asp:SqlDataSource runat="server" ID="SDSpendingentries" ProviderName="MySql.Data.MySqlClient"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                     SelectCommand="SELECT fc.id,rc.order_order_id,fc.order_id,rc.order_confirm_date,total,br_storeGroup,rc.order_confirmed_on,
                            (SELECT store_group_name FROM finascop_branch_group fb WHERE fb.store_group_id=br_storeGroup) AS storegroupname
                             FROM `finascop_autoposting_calculations` fc 
                            INNER JOIN retaline_customer_order rc  ON  rc.order_id=fc.order_id  INNER JOIN  B2CSalesOrder bcso
                             ON  bcso.customer_order_id = rc.order_id
                            INNER JOIN finascop_branch fb ON rc.order_branch_id=fb.br_id
                          WHERE  status_id<4 and (@fromDate IS NULL OR @fromDate = '' OR CAST(rc.order_confirmed_on AS DATE) >= CAST(@fromDate AS DATE)) AND (@toDate IS NULL OR @toDate = '' OR CAST(rc.order_confirmed_on AS DATE) <= CAST(@toDate AS DATE)) and  (trim(@search) like '' or rc.order_order_id like CONCAT('%', @search, '%')) GROUP BY order_id ORDER BY rc.order_order_id DESC">
                    <SelectParameters>    
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
    <div class="modal fade" id="priviewledgerpopup" tabindex="-1" role="dialog" aria-labelledby="personalModalLabel" aria-hidden="true">
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
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="mb-1">Value Detailes - OrderNo:
                                                <asp:Label runat="server" class="tx-bold" ID="ltrorderid"></asp:Label></p>
                                        </div>
                                        <div class="col-12 col-lg-3">
                                            <p class="mb-1">Store:
                                                <asp:Label runat="server" class="tx-bold" ID="ltrstore"></asp:Label></p>
                                        </div>
                                        <div class="col-12 col-lg-4 d-flex align-items-center ">
                                            <span class=" ">Rule:&nbsp</span>
                                            <div class="d-flex align-items-center">
                                                <asp:DropDownList ID="ddlrule" DataSourceID="SDSrulename" DataTextField="rulename" DataValueField="id" AutoPostBack="true" CssClass="form-control select2 py-0 ht-30-force" AppendDataBoundItems="true" runat="server">
                                                    <asp:ListItem Text="Select Rule" Value="-1"></asp:ListItem>
                                                </asp:DropDownList>
                                                <asp:SqlDataSource ID="SDSrulename" runat="server" SelectCommand="SELECT id,rulename FROM `finance_autoposting_rule` ORDER BY id" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-3">
                                            <p class="mb-1">Date: <asp:Label runat="server" class="tx-bold tx-uppercase" id="ltrdate"></asp:Label></p> 
                                        </div>
                                        <%--<div class="col-12 col-lg-2">
                                            <p class="mb-1">Time: <asp:Label runat="server" class="tx-bold" id="ltrTime"></asp:Label></p> 
                                        </div>--%>
                                         <div class="col-12 col-lg-2">
                                            <p class="mb-1">Payment: <asp:Label runat="server" class="tx-bold" id="ltrpayment"></asp:Label></p> 
                                        </div>  
                                         <div class="col-12 col-lg-3">
                                            <p class="mb-0">Delivery: <asp:Label runat="server" class="tx-bold" id="ltrdeliverytype"></asp:Label></p> 
                                        </div>
                                                                            
                                        <div class="col-12 col-lg-3">
                                            <p class="mb-1">Associate Type : <asp:Label runat="server" class="tx-bold" id="ltrassociate"></asp:Label></p> 
                                        </div>
                                       
                                        <div class="col-12 col-lg-6 d-flex align-items-center row row-sm">
                                        <span class=" col-12 col-lg-2 pr-0">Filter By :</span>
                                        <div class="d-flex align-items-center col-12 mb-3 mb-sm-0 col-sm-5">
                                            <asp:DropDownList ID="ddltype"  CssClass="form-control select2 py-0 ht-30-force" AutoPostBack="true" AppendDataBoundItems="true" runat="server">
                                           <%-- <asp:ListItem Text="Select Type" Value="-1"></asp:ListItem>--%>
                                            <asp:ListItem Text="Default View" Selected="True" Value="0"></asp:ListItem>
                                            <asp:ListItem Text="Posting" Value="1"></asp:ListItem>
                                            <asp:ListItem Text="Allocation" Value="3"></asp:ListItem>
                                            <%--<asp:ListItem Text="Not Applicable" Value="2"></asp:ListItem>--%>
                                             <asp:ListItem Text="View All" Value="-1"></asp:ListItem>
                                            </asp:DropDownList>                                          
                                        </div> 
                                        <div class="d-flex align-items-center col-12 col-sm-5"> 
                                            <asp:DropDownList ID="ddlfiter"  AutoPostBack="true"  CssClass="form-control select2 py-0 ht-30-force"  AppendDataBoundItems="true" runat="server">
                                                <asp:ListItem Text="Default View" Value="-1"></asp:ListItem>                                               
                                                <asp:ListItem Text="Cart Checkout" Value="1"></asp:ListItem>
                                                <asp:ListItem Text="Order Placing" Value="2"></asp:ListItem>
                                                <asp:ListItem Text=" Order Packing" Value="3"></asp:ListItem>
                                                <asp:ListItem Text="Order Pickup" Value="5"></asp:ListItem>
                                                 <asp:ListItem Text=" Order Cancellation" Value="6"></asp:ListItem>
                                                <asp:ListItem Text="Order Delivery Confirmation" Value="4"></asp:ListItem>
                                            </asp:DropDownList>
                                            <%--<asp:SqlDataSource ID="SDSevent" runat="server" SelectCommand="SELECT id,EVENT FROM finance_calculation_heads  GROUP BY EVENT" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"></asp:SqlDataSource>--%>

                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <asp:HiddenField ID="hidValueHeadOrderId" runat="server" />
                           <asp:ObjectDataSource runat="server" ID="ODSordervaluehead" TypeName="RetalineProAgent.Finance.Ordercalculation" SelectMethod="LoadOrderValueHeads" >
                               <SelectParameters>
                                   <asp:ControlParameter ControlID="hidValueHeadOrderId" PropertyName="Value" Name="Id" DefaultValue="0" />
                                   <asp:ControlParameter Name="eventId" DefaultValue="0" ControlID="ddlfiter" />
                                   <asp:ControlParameter Name="recordType" DefaultValue="0" ControlID="ddltype"  />
                                     <asp:ControlParameter Name="ruleId" DefaultValue="0" ControlID="ddlrule"  />
                               </SelectParameters>
                           </asp:ObjectDataSource>
                                <div class="card-body rounded-0 p-0">                               
                                    <div class="table-responsive p-0" style="max-height: 300px;">
                                        <asp:HiddenField ID="hidVoucherId" ClientIDMode="Static" Value="0" runat="server" />
                                       <%-- <asp:Button ID="copyButton" runat="server" Text="Copy" />--%>
                                        <asp:ListView ID="lvDataEny" runat="server" DataSourceID="ODSordervaluehead" OnDataBound="lvDataEny_DataBound">
                                            <LayoutTemplate>
                                                <table id="Table1" runat="server" class="table table-bordered table-head-fixed mb-0">
                                                    <tr id="Tr1" runat="server" class="TableHeader">
                                                        <th id="Td1" runat="server"> Value Head </th>
                                                        <th id="Td2" runat="server">Value</th>
                                                         <th style="width:100px" id="Th1" runat="server">Event</th> 
                                                          <th style="width:150px" id="Th4" runat="server">Computed</th>
                                                          <th id="Th5" runat="server">Ledger/Cost centre</th>
                                                         <th id="Th2" runat="server">Debit</th>
                                                         <th id="Th3" runat="server">Credit</th> 
                                                         <th id="Th6" runat="server"></th> 
                                                    </tr>
                                                    <tr id="ItemPlaceholder" runat="server">
                                                    </tr>                                                    
                                                </table>
                                            </LayoutTemplate>
                                            <ItemTemplate>
                                                <asp:PlaceHolder runat="server" Visible='<%# ((bool)Eval("isCostCenter")) || !String.IsNullOrEmpty(GetOrdervalue((string)Eval("ColumnName"))) %>'>
                                                <tr class="TableData">
                                                    <td align="left">
                                                        <asp:Label ID="lbPerticulars" runat="server" Text='<%# (((bool)Eval("isCostCenter")) ? Getcostcentre((string)Eval("costcentre")):GetOrdervalue((string)Eval("ColumnName"))) %>'></asp:Label>
                                                    </td>
                                                    <td align="right">
                                                        <asp:Label ID="lbDramount" runat="server" Text='<%# !String.IsNullOrEmpty(Eval("ColumnValue").ToString()) ? Convert.ToDouble(Eval("ColumnValue")).ToString("0.00"):"" %>'></asp:Label>
                                                    </td>
                                                     <td align="left">
                                                        <asp:Label ID="lblEvents" runat="server" Text='<%# (((bool)Eval("isCostCenter")) ? (string)Eval("eventName") : GetEventOnHead((string)Eval("ColumnName"))) %>' ></asp:Label>
                                                    </td>
                                                    <td align="left" class="headtype">
                                                        <asp:Label ID="lbltype" CssClass="headtype"  runat="server" Text='<%# (((bool)Eval("isCostCenter")) ? "Allocation" : Getordertype((string)Eval("ColumnName")))  %>'></asp:Label>
                                                    </td>
                                                    <td align="left">
                                                        <asp:Label ID="lbledgercostcentre" Text='<%# (((bool)Eval("isCostCenter")) ? Getcostcentre((string)Eval("costcentre")) : Getledgername((string)Eval("ledgerId")))  %>' runat="server"></asp:Label>
                                                    </td>
                                                    <td align="right">
                                                        <asp:Label ID="lbdrname" CssClass="drval" runat="server" Text='<%# !String.IsNullOrEmpty(Eval("splitdrvalue").ToString()) ? Convert.ToDouble(Eval("splitdrvalue")).ToString("0.00"):"" %>' ></asp:Label>
                                                    </td>
                                                    <td align="right">
                                                        <asp:Label ID="lbcrname" CssClass="crval" runat="server" Text='<%# !String.IsNullOrEmpty(Eval("splitcrvalue").ToString()) ? Convert.ToDouble(Eval("splitcrvalue")).ToString("0.00"):"" %>' ></asp:Label>
                                                    </td>
                                                    <td align="center">
                                                       <asp:CheckBox ID="chkvaluehead" CssClass="chkval" onclick="calculatesum(this)" runat="server"/>
                                                    </td>                                                                                                   
                                                </tr>

                                                </asp:PlaceHolder>
                                            </ItemTemplate>                                          
                                            <EmptyDataTemplate>
                                                <div class="text-center">
                                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                                    <h6 class="mb-3">No record available</h6>
                                                </div>
                                            </EmptyDataTemplate>
                                        </asp:ListView>
                                    </div>

                                    <div class="d-flex justify-content-lg-end pt-3">
                                        <asp:TextBox ID="txtdrsum"  runat="server" CssClass="form-control wd-150 text-right"></asp:TextBox>
                                        <asp:TextBox ID="txtcrsum" runat="server" CssClass="form-control wd-150 ml-lg-2 text-right"></asp:TextBox>                                                                          
                                    </div>

                                </div>

                            </div>                                                    
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <style>
        .table.table-head-fixed tr:nth-child(1) th {
                background-color: #13977f;
                color: #fff;
            border-bottom: 0;
            box-shadow: inset 0 1px 0 #dee2e6, inset 0 -1px 0 #dee2e6;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
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
            word-wrap: break-word !important;
            word-break: break-word !important;
        }

        .pg_table table td {
            border-top: 0px !important;
        }

        @media (min-width: 576px) {
            #priviewledgerpopup .modal-dialog {
                max-width: 1080px;
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
      
    <script
        type="text/javascript">
        function calculatesum(obj) {
            var drtotal = 0; var crtotal = 0;
            $(obj).closest('table').find('tr.TableData').each(function () {
                var chk = $(this).find('td span.chkval input');
                if (chk && $(chk).is(':checked')) {
                    if ($(this).find('span.drval').text() != '')
                        drtotal += Number($(this).find('span.drval').text());
                    if ($(this).find('span.crval').text() != '')
                        crtotal += Number($(this).find('span.crval').text());

                }
            });
            $('#<%= txtdrsum.ClientID%>').val(makeDecimal(drtotal));
            $('#<%= txtcrsum.ClientID%>').val(makeDecimal(crtotal));
        }

        function makeDecimal(num) {
            var leftDecimal = num.toString().split("."), rightDecimal = "00", n = num;

            if (leftDecimal.length === 2) {
                n = leftDecimal[0] + "." + leftDecimal[1].slice(0, 2);
            }
            else {
                n = Number(num).toFixed(2);
            }
            return n;
        }
        $('#priviewledgerpopup').on('hidden.bs.modal', function (e) { $('#<%= hidValueHeadOrderId.ClientID %>').val(''); });

        $(document).ready(function () {
            $('#cpMainContent_cpNMainContent_lvDataEny_Table1').find('tr.TableData').each(function () {
                var typeval = $(this).find('span.headtype').text();
                if (typeval == "Computation")
                    $(this).css('background-color', 'rgba(255, 247, 234, 0.5)');
                else if (typeval == "Posting")
                    $(this).css('background-color', 'rgba(240, 251, 238, 0.5)');
                else
                    $(this).css('background-color', 'rgba(255, 234, 234, 0.5)');
            });
        });

    </script>
</asp:Content>

