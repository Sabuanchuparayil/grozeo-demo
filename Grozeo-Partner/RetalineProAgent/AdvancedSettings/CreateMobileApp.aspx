<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/AgentMaster.Master" CodeBehind="CreateMobileApp.aspx.cs" Inherits="RetalineProAgent.AdvancedSettings.CreateMobileApp" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Generate App</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Create Mobile App</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

            <div class="section-wrapper p-3">
          

          <div class="row row-sm">
            <div class="col-12 col-lg-6">
                              
              <div class="form-group">
                <label for="txtSearch1" class="tx-dark mb-1 w-100">Name of App</label>
                <input name="branchname" type="text" id="NameofApp" value="" class="form-control w-100 rounded" placeholder="Name of App">
                <a href="" class="tx-12 mg-t-2 float-right">Suggest</a>
              </div>
              

              <div class="uploadlogo_wrap applogoupload_wrap w-100 mt-5 mb-4">
                <span id="spnImgUpload2" class="btn_upload">
                    <input type="file" name="ctl00$cpMainContent$Imgupload2" id="applogoupload" data-target="#applogouploadPreview" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg">
                    <span id="image_size_dimension1">(size 256px X 256px)</span>
                </span>
                <div class="ImgPreview_wap">
                    <img id="applogouploadPreview" class="preview_img" style="max-width: 256px; max-height: 100px;">
                  
                </div>
                <div class="remove_preview_wrap"><input type="hidden" name="ctl00$cpMainContent$hidDelImg2" id="hidDelImg2">
                    <span id="" data-target="#applogouploadPreview" data-file="#applogoupload" class="btn_rmv_remove">
                      <i class="icon ion-trash-a"></i> Delete Logo
                    </span>
                  
                </div>
              </div> <!--applogoupload_wrap-->

              <div class="applogo_list_wrap d-flex mb-3">
                <div class="applogo_list border">
                  <!-- <img src="index_files/logo5.png"> -->
                  <span class="applogo_sugt_dimension">(size 42px x 42px)</span>
                </div>
                <div class="applogo_list border">
                  <!-- <img src="index_files/logo5.png"> -->
                  <span class="applogo_sugt_dimension">(size 72px X 72px)</span>
                </div>
                <div class="applogo_list border">
                  <!-- <img src="index_files/logo5.png"> -->
                  <span class="applogo_sugt_dimension">(size 96px X 96px)</span>
                </div>
                <div class="applogo_list border">
                  <!-- <img src="index_files/logo5.png"> -->
                  <span class="applogo_sugt_dimension">(size 144px X 144px)</span>
                </div>
                <div class="applogo_list border">
                  <!-- <img src="index_files/logo5.png"> -->
                  <span class="applogo_sugt_dimension">(size 192px X 192px)</span>
                </div>
              </div>


              

            </div>
    
            <div class="col-12 col-lg-6 mb-3 mb-lg-5">

              <div class="splash_screen_wrap d-flex h-100 justify-content-end">

                <div class="splash_screen_list">
                  <label for="SplashScreenOne" class="tx-dark mb-1 w-100">Splash Screen 1</label>
                  <div class="uploadlogo_wrap SplashScreenOne_wrap h-100">
                    <span id="cpMainContent_spnImgSplashUpload2" class="btn_upload">
                        <input type="file" name="ctl00$cpMainContent$Imgupload2" id="SplashScreenOneupload" data-target="#SplashScreenOnePreview" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg">
                        <span id="splashimage_size_dimension">(size 512px X 512px)</span>
                    </span>
                    <div class="ImgPreview_wap">
                        <img id="SplashScreenOnePreview" class="preview_img" style="max-width: 512px; max-height: 512px;">
                      
                    </div>
                    <div class="remove_preview_wrap"><input type="hidden" name="ctl00$cpMainContent$hidDelImg2" id="cpMainContent_hidDelImg2">
                        <span id="" data-target="#SplashScreenOnePreview" data-file="#SplashScreenOneupload" class="btn_rmv_remove">
                          <i class="icon ion-trash-a"></i> Delete Image
                        </span>
                      
                    </div>
                  </div> <!--SplashScreenOne_wrap-->
                </div>

                <div class="splash_screen_list ml-3">
                  <label for="SplashScreenTwo" class="tx-dark mb-1 w-100">Splash Screen 2</label>
                  <div class="uploadlogo_wrap SplashScreenTwo_wrap h-100">
                    <span id="cpMainContent_spnImgUpload2" class="btn_upload">
                        <input type="file" name="ctl00$cpMainContent$Imgupload2" id="SplashScreenTwoupload" data-target="#SplashScreenTwoPreview" class="input-img" accept="image/x-png,image/gif,image/jpeg,image/svg">
                        <span id="image_size_dimension">(size 512px X 512px)</span>
                    </span>
                    <div class="ImgPreview_wap">
                        <img id="SplashScreenTwoPreview" class="preview_img" style="max-width: 512px; max-height: 512px;">
                      
                    </div>
                    <div class="remove_preview_wrap"><input type="hidden" name="ctl00$cpMainContent$hidDelImg2" id="cpMainContent_hidDelImg2">
                        <span id="" data-target="#SplashScreenTwoPreview" data-file="#SplashScreenTwoupload" class="btn_rmv_remove">
                          <i class="icon ion-trash-a"></i> Delete Image
                        </span>
                      
                    </div>
                  </div> <!--SplashScreenTwo_wrap-->
                </div>
                

              </div> <!--splash_screen_wrap-->

              
            </div>

          </div>

          <div class="row">
            <div class="col-12 form-group mb-0 mt-4">
              <div class="input-group w-auto d-inline-block">
                <input type="submit" name="" value="Submit" id="" class="btn btn-success float-right ml-2">  
                <input type="submit" name="" value="Cancel" id="" class="btn btn-secondary float-right">
              </div>
            </div>

          </div>


        </div>


        <script>
          function readURL(input, imgControlName)
          {
            if (input.files && input.files[0])
            {
              var reader = new FileReader();
              reader.onload = function(e)
              {
                $(imgControlName).attr('src', e.target.result);
              }
              reader.readAsDataURL(input.files[0]);
            }
          }

          $(".input-img").change(function()
          {
            var imgControlName = $(this).data('target');
            readURL(this, imgControlName);
            $(this).closest('.uploadlogo_wrap').find('.btn_rmv_remove').addClass('rmv');
            $(this).closest('.uploadlogo_wrap').find('.btn_rmv_remove').attr('hiddenfld', 1);
            $(this).parent('.btn_upload').addClass('rmvbg');
          });

          $(".btn_rmv_remove").click(function(e)
          {
              var hiddenfld = $(this).attr('hiddenfld');
              //console.log(hiddenfld)
              if (hiddenfld && hiddenfld != '') {
                  if (!confirm('Are you sure you want to delete this image?')) {
                      return false;
                  }
                  $(this).removeAttr('hiddenfld');
              }
            e.preventDefault();
            var imgFile = $(this).data('target');
            var imgSelect = $(this).data('file');
            $(imgSelect).val("");
            $(imgFile).attr("src", "");
            $(this).removeClass('rmv');
            $(imgSelect).parent('.btn_upload').removeClass('rmvbg');
          });


        </script>



</asp:Content>
