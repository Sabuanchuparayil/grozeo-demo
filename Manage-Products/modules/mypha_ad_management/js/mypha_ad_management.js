Application.MyphaAdManagement = function () {

    var RECS_PER_PAGE = 12;
    var modURL = '?module=mypha_ad_management';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionadManagementChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('adManagementGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('adManagementGridPanel').getSelectionModel().getSelections()[0].data.adzone_id;
            Application.MyphaAdManagement.ViewAdManagementMode(ID);
        }
    };
    var adManagementPanel = function (id) {
        var _adPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Adzone',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                adManagementGrid(),
                new Ext.Panel({
                    title: 'Adzone Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'admanagementpanel',
                    height: winsize.height * 0.6,
                    items: [
                        adManagementForm(), adManagementDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 505,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonadManagementCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('adManagementGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('adManagementGridPanel').getSelectionModel().getSelections()[0].data.adzone_id;
                                    Application.MyphaAdManagement.ViewAdManagementMode(ID);
                                }
                            }
                        }, /* <?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonadManagementEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 504,
                            handler: function () {
                                var ID = Ext.getCmp('adManagementGridPanel').getSelectionModel().getSelections()[0].data.adzone_id;
                                Application.MyphaAdManagement.EditAdManagementView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 504,
                            cls: 'left-right-buttons',
                            id: 'buttonadManagementSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveAdManagement();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _adPanel;
    };
    var adManagementGridstore = function () {
        var _admanagementList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listadManagement',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'adzone_id',
                root: 'data'
            }, ['adzone_id', 'adzone_name', 'adzone_screen', 'adzone_status']),
            sortInfo: {
                field: 'adzone_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('adManagementGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _admanagementList;
    };
    var nameStore = function () {
        var namestore = new Ext.data.JsonStore({
            url: modURL + '&op=screenName',
            method: 'post',
            fields: ['screen_id', 'screen_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return namestore;
    };
    var layoutNameStore = function () {
        var namestore = new Ext.data.JsonStore({
            url: modURL + '&op=layoutName',
            method: 'post',
            fields: ['layout_type_id', 'layout_type_name', 'type_id'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return namestore;
    };
    var adManagementForm = function () {
        var screenNameStore = nameStore();
        var layoutStore = layoutNameStore();
        var _adManagementForm = new Ext.FormPanel({
            id: 'formpanelAdManagement',
            frame: false,
            border: false,
            hidden: true,
            labelAlign: 'top',
            autoHeight: true,
            labelWidth: 100,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Adzone Name',
                    id: 'adzone_name',
                    name: 'n[adzone_name]',
                    anchor: '98%',
                    allowBlank: false,
                    emptyText: 'Adzone Name',
                    width: 300,
                    tabIndex: 500,
                    maxLength: 300
                },
                {
                    xtype: 'combo',
                    fieldLabel: 'Screen Name',
                    id: 'adzone_screen',
                    name: 'n[adzone_screen]',
                    anchor: '98%',
                    displayField: 'screen_name',
                    valueField: 'screen_name',
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 501,
                    store: screenNameStore
                }, {
                    xtype: 'combo',
                    fieldLabel: 'Layouts',
                    id: 'adzone_type',
                    name: 'n[adzone_type]',
                    anchor: '98%',
                    displayField: 'layout_type_name',
                    valueField: 'layout_type_name',
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 502,
                    store: layoutStore
                },
                {
                    xtype: 'textfield',
                    id: 'adzone_id',
                    name: 'n[adzone_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[adzone_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 503,
                    "emptyText": "Set status..",
                    "id": 'adzone_status'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('adzone_id').getValue())) {
                        var recordSelected = Ext.getCmp('adzone_status').getStore().getAt(0);
                        Ext.getCmp('adzone_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        })
        return _adManagementForm;
    };

    var saveAdManagement = function () {
        var forms_data = {
            form: Ext.getCmp('formpanelAdManagement').getForm().getValues()
        };
        if (Ext.getCmp('adzone_id').getValue() > 0) {
            var adId = Ext.getCmp('adzone_id').getValue();
        } else {
            var d = new Date();
            var adId = '-';
        }
        var params = {
            action: 'Save Ad Details',
            module: 'AdManagement',
            op: 'saveAdManagement',
            extrainfo: 'Ad Details save',
            id: adId
        };
        APICall(params, Application.MyphaAdManagement.saveAdManagement, forms_data);
    };

    var adManagementDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateadManagementViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Adzone Name </th><td>  {adzone_name} </td></tr>',
                    '<tr><th width="40%">Screen Name </th><td>  {adzone_screen} </td></tr>',
                    '<tr><th width="40%">Layouts </th><td>  {adzone_type} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="adzone_status == \'1\'">Active</tpl>',
                    '<tpl if="adzone_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var adManagementGrid = function () {
        var _adManagementGridstore = adManagementGridstore();
        var _adManagementFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'adzone_name'
                },
                {
                    type: 'string',
                    dataIndex: 'adzone_screen'
                },
               {
                   type: 'string',
                   dataIndex: 'adzone_status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'adzone_status'
                }
            ]
        });
        _adManagementFilter.remote = true;
        _adManagementFilter.autoReload = true;
        var _gridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _adManagementGridstore,
            //iconCls: 'money',
            id: 'adManagementGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _adManagementFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Adzone Name',
                    id: 'Adzone_name_auto_exp',
                    dataIndex: 'adzone_name',
                    sortable: true,
                    tooltip: 'Adzone Name',
                    hideable: true
                },
                {
                    header: 'Screen Name',
                    dataIndex: 'adzone_screen',
                    sortable: true,
                    tooltip: 'Screen Name',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'adzone_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _adManagementGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionadManagementChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('adzone_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaAdManagement.Cache.adzone_id = ID;
                        Ext.getCmp('formpanelAdManagement').hide();
                        Application.MyphaAdManagement.ViewAdManagementMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _adManagementGridstore.load();
                }
            },
            tbar: [/*<?php if (user_access("retaline_ad_management", "isSuperAdmin")) { ?> */{
                    text: 'Create Adzone',
                    tooltip: 'Create Adzone',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaAdManagement.AdManagementAddEdit = 'Add';
                        var masterForm = Ext.getCmp('formpanelAdManagement').getForm();
                        Ext.getCmp('admanagementpanel').setTitle('Create Adzone Details');
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('adzone_name').focus(false, 100);
                        /*<?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */
                        Ext.getCmp('buttonadManagementEdit').hide();
                        Ext.getCmp('buttonadManagementSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonadManagementCancel').show();
                        Ext.getCmp('formpanelAdManagement').show();

                        Ext.getCmp('adzone_status').setValue(1);
                        Ext.getCmp('xtemplateadManagementViewDetails').hide();
                        Ext.getCmp('admanagementpanel').doLayout();
                    }
                }/*<?php } ?> */]
        });
        return _gridPanel;
    };



    return {
        Cache: {},
        initManagement: function () {
            var _adManagementPanelId = 'panelAdManagement';
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
            Application.MyphaAdManagement.AdManagementAddEdit = 'Edit';
            Ext.getCmp('admanagementpanel').doLayout();
            Ext.getCmp('admanagementpanel').setTitle('Edit Adzone Details');
            Ext.getCmp('formpanelAdManagement').show();
            Ext.getCmp('xtemplateadManagementViewDetails').hide();
            /*<?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */
            Ext.getCmp('buttonadManagementEdit').hide();
            Ext.getCmp('buttonadManagementSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonadManagementCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {

                var masterForm = Ext.getCmp('formpanelAdManagement').getForm();
                masterForm.load({
                    params: {
                        adzone_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=admanagement_load',
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
        saveAdManagement: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelAdManagement').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveAdManagement',
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
                            if (Application.MyphaAdManagement.AdManagementAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('adManagementGridPanel'));
                                Ext.getCmp('formpanelAdManagement').getForm().reset();
                                Ext.getCmp('adManagementGridPanel').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('adManagementGridPanel').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('adManagementGridPanel').getStore().load();
                            }
                            Application.MyphaAdManagement.AdManagementAddEdit = '';
                            Application.MyphaAdManagement.ViewAdManagementMode(tmp.data.adzone_id);
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
        ViewAdManagementMode: function () {
            var adzone_id = arguments[0];
            /*<?php if (user_access("mypha_ad_management", "saveAdManagement")) { ?> */
            Ext.getCmp('buttonadManagementEdit').show();
            Ext.getCmp('buttonadManagementSave').hide();
            /*<?php } ?> */
            Ext.getCmp('admanagementpanel').setTitle('View Adzone Details');
            Ext.getCmp('buttonadManagementCancel').hide();
            Ext.getCmp('formpanelAdManagement').hide();
            Ext.getCmp('xtemplateadManagementViewDetails').show();
            Ext.getCmp('admanagementpanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=adManagementDetailsView',
                method: 'POST',
                params: {adzone_id: adzone_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateadManagementViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('admanagementpanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('admanagementpanel').doLayout();
        },
    };
}();


