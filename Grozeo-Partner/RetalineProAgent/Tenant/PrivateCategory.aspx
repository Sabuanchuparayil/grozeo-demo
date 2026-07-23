<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Async="true" Title="Private Category" AutoEventWireup="true" CodeBehind="PrivateCategory.aspx.cs" Inherits="RetalineProAgent.PrivateCategory" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href='<%= GetBackLink() %>'><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Delivery Boys</h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle"><asp:Label ID="lblHeader" runat="server" CssClass="slim-pagetitle"></asp:Label></h6>
        <asp:Label ID="lblDescription" runat="server" CssClass="description-class"></asp:Label>
    </div>
    
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top" runat="server" id="createform" visible="true">
                    <div class="card-tools">
                            <div class="row row-sm">
                                <div class="col-12 col-lg-9">
                                    <div class="row row-sm">
                                        <div class="col-sm-6 col-lg-3">
                                                <div class="form-group-sm">
                                                    <asp:Label ID="lblPrivateCategory" runat="server" CssClass="form-control-label tx-dark">
                                                     Private Category: <span class="tx-danger">*</span>
                                                    </asp:Label>
                                                    <input type="text" style="display: none" />
                                                    <input type="password" style="display: none" />
                                                    <asp:TextBox ID="txtVirtCat" runat="server" CssClass="form-control" autocomplete="off" />
                                                    <asp:RequiredFieldValidator runat="server" ControlToValidate="txtVirtCat" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Private category is required" ValidationGroup="InsertCategory" ForeColor="Red"></asp:RequiredFieldValidator>
                                                </div>
                                            </div>
                                        <!-- col-3 -->
                                        <div class="col-sm-6 col-lg-3">
                                            <asp:Panel ID="pnlUploadImage" runat="server" CssClass="form-group-sm addnew_imagebox">
                                                <label class="w-100 text-left m-0 tx-dark">Upload Image</label>
                                                <asp:FileUpload runat="server" accept="image/*" ID="fileUploadImgs" CssClass="form-control fileupload_productimage" data-target="#imgCatImage" />
                                            </asp:Panel>
                                            <asp:Panel ID="pnlCategoryImage" Visible="false" runat="server" CssClass="form-group-sm imageprivew_box">
                                                <label>&nbsp;</label>
                                                <div class="imageprivew">
                                                    <asp:Image ID="imgCatImage" CssClass="addedimage" runat="server" />
                                                    <div class="imgpopover">
                                                        <asp:Image ID="myImage" runat="server" ImageUrl='<%# myImage.ImageUrl %>' />
                                                    </div>
                                                    <asp:LinkButton runat="server" CssClass="change_image ml-1" ID="lbtnDeleteImg" OnClick="lbtnDeleteImg_Click" Text="Delete image"></asp:LinkButton>
                                                </div>
                                            </asp:Panel>
                                        </div>
                                        <div class="col-lg-6 d-flex flex-wrap align-items-end mt-3 mt-lg-0" runat="server" id="homeCatList" visible="true">

                                            <div class="col-sm-6 p-0">
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-2">
                                                        <asp:CheckBox ID="chkHome" CssClass="mr-1" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("vc_isHome").Equals("Active") %>' /></span>
                                                    <span class="tx-dark tx-12">Include in Home Menu</span>
                                                </div>
                                            </div>

                                            <div class="p-0 col-sm-6">
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-2">
                                                        <asp:CheckBox ID="chkCat" CssClass="mr-1" TextAlign="Left" AutoPostBack="true" runat="server" Checked='<%# Eval("vc_isInCategory").Equals("Active") %>' /></span>
                                                    <span class="tx-dark tx-12">Include in Category List</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12" runat="server" visible="true" id="listdropdown">
                                            <div class="row row-sm">
                                                <div class="col-sm-6">
                                                    <div class="form-group-sm">
                                                        <label class="form-control-label tx-dark mt-2" visible="false" id="lblDept" runat="server">Include with retail category: <span class="tx-danger">*</span></label>
                                                        <asp:DropDownList ID="selDept" runat="server" AutoPostBack="true" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSDepartment" DataTextField="parent_category" AppendDataBoundItems="true" DataValueField="parent_category_id" Visible="false">
                                                            <asp:ListItem Text="Select retail category" Value=""></asp:ListItem>
                                                        </asp:DropDownList>
                                                        <asp:SqlDataSource runat="server" ID="SDSDepartment" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                                            SelectCommand="SELECT parent_category_id, parent_category FROM mypha_productparent_category pc
                                                            INNER JOIN finascop_branch_group_business_type bgt ON pc.parent_category_businessType=bgt.business_type_id 
                                                            WHERE store_group_id=@storegroup AND STATUS=1"
                                                            OnSelecting="SDSDepartment_Selecting">
                                                            <SelectParameters>
                                                                <asp:Parameter Name="storegroup" />
                                                            </SelectParameters>
                                                        </asp:SqlDataSource>
                                                        <asp:RequiredFieldValidator runat="server" ID="department" Visible="false" ControlToValidate="selDept" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Retail category is required" ValidationGroup="InsertCategory" ForeColor="Red"></asp:RequiredFieldValidator>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group-sm">
                                                        <label class="form-control-label tx-dark mt-2" id="lblCat" visible="false" runat="server">List under category: <span class="tx-danger">*</span></label>
                                                
                                                        <asp:DropDownList ID="selCat" runat="server" AutoPostBack="True" CssClass="form-control select2" ForeColor="GrayText" DataSourceID="SDSCat" DataTextField="category_name" AppendDataBoundItems="true" DataValueField="category_id" Visible="false">
                                                            <asp:ListItem Text="Select category" Value=""></asp:ListItem>
                                                        </asp:DropDownList>
                                                        <asp:SqlDataSource runat="server" ID="SDSCat" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                                            SelectCommand="SELECT category_id, category_name, parent_category_businessType, store_group_id FROM mypha_productcategory mp
                                                            INNER JOIN mypha_productparent_category pc ON mp.parent_category=pc.parent_category_id 
                                                            INNER JOIN finascop_branch_group_business_type bgt ON pc.parent_category_businessType=bgt.business_type_id 
                                                            WHERE store_group_id=@storegroup AND mp.status=1 AND mp.parent_category=@depName"
                                                            OnSelecting="SDSCat_Selecting">
                                                            <SelectParameters>
                                                                <asp:Parameter Name="storegroup" />
                                                                <asp:ControlParameter Name="depName" ControlID="selDept" />
                                                            </SelectParameters>
                                                        </asp:SqlDataSource>
                                                        <asp:RequiredFieldValidator runat="server" ID="category" Visible="false" ControlToValidate="selCat" CssClass="error_msg_wrap" Display="Dynamic" ErrorMessage="Category is required" ValidationGroup="InsertCategory" ForeColor="Red"></asp:RequiredFieldValidator>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- col-10 -->

                                <div class="mt-2 mt-lg-0 col-12 col-lg-3 px-0 px-lg-2 d-flex flex-wrap justify-content-lg-end align-items-end">
                                    <label class="w-100 d-none d-lg-inline-block mb-1">&nbsp</label>
                                    <div class="d-flex align-items-center">
                                        <asp:Button runat="server" ID="btnRefresh" Visible="false" OnClick="btnRefresh_Click" CssClass="btn btn-outline-primary mt-2 ml-2 mr-2" Text="Refresh" />
                                        <asp:Button runat="server" ID="btnSubmit" OnClick="btnSubmit_Click" CssClass="btn btn-primary mt-2" ValidationGroup="InsertCategory" />
                                    </div>
                                </div>

                            </div>
                            <!-- row -->
                    </div>
                </div>
                    
                <div class="card-body">
               <div class="table-responsive mailbox-messages">
                                <asp:GridView AutoGenerateColumns="false" ID="gvPrivateCat" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" OnRowUpdating="gvPrivateCat_RowUpdating" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnRowDataBound="gvPrivateCat_RowDataBound" OnDataBound="gvPrivateCat_DataBound" DataSourceID="SDSPrivateCat">
                                    <Columns>
                                        <asp:TemplateField HeaderText="" HeaderStyle-Width="220px">
                                            <ItemStyle Width="220px" /> 
                                            <ItemTemplate>
                                                <div class="d-flex align-items-center">
                                                    <div class="prodct_img">
                                                        <asp:Image runat="server" CssClass="tbl_prod_img hoverimgpopover" onerror="this.src='/content/images/image_on_error.svg'" ImageUrl='<%# Eval("image_url") != null && Eval("image_url").ToString() != "" ? Eval("image_url").ToString() : "/content/images/image_on_error.svg" %>' loading="lazy" />
                                                        <div class="imgpopover">
                                                            <asp:Image runat="server" onerror="this.src='/content/images/image_on_error.svg'"
                                                                ImageUrl='<%# Eval("image_url") != null && Eval("image_url").ToString() != "" ? Eval("image_url").ToString() : "/content/images/image_on_error.svg" %>' />
                                                        </div>
                                                    </div>
                                                    <asp:Label runat="server" ID="lblName" CssClass="prd_name" ToolTip='<%# Bind("vc_name") %>'>
                                                    <strong><%# Eval("vc_name") %></strong>
                                                    </asp:Label>
                                                </div>
                                            </ItemTemplate>
                                            <EditItemTemplate>
                                                <div class="upload_box_wrap">
                                                    <div class="upload_box">
                                                        <!-- FileUpload for Image Selection -->
                                                        <asp:FileUpload accept="image/*" runat="server" ID="fileimgUpload" CssClass="form-control fileupload_productimage" />
                                                        <asp:Label ID="lblProd1" runat="server" CssClass="remove" data-target="#imgCatImage">X</asp:Label>

                                                        <!-- HiddenField to Store Previous Image Path -->
                                                        <asp:HiddenField ID="hidImage" runat="server" Value='<%# myImage.ImageUrl %>' />

                                                        <!-- Image Control for Preview -->
                                                        <asp:Image ID="imgCatImage" runat="server" CssClass="uploadimg" ImageUrl='<%# string.IsNullOrEmpty(Eval("image_url")?.ToString()) ? "/content/images/uplad.png" : Eval("image_url").ToString() %>' AlternateText="Upload" onerror="this.src='/content/images/uplad.png'" />
                                                    </div>

                                                    <!-- TextBox for Name -->
                                                    <asp:TextBox ID="txtName" runat="server" Text='<%# Bind("vc_name") %>' CssClass="form-control"></asp:TextBox>
                                                </div>
                                            </EditItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Category">
                                            <ItemTemplate>
                                                <asp:PlaceHolder runat="server" Visible='<%# (Eval("category_name") == DBNull.Value || String.IsNullOrEmpty((string)Eval("category_name")) ? false: true) %>'>
                                                <%# Eval("category_name") %></asp:PlaceHolder>
                                        </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField HeaderText="Retail Category">
                                            <ItemTemplate>
                                            <asp:PlaceHolder runat="server" Visible='<%# (Eval("parent_category") == DBNull.Value || String.IsNullOrEmpty((string)Eval("parent_category")) ? false: true) %>'>
                                            <%# Eval("parent_category") %></asp:PlaceHolder>
                                        </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="No. Of Items" DataField="itemCnt" SortExpression="itemCnt" HeaderStyle-CssClass="left_align" ItemStyle-HorizontalAlign="Right" ReadOnly="true"/>
                                        <asp:TemplateField>
                                            <ItemTemplate>
                                                <a runat="server" title="Add Items" href='<%# $"/Tenant/PrivateCatSettings.aspx?id={Eval("vc_id")}&type={Request.QueryString["type"] ?? ""}" %>'>
                                                    <i class="fa-solid fa-circle-plus"></i>
                                                </a>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField>
                                            <ItemTemplate>
                                                <a runat="server" title="List Items" href='<%# $"/Tenant/PrivateCatItems.aspx?id={Eval("vc_id")}&type={Request.QueryString["type"] ?? ""}" %>'>
                                                    <i class="fa-thin fa-eye"></i>
                                                </a>
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField>
                                            <ItemTemplate>
                                                <asp:LinkButton ID="btnEdit" runat="server" Text="Edit" CausesValidation="false" CommandName="Edit" CommandArgument='<%# Eval("vc_id")%>' />
                                            </ItemTemplate>
                                            <EditItemTemplate>
                                                <div class="d-flex align-items-center">
                                                    <asp:Button ID="btnUpdate" runat="server" CssClass="btn btn-sm btn-outline-primary mr-2" CausesValidation="false" Text="Update" CommandName="Update" CommandArgument='<%# Bind("vc_id") %>' />
                                                    <asp:Button ID="btnCancel" runat="server" CssClass="btn btn-sm btn-outline-secondary" Text="Cancel" CommandName="Cancel" />
                                                </div>
                                            </EditItemTemplate>
                                        </asp:TemplateField>
                                        <asp:TemplateField ItemStyle-HorizontalAlign="Center">
                                            <ItemTemplate>
                                                <asp:LinkButton runat="server" OnClick="DeleteItem_Click" itemid='<%# Eval("vc_id") %>' ForeColor="#dc3545" OnClientClick="return confirm('Are you sure you want to delete the category?');" ><i class="fa fa-trash"></i></asp:LinkButton>
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

                   <asp:SqlDataSource runat="server" ID="SDSPrivateCat" OnSelecting="SDSPrivateCat_Selecting" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                       SelectCommand="SELECT retaline_virtual_category.vc_id, vc_name, retaline_virtual_category.image_url, 
                        vc_parentCategoryId, isFeatured, IF((isFeatured=0), 'No', 'Yes') AS featured, 
                        vc_categoryId, mypha_productparent_category.parent_category AS parent_category,
                        category_name, IF((vc_status=0), 'Inactive', 'Active') AS vc_status,
                        (SELECT COUNT(stit_id) FROM retaline_vc_items vc WHERE vc.vc_id=retaline_virtual_category.vc_id) AS itemCnt
                        FROM retaline_virtual_category 
                        LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId 
                        LEFT JOIN mypha_productcategory ON vc_categoryId = category_id 
                        WHERE store_group_id = @storegroup AND 
                        ((@categoryType IS NULL AND isFeatured = 0 AND isPreferred = 0) OR 
                        (@categoryType = 'featured' AND isFeatured = 1) OR 
                        (@categoryType = 'preferred' AND isPreferred = 1))"
                       UpdateCommand="UPDATE retaline_virtual_category SET vc_name=@vc_name, image_url=@image_url WHERE vc_id=@vc_id">
                       <SelectParameters>
                           <asp:Parameter Name="storegroup" />
                           <asp:Parameter Name="categoryType" Type="String" />
                       </SelectParameters>
                       <UpdateParameters>
                           <asp:Parameter Name="vc_id" />
                           <asp:Parameter Name="vc_name" />
                           <asp:Parameter Name="image_url" />
                       </UpdateParameters>
                   </asp:SqlDataSource>
               </div>
                </div>
            </div>
          </div>
        </div>
    <asp:HiddenField ID="hidCategoryType" runat="server" />
   <asp:HiddenField ID="hidSelectedItems" runat="server" />
    <div id="modaldemo5" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-danger mg-b-20"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success tx-semibold mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->
    <script type="text/javascript">
        $(function () {
            $('#modaldemo4').on('hidden.bs.modal', function () {
                var categoryType = $('#hidCategoryType').val(); 
                var redirectUrl = "/Tenant/PrivateCategory";
                if (categoryType === "featured") {
                    redirectUrl = "/Tenant/PrivateCategory?type=featured";
                } else if (categoryType === "preferred") {
                    redirectUrl = "/Tenant/PrivateCategory?type=preferred";
                }

                window.location.href = redirectUrl;
            });
        });

        $(document).ready(function () {
            // File input change event
            $('div.upload_box_wrap input.fileupload_productimage').unbind('change').on('change', function (e) {
                const file = this.files[0];
                const imgElement = $(this).closest('div.upload_box').find('img.uploadimg');

                if (file) {
                    imgElement.attr('src', URL.createObjectURL(file)).show();
                } else {
                    imgElement.attr('src', '/content/images/uplad.png'); // Default image
                }
            });

            // Remove uploaded image
            $("span.remove").click(function () {
                const uploadBox = $(this).closest(".upload_box");
                const imgElement = uploadBox.find("img.uploadimg");

                imgElement.attr('src', '/content/images/uplad.png'); // Reset to default image
                uploadBox.find("input[type=hidden]").val("");
                uploadBox.find("input[type=file]").val("");
            });

            // Check on page load if an image is already present
            $(".upload_box img.uploadimg").each(function () {
                if (!$(this).attr('src') || $(this).attr('src') === '') {
                    $(this).attr('src', '/content/images/uplad.png'); // Default image
                }
            });
        });

        $(function () {
            // Check on page load if any image is already present
            $(".upload_box img").each(function () {
                if ($(this).attr('src') !== "/content/images/uplad.png" && $(this).attr('src') !== "") {
                    $(this).closest('.upload_box').addClass("rmvbg");
                } else {
                    $(this).closest('.upload_box').removeClass("rmvbg");
                }
            });

            // Change event for the FileUpload control
            $(".fileupload_productimage").change(function () {
                let file = $(this), imgControlName = file.data('target');
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = e => {
                        file.closest('.upload_box').addClass("rmvbg").find(imgControlName).attr('src', e.target.result);
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Click event for the Remove button
            $('.remove').click(function () {
                let imgControlName = $(this).data('target');
                $(this).closest('.upload_box').removeClass("rmvbg").find(imgControlName).attr('src', "/content/images/uplad.png");
            });
        });

        $(document).ready(function () {
            // File upload change event to preview the image
            $('.fileupload_productimage').on('change', function () {
                const fileInput = this;
                const uploadBox = $(this).closest('.upload_box');
                const imgPreview = uploadBox.find('.uploadimg');

                if (fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imgPreview.attr('src', e.target.result);
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                }
            });
        });
    </script>

    <style>
        .upload_box_wrap {
            display:flex;
            gap:10px;
            align-items:center;
        }
        .upload_box_wrap .upload_box {
            width:35px;
            height: 35px;
            padding: 0;
            margin:0;
            background-size: 36px;
        }
        .upload_box input[type="file"] {
            width: 34px;
            height: 34px;
        }
        .upload_box_wrap > input{
            max-width:150px;
        }

    .uploadimg {
        max-width:100%;
        max-height: 100%;
        object-fit: cover; 
    }

    .remove {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(255, 0, 0, 0.7);
        color: white;
        padding: 2px 5px;
        cursor: pointer;
        font-weight: bold;
        z-index: 10;
    }
    @media (max-width: 991px) {
        .upload_box_wrap {
            flex-wrap: wrap
        }
    }

    .imgpopover{
        height: auto;
        max-height: 190px;
    }
    .imgpopover img{
        max-height: 140px;
    }
    </style>
</asp:Content>

