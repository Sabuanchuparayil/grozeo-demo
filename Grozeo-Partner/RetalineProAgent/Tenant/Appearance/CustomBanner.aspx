<%@ Page Language="C#" AutoEventWireup="true" EnableViewState="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="CustomBanner.aspx.cs" Inherits="RetalineProAgent.Appearance.CustomBanner" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Tenant/Appearance/Graphics"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle m-0">Custom Banner</h6>
        <p class="mb-0">Engaging Visuals</p>
    </div>
</asp:Content>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <script type="text/javascript" src="/api/home/GetGraphicsObjects"></script>
    <script type="text/javascript">
        // Embed server-side data
        var jsonData = {
            "storename": "ABC Stores",
            "address": "test address, test city, test state",
            "email": "test@email.com",
            "websiteurl": "mywebsite.com",
            "phone": 9898989898,
            "Images": [
                {
                    "name": "Template",
                    "type": 1,
                    "url": "<%= HiddenImageUrl %>"
                },
                {
                    "name": "QRCode",
                    "type": 2,
                    "url": "<%= HiddenKey %>"
                },
                {
                    "name": "Big-Logo",
                    "type": 7,
                    "url": "https://partner.dev.grozeo.in/Content/canvas/images/big-logo.jpg"
                },
                {
                    "name": "Small-logo",
                    "type": 7,
                    "url": "https://partner.dev.grozeo.in/Content/canvas/images/small-logo.jpg"
                }
            ]
        };

        // Access jsonData.Images[0].url in your JavaScript as needed
        var templateImageUrl = jsonData.Images[0].url;
        console.log(templateImageUrl);

        var secondImageUrl = jsonData.Images[1].url;
        console.log(secondImageUrl);

        // Log HiddenKey value to the console
        console.log("HiddenKey value: ", "<%= HiddenKey %>");
    </script>

   <%--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
      integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
      integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />--%>
   <%--<link rel="stylesheet" href="/Content/canvas/css/style.css">--%>
   <link rel="stylesheet" href="/Content/canvas/css/googlefonts.css">

