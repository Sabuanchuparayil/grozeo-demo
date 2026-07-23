<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Taxreport.aspx.cs" Title="seller Tax Report" Inherits="RetalineProAgent.Finance.Taxreport" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tax Report</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Daily Sales Report">Tax Report</asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
        <p class="mb-0">Taxation Made Simple</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-lg-2 input-group mg-b-10 mg-lg-b-0">
                    <label for="txtSearch1" runat="server" class="tx-dark mb-1 w-10">Select :</label>
                                <asp:PlaceHolder ID="plcSelectbate" runat="server">
                                    <asp:DropDownList ID="seldate" AutoPostBack="true" CssClass="wd-100p-force form-control select2" runat="server">
                                        <asp:ListItem Text="Select" Value=""></asp:ListItem>
                                        <asp:ListItem Text="Year" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Custom Date Range" Value="2"></asp:ListItem>                                        
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" Display="Dynamic" SetFocusOnError="true" ControlToValidate="seldate" ValidationGroup="SearchReport" Text="*" ForeColor="Red" ErrorMessage="Select period"></asp:RequiredFieldValidator>
                                </asp:PlaceHolder>
                </div>
                <div class="col-12 col-lg-10 d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-end">
                                <asp:Panel ID="pnlDateRange" runat="server" CssClass=" d-flex align-items-center w-100 flex-wrap flex-sm-nowrap date_view_wrap">
                                    <%--<div class="input-group mx-2">
                                        <label for="txtDateFrom" runat="server" class="tx-dark mb-1 w-100">&nbsp;</label>
                                        <asp:TextBox ID="txtDateFrom" runat="server"  CssClass="form-control ht-35" Text="07-02-2023" Enabled="false"></asp:TextBox>
                                    </div>--%>
                                   <div class="input-group ml-0 ml-lg-2 mr-0 mr-sm-2 mb-2 mb-sm-0">
                                        <label for="txtDateFrom" runat="server" class="tx-dark mb-1 w-100">From:</label>
                                        <asp:TextBox ID="txtDateFrom" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date From" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                    </div>
                                    <div class="input-group ml-0 ml-sm-2 mr-0 mr-sm-2 mb-3 mb-sm-0">
                                        <label for="txtDateTo" runat="server" class="tx-dark mb-1 w-100">To:</label>
                                        <asp:TextBox ID="txtDateTo" runat="server" TextMode="Date" CssClass="form-control" placeholder="Date To" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask></asp:TextBox>
                                    </div>
                                     <div class="wd-150 ml-0 ml-sm-2">
                                    <label class="d-none d-sm-block" runat="server">&nbsp;</label>
                                    <asp:LinkButton ID="lbtnSearch1" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                                </div>
                                        </asp:Panel>
                                   <asp:Panel ID="plcSelectmonth" runat="server" CssClass=" d-flex align-items-center w-100 flex-wrap flex-sm-nowrap date_view_wrap">
                                    <div class="input-group ml-0 ml-lg-2 mr-0 mr-sm-2 mb-2 mb-sm-0">
                                      <label for="txtSearchmonth" runat="server" class="tx-dark mb-1 w-10">Select Year</label>
                                    <asp:PlaceHolder ID="plcselyear" runat="server">
                                    <asp:DropDownList ID="selyear" AutoPostBack="true" CssClass="wd-100p-force form-control select2" runat="server">
                                        <asp:ListItem Text="Select year" Value=""></asp:ListItem>
                                        <asp:ListItem Text="2022" Value="2022"></asp:ListItem>
                                        <asp:ListItem Text="2023" Value="2023"></asp:ListItem>
                                        <asp:ListItem Text="2024" Value="2024"></asp:ListItem>
                                        <asp:ListItem Text="2025" Value="2025"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" Display="Dynamic" SetFocusOnError="true" ControlToValidate="seldate" ValidationGroup="SearchReport" Text="*" ForeColor="Red" ErrorMessage="Select period"></asp:RequiredFieldValidator>
                                </asp:PlaceHolder>
                                    </div>                                                                           
                                    <div class="input-group ml-0 ml-sm-2 mr-0 mr-sm-2 mb-3 mb-sm-0">
                                        <label for="txtSearchmonth" runat="server" class="tx-dark mb-1 w-10">Select month:</label>
                                       <asp:PlaceHolder ID="PlaceHolder1" runat="server">
                                    <asp:DropDownList ID="selmonth" AutoPostBack="true" CssClass="wd-100p-force form-control select2" runat="server">
                                        <asp:ListItem Text="Select month" Value=""></asp:ListItem>
                                        <asp:ListItem Text="January" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="February" Value="2"></asp:ListItem>
                                        <asp:ListItem Text="March" Value="3"></asp:ListItem>
                                         <asp:ListItem Text="April" Value="4"></asp:ListItem>
                                        <asp:ListItem Text="May " Value="5"></asp:ListItem>
                                        <asp:ListItem Text="June " Value="6"></asp:ListItem>
                                        <asp:ListItem Text="July" Value="7"></asp:ListItem>
                                         <asp:ListItem Text="August " Value="8"></asp:ListItem>
                                        <asp:ListItem Text="September " Value="9"></asp:ListItem>
                                        <asp:ListItem Text="October " Value="10"></asp:ListItem>
                                        <asp:ListItem Text="November" Value="11"></asp:ListItem>
                                         <asp:ListItem Text="December" Value="12"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" Display="Dynamic" SetFocusOnError="true" ControlToValidate="seldate" ValidationGroup="SearchReport" Text="*" ForeColor="Red" ErrorMessage="Select period"></asp:RequiredFieldValidator>
                                </asp:PlaceHolder>                                    
                                    </div> 
                                        <div class="wd-150 ml-0 ml-sm-2">
                                    <label class="d-none d-sm-block" runat="server">&nbsp;</label>
                                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" runat="server">Search</asp:LinkButton>
                                </div>
                                      </asp:Panel>                                
                                <%--<div class="col-12 col-lg-3 pg-ntion p-0">
                                    <label style="width: 100%">&nbsp;</label>
                                    <div class="float-right">
                                        <asp:Literal runat="server" ID="ltrPagingCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPagingCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPagingTotal" Text="200"></asp:Literal>
                                        <div class="btn-group">
                                            <asp:LinkButton ID="lbtnPagerLeft" runat="server" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm page-link">
                      <i class="fa fa-angle-left"></i>
                                            </asp:LinkButton>
                                            <asp:LinkButton ID="lbtnPagerRight" runat="server" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm page-link">
                          <i class="fa fa-angle-right"></i>
                                            </asp:LinkButton>
                                        </div>
                                        <!-- /.btn-group -->
                                    </div>
                                </div>--%>
                            </div>
            </div>
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                        <asp:GridView AutoGenerateColumns="false" DataSourceID="SDStaxreport" ID="gvtaxreport" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                            AllowPaging="true" AllowSorting="true" OnRowDataBound="gvtaxreport_RowDataBound" OnDataBound="gvtaxreport_DataBound" ShowFooter="false" PagerSettings-Visible="true" PageSize="10">
                            <Columns>
                                <asp:BoundField HeaderText="Month" DataField="months" SortExpression="months" />
                                <asp:BoundField HeaderText="Sales" DataField="sales" DataFormatString="{0:n}" SortExpression="sales"  ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="CGST" DataField="cgst" DataFormatString="{0:n}" SortExpression="cgst"  ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="SGST/UTGST" DataField="sgst" DataFormatString="{0:n}" SortExpression="sgst"  ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="IGST" DataField="igst" SortExpression="igst" DataFormatString="{0:n}"  ItemStyle-HorizontalAlign="Right"  />                               
                               <asp:BoundField HeaderText="Cess" DataField="cess" SortExpression="cess" DataFormatString="{0:n}"  ItemStyle-HorizontalAlign="Right"  />                                                             
                                <asp:BoundField HeaderText="TCS-CGST" DataField="tcscgst" SortExpression="tcscgst" DataFormatString="{0:n}" ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="TCS- SGST/UTGST" DataField="tcssgst" SortExpression="tcssgst" DataFormatString="{0:n}"  ItemStyle-HorizontalAlign="Right"  />
                                <asp:BoundField HeaderText="TCS-IGST" DataField="tcsigst" SortExpression="tcsigst" DataFormatString="{0:n}"  ItemStyle-HorizontalAlign="Right"  />                                
                              <asp:TemplateField HeaderText="Download">
                                  <ItemTemplate>
                                      <asp:Button ID="btndownload" runat="server" month='<%# Eval("MonthVal") %>' Text="Download" OnClick="btndownload_Click" />
                                  </ItemTemplate>
                              </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No record available</h6>
                                        </div>
                                    </EmptyDataTemplate>
                            <PagerStyle CssClass="cssPager" />
                          <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                        </asp:GridView>
                    </div>
        </div><!-- card-body -->
     
    </div><!-- card -->
    
    <asp:SqlDataSource ID="SDStaxreport" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="ODStaxreport_Selecting" 
        SelectCommand="SELECT DATE_FORMAT(b.invoiceDate,'%M')AS months, MONTH(b.invoiceDate) AS MonthVal, YEAR(invoiceDate) AS invyear, SUM(order_total_amount)AS sales,SUM(order_total_cgst)AS cgst,SUM(order_total_igst)AS igst,SUM(order_kfc_amount)AS cess,
                        SUM(order_tcs_cgst)AS tcscgst,SUM(order_tcs_igst)AS tcsigst,IF ( SUM(order_total_sgst)>0,SUM(order_total_sgst),SUM(order_total_utgst))AS sgst,
                        IF (SUM(order_tcs_sgst)>0,SUM(order_tcs_sgst),SUM(order_tcs_utgst))AS tcssgst FROM retaline_customer_order INNER JOIN B2CInvoice b ON b.bci_fstr_id = order_id
                        WHERE ifnull(@datefilter, 0) > 0 and status_id>3 AND storegroup_id=@storegroupid  and ((@datefilter = 2 and invoiceDate between @fromDate and @toDate) or 
                        (@datefilter = 1 and YEAR(invoiceDate) = @finyear ) and (ifnull(@finmonth,0)<=0 or MONTH(invoiceDate) = @finmonth ) ) GROUP BY YEAR(b.invoiceDate),MONTH(b.invoiceDate)
                          ORDER BY YEAR(b.invoiceDate),MONTH(b.invoiceDate)">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
            <asp:ControlParameter Name="datefilter" ControlID="seldate" DefaultValue="0" />
            <asp:ControlParameter Name="fromDate" ControlID="txtDateFrom" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="toDate" ControlID="txtDateTo" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="finyear" ControlID="selyear" ConvertEmptyStringToNull="false" />
            <asp:ControlParameter Name="finmonth" ControlID="selmonth" ConvertEmptyStringToNull="false" />
        </SelectParameters>
    </asp:SqlDataSource>
     <asp:SqlDataSource ID="SDSreport" runat="server" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" OnSelecting="ODStaxreport_Selecting" 
      SelectCommand ="SELECT rcr.order_order_id,re.item_id,(SELECT stit_SKU FROM finascop_stock_itemmaster WHERE item_product_id=stit_id) AS productname,b.invoiceDate,order_confirm_date,b.invoiceNumber,b.invoiceValue,bci.br_gst,
                    order_total_gst,order_total_amount,subtotal,order_total_cgst,order_mrp, 0 AS cesstax,item_order_qty,item_cgst,order_item_gst,order_item_cgst,order_item_ugst,order_item_sgst,order_item_igst,order_item_tcs_gst,
                     order_item_tcs_igst,order_item_tcs_cgst ,order_item_tcs_utgst,order_item_tcs_sgst,item_price,item_retail_price,item_sales_price,
                    item_sgst,item_igst,item_kfc,(SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS') AS CFG,
                    order_total_sgst,order_total_utgst,order_total_igst,br_City,br_State,br_pincode,(SELECT st_name FROM finascop_state WHERE st_ID=br_state) AS billstate,
                    order_tcs_cgst,total,order_city,order_state,order_pin,order_delivery_charge,0 AS gpin,order_delivery_charge_gst,CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 
                    order_payment_gateway_refid WHEN payment_mode = 3 THEN 'Wallet' 
                    WHEN payment_mode = 4 THEN 'COD With Wallet' WHEN payment_mode = 5 THEN 'Online With Wallet' WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7 THEN 'Cash On Delivery' END AS paymentcode, 
                    order_delivery_charge_sgst,order_delivery_charge_cgst,order_delivery_charge_utgst,order_delivery_charge_igst,CASE WHEN order_total_igst >0 THEN 'Intrastate' ELSE 'Inter-state' END AS TypeofSale,
                    order_tcs_sgst,order_tcs_utgst,order_tcs_igst,order_tcs_cgst,CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 'Online Payment' WHEN payment_mode = 3 THEN 'Wallet' 
                    WHEN payment_mode = 4 THEN 'COD With Wallet' WHEN payment_mode = 5 THEN 'Online With Wallet' WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7 THEN 'Cash On Delivery' END AS payment_mode 
                     FROM retaline_customer_order rcr INNER JOIN B2CInvoice b ON rcr.order_id=b.bci_bcso_id INNER JOIN B2CInvoiceDetails bd ON b.id=bd.bci_id INNER JOIN finascop_branch bci ON
                     rcr.order_branch_id = bci.br_ID INNER JOIN retaline_customer_order_items re ON re.customer_order_id=rcr.order_id INNER JOIN
                    retaline_customer_order_delivery_address da ON rcr.order_order_id=da.order_id  
                    WHERE storegroup_id =@storegroupid AND status_id>3 and MONTH(invoiceDate) = @month">  
         
         <SelectParameters>
              <asp:Parameter Name="storegroupid" />
              <asp:Parameter Name="month" />
         </SelectParameters>
    </asp:SqlDataSource>

    <style>
        @media (max-width: 991px) {
            .pg-ntion {
                flex:auto;
                max-width:none;
                width:auto;
            }
        }
        
    </style>
</asp:Content>




