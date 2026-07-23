<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" EnableViewState="true" Title="Mobile App" CodeBehind="MobileApp.aspx.cs" Inherits="RetalineProAgent.MobileApp" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Delivery"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
            <h6 class="slim-pagetitle">Android Mobile Application</h6>
    <p class="mb-0">Manage your android mobile application here</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <asp:PlaceHolder runat="server" ID="plcCreateApp">
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row row-sm">
                        <div class="col-12 col-lg-7">
                            <div class="form-group">
                                <label for="lblName" class="tx-dark mb-1 w-100">Name of App (30 Characters) <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtNameApp" runat="server" CssClass="form-control"
                                    placeholder="Enter name of app" autocomplete="nofill"
                                    Enabled="true" MaxLength="30" oninput="updateNameCharacterCount()" required/>
                                <span id="charCountNameApp" class="character-count"></span>
                                <asp:LinkButton runat="server" ID="nameSuggest" Text="Suggest"
                                    CssClass="tx-10 mg-t-2 float-right" Enabled="true" OnClientClick="suggestName(); return false;">
                                </asp:LinkButton>
                                <asp:HiddenField ID="hfAppPackage" runat="server" />
                            </div>

                            <div class="form-group">
                                <label for="lblName" class="tx-dark mb-1 w-100">Logo <span class="tx-danger">*</span></label>
                                <div class="applogo_list_wrap d-flex ">

                                    <div class="uploadlogo_wrap applogoupload_wrap w-100">
                                        <span id="spnImgUpload1" class="btn_upload" runat="server">
                                            <input type="file" name="" runat="server" id="applogoupload" data-val="0" data-target="#applogouploadPreview" class="input-img main-logo" accept=".png" required>
                                        </span>
                                        <div class="ImgPreview_wap">
                                            <%--<asp:Image ID="applogouploadPreview" CssClass="preview_img" Style="max-width: 100%; max-height: 100%;" runat="server" ImageUrl="" />--%>
                                            <img id="applogouploadPreview" class="preview_img" style="max-width: 100%; max-height: 100%;" src="">
                                        </div>
                                        <div class="remove_preview_wrap">
                                            <input type="hidden" name="" id="">
                                            <div id="" data-sizes=".log_variant" data-target="#applogouploadPreview" data-file="#applogoupload" class="btn_rmv_remove">
                                                <i class="icon ion-compose"></i>Change Logo
                             
                                            </div>
                                        </div>
                                        <span class="applogo_sugt_dimension text-danger">( 256px x 256px )</span>
                                    </div>
                                    <!--applogoupload_wrap-->
                                    <div class="applogo_list border">
                                        <img id="imgLogo48" class="log_variant" onerror="this.src='/Content/images/applogo.svg'" src="" style="max-width: 22px;">
                                        <span class="applogo_sugt_dimension">( 48px x 48px )</span>
                                    </div>
                                    <div class="applogo_list border">
                                        <img id="imgLogo72" class="log_variant" onerror="this.src='/Content/images/applogo.svg'" src="" style="max-width: 32px;">
                                        <span class="applogo_sugt_dimension">( 72px X 72px)</span>
                                    </div>
                                    <div class="applogo_list border">
                                        <img id="imgLogo96" class="log_variant" onerror="this.src='/Content/images/applogo.svg'" src="" style="max-width: 42px;">
                                        <span class="applogo_sugt_dimension">( 96px X 96px)</span>
                                    </div>
                                    <div class="applogo_list border">
                                        <img id="imgLogo144" class="log_variant" onerror="this.src='/Content/images/applogo.svg'" src="" style="max-width: 52px">
                                        <span class="applogo_sugt_dimension">( 144px X 144px)</span>
                                    </div>
                                    <div class="applogo_list border">
                                        <img id="imgLogo192" class="log_variant" onerror="this.src='/Content/images/applogo.svg'" src="" style="max-width: 100%;">
                                        <span class="applogo_sugt_dimension">( 192px X 192px)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="splash_screen_wrap row row-sm mt-5">
                  
                        <div class="splash_screen_list col-12 col-md-6">
                          <label for="SplashScreenOne" class="tx-dark mb-1 w-100">Splash Screen <span class="tx-danger">*</span></label>
                          <div class="uploadlogo_wrap SplashScreenOne_wrap h-100 mb-4">
                            <span id="" class="btn_upload">
                                <input type="file" name="" runat="server" id="SplashScreenOneupload" data-val="1" data-target="#SplashScreenOnePreview" class="input-img splash-screen" accept="image/x-png" required>
                              <span id="image_size_dimension">(size 1080px X 1920px)</span>
                            </span>
                            <div class="ImgPreview_wap">
                              <img id="SplashScreenOnePreview" class="preview_img" style="max-width: 100%; max-height: 100%;" src="">
                  
                            </div>
                            <div class="remove_preview_wrap"><input type="hidden" name="delImg" id="hidDelImg2">
                              <span id="" data-target="#SplashScreenOnePreview" data-file="#SplashScreenOneupload" class="btn_rmv_remove">
                                <i class="icon ion-trash-a"></i> Delete Image
                              </span>
                  
                            </div>
                          </div> <!--SplashScreenOne_wrap-->
                          
                        </div><!--splash_screen_list-->
                  
                        <div class="splash_screen_list col-12 col-md-6">
                            <div class="form-group">
                                <label for="txtHeadLine" class="tx-dark mb-1 w-100">
                                    Head Line (80 Characters) <span class="tx-danger">*</span>
                                <asp:LinkButton runat="server" ID="suggestHeadLine" Text="Suggest" Enabled="true" CssClass="tx-10 mg-t-2 float-right" OnClientClick="suggestHeadLine(); return false;"></asp:LinkButton>
                                </label>
                                <asp:TextBox ID="txtHeadLine" TextMode="MultiLine" runat="server" Enabled="true" CssClass="form-control" Height="50px" MaxLength="80" oninput="updateCharacterCount()" required/>
                                <%--<asp:RequiredFieldValidator ID="rfvHeadLine" Visible="false" runat="server" ControlToValidate="txtHeadLine" Display="Dynamic" ErrorMessage="Head line is required" InitialValue="" ForeColor="Red" ValidationGroup="MobileAppValid" />--%>
                                <span id="characterCount" class="tx-12 mg-t-2 float-right"></span>
                            </div>
                            <div class="form-group mt-5">
                                <label for="txtDescription" class="tx-dark mb-1 w-100">
                                    Description (4000 Characters) <span class="tx-danger">*</span>
                                <asp:LinkButton runat="server" ID="suggestDescription" Visible="true" Text="Suggest" CssClass="tx-10 mg-t-2 float-right" OnClientClick="suggestDescription(); return false;"></asp:LinkButton>
                                </label>
                                <asp:TextBox ID="txtDescription" runat="server" CssClass="form-control" Enabled="true" Height="136px" TextMode="MultiLine" MaxLength="4000" oninput="updatedesCharacterCount()" required  ValidateRequestMode="Disabled"/>
                                <%--<asp:RequiredFieldValidator ID="rfvDescription" Visible="false" runat="server" ControlToValidate="txtDescription" Display="Dynamic" ErrorMessage="Description is required" InitialValue="" ForeColor="Red" ValidationGroup="MobileAppValid" />--%>
                                <span id="descriptionCount" class="tx-12 mg-t-2 float-right"></span>
                            </div>

                          <div class="input-group w-auto d-inline-block mb-3 mb-lg-0">
                               <a href="/Navigations/Appearance" class="btn btn-secondary">Cancel</a>
                              <asp:Button runat="server" ID="btnUpload" OnClick="btnUploadMobile_Click" Enabled="true" CssClass="btn btn-primary btn-inline-block mx-2" Text="Submit" />
                          </div>
                        </div><!--splash_screen_list-->
                      </div><!--splash_screen_wrap-->
                        </div><!--col-lg-7-->

                        <div class="col-12 col-lg-5 mb-3">

                            <div class="mobileappview">
                                <%--<h5>Preview your Mobile App</h5>--%>
                                <div class="device">
                                    <div class="iphonex" id="phone" runat="server" visible="true">
                                        <div class="device-border-wrap" id="progressMessage" runat="server" visible="true">
                                            <span class="appmessage" id="createApp" runat="server" visible="true">
                                                <h4 class="tx-18 mb-3 px-2">Create your own App</h4>
                                                <p class="tx-14 px-2">Grozeo can create your branded mobile app within minutes. Update the details and submit</p>
                                            </span>
                                            
                                            
                                            <img alt="device border" src="/Content/images/iPhone_X.png" class="device-border">
                                        </div>
                                    </div>
                                    <%--<div class="screen" id="appScreen" runat="server" visible="true">
                                        <div class="device_content" tabindex="0">
                                             <iframe runat="server" id="frmmobileview"></iframe> 
                                        </div>
                                    </div>--%>

                                </div>
                            </div>
                            <!--mobileappview-->
                        </div>
                        <!--col-lg-5-->
                    </div>
                </div>
                <!--card body-->
            </div>
            <!--card-->
        </div>
    </div>
    </asp:PlaceHolder>
    
    <asp:PlaceHolder runat="server" ID="plcAndroidApp">
        <div class="row row-sm">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row row-sm">
                            <div class="col-12 mb-3">
                                <div class="mobileappview">
                                    <%--<h5>Preview your Mobile App</h5>--%>
                                    <div class="device">
                                        <div class="iphonex" id="android" runat="server" visible="true">
                                            <div class="device-border-wrap" id="progressMessages" runat="server" visible="true">
                                                <span class="appmessage" id="appProcessing" runat="server" visible="false">
                                                    <div class="lodingbusy" id="divlodingbusy" runat="server">
                                                        <div class="dot"></div>
                                                        <div class="dot"></div>
                                                        <div class="dot"></div>
                                                    </div>
                                                    <h4 class="tx-18 mb-3 px-2">
                                                        <asp:Label ID="lblAppStatus" runat="server" CssClass="tx-18 mb-3 px-2">Your App is being build</asp:Label>
                                                    </h4>
                                                    <p class="tx-14 px-2">
                                                        <asp:Label ID="lblAppMessage" runat="server" CssClass="tx-14 px-2">We are building components of your mobile app.. worth waiting.. please check back after a couple of minutes.</asp:Label>
                                                    </p>
                                                   <%-- <h4 class="tx-18 mb-3 px-2">Your App is being build</h4>
                                                    <p class="tx-14 px-2">We are building components of your mobile app.. worth waiting.. please check back after a couple of minutes.</p>--%>
                                                   <%-- <a href="/Tenant/MobileApp" class="tx-center btn btn-secondary" runat="server" id="statusCheck" visible="true">Check Status</a>--%>
                                                   <asp:LinkButton ID="statusCheck" class="tx-center btn btn-secondary" runat="server" Visible="true" OnClick="statusCheck_Click">Check Status</asp:LinkButton>
                                                </span>
                                                <span class="appmessage" id="congratsMessage" runat="server" visible="false">
                                                    <h4 class="tx-20 px-3 mt-5">Congratulations, your App is ready to view</h4>
                                                </span>
                                                <img alt="device border" src="/Content/images/iPhone_X.png" class="device-border">
                                            </div>
                                        </div>
                                        <div class="screen" id="appScreen" runat="server" visible="true">
                                            <div class="device_content" tabindex="0">
                                                <iframe runat="server" id="frmmobileview"></iframe>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <!--mobileappview-->
                            </div>
                            <!--col-lg-6-->
                        </div>
                    </div>
                    <!--card body-->
                </div>
                <!--card-->
            </div>

            <div class="col-12 col-lg-6 pt-3 pt-lg-0">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="row row-sm">
                            <div class="col-12">
                                <div class="mb-3">
                                    <h6 class="tx-dark mb-0">Android Mobile Application</h6>
                                    <p class="tx-dark mb-0">Status</p>
                                    <asp:Label runat="server" ID="lblBuildProgress" CssClass="tx-dark" Text="Your App is being built" Visible="false" Style="font-size: 12px;"></asp:Label>
                                    <asp:Label runat="server" ID="lblBuildComplete" CssClass="tx-dark" Text="Congratulations, your App is ready to view" Visible="false" Style="font-size: 12px;"></asp:Label>
                                </div>
                                <div class="mb-3">
                                    <label class=" mb-0 w-100">Name of the App</label>
                                    <asp:Label runat="server" ID="lblNameOfApp" CssClass="tx-dark" Style="font-size: 12px;"></asp:Label>
                                </div>
                                <div class="mb-3">
                                    <label class=" mb-0 w-100">Headline</label>
                                    <asp:Label runat="server" ID="lblHeadline" CssClass="tx-dark" Style="font-size: 12px;"></asp:Label>
                                </div>
                                <div class="form-group">
                                    <label for="txtSearch1" class="tx-dark mb-1 w-100">Description</label>
                                    <asp:Label runat="server" ID="lblDescription" CssClass="tx-dark" Style="font-size: 12px;"></asp:Label>
                                </div>
                            </div>
                            <!--col-12-->
                        </div>
                        <!--row-->
                        <div class="row row-sm">
                            <div class="col-12 col-sm-6">
                                <label for="txtSearch1" class="tx-dark mb-1 w-100">Logo</label>
                                <div class="border w-100 d-flex justify-content-center align-items-center p-1" style="height: 100px;">
                                    <img id="imageMainLogo" runat="server" style="max-width: 100%; max-height: 85px;" src="" />
                                    <%--<img id="imageLogo" runat="server" style="max-width: 100%; max-height: 100px;" src="index_files/applogo.svg">--%>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                <label for="txtSearch1" class="tx-dark mb-1 w-100">Splash Screen Image</label>
                                <div class="border w-100 d-flex justify-content-center align-items-center p-1" style="height: 100px;">
                                    <img id="imageSplashScreen" runat="server" style="max-width: 100%; max-height: 85px;" src="" />
                                    <%--<img style="max-width: 100%; max-height: 100px" src="index_files/applogo.svg">--%>
                                </div>
                            </div>

                            <div class="col-12 tx-center">
                                <div class="input-group w-auto d-inline-block mt-4">
                                    <asp:Button runat="server" ID="btnDownload" CssClass="btn btn-primary btn-inline-block mx-2" OnClick="btnDownload_Click" Text="Download" Visible="true" />
                                    <%--<a href="" class="btn btn-primary mx-2" runat="server" visible="false">Approve</a>--%>
                                    <asp:Button runat="server" ID="btnRebuild" CssClass="btn btn-primary btn-inline-block mx-2" OnClick="btnRebuild_Click" Text="Rebuild" Visible="true" OnClientClick="return confirm('Are you sure you want to rebuild android app?');" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--col-lg-6-->

        </div>
    </asp:PlaceHolder>



    <style>
        .lodingbusy .dot {
            width: 10px;
            height: 10px;
            background: rgba(151, 151, 151, 0.6);
            animation: dotanim 1.8s infinite ease-in-out;
        }
    </style>
    
    <script>
    // Function to handle the "Suggest" button click for the Name of App field
    function suggestName() {
        // Set a specific word when the "Suggest" button is clicked
        $('#<%= txtNameApp.ClientID %>').val('<%= this.CurrentUser.StoreGroupName %>');
        <%--$('#<%= txtNameApp.ClientID %>').val('SuggestedWord');--%>

        // Optionally, you can update the character count
        updateNameCharacterCount();
    }

    // Function to update the character count for the Name of App field
    function updateNameCharacterCount() {
        var textBox = document.getElementById('<%= txtNameApp.ClientID %>');
        var counter = document.getElementById('charCountNameApp');
        var maxLength = textBox.maxLength;

        // Calculate remaining characters
        var remainingChars = maxLength - textBox.value.length;

        // Update the character count
        counter.textContent = remainingChars + '/' + maxLength;

        // Optionally, you can provide visual feedback when reaching the limit
        if (remainingChars < 0) {
            counter.style.color = 'red'; // Change color to red when exceeding the limit
        } else {
            counter.style.color = ''; // Reset color
        }
    }
