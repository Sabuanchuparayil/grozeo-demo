<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Comboproduct.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.Comboproduct" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">   
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Create Combo Product</h6>
    <p class="mb-0">Add a New Combo Product</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
     <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="row">
            <div class="col-12">              
              <div class="card">
                <div class="card-header shadow_top">
                  <div class="row row-sm align-items-lg-center">                                        
                    <div class="col-12 col-lg-3">
                      <span class="tx-16 tx-dark">Create a new combo</span>
                    </div>
                    <div class="col-12 col-lg-9">
                      <div class="row row-sm">
                        <div class="col-sm-5">
                          <div class="d-flex flex-wrap input-group">
                               <label for="txtBranch" class="tx-dark mb-1 w-100">Select Branch</label>
                                <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                    <asp:DropDownList ID="selBranch" OnSelectedIndexChanged="selBranch_SelectedIndexChanged"  AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSBranch" AppendDataBoundItems="true" DataTextField="br_Name" DataValueField="br_ID" runat="server">
                                        <asp:ListItem Text="Select a Branch" Value="-1"></asp:ListItem>
                                    </asp:DropDownList>
                                </asp:PlaceHolder>
                                <asp:SqlDataSource ID="SDSBranch" runat="server" OnSelecting="SDSBranch_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                    SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                                    ProviderName="MySql.Data.MySqlClient">
                                    <SelectParameters>
                                        <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                        <asp:Parameter Name="branchid" DefaultValue="-1" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                           </div>
                        </div>
                        <div class="col-sm-5 mt-2 mt-sm-0">
                          <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateFrom">Select Master Product</label>
                            <asp:DropDownList ID="selproduct"  OnDataBound="selproduct_DataBound" runat="server"  CssClass="form-control select2-show-search" data-placeholder="Select a Product" ForeColor="GrayText" DataSourceID="SDSProduct" DataTextField="stit_SKU" DataValueField="stit_id"><asp:ListItem Text="Select a Product" Value=""></asp:ListItem></asp:DropDownList>
                            <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSProduct" ProviderName="MySql.Data.MySqlClient" 
                                SelectCommand="SELECT b.stit_id,i.stit_SKU,b.`branch_id` FROM `finascop_stock_itemmaster`i 
                                INNER JOIN `finascop_stock_branch_inventory` b  ON b.`stit_id`=i.`stit_ID` WHERE b.branch_id=@branch_id  AND i.`stit_ID`NOT IN (SELECT `ProductId` FROM `Comboproduct` where `BranchId`=@branch_id and ProductId IS NOT NULL)">
                                  <SelectParameters>
                                     <asp:ControlParameter ControlID="selBranch" Name="branch_id" DefaultValue="0" />
                                  </SelectParameters>
                            </asp:SqlDataSource> 
                            <asp:HiddenField runat="server" ID="hdfmasterproduct" />
<%--                          <input name="" type="text" id="" class="form-control" autocomplete="off" value="Porotta">--%>
                        </div>    
                           <div class="col-sm-2 d-flex align-items-end mt-2 mt-sm-0">
                               <asp:Button  runat="server" CssClass="btn btn-inline-block btn-primary" ID="btncreatecombo" OnClick="btncreatecombo_Click" Text="Create"/>
<%--                          <a id="" class="btn btn-inline-block btn-primary" href="javascript:void(0)" data-toggle="modal" data-target="#ComboSearchResult">Search</a>--%>
                        </div>
                      </div>
                    </div>                            
                  </div><!--row-->
                </div><!--card heder-->       
                <!-- CreateComboProducts Modal -->
                <div class="modal CreateComboProducts fade" id="CreateComboProducts"  data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="CreateComboProductsTitle" aria-hidden="true">
                  <div class="modal-dialog w-100 " role="document">
                    <div class="modal-content">
                      <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="d-flex align-items-center w-100 pr-5 mb-3">
                          <div class="prodct_img">
                            <img class="tbl_prod_img" runat="server" id="imgproduct" onerror="this.src='index_files/image_on_error.svg'" />
                          </div>
                            <asp:Label runat="server" ID="lblproduct" CssClass="prd_name tx-15-force tx-dark"><strong></strong></asp:Label>
