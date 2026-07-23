<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Logo.aspx.cs" Inherits="RetalineProAgent.Appearance.Logo" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/appearance">Appearance</a></li>
    <li class="breadcrumb-item active" aria-current="page">Logo</li>--%>
    <a href="/Navigations/Appearance"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">LOGO</h6>
        <p class="mb-0">Strengthen Your Brand</p>
    </div>
    </asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">

        <div class="card-body p-3 shadow_top">
            <%--<h6 class="mb-1"><i class="fa fa-edit"></i> Logos Settings</h6>--%>
            <p class="mb-2">Changes in the display settings can impact the looks and feel as well as components used.</p>
            <div class="row">
                <div class="col-12 col-sm-12">

                    <div class="partner_logo_upload">
                        <p class="lead mb-0">Add/Edit logo to be displayed</p>
                        <small>If added, the logo will be displayed in the header at the top left of the public website. In the absence of a logo, the name specified in the store's settings will be displayed.</small>
                        <br>
                        <br>
                        <div class="row justify-content-lg-start">
                            <div class="col-12 col-sm-6 col-lg-4 form-group mb-0 pb-3 pb-lg-5">
                                <h6 class="text-dark m-0">Logo for Website view</h6>
                                <p>This logo will be used to show the store brand in website. Preferable size of the logo is 250 X 50px</p>
                                <div class="uploadlogo_wrap">
                                    <span class="btn_upload" id="spnImgUpload1" runat="server">
                                        <asp:FileUpload ClientIDMode="Static" ID="Imgupload1" runat="server" data-target="#ImgPreview1" onchange="UploadFile(this)" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg" />
                                        <%--<input type="file" id="Imgupload123" title="" data-target="#ImgPreview1" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg"/>--%>
                                    </span>
                                    <div class="ImgPreview_wap">
                                        <asp:Image ClientIDMode="Static" runat="server" ID="ImgPreview1" CssClass="preview_img" Style="max-width: 250px; max-height: 50px;" />
                                        <%--<img id="ImgPreview1" src="" class="preview_img" />--%>
                                    </div>
                                    <div class="remove_preview_wrap">
                                        <asp:HiddenField ID="hidDelImg1" runat="server" />
                                        <asp:LinkButton runat="server" ID="lbtnDelImg1" CssClass="btn_rmv_remove" OnClick="lbtnDelImg_Click"><i class="icon ion-trash-a"></i> Delete Logo</asp:LinkButton>
                                        <%--<asp:Label ID="lblImgPreview1" runat="server" data-target="#ImgPreview1" data-file="#Imgupload1" class="btn_rmv_remove" ><i class="icon ion-trash-a"></i> Delete Logo</asp:Label>--%>
                                        <%--<span data-target="#ImgPreview1" data-file="#Imgupload1" class="btn_rmv_remove" ><i class="icon ion-trash-a"></i> Delete Logo</span>--%>
                                    </div>
                                </div>


                            </div>

                            <div class="col-12 col-sm-6 col-lg-4 form-group mb-0 pr-0 pb-5">
                                <h6 class="text-dark m-0">Logo for Responsive/App view</h6>
                                <p>This logo represents the store brand on mobile/tablets and should ideally be 40x40px</p>
                                <div class="uploadlogo_wrap">
                                    <span class="btn_upload" id="spnImgUpload2" runat="server">
                                        <asp:FileUpload ClientIDMode="Static" ID="Imgupload2" runat="server" data-target="#ImgPreview2" onchange="UploadFile(this)" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg" />
                                        <%--<input type="file" id="Imgupload2" title="" data-target="#ImgPreview2" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg"/>--%>
                                    </span>
                                    <div class="ImgPreview_wap">
                                        <asp:Image ClientIDMode="Static" runat="server" ID="ImgPreview2" CssClass="preview_img" Style="max-width: 40px; max-height: 40px;" />
                                        <%--<img id="ImgPreview2" src="" class="preview_img" />--%>
                                    </div>
                                    <div class="remove_preview_wrap">
                                        <asp:HiddenField ID="hidDelImg2" runat="server" />
                                        <asp:LinkButton runat="server" ID="lbtnDelImg2" CssClass="btn_rmv_remove" OnClick="lbtnDelImg_Click"><i class="icon ion-trash-a"></i> Delete Logo</asp:LinkButton>
                                        <%--<asp:Label ID="lblImgPreview2" runat="server" data-file="#Imgupload2" data-target="#ImgPreview2" class="btn_rmv_remove" ><i class="icon ion-trash-a"></i> Delete Logo</asp:Label>--%>
                                        <%--<span data-target="#ImgPreview2" data-file="#Imgupload2" class="btn_rmv_remove" ><i class="icon ion-trash-a"></i> Delete Logo</span>--%>
                                    </div>
                                </div>


                            </div>

                            <div class="col-12 col-sm-6 col-lg-4 form-group mb-0 pr-0 pb-5">
                                <h6 class="text-dark m-0">Fav Icon for Website</h6>
                                <p>Upload the icon or png file for showing the Fav Icon in browser</p>
                                <div class="uploadlogo_wrap">
                                    <span class="btn_upload" id="spnImgUpload3" runat="server">
                                        <asp:FileUpload ClientIDMode="Static" ID="Imgupload3" runat="server" data-target="#ImgPreview3" onchange="UploadFile(this)" class="input-img" accept=".ico,image/x-icon" />
                                    </span>
                                    <div class="ImgPreview_wap">
                                        <asp:Image ClientIDMode="Static" runat="server" ID="ImgPreview3" CssClass="preview_img" Style="max-width: 40px; max-height: 40px;" />
                                    </div>
                                    <div class="remove_preview_wrap">
                                        <asp:HiddenField ID="hidDelImg3" runat="server" />
                                        <asp:LinkButton runat="server" ID="lbtnDelImg3" CssClass="btn_rmv_remove" OnClick="lbtnDelImg_Click"><i class="icon ion-trash-a"></i> Delete</asp:LinkButton>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row">

                            <div class="col-12 form-group">
                                <div class="input-group w-auto d-inline-block">
                                    <asp:Button runat="server" Text="Save changes" ID="btnUpload" Style="display: none;" CssClass="btn btn-success float-right" OnClick="btnUpload_Click" />
                                    <!-- &nbsp; <br><span id="cpMainContent_lblLogoMessage" style="font-weight:bold;"></span> -->

                                </div>
                            </div>

                        </div>

                        <script>
                          function readURL(input, imgControlName) {
                           if (input.files && input.files[0]) {
                            var reader = new FileReader();
                           reader.onload = function (e) {
                           $(imgControlName).attr('src', e.target.result);
                           }
                            reader.readAsDataURL(input.files[0]);
                           }
                         }

                         $(".input-img").change(function () {
                         const fileUpload = this;
                         const file = fileUpload.files[0];
                         const fileName = file.name.toLowerCase();
                         const fileType = file.type;

                       // Favicon validation
                       if (fileUpload.id === "Imgupload3") {
                       const isValidIco = fileName.endsWith(".ico") && fileType === "image/x-icon";
                       if (!isValidIco) {
                       alert("Only .ico files are allowed for favicon.");

                       // Reset UI immediately
                        const $wrap = $(fileUpload).closest('.uploadlogo_wrap');
                        $wrap.find('.input-img').val('');
                        $wrap.find('img').attr('src', '');
                        $wrap.find('.btn_upload').removeClass('rmvbg');
                        $wrap.find('.btn_rmv_remove').removeClass('rmv').addClass('d-none');
                        return; // Stop further execution — do not call readURL or update UI
                       }
                     }

        // Proceed with normal image preview and UI updates
        var imgControlName = $(this).data('target');
        readURL(this, imgControlName);
        $(this).closest('.uploadlogo_wrap').find('.btn_rmv_remove').addClass('rmv').removeClass('d-none');
        $(this).closest('.uploadlogo_wrap').find('.btn_rmv_remove').attr('hiddenfld', 1);
        $(this).parent('.btn_upload').addClass('rmvbg');
    });

    $(".btn_rmv_remove").click(function (e) {
        //var hiddenfld = $(this).attr('hiddenfld');
        //console.log(hiddenfld)
        if (!confirm('Are you sure you want to delete this image?')) {
            return false;
        }
        $(this).addClass('processing_loader')
        return true;
        //if (hiddenfld && hiddenfld != '') {
        //    $(this).removeAttr('hiddenfld');
        //}
        //e.preventDefault();
        //var imgFile = $(this).data('target');
        //var imgSelect = $(this).data('file');
        //$(imgSelect).val("");
        //$(imgFile).attr("src", "");
        //$(this).removeClass('rmv');
        //$(imgSelect).parent('.btn_upload').removeClass('rmvbg');
    });

    function UploadFile(fileUpload) {
        if (fileUpload.value !== '') {
            const file = fileUpload.files[0];
            const fileName = file.name.toLowerCase();
            const fileType = file.type;

            // Only validate for .ico file when fileUpload is for the favicon (Imgupload3)
            if (fileUpload.id === "Imgupload3") {
                const isValidIco = fileName.endsWith(".ico") && fileType === "image/x-icon";
                if (!isValidIco) {
                    alert("Only .ico files are allowed for favicon.");

                    const $wrap = $(fileUpload).closest('.uploadlogo_wrap');

                    // Clear file input and reset preview image
                    $wrap.find('.input-img').val('');
                    $wrap.find('img').attr('src', '');
                    $wrap.find('.btn_upload').removeClass('rmvbg');
                    $wrap.find('.btn_rmv_remove').addClass('d-none');
                    $wrap.find('.btn_rmv_remove').removeClass('rmv');
                    $wrap.find('.ImgPreview_wap').removeClass('d-none');

                    return; // Stop further execution
                }
            }

            // Trigger upload if valid
            $(fileUpload).closest('div').addClass('processing_loader');
            document.getElementById("<%=btnUpload.ClientID %>").click();
        }
    }
                        </script>


                        

                    </div>

                </div>
            </div>

        </div>

    </div>
    



</asp:Content>

