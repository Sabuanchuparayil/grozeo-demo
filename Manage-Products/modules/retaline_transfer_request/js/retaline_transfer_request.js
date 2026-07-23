Application.RetalinTransferRequest = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var modURL = '?module=retaline_transfer_request';
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
                url: modURL + '&op=listRetalineTransferRequest',
                method: 'post'
            }),
            fields: ['fstr_id', 'fstr_uid', 'fstr_source', 'fstr_destination', 'fstr_status', 'fstr_createdOn', 'sourcename', 'branch', 'status_name', 'fstr_type', 'initiatedBranch', 'fstr_initiatedBy'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    this.baseParams.branchName = Ext.getCmp('comboxbranchnametr').getValue();
                }
            }
        });
        store.setDefaultSort('fstr_createdOn', 'DESC');
        return store;
    };

    var retalineTransferRequestGrid = function (id) {
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
                    dataIndex: 'fstr_uid'
                }, {
                    type: 'string',
                    dataIndex: 'fstr_createdOn'
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
                    dataIndex: 'fstr_type'
                }, {
                    type: 'string',
                    dataIndex: 'branch'
                }
//                {
//                    type: 'list',
//                    dataIndex: 'status_name',
//                    options: ['Transfer Requested', 'Partially Attended', 'Completely Attended', 'Invoke Expired'],
////                    value:['Transfer Requested'],
//                    phpMode: true
//                }
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
                    id: 'branch_name_auto_exptr',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fstr_uid',
                    tooltip: 'Stock Request No'
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'fstr_createdOn',
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
                    dataIndex: 'fstr_type',
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
                            var fstr_status = Ext.getCmp('retalineTransferRequest_main_panel').getSelectionModel().getSelections()[0].data.fstr_status;
                            stockrequestActionMenu(e, fstr_status)
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 header: 'Action',
                 hideable: false,
                 sortable: false,
                 groupable: false,
                 tooltip: 'Action',
                 items: [{
                 getClass: function (v, meta, rec) {
                 if ((rec.get('fstr_status') == 1) || (rec.get('fstr_status') == 2) || (rec.get('fstr_status') == 5)) {
                 this.items[0].tooltip = 'Create Packing Order';
                 return 'my-icon18';
                 }
                 else {
                 return 'hideicon';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.getStore().getAt(rowIndex);
                 Application.RetalinTransferRequest.AddNewTransferWindow(record.get('fstr_id'));
                 }
                 }, {
                 iconCls: 'my-icon96',
                 tooltip: 'View Item Details',
                 handler: function (grid, rowIndex, colIndex, itm, evn) {
                 var record = grid.getStore().getAt(rowIndex);
                 Application.RetalinTransferRequest.viewTransferItems(record.get('fstr_id'));
                 }
                 }, {
                 iconCls: 'hideicon',
                 handler: function (grid, rowIndex, colIndex, itm, evn) {
                 var record = grid.getStore().getAt(rowIndex);
                 var _active = record.data.fstr_status
                 Ext.MessageBox.confirm('Confirm', 'Are you sure want to remove this?', function (btn, text) {
                 if (btn == 'yes') {
                 removeEnquiryMain(record.get('fstr_id'));
                 }
                 });
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
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {
                    if (_SESSION.br_PyramidLevel == 1) {
                        Ext.getCmp("comboxbranchnametr").show();
                        Ext.getCmp("trBranchDataeditCS").hide();
                        Ext.getCmp('invokeTransferinTR').show();
                    } else {
                        Ext.getCmp("comboxbranchnametr").hide();
                        Ext.getCmp("trBranchDataeditCS").show();
                        Ext.getCmp("trBranchDataeditCS").setValue(_SESSION.current_branch);
                        Ext.getCmp('invokeTransferinTR').hide();
                    }
                    if (_SESSION.br_PyramidLevel < 4) {
                        Ext.getCmp('addnewTR').show();
                    }
                    else {
                        Ext.getCmp('addnewTR').hide();

                    }
                }
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Stock Request',
                    tooltip: 'Create Stock Request',
                    iconCls: 'add',
                    id: 'addnewTR',
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
                                console.log("temp is -", tmp);
                                var uid = tmp.uid;
                                Application.RetalinTransferRequest.Cache.uuid = uid;
                                createRetalineTransferRequest(0);
                            }
                        });

                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'trBranchDataeditCS',
                    name: 'trBranchDataeditCS',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                }, {
                    xtype: 'combo',
                    id: 'comboxbranchnametr',
                    name: 'comboxbranchnametr',
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
                    hiddenName: 'comboxbranchnametr',
                    listeners: {
                        select: function () {
                            Ext.getCmp('retalineTransferRequest_main_panel').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    id: 'idshowbtntr',
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchnametr').getValue();
                        Ext.getCmp('retalineTransferRequest_main_panel').getStore().load({
                            params: {
                                branchName: branchName
                            }
                        });
                    }
                }, {
                    xtype: 'button',
                    text: 'Invoke Transfer',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    id: 'invokeTransferinTR',
                    handler: function () {
                        setTimeout(function () {
                            Ext.Ajax.request({
                                waitMsg: 'Processing',
                                url: itmodURL,
                                params: {
                                    op: 'schedulerinvoke'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    console.log('tmp', tmp);
                                    //if (tmp.status == 'ok') {
                                    if (tmp.status == true) {
                                        Application.example.msg('Success', tmp.msg);
                                    } else {
                                        Ext.Msg.alert("Notification", tmp.msg);
                                        // Ext.Msg.alert("Error", tmp.error.msg);
                                    }
                                }
                            });
                        }, 100);

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
            autoExpandColumn: 'branch_name_auto_exptr'
        });
        return grid_panel;
    };