<%--                          <span id="" title="" class="prd_name tx-15-force tx-dark"><strong>Masala Kulcha 1 Piece</strong></span>--%>
                        </div>
                       
                        <div class="card">
                          <div class="card-header p-2 border">
                            <div class="row row-sm">
                              <div class="form-group col-12 col-sm-6 mb-2 ">
                                <asp:DropDownList ID="selcomboproduct"  runat="server" CssClass="form-control select2-show-search" OnDataBound="selcomboproduct_DataBound"  data-placeholder="Select a Product" ForeColor="GrayText" DataSourceID="SDScomboproduct" DataTextField="stit_SKU" DataValueField="stit_id">
                                    <asp:ListItem Text="Select a Product" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDScomboproduct" ProviderName="MySql.Data.MySqlClient" 
                                    SelectCommand="SELECT b.stit_id,i.stit_SKU,b.`branch_id` FROM  `finascop_stock_itemmaster` i 
                                        INNER JOIN `finascop_stock_branch_inventory` b ON b.`stit_id`=i.`stit_ID` WHERE b.branch_id=@branch_id and i.`stit_ID` <> @productid and i.stit_ID NOT IN (SELECT ComboProductId FROM Comboproduct WHERE ProductId = @productid and ComboProductId is not null)" OnSelecting="SDScomboproduct_Selecting">
                                    <SelectParameters>
                                        <asp:ControlParameter ControlID="selBranch" Name="branch_id" DefaultValue="0" />
                                         <asp:ControlParameter ControlID="selproduct" Name="productid" DefaultValue="0" />
                                    </SelectParameters>
                                </asp:SqlDataSource> 
                                <asp:RequiredFieldValidator ID="rfvproduc" runat="server" ControlToValidate="selcomboproduct" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Select a product" ValidationGroup="comboproduct" ForeColor="Red"></asp:RequiredFieldValidator>
                              </div>

                              <div class="form-group col-12 col-sm-6 mb-2 ">
                                  <asp:DropDownList runat="server" ID="ddlComboCostType" CssClass="form-control select2" data-placeholder="Select Combo Cost Type" >
                                    <asp:ListItem Value="0">Select Combo Cost Type</asp:ListItem>
                                    <asp:ListItem Value="1">Free with parent product</asp:ListItem>
                                    <asp:ListItem Value="2">Discounted with parent product</asp:ListItem>
                                    <asp:ListItem Value="3">Suggested with parent product</asp:ListItem>
                                  </asp:DropDownList>    
                                  <asp:RequiredFieldValidator ID="rfvcombotypepe" runat="server" ControlToValidate="ddlComboCostType" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Select Combo Cost Type" ValidationGroup="comboproduct" ForeColor="Red"></asp:RequiredFieldValidator>
                              </div>

                              <div class="form-group col-12 col-sm-6 mb-2 ">
                                  <asp:DropDownList runat="server" ID="ddlDiscountInclusion" CssClass="form-control select2 hide-group"  data-placeholder="Select Discount/ inclusion (Free)">
                                       <asp:ListItem Value="0">Select Discount/ inclusion (Free)</asp:ListItem>
                                  </asp:DropDownList>        
                                  <asp:DropDownList runat="server"  ID="ddlfreewithparents" CssClass="form-control select2 show-group" data-placeholder="Select Discount/ inclusion (Free)">
                                    <asp:ListItem Value="0">Select Discount/ inclusion (Free)</asp:ListItem>
                                    <asp:ListItem Value="1">Mandatory</asp:ListItem>
                                    <asp:ListItem Value="2">Optional</asp:ListItem>
                                  </asp:DropDownList>
                                  <asp:RequiredFieldValidator ID="rqfdiscount" runat="server" ControlToValidate="ddlfreewithparents" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Select Discount/ inclusion (Free)" ValidationGroup="comboproduct" ForeColor="Red"></asp:RequiredFieldValidator>
                                    <asp:DropDownList runat="server"  ID="ddldiscountwithparent" CssClass="form-control select2 show-comobo" data-placeholder="Select Discount/ inclusion (Free)">
                                    <asp:ListItem Value="0">Select Discount/ inclusion (Free)</asp:ListItem>
                                    <asp:ListItem Value="1">Value</asp:ListItem>
                                    <asp:ListItem Value="2">Percent</asp:ListItem>
                                  </asp:DropDownList>
                                  <asp:RequiredFieldValidator ID="rfvinclusion" runat="server" ControlToValidate="ddldiscountwithparent" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Select Discount/ inclusion (Free)" ValidationGroup="comboproduct" ForeColor="Red"></asp:RequiredFieldValidator>
                              </div>                                
                              <div class="form-group col-12 col-sm-6 mb-2 ">                          
                                  <asp:TextBox runat="server"  CssClass="form-control select2" MaxLength="5" placeholder="Discount Value" autocomplete="off" ID="txtdiscounttype"></asp:TextBox>  
                                 <asp:RequiredFieldValidator ID="rfvvalue" runat="server" Enabled="false" ControlToValidate="txtdiscounttype" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="please give a value" ValidationGroup="comboproduct" ForeColor="Red"></asp:RequiredFieldValidator>
                              </div>

                              <div class="form-group col-12 col-sm-6 mb-2 mb-sm-0">
                                  <asp:TextBox runat="server" CssClass="form-control select2" ID="txtquantity" MaxLength="5" placeholder="Combo Quantity" autocomplete="off" TextMode="Number"  ></asp:TextBox>   
                                <asp:RequiredFieldValidator ID="rfvqutatity" runat="server" ControlToValidate="txtquantity" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="please give a Quantity" ValidationGroup="comboproduct" ForeColor="Red"></asp:RequiredFieldValidator>

                              </div>
                              <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                                  <asp:Button ID="btnaddcombo" OnClick="btnaddcombo_Click" runat="server" Text="Add to Combo" CssClass="btn px-3 d-inline-block btn-primary" ValidationGroup="comboproduct"/>
