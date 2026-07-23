Application.CRM_Template = function () {
    var winLoadMask;
    var RECS_PER_PAGE = 12;
    var modURL = '?module=crm_template';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionTemplateChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('templateGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('templateGridPanel').getSelectionModel().getSelections()[0].data.template_id;
            Application.CRM_Template.ViewTemplateMode(ID);
        }
    };
    var templateGridstore = function () {
        var _advertisementList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listTemplates',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'template_id',
                root: 'data'
            }, ['template_id', 'template_name', 'campaign_type', 'template_type', 'template_IsActive']),
            sortInfo: {
                field: 'template_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('templateGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _advertisementList;
    };
    var removeTemplate = function (id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=removeTemplate',
            params: {
                template_id: id,
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    Application.example.msg('Success', 'Removed item');
                    Ext.getCmp('templateGridPanel').getStore().reload();
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var templateGrid = function () {
        var _templateGridstore = templateGridstore();
        var _templateFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'template_name'
                },
                {
                    type: 'string',
                    dataIndex: 'campaign_type'
                },
                {
                    type: 'string',
                    dataIndex: 'template_type'
                }
            ]
        });
        _templateFilter.remote = true;
        _templateFilter.autoReload = true;
        var _tempsPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _templateGridstore,
            //iconCls: 'money',
            id: 'templateGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _templateFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Template Name',
                    dataIndex: 'template_name',
                    sortable: true,
                    tooltip: 'Template Name',
                    hideable: true
                },
                {
                    header: 'Campaign Type',
                    dataIndex: 'campaign_type',
                    sortable: true,
                    tooltip: 'Campaign Type',
                    hideable: true
                },
                {
                    header: 'Template Type',
                    dataIndex: 'template_type',
                    sortable: true,
                    tooltip: 'Template Type',
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
                            templateActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                
//                {
//                    header: 'Actions',
//                    xtype: 'actioncolumn',
//                    hideable: false,
//                    sortable: false,
//                    groupable: false,
//                    items: [
//                        
////                        {
////                            iconCls: 'finascop_add',
////                            tooltip: 'Add Variables',
////                            handler: function (grid, rowIndex, colIndex, itm, evn) {
////                                var record = grid.getStore().getAt(rowIndex);
////                                variablesForm(record.get('template_id'));
////                            }
////                        },
//                        /*{
//                            iconCls: 'remove-enquiry',
//                            tooltip: 'Remove',
//                            handler: function (grid, rowIndex, colIndex, itm, evn) {
//                                var record = grid.getStore().getAt(rowIndex);
//                                var _active = record.data.template_IsActive
//                                        Ext.MessageBox.confirm('Confirm', 'Are you sure want to remove this?', function (btn, text) {
//                                            if (btn == 'yes') {
//                                                removeTemplate(record.get('template_id'));
//                                            }
//                                        });
//                            }
//                        }*/
//
//
//                    ]
//                }
            ],
            tbar: [{
                    xtype: 'button',
                    text: 'Create Template',
                    tooltip: 'Create Template',
                    iconCls: 'finascop_add',
                    handler: function () {
                        templateform();
                    }
                }],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _templateGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionTemplateChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('template_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.CRM_Template.Cache.template_id = ID;
                        //Ext.getCmp('formpanelcampaigns').hide();
                        Application.CRM_Template.ViewTemplateMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _templateGridstore.load();
                }
            }
        });
        return _tempsPanel;
    };
    var templateActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Remove",
                handler: function () { 
                    var template_id = Ext.getCmp('templateGridPanel').getSelectionModel().getSelections()[0].data.template_id;
                    Ext.MessageBox.confirm('Confirm', 'Are you sure want to remove this?', function (btn, text) {
                                            if (btn == 'yes') {
                                                removeTemplate(template_id);
                                            }
                                        });
                }
            }]
    });
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    var fileS3BucketView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var uuid = uuidv4(); // for xxxx to field

        Ext.Ajax.request({
            url: modURL + '&op=get_file_s3_details',
            method: 'POST',
            params: {
                rid: uuid,
                apikey: _SESSION.apikey,
                tstamp: t_stamp

            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success !== undefined && tmp.success === true) {

                    Ext.getCmp('s3_accessKey').setValue(tmp.data.accessKey);
                    Ext.getCmp('s3_albumBucketName').setValue(tmp.data.albumBucketName);
                    Ext.getCmp('s3_secretKey').setValue(tmp.data.secretKey);
                    Ext.getCmp('s3_bucketRegion').setValue(tmp.data.bucketRegion);
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var variablesStore = function () {
        var _variablesStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getVariables',
            method: 'post',
            fields: ['variable_id', 'variable_name'],
            remoteSort: true
        });
        return _variablesStore;
    };
    var variablesForm = function (templateId) {
        var variableStore = variablesStore();
        var variablesForm = new Ext.Window({
            width: 250,
            height: 150,
            id: 'add_variableWindow',
            shadow: false,
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAddVariable',
                    title: 'Add Variables',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px"},
                    labelAlign: 'top',
                    items: [
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    columnWidth: '1.0',
                                    border: false,
                                    items: [
                                        {
                                            layout: 'form',
                                            columnWidth: 0.5,
                                            border: false,
                                            items: [
                                                {
                                                    xtype: 'lovcombo',
                                                    id: 'variables_id',
                                                    store: variableStore,
                                                    typeAhead: true,
                                                    triggerAction: 'all',
                                                    lazyRender: true,
                                                    allowBlank: false,
                                                    mode: 'local',
                                                    editable: true,
                                                    emptyText: 'Select',
                                                    displayField: 'variable_name',
                                                    valueField: 'variable_id',
                                                    hiddenName: 'variables_id',
                                                    fieldLabel: 'Variables',
                                                    name: 'variables_id',
                                                    anchor: '95%',
                                                    minChars: 1
                                                }
                                            ]
                                        }

                                    ]
                                }
                            ]


                        }
                    ]

                })
            ],
            fbar: [{
                    text: "Cancel",
                    anchor: '90%',
                    columnWidth: 0.1,
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    tabIndex: 33,
                    handler: function () {
                        variablesForm.close();
                    }
                },
                {
                    text: 'Save',
                    anchor: '95%',
                    columnWidth: 0.1,
                    bodyStyle: {'margin': '0px 0px 0px 140px'},
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    tabIndex: 32,
                    handler: function () {
                        Application.CRM_Template.variableAddition(templateId);
                    }
                }



            ]

        });
        variablesForm.doLayout();
        variablesForm.show();
        variablesForm.center();
        return variablesForm;
    }
    var templateform = function (template_id) {
        var variableStores = variablesStore();
        fileS3BucketView();
        var templateform = new Ext.Window({
            width: 700,
            autoheight: true,
            id: 'add_templateWindow',
            shadow: false,
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAddTemplate',
                    title: 'Create Template',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px"},
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            id: 's3_filename',
                            name: 's3_filename'
                        }, {
                            xtype: 'hidden',
                            id: 's3_albumBucketName',
                            name: 's3_albumBucketName'
                        }, {
                            xtype: 'hidden',
                            id: 's3_accessKey',
                            name: 's3_accessKey'
                        }, {
                            xtype: 'hidden',
                            id: 's3_secretKey',
                            name: 's3_secretKey'
                        }, {
                            xtype: 'hidden',
                            id: 's3_bucketRegion',
                            name: 's3_bucketRegion'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3_oncompleteurl',
                            name: 's3_oncompleteurl'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3_img_path_db',
                            name: 's3_img_path_db'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3filepath',
                            name: 's3filepath'
                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 1.0,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'template_id',
                                            name: 'template_id',
                                            hidden: true
                                        },
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Name',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'textfield',
                                            allowBlank: false,
                                            anchor: '99%',
                                            id: 'template_name',
                                            name: 'template_name',
                                            hideBorders: true,
                                            border: false,
                                        },
                                    ]
                                }

                            ]
                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: '0.5',
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            mode: 'local',
                                            id: 'campaign_type',
                                            name: 'campaign_type',
                                            allowBlank: false,
                                            forceSelection: true,
                                            fieldLabel: 'Campaign Type',
                                            emptyText: 'Campaign Type',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 101,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'Welcome', name: 'Welcome'}, {id: 'Follow Up', name: 'Follow Up'}]
                                            })
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.5,
                                    border: false,
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            mode: 'local',
                                            id: 'template_type',
                                            name: 'template_type',
                                            allowBlank: false,
                                            forceSelection: true,
                                            fieldLabel: 'Template Type',
                                            emptyText: 'Template Type',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 102,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'SMS', name: 'SMS'}, {id: 'Email', name: 'Email'}, {id: 'Voice', name: 'Voice'}]
                                            }),
                                            listeners: {
                                                select: function () {
                                                    if (Ext.getCmp('template_type').getValue() == 'SMS') {
                                                        Ext.getCmp('sms').show();
                                                        Ext.getCmp('email').hide();
                                                        Ext.getCmp('voice').hide();
                                                        Ext.getCmp('text_id').hide();
                                                    } else if (Ext.getCmp('template_type').getValue() == 'Email') {
                                                        Ext.getCmp('email').show();
                                                        Ext.getCmp('sms').hide();
                                                        Ext.getCmp('voice').hide();
                                                        Ext.getCmp('text_id').hide();
                                                    } else if (Ext.getCmp('template_type').getValue() == 'Voice') {
                                                        Ext.getCmp('voice').show();
                                                        Ext.getCmp('sms').hide();
                                                        Ext.getCmp('email').hide();
                                                        Ext.getCmp('text_id').show();
                                                    } else {
                                                        Ext.getCmp('voice').hide();
                                                        Ext.getCmp('sms').hide();
                                                        Ext.getCmp('email').hide();
                                                        Ext.getCmp('text_id').hide();
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: 1.0,
                                    labelAlign: 'left',
                                    labelWidth: 50,
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldLabel: '',
                                            id: 'var',
                                            name: 'var',
                                            value: 'Hint :Please use like this for adding variables in SMS/Email<br>Referer ID:[referer_Id] , Company Name:[company_name]  ',
//                                            Company URL : [company_url],Company Email:[company_email]
                                            anchor: '99%',
                                            tabIndex: 2
                                        }
                                    ]},
