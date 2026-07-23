<%@ Page Language="C#"  MasterPageFile="~/Finance/FinanceMaster.master" Title="Voucher Entry"  AutoEventWireup="true" CodeBehind ="VoucherEntry.aspx.cs" Inherits="RetalineProAgent.Finance.VoucherEntry" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
      <a href="/Finance/DataEntry"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <script src="/content/js/custom/pdf.js"></script>
    <script src="/Content/customadmin/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <script src="../Content/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<%--    <script src="../Content/lib/jquery/js/jquery-ui.js"></script>
    <script src="../Content/lib/jquery/js/jquery.js"></script>--%>
    <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
   <%-- <link rel="stylesheet" href="/Content/css/custom/custom.css"> --%>   
      <link rel="stylesheet" href="/Content/customadmin/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/Content/customadmin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <script>
        function handleImageError(QRImgPreview, QRPdfPreview) {
            let pdfUrl = $(QRImgPreview).attr('src');
            if (pdfUrl == "") {
                return;
            }
            pdfjsLib.getDocument(pdfUrl).promise.then(function (pdf) {
                var newDiv = $("<div></div>");
                $(QRPdfPreview).empty().append(newDiv);
                console.log("the pdf has", pdf.numPages, "page(s).");
                for (var i = 0; i < pdf.numPages; i++) {
                    (function (pageNum) {
                        pdf.getPage(i + 1).then(function (page) {
                            // you can now use *page* here
                            var viewport = page.getViewport(2.0);
                            var pageNumDiv = document.createElement("div");
                            pageNumDiv.className = "pageNumber";
                            pageNumDiv.innerHTML = "Page " + pageNum;
                            var canvas = document.createElement("canvas");
                            canvas.className = "page";
                            canvas.title = "Page " + pageNum;
                            $(QRPdfPreview).append(pageNumDiv);
                            $(QRPdfPreview).append(canvas);
                            $(QRPdfPreview).show();
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;


                            page.render({
                                canvasContext: canvas.getContext('2d'),
                                viewport: viewport
                            }).promise.then(function () {
                                console.log('Page rendered');
                            });
                            page.getTextContent().then(function (text) {
                                console.log(text);
                            });
                        });
                    })(i + 1);
                }

            });
            $(QRImgPreview).hide();
        }
        function handleImageError0(event) {
            handleImageError($('#QRImgPreview0'), $('#QRPdfPreview0'));
        }
        function handleImageError1(event) {
            handleImageError($('#QRImgPreview1'), $('#QRPdfPreview1'));
        }
        function handleImageError2(event) {
            handleImageError($('#QRImgPreview2'), $('#QRPdfPreview2'));
        }
    </script>
  <script src="/Content/customadmin/plugins/select2/js/select2.min.js"></script>
     <h6 class="slim-pagetitle">Voucher Entry</h6>
    <p class="mb-0">Voucher Entry</p>

