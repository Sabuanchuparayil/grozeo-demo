<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="UploadStock.aspx.cs" Inherits="RetalineProAgent.UploadStock" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <style>
  h3 {
    line-height: 30px;
    text-align: center;
  }

  #drag_and_drop {
    height: 200px;
    border: 2px dashed #ccc;
    text-align: center;
    font-size:18px;
    background: #f9f9f9;
    margin-bottom: 15px;
    cursor: pointer;
    border-radius: 10px;
  }

  .drag_over {
    color: #000;
    border-color: #000;
  }

  .thumbnail {
    width: 100px;
    height: 100px;
    padding: 2px;
    margin: 2px;
    border: 2px solid lightgray;
    border-radius: 3px;
    float: left;
  }

  #drag_store_img {
    display: none;
  }
</style>

        <script type="text/javascript">
            var formData = new FormData(); var fileSelected = false;
            $(document).ready(function () {
                formData = new FormData();
                //$("html").on("dragover", function (e) {
                //    e.preventDefault();
                //    e.stopPropagation();
                //});

                //$("html").on("drop", function (e) {
                //    e.preventDefault();
                //    e.stopPropagation();
                //});

                $('#drag_and_drop').on('dragover', function () {
                    $(this).addClass('drag_over');
                    return false;
                });

                $('#drag_and_drop').on('dragleave', function () {
                    $(this).removeClass('drag_over');
                    return false;
                });

                $('#drag_and_drop').on('drop', function (e) {
                    e.preventDefault();
                    $(this).removeClass('drag_over');
                    //var formData = new FormData();
                    var files = e.originalEvent.dataTransfer.files;
                    if (files.length > 0)
                    //for (var i = 0; i < files.length; i++) {
                      
                        //if (!(/\.(xlsx|xls|xlsm)$/i).test(files[0].name)) {
                        //    alert('Please upload valid excel file .xlsx, .xlsm, .xls only.');
                        //}
                        if (validateFile(files[0])){
                            $('#uploaded_file').html("<i class='fa fa-file-excel-o mr-2 tx-24 tx-primary' aria-hidden='true'></i>" + files[0].name);
                        }

                    //}
                    //uploadFormData(formData);
                });

                $('#flExcelUpload').on('change', function (e) {
                    e.preventDefault();
                    $(this).removeClass('drag_over');
                    //var formData = new FormData();
                    var files = $('#flExcelUpload')[0].files;
                    console.log(files);
                    if (files)
                        //for (var i = 0; i < files.length; i++) {

                        //if (!(/\.(xlsx|xls|xlsm)$/i).test(files[0].name)) {
                        //    alert('Please upload valid excel file .xlsx, .xlsm, .xls only.');
                        //}
                        if (validateFile(files[0])) {
                            $('#uploaded_file').html("<i class='fa fa-file-excel-o mr-2 tx-24 tx-primary' aria-hidden='true'></i>" + files[0].name);
                        }
                        //}

                    //}
                    //uploadFormData(formData);
                });

            });

            function validateForm() {
                if (typeof (Page_ClientValidate) == 'function') {
                    Page_ClientValidate('StockUpdate');
                }
                if (Page_IsValid) {
                    return true;
                }
                return false;
            }

            function uploadFormData(form_data, obj) {
                if (!fileSelected || form_data == null || form_data.length <= 0) {
                    showModal('Error!', 'No file selected. Please select a file to upload', false);
                    return false;
                }

                if (!validateForm()) {
                    return false;
                }
                if (obj)
                    $(obj).addClass('processing_loader');
                $.ajax({
                    url: '/tenant/uploadstock',//"img_fl_store.php",
                    method: "POST",
                    data: form_data,
                    fileInputs: $('#flExcelUpload'), 
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        //$('#uploaded_file').html('File uploaded successfully! Please visit the <a href=\'/itemsforsale\'>inventory</a> page to view the updated stock');
                        if (obj)
                            $(obj).removeClass('processing_loader');

                        if (data) {
                            var result = $.parseJSON(data);
                            showModal('Bulk Import Stock', result.data, (result.result == 1 ? true : false), '/Tenant/StockPrice');
                        }
                        else {
                            showModal('Bulk Import Stock', data);
                        }
                        //$('#uploaded_file').append(data);
                    },
                    error: function (er) {
                        if (obj)
                            $(obj).removeClass('processing_loader');
                        showModal('Import Inventory Failed', 'Operation failed. There is a technical error happened at server side. Please try again or contact support for more details.', false);
                        console.log(er);
                    }
                });
            }

            function validateFile(file) {

                if (!(/\.(xlsx|xls|xlsm)$/i).test(file.name)) {
                    showModal('Error!!', 'Please upload valid excel file .xlsx, .xlsm, .xls only.', false);
                    $('#flExcelUpload').val('');
                    return false;
                }
                else {
                    formData = new FormData();
                    formData.append('file[]', file);
                    formData.append('brid', <%= (plcSelectBranchModel.Visible && selBranches.Items.Count > 1 ? String.Format("$('#{0}').find(':selected').val()", selBranches.ClientID) : "'-1'") %>);
                    fileSelected = true;
                    //$('#uploaded_file').html(files[0].name);
                    //formData.append('file[]', files[0]);
                }
                return true;
            }
            

        </script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
