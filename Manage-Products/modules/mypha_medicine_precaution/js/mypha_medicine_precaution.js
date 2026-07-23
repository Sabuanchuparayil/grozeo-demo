Application.MyphaMedicinePrecaution = function () {

    var RECS_PER_PAGE = 12;
    var modURL = '?module=mypha_medicine_precaution';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionAdviceChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('adviceGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('adviceGridPanel').getSelectionModel().getSelections()[0].data.advice_id;
            Application.MyphaMedicinePrecaution.ViewAdviceMode(ID);
        }
    };
    var gridSelectionPrecautionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('precautionGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('precautionGridPanel').getSelectionModel().getSelections()[0].data.precaution_id;
            Application.MyphaMedicinePrecaution.ViewPrecautionMode(ID);
        }
    };
    var gridSelectionAdvicePrecautionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('advicePrecautionGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('advicePrecautionGridPanel').getSelectionModel().getSelections()[0].data.preadv_id;
            Application.MyphaMedicinePrecaution.ViewAdvicePrecautionMode(ID);
        }
    };
    var advicePanel = function (id) {
        var _safetyadvicePanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Safety Advice',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                adviceGrid(),
                new Ext.Panel({
                    title: 'Safety Advice Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'advicePanel',
                    height: winsize.height * 0.6,
                    items: [
                        adviceForm(), adviceDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 503,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonadviceCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('adviceGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('adviceGridPanel').getSelectionModel().getSelections()[0].data.advice_id;
                                    Application.MyphaMedicinePrecaution.ViewAdviceMode(ID);
                                }
                            }
                        }, /* <?php if (user_access("mypha_medicine_precaution", "saveAdvice")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonadviceEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 502,
                            handler: function () {
                                var ID = Ext.getCmp('adviceGridPanel').getSelectionModel().getSelections()[0].data.advice_id;
                                Application.MyphaMedicinePrecaution.EditAdviceView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 502,
                            cls: 'left-right-buttons',
                            id: 'buttonadviceSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveAdvice();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _safetyadvicePanel;
    };
    var adviceGridstore = function () {
        var _adviceList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listadvice',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'advice_id',
                root: 'data'
            }, ['advice_id', 'advice_name', 'advice_status']),
            sortInfo: {
                field: 'advice_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('adviceGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _adviceList;
    };
    var adviceForm = function () {
//        var screenNameStore = nameStore();
        var _adviceForm = new Ext.FormPanel({
            id: 'formpanelAdvice',
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
                    xtype: 'textarea',
                    fieldLabel: 'Safety Advice',
                    id: 'advice_name',
                    name: 'n[advice_name]',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 500,
                    maxLength: 300
                },
                {
                    xtype: 'textfield',
                    id: 'advice_id',
                    name: 'n[advice_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[advice_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 501,
                    "emptyText": "Set status..",
                    "id": 'advice_status'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('advice_id').getValue())) {
                        var recordSelected = Ext.getCmp('advice_status').getStore().getAt(0);
                        Ext.getCmp('advice_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        })
        return _adviceForm;
    };
    var saveAdvice = function () {
        var forms_data = {
            form: Ext.getCmp('formpanelAdvice').getForm().getValues()
        };
        if (Ext.getCmp('advice_id').getValue() > 0) {
            var advId = Ext.getCmp('advice_id').getValue();
        } else {
            var d = new Date();
            var advId = '-';
        }
        var params = {
            action: 'Save Advice Details',
            module: 'SafetyAdvice',
            op: 'saveAdvice',
            extrainfo: 'Advice Details save',
            id: advId
        };
        APICall(params, Application.MyphaMedicinePrecaution.saveAdvice, forms_data);
    };
    var adviceDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateadviceViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Safety Advice </th><td>  {advice_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="advice_status == \'1\'">Active</tpl>',
                    '<tpl if="advice_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var adviceGrid = function () {
        var _adviceGridstore = adviceGridstore();
        var _adviceFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'advice_name'
                },
                {
                    type: 'string',
                    dataIndex: 'advice_status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'advice_status'
                }
            ]
        });
        _adviceFilter.remote = true;
        _adviceFilter.autoReload = true;
        var _gridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _adviceGridstore,
            //iconCls: 'money',
            id: 'adviceGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _adviceFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Safety Advice',
                    id: 'Advice_name_auto_exp',
                    dataIndex: 'advice_name',
                    sortable: true,
                    tooltip: 'Safety Advice',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'advice_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _adviceGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionAdviceChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('advice_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMedicinePrecaution.Cache.advice_id = ID;
                        Ext.getCmp('formpanelAdvice').hide();
                        Application.MyphaMedicinePrecaution.ViewAdviceMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _adviceGridstore.load();
                }
            },
            tbar: [{
                    text: 'Create Safety Advice',
                    tooltip: 'Create Safety Advice',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMedicinePrecaution.AdviceAddEdit = 'Add';
                        var masterForm = Ext.getCmp('formpanelAdvice').getForm();
                        Ext.getCmp('advicePanel').setTitle('Create Safety Advice Details');
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('advice_name').focus(false, 100);
                        /*<?php if (user_access("mypha_medicine_precaution", "saveAdvice")) { ?> */
                        Ext.getCmp('buttonadviceEdit').hide();
                        Ext.getCmp('buttonadviceSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonadviceCancel').show();
                        Ext.getCmp('formpanelAdvice').show();

                        Ext.getCmp('advice_status').setValue(1);
                        Ext.getCmp('xtemplateadviceViewDetails').hide();
                        Ext.getCmp('advicePanel').doLayout();
                    }
                }]
        });
        return _gridPanel;
    };
    var precationPanel = function (id) {
        var _precationPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Safety Precaution',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                precautionGrid(),
                new Ext.Panel({
                    title: 'Precaution Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'precationPanel',
                    height: winsize.height * 0.6,
                    items: [
                        precautionForm(), precautionDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 503,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonprecautionCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('precautionGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('precautionGridPanel').getSelectionModel().getSelections()[0].data.precaution_id;
                                    Application.MyphaMedicinePrecaution.ViewPrecautionMode(ID);
                                }
                            }
                        }, /* <?php if (user_access("mypha_medicine_precaution", "savePrecaution")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonprecautionEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 502,
                            handler: function () {
                                var ID = Ext.getCmp('precautionGridPanel').getSelectionModel().getSelections()[0].data.precaution_id;
                                Application.MyphaMedicinePrecaution.EditPrecautionView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 502,
                            cls: 'left-right-buttons',
                            id: 'buttonprecationSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                savePrecaution();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _precationPanel;
    };
    var precautionGridstore = function () {
        var _precautionList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listprecaution',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'precaution_id',
                root: 'data'
            }, ['precaution_id', 'precaution_name', 'precaution_status']),
            sortInfo: {
                field: 'precaution_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('precautionGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _precautionList;
    };
    var precautionForm = function () {
        var _precautionForm = new Ext.FormPanel({
            id: 'formpanelPrecaution',
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
                    fieldLabel: 'Precaution Name',
                    id: 'precaution_name',
                    name: 'n[precaution_name]',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 500,
                    maxLength: 300
                },
                {
                    xtype: 'textfield',
                    id: 'precaution_id',
                    name: 'n[precaution_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[precaution_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 501,
                    "emptyText": "Set status..",
                    "id": 'precaution_status'
                })
            ],
             listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('precaution_id').getValue())) {
                        var recordSelected = Ext.getCmp('precaution_status').getStore().getAt(0);
                        Ext.getCmp('precaution_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        })
        return _precautionForm;
    };
    var savePrecaution = function () {
        var form_data = {
            form: Ext.getCmp('formpanelPrecaution').getForm().getValues()
        };
        if (Ext.getCmp('precaution_id').getValue() > 0) {
            var preId = Ext.getCmp('precaution_id').getValue();
        } else {
            var d = new Date();
            var preId = '-';
        }
        var params = {
            action: 'Save Precaution Details',
            module: 'SafetyPrecaution',
            op: 'savePrecaution',
            extrainfo: 'Precaution Details save',
            id: preId
        };
        APICall(params, Application.MyphaMedicinePrecaution.savePrecaution, form_data);
    };
    var precautionDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateprecautionViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Precaution Name </th><td>  {precaution_name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="precaution_status == \'1\'">Active</tpl>',
                    '<tpl if="precaution_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var precautionGrid = function () {
        var _precautionGridstore = precautionGridstore();
        var _precautionFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'precaution_name'
                },
                {
                    type: 'string',
                    dataIndex: 'precaution_status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'precaution_status'
                }]
        });
        _precautionFilter.remote = true;
        _precautionFilter.autoReload = true;
        var _precautiongridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _precautionGridstore,
            //iconCls: 'money',
            id: 'precautionGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _precautionFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Precaution Name',
                    id: 'Precaution_name_auto_exp',
                    dataIndex: 'precaution_name',
                    sortable: true,
                    tooltip: 'Precaution Name',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'precaution_status',
                    sortable: true,
                    tooltip: 'Status'
                }],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _precautionGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionPrecautionChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('precaution_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMedicinePrecaution.Cache.precaution_id = ID;
                        Ext.getCmp('formpanelPrecaution').hide();
                        Application.MyphaMedicinePrecaution.ViewPrecautionMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _precautionGridstore.load();
                }
            },
            tbar: [{
                    text: 'Create Safety Precaution',
                    tooltip: 'Create Safety Precaution',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMedicinePrecaution.PrecautionAddEdit = 'Add';
                        var masterForm = Ext.getCmp('formpanelPrecaution').getForm();
                        Ext.getCmp('precationPanel').setTitle('Add Safety Precaution Details');
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('precaution_name').focus(false, 100);
                        /*<?php if (user_access("mypha_medicine_precaution", "savePrecaution")) { ?> */
                        Ext.getCmp('buttonprecautionEdit').hide();
                        Ext.getCmp('buttonprecationSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonprecautionCancel').show();
                        Ext.getCmp('formpanelPrecaution').show();
                        
                        Ext.getCmp('precaution_status').setValue(1);
                        Ext.getCmp('xtemplateprecautionViewDetails').hide();
                        Ext.getCmp('precationPanel').doLayout();
                    }
                }]
        });
        return _precautiongridPanel;
    };
    var advicePrecautionPanel = function (id) {
        var _advicePrecautionPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Map Advice-Precaution',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                advicePrecautionGrid(),
                new Ext.Panel({
                    title: 'Advice-Precaution Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'advicePrecautionPanel',
                    height: winsize.height * 0.6,
                    items: [
                        advicePrecautionForm(), advicePrecautionDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 906,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonadvicePrecautionCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('advicePrecautionGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('advicePrecautionGridPanel').getSelectionModel().getSelections()[0].data.preadv_id;
                                    Application.MyphaMedicinePrecaution.ViewAdvicePrecautionMode(ID);
                                }
                            }
                        }, /* <?php if (user_access("mypha_medicine_precaution", "saveAdvicePrecaution")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonadvicePrecautionEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 905,
                            handler: function () {
                                var ID = Ext.getCmp('advicePrecautionGridPanel').getSelectionModel().getSelections()[0].data.preadv_id;
                                Application.MyphaMedicinePrecaution.EditAdvicePrecautionView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 905,
                            cls: 'left-right-buttons',
                            id: 'buttonadvicePrecautionSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveAdvicePrecaution();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _advicePrecautionPanel;
    };
    var advicePrecautionGridstore = function () {
        var _advicePrecautionList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listadvicePrecaution',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'preadv_id',
                root: 'data'
            }, ['advc_id', 'preadv_id', 'advice_name', 'precaution_name', 'prec_id', 'preadv_content']),
            sortInfo: {
                field: 'preadv_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('advicePrecautionGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _advicePrecautionList;
    };
    var advicenameStore = function () {
        var advicenameStore = new Ext.data.JsonStore({
            url: modURL + '&op=adviceName',
            method: 'post',
            fields: ['advc_id', 'advice_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return advicenameStore;
    };
    var precautionnameStore = function () {
        var precautionnameStore = new Ext.data.JsonStore({
            url: modURL + '&op=precautionName',
            method: 'post',
            fields: ['prec_id', 'precaution_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return precautionnameStore;
    };
    var advicePrecautionForm = function () {
        var safetyadviceNameStore = advicenameStore();
        var safetyprecautionNameStore = precautionnameStore();
        var _advicePrecautionForm = new Ext.FormPanel({
            id: 'formpanelAdvicePrecaution',
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
                    xtype: 'combo',
                    fieldLabel: 'Advice Name',
                    emptyText: 'Advice Name',
                    id: 'advc_id',
                    name: 'n[advice_id]',
                    hiddenName: 'n[advice_id]',
                    anchor: '98%',
                    displayField: 'advice_name',
                    valueField: 'advc_id',
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    //autoShow: true,
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 902,
                    store: safetyadviceNameStore,
                    listeners:{
                                       afterrender:function(field){
                                       Ext.defer(function(){
                                       field.focus(true,100);
                                       },1);
                                     }
                                   }
                },
                {
                    xtype: 'combo',
                    fieldLabel: 'Precaution Name',
                    emptyText: 'Precaution Name',
                    id: 'prec_id',
                    name: 'n[precaution_id]',
                    hiddenName: 'n[precaution_id]',
                    anchor: '98%',
                    displayField: 'precaution_name',
                    valueField: 'prec_id',
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 903,
                    store: safetyprecautionNameStore
                },
                {
                    xtype: 'textfield',
                    id: 'preadv_id',
                    name: 'n[preadv_id]',
                    hidden: true
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Content',
                    id: 'preadv_content',
                    name: 'n[preadv_content]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 904,
                    maxValue: 100
                }
            ],
        })
        return _advicePrecautionForm;
    };
    var saveAdvicePrecaution = function () {
        var formss_data = {
            form: Ext.getCmp('formpanelAdvicePrecaution').getForm().getValues()
        };
        if (Ext.getCmp('preadv_id').getValue() > 0) {
            var advpreId = Ext.getCmp('preadv_id').getValue();
        } else {
            var d = new Date();
            var advpreId = '-';
        }
        var params = {
            action: 'Save Advice-Precaution Details',
            module: 'AdvicePrecaution',
            op: 'saveAdvicePrecaution',
            extrainfo: 'Advice-Precaution Details save',
            id: advpreId
        };
        APICall(params, Application.MyphaMedicinePrecaution.saveAdvicePrecaution, formss_data);
    };
    var advicePrecautionDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateadvicePrecautionViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Advice Name </th><td>  {advice_name} </td></tr>',
                    '<tr><th width="40%">Precaution Name </th><td>  {precaution_name} </td></tr>',
                    '<tr><th width="40%">Content </th><td>  {preadv_content} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var advicePrecautionGrid = function () {
        var _advicePrecautionGridstore = advicePrecautionGridstore();
        var _advicePrecautionFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'advice_name'
                },
                {
                    type: 'string',
                    dataIndex: 'precaution_name'
                },
                {
                    type: 'string',
                    dataIndex: 'preadv_content'
                }
            ]
        });
        _advicePrecautionFilter.remote = true;
        _advicePrecautionFilter.autoReload = true;
        var _gridAdPrePanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _advicePrecautionGridstore,
            //iconCls: 'money',
            id: 'advicePrecautionGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _advicePrecautionFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Advice Name',
                    id: 'Advice_name_auto_exp',
                    dataIndex: 'advice_name',
                    sortable: true,
                    tooltip: 'Advice Name',
                    hideable: true
                },
                {
                    header: 'Precaution Name',
                    dataIndex: 'precaution_name',
                    sortable: true,
                    tooltip: 'Precaution Name',
                    hideable: true
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _advicePrecautionGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionAdvicePrecautionChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('preadv_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaMedicinePrecaution.Cache.preadv_id = ID;
                        Ext.getCmp('formpanelAdvicePrecaution').hide();
                        Application.MyphaMedicinePrecaution.ViewAdvicePrecautionMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _advicePrecautionGridstore.load();
                }
            },
            tbar: [{
                    text: 'Create Advice-Precaution',
                    tooltip: 'Create Advice-Precaution',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.MyphaMedicinePrecaution.AdvicePrecautionAddEdit = 'Add';
                        var masterForm = Ext.getCmp('formpanelAdvicePrecaution').getForm();
                        Ext.getCmp('advicePrecautionPanel').setTitle('Create Advice-Precaution Details');
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('preadv_id').focus(false, 100);
                        /*<?php if (user_access("mypha_medicine_precaution", "saveAdvicePrecaution")) { ?> */
                        Ext.getCmp('buttonadvicePrecautionEdit').hide();
                        Ext.getCmp('buttonadvicePrecautionSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonadvicePrecautionCancel').show();
                        Ext.getCmp('formpanelAdvicePrecaution').show();
                        Ext.getCmp('xtemplateadvicePrecautionViewDetails').hide();
                        Ext.getCmp('advicePrecautionPanel').doLayout();
                    }
                }]
        });
        return _gridAdPrePanel;
    };

    return {
        Cache: {},
        initMedicinePrecaution: function () {
            var _advicePanelId = 'panelAdvice';
            var _advicePanel = Ext.getCmp(_advicePanelId);
            if (Ext.isEmpty(_advicePanel)) {
                _advicePanel = advicePanel(_advicePanelId);
                Application.UI.addTab(_advicePanel);
                _advicePanel.doLayout();
            } else {
                Application.UI.addTab(_advicePanel);
            }
        },
        EditAdviceView: function () {
            Application.MyphaMedicinePrecaution.AdviceAddEdit = 'Edit';
            Ext.getCmp('advicePanel').doLayout();
            Ext.getCmp('advicePanel').setTitle('Edit Safety Advice Details');
            Ext.getCmp('formpanelAdvice').show();
            Ext.getCmp('xtemplateadviceViewDetails').hide();
            /*<?php if (user_access("mypha_medicine_precaution", "saveAdvice")) { ?> */
            Ext.getCmp('buttonadviceEdit').hide();
            Ext.getCmp('buttonadviceSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonadviceCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {

                var masterForm = Ext.getCmp('formpanelAdvice').getForm();
                masterForm.load({
                    params: {
                        advice_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=advice_load',
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
        saveAdvice: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelAdvice').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveAdvice',
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
                            if (Application.MyphaMedicinePrecaution.AdviceAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('adviceGridPanel'));
                                Ext.getCmp('formpanelAdvice').getForm().reset();
                                Ext.getCmp('adviceGridPanel').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('adviceGridPanel').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('adviceGridPanel').getStore().load();
                            }
                            Application.MyphaMedicinePrecaution.AdviceAddEdit = '';
                            Application.MyphaMedicinePrecaution.ViewAdviceMode(tmp.data.advice_id);
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
        ViewAdviceMode: function () {
            var advice_id = arguments[0];
            /*<?php if (user_access("mypha_medicine_precaution", "saveAdvice")) { ?> */
            Ext.getCmp('buttonadviceEdit').show();
            Ext.getCmp('buttonadviceSave').hide();
            /*<?php } ?> */
            Ext.getCmp('advicePanel').setTitle('View Safety Advice Details');
            Ext.getCmp('buttonadviceCancel').hide();
            Ext.getCmp('formpanelAdvice').hide();
            Ext.getCmp('xtemplateadviceViewDetails').show();
            Ext.getCmp('advicePanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=adviceDetailsView',
                method: 'POST',
                params: {advice_id: advice_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateadviceViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('advicePanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('advicePanel').doLayout();
        },
        initPrecaution: function () {
            var _precationPanelId = 'panelPrecaution';
            var _precationPanel = Ext.getCmp(_precationPanelId);
            if (Ext.isEmpty(_precationPanel)) {
                _precationPanel = precationPanel(_precationPanelId);
                Application.UI.addTab(_precationPanel);
                _precationPanel.doLayout();
            } else {
                Application.UI.addTab(_precationPanel);
            }
        },
        EditPrecautionView: function () {
            Application.MyphaMedicinePrecaution.PrecautionAddEdit = 'Edit';
            Ext.getCmp('precationPanel').doLayout();
            Ext.getCmp('precationPanel').setTitle('Edit Safety Precaution Details');
            Ext.getCmp('formpanelPrecaution').show();
            Ext.getCmp('xtemplateprecautionViewDetails').hide();
            /*<?php if (user_access("mypha_medicine_precaution", "savePrecaution")) { ?> */
            Ext.getCmp('buttonprecautionEdit').hide();
            Ext.getCmp('buttonprecationSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonprecautionCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {

                var masterForm = Ext.getCmp('formpanelPrecaution').getForm();
                masterForm.load({
                    params: {
                        precaution_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=precaution_load',
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
        savePrecaution: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelPrecaution').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=savePrecaution',
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
                            if (Application.MyphaMedicinePrecaution.PrecautionAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('precautionGridPanel'));
                                Ext.getCmp('formpanelPrecaution').getForm().reset();
                                Ext.getCmp('precautionGridPanel').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('precautionGridPanel').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('precautionGridPanel').getStore().load();
                            }
                            Application.MyphaMedicinePrecaution.PrecautionAddEdit = '';
                            Application.MyphaMedicinePrecaution.ViewPrecautionMode(tmp.data.precaution_id);
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
        ViewPrecautionMode: function () {
            var precaution_id = arguments[0];
            /*<?php if (user_access("mypha_medicine_precaution", "savePrecaution")) { ?> */
            Ext.getCmp('buttonprecautionEdit').show();
            Ext.getCmp('buttonprecationSave').hide();
            /*<?php } ?> */
            Ext.getCmp('precationPanel').setTitle('View Safety Precaution Details');
            Ext.getCmp('buttonprecautionCancel').hide();
            Ext.getCmp('formpanelPrecaution').hide();
            Ext.getCmp('xtemplateprecautionViewDetails').show();
            Ext.getCmp('precationPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=precautionDetailsView',
                method: 'POST',
                params: {precaution_id: precaution_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateprecautionViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('precationPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('precationPanel').doLayout();
        },
        initAdvicePrecaution: function () {
            var _advicePrecautionPanelId = 'panelAdManagement';
            var _advicePrecautionPanel = Ext.getCmp(_advicePrecautionPanelId);
            if (Ext.isEmpty(_advicePrecautionPanel)) {
                _advicePrecautionPanel = advicePrecautionPanel(_advicePrecautionPanelId);
                Application.UI.addTab(_advicePrecautionPanel);
                _advicePrecautionPanel.doLayout();
            } else {
                Application.UI.addTab(_advicePrecautionPanel);
            }
        },
        EditAdvicePrecautionView: function () {
            Application.MyphaMedicinePrecaution.AdvicePrecautionAddEdit = 'Edit';
            Ext.getCmp('advicePrecautionPanel').doLayout();
            Ext.getCmp('advicePrecautionPanel').setTitle('Edit Advice-Precaution Details');
            Ext.getCmp('formpanelAdvicePrecaution').show();
            Ext.getCmp('xtemplateadvicePrecautionViewDetails').hide();
            /*<?php if (user_access("mypha_medicine_precaution", "saveAdvicePrecaution")) { ?> */
            Ext.getCmp('buttonadvicePrecautionEdit').hide();
            Ext.getCmp('buttonadvicePrecautionSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonadvicePrecautionCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {

                var masterForm = Ext.getCmp('formpanelAdvicePrecaution').getForm();
                masterForm.load({
                    params: {
                        preadv_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=advice_precaution_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                        Ext.getCmp('advice_name').setValue(tmp.advice_name);
                        Ext.getCmp('advice_name').getStore().load();
                        Ext.getCmp('precaution_name').setValue(tmp.precaution_name);
                        Ext.getCmp('precaution_name').getStore().load();
                        Ext.getCmp('preadv_content').setValue(tmp.preadv_content);
                        Ext.getCmp('preadv_content').getStore().load();
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        saveAdvicePrecaution: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelAdvicePrecaution').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveAdvicePrecaution',
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
                            if (Application.MyphaMedicinePrecaution.AdvicePrecautionAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('advicePrecautionGridPanel'));
                                Ext.getCmp('formpanelAdvicePrecaution').getForm().reset();
                                Ext.getCmp('advicePrecautionGridPanel').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('advicePrecautionGridPanel').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('advicePrecautionGridPanel').getStore().load();
                            }
                            Application.MyphaMedicinePrecaution.AdvicePrecautionAddEdit = '';
                            Application.MyphaMedicinePrecaution.ViewAdvicePrecautionMode(tmp.data.preadv_id);
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
        ViewAdvicePrecautionMode: function () {
            var preadv_id = arguments[0];
            /*<?php if (user_access("mypha_medicine_precaution", "saveAdvicePrecaution")) { ?> */
            Ext.getCmp('buttonadvicePrecautionEdit').show();
            Ext.getCmp('buttonadvicePrecautionSave').hide();
            /*<?php } ?> */
            Ext.getCmp('advicePrecautionPanel').setTitle('View Advice-Precaution Details');
            Ext.getCmp('buttonadvicePrecautionCancel').hide();
            Ext.getCmp('formpanelAdvicePrecaution').hide();
            Ext.getCmp('xtemplateadvicePrecautionViewDetails').show();
            Ext.getCmp('advicePrecautionPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=advicePrecautionDetailsView',
                method: 'POST',
                params: {preadv_id: preadv_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateadvicePrecautionViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('advicePrecautionPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('advicePrecautionPanel').doLayout();
        },
    };
}();