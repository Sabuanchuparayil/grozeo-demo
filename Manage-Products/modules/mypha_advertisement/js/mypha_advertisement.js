Application.MyphaAdvertisement = function () {

    var winLoadMask;
    var RECS_PER_PAGE = 12;
    var modURL = '?module=mypha_advertisement';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionadvertisementChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('advertisementGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('advertisementGridPanel').getSelectionModel().getSelections()[0].data.adv_id;
            Application.MyphaAdvertisement.ViewAdvertisementMode(ID);
        }
    };
    var advertisementPanel = function (id, adv_imageurl) {
        var _adPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Advertisement',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                advertisementGrid(),
                new Ext.Panel({
                    title: 'Adzone Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'advertisementPanel',
                    height: winsize.height * 0.6,
                    items: [
                        advertisementDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 5,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonadvertisementCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('advertisementGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('advertisementGridPanel').getSelectionModel().getSelections()[0].data.adv_id;
                                    Application.MyphaAdvertisement.ViewAdvertisementMode(ID);
                                }
                            }
                        }, /* <?php if (user_access("mypha_advertisement", "saveAdvertisement")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonadvertisementEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 4,
                            handler: function () {
//                                var ID = Ext.getCmp('advertisementGridPanel').getSelectionModel().getSelections()[0].data.adv_id;
//                                Application.MyphaAdvertisement.EditAdvertisementView(ID);

                                var ID = Ext.getCmp('advertisementGridPanel').getSelectionModel().getSelections()[0].data.adv_id;
                                var record = Ext.getCmp('advertisementGridPanel').getStore().getById(ID);
                                var main_img = 1;
                                var adv_id = record.data.adv_id;
                                Ext.Ajax.request({
                                    url: modURL + '&op=getAdImage',
                                    method: 'POST',
                                    params: {
                                        main_img: main_img,
                                        adv_id: record.data.adv_id,
                                    },
                                    success: function (res) {
                                        var tmp = Ext.decode(res.responseText);
                                        if (tmp.data != '' && tmp.data[0].adv_imageurl != '') {
                                            var img_url = tmp.data[0].adv_imageurl;
                                            Application.MyphaAdvertisement.EditAdView(adv_id, main_img, img_url);

                                        } else {
                                            var img_url = '/resources/images/default.png';
                                            Application.MyphaAdvertisement.EditAdView(adv_id, main_img, img_url);

                                        }
                                    }
                                })
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 4,
                            cls: 'left-right-buttons',
                            id: 'buttonadvertisementSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveAdvertisement();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _adPanel;
    };
    var advertisementGridstore = function () {
        var _advertisementList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listadvertisement',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'adv_id',
                root: 'data'
            }, ['adv_id', 'adv_title', 'adzone_name', 'adv_imageurl','adv_status','adv_usageType']),
            sortInfo: {
                field: 'adv_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('advertisementGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _advertisementList;
    };
    var adnameStore = function () {
        var adnameStore = new Ext.data.JsonStore({
            url: modURL + '&op=advName',
            method: 'post',
            fields: ['ad_id', 'adzone_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return adnameStore;
    };
    var productdetStore = function () {
        var productStore = new Ext.data.JsonStore({
            url: modURL + '&op=prtName',
            method: 'post',
            fields: ['stit_ID', 'stit_SKU'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return productStore;
    };
    var categorydetStore = function () {
        var categoryStore = new Ext.data.JsonStore({
            url: modURL + '&op=categoryName',
            method: 'post',
            fields: ['sub_category_id', 'sub_category'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return categoryStore;
    };
    var branddetStore = function () {
        var brandStore = new Ext.data.ArrayStore({
            url: modURL + '&op=brandName',
            method: 'POST',
            autoLoad: true,
            fields: ['brand_id', 'brand_name']
        });
        return brandStore;
    };
    var advertisementForm = function () {
        var advNameStore = adnameStore();
        var productStore = productdetStore();
        var categoryStore = categorydetStore();
        var brandStore = branddetStore();
        var _advertisementForm = new Ext.Panel({
            layout: "fit",
            region: 'west',
            width: 400,
            border: false,
            //autoHeight:true,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAdvertisement',
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
                            id: 'adv_title',
                            name: 'n[adv_title]',
                            anchor: '98%',
                            allowBlank: false,
                            width: 300,
                            tabIndex: 500,
                            maxLength: 300,
                            listeners:{
                                       afterrender:function(field){
                                       Ext.defer(function(){
                                       field.focus(true,100);
                                       },1);
                                     }
                                   }
                        }, {
                            xtype: 'combo',
                            fieldLabel: 'Adzone Name',
                            emptyText: 'Adzone Name',
                            id: 'ad_id',
                            hiddenName: 'n[adzone_id]',
                            anchor: '98%',
                            displayField: 'adzone_name',
                            valueField: 'ad_id',
                            triggerAction: 'all',
                            forceSelection: true,
                            selectOnFocus: true,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            lazyRender: true,
                            editable: true,
                            minChars: 2,
                            tabIndex: 501,
                            store: advNameStore
                        },
                        {
                            xtype: 'textfield',
                            id: 'adv_id',
                            name: 'n[adv_id]',
                            hidden: true
                        },
                       {
                            layout: 'column',
                            border: false,
                            items: [{
                                    columnWidth: .5,
                                    layout: 'form',
                                    border: false,
                                    items: [
                                        {
                                            xtype: 'combo',
                                            displayField: 'usagetype',
                                            valueField: 'usageid',
                                            anchor: '98%',
                                            mode: 'local',
                                            id: 'adv_usageType',
                                            name: 'adv_usageType',
                                            allowBlank: false,
                                            hiddenName: 'adv_usageType',
                                            forceSelection: true,
                                            fieldLabel: 'Usage Type',
                                            emptyText: 'Usage Type',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 502,
                                            store: new Ext.data.JsonStore({
                                                fields: ['usageid', 'usagetype'],
                                                data: [{usageid: 1, usagetype: 'Web'}, {usageid: 2, usagetype: 'App'}]
                                            })
                                        },
                                    ]
                                },
                                {
                                    columnWidth: .5,
                                    layout: 'form',
                                    border: false,
                                    items: [
                                         {
                                            xtype: 'combo',
                                            displayField: 'statustype',
                                            valueField: 'stid',
                                            anchor: '98%',
                                            mode: 'local',
                                            id: 'adv_status',
                                            name: 'adv_status',
                                            allowBlank: false,
                                            hiddenName: 'adv_status',
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
                                                data: [{stid: 0, statustype: 'Inactive'}, {stid: 1, statustype: 'Active'}/*,{stid: 2, statustype: 'Delete'}*/]
                                            })
                                        }

                                    ]
                                }

                            ]

                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    columnWidth: .5,
                                    layout: 'form',
                                    border: false,
                                    items: [
                                        {
                                            fieldLabel: 'Date From',
                                            xtype: 'datefield',
                                            text: 'From Date',
                                            id: 'adv_startdate',
                                            name: 'adv_startdate',
                                            hiddenName: 'adv_startdate',
                                            anchor: '98%',
                                            displayField: 'adv_startdate',
                                            tabIndex: 504,
                                            format: 'Y-m-d'
//                                    enableKeyEvents: true
                                        },
                                    ]
                                },
                                {
                                    columnWidth: .5,
                                    layout: 'form',
                                    border: false,
                                    items: [
                                        {
                                            fieldLabel: 'To',
                                            xtype: 'datefield',
                                            text: 'To Date',
                                            id: 'adv_enddate',
                                            name: 'adv_enddate',
                                            hiddenName: 'adv_enddate',
                                            anchor: '98%',
                                            displayField: 'adv_enddate',
                                            tabIndex: 505,
                                            format: 'Y-m-d'
//                                    enableKeyEvents: true
                                        }


                                    ]
                                }

                            ]

                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    xtype: 'radiogroup',
                                    anchor: '98%',
                                    tabIndex: 506,
                                    listeners: {
                                        check: function (rgp, checked) {
                                            Ext.getCmp('type1').getValue();
                                            Ext.getCmp('adv_offerpercent').show();
                                            Ext.getCmp('adv_offerType').show();
                                            Ext.getCmp('stit_ID').hide()
                                            Ext.getCmp('category_id').hide();
                                            Ext.getCmp('brand_id').hide();
                                        }
                                    },
                                    items: [{
                                            boxLabel: 'Offer',
                                            id: 'type1',
                                            name: 'n[adv_offer]',
                                            inputValue: 'Offer',
                                            listeners: {
                                                check: function (rgp, checked) {
                                                    if (checked == true) {
                                                        Ext.getCmp('type1').getValue();
                                                        Ext.getCmp('adv_offerpercent').show();
                                                        Ext.getCmp('adv_offerType').show();
                                                        Ext.getCmp('stit_ID').hide()
                                                        Ext.getCmp('category_id').hide();
                                                        Ext.getCmp('brand_id').hide();
                                                    }

                                                }
                                            }

                                        }, {
                                            boxLabel: 'Product',
                                            id: 'type2',
                                            name: 'n[adv_offer]',
                                            inputValue: 'Product',
                                            listeners: {
                                                check: function (rgp, checked) {
                                                    if (checked == true) {
                                                        Ext.getCmp('type2').getValue();
                                                        Ext.getCmp('adv_offerpercent').hide();
                                                        Ext.getCmp('adv_offerType').hide();
                                                        Ext.getCmp('stit_ID').show()
                                                        Ext.getCmp('category_id').hide();
                                                        Ext.getCmp('brand_id').hide();
                                                    }

                                                }
                                            }
                                        }, {
                                            boxLabel: 'Category',
                                            id: 'type3',
                                            name: 'n[adv_offer]',
                                            inputValue: 'Category',
                                            listeners: {
                                                check: function (rgp, checked) {
                                                    if (checked == true) {
                                                        Ext.getCmp('type3').getValue();
                                                        Ext.getCmp('adv_offerpercent').hide();
                                                        Ext.getCmp('adv_offerType').hide();
                                                        Ext.getCmp('stit_ID').hide()
                                                        Ext.getCmp('category_id').show();
                                                        Ext.getCmp('brand_id').hide();
                                                    }
                                                }
                                            }
                                        }, {
                                            boxLabel: 'Brand',
                                            id: 'type4',
                                            name: 'n[adv_offer]',
                                            inputValue: 'Brand',
                                            listeners: {
                                                check: function (rgp, checked) {
                                                    if (checked == true) {
                                                        Ext.getCmp('type4').getValue();
                                                        Ext.getCmp('adv_offerpercent').hide();
                                                        Ext.getCmp('adv_offerType').hide();
                                                        Ext.getCmp('stit_ID').hide()
                                                        Ext.getCmp('category_id').hide();
                                                        Ext.getCmp('brand_id').show();
                                                    }
                                                }
                                            }
                                        }]
                                }

                            ]

                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    columnWidth: .25,
                                    layout: 'form',
                                    border: false,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'Percentage',
                                            emptyText: 'Percentage',
                                            id: 'adv_offerpercent',
                                            name: 'n[adv_offerpercent]',
                                            anchor: '98%',
                                            hidden: true,
                                            allowBlank: false,
                                            width: 300,
                                            tabIndex: 500,
                                            maxLength: 300
                                        }
                                    ]
                                },
                                {
                                    columnWidth: .25,
                                    layout: 'form',
                                    border: false,
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Type',
                                            emptyText: 'Type',
                                            id: 'adv_offerType',
                                            name: 'n[adv_offerType]',
                                            anchor: '95%',
                                            tabIndex: 513,
                                            store: new Ext.data.ArrayStore({
                                                fields: ['intid', 'intname'],
                                                data: [['Product', 'Product'], ['Category', 'Category'], ['Brand', 'Brand']]
                                            }),
                                            displayField: 'intname',
                                            valueField: 'intid',
                                            mode: 'local',
                                            hidden: true,
                                            triggerAction: 'all',
                                            editable: true,
                                            listeners: {
                                                select: function () {
                                                    var cmb = Ext.getCmp('adv_offerType').getValue();
                                                    Ext.getCmp('stit_ID').reset();
                                                    Ext.getCmp('category_id').reset();
                                                    Ext.getCmp('brand_id').reset();
                                                    if (cmb == 'Product') {
                                                        Ext.getCmp('stit_ID').show();
                                                        Ext.getCmp('category_id').hide();
                                                        Ext.getCmp('brand_id').hide();
                                                    } else if (cmb == 'Category') {
                                                        Ext.getCmp('category_id').show();
                                                        Ext.getCmp('stit_ID').hide();
                                                        Ext.getCmp('brand_id').hide();
                                                    } else if (cmb == 'Brand') {
                                                        Ext.getCmp('brand_id').show();
                                                        Ext.getCmp('stit_ID').hide();
                                                        Ext.getCmp('category_id').hide();
                                                    } else {
                                                        Ext.getCmp('stit_ID').hide();
                                                        Ext.getCmp('category_id').hide();
                                                        Ext.getCmp('brand_id').hide();
                                                    }
                                                }
                                            }
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: 0.50,
                                    border: false,
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Product',
                                            id: 'stit_ID',
                                            hiddenName: 'n[stit_ID]',
                                            anchor: '98%',
                                            displayField: 'stit_SKU',
                                            valueField: 'stit_ID',
                                            triggerAction: 'all',
                                            forceSelection: true,
                                            selectOnFocus: true,
                                            mode: 'local',
                                            typeAhead: true,
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 501,
                                            hidden: true,
                                            store: productStore
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.50,
                                    border: false,
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Brand',
                                            id: 'brand_id',
                                            hiddenName: 'n[brand_id]',
                                            anchor: '98%',
                                            displayField: 'brand_name',
                                            valueField: 'brand_id',
                                            triggerAction: 'all',
                                            forceSelection: false,
                                            selectOnFocus: false,
                                            mode: 'local',
                                            typeAhead: true,
                                            lazyRender: false,
                                            editable: true,
                                            hidden: true,
                                            minChars: 2,
                                            tabIndex: 501,
                                            store: brandStore
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: 0.50,
                                    border: false,
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Category',
                                            id: 'category_id',
                                            hiddenName: 'n[category_id]',
                                            anchor: '98%',
                                            displayField: 'sub_category',
                                            valueField: 'sub_category_id',
                                            triggerAction: 'all',
                                            forceSelection: true,
                                            selectOnFocus: true,
                                            mode: 'local',
                                            typeAhead: true,
                                            lazyRender: true,
                                            editable: true,
                                            hidden: true,
                                            minChars: 2,
                                            tabIndex: 501,
                                            store: categoryStore
                                        }]
                                },
                            ]

                        }
                    ]
                })
            ],
             listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('adv_id').getValue())) {
                        var recordSelected = Ext.getCmp('adv_status').getStore().getAt(0);
                        Ext.getCmp('adv_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _advertisementForm;
    };


    var uploadForm = function (img) {
        return new Ext.Panel({
            id: 'uploadformpanel',
            items: [new Ext.Panel({
                    layout: "fit",
                    id: 'main_image_panel',
                    tpl: new Ext.XTemplate('<div class="details-outer">',
                            '<img width="200" id="demo123" height="200" src="{img_src}"></img>',
                            '</div>')
                }),
                {
                    xtype: 'hidden',
                    id: 'aws_file_location',
                    name: 'aws_file_location'
                }, {
                    xtype: 'hidden',
                    id: 'aws_file_bucket',
                    name: 'aws_file_bucket'
                },
                new Ext.form.FormPanel({
                    id: 'main_image_upload',
                    layout: 'form',
                    fileUpload: true,
                    autoHeight: true,
                    frame: true,
                    items: [{
                            xtype: 'hidden',
                            id: 'file_name',
                            name: 'file_name'
                        }, {
                            xtype: 'hidden',
                            id: 'albumBucketName',
                            name: 'albumBucketName'
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
                            xtype: 'box',
                            //fieldLabel: 'Current Image',
                            width: 200,
                            height: 200,
                            id: 'exist_img_box',
                            //autoEl: { tag: 'img', src: img, width: 120, height: 100 }
                            autoEl: {tag: 'img', src: img, width: '200', height: 200}

                        }
                    ],
                    buttons: [
                        {
                            xtype: 'fileuploadfield',
                            id: 'associated_file',
                            anchor: '98%',
                            fieldLabel: 'Select File',
                            name: 'file',
                            allowBlank: true,
                            buttonOnly: true,
                            buttonCfg: {
                                text: 'Choose Image',
                                width: 80
                            },
                            validator: function (v) {
                                if (v != '') {
                                    v = v.toLowerCase();
                                    var exp = /^.*\.(png|jpg|gif)$/i;
                                    if (!(exp.test(v))) {
                                        Ext.Msg.alert("Notification", "Upload a valid image file");
                                        return;
                                    }

                                    var associated_file = Ext.getCmp('associated_file').getValue();
                                    if (associated_file == '') {
                                        Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                        return;
                                    }

                                    var main_image_upload = Ext.getCmp('main_image_upload').getForm();
                                    if (main_image_upload.isValid()) {
                                        Ext.getCmp('exist_img_box').hide();
                                        winLoadMask.show();
                                        addPhoto();
                                    }
                                    return true;
                                }
                            }
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

    function addPhoto() {

        var albumBucketName = Ext.getCmp('albumBucketName').getValue();
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
            params: {Bucket: albumBucketName}
        });
        var files = document.getElementById('associated_file-file').files;
        if (!files.length) {
            winLoadMask.hide();
            return alert('Please choose a file to upload first.');
        }
        var file = files[0];
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split('.').pop();

        var fileName = uuidv4();
        fileName = fileName + '.' + fileExt;

       s3. upload({
            Key: filepath + fileName,
            /*file_Name*/ /*from server*/
            Body: file,
            ACL: 'public-read'
        }, function (err, data) {

            if (err) {
                winLoadMask.hide();
                //var img_src = Ext.BLANK_IMAGE_URL;
                var img_src = '/resources/images/default.png';
                Ext.getCmp('main_image_panel').update({'img_src': img_src});
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {
                winLoadMask.hide();
                Ext.Msg.alert("Notification", 'File uploaded successfully.');
                Application.MyphaAdvertisement.UploadedFileLocation = data.Location;
                Application.MyphaAdvertisement.UploadedFileBucket = data.Bucket;
                Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
                Ext.getCmp('aws_file_location').setValue(data.Location);
                /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
                Ext.getCmp('main_image_panel').update({'img_src': Application.MyphaAdvertisement.UploadedFileLocation});

            }
        });
    }
    var saveAdvertisement = function () {
        var adv_data = {
            form: Ext.getCmp('formpanelAdvertisement').getForm().getValues()
        };
        if (Ext.getCmp('adv_id').getValue() > 0) {
            var adId = Ext.getCmp('adv_id').getValue();
        } else {
            var d = new Date();
            var adId = '-';
        }
        var params = {
            action: 'Advertisement Details',
            module: 'Advertisement',
            op: 'saveAdvertisement',
            extrainfo: 'Adv Details save',
            id: adId
        };
        APICall(params, Application.MyphaAdvertisement.saveAdvertisement, adv_data);
    };
    var advertisementDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateadvertisementViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tpl if="adv_imageurl != \'\'"><tr><td><img id="preview_image" name="preview_image" src="{adv_imageurl}" width="360px" height="150px" /> </td></tr></tpl>',
                    '<tr><th width="40%">Title </th><td>  {adv_title} </td></tr>',
                    '<tr><th width="40%">Adzone Name </th><td>  {adzone_name} </td></tr>',
                    '<tr><th width="40%">From Date </th><td>  {adv_startdate} </td></tr>',
                    '<tr><th width="40%">To Date </th><td>  {adv_enddate} </td></tr>',
//                    '<tpl if="adv_offerpercent != 0"><tr><th width="40%">Offer % </th><td>  {adv_offerpercent } </td></tr></tpl>',
//                    '<tpl if="adv_offerType != \'\'"><tr><th width="40%">Offer Type</th><td>  {adv_offerType} </td></tr></tpl>',
//                    '<tr><tpl if="adv_offerType == \'\'"><th width="40%">{adv_offer}</th></tpl><tpl if="adv_offerType != \'\'"><th width="40%">{adv_offerType}</th>\n\
//                    <tpl if="adv_offer == \'offer\'"><td>  {product} </td></tpl><tpl if="adv_offer != \'offer\'"><td>  {product1} </td></tpl></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var advertisementGrid = function () {
        var _advertisementGridstore = advertisementGridstore();
        var _advertisementFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'adv_title'
                },
                {
                    type: 'string',
                    dataIndex: 'adzone_name'
                }
            ]
        });
        _advertisementFilter.remote = true;
        _advertisementFilter.autoReload = true;
        var _advPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _advertisementGridstore,
            //iconCls: 'money',
            id: 'advertisementGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _advertisementFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Title',
                    id: 'Title_auto_exp',
                    dataIndex: 'adv_title',
                    sortable: true,
                    tooltip: 'Title',
                    hideable: true
                },
                {
                    header: 'Adzone Name',
                    dataIndex: 'adzone_name',
                    sortable: true,
                    tooltip: 'Adzone Name',
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
                            advertisementActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    header: 'Action',
                    xtype: 'actioncolumn',
                    hideable: true,
                    sortable: false,
                    groupable: false,
                    items: [
                        {
                            icon: './resources/images/default/icons/drop.png',
                            tooltip: 'Delete Ad',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                            //    console.log(record);
                                var _active = record.data.adv_status;
                            //    if (_active == 2) {
                                    Ext.MessageBox.confirm('Confirm', 'Do you want to delete this ad?', function (btn, text) {
                                        if (btn == 'yes') {
                                            deleteAd(record.get('adv_id'));
                                        }
                                    });
                                //}
                            }
                        }
                    ]
                }*/

//                {
//                    header: 'Status',
//                    dataIndex: 'adzone_status',
//                    sortable: true,
//                    tooltip: 'Status'
//                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _advertisementGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionadvertisementChanged
                }
            }),
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                    _advertisementGridstore.load();
                }
            },
            tbar: [{
                    text: 'Create Advertisement',
                    tooltip: 'Create Advertisement ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        var img_url = '/resources/images/default.png';
                        var main_img = 1;
                        var newwsfeed_id = '';
                        sliderWindow(newwsfeed_id, main_img, img_url);

                    }
                }


            ]
        });
        return _advPanel;
    };
var advertisementActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Delete",
                handler: function () {
                                var adv_id = Ext.getCmp('advertisementGridPanel').getSelectionModel().getSelections()[0].data.adv_id;
                                    Ext.MessageBox.confirm('Confirm', 'Do you want to delete this ad?', function (btn, text) {
                                        if (btn == 'yes') {
                                            deleteAd(adv_id);
                                        }
                                    });
                }
            }]
    });
    var deleteAd = function (id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=deleteAd',
            params: {
                adv_id: id,
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    Application.example.msg('Success', 'Removed item');
                    Ext.getCmp('advertisementGridPanel').getStore().reload();

                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };

    var sliderWindow = function (menu_id, content_id, img_url) {
        console.log('menu_id', menu_id);
        if (menu_id == '')
            var tit = 'Create Advertisement Details';
        else
            var tit = 'Edit Advertisement Details';
        var resultWindow = new Ext.Window({
            id: 'windowNewsfeedSliderWindow',
            title: tit,
            //iconCls: 'vender-items',
            shadow: false,
            width: 650,
            height: 350,
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [advertisementForm(), new Ext.Panel({
                    layout: "fit",
                    region: 'center',
                    width: 400,
                    border: false,
                    items: [uploadForm(img_url)]
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
                        var offer, adv_offerType, edit_status;
                        if (Ext.getCmp('type1').getValue() == true) {
                            offer = 'Offer';
                        }
                        else if (Ext.getCmp('type2').getValue() == true) {
                            offer = 'Product';
                            adv_offerType = Ext.getCmp('stit_ID').getValue();
                        }
                        else if (Ext.getCmp('type3').getValue() == true) {
                            offer = 'Category';
                            adv_offerType = Ext.getCmp('category_id').getValue();
                        }
                        else if (Ext.getCmp('type4').getValue() == true) {
                            offer = 'Brand';
                            adv_offerType = Ext.getCmp('brand_id').getValue();
                        }
                        if (Ext.getCmp('adv_offerType').getValue() == 'Category') {
                            adv_offerType = Ext.getCmp('category_id').getValue();
                        } else if (Ext.getCmp('adv_offerType').getValue() == 'Product') {
                            adv_offerType = Ext.getCmp('stit_ID').getValue();
                        } else if (Ext.getCmp('adv_offerType').getValue() == 'Brand') {
                            adv_offerType = Ext.getCmp('brand_id').getValue();
                        }
                        if (menu_id == '')
                            edit_status = 0;
                        else
                            edit_status = 1;
                        Ext.Ajax.request({
                            url: modURL + '&op=saveAdvertisement',
                            method: 'POST',
                            params: {
                                id: Ext.getCmp('adv_id').getValue(),
                                title: Ext.getCmp('adv_title').getValue(),
                                adzone: Ext.getCmp('ad_id').getValue(),
                                adv_offerpercent: Ext.getCmp('adv_offerpercent').getValue(),
                                adv_startdate: Ext.getCmp('adv_startdate').getValue(),
                                adv_enddate: Ext.getCmp('adv_enddate').getValue(),
                                adv_offer: offer,
                                adv_offerType: Ext.getCmp('adv_offerType').getValue(),
                                adv_offerValueId: adv_offerType,
                                edit_status: edit_status,
                                adv_imageurl: Ext.getCmp('aws_file_location').getValue(),
                                Status : Ext.getCmp('adv_status').getValue(),
                                usageType : Ext.getCmp('adv_usageType').getValue()
                            },
                            success: function (response) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.success === true && tmp.valid === true) {
                                    Application.example.msg('Success', tmp.message);

                                    RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('advertisementGridPanel'));
                                    Ext.getCmp('formpanelAdvertisement').getForm().reset();
                                    Ext.getCmp('advertisementGridPanel').store.reload({
                                        params: {
                                            start: 0,
                                            limit: RECS_PER_PAGE
                                        }
                                    });

                                    Application.MyphaAdvertisement.EventAddEdit = '';
                                    Application.MyphaAdvertisement.ViewAdvertisementMode(tmp.data.adv_id);


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
        initAdvertisement: function () {
            var _advertisementPanelId = 'panelAdvertisement';
            var _advertisementPanel = Ext.getCmp(_advertisementPanelId);
            if (Ext.isEmpty(_advertisementPanel)) {
                _advertisementPanel = advertisementPanel(_advertisementPanelId);
                Application.UI.addTab(_advertisementPanel);
                _advertisementPanel.doLayout();
            } else {
                Application.UI.addTab(_advertisementPanel);
            }
        },
        EditAdvertisementView: function () {
            Application.MyphaAdvertisement.AdvertisementAddEdit = 'Edit';
            Ext.getCmp('advertisementPanel').doLayout();
            Ext.getCmp('advertisementPanel').setTitle('Edit Advertisement Details');
            Ext.getCmp('formpanelAdvertisement').show();
            Ext.getCmp('xtemplateadvertisementViewDetails').hide();
            /*<?php if (user_access("mypha_advertisement", "saveAdvertisement")) { ?> */
            Ext.getCmp('buttonadvertisementEdit').hide();
            Ext.getCmp('buttonadvertisementSave').show();
            /*<?php } ?> */
            Ext.getCmp('uploadformpanel').show();
            Ext.getCmp('buttonadvertisementCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {

                var masterForm = Ext.getCmp('formpanelAdvertisement').getForm();
                masterForm.load({
                    params: {
                        adv_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=advertisement_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                        console.log('tmp', tmp);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        saveAdvertisement: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelAdvertisement').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveAdvertisement',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            if (Application.MyphaAdvertisement.AdvertisementAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('advertisementGridPanel'));
                                Ext.getCmp('formpanelAdvertisement').getForm().reset();
                                Ext.getCmp('advertisementGridPanel').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('advertisementGridPanel').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('advertisementGridPanel').getStore().load();
                            }
                            Application.MyphaAdvertisement.AdvertisementAddEdit = '';
                            Application.MyphaAdvertisement.ViewAdvertisementMode(tmp.data.adv_id);
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Error', result.message);
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Error', 'Check the required fields');
            }
        },
        ViewAdvertisementMode: function () {
            var adv_id = arguments[0];

            /*<?php if (user_access("mypha_advertisement", "saveAdvertisement")) { ?> */
            Ext.getCmp('buttonadvertisementEdit').show();
            Ext.getCmp('buttonadvertisementSave').hide();
            /*<?php } ?> */
            Ext.getCmp('advertisementPanel').setTitle('View Advertisement Details');
            Ext.getCmp('buttonadvertisementCancel').hide();
            //Ext.getCmp('formpanelAdvertisement').hide();
            //Ext.getCmp('uploadformpanel').hide();
            Ext.getCmp('xtemplateadvertisementViewDetails').show();
            Ext.getCmp('advertisementPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=advertisementDetailsView',
                method: 'POST',
                params: {adv_id: adv_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateadvertisementViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('advertisementPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('advertisementPanel').doLayout();
        },
        EditAdView: function () {
            var adv_id = arguments[0];
            var main_img = arguments[1];
            var img_url = arguments[2];
            sliderWindow(adv_id, main_img, img_url);

            Application.MyphaAdvertisement.EventsAddEdit = 'Edit';

            Ext.getCmp('xtemplateadvertisementViewDetails').show();
            Ext.getCmp('buttonadvertisementEdit').show();

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var hnsForm = Ext.getCmp('formpanelAdvertisement').getForm();
                hnsForm.load({
                    params: {
                        adv_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=advertisement_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                        Ext.getCmp('aws_file_location').setValue(tmp.data.adv_imageurl);
                        if (tmp.data.adv_offer == 'Offer') {
                            Ext.getDom('type1').checked = true;
                            Ext.getCmp('adv_offerpercent').show();
                            Ext.getCmp('adv_offerType').show();
                            if (tmp.data.adv_offerType == 'Product') {
                                Ext.getCmp('stit_ID').show();
                                Ext.getCmp('stit_ID').setValue(tmp.data.adv_offerValueId);
                                Ext.getCmp('stit_ID').setRawValue(tmp.data.adv_offerValue_name);
                            } else if (tmp.data.adv_offerType == 'Category') {
                                Ext.getCmp('category_id').show();
                                Ext.getCmp('category_id').setValue(tmp.data.adv_offerValueId);
                                Ext.getCmp('category_id').setRawValue(tmp.data.adv_offerValue_name);
                            } else if (tmp.data.adv_offerType == 'Brand') {
                                Ext.getCmp('brand_id').show();
                                Ext.getCmp('brand_id').setValue(tmp.data.adv_offerValueId);
                                Ext.getCmp('brand_id').setRawValue(tmp.data.adv_offerValue_name);
                            }
                        }
                        else if (tmp.data.adv_offer == 'Product') {
                            Ext.getDom('type2').checked = true;
                            Ext.getCmp('adv_offerpercent').hide();
                            Ext.getCmp('adv_offerType').hide();
                            Ext.getCmp('stit_ID').show();
                            Ext.getCmp('category_id').hide();
                            Ext.getCmp('brand_id').hide();
                            Ext.getCmp('stit_ID').setValue(tmp.data.adv_offerValueId);
                            Ext.getCmp('stit_ID').setRawValue(tmp.data.adv_offerValue_namess);
                        }
                        else if (tmp.data.adv_offer == 'Category') {
                            Ext.getDom('type3').checked = true;
                            Ext.getCmp('adv_offerpercent').hide();
                            Ext.getCmp('adv_offerType').hide();
                            Ext.getCmp('stit_ID').hide();
                            Ext.getCmp('category_id').show();
                            Ext.getCmp('brand_id').hide();
                            Ext.getCmp('category_id').setValue(tmp.data.adv_offerValueId);
                            Ext.getCmp('category_id').setRawValue(tmp.data.adv_offerValue_namess);
                        }
                        else if (tmp.data.adv_offer == 'Brand') {
                            Ext.getDom('type4').checked = true;
                            Ext.getCmp('adv_offerpercent').hide();
                            Ext.getCmp('adv_offerType').hide();
                            Ext.getCmp('stit_ID').hide();
                            Ext.getCmp('category_id').hide();
                            Ext.getCmp('brand_id').show();
                            Ext.getCmp('brand_id').setValue(tmp.data.adv_offerValueId);
                            Ext.getCmp('brand_id').setRawValue(tmp.data.adv_offerValue_namess);
                        }
                        //Ext.getCmp('type1').setValue(tmp.data.adv_offer);

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