</asp:Content>


 
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <%--<a href="/Tenant/Appearance/CustomBanner"><img src="/Content/images/Template-Editor.jpg" /></a>--%>

    <style>
        .icon-btn {
            cursor: pointer;
        }

        .w-35 {
            width: 35px;
        }

        .h-35 {
            height: 35px;
        }

        .source-img {
            width: auto;
            max-width: 100%;
        }
    </style>

    <div class="row row-sm">
        <div class="col-12">
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="row row-sm">
                        <div class="col-12 col-md-4 col-lg-3 mb-2 mb-md-0 d-flex align-items-center">
                            <input type="hidden" id="hiddenImageData" runat="server" clientidmode="Static" />
                            
                            <a class="icon-btn w-35 h-35 d-flex justify-content-center align-items-center border mx-1" onclick="addtextBox()">
                                <i class="fa-solid fa-font fa-lg"></i>
                            </a>
                            <a class="icon-btn w-35 h-35 d-flex justify-content-center align-items-center border mx-1" onclick="deleteObject()">
                                <i class="fa-regular fa-trash-can fa-lg"></i>
                            </a>
                            <input class="w-35 h-35 d-flex justify-content-center align-items-center border mx-1 position-relative bg-transparent" type="color" id="text-color" value="#ff0000">
                            <a class="icon-btn w-35 h-35 d-flex justify-content-center align-items-center border mx-1" onclick="drawCircle()">
                                <i class="fa-regular fa-circle fa-lg"></i>
                            </a>
                            <a class="icon-btn w-35 h-35 d-flex justify-content-center align-items-center border mx-1" onclick="drawRectangle()">
                                <i class="fa-regular fa-square fa-lg"></i>
                            </a>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2 mb-sm-0 d-flex align-items-center">
                            <select id="font-family" class="canvas-font float-end form-control select2 mr-2">
                                <option value="FontFamily">Font Family</option>
                            </select>
                            <select id="selElement" class="canvas-font float-end form-control select2 ml-2">
                                <option value="InsertElement">Insert Element</option>
                                <option value="QRCode">QR Code</option>
                                <option value="UserLogo">Logo</option>
                                
                            </select>
                            <%--<input id="btn-select" readonly class="canvas-font float-start ms-2" value="Insert element" onfocusout="hideImagePicker()">
                            <ul id="image-picker" class="image-picker">
                            </ul>--%>
                            <%--<asp:DropDownList ID="selElement" CssClass="canvas-font float-end form-control select2 ml-2" runat="server">
                                <asp:ListItem Text="Insert element" Value="InsertElement"></asp:ListItem>
                                <asp:ListItem Text="QR Code" Value="QR Code"></asp:ListItem>
                                <asp:ListItem Text="Logo" Value="Logo"></asp:ListItem>
                            </asp:DropDownList>--%>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3 d-flex align-items-center">
                            <div class="d-flex align-items-center zoom-levewrap">
                                <%--<input class="zoom-level mr-2" type="range" id="zoom" min="50" value="100" max="150"
                                    onchange="changeZoom(this.value)">--%>
                                <input class="zoom-level mr-2" type="range" id="zoom" min="20" max="100"
                                    onchange="changeZoom(this.value)">
                                <div class="zoom-val" id="zoom-val">100 %</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex align-items-center mt-3 mt-lg-0 justify-content-lg-end">
                          <div class="viewDesign d-flex justify-content-start justify-content-lg-end align-items-center mr-3 flex-wrap">
                            View Design
                              <div class="viewimg mx-wd-100p ml-2">
                                  <img id="viewImage" data-toggle="modal" data-target="#viewDesign" src="" runat="server" style="max-width: 40px; max-height: 24px;"/>
                              </div>

                              <div class="viewdesignimage p-2 bg-white rounded">
                                  <img id="designImage" src="" runat="server" />
                              </div>
                            </div>
                            <div class="grph_btn">
                                <asp:LinkButton Text="Save" runat="server" ID="lbtnUpload" OnClick="lbtnUpload_Click" OnClientClick="downloadImage();" CssClass="btn btn-primary">Save</asp:LinkButton>
                            </div>
                        </div>

                    </div>
                </div>
                <!--card-header-->

                <div class="card-body">
                    <div class="row row-sm">
                        <%--<div class="col-12 col-lg-9 card p-2">
                            <div id="canvas-base" class="canvas-base" oncontextmenu="return false">--%>
                        <div class="col-12 card p-2" style="max-height:600px;">
                            <div id="canvas-base" class="canvas-base d-flex w-100 justify-content-center" oncontextmenu="return false">
                                <canvas id="canvas" runat="server" clientidmode="Static" width="400" height="300"></canvas>
                            </div>
                            <asp:HiddenField runat="server" ID="hiddenImageUrl" ClientIDMode="Static" />
                        </div>

                        <div class="col-12 col-lg-3 card border-left">
                            <%--<div class="row row-sm" id="template-images">
                                <div class="col-12 p-2 text-center">
                                    <b>Click to insert element</b>
                                </div>
                            </div>--%>
                            <asp:HiddenField runat="server" ID="hiddenKey" ClientIDMode="Static" />
                            <%--<div class="row row-sm" id="template-text">
                                <div class="col-12 p-2 text-center"><b>Store Address</b></div>
                            </div>--%>
                        </div>
                    </div>
                </div>
                <!--card-body-->
            </div>
        </div>
    </div>


