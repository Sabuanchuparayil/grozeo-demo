<%@ Page Language="C#" AutoEventWireup="true" Async="true" EnableEventValidation="true" EnableViewState="true" EnableViewStateMac="false" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="CustomisedGraphics.aspx.cs" Inherits="RetalineProAgent.Appearance.CustomisedGraphics" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Tenant/Appearance/Graphics"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle m-0">Customised Graphics</h6>
        <p class="mb-0"></p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
    <div class="card-body p-3 shadow_top">
        <div class="banner_list_wrap mt-3 pt-2">
            <div class="row row-sm">
                <asp:Repeater ID="rptOwnbanners" runat="server">
                    <ItemTemplate>
                        <div class="col-12 col-sm-6 col-lg-4 mb-3 pb-2">
                            <div class="banner_list border p-2 position-relative rounded d-flex flex-wrap justify-content-center">
                                <div class="banner_img text-center">
                                    <img src='<%# Eval("graphicsURL") %>' />
                                </div>

                                <div class="grph_btn w-100 d-flex justify-content-center align-items-end mt-2 flex-wrap">
                                    <asp:LinkButton ID="lnkDownload" runat="server" Text="Download" CssClass="btn btn-primary btn-inline-block mx-2" OnClick="btnDownload_OnClick" CommandArgument='<%# Eval("graphicsURL") %>' />
                                    <a href='<%# "#modal_" + Container.ItemIndex %>' data-toggle="modal" data-target='<%# "#modal_" + Container.ItemIndex %>' class="btn btn-primary btn-inline-block mx-2">View</a>
                                </div>
                            </div>
                            <!--banner_list-->
                        </div>
                        <!--col-lg-6-->
                        
                        <div class="modal fade" id='<%# "modal_" + Container.ItemIndex %>' tabindex="-1" role="dialog" aria-labelledby='<%# "modalTitle_" + Container.ItemIndex %>' aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id='<%# "modalTitle_" + Container.ItemIndex %>'>View Image</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src='<%# Eval("graphicsURL") %>' class="img-fluid" style="max-width: 100%; max-height: 70vh;" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal -->
                    </ItemTemplate>
                </asp:Repeater>
            </div>
            <!--row-->
        </div>
        <!-- table-responsive -->

        <div class="col-12 text-center" runat="server" id="noRecordsMessage" visible='<%# rptOwnbanners.Items.Count == 0 %>'>
                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                    <h6 class="mb-3">No record available</h6>
                </div>
    </div>
    <!-- card-body -->
</div>
    <!-- card -->
   
<style>
    .tx-white {
        color:#FFF!important;
    }
          .banner_list.border .banner_disc{
            position: absolute;
            background:rgba(0, 0, 0, 0.6);
            overflow: hidden;
            width: 100%;
            height: 0;
            opacity: 0;
            transition: .5s ease;
            bottom: 0;
            left: 50%;
            -webkit-transform: translate(-50%, 0%);
            -ms-transform: translate(-50%, 0%);
            transform: translate(-50%, 0%); 
          }
          .banner_list.border .banner_disc p{
            color: white;
            padding: 10px;
          }
          .banner_list.border:hover .banner_disc {
            height:60%;
            opacity:1;
          }
          .banner_list.border {
            min-height: 200px;
          }
          .banner_list.border .btn_upload {
              background-position-y: 20px;
          }

          .banner_list .slim-pageheader{
            flex-direction: unset;
            padding: 0px!important;
          }
          .banner_list .slim-pageheader h6{
            max-width: 90%;
          }
          .banner_list .slim-pageheader .messages-compose{
            font-size: 0.875rem;
          }
        </style>

<script type="text/javascript">
        function getFilenameFromUrl(url) {
            // Extract the filename from the URL
            var index = url.lastIndexOf('/') + 1;
            return url.substr(index);
        }

        function downloadImage(btn) {
            var imageUrl = btn.getAttribute('data-image-url') || btn.getAttribute('data-command-argument') || btn.getAttribute('CommandArgument');
            var filename = getFilenameFromUrl(imageUrl);

            // Create a temporary anchor tag
            var link = document.createElement('a');
            link.href = 'CustomisedGraphics.aspx?download=true&imageUrl=' + imageUrl;
            link.download = filename;
            link.style.display = 'none';

            // Append the anchor tag to the body
            document.body.appendChild(link);

            // Trigger a click on the anchor tag
            link.click();

            // Remove the anchor tag from the body
            document.body.removeChild(link);
    }

    function showModal(index, graphicsURL) {
        // Construct the modal ID dynamically
        var modalId = "#modal_" + index;

        // Set the image source dynamically
        $(modalId + " img").attr("src", graphicsURL);

        // Show the modal using Bootstrap's modal method
        $(modalId).modal('show');
    }
</script>

</asp:Content>
