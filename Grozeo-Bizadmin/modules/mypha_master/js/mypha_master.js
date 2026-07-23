Application.MyphaMaster = function () {
    var recs_per_page = 22;
    var modURL = '?module=mypha_master';
    var winsize = Ext.getBody().getViewSize();
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    ;
    var gridSelectionChangedSubcategory = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterSubcategoryDataview').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterSubcategoryDataview').getSelectionModel().getSelections()[0].data.subCategory_id;
            Application.MyphaMaster.ViewSubcategory(ID);
        }
    };
    var gridSelectionChangedmanufacture = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterListingManufacture').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterListingManufacture').getSelectionModel().getSelections()[0].data.manufacture_id;
            Application.MyphaMaster.ViewManufactureMode(ID);
        }
    };

    var gridSelectionChangedWork = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewWorksdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewWorksdata').getSelectionModel().getSelections()[0].data.medwork_id;
            Application.MyphaMaster.ViewWork(ID);
        }
    };



    var gridSelectionChangedUse = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewUsedata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewUsedata').getSelectionModel().getSelections()[0].data.meduse_id;
            Application.MyphaMaster.ViewUse(ID);
        }
    };

    var gridSelectionChangeddisease = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().getSelections()[0].data.disease_id;
            Application.MyphaMaster.ViewDisease(ID);
        }
    };
    var gridSelectionChangedMedicinetypes = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').getSelectionModel().getSelections()[0].data.medicine_type_id;
            Application.MyphaMaster.ViewMedicineTypes(ID);
        }
    };
    var gridSelectionChangedCategory = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewCategorysdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewCategorysdata').getSelectionModel().getSelections()[0].data.category_id;
            Application.MyphaMaster.ViewCategorys(ID);
        }
    };
    var gridSelectionChangedWarningCategory = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').getSelectionModel().getSelections()[0].data.warningCategory_id;
            Application.MyphaMaster.ViewWarningCategorys(ID);
        }
    };
    var gridSelectionChangedWarning = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewWarningdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewWarningdata').getSelectionModel().getSelections()[0].data.warning_id;
            Application.MyphaMaster.ViewWarning(ID);
        }
    };

    var gridSelectionChangedSideeffect = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewSideeffectdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewSideeffectdata').getSelectionModel().getSelections()[0].data.medsideffect_id;
            Application.MyphaMaster.ViewSideEffect(ID);
        }
    };

    var gridSelectionChangedInfo = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewInformationdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewInformationdata').getSelectionModel().getSelections()[0].data.medadinfo_id;
            Application.MyphaMaster.ViewInfo(ID);
        }
    };

    var gridSelectionChangedComposition = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().getSelections()[0].data.composition_id;
            Application.MyphaMaster.ViewComposition(ID);
        }
    };

    var gridSelectionChangedUnit = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewUnitdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewUnitdata').getSelectionModel().getSelections()[0].data.unit_id;
            Application.MyphaMaster.ViewUnit(ID);
        }
    };



    var gridSelectionChangedMedicineContent = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').getSelectionModel().getSelections()[0].data.medicineContent_id;
            Application.MyphaMaster.ViewMedicineContents(ID);
        }
    };
    var diseaseForm = function () {
        // var centreStore = deptForDisease();
        var diseaseSaveForm = new Ext.form.FormPanel({
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            id: 'diseaseSaveForm',
            items: [{
                    xtype: 'hidden',
                    id: 'disease_id',
                    name: 'n[disease_id]'
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Disease Name',
                    id: 'disease_name',
                    name: 'n[disease_name]',
                    anchor: '98%',
                    tabIndex: 201,
                    allowBlank: false
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Disease Description',
                    id: 'disease_description',
                    name: 'n[disease_description]',
                    anchor: '98%',
                    tabIndex: 201,
                    allowBlank: false
                }
            ]
        });
        return diseaseSaveForm;
    };
    var diseaseSave = function () {
        var form_data = {
            form: Ext.getCmp('diseaseSaveForm').getForm().getValues()
        };
        if (Ext.getCmp('disease_id').getValue() > 0) {
            var mstId = Ext.getCmp('disease_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Disease',
            module: 'Master',
            op: 'saveDisease',
            extrainfo: 'Disease save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.diseaseSave, form_data);
    };





    var diseaseStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listDisease',
                method: 'post'
            }),
            fields: ['disease_id', 'disease_name', 'disease_description'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().selectRow(0);
                }
            }
        });
        store.setDefaultSort('disease_name', 'ASC');
        return store;
    };
    var diseaseGrid = function (id) {
        var disease_store = diseaseStore();
        var disease_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'disease_name'
                },
                {
                    type: 'string',
                    dataIndex: 'disease_description'
                }
            ]
        });
        disease_filter.remote = true;
        disease_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: disease_store,
           // title: 'Disease',
            //iconCls: 'optickets',
            plugins: [disease_filter],
            id: 'gridpanelMasterDiseaseDataview',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Disease Name',
                    id: 'disease_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'disease_name',
                    tooltip: 'Disease Name'
                },
                {
                    header: 'Disease Description',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'disease_description',
                    tooltip: 'Disease Description'
                },{
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            diseaseActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            iconCls: 'icon-add-table',
                            tooltip: 'Disease',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var disease_id = record.get('disease_id');
                                Application.MyphaMaster.Cache.disease_id = disease_id;
                                MedDiseaseAddWindow(disease_id);
                            }
                        }, {
                            sortable: false,
                            getClass: function (v, meta, rec) {
                                this.items[0].tooltip = 'Upload Image';
                                return 'upload';

                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var disease_id = record.data.disease_id;
                                var uploadtype = 'disease';
                                Ext.Ajax.request({
                                    url: modURL + '&op=getDiseaseImage',
                                    method: 'POST',
                                    params: {
                                        disease_id: disease_id
                                    },
                                    success: function (res) {
                                        var tmp = Ext.decode(res.responseText);
                                        console.log("temp is -", tmp);
                                        if (tmp.data != '')
                                        {
                                            var disease_image = tmp.data[0].disease_image;
                                            Application.MyphaMaster.uploadimageDisease(disease_id, uploadtype, disease_image);
                                        }
                                        else {
                                            var disease_image = '';
                                            Application.MyphaMaster.uploadimageDisease(disease_id, uploadtype, disease_image);

                                        }
                                    }
                                })

                            }
                        }
                    ]
                }*/
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangeddisease
                }

            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('disease_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.disease_id = ID;
                        Ext.getCmp('diseaseSaveForm').hide();
                        Application.MyphaMaster.ViewDisease(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    disease_store.load();
                }
            },
            tbar: [/* <?php if (user_access("mypha_master", "saveDisease")) { ?> */{
                    xtype: 'button',
                    text: 'Create Disease',
                    tooltip: 'Create Disease',
                    iconCls: 'add',
                    handler: function () {
                        Application.MyphaMaster.diseaseAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('diseaseSaveForm').getForm();
                        Ext.getCmp('panelMasterDiseaseParent').setTitle('Create Disease Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('disease_name').focus(false, 100);
                        Ext.getCmp('buttonMasterdiseaseEdit').hide();
                        Ext.getCmp('buttonMasterdiseaseSave').show();
                        Ext.getCmp('buttonMasterdiseaseCancel').show();
                        Ext.getCmp('diseaseSaveForm').show();
                        Ext.getCmp('panelMasterDiseaseDetailsView').hide();
                        Ext.getCmp('panelMasterDiseaseParent').doLayout();


                    }

                }/*<?php  } ?>*/],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: disease_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [disease_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'disease_name_auto_exp'
        });
        return grid_panel;
    };
 var diseaseActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Disease",
                handler: function () {
                    var disease_id = Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().getSelections()[0].data.disease_id;
                    MedDiseaseAddWindow(disease_id);
                }
            }, {
                text: "Upload Image",
                handler: function () {
                                var disease_id = Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().getSelections()[0].data.disease_id;
                                var uploadtype = 'disease';
                                Ext.Ajax.request({
                                    url: modURL + '&op=getDiseaseImage',
                                    method: 'POST',
                                    params: {
                                        disease_id: disease_id
                                    },
                                    success: function (res) {
                                        var tmp = Ext.decode(res.responseText);
                                        console.log("temp is -", tmp);
                                        if (tmp.data != '')
                                        {
                                            var disease_image = tmp.data[0].disease_image;
                                            Application.MyphaMaster.uploadimageDisease(disease_id, uploadtype, disease_image);
                                        }
                                        else {
                                            var disease_image = '';
                                            Application.MyphaMaster.uploadimageDisease(disease_id, uploadtype, disease_image);

                                        }
                                    }
                                })
                }
            }]
    });

    var diseaseuploadForm = function (img) {
        return new Ext.Panel({
            height: "400",
            items: [new Ext.Panel({
                    layout: "fit",
                    id: 'disease_image_panel',
                    tpl: new Ext.XTemplate('<div class="details-outer">',
                            '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
                            '</div>')
                }),
                {
                    xtype: 'hidden',
                    id: 'di_aws_file_location',
                    name: 'di_aws_file_location'
                }, {
                    xtype: 'hidden',
                    id: 'di_aws_file_bucket',
                    name: 'di_aws_file_bucket'
                },
                new Ext.form.FormPanel({
                    id: 'disease_image_upload',
                    layout: 'form',
                    fileUpload: true,
                    autoHeight: true,
                    frame: true,
                    items: [
                        {
                            xtype: 'hidden',
                            id: 'di_file_name', name: 'di_file_name'
                        }, {
                            xtype: 'hidden',
                            id: 'di_albumBucketName',
                            name: 'di_albumBucketName'
                        }, {
                            xtype: 'hidden',
                            id: 'di_accessKey',
                            name: 'di_accessKey'
                        }, {
                            xtype: 'hidden',
                            id: 'di_secretKey',
                            name: 'di_secretKey'
                        }, {
                            xtype: 'hidden',
                            id: 'di_bucketRegion',
                            name: 'di_bucketRegion'
                        },
                        {
                            xtype: 'hidden',
                            id: 'di_oncompleteurl',
                            name: 'di_oncompleteurl'
                        },
                        {
                            xtype: 'hidden',
                            id: 'di_img_path_db',
                            name: 'di_img_path_db'
                        },
                        {
                            xtype: 'box',
                            width: 200,
                            height: 200,
                            id: 'di_exist_img_box',
                            autoEl: {tag: 'img', src: img, width: 200, height: 200}
                        }
                    ],
                    buttons: [
                        {
                            xtype: 'fileuploadfield',
                            id: 'diseaseimg_file',
                            anchor: '98%',
                            fieldLabel: 'Select File',
                            name: 'file',
                            allowBlank: true,
                            buttonOnly: true,
                            // hidden: true,
                            buttonCfg: {
                                text: 'Choose File',
                                //iconCls: 'finascop_upload_file',
                                width: 80
                            },
                            validator: function (v) {
                                if (v != '') {
                                    v = v.toLowerCase();
                                    var exp = /^.*\.(png|jpg|gif)$/i;
                                    if (!(exp.test(v))) {
                                        Ext.Msg.alert("Notification", "Upload a valid image file");
                                        return;
                                        //return 'Upload a valid image file of format JPG.';
                                    }

                                    var diseaseimg_file = Ext.getCmp('diseaseimg_file').getValue();
                                    if (diseaseimg_file == '') {
                                        Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                        return;
                                    }

                                    var disease_image_upload = Ext.getCmp('disease_image_upload').getForm();
                                    if (disease_image_upload.isValid()) {
                                        Ext.getCmp('di_exist_img_box').hide();
                                        winLoadMask.show();
                                        adddiseasePhoto();
                                    }
                                    return true;
                                }
                            }
                        }]
                })]
        });
        // });
    };
    function adddiseasePhoto() {

        var di_albumBucketName = Ext.getCmp('di_albumBucketName').getValue();
        var di_bucketRegion = Ext.getCmp('di_bucketRegion').getValue();
        var filepath = Ext.getCmp('di_oncompleteurl').getValue();
        AWS.config.update({
            region: di_bucketRegion,
            credentials: new AWS.Credentials(
                    Ext.getCmp('di_accessKey').getValue(),
                    Ext.getCmp('di_secretKey').getValue(), null
                    )
        });
        var s3 = new AWS.S3({
            apiVersion: '2006-03-01',
            params: {Bucket: di_albumBucketName}
        });
        var files = document.getElementById('diseaseimg_file-file').files;
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


        s3.upload({
            Key: filepath + fileName, /*file_Name*/ /*from server*/
            Body: file,
            ACL: 'public-read'
        }, function (err, data) {

            if (err) {
                winLoadMask.hide();
                var img_src = Ext.BLANK_IMAGE_URL;
                Ext.getCmp('disease_image_panel').update({'img_root': img_src});
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {

                winLoadMask.hide();
                Application.example.msg("Notification", 'File uploaded successfully.');
                Application.MyphaMaster.UploadedFileLocation = data.Location;
                Application.MyphaMaster.UploadedFileBucket = data.Bucket;
                Ext.getCmp('di_aws_file_bucket').setValue(data.Bucket);
                Ext.getCmp('di_aws_file_location').setValue(data.Location);
                /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
                Ext.getCmp('disease_image_panel').update({'img_root': Application.MyphaMaster.UploadedFileLocation});

            }
        });
    }
    ;
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    ;




    var MedDiseaseAddWindow = function (disease_id) {
        /* Store For Medicine Combo */
        var _productStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=loadProductCombo',
            fields: ['stit_ID', 'stit_SKU'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            remoteSort: true,
            listeners: {
                beforeload: function () {
                    this.baseParams.isMedicine = Application.MyphaMaster.Cache.isMedicine;
                }

            }
        });

        var medDiseasemapWindow = Ext.getCmp('window_AddData');
        if (Ext.isEmpty(medDiseasemapWindow)) {
            var medDiseasemapWindow = new Ext.Window({
                id: 'window_AddData',
                title: 'Disease Medicine Map',
                //iconCls: 'additem',
                shadow: false,
                width: 600,
                autoScroll: true,
                height: 400,
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                closable: true,
                items: [
                    new Ext.Panel({
                        layout: "fit",
                        region: 'center',
                        width: 550,
                        border: false,
                        items: [{
                                region: 'north',
                                autoHeight: true,
                                items: new Ext.form.FormPanel({
                                    frame: false,
                                    border: false,
                                    autoHeight: true,
                                    autoScroll: true,
                                    labelWidth: 120,
                                    labelAlign: "top",
                                    bodyStyle: {
                                        "background-color": "white",
                                        padding: "5px 5px 5px 10px"
                                    },
                                    id: "medDiseaseForm",
                                    items: [{
                                            xtype: 'hidden',
                                            id: 'medDise_id',
                                            name: 'medDise_id'
                                        },
                                        {
                                            layout: "column",
                                            border: false,
                                            items: [{
                                                    xtype: 'radiogroup',
                                                    labelWidth: 100,
                                                    id: 'radiobuttonMedicineId',
                                                    columnWidth: 0.25,
                                                    listeners: {
                                                        check: function (rgp, checked) {
                                                            Ext.getCmp('radiobuttonMedicineId').getValue();
                                                        }
                                                    },
//                                                    
                                                    items: [{
                                                            boxLabel: 'Medicine',
                                                            id: 'med',
                                                            name: 'rb-auto',
                                                            checked: true,
                                                            inputValue: 'Medicine',
                                                            listeners: {
                                                                check: function (rgp, checked) {
                                                                    if (checked == true) {
                                                                        Application.MyphaMaster.Cache.isMedicine = 1;
                                                                        Ext.getCmp('pr_id').getStore().load({
                                                                            params: {
                                                                                isMedicine: Application.MyphaMaster.Cache.isMedicine
                                                                            }
                                                                        });
                                                                    }

                                                                }
                                                            }

                                                        }, {
                                                            boxLabel: 'Product',
                                                            id: 'prd',
                                                            name: 'rb-auto',
                                                            inputValue: 'Product',
                                                            listeners: {
                                                                check: function (rgp, checked) {
                                                                    if (checked == true) {
                                                                        Application.MyphaMaster.Cache.isMedicine = 0;
                                                                        Ext.getCmp('pr_id').getStore().load({
                                                                            params: {
                                                                                isMedicine: Application.MyphaMaster.Cache.isMedicine
                                                                            }
                                                                        });
                                                                    }

                                                                }
                                                            }
                                                        }]
                                                },
                                                {
                                                    layout: 'form',
                                                    columnWidth: 0.33,
                                                    border: false,
                                                    items: [{
                                                            xtype: "combo",
                                                            fieldLabel: "Product",
                                                            hideLabel: true,
                                                            name: "stit_ID",
                                                            id: "pr_id",
                                                            anchor: "98%",
                                                            store: _productStore,
                                                            mode: "local",
                                                            selectOnFocus: true,
                                                            hiddenName: "stit_ID",
                                                            displayField: "stit_SKU",
                                                            valueField: "stit_ID",
                                                            typeAhead: true,
                                                            triggerAction: "all",
                                                            lazyRender: true,
                                                            allowBlank: true,
                                                            editable: true,
                                                            hideTrigger: false,
                                                            tabIndex: 602
                                                        }]
                                                },
                                                {
                                                    columnWidth: 0.20,
                                                    layout: "form",
                                                    border: false,
                                                    style: 'margin-top:7px;',
                                                    items: [{
                                                            xtype: 'button',
                                                            tooltip: 'Add New ',
                                                            text: 'Add',
                                                            iconCls: 'add',
                                                            width: 60,
                                                            tabIndex: 604,
                                                            handler: function () {
                                                                /* Save Results */
                                                                //var disease_id = arguments[0];
                                                                var form = Ext.getCmp('medDiseaseForm').getForm();
                                                                Application.MyphaMaster.Cache.disease_id = disease_id;
                                                                if (form.isValid()) {
                                                                    var form_data = {
                                                                        form: Ext.getCmp('medDiseaseForm').getForm().getValues()
                                                                    };
                                                                    var params = {
                                                                        action: 'Save Details',
                                                                        module: 'mypha_master',
                                                                        op: 'mapMedicinetoDisease',
                                                                        extrainfo: 'Map Medicine to Disease',
                                                                        id: Application.MyphaMaster.Cache.disease_id
                                                                    };
                                                                    APICall(params, Application.MyphaMaster.mapMedicinetoDisease, form_data);
                                                                } else {
                                                                    Application.example.msg('Warning', 'Fill the required fields');
                                                                }
                                                            }
                                                        }]
                                                }]
                                        }
                                    ]
                                })
                            },
                            {
                                region: 'center',
                                layout: 'fit',
                                items: medicineDiseaseShowGrid()
                            }
                        ]
                    })
                ]
            });
        }
        medDiseasemapWindow.show();
        medDiseasemapWindow.doLayout();
        medDiseasemapWindow.center();
        if (Ext.getCmp('radiobuttonMedicineId').getValue() == 'Medicine') {
            Application.MyphaMaster.Cache.isMedicine = 1;
        } else {
            Application.MyphaMaster.Cache.isMedicine = 0;
        }
    };

    var medicineDiseaseShowGrid = function () {
        var mapmedDisease_store = _mapmedDiseaseGridStore();
//        var med_filter = new Ext.ux.grid.GridFilters({
//            filters: [{
//                    type: 'string',
//                    dataIndex: 'stit_SKU'
//                }]
//        });
//        med_filter.remote = true;
//        med_filter.autoReload = true;
        var _mapMedDisease_grid = new Ext.grid.GridPanel({
            id: 'gridpanelMedicineDiseaseMap',
            store: mapmedDisease_store,
            layout: 'fit',
            frame: false,
            border: false,
            height: 400,
            autoScroll: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText:
                        '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl:
                        '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary()],
            viewConfig: {
                forceFit: true,
            },
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Medicine',
                    sortable: true,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Medicine',
                    hideable: false,
                    width: 200
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    width: 150,
                    items: [{
                            getClass: function (v, meta, rec) {

                                this.items[0].tooltip = 'Delete Medicine';
                                return 'my-icon12';
                            },
                            handler: function (grid, rowIndex, colIndex)
                            {
                                var record = grid.store.getAt(rowIndex);
                                stit_ID = record.data.stit_ID;
                                disease_id = record.data.disease_id;
                                Ext.MessageBox.confirm("Confirm", "Are you sure to delete this record?", function (btn) {
                                    if (btn == "yes") {
                                        Ext.Ajax.request({
                                            url: modURL + '&op=deleteMedicine',
                                            method: 'POST',
                                            params: {
                                                disease_id: disease_id,
                                                stit_ID: stit_ID
                                            },
                                            success: function (response) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true && tmp.valid === true) {
                                                    Application.example.msg('Success', tmp.message);
                                                    var document_grid_store = Ext.getCmp('gridpanelMedicineDiseaseMap').getStore();
                                                    document_grid_store.load({
                                                        params: {
                                                            disease_id: disease_id,
                                                            stit_ID: stit_ID
                                                        }
                                                    });
                                                    Ext.getCmp('medDise_id').reset();
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
                                                Ext.MessageBox.alert('Error', tmp.message);
                                            }

                                        })
                                    }
                                });
                            }
                        }]
                }],
            listeners: {
                afterrender: function () {
                    mapmedDisease_store.load();
                }

            },
            stripeRows: true
        });
        return _mapMedDisease_grid;
    };
    var _mapmedDiseaseGridStore = function () {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + "&op=listmappedDiseaseinMedi",
                method: "post"
            }),
            reader: new Ext.data.JsonReader(
                    {
                        totalProperty: "totalCount",
                        idProperty: "medDise_id",
                        root: "data"
                    },
            ["stit_ID", "disease_id", "is_medicine", "medDise_id", "stit_SKU"]
                    ),
            sortInfo: {
                field: "medDise_id",
                direction: "DESC"
            },
            groupField: "",
            groupDir: "ASC",
            remoteSort: true,
            autoLoad: false,
            root: "data",
            listeners: {
                load: function () {

                },
                beforeload: function (store, e) {
                    this.baseParams.disease_id = Application.MyphaMaster.Cache.disease_id;
                }
            }
        });
        return _store;
    };




    var manufacturePanel = function (id) {
        var _manufacturePanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Manufacturer/Supplier',
            id: id,
            //iconCls: 'company',
            items: [
                manufactureGrid(),
                new Ext.Panel({
                    title: 'Manufacturer/Supplier Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterManufacture',
                    height: winsize.height * 0.6,
                    items: [manufactureForm(), manufactureDetailsView()],
                    buttonAlign: 'right',
                    fbar: [
                        {
                            text: "Cancel",
                            tabIndex: 103,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterManufactureCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterListingManufacture').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterListingManufacture').getSelectionModel().getSelections()[0].data.manufacture_id;
                                    Application.MyphaMaster.ViewManufactureMode(ID);
                                }
                            }
                        },
                        /* <?php if (user_access("mypha_master", "saveManufacture")) { ?> */{
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterManufactureEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 104,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterListingManufacture').getSelectionModel().getSelections()[0].data.manufacture_id;
                                Application.MyphaMaster.EditManufactureView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 102,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterManufactureSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveManufacture();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _manufacturePanel;
    };


    var diseasePanel = function (id) {
        var diseasePanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Disease',
            id: id,
            //iconCls: 'company',
            items: [
                diseaseGrid(),
                new Ext.Panel({
                    title: 'Disease Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterDiseaseParent',
                    height: winsize.height * 0.6,
                    items: [diseaseForm(), DiseaseMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [
                        {
                            text: "Cancel",
                            tabIndex: 204,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterdiseaseCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().getSelections()[0].data.disease_id;
                                    Application.MyphaMaster.ViewDisease(ID);
                                }
                            }
                        },
                        /* <?php if (user_access("mypha_master", "saveDisease")) { ?> */
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterdiseaseEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 205,
                            handler: function () {


                                var ID = Ext.getCmp('gridpanelMasterDiseaseDataview').getSelectionModel().getSelections()[0].data.disease_id;
                                Application.MyphaMaster.EditDiseaseView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 203,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterdiseaseSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                diseaseSave();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return diseasePanel;
    };


    var manufactureGrid = function () {
        var _manufactureGridstore = manufactureGridstore();
        var _manufactureFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'manufacture_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }
            ]
        });
        _manufactureFilter.remote = true;
        _manufactureFilter.autoReload = true;
        var _gridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _manufactureGridstore,
            //iconCls: 'money',
            id: 'gridpanelMasterListingManufacture',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [_manufactureFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Manufacturer/Supplier',
                    dataIndex: 'manufacture_name',
                    sortable: true,
                    tooltip: 'Manufacturer/Supplier',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _manufactureGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display",
                plugins: [_manufactureFilter]
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedmanufacture
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('manufacture_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.manufacture_id = ID;
                        Ext.getCmp('formpanelMasterManufacture').hide();
                        Application.MyphaMaster.ViewManufactureMode(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _manufactureGridstore.load();
                }
            },
            tbar: [/* <?php if (user_access("mypha_master", "saveManufacture")) { ?> */{
                    text: 'Create Manufacturer/Supplier',
                    tooltip: 'Create Manufacturer/Supplier',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.ManufactureAddEdit = 'Add';
                        var masterForm = Ext.getCmp('formpanelMasterManufacture').getForm();
                        Ext.getCmp('panelMasterManufacture').setTitle('Create Manufacturer/Supplier Details');
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('textfieldMasterManufacture').focus(false, 100);
                        Ext.getCmp('buttonMasterManufactureEdit').hide();
                        Ext.getCmp('buttonMasterManufactureSave').show();
                        Ext.getCmp('buttonMasterManufactureCancel').show();
                        Ext.getCmp('formpanelMasterManufacture').show();
                        Ext.getCmp('xtemplateMasterManufactureViewDetails').hide();
                        Ext.getCmp('panelMasterManufacture').doLayout();
                        var recordSelected = Ext.getCmp('manufacturStatus').getStore().getAt(0);
                        Ext.getCmp('manufacturStatus').setValue(recordSelected.get('id'));
                    }
                }/*<?php  } ?>*/]
        });
        return _gridPanel;
    };

    var manufactureGridstore = function () {
        var _manufactureList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listManufacture',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'manufacture_id',
                root: 'data'
            }, ['manufacture_id', 'manufacture_name', 'status', 'status']),
            sortInfo: {
                field: 'manufacture_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterListingManufacture').getSelectionModel().selectRow(0);
                }
            }
        });
        return _manufactureList;
    };
    var manufactureForm = function () {
        var _manufactureForm = new Ext.FormPanel({
            id: 'formpanelMasterManufacture',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            labelWidth: 100,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Manufacturer/Supplier',
                    id: 'textfieldMasterManufacture',
                    name: 'n[manufacture_name]',
                    anchor: '95%',
                    allowBlank: false,
                    tabIndex: 100,
                    maxLength: 300
                },
                {
                    xtype: 'textfield',
                    id: 'textfieldMasterManufactureId',
                    name: 'n[manufacture_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "anchor": "95%",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 101,
                    "emptyText": "Set status..",
                    "id": 'manufacturStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('textfieldMasterManufactureId').getValue())) {
                        var recordSelected = Ext.getCmp('manufacturStatus').getStore().getAt(0);
                        Ext.getCmp('manufacturStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _manufactureForm;
    };
    var saveManufacture = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterManufacture').getForm().getValues()
        };
        if (Ext.getCmp('textfieldMasterManufactureId').getValue() > 0) {
            var mstId = Ext.getCmp('textfieldMasterManufactureId').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Manufacture',
            module: 'Master',
            op: 'saveManufacture',
            extrainfo: 'Manufacturer save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveManufacture, form_data);
    };
    var manufactureDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateMasterManufactureViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Manufacturer/Supplier </th><td>  {manufacture_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var MedicineTypeForm = function () {
        var _medicinetypesFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterMedicineTypes',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Dosage Form',
                    id: 'medicine_type_name',
                    name: 'n[medicine_type_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 301,
                    maxLength: 200
                },
                {
                    xtype: 'textfield',
                    id: 'medicine_type_id',
                    name: 'n[medicine_type_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 302,
                    "emptyText": "Set status..",
                    "id": 'comboMasterMedicineTypesStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('medicine_type_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterMedicineTypesStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterMedicineTypesStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _medicinetypesFormPanel;
    };
    var MedicineTypeMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterMedicineTypesDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Dosage Form </th><td>  {medicine_type_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var MedicineTypesMasterStore = function () {
        var _medicinetypesMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMedicineTypes',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'medicine_type_id',
                root: 'data'
            }, ['medicine_type_id', 'medicine_type_name', 'status']),
            sortInfo: {
                field: 'medicine_type_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _medicinetypesMasterStore;
    };
    var MedicineTypeMainGrid = function () {
        var _medicinetypesStore = MedicineTypesMasterStore();
        var _medicinetypesGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'medicine_type_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }
            ]
        });
        _medicinetypesGridFilter.remote = true;
        _medicinetypesGridFilter.autoReload = true;
        var _medicinetypesmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _medicinetypesStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewMedicineTypesdata',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [_medicinetypesGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Dosage Form',
                    dataIndex: 'medicine_type_name',
                    sortable: true,
                    tooltip: 'Dosage Form',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _medicinetypesStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display",
                plugins: [_medicinetypesGridFilter],
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMedicinetypes
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('medicine_type_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.medicine_type_id = ID;
                        Ext.getCmp('formpanelMasterMedicineTypes').hide();
                        Application.MyphaMaster.ViewMedicineTypes(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _medicinetypesStore.load();
                }
            },
            tbar: [/* <?php if (user_access("mypha_master", "saveMedicineTypes")) { ?> */{
                    text: 'Create Dosage Form',
                    tooltip: 'Create Dosage Form',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.MedicineTypesAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterMedicineTypes').getForm();
                        Ext.getCmp('panelMasterMedicineTypeParent').setTitle('Create Dosage Form Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('medicine_type_name').focus(false, 100);
                        /*<?php if (user_access("mypha_master", "saveMedicineTypes")) { ?> */
                        Ext.getCmp('buttonMasterMedicineTypeEdit').hide();
                        Ext.getCmp('buttonMasterMedicineTypeSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonMasterMedicineTypeCancel').show();
                        Ext.getCmp('formpanelMasterMedicineTypes').show();
                        Ext.getCmp('panelMasterMedicineTypesDetailsView').hide();
                        Ext.getCmp('panelMasterMedicineTypeParent').doLayout();
                        var recordSelected = Ext.getCmp('comboMasterMedicineTypesStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterMedicineTypesStatus').setValue(recordSelected.get('id'));
                    }
                }/*<?php  } ?>*/]
        });
        return _medicinetypesmaingridPanel;
    };
    var saveMedicineTypes = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterMedicineTypes').getForm().getValues()
        };
        if (Ext.getCmp('medicine_type_id').getValue() > 0) {
            var mstId = Ext.getCmp('medicine_type_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Dosage Form',
            module: 'Master',
            op: 'saveMedicineTypes',
            extrainfo: 'Dosage Form save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveMedicineTypes, form_data);
    };
    var masterPanelforMedicineType = function (id) {

        var _mpanelforMedicineType = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Dosage Form',
            id: id,
            //iconCls: 'optickets',
            items: [MedicineTypeMainGrid(), new Ext.Panel({
                    title: 'Dosage Form Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterMedicineTypeParent',
                    height: winsize.height * 0.6,
                    items: [MedicineTypeForm(), MedicineTypeMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [
                        {
                            text: "Cancel",
                            tabIndex: 304,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterMedicineTypeCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').getSelectionModel().getSelections()[0].data.medicine_type_id;
                                    Application.MyphaMaster.ViewMedicineTypes(ID);
                                }
                            }
                        },
                        /*<?php if (user_access("mypha_master", "saveMedicineTypes")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterMedicineTypeEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 305,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').getSelectionModel().getSelections()[0].data.medicine_type_id;
                                Application.MyphaMaster.EditMedicineTypesView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 303,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterMedicineTypeSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveMedicineTypes();
                            }
                        } /*<?php } ?> */

                    ]
                })
            ]
        });
        return _mpanelforMedicineType;
    };

    var CategoryForm = function () {
        var _categorysFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterCategorys',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'System',
                    id: 'category_name',
                    name: 'n[category_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 401,
                    maxLength: 250
                },
                {
                    xtype: 'textfield',
                    id: 'category_id',
                    name: 'n[category_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 402,
                    "emptyText": "Set status..",
                    "id": 'comboMasterCategorysStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('category_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterCategorysStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterCategorysStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _categorysFormPanel;
    };
    var CategoryMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterCategorysDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">System </th><td>  {category_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var CategorysMasterStore = function () {
        var _categorysMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCategorys',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'category_id',
                root: 'data'
            }, ['category_id', 'category_name', 'status']),
            sortInfo: {
                field: 'category_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewCategorysdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _categorysMasterStore;
    };
    var CategoryMainGrid = function () {
        var _categorysStore = CategorysMasterStore();
        var _categorysGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'category_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }
            ]
        });
        _categorysGridFilter.remote = true;
        _categorysGridFilter.autoReload = true;
        var _categorysmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _categorysStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewCategorysdata',
            plugins: [_categorysGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'System',
                    dataIndex: 'category_name',
                    sortable: true,
                    tooltip: 'System',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: true
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _categorysStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display",
                plugins: [_categorysGridFilter]
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedCategory
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('category_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.category_id = ID;
                        Ext.getCmp('formpanelMasterCategorys').hide();
                        Application.MyphaMaster.ViewCategorys(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _categorysStore.load();
                }
            },
            tbar: [/* <?php if (user_access("mypha_master", "saveCategorys")) { ?> */{
                    text: 'Create System',
                    tooltip: 'Create System',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.CategorysAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterCategorys').getForm();
                        Ext.getCmp('panelMasterCategoryParent').setTitle('Create System Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('category_name').focus(false, 100);
                        /*<?php if (user_access("mypha_master", "saveCategorys")) { ?> */
                        Ext.getCmp('buttonMasterCategoryEdit').hide();
                        Ext.getCmp('buttonMasterCategorySave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonMasterCategoryCancel').show();
                        Ext.getCmp('formpanelMasterCategorys').show();
                        Ext.getCmp('panelMasterCategorysDetailsView').hide();
                        Ext.getCmp('panelMasterCategoryParent').doLayout();
                        var recordSelected = Ext.getCmp('comboMasterCategorysStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterCategorysStatus').setValue(recordSelected.get('id'));
                    }
                }/*<?php } ?> */]
        });
        return _categorysmaingridPanel;
    };
    var saveCategorys = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterCategorys').getForm().getValues()
        };
        if (Ext.getCmp('category_id').getValue() > 0) {
            var mstId = Ext.getCmp('category_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save System',
            module: 'Master',
            op: 'saveCategorys',
            extrainfo: 'System save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveCategorys, form_data);
    };
    var saveWarningCategorys = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterWarningCategorys').getForm().getValues()
        };
        if (Ext.getCmp('warningCategory_id').getValue() > 0) {
            var mstId = Ext.getCmp('warningCategory_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Warning Category',
            module: 'Master',
            op: 'saveWarningCategorys',
            extrainfo: 'Warning Category save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveWarningCategorys, form_data);
    };

    var saveComposition = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterComposition').getForm().getValues()
        };
        if (Ext.getCmp('composition_id').getValue() > 0) {
            var mstId = Ext.getCmp('composition_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Single API Drug',
            module: 'Master',
            op: 'saveComposition',
            extrainfo: 'Composition  save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveComposition, form_data);
    };

    var saveSideEffect = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterSideeffect').getForm().getValues()
        };
        if (Ext.getCmp('medsideffect_id').getValue() > 0) {
            var mstId = Ext.getCmp('medsideffect_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Side Effect ',
            module: 'Master',
            op: 'saveSideEffect',
            extrainfo: 'Side Effect  save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveSideEffect, form_data);
    };


    var saveInfo = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterInformation').getForm().getValues()
        };
        if (Ext.getCmp('medadinfo_id').getValue() > 0) {
            var mstId = Ext.getCmp('medadinfo_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Information ',
            module: 'Master',
            op: 'saveInfo',
            extrainfo: ' More information save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveInfo, form_data);
    };


    var saveWork = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterWorks').getForm().getValues()
        };
        if (Ext.getCmp('medwork_id').getValue() > 0) {
            var mstId = Ext.getCmp('medwork_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Work ',
            module: 'Master',
            op: 'saveWork',
            extrainfo: ' Medicine Work  save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveWork, form_data);
    };

    var saveUnit = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterUnit').getForm().getValues()
        };
        if (Ext.getCmp('unit_id').getValue() > 0) {
            var mstId = Ext.getCmp('unit_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Unit ',
            module: 'Master',
            op: 'saveUnit',
            extrainfo: 'Unit  save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveUnit, form_data);
    };



    var saveUses = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterUse').getForm().getValues()
        };
        if (Ext.getCmp('meduse_id').getValue() > 0) {
            var mstId = Ext.getCmp('meduse_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Uses ',
            module: 'Master',
            op: 'saveUses',
            extrainfo: 'Medicine usage  save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveUses, form_data);
    };



    var saveWarning = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterWarning').getForm().getValues()
        };
        if (Ext.getCmp('warning_id').getValue() > 0) {
            var mstId = Ext.getCmp('warning_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Warning ',
            module: 'Master',
            op: 'saveWarning',
            extrainfo: 'Warning  save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveWarning, form_data);
    };
    var masterPanelforCategory = function (id) {

        var _mpanelforCategory = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'System',
            id: id,
            //iconCls: 'company',
            items: [CategoryMainGrid(), new Ext.Panel({
                    title: 'System Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterCategoryParent',
                    height: winsize.height * 0.6,
                    items: [CategoryForm(), CategoryMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 404,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterCategoryCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewCategorysdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewCategorysdata').getSelectionModel().getSelections()[0].data.category_id;
                                    Application.MyphaMaster.ViewCategorys(ID);
                                }
                            }
                        },
                        /*<?php if (user_access("mypha_master", "saveCategorys")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterCategoryEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 405,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewCategorysdata').getSelectionModel().getSelections()[0].data.category_id;
                                Application.MyphaMaster.EditCategorysView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 403,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterCategorySave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveCategorys();
                            }
                        } /*<?php } ?> */

                    ]
                })
            ]
        });
        return _mpanelforCategory;
    };
    var MedicineContentForm = function () {
        var _medicinecontentFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterMedicineContents',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Medicine Content',
                    id: 'medicineContent_name',
                    name: 'n[medicineContent_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 601,
                    maxLength: 250
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'Uses',
                    id: 'medicineContent_uses',
                    name: 'n[medicineContent_uses]',
                    anchor: '98%',
                    height: 150,
                    allowBlank: false,
                    tabIndex: 602,
                    maxLength: 500
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'How it works',
                    height: 150,
                    id: 'medicineContent_works',
                    name: 'n[medicineContent_works]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 603,
                    maxLength: 500
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'Common side effects',
                    id: 'medicineContent_side_effects',
                    height: 150,
                    name: 'n[medicineContent_side_effects]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 604,
                    maxLength: 500
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'Expert advice',
                    height: 150,
                    id: 'medicineContent_advice',
                    name: 'n[medicineContent_advice]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 605,
                    maxLength: 800
                },
                {
                    xtype: 'textfield',
                    id: 'medicineContent_id',
                    name: 'n[medicineContent_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 606,
                    "emptyText": "Set status..",
                    "id": 'comboMasterMedicineContentsStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('medicineContent_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterMedicineContentsStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterMedicineContentsStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _medicinecontentFormPanel;
    };

    var MedicineContentMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterMedicineContentsDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {medicineContent_name} </td></tr>',
                    '<tr><th width="40%">Uses </th><td>  {medicineContent_uses} </td></tr>',
                    '<tr><th width="40%">How it works </th><td>  {medicineContent_works} </td></tr>',
                    '<tr><th width="40%">Side Effects </th><td>  {medicineContent_side_effects} </td></tr>',
                    '<tr><th width="40%">Expert Advice </th><td>  {medicineContent_advice} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var DiseaseMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterDiseaseDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Disease Name </th><td>  {disease_name} </td></tr>',
                    '<tr><th width="40%">Disease Description </th><td>  {disease_description} </td></tr>',
                    // '<tr><th width="40%">Status</th><td>',
                    // '<tpl if="status == \'1\'">Active</tpl>',
                    // '<tpl if="status == \'0\'">Inactive</tpl>',
                    // '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var MedicineContentsMasterStore = function () {
        var _medicinecantentMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMedicineContents',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'medicineContent_id',
                root: 'data'
            }, ['medicineContent_id', 'medicineContent_name', 'status']),
            sortInfo: {
                field: 'medicineContent_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _medicinecantentMasterStore;
    };
    var MedicineContentMainGrid = function () {
        var _medicinecontentStore = MedicineContentsMasterStore();
        var _medicinecontentGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'medicineContent_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                }
            ]
        });
        _medicinecontentGridFilter.remote = true;
        _medicinecontentGridFilter.autoReload = true;
        var _medicinecontentmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _medicinecontentStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewMedicineContentsdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _medicinecontentGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'MedicineContent',
                    dataIndex: 'medicineContent_name',
                    sortable: true,
                    tooltip: 'MedicineContent',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _medicinecontentStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMedicineContent
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('medicineContent_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.medicineContent_id = ID;
                        Ext.getCmp('formpanelMasterMedicineContents').hide();
                        Application.MyphaMaster.ViewMedicineContents(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _medicinecontentStore.load();
                }
            },
            tbar: [/*<?php if (user_access("mypha_master", "saveMedicineContents")) { ?> */{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.MedicineContentsAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterMedicineContents').getForm();
                        Ext.getCmp('panelMasterMedicineContentParent').setTitle('Create Single API Drug Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('medicineContent_name').focus(false, 100);
                        /*<?php if (user_access("mypha_master", "saveMedicineContents")) { ?> */
                        Ext.getCmp('buttonMasterMedicineContentEdit').hide();
                        Ext.getCmp('buttonMasterMedicineContentSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonMasterMedicineContentCancel').show();
                        Ext.getCmp('formpanelMasterMedicineContents').show();
                        Ext.getCmp('panelMasterMedicineContentsDetailsView').hide();
                        Ext.getCmp('panelMasterMedicineContentParent').doLayout();
                    }
                }/*<?php } ?> */]
        });
        return _medicinecontentmaingridPanel;
    };
    var saveMedicineContents = function () {
        var form_data = {
            form: Ext.getCmp('formpanelMasterMedicineContents').getForm().getValues()
        };
        if (Ext.getCmp('medicineContent_id').getValue() > 0) {
            var mstId = Ext.getCmp('medicineContent_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Medicine Contents',
            module: 'Master',
            op: 'saveMedicineContents',
            extrainfo: 'Medicine Contents save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.saveMedicineContents, form_data);
    };
    var masterPanelforMedicineContent = function (id) {
        var _mpanelforMedicineContent = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Medicine Content',
            id: id,
            //iconCls: 'company',
            items: [MedicineContentMainGrid(), new Ext.Panel({
                    title: 'Single API Drug Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterMedicineContentParent',
                    height: winsize.height * 0.6,
                    items: [MedicineContentForm(), MedicineContentMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 608,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterMedicineContentCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').getSelectionModel().getSelections()[0].data.medicineContent_id;
                                    Application.MyphaMaster.ViewMedicineContents(ID);
                                }
                            }
                        },
                        /*<?php if (user_access("mypha_master", "saveMedicineContents")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterMedicineContentEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 609,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').getSelectionModel().getSelections()[0].data.medicineContent_id;
                                Application.MyphaMaster.EditMedicineContentsView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 607,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterMedicineContentSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveMedicineContents();
                            }
                        } /*<?php } ?> */

                    ]
                })
            ]
        });
        return _mpanelforMedicineContent;
    };
    /*Warning Category Master */
    var WarningCategoryMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterWarningCategorysDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>{warningCategory_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var WarningMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterWarningDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Warning Name </th><td>{warning_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var UseMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterUseDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Use </th><td>{meduse_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="meduse_status == \'1\'">Active</tpl>',
                    '<tpl if="meduse_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var UnitMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterUnitDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Unit </th><td>{unit_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };



    var SideEffectMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterSideeffectDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Side Effect </th><td>{medsideffect_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="medsideffect_status == \'1\'">Active</tpl>',
                    '<tpl if="medsideffect_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var WorksMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterWorksDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Medicine Work Name </th><td>{medwork_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="medwork_status == \'1\'">Active</tpl>',
                    '<tpl if="medwork_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var InformationMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterInformationDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Storage  </th><td>{medadinfo_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="medadinfo_status == \'1\'">Active</tpl>',
                    '<tpl if="medadinfo_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var CompositionMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterCompositionDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Single API Drug </th><td>{composition_name} </td></tr>',
                    '<tr><th width="40%">Drug Group </th><td>{subCategory} </td></tr>',
                    '<tr><th width="40%">Contraindications </th><td>  {contraindications} </td></tr>',
                    '<tr><th width="40%">Special Precautions </th><td>  {special_precautions} </td></tr>',
                    '<tr><th width="40%">Interactions </th><td>  {interactions} </td></tr>',
                    '<tr><th width="40%">Adverse drug reactions </th><td>  {adverse_drug_reactions} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="composition_status == \'1\'">Active</tpl>',
                    '<tpl if="composition_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };



    var WarningCategorysMasterStore = function () {
        var _categorysMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listwarningCategorys',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'warningCategory_id',
                root: 'data'
            }, ['warningCategory_id', 'warningCategory_name', 'status']),
            sortInfo: {
                field: 'warningCategory_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _categorysMasterStore;
    };
    var WarningCategoryForm = function () {
        var _categorysFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterWarningCategorys',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Warning Category',
                    id: 'warningCategory_name',
                    name: 'n[warningCategory_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 651,
                    maxLength: 250
                },
                {
                    xtype: 'textfield',
                    id: 'warningCategory_id',
                    name: 'n[warningCategory_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[warningCategory_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 652,
                    "emptyText": "Set status..",
                    "id": 'comboMasterWarningCategorysStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('warningCategory_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterWarningCategorysStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterWarningCategorysStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _categorysFormPanel;
    };

    var WarningForm = function () {
        var _categorysFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterWarning',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Warning Name',
                    id: 'warning_name',
                    height: 150,
                    name: 'n[warning_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 701,
                    maxLength: 2000
                },
                {
                    xtype: 'textfield',
                    id: 'warning_id',
                    name: 'n[warning_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[warning_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 702,
                    "emptyText": "Set status..",
                    "id": 'comboMasterWarningStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('warning_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterWarningStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterWarningStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _categorysFormPanel;
    };

    var UnitForm = function () {
        var _unitFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterUnit',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Unit Name',
                    id: 'unit_name',
                    name: 'n[unit_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 551,
                    maxLength: 250
                },
                {
                    xtype: 'textfield',
                    id: 'unit_id',
                    name: 'n[unit_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 552,
                    "emptyText": "Set status..",
                    "id": 'comboMasterUnitStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('unit_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterUnitStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterUnitStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _unitFormPanel;
    };


    var UseForm = function () {
        var _useFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterUse',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Use Name',
                    id: 'meduse_name',
                    name: 'n[meduse_name]',
                    anchor: '98%',
                    height: 150,
                    allowBlank: false,
                    tabIndex: 521,
                    maxLength: 2000
                },
                {
                    xtype: 'textfield',
                    id: 'meduse_id',
                    name: 'n[meduse_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[meduse_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 522,
                    "emptyText": "Set status..",
                    "id": 'comboMasterUseStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('meduse_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterUseStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterUseStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _useFormPanel;
    };

    var InformationForm = function () {
        var _informationFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterInformation',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Storage',
                    id: 'medadinfo_name',
                    height: 150,
                    name: 'n[medadinfo_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 901,
                    maxLength: 2000
                },
                {
                    xtype: 'textfield',
                    id: 'medadinfo_id',
                    name: 'n[medadinfo_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[medadinfo_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 902,
                    "emptyText": "Set status..",
                    "id": 'comboMasterInformationStatus',
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('medadinfo_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterInformationStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterInformationStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _informationFormPanel;
    };


    var WorkForm = function () {
        var _workFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterWorks',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Medicine work Name',
                    id: 'medwork_name',
                    name: 'n[medwork_name]',
                    anchor: '98%',
                    height: 150,
                    allowBlank: false,
                    tabIndex: 101,
                    maxLength: 2000
                },
                {
                    xtype: 'textfield',
                    id: 'medwork_id',
                    name: 'n[medwork_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[medwork_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 102,
                    "emptyText": "Set status..",
                    "id": 'comboMasterWorkStatus',
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('medwork_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterWorkStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterWorkStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _workFormPanel;
    };




    var SideeffectForm = function () {
        var _sideEffectFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterSideeffect',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Side Effect Name',
                    id: 'medsideffect_name',
                    height: 150,
                    name: 'n[medsideffect_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 1,
                    maxLength: 2000
                },
                {
                    xtype: 'textfield',
                    id: 'medsideffect_id',
                    name: 'n[medsideffect_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[medsideffect_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 2,
                    "emptyText": "Set status..",
                    "id": 'comboMasterSideEffectStatus',
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('medsideffect_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterSideEffectStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterSideEffectStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _sideEffectFormPanel;
    };

    var fnmedSubCategoryStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medSubCategory',
            method: 'post',
            fields: ['subCategory_id', 'subCategory_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return natureOfGroupStore;
    };


    var CompositionForm = function () {
        var medsubCategoryStore = fnmedSubCategoryStore();
        var _compositionFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterComposition',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Single API Drug',
                    id: 'composition_name',
                    name: 'n[composition_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 801,
                    maxLength: 250
                }, {
                    hiddenName: 'n[subCategory_id]',
                    xtype: 'combo',
                    name: 'category_name',
                    fieldLabel: 'Drug Group',
                    id: 'sub_category',
                    anchor: '98%',
                    displayField: 'subCategory_name',
                    valueField: 'subCategory_id',
                    store: medsubCategoryStore,
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 1,
                    tabIndex: 802
                },
                {
                    xtype: 'textfield',
                    id: 'composition_id',
                    name: 'n[composition_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[composition_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 803,
                    "emptyText": "Set status..",
                    "id": 'comboMasterCompositionStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('composition_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterCompositionStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterCompositionStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _compositionFormPanel;
    };



    var WarningCategoryMainGrid = function () {
        var _warningcategorysStore = WarningCategorysMasterStore();
        var _warningcategorysGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'warningCategory_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                }
            ]
        });
        _warningcategorysGridFilter.remote = true;
        _warningcategorysGridFilter.autoReload = true;
        var _warningcategorysmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _warningcategorysStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewWarningCategorysdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _warningcategorysGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Warning Category',
                    dataIndex: 'warningCategory_name',
                    sortable: true,
                    tooltip: 'Warning Category',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _warningcategorysStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedWarningCategory
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('warningCategory_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.warningcategory_id = ID;
                        Ext.getCmp('formpanelMasterWarningCategorys').hide();
                        Application.MyphaMaster.ViewWarningCategorys(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _warningcategorysStore.load();
                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.warningCategorysAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterWarningCategorys').getForm();
                        Ext.getCmp('panelMasterWarningCategoryParent').setTitle('Add Warning Category Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('warningCategory_name').focus(false, 100);
                        Ext.getCmp('buttonMasterwarningCategoryEdit').hide();
                        Ext.getCmp('buttonMasterwarningCategorySave').show();
                        Ext.getCmp('buttonMasterwarningCategoryCancel').show();
                        Ext.getCmp('formpanelMasterWarningCategorys').show();
                        Ext.getCmp('panelMasterWarningCategorysDetailsView').hide();
                        Ext.getCmp('panelMasterWarningCategoryParent').doLayout();
                        var recordSelected = Ext.getCmp('comboMasterCategorysStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterCategorysStatus').setValue(recordSelected.get('id'));

                    }
                }]
        });
        return _warningcategorysmaingridPanel;
    };

    var WarningMasterStore = function () {
        var _categorysMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listwarnings',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'warning_id',
                root: 'data'
            }, ['warning_id', 'warning_name', 'status']),
            sortInfo: {
                field: 'warning_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewWarningdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _categorysMasterStore;
    };


    var CompositionMasterStore = function () {
        var _categorysMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listComposition',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'composition_id',
                root: 'data'
            }, ['composition_id', 'composition_name', 'composition_status', 'subCategory_name', 'contraindications', 'special_precautions', 'interactions', 'adverse_drug_reactions',
                'adrstatus', 'isstatus', 'spstatus', 'csstatus']),
            sortInfo: {
                field: 'composition_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _categorysMasterStore;
    };
    var UseMasterStore = function () {
        var _usesMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listUses',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'meduse_id',
                root: 'data'
            }, ['meduse_id', 'meduse_name', 'meduse_status']),
            sortInfo: {
                field: 'meduse_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewUsedata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _usesMasterStore;
    };

    var UnitMasterStore = function () {
        var _unitMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listUnit',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'unit_id',
                root: 'data'
            }, ['unit_id', 'unit_name', 'status']),
            sortInfo: {
                field: 'unit_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewUnitdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _unitMasterStore;
    };




    var WorkMasterStore = function () {
        var _workMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listWorks',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'medwork_id',
                root: 'data'
            }, ['medwork_id', 'medwork_name', 'medwork_status']),
            sortInfo: {
                field: 'medwork_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewWorksdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _workMasterStore;
    };

    var InfoMasterStore = function () {
        var _infoMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listInfo',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'medadinfo_id',
                root: 'data'
            }, ['medadinfo_id', 'medadinfo_name', 'medadinfo_status']),
            sortInfo: {
                field: 'medadinfo_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewInformationdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _infoMasterStore;
    };

    var SideeffectMasterStore = function () {
        var _sideeffectMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listSideeffect',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'medsideffect_id',
                root: 'data'
            }, ['medsideffect_id', 'medsideffect_name', 'medsideffect_status']),
            sortInfo: {
                field: 'medsideffect_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewSideeffectdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _sideeffectMasterStore;
    };

    var WarningMainGrid = function () {
        var _warningcategorysStore = WarningMasterStore();
        var _warningcategorysGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'warning_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                }
            ]
        });
        _warningcategorysGridFilter.remote = true;
        _warningcategorysGridFilter.autoReload = true;
        var _warningcategorysmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _warningcategorysStore,
            iconCls: 'money',
            id: 'gridpanelMasterDataviewWarningdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _warningcategorysGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Warning Name',
                    dataIndex: 'warning_name',
                    sortable: true,
                    tooltip: 'Warning Name',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _warningcategorysStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedWarning
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('warning_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.warningcategory_id = ID;
                        Ext.getCmp('formpanelMasterWarning').hide();
                        Application.MyphaMaster.ViewWarning(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _warningcategorysStore.load();
                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.warningCategorysAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterWarning').getForm();
                        Ext.getCmp('panelMasterWarningParent').setTitle('Add Warning Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('warning_name').focus(false, 100);
                        Ext.getCmp('buttonMasterwarningEdit').hide();
                        Ext.getCmp('buttonMasterwarningSave').show();
                        Ext.getCmp('buttonMasterwarningCancel').show();
                        Ext.getCmp('formpanelMasterWarning').show();
                        Ext.getCmp('panelMasterWarningDetailsView').hide();
                        Ext.getCmp('panelMasterWarningParent').doLayout();
                        var recordSelected = Ext.getCmp("comboMasterWarningStatus").getStore().getAt(0);
                        Ext.getCmp("comboMasterWarningStatus").setValue(recordSelected.get("id"));
                    }
                }]
        });
        return _warningcategorysmaingridPanel;
    };
    var CompositionMainGrid = function () {
        var _compositionStore = CompositionMasterStore();
        var _compositionGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'composition_name'
                },
                {
                    type: 'string',
                    dataIndex: 'composition_status'
                },
                {
                    type: 'string',
                    dataIndex: 'subCategory_name'
                }, {
                    type: 'string',
                    dataIndex: 'adrstatus'
                },
                {
                    type: 'string',
                    dataIndex: 'isstatus'
                }, {
                    type: 'string',
                    dataIndex: 'spstatus'
                }, {
                    type: 'string',
                    dataIndex: 'csstatus'
                }
            ]
        });
        _compositionGridFilter.remote = true;
        _compositionGridFilter.autoReload = true;
        var _compositionmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _compositionStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewCompositiondata',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [_compositionGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Single API Drug',
                    dataIndex: 'composition_name',
                    sortable: true,
                    tooltip: 'Single API Drug',
                    hideable: true
                }, {
                    header: 'Drug Group',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'subCategory_name',
                    tooltip: 'Drug Group'
                }, {
                    header: 'Contraindications',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'csstatus',
                    tooltip: 'Contraindications'
                }, {
                    header: 'Special Precautions',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'spstatus',
                    tooltip: 'Special Precautions'
                }, {
                    header: 'Interactions',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'isstatus',
                    tooltip: 'Interactions'
                }, {
                    header: 'Adverse drug reactions',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'adrstatus',
                    tooltip: 'Adverse drug reactions'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'composition_status',
                    tooltip: 'Status'
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
                            sapiActionMenu.showAt(e.getXY());
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
                            iconCls: 'my-icon96',
                            tooltip: 'Add Details',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var ID = record.get('composition_id');
                                if (!Ext.isEmpty(ID)) {
                                    Application.MyphaComposition.myphSAPIContents(ID, 'single');
                                }
                            }
                        },
                        {
                            iconCls: 'icon-add-table',
                            tooltip: 'Add Strength',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var ID = record.get('composition_id');
                                if (!Ext.isEmpty(ID)) {
                                    Application.MyphaComposition.myphaAPIStrength(ID, 'single');
                                }
                            }
                        }
                    ]
                }*/
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _compositionStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display",
                plugins: [_compositionGridFilter],
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedComposition
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('composition_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.composition_id = ID;
                        Ext.getCmp('formpanelMasterComposition').hide();
                        Application.MyphaMaster.ViewComposition(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _compositionStore.load();
                }
            },
            tbar: [{
                    text: 'Create Single API',
                    tooltip: 'Create Single API',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.compositionAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterComposition').getForm();
                        Ext.getCmp('panelMasterCompositionParent').setTitle('Create Single API Drug Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('composition_name').focus(false, 100);
                        Ext.getCmp('buttonMastercompositionEdit').hide();
                        Ext.getCmp('buttonMastercompositionSave').show();
                        Ext.getCmp('buttonMastercompositionCancel').show();
                        Ext.getCmp('formpanelMasterComposition').show();
                        Ext.getCmp('panelMasterCompositionDetailsView').hide();
                        Ext.getCmp('panelMasterCompositionParent').doLayout();
                        var recordSelected = Ext.getCmp("comboMasterCompositionStatus").getStore().getAt(0);
                        Ext.getCmp("comboMasterCompositionStatus").setValue(recordSelected.get("id"));
                    }
                }]
        });
        return _compositionmaingridPanel;
    };
    
    var sapiActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Add",
                handler: function () {
                    var composition_id = Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().getSelections()[0].data.composition_id;
                    if (!Ext.isEmpty(composition_id)) {
                                    Application.MyphaComposition.myphSAPIContents(composition_id, 'single');
                                }
                }
            }, {
                text: "API Strength",
                handler: function () {
                    var composition_id = Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().getSelections()[0].data.composition_id;
                   if (!Ext.isEmpty(composition_id)) {
                                    Application.MyphaComposition.myphaAPIStrength(composition_id, 'single');
                                }
                }
            }]
    }); 

    var InformationMainGrid = function () {
        var _informationStore = InfoMasterStore();
        var _informationGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'medadinfo_name'
                },
                {
                    type: 'string',
                    dataIndex: 'medadinfo_status'
                }
            ]
        });
        _informationGridFilter.remote = true;
        _informationGridFilter.autoReload = true;
        var _informationmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _informationStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewInformationdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _informationGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Storage',
                    dataIndex: 'medadinfo_name',
                    sortable: true,
                    tooltip: 'More Information',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'medadinfo_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _informationStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedInfo
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('medwork_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.medwork_id = ID;
                        Ext.getCmp('formpanelMasterInformation').hide();
                        Application.MyphaMaster.ViewWork(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _informationStore.load();
                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.infoAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterInformation').getForm();
                        Ext.getCmp('panelMasterInformationParent').setTitle('Add More Information');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('medadinfo_name').focus(false, 100);
                        Ext.getCmp('buttonMasterinformationEdit').hide();
                        Ext.getCmp('buttonMasterinformationSave').show();
                        Ext.getCmp('buttonMasterinformationCancel').show();
                        Ext.getCmp('formpanelMasterInformation').show();
                        Ext.getCmp('panelMasterInformationDetailsView').hide();
                        Ext.getCmp('panelMasterInformationParent').doLayout();
                        var recordSelected = Ext.getCmp("comboMasterInformationStatus").getStore().getAt(0);
                        Ext.getCmp("comboMasterInformationStatus").setValue(recordSelected.get("id"));
                    }
                }]
        });
        return _informationmaingridPanel;
    };



    var WorksMainGrid = function () {
        var _worksStore = WorkMasterStore();
        var _worksGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'medwork_name'
                },
                {
                    type: 'string',
                    dataIndex: 'medsideffect_status'
                }
            ]
        });
        _worksGridFilter.remote = true;
        _worksGridFilter.autoReload = true;
        var _workmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _worksStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewWorksdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _worksGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Medicine Work',
                    dataIndex: 'medwork_name',
                    sortable: true,
                    tooltip: 'Warning Name',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'medwork_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _worksStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedWork
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('medwork_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.medwork_id = ID;
                        Ext.getCmp('formpanelMasterWorks').hide();
                        Application.MyphaMaster.ViewWork(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _worksStore.load();
                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.workAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterWorks').getForm();
                        Ext.getCmp('panelMasterWorksParent').setTitle('Add Medicine Working Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('medwork_name').focus(false, 100);
                        Ext.getCmp('buttonMasterworksEdit').hide();
                        Ext.getCmp('buttonMasterworksSave').show();
                        Ext.getCmp('buttonMasterworksCancel').show();
                        Ext.getCmp('formpanelMasterWorks').show();
                        Ext.getCmp('panelMasterWorksDetailsView').hide();
                        Ext.getCmp('panelMasterWorksParent').doLayout();
                        var recordSelected = Ext.getCmp("comboMasterWorkStatus").getStore().getAt(0);
                        Ext.getCmp("comboMasterWorkStatus").setValue(recordSelected.get("id"));
                    }
                }]
        });
        return _workmaingridPanel;
    };


    var SideEffectMainGrid = function () {
        var _sideeffectStore = SideeffectMasterStore();
        var _sideEffectGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'medsideffect_name'
                },
                {
                    type: 'string',
                    dataIndex: 'medsideffect_status'
                }
            ]
        });
        _sideEffectGridFilter.remote = true;
        _sideEffectGridFilter.autoReload = true;
        var _sideeffectmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _sideeffectStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewSideeffectdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _sideEffectGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Side Effect',
                    dataIndex: 'medsideffect_name',
                    sortable: true,
                    tooltip: 'Warning Name',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'medsideffect_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _sideeffectStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedSideeffect
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('medsideffect_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.medsideffect_id = ID;
                        Ext.getCmp('formpanelMasterSideeffect').hide();
                        Application.MyphaMaster.ViewUse(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _sideeffectStore.load();
                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.sideeffectAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterSideeffect').getForm();
                        Ext.getCmp('panelMasterSideeffectParent').setTitle('Add Medicine Side Effects Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('medsideffect_name').focus(false, 100);
                        Ext.getCmp('buttonMastersideeffectEdit').hide();
                        Ext.getCmp('buttonMastersideeffectSave').show();
                        Ext.getCmp('buttonMastersideeffectCancel').show();
                        Ext.getCmp('formpanelMasterSideeffect').show();
                        Ext.getCmp('panelMasterSideeffectDetailsView').hide();
                        Ext.getCmp('panelMasterSideeffectParent').doLayout();
                        var recordSelected = Ext.getCmp("comboMasterSideEffectStatus").getStore().getAt(0);
                        Ext.getCmp("comboMasterSideEffectStatus").setValue(recordSelected.get("id"));
                    }
                }]
        });
        return _sideeffectmaingridPanel;
    };


    var UnitMainGrid = function () {
        var _unitStore = UnitMasterStore();
        var _unitGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'unit_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                }
            ]
        });
        _unitGridFilter.remote = true;
        _unitGridFilter.autoReload = true;
        var _unitmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _unitStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewUnitdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _unitGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Unit Name',
                    dataIndex: 'unit_name',
                    sortable: true,
                    tooltip: 'Unit Name',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _unitStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedUnit
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('unit_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.unit_id = ID;
                        Ext.getCmp('formpanelMasterUnit').hide();
                        Application.MyphaMaster.ViewUnit(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _unitStore.load();
                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    hidden: true,
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.unitAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterUnit').getForm();
                        Ext.getCmp('panelMasterUnitParent').setTitle('Create Unit Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('unit_name').focus(false, 100);
                        Ext.getCmp('buttonMasterunitEdit').hide();
                        Ext.getCmp('buttonMasterunitSave').show();
                        Ext.getCmp('buttonMasterunitCancel').show();
                        Ext.getCmp('formpanelMasterUnit').show();
                        Ext.getCmp('panelMasterUnitDetailsView').hide();
                        Ext.getCmp('panelMasterUnitParent').doLayout();
                        Ext.getCmp('gridUnitValueList').hide();
                        var recordSelected = Ext.getCmp("comboMasterUnitStatus").getStore().getAt(0);
                        Ext.getCmp("comboMasterUnitStatus").setValue(recordSelected.get("id"));

                    }
                }]
        });
        return _unitmaingridPanel;
    };




    var UseMainGrid = function () {
        var _useStore = UseMasterStore();
        var _useGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'meduse_name'
                },
                {
                    type: 'string',
                    dataIndex: 'meduse_status'
                }
            ]
        });
        _useGridFilter.remote = true;
        _useGridFilter.autoReload = true;
        var _usemaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _useStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewUsedata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _useGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Medicine Usage',
                    dataIndex: 'meduse_name',
                    sortable: true,
                    tooltip: 'Warning Name',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'meduse_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _useStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedUse
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('meduse_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.meduse_id = ID;
                        Ext.getCmp('formpanelMasterUse').hide();
                        Application.MyphaMaster.ViewUse(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _useStore.load();
                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.useAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('formpanelMasterUse').getForm();
                        Ext.getCmp('panelMasterUseParent').setTitle('Add Medicine Usage Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('meduse_name').focus(false, 100);
                        Ext.getCmp('buttonMasteruseEdit').hide();
                        Ext.getCmp('buttonMasteruseSave').show();
                        Ext.getCmp('buttonMasteruseCancel').show();
                        Ext.getCmp('formpanelMasterUse').show();
                        Ext.getCmp('panelMasterUseDetailsView').hide();
                        Ext.getCmp('panelMasterUseParent').doLayout();
                        var recordSelected = Ext.getCmp("comboMasterUseStatus").getStore().getAt(0);
                        Ext.getCmp("comboMasterUseStatus").setValue(recordSelected.get("id"));

                    }
                }]
        });
        return _usemaingridPanel;
    };

    var masterPanelforWarning = function (id) {

        var _mpanelforWarningCategory = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Warning',
            id: id,
            //iconCls: 'company',
            items: [WarningMainGrid(), new Ext.Panel({
                    title: 'Warning Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterWarningParent',
                    height: winsize.height * 0.6,
                    items: [WarningForm(), WarningMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 704,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterwarningCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewWarningdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewWarningdata').getSelectionModel().getSelections()[0].data.warning_id;
                                    Application.MyphaMaster.ViewWarning(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterwarningEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 705,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewWarningdata').getSelectionModel().getSelections()[0].data.warning_id;
                                Application.MyphaMaster.EditWarningView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 703,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterwarningSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveWarning();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforWarningCategory;
    };

    var masterPanelforComposition = function (id) {

        var _mpanelforComposition = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Single API',
            id: id,
            //iconCls: 'company',
            items: [CompositionMainGrid(), new Ext.Panel({
                    title: 'Single API Drug Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterCompositionParent',
                    height: winsize.height * 0.6,
                    items: [CompositionForm(), CompositionMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 805,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMastercompositionCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().getSelections()[0].data.composition_id;
                                    Application.MyphaMaster.ViewComposition(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMastercompositionEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 806,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewCompositiondata').getSelectionModel().getSelections()[0].data.composition_id;
                                Application.MyphaMaster.EditCompositionView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 804,
                            cls: 'left-right-buttons',
                            id: 'buttonMastercompositionSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveComposition();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforComposition;
    };

    var masterPanelforInformation = function (id) {

        var _mpanelforWorks = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'More Information',
            id: id,
            //iconCls: 'company',
            items: [InformationMainGrid(), new Ext.Panel({
                    title: 'More Information',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterInformationParent',
                    height: winsize.height * 0.6,
                    items: [InformationForm(), InformationMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 904,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterinformationCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewInformationdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewInformationdata').getSelectionModel().getSelections()[0].data.medadinfo_id;
                                    Application.MyphaMaster.ViewInfo(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterinformationEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 905,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewInformationdata').getSelectionModel().getSelections()[0].data.medadinfo_id;
                                Application.MyphaMaster.EditInfoView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 903,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterinformationSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveInfo();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforWorks;
    };




    var masterPanelforWorks = function (id) {

        var _mpanelforWorks = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Medicine Works',
            id: id,
            //iconCls: 'company',
            items: [WorksMainGrid(), new Ext.Panel({
                    title: 'Medicine working Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterWorksParent',
                    height: winsize.height * 0.6,
                    items: [WorkForm(), WorksMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 104,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterworksCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewWorksdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewWorksdata').getSelectionModel().getSelections()[0].data.medwork_id;
                                    Application.MyphaMaster.ViewWork(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterworksEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 105,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewWorksdata').getSelectionModel().getSelections()[0].data.medwork_id;
                                Application.MyphaMaster.EditWorkView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 103,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterworksSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveWork();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforWorks;
    };


    var masterPanelforSideeffect = function (id) {

        var _mpanelforSideEffect = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Side Effects',
            id: id,
            //iconCls: 'company',
            items: [SideEffectMainGrid(), new Ext.Panel({
                    title: 'Side effect Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterSideeffectParent',
                    height: winsize.height * 0.6,
                    items: [SideeffectForm(), SideEffectMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 5,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMastersideeffectCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewSideeffectdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewSideeffectdata').getSelectionModel().getSelections()[0].data.medsideffect_id;
                                    Application.MyphaMaster.ViewSideEffect(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMastersideeffectEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 4,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewSideeffectdata').getSelectionModel().getSelections()[0].data.medsideffect_id;
                                Application.MyphaMaster.EditSideEffectView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 3,
                            cls: 'left-right-buttons',
                            id: 'buttonMastersideeffectSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveSideEffect();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforSideEffect;
    };


    var masterPanelforUse = function (id) {

        var _mpanelforUnit = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Medicine Usage',
            id: id,
            //iconCls: 'company',
            items: [UseMainGrid(), new Ext.Panel({
                    title: 'Uses Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterUseParent',
                    height: winsize.height * 0.6,
                    items: [UseForm(), UseMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 524,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasteruseCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewUsedata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewUsedata').getSelectionModel().getSelections()[0].data.meduse_id;
                                    Application.MyphaMaster.ViewUse(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasteruseEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 525,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewUsedata').getSelectionModel().getSelections()[0].data.meduse_id;
                                Application.MyphaMaster.EditUseView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 523,
                            cls: 'left-right-buttons',
                            id: 'buttonMasteruseSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveUses();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforUnit;
    };



    var unitValue = function(){
        var unitValueGridStore = new Ext.data.JsonStore({
          autoLoad: false,
          url: modURL + "&op=listUnitValues",
          method: "post",
          fields: ["id", "value"],
          remoteSort: true,
        });
        
        var __uvGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "value",
            },
          ],
        });
        __uvGridFilter.remote = true;
        __uvGridFilter.autoReload = true;
        var _unitValueGrid = new Ext.grid.GridPanel({
          id: "gridUnitValueList",
          region: "north",
          height: 200,
          width: winsize.width * 0.38,
          frame: true,
          hidden:true,
          border: false,
          autoScroll: true,
          store: unitValueGridStore,
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true
        }),
          viewConfig: {
            forceFit: true,
          },
          plugins: [__uvGridFilter],
          fields: ["id", "value"],
          colModel: new Ext.grid.ColumnModel({
            columns: [
              {
                header: "Value",
                dataIndex: "value",
              },
            ],
          }),
          iconCls: "icon-grid",
          tbar:[{ html: "Value : &nbsp;"},
            {
                xtype: "textfield",
                fieldLabel: "Value",
                id: "unitValue",
                name: "unitValue",
                allowBlank: false,
                anchor: "95%",
              },{
            xtype: 'button',
            hidden:true,
            text: 'Add',
            iconCls: 'add',
            handler: function () {
                
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      //var prescriptionForm = Ext.getCmp('rrpFormPanel').getForm();

      var unitValue = Ext.getCmp("unitValue").getValue();
      var unitId = Ext.getCmp('gridpanelMasterDataviewUnitdata').getSelectionModel().getSelections()[0].data.unit_id;

      if (unitId > 0 && !Ext.isEmpty(unitValue)) {
          Ext.Ajax.request({
            url: modURL + "&op=saveUnitValue",
            params: {
                unitId: unitId,
                unitValue: unitValue
            },
            failure: function (response) {
              Ext.MessageBox.alert("Error", response.responseText);
            },
            success: function (response, options) {
              var tmp = Ext.decode(response.responseText);
              console.log("tmp", tmp);
              if (tmp.success === true && tmp.valid === true) {
                Ext.getCmp('gridUnitValueList').getStore().load({
                    params: {
                        unitId: unitId,
                    }
                });
                Ext.getCmp("unitValue").reset();
              } else if (tmp.success === false && tmp.valid === false) {
                Ext.Msg.alert("Error", tmp.message);
                Ext.getCmp("unitValue").reset();
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
          });
        
      } else {
        Ext.Msg.alert("Notification", "Please enter required fields");
      }
            }
        }],
          listeners: {
            afterrender: function () {
              
            },
          },
        });
        return _unitValueGrid;
      };

    var masterPanelforUnit = function (id) {

        var _mpanelforUnit = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Unit',
            id: id,
            //iconCls: 'company',
            items: [UnitMainGrid(), new Ext.Panel({
                    title: 'Unit Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterUnitParent',
                    height: winsize.height * 0.6,
                    items: [UnitForm(), UnitMasterDetailsView(),unitValue()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 554,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterunitCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewUnitdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewUnitdata').getSelectionModel().getSelections()[0].data.unit_id;
                                    Application.MyphaMaster.ViewUnit(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterunitEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 555,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewUnitdata').getSelectionModel().getSelections()[0].data.unit_id;
                                Application.MyphaMaster.EditUnitView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 553,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterunitSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveUnit();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforUnit;
    };












    var masterPanelforWarningCategory = function (id) {

        var _mpanelforWarningCategory = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Warning Category',
            id: id,
            //iconCls: 'company',
            items: [WarningCategoryMainGrid(), new Ext.Panel({
                    title: 'Warning Category Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterWarningCategoryParent',
                    height: winsize.height * 0.6,
                    items: [WarningCategoryForm(), WarningCategoryMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 654,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterwarningCategoryCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').getSelectionModel().getSelections()[0].data.warningCategory_id;
                                    Application.MyphaMaster.ViewWarningCategorys(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterwarningCategoryEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 655,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').getSelectionModel().getSelections()[0].data.warningCategory_id;
                                Application.MyphaMaster.EditWarningCategorysView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 653,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterwarningCategorySave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveWarningCategorys();
                            }
                        }

                    ]
                })
            ]
        });
        return _mpanelforWarningCategory;
    };
    /*Warning Category End*/
    var masterPanelforsubCategory = function (id) {
        var SubcategoryPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Drug Group',
            id: id,
            //iconCls: 'company',
            items: [
                SubcategoryGrid(),
                new Ext.Panel({
                    title: 'Drug Group Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterSubcategoryParent',
                    height: winsize.height * 0.6,
                    items: [SubcategoryForm(), SubcategoryMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [
                        {
                            text: "Cancel",
                            tabIndex: 905,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterSubcategoryCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterSubcategoryDataview').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterSubcategoryDataview').getSelectionModel().getSelections()[0].data.subCategory_id;
                                    Application.MyphaMaster.ViewSubcategory(ID);
                                }
                            }
                        },
                        /* <?php if (user_access("mypha_master", "saveSubCategory")) { ?> */
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterSubcategoryEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 906,
                            handler: function () {


                                var ID = Ext.getCmp('gridpanelMasterSubcategoryDataview').getSelectionModel().getSelections()[0].data.subCategory_id;
                                Application.MyphaMaster.EditSubcategoryView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 904,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterSubcategorySave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                SubcategorySave();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return SubcategoryPanel;
    };
    var SubcategoryStore = function () {

        var _categorysMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listSubCategorys',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'subCategory_id',
                root: 'data'
            }, ['subCategory_id', 'subCategory_name', 'category_name', 'status']),
            sortInfo: {
                field: 'subCategory_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterSubcategoryDataview').getSelectionModel().selectRow(0);
                }
            }
        });
        return _categorysMasterStore;
    };
    var SubcategoryGrid = function () {

        var Subcategory_store = SubcategoryStore();
        var Subcategory_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'subCategory_name'
                },
                {
                    type: 'string',
                    dataIndex: 'category_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }

            ]
        });
        Subcategory_filter.remote = true;
        Subcategory_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: Subcategory_store,
            //iconCls: 'optickets',
            plugins: [Subcategory_filter],
            id: 'gridpanelMasterSubcategoryDataview',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Drug Group',
                    id: 'Subcategory_filter_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'subCategory_name',
                    tooltip: 'Drug Group'
                },
                {
                    header: 'System',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'category_name',
                    tooltip: 'System'
                }, {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'status',
                    tooltip: 'Status'
                },
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedSubcategory
                }

            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('subCategory_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.subCategory_id = ID;
                        Ext.getCmp('SubcategorySaveForm').hide();
                        Application.MyphaMaster.ViewSubcategory(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    Subcategory_store.load();
                }
            },
            tbar: [/* <?php if (user_access("mypha_master", "saveSubCategory")) { ?> */{
                    xtype: 'button',
                    text: 'Create Drug Group',
                    tooltip: 'Create Drug Group',
                    iconCls: 'add',
                    handler: function () {
                        Application.MyphaMaster.SubcategoryAddEdit = 'Add';
                        var packagetypesForm = Ext.getCmp('SubcategorySaveForm').getForm();
                        Ext.getCmp('panelMasterSubcategoryParent').setTitle('Create Drug Group Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('subCategory_name').focus(false, 100);
                        Ext.getCmp('buttonMasterSubcategoryEdit').hide();
                        Ext.getCmp('buttonMasterSubcategorySave').show();
                        Ext.getCmp('buttonMasterSubcategoryCancel').show();
                        Ext.getCmp('SubcategorySaveForm').show();
                        Ext.getCmp('panelMasterSubcategoryDetailsView').hide();
                        Ext.getCmp('panelMasterSubcategoryParent').doLayout();
                        var recordSelected = Ext.getCmp('comboMasterSubcategoryStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterSubcategoryStatus').setValue(recordSelected.get('id'));


                    }

                }/*<?php  } ?>*/],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: Subcategory_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [Subcategory_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'Subcategory_filter_name_auto_exp'
        });
        return grid_panel;
    };
    var fnmedCategoryStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medCategory',
            method: 'post',
            fields: ['category_id', 'category_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return natureOfGroupStore;
    };
    var SubcategoryForm = function () {
        var medCategoryStore = fnmedCategoryStore();
        var _SubcategoryFormPanel = new Ext.form.FormPanel({
            id: 'SubcategorySaveForm',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Drug Group',
                    id: 'subCategory_name',
                    name: 'n[subCategory_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 901,
                    maxLength: 250
                }, {
                    hiddenName: 'n[category_id]',
                    xtype: 'combo',
                    name: 'category_name',
                    fieldLabel: 'System',
                    id: 'main_cat',
                    anchor: '98%',
                    displayField: 'category_name',
                    valueField: 'category_id',
                    store: medCategoryStore,
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 1,
                    tabIndex: 902
                },
                {
                    xtype: 'textfield',
                    id: 'subCategory_id',
                    name: 'n[subCategory_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 903,
                    "emptyText": "Set status..",
                    "id": 'comboMasterSubcategoryStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('subCategory_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterSubcategoryStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterSubcategoryStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _SubcategoryFormPanel;
    };
    var SubcategoryMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterSubcategoryDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Drug Group </th><td>  {subCategory_name} </td></tr>',
                    '<tr><th width="40%">System  </th><td>  {category_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var SubcategorySave = function () {
        var form_data = {
            form: Ext.getCmp('SubcategorySaveForm').getForm().getValues()
        };
        if (Ext.getCmp('subCategory_id').getValue() > 0) {
            var mstId = Ext.getCmp('subCategory_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Drug Group',
            module: 'Master',
            op: 'saveSubCategory',
            extrainfo: 'Drug Group save',
            id: mstId
        };
        APICall(params, Application.MyphaMaster.SubcategorySave, form_data);
    };
    var gridSelectionChangedRoas = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewRoasdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewRoasdata').getSelectionModel().getSelections()[0].data.roa_id;

            ﻿Application.MyphaMaster.ViewRoas(ID);
        }
    };

    var RoasMasterStore = function () {
        var _roasMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRoas',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'roa_id',
                root: 'data'
            }, ['roa_id', 'roa_name', 'status']),
            sortInfo: {
                field: 'roa_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewRoasdata').getView().refresh();
                    Ext.getCmp('gridpanelMasterDataviewRoasdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _roasMasterStore;
    };

    var RoaMainGrid = function () {
        var _roasStore = RoasMasterStore();
        var _roasGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'roa_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }

            ]
        });
        _roasGridFilter.remote = true;
        _roasGridFilter.autoReload = true;
        var _roasmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _roasStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewRoasdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _roasGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'RoA',
                    dataIndex: 'roa_name',
                    sortable: true,
                    tooltip: 'RoA',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _roasStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedRoas
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('roa_id');
                    if (!Ext.isEmpty(ID)) {
                        ﻿Application.MyphaMaster.Cache.roa_id = ID;
                        Ext.getCmp('formpanelMasterRoas').hide();
                        ﻿Application.MyphaMaster.ViewRoas(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _roasStore.load();
                }
            },
            tbar: [{
                    text: 'Create RoA',
                    tooltip: 'Create RoA',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        ﻿Application.MyphaMaster.RoasAddEdit = 'Add';
                        var roasForm = Ext.getCmp('formpanelMasterRoas').getForm();
                        Ext.getCmp('panelMasterRoaParent').setTitle('Create RoA Details');
                        loadedForm = null;
                        roasForm.reset();
                        Ext.getCmp('roa_name').focus(false, 100);
                        /*<?php if (user_access("mypha_master", "saveRoas")) { ?> */
                        Ext.getCmp('buttonMasterRoaEdit').hide();
                        Ext.getCmp('buttonMasterRoaSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonMasterRoaCancel').show();
                        Ext.getCmp('formpanelMasterRoas').show();
                        Ext.getCmp('comboMasterRoasStatus').setValue(1);
                        Ext.getCmp('panelMasterRoasDetailsView').hide();
                        Ext.getCmp('panelMasterRoaParent').doLayout();
                    }
                }]
        });
        return _roasmaingridPanel;
    };
    var RoaForm = function () {
        var _roasFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterRoas',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'RoA (Route of Administration)',
                    id: 'roa_name',
                    name: 'n[roa_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 1,
                    maxValue: 100,
                    maxLength: 20
                },
                {
                    xtype: 'textfield',
                    id: 'roa_id',
                    name: 'n[roa_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 2,
                    "emptyText": "Set status..",
                    "id": 'comboMasterRoasStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('roa_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterRoasStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterRoasStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _roasFormPanel;
    };
    var RoaMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterRoasDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">RoA </th><td>  {roa_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var saveRoas = function () {
        var ptId = Ext.getCmp('roa_id').getValue();
        if (!Ext.isEmpty(Ext.getCmp('roa_name').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('comboMasterRoasStatus').getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveRoas',
                method: 'POST',
                params: {
                    id: Ext.getCmp('roa_id').getValue(),
                    name: Ext.getCmp('roa_name').getValue(),
                    status: Ext.getCmp('comboMasterRoasStatus').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (﻿Application.MyphaMaster.RoasAddEdit == 'Add') {
                            recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewRoasdata'));
                            Ext.getCmp('formpanelMasterRoas').getForm().reset();
                            Ext.getCmp('gridpanelMasterDataviewRoasdata').store.reload({
                                params: {
                                    start: 0,
                                    limit: recs_per_page
                                }
                            });
                        } else {
                            Ext.getCmp('gridpanelMasterDataviewRoasdata').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('gridpanelMasterDataviewRoasdata');
                                            var index = gridPanel.store.find('roa_id', ptId);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );
                        }
                        ﻿Application.MyphaMaster.RoasAddEdit = '';
                        ﻿Application.MyphaMaster.ViewRoas(tmp.data.roa_id);
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
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert("Notification", 'Please enter all required fields');
        }

    };
    var masterPanelforRoa = function (id) {
        var _mpanelforRoa = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'RoA',
            id: id,
            //iconCls: 'my-icon448',
            items: [RoaMainGrid(), new Ext.Panel({
                    title: 'RoA Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterRoaParent',
                    height: winsize.height * 0.6,
                    items: [
                        RoaForm(), RoaMasterDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [/*<?php if (user_access("mypha_master", "saveRoas")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterRoaEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 4,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewRoasdata').getSelectionModel().getSelections()[0].data.roa_id;
                                ﻿Application.MyphaMaster.EditRoasView(ID);
                            }
                        }, /*<?php } ?> */
                        {
                            text: "Cancel",
                            tabIndex: 5,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterRoaCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewRoasdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewRoasdata').getSelectionModel().getSelections()[0].data.roa_id;
                                    ﻿Application.MyphaMaster.ViewRoas(ID);
                                }
                            }
                        },
                        /*<?php if (user_access("mypha_master", "saveRoas")) { ?> */
                        {
                            text: "Save",
                            tabIndex: 4,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterRoaSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveRoas();
                            }
                        } /*<?php } ?> */

                    ]
                })
            ]
        });
        return _mpanelforRoa;
    };
    var gridSelectionChangedUnitDose = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('unitDoseMasterGrid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('unitDoseMasterGrid').getSelectionModel().getSelections()[0].data.unitdose_id;
            Application.MyphaMaster.unitDoseViewMode(ID);
        }
    };
    var unitDoseMasterStore = function () {
        var _unitDoseMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listUnitDoseMaster',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'unitdose_id',
                root: 'data'
            }, ['unitdose_id', 'unitdose_name', 'unitdose_name', 'image', 'roaname']),
            sortInfo: {
                field: 'unitdose_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('unitDoseMasterGrid').getView().refresh();
                    Ext.getCmp('unitDoseMasterGrid').getSelectionModel().selectRow(0);
                }
            }
        });
        return _unitDoseMasterStore;
    };
    var UnitDoseGrid = function () {

        var _UnitDoseGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'unitdose_name'
                },
                {
                    type: 'string',
                    dataIndex: 'roaname'
                }

            ]
        });
        _UnitDoseGridFilter.remote = true;
        _UnitDoseGridFilter.autoReload = true;
        var _unitDoseMasterStore = unitDoseMasterStore();
        var _gridPanel = new Ext.grid.GridPanel({
            store: _unitDoseMasterStore,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            //iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _UnitDoseGridFilter],
            id: 'unitDoseMasterGrid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Unit Dose',
                    sortable: true,
                    dataIndex: 'unitdose_name',
                    tooltip: 'Unit Dose',
                    hideable: true
                },
                {
                    header: 'RoA',
                    sortable: true,
                    dataIndex: 'roaname',
                    tooltip: 'RoA'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    width: 40,
                    items: [{
                            sortable: false,
                            getClass: function (v, meta, rec) {
                                /*  <?php if (user_access("mypha_master", "saveMainUnitDoseImage")) { ?> */
                                return 'upload_image';
                                /*<?php } else{ ?> */
                                return 'finascop_hideicon';
                                /*<?php } ?> */

                            },
                            handler: function (grid, rowIndex, colIndex) {

                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedUnitDose
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('unitdose_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMaster.Cache.unitdose_id = ID;
                        Ext.getCmp('unitDoseMasterForm').hide();
                        Application.MyphaMaster.unitDoseViewMode(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    _unitDoseMasterStore.load();
                }
            },
            tbar: [{
                    text: 'Create Unit Dose',
                    tooltip: 'Create Unit Dose',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMaster.unitDoseAddEdit = 'Add';
                        var masterForm = Ext.getCmp('unitDoseMasterForm').getForm();
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('unitdose_name').focus(false, 100);
                        /*<?php if (user_access("mypha_master", "saveUnitDose")) { ?> */
                        Ext.getCmp('UnitDoseEditBtn').hide();
                        Ext.getCmp('UnitDoseSaveBtn').show();
                        /*<?php } ?> */
                        Ext.getCmp('UnitDoseCancelBtn').show();
                        Ext.getCmp('unitDoseMasterForm').show();
                        Ext.getCmp('UnitDoseMasterDetailsViewPanel').hide();
                        Ext.getCmp('UnitDoseparentPanel').doLayout();
                        Ext.getCmp('UnitDoseparentPanel').setTitle("Create Unit Dose Details");
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _unitDoseMasterStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
        });
        return _gridPanel;
    };
    var UnitDoseMasterForms = function () {
        var _catFormPanel = new Ext.form.FormPanel({
            frame: false,
            border: true,
            hideBorders: true,
            labelWidth: 120,
            labelAlign: 'top',
            fileUpload: true,
            autoScroll: true,
            hidden: true,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            id: 'unitDoseMasterForm',
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Unit Dose',
                    id: 'unitdose_name',
                    name: 'n[unitdose_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 1,
                    maxValue: 100
                }, {
                    xtype: 'hidden',
                    id: 'unitdose_id',
                    name: 'n[unitdose_id]'
                }, mkCombo({
                    "type": 'mypha_roa',
                    "value": "roa_id",
                    "display": "roa_name",
                    "name": "n[unitdose_roa]",
                    "fieldLabel": "RoA",
                    "emptyText": "Select Roa",
                    tabIndex: 2,
                    anchor: '98%',
                    "id": "unitdose_roa",
                    "listeners": false,
                    "cx": "S_1"
                }),
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "emptyText": "Set status..",
                    "editable": false,
                    "typeAhead": false,
                    tabIndex: 3,
                    "id": "statusunitDose"
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('unitdose_id').getValue())) {
                        var recordSelected = Ext.getCmp('statusunitDose').getStore().getAt(0);
                        Ext.getCmp('statusunitDose').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _catFormPanel;
    };
    var UnitDoseMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'UnitDoseMasterDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Unit Dose </th><td> {unitdose_name} </td></tr>',
                    '<tr><th width="40%">RoA </th><td> {unitdose_roa_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var saveUnitDose = function () {
        var catId = Ext.getCmp('unitdose_id').getValue();
        if (!Ext.isEmpty(Ext.getCmp('unitdose_name').getValue() &&
                Ext.getCmp('unitdose_roa').getValue() && Ext.getCmp('statusunitDose').getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveUnitDose',
                method: 'POST',
                params: {
                    id: Ext.getCmp('unitdose_id').getValue(),
                    name: Ext.getCmp('unitdose_name').getValue(),
                    unitdose_roa: Ext.getCmp('unitdose_roa').getValue(),
                    status: Ext.getCmp('statusunitDose').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (Application.MyphaMaster.unitDoseAddEdit == 'Add') {
                            recs_per_page = updateRecsPerPage(Ext.getCmp('unitDoseMasterGrid'));
                            Ext.getCmp('unitDoseMasterGrid').store.reload({
                                params: {
                                    start: 0,
                                    limit: recs_per_page
                                }
                            });
                        } else {
                            Ext.getCmp('unitDoseMasterGrid').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('unitDoseMasterGrid');
                                            var index = gridPanel.store.find('unitdose_id', catId);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );
                        }
                        Application.MyphaMaster.unitDoseAddEdit = '';
                        Application.MyphaMaster.unitDoseViewMode(tmp.data.unitdose_id);
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
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert("Notification", 'Please enter all required fields');
        }

    };
    var masterPanelforUnitDose = function (id) {
        var _mpanelforunitDose = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Unit Doses',
            id: id,
            //iconCls: 'my-icon444',
            items: [UnitDoseGrid(), new Ext.Panel({
                    title: 'Unit Dose Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    id: 'UnitDoseparentPanel',
                    height: winsize.height * 0.6,
                    items: [UnitDoseMasterForms(), UnitDoseMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [/*<?php if (user_access("mypha_master", "saveUnitDose")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'UnitDoseEditBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 4,
                            hidden: true,
                            handler: function () {
                                var ID = Ext.getCmp('unitDoseMasterGrid').getSelectionModel().getSelections()[0].data.unitdose_id;
                                Application.MyphaMaster.unitDoseEditView(ID);
                            }
                        }, /*<?php } ?> */
                        {
                            text: "Cancel",
                            tabIndex: 5,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'UnitDoseCancelBtn',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('unitDoseMasterGrid').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('unitDoseMasterGrid').getSelectionModel().getSelections()[0].data.unitdose_id;
                                    Application.MyphaMaster.unitDoseViewMode(ID);
                                }
                            }
                        },
                        /*<?php if (user_access("mypha_master", "saveUnitDose")) { ?> */
                        {
                            text: "Save",
                            tabIndex: 4,
                            cls: 'left-right-buttons',
                            id: 'UnitDoseSaveBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveUnitDose(id);
                            }
                        } /*<?php } ?> */
                    ]
                })
            ]
        });
        return _mpanelforunitDose;
    };
    return {
        Cache: {},
        initDisease: function () {

            var _warningcategoryPanelId = 'panelMasterParentDisease';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = diseasePanel(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }
        },
        initManufacture: function () {
            console.log('here');
            var panelId = 'masterManufacture';
            var manufacture_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(manufacture_panel)) {
                manufacture_panel = manufacturePanel(panelId);
                Application.UI.addTab(manufacture_panel);
                manufacture_panel.doLayout();
            } else {
                Application.UI.addTab(manufacture_panel);
            }
        },
        EditManufactureView: function () {
            Application.MyphaMaster.ManufactureAddEdit = 'Edit';
            Ext.getCmp('panelMasterManufacture').doLayout();
            Ext.getCmp('panelMasterManufacture').setTitle('Edit Manufacturer Details');
            Ext.getCmp('formpanelMasterManufacture').show();
            Ext.getCmp('xtemplateMasterManufactureViewDetails').hide();
            /* <?php if (user_access("mypha_master", "saveManufacture")) { ?> */
            Ext.getCmp('buttonMasterManufactureEdit').hide();
            Ext.getCmp('buttonMasterManufactureSave').show();
            /*<?php  } ?>*/

            Ext.getCmp('buttonMasterManufactureCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var masterForm = Ext.getCmp('formpanelMasterManufacture').getForm();
                masterForm.load({
                    params: {
                        manufacture_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=manufacture_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        ViewManufactureMode: function () {
            var manufacture_id = arguments[0];
            /* <?php if (user_access("mypha_master", "saveManufacture")) { ?> */
            Ext.getCmp('buttonMasterManufactureEdit').show();
            Ext.getCmp('buttonMasterManufactureSave').hide();
            /*<?php  } ?>*/
            Ext.getCmp('buttonMasterManufactureCancel').hide();
            Ext.getCmp('formpanelMasterManufacture').hide();
            Ext.getCmp('xtemplateMasterManufactureViewDetails').show();
            Ext.getCmp('panelMasterManufacture').doLayout();
            Ext.getCmp('panelMasterManufacture').setTitle('View Manufacturer Details');
            Ext.Ajax.request({
                url: modURL + '&op=ManufacturedetailsView',
                method: 'POST',
                params: {manufacture_id: manufacture_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateMasterManufactureViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterManufacture').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterManufacture').doLayout();
        },
        initMedicineType: function () {
            var _medicinetypePanelId = 'panelMasterMainMedicineType';
            var _masterPanelMedicineType = Ext.getCmp(_medicinetypePanelId);
            if (Ext.isEmpty(_masterPanelMedicineType)) {
                _masterPanelMedicineType = masterPanelforMedicineType(_medicinetypePanelId);
                Application.UI.addTab(_masterPanelMedicineType);
                _masterPanelMedicineType.doLayout();
            } else {
                Application.UI.addTab(_masterPanelMedicineType);
            }
        },
        ViewMedicineTypes: function () {
            var medicine_type_id = arguments[0];
            /*<?php if (user_access("mypha_master", "saveMedicineTypes")) { ?> */
            Ext.getCmp('buttonMasterMedicineTypeEdit').show();
            Ext.getCmp('buttonMasterMedicineTypeSave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterMedicineTypeCancel').hide();
            Ext.getCmp('formpanelMasterMedicineTypes').hide();
            Ext.getCmp('panelMasterMedicineTypesDetailsView').show();
            Ext.getCmp('panelMasterMedicineTypeParent').doLayout();
            Ext.getCmp('panelMasterMedicineTypeParent').setTitle('View Dosage Form Details');
            Ext.Ajax.request({
                url: modURL + '&op=medicinetypesdetailsView',
                method: 'POST',
                params: {medicine_type_id: medicine_type_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterMedicineTypesDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterMedicineTypeParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterMedicineTypeParent').doLayout();
        },
        ViewDisease: function () {
            var disease_id = arguments[0];
            Ext.getCmp('buttonMasterdiseaseEdit').show();
            Ext.getCmp('buttonMasterdiseaseSave').hide();
            Ext.getCmp('buttonMasterdiseaseCancel').hide();
            Ext.getCmp('diseaseSaveForm').hide();
            Ext.getCmp('panelMasterDiseaseDetailsView').show();
            Ext.getCmp('panelMasterDiseaseParent').doLayout();
            Ext.getCmp('panelMasterDiseaseParent').setTitle('View Disease Details');
            Ext.Ajax.request({
                url: modURL + '&op=diseasedetailsView',
                method: 'POST',
                params: {disease_id: disease_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterDiseaseDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterDiseaseParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterDiseaseParent').doLayout();
        },
        EditMedicineTypesView: function () {
            Application.MyphaMaster.MedicineTypesAddEdit = 'Edit';
            Ext.getCmp('panelMasterMedicineTypeParent').doLayout();
            Ext.getCmp('panelMasterMedicineTypeParent').setTitle('Edit Dosage Forms Details');
            Ext.getCmp('formpanelMasterMedicineTypes').show();
            Ext.getCmp('panelMasterMedicineTypesDetailsView').hide();
            /*<?php if (user_access("mypha_master", "saveMedicineTypes")) { ?> */
            Ext.getCmp('buttonMasterMedicineTypeEdit').hide();
            Ext.getCmp('buttonMasterMedicineTypeSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterMedicineTypeCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterMedicineTypes').getForm();
                packagetypesForm.load({
                    params: {
                        medicine_type_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=medicinetype_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        initCategory: function () {
            console.log('system');
            var _categoryPanelId = 'panelMasterMainCategory';
            var _masterPanelCategory = Ext.getCmp(_categoryPanelId);
            if (Ext.isEmpty(_masterPanelCategory)) {
                _masterPanelCategory = masterPanelforCategory(_categoryPanelId);
                Application.UI.addTab(_masterPanelCategory);
                _masterPanelCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelCategory);
            }
        },
        ViewCategorys: function () {
            var category_id = arguments[0];
            /*<?php if (user_access("mypha_master", "saveCategorys")) { ?> */
            Ext.getCmp('buttonMasterCategoryEdit').show();
            Ext.getCmp('buttonMasterCategorySave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterCategoryCancel').hide();
            Ext.getCmp('formpanelMasterCategorys').hide();
            Ext.getCmp('panelMasterCategorysDetailsView').show();
            Ext.getCmp('panelMasterCategoryParent').doLayout();
            Ext.getCmp('panelMasterCategoryParent').setTitle('View System Details');
            Ext.Ajax.request({
                url: modURL + '&op=categorysdetailsView',
                method: 'POST',
                params: {category_id: category_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterCategorysDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterCategoryParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterCategoryParent').doLayout();
        },
        EditCategorysView: function () {
            Application.MyphaMaster.CategorysAddEdit = 'Edit';
            Ext.getCmp('panelMasterCategoryParent').doLayout();
            Ext.getCmp('panelMasterCategoryParent').setTitle('Edit System Details');
            Ext.getCmp('formpanelMasterCategorys').show();
            Ext.getCmp('panelMasterCategorysDetailsView').hide();
            /*<?php if (user_access("mypha_master", "saveCategorys")) { ?> */
            Ext.getCmp('buttonMasterCategoryEdit').hide();
            Ext.getCmp('buttonMasterCategorySave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterCategoryCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterCategorys').getForm();
                packagetypesForm.load({
                    params: {
                        category_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=category_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        initMedicineContent: function () {
            var _medicineContentPanelId = 'panelMasterMainMedicineContent';
            var _masterPanelMedicineContent = Ext.getCmp(_medicineContentPanelId);
            if (Ext.isEmpty(_masterPanelMedicineContent)) {
                _masterPanelMedicineContent = masterPanelforMedicineContent(_medicineContentPanelId);
                Application.UI.addTab(_masterPanelMedicineContent);
                _masterPanelMedicineContent.doLayout();
            } else {
                Application.UI.addTab(_masterPanelMedicineContent);
            }
        },
        ViewMedicineContents: function () {
            var medicineContent_id = arguments[0];
            /*<?php if (user_access("mypha_master", "saveMedicineContents")) { ?> */
            Ext.getCmp('buttonMasterMedicineContentEdit').show();
            Ext.getCmp('buttonMasterMedicineContentSave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterMedicineContentCancel').hide();
            Ext.getCmp('formpanelMasterMedicineContents').hide();
            Ext.getCmp('panelMasterMedicineContentsDetailsView').show();
            Ext.getCmp('panelMasterMedicineContentParent').doLayout();
            Ext.getCmp('panelMasterMedicineContentParent').setTitle('View Single API Drug Details');
            Ext.Ajax.request({
                url: modURL + '&op=medicineContentsdetailsView',
                method: 'POST',
                params: {medicineContent_id: medicineContent_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterMedicineContentsDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterMedicineContentParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterMedicineContentParent').doLayout();
        },
        EditMedicineContentsView: function () {
            Application.MyphaMaster.MedicineContentsAddEdit = 'Edit';
            Ext.getCmp('panelMasterMedicineContentParent').doLayout();
            Ext.getCmp('panelMasterMedicineContentParent').setTitle('Edit Single API Drug Details');
            Ext.getCmp('formpanelMasterMedicineContents').show();
            Ext.getCmp('panelMasterMedicineContentsDetailsView').hide();
            /*<?php if (user_access("mypha_master", "saveMedicineContents")) { ?> */
            Ext.getCmp('buttonMasterMedicineContentEdit').hide();
            Ext.getCmp('buttonMasterMedicineContentSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterMedicineContentCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterMedicineContents').getForm();
                packagetypesForm.load({
                    params: {
                        medicineContent_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=medicineContent_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        saveManufacture: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterManufacture').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveManufacture',
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
                            if (Application.MyphaMaster.ManufactureAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterListingManufacture'));
                                Ext.getCmp('formpanelMasterManufacture').getForm().reset();
                                Ext.getCmp('gridpanelMasterListingManufacture').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterListingManufacture').selModel.getSelected().data.manufacture_id = tmp.data.manufacture_id;
                                Ext.getCmp('gridpanelMasterListingManufacture').getStore().reload();
                                Ext.getCmp('gridpanelMasterListingManufacture').getView().refresh();
                            }
                            Application.MyphaMaster.ManufactureAddEdit = '';
                            Application.MyphaMaster.ViewManufactureMode(tmp.data.manufacture_id);


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
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', 'Please enter all required fields');
            }
        },
        saveMedicineTypes: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterMedicineTypes').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveMedicineTypes',
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
                            if (Application.MyphaMaster.MedicineTypesAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata'));
                                Ext.getCmp('formpanelMasterMedicineTypes').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewMedicineTypesdata').getStore().load();
                            }
                            Application.MyphaMaster.MedicineTypesAddEdit = '';
                            Application.MyphaMaster.ViewMedicineTypes(tmp.data.medicine_type_id);
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
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', 'Please enter all required fields');
            }
        },
        saveCategorys: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterCategorys').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveCategorys',
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
                            if (Application.MyphaMaster.CategorysAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewCategorysdata'));
                                Ext.getCmp('formpanelMasterCategorys').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewCategorysdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewCategorysdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewCategorysdata').getStore().load();
                            }
                            Application.MyphaMaster.CategorysAddEdit = '';
                            Application.MyphaMaster.ViewCategorys(tmp.data.category_id);
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
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', 'Please enter all required fields');
            }
        },
        saveMedicineContents: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterMedicineContents').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveMedicineContents',
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
                            if (Application.MyphaMaster.MedicineContentsAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata'));
                                Ext.getCmp('formpanelMasterMedicineContents').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewMedicineContentsdata').getStore().load();
                            }
                            Application.MyphaMaster.MedicineContentsAddEdit = '';
                            Application.MyphaMaster.ViewMedicineContents(tmp.data.medicineContent_id);
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
        initWarningCategory: function () {
            var _warningcategoryPanelId = 'panelMasterWarningCategory';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforWarningCategory(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }
        }, EditWarningCategorysView: function () {
            Application.MyphaMaster.warningCategorysAddEdit = 'Edit';
            Ext.getCmp('panelMasterWarningCategoryParent').doLayout();
            Ext.getCmp('panelMasterWarningCategoryParent').setTitle('Edit Warning Category details');
            Ext.getCmp('formpanelMasterWarningCategorys').show();
            Ext.getCmp('panelMasterWarningCategorysDetailsView').hide();

            Ext.getCmp('buttonMasterwarningCategoryEdit').hide();
            Ext.getCmp('buttonMasterwarningCategorySave').show();

            Ext.getCmp('buttonMasterwarningCategoryCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterWarningCategorys').getForm();
                packagetypesForm.load({
                    params: {
                        warningCategory_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=warning_category_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        EditInfoView: function () {
            Application.MyphaMaster.infoAddEdit = 'Edit';
            Ext.getCmp('panelMasterInformationParent').doLayout();
            Ext.getCmp('panelMasterInformationParent').setTitle('Edit Information details');
            Ext.getCmp('formpanelMasterInformation').show();
            Ext.getCmp('panelMasterInformationDetailsView').hide();

            Ext.getCmp('buttonMasterinformationEdit').hide();
            Ext.getCmp('buttonMasterinformationSave').show();

            Ext.getCmp('buttonMasterinformationCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterInformation').getForm();
                packagetypesForm.load({
                    params: {
                        medadinfo_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=info_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        EditWorkView: function () {
            Application.MyphaMaster.workAddEdit = 'Edit';
            Ext.getCmp('panelMasterWorksParent').doLayout();
            Ext.getCmp('panelMasterWorksParent').setTitle('Edit Medicine working details');
            Ext.getCmp('formpanelMasterWorks').show();
            Ext.getCmp('panelMasterWorksDetailsView').hide();

            Ext.getCmp('buttonMasterworksEdit').hide();
            Ext.getCmp('buttonMasterworksSave').show();

            Ext.getCmp('buttonMasterworksCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterWorks').getForm();
                packagetypesForm.load({
                    params: {
                        medwork_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=work_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        EditSideEffectView: function () {
            Application.MyphaMaster.sideeffectAddEdit = 'Edit';
            Ext.getCmp('panelMasterSideeffectParent').doLayout();
            Ext.getCmp('panelMasterSideeffectParent').setTitle('Edit Side Effect details');
            Ext.getCmp('formpanelMasterSideeffect').show();
            Ext.getCmp('panelMasterSideeffectDetailsView').hide();

            Ext.getCmp('buttonMastersideeffectEdit').hide();
            Ext.getCmp('buttonMastersideeffectSave').show();

            Ext.getCmp('buttonMastersideeffectCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterSideeffect').getForm();
                packagetypesForm.load({
                    params: {
                        medsideffect_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=sideeffect_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        EditUnitView: function () {
            Application.MyphaMaster.unitAddEdit = 'Edit';
            Ext.getCmp('panelMasterUnitParent').doLayout();
            Ext.getCmp('panelMasterUnitParent').setTitle('Edit Unit Details');
            Ext.getCmp('formpanelMasterUnit').show();
            Ext.getCmp('panelMasterUnitDetailsView').hide();

            Ext.getCmp('buttonMasterunitEdit').hide();
            Ext.getCmp('buttonMasterunitSave').show();
            Ext.getCmp('gridUnitValueList').hide();

            Ext.getCmp('buttonMasterunitCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterUnit').getForm();
                packagetypesForm.load({
                    params: {
                        unit_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=unit_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        EditUseView: function () {
            Application.MyphaMaster.useAddEdit = 'Edit';
            Ext.getCmp('panelMasterUseParent').doLayout();
            Ext.getCmp('panelMasterUseParent').setTitle('Edit Usage details');
            Ext.getCmp('formpanelMasterUse').show();
            Ext.getCmp('panelMasterUseDetailsView').hide();

            Ext.getCmp('buttonMasteruseEdit').hide();
            Ext.getCmp('buttonMasteruseSave').show();

            Ext.getCmp('buttonMasteruseCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterUse').getForm();
                packagetypesForm.load({
                    params: {
                        meduse_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=use_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        EditCompositionView: function () {
            Application.MyphaMaster.compositionAddEdit = 'Edit';
            Ext.getCmp('panelMasterCompositionParent').doLayout();
            Ext.getCmp('panelMasterCompositionParent').setTitle('Edit Single API Drug Details');
            Ext.getCmp('formpanelMasterComposition').show();
            Ext.getCmp('panelMasterCompositionDetailsView').hide();

            Ext.getCmp('buttonMastercompositionEdit').hide();
            Ext.getCmp('buttonMastercompositionSave').show();

            Ext.getCmp('buttonMastercompositionCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterComposition').getForm();
                packagetypesForm.load({
                    params: {
                        composition_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=composition_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                        Ext.getCmp('sub_category').setValue(tmp.data.subCategory_id);
                        Ext.getCmp('sub_category').getStore().load();
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        EditWarningView: function () {
            Application.MyphaMaster.warningAddEdit = 'Edit';
            Ext.getCmp('panelMasterWarningParent').doLayout();
            Ext.getCmp('panelMasterWarningParent').setTitle('Edit Warning details');
            Ext.getCmp('formpanelMasterWarning').show();
            Ext.getCmp('panelMasterWarningDetailsView').hide();

            Ext.getCmp('buttonMasterwarningEdit').hide();
            Ext.getCmp('buttonMasterwarningSave').show();

            Ext.getCmp('buttonMasterwarningCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var packagetypesForm = Ext.getCmp('formpanelMasterWarning').getForm();
                packagetypesForm.load({
                    params: {
                        warning_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=warning_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        ViewWarningCategorys: function () {
            var warningCategory_id = arguments[0];

            Ext.getCmp('buttonMasterwarningCategoryEdit').show();
            Ext.getCmp('buttonMasterwarningCategorySave').hide();

            Ext.getCmp('buttonMasterwarningCategoryCancel').hide();
            Ext.getCmp('formpanelMasterWarningCategorys').hide();
            Ext.getCmp('panelMasterWarningCategorysDetailsView').show();
            Ext.getCmp('panelMasterWarningCategoryParent').doLayout();
            Ext.getCmp('panelMasterWarningCategoryParent').setTitle('Warning Category Details');
            Ext.Ajax.request({
                url: modURL + '&op=warningcategorysdetailsView',
                method: 'POST',
                params: {warningCategory_id: warningCategory_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterWarningCategorysDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterWarningCategoryParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterWarningCategoryParent').doLayout();
        }, saveWarningCategorys: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterWarningCategorys').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveWarningCategorys',
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
                            if (Application.MyphaMaster.warningCategorysAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata'));
                                Ext.getCmp('formpanelMasterWarningCategorys').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewWarningCategorysdata').getStore().load();
                            }
                            Application.MyphaMaster.warningCategorysAddEdit = '';
                            Application.MyphaMaster.ViewWarningCategorys(tmp.data.warningCategory_id);
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
        saveComposition: function () {
            var compositonId = Ext.getCmp('composition_id').getValue();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterComposition').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveComposition',
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
                            if (Application.MyphaMaster.compositionAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewCompositiondata'));
                                Ext.getCmp('formpanelMasterComposition').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewCompositiondata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewCompositiondata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewCompositiondata').getView().refresh(
                                        {
                                            callback: function (record, options, success) {
                                                var gridPanel = Ext.getCmp('gridpanelMasterDataviewCompositiondata');
                                                var index = gridPanel.store.find('composition_id', compositonId);
                                                gridPanel.getSelectionModel().selectRow(index);
                                            }
                                        }
                                );
                            }
                            Application.MyphaMaster.compositionAddEdit = '';
                            Application.MyphaMaster.ViewComposition(tmp.data.composition_id);
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
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', 'Please enter all required fields');
            }
        },
        saveSideEffect: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterSideeffect').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveSideEffect',
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
                            if (Application.MyphaMaster.sideeffectAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewSideeffectdata'));
                                Ext.getCmp('formpanelMasterSideeffect').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewSideeffectdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewSideeffectdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewSideeffectdata').getStore().load();
                            }
                            Application.MyphaMaster.sideeffectAddEdit = '';
                            Application.MyphaMaster.ViewSideEffect(tmp.data.medsideffect_id);
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
        saveInfo: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterInformation').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveInfo',
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
                            if (Application.MyphaMaster.infoAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewInformationdata'));
                                Ext.getCmp('formpanelMasterInformation').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewInformationdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewInformationdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewInformationdata').getStore().load();
                            }
                            Application.MyphaMaster.infoAddEdit = '';
                            Application.MyphaMaster.ViewInfo(tmp.data.medadinfo_id);
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
        saveWork: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterWorks').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveWork',
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
                            if (Application.MyphaMaster.workAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewWorksdata'));
                                Ext.getCmp('formpanelMasterWorks').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewWorksdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewWorksdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewWorksdata').getStore().load();
                            }
                            Application.MyphaMaster.workAddEdit = '';
                            Application.MyphaMaster.ViewWork(tmp.data.medwork_id);
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
        saveUnit: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterUnit').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveUnit',
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
                            if (Application.MyphaMaster.unitAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewUnitdata'));
                                Ext.getCmp('formpanelMasterUnit').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewUnitdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewUnitdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewUnitdata').getStore().load();
                            }
                            Application.MyphaMaster.unitAddEdit = '';
                            Application.MyphaMaster.ViewUnit(tmp.data.unit_id);
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
        saveUses: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterUse').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveUses',
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
                            if (Application.MyphaMaster.useAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewUsedata'));
                                Ext.getCmp('formpanelMasterUse').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewUsedata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewUsedata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewUsedata').getStore().load();
                            }
                            Application.MyphaMaster.useAddEdit = '';
                            Application.MyphaMaster.ViewUse(tmp.data.meduse_id);
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
        saveWarning: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelMasterWarning').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveWarning',
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
                            if (Application.MyphaMaster.warningAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewWarningdata'));
                                Ext.getCmp('formpanelMasterWarning').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewWarningdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewWarningdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewWarningdata').getStore().load();
                            }
                            Application.MyphaMaster.warningAddEdit = '';
                            Application.MyphaMaster.ViewWarning(tmp.data.warning_id);
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
        diseaseSave: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('diseaseSaveForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=saveDisease',
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
                            if (Application.MyphaMaster.diseaseAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDiseaseDataview'));
                                Ext.getCmp('diseaseSaveForm').getForm().reset();
                                Ext.getCmp('gridpanelMasterDiseaseDataview').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDiseaseDataview').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDiseaseDataview').getStore().load();
                            }
                            Application.MyphaMaster.diseaseAddEdit = '';
                            Application.MyphaMaster.ViewDisease(tmp.data.disease_id);
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            console.log(result.message);
                            Ext.Msg.alert('Status', result.message);
                        } else {
                            Ext.Msg.alert('Notification', 'Please enter all required fields');

                        }
                    }
                });
            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');

            }
        },
        ViewInfo: function () {
            var medadinfo_id = arguments[0];

            Ext.getCmp('buttonMasterinformationEdit').show();
            Ext.getCmp('buttonMasterinformationSave').hide();

            Ext.getCmp('buttonMasterinformationCancel').hide();
            Ext.getCmp('formpanelMasterInformation').hide();
            Ext.getCmp('panelMasterInformationDetailsView').show();
            Ext.getCmp('panelMasterInformationParent').doLayout();
            Ext.getCmp('panelMasterInformationParent').setTitle('More Information Details');
            Ext.Ajax.request({
                url: modURL + '&op=informationdetailsView',
                method: 'POST',
                params: {medadinfo_id: medadinfo_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterInformationDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterInformationParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterInformationParent').doLayout();
        },
        ViewWork: function () {
            var medwork_id = arguments[0];

            Ext.getCmp('buttonMasterworksEdit').show();
            Ext.getCmp('buttonMasterworksSave').hide();

            Ext.getCmp('buttonMasterworksCancel').hide();
            Ext.getCmp('formpanelMasterWorks').hide();
            Ext.getCmp('panelMasterWorksDetailsView').show();
            Ext.getCmp('panelMasterWorksParent').doLayout();
            Ext.getCmp('panelMasterWorksParent').setTitle('Medicine Works Details');
            Ext.Ajax.request({
                url: modURL + '&op=worksdetailsView',
                method: 'POST',
                params: {medwork_id: medwork_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterWorksDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterWorksParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterWorksParent').doLayout();
        },
        ViewSideEffect: function () {
            var medsideffect_id = arguments[0];

            Ext.getCmp('buttonMastersideeffectEdit').show();
            Ext.getCmp('buttonMastersideeffectSave').hide();

            Ext.getCmp('buttonMastersideeffectCancel').hide();
            Ext.getCmp('formpanelMasterSideeffect').hide();
            Ext.getCmp('panelMasterSideeffectDetailsView').show();
            Ext.getCmp('panelMasterSideeffectParent').doLayout();
            Ext.getCmp('panelMasterSideeffectParent').setTitle('Side Effect  Details');
            Ext.Ajax.request({
                url: modURL + '&op=sideeffectdetailsView',
                method: 'POST',
                params: {medsideffect_id: medsideffect_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterSideeffectDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterSideeffectParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterSideeffectParent').doLayout();
        },
        ViewUnit: function () {
            var unit_id = arguments[0];

            Ext.getCmp('buttonMasterunitEdit').show();
            Ext.getCmp('buttonMasterunitSave').hide();

            Ext.getCmp('buttonMasterunitCancel').hide();
            
            Ext.getCmp('formpanelMasterUnit').hide();
            Ext.getCmp('panelMasterUnitDetailsView').show();
            Ext.getCmp('panelMasterUnitParent').doLayout();
            Ext.getCmp('panelMasterUnitParent').setTitle('Unit Details');
            Ext.Ajax.request({
                url: modURL + '&op=unitdetailsView',
                method: 'POST',
                params: {unit_id: unit_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterUnitDetailsView');
                        visualsDescPanel.update(tmp);
                        Ext.getCmp('gridUnitValueList').show();
                        Ext.getCmp('gridUnitValueList').getStore().load({
                            params:{
                                unitId: unit_id
                            }
                        });
                    }
                    Ext.getCmp('panelMasterUnitParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterUnitParent').doLayout();
        },
        ViewUse: function () {
            var meduse_id = arguments[0];

            Ext.getCmp('buttonMasteruseEdit').show();
            Ext.getCmp('buttonMasteruseSave').hide();

            Ext.getCmp('buttonMasteruseCancel').hide();
            Ext.getCmp('formpanelMasterUse').hide();
            Ext.getCmp('panelMasterUseDetailsView').show();
            Ext.getCmp('panelMasterUseParent').doLayout();
            Ext.getCmp('panelMasterUseParent').setTitle('Medicine usage  Details');
            Ext.Ajax.request({
                url: modURL + '&op=usedetailsView',
                method: 'POST',
                params: {meduse_id: meduse_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterUseDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterUseParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterUseParent').doLayout();
        },
        ViewComposition: function () {
            var composition_id = arguments[0];

            Ext.getCmp('buttonMastercompositionEdit').show();
            Ext.getCmp('buttonMastercompositionSave').hide();

            Ext.getCmp('buttonMastercompositionCancel').hide();
            Ext.getCmp('formpanelMasterComposition').hide();
            Ext.getCmp('panelMasterCompositionDetailsView').show();
            Ext.getCmp('panelMasterCompositionParent').doLayout();
            Ext.getCmp('panelMasterCompositionParent').setTitle('View Single API Drug Details');
            Ext.Ajax.request({
                url: modURL + '&op=compositiondetailsView',
                method: 'POST',
                params: {composition_id: composition_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterCompositionDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterCompositionParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterCompositionParent').doLayout();
        },
        ViewWarning: function () {
            var warning_id = arguments[0];

            Ext.getCmp('buttonMasterwarningEdit').show();
            Ext.getCmp('buttonMasterwarningSave').hide();

            Ext.getCmp('buttonMasterwarningCancel').hide();
            Ext.getCmp('formpanelMasterWarning').hide();
            Ext.getCmp('panelMasterWarningDetailsView').show();
            Ext.getCmp('panelMasterWarningParent').doLayout();
            Ext.getCmp('panelMasterWarningParent').setTitle('Warning  Details');
            Ext.Ajax.request({
                url: modURL + '&op=warningdetailsView',
                method: 'POST',
                params: {warning_id: warning_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterWarningDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterWarningParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterWarningParent').doLayout();
        },
        initWarnings: function () {
            var _warningcategoryPanelId = 'panelMasterParentWarning';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforWarning(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }

        },
        initComposition: function () {
            var _warningcategoryPanelId = 'panelMasterParentComposition';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforComposition(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }

        },
        initUnit: function () {
            var _warningcategoryPanelId = 'panelMasterParentUnit';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforUnit(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }

        },
        initMedUse: function () {
            var _warningcategoryPanelId = 'panelMasterParentUse';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforUse(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }


        },
        initMedSideEffects: function () {
            var _warningcategoryPanelId = 'panelMasterParentSideEffect';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforSideeffect(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }

        },
        initMedWorks: function () {
            var _warningcategoryPanelId = 'panelMasterParentWorks';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforWorks(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }


        },
        initMedAdinfo: function () {

            var _warningcategoryPanelId = 'panelMasterParentInformation';
            var _masterPanelWarningCategory = Ext.getCmp(_warningcategoryPanelId);
            if (Ext.isEmpty(_masterPanelWarningCategory)) {
                _masterPanelWarningCategory = masterPanelforInformation(_warningcategoryPanelId);
                Application.UI.addTab(_masterPanelWarningCategory);
                _masterPanelWarningCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelWarningCategory);
            }

        },
        EditDiseaseView: function () {
            Application.MyphaMaster.diseaseAddEdit = 'Edit';
            Ext.getCmp('panelMasterDiseaseParent').doLayout();
            Ext.getCmp('panelMasterDiseaseParent').setTitle('Edit Disease Details');
            Ext.getCmp('diseaseSaveForm').show();
            Ext.getCmp('panelMasterDiseaseDetailsView').hide();

            Ext.getCmp('buttonMasterdiseaseEdit').hide();
            Ext.getCmp('buttonMasterdiseaseSave').show();

            Ext.getCmp('buttonMasterdiseaseCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('diseaseSaveForm').getForm();
                dept_form.load({
                    params: {
                        disease_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadDisease',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }


                });
            }
        }, initSubCategory: function () {
            var _subcategoryPanelId = 'panelMasterSubCategory';
            var _masterPanelSubCategory = Ext.getCmp(_subcategoryPanelId);
            if (Ext.isEmpty(_masterPanelSubCategory)) {
                _masterPanelSubCategory = masterPanelforsubCategory(_subcategoryPanelId);
                Application.UI.addTab(_masterPanelSubCategory);
                _masterPanelSubCategory.doLayout();
            } else {
                Application.UI.addTab(_masterPanelSubCategory);
            }
        }, SubcategorySave: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('SubcategorySaveForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=saveSubCategory',
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
                            if (Application.MyphaMaster.SubCategoryAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterSubcategoryDataview'));
                                Ext.getCmp('SubcategorySaveForm').getForm().reset();
                                Ext.getCmp('gridpanelMasterSubcategoryDataview').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterSubcategoryDataview').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterSubcategoryDataview').getStore().load();
                            }
                            Application.MyphaMaster.SubCategoryAddEdit = '';
                            Application.MyphaMaster.ViewSubCategory(tmp.data.subCategory_id);
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            console.log(result.message);
                            Ext.Msg.alert('Status', result.message);
                        } else {
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');

                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', 'Please enter all required fields');

            }
        }, EditSubcategoryView: function (subCategory_id) {
            Application.MyphaMaster.diseaseAddEdit = 'Edit';
            Ext.getCmp('panelMasterSubcategoryParent').doLayout();
            Ext.getCmp('panelMasterSubcategoryParent').setTitle('Edit Drug Group Details');
            Ext.getCmp('SubcategorySaveForm').show();
            Ext.getCmp('panelMasterSubcategoryDetailsView').hide();

            Ext.getCmp('buttonMasterSubcategoryEdit').hide();
            Ext.getCmp('buttonMasterSubcategorySave').show();

            Ext.getCmp('buttonMasterSubcategoryCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('SubcategorySaveForm').getForm();
                dept_form.load({
                    params: {
                        subCategory_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadSubcategory',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                        Ext.getCmp('main_cat').setValue(tmp.data.category_id);
                        Ext.getCmp('main_cat').getStore().load();
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }


                });
            }
        },
        ViewSubcategory: function () {
            var subCategory_id = arguments[0];

            Ext.getCmp('buttonMasterSubcategoryEdit').show();
            Ext.getCmp('buttonMasterSubcategorySave').hide();

            Ext.getCmp('buttonMasterSubcategoryCancel').hide();
            Ext.getCmp('SubcategorySaveForm').hide();
            Ext.getCmp('panelMasterSubcategoryDetailsView').show();
            Ext.getCmp('panelMasterSubcategoryParent').doLayout();
            Ext.getCmp('panelMasterSubcategoryParent').setTitle('View Drug Group Details');
            Ext.Ajax.request({
                url: modURL + '&op=SubcategorydetailsView',
                method: 'POST',
                params: {subCategory_id: subCategory_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterSubcategoryDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterSubcategoryParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterSubcategoryParent').doLayout();
        }, initRoa: function () {
            var _packagetypePanelId = 'panelMasterMainRoa';
            var _masterPanelRoa = Ext.getCmp(_packagetypePanelId);
            if (Ext.isEmpty(_masterPanelRoa)) {
                _masterPanelRoa = masterPanelforRoa(_packagetypePanelId);
                Application.UI.addTab(_masterPanelRoa);
                _masterPanelRoa.doLayout();
            } else {
                Application.UI.addTab(_masterPanelRoa);
            }

        },
        EditRoasView: function () {
            ﻿Application.MyphaMaster.RoasAddEdit = 'Edit';
            Ext.getCmp('panelMasterRoaParent').doLayout();
            Ext.getCmp('panelMasterRoaParent').setTitle('Edit RoA Details');
            Ext.getCmp('formpanelMasterRoas').show();
            Ext.getCmp('panelMasterRoasDetailsView').hide();
            /*<?php if (user_access("mypha_master", "saveRoas")) { ?> */
            Ext.getCmp('buttonMasterRoaEdit').hide();
            Ext.getCmp('buttonMasterRoaSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterRoaCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");

            if (!Ext.isEmpty(arguments[0])) {
                var roasForm = Ext.getCmp('formpanelMasterRoas').getForm();
                roasForm.load({
                    params: {
                        roa_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=roas_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        ViewRoas: function () {
            var roa_id = arguments[0];
            /*<?php if (user_access("mypha_master", "saveRoas")) { ?> */
            Ext.getCmp('buttonMasterRoaEdit').show();
            Ext.getCmp('buttonMasterRoaSave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterRoaCancel').hide();
            Ext.getCmp('formpanelMasterRoas').hide();
            Ext.getCmp('panelMasterRoasDetailsView').show();
            Ext.getCmp('panelMasterRoaParent').doLayout();
            Ext.getCmp('panelMasterRoaParent').setTitle("View Route of Administration Details");
            Ext.Ajax.request({
                url: modURL + '&op=roasdetailsView',
                method: 'POST',
                params: {roa_id: roa_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterRoasDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterRoaParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterRoaParent').doLayout();
        }, initUnitDose: function () {
            var _unitDosePanelId = 'panelidforUnitDose';
            var _masterPanelUnitDose = Ext.getCmp(_unitDosePanelId);
            if (Ext.isEmpty(_masterPanelUnitDose)) {
                _masterPanelUnitDose = masterPanelforUnitDose(_unitDosePanelId);
                Application.UI.addTab(_masterPanelUnitDose);
                _masterPanelUnitDose.doLayout();
            } else {
                Application.UI.addTab(_masterPanelUnitDose);
            }
        },
        unitDoseEditView: function () {
            Application.MyphaMaster.unitDoseAddEdit = 'Edit';
            Ext.getCmp('UnitDoseparentPanel').doLayout();
            Ext.getCmp('UnitDoseparentPanel').setTitle('Edit Unit Dose Details');
            Ext.getCmp('unitDoseMasterForm').show();
            Ext.getCmp('UnitDoseMasterDetailsViewPanel').hide();
            /*<?php if (user_access("mypha_master", "saveUnitDose")) { ?> */
            Ext.getCmp('UnitDoseEditBtn').hide();
            Ext.getCmp('UnitDoseSaveBtn').show();
            /*<?php } ?> */
            Ext.getCmp('UnitDoseCancelBtn').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var masterForm = Ext.getCmp('unitDoseMasterForm').getForm();
                masterForm.load({
                    params: {
                        unitdose_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=unitDose_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {
                        //pdtLoadedForm = [form, action.response.responseText];
                        var tmp = Ext.decode(action.response.responseText);
                        Ext.getCmp('unitdose_roa').getStore().load();
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        unitDoseViewMode: function () {
            var unitdose_id = arguments[0];
            /*<?php if (user_access("mypha_master", "saveUnitDose")) { ?> */
            Ext.getCmp('UnitDoseEditBtn').show();
            Ext.getCmp('UnitDoseSaveBtn').hide();
            /*<?php } ?> */
            Ext.getCmp('UnitDoseCancelBtn').hide();
            Ext.getCmp('unitDoseMasterForm').hide();
            Ext.getCmp('UnitDoseMasterDetailsViewPanel').show();
            Ext.getCmp('UnitDoseparentPanel').setTitle('View Unit Dose Details');
            Ext.getCmp('UnitDoseparentPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=UnitDosedetailsView',
                method: 'POST',
                params: {unitdose_id: unitdose_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('UnitDoseMasterDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('UnitDoseparentPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('UnitDoseparentPanel').doLayout();
            Ext.getCmp('unitdose_roa').getStore().load();
        },
        mapMedicinetoDisease: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var _testResultsForm = Ext.getCmp('medDiseaseForm').getForm();
            if (_testResultsForm.isValid()) {
                _testResultsForm.submit({
                    url: modURL + '&op=mapMedicinetoDisease',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        disease_id: Application.MyphaMaster.Cache.disease_id,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Ext.getCmp('medDiseaseForm').getForm().reset();
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('gridpanelMedicineDiseaseMap').getStore().load({
                                params: {
                                    disease_id: Application.MyphaMaster.Cache.disease_id
                                }
                            });
                        } else if (tmp.success === true && tmp.valid === false) {
                            Application.example.msg('Error', tmp.message);
                        } else if (tmp.success === false && tmp.valid === false) {
                            Application.example.msg('Error', tmp.message);
                        } else {
                            Application.example.msg('Error', tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Status', result.message);
                        } else {
                            Application.example.msg('Warning', 'Fill the required fields');
                        }
                    }
                });
            } else {
                Application.example.msg('Warning', 'Fill the required fields');
            }
        },
        saveDiseaseImage: function () {
            var bucket_name = Ext.getCmp('di_albumBucketName').getValue();
            var file_name = Ext.getCmp('di_file_name').getValue();

            if (bucket_name != '' && file_name != '')
            {
                Ext.Ajax.request({
                    url: modURL + '&op=saveDiseaseImage',
                    method: 'POST',
                    params: {
                        disease_id: Application.MyphaMaster.Cache.rid,
                        uploaded_file_name: file_name,
                        bucket: bucket_name,
                        filepath: Ext.getCmp('di_aws_file_location').getValue(),
                        bucket_path: Ext.getCmp('di_oncompleteurl').getValue()
                    },
                    success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                            Application.example.msg('Notification', 'Image saved..');
                            Ext.getCmp('diseaseuploadwindow').close();

                        } else {
                            Ext.Msg.alert('Error', "Image not saved. Try again");
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Notification', result.error);
                        } else {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.MessageBox.alert('Error', result.error);
                        }
                    }
                });
            }
        },
        uploadimageDisease: function (rid, uploadtype, disease_image) {
            if (uploadtype === 'disease') {
                var main_img_panel = diseaseuploadForm(disease_image);
            }

            var window_id = "diseaseuploadwindow";
            var diseaseuploadwindow = new Ext.Window({
                id: window_id,
                title: 'Upload Image',
                layout: 'fit',
                width: 230,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                iconCls: 'finascop_dataentry_receipt',
                resizable: false,
                closable: false,
                items: main_img_panel,
                listeners: {
                    afterrender: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        winLoadMask = new Ext.LoadMask(Ext.getCmp('diseaseuploadwindow').getEl());
                        winLoadMask.msg = 'Please wait...';
                        if (uploadtype === 'disease')
                        {
                            Ext.getCmp('disease_image_upload').getForm().load({
                                waitTitle: 'Please Wait',
                                waitMsg: 'Loading...',
                                url: modURL + '&op=get_diseaseimg_s3_details',
                                params: {
                                    rid: rid,
                                    uploadtype: uploadtype,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                }
                            });
                        }

                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        iconCls: 'finascop_my-icon1',
                        handler: function () {

                            diseaseuploadwindow.close();
                        }
                    }, {
                        text: 'Upload',
                        icon: './resources/images/submenuicons/upload_fl.png',
                        iconCls: 'upload',
                        id: 'saveButton',
                        handler: function () {
                            if (uploadtype === 'disease')
                            {
                                Application.MyphaMaster.Cache.rid = rid;
                                var bucket_name = Ext.getCmp('di_albumBucketName').getValue();
                                var file_name = Ext.getCmp('di_file_name').getValue();
                                var file_path = Ext.getCmp('di_aws_file_location').getValue();
                                var form_data = {
                                    disease_id: rid,
                                    uploaded_file_name: file_name,
                                    bucket: bucket_name,
                                    filepath: Ext.getCmp('di_aws_file_location').getValue(),
                                    bucket_path: Ext.getCmp('di_oncompleteurl').getValue()
                                };
                                var params = {
                                    action: 'Add Disease Image',
                                    module: 'mypha_master',
                                    op: 'saveDiseaseImage',
                                    extrainfo: 'Add Disease Image',
                                    id: rid
                                };
                                if (file_path != '') {
                                    APICall(params, Application.MyphaMaster.saveDiseaseImage, form_data);
                                }
                                else {
                                    Ext.Msg.alert("Notification.", 'Please select a valid Image file');
                                }
                            }
                        }
                    }]
            });
            diseaseuploadwindow.doLayout();
            diseaseuploadwindow.show(this);
            diseaseuploadwindow.center();
        },
    };
}();