<div class="container-fluid pt-2 pb-2" runat="server">
        <div class="row mt-2 mb-2 px-4">
            <div class="col card p-2 d-block">
            
         </div>
      </div>
      <div class="row px-4">
         <%--<div class="col-9 card p-2">
            <div id="canvas-base" class="canvas-base" oncontextmenu="return false">
               <canvas id="canvas"></canvas>
            </div>
         </div>--%>
          
          <div class="col-9 card p-2">
              <%--<div id="canvas-base" class="canvas-base" oncontextmenu="return false">--%>
              <%--<img id="imgTemplate" runat="server" alt="Template Image" />--%>
              <%--<div id="canvas-base" class="canvas-base" ondrop="drop(event)" ondragover="allowDrop(event)">--%>
              <%--<div id="canvas-base" class="canvas-base" oncontextmenu="return false">
                  <canvas id="canvas" runat="server" ClientIDMode="Static" width="400" height="300"></canvas>
              </div>
              <asp:HiddenField runat="server" ID="hiddenImageUrl" ClientIDMode="Static" />--%>
              <%--</div>--%>
              <%--<div id="dropArea" ondrop="drop(event)" ondragover="allowDrop(event)">
                  <img id="imgTemplate" runat="server" alt="Template Image" src="<%= GetTemplateUrl() %>" draggable="true" ondragstart="drag(event)" />
              </div>--%>
          </div>
         <div class="col-3 card">
            <%--<div class="row" id="template-images">
               <div class="col-12 p-2 text-center"><b>Click to insert element</b>
               </div>
            </div>
             <asp:HiddenField runat="server" ID="hiddenKey" ClientIDMode="Static" />
            <div class="row" id="template-text">
               <div class="col-12 p-2 text-center"><b>Store Address</b></div>
            </div>--%>
         </div>
      </div>
   </div>

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
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Cancel</button>
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

            <button type="button" class="btn btn-primary pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->



    <%--<div class="modal fade" id="viewDesign" tabindex="-1" role="dialog" aria-labelledby="viewDesignTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    
                </div>
            </div>
        </div>
    </div>--%>



    <%--<div class="modal fade" id="viewDesign" tabindex="-1" role="dialog" aria-labelledby="viewDesignTitle" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">View Image</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="" class="img-fluid" style="max-width: 100%; max-height: 70vh;" />
                                    </div>
                                </div>
                            </div>
                        </div>--%>

    <script type="text/javascript">
        $(function () {

            // hide modal with effect
            $('#modaldemo4').on('hidden.bs.modal', function (e) {
                window.location.href = "/Tenant/Appearance/CustomisedGraphics";
            });
        });
    </script>
    
   
   <script src="/Content/canvas/js/fabric.min.js"></script>
   <script src="/Content/canvas/js/fontfaceobserver.js"></script>
   <script src="/Content/canvas/js/app.js"></script>

    <script type="text/javascript">
        function downloadImage() {
            canvas.discardActiveObject().renderAll();
            let dt = canvas.toDataURL({
                format: 'png',
                quality: 1,
            });

            // Use hiddenImageUrl.Value directly
            var imageUrl = '<%= HiddenImageUrl %>';
            console.log(imageUrl);
            document.getElementById('<%= hiddenImageData.ClientID %>').value = dt;

            // Use hiddenKey.Value
            var qrUrl = '<%= HiddenKey %>';
            console.log(qrUrl);

            // Run your code after the page is loaded
            window.onload = function () {
                // Log HiddenKey value again (just to make sure)
                console.log("HiddenKey value: ", "<%= HiddenKey %>");
            };
            alert("Save the design!");
            
        }
        function redirectToAnotherPage() {
            window.location.href = "/Tenant/Appearance/CustomisedGraphics"; 
        }

        // Get the template URL from the server-side code
        var templateUrl = '<%= GetTemplateUrl() %>';

            // Load the image onto the canvas
            var canvass = document.getElementById('canvas');
            var context = canvass.getContext('2d');
            var image = new Image();
            
            image.onload = function () {
                context.drawImage(image, 0, 0);
            };
            
            image.src = templateUrl;

            // Server-side method to get the template URL
            function GetTemplateUrl() {
                return '<%= ResolveUrl("~/Tenant/Appearance/CustomBanner?id=" + Request.QueryString["id"]) %>';
        }
        var dynamicQrCodeUrl = '<%= HiddenKey %>';
    </script>
 

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

</asp:Content>

<%--<asp:Content ContentPlaceHolderID="cntTenantPendingTasks" runat="server"></asp:--%>
