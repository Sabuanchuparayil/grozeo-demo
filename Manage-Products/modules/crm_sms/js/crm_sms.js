Application.CRM_SMS = function () {
    var winLoadMask;
    var RECS_PER_PAGE = 12;
    var modURL = '?module=crm_sms';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionSMSChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('smsGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('smsGridPanel').getSelectionModel().getSelections()[0].data.campaign_id;
            Application.CRM_SMS.ViewSmsMode(ID);
        }
    };

    var sms_name = new Array();
    var email_name = new Array();
    var smsPanel = function (id) {
        var _smsCampPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Campaigns',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                smsGrid(),
                new Ext.Panel({
                    title: 'Campaigns',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'smsPanel',
                    height: winsize.height * 0.6,
                    items: [
                        smsDetailsView()
                    ]
                })
            ]
        });
        return _smsCampPanel;
    };
    var smsGridstore = function () {
        var _smsList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCampaigns',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'campaign_id',
                root: 'data'
            }, ['campaign_name', 'campaign_id', 'campaign_type', 'campaign_count', 'campaign_startedOn']),
            sortInfo: {
                field: 'campaign_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('smsGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _smsList;
    };
    var smsDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplatesmsViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {campaign_name} </td></tr>',
                    '<tr><th width="40%">Type </th><td>  {campaign_type} </td></tr>',
                    '<tr><th width="40%">Template </th><td>  {campaign_templateId} </td></tr>',
                    '<tr><th width="40%">Count </th><td>  {campaign_count} </td></tr>',
                    '<tr><th width="40%">Date</th><td>  {campaign_startedOn} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var templateStores = function () {
        var _templateStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getTemplates',
            method: 'post',
            fields: ['template_id', 'template_name'],
            remoteSort: true
        });
        return _templateStore;
    };
    var referenceStores = function () {
        var _referenceStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getReference',
            method: 'post',
            fields: ['referers_id', 'referers_contact_id'],
            remoteSort: true
        });
        return _referenceStore;
    };
    var testform = function (type) {
        var templatesStore = templateStores();
        var testform = new Ext.Window({
            width: 400,
            height: 250,
            id: 'add_testWindow',
            shadow: false,
            resizable: false,
            plain: true,
            title: 'Test Campaign',
            constrain: true,
            draggable: true,
            modal: true,
            closable: true,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAddTestTemplate',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px", height: 500},
                    labelAlign: 'top',
                    items: [{
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: '1.0',
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            mode: 'local',
                                            id: 'smss_type',
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
                                                select: function (combo, record, index) {
                                                    var type = this.value;
                                                    if (type == 'SMS') {
                                                        Ext.getCmp('test_number').show();
                                                        Ext.getCmp('test_email').hide();
                                                    }
                                                    else if (type == 'Email') {
                                                        Ext.getCmp('test_email').show();
                                                        Ext.getCmp('test_number').hide();
                                                    }
                                                    Ext.getCmp('smss_temp').setValue('');
                                                    templatesStore.baseParams.templates_type = this.value;
                                                    templatesStore.load();
                                                }
                                            }
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 1.0,
                                    items: [
                                        {
                                            xtype: 'combo',
                                            displayField: 'template_name',
                                            valueField: 'template_id',
                                            mode: 'local',
                                            id: 'smss_temp',
                                            forceSelection: true,
                                            fieldLabel: 'Campaign Template',
                                            emptyText: 'Templates',
                                            anchor: '99%',
                                            allowBlank: false,
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 101,
                                            store: templatesStore
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 1.0,
                                    items: [
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Enter Mobile Number',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'numberfield',
                                            hidden: true,
                                            anchor: '99%',
                                            id: 'test_number',
                                            name: 'test_number',
                                            hideBorders: true,
                                            border: false,
                                        },
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Enter Email Address',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'textfield',
                                            vtype: 'email',
                                            anchor: '99%',
                                            hidden: true,
                                            id: 'test_email',
                                            name: 'test_email',
                                            hideBorders: true,
                                            border: false,
                                        }
                                    ]
                                }

                            ]
                        }
                    ]

                })
            ],
            fbar: [{
                    text: 'Start Test',
                    //iconCls: 'my-icon38',
                    handler: function () {
                        Ext.Ajax.request({
                            url: modURL + '&op=sendTestCampaign',
                            method: 'POST',
                            params: {
                                mob_num: Ext.getCmp('test_number').getValue(),
                                email: Ext.getCmp('test_email').getValue(),
                                type: Ext.getCmp('smss_type').getValue(),
                                template: Ext.getCmp('smss_temp').getValue()
                            },
                            success: function (response) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.success == false) {
                                    Ext.MessageBox.alert('Notification', tmp.msg);
                                }
                                else {
                                    Application.example.msg('Success', tmp.msg);
                                    Ext.getCmp('gidRefsCharge').getStore().load({
                                        baseParams: {"refrence_referers_id": Application.CRM_SMS.Cache.referers_id}
                                    });
                                    Ext.getCmp('add_testWindow').close();
                                    Ext.getCmp('add_smsWindow').close();
                                }
                            },
                            failure: function (response, options) {
                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                            }
                        });
//                        }
//                        else {
//                            Ext.MessageBox.alert('Notification', 'Please select a value');
//                        }
                    }
                }


            ]

        });

        testform.doLayout();
        testform.show();
        testform.center();
        return testform;
    }
    var smsform = function (template_id) {
        var templateStore = templateStores();
        var referenceStore = referenceStores();
        var smsform = new Ext.Window({
            width: 900,
            autoheight: true,
            id: 'add_smsWindow',
            shadow: false,
            resizable: false,
            plain: true,
            title: 'Create Campaign',
            constrain: true,
            draggable: true,
            modal: true,
            closable: true,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAddSMSTemplate',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px", height: 500},
                    labelAlign: 'top',
                    items: [{
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 0.33,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'template_sms_id',
                                            hidden: true
                                        },
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Campaign Name',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'sms_name',
                                            allowBlank: false,
                                            name: 'sms_name',
                                            hideBorders: true,
                                            border: false,
                                        },
                                    ]
                                }, {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: '0.33',
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            mode: 'local',
                                            id: 'templates_type',
                                            forceSelection: true,
                                            allowBlank: false,
                                            fieldLabel: 'Template Type',
                                            emptyText: 'Template Type',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 101,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'SMS', name: 'SMS'}, {id: 'Email', name: 'Email'}, {id: 'Voice', name: 'Voice'}]
                                            }),
                                            listeners: {
                                                select: function (combo, record, index) {
                                                    Ext.getCmp('sms_type').setValue('');
                                                    templateStore.baseParams.templates_type = this.value;
                                                    templateStore.load();
                                                }
                                            }
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 0.33,
                                    items: [
                                        {
                                            xtype: 'combo',
                                            displayField: 'template_name',
                                            valueField: 'template_id',
                                            mode: 'local',
                                            id: 'sms_type',
                                            forceSelection: true,
                                            fieldLabel: 'Campaign Template',
                                            emptyText: 'Templates',
                                            anchor: '99%',
                                            allowBlank: false,
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 102,
                                            store: templateStore
                                        }
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
                                    columnWidth: 0.33,
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'referers_contact_id',
                                            valueField: 'referers_id',
                                            mode: 'local',
                                            id: 'sms_recepients',
                                            forceSelection: true,
                                            fieldLabel: 'Referers',
                                            emptyText: 'Referers',
                                            anchor: '99%',
                                            typeAhead: true,
                                            allowBlank: false,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 103,
                                            store: referenceStore,
                                            listeners: {
                                                select: function (combo, record, index) {
                                                    Application.CRM_SMS.Cache.referers_id = record.data.referers_id;

                                                }
                                            }
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: '0.25',
                                    items: [{
                                            text: "Select Recepients",
                                            style: 'margin:19px 0px 22px 13px',
                                            anchor: '90%',
                                            columnWidth: 0.1,
                                            xtype: 'button',
                                            tabIndex: 104,
                                            handler: function () {
                                                var ID = Ext.getCmp('sms_recepients').getValue();
//                                                Application.CRM_SMS.winMapSmsReferers(ID);
                                                Ext.getCmp('gidRefsCharge').getStore().load({
                                                    baseParams: {"refrence_referers_id": ID}
                                                });
                                            }
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: '0.25',
                                    items: [{
                                            xtype: 'button',
                                            style: 'margin:19px 8px 0px 0px',
                                            iconCls: 'csv',
                                            anchor: '90%',
                                            tabIndex: 105,
                                            text: 'Import',
                                            handler: function () {
                                                csv_upload();

                                            }
                                        }
                                    ]
                                }]
                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: '1.0',
                                    items: [{
                                            layout: 'form',
                                            style: 'margin-bottom:3px;',
                                            columnWidth: 1,
                                            items: [
                                                creatMapRefererGrid(Application.CRM_SMS.Cache.referers_id)
                                            ]
                                        }
                                    ]
                                }],
                        }
                    ]

                })
            ],
            fbar: [
//                {
//                    text: "Close",
//                    anchor: '90%',
//                    columnWidth: 0.1,
//                    xtype: 'button',
//                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
//                    tabIndex: 33,
//                    handler: function () {
//                        // Ext.getCmp('gridMarketingViewcontacts').getStore().load();
//                        smsform.close();
//                    }
//                },
                {
                    //icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Delete from list',
                    tabIndex: 106,
                    handler: function () {
                        var store_fields = Ext.getCmp('gidRefsCharge').getSelectionModel().getSelections();
                        console.log('store_fields--', store_fields);
                        var mob_num = Array();
                        if (store_fields.length > 0) {
                            sms_name = [];
                            mob_num = [];
                            email_name = [];
                            for (var i = 0; i < store_fields.length; i++) {
                                sms_name[i] = store_fields[i].data.reference_cl_id;
                                mob_num[i] = store_fields[i].data.contactNumber;
                                email_name[i] = store_fields[i].data.contactEmail;
                            }
                            Ext.Ajax.request({
                                url: modURL + '&op=removeCampaign',
                                method: 'POST',
                                params: {
                                    refIds: Ext.encode(sms_name),
                                    mob_num: Ext.encode(mob_num),
                                    email_name: Ext.encode(email_name),
                                    referers_id: Application.CRM_SMS.Cache.referers_id
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == false) {
                                        Ext.MessageBox.alert('Notification', tmp.msg);
                                    }
                                    else {
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp('gidRefsCharge').getStore().load({
                                            baseParams: {"refrence_referers_id": Application.CRM_SMS.Cache.referers_id}
                                        });
                                        Ext.getCmp('add_smsWindow').close();
                                    }
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });
                        }
                        else {
                            Ext.MessageBox.alert('Notification', 'Please select a value');
                        }
                    }

                }, {
                    text: "Test Campaign",
                    anchor: '90%',
                    columnWidth: 0.1,
                    xtype: 'button',
//                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    tabIndex: 107,
                    handler: function () {
                        if (Ext.getCmp('sms_type').getValue() != '' && Ext.getCmp('templates_type').getValue() != '') {
                            var type = Ext.getCmp('templates_type').getValue();
                            testform(type);
                        }
                        else {
                            Ext.MessageBox.alert('Notification', 'Please select template type and template to proceed');
                        }
                    }
                }, {
                    text: 'Start Campaign',
                    tabIndex: 108,
                    //iconCls: 'my-icon38',
                    handler: function () {
                        var store_fields = Ext.getCmp('gidRefsCharge').getSelectionModel().getSelections();
                        console.log('store_fields--', store_fields);
                        var mob_num = Array();
                        if (store_fields.length > 0) {
                            sms_name = [];
                            mob_num = [];
                            email_name = [];
                            for (var i = 0; i < store_fields.length; i++) {
                                sms_name[i] = store_fields[i].data.reference_cl_id;
                                mob_num[i] = store_fields[i].data.contactNumber;
                                email_name[i] = store_fields[i].data.contactEmail;
                            }
                            Ext.Ajax.request({
                                url: modURL + '&op=sendCampaign',
                                method: 'POST',
                                params: {
                                    refIds: Ext.encode(sms_name),
                                    mob_num: Ext.encode(mob_num),
                                    type: Ext.getCmp('templates_type').getValue(),
                                    template: Ext.getCmp('sms_type').getValue(),
                                    camp_name: Ext.getCmp('sms_name').getValue(),
                                    email_name: Ext.encode(email_name),
                                    referers_id: Application.CRM_SMS.Cache.referers_id
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == false) {
                                        Ext.MessageBox.alert('Notification', tmp.msg);
                                    }
                                    else {
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp('gidRefsCharge').getStore().load({
                                            baseParams: {"refrence_referers_id": Application.CRM_SMS.Cache.referers_id}
                                        });
                                        Ext.getCmp('add_smsWindow').close();
                                    }
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });
                        }
                        else {
                            Ext.MessageBox.alert('Notification', 'Please select a value');
                        }
                    }
                }


            ]

        });

        smsform.doLayout();
        smsform.show();
        smsform.center();
        return smsform;
    }
    var csv_upload = function () {
        var refererStore = referenceStores();
        var winRef_csv = new Ext.Window({
            layout: 'fit',
            width: 400,
            autoHeight: true,
            border: false,
            title: 'Upload File',
            icon: './resources/images/submenuicons/upload_fl.png',
            //iconCls: 'upload',
            shadow: false,
            floating: true,
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            items: [
                new Ext.form.FormPanel({
                    labelAlign: 'top',
                    labelSeparator: '',
                    bodyStyle: {
                        "background-color": "white",
                        "padding": "5px 5px 5px 10px"
                    },
                    autoHeight: true,
                    id: 'csv_ref_form',
                    frame: false,
                    border: false,
                    fileUpload: true,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .3,
                            items: [
                                {
                                    fieldLabel: 'Upload File',
                                    labelAlign: 'top',
                                    xtype: 'fileuploadfield',
                                    accept: '.csv',
                                    id: 'excel_ref_file',
                                    allowBlank: false,
                                    name: 'excel_ref_file',
                                    tabIndex: 1,
                                    msgTarget: 'under',
                                    anchor: '98%',
                                    validator: function (v) {
                                        if (v != '') {
                                            //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                                            var exp = /^.*\.csv$/;
                                            if (!(exp.test(v))) {
                                                return 'Upload a valid CSV file.';
                                            }
                                            return true;
                                        }
                                        var filen = Ext.getCmp('excel_ref_file').getValue();
                                        if (filen == '') {
                                            return 'Upload a file.';
                                        }
                                    }
                                }],
                            buttons: [
                                {
                                    iconCls: 'upload',
                                    text: 'Upload',
                                    handler: function () {
                                        var csv_form = Ext.getCmp('csv_ref_form').getForm();
                                        var referers_ids = Ext.getCmp('sms_recepients').getValue();
                                        var t = new Date();
                                        var t_stamp = t.format("YmdHis");
                                        if (csv_form.isValid()) {
                                            csv_form.submit({
                                                url: modURL + '&op=uploadcsvFile',
                                                waitTitle: 'Please Wait..',
                                                waitMsg: 'Saving data...',
                                                params: {
                                                    referers_ids: referers_ids,
                                                    apikey: _SESSION.apikey,
                                                    tstamp: t_stamp
                                                },
                                                success: function (csv_form, action) {
                                                    var result = Ext.decode(action.response.responseText);
                                                    if (result.valid === true && result.success === true) {
                                                        winRef_csv.close();
                                                        Ext.getCmp('gidRefsCharge').getStore().load({
                                                            baseParams: {"refrence_referers_id": Ext.getCmp('sms_recepients').getValue()}
                                                        });
                                                        Application.example.msg('Notification', 'Details saved Successfully');

                                                    }
                                                },
                                                failure: function () {
                                                    Ext.Msg.alert('Error', 'Supplied CSV File could not be validated. ', function (btn) {
                                                        if (btn === 'ok') {
                                                            Ext.Msg.hide();
                                                            winRef_csv.close();
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    }
                                }]
                        }
                    ]
                })
            ]
        });
        winRef_csv.show();
        winRef_csv.doLayout();
        winRef_csv.center();
    }
    var smsGrid = function () {
        var _smsGridstore = smsGridstore();
        var _smsFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'campaign_name'
                },
                {
                    type: 'string',
                    dataIndex: 'campaign_type'
                },
                {
                    type: 'string',
                    dataIndex: 'campaign_count'
                },
                {
                    type: 'date',
                    dataIndex: 'campaign_startedOn'
                }
            ]
        });
        _smsFilter.remote = true;
        _smsFilter.autoReload = true;
        var _smsCampPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _smsGridstore,
            //iconCls: 'money',
            id: 'smsGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _smsFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Campaign Name',
                    dataIndex: 'campaign_name',
                    sortable: true,
                    tooltip: 'Campaign Name',
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
                    header: 'Campaign Count',
                    dataIndex: 'campaign_count',
                    sortable: true,
                    tooltip: 'Campaign Count',
                    hideable: true
                },
                {
                    header: 'Campaign Date',
                    dataIndex: 'campaign_startedOn',
                    sortable: true,
                    tooltip: 'Campaign Date',
                    hideable: true
                }
            ],
            tbar: [{
                    xtype: 'button',
                    text: 'Create Campaign',
                    tooltip: 'Create Campaign',
                    iconCls: 'finascop_add',
                    handler: function () {
                        smsform();
                    }
                }],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _smsGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionSMSChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('referers_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.CRM_SMS.Cache.referers_id = ID;
                        //Ext.getCmp('formpanelcampaigns').hide();
                        Application.CRM_SMS.ViewSmsMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _smsGridstore.load();
                }
            }
        });
        return _smsCampPanel;
    };
    var chargeStore = function (refrence_referers_id) {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=MapReference',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'id',
                root: 'data'
            }, ['refrence_referers_id', 'contactPerson', 'checkeds', 'itemcount', 'contactNumber', 'reference_cl_id', 'reference_id','contactEmail']),
            sortInfo: {
                field: 'reference_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: false,
            root: 'data',
            remoteSort: true,
            listeners: {
                beforeload: function (store, e) {
//                    this.baseParams.refrence_referers_id = refrence_referers_id;
                    this.baseParams.refrence_referers_id = Ext.getCmp('sms_recepients').getValue();
                }
            }
        });
        return store;
    };
    var creatMapRefererGrid = function (id) {
        var refId = arguments[0];
        var grid_title = 'Reference';
        var chk_model = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    console.log(record);
                    var ind = sms_name.indexOf(record.get('reference_cl_id'));
                    if (ind > -1)
                        sms_name.splice(ind, 1);
                    record.set('checked', 'false');
                },
                rowselect: function (sm, rowIndex, record) {
                    console.log(record);
                    var ind = sms_name.indexOf(record.get('reference_cl_id'));
                    if (ind == -1)
                        sms_name.push(record.get('reference_cl_id'));
                    record.set('checked', 'true');
                }
            }
        });
        var charge_store = chargeStore(refId);
        var charge_grid = new Ext.grid.GridPanel({
            store: charge_store,
            layout: 'fit',
            title: grid_title,
            autoScroll: true,
            height: 400,
            selModel: chk_model,
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            id: 'gidRefsCharge',
            clicksToEdit: '2',
            columns: [chk_model, {
                    header: '',
                    hidden: true,
                    dataIndex: 'refrence_referers_id',
                    tooltip: 'Main Type'
                },
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'contactPerson',
                    tooltip: 'Name'
                },
                {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'contactNumber',
                    tooltip: 'Mobile'
                },
                {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'contactEmail',
                    tooltip: 'Email'
                }
            ],
            sm: chk_model,
            viewConfig: {
                forceFit: true
            },
            view: new Ext.grid.GroupingView({
                forceFit: true,
                showGroupName: false,
                enableNoGroups: false,
                enableGroupingMenu: false,
                hideGroupedColumn: false,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    switch (record.get('checkeds')) {
                        case '0':
                            return '';
                            break;
                        case '1':
                            return 'finascop_indicateColPINK';
                            break;
                    }

                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            }),
                    listeners: {
                        afterrender: function () {
                            {
                                var me = this;
                                charge_store.on('load', function () {
                                    var data = me.getStore().data.items;
                                    var recs = [];
                                    Ext.each(data, function (item, index) {
                                        if (item.data.checked == 1) {
                                            recs.push(index);
                                        }
                                    });
                                    me.getSelectionModel().selectRows(recs);
                                });
                            }
                        }
                    },
            stripeRows: true,
            autoExpandColumn: 'contactPerson'

        });
        return charge_grid;
    };
    return {
        Cache: {},
        initSMS: function () {
            var _smsPanelId = 'panelSMS';
            var _smsPanel = Ext.getCmp(_smsPanelId);
            if (Ext.isEmpty(_smsPanel)) {
                _smsPanel = smsPanel(_smsPanelId);
                Application.UI.addTab(_smsPanel);
                _smsPanel.doLayout();
            } else {
                Application.UI.addTab(_smsPanel);
            }
        },
        ViewSmsMode: function () {
            var campaign_id = arguments[0];
            Ext.getCmp('smsPanel').setTitle('View Campaign Details');
            Ext.getCmp('xtemplatesmsViewDetails').show();
            Ext.getCmp('smsPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=campaignsDetailsView',
                method: 'POST',
                params: {campaign_id: campaign_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplatesmsViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('smsPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('smsPanel').doLayout();
        },
        winMapSmsReferers: function () {
            Application.CRM_SMS.Cache.referers_id = arguments[0];
            var wbGrid = creatMapRefererGrid(Application.CRM_SMS.Cache.referers_id);
//            Application.VellnezLabPackages.EditPackageForm(Application.VellnezLabPackages.Cache.packageId);
            var resultWindow = new Ext.Window({
                id: "windowForReference",
                iconCls: 'vender-items',
                shadow: false,
                height: 600,
                width: 900,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [wbGrid],
                buttons: [
                    {
                        //icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowForReference').close();
                        }

                    }, {
                        //icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Remove',
                        handler: function () {
                            var store_fields = Ext.getCmp('gidRefsCharge').getSelectionModel().getSelections();
                            console.log('store_fields--', store_fields);
                            var mob_num = Array();
                            if (store_fields.length > 0) {
                                sms_name = [];
                                mob_num = [];
                                for (var i = 0; i < store_fields.length; i++) {
                                    sms_name[i] = store_fields[i].data.reference_cl_id;
                                    mob_num[i] = store_fields[i].data.contactNumber;
                                }
                                Ext.Ajax.request({
                                    url: modURL + '&op=removeCampaign',
                                    method: 'POST',
                                    params: {
                                        refIds: Ext.encode(sms_name),
                                        mob_num: Ext.encode(mob_num),
                                        referers_id: Ext.getCmp('sms_recepients').getValue()
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == false) {
                                            Ext.MessageBox.alert('Notification', tmp.msg);
                                        }
                                        else {
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('smsGridPanel').getStore().load({
                                                baseParams: {"referers_id": Ext.getCmp('sms_recepients').getValue()}
                                            });
                                            Ext.getCmp('smsGridPanel').getStore().load();
                                            Ext.getCmp('add_smsWindow').close();
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            }
                            else {
                                Ext.MessageBox.alert('Notification', 'Please select a value');
                            }
                        }

                    }, {
                        text: 'Start Campaign',
                        //iconCls: 'my-icon38',
                        handler: function () {
                            var store_fields = Ext.getCmp('gidRefsCharge').getSelectionModel().getSelections();
                            console.log('store_fields--', store_fields);
                            var mob_num = Array();
                            if (store_fields.length > 0) {
                                sms_name = [];
                                mob_num = [];
                                for (var i = 0; i < store_fields.length; i++) {
                                    sms_name[i] = store_fields[i].data.reference_cl_id;
                                    mob_num[i] = store_fields[i].data.contactNumber;
                                }
                                Ext.Ajax.request({
                                    url: modURL + '&op=sendCampaign',
                                    method: 'POST',
                                    params: {
                                        refIds: Ext.encode(sms_name),
                                        mob_num: Ext.encode(mob_num),
                                        referers_id: Ext.getCmp('sms_recepients').getValue()
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == false) {
                                            Ext.MessageBox.alert('Notification', tmp.msg);
                                        }
                                        else {
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('smsGridPanel').getStore().load({
                                                baseParams: {"referers_id": Ext.getCmp('sms_recepients').getValue()}
                                            });
                                            Ext.getCmp('add_smsWindow').close();
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            }
                            else {
                                Ext.MessageBox.alert('Notification', 'Please select a value');
                            }
                        }
                    }
                ]
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();
            Ext.getCmp('gidRefsCharge').getStore().load({
                baseParams: {"refrence_referers_id": Application.CRM_SMS.Cache.referers_id}
            });
        }
    }
}();