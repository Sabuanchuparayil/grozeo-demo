Application.MyphaAdManagement = (function () {
    var RECS_PER_PAGE = 12;
    var modURL = "?module=mypha_ad_management";
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
      RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionadManagementChanged = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp("adManagementGridPanel").getSelectionModel().getSelections()
        )
      ) {
        var ID = Ext.getCmp("adManagementGridPanel")
          .getSelectionModel()
          .getSelections()[0].data.adzone_id;
        Application.MyphaAdManagement.ViewAdManagementMode(ID);
      }
    };
    var adManagementPanel = function (id) {
      var _adPanel = new Ext.Panel({
        frame: false,
        hideBorders: true,
        layout: "border",
        border: false,
        title: "Adzone",
        id: id,
        //iconCls: 'my-icon444',
        items: [
          adManagementGrid(),
          new Ext.Panel({
            title: "Adzone Details",
            frame: false,
            border: true,
            region: "east",
            width: winsize.width * 0.4,
            autoScroll: true,
            cls: "left_side_panel",
            id: "admanagementpanel",
            height: winsize.height * 0.6,
            items: [adManagementForm(), adManagementDetailsView()],
            buttonAlign: "right",
            fbar: [
              {
                text: "Cancel",
                tabIndex: 505,
                cls: "left-right-buttons",
                icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                id: "buttonadManagementCancel",
                hidden: true,
                handler: function () {
                  if (
                    !Ext.isEmpty(
                      Ext.getCmp("adManagementGridPanel")
                        .getSelectionModel()
                        .getSelections()
                    )
                  ) {
                    var ID = Ext.getCmp("adManagementGridPanel")
                      .getSelectionModel()
                      .getSelections()[0].data.adzone_id;
                    Application.MyphaAdManagement.ViewAdManagementMode(ID);
                  }
                },
              },
              /* <?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */ {
                text: "Edit",
                cls: "left-right-buttons",
                id: "buttonadManagementEdit",
                icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
                tabIndex: 504,
                handler: function () {
                  var ID = Ext.getCmp("adManagementGridPanel")
                    .getSelectionModel()
                    .getSelections()[0].data.adzone_id;
                  Application.MyphaAdManagement.EditAdManagementView(ID);
                },
              },
              {
                text: "Save",
                tabIndex: 504,
                cls: "left-right-buttons",
                id: "buttonadManagementSave",
                icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                hidden: true,
                handler: function () {
                  saveAdManagement();
                },
              } /*<?php  } ?>*/,
            ],
          }),
        ],
      });
      return _adPanel;
    };
    var adManagementGridstore = function () {
      var _admanagementList = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listadManagement",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "adzone_id",
            root: "data",
          },
          ["adzone_id", "adzone_name", "adzone_screen", "adzone_status"]
        ),
        sortInfo: {
          field: "adzone_id",
          direction: "ASC",
        },
        groupField: "",
        groupDir: "ASC",
        remoteSort: true,
        autoLoad: false,
        root: "data",
        listeners: {
          load: function () {
            Ext.getCmp("adManagementGridPanel").getSelectionModel().selectRow(0);
          },
        },
      });
      return _admanagementList;
    };
    var nameStore = function () {
      var namestore = new Ext.data.JsonStore({
        url: modURL + "&op=screenName",
        method: "post",
        fields: ["screen_id", "screen_name"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
      });
      return namestore;
    };
    var layoutNameStore = function () {
      var namestore = new Ext.data.JsonStore({
        url: modURL + "&op=layoutName",
        method: "post",
        fields: ["layout_type_id", "layout_type_name", "type_id"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
      });
      return namestore;
    };
    var themeNameStore = function () {
      var themestore = new Ext.data.JsonStore({
        url: modURL + "&op=themeName",
        method: "post",
        fields: ["theme_id", "theme_name"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
      });
      return themestore;
    };
    var adManagementForm = function () {
      var img_url = '/resources/images/default.png';
      if(!Ext.isEmpty(Application.MyphaAdManagement.UploadedPreview))
        img_url = Application.MyphaAdManagement.UploadedPreview;
      var screenNameStore = nameStore();
      var layoutStore = layoutNameStore();
      var themeStore = themeNameStore();
      var _adManagementForm = new Ext.FormPanel({
        id: "formpanelAdManagement",
        frame: false,
        border: false,
        hidden: true,
        labelAlign: "top",
        autoHeight: true,
        labelWidth: 100,
        bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
        items: [
          {
            xtype: "spacer",
            height: 10,
          },
          {
            xtype: "textfield",
            fieldLabel: "Adzone Name",
            id: "adzone_name",
            name: "n[adzone_name]",
            anchor: "98%",
            allowBlank: false,
            emptyText: "Adzone Name",
            width: 300,
            tabIndex: 500,
            maxLength: 300,
          },
          {
            xtype: "combo",
            fieldLabel: "Screen Name",
            id: "adzone_screen",
            name: "n[adzone_screen]",
            anchor: "98%",
            displayField: "screen_name",
            valueField: "screen_name",
            triggerAction: "all",
            forceSelection: true,
            selectOnFocus: true,
            mode: "local",
            typeAhead: true,
            lazyRender: true,
            editable: true,
            minChars: 2,
            tabIndex: 501,
            store: screenNameStore,
          },
          {
            xtype: "combo",
            fieldLabel: "Themes",
            allowBlank: false,
            id: "adzone_theme",
            name: "n[adzone_theme]",
            anchor: "98%",
            displayField: "theme_name",
            valueField: "theme_id",
            triggerAction: "all",
            forceSelection: true,
            selectOnFocus: true,
            mode: "local",
            typeAhead: true,
            lazyRender: true,
            editable: true,
            minChars: 2,
            tabIndex: 502,
            store: themeStore,
          },
          {
            layout: "column",
            border: false,
            items: [
              {
                columnWidth: 0.3,
                layout: "form",
                border: false,
                items: [
                  {
                    xtype: "combo",
                    displayField: "usagetype",
                    valueField: "usageid",
                    anchor: "98%",
                    mode: "local",
                    id: "adzone_mode",
                    name: "adzone_mode",
                    allowBlank: false,
                    name: "n[adzone_mode]",
                    forceSelection: true,
                    fieldLabel: "Mode",
                    emptyText: "Mode",
                    typeAhead: true,
                    triggerAction: "all",
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 502,
                    store: new Ext.data.JsonStore({
                      fields: ["usageid", "usagetype"],
                      data: [
                        { usageid: 1, usagetype: "Web" },
                        { usageid: 2, usagetype: "App" },
                      ],
                    }),
                  },
                ],
              },
              {
                columnWidth: 0.3,
                layout: "form",
                border: false,
                items: [
                  {
                    fieldLabel: "Width",
                    xtype: "textfield",
                    id: "adzone_width",
                    emptyText: "Width",
                    tabIndex: 503,
                    allowBlank: false,
                    name: "n[adzone_width]",
                  },
                ],
              },
              {
                columnWidth: 0.3,
                layout: "form",
                border: false,
                items: [
                  {
                    fieldLabel: "Height",
                    xtype: "textfield",
                    id: "adzone_height",
                    emptyText: "Height",
                    tabIndex: 504,
                    allowBlank: false,
                    name: "n[adzone_height]",
                  },
                ],
              },
              {
                columnWidth: 1,
                layout: 'form',
                id: 'template_image_panel_form',
                height:300,
                border: false,
                items: [{
                  xtype: "combo",
                  fieldLabel: "Layouts",
                  id: "adzone_type",
                  hidden: true,
                  name: "n[adzone_type]",
                  anchor: "98%",
                  displayField: "layout_type_name",
                  valueField: "layout_type_name",
                  triggerAction: "all",
                  forceSelection: true,
                  selectOnFocus: true,
                  mode: "local",
                  typeAhead: true,
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 502,
                  store: layoutStore,
                },
                {
                  xtype: "textfield",
                  id: "adzone_id",
                  name: "n[adzone_id]",
                  hidden: true,
                },
                mkCombo({
                  type: STATUS_COMBO_DATA,
                  value: "id",
                  display: "text",
                  name: "n[adzone_status]",
                  fieldLabel: "Status",
                  tabIndex: 505,
                  emptyText: "Set status..",
                  id: "adzone_status",
                }),{
                  xtype: 'hidden',
                  id: 'aws_file_locationtemplate',
                  name: 'aws_file_locationtemplate'
              }, {
                  xtype: 'hidden',
                  id: 'aws_file_bucket',
                  name: 'aws_file_bucket'
              },{
                  xtype: 'hidden',
                  id: 'file_name',
                  name: 'file_name'
              }, {
                  xtype: 'hidden',
                  id: 'grzBucketName',
                  name: 'grzBucketName'
              }, {
                  xtype: 'hidden',
                  id: 'accessKey',
                  name: 'accessKey'
              }, {
                  xtype: 'hidden',
                  id: 'secretKey',
                  name: 'secretKey'
              }, {
                  xtype: 'hidden',
                  id: 'bucketRegion',
                  name: 'bucketRegion'
              },
              {
                  xtype: 'hidden',
                  id: 'oncompleteurl',
                  name: 'oncompleteurl'
              },
              {
                  xtype: 'hidden',
                  id: 'img_path_db',
                  name: 'img_path_db'
              },new Ext.Panel({
                        layout: "fit",
                        id: 'template_image_panel',
                        tpl: new Ext.XTemplate('<div class="details-outer-event">',
                                '<img style="width: 100%; max-width: 250px; max-height: 250px;" src="{img_url}"></img>',
                                '</div>')
                    }), {
                        xtype: 'box',
                        width: 368,
                        height: 210,
                        id: 'exist_img_box',
                        autoEl: {tag: 'img', src: img_url, width: '368', height: 210},
                        listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
    
                    }, {
                        xtype: 'fileuploadfield',
                        id: 'associated_templatefile',
                        anchor: '98%',
                        name: 'associated_templatefile',
                        allowBlank: true,
                        buttonOnly: true,
                        buttonCfg: {
                            text: 'Upload Image',
                            width: 80
                        },
                        validator: function (v) {
                            if (v != '') {
                                v = v.toLowerCase();
                                var exp = /^.*\.(png|jpe?g|gif)$/i;
                                if (!(exp.test(v))) {
                                    Ext.Msg.alert("Notification", "Upload a valid image file");
                                    return;
                                }
    
                                var associated_templatefile = Ext.getCmp('associated_templatefile').getValue();
                                if (associated_templatefile == '') {
                                    Ext.Msg.alert("Notification", "Please choose a file to upload");
                                    return;
                                }
                                var mask = new Ext.LoadMask(Ext.getCmp('formpanelAdManagement').el, { msg: "Saving..." });
                                mask.show();                                
                                console.log('main here');
                                addAdzonePreview('main');
                                return true;
                            }
                        }
                    }]
            }
            ],
          },
          
         
        ],
        listeners: {
          afterrender: function () {
            if (Ext.isEmpty(Ext.getCmp("adzone_id").getValue())) {
              var recordSelected = Ext.getCmp("adzone_status")
                .getStore()
                .getAt(0);
              Ext.getCmp("adzone_status").setValue(recordSelected.get("id"));                
              
            }
            
          },
        },
      });
      return _adManagementForm;
    };
  function addAdzonePreview(type) {

        var grzBucketName = Ext.getCmp('grzBucketName').getValue();
        var bucketRegion = Ext.getCmp('bucketRegion').getValue();
        var filepath = Ext.getCmp('oncompleteurl').getValue();
        console.log(filepath);
        AWS.config.update({
            region: bucketRegion,
            credentials: new AWS.Credentials(
                    Ext.getCmp('accessKey').getValue(),
                    Ext.getCmp('secretKey').getValue(), null
                    )
        });
        var s3 = new AWS.S3({
            apiVersion: '2006-03-01',
            params: {Bucket: grzBucketName}
        });
        switch (type) {
            case 'main':
                files = document.getElementById('associated_templatefile-file').files;
                break;            
        }
        console.log(files);
        if (!files.length) {
            winLoadMask.hide();
            return alert('Please choose a file to upload first.');
        }
        var file = files[0];
        var filesize = files[0]['size'];
        var size=(filesize)/1000;
        /*if (size > 200) {
            winLoadMask.hide();
            return alert('File size should not exceed 200KB.');
        }*/
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split('.').pop();

        var fileName = uuidv4();
        fileName = fileName + '.' + fileExt;
        console.log(filesize);
        console.log(size);
        s3.upload({
            Key: filepath + fileName,
            /*file_Name*/ /*from server*/
            Body: file,
            ACL: 'public-read'
        }, function (err, data) {

            if (err) {
                winLoadMask.hide();
                //var img_src = Ext.BLANK_IMAGE_URL;
                var img_src_main = '/resources/images/awesomeupload/no_image.png';
                var img_src_list = '/resources/images/awesomeupload/no_image.png';
                switch (type) {
                    case 'main':
                        Ext.getCmp('template_image_panel').update({'img_url': img_src_main});
                        break;
                    
                }
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            console.log('data.Location');
            console.log(data.Location);
            if (!Ext.isEmpty(data.Location)) {
                var img_width = '640';
                var img_height = '430';
                var img = new Image();
                img.onload = function () {
                  console.log('imagedetails');
                  console.log(this.width);
                  console.log(this.height);
                    var flag = 1;
                    /*if (this.width != img_width || this.height != img_height) {
                        Ext.Msg.alert("Notification", 'Image size should be ' + img_width + '*' + img_height);
                        winLoadMask.hide();
                        flag = 0;
                    }*/

                    if (flag == 1) {
                        var mask = new Ext.LoadMask(Ext.getCmp('formpanelAdManagement').el, { msg: "Saving..." });
                                mask.hide();
                Ext.Msg.alert("Notification", 'File has been uploaded successfully.');
                Application.MyphaAdManagement.UploadedPreview = data.Location;
                Application.MyphaAdManagement.UploadedFileBucket = data.Bucket;
                Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
                switch (type) {
                    case 'main':
                        Ext.getCmp('aws_file_locationtemplate').setValue(data.Location);
                        Ext.getCmp('template_image_panel').update({'img_url': Application.MyphaAdManagement.UploadedPreview});
                        break;                    
                }
                    }

                }
                img.src = data.Location;               
                Ext.getCmp('exist_img_box').hide();
            }else{
                var img_src_main = '/resources/images/awesomeupload/no_image.png';
                var img_src_list = '/resources/images/awesomeupload/no_image.png';
                switch (type) {
                    case 'main':
                        Ext.getCmp('template_image_panel').update({'img_url': img_src_main});
                        break;                    
                }
            }
        });
    }
    var saveAdManagement = function () {
      var forms_data = {
        form: Ext.getCmp("formpanelAdManagement").getForm().getValues(),
      };
      if (Ext.getCmp("adzone_id").getValue() > 0) {
        var adId = Ext.getCmp("adzone_id").getValue();
      } else {
        var d = new Date();
        var adId = "-";
      }
      var params = {
        action: "Save Ad Details",
        module: "AdManagement",
        op: "saveAdManagement",
        extrainfo: "Ad Details save",
        id: adId,
      };
      APICall(params, Application.MyphaAdManagement.saveAdManagement, forms_data);
    };
  
    var adManagementDetailsView = function () {
      return new Ext.Panel({
        layout: "fit",
        border: false,
        hideBorders: true,
        autoHeight: true,
        id: "xtemplateadManagementViewDetails",
        tpl: new Ext.XTemplate(
          '<div class="details-outer">',
          '<table border="0" width="100%" class="details_view_table">',
          '<tr><th width="40%">Adzone Name </th><td>  {adzone_name} </td></tr>',
          '<tr><th width="40%">Screen Name </th><td>  {adzone_screen} </td></tr>',
          '<tr><th width="40%">Theme </th><td>  {adzone_themeName} </td></tr>',
          '<tr><th width="40%">Width </th><td>  {adzone_width} </td></tr>',
          '<tr><th width="40%">Height </th><td>  {adzone_height} </td></tr>',
          '<tr><th width="40%">Status</th><td>',
          "<tpl if=\"adzone_status == '1'\">Active</tpl>",
          "<tpl if=\"adzone_status == '0'\">Inactive</tpl>",
          "</td></tr>",
          '<tpl if="previewImage != null">',
          "<tpl if=\"previewImage != ''\">",
          "<tr><td>",
          '<div border=0 ><img border=0 width="200" id="MasterParentViewPanel" height="200" src="{previewImage}" class="image-clickable" data-preview-url="{previewImage}" style="cursor: pointer;" title="Click to view full image"></img></div>',
          "</td></tr>",
          "</tpl>",
          "</tpl>",
          "</table>",
          "</div>"
        ),
        listeners: {
        afterrender: function(panel) {
            // Find all elements with the class 'image-clickable' within this panel
            var images = panel.getEl().query('.image-clickable');

            // Iterate through them and add a click listener
            Ext.each(images, function(imgEl) {
                Ext.get(imgEl).on('click', function() {
                    // Get the URL from the custom data attribute
                    var imageUrl = this.getAttribute('data-preview-url');
                    if (imageUrl) {
                        window.open(imageUrl, '_blank');
                    }
                });
            });
        }
    }
      });
    };
    var adManagementGrid = function () {
      var _adManagementGridstore = adManagementGridstore();
      var _adManagementFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "adzone_name",
          },
          {
            type: "string",
            dataIndex: "adzone_screen",
          },
          {
            type: "string",
            dataIndex: "adzone_status",
          },
          {
            type: "list",
            options: ["Active", "Inactive"],
            phpMode: true,
            dataIndex: "adzone_status",
          },
        ],
      });
      _adManagementFilter.remote = true;
      _adManagementFilter.autoReload = true;
      var _gridPanel = new Ext.grid.GridPanel({
        region: "center",
        layout: "fit",
        frame: false,
        border: false,
        loadMask: true,
        store: _adManagementGridstore,
        //iconCls: 'money',
        id: "adManagementGridPanel",
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          emptyText:
            '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        plugins: [new Ext.ux.grid.GroupSummary(), _adManagementFilter],
        columns: [
          new Ext.grid.RowNumberer(),
          {
            header: "ID",
            dataIndex: "adzone_id",
            sortable: true,
            tooltip: "ID",
            hidden: true,
          },
          {
            header: "Adzone Name",
            id: "Adzone_name_auto_exp",
            dataIndex: "adzone_name",
            sortable: true,
            tooltip: "Adzone Name",
            hideable: true,
          },
          {
            header: "Screen Name",
            dataIndex: "adzone_screen",
            sortable: true,
            tooltip: "Screen Name",
            hideable: true,
          },
          {
            header: "Status",
            dataIndex: "adzone_status",
            sortable: true,
            tooltip: "Status",
          },
        ],
        viewConfig: {
          forceFit: true,
        },
        bbar: new Ext.PagingToolbar({
          pageSize: RECS_PER_PAGE,
          store: _adManagementGridstore,
          displayInfo: true,
          displayMsg: "Displaying records {0} - {1} of {2}",
          emptyMsg: "No pages to display",
        }),
        sm: new Ext.grid.RowSelectionModel({
          singleSelect: true,
          listeners: {
            selectionchange: gridSelectionadManagementChanged,
          },
        }),
        listeners: {
          cellClick: function (grid, rowIndex, columnIndex, e) {
            var record = grid.getStore().getAt(rowIndex);
            var ID = record.get("adzone_id");
  
            if (!Ext.isEmpty(ID)) {
              Application.MyphaAdManagement.Cache.adzone_id = ID;
              Ext.getCmp("formpanelAdManagement").hide();
              Application.MyphaAdManagement.ViewAdManagementMode(ID);
            }
          },
          resize: onGridResize,
          afterrender: function () {
            _adManagementGridstore.load();
          },
        },
        tbar: [
          {
            text: "Create Adzone",
            tooltip: "Create Adzone",
            icon: "./resources/images/default/icons/add.png",
            iconCls: "my-icon1",
            handler: function () {
              Application.MyphaAdManagement.AdManagementAddEdit = "Add";
              var masterForm = Ext.getCmp("formpanelAdManagement").getForm();
              Ext.getCmp("admanagementpanel").setTitle("Create Adzone Details");
              loadedForm = null;
              masterForm.reset();
              Ext.getCmp("adzone_name").focus(false, 100);
              /*<?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */
              Ext.getCmp("buttonadManagementEdit").hide();
              Ext.getCmp("buttonadManagementSave").show();
              /*<?php } ?> */
              Ext.getCmp("buttonadManagementCancel").show();
              Ext.getCmp("formpanelAdManagement").show();
  
              Ext.getCmp("adzone_status").setValue(1);
              Ext.getCmp("xtemplateadManagementViewDetails").hide();
              Ext.getCmp("admanagementpanel").doLayout();
            },
          }
        ],
      });
      return _gridPanel;
    };
  
    var gridSelectionthemeManagementChanged = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp("themeManagementGridPanel")
            .getSelectionModel()
            .getSelections()
        )
      ) {
        var ID = Ext.getCmp("themeManagementGridPanel")
          .getSelectionModel()
          .getSelections()[0].data.theme_id;
        Application.MyphaAdManagement.ViewThemeManagementMode(ID);
      }
    };
    var themeManagementPanel = function (id) {
      var _adPanel = new Ext.Panel({
        frame: false,
        hideBorders: true,
        layout: "border",
        border: false,
        title: "Theme",
        id: id,
        //iconCls: 'my-icon444',
        items: [
          themeManagementGrid(),
          new Ext.Panel({
            title: "Theme Details",
            frame: false,
            border: true,
            region: "east",
            width: winsize.width * 0.4,
            autoScroll: true,
            cls: "left_side_panel",
            id: "thememanagementpanel",
            height: winsize.height * 0.6,
            items: [themeManagementDetailsView()],
            buttonAlign: "right",
            fbar: [
              {
                text: "Cancel",
                tabIndex: 505,
                cls: "left-right-buttons",
                icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                id: "buttonthemeManagementCancel",
                hidden: true,
                handler: function () {
                  if (
                    !Ext.isEmpty(
                      Ext.getCmp("themeManagementGridPanel")
                        .getSelectionModel()
                        .getSelections()
                    )
                  ) {
                    var ID = Ext.getCmp("themeManagementGridPanel")
                      .getSelectionModel()
                      .getSelections()[0].data.theme_id;
                    Application.MyphaAdManagement.ViewThemeManagementMode(ID);
                  }
                },
              },
              /* <?php if (user_access("mypha_ad_management", "saveThemeManagement")) { ?> */ {
                text: "Edit",
                cls: "left-right-buttons",
                id: "buttonthemeManagementEdit",
                icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
                tabIndex: 504,
                handler: function () {
                  var ID = Ext.getCmp("themeManagementGridPanel")
                    .getSelectionModel()
                    .getSelections()[0].data.theme_id;
                    var img_url = "/resources/images/default.png";
                    var thumpurl = "/resources/images/default.png";
                    
                    Ext.Ajax.request({
                      url: modURL + '&op=getDesignImages',
                      method: 'POST',
                      params: {
                          themeId:ID
                      },
                      success: function (res) {
                          var tmp = Ext.decode(res.responseText);
                          var img_url, thumpurl;
                          img_url = '/resources/images/default.png';
                          
                          
                          themeDesignWindow(ID, img_url, thumpurl);
                          
                      }
                  })
                },
              },
              {
                text: "Save",
                tabIndex: 504,
                cls: "left-right-buttons",
                id: "buttonthemeManagementSave",
                icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                hidden: true,
                handler: function () {
                  Application.MyphaAdManagement.saveThemeManagement();
                },
              } /*<?php  } ?>*/,
            ],
          }),
        ],
      });
      return _adPanel;
    };
    var themeManagementGridstore = function () {
      var _thememanagementList = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listthemeManagement",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "theme_id",
            root: "data",
          },
          ["theme_id", "theme_name", "theme_description", "theme_status","themeAvailable","store_group_name"]
        ),
        sortInfo: {
          field: "theme_id",
          direction: "ASC",
        },
        groupField: "",
        groupDir: "ASC",
        remoteSort: true,
        autoLoad: false,
        root: "data",
        listeners: {
          load: function () {
            Ext.getCmp("themeManagementGridPanel")
              .getSelectionModel()
              .selectRow(0);
          },
        },
      });
      return _thememanagementList;
    };
    var themeRetailCategoryStore = function () {
      var natureOfGroupStore = new Ext.data.JsonStore({
        url: modURL + "&op=themeRetailCategories",
        method: "post",
        fields: ["business_type_id", "business_type_name"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
      });
      return natureOfGroupStore;
    };
    var themeManagementForm = function () {
      var themeRCstore = themeRetailCategoryStore();
      var _themeManagementForm = new Ext.FormPanel({
        id: "formpanelThemeManagement",
        frame: false,
        border: false,
        hidden: true,
        labelAlign: "top",
        autoHeight: true,
        fileUpload: true,
        labelWidth: 100,
        layout: "column",
        bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
        items: [
          {
            columnWidth: 1,
            layout: "form",
            border: false,
            items: [
              {
                xtype: "spacer",
                height: 10,
              },
              {
                xtype: "textfield",
                fieldLabel: "Theme Name",
                id: "theme_name",
                name: "n[theme_name]",
                anchor: "98%",
                allowBlank: false,
                emptyText: "Theme Name",
                width: 300,
                tabIndex: 500,
                maxLength: 300,
              },
              {
                xtype: "textarea",
                fieldLabel: "Description",
                id: "theme_description",
                name: "n[theme_description]",
                anchor: "98%",
                allowBlank: false,
                emptyText: "Description",
                width: 300,
                tabIndex: 500,
                maxLength: 300,
              },
              {
                xtype: "textfield",
                id: "theme_id",
                name: "n[theme_id]",
                hidden: true,
              },
            ],
          },
          mkCombo({
            type: STATUS_COMBO_DATA,
            value: "id",
            display: "text",
            name: "n[theme_status]",
            fieldLabel: "Status",
            tabIndex: 503,
            emptyText: "Set status..",
            id: "theme_status",
          }),
        ],
        listeners: {
          afterrender: function () {
            if (Ext.isEmpty(Ext.getCmp("theme_id").getValue())) {
              var recordSelected = Ext.getCmp("theme_status").getStore().getAt(0);
              Ext.getCmp("theme_status").setValue(recordSelected.get("id"));
            }
          },
        },
      });
      return _themeManagementForm;
    };
    function uuidv4() {
      return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
        /[xy]/g,
        function (c) {
          var r = (Math.random() * 16) | 0,
            v = c == "x" ? r : (r & 0x3) | 0x8;
          return v.toString(16);
        }
      );
    }
  
    var themeManagementDetailsView = function () {
      return new Ext.Panel({
        layout: "fit",
        border: false,
        hideBorders: true,
        autoHeight: true,
        id: "xtemplatethemeManagementViewDetails",
        tpl: new Ext.XTemplate(
          '<div class="details-outer">',
          '<table border="0" width="100%" class="details_view_table">',
          '<tr><th width="40%">Theme Name </th><td>  {theme_name} </td></tr>',
          '<tr><th width="40%">Description </th><td>  {theme_description} </td></tr>',
          '<tr><th width="40%">Status</th><td>',
          "<tpl if=\"theme_status == '1'\">Active</tpl>",
          "<tpl if=\"theme_status == '0'\">Inactive</tpl>",
          "</td></tr>",
          '<tr><th width="40%">Theme Files </th><td>  {zipContent} </td></tr>',
          "</table>",
          "</div>"
        ),
      });
    };
    var themeManagementGrid = function () {
      var _themeManagementGridstore = themeManagementGridstore();
      var _themeManagementFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "theme_name",
          },
          {
            type: "string",
            dataIndex: "theme_description",
          },
          {
            type: "string",
            dataIndex: "theme_status",
          },
          {
            type: "list",
            options: ["Active", "Inactive"],
            phpMode: true,
            dataIndex: "theme_status",
          },
        ],
      });
      _themeManagementFilter.remote = true;
      _themeManagementFilter.autoReload = true;
      var _gridPanel = new Ext.grid.GridPanel({
        region: "center",
        layout: "fit",
        frame: false,
        border: false,
        loadMask: true,
        store: _themeManagementGridstore,
        //iconCls: 'money',
        id: "themeManagementGridPanel",
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          emptyText:
            '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        plugins: [new Ext.ux.grid.GroupSummary(), _themeManagementFilter],
        columns: [
          new Ext.grid.RowNumberer(),
           {
            header: "ID",
            dataIndex: "theme_id",
            sortable: true,
            tooltip: "ID",
            hidden: true,
          },
          {
            header: "Theme Name",
            id: "Theme_name_auto_exp",
            dataIndex: "theme_name",
            sortable: true,
            tooltip: "Theme Name",
            hideable: true,
          },
          {
            header: "Description",
            dataIndex: "theme_description",
            sortable: true,
            tooltip: "Screen Name",
            hideable: true,
          },
          {
            header: "Status",
            dataIndex: "theme_status",
            sortable: true,
            tooltip: "Status",
          },{
            header: "Availablility",
            dataIndex: "themeAvailable",
            sortable: true,
            tooltip: "Availablility",
          },{
            header: "Store Group",
            dataIndex: "store_group_name",
            sortable: true,
            tooltip: "Store Group",
          },
        ],
        viewConfig: {
          forceFit: true,
        },
        bbar: new Ext.PagingToolbar({
          pageSize: RECS_PER_PAGE,
          store: _themeManagementGridstore,
          displayInfo: true,
          displayMsg: "Displaying records {0} - {1} of {2}",
          emptyMsg: "No pages to display",
        }),
        sm: new Ext.grid.RowSelectionModel({
          singleSelect: true,
          listeners: {
            selectionchange: gridSelectionthemeManagementChanged,
          },
        }),
        listeners: {
          cellClick: function (grid, rowIndex, columnIndex, e) {
            var record = grid.getStore().getAt(rowIndex);
            var ID = record.get("theme_id");
  
            if (!Ext.isEmpty(ID)) {
              Application.MyphaAdManagement.Cache.theme_id = ID;
              //Ext.getCmp("formpanelThemeManagement").hide();
              Application.MyphaAdManagement.ViewThemeManagementMode(ID);
            }
          },
          resize: onGridResize,
          afterrender: function () {
            _themeManagementGridstore.load();
          },
        },
        tbar: [
          {
            text: "Create Theme",
            tooltip: "Create Theme",
            icon: "./resources/images/default/icons/add.png",
            iconCls: "my-icon1",
            handler: function () {
              var img_url = "/resources/images/default.png";
              var thumpurl = "/resources/images/default.png";
              themeDesignWindow(0, img_url, thumpurl);
              Application.MyphaAdManagement.ThemeManagementAddEdit = "Add";
              /*var masterForm = Ext.getCmp('formpanelThemeManagement').getForm();
                          Ext.getCmp('thememanagementpanel').setTitle('Create Theme Details');
                          loadedForm = null;
                          masterForm.reset();
                          Ext.getCmp('theme_name').focus(false, 100);
                          Ext.getCmp('buttonthemeManagementEdit').hide();
                          Ext.getCmp('buttonthemeManagementSave').show();
                        
                          Ext.getCmp('buttonthemeManagementCancel').show();
                          Ext.getCmp('formpanelThemeManagement').show();
  
                          Ext.getCmp('theme_status').setValue(1);
                          Ext.getCmp('xtemplatethemeManagementViewDetails').hide();
                          Ext.getCmp('thememanagementpanel').doLayout();*/
            },
          },{
            text: "Common Theme Pages",
            tooltip: "Common Theme Pages",
            icon: "./resources/images/default/icons/add.png",
            iconCls: "my-icon1",
            handler: function () {
              var img_url = "/resources/images/default.png";
              var thumpurl = "/resources/images/default.png";
              themeCommonPageWindow(0, img_url, thumpurl);
              Application.MyphaAdManagement.ThemeManagementAddEdit = "Add";
              
            },
          }
        ],
      });
      return _gridPanel;
    };
    var themeDesignWindow = function (themeId, img_url, thumpurl) {
      if (themeId == "") var tit = "Create Theme Details";
      else var tit = "Edit Theme Details";
      var resultWindow = new Ext.Window({
        id: "windowThemeWindow",
        title: tit,
        shadow: false,
        width: 950,
        height: 650,
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        closable: false,
        items: [
          themeManagementDesignForm(),
          new Ext.Panel({
            layout: "fit",
            region: "center",
            width: 620,
            border: false,
            items: [designUploadForm(img_url, thumpurl)],
          }),
        ],
        buttons: [
          {
            text: "Cancel",
            id: "themecancel_btn",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            iconCls: "my-icon1",
            tabIndex: 505,
            handler: function () {
              resultWindow.close();
            },
          },
          {
            text: "Save",
            id: "themesave_btn",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            iconCls: "thes_save",
            tabIndex: 504,
            handler: function () {
              Application.MyphaAdManagement.saveThemeManagement();
            },
          },
        ],
        listeners: {
          afterrender: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var upload_type = 1;
            winLoadMask = new Ext.LoadMask(
              Ext.getCmp("windowThemeWindow").getEl()
            );
            winLoadMask.msg = "Please wait...";
            Ext.getCmp("formpanelThemeManagement")
              .getForm()
              .load({
                waitTitle: "Please Wait",
                waitMsg: "Loading...",
                url: modURL + "&op=get_img_s3_details",
                params: {
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                  themeId: themeId
                },
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  console.log('tmp');console.log(tmp);
                  Ext.getCmp('retailCategoryIds').setValue(tmp.data.retailCategoryIds);
                  Ext.getCmp('retailCategoryIds').setRawValue(tmp.data.retailCategorys);
                  Ext.getCmp('retailCategoryIds').getStore().load();
                  /*Ext.getCmp('theme_id').setValue(tmp.data.theme_id);
                  Ext.getCmp('theme_name').setValue(tmp.data.theme_name);
                  Ext.getCmp('theme_description').setValue(tmp.data.theme_description);
                  Ext.getCmp('theme_status').setValue(tmp.data.theme_status);
                  Ext.getCmp('retailCategoryIds').setValue(tmp.data.retailCategoryIds);
                  var imageBox = Ext.getCmp('home_box');  // Get the box component by its id
                  imageBox.dom.src = tmp.data.homePage_awsLocation;*/
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
            Ext.getCmp("designimageUploadForm")
              .getForm()
              .load({
                waitTitle: "Please Wait",
                waitMsg: "Loading...",
                url: modURL + "&op=get_img_s3_details",
                params: {
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                  themeId: themeId
                },
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  console.log('tmp');console.log(tmp);
                  
                  Ext.getCmp('home_box').getEl().dom.src = (tmp.data.homePage_awsLocation != ''?tmp.data.homePage_awsLocation:img_url);                
                  Ext.getCmp('search_box').getEl().dom.src = (tmp.data.searchPage_awsLocation != ''?tmp.data.searchPage_awsLocation:img_url);
                  Ext.getCmp('prdct_box').getEl().dom.src = (tmp.data.prdctPage_awsLocation != ''?tmp.data.prdctPage_awsLocation:img_url);
                  Ext.getCmp('cart_box').getEl().dom.src = (tmp.data.cartPage_awsLocation != ''?tmp.data.cartPage_awsLocation:img_url);
                  Ext.getCmp('checkOut_box').getEl().dom.src = (tmp.data.checkOutPage_awsLocation != ''?tmp.data.checkOutPage_awsLocation:img_url);
                  Ext.getCmp('itemView_box').getEl().dom.src = (tmp.data.itemView_awsLocation != ''?tmp.data.itemView_awsLocation:img_url);
                  Ext.getCmp('payment_box').getEl().dom.src = (tmp.data.payment_awsLocation != ''?tmp.data.payment_awsLocation:img_url);
                  Ext.getCmp('orderPage_box').getEl().dom.src = (tmp.data.walletPage_awsLocation != ''?tmp.data.walletPage_awsLocation:img_url);
                  Ext.getCmp('orderDetails_box').getEl().dom.src = (tmp.data.orderPage_awsLocation != ''?tmp.data.orderPage_awsLocation:img_url);
                  Ext.getCmp('walletPage_box').getEl().dom.src = (tmp.data.orderDetails_awsLocation != ''?tmp.data.orderDetails_awsLocation:img_url);
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
          },
        },
      });
      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    };
    var themeManagementDesignForm = function () {
      var themeRCstore = themeRetailCategoryStore();
      var _themeManagementForm = new Ext.FormPanel({
        id: "formpanelThemeManagement",
        frame: false,
        border: false,
        labelAlign: "top",
        autoHeight: true,
        fileUpload: true,
        labelWidth: 100,
        region: "north",
        bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
        items: [
          {
            xtype: "spacer",
            height: 10,
          },
          {
            xtype: "textfield",
            fieldLabel: "Theme Name",
            id: "theme_name",
            name: "theme_name",
            anchor: "98%",
            allowBlank: false,
            emptyText: "Theme Name",
            width: 300,
            tabIndex: 500,
            maxLength: 300,
          },
          {
            xtype: "textarea",
            fieldLabel: "Description",
            id: "theme_description",
            name: "theme_description",
            anchor: "98%",
            allowBlank: false,
            emptyText: "Description",
            width: 300,
            tabIndex: 500,
            maxLength: 300,
          },
          {
            xtype: "textfield",
            id: "theme_id",
            name: "theme_id",
            hidden: true,
          },
          {
            xtype: "lovcombo",
            fieldLabel: "Applicable Retail Categories",
            id: "retailCategoryIds",
            name: "retailCategoryIds",
            anchor: "98%",
            allowBlank: false,
            emptyText: "Applicable Retail Categories",
            width: 300,
            tabIndex: 500,
            displayField: "business_type_name",
            valueField: "business_type_id",
            store: themeRCstore,
            triggerAction: "all",
            selectOnFocus: true,
            mode: "local",
            forceSelection: true,
            tabIndex: 6,
            typeAhead: true,
            lazyRender: true,
            editable: true,
            minChars: 1,
          },
          mkCombo({
            type: STATUS_COMBO_DATA,
            value: "id",
            display: "text",
            name: "theme_status",
            fieldLabel: "Status",
            tabIndex: 503,
            emptyText: "Set status..",
            id: "theme_status",
          }),
        ],
        listeners: {
          afterrender: function () {
            if (Ext.isEmpty(Ext.getCmp("theme_id").getValue())) {
              var recordSelected = Ext.getCmp("theme_status").getStore().getAt(0);
              Ext.getCmp("theme_status").setValue(recordSelected.get("id"));
            }
          },
        },
      });
      return _themeManagementForm;
    };
    var designUploadForm = function (img, thumpurl) {
      return new Ext.Panel({
        id: "uploadformpanel",      
        items: [
          {
            xtype: "hidden",
            id: "themeFile_Location",
            name: "themeFile_Location",
          },
          {
            xtype: "hidden",
            id: "homePage_awsLocation",
            name: "homePage_awsLocation",
          },
          {
            xtype: "hidden",
            id: "searchPage_awsLocation",
            name: "searchPage_awsLocation",
          },{
              xtype: "hidden",
              id: "prdctPage_awsLocation",
              name: "prdctPage_awsLocation",
            },
          {
            xtype: "hidden",
            id: "cartPage_awsLocation",
            name: "cartPage_awsLocation",
          },{
              xtype: "hidden",
              id: "checkOutPage_awsLocation",
              name: "checkOutPage_awsLocation",
            },{
              xtype: "hidden",
              id: "itemView_awsLocation",
              name: "itemView_awsLocation",
            },{
              xtype: "hidden",
              id: "payment_awsLocation",
              name: "payment_awsLocation",
            },{
              xtype: "hidden",
              id: "walletPage_awsLocation",
              name: "walletPage_awsLocation",
            },{
              xtype: "hidden",
              id: "orderPage_awsLocation",
              name: "orderPage_awsLocation",
            },{
              xtype: "hidden",
              id: "orderDetails_awsLocation",
              name: "orderDetails_awsLocation",
            },
          {
            xtype: "hidden",
            id: "aws_file_bucket",
            name: "aws_file_bucket",
          },
          new Ext.form.FormPanel({
            id: "designimageUploadForm",
            layout: "form",
            fileUpload: true,
            bodyStyle: {
              "background-color": "white",
              padding: "5px 5px 5px 10px",
            },
            //autoHeight: true,
            height: 600,
            hidLabel: true,
            frame: true,
            items: [
              {
                layout: "column",
                border: false,
                style: "padding:5px;",
                items: [                  
                  {
                    columnWidth: 0.6,
                    layout: "form",
                    border: false,
                    labelAlign: "top",
                    items: [
                      {
                        xtype: "combo",                        
                        fieldLabel: "Upload Image for",
                        emptyText: "Choose Type",
                        id: "themeType",
                        name: "themeType",
                        labelStyle: mandatory_label,
                        allowBlank: false,
                        mode: "local",
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: "97%",
                        store: new Ext.data.JsonStore({
                          fields: ["id", "name"],
                          data: [
                            { id: "1", name: "Home Page" },
                            { id: "2", name: "Search" },
                            { id: "3", name: "Product" },
                            { id: "4", name: "Cart" },
                            { id: "5", name: "Checkout" },
                            { id: "6", name: "Itemview" },
                            { id: "7", name: "Payment" },
                            { id: "8", name: "My Order" },
                            { id: "9", name: "Order Details" },
                            { id: "10", name: "My Wallet" },
                          ],
                        }),
                        triggerAction: "all",
                        minChars: 2,
                        displayField: "name",
                        valueField: "id",
                        hiddenName: "themeType",
                        tabIndex: 1,
                        listeners: {
                          select: function () {},
                        },
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    border: false,
                    labelAlign: "top",
                    items: [
                      {
                        xtype: "fileuploadfield",                        
                        id: "associated_templatefile",
                        anchor: "98%",
                        name: "associated_templatefile",
                        allowBlank: true,
                        buttonOnly: true,
                        buttonCfg: {
                          text: "Upload Designs",
                          buttonAlign: "center",
                          style: "margin:5px;",
                        },
                        validator: function (v) {
                          if (v != "") {
                              var designType = Ext.getCmp("themeType").getValue();
                              if (Ext.isEmpty(designType)) {
                                  Ext.Msg.alert(
                                    "Notification",
                                    "Please choose design type to upload"
                                  );
                                  return;
                                }
                            v = v.toLowerCase();
                            var exp = /^.*\.(png|jpe?g|gif)$/i;
                            if (!exp.test(v)) {
                              Ext.Msg.alert(
                                "Notification",
                                "Upload a valid image file"
                              );
                              return;
                            }
  
                            var associated_templatefile = Ext.getCmp(
                              "associated_templatefile"
                            ).getValue();
                            if (associated_templatefile == "") {
                              Ext.Msg.alert(
                                "Notification",
                                "Please choose a scanned file to upload"
                              );
                              return;
                            }
  
                            //                                    var designimageUploadForm = Ext.getCmp('designimageUploadForm').getForm();
                            //                                    if (designimageUploadForm.isValid()) {
                            
                            switch (designType) {
                              case "1":
                                Ext.getCmp("home_box").hide();
                                break;
                              case "2":
                                Ext.getCmp("search_box").hide();
                                break;
                              case "3":
                                Ext.getCmp("prdct_box").hide();
                                break;
                              case "4":
                                Ext.getCmp("cart_box").hide();
                                break;
                              case "5":
                                Ext.getCmp("checkOut_box").hide();
                                break;
                              case "6":
                                Ext.getCmp("itemView_box").hide();
                                break;
                              case "7":
                                Ext.getCmp("payment_box").hide();
                                break;
                              case "8":
                                Ext.getCmp("orderPage_box").hide();
                                break;
                              case "9":
                                Ext.getCmp("orderDetails_box").hide();
                                break;
                              case "10":
                                Ext.getCmp("walletPage_box").hide();
                                break;
                            }
  
                            winLoadMask.show();
                            console.log("main here");
                            if (designType > 0) {
                              addDesigns(designType);
                            } else {
                              Ext.Msg.alert(
                                "Notification",
                                "Choose Design Type and Proceed."
                              );
                              winLoadMask.hide();
                              return;
                            }
  
                            //                                    }
                            return true;
                          }
                        },
                      },
                    ],
                  },{
                    columnWidth: 0.2,
                    layout: "form",
                    border: false,
                    labelAlign: "top",
                    items: [{
                        xtype: 'fileuploadfield',
                        id: 'theme_file',
                        anchor: '98%',
                        name: 'theme_file',
                        allowBlank: true,
                        buttonOnly: true,
                        buttonCfg: {
                            text: 'Upload Theme Zip',
                            width: 80
                        },
                        listeners: {
                            afterrender: function(field) {
                                field.fileInput.dom.setAttribute("accept", ".zip");  // restrict chooser
                            }
                        },
                        validator: function (v) {
                            if (v != '') {
                                v = v.toLowerCase();
                                var exp = /^.*\.zip$/i;
                                if (!(exp.test(v))) {
                                    Ext.Msg.alert("Notification", "Upload a valid image file");
                                    return;
                                }
    
                                var theme_file = Ext.getCmp('theme_file').getValue();
                                if (theme_file == '') {
                                    Ext.Msg.alert("Notification", "Please choose a file to upload");
                                    return;
                                }
                                winLoadMask.show();                              
                                console.log('themezip');
                                uploadThemeZip();
                                winLoadMask.hide();
                                return true;
                            }
                        }
                    }]
                  }
                ],
              },
              {
                layout: "column",
                border: false,
                items: [
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    height: 125,
                    style:"text-align:center",
                    border: false,
                    frame:false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Home",                        
                        id: "homePage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-event">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",                        
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "home_box",
                        autoEl: {
                          tag: "img",
                          src: img,
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    height: 125,
                    style:"text-align:center",
                    id: "searchPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Search",
                        id: "searchPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "search_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    style:"text-align:center",
                    height: 125,
                    id: "prdctPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Product",
                        id: "prdctPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "prdct_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    style:"text-align:center",
                    height: 125,
                    id: "cartPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Cart",
                        id: "cartPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "cart_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    style:"text-align:center",
                    height: 125,
                    id: "checkOutPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Checkout",
                        id: "checkOutPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "checkOut_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },
                ],
              },
              {
                  layout: "column",
                  border: false,
                  items: [
                    {
                      columnWidth: 0.2,
                      style:"text-align:center",
                      layout: "form",
                      height: 125,
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "Item View",
                          id: "itemView_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-event">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "itemView_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                        },
                      ],
                    },
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "payment_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "Payment",
                          id: "payment_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "payment_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "orderPage_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "My Order",
                          id: "orderPage_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "orderPage_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "orderDetails_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "Order Details",
                          id: "orderDetails_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "orderDetails_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "walletPage_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "My Wallet",
                          id: "walletPage_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "walletPage_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                  ],
                },
              {
                xtype: "hidden",
                id: "file_name",
                name: "file_name",
              },
              {
                xtype: "hidden",
                id: "grzBucketName",
                name: "grzBucketName",
              },
              {
                xtype: "hidden",
                id: "accessKey",
                name: "accessKey",
              },
              {
                xtype: "hidden",
                id: "secretKey",
                name: "secretKey",
              },
              {
                xtype: "hidden",
                id: "bucketRegion",
                name: "bucketRegion",
              },
              {
                xtype: "hidden",
                id: "oncompleteurl",
                name: "oncompleteurl",
              },
              {
                xtype: "hidden",
                id: "img_path_db",
                name: "img_path_db",
              },
            ],
          }),
        ],
      });
    };
    function addDesigns(type) {
      var grzBucketName = Ext.getCmp("grzBucketName").getValue();
      var bucketRegion = Ext.getCmp("bucketRegion").getValue();
      var filepath = Ext.getCmp("oncompleteurl").getValue();
      console.log(filepath);
      AWS.config.update({
        region: bucketRegion,
        credentials: new AWS.Credentials(
          Ext.getCmp("accessKey").getValue(),
          Ext.getCmp("secretKey").getValue(),
          null
        ),
      });
      var s3 = new AWS.S3({
        apiVersion: "2006-03-01",
        params: { Bucket: grzBucketName },
      });
      var files = document.getElementById("associated_templatefile-file").files;
      console.log(files);
      if (!files.length) {
        winLoadMask.hide();
        return alert("Please choose a file to upload first.");
      }
      var file = files[0];
      var filesize = files[0]["size"];
      var size = filesize / 1000;
  
      var actualfileName = file.name;
      var file_Name = JSON.stringify(actualfileName).slice(1, -1);
      var fileExt = file_Name.split(".").pop();
  
      var fileName = uuidv4();
      fileName = fileName + "." + fileExt;
      console.log(filesize);
      console.log(size);
      s3.upload(
        {
          Key: filepath + fileName,
          /*file_Name*/ /*from server*/ Body: file,
          ACL: "public-read",
        },
        function (err, data) {
          if (err) {
            winLoadMask.hide();
            //var img_src = Ext.BLANK_IMAGE_URL;
            var img_src_main = "/resources/images/awesomeupload/no_image.png";
            switch (type) {
              case "1":
                Ext.getCmp("homePage_image_panel").update({
                  img_src: img_src_main,
                });
                break;
              case "2":
                Ext.getCmp("searchPage_image_panel").update({
                  img_src: img_src_main,
                });
                break;
              case "3":
                Ext.getCmp("prdctPage_image_panel").update({
                  img_src: img_src_main,
                });
                break;
              case "4":
                  Ext.getCmp("cartPage_image_panel").update({
                    img_src: img_src_main,
                  });
                  break;
              case "5":
                  Ext.getCmp("checkOutPage_image_panel").update({
                    img_src: img_src_main,
                  });
                  break;
              case "6":
                Ext.getCmp("itemView_image_panel").update({
                    img_src: img_src_main,
                  });
                  break;
              case "7":
                  Ext.getCmp("payment_image_panel").update({
                          img_src: img_src_main,
                  });
                  break;
              case "8":
                  Ext.getCmp("orderPage_image_panel").update({
                          img_src: img_src_main,
                  });
                  break;
              case "9":
                  Ext.getCmp("orderDetails_image_panel").update({
                          img_src: img_src_main,
                  });
                  break;
              case "10":
                  Ext.getCmp("walletPage_image_panel").update({
                              img_src: img_src_main,
                      });
                  break;
            }
            return Ext.Msg.alert(
              "Notification",
              "There was an error uploading your photo: " + err.message
            );
          }
          if (!Ext.isEmpty(data.Location)) {
            var img = new Image();
            img.onload = function () {
              var flag = 1;
  
              if (flag == 1) {
                winLoadMask.hide();
                Ext.Msg.alert(
                  "Notification",
                  "File has been uploaded successfully."
                );
                Application.MyphaAdManagement.UploadedFileLocation =
                  data.Location;
                Application.MyphaAdManagement.UploadedFileBucket = data.Bucket;
                Ext.getCmp("aws_file_bucket").setValue(data.Bucket);
                switch (type) {
                  case "1":
                    Ext.getCmp("homePage_awsLocation").setValue(data.Location);
                    Ext.getCmp("homePage_image_panel").update({
                      img_src: Application.MyphaAdManagement.UploadedFileLocation,
                    });
                    break;
                  case "2":
                    Ext.getCmp("searchPage_awsLocation").setValue(data.Location);
                    Ext.getCmp("searchPage_image_panel").update({
                      img_src: Application.MyphaAdManagement.UploadedFileLocation,
                    });
                    break;
                  case "3":
                    Ext.getCmp("prdctPage_awsLocation").setValue(data.Location);
                    Ext.getCmp("prdctPage_image_panel").update({
                      img_src: Application.MyphaAdManagement.UploadedFileLocation,
                    });
                    break;
                  case "4":
                      Ext.getCmp("cartPage_awsLocation").setValue(data.Location);
                      Ext.getCmp("cartPage_image_panel").update({
                        img_src: Application.MyphaAdManagement.UploadedFileLocation,
                      });
                  break;
                  case "5":
                      Ext.getCmp("checkOutPage_awsLocation").setValue(data.Location);
                      Ext.getCmp("checkOutPage_image_panel").update({
                        img_src: Application.MyphaAdManagement.UploadedFileLocation,
                      });
                      break;
                  case "6":
                      Ext.getCmp("itemView_awsLocation").setValue(data.Location);
                      Ext.getCmp("itemView_image_panel").update({
                        img_src: Application.MyphaAdManagement.UploadedFileLocation,
                      });
                      break;
                  case "7":
                      Ext.getCmp("payment_awsLocation").setValue(data.Location);
                      Ext.getCmp("payment_image_panel").update({
                        img_src: Application.MyphaAdManagement.UploadedFileLocation,
                      });
                      break;
                  case "8":
                      Ext.getCmp("orderPage_awsLocation").setValue(data.Location);
                      Ext.getCmp("orderPage_image_panel").update({
                        img_src: Application.MyphaAdManagement.UploadedFileLocation,
                      });
                      break;
                  case "9":
                      Ext.getCmp("orderDetails_awsLocation").setValue(data.Location);
                      Ext.getCmp("orderDetails_image_panel").update({
                        img_src: Application.MyphaAdManagement.UploadedFileLocation,
                      });
                      break;
                  case "10":
                      Ext.getCmp("walletPage_awsLocation").setValue(data.Location);
                      Ext.getCmp("walletPage_image_panel").update({
                        img_src: Application.MyphaAdManagement.UploadedFileLocation,
                      });
                      break;
                      break;
                }
              }
            };
            img.src = data.Location;
          } else {
            var img_src_main = "/resources/images/awesomeupload/no_image.png";
            var img_src_list = "/resources/images/awesomeupload/no_image.png";
            switch (type) {
              case "main":
                Ext.getCmp("homePage_image_panel").update({
                  img_src: img_src_main,
                });
                break;
              case "thump":
                Ext.getCmp("searchPage_image_panel").update({
                  img_src: img_src_list,
                });
                break;
            }
          }
        }
      );
    }
    function uploadThemeZip() {
    var grzBucketName = Ext.getCmp('grzBucketName').getValue();
    var bucketRegion = Ext.getCmp('bucketRegion').getValue();
    var filepath = Ext.getCmp('oncompleteurl').getValue(); // should end with `themes/`

    AWS.config.update({
        region: bucketRegion,
        credentials: new AWS.Credentials(
            Ext.getCmp('accessKey').getValue(),
            Ext.getCmp('secretKey').getValue(),
            null
        )
    });

    var s3 = new AWS.S3({
        apiVersion: '2006-03-01',
        params: { Bucket: grzBucketName }
    });

    var files = document.getElementById('theme_file-file').files;
    console.log(files);
    if (!files.length) {
        return Ext.Msg.alert("Notification", 'Please choose a ZIP file to upload.');
    }

    var file = files[0];
    console.log(file);
    var actualfileName = file.name;
    var fileExt = actualfileName.split('.').pop().toLowerCase();

    if (fileExt !== 'zip') {
        return Ext.Msg.alert("Notification", 'Only .zip files are allowed.');
    }

    var themeName = actualfileName.replace('.zip', '');
    var s3Key = filepath + themeName + '.zip'; // e.g., themes/darkmode.zip

    // Optional: Validate size
    var fileSizeMB = file.size / (1024 * 1024);
    if (fileSizeMB > 50) {
        return Ext.Msg.alert("Notification", 'ZIP file size should not exceed 50MB.');
    }

    var mask = new Ext.LoadMask(Ext.getBody(), { msg: "Uploading ZIP..." });
    mask.show();

    s3.upload({
        Key: s3Key,
        Body: file,
        ContentType: 'application/zip',
        ACL: 'private' // or 'public-read' if needed
    }, function (err, data) {
        mask.hide();

        if (err) {
            console.error(err);
            return Ext.Msg.alert("Upload Failed", 'Error uploading ZIP: ' + err.message);
        }

        console.log("ZIP uploaded to: " + data.Location);
        Ext.Msg.alert("Upload Success", 'Theme zip uploaded');

        // Save or update values as needed
        Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
        Ext.getCmp('themeFile_Location').setValue(data.Location);
    });
    }
    var themeCommonPageWindow = function (themeId, img_url, thumpurl) {
      if (themeId == "") var tit = "Theme Common Pages";
      else var tit = "Theme Common Pages";
      var resultWindow = new Ext.Window({
        id: "commonThemeWindow",
        title: tit,
        shadow: false,
        width: 950,
        height: 450,
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        closable: false,
        items: [commonDesignUploadForm(img_url, thumpurl)
        ],
        buttons: [
          {
            text: "Cancel",
            id: "themecancel_btn",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            iconCls: "my-icon1",
            tabIndex: 505,
            handler: function () {
              resultWindow.close();
            },
          },
          {
            text: "Save",
            id: "themesave_btn",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            iconCls: "thes_save",
            tabIndex: 504,
            handler: function () {
              Application.MyphaAdManagement.saveCommonThemePages();
            }
          }
        ],
        listeners: {
          afterrender: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var upload_type = 1;
            winLoadMask = new Ext.LoadMask(
              Ext.getCmp("commonThemeWindow").getEl()
            );
            winLoadMask.msg = "Please wait...";
            
            Ext.getCmp("commonDesignimageUploadForm")
              .getForm()
              .load({
                waitTitle: "Please Wait",
                waitMsg: "Loading...",
                url: modURL + "&op=get_img_s3_details",
                params: {
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                  themeId: 'Grozeo',
                  isCommonPage:1
                },
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  console.log('tmp');console.log(tmp);
                  
                  Ext.getCmp('home_box').getEl().dom.src = (tmp.data.homePage_awsLocation != ''?tmp.data.homePage_awsLocation:img_url);                
                  Ext.getCmp('search_box').getEl().dom.src = (tmp.data.searchPage_awsLocation != ''?tmp.data.searchPage_awsLocation:img_url);
                  Ext.getCmp('prdct_box').getEl().dom.src = (tmp.data.prdctPage_awsLocation != ''?tmp.data.prdctPage_awsLocation:img_url);
                  Ext.getCmp('cart_box').getEl().dom.src = (tmp.data.cartPage_awsLocation != ''?tmp.data.cartPage_awsLocation:img_url);
                  Ext.getCmp('checkOut_box').getEl().dom.src = (tmp.data.checkOutPage_awsLocation != ''?tmp.data.checkOutPage_awsLocation:img_url);
                  Ext.getCmp('itemView_box').getEl().dom.src = (tmp.data.itemView_awsLocation != ''?tmp.data.itemView_awsLocation:img_url);
                  Ext.getCmp('payment_box').getEl().dom.src = (tmp.data.payment_awsLocation != ''?tmp.data.payment_awsLocation:img_url);
                  Ext.getCmp('orderPage_box').getEl().dom.src = (tmp.data.walletPage_awsLocation != ''?tmp.data.walletPage_awsLocation:img_url);
                  Ext.getCmp('orderDetails_box').getEl().dom.src = (tmp.data.orderPage_awsLocation != ''?tmp.data.orderPage_awsLocation:img_url);
                  Ext.getCmp('walletPage_box').getEl().dom.src = (tmp.data.orderDetails_awsLocation != ''?tmp.data.orderDetails_awsLocation:img_url);
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
          },
        },
      });
      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    };
    var commonDesignUploadForm = function (img, thumpurl) {
      return new Ext.Panel({
        id: "uploadformpanel", 
        region:'center',     
        items: [
          {
            xtype: "hidden",
            id: "themeFile_Location",
            name: "themeFile_Location",
          },
          {
            xtype: "hidden",
            id: "homePage_awsLocation",
            name: "homePage_awsLocation",
          },
          {
            xtype: "hidden",
            id: "searchPage_awsLocation",
            name: "searchPage_awsLocation",
          },{
              xtype: "hidden",
              id: "prdctPage_awsLocation",
              name: "prdctPage_awsLocation",
            },
          {
            xtype: "hidden",
            id: "cartPage_awsLocation",
            name: "cartPage_awsLocation",
          },{
              xtype: "hidden",
              id: "checkOutPage_awsLocation",
              name: "checkOutPage_awsLocation",
            },{
              xtype: "hidden",
              id: "itemView_awsLocation",
              name: "itemView_awsLocation",
            },{
              xtype: "hidden",
              id: "payment_awsLocation",
              name: "payment_awsLocation",
            },{
              xtype: "hidden",
              id: "walletPage_awsLocation",
              name: "walletPage_awsLocation",
            },{
              xtype: "hidden",
              id: "orderPage_awsLocation",
              name: "orderPage_awsLocation",
            },{
              xtype: "hidden",
              id: "orderDetails_awsLocation",
              name: "orderDetails_awsLocation",
            },
          {
            xtype: "hidden",
            id: "aws_file_bucket",
            name: "aws_file_bucket",
          },
          new Ext.form.FormPanel({
            id: "commonDesignimageUploadForm",
            layout: "form",
            fileUpload: true,
            bodyStyle: {
              "background-color": "white",
              padding: "5px 5px 5px 10px",
            },
            //autoHeight: true,
            height: 600,
            hidLabel: true,
            frame: true,
            items: [
              {
                layout: "column",
                border: false,
                style: "padding:5px;",
                items: [                  
                  {
                    columnWidth: 0.6,
                    layout: "form",
                    border: false,
                    labelAlign: "top",
                    items: [
                      {
                        xtype: "combo",                        
                        fieldLabel: "Upload Image for",
                        emptyText: "Choose Type",
                        id: "themeType",
                        name: "themeType",
                        labelStyle: mandatory_label,
                        mode: "local",
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: "97%",
                        store: new Ext.data.JsonStore({
                          fields: ["id", "name"],
                          data: [
                            //{ id: "1", name: "Home Page" },
                            { id: "2", name: "Search" },
                            { id: "3", name: "Product" },
                            { id: "4", name: "Cart" },
                            { id: "5", name: "Checkout" },
                            { id: "6", name: "Itemview" },
                            { id: "7", name: "Payment" },
                            { id: "8", name: "My Order" },
                            { id: "9", name: "Order Details" },
                            { id: "10", name: "My Wallet" },
                          ],
                        }),
                        triggerAction: "all",
                        minChars: 2,
                        displayField: "name",
                        valueField: "id",
                        hiddenName: "themeType",
                        tabIndex: 1,
                        listeners: {
                          select: function () {},
                        },
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    border: false,
                    labelAlign: "top",
                    items: [
                      {
                        xtype: "fileuploadfield",                        
                        id: "associated_templatefile",
                        anchor: "98%",
                        name: "associated_templatefile",
                        allowBlank: true,
                        buttonOnly: true,
                        buttonCfg: {
                          text: "Upload Designs",
                          buttonAlign: "center",
                          style: "margin:5px;",
                        },
                        validator: function (v) {
                          if (v != "") {
                              var designType = Ext.getCmp("themeType").getValue();
                              if (Ext.isEmpty(designType)) {
                                  Ext.Msg.alert(
                                    "Notification",
                                    "Please choose design type to upload"
                                  );
                                  return;
                                }
                            v = v.toLowerCase();
                            var exp = /^.*\.(png|jpe?g|gif)$/i;
                            if (!exp.test(v)) {
                              Ext.Msg.alert(
                                "Notification",
                                "Upload a valid image file"
                              );
                              return;
                            }
  
                            var associated_templatefile = Ext.getCmp(
                              "associated_templatefile"
                            ).getValue();
                            if (associated_templatefile == "") {
                              Ext.Msg.alert(
                                "Notification",
                                "Please choose a scanned file to upload"
                              );
                              return;
                            }
  
                            //                                    var designimageUploadForm = Ext.getCmp('designimageUploadForm').getForm();
                            //                                    if (designimageUploadForm.isValid()) {
                            
                            switch (designType) {
                              case "1":
                                Ext.getCmp("home_box").hide();
                                break;
                              case "2":
                                Ext.getCmp("search_box").hide();
                                break;
                              case "3":
                                Ext.getCmp("prdct_box").hide();
                                break;
                              case "4":
                                Ext.getCmp("cart_box").hide();
                                break;
                              case "5":
                                Ext.getCmp("checkOut_box").hide();
                                break;
                              case "6":
                                Ext.getCmp("itemView_box").hide();
                                break;
                              case "7":
                                Ext.getCmp("payment_box").hide();
                                break;
                              case "8":
                                Ext.getCmp("orderPage_box").hide();
                                break;
                              case "9":
                                Ext.getCmp("orderDetails_box").hide();
                                break;
                              case "10":
                                Ext.getCmp("walletPage_box").hide();
                                break;
                            }
  
                            winLoadMask.show();
                            console.log("main here");
                            if (designType > 0) {
                              addDesigns(designType);
                            } else {
                              Ext.Msg.alert(
                                "Notification",
                                "Choose Design Type and Proceed."
                              );
                              winLoadMask.hide();
                              return;
                            }
  
                            //                                    }
                            return true;
                          }
                        },
                      },
                    ],
                  }
                ],
              },
              {
                layout: "column",
                border: false,
                items: [
                  {
                    hidden:true,
                    columnWidth: 0.2,
                    layout: "form",
                    height: 125,
                    style:"text-align:center",
                    border: false,
                    frame:false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Home",                        
                        id: "homePage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-event">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",                        
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "home_box",
                        autoEl: {
                          tag: "img",
                          src: img,
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    height: 125,
                    style:"text-align:center",
                    id: "searchPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Search",
                        id: "searchPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "search_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    style:"text-align:center",
                    height: 125,
                    id: "prdctPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Product",
                        id: "prdctPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "prdct_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    style:"text-align:center",
                    height: 125,
                    id: "cartPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Cart",
                        id: "cartPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "cart_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },
                  {
                    columnWidth: 0.2,
                    layout: "form",
                    style:"text-align:center",
                    height: 125,
                    id: "checkOutPage_image_panel_form",
                    border: false,
                    items: [
                      new Ext.Panel({
                        layout: "fit",
                        title: "Checkout",
                        id: "checkOutPage_image_panel",
                        tpl: new Ext.XTemplate(
                          '<div class="details-outer-events">',
                          '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                          "</div>"
                        ),
                      }),
                      {
                        xtype: "box",
                        style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                        id: "checkOut_box",
                        autoEl: {
                          tag: "img",
                          src: img
                        },listeners: {
                          afterrender: function (box) {
                            box.getEl().on('click', function () {
                              var currentImgSrc = box.getEl().dom.src;
                              Application.MyphaAdManagement.viewTheme(currentImgSrc);
                            });
                          }
                        }
                        //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                      },
                    ],
                  },{
                      columnWidth: 0.2,
                      style:"text-align:center",
                      layout: "form",
                      height: 125,
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "Item View",
                          id: "itemView_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-event">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "itemView_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                        },
                      ],
                    },
                ],
              },
              {
                  layout: "column",
                  border: false,
                  items: [
                    
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "payment_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "Payment",
                          id: "payment_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "payment_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "orderPage_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "My Order",
                          id: "orderPage_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "orderPage_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "orderDetails_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "Order Details",
                          id: "orderDetails_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "orderDetails_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                    {
                      columnWidth: 0.2,
                      layout: "form",
                      style:"text-align:center",
                      height: 125,
                      id: "walletPage_image_panel_form",
                      border: false,
                      items: [
                        new Ext.Panel({
                          layout: "fit",
                          title: "My Wallet",
                          id: "walletPage_image_panel",
                          tpl: new Ext.XTemplate(
                            '<div class="details-outer-events">',
                            '<img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="{img_src}"></img>',
                            "</div>"
                          ),
                        }),
                        {
                          xtype: "box",
                          style:"width: auto; height: auto; max-width: 100%; max-height: 100%;",
                          id: "walletPage_box",
                          autoEl: {
                            tag: "img",
                            src: img
                          },listeners: {
                            afterrender: function (box) {
                              box.getEl().on('click', function () {
                                var currentImgSrc = box.getEl().dom.src;
                                Application.MyphaAdManagement.viewTheme(currentImgSrc);
                              });
                            }
                          }
                          //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}
                        },
                      ],
                    },
                  ],
                },
              {
                xtype: "hidden",
                id: "file_name",
                name: "file_name",
              },
              {
                xtype: "hidden",
                id: "grzBucketName",
                name: "grzBucketName",
              },
              {
                xtype: "hidden",
                id: "accessKey",
                name: "accessKey",
              },
              {
                xtype: "hidden",
                id: "secretKey",
                name: "secretKey",
              },
              {
                xtype: "hidden",
                id: "bucketRegion",
                name: "bucketRegion",
              },
              {
                xtype: "hidden",
                id: "oncompleteurl",
                name: "oncompleteurl",
              },
              {
                xtype: "hidden",
                id: "img_path_db",
                name: "img_path_db",
              },
            ],
          }),
        ],
      });
    };
    return {
      Cache: {},
      initManagement: function () {
        var _adManagementPanelId = "panelAdManagement";
        var _adManagementPanel = Ext.getCmp(_adManagementPanelId);
        if (Ext.isEmpty(_adManagementPanel)) {
          _adManagementPanel = adManagementPanel(_adManagementPanelId);
          Application.UI.addTab(_adManagementPanel);
          _adManagementPanel.doLayout();
        } else {
          Application.UI.addTab(_adManagementPanel);
        }
      },
      EditAdManagementView: function () {
        Application.MyphaAdManagement.AdManagementAddEdit = "Edit";
        Ext.getCmp("admanagementpanel").doLayout();
        Ext.getCmp("admanagementpanel").setTitle("Edit Adzone Details");
        Ext.getCmp("formpanelAdManagement").show();
        Ext.getCmp("xtemplateadManagementViewDetails").hide();
        /*<?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */
        Ext.getCmp("buttonadManagementEdit").hide();
        Ext.getCmp("buttonadManagementSave").show();
        /*<?php } ?> */
        Ext.getCmp("buttonadManagementCancel").show();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(arguments[0])) {
          Application.MyphaAdManagement.UploadedPreview="";
          var masterForm = Ext.getCmp("formpanelAdManagement").getForm();
          masterForm.load({
            params: {
              adzone_id: arguments[0],
              apikey: _SESSION.apikey,
              tstamp: t_stamp,
            },
            url: modURL + "&op=admanagement_load",
            waitMsg: "Loading...",
            success: function (form, action) {
              var tmp = Ext.decode(action.response.responseText);
              Application.MyphaAdManagement.UploadedPreview = tmp.data.previewImage;
            },
            failure: function (form, action) {
              Ext.Msg.alert("Error.", "This error");
            },
          });
        }
      },
      saveAdManagement: function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var store_form = Ext.getCmp("formpanelAdManagement").getForm();
        if (store_form.isValid()) {
          store_form.submit({
            url: modURL + "&op=saveAdManagement",
            waitMsg: "Saving Details....",
            waitTitle: "Please Wait...",
            params: {
              apikey: _SESSION.apikey,
              tstamp: t_stamp,
            },
            success: function (response, action) {
              var tmp = Ext.decode(action.response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (Application.MyphaAdManagement.AdManagementAddEdit == "Add") {
                  recs_per_page = updateRecsPerPage(
                    Ext.getCmp("adManagementGridPanel")
                  );
                  Ext.getCmp("formpanelAdManagement").getForm().reset();
                  Ext.getCmp("adManagementGridPanel").store.reload({
                    params: {
                      start: 0,
                      limit: recs_per_page,
                    },
                  });
                } else {
                  Ext.getCmp(
                    "adManagementGridPanel"
                  ).selModel.getSelected().data = tmp.data;
                  Ext.getCmp("adManagementGridPanel").getStore().load();
                }
                Application.MyphaAdManagement.AdManagementAddEdit = "";
                Application.MyphaAdManagement.ViewAdManagementMode(
                  tmp.data.adzone_id
                );
              } else if (tmp.success === true && tmp.valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else if (tmp.success === true && tmp.img_valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
            failure: function (elm, conf) {
              if (conf.failureType === "server") {
                var result = Ext.decode(conf.response.responseText);
                Ext.Msg.alert("Error", result.message);
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Please enter all required fields"
                );
              }
            },
          });
        } else {
          Ext.MessageBox.alert(
            "Notification",
            "Please enter all required fields"
          );
        }
      },
      ViewAdManagementMode: function () {
        var adzone_id = arguments[0];
        /*<?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */
        Ext.getCmp("buttonadManagementEdit").show();
        Ext.getCmp("buttonadManagementSave").hide();
        /*<?php } ?> */
        Ext.getCmp("admanagementpanel").setTitle("View Adzone Details");
        Ext.getCmp("buttonadManagementCancel").hide();
        Ext.getCmp("formpanelAdManagement").hide();
        Ext.getCmp("xtemplateadManagementViewDetails").show();
        Ext.getCmp("admanagementpanel").doLayout();
        Ext.Ajax.request({
          url: modURL + "&op=adManagementDetailsView",
          method: "POST",
          params: { adzone_id: adzone_id },
          success: function (res) {
            var tmp = Ext.decode(res.responseText);
            if (tmp.success === true) {
              var visualsDescPanel = Ext.getCmp(
                "xtemplateadManagementViewDetails"
              );
              visualsDescPanel.update(tmp);
            }
            Ext.getCmp("admanagementpanel").doLayout();
          },
          failure: function () {
            Ext.MessageBox.alert("Error", "Error occured while sending data");
          },
        });
        Ext.getCmp("admanagementpanel").doLayout();
      },
      initThemes: function () {
        var _themeManagementPanelId = "panelThemesManagement";
        var _themeManagementPanel = Ext.getCmp(_themeManagementPanelId);
        if (Ext.isEmpty(_themeManagementPanel)) {
          _themeManagementPanel = themeManagementPanel(_themeManagementPanelId);
          Application.UI.addTab(_themeManagementPanel);
          _themeManagementPanel.doLayout();
        } else {
          Application.UI.addTab(_themeManagementPanel);
        }
      },
      EditThemeManagementView: function () {
        Application.MyphaAdManagement.ThemeManagementAddEdit = "Edit";
        Ext.getCmp("thememanagementpanel").doLayout();
        Ext.getCmp("thememanagementpanel").setTitle("Edit Adzone Details");
        //Ext.getCmp("formpanelThemeManagement").show();
        Ext.getCmp("xtemplatethemeManagementViewDetails").hide();
        /*<?php if (user_access("mypha_ad_management", "saveThemeManagement")) { ?> */
        Ext.getCmp("buttonthemeManagementEdit").hide();
        Ext.getCmp("buttonthemeManagementSave").show();
        /*<?php } ?> */
        Ext.getCmp("buttonthemeManagementCancel").show();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(arguments[0])) {
          var masterForm = Ext.getCmp("formpanelThemeManagement").getForm();
          masterForm.load({
            params: {
              theme_id: arguments[0],
              apikey: _SESSION.apikey,
              tstamp: t_stamp,
            },
            url: modURL + "&op=themeManagement_load",
            waitMsg: "Loading...",
            success: function (form, action) {
              var tmp = Ext.decode(action.response.responseText);
            },
            failure: function (form, action) {
              Ext.Msg.alert("Error.", "This error");
            },
          });
        }
      },
      saveThemeManagement: function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var store_form = Ext.getCmp("formpanelThemeManagement").getForm();
        if (store_form.isValid()) {
          themeDesigns = [];
          themeDesigns[1] = Ext.getCmp('homePage_awsLocation').getValue();
          themeDesigns[2] = Ext.getCmp('searchPage_awsLocation').getValue();
          themeDesigns[3] = Ext.getCmp('prdctPage_awsLocation').getValue();
          themeDesigns[4] = Ext.getCmp('cartPage_awsLocation').getValue();
          themeDesigns[5] = Ext.getCmp('checkOutPage_awsLocation').getValue();
          themeDesigns[6] = Ext.getCmp('itemView_awsLocation').getValue();
          themeDesigns[7] = Ext.getCmp('payment_awsLocation').getValue();
          themeDesigns[8] = Ext.getCmp('walletPage_awsLocation').getValue();
          themeDesigns[9] = Ext.getCmp('orderPage_awsLocation').getValue();
          themeDesigns[10] = Ext.getCmp('orderDetails_awsLocation').getValue();
          
          store_form.submit({
            url: modURL + "&op=saveThemeManagement",
            waitMsg: "Saving Details....",
            waitTitle: "Please Wait...",
            params: {
              apikey: _SESSION.apikey,
              tstamp: t_stamp,
              themeDesigns:Ext.encode(themeDesigns),
              retailCategorys: Ext.getCmp('retailCategoryIds').getValue(),
              themeFile_Location:Ext.getCmp('themeFile_Location').getValue()
            },
            success: function (response, action) {
              var tmp = Ext.decode(action.response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (
                  Application.MyphaAdManagement.ThemeManagementAddEdit == "Add"
                ) {
                  recs_per_page = updateRecsPerPage(
                    Ext.getCmp("themeManagementGridPanel")
                  );
                  Ext.getCmp("formpanelThemeManagement").getForm().reset();
                  Ext.getCmp("themeManagementGridPanel").store.reload({
                    params: {
                      start: 0,
                      limit: recs_per_page,
                    },
                  });
                  Ext.getCmp('windowThemeWindow').close();
                } else {
                  Ext.getCmp(
                    "themeManagementGridPanel"
                  ).selModel.getSelected().data = tmp.data;
                  Ext.getCmp("themeManagementGridPanel").getStore().load();
                }
                Application.MyphaAdManagement.ThemeManagementAddEdit = "";
                Application.MyphaAdManagement.ViewThemeManagementMode(
                  tmp.data.adzone_id
                );
                Ext.getCmp('windowThemeWindow').close();
              } else if (tmp.success === true && tmp.valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else if (tmp.success === true && tmp.img_valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
            failure: function (elm, conf) {
              if (conf.failureType === "server") {
                var result = Ext.decode(conf.response.responseText);
                Ext.Msg.alert("Error", result.message);
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Please enter all required fields"
                );
              }
            },
          });
        } else {
          Ext.MessageBox.alert(
            "Notification",
            "Please enter all required fields"
          );
        }
      },
      ViewThemeManagementMode: function () {
        var theme_id = arguments[0];
        /*<?php if (user_access("mypha_ad_management", "saveThemeManagement")) { ?> */
        Ext.getCmp("buttonthemeManagementEdit").show();
        Ext.getCmp("buttonthemeManagementSave").hide();
        /*<?php } ?> */
        Ext.getCmp("thememanagementpanel").setTitle("View Theme Details");
        Ext.getCmp("buttonthemeManagementCancel").hide();
        //Ext.getCmp("formpanelThemeManagement").hide();
        Ext.getCmp("xtemplatethemeManagementViewDetails").show();
        Ext.getCmp("thememanagementpanel").doLayout();
        Ext.Ajax.request({
          url: modURL + "&op=themeManagementDetailsView",
          method: "POST",
          params: { theme_id: theme_id },
          success: function (res) {
            var tmp = Ext.decode(res.responseText);
            if (tmp.success === true) {
              var visualsDescPanel = Ext.getCmp(
                "xtemplatethemeManagementViewDetails"
              );
              visualsDescPanel.update(tmp);
            }
            Ext.getCmp("thememanagementpanel").doLayout();
          },
          failure: function () {
            Ext.MessageBox.alert("Error", "Error occured while sending data");
          },
        });
        Ext.getCmp("thememanagementpanel").doLayout();
      },viewTheme : function(path){
        var extension = path.split(".").pop();
        var embedhtml ;
        switch(extension){
            case 'pdf':
                path = "https://mozilla.github.io/pdf.js/web/viewer.html?file="+path;
                embedhtml = '<iframe src="'+path+'" width="100%" height="100%" style="border: none;"></iframe>';
                break;
            case 'docx':
                path = "https://docs.google.com/viewer?url="+path+"&embedded=true";
                embedhtml = '<iframe src="'+path+'" width="100%" height="100%" style="border: none;"></iframe>';
                break;
            default:                   
                path = path;
                embedhtml = '<embed src="'+path+'" width="auto" height="auto" style="border: none;">';
                break;
        }
        var win_id = "view_documents";
        var view_documents_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(view_documents_window)) {
            view_documents_window = new Ext.Window({
                id: win_id,
                title: 'View Theme',
                layout: 'fit',
                width: winsize.width * 0.85,
                height: 700,
                plain: false,
                constrain: true,
                modal: true,
                autoScroll:true,
                frame: true,
                resizable: true,
                closable: true,
                items: [{
                        region: 'center',
                        border: false,
                        html: embedhtml
                    }],
                    fbar:[]
            });

        }

        view_documents_window.doLayout();
        view_documents_window.show();
        view_documents_window.center();
    },saveCommonThemePages: function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var design_form = Ext.getCmp("commonDesignimageUploadForm").getForm();
          themeDesigns = [];
          themeDesigns[1] = Ext.getCmp('homePage_awsLocation').getValue();
          themeDesigns[2] = Ext.getCmp('searchPage_awsLocation').getValue();
          themeDesigns[3] = Ext.getCmp('prdctPage_awsLocation').getValue();
          themeDesigns[4] = Ext.getCmp('cartPage_awsLocation').getValue();
          themeDesigns[5] = Ext.getCmp('checkOutPage_awsLocation').getValue();
          themeDesigns[6] = Ext.getCmp('itemView_awsLocation').getValue();
          themeDesigns[7] = Ext.getCmp('payment_awsLocation').getValue();
          themeDesigns[8] = Ext.getCmp('walletPage_awsLocation').getValue();
          themeDesigns[9] = Ext.getCmp('orderPage_awsLocation').getValue();
          themeDesigns[10] = Ext.getCmp('orderDetails_awsLocation').getValue();
          
          design_form.submit({
            url: modURL + "&op=saveCommonThemeDesigns",
            waitMsg: "Saving Details....",
            waitTitle: "Please Wait...",
            params: {
              apikey: _SESSION.apikey,
              tstamp: t_stamp,
              themeDesigns:Ext.encode(themeDesigns),
              themeName: 'Grozeo'
            },
            success: function (response, action) {
              var tmp = Ext.decode(action.response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (
                  Application.MyphaAdManagement.ThemeManagementAddEdit == "Add"
                ) {
                  recs_per_page = updateRecsPerPage(
                    Ext.getCmp("themeManagementGridPanel")
                  );
                  Ext.getCmp("formpanelThemeManagement").getForm().reset();
                  Ext.getCmp("themeManagementGridPanel").store.reload({
                    params: {
                      start: 0,
                      limit: recs_per_page,
                    },
                  });
                  Ext.getCmp('commonThemeWindow').close();
                } else {
                  Ext.getCmp(
                    "themeManagementGridPanel"
                  ).selModel.getSelected().data = tmp.data;
                  Ext.getCmp("themeManagementGridPanel").getStore().load();
                }
                Application.MyphaAdManagement.ThemeManagementAddEdit = "";
                Application.MyphaAdManagement.ViewThemeManagementMode(
                  tmp.data.adzone_id
                );
                Ext.getCmp('commonThemeWindow').close();
              } else if (tmp.success === true && tmp.valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else if (tmp.success === true && tmp.img_valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
            failure: function (elm, conf) {
              if (conf.failureType === "server") {
                var result = Ext.decode(conf.response.responseText);
                Ext.Msg.alert("Error", result.message);
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Please enter all required fields"
                );
              }
            },
          });
        
      }
    };
  })();
  