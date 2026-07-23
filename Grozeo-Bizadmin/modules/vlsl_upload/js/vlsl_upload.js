Application.VLSLUpload = function () {
    var recs_per_page = 12;
    var modURL = '?module=vlsl_upload';
    var winsize = Ext.getBody().getViewSize();
    var uploadPanel = function (itemId) {
        return new Ext.Panel({
            layout: "fit",
            title: 'Upload',
            id: id,
            html: '<iframe id="downloadContactsIframe" name="downloadContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="/vlsupload/?module=vlsl_upload&itemId='+itemId+'"; ></iframe>'
        });
    };
    return {
        vlslUpload: function (itemId) {
            var panelId = 'vlsupload_main_panel';
            var vlsupload_main_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(vlsupload_main_panel)) {
                vlsupload_main_panel = uploadPanel(itemId);
            }
            Application.UI.addTab(vlsupload_main_panel);
            vlsupload_main_panel.doLayout();
        },showImages: function () {
            var productId = arguments[0];
            var type = 1;
            //var printOrderPanel = createprintInvoicePanel(order_auto_id, order_generated_id);
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var src =
              "?module=vlsl_upload&op=showImages&productId=" + productId + "&type=" + type;
            var printOrderWindowid = Ext.getCmp("printOrderWindow");
            if (Ext.isEmpty(printOrderWindowid)) {
              printOrderWindowid = new Ext.Window({
                id: "printOrderWindow",
                title: "Image Details",
                modal: true,
                height: 500,
                width: winsize.width * 0.7,
                shadow: false,
                resizable: false,
                items: [
                  new Ext.Panel({
                    layout: "fit",
                    border: false,
                    width: winsize.width * 0.7,
                    autoScroll: true,
                    items: [
                      {
                        region: "center",
                        border: false,
                        html:
                          '<iframe src="' +
                          src +
                          '" id="iframeRequest" name="iframeRequest" ' +
                          'width="100%" height="100%" style="border:none">',
                      },
                    ],
                  }),
                ],
                buttons: [
                  {
                    text: "Cancel",
                    iconCls: "my-icon61",
                    handler: function () {
                      printOrderWindowid.close();
                    },
                  },
                ],
                listeners: {
                  close: function () {},
                },
              });
            }
            printOrderWindowid.doLayout();
            printOrderWindowid.show();
            printOrderWindowid.center();
          }
    };
}();