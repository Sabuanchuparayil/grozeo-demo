<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlLanguages.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlLanguages" %>

<div id="modallanguage" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 tx-14">
            <div class="modal-header">
                <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Language preference</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pd-25">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-control-label">Select language Preference: <span class="tx-danger">*</span></label>
                        <div class="row row-sm">
                            <div class="col-lg-6">
                                <asp:DropDownList ID="selFirstLanguage" runat="server" CssClass="form-control select2 large-dropdown" ForeColor="GrayText" AutoPostBack="true" AppendDataBoundItems="true" OnSelectedIndexChanged="selFirstLanguage_SelectedIndexChanged">
                                    <asp:ListItem Text="Select first preference" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator ID="rfvFirstLanguage" runat="server" ControlToValidate="selFirstLanguage" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Primary language is required" ValidationGroup="CreateLanguage" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                            <div class="col-lg-6">
                                <asp:DropDownList ID="selSecondLanguage" runat="server" CssClass="form-control select2 large-dropdown" ForeColor="GrayText" AppendDataBoundItems="true">
                                    <asp:ListItem Text="Select second preference" Value=""></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator ID="rfvSecondLanguage" runat="server" ControlToValidate="selSecondLanguage" CssClass="error_msg_wrap b--15i" Display="Dynamic" ErrorMessage="Secondary language is required" ValidationGroup="CreateLanguage" ForeColor="Red"></asp:RequiredFieldValidator>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <asp:Button runat="server" ID="btnAdd" OnClick="btnSave_Click" CssClass="btn btn-primary" Text="Save" ValidationGroup="CreateLanguage"/>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div><!-- modal -->


<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        var primaryDropdown = document.getElementById('<%= selFirstLanguage.ClientID %>');
            var secondaryDropdown = document.getElementById('<%= selSecondLanguage.ClientID %>');

            primaryDropdown.addEventListener('change', function () {
                filterSecondaryDropdown(primaryDropdown, secondaryDropdown);
            });

            function filterSecondaryDropdown(primary, secondary) {
                var selectedPrimaryValue = primary.value;

                for (var i = 0; i < secondary.options.length; i++) {
                    var option = secondary.options[i];
                    if (option.value === selectedPrimaryValue) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'block';
                    }
                }
            }

            // Initial call to filter the secondary dropdown in case a primary value is preselected
            filterSecondaryDropdown(primaryDropdown, secondaryDropdown);
        });
</script>
    
    <script>
        $(document).ready(function () {
            $(document).ready(function () {
                $('.select2').select2();

                //Bootstrap Duallistbox
                $('.duallistbox').bootstrapDualListbox();
            });
        });
    </script>

    <style>
        .select2.select2-container {
            width:100%!important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            display: block;
            line-height: 36px;
        }
        .select2-container.select2-container--open {
          z-index: 1050;
        }
        .slim-sticky-sidebar .slim-header {
        z-index: 1051;
        }

        .modal-content {
    max-width: 80vw; /* Adjust as needed */
}

/* Increase the size of the dropdowns */
.large-dropdown {
    font-size: 20px; /* Adjust as needed */
    height: 100%; /* Adjust as needed */
    width: 100%; /* Ensure it takes full width of its container */
}

    </style>