//    var stockrequestActionMenu = new Ext.menu.Menu({
//        items: [{
//                text: "View",
//                handler: function () {
//                    var fstr_id = Ext.getCmp('retalineTransferRequest_main_panel').getSelectionModel().getSelections()[0].data.fstr_id;
//                    Application.RetalinTransferRequest.viewTransferItems(fstr_id);
//                }
//            },
//            {
//                text: "Create",
//                handler: function () {
//                    var fstr_id = Ext.getCmp('retalineTransferRequest_main_panel').getSelectionModel().getSelections()[0].data.fstr_id;
//                    Application.RetalinTransferRequest.AddNewTransferWindow(fstr_id);
//                }
//            }
//            /*{
//                            iconCls: 'hideicon',
//                            handler: function () {
//                                //var _active = fstr_status
//                                Ext.MessageBox.confirm('Confirm', 'Are you sure want to remove this?', function (btn, text) {
//                                    if (btn == 'yes') {
//                                        removeEnquiryMain(fstr_id);
//                                    }
//                                });
//                            }
//                        }*/
//                    ]
//    });
    var stockrequestActionMenu = function (e, fstr_status) {
        var stockrequestActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "View",
                    handler: function () {
                        var fstr_id = Ext.getCmp('retalineTransferRequest_main_panel').getSelectionModel().getSelections()[0].data.fstr_id;
                        Application.RetalinTransferRequest.viewTransferItems(fstr_id);
                    }
                },
                {
                    text: "Create",
                    hidden: !(fstr_status == 1 || fstr_status == 2 || fstr_status == 5),
                    handler: function () {
                        var fstr_id = Ext.getCmp('retalineTransferRequest_main_panel').getSelectionModel().getSelections()[0].data.fstr_id;
                        Application.RetalinTransferRequest.AddNewTransferWindow(fstr_id);
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
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    this.baseParams.type = isCPD;
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
                }
            }
        });
        return store;
    };
    var rtItemStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU', 'packageName','id','fsbg_id','fpod_leastSKUmrp'],
            url: modURL + '&op=getItemName',
            autoLoad: true,
            method: 'post'
        });
        return store;
    };
    var packTypeStore = new Ext.data.JsonStore({
        url: modURL + '&op=getPTStore',
        fields: ['package_type_id', 'package_type_name'],
        totalProperty: 'totalCount',
        root: 'data',
        autoload: true,
        remoteFilter: true,
        listeners: {
            beforeload: function () {
            }
        }
    });
    var createRetalineTransferRequestform = function (fstr_id) {

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
                            id: 'fstr_source',
                            name: 'fstr_source',
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
                            hiddenName: 'fstr_source',
                            listeners: {
//                                afterrender: function (field) {
//                                        Ext.defer(function () {
//                                        field.focus(true, 100);
//                                    }, 1);
//                                        },
                                select: function () {
                                    var fstr_source = Ext.getCmp('fstr_source').getValue();
                                    Ext.getCmp('fstr_destination').getStore().load({
                                        params: {
                                            br_cpd: fstr_source
                                        }
                                    });
                                    Ext.getCmp('fstr_ItemId').getStore().load({
                                        params: {
                                            sourceBranch: fstr_source
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
                            id: 'fstr_destination',
                            name: 'fstr_destination',
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
                            hiddenName: 'fstr_destination',
                            listeners: {
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
                            id: 'fstr_ItemId',
                            name: 'fstr_ItemId',
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
                            hiddenName: 'fstr_ItemId',
                            tabIndex: 702,
                            listeners: {
                                select: function (cmbo, record, index) {
                                    var fpod_leastSKUmrp = record.data.fpod_leastSKUmrp;
                                    Ext.getCmp('fstr_ItemMRP').setValue(fpod_leastSKUmrp);
                                    var itemId = Ext.getCmp('fstr_ItemId').getValue();
                                    if (itemId > 0) {
                                        Ext.getCmp('fstr_StockItemUnits').getStore().load({
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
                            id: 'fstr_ItemMRP',
                            style: {'font-weight': 'bold'},
                            anchor: '97%',
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Quantity',
                            id: 'fstr_RequiredItemQty',
                            name: 'fstr_RequiredItemQty',
                            allowBlank: false,
                            tabIndex: 703,
                            anchor: '97%'
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
                            id: 'fstr_RequiredItemUnit',
                            style: {'font-weight': 'bold'},
                            anchor: '97%',
                        }, {
                            xtype: 'combo',
                            fieldLabel: 'Units',
                            id: 'fstr_StockItemUnits',
                            name: 'fstr_StockItemUnits',
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
                            hiddenName: 'fstr_StockItemUnits',
                            tabIndex: 704, 
                            listeners: {
                                select: function () {
                                    var fstr_StockItemUnits = Ext.getCmp('fstr_StockItemUnits').getValue();
                                    var itemId = Ext.getCmp('fstr_ItemId').getValue();
                                    var fstr_RequiredItemQty = Ext.getCmp('fstr_RequiredItemQty').getValue();
                                    Ext.Ajax.request({
                                        url: modURL + '&op=getPackingDetails',
                                        params: {fstr_StockItemUnits: fstr_StockItemUnits, itemId: itemId, fstr_RequiredItemQty: fstr_RequiredItemQty},
                                        failure: function (response) {
                                            Ext.MessageBox.alert('Error', response.responseText);

                                        },
                                        success: function (response, options) {
                                            var tmp = Ext.decode(response.responseText);
                                            console.log('tmp', tmp);
                                            if (tmp.success === true) {
                                                console.log('here');
                                                Ext.getCmp('leastSKUDisplayfstr').show();
                                                Ext.getCmp('fstrleast_package_type_id').setValue(tmp.least_package_type_id);
                                                Ext.getCmp('fstrleast_package_type_name').setValue(tmp.least_package_type_name);
                                                Ext.getCmp('leastSKUCountfstr').setValue(tmp.leastSKUqty);

                                                Ext.getCmp('leastSKUDisplayfstr').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
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

                                if (!Ext.isEmpty(Ext.getCmp('fstr_ItemId').getValue()) &&
                                        !Ext.isEmpty(Ext.getCmp('fstr_RequiredItemQty').getValue())) {

                                    Ext.Ajax.request({
                                        url: modURL + '&op=addItemstoTransferRequest',
                                        method: 'POST',
                                        params: {
                                            uuid: Application.RetalinTransferRequest.Cache.uuid,
                                            fstr_id: fstr_id,
                                            fstr_ItemId: Ext.getCmp('fstr_ItemId').getValue(),
                                            fstr_RequiredItemQty: Ext.getCmp('fstr_RequiredItemQty').getValue(),
                                            fstr_StockItemUnits: Ext.getCmp('fstr_StockItemUnits').getValue(),
                                            leastSKUCountfstr: Ext.getCmp('leastSKUCountfstr').getValue(),
                                            fstrleast_package_type_id: Ext.getCmp('fstrleast_package_type_id').getValue(),
                                            fstr_ItemMRP:Ext.getCmp('fstr_ItemMRP').getValue(),
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', tmp.msg);
                                                Ext.getCmp('retalineTransferRequestItemGridPanel').getStore().load({
                                                    params: {
                                                        uuid: Application.RetalinTransferRequest.Cache.uuid,
                                                        fstr_id: fstr_id
                                                    }
                                                });
                                                Ext.getCmp('fstr_ItemId').reset();
                                                Ext.getCmp('fstr_RequiredItemQty').reset();
                                                Ext.getCmp('fstr_StockItemUnits').reset();
                                                Ext.getCmp('leastSKUCountfstr').reset();
                                                Ext.getCmp('leastSKUDisplayfstr').reset();
                                                Ext.getCmp('leastSKUDisplayfstr').hide();
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
                                    name: 'leastSKUDisplayfstr',
                                    id: 'leastSKUDisplayfstr',
                                    hidden: true
                                }, {
                                    xtype: 'hidden',
                                    id: 'fstrleast_package_type_id',
                                    name: 'fstrleast_package_type_id'
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'fstrleast_package_type_name',
                                    name: 'fstrleast_package_type_name'
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'leastSKUCountfstr',
                                    name: 'leastSKUCountfstr'
                                }]
                        }]
                }]
        }]
        });
        return poItemStockformPanel;
    };
    var retalineTransferRequestItemStore = function (fstr_id) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listTransferRequestItemStore',
            fields: ['uuid', 'fstr_id', 'fstrd_id', 'fstr_ItemId', 'fstr_RequiredItemQty', 'fstrd_status', 'fstr_ItemName', 'fstr_ItemUnits', 'fstr_leastSKUCount', 'least_package_type_id', 'leastSkuUnit', 'unitName'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        uuid: Application.RetalinTransferRequest.Cache.uuid,
                        fstr_id: fstr_id
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var createRetalineTransferRequestItemStockGrid = function (fstr_id) {

        var retalineTransferRequestItemGridStore = retalineTransferRequestItemStore(fstr_id);
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineTransferRequestItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineTransferRequestItemGridPanel',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'fstr_ItemName',
                    width: 150,
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
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
                    dataIndex: 'fstr_leastSKUCount',
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
                                deleteItem(record.get('fstrd_id'));
                            }
                        },
//                        {
//                            iconCls: 'my-icon18',
//                            tooltip: 'Edit Order',
//                            handler: function (grid, rowIndex, colIndex) {
//                                var record = grid.store.getAt(rowIndex);
//
//                            }
//                        }
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
    var fstr_ItemId = new Array();

    var fstrd_id = new Array();
    var fstr_ApprovedItemQty = new Array();
    var fstr_ItemName = new Array();
    var fstr_id = new Array();
    var subcategoryname = new Array();
    var ckSelection = function () {
        var ApprovedItemQty = 0;
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    var ind = fstr_ItemId.indexOf(record.get('fstr_ItemId'));
                    console.log("ind:", ind);
                    console.log("rowIndex:", rowIndex);
                    console.log("sm:", sm);
                    console.log("record:", record);
                    if (ind == -1) {
                        ApprovedItemQty -= parseInt(record.get('fstr_leastSKUCount'));
                        console.log('rowdeselect', ApprovedItemQty);
                        Ext.getCmp('nootitemto').setValue(ApprovedItemQty);
                        //if (ind > -1)
                        //  fstr_ItemId.splice(ind, 1);
                        record.set('checked', 'false');
                    }

                },
                rowselect: function (sm, rowIndex, record) {
                    var ind = fstr_ItemId.indexOf(record.get('fstr_ItemId'));
                    console.log("ind:", ind);
                    console.log("rowIndex:", rowIndex);
                    console.log("sm:", sm);
                    console.log("record:", record);
                    console.log("rowselect:", ind);
                    if (ind == -1) {
                        ApprovedItemQty += parseInt(record.get('fstr_leastSKUCount'));
                    }

                    console.log('rowselect', ApprovedItemQty);
                    Ext.getCmp('nootitemto').setValue(ApprovedItemQty);
                    //if (ind == -1)
                    //fstr_ItemId.push(record.get('fstr_ItemId'));
                    record.set('checked', 'true');
                }, selectAll: function () {
                    console.log('Selectall');
                    var ApprovedItemQty = 0;
                    var store = this.grid.getStore();
                    store.each(function (record) {
                        if (record.get('fstr_ItemId') > 0) {
                            ApprovedItemQty += parseInt(record.get('fstr_ApprovedItemQty'));
                        }
                    });
                }
            }
        });
    }

    var _Storetr = function (fstr_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCreateTransferOrderData',
                method: 'post'
            }),
            fields: ['fstr_id', 'fstrd_id', 'fstr_ItemId', 'fstr_RequiredItemQty', 'fstr_ApprovedItemQty', 'fstr_ItemName', 'subcategoryname', 'status_name', 'fstrd_status', 'category_name',
                'packageType', 'fstr_ItemUnits', 'fstr_leastSKUCount', 'least_package_type_id', 'least_package_type'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fstr_id = fstr_id;
                }
            }
        });
        return _Store;
    };
    var removeEnquiry = function (id, fstr_id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=removeEnquiry',
            params: {
                fstrd_id: id,
                fstr_id: fstr_id,
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {

                    Ext.getCmp('gridpanelAddnewCreateOrderdata').getStore().reload();
                    Ext.getCmp('retalineTransferRequest_main_panel').getStore().reload();
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var deleteItem = function (id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
           if (btn == 'yes') {
                Ext.Ajax.request({
                waitMsg: 'Processing',
                method: 'POST',
                url: modURL + '&op=deleteItem',
                params: {
                    fstrd_id: id
                },
                success: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg('Success', 'Removed item');
                        Ext.getCmp('retalineTransferRequestItemGridPanel').getStore().reload();
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
    var removeEnquiryMain = function (id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=removeEnquiryMain',
            params: {
                fstr_id: id
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {

                    Ext.getCmp('retalineTransferRequest_main_panel').getStore().reload();

                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var AddNewTransferGrid = function (fstr_id) {
        var ck_selection = ckSelection();
        var branchD_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fstr_ItemName'
                }, {
                    type: 'string',
                    dataIndex: 'fstr_ApprovedItemQty'
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
            store: _Storetr(fstr_id),
            autoScroll: true,
            width: winsize.width * 0.6,
            plugins: [branchD_filter],
            height: 250,
            bodyStyle: {"background-color": "white"},
            sm: ck_selection,
            id: 'gridpanelAddnewCreateOrderdata',
            columns: [
                ck_selection,
                {
                    header: 'Item',
                    dataIndex: 'fstr_ItemName',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                },
                {
                    header: 'Qty Requested',
                    dataIndex: 'fstr_RequiredItemQty',
                    sortable: true,
                    tooltip: 'Quantity',
                    hideable: true
                },
                {
                    header: 'Qty Approved',
                    sortable: true,
                    dataIndex: 'fstr_ApprovedItemQty',
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
                },{
                    header: 'SKU Qty',
                    sortable: true,
                    dataIndex: 'fstr_leastSKUCount',
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
                    header: 'Category / System',
                    dataIndex: 'category_name',
                    sortable: true,
                    tooltip: 'Category / System',
                    hideable: false
                },
                {
                    header: 'Sub Category/ Drug Group',
                    dataIndex: 'subcategoryname',
                    sortable: true,
                    tooltip: 'Sub Category/ Drug Group',
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
                                if (rec.get('fstrd_status') == 1) {
                                    this.items[0].tooltip = 'Delete Item';
                                    return 'remove-enquiry';
                                }
                                else {
                                    return 'hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                var _active = record.data.fstrd_status
                                Ext.MessageBox.confirm('Confirm', 'Do you want to remove this?', function (btn, text) {
                                    if (btn == 'yes') {
                                        Application.example.msg('Success', 'Removed item');
                                        removeEnquiry(record.get('fstrd_id'), record.get('fstr_id'));
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
                    Ext.getCmp('gridpanelAddnewCreateOrderdata').getStore().load({
                        params: {
                            fstr_id: fstr_id

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
    var createRetalineTransferRequest = function (fstr_id) {
        var resultWindow = new Ext.Window({
            id: "windowRetalineTransferRequest",
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
                    items: createRetalineTransferRequestform(fstr_id)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 400,
                    items: createRetalineTransferRequestItemStockGrid(fstr_id)
                }],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        Ext.getCmp('windowRetalineTransferRequest').close();
                        Ext.getCmp('retalineTransferRequest_main_panel').getStore().load();
                    }

                }, {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('fstr_source').getValue()) &&
                                !Ext.isEmpty(Ext.getCmp('fstr_destination').getValue())) {

                            Ext.Ajax.request({
                                url: modURL + '&op=saveTransferRequest',
                                method: 'POST',
                                params: {
                                    uuid: Application.RetalinTransferRequest.Cache.uuid,
                                    fstr_id: fstr_id,
                                    fstr_source: Ext.getCmp('fstr_source').getValue(),
                                    fstr_destination: Ext.getCmp('fstr_destination').getValue(),
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        Application.example.msg('Success', tmp.msg);
                                        Application.RetalinTransferRequest.Cache.uuid = '';
                                        Ext.getCmp('retalineTransferRequest_main_panel').getStore().reload();
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
    var ViewTransferGrid = function (fstr_id) {
        var viewTeansfer_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fstr_ItemName'
                }, {
                    type: 'string',
                    dataIndex: 'fstr_ApprovedItemQty'
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
            store: _Storetr(fstr_id),
            autoScroll: true,
            width: winsize.width * 0.6,
            plugins: [viewTeansfer_filter],
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelVieItemsdata',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    dataIndex: 'fstr_ItemName',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                },
                {
                    header: 'Qty Requested',
                    dataIndex: 'fstr_RequiredItemQty',
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
                    header: 'Category/System',
                    dataIndex: 'category_name',
                    sortable: true,
                    tooltip: 'Category/System',
                    hideable: false
                },
                {
                    header: 'Sub Category/Drug Group',
                    dataIndex: 'subcategoryname',
                    sortable: true,
                    tooltip: 'Sub Category/Drug Group',
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
                            fstr_id: fstr_id
                        }
                    });
                }
            }
        });
        return _CreateOrdergridPanel;
    };
    return{
        Cache: {},
        init: function () {
            var panelId = 'retalineTransferRequest_main_panel';
            var branch_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(branch_panel)) {
                branch_panel = retalineTransferRequestGrid(panelId);
                Application.UI.addTab(branch_panel);
                branch_panel.doLayout();
            } else {
                Application.UI.addTab(branch_panel);
            }
            return branch_panel;
        },
        AddNewTransferWindow: function (fstr_id) {
            var fstr_id = fstr_id;
            var _addnewtransferWindow = new Ext.Window({
                title: 'Create Packing Order from Request',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 900,
                resizable: false,
                draggable: true,
                closable: true,
                id: 'windowTransferOrder',
                bodyStyle: {"background-color": "white"},
                items: [AddNewTransferGrid(fstr_id)],
                buttonAlign: 'left',
                fbar: [{'html': 'No of Items'}, {
                        id: 'nootitemto',
                        name: 'nootitemto',
                        allowBlank: false,
                        xtype: 'numberfield',
                        fieldlabel: 'No of Items'
                    }, '->',
                    {
                        text: "Create Packing Order",
                        cls: 'left-right-buttons',
                        tooltip: 'Create Packing Order',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        handler: function () {
                            var store_fields = Ext.getCmp('gridpanelAddnewCreateOrderdata').getSelectionModel().getSelections();
                            if (store_fields.length > 0) {
                                fstr_ItemId = [];
                                fstrd_id = [];
                                fstr_ApprovedItemQty = [];
                                 fstr_leastSKUCount = [];
                                fstr_ItemName = [];
                                subcategoryname = [];
                                for (var i = 0; i < store_fields.length; i++) {
                                    fstr_ItemId[i] = store_fields[i].data.fstr_ItemId;
                                    fstrd_id[i] = store_fields[i].data.fstrd_id;
                                    fstr_ApprovedItemQty[i] = store_fields[i].data.fstr_ApprovedItemQty;
                                    fstr_leastSKUCount[i] = store_fields[i].data.fstr_leastSKUCount;
                                    fstr_ItemName[i] = store_fields[i].data.fstr_ItemName;
                                    subcategoryname[i] = store_fields[i].data.subcategoryname;
                                }
                                Ext.Ajax.request({
                                    url: modURL + '&op=createTransferOrder',
                                    method: 'POST',
                                    params: {
                                        fstr_ItemId: Ext.encode(fstr_ItemId),
                                        fstrd_id: Ext.encode(fstrd_id),
                                        fstr_ApprovedItemQty: Ext.encode(fstr_ApprovedItemQty),
                                        fstr_leastSKUCount: Ext.encode(fstr_leastSKUCount),
                                        fstr_ItemName: Ext.encode(fstr_ItemName),
                                        fstr_id: fstr_id,
                                        subcategoryname: Ext.encode(subcategoryname),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            var tmp = Ext.decode(response.responseText);
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('retalineTransferRequest_main_panel').getStore().load();
                                            Ext.getCmp('windowTransferOrder').close();
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
        viewTransferItems: function (fstr_id) {
            var fstr_id = fstr_id;
            var _addnewtransferWindow = new Ext.Window({
                title: 'View Items',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 600,
                resizable: false,
                draggable: true,
                closable: true,
                id: 'windowTransferOrder',
                bodyStyle: {"background-color": "white"},
                items: [ViewTransferGrid(fstr_id)]
            });
            _addnewtransferWindow.doLayout();
            _addnewtransferWindow.show();
            _addnewtransferWindow.center();
        }
    };
}();