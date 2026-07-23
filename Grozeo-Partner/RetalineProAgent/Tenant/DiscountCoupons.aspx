<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Discount Coupons" AutoEventWireup="true" CodeBehind="DiscountCoupons.aspx.cs" Inherits="RetalineProAgent.DiscountCoupons" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/crm"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Discount Coupons"></asp:Literal></h6>
        <p class="mb-0">Manage discount coupons and offers</p>
    </div>
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm align-items-lg-end">
                <div class="col-lg-2 d-flex justify-content-lg-start align-items-lg-end">
                    <a href="/Tenant/CreateCoupon" type="button" class="btn btn-primary mb-3 mb-lg-0 w-auto w-lg-100">Create Coupon<i class="icon ion-plus-circled ml-2"></i></a>
                </div>
                <div class="col-lg-4 mt-2 mt-lg-0 d-flex align-items-end">
                    <div class="input-group ">
                        <input type="text" style="display: none" />
                        <input type="password" style="display: none" />
                        <div class="input_search_box">
                          <asp:TextBox ID="txtSearch" runat="server" autocomplete="off" CssClass="form-control" placeholder="Search coupons"></asp:TextBox>
                          <asp:LinkButton ID="lbtnSearch" CssClass="btn bd bd-l-0 tx-gray-600" OnClick="lbtnSearch_Click" runat="server"><i class="fa fa-search"></i></asp:LinkButton>
                        </div>                        
                    </div>
                   <asp:Button runat="server" ID="Button1" CssClass="btn btn-outline-primary mt-2 mt-lg-0 ml-2"  PostBackUrl="~/Tenant/DiscountCoupons.aspx" Text="Reset" />
                </div>
            </div><!--row-->
        </div><!-- card-header -->
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvDiscountCoupon" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvDiscountCoupon_DataBound" DataSourceID="SDSDiscountCoupon">
                                    <Columns>
                                        <asp:TemplateField HeaderText="Coupon">
                                            <ItemTemplate>
                                                <asp:Label ID="lblCouponSummary"  runat="server" Text='<%# GetOfferSummary(Eval("bom_narration").ToString(), Eval("bom_offerCode").ToString(), Eval("discountType").ToString()) %>' />
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                          <asp:TemplateField HeaderText="Store">
                                            <ItemTemplate>
                                             <asp:Label ID="lblBranchNames" runat="server" Text='<%# GetBranchNames(Eval("branch").ToString()) %>' />
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Value" DataField="offerValue" SortExpression="offerValue"/>
                                        <asp:BoundField HeaderText="Applicable For" DataField="offerType" Visible="false" SortExpression="offerType"/>
                                        <asp:BoundField HeaderText="Max Dis. Amount" DataField="maxDiscountValue" SortExpression="maxDiscountValue"/>
                                        <asp:BoundField HeaderText="Expiry Date" DataField="endDate" SortExpression="endDate"/>
                                        <asp:BoundField HeaderText="Redemption" DataField="redemption" SortExpression="redemption"/>
                                         <asp:TemplateField  HeaderText="Action">
                                             <ItemTemplate>
                                                 <asp:LinkButton runat="server" Visible="false" CssClass="btn btn-primary py-1">View</asp:LinkButton>
                                                 <asp:LinkButton runat="server" ID="btndelete" OnClick="btndelete_Click" discountid='<%# Eval("bom_id") %>' CssClass="tx-danger tx-14"><i class="icon ion-trash-a"></i></asp:LinkButton>
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

                <asp:SqlDataSource runat="server" ID="SDSDiscountCoupon" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                    SelectCommand="SELECT bom_id, bom_type,maxDiscountValue,branch, CASE WHEN discountType=1 THEN 'Flat Discount'  WHEN discountType=2 THEN 'Invoice Target'  WHEN discountType=3 THEN 'Product Discount' 
                                    WHEN discountType=4 THEN 'Buy X-Get Y'  WHEN discountType=5 THEN 'Delivery Discount'  END AS discountType, bom_offerType, CASE WHEN bom_offerType=1 
                                    THEN 'Flat Offer' WHEN bom_offerType=2 THEN 'Category Offer' WHEN bom_offerType=3 THEN 'Item Offer' ELSE 'Not Applicable' END AS offerType,
                                    stiid_fpoid, stiid_itemmasterid, bom_offerCode, bom_offrPlacement, CONCAT(FORMAT(bom_offrPlacement, 0), '%') AS offerValue, bom_narration, bom_enddate, DATE_FORMAT(bom_enddate,'%d %b %Y') AS endDate, bom_use,
                                    CASE WHEN bom_use=1  THEN 'One Time Use'  WHEN bom_use=2 THEN 'Multiple' ELSE 'Npt Applicable' END AS redemption  FROM retaline_offer_management                                      
                                    WHERE bom_status=1 and storeGroupId=@storegroupid and (trim(ifnull(@searchKey, '')) like '' or bom_offerCode like CONCAT('%', @searchKey, '%'))  ORDER BY bom_id DESC" OnSelecting="SDSDiscountCoupon_Selecting" >
                    <SelectParameters>
                        <asp:Parameter Name="storegroupid" />
                        <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                    </SelectParameters>
                </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
    </div><!-- card -->
</asp:Content>