<%--                           <asp:LinkButton runat="server" ID="btnaddcombo" OnClick="btnaddcombo_Click1" Text="Add to Combo" CssClass="btn px-3 d-inline-block btn-primary"></asp:LinkButton>--%>
                                  </div>                              
                            </div>
                          </div>                            
                          <div class="card-body">
                            <div class="table-responsive" style="max-height:350px;">
                              <table class="table table-bordered mg-b-0 table-head-fixed" cellspacing="0" style="border-collapse:collapse;">                                
                                <thead>
                                  <tr>
                                    <th scope="col">Product & Combination</th>
                                    <th class="left_align" scope="col" style="width:100px;">Action</th>
                                  </tr><!--tr-->
                                </thead>
                                  <asp:Repeater runat="server" ID="rptrcomboproduct" OnItemCommand="rptrcomboproduct_ItemCommand">
                                      <ItemTemplate>
                                          <tr>
                                  <td>
                                    <div class="d-flex align-items-center">
                                      <div class="prodct_img">
                                        <img class="tbl_prod_img hoverimgpopover" runat="server" onerror="this.src='index_files/image_on_error.svg'"
                                          src='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("image_url").ToString()) %>'>
                                        <div class="imgpopover">
                                          <img onerror="this.src='index_files/image_on_error.svg'"
                                            src='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("image_url").ToString()) %>' runat="server">
                                        </div>
                                        <input type="hidden" value="4443">
                                      </div>
<%--                                      <span id="" title="" class="prd_name"><strong>3 Porotta + 2 pc Chicken Chukka 1 Pack</strong></span>--%>
                                      <asp:Label runat="server" ID="ltrproductname" Text='<%# Eval("ProductDetails")%>' CssClass="prd_name"></asp:Label>
                                    </div>
                                  </td>
                                  
                                  <td align="center">
