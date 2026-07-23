<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="BulkImport.aspx.cs" Inherits="RetalineProAgent.BulkImport" %>

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
                        if (validateFile(files[0])) {
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
                    url: '/tenant/bulkimport',//"img_fl_store.php",
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
                            showModal('Bulk Import Stock', result.data, (result.result == 1 ? true : false), '/Tenant/BulkImport');
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
    <div class="row row-sm">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row row-sm">
                        <div class="col-lg-6 input-group">
                            <%--<label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Branch:</label>
                                <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server" visible="false">--%>
                            <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                <asp:CustomValidator runat="server" Display="Dynamic" Text=" " ErrorMessage=" "></asp:CustomValidator>
                                <label class="w-100 mb-1 tx-dark">Branch: </label>
                                <asp:DropDownList ID="selBranches" OnDataBound="selBranches_DataBound" DataSourceID="SDSBranches" AppendDataBoundItems="true" DataTextField="br_Name" ValidationGroup="StockUpdate" DataValueField="br_ID" CssClass="form-control w-100 select2" runat="server" AutoPostBack="true">
                                    <asp:ListItem Text="Select Branch" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" CssClass="error_msg_wrap" Display="Dynamic" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="Select Store" ForeColor="Red" ErrorMessage="Select store"></asp:RequiredFieldValidator>
                            </asp:PlaceHolder>
                            <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                                ProviderName="MySql.Data.MySqlClient">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                    <asp:Parameter Name="branchid" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                        <div class="col-sm-6 d-flex align-items-end justify-content-sm-end">
                            <div class="float-left float-lg-right mt-3 mt-lg-0">
                                <asp:LinkButton ID="lbtnDownload" runat="server" CssClass="btn px-2 d-block d-md-inline-block btn-primary" ValidationGroup="StockUpdate" OnClick="lbtnDownloadExcel_Click">Download Sample File</asp:LinkButton>
                            </div>
                        </div>
                        <div class="col-12 pt-1">
                            <%--<small>Download a <asp:LinkButton ID="lbtnDownload" runat="server" ValidationGroup="StockUpdate" OnClick="lbtnDownloadExcel_Click">sample file template</asp:LinkButton> pre-loaded with your current stock. You can update the values and upload it.</small>--%>
                            <small>Manage your stock & price in bulk through uploading excel file. Download the sample to view the format required for uploading</small>
                        </div>

                        <div class="col-12">
                            <div id="actions" class="row">
                                <div class="col-lg-12">
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

        <!-- Second Column -->
        <div class="col-lg-7">
            <div class="card-body h-100">
                <div class="table-responsive h-100">
                    <asp:GridView AutoGenerateColumns="false" ID="gvImportProcessData" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                        AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvImportProcessData_DataBound" DataSourceID="SDSBulkImport">
                        <Columns>
                            <asp:BoundField HeaderText="Date" DataField="updatedDate" SortExpression="updatedDate" />
                            <asp:BoundField HeaderText="Time" DataField="updatedTime" SortExpression="updatedTime" />
                            <asp:BoundField HeaderText="Total Records" DataField="totalCount" SortExpression="totalCount" />
                            <asp:BoundField HeaderText="Success" DataField="successCount" SortExpression="successCount" />
                            <asp:BoundField HeaderText="Failure" DataField="missedCount" SortExpression="missedCount" />
                            <asp:TemplateField HeaderStyle-Width="50" HeaderText="Action">
                                <ItemTemplate>
                                    <%--<asp:LinkButton Visible='<%#Convert.ToInt32(Eval("missedCount")) > 0? true : false %>' ID="btnAction" fbiu_id='<%# Eval("fbiu_id") %>' OnClick="btnAction_Click" runat="server" Text="View"></asp:LinkButton>--%>
                                    <asp:Button Enabled='<%#Convert.ToInt32(Eval("missedCount")) > 0? true : false %>' CssClass="btn no-border" ID="btnAction" fbiu_id='<%# Eval("fbiu_id") %>' branchId='<%# Eval("fbiu_branch") %>' totalcount='<%# Eval("totalCount") %>' successcount='<%# Eval("successCount") %>' failedcount='<%# Eval("missedCount") %>' dateTime='<%# Eval("updatedDateTime") %>' filename='<%# Eval("filename") %>' OnClick="btnAction_Click" runat="server" Text="View"></asp:Button>
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
            <!-- card-body -->
        </div>
    </div>

<asp:SqlDataSource runat="server" ID="SDSInventory" OnSelecting="SDSInventory_Selecting" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand="INSERT INTO finascop_stock_branch_inventory(stit_id, branch_id, item_count, mrp, selling_price)
SELECT * FROM(SELECT bi.stit_id, @BranchId, 0 as item_count, 0 as mrp, 0 as selling_price FROM finascop_stock_branch_inventory bi left JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE ifnull(@BranchId, 0) > 0  and b.br_storeGroup=@storeId GROUP BY bi.stit_id) AS tmp
WHERE NOT EXISTS (SELECT stit_id FROM finascop_stock_branch_inventory WHERE branch_id = @BranchId); 
 SELECT i.stit_id, i.stit_sku, bi.item_count, bi.mrp, bi.selling_price, bi.discount_selling_price FROM finascop_stock_itemmaster i
LEFT JOIN finascop_stock_branch_inventory bi ON i.stit_id=bi.stit_id AND bi.branch_id=@BranchId
WHERE i.stit_id IN(SELECT DISTINCT stit_id FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storegroup=@storeId) " >
        <SelectParameters>
            <asp:Parameter Name="BranchId" Type="Int32" DefaultValue="-1" ConvertEmptyStringToNull="false" />
            <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
        </SelectParameters>
    </asp:SqlDataSource>

    <asp:SqlDataSource runat="server" ID="SDSBulkImport" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT DISTINCT iu.fbiu_id, fbiu_branch, totalCount, missedCount, successCount, DATE_FORMAT(iu.fbiu_updatedOn, '%d %b %Y') AS updatedDate,
                                                  TIME(iu.fbiu_updatedOn) AS updatedTime, CONCAT(DATE_FORMAT(fbiu_updatedOn, '%d %b %Y'), ' ', TIME(fbiu_updatedOn)) AS updatedDateTime, filename FROM finascop_stock_branch_inventory_upload iu
                                                  LEFT JOIN finascop_stock_branch_inventory_upload_detail ud ON iu.fbiu_id=ud.fbiu_id
                                                  WHERE fbiu_branch=@branchid AND fbiu_uploadedbyapi <> 1 ORDER BY fbiu_id DESC" OnSelecting="SDSBulkImport_Selecting">
                                    <SelectParameters>
                                        <asp:ControlParameter ControlID="selBranches" Name="branchid" />
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
                    <div class="section-wrapper p-0 border-0">
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