</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent"> 
        <div class="row row-sm">
            <div class="col-12 col-lg-5">
                <div class="card" style="height: calc(100% - 15px); width: 100%;">

                    <div class="card-body p-3 shadow_top" style="height: 100%; width: calc(100% - 15px);">
                        <div class="row row-sm">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="mb-0">Voucher Type</label>
                                    <asp:DropDownList ID="ddlEntryType" DataSourceID="SDSEntryTypes" CssClass="form-control" DataTextField="name" AutoPostBack="true"
                                        DataValueField="id" AppendDataBoundItems="true" OnSelectedIndexChanged="ddlEntryType_SelectedIndexChanged" runat="server">
                                        <asp:ListItem Text="Select Voucher Type" Value=""></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:Label ID="lblentry" runat="server"></asp:Label>
                                    <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select entry type" ValidationGroup="AddEntry" ControlToValidate="ddlEntryType" Display="Dynamic"></asp:RequiredFieldValidator>
                                    <asp:SqlDataSource ID="SDSEntryTypes" runat="server" SelectCommand="select id, name from [voucher_type]" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                </div>
                            </div>
                            <!--col-->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="VoucherDate" class="mb-0">Date</label>
                                    <asp:TextBox ID="txtVoucherDate" DataFormatString="{0:dd-MM-yyyy}" CssClass="form-control v_active" runat="server" TextMode="Date" />
                                </div>
                            </div>
                            <!--col-->

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-0">Entry Type</label>
                                    <asp:DropDownList ID="Ddlentertype" CssClass="form-control v_active" runat="server">
                                        <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                        <asp:ListItem Text="Debit" Value="1"></asp:ListItem>
                                        <asp:ListItem Text="Credit" Value="2"></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:Literal runat="server" ID="ltrselectdebit"></asp:Literal>
                                </div>
                            </div>
                            <!--col-->

                            <div class="col-md-9">
                                <div class="form-group">
                                    <label class="mb-0 w-100">Ledger</label>
                                    <asp:DropDownList ID="selLedger" DataSourceID="SDSLedgerTypes" CssClass="form-control select2 v_LedgerDropdown" DataTextField="name" OnSelectedIndexChanged="selLedger_SelectedIndexChanged"
                                        DataValueField="id" AppendDataBoundItems="true" runat="server">
                                        <asp:ListItem Text="Select Ledger" Value=""></asp:ListItem>
                                    </asp:DropDownList>
                                    <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Ledger" ValidationGroup="AddEntry" ControlToValidate="selLedger" Display="Dynamic"></asp:RequiredFieldValidator>
                                    <asp:Label ID="lblledgershow" runat="server"></asp:Label>
                                    <asp:SqlDataSource ID="SDSLedgerTypes" runat="server" SelectCommand="select 0 as id ,'Suspense Entry' as name union all select id, name from [ledger]" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                </div>
                            </div>
                            <!--col-->

                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label class="mb-0" id="txtAmounts">Amount</label>
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtAmount" CssClass="form-control v_active_two" runat="server" autocomplete="off" onchange="updateCreditField();"></asp:TextBox>
                                    <asp:RequiredFieldValidator runat="server" ValidationGroup="lbAddEntry" ControlToValidate="txtAmount" ForeColor="Red" ErrorMessage="Amount is required"></asp:RequiredFieldValidator>
                                    <%-- <input type="text" id="txtAmountd" class="form-control text-right"  placeholder="Enter Amount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                                </div>
                            </div>
                            <!--col-->
                            <div class="col-sm-7">
                                <div class="form-group">
                                    <label class="mb-0">Reference</label>
                                    <%--<input type="text" class="form-control v_active_two" placeholder="Reference" >--%>
                                    <asp:TextBox ID="txtreference" CssClass="form-control v_active_two" runat="server"></asp:TextBox>
                                    <asp:RequiredFieldValidator runat="server" ValidationGroup="lbAddEntry" ControlToValidate="txtreference" ForeColor="Red" ErrorMessage="Reference is required"></asp:RequiredFieldValidator>
                                </div>
                            </div>
                            <asp:PlaceHolder runat="server" ID="plccostcentre" Visible="false">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="mb-0">Cost Center</label>
                                        <asp:DropDownList ID="ddlcostcentre" DataSourceID="SDScostcentre" CssClass="form-control" DataTextField="name"
                                            DataValueField="id" AppendDataBoundItems="true" runat="server">
                                            <asp:ListItem Text="Select Cost Centre" Value=""></asp:ListItem>
                                        </asp:DropDownList>
                                        <asp:SqlDataSource ID="SDScostcentre" runat="server" SelectCommand="select id, name from cost_centre" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="mb-0">Amount</label>
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:TextBox ID="txtcostamount" CssClass="form-control" runat="server" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                        <%--                          <input name="CostAmount" type="text" id="CostAmount" class="form-control" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                                        <asp:CompareValidator runat="server" ID="Numbers" ValidationGroup="AddEntry" Display="Dynamic" ControlToValidate="txtcostamount" ControlToCompare="txtAmount" Operator="LessThanEqual" Type="Integer" ErrorMessage=" The cost amount less than Ledgeramount" />
                                        <asp:RequiredFieldValidator runat="server" ValidationGroup="AddEntry" Display="Dynamic" ControlToValidate="txtcostamount" ErrorMessage="Amount is required"></asp:RequiredFieldValidator>
                                    </div>
                                </div>
                                <div class="col-sm-3 d-flex align-items-start">
                                    <%--                        <button id="AddCostCenterBTN" class="btn btn-info w-100 AddCostCenterBTN" style="margin-bottom: 1rem;"><i class="fa fa-plus mr-2" aria-hidden="true"></i>Add</button>--%>
                                    <asp:LinkButton runat="server" ID="lbncostcentre" ValidationGroup="AddEntry" OnClick="lbncostcentre_Click" CssClass="btn btn-info w-100 AddCostCenterBTN" Style="margin-top: 1.3rem;"><i class="fa fa-plus mr-2" aria-hidden="true"></i>Add</asp:LinkButton>
                                </div>
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table id="tblcostcentre" class="table table-bordered mb-3">
                                            <thead>
                                                <tr class="TableHeader">
                                                    <th>Cost Centre</th>
                                                    <th width="125" align="right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <asp:ListView ID="lvcostcentre" runat="server">
                                                    <ItemTemplate>
                                                        <tr>
                                                            <td><%# Eval("CostCentreName")%></td>
                                                            <td align="right"><%# Eval("CostAmount")%></td>
                                                        </tr>
                                                    </ItemTemplate>
                                                </asp:ListView>
                                            </tbody>

                                        </table>

                                    </div>
                                </div>
                            </asp:PlaceHolder>
                            <div class="col-sm-12">
                                <div class="form-group text-right">
                                    <label class="mb-0"></label>
                                    <asp:LinkButton runat="server" ID="lbAddEntry" OnClick="lbAddEntry_Click" ValidationGroup="lbAddEntry" CssClass="btn btn-primary AddVoucherBTN"><i class="fa fa-plus mr-2"></i>Add Entry</asp:LinkButton>
                                    <%--<a id="AddVoucher" class="btn btn-primary  AddVoucherBTN" href="javscript:void(0)">
                            <i class="fas fa-plus  mr-2"></i>Add Entry
                             
                          </a>--%>
                                    <div class="form-group">
                                        <asp:Label ID="lblError" CssClass="highlight" runat="server" Text=""></asp:Label>
                                    </div>
                                </div>
                            </div>
                            <!--col-->

                        </div>
                        <!--row -->

                        <hr></hr>

                        <%-- <script>
                          $('.v_LedgerDropdown').prop('disabled', true);
                      </script>--%>

                        <div class="row">
                            <div class="col-12">
                                <h6>Upload files</h6>
                                <div class="upload_qrqcode_wrap">
                                    <div class="upload_btnicon" data-toggle="modal" data-target="#DocumentUploadpopup">
                                        <img src="/content/images/uplad_logo_icon.png">
                                    </div>
                                </div>
                               <asp:CustomValidator ID="FileUploadValidator" runat="server" 
                                    ErrorMessage="Please upload at least one file." 
                                    OnServerValidate="FileUploadValidator_ServerValidate" 
                                    Display="Dynamic" ForeColor="Red"
                                    ValidationGroup="SaveGroup" />
                            </div>
                            <!--col-->
                            <div class="col-12">
                                <ol class="upload_list p-0 m-0 mt-2 ml-3 mb-3">
                                    <asp:Repeater ID="Repterupload" OnItemCommand="Repterupload_ItemCommand" OnDataBinding="Repterupload_DataBinding" runat="server">
                                        <ItemTemplate>
                                            <li class="mb-1">
                                                <div class="d-flex align-items-center">
                                                    <a class="text-dark text-truncate d-inline-block mw-100" href="<%# Eval("DocumentURL")%>" download=""><%# Eval("DocumentName")%> </a>
                                                        <asp:LinkButton runat="server" CommandName="Delete" OnClientClick="return confirm('Are you sure you want to delete this file?');">
                                                            <i class="fa fa-trash text-danger ml-3 dlt_docmt" style="font-size: 20px;"></i>
                                                        </asp:LinkButton>
                                                </div>
                                            </li>
                                        </ItemTemplate>
                                    </asp:Repeater>
                            </div>
                            <!--col-->
                        </div>
                        <!--row-->

                        <!-- Modal -->
                        <div class="modal fade col-12" id="DocumentUploadpopup" data-backdrop="static" data-keyboard="false"
                            tabindex="-1" aria-labelledby="DocumentUploadpopupLabel" aria-hidden="true" style="height: 100%; width: 100%; overflow: visible;">
                            <div class="modal-dialog modal-dialog-centered modal-lg" style="height: calc(100% - 15px); width: calc(100% - 15px);">
                                <div class="modal-content doument-upload-dialog">

                                            <div class="modal-body">
                                                <div class="modaltitle ">
                                                    <h5 class="modal-title" id="sDocumentUploadpopupLabel">Document Upload</h5>
                                                    <asp:HiddenField ID="hfdBlobURL" Value="" runat="server"  />
                                                    <asp:HiddenField ID="hfdKey" Value="" runat="server"  />
                                                    <asp:HiddenField ID="folder" Value = "" runat="server" ClientIDMode="Static"  />
                                                    <asp:HiddenField ID="modalDialogShow" Value = "false" runat="server" ClientIDMode="Static"  /> 
                                                    <asp:HiddenField ID="hfdHasSuspenseAccount" Value = "" runat="server" ClientIDMode="Static"  />
                                                    <asp:HiddenField ID="hfdHasDocumentAttached" Value = "" runat="server" ClientIDMode="Static"  />

                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <div class="form-group">
                                                            <label class="mb-0">Document Name</label>
                                                            <asp:TextBox ID="txtdocname" runat="server" CssClass="form-control document-name"></asp:TextBox>
                                                        </div>
                                                    </div>
                                                    <!--col-->

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label class="mb-0">Document Type</label>
                                                            <asp:DropDownList ID="dltype" CssClass="form-control file_type" runat="server">
                                                                <asp:ListItem Text="PDF" Value="1"></asp:ListItem>
                                                                <asp:ListItem Text="JPEG/JPG/PNG" Value="2"></asp:ListItem>
                                                            </asp:DropDownList>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label class="mb-0" id="lblDUnarration">Narration</label>
                                                            <asp:TextBox ID="tbxDUNarration" CssClass="form-control" Style="height: 150px; max-width: 100%;" TextMode="MultiLine" Rows="5" runat="server"></asp:TextBox>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-8 d-flex flex-wrap flex-lg-nowrap">
                                                        <div ID="UploadFile0" class="Uploadbox enabled">
                                                            <div class="upload_qrqcode_wrap m-2 repeater_block-class" style="height: 150px; width: 150px;">
                                                                <asp:HiddenField ID="DocumentID0" Value = "DOC0" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="DocumentName0"  Value = "Proof Document 1" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="DocumentURL0" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="Narration0" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="blobFileURL0" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="blobFileName0" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <%--<asp:HiddenField ID="fileUploadURL0" Value = "" runat="server" ClientIDMode="Static"  />--%>
                                                                        <div id="docUpload_wap0" class="uploadfile_wrap d-flex align-items-center justify-content-between w-100 h-100" style="background-color: #ececec;">
                                                                            <div id="actions0" class="upload_btnicon m-1 upload_interface" >
                                                                                <div id="documentupload_input0" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100">
                                                                                    <a id="pdfUpload0" class="d-inline-block text-center w-100 addtext">
                                                                                        <img src="/content/images/loc_update.png">
                                                                                    </a>
                                                                                    <asp:FileUpload ID ="fupPdfFileUpload1" runat="server" class="fup_block-class position-absolute w-100 fup_pdf_upload" Style="opacity: 0; height: 38px;" accept="application/pdf" />
                                                                                </div>
        
                                                                                <div id="imageupload_input0" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100" style="display: none;">
                                                                                    <a id="imgUpload0" class="d-inline-block text-center w-100 addtext">
                                                                                        <img src="/content/images/loc_update.png">
                                                                                    </a>
                                                                                    <asp:FileUpload ID="fupImageUpload1" runat="server" class="fup_block-class position-absolute w-100 fup_img_upload" Style="opacity: 0; height: 38px;" onchange="UploadFile(this)" accept="image/x-png,image/jpeg,image/jpg" />
                                                                                </div>
                                                                            </div>
                                                            
                                                                            <div id="qrcode-section0" class="qrqcode_sec m-1"> 
                                                                                <img style="max-width: 55px;" src="/content/images/Qr_code.png">
                                                                            </div>
                                                                            <div id="QRImgPreview_wap0" class="qrimg_preview_block-class text-center align-items-center h-100  w-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                                <img id="QRImgPreview0" src="" class="" style="max-width: 100%;"">
                                                                                <div id="QRPdfPreview0" class="qrpdf_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                                                <%--<object id="QRPdfPreview0" class="qrpdf_preview_block-class" data="" type="application/pdf" style="width: 100%;"></object>--%>
                                                                            </div>                                                           
                                                                            <div id="docPreview_wap0" class="doc_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                                            <div id="ImgPreview_wap0" class="img_preview_block-class text-center w-100 h-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                                <img id="ImgPreview0" src="" class="preview_img" style="max-width: 100%; min-height: 100px;">
                                                                            </div>                                                           
                                                                        </div>
   
                                                                <div  class="qrqcode_btnicon" style="display: none;">
                                                                    <button id="close_btn0" type="button" class="btn-close close btn-link" aria-label="" style="width: 5px; height: 5px; border:0px">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>    
                                                                    <img id="imgUploadQrcode0" runat="server" ClientIDMode="Static" src ="" class="img-upload-qrcode-class" style="width: 80%; height: 80%; position: absolute; top: 10%; left: 7%;">
                                                                </div> 
                                                                <div class="remove_preview_wrap">
                                                                     <asp:LinkButton ID="lbnBlobDelete0" runat="server" OnClick="DeleteFile">
                                                                        <span><i class="icon ion-trash-a"></i>Delete File</span>
                                                                     </asp:LinkButton>
                                                                </div>
                                                            </div><!--upload_qrqcode_wrap-->
                                                        </div><!--Uploadbox-->
                                                        <div ID="UploadFile1" class="Uploadbox disabled">
                                                            <div class="upload_qrqcode_wrap m-2 repeater_block-class" style="height: 150px; width: 150px;">
                                                                <asp:HiddenField ID="DocumentID1" Value = "DOC1" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="DocumentName1"  Value = "Proof Document 2" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="DocumentURL1" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="Narration1" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="blobFileURL1" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="blobFileName1" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <div id="docUpload_wap1" class="uploadfile_wrap d-flex align-items-center justify-content-between w-100 h-100" style="background-color: #ececec;">
                                                                    <div id="actions1" class="upload_btnicon m-1 upload_interface" >
                                                                        <div id="documentupload_input1" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100">
                                                                            <a id="pdfUpload1" class="d-inline-block text-center w-100 addtext">
                                                                                <img src="/content/images/loc_update.png">
                                                                            </a>
                                                                            <asp:FileUpload ID ="fupPdfFileUpload2" runat="server" class="fup_block-class position-absolute w-100 fup_pdf_upload" Style="opacity: 0; height: 38px;" accept="application/pdf" />
                                                                        </div>
        
                                                                        <div id="imageupload_input1" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100" style="display: none;">
                                                                            <a id="imgUpload1" class="d-inline-block text-center w-100 addtext">
                                                                                <img src="/content/images/loc_update.png">
                                                                            </a>
                                                                            <asp:FileUpload ID="fupImageUpload2" runat="server" class="fup_block-class position-absolute w-100 fup_img_upload" Style="opacity: 0; height: 38px;" onchange="UploadFile(this)" accept="image/x-png,image/jpeg,image/jpg" />
                                                                        </div>
                                                                    </div>                                                            .                                                            
                                                                    <div id="qrcode-section1" class="qrqcode_sec m-1"> 
                                                                        <img style="max-width: 55px;" src="/content/images/Qr_code.png">
                                                                    </div>
                                                                    <div id="QRImgPreview_wap1" class="qrimg_preview_block-class text-center align-items-center h-100  w-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                        <img id="QRImgPreview1" src="" class="" style="max-width: 100%;">
                                                                        <div id="QRPdfPreview1" class="qrpdf_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                                    </div>                                                            
                                                                    <div id="docPreview_wap1" class="doc_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                                    <div id="ImgPreview_wap1" class="img_preview_block-class text-center w-100 h-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                        <img id="ImgPreview1" src="" class="preview_img" style="max-width: 100%; min-height: 100px;">
                                                                    </div>                                                           
                                                                </div>
                                                        
    
                                                                <div  class="qrqcode_btnicon" style="display: none;">
                                                                    <button id="close_btn1" type="button" class="btn-close close btn-link" aria-label="" style="width: 5px; height: 5px; border:0px">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>    
                                                                    <img id="imgUploadQrcode1" src =""  runat="server" ClientIDMode="Static"  class="img-upload-qrcode-class" style="width: 80%; height: 80%; position: absolute; top: 10%; left: 7%;">
                                                                </div> 
                                                                <div class="remove_preview_wrap">
                                                                    <asp:LinkButton ID="lbnBlobDelete1" runat="server" OnClick="DeleteFile">
                                                                        <span><i class="icon ion-trash-a"></i>Delete File</span>
                                                                     </asp:LinkButton>
                                                                </div>

                                                            </div><!--upload_qrqcode_wrap-->
                                                        </div><!--Uploadbox-->
                                                        <div ID="UploadFile2" class="Uploadbox disabled">
                                                            <div class="upload_qrqcode_wrap m-2 repeater_block-class" style="height: 150px; width: 150px;">
                                                                <asp:HiddenField ID="DocumentID2" Value = "DOC2" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="DocumentName2" Value = "Proof Document 3" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="DocumentURL2" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="Narration2" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="blobFileURL2" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <asp:HiddenField ID="blobFileName2" Value = "" runat="server" ClientIDMode="Static"  />
                                                                <div id="docUpload_wap2" class="uploadfile_wrap d-flex align-items-center justify-content-between w-100 h-100" style="background-color: #ececec;">
                                                                    <div id="actions2" class="upload_btnicon m-1 upload_interface" >
                                                                        <div id="documentupload_input2" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100">
                                                                            <a id="pdfUpload2" class="d-inline-block text-center w-100 addtext">
                                                                                <img src="/content/images/loc_update.png">
                                                                            </a>
                                                                            <asp:FileUpload ID ="fupPdfFileUpload3" runat="server" class="fup_block-class position-absolute w-100 fup_pdf_upload" Style="opacity: 0; height: 38px;" accept="application/pdf" />
                                                                        </div>
        
                                                                        <div id="imageupload_input2" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100" style="display: none;">
                                                                            <a id="imgUpload2" class="d-inline-block text-center w-100 addtext">
                                                                                <img src="/content/images/loc_update.png">
                                                                            </a>
                                                                            <asp:FileUpload ID="fupImageUpload3" runat="server" class="fup_block-class position-absolute w-100 fup_img_upload" Style="opacity: 0; height: 38px;" onchange="UploadFile(this)" accept="image/x-png,image/jpeg,image/jpg" />
                                                                        </div>
                                                                    </div>
                                                            
                                                                    <div id="qrcode-section2" class="qrqcode_sec m-1"> 
                                                                        <img style="max-width: 55px;" src="/content/images/Qr_code.png">
                                                                    </div>
                                                                    <div id="QRImgPreview_wap2" class="qrimg_preview_block-class text-center align-items-center h-100  w-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                        <img id="QRImgPreview2" src="" class="" style="max-width: 100%;">
                                                                        <div id="QRPdfPreview2" class="qrpdf_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                                    </div>                                                            
                                                                    <div id="docPreview_wap2" class="doc_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                                    <div id="ImgPreview_wap2" class="img_preview_block-class text-center w-100 h-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                        <img id="ImgPreview2" src="" class="preview_img" style="max-width: 100%; min-height: 100px;">
                                                                    </div>                                                           
                                                                </div>
                                                        
    
                                                                <div  class="qrqcode_btnicon" style="display: none;">
                                                                    <button id="close_btn2" type="button" class="btn-close close btn-link" aria-label="" style="width: 5px; height: 5px; border:0px">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>    
                                                                    <img id="imgUploadQrcode2"  runat="server" ClientIDMode="Static"  src ="" class="img-upload-qrcode-class" style="width: 80%; height: 80%; position: absolute; top: 10%; left: 7%;">
                                                                </div> 
                                                                <div class="remove_preview_wrap">
                                                                    <asp:LinkButton ID="lbnBlobDelete2" runat="server" OnClick="DeleteFile">
                                                                        <span><i class="icon ion-trash-a"></i>Delete File</span>
                                                                     </asp:LinkButton>
                                                                </div>

                                                            </div><!--upload_qrqcode_wrap-->
                                                        </div><!--Uploadbox-->
                                                        <div class="col-12 col-lg-4 pl-lg-0 ">
                                                            <div class="d-flex align-items-end h-100">
                                                                <div class="modal-btn mb-2 ">
                                                                    <asp:Button ID="btnClose" CssClass="btn_same btn btn-secondary mr-2" runat="server" data-dismiss="modal" Text="Close" />
                                                                    <asp:Button ID="btnupload" CssClass="btn_same btn btn-primary" runat="server" OnClick="btnupload_Click" Text="Upload" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div style ="margin: 7px;">
                                                    </div>
                                                <!--row-->
                                                </div>

                                                </div>
                                     <!--modal-body-->
                                </div>
                                <!--modal-content-->
                            </div>
                            <!--modal-dialog-->
                        </div>
                        <!--modal-->
                    </div>
                    <!--card-body -->
                </div>
                <!--card-->
            </div>
            <!--col-lg-6-->
            <script>
                let $current_upload_qrqcode_wrap = null;

                $('#QRImgPreview0').on("error", handleImageError0);
                $('#QRImgPreview1').on("error", handleImageError1);
                $('#QRImgPreview2').on("error", handleImageError2);

                $(document).ready(function () {
                    $(".remove_preview_wrap").click(function () {
                        $upload_qrqcode_wrap = $(this).closest('.upload_qrqcode_wrap');
                        //DocumentURL = null;
                        //hfdDocumentURL = null;
                        //if ($upload_qrqcode_wrap.find('#DocumentURL0').length != 0) {
                        //    hfdDocumentURL = $upload_qrqcode_wrap.find('#DocumentURL0');
                        //    DocumentURL = hfdDocumentURL.val();
                        //}
                        //if ($upload_qrqcode_wrap.find('#DocumentURL1').length != 0) {
                        //    hfdDocumentURL = $upload_qrqcode_wrap.find('#DocumentURL1');
                        //    DocumentURL = hfdDocumentURL.val();
                        //}
                        //if ($upload_qrqcode_wrap.find('#DocumentURL2').length != 0) {
                        //    hfdDocumentURL = $upload_qrqcode_wrap.find('#DocumentURL2');
                        //    DocumentURL = hfdDocumentURL.val();
                        //}
                        //if (DocumentURL != null) {
                        //    deleteBlobFile(DocumentURL, hfdDocumentURL);
                        //}
                        var fileInput = $upload_qrqcode_wrap.find(".fup_block-class");
                        fileInput.val('');
                        $upload_qrqcode_wrap.find('.doc_preview_block-class').html('');
                        $upload_qrqcode_wrap.find('.img_preview_block-class  img').attr("src", "");
                        $upload_qrqcode_wrap.find('.doc_preview_block-class').hide();
                        $upload_qrqcode_wrap.find('.img_preview_block-class').hide();
                        $upload_qrqcode_wrap.find(".qrimg_preview_block-class").hide();
                        $upload_qrqcode_wrap.find('.uploadfile_wrap').addClass('w-100');
                        $upload_qrqcode_wrap.find('.text-center').addClass('w-100');

                        $upload_qrqcode_wrap.find('.qrqcode_sec').show();
                        $upload_qrqcode_wrap.find('.upload_btnicon').show();
                    });
                });

                $(document).ready(function () {
                    $(".repeater_block-class").on("click", function () {
                        $(this).closest(".repeater_block-class").addClass("repeater-item-focus");
                    });
                });
                $(document).ready(function () {

                    if ($('#folder').val() == "") {
                        $('#folder').val(folder);
                    } else {
                        folder = $('#folder').val();
                    }
                    var blobFileURL = "";
                    //if ($('#blobFileURL0').val() == "") {
                    //    blobFileURL = generateFileUrl($('#UploadFile0').find('.qrqcode_btnicon'));
                    //    $('#blobFileURL0').val(blobFileURL);
                    //}
                    var fileuploadurl = cururl;
                    var filename = $('#blobFileName0').val();
                    /*if (!filename || filename.trim() === "") {
                        __doPostBack('documentReady', 'AllHiddenFieldsUpdated');
                    }*/
                    fileuploadurl += '/Finance/UploadFile?key=' + folder + '&file=' + filename;
                    console.log(fileuploadurl);
                    console.log($('#blobFileURL0').val());
                    //fileuploadurl = encodeURIComponent(fileuploadurl);
                    //$('#imgUploadQrcode0').attr('src', 'https://chart.googleapis.com/chart?chs=350x350&cht=qr&chl=' + fileuploadurl);
                    //$('#imgUploadQrcode0').attr('src', 'https://qrcode.tec-it.com/API/QRCode?data=' + fileuploadurl + '&dim=325');
                    //if ($('#blobFileURL1').val() == "") {
                    //    blobFileURL = generateFileUrl($('#UploadFile1').find('.qrqcode_btnicon'));
                    //    $('#blobFileURL1').val(blobFileURL);
                    //}
                    fileuploadurl = cururl;
                    filename = $('#blobFileName1').val();
                    fileuploadurl += '/Finance/UploadFile?key=' + folder + '&file=' + filename;
                    console.log(fileuploadurl);
                    console.log($('#blobFileURL1').val());
                    fileuploadurl = encodeURIComponent(fileuploadurl);
                    //$('#imgUploadQrcode1').attr('src', 'https://qrcode.tec-it.com/API/QRCode?data=' + fileuploadurl + '&dim=325');

                    //if ($('#blobFileURL2').val() == "") {
                    //    blobFileURL = generateFileUrl($('#UploadFile2').find('.qrqcode_btnicon'));
                    //    $('#blobFileURL2').val(blobFileURL);
                    //}
                    fileuploadurl = cururl;
                    filename = $('#blobFileName2').val();
                    fileuploadurl += '/Finance/UploadFile?key=' + folder + '&file=' + filename;
                    console.log(fileuploadurl);
                    console.log($('#blobFileURL2').val());
                    fileuploadurl = encodeURIComponent(fileuploadurl);
                    //$('#imgUploadQrcode2').attr('src', 'https://qrcode.tec-it.com/API/QRCode?data=' + fileuploadurl + '&dim=325');

                });

                function UploadFile(input) {
                    if (input.files && input.files[0]) {
                        var $uploadWrap = $(input).closest('.upload_qrqcode_wrap');
                        $uploadWrap.find('.uploadfile_wrap').removeClass('w-100');
                        $uploadWrap.find('.text-center').removeClass('w-100');
                        $uploadWrap.find('.upload_btnicon').hide();
                        $uploadWrap.find('.qrqcode_sec').hide();

                        var reader = new FileReader();

                        reader.onload = function (e) {
                            $uploadWrap.find('.preview_img').attr('src', e.target.result);
                            $uploadWrap.find('.text-center').show();
                        }

                        reader.readAsDataURL(input.files[0]);
                        var nextTargetDiv = $uploadWrap.closest('.Uploadbox').first().nextAll(".Uploadbox").first();
                        nextTargetDiv.removeClass('Uploadbox disabled');
                        nextTargetDiv.addClass('Uploadbox enabled');
                    }
                };
                $(".upload_qrqcode_wrap").click(function (e) {
                    if (ftimer != null) {
                        clearInterval(ftimer);
                        ftimer = null;
                    }
                    var repeaterItems = $(".upload_qrqcode_wrap");
                    repeaterItems.each(function (index, item) {
                        if (!$(item).is(e.target.closest(".repeater_block-class"))) {
                            $(item).removeClass("repeater-item-focus");
                            $qr_code_icon = $(item).closest('.upload_qrqcode_wrap').find('.qrqcode_btnicon');
                            $qr_code_icon.hide();
                            $qr_code_icon.removeClass("repeater-item-focus");
                        } else {
                            $current_upload_qrqcode_wrap = $(item);
                            var DocumentName = "";

                            if ($(item).find('#DocumentName0').length != 0) { DocumentName = $(item).find('#DocumentName0').val(); }
                            if ($(item).find('#DocumentName1').length != 0) { DocumentName = $(item).find('#DocumentName1').val(); }
                            if ($(item).find('#DocumentName2').length != 0) { DocumentName = $(item).find('#DocumentName2').val(); }
                            $('#<%=txtdocname.ClientID %>').val(DocumentName);

                            var Narration = "";
                            if ($(item).find('#Narration0').length != 0) { Narration = $(item).find('#Narration0').val(); }
                            if ($(item).find('#Narration1').length != 0) { Narration = $(item).find('#Narration1').val(); }
                            if ($(item).find('#Narration2').length != 0) { Narration = $(item).find('#Narration2').val(); }
                            $('#<%=tbxDUNarration.ClientID %>').val(Narration);

                        }
                    });
                });

                $('#<%=txtdocname.ClientID %>').click(function (e) {
                    $(this).select();
                });

                $('#<%=txtdocname.ClientID %>').blur(function () {
                    var DocumentName = $(this).val();
                    if ($current_upload_qrqcode_wrap.find('#DocumentName0').length != 0) { $current_upload_qrqcode_wrap.find('#DocumentName0').val(DocumentName); }
                    if ($current_upload_qrqcode_wrap.find('#DocumentName1').length != 0) { $current_upload_qrqcode_wrap.find('#DocumentName1').val(DocumentName); }
                    if ($current_upload_qrqcode_wrap.find('#DocumentName2').length != 0) { $current_upload_qrqcode_wrap.find('#DocumentName2').val(DocumentName); }
                });
                $('#<%=tbxDUNarration.ClientID %>').blur(function () {
                    var Narration = $(this).val();
                    if ($current_upload_qrqcode_wrap.find('#Narration0').length != 0) { $current_upload_qrqcode_wrap.find('#Narration0').val(Narration); }
                    if ($current_upload_qrqcode_wrap.find('#Narration1').length != 0) { $current_upload_qrqcode_wrap.find('#Narration1').val(Narration); }
                    if ($current_upload_qrqcode_wrap.find('#Narration2').length != 0) { $current_upload_qrqcode_wrap.find('#Narration2').val(Narration); }
                });


                $(".qrqcode_sec").click(function (e) {
                    if (ftimer != null) {
                        clearInterval(ftimer);
                        ftimer = null;
                    }
                    $upload_qrqcode_wrap = $(this).closest('.upload_qrqcode_wrap');
                    $upload_qrqcode_wrap.find('.qrqcode_btnicon').show();
                    $upload_qrqcode_wrap.find('.qrqcode_btnicon').addClass("repeater-item-focus");

                    var blobFileURL = "";
                    if ($upload_qrqcode_wrap.find('#blobFileURL0').length != 0) { blobFileURL = $upload_qrqcode_wrap.find('#blobFileURL0').val(); }
                    if ($upload_qrqcode_wrap.find('#blobFileURL1').length != 0) { blobFileURL = $upload_qrqcode_wrap.find('#blobFileURL1').val(); }
                    if ($upload_qrqcode_wrap.find('#blobFileURL2').length != 0) { blobFileURL = $upload_qrqcode_wrap.find('#blobFileURL2').val(); }
                    ftimer = setInterval(loadFile(blobFileURL, $(this).closest('.upload_qrqcode_wrap')), 10 * 1000);
                });

                $(".btn-close").click(function (e) {
                    $qr_code_icon = $(this).closest('.upload_qrqcode_wrap').find('.qrqcode_btnicon');
                    $qr_code_icon.hide();
                    $qr_code_icon.removeClass("repeater-item-focus");
                    //$qr_code_icon.find('#imgUploadQrcode').attr('src', '');
                    if (ftimer != null) {
                        clearInterval(ftimer);
                        ftimer = null;
                    }
                });

                function fupPdfFileUploadChange() {
                    if (this.files.length > 0) {
                        var $uploadWrap = $(this).closest('.upload_qrqcode_wrap');

                        $uploadWrap.find('.uploadfile_wrap').removeClass('w-100');

                        $uploadWrap.find('.upload_btnicon').hide();
                        $uploadWrap.find('.qrqcode_sec').hide();

                        var nextTargetDiv = $uploadWrap.closest('.Uploadbox').first().nextAll(".Uploadbox").first();
                        nextTargetDiv.removeClass('Uploadbox disabled');
                        nextTargetDiv.addClass('Uploadbox enabled');
                    }
                }

                $('#UploadFile0').find('.fup_pdf_upload').change(fupPdfFileUploadChange);
                $('#UploadFile1').find('.fup_pdf_upload').change(fupPdfFileUploadChange);
                $('#UploadFile2').find('.fup_pdf_upload').change(fupPdfFileUploadChange);
            </script>
            <style>
                .class-blobfileurl {
                }

                .doument-upload-dialog {
                }

                .document-name {
                }

                .fup_block-class {
                }

                .repeater-item-focus {
                    background-color: #c6def7; /* Change to the color you prefer */
                }

                .repeater_block-class {
                }

                .doc_preview_block-class {
                    display: block;
                }

                .btn-link:hover {
                    opacity: 1;
                }

                .btn-close {
                    box-sizing: border-box;
                    padding: 0em 0em;
                    color: #000;
                    border: 0;
                    border-radius: 0rem;
                    opacity: 0.2;
                }

                .btn_same {
                    width: 70px;
                    margin-top: 10px;
                }

                .remove_preview_wrap {
                    bottom: -25px;
                    right: 30px;
                }

                .qrqcode_btnicon {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    left: 0;
                    padding: 5px;
                    border-radius: 10px;
                }

                .modal-btn {
                    text-align: left;
                }

                .Uploadbox.disabled {
                    pointer-events: none;
                    opacity: 0.4;
                }

                .Uploadbox.enabled {
                    pointer-events: auto;
                    opacity: 1.0;
                }
            </style>

            <script type="text/javascript">

                $(function () {

                    $(".file_type").on('change', function () {
                        var selectedOption = $(this).val();

                        if (selectedOption == "1") {
                            $('#UploadFile0').find('#documentupload_input0').show();
                            $('#UploadFile0').find('#imageupload_input0').hide();
                            $('#UploadFile1').find('#documentupload_input1').show();
                            $('#UploadFile1').find('#imageupload_input1').hide();
                            $('#UploadFile2').find('#documentupload_input2').show();
                            $('#UploadFile2').find('#imageupload_input2').hide();
                        } else {
                            $('#UploadFile0').find('#documentupload_input0').hide();
                            $('#UploadFile0').find('#imageupload_input0').show();
                            $('#UploadFile1').find('#documentupload_input1').hide();
                            $('#UploadFile1').find('#imageupload_input1').show();
                            $('#UploadFile2').find('#documentupload_input2').hide();
                            $('#UploadFile2').find('#imageupload_input2').show();

                        }

                    });
                });



                $(function () {

                    $('.dlt_docmt').click(function () {
                        $(this).closest('li').remove(); //hide();
                    });

                    $('.objdiv').click(function () {
                        $(this).closest('div').addClass('processing_loader');
                        setTimeout(function () {
                            $('.objdiv').removeClass('processing_loader');
                        }, 7000);
                    });


                });

            </script>

            <style>
                body {
                    overflow-x: hidden;
                }

                .table.table-head-fixed thead tr:nth-child(1) th {
                }

                .table.table-head-fixed tfoot tr:nth-child(1) th {
                    position: sticky;
                    bottom: 0;
                    z-index: 10;
                    border-top: 0;
                    box-shadow: inset 0 1px 0 #dee2e6,inset 0 -1px 0 #dee2e6;
                }

                @keyframes placeHolderShimmer {
                    0% {
                        background-position: -800px 0
                    }

                    100% {
                        background-position: 800px 0
                    }
                }

                .wireframe {
                    height: 8px;
                    width: 100%;
                    max-width: 75%;
                    background: #e8e8e8;
                    border-radius: 10px;
                    margin-top: 5px;
                    animation-duration: 2s;
                    animation-fill-mode: forwards;
                    animation-iteration-count: infinite;
                    animation-name: placeHolderShimmer;
                    animation-timing-function: linear;
                    background-color: #f6f7f8;
                    background: linear-gradient(to right, #eee 8%, #e4e4e4 18%, #eee 33%);
                    background-size: 800px 104px;
                }
            </style>
            <div class="col-12 col-lg-7">
                <div class="card" style="height: calc(100% - 15px);">
                    <div class="card-body shadow_top">
                        <div class="row row-sm">
                            <div class="col-12">
                                <asp:Panel ID="Panel1" runat="server" CssClass="right-panel">
                                    <div class="table-responsive p-0 mb-3" id="divLedger" style="max-height: 400px;">                                      
                                        <table id="Table1" class="table table-bordered table-head-fixed border-top mb-0">
                                            <thead>
                                                <tr class="TableHeader">
                                                    <th>Head of Account</th>
                                                    <th align="right" width="125">Debit</th>
                                                    <th align="right" width="125">Credit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <asp:Panel runat="server" Visible="true" CssClass="w-100 d-none" ID="ShowDiv">
                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                        <td align="right"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                        <td align="right"></td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                        <td align="right"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="wireframe"></div>
                                                        </td>
                                                        <td align="right"></td>
                                                        <td align="right"></td>
                                                    </tr>
                                                </asp:Panel>
                                                <asp:ListView ID="lvVoucherEntry" OnDataBound="lvVoucherEntry_DataBound" runat="server" DataKeyNames="ledgerId" OnItemDataBound="lvVoucherEntry_ItemDataBound"
                                                    OnItemCommand="lvVoucherEntry_ItemCommand" OnItemCanceling="lvVoucherEntry_ItemCanceling" OnItemUpdating="lvVoucherEntry_ItemUpdating"
                                                    OnItemEditing="lvVoucherEntry_ItemEditing" ItemPlaceholderID="plcItems" OnItemDeleting="lvVoucherEntry_ItemDeleting">
                                                    <LayoutTemplate>
                                                        <asp:PlaceHolder ID="plcItems" runat="server"></asp:PlaceHolder>
                                                    </LayoutTemplate>
                                                    <ItemTemplate>
                                                        <tr class="TableData">
                                                            <td>
                                                                <span class="w-100">
                                                                    <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("particulars")%>'>   
                                                                    </asp:Label>
                                                                </span>
                                                                <asp:LinkButton runat="server" CommandName="Delete" OnClientClick="return confirm('Are you sure you want to delete this record?');"><i class="fa fa-trash-o text-danger ml-2"></i></asp:LinkButton>
                                                                <asp:LinkButton runat="server" CssClass="ml-1" CommandName="Edit">Edit</asp:LinkButton>                                                                                            
                                                            </td>
                                                            <td align="right">
                                                                <asp:Label ID="lbDebit" runat="server" Text='<%# ((double)Eval("debit") <= 0) ? Eval("debit","{0:0.00}") : Eval("debit","{0:n}") %>'>
                                                                </asp:Label>
                                                            </td>
                                                            <td align="right">
                                                                <asp:Label ID="lbCredit" runat="server" Text='<%# ((double)Eval("credit") <= 0) ? Eval("credit","{0:0.00}") : Eval("credit","{0:n}") %>'>
                                                                </asp:Label>
                                                            </td>
                                                        </tr>
                                                    </ItemTemplate>
                                                    <EditItemTemplate>
                                                        <tr class="">
                                                            <td colspan="3">
                                                                <div class="row row-sm">
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label class="mb-0">Entry Type</label>
                                                                            <asp:DropDownList ID="dlentrytpeupdae" CssClass="form-control" SelectedValue='<%# Convert.ToBoolean(Eval("IsDebit")) ? "1" : "2" %>' runat="server">
                                                                                <asp:ListItem Enabled="true" Text="Enter type" Value="-1"></asp:ListItem>
                                                                                <asp:ListItem Text="Debit" Value="1"></asp:ListItem>
                                                                                <asp:ListItem Text="Credit" Value="2"></asp:ListItem>
                                                                            </asp:DropDownList>
                                                                            <asp:RangeValidator MinimumValue="1" MaximumValue="2" runat="server" ForeColor="Red" ControlToValidate="dlentrytpeupdae" ErrorMessage="Please select a valid entry type" ValidationGroup="EditVoucher"></asp:RangeValidator>
                                                                        </div>
                                                                    </div>
                                                                    <!--col-->
                                                                    <div class="col-7">
                                                                        <div class="form-group">
                                                                            <label class="mb-0 w-100">Ledger</label>
                                                                            <asp:DropDownList ID="selLedger2" DataSourceID="SDSLedgerTypes" CssClass="form-control select2" DataTextField="name" AutoPostBack="false" Selected="True" DataValueField="id" AppendDataBoundItems="true" runat="server" SelectedValue='<%# Eval("ledgerId") %>'
                                                                                OnSelectedIndexChanged="selLedger2_SelectedIndexChanged">
                                                                                <asp:ListItem Text="Select Ledger" Value=""></asp:ListItem>
                                                                            </asp:DropDownList>
                                                                            <asp:RequiredFieldValidator runat="server" CssClass="highlight" ErrorMessage="Please select Ledger" ValidationGroup="AddEntry" ControlToValidate="selLedger2"></asp:RequiredFieldValidator>
                                                                        </div>
                                                                    </div>
                                                                    <!--col-->
                                                                    <div class="col-sm-3">
                                                                        <div class="form-group">
                                                                            <label class="mb-0">Amount</label>
                                                                            <asp:TextBox ID="txtamoundup" CssClass="form-control" Text='<%# Convert.ToBoolean(Eval("IsDebit")) ?  String.Format("{0:n}", Eval("debit") ?? 0) : String.Format("{0:n}", Eval("credit") ?? 0) %>' runat="server" onchange="updateCreditField();"></asp:TextBox>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-9">
                                                                        <asp:Repeater ID="rptEditCostCenter" runat="server">
                                                                            <ItemTemplate>
                                                                                <div class="col-12">
                                                                                    <div class="row row-sm">
                                                                                        <div class="col-sm-8">
                                                                                            <div class="form-group">
                                                                                                <label class="mb-0">Cost Center</label>
                                                                                                <asp:DropDownList ID="ddlcostcentreup" DataSourceID="SDScostcentreup" CssClass="form-control" DataTextField="name"
                                                                                                    DataValueField="id" AppendDataBoundItems="true" SelectedValue='<%# Eval("CostCentreId") %>' runat="server">
                                                                                                    <asp:ListItem Text="Select Cost Centre" Value=""></asp:ListItem>
                                                                                                </asp:DropDownList>
                                                                                                <asp:SqlDataSource ID="SDScostcentreup" runat="server" SelectCommand="select id, name from cost_centre" ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"></asp:SqlDataSource>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-4">
                                                                                            <div class="form-group">
                                                                                                <label class="mb-0">Amount</label>
                                                                                                <input type="text" style="display: none" />
                                                                                                <input type="password" style="display: none" />
                                                                                                <asp:TextBox ID="txtcostamountup" CssClass="form-control" runat="server" Text='<%# Eval("CostAmount") %>' autocomplete="off" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </ItemTemplate>
                                                                        </asp:Repeater>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <asp:LinkButton runat="server" CssClass="ml-1 mr-1 btn btn-danger py-1" CommandName="Cancel">Cancel</asp:LinkButton>
                                                                        <asp:LinkButton runat="server" CssClass="ml-1 ml-1 btn btn-primary py-1" ValidationGroup="EditVoucher" CommandName="Update">Save</asp:LinkButton>                                                                       
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </EditItemTemplate>
                                                    <EmptyDataTemplate>
                                                    </EmptyDataTemplate>
                                                </asp:ListView>
                                            </tbody>
                                            <tfoot>
                                                <tr id="tot" runat="server">
                                                    <th style="text-align: right;" align="right" id="thtot" runat="server">
                                                        <asp:Literal ID="Literal1" runat="server"> Total:</asp:Literal></th>
                                                    <th style="text-align: right;" align="right" id="thDr" runat="server">
                                                        <asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></th>
                                                    <th style="text-align: right;" align="right" id="thCr" runat="server">
                                                        <asp:Literal ID="ltrCrTotal" runat="server"></asp:Literal></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <!--table-responsive-->
                                </asp:Panel>
                            </div>
                            <!--col-12-->
                            <div class="col-12 px-4">
                                <div class="form-group">
                                    <label class="mb-0" id="lblnarration">Narration</label>
                                    <asp:TextBox ID="txtNarration" CssClass="form-control" Style="height: 150px; max-width: 100%;" TextMode="MultiLine" Rows="5" runat="server"></asp:TextBox>
                                    <asp:RegularExpressionValidator ID="RegularExpressionValidator1" ControlToValidate="txtNarration" runat="server" CssClass="highlight" ErrorMessage="" ValidationGroup="SaveGroup"></asp:RegularExpressionValidator>
                                </div>
                            </div>
                            <!--col-12-->
                            <div class="col-12 px-4">
                                <div class="form-group mb-2 float-right">
                                    <asp:Button ID="btnSave" runat="server" Enabled="false" CssClass="btn btn-primary mb-3 Voucher_entryBTN"  OnClick="btnsanve_Click" Text="Save" />
                                    <%--<button type="button" id="btnDetails"  class="btn btn-success mb-3 Voucher_entryBTN" onclick="btnsanve_Click">Save</button>--%>
                                    <%--                           <asp:Button ID="btnsanve" CssClass="btn btn-success mb-3 Voucher_entryBTN "  data-toggle="modal" data-target="#priviewledgerpopup" Text="save" runat="server" />--%>
                                    <%--                          <a id="cpMainContent_btnSave" data-toggle="modal"  data-target="#priviewledgerpopup" class="btn btn-success mb-3 Voucher_entryBTN">Save</a>                           --%>
                                </div>
                            </div>
                        </div>
                        <!--row-->
                    </div>
                    <!--card-body-->
                </div>
                <!--card-->
            </div>
            <!--col-lg-6-->
        </div>
        <!--row-->
            <div class="modal fade" id="priviewledgerpopup" data-bs-backdrop="static" data-bs-keyboard="false"
                      tabindex="-1" aria-labelledby="DocumentUploadpopupLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                    <h6 class="modal-title lh-1 font-weight-bold" id="priviewledgerpopupLabel">
                        <asp:Literal ID="ltrTitle" runat="server"></asp:Literal>
                        <%--<span class="voucherofdate w-100 d-inline-block font-weight-normal text-sm">12/03/2023</span>--%>
                        <asp:Label ID="ltrdate" CssClass="voucherofdate w-100 d-inline-block font-weight-normal text-sm" runat="server"></asp:Label>
                    </h6>
                    <!-- <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->                            
                    </div>
                    <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive p-0" style="max-height: 400px;">
                                <asp:GridView ID="gvpopup" OnDataBound="gvpopup_DataBound" CssClass="table table-bordered table-head-fixed mb-0" AutoGenerateColumns="false" ShowFooter="true" runat="server">
                                    <Columns>
                                        <asp:BoundField HeaderText="Header of Account"   DataField="particulars" SortExpression="particulars"  ItemStyle-Width="50%" />                              
                                        <asp:BoundField HeaderText="Debit" DataField="debit" SortExpression="debit"   DataFormatString="{0:n}" ItemStyle-HorizontalAlign="Right" ItemStyle-Width="25%"  />                                                              
                                        <asp:BoundField HeaderText="Credit" DataField="credit" SortExpression="credit"   DataFormatString="{0:n}" ItemStyle-HorizontalAlign="Right" ItemStyle-Width="25%" />
                                    </Columns>                                                          
                                </asp:GridView>                                                                          
                            </div><!--table-responsive-->                                
                            </div><!--col-12-->
                            <div class="col-12">
                                <h6 class="mt-2 font-weight-bold mb-0">Narration</h6>
                                <asp:Literal ID="ltrnarration" runat="server"></asp:Literal>
                                <h6 class="mt-2 font-weight-bold mb-0">Uploaded files</h6>
                                <asp:Repeater ID="rptupdate" OnDataBinding="rptupdate_DataBinding" runat="server">
                                    <ItemTemplate>
                                        <ol class="upload_list p-0 m-0 mt-2 ml-3 mb-3">
                                            <li class="mb-1"><%# Eval("DocumentName")%></li>                                 
                                        </ol>
                                    </ItemTemplate>
                                </asp:Repeater>
                            </div>        
                        </div><!--row-->
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <div class="btn_sec d-inline-block">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <asp:LinkButton ID="savemod"  CssClass="btn btn-primary ml-0 ml-sm-2 Voucher_entryBTN objdiv" OnClick="savemod_Click"  runat="server"  Text="Confirm & Save"></asp:LinkButton>  
                            </div>
                            
                        <%-- <button type="button" v class="btn btn-success">Confirm & Save</button>--%>                                                  
    <%--                          <a id="cpMainContent_btnSave" class="btn btn-success mb-3 Voucher_entryBTN objdiv" onclick="savemod_Click"  href="javascript:__doPostBack('ctl00$cpMainContent$btnSave','')">Save</a>                           --%>                                                                             
                        </div>
                    </div><!--modal-content-->
                </div><!--modal-dialog-->
            </div><!--modal-->
<script>

    function loadFile(fileurl, upload_qrqcode_wrap) {
        $.ajax({
            url: fileurl,
            type: 'GET',
            headers: {
                'Access-Control-Allow-Credentials': true,
                'Access-Control-Allow-Origin': fileurl,
            },
            error: function (xhr, status, error) {
                //file not exists
                upload_qrqcode_wrap.find(".qrimg_preview_block-class").hide();
                clearInterval(ftimer);
                ftimer = null;
                ftimer = setTimeout(function () {
                    loadFile(fileurl, upload_qrqcode_wrap); // repeat
                }, 10 * 1000);

            },
            success: function (data) {
                //file exists
                if (ftimer != null) {
                    clearInterval(ftimer);
                    ftimer = null;
                }
                upload_qrqcode_wrap.find('.uploadfile_wrap').removeClass('w-100');

                upload_qrqcode_wrap.find('.upload_btnicon').hide();
                upload_qrqcode_wrap.find('.qrqcode_sec').hide();
                if (upload_qrqcode_wrap.find('#docPreview_wap0').length != 0) {
                    $('#docPreview_wap0').hide();
                    $('#docPreview_wap0').html('');
                    $('#docPreview_wap0').innerHTML = "";
                }
                if (upload_qrqcode_wrap.find('#docPreview_wap1').length != 0) {
                    $('#docPreview_wap1').hide();
                    $('#docPreview_wap1').html('');
                    $('#docPreview_wap1').innerHTML = "";
                }
                if (upload_qrqcode_wrap.find('#docPreview_wap2').length != 0) {
                    $('#docPreview_wap2').hide();
                    $('#docPreview_wap2').html('');
                    $('#docPreview_wap2').innerHTML = "";
                }
                if (upload_qrqcode_wrap.find('#ImgPreview_wap0').length != 0) {
                    $('#ImgPreview_wap0').hide();
                    $('#ImgPreview_wap0 img').attr("src", "");
                }
                if (upload_qrqcode_wrap.find('#ImgPreview_wap1').length != 0) {
                    $('#ImgPreview_wap1').hide();
                    $('#ImgPreview_wap1 img').attr("src", "");
                }
                if (upload_qrqcode_wrap.find('#ImgPreview_wap2').length != 0) {
                    $('#ImgPreview_wap2').hide();
                    $('#ImgPreview_wap2 img').attr("src", "");
                }

                upload_qrqcode_wrap.find('.qrqcode_btnicon').hide();

                if (upload_qrqcode_wrap.find('#QRImgPreview_wap0').length != 0) {
                    $('#DocumentURL0').val(fileurl);
                    $("#QRImgPreview_wap0").show();
                    $('#QRImgPreview0').attr("src", fileurl);
                    $("#QRImgPreview0").show();
                }
                if (upload_qrqcode_wrap.find('#QRImgPreview_wap1').length != 0) {
                    $('#DocumentURL1').val(fileurl);
                    $("#QRImgPreview_wap1").show();
                    $('#QRImgPreview1').attr("src", fileurl);
                    $("#QRImgPreview1").show();
                }
                if (upload_qrqcode_wrap.find('#QRImgPreview_wap2').length != 0) {
                    $('#DocumentURL2').val(fileurl);
                    $("#QRImgPreview_wap2").show();
                    $('#QRImgPreview2').attr("src", fileurl);
                    $("#QRImgPreview2").show();
                }
                var nextTargetDiv = upload_qrqcode_wrap.closest('.Uploadbox').first().nextAll(".Uploadbox").first();
                nextTargetDiv.removeClass('Uploadbox disabled');
                nextTargetDiv.addClass('Uploadbox enabled');

            }
        });
    }


    pdfjsLib.GlobalWorkerOptions.workerSrc = '/content/js/custom/pdf.worker.js';

    function addChangeEventListener(e) {
        var $docPreview_wap = $(this).closest('.upload_qrqcode_wrap').find('.doc_preview_block-class');
        $docPreview_wap.innerHTML = "";
        //document.querySelector("#docPreview_wap").innerHTML = "";

        var file = e.target.files[0]
        if (file.type != "application/pdf") {
            alert(file.name + " is not a pdf file.")
            return
        }

        var fileReader = new FileReader();

        fileReader.onload = function () {
            var typedarray = new Uint8Array(this.result);

            pdfjsLib.getDocument(typedarray).promise.then(function (pdf) {
                // you can now use *pdf* here
                console.log("the pdf has", pdf.numPages, "page(s).");
                for (var i = 0; i < pdf.numPages; i++) {
                    (function (pageNum) {
                        pdf.getPage(i + 1).then(function (page) {
                            // you can now use *page* here
                            var viewport = page.getViewport(2.0);
                            var pageNumDiv = document.createElement("div");
                            pageNumDiv.className = "pageNumber";
                            pageNumDiv.innerHTML = "Page " + pageNum;
                            var canvas = document.createElement("canvas");
                            canvas.className = "page";
                            canvas.title = "Page " + pageNum;
                            $docPreview_wap.append(pageNumDiv);
                            $docPreview_wap.append(canvas);
                            $docPreview_wap.show();
                            //document.querySelector("#docPreview_wap").appendChild(pageNumDiv);
                            //document.querySelector("#docPreview_wap").appendChild(canvas);
                            //$('#docPreview_wap').show();
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;


                            page.render({
                                canvasContext: canvas.getContext('2d'),
                                viewport: viewport
                            }).promise.then(function () {
                                console.log('Page rendered');
                            });
                            page.getTextContent().then(function (text) {
                                console.log(text);
                            });
                        });
                    })(i + 1);
                }

            });
        };

        fileReader.readAsArrayBuffer(file);
    }
    $('#UploadFile0').find('.fup_pdf_upload').on("change", addChangeEventListener);
    $('#UploadFile1').find('.fup_pdf_upload').on("change", addChangeEventListener);
    $('#UploadFile2').find('.fup_pdf_upload').on("change", addChangeEventListener);

    function readURL(input, imgControlName) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(imgControlName).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }


    function Generator() { };

    Generator.prototype.rand = Math.floor(Math.random() * 26) + Date.now();

    Generator.prototype.getId = function () {
        return this.rand++;
    };
    var idGen = new Generator();
    var folder;// = '<%= Guid.NewGuid().ToString() %>';


    $('#modalDialogShow').val('false');


    var key = document.getElementById('<%= hfdKey.ClientID %>').value;
    document.getElementById('<%= hfdKey.ClientID %>').value = (key == "") ? folder : key;
    var cururl = '<%= GetCurrentUrl()%>';
    /*var bloburl = 'https://finascopstorage.blob.core.windows.net/finascop-files';*/
    let ftimer = null;

    var bloburl = document.getElementById('<%= hfdBlobURL.ClientID %>').value;

    function generateFileUrl(qr_code_icon) {

        var filename = idGen.getId();
        var blobFileURL = bloburl + '/finascopupload/' + folder + '/' + filename;
        if ($(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName0').length != 0) { $(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName0').val(filename); }
        if ($(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName1').length != 0) { $(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName1').val(filename); }
        if ($(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName2').length != 0) { $(qr_code_icon).closest('.upload_qrqcode_wrap').find('#blobFileName2').val(filename); }
        return blobFileURL;
    }

    function deleteBlobFile(blobFileUrl, hfdDocumentURL) {
        $.ajax({
            type: "POST",
            url: '/Finance/UploadFile.aspx/deleteBlobFile',
            data: JSON.stringify({ "blobFileURL": blobFileUrl }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function (response) {
                hfdDocumentURL.val("");
            },
            error: function (err) {

            }
        });
    }

    $(document).on('show.bs.modal', '#DocumentUploadpopup', function () {
        $('#<%= dltype.ClientID %>').val('1');

        if ($('#DocumentURL0').val() != "") {
            $('#UploadFile0').find('.uploadfile_wrap').removeClass('w-100');
            $('#UploadFile0').find('.upload_btnicon').hide();
            $('#UploadFile0').find('.qrqcode_sec').hide();
            $('#docPreview_wap0').hide();
            $('#docPreview_wap0').html('');
            $('#docPreview_wap0').innerHTML = "";
            $('#ImgPreview_wap0').hide();
            $('#ImgPreview_wap0 img').attr("src", "");
            $('#UploadFile0').find('.qrqcode_btnicon').hide();
            $('#QRImgPreview_wap0').show();
            $('#QRImgPreview0').attr("src", $('#DocumentURL0').val());
            $('#QRImgPreview0').show();
            $('#QRImgPreview0').on("error", handleImageError0);
            $('#UploadFile0').removeClass('Uploadbox disabled');
            $('#UploadFile0').addClass('Uploadbox enabled');
            $('#UploadFile1').removeClass('Uploadbox disabled');
            $('#UploadFile1').addClass('Uploadbox enabled');
        } else {
            $('#UploadFile0').find(".repeater_block-class").removeClass("repeater-item-focus");
            $qr_code_icon = $('#UploadFile0').find('.qrqcode_btnicon');
            $qr_code_icon.hide();
            $qr_code_icon.removeClass("repeater-item-focus");
        }


        if ($('#DocumentURL1').val() != "") {
            $('#UploadFile1').find('.uploadfile_wrap').removeClass('w-100');
            $('#UploadFile1').find('.upload_btnicon').hide();
            $('#UploadFile1').find('.qrqcode_sec').hide();
            $('#docPreview_wap1').hide();
            $('#docPreview_wap1').html('');
            $('#docPreview_wap1').innerHTML = "";
            $('#ImgPreview_wap1').hide();
            $('#ImgPreview_wap1 img').attr("src", "");
            $('#UploadFile1').find('.qrqcode_btnicon').hide();
            $('#QRImgPreview_wap1').show();
            $('#QRImgPreview1').attr("src", $('#DocumentURL1').val());
            $('#QRImgPreview1').show();
            $('#QRImgPreview1').on("error", handleImageError1);
            $('#UploadFile1').removeClass('Uploadbox disabled');
            $('#UploadFile1').addClass('Uploadbox enabled');
            $('#UploadFile2').removeClass('Uploadbox disabled');
            $('#UploadFile2').addClass('Uploadbox enabled');
        } else {
            $('#UploadFile1').find(".repeater_block-class").removeClass("repeater-item-focus");
            $qr_code_icon = $('#UploadFile1').find('.qrqcode_btnicon');
            $qr_code_icon.hide();
            $qr_code_icon.removeClass("repeater-item-focus");
        }

        if ($('#DocumentURL2').val() != "") {
            $('#UploadFile2').find('.uploadfile_wrap').removeClass('w-100');
            $('#UploadFile2').find('.upload_btnicon').hide();
            $('#UploadFile2').find('.qrqcode_sec').hide();
            $('#docPreview_wap2').hide();
            $('#docPreview_wap2').html('');
            $('#docPreview_wap2').innerHTML = "";
            $('#ImgPreview_wap2').hide();
            $('#ImgPreview_wap2 img').attr("src", "");
            $('#UploadFile2').find('.qrqcode_btnicon').hide();
            $('#QRImgPreview_wap2').show();
            $('#QRImgPreview2').attr("src", $('#DocumentURL2').val());
            $('#QRImgPreview2').show();
            $('#QRImgPreview2').on("error", handleImageError2);
            $('#UploadFile2').removeClass('Uploadbox disabled');
            $('#UploadFile2').addClass('Uploadbox enabled');
            $('#UploadFile0').removeClass('Uploadbox disabled');
            $('#UploadFile0').addClass('Uploadbox enabled');
        }
        else {
            $('#UploadFile2').find(".repeater_block-class").removeClass("repeater-item-focus");
            $qr_code_icon = $('#UploadFile2').find('.qrqcode_btnicon');
            $qr_code_icon.hide();
            $qr_code_icon.removeClass("repeater-item-focus");
        }


        $('#UploadFile0').find('.upload_qrqcode_wrap').click();
        $('#modalDialogShow').val('true');

    });


    $(document).on('hidden.bs.modal', '#DocumentUploadpopup', function () {
        if (ftimer != null) {
            // close timer.
            clearInterval(ftimer);
            ftimer = null;
        }
        $('#modalDialogShow').val('false');
    })

</script>

         <script>
             $(document).ready(function () {
                 $(document).ready(function () {
                     $('.select2').select2();
                 });
             });
         </script>
  <style>
      .modal-body table.table tbody > tr:last-child > td {
          background: #DEE2E6;
          font-weight: bold;
          text-align: right;
      }
  </style>

</asp:Content>