<%--                                    <a id="" class="btn btn-outline-primary py-0 mr-2" action="Edit" href="javascript:void(0)">Edit</a>--%>
<%--                                    <a itemid="" href="javascript:void(0)" style="color:#DC3545;" title="Remove"><i class="fa fa-trash"></i></a>--%>
                                      <asp:LinkButton ID="btnDelete" style="color:#DC3545;"  runat="server" CommandName="DeleteItem" CommandArgument='<%# Eval("ComboProductId") + "," + Eval("productId") %>'  OnClientClick="return confirm('Are you sure you want to delete this item?');"><i class="fa fa-trash"></i></asp:LinkButton>
                                  </td>
                                </tr><!--tr-->
                                      </ItemTemplate>                                 
                                  </asp:Repeater>                                  
                              </table><!--table-->
                            </div><!--table-responsive-->
                            <div class="d-flex justify-content-center mt-3">
                                <asp:LinkButton runat="server" Text="Save Combo" ID="btnsavecombo" OnClick="btnsavecombo_Click"  CssClass="btn btn-primary bd-0 px-3"></asp:LinkButton>
                            </div>                             
                          </div>
                        </div>
                        
                      </div>
                    </div>
                  </div>
                </div><!--CreateComboProducts-->


                <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-bordered mg-b-0" cellspacing="0" id="tblcombo" style="border-collapse:collapse;">
                        <thead>
                          <tr>
                            <th scope="col">Combo Parent Product</th>
                            <th scope="col">Free Products</th>
                            <th scope="col">Discounted Products</th>
                            <th class="left_align" scope="col" style="width:100px;">Suggested </th>
                            <th class="left_align" scope="col" style="width:100px;">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                            <asp:ListView runat="server" ID="lstshowcombo" DataSourceID="SDScomboproductshow" OnItemCommand="lstshowcombo_ItemCommand">
                                <ItemTemplate>
                                     <tr>
                            <td>
                              <div class="d-flex align-items-center">
                                <div class="prodct_img">
                                  <img class="tbl_prod_img hoverimgpopover" onerror="this.src='index_files/image_on_error.svg'"
                                    src='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("image_url").ToString()) %>'>
                                  <div class="imgpopover">
                                    <img onerror="this.src='index_files/image_on_error.svg'"
                                      src='<%# RetalineProAgent.Service.Common.ImageUrl(Eval("image_url").ToString()) %>'>
                                  </div>                                
                                </div>
                                <span id=""
                                  title="" class="prd_name"><strong><%# Eval("stit_SKU")%></strong></span>                
                              </div>
                            </td>
                            <td><%# Eval("freeProducts") == DBNull.Value ? "NA" : Eval("freeProducts") %></td>
                            <td><%# Eval("discProducts") == DBNull.Value ? "NA": Eval("discProducts")%></td>
                            <td><%# Eval("suggestedCount") == DBNull.Value ? "NA":Eval("suggestedCount")%> Nos.</td>
                            <td>
                              <div class="d-flex justify-content-center">
                                  <asp:LinkButton runat="server" ID="btncomboedit" comboid='<%# Eval("ProductId")%>' brid='<%# Eval("BranchId")%>' CssClass="btn btn-outline-primary btn-sm" OnClick="btncomboedit_Click" Text="Edit"></asp:LinkButton>  
                                 <asp:LinkButton ID="btncomboDelete" CssClass="ml-2" style="color:#DC3545;"  runat="server"  comboid='<%# Eval("ProductId")%>' brid='<%# Eval("BranchId")%>'  OnClick="btncomboDelete_Click"  OnClientClick="return confirm('Are you sure you want to delete this item?');"><i class="fa fa-trash"></i></asp:LinkButton>
                              </div>
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
                             <selectedrowstyle cssclass="selectrow" />                            
                            <asp:SqlDataSource runat="server" ID="SDScomboproductshow" OnSelecting="SDScomboproductshow_Selecting"  ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" 
                             SelectCommand="SELECT cp.*, i.stit_SKU
                                ,(SELECT image_url FROM finascop_stock_item_images WHERE product_id=i.stit_id ORDER BY image_type DESC LIMIT 1 ) AS image_url
                                ,(SELECT COUNT(*) FROM Comboproduct c WHERE c.productId= cp.productId and costType=3 AND StoreGroupId=@storegroupid AND c.`BranchId`=cp.BranchId) AS suggestedCount
                                ,(SELECT GROUP_CONCAT(stit_SKU, ',') FROM Comboproduct c INNER JOIN finascop_stock_itemmaster i ON i.stit_ID=c.ComboProductId WHERE c.ProductId=cp.ProductId AND costType=1 AND StoreGroupId=@storegroupid AND c.`BranchId`=cp.BranchId) AS freeProducts
                                ,(SELECT GROUP_CONCAT(stit_SKU, ',') FROM Comboproduct c INNER JOIN finascop_stock_itemmaster i ON i.stit_ID=c.ComboProductId WHERE c.ProductId=cp.ProductId AND  costType=2 AND StoreGroupId=@storegroupid AND c.`BranchId`=cp.BranchId) AS discProducts
                                FROM Comboproduct cp 
                                INNER JOIN  finascop_stock_itemmaster i ON i.stit_ID=cp.productId 
                                WHERE StoreGroupId=@storegroupid GROUP BY cp.productId">
                                 <SelectParameters>
                                        <asp:Parameter Name="storegroupid" DefaultValue="-1" />   
                                         <asp:Parameter Name="branchid" DefaultValue="-1" />   
                                    </SelectParameters>

                            </asp:SqlDataSource>
                        </tbody>
                      </table>   
                         <div class="pagenation_listview p-3">
                        <asp:DataPager ID="DataPager1" runat="server" PageSize="10"
                            PagedControlID="lstshowcombo">
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
                  </div><!-- table-responsive -->
                </div><!--card-body-->
              </div>
            </div>
          </div>
    <script type="text/javascript">
        $(document).ready(function ()
        {           
           
            $('.select2-show-search').select2();
            // Reference all dropdowns by class
            var $showGroupDropdowns = $('.show-group');
            var $hideGroupDropdowns = $('.hide-group');
            var $hideshowcombodropdown = $('.show-comobo');
            var $discountTypeTextbox = $('#<%=txtdiscounttype.ClientID %>');

            // Reset visibility and textbox state
            $showGroupDropdowns.hide();
            $hideshowcombodropdown.hide();
            $hideGroupDropdowns.show();
            $('#<%=ddlComboCostType.ClientID %>').change(function () {

                // Get the selected value of ddlComboCostType
                var selectedValue = $(this).val();

                
                $discountTypeTextbox.prop('disabled', true);

                // Show/hide dropdowns based on the selected value
                switch (selectedValue) {
            case "1": // Free with parent product
                    $('#<%=ddlfreewithparents.ClientID %>').show();
                    $showGroupDropdowns.show();
                    $hideGroupDropdowns.hide();
                    $hideshowcombodropdown.hide();
                 break;

            case "2": // Discounted with parent product
                    $('#<%=ddldiscountwithparent.ClientID %>').show();
                    $hideshowcombodropdown.show();
                    $hideGroupDropdowns.hide();
                    $showGroupDropdowns.hide();
                    $discountTypeTextbox.prop('disabled', false);
                 break;

            case "3": // Suggested with parent product
                        $hideGroupDropdowns.show();
                        $('#<%=ddlDiscountInclusion.ClientID %>').prop('disabled', true);
                        $showGroupDropdowns.hide();
                        $hideshowcombodropdown.hide();

                break;

            default: // Handle any other cases
                        $hideGroupDropdowns.show();
                        $showGroupDropdowns.hide();
                        $hideshowcombodropdown.hide();
            break;
            }

            });

        });
    </script>

    <style>
        .select2-container {
             width: 100% !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
                display: block;
        }

        .select2-container.select2-container--open {
              z-index: 1050;
            }
    </style>
</asp:Content>