//                                {
//                                    layout: 'form',
//                                    columnWidth: 0.5,
//                                    border: false,
//                                    items: [
//                                        {
//                                                    xtype: 'lovcombo',
//                                                    id: 'variabless_id',
//                                                    store: variableStores,
//                                                    typeAhead: true,
//                                                    triggerAction: 'all',
//                                                    lazyRender: true,
//                                                     allowBlank: false,
//                                                    mode: 'local',
//                                                    editable: true,
//                                                    emptyText: 'Select',
//                                                    displayField: 'variable_name',
//                                                    valueField: 'variable_id',
//                                                    hiddenName: 'variabless_id',
//                                                    fieldLabel: 'Variables',
//                                                    name: 'variabless_id',
//                                                    anchor: '95%',
//                                                    minChars: 1
//                                                }
//                                    ]},
                                {
                                    layout: 'form',
                                    columnWidth: 1.0,
                                    border: false,
                                    items: [
                                        {
                                            fieldLabel: 'SMS',
                                            id: 'sms',
                                            name: 'sms',
                                            maxLength: 300,
                                            anchor: '98%',
                                            hidden: true,
                                            height: 200,
                                            xtype: 'textarea',
                                            tabIndex: 103
                                        },
                                        {
                                            xtype: 'templateeditormce',
                                            fieldLabel: 'Email ',
                                            id: 'email',
                                            name: 'email',
                                            anchor: '98%',
                                            height: 300,
                                            hidden: true,
                                            allowBlank: false,
                                            tabIndex: 103,
                                            maxLength: 900
                                        }
                                    ]
                                },
                                {
                                    columnWidth: .16,
                                    border: false,
                                    buttons: [
                                        {
                                            xtype: 'fileuploadfield',
                                            fieldLabel: 'Voice File',
                                            style: 'margin-bottom:5px;',
                                            id: 'voice',
                                            border: false,
                                            anchor: '97%',
                                            hidden: true,
                                            name: 'voice',
                                            allowBlank: true,
                                            buttonOnly: true,
                                            buttonCfg: {
                                                text: 'Upload File',
                                                border: false,
                                                width: 80
                                            },
                                            validator: function (v) {
                                                if (v != '') {
//                                                    v = v.toLowerCase();
//                                                    var exp = /^.*\.(png|jpg|gif|pdf|docx)$/i;
//                                                    if (!(exp.test(v))) {
//                                                        return 'Upload a valid file format.';
//                                                    }
                                                    var v = v.toLowerCase();
                                                    regex = new RegExp("(.*?)\.(mp3|mp4)$");
                                                    if (!(regex.test(v))) {
                                                        Ext.Msg.alert("Please select correct file format");
                                                        return;
                                                    }

                                                    var associated_file = Ext.getCmp('voice').getValue();
                                                    if (associated_file == '') {
                                                        Ext.Msg.alert("Notification", "Please choose a file to upload");
                                                        return;
                                                    }
                                                    addFile('D');
                                                    return true;
                                                }
                                            }
                                        }
                                    ]
                                }, {
                                    columnWidth: .5,
                                    layout: 'form',
                                    border: false,
                                    items: [{
                                            xtype: 'label',
                                            id: 'text_id',
                                            text: 'Please select MP3/MP4 file',
                                            style: 'padding-top:10px;',
                                            cls: 'my-label-style',
                                            hidden: true
                                        }]
                                },
                                {
                                    columnWidth: 1,
                                    layout: 'form',
                                    border: false,
                                    items: [{xtype: 'displayfield', id: 'supporting', cls: 'fpcls', value: 'File uploaded successfully', hidden: true}]
                                }]
                        }
                    ]

                })
            ],
            fbar: [{
                    text: "Cancel",
                    anchor: '90%',
                    columnWidth: 0.1,
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    tabIndex: 33,
                    handler: function () {
                        // Ext.getCmp('gridMarketingViewcontacts').getStore().load();
                        templateform.close();
                    }
                },
                {
                    text: 'Save',
                    anchor: '95%',
                    columnWidth: 0.1,
                    bodyStyle: {'margin': '0px 0px 0px 140px'},
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    tabIndex: 32,
                    handler: function () {

                        Application.CRM_Template.templateInsertion();
                    }
                }



            ]

        });
        templateform.doLayout();
        templateform.show();
        templateform.center();
        return templateform;
    }
    var addFile = function (type) {
        var albumBucketName = Ext.getCmp('s3_albumBucketName').getValue();
        var bucketRegion = Ext.getCmp('s3_bucketRegion').getValue();
        AWS.config.update({
            region: bucketRegion,
            credentials: new AWS.Credentials(
                    Ext.getCmp('s3_accessKey').getValue(),
                    Ext.getCmp('s3_secretKey').getValue(), null
                    )
        });
        var s3 = new AWS.S3({
            apiVersion: '2006-03-01',
            params: {Bucket: albumBucketName}
        });
        switch (type) {
            case 'D':
                var files = document.getElementById('voice-file').files;
                break;
        }

        if (!files.length) {

            return alert('Please choose a file to upload first.');
        }
        var file = files[0];
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split('.').pop();
        var fileName = uuidv4();
        fileName = fileName + '.' + fileExt;
        Ext.getCmp('s3_filename').setValue(fileName);
        s3.upload({
            Key: fileName,
            Body: file,
            ACL: 'public-read'
        }, function (err, data) {

            if (err) {

                var img_src = Ext.BLANK_IMAGE_URL;
                return Ext.Msg.alert("Notification", 'There was an error uploading file: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {


                Ext.MessageBox.show({
                    msg: 'Uploading, Please wait...',
                    progressText: 'Saving...',
                    width: 300,
                    wait: true,
                    waitConfig:
                            {
                                duration: 5000,
                                increment: 15,
                                text: 'Saving...',
                                scope: this,
                                fn: function () {
                                    Ext.MessageBox.hide();
                                    Application.example.msg("Notification", 'File uploaded successfully.');
                                    switch (type) {
                                        case 'D':
                                            Ext.getCmp('supporting').show();
                                            Ext.getCmp('supporting').disable();
                                            break;
                                    }

                                }
                            }
                });
                Ext.getCmp('s3filepath').setValue(data.Location);
                Application.CRM_Template.UploadedFileLocationforLead = data.Location;
            }
        });
    };
    var templateDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateTemplateViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Template Name </th><td>  {template_name} </td></tr>',
                    '<tr><th width="40%">Campaign Type </th><td>  {campaign_type} </td></tr>',
                    '<tr><th width="40%">Template Type </th><td>  {template_type} </td></tr>',
                    '<tr><th width="40%">Template </th><td>  {template_sms} </td></tr>',
//                    '<tr><th width="40%">Refered By </th><td>  {reference} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var templatePanel = function (id) {
        var _tempPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Templates',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                templateGrid(),
                new Ext.Panel({
                    title: 'Templates',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'templatesPanel',
                    height: winsize.height * 0.6,
                    items: [
                        templateDetailsView()
                    ]
                })
            ]
        });
        return _tempPanel;
    };
    return {
        Cache: {},
        initTemplate: function () {
            var _templatePanelId = 'panelTemplate';
            var _templatePanel = Ext.getCmp(_templatePanelId);
            if (Ext.isEmpty(_templatePanel)) {
                _templatePanel = templatePanel(_templatePanelId);
                Application.UI.addTab(_templatePanel);
                _templatePanel.doLayout();
            } else {
                Application.UI.addTab(_templatePanel);
            }
        },
        templateInsertion: function () {
            $template_id = 0;
            if (Ext.getCmp('formpanelAddTemplate').getForm().isValid()) {
                Ext.Ajax.request({
                    url: modURL + '&op=insertTemplate',
                    method: 'POST',
                    params: {
                        template_name: Ext.getCmp('template_name').getValue(),
                        campaign_type: Ext.getCmp('campaign_type').getValue(),
                        template_type: Ext.getCmp('template_type').getValue(),
//                        variabless_id: Ext.getCmp('variabless_id').getValue(),
                        email: Ext.getCmp('email').getValue(),
                        voice: Ext.getCmp('voice').getValue(),
                        sms: Ext.getCmp('sms').getValue()
                    },
                    success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', tmp.msg);
                            Ext.getCmp('add_templateWindow').close();
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('templateGridPanel'))
                            Ext.getCmp('templateGridPanel').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                        } else {
                            Ext.Msg.alert("Error", tmp.msg);
                        }
                    },
                    failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert('Error', tmp.msg);
                    }

                })

            } else {
                Ext.MessageBox.alert("Notification", 'Please fill all Mandatory fields');
            }

        },
        variableAddition: function () {
            var templateId = arguments[0];
            if (Ext.getCmp('formpanelAddVariable').getForm().isValid()) {
                Ext.Ajax.request({
                    url: modURL + '&op=insertVariable',
                    method: 'POST',
                    params: {
                        templateId: templateId,
                        variables_id: Ext.getCmp('variables_id').getValue(),
                    },
                    success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', tmp.msg);
                            Ext.getCmp('add_variableWindow').close();
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('templateGridPanel'))
                            Ext.getCmp('templateGridPanel').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                        } else {
                            Ext.Msg.alert("Error", tmp.msg);
                        }
                    },
                    failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert('Error', tmp.msg);
                    }

                })

            }

        },
        ViewTemplateMode: function () {
            var template_id = arguments[0];
            Ext.getCmp('xtemplateTemplateViewDetails').show();
            Ext.getCmp('templatesPanel').show();
            Ext.getCmp('templatesPanel').doLayout();
            Ext.getCmp('templatesPanel').setTitle('Details');
            Ext.Ajax.request({
                url: modURL + '&op=templateDetailsView',
                method: 'POST',
                params: {
                    template_id: template_id
                },
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateTemplateViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('templatesPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('templatesPanel').doLayout();
        }
    }
}();