<asp:PlaceHolder ID="plcWizardBrudcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Navigations/Products">Products</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/ItemsForSale">Manage stock & price</a></li>
    <li class="breadcrumb-item active" aria-current="page">Import Stock</li>--%>
    <a href="/Tenant/StockPrice"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:PlaceHolder>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">Manage stock and price</h6>
        <p class="mb-0">Upload stock from excel file</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body p-3">
                        <div class="row row-sm">
                            <div class="col-sm-4 input-group">
                                <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                    <asp:CustomValidator runat="server" Display="Dynamic" Text=" " ErrorMessage=" "></asp:CustomValidator>
                                    <label class="w-100 mb-1 tx-dark">Branch: </label>
                                    <asp:DropDownList ID="selBranches" OnDataBound="selBranches_DataBound" DataSourceID="SDSBranches" AppendDataBoundItems="true" DataTextField="br_Name" ValidationGroup="StockUpdate" DataValueField="br_ID" CssClass="form-control w-100 select2" runat="server">
                                        <asp:ListItem Text="Select Branch" Value=""></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" CssClass="error_msg_wrap" Display="Dynamic" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="Select Store" ForeColor="Red" ErrorMessage="Select store"></asp:RequiredFieldValidator>
                                </asp:PlaceHolder>
                                <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                                ProviderName="MySql.Data.MySqlClient" ><SelectParameters><asp:Parameter Name="storegroupid" DefaultValue="-1" /><asp:Parameter Name="branchid" DefaultValue="-1" /></SelectParameters></asp:SqlDataSource>
                            </div>
                            <div class="col-12 pt-1">
                                <small>Download a <asp:LinkButton ID="lbtnDownload" runat="server" ValidationGroup="StockUpdate" OnClick="lbtnDownloadExcel_Click">sample file template</asp:LinkButton> pre-loaded with your current stock. You can update the values and upload it.</small>
                            </div>

                            <div class="col-12">
                                <div id="actions" class="row">
                                    <div class="col-lg-6">
                                        <div class="mt-4 pb-3">
                                            <div id="drag_and_drop" class="p-4 d-flex flex-wrap justify-content-center align-items-center align-content-center" onclick="$('#flExcelUpload').click()">
                                                
                                                <i class="fa fa-upload mb-3" aria-hidden="true"></i>
                                                <h5 class="font-weight-normal w-100">Drag & Drop or Choose file to upload</h5>
                                            </div>
                                            <div id="uploaded_file" class="lh-1 tx-12 d-flex align-items-center"></div>
                                        </div>
                                        <input type="file" id="flExcelUpload" accept=".xlsx, .xls, .csv" onchange="return validateFile(this.files[0])" style="display: none" />
                                    </div>

                                </div>
                            </div>

                            <div class="col-12">
                                <a href="javascript:void(0);" onclick="uploadFormData(formData, this);" id="hlUploadFile" class="btn btn-inline-block btn-primary">Upload</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

<asp:SqlDataSource runat="server" ID="SDSInventory" OnSelecting="SDSInventory_Selecting" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand="INSERT INTO finascop_stock_branch_inventory(stit_id, branch_id, item_count, mrp, selling_price)
SELECT * FROM(SELECT bi.stit_id, @BranchId, 0 as item_count, 0 as mrp, 0 as selling_price FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE ifnull(@BranchId, 0) > 0  and b.br_storeGroup=@storeId GROUP BY bi.stit_id) AS tmp
WHERE NOT EXISTS (SELECT stit_id FROM finascop_stock_branch_inventory WHERE branch_id = @BranchId); 
 SELECT bi.id, sipc.fsipc_code, bi.discount_selling_price, bi.stit_id, bi.branch_id, bi.item_count, bi.mrp, bi.selling_price, bi.purchasing_unit, stit_brand_name, stit_category_name, med_manufacturename,
bi.fpod_leastSKUmrp, bi.fpod_customerRateHmDel, bi.fpod_customerRateCouDel, bi.fpod_customerRatePikup, bi.fpod_poLandingCostleastSKU, bi.fpod_poMMGleastSKU, 
 (SELECT image_url FROM finascop_stock_item_images WHERE product_id=i.stit_ID LIMIT 1) AS imageurl, i.stit_SKU, (case when bi.item_count > 0 then 1 else 0 end) as stockOrder 
 FROM finascop_stock_branch_inventory bi INNER JOIN finascop_stock_itemmaster i ON i.stit_Id=bi.stit_id INNER JOIN finascop_branch b ON 
b.br_ID=bi.branch_id INNER JOIN finascop_stock_itemmaster_product_codes sipc ON bi.stit_ID=sipc.fsipc_stit_id WHERE b.br_storeGroup=@storeId AND branch_id=@BranchId ORDER BY stockOrder desc, stit_SKU " >
        <SelectParameters>
            <asp:Parameter Name="BranchId" Type="Int32" DefaultValue="-1" ConvertEmptyStringToNull="false" />
            <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
        </SelectParameters>
    </asp:SqlDataSource>

</asp:Content>