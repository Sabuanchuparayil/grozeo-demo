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
    var importPanel = function (itemId) {
        return new Ext.FormPanel({
              id: "imageUrlImportFormPanel",
              height: 400,
              width: winsize.width * 0.3,
              autoScroll:true,
              frame: true,
              border: false,
              labelAlign: "top",
              items: [
                {
              xtype: "textfield",
              fieldLabel: "Main Image Url",
              emptyText: "Main Image Url",
              id: "imgurl1",
              name: "imgurl1",
              anchor: "98%",
              maxLength: 250,
              tabIndex: 531,
            },{
              xtype: "textfield",
              fieldLabel: "Add Image Url",
              emptyText: "Add Image Url",
              id: "imgurl2",
              name: "imgurl2",
              anchor: "100%",
              maxLength: 250,
              tabIndex: 531,
            },{
              xtype: "textfield",
              fieldLabel: "Add Image Url",
              emptyText: "Add Image Url",
              id: "imgurl3",
              name: "imgurl3",
              anchor: "100%",
              maxLength: 250,
              tabIndex: 531,
            },{
              xtype: "textfield",
              fieldLabel: "Image 4",
              emptyText: "Add Image Url",
              id: "imgurl4",
              name: "imgurl4",
              anchor: "100%",
              maxLength: 250,
              tabIndex: 531,
            },{
              xtype: "textfield",
              fieldLabel: "Add Image Url",
              emptyText: "Add Image Url",
              id: "imgurl5",
              name: "imgurl5",
              anchor: "100%",
              maxLength: 250,
              tabIndex: 531,
            }],
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
        },
        vlslUploadWindow: function (itemId) {
            var resultWindow = new Ext.Window({
                id: "windowFinascopStockAddvenderitemCreatevendoritem",
                title: "Upload Images",
                shadow: false,
                height: 600,
                width: winsize.width * 0.7,
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [uploadPanel(itemId)],
                buttons: [],
                listeners: {
                  afterrender: function () {},
                },
              });
        
              resultWindow.doLayout();
              resultWindow.show();
              resultWindow.center();
        },
        imageUrlUploadWindow: function (itemId) {
            var resultWindow = new Ext.Window({
                id: "windowImageUrlUpload",
                title: "Import Images",
                shadow: false,
                height: 400,
                width: winsize.width * 0.3,
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [importPanel(itemId)],
                buttons: [{
                icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                text: "Close",
                align: "right",
                handler: function () {
                    resultWindow.close();
                }
            },
            {
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
              text: "Import Images",
              align: "right",
              handler: function () {
                 Ext.getCmp('imageUrlImportFormPanel').getForm().submit({
                    url: modURL + '&op=importImages',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        itemId:itemId,
                        apikey: _SESSION.apikey,
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true) {
                            resultWindow.close();
                            Application.example.msg('Success', "Image imported.");                       
                       
                        } else {
                            Ext.Msg.alert("Error", "Issue in Importing.");
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Error', result.message);
                        } else {
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                        }
                    }
                });
              }
            }],
                listeners: {
                  afterrender: function () {
                    Ext.getCmp('imageUrlImportFormPanel').getForm().load({
                        waitTitle: 'Please Wait',
								waitMsg: 'Loading...',
								url: modURL + '&op=getImageUrls',
								params: {
                                    itemId:itemId
								}
                    });
                  },
                },
              });
        
              resultWindow.doLayout();
              resultWindow.show();
              resultWindow.center();
        }
    };
}();