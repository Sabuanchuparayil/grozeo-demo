Application.RetalinStockRequest = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var modURL = '?module=retaline_stock_request';
    var itmodURL = '?module=retaline_cpd';
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var transferRequestStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalineStockRequest',
                method: 'post'
            }),
            fields: ['fsr_id', 'fsr_uid', 'fsr_source', 'fsr_destination', 'fstrs_status', 'fsr_createdOn', 'sourcename', 'branch', 'status_name', 'fsr_type', 'initiatedBranch', 'fsr_initiatedBy','fsrs_status'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    this.baseParams.branchName = Ext.getCmp('comboxbranchnamestckreq').getValue();
                }
            }
        });
        store.setDefaultSort('fsr_createdOn', 'DESC');
        return store;
    };

    var retalineStockRequestGrid = function (id) {
        var branch_store = transferRequestStore();

        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fsr_uid'
                }, {
                    type: 'string',
                    dataIndex: 'fsr_createdOn'
                },
                {
                    type: 'string',
                    dataIndex: 'status_name'
                },
                {
                    type: 'string',
                    dataIndex: 'sourcename'
                }, {
                    type: 'string',
                    dataIndex: 'fsr_type'
                }, {
                    type: 'string',
                    dataIndex: 'branch'
                }
            ]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Stock Request',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Stock Request No',
                    id: 'branch_name_auto_expstkreq',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fsr_uid',
                    tooltip: 'Stock Request No'
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'fsr_createdOn',
                    tooltip: 'Created Date'
                },
                {
                    header: 'Source',
                    sortable: true,
                    dataIndex: 'sourcename',
                    tooltip: 'Source'
                },
                {
                    header: 'Destination',
                    sortable: true,
                    dataIndex: 'branch',
                    tooltip: 'Destination'
                }, {
                    header: 'Initiated Branch',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'initiatedBranch',
                    tooltip: 'Initiated Branch'
                },
                {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'fsr_type',
                    tooltip: 'Type'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'status_name',
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
                            var fstrs_status = Ext.getCmp('retalineStockRequest_main_panel').getSelectionModel().getSelections()[0].data.fstrs_status;
                            stockrequestActionMenu(e, fstrs_status)
                        }
                    }
                }

            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {
                    if (_SESSION.br_PyramidLevel == 1) {
                        Ext.getCmp("comboxbranchnamestckreq").show();
                        Ext.getCmp("stkReqBranchDataeditCS").hide();
                    } else {
                        Ext.getCmp("comboxbranchnamestckreq").hide();
                        Ext.getCmp("stkReqBranchDataeditCS").show();
                        Ext.getCmp("stkReqBranchDataeditCS").setValue(_SESSION.current_branch);
                    }

                }
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Stock Request',
                    tooltip: 'Create Stock Request',
                    id: 'addnewStchReq',
                    handler: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        Ext.Ajax.request({
                            url: modURL + '&op=generateUniqueId',
                            params: {
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp

                            },
                            method: 'POST',
                            success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                var uid = tmp.uid;
                                Application.RetalinStockRequest.Cache.uuid = uid;
                                createRetalineStockRequest(0);
                            }
                        });

                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'stkReqBranchDataeditCS',
                    name: 'stkReqBranchDataeditCS',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                }, {
                    xtype: 'combo',
                    id: 'comboxbranchnamestckreq',
                    name: 'comboxbranchnamestckreq',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    emptyText: 'Select Branch',
                    fieldLabel: 'Branch Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnamestckreq',
                    listeners: {
                        select: function () {
                            Ext.getCmp('retalineStockRequest_main_panel').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchnamestckreq').getValue();
                        Ext.getCmp('retalineStockRequest_main_panel').getStore().load({
                            params: {
                                branchName: branchName
                            }
                        });
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branch_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_expstkreq'
        });
        return grid_panel;
    };
    var stockrequestActionMenu = function (e, fstrs_status) {
        var stockrequestActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Edit",
                    handler: function () {
                        var fsr_id = Ext.getCmp('retalineStockRequest_main_panel').getSelectionModel().getSelections()[0].data.fsr_id;
                        var fsrs_status = Ext.getCmp('retalineStockRequest_main_panel').getSelectionModel().getSelections()[0].data.fsrs_status;
                        Application.RetalinStockRequest.Cache.uuid = '';
                        if (fsrs_status == '1') {
                        editRetalineStockRequest(fsr_id);
                        } else {
                            Application.RetalinStockRequest.viewTransferItems(fsr_id);
                    }

                    }
                }, {
                    text: "View",
                    handler: function () {
                        var fsr_id = Ext.getCmp('retalineStockRequest_main_panel').getSelectionModel().getSelections()[0].data.fsr_id;
                        Application.RetalinStockRequest.viewTransferItems(fsr_id);
                    }
                },
                {
                    text: "Send To Branch Indent",
                    handler: function () {
                        var fsr_id = Ext.getCmp('retalineStockRequest_main_panel').getSelectionModel().getSelections()[0].data.fsr_id;
                        var fsrs_status = Ext.getCmp('retalineStockRequest_main_panel').getSelectionModel().getSelections()[0].data.fsrs_status;
                        if (fsrs_status == '1') {
                        Application.RetalinStockRequest.AddNewTransferWindow(fsr_id);
                        } else {
                            Ext.MessageBox.alert('Notification', 'Request already sent to the branch');
                    }

                    }
                }]
        });
        stockrequestActionMenu.showAt(e.getXY());
    };
    var rtrBranchStore = function (isCPD) {
        var store = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchDestination',
            method: 'post',
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    this.baseParams.type = isCPD;
                }, load: function (store, rec, opt) {
                    if (store.totalLength > 0) { //totalLength == 1
                        Ext.getCmp('fsr_destination').setValue(store.getAt(0).get("br_ID"));
                        Ext.getCmp('fsr_destination').setHideTrigger(true);

                        var fsr_destination = Ext.getCmp('fsr_destination').getValue();

                        Ext.getCmp('fsr_ItemId').getStore().load({
                            params: {
                                sourceBranch: fsr_destination
                            }
                        });
                    }
                }
            }
        });
        return store;
    };
    var rtrSourceStore = function (isCPD) {
        var store = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranch',
            method: 'post',
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    this.baseParams.type = isCPD;
                }, load: function () {
                    if (_SESSION.br_PyramidLevel != 1) {
                        Ext.getCmp("fsr_source").setValue(_SESSION.finascop_current_branch_id);
                        Ext.getCmp('fsr_source').setHideTrigger(true);
                        Ext.getCmp('fsr_destination').getStore().load({
                            params: {
                                br_cpd: _SESSION.finascop_current_branch_id
                            }
                        });
                    }



                }
            }
        });
        return store;
    };
    var rtItemStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU', 'packageName', 'id', 'fsbg_id', 'fpod_leastSKUmrp'],
            url: modURL + '&op=getItemName',
            autoLoad: false,
            method: 'post'
        });
        return store;
    };
    var intrBrnchItemStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU', 'packageName', 'id', 'fsbg_id', 'fpod_leastSKUmrp', 'item_count'],
            url: modURL + '&op=getItemNameIntrBrnch',
            autoLoad: false,
            method: 'post'
        });
        return store;
    };

    var packTypeStore = new Ext.data.JsonStore({
        url: modURL + '&op=getPTStore',
        fields: ['package_type_id', 'package_type_name'],
        totalProperty: 'totalCount',
        root: 'data',
        autoload: false,
        remoteFilter: true,
        listeners: {
            load: function (store, rec, opt) {
                if (store.totalLength > 0) { //totalLength == 1
                    Ext.getCmp('fsr_StockItemUnits').setValue(store.getAt(0).get("package_type_id"));
                }
            }
        }
    });
    var createRetalineStockRequestform = function (fsr_id) {

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var itemsStore = rtItemStore();
        var sourceStore = rtrSourceStore(1);
        var branchStore = rtrBranchStore(0);
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 200,
            frame: true,
            border: false,
            id: 'poItemStockItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.50,
                            items: [{
                                    xtype: 'combo',
                                    id: 'fsr_source',
                                    name: 'fsr_source',
                                    mode: 'local',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    fieldLabel: 'Source',
                                    editable: true,
                                    anchor: '97%',
                                    store: sourceStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    tabIndex: 700,
                                    hiddenName: 'fsr_source',
                                    listeners: {
                                        select: function () {
                                            var fsr_source = Ext.getCmp('fsr_source').getValue();
                                            Ext.getCmp('fsr_destination').getStore().load({
                                                params: {
                                                    br_cpd: fsr_source
                                                }
                                            });
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.50,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    id: 'fsr_destination',
                                    name: 'fsr_destination',
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    fieldLabel: 'To',
                                    editable: true,
                                    anchor: '97%',
                                    store: branchStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    tabIndex: 701,
                                    hiddenName: 'fsr_destination',
                                    listeners: {
                                        select: function () {
                                            var fsr_destination = Ext.getCmp('fsr_destination').getValue();

                                            Ext.getCmp('fsr_ItemId').getStore().load({
                                                params: {
                                                    sourceBranch: fsr_destination
                                                }
                                            });
                                        }
                                    }
                                }]
                        }]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.65,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Items',
                                    id: 'fsr_ItemId',
                                    name: 'fsr_ItemId',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    editable: true,
                                    anchor: '98%',
                                    store: itemsStore,
                                    triggerAction: 'all',
                                    //hideTrigger: true,
                                    minChars: 1,
                                    selectOnFocus: false,
                                    displayField: 'stit_SKU',
                                    valueField: 'stit_ID',
                                    hiddenName: 'fsr_ItemId',
                                    tabIndex: 702,
                                    listeners: {
                                        select: function (cmbo, record, index) {
                                            var fpod_leastSKUmrp = record.data.fpod_leastSKUmrp;
                                            Ext.getCmp('fsr_ItemMRP').setValue(fpod_leastSKUmrp);
                                            var itemId = Ext.getCmp('fsr_ItemId').getValue();
                                            if (itemId > 0) {
                                                Ext.getCmp('fsr_StockItemUnits').getStore().load({
                                                    params: {
                                                        itemId: itemId
                                                    }
                                                });

                                            }

                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'MRP',
                                    hidden: true,
                                    id: 'fsr_ItemMRP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Quantity',
                                    id: 'fsr_RequiredItemQty',
                                    name: 'fsr_RequiredItemQty',
                                    allowBlank: false,
                                    tabIndex: 703,
                                    anchor: '97%',
                                    listeners: {
                                        change: function () {
                                            var fsr_StockItemUnits = Ext.getCmp('fsr_StockItemUnits').getValue();
                                            var itemId = Ext.getCmp('fsr_ItemId').getValue();
                                            var fsr_RequiredItemQty = Ext.getCmp('fsr_RequiredItemQty').getValue();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=getPackingDetails',
                                                params: {fsr_StockItemUnits: fsr_StockItemUnits, itemId: itemId, fsr_RequiredItemQty: fsr_RequiredItemQty},
                                                failure: function (response) {
                                                    Ext.MessageBox.alert('Error', response.responseText);

                                                },
                                                success: function (response, options) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true) {
                                                        Ext.getCmp('leastSKUDisplayfsr').show();
                                                        Ext.getCmp('fsrleast_package_type_id').setValue(tmp.least_package_type_id);
                                                        Ext.getCmp('fsrleast_package_type_name').setValue(tmp.least_package_type_name);
                                                        Ext.getCmp('leastSKUCountfsr').setValue(tmp.leastSKUqty);

                                                        Ext.getCmp('leastSKUDisplayfsr').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                    } else {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            ]

                        }, {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Unit',
                                    hidden: true,
                                    id: 'fsr_RequiredItemUnit',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Units',
                                    id: 'fsr_StockItemUnits',
                                    name: 'fsr_StockItemUnits',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    labelAlign: 'top',
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '97%',
                                    store: packTypeStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    hiddenName: 'fsr_StockItemUnits',
                                    tabIndex: 704,
                                    listeners: {
                                        select: function () {
                                            var fsr_StockItemUnits = Ext.getCmp('fsr_StockItemUnits').getValue();
                                            var itemId = Ext.getCmp('fsr_ItemId').getValue();
                                            var fsr_RequiredItemQty = Ext.getCmp('fsr_RequiredItemQty').getValue();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=getPackingDetails',
                                                params: {fsr_StockItemUnits: fsr_StockItemUnits, itemId: itemId, fsr_RequiredItemQty: fsr_RequiredItemQty},
                                                failure: function (response) {
                                                    Ext.MessageBox.alert('Error', response.responseText);

                                                },
                                                success: function (response, options) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true) {
                                                        Ext.getCmp('leastSKUDisplayfsr').show();
                                                        Ext.getCmp('fsrleast_package_type_id').setValue(tmp.least_package_type_id);
                                                        Ext.getCmp('fsrleast_package_type_name').setValue(tmp.least_package_type_name);
                                                        Ext.getCmp('leastSKUCountfsr').setValue(tmp.leastSKUqty);

                                                        Ext.getCmp('leastSKUDisplayfsr').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                    } else {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    }
                                                }
                                            });
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            items: [{
                                    xtype: 'button',
                                    text: 'Add',
                                    tabIndex: 705,
                                    anchor: '85%',
                                    iconCls: 'finascop_add',
                                    style: 'margin-top:17px;',
                                    handler: function () {

                                        if (!Ext.isEmpty(Ext.getCmp('fsr_ItemId').getValue()) &&
                                                !Ext.isEmpty(Ext.getCmp('fsr_RequiredItemQty').getValue())) {

                                            Ext.Ajax.request({
                                                url: modURL + '&op=addItemstoStockRequest',
                                                method: 'POST',
                                                params: {
                                                    uuid: Application.RetalinStockRequest.Cache.uuid,
                                                    fsr_id: fsr_id,
                                                    fsr_ItemId: Ext.getCmp('fsr_ItemId').getValue(),
                                                    fsr_RequiredItemQty: Ext.getCmp('fsr_RequiredItemQty').getValue(),
                                                    fsr_StockItemUnits: Ext.getCmp('fsr_StockItemUnits').getValue(),
                                                    leastSKUCountfsr: Ext.getCmp('leastSKUCountfsr').getValue(),
                                                    fsrleast_package_type_id: Ext.getCmp('fsrleast_package_type_id').getValue(),
                                                    fsr_ItemMRP: Ext.getCmp('fsr_ItemMRP').getValue(),
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true && tmp.valid === true) {
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineStockRequestItemGridPanel').getStore().load({
                                                            params: {
                                                                uuid: Application.RetalinStockRequest.Cache.uuid,
                                                                fsr_id: fsr_id
                                                            }
                                                        });
                                                        Ext.getCmp('fsr_ItemId').reset();
                                                        Ext.getCmp('fsr_RequiredItemQty').reset();
                                                        Ext.getCmp('fsr_StockItemUnits').reset();
                                                        Ext.getCmp('leastSKUCountfsr').reset();
                                                        Ext.getCmp('leastSKUDisplayfsr').reset();
                                                        Ext.getCmp('leastSKUDisplayfsr').hide();
                                                    } else if (tmp.success === true && tmp.valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.message);
                                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.message);
                                                    } else {
                                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                                    }
                                                },
                                                failure: function (response) {
                                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                                    Ext.MessageBox.alert('Error', tmp.message);
                                                }
                                            });
                                        }
                                        else
                                        {
                                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                                        }
                                    }
                                }]
                        }, {
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.50,
                                    items: [{
                                            xtype: 'label',
                                            html: '&nbsp;'
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.45,
                                    items: [{
                                            xtype: 'displayfield',
                                            fieldLabel: 'Total SKUs',
                                            name: 'leastSKUDisplayfsr',
                                            id: 'leastSKUDisplayfsr',
                                            hidden: true
                                        }, {
                                            xtype: 'hidden',
                                            id: 'fsrleast_package_type_id',
                                            name: 'fsrleast_package_type_id'
                                        },
                                        {
                                            xtype: 'hidden',
                                            id: 'fsrleast_package_type_name',
                                            name: 'fsrleast_package_type_name'
                                        },
                                        {
                                            xtype: 'hidden',
                                            id: 'leastSKUCountfsr',
                                            name: 'leastSKUCountfsr'
                                        }]
                                }]
                        }]
                }]
        });
        return poItemStockformPanel;
    };
    var retalineStockRequestItemStore = function (fsr_id) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listStockRequestItemStore',
            fields: ['uuid', 'fsr_id', 'fsrd_id', 'fsr_ItemId', 'fsr_RequiredItemQty', 'fsrd_status', 'fsr_ItemName', 'fsr_ItemUnits', 'fsr_leastSKUCount', 'least_package_type_id', 'leastSkuUnit', 'unitName'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        uuid: Application.RetalinStockRequest.Cache.uuid,
                        fsr_id: fsr_id
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var createRetalineStockRequestItemStockGrid = function (fsr_id) {

        var retalineStockRequestItemGridStore = retalineStockRequestItemStore(fsr_id);
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineStockRequestItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineStockRequestItemGridPanel',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'fsr_ItemName',
                    width: 150,
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'fsr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Quantity'
                }, {
                    header: 'Unit',
                    sortable: true,
                    dataIndex: 'unitName',
                    align: 'right',
                    tooltip: 'Unit'
                }, {
                    header: 'SKU Qty',
                    sortable: true,
                    dataIndex: 'fsr_leastSKUCount',
                    align: 'right',
                    tooltip: 'SKU Quantity'
                }, {
                    header: 'SKU Unit',
                    sortable: true,
                    dataIndex: 'leastSkuUnit',
                    align: 'right',
                    tooltip: 'SKU Unit'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete Order',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteItem(record.get('fsrd_id'), 'sr');
                            }
                        }
                    ]

                }],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {},
            stripeRows: true,
        });
        return poStockEntryGrid;
    };
    var fsr_ItemId = new Array();

    var fsrd_id = new Array();
    var fsr_ApprovedItemQty = new Array();
    var fsr_ItemName = new Array();
    var fsr_id = new Array();
    var subcategoryname = new Array();
    var ckSelection = function () {
        var ApprovedItemQty = 0;
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    var ind = fsr_ItemId.indexOf(record.get('fsr_ItemId'));
                    if (ind == -1) {
                        ApprovedItemQty -= parseInt(record.get('fsr_leastSKUCount'));
                        Ext.getCmp('nootitemto').setValue(ApprovedItemQty);
                        record.set('checked', 'false');
                    }

                },
                rowselect: function (sm, rowIndex, record) {
                    var ind = fsr_ItemId.indexOf(record.get('fsr_ItemId'));
                    if (ind == -1) {
                        ApprovedItemQty += parseInt(record.get('fsr_leastSKUCount'));
                    }

                    Ext.getCmp('nootitemto').setValue(ApprovedItemQty);
                    record.set('checked', 'true');
                }, selectAll: function () {
                    var ApprovedItemQty = 0;
                    var store = this.grid.getStore();
                    store.each(function (record) {
                        if (record.get('fsr_ItemId') > 0) {
                            ApprovedItemQty += parseInt(record.get('fsr_ApprovedItemQty'));
                        }
                    });
                }
            }
        });
    };

    var _Storetr = function (fsr_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCreateTransferOrderData',
                method: 'post'
            }),
            fields: ['fsr_id', 'fsrd_id', 'fsr_ItemId', 'fsr_RequiredItemQty', 'fsr_ApprovedItemQty', 'fsr_ItemName', 'subcategoryname', 'status_name', 'fsrd_status', 'category_name',
                'packageType', 'fsr_ItemUnits', 'fsr_leastSKUCount', 'least_package_type_id', 'least_package_type'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fsr_id = fsr_id;
                }
            }
        });
        return _Store;
    };
    var removeEnquiry = function (id, fsr_id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=removeEnquiry',
            params: {
                fsrd_id: id,
                fsr_id: fsr_id,
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {

                    Ext.getCmp('gridpanelAddnewBranchIndentdata').getStore().reload();
                    Ext.getCmp('retalineStockRequest_main_panel').getStore().reload();
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var deleteItem = function (id, type) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteItem',
                    params: {
                        fsrd_id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed item');
                            if (type == 'sr') {
                                Ext.getCmp('retalineStockRequestItemGridPanel').getStore().reload();
                            } else {
                                Ext.getCmp('retalineInterBranchItemGridPanel').getStore().reload();
                            }

                        }
                    },
                    failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert('Error', "Error occurred");
                    }
                });
            }
        });

    };
    var AddNewTransferGrid = function (fsr_id) {
        var ck_selection = ckSelection();
        var branchD_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fsr_ItemName'
                }, {
                    type: 'string',
                    dataIndex: 'fsr_ApprovedItemQty'
                },
                {
                    type: 'string',
                    dataIndex: 'category_name'
                },
                {
                    type: 'string',
                    dataIndex: 'subcategoryname'
                },
                {
                    type: 'string',
                    dataIndex: 'status_name'
                },
                {
                    type: 'list',
                    dataIndex: 'status_name',
                    options: ['Requested', 'Ordered', 'Deleted'],
                    value: ['Requested'],
                    phpMode: true
                }]
        });
        branchD_filter.remote = true;
        branchD_filter.autoReload = true;
        var _CreateOrdergridPanel = new Ext.grid.EditorGridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _Storetr(fsr_id),
            autoScroll: true,
            width: winsize.width * 0.6,
            plugins: [branchD_filter],
            height: 250,
            bodyStyle: {"background-color": "white"},
            sm: ck_selection,
            id: 'gridpanelAddnewBranchIndentdata',
            columns: [
                ck_selection,
                {
                    header: 'Item',
                    dataIndex: 'fsr_ItemName',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                },
                {
                    header: 'Qty Requested',
                    dataIndex: 'fsr_RequiredItemQty',
                    sortable: true,
                    tooltip: 'Quantity',
                    hideable: true
                },
                {
                    header: 'Qty Approved',
                    sortable: true,
                    dataIndex: 'fsr_ApprovedItemQty',
                    hideable: false,
                    tooltip: 'TO Qty',
                    groupable: false,
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                }, {
                    header: 'Unit',
                    dataIndex: 'packageType',
                    sortable: true,
                    tooltip: 'Unit',
                    hideable: false
                }, {
                    header: 'SKU Qty',
                    sortable: true,
                    dataIndex: 'fsr_leastSKUCount',
                    hideable: false,
                    tooltip: 'SKU Qty',
                    groupable: false
                }, {
                    header: 'SKU Unit',
                    dataIndex: 'least_package_type',
                    sortable: true,
                    tooltip: 'SKU Unit',
                    hideable: false
                },
                {
                    header: 'Category',
                    dataIndex: 'category_name',
                    sortable: true,
                    tooltip: 'Category',
                    hideable: false
                },
                {
                    header: 'Sub Category',
                    dataIndex: 'subcategoryname',
                    sortable: true,
                    tooltip: 'Sub Category',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'status_name',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    sortable: false,
                    tooltip: 'Action',
                    hideable: false,
                    groupable: false,
                    items: [
                        {
                            getClass: function (v, meta, rec) {
                                if (rec.get('fsrd_status') == 1) {
                                    this.items[0].tooltip = 'Delete Item';
                                    return 'remove-enquiry';
                                }
                                else {
                                    return 'hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                var _active = record.data.fsrd_status
                                Ext.MessageBox.confirm('Confirm', 'Do you want to remove this?', function (btn, text) {
                                    if (btn == 'yes') {
                                        Application.example.msg('Success', 'Removed item');
                                        removeEnquiry(record.get('fsrd_id'), record.get('fsr_id'));
                                    }
                                });
                            }

                        }
                    ]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelAddnewBranchIndentdata').getStore().load({
                        params: {
                            fsr_id: fsr_id

                        }
                    });
                },
                afteredit: function (grid_event) {
                    updateToItemQuantity(grid_event);
                }
            }
        });
        return _CreateOrdergridPanel;
    };
    function updateToItemQuantity(grid_event, labId) {
        var data = Ext.encode(grid_event.record.data);
        Ext.Ajax.request({
            waitMsg: 'Please wait...',
            url: modURL + '&op=updateToItemQuantity',
            params: {
                data: Ext.encode(grid_event.record.data),
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', 'Save Failed');
            },
            success: function (response, options) {
                if (response.responseText != "") {
                    eval('var tmp=' + response.responseText);
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Application.example.msg('Notification', tmp.msg);
                    } else if (tmp.success === false) {
                        Ext.MessageBox.alert('Notification', 'Error Occured while saving', function (btn) {
                            if (btn == 'ok') {
                            }
                        });
                    }
                }
            }
        });
    }
    var createRetalineStockRequest = function (fsr_id) {
        var resultWindow = new Ext.Window({
            id: "windowRetalineStockRequest",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 700,
            title: 'Create New Stock Request',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 200,
                    items: createRetalineStockRequestform(fsr_id)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 400,
                    items: createRetalineStockRequestItemStockGrid(fsr_id)
                }],
            buttons: [
                {
                    text: 'Save & Close',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {
                            var store_fields = Ext.getCmp('retalineStockRequestItemGridPanel').getStore().data;
                            if (store_fields.length > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=saveStockRequest',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.RetalinStockRequest.Cache.uuid,
                                        fsr_id: fsr_id,
                                        fsr_source: Ext.getCmp('fsr_source').getValue(),
                                        fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            Application.RetalinStockRequest.Cache.uuid = '';
                                            Ext.getCmp('retalineStockRequest_main_panel').getStore().reload();
                                            resultWindow.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true && tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.message);
                                    }
                                });
                            } else
                            {
                                Ext.MessageBox.alert("Notification", 'No items to save.');
                            }


                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }, {
                    text: 'Confirm & Send',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {

                            Ext.Ajax.request({
                                url: modURL + '&op=saveStockRequest',
                                method: 'POST',
                                params: {
                                    uuid: Application.RetalinStockRequest.Cache.uuid,
                                    fsr_id: fsr_id,
                                    fsr_source: Ext.getCmp('fsr_source').getValue(),
                                    fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        var store_fields = Ext.getCmp('retalineStockRequestItemGridPanel').getStore().data;
                                        if (store_fields.length > 0) {
                                            fsr_ItemId = [];
                                            fsrd_id = [];
                                            fsr_ApprovedItemQty = [];
                                            fsr_leastSKUCount = [];
                                            fsr_ItemName = [];
                                            subcategoryname = [];
                                            for (var i = 0; i < store_fields.length; i++) {
                                                fsr_ItemId[i] = store_fields.items[i].data.fsr_ItemId;
                                                fsrd_id[i] = store_fields.items[i].data.fsrd_id;
                                                fsr_ApprovedItemQty[i] = store_fields.items[i].data.fsr_ApprovedItemQty;
                                                fsr_leastSKUCount[i] = store_fields.items[i].data.fsr_leastSKUCount;
                                                fsr_ItemName[i] = store_fields.items[i].data.fsr_ItemName;
                                                subcategoryname[i] = store_fields.items[i].data.subcategoryname;
                                            }
                                            Ext.Ajax.request({
                                                url: modURL + '&op=createTransferRequest',
                                                method: 'POST',
                                                params: {
                                                    fsr_ItemId: Ext.encode(fsr_ItemId),
                                                    fsrd_id: Ext.encode(fsrd_id),
                                                    fsr_ApprovedItemQty: Ext.encode(fsr_ApprovedItemQty),
                                                    fsr_leastSKUCount: Ext.encode(fsr_leastSKUCount),
                                                    fsr_ItemName: Ext.encode(fsr_ItemName),
                                                    fsr_id: tmp.fsrId,
                                                    subcategoryname: Ext.encode(subcategoryname),
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineStockRequest_main_panel').getStore().reload();
                                                        resultWindow.close();
                                                    }
                                                    else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }
                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert('Notification', 'Please select Items');
                                        }



                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }
            ], listeners: {
                afterrender: function () {

                }
            }

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var editRetalineStockRequest = function (fsr_id) {
        var resultWindow = new Ext.Window({
            id: "windowRetalineStockRequest",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 700,
            title: 'Stock Request',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 200,
                    items: createRetalineStockRequestform(fsr_id)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 400,
                    items: createRetalineStockRequestItemStockGrid(fsr_id)
                }],
            buttons: [
                {
                    text: 'Save & Close',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {
                            var store_fields = Ext.getCmp('retalineStockRequestItemGridPanel').getStore().data;
                            if (store_fields.length > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=saveStockRequest',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.RetalinStockRequest.Cache.uuid,
                                        fsr_id: fsr_id,
                                        fsr_source: Ext.getCmp('fsr_source').getValue(),
                                        fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            Application.RetalinStockRequest.Cache.uuid = '';
                                            Ext.getCmp('retalineStockRequest_main_panel').getStore().reload();
                                            resultWindow.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true && tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.message);
                                    }
                                });
                            } else
                            {
                                Ext.MessageBox.alert("Notification", 'No items to save.');
                            }


                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }, {
                    text: 'Confirm & Send',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {

                            Ext.Ajax.request({
                                url: modURL + '&op=saveStockRequest',
                                method: 'POST',
                                params: {
                                    uuid: Application.RetalinStockRequest.Cache.uuid,
                                    fsr_id: fsr_id,
                                    fsr_source: Ext.getCmp('fsr_source').getValue(),
                                    fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        var store_fields = Ext.getCmp('retalineStockRequestItemGridPanel').getStore().data;
                                        if (store_fields.length > 0) {
                                            fsr_ItemId = [];
                                            fsrd_id = [];
                                            fsr_ApprovedItemQty = [];
                                            fsr_leastSKUCount = [];
                                            fsr_ItemName = [];
                                            subcategoryname = [];
                                            for (var i = 0; i < store_fields.length; i++) {
                                                fsr_ItemId[i] = store_fields.items[i].data.fsr_ItemId;
                                                fsrd_id[i] = store_fields.items[i].data.fsrd_id;
                                                fsr_ApprovedItemQty[i] = store_fields.items[i].data.fsr_ApprovedItemQty;
                                                fsr_leastSKUCount[i] = store_fields.items[i].data.fsr_leastSKUCount;
                                                fsr_ItemName[i] = store_fields.items[i].data.fsr_ItemName;
                                                subcategoryname[i] = store_fields.items[i].data.subcategoryname;
                                            }
                                            Ext.Ajax.request({
                                                url: modURL + '&op=createTransferRequest',
                                                method: 'POST',
                                                params: {
                                                    fsr_ItemId: Ext.encode(fsr_ItemId),
                                                    fsrd_id: Ext.encode(fsrd_id),
                                                    fsr_ApprovedItemQty: Ext.encode(fsr_ApprovedItemQty),
                                                    fsr_leastSKUCount: Ext.encode(fsr_leastSKUCount),
                                                    fsr_ItemName: Ext.encode(fsr_ItemName),
                                                    fsr_id: tmp.fsrId,
                                                    subcategoryname: Ext.encode(subcategoryname),
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineStockRequest_main_panel').getStore().reload();
                                                        resultWindow.close();
                                                    }
                                                    else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }
                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert('Notification', 'Please select Items');
                                        }



                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }
            ], listeners: {
                afterrender: function () {

                }
            }

        });
        Ext.Ajax.request({
            url: modURL + '&op=loadStockRequest',
            params: {
                fsr_id: fsr_id
            },
            method: 'POST',
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                Ext.getCmp('fsr_source').setValue(tmp.fsr_source);
                Ext.getCmp('fsr_destination').setValue(tmp.fsr_destination);
                var fsr_destination = Ext.getCmp('fsr_destination').getValue();

                Ext.getCmp('fsr_ItemId').getStore().load({
                    params: {
                        sourceBranch: fsr_destination
                    }
                });

            }
        });
        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var ViewTransferGrid = function (fsr_id) {
        var viewTeansfer_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fsr_ItemName'
                }, {
                    type: 'string',
                    dataIndex: 'fsr_ApprovedItemQty'
                },
                {
                    type: 'string',
                    dataIndex: 'category_name'
                },
                {
                    type: 'string',
                    dataIndex: 'subcategoryname'
                },
                {
                    type: 'string',
                    dataIndex: 'status_name'
                },
                {
                    type: 'list',
                    dataIndex: 'status_name',
                    options: ['Requested', 'Ordered', 'Deleted'],
                    phpMode: true
                }]
        });
        viewTeansfer_filter.remote = true;
        viewTeansfer_filter.autoReload = true;
        var _CreateOrdergridPanel = new Ext.grid.EditorGridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _Storetr(fsr_id),
            autoScroll: true,
            width: winsize.width * 0.6,
            plugins: [viewTeansfer_filter],
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelVieItemsdata',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    dataIndex: 'fsr_ItemName',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                },
                {
                    header: 'Qty Requested',
                    dataIndex: 'fsr_RequiredItemQty',
                    sortable: true,
                    tooltip: 'Quantity',
                    hideable: true
                }, {
                    header: 'Unit',
                    dataIndex: 'packageType',
                    sortable: true,
                    tooltip: 'Unit',
                    hideable: false
                },
                {
                    header: 'Category',
                    dataIndex: 'category_name',
                    sortable: true,
                    tooltip: 'Category',
                    hideable: false
                },
                {
                    header: 'Sub Category',
                    dataIndex: 'subcategoryname',
                    sortable: true,
                    tooltip: 'Sub Category',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'status_name',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelVieItemsdata').getStore().load({
                        params: {
                            fsr_id: fsr_id
                        }
                    });
                }
            }
        });
        return _CreateOrdergridPanel;
    };
    var retalineInterBranchGrid = function (id) {

        var branch_store = transferBranchStore();

        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fsr_uid'
                }, {
                    type: 'string',
                    dataIndex: 'fsr_createdOn'
                },
                {
                    type: 'string',
                    dataIndex: 'status_name'
                },
                {
                    type: 'string',
                    dataIndex: 'sourcename'
                }, {
                    type: 'string',
                    dataIndex: 'fsr_type'
                }, {
                    type: 'string',
                    dataIndex: 'branch'
                }
            ]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Stock Return',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Stock Request No',
                    id: 'branch_name_auto_expstkintrbrnc',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fsr_uid',
                    tooltip: 'Stock Request No'
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'fsr_createdOn',
                    tooltip: 'Created Date'
                },
                {
                    header: 'Source',
                    sortable: true,
                    dataIndex: 'sourcename',
                    tooltip: 'Source'
                },
                {
                    header: 'Destination',
                    sortable: true,
                    dataIndex: 'branch',
                    tooltip: 'Destination'
                }, {
                    header: 'Initiated Branch',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'initiatedBranch',
                    tooltip: 'Initiated Branch'
                },
                {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'fsr_type',
                    tooltip: 'Type'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'status_name',
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
                            var fstrs_status = Ext.getCmp('retalineInterBranch_main_panel').getSelectionModel().getSelections()[0].data.fstrs_status;
                            stockintrbrncuestActionMenu(e, fstrs_status)
                        }
                    }
                }

            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {
                    if (_SESSION.br_PyramidLevel == 1) {
                        Ext.getCmp("comboxbranchnamestckintrbrnc").show();
                        Ext.getCmp("intrBrnchBranchDataeditCS").hide();
                    } else {
                        Ext.getCmp("comboxbranchnamestckintrbrnc").hide();
                        Ext.getCmp("intrBrnchBranchDataeditCS").show();
                        Ext.getCmp("intrBrnchBranchDataeditCS").setValue(_SESSION.current_branch);
                    }

                }
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Branch Transfer',
                    tooltip: 'Create Branch Transfer',
                    handler: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        Ext.Ajax.request({
                            url: modURL + '&op=generateUniqueId',
                            params: {
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp

                            },
                            method: 'POST',
                            success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                var uid = tmp.uid;
                                Application.RetalinStockRequest.Cache.uuid = uid;
                                createRetalineInterBranch(0);
                            }
                        });

                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'intrBrnchBranchDataeditCS',
                    name: 'intrBrnchBranchDataeditCS',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                }, {
                    xtype: 'combo',
                    id: 'comboxbranchnamestckintrbrnc',
                    name: 'comboxbranchnamestckintrbrnc',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    emptyText: 'Select Branch',
                    fieldLabel: 'Branch Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnamestckintrbrnc',
                    listeners: {
                        select: function () {
                            Ext.getCmp('retalineInterBranch_main_panel').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchnamestckintrbrnc').getValue();
                        Ext.getCmp('retalineInterBranch_main_panel').getStore().load({
                            params: {
                                branchName: branchName
                            }
                        });
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branch_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_expstkintrbrnc'
        });
        return grid_panel;
    };
    var createRetalineInterBranch = function (fsr_id) {
        var resultWindow = new Ext.Window({
            id: "windowRetalineInterBranch",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 700,
            title: 'Create Branch Transfer',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 200,
                    items: createRetalineInterBranchform(fsr_id)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 400,
                    items: createRetalineInterBranchItemStockGrid(fsr_id)
                }],
            buttons: [
                {
                    text: 'Save & Close',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {
                            var store_fields = Ext.getCmp('retalineInterBranchItemGridPanel').getStore().data;
                            if (store_fields.length > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=saveInterBranch',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.RetalinStockRequest.Cache.uuid,
                                        fsr_id: fsr_id,
                                        fsr_source: Ext.getCmp('fsr_source').getValue(),
                                        fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            Application.RetalinStockRequest.Cache.uuid = '';
                                            Ext.getCmp('retalineInterBranch_main_panel').getStore().reload();
                                            resultWindow.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true && tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.message);
                                    }
                                });
                            }

                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }, {
                    text: 'Confirm & Send',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {

                            Ext.Ajax.request({
                                url: modURL + '&op=saveInterBranch',
                                method: 'POST',
                                params: {
                                    uuid: Application.RetalinStockRequest.Cache.uuid,
                                    fsr_id: fsr_id,
                                    fsr_source: Ext.getCmp('fsr_source').getValue(),
                                    fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        var store_fields = Ext.getCmp('retalineInterBranchItemGridPanel').getStore().data;
                                        if (store_fields.length > 0) {
                                            fsr_ItemId = [];
                                            fsrd_id = [];
                                            fsr_ApprovedItemQty = [];
                                            fsr_leastSKUCount = [];
                                            fsr_ItemName = [];
                                            subcategoryname = [];
                                            for (var i = 0; i < store_fields.length; i++) {
                                                fsr_ItemId[i] = store_fields.items[i].data.fsr_ItemId;
                                                fsrd_id[i] = store_fields.items[i].data.fsrd_id;
                                                fsr_ApprovedItemQty[i] = store_fields.items[i].data.fsr_ApprovedItemQty;
                                                fsr_leastSKUCount[i] = store_fields.items[i].data.fsr_leastSKUCount;
                                                fsr_ItemName[i] = store_fields.items[i].data.fsr_ItemName;
                                                subcategoryname[i] = store_fields.items[i].data.subcategoryname;
                                            }
                                            Ext.Ajax.request({
                                                url: modURL + '&op=intrBranchTransferRequest',
                                                method: 'POST',
                                                params: {
                                                    fsr_ItemId: Ext.encode(fsr_ItemId),
                                                    fsrd_id: Ext.encode(fsrd_id),
                                                    fsr_ApprovedItemQty: Ext.encode(fsr_ApprovedItemQty),
                                                    fsr_leastSKUCount: Ext.encode(fsr_leastSKUCount),
                                                    fsr_ItemName: Ext.encode(fsr_ItemName),
                                                    fsr_id: tmp.fsrId,
                                                    subcategoryname: Ext.encode(subcategoryname),
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineInterBranch_main_panel').getStore().reload();
                                                        resultWindow.close();
                                                    }
                                                    else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }
                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert('Notification', 'Please select Items');
                                        }



                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }
            ], listeners: {
                afterrender: function () {

                }
            }

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var createRetalineInterBranchform = function (fsr_id) {

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var itemsStore = intrBrnchItemStore();
        var sourceStore = rbrnchTrsSourceStore(1);
        var branchStore = retBrnchTrnsBranchStore(0);
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 200,
            frame: true,
            border: false,
            id: 'poItemStockItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.50,
                            items: [{
                                    xtype: 'combo',
                                    id: 'fsr_source',
                                    name: 'fsr_source',
                                    mode: 'local',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    fieldLabel: 'Source',
                                    editable: true,
                                    anchor: '97%',
                                    store: sourceStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    tabIndex: 700,
                                    hiddenName: 'fsr_source',
                                    listeners: {
                                        select: function () {
                                            var fsr_source = Ext.getCmp('fsr_source').getValue();
                                            Ext.getCmp('fsr_destination').getStore().load({
                                                params: {
                                                    br_cpd: fsr_source
                                                }
                                            });
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.50,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    id: 'fsr_destination',
                                    name: 'fsr_destination',
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    fieldLabel: 'To',
                                    editable: true,
                                    anchor: '97%',
                                    store: branchStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    tabIndex: 701,
                                    hiddenName: 'fsr_destination',
                                    listeners: {
                                        select: function () {
                                            var fsr_source = Ext.getCmp('fsr_source').getValue();

                                            Ext.getCmp('fsr_ItemId').getStore().load({
                                                params: {
                                                    sourceBranch: fsr_source
                                                }
                                            });
                                        }
                                    }
                                }]
                        }]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.65,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Items',
                                    id: 'fsr_ItemId',
                                    name: 'fsr_ItemId',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    editable: true,
                                    anchor: '98%',
                                    store: itemsStore,
                                    triggerAction: 'all',
                                    //hideTrigger: true,
                                    minChars: 1,
                                    selectOnFocus: false,
                                    displayField: 'stit_SKU',
                                    valueField: 'stit_ID',
                                    hiddenName: 'fsr_ItemId',
                                    tabIndex: 702,
                                    listeners: {
                                        select: function (cmbo, record, index) {
                                            var fpod_leastSKUmrp = record.data.fpod_leastSKUmrp;
                                            Ext.getCmp('fsr_ItemMRP').setValue(fpod_leastSKUmrp);
                                            Ext.getCmp('currentItemCount').setValue(record.data.item_count);
                                            var itemId = Ext.getCmp('fsr_ItemId').getValue();
                                            if (itemId > 0) {
                                                Ext.getCmp('fsr_StockItemUnits').getStore().load({
                                                    params: {
                                                        itemId: itemId
                                                    }
                                                });

                                            }

                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'MRP',
                                    hidden: true,
                                    id: 'fsr_ItemMRP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Item Count',
                                    hidden: true,
                                    id: 'currentItemCount',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Quantity',
                                    id: 'fsr_RequiredItemQty',
                                    name: 'fsr_RequiredItemQty',
                                    allowBlank: false,
                                    tabIndex: 703,
                                    anchor: '97%',
                                    listeners: {
                                        change: function () {
                                            var currentItemCount = Ext.getCmp('currentItemCount').getValue();
                                            var fsr_StockItemUnits = Ext.getCmp('fsr_StockItemUnits').getValue();
                                            var itemId = Ext.getCmp('fsr_ItemId').getValue();
                                            var fsr_RequiredItemQty = Ext.getCmp('fsr_RequiredItemQty').getValue();
                                            if (fsr_StockItemUnits > 0 && itemId > 0 && fsr_RequiredItemQty > 0) {
                                                Ext.Ajax.request({
                                                    url: modURL + '&op=getPackingDetails',
                                                    params: {fsr_StockItemUnits: fsr_StockItemUnits, itemId: itemId, fsr_RequiredItemQty: fsr_RequiredItemQty},
                                                    failure: function (response) {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    },
                                                    success: function (response, options) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        if (tmp.success === true) {
                                                            if (parseFloat(fsr_RequiredItemQty) <= parseFloat(currentItemCount)) {
                                                                Ext.getCmp('leastSKUDisplayfsr').show();
                                                                Ext.getCmp('fsrleast_package_type_id').setValue(tmp.least_package_type_id);
                                                                Ext.getCmp('fsrleast_package_type_name').setValue(tmp.least_package_type_name);
                                                                Ext.getCmp('leastSKUCountfsr').setValue(tmp.leastSKUqty);

                                                                Ext.getCmp('leastSKUDisplayfsr').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                            } else {
                                                                Ext.MessageBox.alert('Notification', "Item stock is only " + currentItemCount + ' ' + tmp.least_package_type_name);
                                                                Ext.getCmp('fsr_RequiredItemQty').reset();
                                                                Ext.getCmp('fsrleast_package_type_id').reset();
                                                                Ext.getCmp('fsrleast_package_type_name').reset();
                                                                Ext.getCmp('leastSKUCountfsr').reset();
                                                                Ext.getCmp('leastSKUDisplayfsr').hide();

                                                            }
                                                        } else {
                                                            Ext.MessageBox.alert('Error', response.responseText);

                                                        }
                                                    }
                                                });
                                            }

                                        }
                                    }
                                }
                            ]

                        }, {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Unit',
                                    hidden: true,
                                    id: 'fsr_RequiredItemUnit',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Units',
                                    id: 'fsr_StockItemUnits',
                                    name: 'fsr_StockItemUnits',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    labelAlign: 'top',
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '97%',
                                    store: packTypeStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    hiddenName: 'fsr_StockItemUnits',
                                    tabIndex: 704,
                                    listeners: {
                                        select: function () {
                                            var currentItemCount = Ext.getCmp('currentItemCount').getValue();
                                            var fsr_StockItemUnits = Ext.getCmp('fsr_StockItemUnits').getValue();
                                            var itemId = Ext.getCmp('fsr_ItemId').getValue();
                                            var fsr_RequiredItemQty = Ext.getCmp('fsr_RequiredItemQty').getValue();
                                            if (fsr_StockItemUnits > 0 && itemId > 0 && fsr_RequiredItemQty > 0) {
                                                Ext.Ajax.request({
                                                    url: modURL + '&op=getPackingDetails',
                                                    params: {fsr_StockItemUnits: fsr_StockItemUnits, itemId: itemId, fsr_RequiredItemQty: fsr_RequiredItemQty},
                                                    failure: function (response) {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    },
                                                    success: function (response, options) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        if (tmp.success === true) {
                                                            if (parseFloat(fsr_RequiredItemQty) <= parseFloat(currentItemCount)) {
                                                                Ext.getCmp('leastSKUDisplayfsr').show();
                                                                Ext.getCmp('fsrleast_package_type_id').setValue(tmp.least_package_type_id);
                                                                Ext.getCmp('fsrleast_package_type_name').setValue(tmp.least_package_type_name);
                                                                Ext.getCmp('leastSKUCountfsr').setValue(tmp.leastSKUqty);

                                                                Ext.getCmp('leastSKUDisplayfsr').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                            } else {
                                                                Ext.MessageBox.alert('Notification', "Item stock is only " + currentItemCount + ' ' + tmp.least_package_type_name);
                                                                Ext.getCmp('fsr_RequiredItemQty').reset();
                                                                Ext.getCmp('fsrleast_package_type_id').reset();
                                                                Ext.getCmp('fsrleast_package_type_name').reset();
                                                                Ext.getCmp('leastSKUCountfsr').reset();
                                                                Ext.getCmp('leastSKUDisplayfsr').hide();

                                                            }

                                                        } else {
                                                            Ext.MessageBox.alert('Error', response.responseText);

                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            items: [{
                                    xtype: 'button',
                                    text: 'Add',
                                    tabIndex: 705,
                                    anchor: '85%',
                                    iconCls: 'finascop_add',
                                    style: 'margin-top:17px;',
                                    handler: function () {

                                        if (!Ext.isEmpty(Ext.getCmp('fsr_ItemId').getValue()) &&
                                                !Ext.isEmpty(Ext.getCmp('fsr_RequiredItemQty').getValue())) {

                                            Ext.Ajax.request({
                                                url: modURL + '&op=addItemstoInterBranch',
                                                method: 'POST',
                                                params: {
                                                    uuid: Application.RetalinStockRequest.Cache.uuid,
                                                    fsr_id: fsr_id,
                                                    fsr_ItemId: Ext.getCmp('fsr_ItemId').getValue(),
                                                    fsr_RequiredItemQty: Ext.getCmp('fsr_RequiredItemQty').getValue(),
                                                    fsr_StockItemUnits: Ext.getCmp('fsr_StockItemUnits').getValue(),
                                                    leastSKUCountfsr: Ext.getCmp('leastSKUCountfsr').getValue(),
                                                    fsrleast_package_type_id: Ext.getCmp('fsrleast_package_type_id').getValue(),
                                                    fsr_ItemMRP: Ext.getCmp('fsr_ItemMRP').getValue(),
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true && tmp.valid === true) {
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineInterBranchItemGridPanel').getStore().load({
                                                            params: {
                                                                uuid: Application.RetalinStockRequest.Cache.uuid,
                                                                fsr_id: fsr_id
                                                            }
                                                        });
                                                        Ext.getCmp('fsr_ItemId').reset();
                                                        Ext.getCmp('fsr_RequiredItemQty').reset();
                                                        Ext.getCmp('fsr_StockItemUnits').reset();
                                                        Ext.getCmp('leastSKUCountfsr').reset();
                                                        Ext.getCmp('leastSKUDisplayfsr').reset();
                                                        Ext.getCmp('leastSKUDisplayfsr').hide();
                                                    } else if (tmp.success === true && tmp.valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.message);
                                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.message);
                                                    } else {
                                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                                    }
                                                },
                                                failure: function (response) {
                                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                                    Ext.MessageBox.alert('Error', tmp.message);
                                                }
                                            });
                                        }
                                        else
                                        {
                                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                                        }
                                    }
                                }]
                        }, {
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.50,
                                    items: [{
                                            xtype: 'label',
                                            html: '&nbsp;'
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.45,
                                    items: [{
                                            xtype: 'displayfield',
                                            fieldLabel: 'Total SKUs',
                                            name: 'leastSKUDisplayfsr',
                                            id: 'leastSKUDisplayfsr',
                                            hidden: true
                                        }, {
                                            xtype: 'hidden',
                                            id: 'fsrleast_package_type_id',
                                            name: 'fsrleast_package_type_id'
                                        },
                                        {
                                            xtype: 'hidden',
                                            id: 'fsrleast_package_type_name',
                                            name: 'fsrleast_package_type_name'
                                        },
                                        {
                                            xtype: 'hidden',
                                            id: 'leastSKUCountfsr',
                                            name: 'leastSKUCountfsr'
                                        }]
                                }]
                        }]
                }]
        });
        return poItemStockformPanel;
    };
    var retalineInterBranchItemStore = function (fsr_id) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listInterBranchItemStore',
            fields: ['uuid', 'fsr_id', 'fsrd_id', 'fsr_ItemId', 'fsr_RequiredItemQty', 'fsrd_status', 'fsr_ItemName', 'fsr_ItemUnits', 'fsr_leastSKUCount', 'least_package_type_id', 'leastSkuUnit', 'unitName'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        uuid: Application.RetalinStockRequest.Cache.uuid,
                        fsr_id: fsr_id
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var createRetalineInterBranchItemStockGrid = function (fsr_id) {

        var retalineInterBranchItemGridStore = retalineInterBranchItemStore(fsr_id);
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineInterBranchItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineInterBranchItemGridPanel',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'fsr_ItemName',
                    width: 150,
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'fsr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Quantity'
                }, {
                    header: 'Unit',
                    sortable: true,
                    dataIndex: 'unitName',
                    align: 'right',
                    tooltip: 'Unit'
                }, {
                    header: 'SKU Qty',
                    sortable: true,
                    dataIndex: 'fsr_leastSKUCount',
                    align: 'right',
                    tooltip: 'SKU Quantity'
                }, {
                    header: 'SKU Unit',
                    sortable: true,
                    dataIndex: 'leastSkuUnit',
                    align: 'right',
                    tooltip: 'SKU Unit'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete Order',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteItem(record.get('fsrd_id'), 'ib');
                            }
                        }
                    ]

                }],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {},
            stripeRows: true,
        });
        return poStockEntryGrid;
    };
    var rbrnchTrsSourceStore = function (isCPD) {
        var store = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=intrBranch',
            method: 'post',
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    this.baseParams.type = isCPD;
                }, load: function () {
                    if (_SESSION.br_PyramidLevel != 1) {
                        Ext.getCmp("fsr_source").setValue(_SESSION.finascop_current_branch_id);
                        Ext.getCmp('fsr_source').setHideTrigger(true);
                        Ext.getCmp('fsr_destination').getStore().load({
                            params: {
                                br_cpd: _SESSION.finascop_current_branch_id
                            }
                        });
                    }



                }
            }
        });
        return store;
    };
    var retBrnchTrnsBranchStore = function (isCPD) {
        var store = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=intrBranchDestination',
            method: 'post',
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    this.baseParams.type = isCPD;
                }, load: function (store, rec, opt) {

                }
            }
        });
        return store;
    };
    var transferBranchStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalineInterBranch',
                method: 'post'
            }),
            fields: ['fsr_id', 'fsr_uid', 'fsr_source', 'fsr_destination', 'fstrs_status', 'fsr_createdOn', 'sourcename', 'branch', 'status_name', 'fsr_type', 'initiatedBranch', 'fsr_initiatedBy'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    this.baseParams.branchName = Ext.getCmp('comboxbranchnamestckintrbrnc').getValue();
                }
            }
        });
        store.setDefaultSort('fsr_createdOn', 'DESC');
        return store;
    };
    var stockintrbrncuestActionMenu = function (e, fstrs_status) {
        var stockrequestActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Edit",
                    handler: function () {
                        var fsr_id = Ext.getCmp('retalineInterBranch_main_panel').getSelectionModel().getSelections()[0].data.fsr_id;
                        Application.RetalinStockRequest.Cache.uuid = '';
                        editRetalineInterBrancTransfer(fsr_id);
                    }
                }, {
                    text: "View",
                    handler: function () {
                        var fsr_id = Ext.getCmp('retalineInterBranch_main_panel').getSelectionModel().getSelections()[0].data.fsr_id;
                        Application.RetalinStockRequest.viewTransferItems(fsr_id);
                    }
                }]
        });
        stockrequestActionMenu.showAt(e.getXY());
    };
    var editRetalineInterBrancTransfer = function (fsr_id) {
        var resultWindow = new Ext.Window({
            id: "windowRetalineInterBranch",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 700,
            title: 'Branch Transfer',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 200,
                    items: createRetalineInterBranchform(fsr_id)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 400,
                    items: createRetalineInterBranchItemStockGrid(fsr_id)
                }],
            buttons: [
                {
                    text: 'Save & Close',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {
                            var store_fields = Ext.getCmp('retalineInterBranchItemGridPanel').getStore().data;
                            if (store_fields.length > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=saveInterBranch',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.RetalinStockRequest.Cache.uuid,
                                        fsr_id: fsr_id,
                                        fsr_source: Ext.getCmp('fsr_source').getValue(),
                                        fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            Application.RetalinStockRequest.Cache.uuid = '';
                                            Ext.getCmp('retalineInterBranch_main_panel').getStore().reload();
                                            resultWindow.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true && tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.message);
                                    }
                                });
                            }

                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }, {
                    text: 'Confirm & Send',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fsr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fsr_destination').getValue())) {

                            Ext.Ajax.request({
                                url: modURL + '&op=saveInterBranch',
                                method: 'POST',
                                params: {
                                    uuid: Application.RetalinStockRequest.Cache.uuid,
                                    fsr_id: fsr_id,
                                    fsr_source: Ext.getCmp('fsr_source').getValue(),
                                    fsr_destination: Ext.getCmp('fsr_destination').getValue(),
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        var store_fields = Ext.getCmp('retalineInterBranchItemGridPanel').getStore().data;
                                        if (store_fields.length > 0) {
                                            fsr_ItemId = [];
                                            fsrd_id = [];
                                            fsr_ApprovedItemQty = [];
                                            fsr_leastSKUCount = [];
                                            fsr_ItemName = [];
                                            subcategoryname = [];
                                            for (var i = 0; i < store_fields.length; i++) {
                                                fsr_ItemId[i] = store_fields.items[i].data.fsr_ItemId;
                                                fsrd_id[i] = store_fields.items[i].data.fsrd_id;
                                                fsr_ApprovedItemQty[i] = store_fields.items[i].data.fsr_ApprovedItemQty;
                                                fsr_leastSKUCount[i] = store_fields.items[i].data.fsr_leastSKUCount;
                                                fsr_ItemName[i] = store_fields.items[i].data.fsr_ItemName;
                                                subcategoryname[i] = store_fields.items[i].data.subcategoryname;
                                            }
                                            Ext.Ajax.request({
                                                url: modURL + '&op=intrBranchTransferRequest',
                                                method: 'POST',
                                                params: {
                                                    fsr_ItemId: Ext.encode(fsr_ItemId),
                                                    fsrd_id: Ext.encode(fsrd_id),
                                                    fsr_ApprovedItemQty: Ext.encode(fsr_ApprovedItemQty),
                                                    fsr_leastSKUCount: Ext.encode(fsr_leastSKUCount),
                                                    fsr_ItemName: Ext.encode(fsr_ItemName),
                                                    fsr_id: tmp.fsrId,
                                                    subcategoryname: Ext.encode(subcategoryname),
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineInterBranch_main_panel').getStore().reload();
                                                        resultWindow.close();
                                                    }
                                                    else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }
                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert('Notification', 'Please select Items');
                                        }



                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }
            ], listeners: {
                afterrender: function () {

                }
            }

        });
        Ext.Ajax.request({
            url: modURL + '&op=loadStockRequest',
            params: {
                fsr_id: fsr_id
            },
            method: 'POST',
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                Ext.getCmp('fsr_source').setValue(tmp.fsr_source);
                Ext.getCmp('fsr_destination').setValue(tmp.fsr_destination);
                var fsr_destination = Ext.getCmp('fsr_destination').getValue();
                Ext.getCmp('fsr_destination').getStore().load();

                Ext.getCmp('fsr_ItemId').getStore().load({
                    params: {
                        sourceBranch: fsr_destination
                    }
                });

            }
        });
        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    return{
        Cache: {},
        init: function () {
            var panelId = 'retalineStockRequest_main_panel';
            var branch_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(branch_panel)) {
                branch_panel = retalineStockRequestGrid(panelId);
                Application.UI.addTab(branch_panel);
                branch_panel.doLayout();
            } else {
                Application.UI.addTab(branch_panel);
            }
            return branch_panel;
        },
        AddNewTransferWindow: function (fsr_id) {
            var fsr_id = fsr_id;
            var _addnewtransferWindow = new Ext.Window({
                title: 'Send to Branch Indent',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 900,
                resizable: false,
                draggable: true,
                closable: true,
                id: 'windowTransferRequest',
                bodyStyle: {"background-color": "white"},
                items: [AddNewTransferGrid(fsr_id)],
                buttonAlign: 'left',
                fbar: [{
                        id: 'nootitemto',
                        name: 'nootitemto',
                        hidden: true,
                        allowBlank: false,
                        xtype: 'numberfield',
                        fieldlabel: 'No of Items'
                    }, '->',
                    {
                        text: "Send",
                        cls: 'left-right-buttons',
                        tooltip: 'Send',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        handler: function () {
                            var store_fields = Ext.getCmp('gridpanelAddnewBranchIndentdata').getSelectionModel().getSelections();
                            if (store_fields.length > 0) {
                                fsr_ItemId = [];
                                fsrd_id = [];
                                fsr_ApprovedItemQty = [];
                                fsr_leastSKUCount = [];
                                fsr_ItemName = [];
                                subcategoryname = [];
                                for (var i = 0; i < store_fields.length; i++) {
                                    fsr_ItemId[i] = store_fields[i].data.fsr_ItemId;
                                    fsrd_id[i] = store_fields[i].data.fsrd_id;
                                    fsr_ApprovedItemQty[i] = store_fields[i].data.fsr_ApprovedItemQty;
                                    fsr_leastSKUCount[i] = store_fields[i].data.fsr_leastSKUCount;
                                    fsr_ItemName[i] = store_fields[i].data.fsr_ItemName;
                                    subcategoryname[i] = store_fields[i].data.subcategoryname;
                                }
                                Ext.Ajax.request({
                                    url: modURL + '&op=createTransferRequest',
                                    method: 'POST',
                                    params: {
                                        fsr_ItemId: Ext.encode(fsr_ItemId),
                                        fsrd_id: Ext.encode(fsrd_id),
                                        fsr_ApprovedItemQty: Ext.encode(fsr_ApprovedItemQty),
                                        fsr_leastSKUCount: Ext.encode(fsr_leastSKUCount),
                                        fsr_ItemName: Ext.encode(fsr_ItemName),
                                        fsr_id: fsr_id,
                                        subcategoryname: Ext.encode(subcategoryname),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            var tmp = Ext.decode(response.responseText);
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('retalineStockRequest_main_panel').getStore().load();
                                            Ext.getCmp('windowTransferRequest').close();
                                        }
                                        else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert('Notification', 'Please select Items');
                            }

                        }
                    }

                ]

            });
            _addnewtransferWindow.doLayout();
            _addnewtransferWindow.show();
            _addnewtransferWindow.center();
        },
        viewTransferItems: function (fsr_id) {
            var fsr_id = fsr_id;
            var _addnewtransferWindow = new Ext.Window({
                title: 'View Items',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 600,
                resizable: false,
                draggable: true,
                closable: true,
                id: 'windowTransferRequest',
                bodyStyle: {"background-color": "white"},
                items: [ViewTransferGrid(fsr_id)]
            });
            _addnewtransferWindow.doLayout();
            _addnewtransferWindow.show();
            _addnewtransferWindow.center();
        }, initerBranchTransfer: function () {
            var panelId = 'retalineInterBranch_main_panel';
            var branch_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(branch_panel)) {
                branch_panel = retalineInterBranchGrid(panelId);
                Application.UI.addTab(branch_panel);
                branch_panel.doLayout();
            } else {
                Application.UI.addTab(branch_panel);
            }
            return branch_panel;
        },
    };
}();