// Function to handle the "Suggest" button click for the Head Line field
    function suggestHeadLine() {
        var storeName = '<%= this.CurrentUser.StoreGroupName %>';
        // Set the value of txtHeadLine to the suggested value
        $('#<%= txtHeadLine.ClientID %>').val('Welcome to ' + storeName + '!');

        // Optionally, you can update the character count
        updateCharacterCount();
    }

    // Function to update the character count for the Head Line field
    function updateCharacterCount() {
        var textBox = document.getElementById('<%= txtHeadLine.ClientID %>');
        var counter = document.getElementById('characterCount');
        var maxLength = textBox.maxLength;

        // Calculate remaining characters
        var remainingChars = maxLength - textBox.value.length;

        // Update the character count
        counter.textContent = remainingChars + '/' + maxLength;

        // Optionally, you can provide visual feedback when reaching the limit
        if (remainingChars < 0) {
            counter.style.color = 'red'; // Change color to red when exceeding the limit
        } else {
            counter.style.color = ''; // Reset color
        }
    }
// Function to handle the "Suggest" button click for the Description field
    function suggestDescription() {
        var storeName = '<%= this.CurrentUser.StoreGroupName %>';
        // Set the value of txtDescription to the suggested value
        $('#<%= txtDescription.ClientID %>').val('Welcome to ' + storeName + '! We are a locally owned and operated business committed to providing our customers with top-quality products and exceptional customer service.');

        // Optionally, you can update the character count
        updatedesCharacterCount();
    }

    // Function to update the character count for the Description field
    function updatedesCharacterCount() {
        var textBox = document.getElementById('<%= txtDescription.ClientID %>');
        var counter = document.getElementById('descriptionCount');
        var maxLength = textBox.maxLength;

        // Calculate remaining characters
        var remainingChars = maxLength - textBox.value.length;

        // Update the character count
        counter.textContent = remainingChars + '/' + maxLength;

        // Optionally, you can provide visual feedback when reaching the limit
        if (remainingChars < 0) {
            counter.style.color = 'red'; // Change color to red when exceeding the limit
        } else {
            counter.style.color = ''; // Reset color
        }
    }
    </script>
    
    <script>
        function readURL(input, val) {
            if (input.files && input.files[0]) {
                // Check the file extension
                var fileExtension = input.files[0].name.split('.').pop().toLowerCase();
                if (fileExtension !== 'png') {
                    alert('Please upload a .png file.');
                    // Optionally, clear the file input or take other actions
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    var image = new Image();
                    image.src = e.target.result;
                    image.onload = function () {
                        if (this.width === imagedata[val]['width'] && this.height === imagedata[val]['height']) {
                            // Load the selected image into the corresponding preview element
                            var targetElementId = val === 0 ? 'applogouploadPreview' : 'SplashScreenOnePreview';
                            $('#' + targetElementId).attr('src', e.target.result);
                            $('.btn_rmv_remove').addClass('rmv');
                            $('.btn_upload').addClass('rmvbg');

                            // Generate and load images with different sizes for the logo (data-val=0) only
                            if (val === 0) {
                                generateAndLoadDifferentSizes(image, val);
                            }
                        } else {
                            alert('Image resolution must be ' + imagedata[val]['width'] + 'px X ' + imagedata[val]['height']);
                        }
                    };
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function generateAndLoadDifferentSizes(image, val) {
            var sizes = [48, 72, 96, 144, 192]; // Add other sizes as needed

            if (val === 0) { // Only process when it's a logo (data-val equals 0)
                sizes.forEach(function (size) {
                    var canvas = document.createElement('canvas');
                    var context = canvas.getContext('2d');
                    var newSize = size;
                    canvas.width = newSize;
                    canvas.height = newSize;
                    context.drawImage(image, 0, 0, newSize, newSize);
                    var resizedImageSrc = canvas.toDataURL('image/x-png');

                    var divIndex;
                    if (size === 48) {
                        divIndex = 0; // 48px corresponds to the first div
                    } else if (size === 72) {
                        divIndex = 1; // 72px corresponds to the second div
                    } else if (size === 96) {
                        divIndex = 2; // 96px corresponds to the third div
                    } else if (size === 144) {
                        divIndex = 3; // 144px corresponds to the fourth div
                    } else if (size === 192) {
                        divIndex = 4; // 192px corresponds to the fifth div
                    } else {
                        alert('Image resolution must be ' + imagedata[val]['width'] + 'px X ' + imagedata[val]['height']);
                    }

                    var imgElement = $('.log_variant').eq(divIndex);
                    imgElement.attr('src', resizedImageSrc);
                });
            }
        }

        $('.main-logo').change(function () {
            var val = $(this).data('val');
            readURL(this, val);
            $('.btn_rmv_remove').addClass('rmv');
            $('.btn_rmv_remove').attr('hiddenfld', 1);
            $('.btn_upload').addClass('rmvbg');
        });

        $('.splash-screen').change(function () {
            var val = $(this).data('val');
            readURL(this, val);
            $('.btn_rmv_remove').addClass('rmv');
            $('.btn_rmv_remove').attr('hiddenfld', 1);
            $('.btn_upload').addClass('rmvbg');
        });

        $('.btn_rmv_remove').click(function (e) {
            var hiddenfld = $(this).attr('hiddenfld');
            if (hiddenfld && hiddenfld !== '') {
                if (!confirm('Are you sure you want to delete this image?')) {
                    return false;
                }
                $(this).removeAttr('hiddenfld');
            }
            e.preventDefault();

            // Clear the selected image and previews
            $('#applogoupload').val('');
            $('#applogouploadPreview').attr('src', '');
            $('#SplashScreenOneupload').val('');
            $('#SplashScreenOnePreview').attr('src', '');

            $(this).removeClass('rmv');
            $('#applogoupload').parent('.btn_upload').removeClass('rmvbg');
            $('.log_variant').attr('src', '');
        });

        var imagedata = [
            {
                'width': 256,
                'height': 256,
                'size': 1048576
            },
            {
                'width': 1080,  // size 1080
                'height': 1920, // size 1920
                'size': 1048576
            },
            {
                'width': 1080,  // size 1080 
                'height': 1920, // size 1920 
                'size': 1048576
            }
        ];
    </script>

<script>
    $(document).ready(function () {
        // Attach a click event handler to your submit button
        $('#btnUpload').click(function () {
            // Disable the logo and splash screen input elements
            $('#applogoupload').prop('disabled', true);
            $('#SplashScreenOneupload').prop('disabled', true);
        });
    });
</script>
    <script type="text/javascript">
        window.onload = function () {
            // Load the URLs from Amazon S3
            var logoData48 = "https://grozeoindia-tenentapp-frontenddata.s3.ap-south-1.amazonaws.com/tenentapp-logo/mipmap-mdpi/" + logoResult;
            var logoData72 = "https://grozeoindia-tenentapp-frontenddata.s3.ap-south-1.amazonaws.com/tenentapp-logo/mipmap-hdpi/" + logoResult;
            var logoData96 = "https://grozeoindia-tenentapp-frontenddata.s3.ap-south-1.amazonaws.com/tenentapp-logo/mipmap-xhdpi/" + logoResult;
            var logoData144 = "https://grozeoindia-tenentapp-frontenddata.s3.ap-south-1.amazonaws.com/tenentapp-logo/mipmap-xxhdpi/" + logoResult;
            var logoData192 = "https://grozeoindia-tenentapp-frontenddata.s3.ap-south-1.amazonaws.com/tenentapp-logo/mipmap-xxxhdpi/" + logoResult;

            // Set the src attribute of the image elements
            document.querySelectorAll('.log_variant').forEach(function (img) {
                var maxDimension = img.getAttribute('data-dimension');
                switch (maxDimension) {
                    case '48':
                        img.src = logoData48;
                        break;
                    case '72':
                        img.src = logoData72;
                        break;
                    case '96':
                        img.src = logoData96;
                        break;
                    case '144':
                        img.src = logoData144;
                        break;
                    case '192':
                        img.src = logoData192;
                        break;
                }
                    // Load the image using the loadImage function
                    loadImage(img.src);
            });
        };
    </script>

    <script>
    function loadImage(imageUrl) {
        $(document).ready(function () {
            // Load the image during page load
            $('#applogouploadPreview').attr('src', imageUrl);
        });
    }
    </script>

</asp:Content>