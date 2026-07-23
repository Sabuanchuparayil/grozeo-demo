<%@ Page Language="C#"  MaintainScrollPositionOnPostback="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Vouchers" AutoEventWireup="true" CodeBehind="DataEntry.aspx.cs" Inherits="RetalineProAgent.DataEntry" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
        <a href="/Finance/Navigations/Accounting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    <head> 
        <link rel="stylesheet" href="/Content/customadmin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <link rel="stylesheet" href="/Content/customadmin/plugins/select2/css/select2.min.css">
        <link rel="stylesheet" href="/Content/customadmin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

        <script src="/Content/customadmin/plugins/select2/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
        <script src="/content/js/custom/pdf.js"></script>



        <script>
            pdfjsLib.GlobalWorkerOptions.workerSrc = '/content/js/custom/pdf.worker.js';



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
                        $('#DU_DocumentURL').val("");
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
                        if (upload_qrqcode_wrap.find('#DU_docPreview_wap').length != 0) {
                            $('#DU_docPreview_wap').hide();
                            $('#DU_docPreview_wap').html('');
                            $('#DU_docPreview_wap').innerHTML = "";
                        }
                        if (upload_qrqcode_wrap.find('#DU_ImgPreview_wap').length != 0) {
                            $('#DU_ImgPreview_wap').hide();
                            $('#DU_ImgPreview_wap img').attr("src", "");
                        }

                        upload_qrqcode_wrap.find('.qrqcode_btnicon').hide();

                        if (upload_qrqcode_wrap.find('#DU_QRImgPreview_wap').length != 0) {
                            $('#DU_DocumentURL').val(fileurl);
                            $("#DU_QRImgPreview_wap").show();
                            $('#DU_QRImgPreview').attr("src", fileurl);
                            $("#DU_QRImgPreview").show();
                        }

                    }
                });
            }



            function readURL(input, imgControlName) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $(imgControlName).attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            let $current_upload_qrqcode_wrap = null;

            function Generator() { };

            Generator.prototype.rand = Math.floor(Math.random() * 26) + Date.now();

            Generator.prototype.getId = function () {
                return this.rand++;
            };
            var idGen = new Generator();
            var folder;// = '<%= Guid.NewGuid().ToString() %>';


            $('#DU_modalDialogShow').val('false');


            var key; // = document.getElementById('<%= DU_hfdKey.ClientID %>').value;
            //document.getElementById('<%= DU_hfdKey.ClientID %>').value = (key == "") ? folder : key;
            var cururl = '<%= GetCurrentUrl()%>';

            let ftimer = null;

            var bloburl; // = document.getElementById('<%= DU_hfdBlobURL.ClientID %>').value;

            $(document).ready(function () {
                $('#DocumentUploadpopup').on('shown.bs.modal', function () {
                    // Call generateFileUrl when the modal is shown
                    $('#DU_blobFileName').val("");
                    $('#DU_DocumentURL').val("");
                    var qrCodeIcon = $('#DU_UploadFile').find('.qrqcode_btnicon');
                    generateFileUrl(qrCodeIcon);
                });
            });

            function generateFileUrl(qr_code_icon) {
                var filename = $('#DU_blobFileName').val();
                if (!filename || filename.trim() === "") {
                    filename = idGen.getId();
                    $('#DU_blobFileName').val(filename);
                } else {
                    filename = $('#DU_blobFileName').val();
                }


                folder = $('#DU_folder').val();

                key = document.getElementById('<%= DU_hfdKey.ClientID %>').value;
                document.getElementById('<%= DU_hfdKey.ClientID %>').value = (key == "") ? folder : key;
                bloburl = document.getElementById('<%= DU_hfdBlobURL.ClientID %>').value;

                var blobFileURL = "";
                bloburl = document.getElementById('<%= DU_hfdBlobURL.ClientID %>').value;
                blobFileURL = bloburl + '/finascopupload/' + folder + '/' + filename;
                $('#DU_blobFileURL').val(blobFileURL);
                if ($(qr_code_icon).closest('.upload_qrqcode_wrap').find('#DU_blobFileName').length != 0) { $(qr_code_icon).closest('.upload_qrqcode_wrap').find('#DU_blobFileName').val(filename); }


                //var fileuploadurl = cururl;

                var filename = $('#DU_blobFileName').val();
                if (!filename || filename.trim() === "") {
                    filename = idGen.getId();
                    $('#DU_blobFileName').val(filename);
                } else {
                    filename = $('#DU_blobFileName').val();
                }
                $('#DU_blobFileName').val(filename); // Set the generated file name to the hidden field

                // Generate the QR code
                //var cururl = '<%= GetCurrentUrl() %>';
                //var folder = $('#DU_folder').val();
                fileuploadurl = cururl + '/Finance/UploadFile?key=' + folder + '&file=' + filename;
                var qrCodeBase64 = generateQRCodeBase64(fileuploadurl, 325);

                // Set the QR code image source
                $('#DU_imgUploadQrcode').attr('src', 'data:image/png;base64,' + qrCodeBase64);


                // fileuploadurl += '/Finance/UploadFile?key=' + folder + '&file=' + filename;
                console.log(fileuploadurl);
                console.log(blobFileURL);
                return blobFileURL;
            }

            $(document).ready(function () {
                key = document.getElementById('<%= DU_hfdKey.ClientID %>').value;
                document.getElementById('<%= DU_hfdKey.ClientID %>').value = (key == "") ? folder : key;
                bloburl = document.getElementById('<%= DU_hfdBlobURL.ClientID %>').value;
                $(".remove_preview_wrap").click(function () {
                    $upload_qrqcode_wrap = $(this).closest('.upload_qrqcode_wrap');
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

                if ($('#DU_folder').val() == "") {
                    $('#DU_folder').val(folder);
                } else {
                    folder = $('#DU_folder').val();
                }
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

                        if ($(item).find('#DU_DocumentName').length != 0) { DocumentName = $(item).find('#DU_DocumentName').val(); }
                        $('#<%=DU_txtdocname.ClientID %>').val(DocumentName);

                        var Narration = "";
                        if ($(item).find('#DU_Narration').length != 0) { Narration = $(item).find('#DU_Narration').val(); }

                        $('#<%=tbxNarration.ClientID %>').val(Narration);

                    }
                });
            });

            $('#<%=DU_txtdocname.ClientID %>').click(function (e) {
                $(this).select();
            });

            $('#<%=DU_txtdocname.ClientID %>').blur(function () {
                var DocumentName = $(this).val();
                if ($current_upload_qrqcode_wrap.find('#DU_DocumentName').length != 0) { $current_upload_qrqcode_wrap.find('#DU_DocumentName').val(DocumentName); }

            });
            $('#<%=tbxNarration.ClientID %>').blur(function () {
                var Narration = $(this).val();
                if ($current_upload_qrqcode_wrap.find('#DU_Narration').length != 0) { $current_upload_qrqcode_wrap.find('#DU_Narration').val(Narration); }
            });


            $(document).on('click', '#DU_qrcode-section', function (e) {
                if (ftimer != null) {
                    clearInterval(ftimer);
                    ftimer = null;
                }
                $upload_qrqcode_wrap = $(this).closest('.upload_qrqcode_wrap');
                $upload_qrqcode_wrap.find('.qrqcode_btnicon').show();
                $upload_qrqcode_wrap.find('.qrqcode_btnicon').addClass("repeater-item-focus");

                var blobFileURL = "";
                blobFileURL = $('#DU_blobFileURL').val();
                ftimer = setInterval(loadFile(blobFileURL, $(this).closest('.upload_qrqcode_wrap')), 10 * 1000);
            });

            $(document).on('click', '#DU_close_btn', function (e) {
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
                }
            }

            $('#DU_UploadFile').find('.fup_pdf_upload').change(fupPdfFileUploadChange);

        </script>
        <script>
            $(document).ready(function () {
                $(".horizontal-scroll").on("wheel", function (e) {
                    e.preventDefault();
                    this.scrollLeft += e.originalEvent.deltaY;
                });


            });


            $(document).ready(function () {
                $(".repeater_block-class").on("click", function () {
                    $('#UploadFile .repeater_block-class').removeClass('repeater-item-focus');
                    $(this).closest(".repeater_block-class").addClass("repeater-item-focus");
                });
            });

            function handleImageErrorDU(imgElement) {
                //handleImgError($('#DU_QRImgPreview'), $('#DU_QRPdfPreview'));
                var parentDiv = $(imgElement).closest('.repeater_block-class');

                var qrImgPreview = parentDiv.find('#DU_QRImgPreview');
                var qrPdfPreview = parentDiv.find('#DU_QRPdfPreview');

                handleImgError($(qrImgPreview), $(qrPdfPreview));
            }

            $(document).ready(function () {
                // Show the QRImgPreview_wap
                $('.qrimg_preview_block-class').css('display', 'block');

                // Hide other obstructing elements
                $('#docPreview_wap, #ImgPreview_wap, #actions, #qrcode-section, #documentupload_input, #imageupload_input').css('display', 'none');

                // Optionally, ensure scrolling is disabled for better focus
                $('body').css('overflow', 'auto');

                //$('#QRImgPreview').on("error", handleImageError);
                $('#popupDocumentImage').on("error", handleImageErrorPopup);
            });



            $('#popupDocumentImage').on("error", handleImageErrorPopup);


            function handleImageError(imgElement) {

                var parentDiv = $(imgElement).closest('.repeater_block-class');

                var qrImgPreview = parentDiv.find('#QRImgPreview');
                var qrPdfPreview = parentDiv.find('#QRPdfPreview');
                
                handleImgError($(qrImgPreview), $(qrPdfPreview));
            }

            function handleImageErrorPopup(event) {
                handleImgError($('#popupDocumentImage'), $('#popupPdf'));
            }


            function handleImgError(QRImgPreview, QRPdfPreview) {
                let pdfUrl = $(QRImgPreview).attr('src');
                if (pdfUrl == "") {
                    return;
                }

                $(QRImgPreview).attr('src', '').hide();

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
                $(QRPdfPreview).show();
                $('#docPreview_wap').hide(); // docPreview_wap
            }
        </script>

        <script>
            document.addEventListener("visibilitychange", function () {
                if (document.visibilityState === "visible") {
                    location.reload(); // Reloads the page when it becomes visible
                }
            });

            function copyOrderNumber(fullReference) {
                // Extract the order number using a regular expression (digits only after ":")
                const orderNumber = fullReference.match(/:\s*(\d+)/)[1];
                navigator.clipboard.writeText(orderNumber).then(() => {
                    const message = document.getElementById("fadeMessage");
                    message.innerText = `Order Number ${orderNumber} copied to clipboard`;
                    message.classList.add("show");
                    setTimeout(() => {
                        message.classList.remove("show");
                    }, 5000);
                }).catch(err => {
                    console.error("Failed to copy: ", err);
                });
            }

            document.addEventListener("DOMContentLoaded", () => {
                const scrollContainer = document.querySelector(".docattachment_list");
                const leftButton = document.querySelector(".left-button");
                const rightButton = document.querySelector(".right-button");
                const scrollAmount = 150; // Adjust scroll amount as needed

                leftButton.addEventListener("click", (event) => {
                    event.preventDefault(); // Prevent form submission or page reload
                    scrollContainer.scrollBy({
                        left: -scrollAmount,
                        behavior: "smooth",
                    });
                });

                rightButton.addEventListener("click", (event) => {
                    event.preventDefault(); // Prevent form submission or page reload
                    scrollContainer.scrollBy({
                        left: scrollAmount,
                        behavior: "smooth",
                    });
                });
            });
        </script>

        <style>
                .class-blobfileurl{}
                .doument-upload-dialog{}
                .document-name{}
                .fup_block-class {}
                .repeater-item-focus {
                    background-color: #c6def7; /* Change to the color you prefer */
                }
                .repeater_block-class {}
                .doc_preview_block-class { 
                    display: block; 
                }
                .btn-link:hover{
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

                .btn_same{
                    width: 70px;
                    margin-top: 10px;
                }

            .remove_preview_wrap {
                bottom: -25px;
                right: 30px;
            }
            .qrqcode_btnicon{
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
        <style>
            .modal-body table.table tbody > tr:last-child > td{background:#DEE2E6; font-weight:bold; text-align:right;}
        </style>
        <style>
            /* Styling for the fading message */
            #QRPdfPreview {
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
            .pageContainer {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .pageCanvas {
                border: 1px solid #ccc;
            }
            .pageNumber {
                font-size: 14px;
                margin-bottom: 5px;
                font-weight: bold;
            }

            .repeater-item-focus {
                background-color: #c6def7; 
            }
            .repeater_block-class {}
            .fade-message {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: #2B712E;
                color: white;
                padding: 15px 30px;
                border-radius: 10px;
                opacity: 50;
                visibility: hidden;
                transition: opacity 3.5s, visibility 3.5s;
            }
            .fade-message.show {
                opacity: 0.5;
                visibility: visible;
            }
            .copy-icon {
            cursor: pointer;
            margin-left: 8px;
            width: 20px;
            height: 20px;
        }
        </style>
    </head>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
    <div class="">
        <div class="d-flex align-items-center">
            <h6 class="slim-pagetitle">Vouchers</h6>
            <div class="d-inline-block">
                <div class="d-inline-block ml-3" >
                    <asp:HyperLink runat="server"  CssClass="input-group-append" NavigateUrl="~/Finance/VoucherEntry.aspx"   Text="Create New">
                        <div class="btn btn-primary btn-sm">
                            <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                            Create New
                        </div>
                    </asp:HyperLink>                                    
               </div>
                <%--<div class="d-inline-block" >
                    <asp:CheckBox ID="chkshow" OnCheckedChanged="chkshow_CheckedChanged" AutoPostBack="true" CssClass="showallckbx" runat="server" Text="Show All Entries" />
                </div>--%>
                
            </div> 
         </div>
        <p class="mb-">You can see Vouchers</p>
    </div>


</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row row-sm">
            <div class="col-12 col-lg-7 pb-3">
                <div class="card">
                    <div class="card-header shadow_top">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-7">
                                <div class="form-group d-flex row row-sm mb-2 mb-lg-0">
                                    <div class="input-group input-group col-12 col-md-5 pr-md-1 mb-2 mb-md-0">
                                        <label for="FromDate" class="mb-0 w-100">From</label>
                                        <asp:TextBox ID="txtFromDate" CssClass="form-control" runat="server" TextMode="Date" />
                                    </div>

                                    <div class="input-group input-group col-12 col-md-5 pl-md-1 pr-md-1">
                                        <label for="ToDate" class="mb-0 w-100">To</label>
                                        <asp:TextBox ID="txtToDate" CssClass="form-control"  runat="server" TextMode="Date"/>
                                    </div>
                                    <div class="input-group input-group col-12 col-md-2 align-items-end pl-md-1 mt-2 mt-md-0">
                                        <asp:Button ID="btnsearch" CssClass="btn btn-primary" runat="server" Text="GO" OnClick ="btnsearch_Click" />
                                    </div>
                                </div>
                            </div>
                            <!--col-lg-7-->
                            <div class="col-12 col-lg-5 align-items-end d-flex">
                                <div class="d-flex w-100">
                                    <input type="text" style="display:none" />
                                    <input type="password" style="display:none" />
                                    <div class="input_search_box">
                                        <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton runat="server" CssClass="input-group-append" ID="lbnSearch" OnClick="lbnSearch_Click">
                                        <div class="btn bd bd-l-0 tx-gray-600">
                                          <i class="fa fa-search"></i>
                                        </div>
                                    </asp:LinkButton>
                                    </div>
                                    
                                </div>
                                  <div class="ml-3 d-flex align-content-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="line-height: 100%;font-size:16px;">
                                            <i class="fa fa-sliders"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a class="dropdown-item" href="DataEntry?et=2">Manual Vouchers</a></li>
                                            <li><a class="dropdown-item" href="DataEntry?et=1">System Vouchers</a></li>
                                            <li><a class="dropdown-item" href="DataEntry">All Vouchers</a></li>
                                        </ul>
                                    </div>
                                  </div>
                            </div>
                            <!--col-lg-4-->
                        </div>

                    </div>

                    <div class="row row-sm " ID="divTranLogStatus" runat="server">
                        <div class="col-lg-12 mb-3 mb-lg-4">
                            <div class="card-body p-0 tx-left position-relative">
                                <asp:Label ID="lblStatus" runat="server" TextMode="MultiLine" CssClass="richText">
                                </asp:Label>                                
                            </div>
                        </div>
                    </div>

                    <div class="card-body ">
                        <div class="table-responsive">
                            <table class="table table-bordered" cellspacing="0" rules="all" id="cpMainContent_gvDataEntry" style="border-collapse: collapse;">
                                <thead>
                                    <tr class="border-top">

                                        <th scope="col" width="20%">Date
                                        </th>
                                        <th scope="col" width="20%">
                                            <a href="javascript:__doPostBack('ctl00$cpMainContent$gvDataEntry','Sort$name')">Voucher</a>
                                        </th>
                                        <th scope="col" width="40%">Reference</th>
                                        <th scope="col" width="20%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <asp:ListView ID="lvdatatable" OnSelectedIndexChanged="lvdatatable_SelectedIndexChanged" OnDataBound="lvdatatable_DataBound" runat="server" DataSourceID="SDSDataEntry" >
                                        <ItemTemplate>
                                            <tr class="tbldataentry position-relative <%# (hidVoucherId.Value == Eval("id").ToString() ? "selectrow" : "") %>" onclick="otherBtnClick(this)">
                                                <td class="py-2" style="vertical-align: middle; line-height: 100%;"><%# ((DateTime)Eval("datetime")).ToString("dd/MMM/yyyy hh:mm tt")%></td>
                                                <td class="py-2" style="vertical-align: middle;">
                                                    <div class="d-flex" style="align-items: center;">
                                                        <div style="line-height: 100%; margin-top: 2px;">
                                                            <span class=" d-inline-block w-100 mt-1"><%# Eval("voucherSlNoString")%></span>
                                                            <%# Eval("name")%><b class="ml-2 font-weight-normal" style="border: 1px solid #a5a5a5; padding: 2px 2px; line-height: 9px; height: 14px; display: inline-block; border-radius: 3px; color: #5e5e5e; font-size: 12px;"><%# Eval("entrytype")%></b>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-2" style="vertical-align: middle;">
                                                    <div style="line-height: 100%;">
                                                           <%# Eval("reference")%>
                                                            <img src="/Content/images/Copy.png" style="width: 12px; height: 14px;" alt="Copy Order Number" class="copy-icon"  title="Copy Order Number" onclick="event.preventDefault(); event.stopPropagation();copyOrderNumber('<%# Eval("reference") %>')" />
                                                    </div>
                                                </td>
                                                <td class="py-2" align="right" style="vertical-align: middle;"><%# Eval("amount","{0:n}") %>
                                                    <asp:LinkButton ID="lbSelectData" runat="server" CssClass="d-none btnvoucherrow td_link text-black-50" dataid='<%# Eval("id") %>'
                                                        order_order_id='<%# Eval("entity_id") %>' OnClick="lbSelectData_Click" Text='<%# Eval("amount","{0:n}") %>'></asp:LinkButton>
                                                </td>
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
                            </table>
                         <asp:SqlDataSource runat="server" ID="SDSDataEntry"  ConnectionString="<%$ connectionStrings:FinascopConnection %>" 
                                SelectCommand="select de.id,de.voucherSlNoString,de.entity_id,entry_type,vt.name,(case when entry_type=1 then 'A' when entry_type=2 then 'M' END) as entrytype,(select top 1 tr.reference from transactions tr where tr.data_entry_id=de.id) as reference,CONVERT(DATE,de.createdOn) as date,CONVERT(datetime, SWITCHOFFSET(de.createdOn, DATEPART(TZOFFSET,de.createdOn AT TIME ZONE 'India Standard Time'))) as datetime,de.narration,de.amount ,de.docSerialNo from data_entry de inner join voucher_type vt ON de.voucher_type_id=vt.id
                                               WHERE (@search <> '' AND de.entity_id LIKE CONCAT('%', @search, '%')) OR (@search = '' AND (@fromDate IS NULL OR @fromDate = '' OR CAST(de.createdOn AS DATE) >= @fromDate) AND (@toDate IS NULL OR @toDate = '' OR CAST(de.createdOn AS DATE) <= @toDate)) AND (@entrytype <= 0 OR entry_type = @entrytype) ORDER BY de.id DESC;">
                             <SelectParameters>
                                 <asp:QueryStringParameter QueryStringField="et" Name="entrytype" DefaultValue="0" />
                                 <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                 <asp:ControlParameter ControlID="txtFromDate" PropertyName="Text" ConvertEmptyStringToNull="false" Name="fromDate" />
                                 <asp:ControlParameter ControlID="txtToDate" PropertyName="Text" Name="toDate" ConvertEmptyStringToNull="false" />
                             </SelectParameters>
                            </asp:SqlDataSource>
                            <div id="fadeMessage" class="fade-message"></div>
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
            <div class="col-12 col-lg-5 pb-3">

                  <div class="card m-0 h-100">
                  <div class="card-header d-flex shadow_top " style="min-height: 90px;">
                    <div class="row row-sm w-100">
                      <div class="col-12 col-lg-7">
                        <div class="text-left"><b class="mr-1">Voucher Type:</b><asp:Literal ID="lbVoucher" runat="server"></asp:Literal></div>
                        <div class="text-left w-100"><b class="mr-1">Voucher Number:</b><asp:Literal ID="lbVocherId" runat="server"></asp:Literal></div>
                      </div>
                      <div class="col-12 col-lg-5">
                        <div class="text-lg-right"><b class="mr-1">Date:</b><asp:Literal ID="lbDate" runat="server"></asp:Literal></div>
                        <div class="text-lg-right"><asp:Label ID="lbtime" runat="server"></asp:Label></div>
                      </div>
                    </div>
                  </div>
                       
                    <div class="table-responsive">
                        <asp:HiddenField ID="hidVoucherId" ClientIDMode="Static" Value="0" runat="server" />
                        <asp:HiddenField ID="hidOrderOrderId" ClientIDMode="Static" Value="0" runat="server" />
                        <div class="card-body">
                    <div class="table-responsive mb-3 border-bottom" style="max-height:315px;">
                         <asp:ListView ID="lvDataEntry" runat="server" DataSourceID="SqlDataEnt" OnDataBound="lvDataEntry_DataBound">
                             <LayoutTemplate>
                      <table id="" class="table table-bordered table-head-fixed m-0">
                        <thead>
                          <tr id="Tr1" class="TableHeader">
                            <th id="Td1" width="44%" class="border-top">Head of Account</th>
                            <th id="Td2" width="28%" class="border-top" align="center">Debit</th>
                            <th id="Td3" width="28%" class="border-top" align="center">Credit</th>
                          </tr>
                        </thead>                           
                        <%--<tbody>                          
                          <tr>                             
                            <td style="line-height: 100%;">
                              <span id="lbPerticulars"><%# Eval("particulars")%></span>
                            </td>
                            <td align="right">
                              <span id="lbDramount"><%# Eval("dr_amount","{0:n}")%></span>
                            </td>
                            <td align="right">
                              <span id="lbCramount"><%# Eval("cr_amount","{0:n}")%></span>
                            </td>
                          </tr>                          
                                               
                        </tbody>--%>
                           <tr id="ItemPlaceholder" runat="server">
                             </tr>      
                        <tfoot>
                          <tr>
                            <th class="py-2" id="Td4">Total</th>
                            <th class="py-2" align="right" style="text-align:right;"><asp:Literal ID="ltrDrTotal" runat="server"></asp:Literal></th>
                            <th class="py-2" align="right" style="text-align:right;"><asp:Literal ID="ltrCRTotal" runat="server"></asp:Literal></th>
                          </tr>
                        </tfoot>                          
                      </table>
                       </LayoutTemplate>
                              <ItemTemplate>
                                    <tr class="TableData">
                                        <td style="line-height: 100%;">
                                            <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("particulars")%>'>   
                                            </asp:Label>
                                        </td>
                                        <td align="right">
                                            <asp:Label ID="lbDramount" runat="server" Text='<%# Eval("dr_amount","{0:n}")%>'>   
                                            </asp:Label>
                                        </td>
                                        <td align="right">
                                            <asp:Label ID="lbCramount" runat="server" Text='<%# Eval("cr_amount","{0:n}")%>'>   
                                            </asp:Label>
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
                  <asp:SqlDataSource runat="server" ID="SqlDataEnt" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                                SelectCommand="SELECT tr.particulars,CASE WHEN [isDebtor] = 1 THEN  tr.amount  END AS dr_amount,CASE WHEN [isDebtor] =0 THEN  tr.amount  END AS cr_amount FROM transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id WHERE data_entry_id=@dataentryid ">
                                <SelectParameters>
                                    <asp:ControlParameter Name="dataentryid" ControlID="hidVoucherId" ConvertEmptyStringToNull="false" />
                                </SelectParameters>
                  </asp:SqlDataSource>
                  <asp:SqlDataSource runat="server" ID="sdsCommentLog" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                                SelectCommand="SELECT STRING_AGG(CONVERT(NVARCHAR(20), id), ', ') AS ids
                                FROM finascop_log WHERE order_order_id =@order_order_id AND status = 0;">
                                <SelectParameters>
                                    <asp:ControlParameter Name="order_order_id" ControlID="hidOrderOrderId" ConvertEmptyStringToNull="false" />
                                </SelectParameters>
                  </asp:SqlDataSource>
                    <div class="table-responsive">
                      <table id="cpMainContent_Table2" class="table table-bordered">
                          <thead>
                              <tr>
                                  <th class="py-2  border-top">
                                      <span id="cpMainContent_lbNartion">Narration</span>
                                  </th>
                              </tr>
                          </thead>
                        <tbody>
                          <tr>
                            <td>
                             <asp:Literal ID="lbNarration" runat="server"> </asp:Literal>
                            </td>
                          </tr>
                        </tbody>                                    
                      </table>  
                    </div>

                    <div class="docattachmentsec d-flex flex-wrap align-items-center">
                        <div class="doctitle w-100">
                            <span id="cpMainContent_spnAttachments">Attachments</span>
                        </div>
                        <div class="docattachment_wrap d-flex align-items-center px-2 w-100 mt-3">
                            <div class="controlbtn">
                                <button class="scroll-button left-button">◀</button>
                            </div>
                            <div class="docattachment_list d-flex align-items-center" style="height: 16vh;">
                                <asp:Repeater ID="rptrAttachments" runat="server"  EnableViewState="true">
                                    <ItemTemplate>
                                    <div data-toggle="modal" data-target="#DocumentViewerpopup" data-document_url='<%# Eval("DocumentURL") %>' data-docname='<%# Eval("DocumentName") %>' data-docnarration='<%# Eval("DocumentNarration") %>' class="document-item">
                                        <div ID="UploadFile" class="Uploadbox enabled">
                                            <div class="upload_qrqcode_wrap m-2 repeater_block-class" style="height: 150px; width: 150px;">
                                                <div id="docUpload_wap" class="uploadfile_wrap d-flex align-items-center justify-content-between w-100 h-100" style="background-color: #ececec;">
                                                    <div id="QRImgPreview_wap" class="qrimg_preview_block-class text-center align-items-center h-100 w-100" style="display: none; min-height: 100px; overflow: auto;">
                                                        <img id="QRImgPreview" src='<%# Eval("DocumentURL") %>' class="qrimg_preview_block-class" style="max-width: 100%;"  onerror="handleImageError(this)">
                                                        <div id="QRPdfPreview" class="qrpdf_preview_block-class align-items-center w-100 h-100" style="display: none; overflow: auto;" ></div>
                                                    </div>
                                                    <div id="docPreview_wap" class="doc_preview_block-class align-items-center w-100 h-100" style="display: none; overflow: auto;"></div>
                                                    <div id="ImgPreview_wap" class="img_preview_block-class text-center w-100 h-100" style="display: none; min-height: 100px; overflow: auto;">
                                                        <img id="ImgPreview" src="" class="preview_img" style="max-width: 100%; min-height: 100px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </ItemTemplate>
                                </asp:Repeater>
                                <div class="upload_qrqcode_wrap" style="transform: scale(1.32); margin-left: 4%; margin-top: 1.5%;">
                                    <div id="uploadBtn" class="upload_btnicon" data-toggle="modal" data-target="#DocumentUploadpopup" >
                                        <img src="/content/images/uplad_logo_icon.png" />
                                    </div>
                                </div>
                            </div>

                            <div class="controlbtn">
                                <button class="scroll-button right-button">▶</button>
                            </div>
                        </div>
                    </div><%--docattachmentsec--%>


                            <style>
                                .doctitle {
                                    line-height: 1.5;
                                    font-weight: 400;
                                    font-size: 13px;
                                    color:#FFF;
                                    padding:0.5rem 1rem;
                                    background-color: #13977f;
                                }
                                .controlbtn {
                                    width:40px;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                }
                                .docattachment_list {
                                    width: calc(100% - 80px);
                                    overflow: hidden;
                                    overflow-x: auto;
                                    scrollbar-width: none; /* For Firefox */
                                }

                                .docattachment_list::-webkit-scrollbar {
                                    display: none; /* For Chrome, Safari, and Edge */
                                }
                                   
                            </style>



                    <div class="table-responsive">



                    </div>
                    <div class="table-responsive d-none">
                      <table class="table table-bordered mt-2 mb-">
                        <tbody>
                          <tr>
                            <th align="center" class="py-2  text-center border-top"><label class="mb-0 text-center">Prepared by</label></th>
                            <th align="center" class="py-2  text-center border-top"><label class="mb-0 text-center">Approved by</label></th>
                          </tr>
                          <tr>
                            <td align="center">Anjana</td>
                            <td align="center">Sasi Narayan</td>
                          </tr>
                          <tr>
                            <td colspan="2" class="py-1">
                              <span class="d-inline-block w-100">
                                <b class="mr-1" style="border: 1px solid #c7c7c7; padding: 3px 4px; line-height: 11px; height: 18px; display: inline-block; border-radius: 3px; color: #929292;"><asp:Label ID="lblentrytype" runat="server"></asp:Label></b> - <span class="ml-1" style="opacity:0.5"><asp:Label ID="lblname" runat="server"></asp:Label></span>
                              </span>
                            </td>
                          </tr>
                        </tbody>
                      </table>  
                    </div>
                  </div>
                </div>  
                 <asp:PlaceHolder runat="server" ID="plccostcentre" >
               <div class="card-body mt-2">
                    <div class="p-3 d-flex align-items-center bg-gray-500 mb-3" style="color: #FFF;">
                        <h6 class="mb-">Cost Allocation</h6>
                    </div>
                                     
                    <div class="table-responsive mt-0">
                        <h6 class="px-3 m-0 py-3 bg-gray-200 tx-bold">Allocation from <asp:Literal runat="server" ID="ltrledger"></asp:Literal>:<asp:Literal runat="server" ID="ltrcostamount"></asp:Literal></h6>
                        <asp:ListView runat="server" ID="lvcostcentre" DataSourceID="SDScostcentre" OnDataBound="lvcostcentre_DataBound">
                       <LayoutTemplate>    
                        <table id="" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="py-2">cost centre</th>
                                    <th class="py-2">Debit</th>
                                    <th class="py-2">Credit</th>
                                </tr>
                            </thead>
                             <tr id="ItemPlaceholder" runat="server">
                             </tr>  
                             <tfoot>
                          <tr>
                            <th class="py-2" id="Td4">Total</th>
                            <th class="py-2" align="right" style="text-align:right;"><asp:Literal ID="ltrcostDrTotal" runat="server"></asp:Literal></th>
                            <th class="py-2" align="right" style="text-align:right;"><asp:Literal ID="ltrcostCRTotal" runat="server"></asp:Literal></th>
                          </tr>
                        </tfoot>                                                         
                        </table>
                           </LayoutTemplate>
                            <ItemTemplate>
                                   <tr class="TableData">
                                        <td style="line-height: 100%;">
                                            <asp:Label ID="lbPerticulars" runat="server" Text='<%# Eval("cost_centre_name")%>'>   
                                            </asp:Label>
                                        </td>
                                        <td align="right">
                                            <asp:Label ID="lbDramount" runat="server" Text='<%# Eval("dr_amount")%>'>   
                                            </asp:Label>
                                        </td>
                                        <td align="right">
                                            <asp:Label ID="lbCramount" runat="server" Text='<%# Eval("cr_amount")%>'>   
                                            </asp:Label>
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
                   <asp:SqlDataSource runat="server" ID="SDScostcentre" ConnectionString="<%$ connectionStrings:FinascopConnection %>"
                                SelectCommand="SELECT tr.particulars as ledgername,cost_centre_name,cc.amount,CASE WHEN cc.[isDebtor] = 1 THEN  cc.amount  END AS dr_amount,CASE
                                   WHEN cc.[isDebtor] =0 THEN  cc.amount  END AS cr_amount  FROM transactions tr inner join cost_centre_entries cc on  tr.id=transactions_id where tr.data_entry_id=@dataentryid ">
                                <SelectParameters>
                                    <asp:ControlParameter Name="dataentryid" ControlID="hidVoucherId" ConvertEmptyStringToNull="false" />
                                </SelectParameters>
                  </asp:SqlDataSource>
                </div>
               </asp:PlaceHolder>  
        </div>
       </div>

    </div>

                        <!-- Modal -->
                        <div class="modal fade col-12" id="DocumentUploadpopup" data-backdrop="static" data-keyboard="false"
                            tabindex="-1" aria-labelledby="DocumentUploadpopupLabel" aria-hidden="true" style="height: 100%; width: 100%; overflow: visible;">
                            <div class="modal-dialog modal-dialog-centered modal-lg" style="height: calc(100% - 15px); width: calc(100% - 15px);">
                                <div class="modal-content doument-upload-dialog">
                                    <div class="modal-body">
                                        <div class="modaltitle ">
                                            <h5 class="modal-title" id="DocumentUploadpopupLabel">Document Upload</h5>
                                            <asp:HiddenField ID="DU_hfdBlobURL" Value="" runat="server"  />
                                            <asp:HiddenField ID="DU_hfdKey" Value="" runat="server"  />
                                            <asp:HiddenField ID="DU_folder" Value = "" runat="server" ClientIDMode="Static"  />
                                            <asp:HiddenField ID="DU_modalDialogShow" Value = "false" runat="server" ClientIDMode="Static"  /> 
                                            <asp:HiddenField ID="DU_hfdHasSuspenseAccount" Value = "" runat="server" ClientIDMode="Static"  />
                                            <asp:HiddenField ID="DU_hfdHasDocumentAttached" Value = "" runat="server" ClientIDMode="Static"  />

                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>

                                            <div class="row">
                                                <div class="col-sm-9">
                                                    <div class="form-group">
                                                        <label class="mb-0">Document Name</label>
                                                        <asp:TextBox ID="DU_txtdocname" runat="server" CssClass="form-control document-name"></asp:TextBox>
                                                    </div>
                                                </div>
                                                <!--col-->

                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label class="mb-0">Document Type</label>
                                                        <asp:DropDownList ID="DU_dltype" CssClass="form-control file_type" runat="server">
                                                            <asp:ListItem Text="PDF" Value="1"></asp:ListItem>
                                                            <asp:ListItem Text="JPEG/JPG/PNG" Value="2"></asp:ListItem>
                                                        </asp:DropDownList>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label class="mb-0" id="DU_lblnarration">Narration</label>
                                                        <asp:TextBox ID="DU_tbxNarration" CssClass="form-control" Style="height: 150px; max-width: 100%;" TextMode="MultiLine" Rows="5" runat="server"></asp:TextBox>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-lg-8 d-flex flex-wrap flex-lg-nowrap">
	                                                <div ID="DU_UploadFile" class="Uploadbox enabled">
		                                                <div class="upload_qrqcode_wrap m-2 repeater_block-class" style="height: 150px; width: 150px;">
                                                            <asp:HiddenField ID="DU_DocumentID" Value = "DOC0" runat="server" ClientIDMode="Static"  />
                                                            <asp:HiddenField ID="DU_DocumentName"  Value = "Proof Document 1" runat="server" ClientIDMode="Static"  />
                                                            <asp:HiddenField ID="DU_DocumentURL" Value = "" runat="server" ClientIDMode="Static"  />
                                                            <asp:HiddenField ID="DU_Narration" Value = "" runat="server" ClientIDMode="Static"  />
                                                            <asp:HiddenField ID="DU_blobFileURL" Value = "" runat="server" ClientIDMode="Static"  />
                                                            <asp:HiddenField ID="DU_blobFileName" Value = "" runat="server" ClientIDMode="Static"  />
			                                                <div id="DU_docUpload_wap" class="uploadfile_wrap d-flex align-items-center justify-content-between w-100 h-100" style="background-color: #ececec;">
				                                                <div id="DU_actions" class="upload_btnicon m-1 upload_interface">
					                                                <div id="DU_documentupload_input" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100">
						                                                <a id="DU_pdfUpload" class="d-inline-block text-center w-100 addtext">
							                                                <img src="/content/images/loc_update.png">
						                                                </a>
						                                                <asp:FileUpload ID="DU_fupPdfFileUpload" runat="server" class="fup_block-class position-absolute w-100 fup_pdf_upload" style="opacity: 0; height: 38px;" accept="application/pdf" />
					                                                </div>

					                                                <div id="DU_imageupload_input" class="btn-group w-100 rounded-10 position-relative align-items-center uplodbtm h-100" style="display: none;">
						                                                <a id="DU_imgUpload" class="d-inline-block text-center w-100 addtext">
							                                                <img src="/content/images/loc_update.png">
						                                                </a>
						                                                <asp:FileUpload ID="DU_fupImageUpload" runat="server" class="fup_block-class position-absolute w-100 fup_img_upload" style="opacity: 0; height: 38px;" onchange="UploadFile(this)" accept="image/x-png,image/jpeg,image/jpg" />
					                                                </div>
				                                                </div>

				                                                <div id="DU_qrcode-section" class="qrqcode_sec m-1">
					                                                <img style="max-width: 55px;" src="/content/images/Qr_code.png">
				                                                </div>
                                                                 <div id="DU_QRImgPreview_wap" class="text-center align-items-center h-100  w-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                 <img id="DU_QRImgPreview" src='' class="" style="max-width: 100%;" onerror="handleImageErrorDU(this)">
                                                                 <div id="DU_QRPdfPreview" class="qrpdf_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                             </div>                                                           
                                                             <div id="DU_docPreview_wap" class="doc_preview_block-class align-items-center w-100 h-100 " style="display: none; overflow: auto;" ></div>
                                                             <div id="DU_ImgPreview_wap" class="img_preview_block-class text-center w-100 h-100" style="display: none; min-height: 100px; overflow: auto;">
                                                                 <img id="DU_ImgPreview" src="" class="preview_img" style="max-width: 100%; min-height: 100px;">
                                                             </div> 
			                                                </div>
                                                                <div  class="qrqcode_btnicon" style="display: none;">
                                                                    <button id="DU_close_btn" type="button" class="btn-close close btn-link" aria-label="" style="width: 5px; height: 5px; border:0px">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>    
                                                                    <img id="DU_imgUploadQrcode" runat="server" ClientIDMode="Static" src ="" class="img-upload-qrcode-class" style="width: 80%; height: 80%; position: absolute; top: 10%; left: 7%;">
                                                                </div> 
                                                                <div class="remove_preview_wrap">
                                                                     <asp:LinkButton ID="DU_lbnBlobDelete" runat="server" OnClick="DeleteFile">
                                                                        <span><i class="icon ion-trash-a"></i>Delete File</span>
                                                                     </asp:LinkButton>
                                                                </div>
		                                                </div><!--upload_qrqcode_wrap-->
	                                                </div><!--Uploadbox-->

                                                    <div class="col-12 col-lg-4 pl-lg-0" style="margin-left: 90%;">
                                                        <div class="d-flex justify-content-end align-items-end h-100">
                                                            <div class="modal-btn mb-2">
                                                                <asp:Button ID="DU_btnClose" CssClass="btn_same btn btn-secondary mr-2" runat="server" data-dismiss="modal" Text="Close" OnClientClick="$('#DU_modalDialogShow').val('false');" />
                                                                <asp:Button ID="DU_btnupload" CssClass="btn_same btn btn-primary" runat="server" OnClick="btnupload_Click" Text="Upload" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--row-->

                                        </div>
                                    </div> <!--modal-body-->
                                </div>
                                <!--modal-content-->
                            </div>
                            <!--modal-dialog-->
                        </div>
                        <!--modal-->
                        <!-- Modal -->
                        <div class="modal fade col-12" id="DocumentViewerpopup" data-backdrop="static" data-keyboard="false"
                            tabindex="-1" aria-labelledby="DocumentViewerpopupLabel" aria-hidden="true" style="height: 100%; width: 100%; overflow: visible;">
                            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-custom" style="height: calc(100% - 15px); width: calc(100% - 15px);">
                                <div class="modal-content doument-upload-dialog">

                                            <div class="modal-body">
                                                <div class="modaltitle">
                                                    <h5 class="modal-title" id="DocumentViewerpopupLabel">Document Viewer</h5>

                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                ' </div>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <div class="form-group">
                                                            <label class="mb-0">Document Name</label>
                                                            <asp:TextBox ID="txtdocname" runat="server" CssClass="form-control document-name"></asp:TextBox>
                                                        </div>
                                                    </div>
                                                    <!--col-->

                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label class="mb-0" id="lblnarration">Narration</label>
                                                            <asp:TextBox ID="tbxNarration" CssClass="form-control" Style="height: 150px; max-width: 100%;" TextMode="MultiLine" Rows="5" runat="server"></asp:TextBox>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <!-- Scrollable container -->
                                                        <div style="max-height: 500px; overflow-y: auto;">
                                                            <img id="popupDocumentImage" src="" alt="Document Preview" style="width: 100%;" onerror="handleImageErrorPopup()">
                                                            <div id="popupPdf" class="qrpdf_preview_block-class align-items-center w-100 h-100" style="display: none; overflow: auto;" ></div>
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
                    <!-- Modal -->
    
    <style>
        tr.selectrow  {
            background-color:#e6eff9;
        }
        .modal-dialog-custom {
            max-width: 60%; /* Adjust as needed, e.g., 90% of the viewport width */
            width: 50%; /* For explicit width control */
    `   }       
    </style>

    <script>

        $(document).ready(function () {
            $('#uploadBtn').click(function () {
                // Generate a unique file name

            });

 
        });


        function generateQRCodeBase64(url, imgSize) {
            //var qrcode = new QRCode(document.createElement("div"), {
            //    text: url,
            //    width: imgSize,
            //    height: imgSize
            //});

            var qrcode = new QRCode(document.createElement("div"), {
                text: fileuploadurl,
                width: imgSize, // Adjust the size as needed
                height: imgSize,
                colorDark: "#000000", // Dark color of the QR code
                colorLight: "#ffffff", // Light color of the QR code
                correctLevel: QRCode.CorrectLevel.H // Error correction level
            });

            // Show the QR code container
            $('#qrCodeContainer').show();

            var canvas = qrcode._el.querySelector('canvas');
            return canvas.toDataURL("image/png").split(',')[1]; // Return base64 string
        }


        function addChangeEventListener(e) {
            var $docPreview_wap = $(this).closest('.upload_qrqcode_wrap').find('.doc_preview_block-class');
            $docPreview_wap.innerHTML = "";
            //document.querySelector("#docPreview_wap").innerHTML = "";

            var file = e.target.files[0]
            if (file.type != "application/pdf") {
                alert(file.name + " is not a pdf file.")
                return
            }

            var $uploadWrap = $(this).closest('.upload_qrqcode_wrap');

            $uploadWrap.find('.uploadfile_wrap').removeClass('w-100');

            $uploadWrap.find('.upload_btnicon').hide();
            $uploadWrap.find('.qrqcode_sec').hide();

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
        $('#DU_UploadFile').find('.fup_pdf_upload').on("change", addChangeEventListener);


        $(document).ready(function () {
            // Attach a click event to the modal's close button
            $('.close[data-dismiss="modal"]').on('click', function () {
                $('#DU_modalDialogShow').val('false');
            });
        });

        $(document).on('click', '.document-item', function () {
            // Get the docURL from the clicked item
            const docURL = $(this).data('document_url');

            $('#popupDocumentImage').attr('src', docURL);
            $('#popupDocumentImage').show();
            $('#popupPdf').hide();

            const docName = $(this).data('docname');
            const docNarration = $(this).data('docnarration');

            $('#<%= txtdocname.ClientID %>').val(docName);
            $('#<%= tbxNarration.ClientID %>').val(docNarration);
        });

        function otherBtnClick(val) {
            var target = $(val).find('.btnvoucherrow.td_link').attr("href");
            console.log(target);
            window.location = target;
        }




    </script>

    <script type="text/javascript">

        var previouslySelectedOption = 1; // Variable to store the selected option
        $(function () {

            $(document).on('show.bs.modal', '#DocumentUploadpopup', function () {
                var qrCodeIcon = $('#DU_UploadFile').find('.qrqcode_btnicon');
                generateFileUrl(qrCodeIcon);
                $('#DU_modalDialogShow').val('true');

                $(".file_type").val(previouslySelectedOption).trigger('change');

                $('#DU_DocumentURL').val("");

                $('#DU_UploadFile').find(".repeater_block-class").removeClass("repeater-item-focus");
                $qr_code_icon = $('#DU_UploadFile').find('.qrqcode_btnicon');
                $qr_code_icon.hide();
                $qr_code_icon.removeClass("repeater-item-focus");


            });

            $(document).on('hidden.bs.modal', '#DocumentUploadpopup', function () {
                if (ftimer != null) {
                    // close timer.
                    clearInterval(ftimer);
                    ftimer = null;
                }
                $('#DU_modalDialogShow').val('false');
            });



            $(".file_type").on('change', function () {
                previouslySelectedOption = $(this).val(); // Save the selected value
                updateInputs(previouslySelectedOption);   // Update the modal based on the selection
            });

            // Function to update inputs based on the selected option
            function updateInputs(selectedOption) {
                if (selectedOption == "1") {
                    $('#DU_UploadFile').find('#DU_documentupload_input').show();
                    $('#DU_UploadFile').find('#DU_imageupload_input').hide();
                } else {
                    $('#DU_UploadFile').find('#DU_documentupload_input').hide();
                    $('#DU_UploadFile').find('#DU_imageupload_input').show();
                }
            }

            // Event to handle modal show
            $('#yourModalId').on('show.bs.modal', function () {
                if (previouslySelectedOption) {
                    $(".file_type").val(previouslySelectedOption); // Reapply the previously selected value
                    updateInputs(previouslySelectedOption);        // Ensure inputs reflect the correct state
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


</asp:Content>


