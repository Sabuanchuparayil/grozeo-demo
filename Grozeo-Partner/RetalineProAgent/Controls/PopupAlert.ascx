<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="PopupAlert.ascx.cs" Inherits="RetalineProAgent.Controls.PopupAlert" %>


    <div id="modalErrorpopup" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-danger mg-b-20 modalcustomtittle"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20 modalcustombody"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Close</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<!-- MODAL ALERT MESSAGE -->
    <div id="modalsuccesspopup" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success tx-semibold mg-b-20 modalcustomtittle"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20 modalcustombody"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Close</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<script type="text/javascript">
    function showModal(strtitle, strbody, success = true, oncloseredirecturl = '') {
        var modalobj = (success ? $('#modalsuccesspopup') : $('#modalErrorpopup'));
        if (!modalobj)
            return;

        $(modalobj).find('h4.modalcustomtittle').html(strtitle);
        $(modalobj).find('p.modalcustombody').html(strbody);
        $(modalobj).unbind('hidden.bs.modal');
        if (oncloseredirecturl != '')
            $(modalobj).on('hidden.bs.modal', function (e) { window.location.href = oncloseredirecturl; });

        $(modalobj).modal({ backdrop: 'static' });
    }




</script>