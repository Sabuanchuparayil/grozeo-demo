Application.NewsRoom = (function () {
    var RECS_PER_PAGE = 23;
    var modURL = "?module=newsroom";
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
      RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var masterPanelforArticle = function (id) {
        var panel = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "News Room",
          id: id,
          //iconCls: 'my-icon444',
          items: [
            newsRoomGrid(),
            new Ext.Panel({
              title: "News Room Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.6,
              autoScroll: true,
              id: "newsRoomParentPanel",
              height: winsize.height * 0.6,
              items: [newsRoomDetailsView()],
              buttonAlign: "right",
              fbar: [                
                /*<?php if (user_access("newsroom", "saveNewsRoom")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  id: "newsRoomEditBtn",
                  icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
                  tabIndex: 4,
                  hidden: true,
                  handler: function () {

                    var ID = Ext.getCmp("newsRoomMasterGrid")
                      .getSelectionModel()
                      .getSelections()[0].data.newsRoomId;
                      var ID = Ext.getCmp('newsRoomMasterGrid').getSelectionModel().getSelections()[0].data.newsRoomId;
                                var record = Ext.getCmp('newsRoomMasterGrid').getStore().getById(ID);
                                var main_img = 1;
                                var newsRoomId = record.data.newsRoomId;
                                Ext.Ajax.request({
                                    url: modURL + '&op=getImage',
                                    method: 'POST',
                                    params: {
                                        main_img: main_img,
                                        newsRoomId: record.data.newsRoomId,
                                    },
                                    success: function (res) {
                                        var tmp = Ext.decode(res.responseText);
                                        var img_url, thumpurl;                                        
                                        if (tmp.data[0].displayImaage != '') {
                                            img_url = tmp.data[0].displayImaage;
                                        } else {
                                            img_url = '/resources/images/default.png';
                                        }
                                        Application.NewsRoom.EditViewNewsRoom(newsRoomId, main_img, img_url,thumpurl);
                                        
                                    }
                                })
                    
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return panel;
      };
      var applicableCountryStore = function () {
        var store = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getApplicableCountry",
          method: "post",
          fields: ["country_id", "country_name"],
          //totalProperty: 'totalCount',
          root: "data",
        });
        return store;
      };
      function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                    v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    ;

    function addPhoto(type) {

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
                        Ext.getCmp('template_image_panel').update({'img_src': img_src_main});
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
                    if (this.width != img_width || this.height != img_height) {
                        Ext.Msg.alert("Notification", 'Image size should be ' + img_width + '*' + img_height);
                        winLoadMask.hide();
                        flag = 0;
                    }

                    if (flag == 1) {
                        winLoadMask.hide();
                Ext.Msg.alert("Notification", 'File has been uploaded successfully.');
                Application.NewsRoom.UploadedFileLocation = data.Location;
                Application.NewsRoom.UploadedFileBucket = data.Bucket;
                Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
                switch (type) {
                    case 'main':
                        Ext.getCmp('aws_file_locationtemplate').setValue(data.Location);
                        Ext.getCmp('template_image_panel').update({'img_src': Application.NewsRoom.UploadedFileLocation});
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
                        Ext.getCmp('template_image_panel').update({'img_src': img_src_main});
                        break;                    
                }
            }
        });
    }
      var newsRoomMasterForms = function (menu_id, content_id, img_url,thumpurl) {
        var countryComboStore = applicableCountryStore();
        var panel = new Ext.form.FormPanel({
          frame: true,
          border: true,
          hideBorders: true,
          labelWidth: 120,
          labelAlign: "top",
          fileUpload: true,
          autoScroll: true,
          bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
          id: "newsRoomMasterForm",
          items: [{
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
        },
            {
              xtype: "spacer",
              height: 10,
            },
            {
              xtype: "textfield",
              fieldLabel: "Heading",
              id: "newsRoomHeading",
              name: "n[newsRoomHeading]",
              anchor: "98%",
              allowBlank: false,
              tabIndex: 800,
              maxLength: 800,
            },
            {
              xtype: "hidden",
              id: "newsRoomId",
              name: "n[newsRoomId]",
            },{
                xtype: 'radiogroup',
                id: 'newsRoomType',
                title:'Mode',
                items: [
                    {boxLabel: 'News', name: 'nrt-auto', inputValue: 1, labelWidth: 50,checked: true},
                    {boxLabel: 'Event', name: 'nrt-auto', inputValue: 2, labelWidth: 80},
                    {boxLabel: 'Press Release', name: 'nrt-auto', inputValue: 3, labelWidth: 130}

                ],
                listeners: {
                    change: function (event, checked)
                    {
                        var current_firstid = event.items.items[0].inputValue;
                        var current_secondid = event.items.items[1].inputValue;
                        var radioid = Ext.getCmp('newsRoomType').getValue();
                    }
                }
            },{
                xtype: "textarea",
                fieldLabel: "Breif",
                id: "newsRoomBreif",
                name: "n[newsRoomBreif]",
                anchor: "98%",
                allowBlank: false,
                tabIndex: 801,
                maxLength: 1500,
              },{
                xtype: 'radiogroup',
                id: 'newsRoomMode',
                title:'Mode',
                items: [
                    {boxLabel: 'Internal', name: 'nrm-auto', inputValue: 1, labelWidth: 100,
                      listeners: {
                      check: function (rgp, checked) {
                          if (checked == true) {  
                            Ext.getCmp('newsRoomExternalLink').hide();
                            Ext.getCmp('newsRoomDetails').show();                            
                          }
                      }
                  }
                },
                    {boxLabel: 'External', name: 'nrm-auto', inputValue: 2, labelWidth: 100,
                      listeners: {
                        check: function (rgp, checked) {
                            if (checked == true) {  
                              Ext.getCmp('newsRoomExternalLink').show();
                              Ext.getCmp('newsRoomDetails').hide();                        
                            }
                        }
                    }
                    }

                ]
            },{
                xtype: "textfield",
                fieldLabel: "URL",
                id: "newsRoomExternalLink",
                name: "n[newsRoomExternalLink]",
                anchor: "98%",
                allowBlank: false,
                tabIndex: 802,
                maxLength: 1500,
              },{
                xtype: "templateeditormce",
                fieldLabel: "Content",
                id: "newsRoomDetails",
                name: "n[newsRoomDetails]",
                anchor: "95%",
                height: 270,
                border:true,
                allowBlank: false,
                tabIndex: 803
              },{
                xtype: "panel",
                layout: "column",
                frame: false,
                border: false,
                items: [{
                  columnWidth: 0.5,
                  layout: "form",
                  frame: false,
                  border: false,
                  items: [
                    {
                      xtype: "checkbox",
                      id: "isGlobal",
                      name: "n[isGlobal]",
                      boxLabel: "Global",
                      listeners: {
                        check: function (checkbox, checked) {
                          if (checked == true) {
                            Ext.getCmp('newsRoomCountry').disable();
                          }else{
                            Ext.getCmp('newsRoomCountry').enable();
                          }
                        },
                      }
                    },
                  ],
                },{
                  columnWidth: 0.5,
                  layout: "form",
                  frame: false,
                  border: false,
                  items: [{
                    xtype: "combo",
                    store: countryComboStore,
                    mode: "local",
                    id: "newsRoomCountry",                    
                    fieldLabel: "Country",
                    hiddenName: "n[newsRoomCountry]",
                    displayField: "country_name",
                    valueField: "country_id",
                    typeAhead: true,
                    forceSelection: true,
                    editable: true,
                    minChars: 2,
                    anchor: "98%",
                    //selectOnFocus: true,
                    triggerAction: "all",
                    lazyRender: true,
                    tabIndex: 804,
                    listeners: {
                      select: function () {
                        var value = Ext.getCmp("newsRoomCountry").getValue();
                        
                      },
                    },
                  }
                  ]
                }]
              },{
                columnWidth: 1,
                layout: 'form',
                id: 'template_image_panel_form',
                height:300,
                border: false,
                items: [new Ext.Panel({
                        layout: "fit",
                        id: 'template_image_panel',
                        tpl: new Ext.XTemplate('<div class="details-outer-event">',
                                '<img style="width: 100%; max-width: 300px; max-height: 300px;" src="{img_url}"></img>',
                                '</div>')
                    }), {
                        xtype: 'box',
                        width: 468,
                        height: 210,
                        id: 'exist_img_box',
                        autoEl: {tag: 'img', src: img_url, width: '468', height: 210}

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
                                var exp = /^.*\.(png|jpg|jpeg|gif)$/i;
                                if (!(exp.test(v))) {
                                    Ext.Msg.alert("Notification", "Upload a valid image file");
                                    return;
                                }

                                var associated_templatefile = Ext.getCmp('associated_templatefile').getValue();
                                if (associated_templatefile == '') {
                                    Ext.Msg.alert("Notification", "Please choose a file to upload");
                                    return;
                                }                                
                                winLoadMask.show();
                                console.log('main here');
                                addPhoto('main');
                                return true;
                            }
                        }
                    }]
            },
            {
                xtype: "panel",
                layout: "column",
                frame: false,
                border: false,
                items: [{
                  columnWidth: 0.5,
                  layout: "form",
                  frame: false,
                  border: false,
                  items: [
                    {
                      xtype: "checkbox",
                      id: "isDefault",
                      name: "n[isDefault]",
                      boxLabel: "Default",
                    },
                  ],
                },{
                  columnWidth: 0.5,
                  layout: "form",
                  frame: false,
                  border: false,
                  items: [mkCombo({
                    type: STATUS_COMBO_DATA,
                    value: "id",
                    display: "text",
                    allowBlank: false,
                    name: "n[newsRoomStatus]",
                    fieldLabel: "Status",
                    emptyText: "Set status..",
                    tabIndex: 805,
                    id: "newsRoomStatus",
                  })
                  ]
                }]
              }            
          ]
        });
        return panel;
      };
      var newsRoomDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          border: false,
          frame:true,
          hideBorders: true,
          autoHeight: true,
          id: "newsRoomDetailsViewPanel",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Heading </th></tr>',
            '<tr><td> {newsRoomHeading} </td></tr>',
            '<tr><th width="40%">Brief </th></tr>',
            '<tr><td> {newsRoomBreif} </td></tr>',
            '<tr><th width="40%">Details </th></tr>',
            '<tr><td> {newsRoomDetails} </td></tr>',
            '<tr><th width="40%">Image </th></tr>',
            '<tr><td> <img width = "640px" height="430px" src = "{displayImaage}"/> </td></tr>',
            "</table>",
            "</div>"
          ),
        });
      };
      var newsRoomGrid = function () {
        var _newsRoomGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "heading",
            },
            {
              type: "string",
              dataIndex: "type",
            },
            {
              type: "string",
              dataIndex: "mode",
            },{
              type: "string",
              dataIndex: "marketType",
            },
            {
              type: "list",
              options: ["Active", "Inactive"],
              phpMode: true,
              dataIndex: "status",
            },
          ],
        });
        _newsRoomGridFilter.remote = true;
        _newsRoomGridFilter.autoReload = true;
        var _newsRoomGridStore = newsRoomGridStore();
        var _gridPanel = new Ext.grid.GridPanel({
          store: _newsRoomGridStore,
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          //iconCls: 'my-icon444',
          viewConfig: {
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          },
          plugins: [_newsRoomGridFilter],
          id: "newsRoomMasterGrid",
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Heading",
              sortable: true,
              dataIndex: "newsRoomHeading",
              tooltip: "Heading",
              hideable: true,
            },{
              header: "Type",
              sortable: true,
              dataIndex: "typeName",
              tooltip: "Type",
              hideable: true,
            },{
              header: "Mode",
              sortable: true,
              dataIndex: "modeName",
              tooltip: "Mode",
              hideable: true,
            },
            {
              header: "Market",
              sortable: true,
              dataIndex: "marketType",
              tooltip: "Market",
              hideable: true,
            },
            {
              header: "Start Date",
              sortable: true,
              hidden: true,
              dataIndex: "startDate",
              tooltip: "Start Date",
            },
            {
              header: "End Date",
              sortable: true,
              hidden: true,
              dataIndex: "endDate",
              tooltip: "End Date",
            },
            {
              header: "Status",
              sortable: true,
              dataIndex: "statusName",
              tooltip: "Status",
            },
          ],
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedArticle,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("newsRoomId");
              if (!Ext.isEmpty(ID)) {
                Application.NewsRoom.Cache.newsRoomId = ID;
                Application.NewsRoom.ViewModeNewsRoom(ID);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _newsRoomGridStore.load();
            },
          },
          tbar: [
            {
              text: "Create Article",
              tooltip: "Create Article",
              icon: "./resources/images/default/icons/add.png",
              iconCls: "my-icon1",
              handler: function () {
                var img_url = '/resources/images/default.png';
                var thumpurl = '/resources/images/default.png';
                var main_img = 1;
                var grpTemp_id = '';
                newsRoomWindow(grpTemp_id, main_img, img_url,thumpurl);
              },
            },
          ],
          bbar: new Ext.PagingToolbar({
            pageSize: RECS_PER_PAGE,
            store: _newsRoomGridStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
            plugins: [_newsRoomGridFilter],
          }),
          stripeRows: true,
          autoExpandColumn: "pdt_name_col",
        });
        return _gridPanel;
      };
      var newsRoomGridStore = function () {
        var _store = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listNewsRoom",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "newsRoomId",
              root: "data",
            },
            [
              "newsRoomId","newsRoomHeading","newsRoomStatus","typeName","modeName","startDate","endDate","isGlobal",
              "isDefault","marketType","mainArticle","statusName"
            ]
          ),
          sortInfo: {
            field: "newsRoomId",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              loadCount++;
              if (loadCount == 1) {
                Ext.getCmp("newsRoomMasterGrid").getSelectionModel().selectRow(0);
              }
            },
          },
        });
        return _store;
      };
      var saveNewsRoom = function (edit_status) {
        if (Ext.getCmp("newsRoomMasterGrid").getStore().getCount() > 0) {
          var index = Ext.getCmp("newsRoomMasterGrid")
            .getSelectionModel()
            .getSelections()[0].rowIndex;
        } else {
          var index = 0;
        }
    
        var lastOptions = Ext.getCmp("newsRoomMasterGrid").getStore().lastOptions;
        var subtopicid = Ext.getCmp("newsRoomId").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("newsRoomHeading").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("newsRoomStatus").getValue()) && 
          !Ext.isEmpty(Ext.getCmp("newsRoomBreif").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveNewsRoom",
            method: "POST",
            params: {
              edit_status:edit_status,
              newsRoomId: Ext.getCmp('newsRoomId').getValue(),
              newsRoomHeading: Ext.getCmp('newsRoomHeading').getValue(),
              newsRoomType: Ext.getCmp('newsRoomType').getValue(),
              newsRoomBreif: Ext.getCmp('newsRoomBreif').getRawValue(),
              newsRoomMode: Ext.getCmp('newsRoomMode').getValue(),
              newsRoomExternalLink: Ext.getCmp('newsRoomExternalLink').getRawValue(),
              newsRoomDetails:Ext.getCmp('newsRoomDetails').getValue(),
              isGlobal:Ext.getCmp('isGlobal').getValue(),
              newsRoomCountry: Ext.getCmp('newsRoomCountry').getValue(),
              aws_file_locationtemplate: Ext.getCmp('aws_file_locationtemplate').getRawValue(),
              isDefault: Ext.getCmp('isDefault').getValue(),
              newsRoomStatus: Ext.getCmp('newsRoomStatus').getValue()
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Ext.getCmp('windowNewsRoomWindow').close();
                Application.example.msg("Success", tmp.message);
                if (Application.NewsRoom.articleAddEdit == "Add") {
                  RECS_PER_PAGE = updateRecsPerPage(
                    Ext.getCmp("newsRoomMasterGrid")
                  );
                  Ext.getCmp("newsRoomMasterGrid").store.reload({
                    params: {
                      start: 0,
                      limit: RECS_PER_PAGE,
                    },
                  });
                } else {
                  Ext.getCmp("newsRoomMasterGrid").getStore().reload(lastOptions);
                  var gridPanel = Ext.getCmp("newsRoomMasterGrid");
                  gridPanel.getSelectionModel().selectRow(index);
                  Application.NewsRoom.ViewModeNewsRoom(tmp.data.newsRoomId);
                }
                Application.NewsRoom.articleAddEdit = "";
                Application.NewsRoom.ViewModeNewsRoom(tmp.data.newsRoomId);
              } else if (tmp.success === true && tmp.valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else if (tmp.success === true && tmp.img_valid === false) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
            failure: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.MessageBox.alert("Error", tmp.msg);
            },
          });
        } else {
          Ext.MessageBox.alert("Notification", "Please enter all required fields");
        }
      };
      var gridSelectionChangedArticle = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("newsRoomMasterGrid").getSelectionModel().getSelections()
          )
        ) {
          var ID = Ext.getCmp("newsRoomMasterGrid")
            .getSelectionModel()
            .getSelections()[0].data.newsRoomId;
          Application.NewsRoom.ViewModeNewsRoom(ID);
        }
      };
      var newsRoomWindow = function (menu_id, content_id, img_url,thumpurl) {
        console.log('menu_id', menu_id);
        if (menu_id == '')
            var tit = 'Create News Room';
        else
            var tit = 'Edit News Room';
        var resultWindow = new Ext.Window({
            id: 'windowNewsRoomWindow',
            title: tit,
            shadow: false,
            width: 950,
            height: 650,
            layout: 'fit',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [newsRoomMasterForms(menu_id, content_id, img_url,thumpurl)],
            buttons: [
                {
                    text: 'Cancel',
                    id: 'newsRoomCancelBtn',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    tabIndex: 505,
                    handler: function () {
                        resultWindow.close();
                    }
                }, {
                    text: "Save",
                    id: 'newsRoomSaveBtn',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    iconCls: 'thes_save',
                    tabIndex: 504,
                    handler: function () {
                        var edit_status;  
                        if (menu_id == '')
                            edit_status = 0;
                        else
                            edit_status = 1;
                            saveNewsRoom(edit_status);
                    }
                }
            ],
            listeners: {
                afterrender: function () {
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    var upload_type = 1;
                    winLoadMask = new Ext.LoadMask(Ext.getCmp('windowNewsRoomWindow').getEl());
                    winLoadMask.msg = 'Please wait...';
                    if (content_id == 1) {
                      Ext.getCmp('newsRoomMasterForm').getForm().load({
                        waitTitle: 'Please Wait',
                        waitMsg: 'Loading...',
                        url: modURL + '&op=get_img_s3_details',
                        params: {
                            apikey: _SESSION.apikey,
                            tstamp: t_stamp
                        }
                    });
                    }
                }
            }

        });
        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();

    };
    var helpInfoMasterForms = function (menu_id, content_id, img_url,thumpurl) {
      var panel = new Ext.form.FormPanel({
        frame: true,
        border: true,
        hideBorders: true,
        labelWidth: 120,
        labelAlign: "top",
        fileUpload: true,
        autoScroll: true,
        bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
        id: "helpInfoMasterForm",
        items: [
          {
            xtype: "spacer",
            height: 10,
          },
          {
            xtype: "textfield",
            fieldLabel: "Title",
            id: "helpInfoHeading",
            name: "n[helpInfoHeading]",
            anchor: "98%",
            allowBlank: false,
            tabIndex: 800,
            maxLength: 249,
          },
          {
            xtype: "hidden",
            id: "helpInfoId",
            name: "n[helpInfoId]",
          },{
              xtype: "templateeditormce",
              fieldLabel: "Content",
              id: "helpInfoDetails",
              name: "n[helpInfoDetails]",
              anchor: "95%",
              height: 270,
              border:true,
              allowBlank: false,
              tabIndex: 801
            }                    
        ]
      });
      return panel;
    };
    var HelpInfoDetailsView = function () {
      return new Ext.Panel({
        layout: "fit",
        border: false,
        frame:true,
        hideBorders: true,
        autoHeight: true,
        id: "HelpInfoDetailsViewPanel",
        tpl: new Ext.XTemplate(
          '<div class="details-outer">',
          '<table border="0" width="100%" class="details_view_table">',
          '<tr><th width="40%">Heading </th></tr>',
          '<tr><td> {helpInfoHeading} </td></tr>',          
          '<tr><iframe src="{displayContent}" width="100%" height="300px"></iframe></tr>',
          "</table>",
          "</div>"
        ),
      });
    };
    var HelpInfoGrid = function () {
      var _helpInfoGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "heading",
          },
          {
            type: "string",
            dataIndex: "type",
          },
          {
            type: "string",
            dataIndex: "mode",
          },{
            type: "string",
            dataIndex: "marketType",
          },
          {
            type: "list",
            options: ["Active", "Inactive"],
            phpMode: true,
            dataIndex: "status",
          },
        ],
      });
      _helpInfoGridFilter.remote = true;
      _helpInfoGridFilter.autoReload = true;
      var _helpInfoGridStore = helpInfoGridStore();
      var _gridPanel = new Ext.grid.GridPanel({
        store: _helpInfoGridStore,
        region: "center",
        layout: "fit",
        frame: false,
        border: false,
        //iconCls: 'my-icon444',
        viewConfig: {
          forceFit: true,
          deferEmptyText: false,
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        },
        plugins: [_helpInfoGridFilter],
        id: "helpInfoMasterGrid",
        columns: [
          new Ext.grid.RowNumberer(),
          {
            header: "Title",
            sortable: true,
            dataIndex: "helpInfoHeading",
            tooltip: "Title",
            hideable: true,
          },
          {
            header: "Status",
            sortable: true,
            dataIndex: "statusName",
            tooltip: "Status",
          },
        ],
        sm: new Ext.grid.RowSelectionModel({
          singleSelect: true,
          listeners: {
            selectionchange: gridSelectionChangedHelpInfo,
          },
        }),
        listeners: {
          cellClick: function (grid, rowIndex, columnIndex, e) {
            var record = grid.getStore().getAt(rowIndex);
            var ID = record.get("helpInfoId");
            if (!Ext.isEmpty(ID)) {
              Application.NewsRoom.Cache.helpInfoId = ID;
              Application.NewsRoom.ViewModeHelpInfo(ID);
            }
          },
          resize: onGridResize,
          afterrender: function () {
            _helpInfoGridStore.load();
          },
        },
        tbar: [
          {
            text: "Create Info",
            tooltip: "Create Info",
            icon: "./resources/images/default/icons/add.png",
            iconCls: "my-icon1",
            handler: function () {
              var img_url = '/resources/images/default.png';
              var thumpurl = '/resources/images/default.png';
              var main_img = 1;
              var grpTemp_id = '';
              helpInfoWindow(grpTemp_id, main_img, img_url,thumpurl);
            },
          },
        ],
        bbar: new Ext.PagingToolbar({
          pageSize: RECS_PER_PAGE,
          store: _helpInfoGridStore,
          displayInfo: true,
          displayMsg: "Displaying records {0} - {1} of {2}",
          emptyMsg: "No pages to display",
          plugins: [_helpInfoGridFilter],
        }),
        stripeRows: true,
        autoExpandColumn: "pdt_name_col",
      });
      return _gridPanel;
    };
    var helpInfoGridStore = function () {
      var _store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listHelpInfo",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "helpInfoId",
            root: "data",
          },
          [
            "helpInfoId","helpInfoHeading","helpInfoStatus","typeName","modeName","startDate","endDate","isGlobal",
            "isDefault","marketType","mainArticle","statusName"
          ]
        ),
        sortInfo: {
          field: "helpInfoId",
          direction: "DESC",
        },
        groupField: "",
        groupDir: "ASC",
        remoteSort: true,
        autoLoad: false,
        root: "data",
        listeners: {
          load: function () {
            loadCount++;
            if (loadCount == 1) {
              Ext.getCmp("helpInfoMasterGrid").getSelectionModel().selectRow(0);
            }
          },
        },
      });
      return _store;
    };
    var saveHelpInfo = function (edit_status) {
      if (Ext.getCmp("helpInfoMasterGrid").getStore().getCount() > 0) {
        var index = Ext.getCmp("helpInfoMasterGrid")
          .getSelectionModel()
          .getSelections()[0].rowIndex;
      } else {
        var index = 0;
      }
  
      var lastOptions = Ext.getCmp("helpInfoMasterGrid").getStore().lastOptions;
      var subtopicid = Ext.getCmp("helpInfoId").getValue();
      if (
        !Ext.isEmpty(Ext.getCmp("helpInfoHeading").getValue())
      ) {
        Ext.Ajax.request({
          url: modURL + "&op=saveHelpInfo",
          method: "POST",
          params: {
            edit_status:edit_status,
            helpInfoId: Ext.getCmp('helpInfoId').getValue(),
            helpInfoHeading: Ext.getCmp('helpInfoHeading').getValue(),
            helpInfoDetails:Ext.getCmp('helpInfoDetails').getValue(),            
          },
          success: function (response) {
            var tmp = Ext.decode(response.responseText);
            if (tmp.success === true && tmp.valid === true) {
              Ext.getCmp('windowHelpInfoWindow').close();
              Application.example.msg("Success", tmp.message);
              if (Application.NewsRoom.articleAddEdit == "Add") {
                RECS_PER_PAGE = updateRecsPerPage(
                  Ext.getCmp("helpInfoMasterGrid")
                );
                Ext.getCmp("helpInfoMasterGrid").store.reload({
                  params: {
                    start: 0,
                    limit: RECS_PER_PAGE,
                  },
                });
              } else {
                Ext.getCmp("helpInfoMasterGrid").getStore().reload(lastOptions);
                var gridPanel = Ext.getCmp("helpInfoMasterGrid");
                gridPanel.getSelectionModel().selectRow(index);
                Application.NewsRoom.ViewModeHelpInfo(tmp.data.helpInfoId);
              }
              Application.NewsRoom.articleAddEdit = "";
              Application.NewsRoom.ViewModeHelpInfo(tmp.data.helpInfoId);
            } else if (tmp.success === true && tmp.valid === false) {
              Ext.Msg.alert("Notification.", tmp.message);
            } else if (tmp.success === true && tmp.img_valid === false) {
              Ext.Msg.alert("Notification.", tmp.message);
            } else {
              Ext.Msg.alert("Error", tmp.message);
            }
          },
          failure: function (response) {
            var tmp = Ext.util.JSON.decode(response.responseText);
            Ext.MessageBox.alert("Error", tmp.msg);
          },
        });
      } else {
        Ext.MessageBox.alert("Notification", "Please enter all required fields");
      }
    };
    var gridSelectionChangedHelpInfo = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp("helpInfoMasterGrid").getSelectionModel().getSelections()
        )
      ) {
        var ID = Ext.getCmp("helpInfoMasterGrid")
          .getSelectionModel()
          .getSelections()[0].data.helpInfoId;
        Application.NewsRoom.ViewModeHelpInfo(ID);
      }
    };
    var helpInfoWindow = function (menu_id, content_id, img_url,thumpurl) {
      console.log('menu_id', menu_id);
      if (menu_id == '')
          var tit = 'Create Help Info';
      else
          var tit = 'Edit Help Info';
      var resultWindow = new Ext.Window({
          id: 'windowHelpInfoWindow',
          title: tit,
          shadow: false,
          width: 950,
          height: 550,
          layout: 'fit',
          resizable: false,
          plain: true,
          constrain: true,
          draggable: true,
          modal: true,
          closable: false,
          items: [helpInfoMasterForms(menu_id, content_id, img_url,thumpurl)],
          buttons: [
              {
                  text: 'Cancel',
                  id: 'helpInfoCancelBtn',
                  icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                  iconCls: 'my-icon1',
                  tabIndex: 505,
                  handler: function () {
                      resultWindow.close();
                  }
              }, {
                  text: "Save",
                  id: 'helpInfoSaveBtn',
                  icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                  iconCls: 'thes_save',
                  tabIndex: 504,
                  handler: function () {
                      var edit_status;  
                      if (menu_id == '')
                          edit_status = 0;
                      else
                          edit_status = 1;
                          saveHelpInfo(edit_status);
                  }
              }
          ],
          listeners: {
              afterrender: function () {
                  var t = new Date();
                  var t_stamp = t.format("YmdHis");
                  var upload_type = 1;
                  winLoadMask = new Ext.LoadMask(Ext.getCmp('windowHelpInfoWindow').getEl());
                  winLoadMask.msg = 'Please wait...';
                  if (content_id == 1) {
                    Ext.getCmp('helpInfoMasterForm').getForm().load({
                      waitTitle: 'Please Wait',
                      waitMsg: 'Loading...',
                      url: modURL + '&op=get_img_s3_details',
                      params: {
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp
                      }
                  });
                  }
              }
          }

      });
      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();

  };
    var masterPanelforHelpInfo = function (id) {
      var panel = new Ext.Panel({
        frame: false,
        hideBorders: true,
        layout: "border",
        border: false,
        title: "Help Info",
        id: id,
        //iconCls: 'my-icon444',
        items: [
          HelpInfoGrid(),
          new Ext.Panel({
            title: "Help Info Details",
            frame: false,
            border: true,
            region: "east",
            width: winsize.width * 0.6,
            autoScroll: true,
            id: "HelpInfoParentPanel",
            height: winsize.height * 0.6,
            items: [HelpInfoDetailsView()],
            buttonAlign: "right",
            fbar: [                
              /*<?php if (user_access("newsroom", "saveHelpInfo")) { ?> */ {
                text: "Edit",
                cls: "left-right-buttons",
                id: "HelpInfoEditBtn",
                icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
                tabIndex: 4,
                hidden: true,
                handler: function () {
                    var ID = Ext.getCmp('helpInfoMasterGrid').getSelectionModel().getSelections()[0].data.helpInfoId;
                    var record = Ext.getCmp('helpInfoMasterGrid').getStore().getById(ID);
                    var main_img = 1;var img_url, thumpurl; 
                    thumpurl = img_url = '/resources/images/default.png';
                    var helpInfoId = record.data.helpInfoId;
                    Application.NewsRoom.EditViewHelpInfo(helpInfoId, main_img, img_url,thumpurl);
                              
                  
                },
              } /*<?php } ?> */,
            ],
          }),
        ],
      });
      return panel;
    };
    return {
        Cache: {},
        initNewsRoom: function () {
            loadCount = 0;
            var _panelId = "panelidforNewsRoom";
            var _masterPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_masterPanel)) {
              _masterPanel = masterPanelforArticle(_panelId);
              Application.UI.addTab(_masterPanel);
              _masterPanel.doLayout();
            } else {
              Application.UI.addTab(_masterPanel);
            }
          },
          EditViewNewsRoom: function () {
            Application.NewsRoom.subtopicAddEdit = "Edit";            
            /*<?php if (user_access("newsroom", "saveNewsRoom")) { ?> */
            Ext.getCmp("newsRoomEditBtn").hide();
            /*<?php } ?> */
            var newsRoomId = arguments[0];
            var main_img = arguments[1];
            var img_url = arguments[2];
            var thumpurl = arguments[3];
            newsRoomWindow(newsRoomId, main_img, img_url,thumpurl);
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var masterForm = Ext.getCmp("newsRoomMasterForm").getForm();
              masterForm.load({
                params: {
                  newsRoomId: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=nr_form_load",
                waitMsg: "Loading...",
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  Ext.getCmp('newsRoomBreif').setValue(tmp.data.newsRoomBreif);
                  
                  switch(tmp.data.newsRoomMode){
                    case '1':
                      Ext.getCmp('newsRoomDetails').setValue(tmp.data.newsRoomDetails);
                      break;
                    case '2':
                      Ext.getCmp('newsRoomExternalLink').setValue(tmp.data.newsRoomDetails);
                      break
                  }
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
            }
          },
          ViewModeNewsRoom: function () {
            var newsRoomId = arguments[0];
            /*<?php if (user_access("newsroom", "saveNewsRoom")) { ?> */
            Ext.getCmp("newsRoomEditBtn").show();
            /*<?php } ?> */
            Ext.getCmp("newsRoomDetailsViewPanel").show();
            Ext.getCmp("newsRoomParentPanel").doLayout();
            Ext.Ajax.request({
              url: modURL + "&op=nrDetailsView",
              method: "POST",
              params: { newsRoomId: newsRoomId },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp("newsRoomDetailsViewPanel");
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("newsRoomParentPanel").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("newsRoomParentPanel").doLayout();
          },initHelpInfo: function () {
            loadCount = 0;
            var _panelId = "panelidforHelpInfo";
            var _masterPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_masterPanel)) {
              _masterPanel = masterPanelforHelpInfo(_panelId);
              Application.UI.addTab(_masterPanel);
              _masterPanel.doLayout();
            } else {
              Application.UI.addTab(_masterPanel);
            }
          },
          EditViewHelpInfo: function () {
            Application.NewsRoom.subtopicAddEdit = "Edit";            
            /*<?php if (user_access("newsroom", "saveHelpInfo")) { ?> */
            Ext.getCmp("HelpInfoEditBtn").hide();
            /*<?php } ?> */
            var helpInfoId = arguments[0];
            var main_img = arguments[1];
            var img_url = arguments[2];
            var thumpurl = arguments[3];
            helpInfoWindow(helpInfoId, main_img, img_url,thumpurl);
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var masterForm = Ext.getCmp("helpInfoMasterForm").getForm();
              masterForm.load({
                params: {
                  helpInfoId: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=hi_form_load",
                waitMsg: "Loading...",
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  Ext.getCmp('helpInfoDetails').setValue(tmp.data.helpInfoDetails);                  
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
            }
          },
          ViewModeHelpInfo: function () {
            var helpInfoId = arguments[0];
            /*<?php if (user_access("newsroom", "saveHelpInfo")) { ?> */
            Ext.getCmp("HelpInfoEditBtn").show();
            /*<?php } ?> */
            Ext.getCmp("HelpInfoDetailsViewPanel").show();
            Ext.getCmp("HelpInfoParentPanel").doLayout();
            Ext.Ajax.request({
              url: modURL + "&op=hiDetailsView",
              method: "POST",
              params: { helpInfoId: helpInfoId },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp("HelpInfoDetailsViewPanel");
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("HelpInfoParentPanel").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("HelpInfoParentPanel").doLayout();
          }
    };
})();