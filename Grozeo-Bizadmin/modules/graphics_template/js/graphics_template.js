Application.GraphicsTemplate = function () {

    var winLoadMask;
    var RECS_PER_PAGE = 12;
    var modURL = '?module=graphics_template';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectiongraphicsTemplateChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().getSelections()[0].data.grpTemp_id;
            Application.GraphicsTemplate.ViewGraphicsTemplateMode(ID);
        }
    };
    var graphicsTemplatePanel = function (id, grpTemp_imageurl) {
        var _adPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Graphics Template',
            id: id,
            items: [
                graphicsTemplateGrid(),
                new Ext.Panel({
                    title: 'Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'graphicsTemplatePanel',
                    height: winsize.height * 0.6,
                    items: [
                        graphicsTemplateDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 5,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttongraphicsTemplateCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().getSelections()[0].data.grpTemp_id;
                                    Application.GraphicsTemplate.ViewGraphicsTemplateMode(ID);
                                }
                            }
                        }, /* <?php if (user_access("graphics_template", "saveGraphicsTemplate")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttongraphicsTemplateEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 4,
                            handler: function () {
//                                var ID = Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().getSelections()[0].data.grpTemp_id;
//                                Application.GraphicsTemplate.EditGraphicsTemplateView(ID);

                                var ID = Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().getSelections()[0].data.grpTemp_id;
                                var record = Ext.getCmp('graphicsTemplateGridPanel').getStore().getById(ID);
                                var main_img = 1;
                                var grpTemp_id = record.data.grpTemp_id;
                                Ext.Ajax.request({
                                    url: modURL + '&op=getAdImage',
                                    method: 'POST',
                                    params: {
                                        main_img: main_img,
                                        grpTemp_id: record.data.grpTemp_id,
                                    },
                                    success: function (res) {
                                        var tmp = Ext.decode(res.responseText);
                                        var img_url, thumpurl;
                                        if (tmp.data[0].designUrl != '') {
                                            thumpurl = tmp.data[0].designUrl;
                                        } else {
                                            thumpurl = '/resources/images/default.png';
                                        }
                                        if (tmp.data[0].templateUrl != '') {
                                            img_url = tmp.data[0].templateUrl;
                                        } else {
                                            img_url = '/resources/images/default.png';
                                        }
                                        Application.GraphicsTemplate.EditGrphTempView(grpTemp_id, main_img, img_url,thumpurl);
                                        
                                    }
                                })
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _adPanel;
    };
    var graphicsTemplateGridstore = function () {
        var _graphicsTemplateList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listgraphicsTemplate',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'grpTemp_id',
                root: 'data'
            }, ['templateID','grpTemp_id','grpTemp_title','grpTemp_status','grpTemp_application',
            'grpTemp_location','grpTemp_type','grpTemp_adzones','grApplication','grLocation','grTemplates',
            'grStatus','bannerPosition','FirstName','createdOn']),
            sortInfo: {
                field: 'grpTemp_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _graphicsTemplateList;
    };
    var adZoneStore = function () {
        var adZoneStore = new Ext.data.JsonStore({
            url: modURL + '&op=advName',
            method: 'post',
            fields: ['ad_id', 'adzone_name','adzone_width','adzone_height'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return adZoneStore;
    };
    var appLocationStorefn = function () {
        var locationStore = new Ext.data.JsonStore({
            url: modURL + '&op=appLocations',
            method: 'post',
            fields: ['locationId','locationName','width','height'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: false
        });
        return locationStore;
    };
    var appThemeStorefn = function(){
        var themeStore = new Ext.data.JsonStore({
            url: modURL + '&op=appThemes',
            method: 'post',
            fields: ['theme_id','theme_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return themeStore;
    };
   
    var graphicsTemplateForm = function () {
        var adZoneNamStore = adZoneStore();  
        var appLocationStore = appLocationStorefn();
        var appThemeStore= appThemeStorefn();
        var _graphicsTemplateForm = new Ext.Panel({
            layout: "fit",
            region: 'west',
            width: 300,
            border: false,
            //autoHeight:true,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelGraphicsTemplate',
                    frame: false,
                    border: false,
                    autoHeight: true,
                    autoScroll: true,
                    labelWidth: 120,
                    labelAlign: 'top',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Title',
                            emptyText: 'Title',
                            id: 'grpTemp_title',
                            name: 'n[grpTemp_title]',
                            anchor: '98%',
                            hidden: true,
                            width: 300,
                            tabIndex: 500,
                            maxLength: 300,
                            listeners: {
                                
                            }
                        }, 
                        {
                            xtype: 'textfield',
                            id: 'grpTemp_id',
                            name: 'n[grpTemp_id]',
                            hidden: true
                        },{
                            xtype: 'combo',
                            displayField: 'usagetype',
                            valueField: 'usageid',
                            anchor: '98%',
                            mode: 'local',
                            id: 'grpTemp_application',
                            name: 'grpTemp_application',
                            allowBlank: false,
                            hiddenName: 'grpTemp_application',
                            forceSelection: true,
                            fieldLabel: 'Application',
                            emptyText: 'Application',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            editable: true,
                            minChars: 2,
                            tabIndex: 502,
                            store: new Ext.data.JsonStore({
                                fields: ['usageid', 'usagetype'],
                                data: [{usageid: 1, usagetype: 'Web'}, {usageid: 2, usagetype: 'App'},
                                {usageid: 3, usagetype: 'Facebook'}, {usageid: 4, usagetype: 'Instagram'},{usageid: 5, usagetype: 'Watsapp'}]
                            }), listeners: {
                                select: function () {
                                    var applicationVal = Ext.getCmp('grpTemp_application').getValue();
                                    if(applicationVal == 1 || applicationVal == 2){
                                        Ext.getCmp('grpTemp_location').hide();
                                        Ext.getCmp('design_image_panel_form').hide();
                                    if(applicationVal == 1){
                                        Ext.getCmp('grpTemp_theme').show();
                                        Ext.getCmp('grpTemp_theme').getStore().load();  
                                    }else{
                                        Ext.getCmp('grpTemp_theme').hide();
                                    }                                      
                                        Ext.getCmp('grpTemp_type').setValue(1);
                                        Ext.getCmp('grpTemp_type').setHideTrigger(true);
                                        Ext.getCmp('grpTemp_adzones').show();
                                        Ext.getCmp('grpTemp_adzones').getStore().load({
                                            params: {
                                                applicationId: applicationVal,
                                            }
                                        });
                                    }else{
                                        Ext.getCmp('design_image_panel_form').show();
                                        Ext.getCmp('grpTemp_location').show();
                                        Ext.getCmp('grpTemp_theme').hide();
                                        Ext.getCmp('grpTemp_type').setHideTrigger(false);
                                        Ext.getCmp('grpTemp_type').store.removeAt(0);
                                        Ext.getCmp('grpTemp_location').getStore().load({
                                            params: {
                                                applicationId: applicationVal
                                            }
                                        });
                                    }
                                }
                            }
                        },{
                            
                            xtype: 'combo',
                            displayField: 'locationName',
                            valueField: 'locationId',
                            anchor: '98%',
                            mode: 'local',
                            id: 'grpTemp_location',
                            name: 'grpTemp_location',
                            hiddenName: 'grpTemp_location',
                            forceSelection: true,
                            fieldLabel: 'Location',
                            emptyText: 'Set Location',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            editable: true,
                            minChars: 2,
                            tabIndex: 503,
                            store: appLocationStore,
                            listeners:{
                                select:function(combo, record, index){
                                    var adzone_width = record.get('width');
                                    var adzone_height = record.get('height');
                                    Ext.getCmp('widgetImageWidth').setValue(adzone_width);
                                    Ext.getCmp('widgetImageHeight').setValue(adzone_height);
                                }
                            }
                        },{
                            
                            xtype: 'combo',
                            displayField: 'theme_name',
                            valueField: 'theme_id',
                            anchor: '98%',
                            mode: 'local',
                            id: 'grpTemp_theme',
                            name: 'grpTemp_theme',
                            hiddenName: 'grpTemp_theme',
                            forceSelection: true,
                            fieldLabel: 'Theme',
                            emptyText: 'Set Theme',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            editable: true,
                            minChars: 2,
                            tabIndex: 503,
                            store: appThemeStore,
                            listeners:{
                                select: function () {
                                    var applicationVal = Ext.getCmp('grpTemp_application').getValue();
                                    var themeId = Ext.getCmp('grpTemp_theme').getValue();
                                    Ext.getCmp('grpTemp_adzones').getStore().load({
                                        params: {
                                            applicationId: applicationVal,
                                            themeId: themeId
                                        }
                                    });
                                }
                            }
                        }
                        ,{
                            xtype: 'combo',
                            displayField: 'usagetype',
                            valueField: 'usageid',
                            anchor: '98%',
                            mode: 'local',
                            id: 'grpTemp_type',
                            name: 'grpTemp_type',
                            allowBlank: false,
                            hiddenName: 'grpTemp_type',
                            forceSelection: true,
                            fieldLabel: 'Template Type',
                            emptyText: 'Template Type',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            editable: true,
                            minChars: 2,
                            tabIndex: 502,
                            store: new Ext.data.JsonStore({
                                fields: ['usageid', 'usagetype'],
                                data: [{usageid: 1, usagetype: 'Banner'}, {usageid: 2, usagetype: 'Invitation'},
                                {usageid: 3, usagetype: 'Greetings'}, {usageid: 4, usagetype: 'Announcement'},{usageid: 5, usagetype: 'Offers'}]
                                                                        }),
                            listeners: {
                                select: function () {
                                    if(Ext.getCmp('grpTemp_type').getValue() == 1){
                                        Ext.getCmp('grpTemp_adzones').show();
                                    }else{
                                        Ext.getCmp('grpTemp_adzones').hide();
                                    }
                                    
                                }
                            }
                        },{
                            xtype: 'combo',
                            fieldLabel: 'Banner Location',
                            id: 'grpTemp_adzones',
                            hiddenName: 'grpTemp_adzones',
                            hidden:true,
                            anchor: '98%',
                            displayField: 'adzone_name',
                            valueField: 'ad_id',
                            triggerAction: 'all',
                            forceSelection: true,
                            selectOnFocus: true,
                            mode: 'local',
                            typeAhead: true,
                            lazyRender: true,
                            editable: true,
                            minChars: 2,
                            tabIndex: 501,
                            store: adZoneNamStore,
                            listeners:{
                                select:function(combo, record, index){
                                    var adzone_width = record.get('adzone_width');
                                    var adzone_height = record.get('adzone_height');
                                    Ext.getCmp('widgetImageWidth').setValue(adzone_width);
                                    Ext.getCmp('widgetImageHeight').setValue(adzone_height);
                                }
                            }
                        },{
                            xtype: 'textfield',
                            name: 'widgetImageWidth',
                            hidden: true,
                            id: 'widgetImageWidth'
                        },
                        {
                            xtype: 'textfield',
                            name: 'widgetImageHeight',
                            hidden: true,
                            id: 'widgetImageHeight'
                        },{
                            xtype: 'combo',
                            displayField: 'statustype',
                            valueField: 'stid',
                            anchor: '98%',
                            mode: 'local',
                            id: 'grpTemp_status',
                            name: 'grpTemp_status',
                            allowBlank: false,
                            hiddenName: 'grpTemp_status',
                            forceSelection: true,
                            fieldLabel: 'Status',
                            emptyText: 'Set Status',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            editable: true,
                            minChars: 2,
                            tabIndex: 503,
                            store: new Ext.data.JsonStore({
                                fields: ['stid', 'statustype'],
                                data: [{stid: 1, statustype: 'Active'},{stid: 0, statustype: 'Inactive'}/*,{stid: 2, statustype: 'Delete'}*/]
                            })
                        }
                    ]
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('grpTemp_id').getValue())) {
                        var recordSelected = Ext.getCmp('grpTemp_status').getStore().getAt(0);
                        Ext.getCmp('grpTemp_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _graphicsTemplateForm;
    };


    var uploadForm = function (img, thumpurl) {
        return new Ext.Panel({
            id: 'uploadformpanel',
            items: [                
                {
                    xtype: 'hidden',
                    id: 'aws_file_locationtemplate',
                    name: 'aws_file_locationtemplate'
                }, {
                    xtype: 'hidden',
                    id: 'aws_file_locationdesign',
                    name: 'aws_file_locationdesign'
                }, {
                    xtype: 'hidden',
                    id: 'aws_file_bucket',
                    name: 'aws_file_bucket'
                },
                new Ext.form.FormPanel({
                    id: 'main_image_upload',
                    layout: 'form',
                    fileUpload: true,
                    //autoHeight: true,
                    height :600,
                    hidLabel: true,
                    frame: true,
                    items: [{
                            layout: 'column',
                            border: false,
                            items: [{
                                    columnWidth: 1,
                                    layout: 'form',
                                    id: 'template_image_panel_form',
                                    height:300,
                                    border: false,
                                    items: [new Ext.Panel({
                                            layout: "fit",
                                            id: 'template_image_panel',
                                            tpl: new Ext.XTemplate('<div class="details-outer-event">',
                                                    '<img style="width: 100%; max-width: 300px; max-height: 300px;" src="{img_src}"></img>',
                                                    '</div>')
                                        }), {
                                            xtype: 'box',
                                            // fieldLabel: 'Current Image',
                                            width: 468,
                                            height: 210,
                                            id: 'exist_img_box',
                                            //autoEl: { tag: 'img', src: img, width: 120, height: 100 }
                                            autoEl: {tag: 'img', src: img, width: '468', height: 210}

                                        }, {
                                            xtype: 'fileuploadfield',
                                            id: 'associated_templatefile',
                                            anchor: '98%',
                                            name: 'associated_templatefile',
                                            allowBlank: true,
                                            //tabIndex: 511,
                                            buttonOnly: true,
                                            buttonCfg: {
                                                text: 'Upload Template',
                                                //iconCls:'so_upload',
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
                                                        Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                                        return;
                                                    }

//                                    var main_image_upload = Ext.getCmp('main_image_upload').getForm();
//                                    if (main_image_upload.isValid()) {
                                                    Ext.getCmp('exist_img_box').hide();
                                                    winLoadMask.show();
                                                    console.log('main here');
                                                    addPhoto('main');
//                                    }
                                                    return true;
                                                }
                                            }
                                        }]
                                }, {
                                    columnWidth: 1,
                                    layout: 'form',
                                    height:300,
                                    id: 'design_image_panel_form',
                                    border: false,
                                    items: [new Ext.Panel({
                                            layout: "fit",
                                            id: 'design_image_panel',
                                            tpl: new Ext.XTemplate('<div class="details-outer-events">',
                                                    '<img style="width: 100%; max-width: 300px; max-height: 300px;" src="{thumpimg_src}"></img>',
                                                    '</div>')
                                        }), {
                                            xtype: 'box',
                                            width: 468,
                                            height: 210,
                                            id: 'exist_thumpimg_box',
                                            autoEl: {tag: 'img', src: thumpurl, width: '468', height: 210}
                                            //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}

                                        }, {
                                            xtype: 'fileuploadfield',
                                            id: 'associated_designfile',
                                            anchor: '98%',
                                            name: 'associated_designfile',
                                            allowBlank: true,
                                            buttonOnly: true,
                                            //tabIndex: 512,
                                            buttonCfg: {
                                                text: 'Upload Sample',
                                                //iconCls:'so_upload',
                                                width: 80
                                            },
                                            validator: function (vt) {
                                                if (vt != '') {
                                                    vt = vt.toLowerCase();
                                                    var exp = /^.*\.(png|jpg|jpeg|gif)$/i;
                                                    if (!(exp.test(vt))) {
                                                        Ext.Msg.alert("Notification", "Upload a valid image file");
                                                        return;
                                                    }

                                                    var associated_designfile = Ext.getCmp('associated_designfile').getValue();
                                                    if (associated_designfile == '') {
                                                        Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                                        return;
                                                    }

//                                    var main_image_upload = Ext.getCmp('main_image_upload').getForm();
//                                    if (main_image_upload.isValid()) {
                                                    Ext.getCmp('exist_thumpimg_box').hide();
                                                    winLoadMask.show();
                                                    console.log('thump here');
                                                    addPhoto('thump');
                                                    //}
                                                    return true;
                                                }
                                            }
                                        }]
                                }
                            ]
                        }, {
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
                        }
                    ]
                })
            ]
        });
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
            case 'thump':
                files = document.getElementById('associated_designfile-file').files;
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
        if (size > 200) {
            winLoadMask.hide();
            return alert('File size should not exceed 200KB.');
        }
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
                    case 'thump':
                        Ext.getCmp('design_image_panel').update({'thumpimg_src': img_src_list});
                        break;
                }
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {
                var img_width = Ext.getCmp('widgetImageWidth').getValue();
                var img_height = Ext.getCmp('widgetImageHeight').getValue();
                var img = new Image();
                img.onload = function () {
                    var flag = 1;
                    if (this.width != img_width || this.height != img_height) {
                        Ext.Msg.alert("Notification", 'Image size should be ' + img_width + '*' + img_height);
                        winLoadMask.hide();
                        flag = 0;
                    }

                    if (flag == 1) {
                        winLoadMask.hide();
                Ext.Msg.alert("Notification", 'File has been uploaded successfully.');
                Application.GraphicsTemplate.UploadedFileLocation = data.Location;
                Application.GraphicsTemplate.UploadedFileBucket = data.Bucket;
                Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
                switch (type) {
                    case 'main':
                        Ext.getCmp('aws_file_locationtemplate').setValue(data.Location);
                        Ext.getCmp('template_image_panel').update({'img_src': Application.GraphicsTemplate.UploadedFileLocation});
                        break;
                    case 'thump':
                        Ext.getCmp('aws_file_locationdesign').setValue(data.Location);
                        Ext.getCmp('design_image_panel').update({'thumpimg_src': Application.GraphicsTemplate.UploadedFileLocation});
                        break;
                }
                    }

                }
                img.src = data.Location;

                

            }else{
                var img_src_main = '/resources/images/awesomeupload/no_image.png';
                var img_src_list = '/resources/images/awesomeupload/no_image.png';
                switch (type) {
                    case 'main':
                        Ext.getCmp('template_image_panel').update({'img_src': img_src_main});
                        break;
                    case 'thump':
                        Ext.getCmp('design_image_panel').update({'thumpimg_src': img_src_list});
                        break;
                }
            }
        });
    }
    
    var graphicsTemplateDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplategraphicsTemplateViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tpl if="templateUrl != \'\'"><tr><th width="40%">Template </th><td><img id="preview_image" name="preview_image" src="{templateUrl}" style="width: 100%; max-width: 300px; max-height: 300px;" /> </td></tr></tpl>',
                    '<tpl if="designUrl != \'\'"><tr><th width="40%">Design </th><td><img id="preview_image" name="preview_image" src="{designUrl}" style="width: 100%; max-width: 300px; max-height: 300px;" /> </td></tr></tpl>',
                    '<tr><th width="40%">Title </th><td>  {grpTemp_title} </td></tr>',
                    '<tr><th width="40%">Application </th><td>  {grApplication} </td></tr>',
                    '<tr><th width="40%">Location / Theme</th><td>  {grLocation} </td></tr>',
                    '<tr><th width="40%">Templates </th><td>  {grTemplates} </td></tr>',
                    '<tpl if="grpTemp_type == \'1\'"><tr><th width="40%">Banner Position </th><td>  {bannerPosition} </td></tr></tpl>',
                    '<tr><th width="40%">Status </th><td>  {grStatus} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var graphicsTemplateGrid = function () {
        var _graphicsTemplateGridstore = graphicsTemplateGridstore();
        var _graphicsTemplateFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'templateID'
                },{
                    type: 'string',
                    dataIndex: 'grApplication'
                },{
                    type: 'string',
                    dataIndex: 'grLocation'
                },{
                    type: 'string',
                    dataIndex: 'grTemplates'
                },{
                    type: 'string',
                    dataIndex: 'FirstName'
                }
            ]
        });
        _graphicsTemplateFilter.remote = true;
        _graphicsTemplateFilter.autoReload = true;
        var _advPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _graphicsTemplateGridstore,
            //iconCls: 'money',
            id: 'graphicsTemplateGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _graphicsTemplateFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Template ID',
                    id: 'Title_auto_exp',
                    dataIndex: 'templateID',
                    sortable: true,
                    tooltip: 'Template ID',
                    hideable: true
                },
                {
                    header: 'Application',
                    dataIndex: 'grApplication',
                    sortable: true,
                    tooltip: 'Application',
                    hideable: true
                },{
                    header: 'Location / Theme',
                    dataIndex: 'grLocation',
                    sortable: true,
                    tooltip: 'Location / Theme',
                    hideable: true
                },{
                    header: 'Templates',
                    dataIndex: 'grTemplates',
                    sortable: true,
                    tooltip: 'Templates',
                    hideable: true
                },{
                    header: 'Banner Position',
                    dataIndex: 'bannerPosition',
                    sortable: true,
                    tooltip: 'Banner Position',
                    hideable: true
                },{
                    header: 'Status',
                    dataIndex: 'grStatus',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: true
                },{
                    header: 'Created By',
                    dataIndex: 'FirstName',
                    sortable: true,
                    tooltip: 'Created By',
                    hideable: true
                },{
                    header: 'Created On',
                    dataIndex: 'createdOn',
                    hidden: true,
                    sortable: true,
                    tooltip: 'Created On',
                    hideable: true
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            graphicsTemplateActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _graphicsTemplateGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectiongraphicsTemplateChanged
                }
            }),
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                    _graphicsTemplateGridstore.load();
                }
            },
            tbar: [{
                    text: 'Create Template',
                    tooltip: 'Create Template ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        var img_url = '/resources/images/default.png';
                        var thumpurl = '/resources/images/default.png';
                        var main_img = 1;
                        var grpTemp_id = '';
                        sliderWindow(grpTemp_id, main_img, img_url,thumpurl);

                    }
                }
            ]
        });
        return _advPanel;
    };
    var graphicsTemplateActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Delete",
                handler: function () {
                    var grpTemp_id = Ext.getCmp('graphicsTemplateGridPanel').getSelectionModel().getSelections()[0].data.grpTemp_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you want to delete this ad?', function (btn, text) {
                        if (btn == 'yes') {
                            deleteGraphTempl(grpTemp_id);
                        }
                    });
                }
            }]
    });
    var deleteGraphTempl = function (id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=deleteGraphTempl',
            params: {
                grpTemp_id: id,
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    Application.example.msg('Success', 'Removed item');
                    Ext.getCmp('graphicsTemplateGridPanel').getStore().reload();

                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };

    var sliderWindow = function (menu_id, content_id, img_url,thumpurl) {
        console.log('menu_id', menu_id);
        if (menu_id == '')
            var tit = 'Create Template Details';
        else
            var tit = 'Edit Graphics Template Details';
        var resultWindow = new Ext.Window({
            id: 'windowNewsfeedSliderWindow',
            title: tit,
            //iconCls: 'vender-items',
            shadow: false,
            width: 950,
            height: 650,
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [graphicsTemplateForm(), new Ext.Panel({
                    layout: "fit",
                    region: 'center',
                    width: 620,
                    border: false,
                    items: [uploadForm(img_url,thumpurl)]
                })],
            buttons: [
                {
                    text: 'Cancel',
                    id: 'cms_newsfeedcancel_btn',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    tabIndex: 505,
                    handler: function () {
                        resultWindow.close();
                    }
                }, {
                    text: "Save",
                    id: 'cms_newsfeedsave_btn',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    iconCls: 'thes_save',
                    tabIndex: 504,
                    handler: function () {
                        var edit_status;                    


                        if (menu_id == '')
                            edit_status = 0;
                        else
                            edit_status = 1;
                        Ext.Ajax.request({
                            url: modURL + '&op=saveGraphicsTemplate',
                            method: 'POST',
                            params: {
                                edit_status:edit_status,
                                id: Ext.getCmp('grpTemp_id').getValue(),
                                title: Ext.getCmp('grpTemp_title').getValue(),                                
                                application: Ext.getCmp('grpTemp_application').getValue(),
                                applicationName: Ext.getCmp('grpTemp_application').getRawValue(),
                                location: Ext.getCmp('grpTemp_location').getValue(),                                
                                locationName: Ext.getCmp('grpTemp_location').getRawValue(),
                                themeId:Ext.getCmp('grpTemp_theme').getValue(),
                                themeName:Ext.getCmp('grpTemp_theme').getRawValue(),
                                template: Ext.getCmp('grpTemp_type').getValue(),
                                templateName: Ext.getCmp('grpTemp_type').getRawValue(),
                                adzoneId: Ext.getCmp('grpTemp_adzones').getValue(),
                                designUrl: Ext.getCmp('aws_file_locationdesign').getValue(),
                                templateUrl: Ext.getCmp('aws_file_locationtemplate').getValue(),
                                status: Ext.getCmp('grpTemp_status').getValue(),
                            },
                            success: function (response) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.success === true && tmp.valid === true) {
                                    Application.example.msg('Success', tmp.message);

                                    RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('graphicsTemplateGridPanel'));
                                    Ext.getCmp('formpanelGraphicsTemplate').getForm().reset();
                                    Ext.getCmp('graphicsTemplateGridPanel').store.reload({
                                        params: {
                                            start: 0,
                                            limit: RECS_PER_PAGE
                                        }
                                    });

                                    Application.GraphicsTemplate.EventAddEdit = '';
                                    Application.GraphicsTemplate.ViewGraphicsTemplateMode(tmp.data.grpTemp_id);


                                    resultWindow.close();

                                } else if (tmp.success === true && tmp.valid === false) {
                                    Ext.Msg.alert("Notification.", tmp.message);
                                } else if (tmp.success === true && tmp.img_valid === false) {
                                    Ext.Msg.alert("Notification.", tmp.message);
                                } else {
                                    Ext.Msg.alert("Notification", "Please enter all required fields");
                                }
                            },
                            failure: function (response) {
                                var tmp = Ext.util.JSON.decode(response.responseText);
                                Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                            }

                        })



                    }
                }
            ],
            listeners: {
                afterrender: function () {
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    var upload_type = 1;
                    winLoadMask = new Ext.LoadMask(Ext.getCmp('windowNewsfeedSliderWindow').getEl());
                    winLoadMask.msg = 'Please wait...';
                    if (content_id == 1) {
                        Ext.getCmp('main_image_upload').getForm().load({
                            waitTitle: 'Please Wait',
                            waitMsg: 'Loading...',
                            url: modURL + '&op=get_img_s3_details',
                            params: {
                                rid: menu_id,
                                upload_type: content_id,
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
    return {
        Cache: {},
        initGraphicsTemplate: function () {
            var _graphicsTemplatePanelId = 'panelGraphicsTemplate';
            var _graphicsTemplatePanel = Ext.getCmp(_graphicsTemplatePanelId);
            if (Ext.isEmpty(_graphicsTemplatePanel)) {
                _graphicsTemplatePanel = graphicsTemplatePanel(_graphicsTemplatePanelId);
                Application.UI.addTab(_graphicsTemplatePanel);
                _graphicsTemplatePanel.doLayout();
            } else {
                Application.UI.addTab(_graphicsTemplatePanel);
            }
        },
        ViewGraphicsTemplateMode: function () {
            var grpTemp_id = arguments[0];

            /*<?php if (user_access("graphics_template", "saveGraphicsTemplate")) { ?> */
            Ext.getCmp('buttongraphicsTemplateEdit').show();
            /*<?php } ?> */
            Ext.getCmp('graphicsTemplatePanel').setTitle('View GraphicsTemplate Details');
            Ext.getCmp('buttongraphicsTemplateCancel').hide();
            //Ext.getCmp('formpanelGraphicsTemplate').hide();
            //Ext.getCmp('uploadformpanel').hide();
            Ext.getCmp('xtemplategraphicsTemplateViewDetails').show();
            Ext.getCmp('graphicsTemplatePanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=graphicsTemplateDetailsView',
                method: 'POST',
                params: {grpTemp_id: grpTemp_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplategraphicsTemplateViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('graphicsTemplatePanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('graphicsTemplatePanel').doLayout();
        },
        EditGrphTempView: function () {
            var grpTemp_id = arguments[0];
            var main_img = arguments[1];
            var img_url = arguments[2];
            var thumpurl = arguments[3];
            sliderWindow(grpTemp_id, main_img, img_url,thumpurl);

            Ext.getCmp('xtemplategraphicsTemplateViewDetails').show();
            Ext.getCmp('buttongraphicsTemplateEdit').show();

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var hnsForm = Ext.getCmp('formpanelGraphicsTemplate').getForm();
                hnsForm.load({
                    params: {
                        grpTemp_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=graphicsTemplate_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.data.templateUrl != '') {
                            Ext.getCmp('aws_file_locationtemplate').setValue(tmp.data.templateUrl);
                        }
                        if (tmp.data.designUrl != '') {
                            Ext.getCmp('aws_file_locationdesign').setValue(tmp.data.designUrl);
                        }
                        var applicationVal = tmp.data.grpTemp_application;
                                    if(applicationVal == 1 || applicationVal == 2){
                                        Ext.getCmp('grpTemp_location').hide();
                                        Ext.getCmp('design_image_panel_form').hide();
                                    if(applicationVal == 1){
                                        Ext.getCmp('grpTemp_theme').show();
                                        Ext.getCmp('grpTemp_theme').setValue(tmp.data.grpTemp_theme);
                                        Ext.getCmp('grpTemp_theme').getStore().load();
                                          
                                    }else{
                                        Ext.getCmp('grpTemp_theme').hide();
                                    }                                     
                                        Ext.getCmp('grpTemp_type').setValue(1);
                                        Ext.getCmp('grpTemp_type').setHideTrigger(true);
                                        Ext.getCmp('grpTemp_adzones').show();
                                        Ext.getCmp('grpTemp_adzones').setValue(tmp.data.grpTemp_adzones);
                                        Ext.getCmp('grpTemp_adzones').getStore().load({
                                            params: {
                                                applicationId: applicationVal,
                                            }
                                        });
                                        
                                    }else{
                                        Ext.getCmp('design_image_panel_form').show();
                                        Ext.getCmp('grpTemp_location').show();
                                        
                                        Ext.getCmp('grpTemp_theme').hide();
                                        Ext.getCmp('grpTemp_type').setHideTrigger(false);
                                        Ext.getCmp('grpTemp_type').store.removeAt(0);
                                        Ext.getCmp('grpTemp_location').setValue(tmp.data.grpTemp_location);
                                        Ext.getCmp('grpTemp_location').setRawValue(tmp.data.grLocation);
                                        Ext.getCmp('grpTemp_location').getStore().load({
                                            params: {
                                                applicationId: applicationVal
                                            }
                                        });
                                        
                                    }
                       
                            Ext.getCmp('widgetImageWidth').setValue(tmp.data.adzone_width);
                            Ext.getCmp('widgetImageHeight').setValue(tmp.data.adzone_height);
                                                

                    },
                    failure: function (form, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        Ext.Msg.alert("Error.", tmp.msg);
                    }
                });
            }
        },
    };

}();


