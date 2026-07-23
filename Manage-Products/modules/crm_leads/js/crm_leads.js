Application.Crm_FLead = function () {

    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=crm_leads';
    var recs_per_page = 16;
    var WinMask;
    var imgpath = IMAGE_BASE_PATH;

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    ;
    var crmLeadGridStore = function () {
        return new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getLeadDetails',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: '',
                root: 'data'
            },
            ['crle_id', 'lead_name', 'email', 'mobile', 'STATUS', 'TYPE', 'crle_isActive', 'ABO']
                    ),
            groupField: '',
            sortInfo: {
                field: 'lead_name',
                direction: 'ASC'
            },
            root: 'data',
            autoLoad: true,
        //    remoteSort: true,
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0)
                    {
                        Ext.getCmp('gridMarketingLeadsList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
    };
    var crmLeadsCommunicationPanel = function () {
        var _communicationPanel = new Ext.Panel({
            region: 'center',
            id: 'LeadsPanelCommunicationId',
            height: winsize.height * 0.55,
            tpl: new Ext.XTemplate(
                    '<div class="cdetails-outer no-border" style="width:100%; margin:15px auto;"><h3 class="history"></h3><ul class="anexure">',
                    '<tpl for=".">',
                    '<div class="details-outer">',
                    '<table style="width:100%;">',
                    '<tr>',
                    '<td ><span class="field" style="color:grey;font-weight:bold"><img src = {calender}></span><span class="crmdate">{date_and_time}</span></td>',
                    '<td ><span class="field" style="color:grey;font-weight:bold;align:right;"><img src={crmm_name}></span><span class="crmname" >{resource}</span></td>',
                    '</tr>',
                    '<tpl if="remark != \'\'">',
                    '<tr>',
                    '<td colspan="2" style= "padding-top : 5px">',
                    '<span span class="crmtext">{remark}</span>',
                    '</td>',
                    '</tr>',
                    '</tpl>',
                    '<tr>',
                    '<td style= "padding-top : 5px"><span class="crmresponse">{response} {crsc_ScheduleDate}</span> </td>',
                    '</tr>',
                    '<td colspan="2">',
                    '<hr></td></tr>',
                    '</table>',
                    '</div></li>',
                    '</tpl>',
                    '</ul></div>',
                    '<style>.field{ padding-right: 10px; }</style>'
                    )
        });
        return _communicationPanel;
    };


    var htmlpanelfun = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '?module=crm_leads&op=loadEditLeadData&crle_id=' + Application.Crm_FLead.Cache.crle_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var _htmlpanel = new Ext.Panel({
            id: 'htmlid',
            region: 'center',
            frame: false,
            border: false,
            bodyStyle: {"background-color": "white"},
            width: winsize.width * 0.39,
            cls: 'left_side_panel',
            items: [{
                    html: '<iframe id="downloadIframelead" name="downloadIframelead" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' + src + '"; ></iframe>'
                }]
        });
        return _htmlpanel;
    };
    var panelLead = function () {

        var panel = new Ext.TabPanel({
            id: 'tabpanelMarketingLead',
            region: 'east',
            frame: false,
            activeTab: 0,
            tabPosition: 'top',
            border: true,
            bodyStyle: {
                "background-color": "white"
            },
            width: winsize.width * 0.4,
            defaults: {
                layout: 'fit',
                frame: false
            },
            items: [{
                    title: 'Lead Details',
                    id: 'tabpanelMarketingLeadAddlead',
                    layout: 'border',
                    items: [htmlpanelfun()]
                }
            ],
            listeners: {
                'afterrender': function (component) {
                    Ext.getCmp('gridMarketingLeadsList').getSelectionModel().selectRow(0);
                    var _tabpanel = Ext.getCmp('tabpanelMarketingLead');
                    _tabpanel.setActiveTab(0);
                    Ext.getCmp('htmlid').show();

                },
                tabchange: function (s, tab) {
                    var _current = Application.Crm_FLead.Cache.status;
                    switch (tab.title)
                    {
                        case 'Lead Details':
                            if (_current == 'UnAttended')
                            {
                                Ext.getCmp('htmlid').show();
                            } else
                            {
                                Ext.getCmp('htmlid').show();
                            }
                            break;
                    }
                }
            }
        });
        return panel;
    };

    var marketingLeadsPanel = function (id) {
        var _leadPanel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            title: 'Leads',
            hideBorders: true,
            id: id,
            items: [crmLeadGrid(), panelLead()]
        });
        return _leadPanel;
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
//    var referenceStores = function () {
//        var _referenceStore = new Ext.data.JsonStore({
//            autoLoad: true,
//            url: modURL + '&op=getReferences',
//            method: 'post',
//            fields: ['referers_ids', 'referers_contact_ids'],
//            remoteSort: true
//        });
//        return _referenceStore;
//
//    };
    var csv_upload = function () {
        var refererStore = referenceStores();
        var win_csv = new Ext.Window({
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
                    id: 'csv_form',
                    frame: false,
                    border: false,
                    fileUpload: true,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .3,
                            items: [{
                                    xtype: 'combo',
                                    mode: 'local',
                                    fieldLabel: 'Referer',
                                    id: 'referers_ids',
                                    displayField: 'referers_contact_id',
                                    valueField: 'referers_id',
                                    hiddenName: 'referers_id',
                                    allowBlank: false,
                                    anchor: '98%',
                                    tabIndex: 9,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    store: refererStore
                                },
                                {
                                    fieldLabel: 'Upload File',
                                    labelAlign: 'top',
                                    xtype: 'fileuploadfield',
                                    accept: '.csv',
                                    id: 'excel_file',
                                    allowBlank: false,
                                    name: 'excel_file',
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
                                        var filen = Ext.getCmp('excel_file').getValue();
                                        if (filen == '') {
                                            return 'Upload a file.';
                                        }
                                    }
                                }],
                            buttons: [
                                {
                                    //iconCls: 'csv',
                                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                                    text: 'Upload',
                                    handler: function () {
                                        var csv_form = Ext.getCmp('csv_form').getForm();
                                        var referers_ids = Ext.getCmp('referers_ids').getValue();
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
                                                        win_csv.close();
                                                        Application.example.msg('Notification', 'Details saved Successfully');
                                                    }
                                                },
                                                failure: function () {
                                                    Ext.Msg.alert('Error', 'Supplied CSV File could not be validated. ', function (btn) {
                                                        if (btn === 'ok') {
                                                            Ext.Msg.hide();
                                                            win_csv.close();
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
        win_csv.show();
        win_csv.doLayout();
        win_csv.center();
    }
    var leadform = function (contactid) {
        var referenceStore = referenceStores();
        var leadform = new Ext.Window({
            width: 500,
            height: 230,
            id: 'add_leadWindow',
            shadow: false,
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAddLead',
                    title: !Ext.isEmpty(contactid)?'Edit Lead':'Create Lead',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px"},
                    labelAlign: 'top',
                    items: [
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 1.0,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'lead_customerId',
                                            hidden: true
                                        },
                                        {
                                            xtype: 'textfield',
                                            id: 'statusABOhidden',
                                            hidden: true
                                        },
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Name',
                                            maxLength: 250,
                                            tabIndex: 600,
                                            xtype: 'textfield',
                                            anchor: '95%',
                                            allowBlank: false,
                                            id: 'textfieldMarketingLeadIndividualContactPerson',
                                            name: 'textfieldMarketingLeadIndividualContactPerson',
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
                                    columnWidth: 1.0,
                                    border: false,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Mobile',
                                            tabIndex: 601,
                                            id: 'numberfieldMarketingLeadindividualPrimarymobile',
                                            name: 'numberfieldMarketingLeadindividualPrimarymobile',
                                            anchor: '95%',
                                            minLength: 10,
                                            maxLength: 10,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            maxLengthText: "Maximum length for the field is 12",
                                            minLengthText: "Minimum length for the field is 10"
                                        }
                                    ]

                                }
//                                {
//                                    layout: 'form',
//                                    columnWidth: .25,
//                                    border: false,
//                                    items: [
//                                        
//                                    ]
//                                },
//                                {
//                                    layout: 'form',
//                                    columnWidth: 0.5,
//                                    border: false,
//                                    items: [
//                                        {
//                                            xtype: 'lovcombo',
//                                            id: 'referers_id',
//                                            store: referenceStore,
//                                            typeAhead: true,
//                                            triggerAction: 'all',
//                                            lazyRender: true,
//                                            allowblank: false,
//                                            mode: 'local',
//                                            editable: true,
//                                            emptyText: 'Select',
////                                            hidden: true,
//                                            displayField: 'referers_contact_id',
//                                            valueField: 'referers_id',
//                                            hiddenName: 'referers_id',
//                                            fieldLabel: 'Referers',
//                                            name: 'referers_id',
//                                            anchor: '95%',
//                                            minChars: 1
//                                        }
//                                    ]
//                                }
                            ]
                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [
                                {
                                    layout: 'form',
                                    columnWidth: 1.0,
                                    border: false,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'Email',
                                            tabIndex: 602,
                                            id: 'textfieldMarketingLeadIndividualEmail',
                                            name: 'textfieldMarketingLeadIndividualEmail',
                                            allowBlank: false,
                                            anchor: '95%',
                                            vtype: 'email',
                                            maxLength: 100,
                                        }
//                                        {
//                                            xtype: 'checkbox',
//                                            boxLabel: 'Referer',
//                                            id: 'refererse_id',
//                                            name: 'refererse_id',
//                                            style: 'margin: 15px 0px 0px 1px;',
//                                            inputValue: 'Yes',
//                                            //hidden: true,
//                                            hideLabel: true,
//                                        }

                                    ]
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
                    tabIndex: 603,
                    handler: function () {
                        leadform.close();
                    }
                },
                {
                    text: 'Save',
                    anchor: '95%',
                    columnWidth: 0.1,
                    bodyStyle: {'margin': '0px 0px 0px 140px'},
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    tabIndex: 604,
                    handler: function () {
                        var _customerId = Ext.getCmp('lead_customerId').getValue();
                        var _addContactData = Ext.getCmp('formpanelAddLead');
                        Ext.getCmp('numberfieldMarketingLeadindividualPrimarymobile').allowBlank = false;
                        Ext.getCmp('textfieldMarketingLeadIndividualContactPerson').allowBlank = false;
//                        Ext.getCmp('textfieldMarketingLeadIndividualEmail').allowBlank = false;
                        leadInsertion(_customerId);
                    }
                }



            ]

        });
        if (contactid > 0) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var regFee_form = Ext.getCmp('formpanelAddLead').getForm();
            regFee_form.load({
                params: {
                    EditStatus: 1,
                    _edit_crco_id: contactid,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                url: modURL + '&op=loadEditLeadData',
                waitMsg: 'Loading...', success: function (form, action) {
                    eval('var tmp=' + action.response.responseText);
                    var tmp = Ext.decode(action.response.responseText);
//                    if (tmp.data.referedl_by == 1) {
//                        Ext.getDom('referedl_by').checked = true;
                    Ext.getCmp('referers_id').show();
                    Ext.getCmp('referers_id').setValue(tmp.data.referers_id);
                    Ext.getCmp('referers_id').getStore().load();
//                    }
                    if (tmp.data.refererse_id == 1)
                        Ext.getDom('refererse_id').checked = true;
                },
                failure: function (form, action) {
                }
            });
        }
        leadform.doLayout();
        leadform.show();
        leadform.center();
        return leadform;
    }
    var leadInsertion = function (_customerId) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var _individualMobile = Ext.getCmp('numberfieldMarketingLeadindividualPrimarymobile').getValue();
        var _individualName = Ext.getCmp('textfieldMarketingLeadIndividualContactPerson').getValue();
        var _addeditform = Ext.getCmp('formpanelAddLead');
        if (_customerId > 0) {
            _customerId = _customerId;
            Application.Crm_FLead.Cache.upcrocid = _customerId;
        } else {
            _customerId = '-';
            Application.Crm_FLead.Cache.upcrocid = 0;
        }
        if (_addeditform.getForm().isValid()) {
            if (_individualName != '' && _individualMobile != '') {
                var form_data = {
                    textfieldMarketingLeadIndividualContactPerson: Ext.getCmp('textfieldMarketingLeadIndividualContactPerson').getValue(),
                    contact_primarymob: Ext.getCmp('numberfieldMarketingLeadindividualPrimarymobile').getValue(),
                    textfieldMarketingLeadIndividualEmail: Ext.getCmp('textfieldMarketingLeadIndividualEmail').getValue(),
//                    reference: Ext.getCmp('referers_id').getValue(),
//                    refered_by: Ext.getCmp('referedl_by').getValue(),
//                    referer_id: Ext.getCmp('refererse_id').getValue()
//                    textareaContactDescription: Ext.getCmp('textareaContactDescription').getValue(),
                            //tstamp: t_stamp


                };
                var params = {
                    action: 'Insert',
                    module: 'crm_lead',
                    op: 'EditLeadDetails',
                    id: _customerId,
                    extrainfo: 'INS'
                };
                APICall(params, Application.Crm_FLead.editLeadData, form_data);
            }


        } else
        {

            Ext.MessageBox.alert('Notification', "Please enter all required fields");
        }

    };
    var crmLeadGrid = function () {
        var _gridStore = crmLeadGridStore();
        var filters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'lead_name'
                },
                {
                    type: 'string',
                    dataIndex: 'mobile'
                },
                {
                    type: 'string',
                    dataIndex: 'email'
                }, {
                    type: 'string',
                    dataIndex: 'TYPE'
                }
            ]
        });
        filters.remote = true;
        filters.autoReload = true;

        var _crmLeadGrid = new Ext.grid.GridPanel({
            id: 'gridMarketingLeadsList',
            store: _gridStore,
            region: 'center',
            frame: true,
            border: false,
            plugins: [filters],
            loadMask: true,
            columns: [
                {
                    header: 'Lead Name',
                    dataIndex: 'lead_name',
                    sortable: true,
                    width: 200
                },
                {
                    header: 'Contact Number',
                    dataIndex: 'mobile',
                    sortable: true,
                    width: 150
                },
                {
                    header: 'Email Address',
                    dataIndex: 'email',
                    sortable: true,
                    width: 200
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
                            leadsActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    header: 'Actions',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [
                        {
                            text: ' ',
                            tooltip: 'Edit Lead Details',
                            iconCls: 'edit',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var _status = record.data.STATUS;
                                var _active = record.data.crle_isActive;
                                //loadContactWindow(record.get('crco_id'));
                                leadform(record.get('crle_id'));
                            }
                        }

                    ]
                }*/
            ],
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
                    if (record.get('STATUS') == 'Assigned')
                    {
                        return '';
                    } else if (record.get('STATUS') == 'UnAttended')
                    {
                        return '';
                    } else if (record.get('STATUS') == 'Call Later')
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else
                    {
                        return 'finascop_indicateColPINK';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            }), tbar: [{
                    xtype: 'button',
                    text: 'Create Lead',
                    tooltip: 'Create Lead',
                    iconCls: 'finascop_add',
                    handler: function () {
                        leadform();

                    }
                },
                {
                    xtype: 'button',
                    iconCls: 'csv',
                    text: 'Import',
                    handler: function () {
                        csv_upload();
//                        var csv_form = Ext.getCmp('csv_form').getForm();
//                        var table = Ext.getCmp('csv_table').getValue();
//                        var t = new Date();
//                        var t_stamp = t.format("YmdHis");
//                        if (csv_form.isValid()) {
//                            csv_form.submit({
//                                url: modURL + '&op=uploadcsvFile',
//                                waitTitle: 'Please Wait..',
//                                waitMsg: 'Saving data...',
//                                params: {
//                                    table: table,
//                                    apikey: _SESSION.apikey,
//                                    tstamp: t_stamp
//                                },
//                                success: function (csv_form, action) {
//                                    var result = Ext.decode(action.response.responseText);
//                                    if (result.valid === true && result.success === true) {
//                                        win.close();
//                                        Application.example.msg('Notification', 'Details saved Successfully. contentid' + result.contentid + '$dupvalues' + result.$dupvalues);
//                                    }
//                                },
//                                failure: function () {
//                                    Ext.Msg.alert('Error', 'Supplied CSV File could not be validated. ', function (btn) {
//                                        if (btn === 'ok') {
//                                            Ext.Msg.hide();
//                                            win.close();
//                                        }
//                                    });
//                                }
//                            });
//                        }
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _gridStore,
                displayInfo: true,
                plugins: [filters],
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: 'No records to display',
                items: [{
                        html: '<div class="finascop_color_wr">\
                    <div class="color-light-yellow_small"></div><div class="text_c"> Call Later </div>\
                    <div class="color-light-red_small"></div> <div class="text_c">Not Interested</div>\
                </div> '
                    }]

            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedLead
                }
            }),
            listeners: {
                afterrender: function () {
                    _gridStore.load();
                },
                cellclick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var record = record.data;
                    var ID = record.crle_id;
                    Application.Crm_FLead.Cache.crle_id = ID;
                    if (columnIndex != 5)
                    {

                        var _tabpanel = Ext.getCmp('tabpanelMarketingLead');

                        Ext.getCmp('htmlid').show();
                        if (record.STATUS == "UnAttended")
                        {
                            Application.Crm_FLead.Cache.status = record.STATUS;
                            _tabpanel.setActiveTab(0);
                            Ext.getCmp("htmlid").show();
                        } else
                        {

                            Application.Crm_FLead.ViewMode(Application.Crm_FLead.Cache.crle_id, 'NOT');
                            Application.Crm_FLead.Cache.status = record.STATUS;
                            _tabpanel.setActiveTab(0);

                        }

                    }
                },
                viewready: updatePagination
            }
        });
        return _crmLeadGrid;
    };
    var leadsActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () { 
                    var crle_id = Ext.getCmp('gridMarketingLeadsList').getSelectionModel().getSelections()[0].data.crle_id;
                    leadform(crle_id);
                }
            }]
    });
    var leadUserDetails = function (id, currentStatus) {
        var lead_id = id;
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=getUserid',
            params: {
                crle_id: id,
                status: currentStatus
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true)
                {
                    Ext.getCmp('gridMarketingLeadsList').getStore().load(
                            {
                                callback: function (record, options, success) {
                                    var gridPanel = Ext.getCmp('gridMarketingLeadsList');
                                    var index = gridPanel.store.find('crle_id', id);
                                    gridPanel.getSelectionModel().selectRow(index);
                                    loadLeadWindow(lead_id);
                                }
                            }
                    );


                } else
                {
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            },
            failure: function (form, action) {
                var res = Ext.decode(action.response.responseText);
                Ext.MessageBox.alert('Error', res.errors.msg);
            }
        });
    };

    var gridSelectionChangedLead = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('gridMarketingLeadsList').getSelectionModel().getSelections()))
        {
            var ID = Ext.getCmp('gridMarketingLeadsList').getSelectionModel().getSelections()[0].data.crle_id;
            Ext.getCmp('tabpanelMarketingLead').setActiveTab(0);
            Application.Crm_FLead.ViewMode(ID);
        } else
        {
            console.log('is it here');
            Application.Crm_FLead.Cache.crle_id = 0;
            Application.Crm_FLead.ViewMode(0);
        }
    };

    return {
        Cache: {},
        initLeads: function () {
            var panelId = 'leadspanel';
            var leads_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(leads_panel))
            {
                leads_panel = marketingLeadsPanel(panelId);
                Application.UI.addTab(leads_panel);
                leads_panel.doLayout();
            } else
            {
                Application.UI.addTab(leads_panel);
                leads_panel.doLayout();
            }


        }, ViewMode: function () {
            var lead_id = arguments[0];
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var is_UnAttended = arguments[1];
            var apikey = _SESSION.apikey;
            Ext.get('downloadIframelead').dom.src = modURL + '&op=loadEditLeadData&crle_id=' + lead_id + '&tstamp=' + t_stamp + '&apikey=' + apikey + '&is_UnAttended=' + is_UnAttended;
        }, editLeadData: function () {
            var _editForm = Ext.getCmp('formpanelAddLead');
            var _customerId = Ext.getCmp('lead_customerId').getValue();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            _editForm.getForm().submit({
                waitMsg: "saving.... ",
                url: modURL,
                params: {
                    op: 'EditLeadDetails',
                    customer_id: _customerId,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                },
                success: function (form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                            Ext.getCmp('add_leadWindow').close();//leadWindowforMarketing

                            Ext.getCmp('gridMarketingLeadsList').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            if (_customerId > 0)
                                            {
                                                console.log('here inside if');
                                                var gridPanel = Ext.getCmp('gridMarketingLeadsList');
                                                var index = gridPanel.store.find('crle_id', _customerId);
                                                gridPanel.getSelectionModel().selectRow(index);
                                            }
                                        }
                                    }
                            );
                            Ext.getCmp('htmlid').show();
                        });
                    }
                },
                failure: function (form, action) {
                    var res = Ext.decode(action.response.responseText);
                    Ext.MessageBox.alert('Error', res.errors.msg);
                }
            });
        }
    }
}();




