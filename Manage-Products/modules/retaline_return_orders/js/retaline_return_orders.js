Application.RetalineReturnOrders = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var transferRulesLoadedForm = null;
    var modURL = '?module=retaline_return_orders';
    var brmodURL = '?module=retaline_currentstock';
    var onGridResize = function (cmp) {
        recs_per_page = updateRecsPerPage(cmp);
    };
    var qtipRenderer = function (value, metadata, record, rowIndex, colIndex, store) {


        metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
        return value;

    };
    var updateRecsPerPage = function (cmp) {

        var grid_height = cmp.getInnerHeight();
        var recsAllowed = Math.round(grid_height / GRID_ROW_HEIGHT);
        var cmpPaginBar = cmp.getBottomToolbar();
        cmpPaginBar.pageSize = recsAllowed;
        cmpPaginBar.doLoad(0);
        return recsAllowed;
    };
    var ReturnStockStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPurchaseReturned',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'stit_id',
                root: 'data'
            }, ['stit_category_name', 'br_Name', 'stit_SKU', 'item_count', 'item_returned', 'mrp', 'selling_price', 'stit_id', 'frbi_epr', 'frbi_id',
                'branchId', 'fsbg_name', 'fsbg_id', 'stit_quantity', 'stit_brand_name', 'stit_product_variant', 'frbi_statusName', 'frbi_status'
            ]),
            sortInfo: {
                field: 'stit_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            //    remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function () {
                    this.baseParams.branchName = Ext.getCmp('comboxbranchnamePurRetedStock').getValue();
                }
            }
        });

        return store;
    };
    var ReturnStockLostStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStockLost',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'stit_id',
                root: 'data'
            }, ['stit_category_name', 'br_Name', 'stit_SKU', 'item_count', 'item_returned', 'mrp', 'selling_price', 'stit_id', 'frbi_epr', 'frbi_id',
                'branchId', 'fsbg_name', 'fsbg_id', 'stit_quantity', 'stit_brand_name', 'stit_product_variant', 'frbi_statusName', 'frbi_status', 'item_lossCount'
            ]),
            sortInfo: {
                field: 'stit_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            //    remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function () {
                    this.baseParams.branchName = Ext.getCmp('comboxbranchnameStockLost').getValue();
                }
            }
        });

        return store;
    };
    var masterPanelforPurchaseReturnedtStockGrid = function (id) {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });
        var ReturnStock_store = ReturnStockStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'item_count'
                }, {
                    type: 'string',
                    dataIndex: 'mrp'
                }, {
                    type: 'string',
                    dataIndex: 'selling_price'
                }, {
                    type: 'string',
                    dataIndex: 'stit_category_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_product_variant'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: ReturnStock_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Purchase Returned',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item SKU',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item SKU',
                }, {
                    header: 'Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_category_name',
                    tooltip: 'Category',
                }, {
                    header: 'Brand',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_brand_name',
                    tooltip: 'Brand',
                }, {
                    header: 'Variant',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_product_variant',
                    tooltip: 'Variant',
                }, {
                    header: 'Quantity',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_quantity',
                    tooltip: 'Quantity',
                },
                {
                    header: 'Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_returned',
                    tooltip: 'Returned Count'
                },
                {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: 'MRP'
                },
                {
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Selling Price',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT
                }, {
                    header: 'EPR',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'frbi_epr',
                    tooltip: 'EPR',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'frbi_statusName',
                    tooltip: 'Status'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            iconCls: 'cancel_order',
                            tooltip: 'View Details',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                var itemId = record.get('stit_id');
                                Application.RetalineReturnOrders.purchaseReturnedDetails(itemId);


                            }
                        }
                    ]
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            viewConfig: {
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchnamePurRetedStock").show();
                        Ext.getCmp("textboxPurchaseReurned").hide();
                    }
                    else {
                        Ext.getCmp("textboxPurchaseReurned").show();
                        Ext.getCmp("textboxPurchaseReurned").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchnamePurRetedStock").hide();
                    }
                }
            },
            tbar: [{
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxPurchaseReurned',
                    name: 'textboxPurchaseReurned',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchnamePurRetedStock',
                    name: 'comboxbranchnamePurRetedStock',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Branch Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnamePurRetedStock',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchnamePurRetedStock').getValue();
                            Ext.getCmp('panelMasterReturnedtStock').getStore().load({
                                params: {
                                    branchName: branchName
                                }
                            });
                        }
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: ReturnStock_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });
        return grid_panel;
    };
    var masterPanelforStockLostGrid = function (id) {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });
        var ReturnStock_store = ReturnStockLostStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'item_count'
                }, {
                    type: 'string',
                    dataIndex: 'mrp'
                }, {
                    type: 'string',
                    dataIndex: 'selling_price'
                }, {
                    type: 'string',
                    dataIndex: 'stit_category_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_product_variant'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: ReturnStock_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Stock Lost',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item SKU',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item SKU',
                }, {
                    header: 'Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_category_name',
                    tooltip: 'Category',
                }, {
                    header: 'Brand',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_brand_name',
                    tooltip: 'Brand',
                }, {
                    header: 'Variant',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_product_variant',
                    tooltip: 'Variant',
                }, {
                    header: 'Quantity',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_quantity',
                    tooltip: 'Quantity',
                },
                {
                    header: 'Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_lossCount',
                    tooltip: 'Lost Count'
                },
                {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: 'MRP'
                },
                {
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Selling Price',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT
                }, {
                    header: 'EPR',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'frbi_epr',
                    tooltip: 'EPR',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'frbi_statusName',
                    tooltip: 'Status'
                }, {
                    hidden: true
                }, {
                    hidden: true
                }, {
                    hidden: true
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            viewConfig: {
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchnameStockLost").show();
                        Ext.getCmp("textboxStockLost").hide();
                    }
                    else {
                        Ext.getCmp("textboxStockLost").show();
                        Ext.getCmp("textboxStockLost").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchnameStockLost").hide();
                    }
                }
            },
            tbar: [{
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxStockLost',
                    name: 'textboxStockLost',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchnameStockLost',
                    name: 'comboxbranchnameStockLost',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Branch Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnameStockLost',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchnameStockLost').getValue();
                            Ext.getCmp('panelStockLost').getStore().load({
                                params: {
                                    branchName: branchName
                                }
                            });
                        }
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: ReturnStock_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });
        return grid_panel;
    };
    var createpurchaseReturnedDetailskGrid = function (itemId) {
        var purchaseReturnedDetailGridStore = purchaseReturnedDetailStore(itemId);
        var purchaseReturnedDetailGrid = new Ext.grid.GridPanel({
            store: purchaseReturnedDetailGridStore,
            frame: false,
            border: true,
            height: 300,
            autoScroll: true,
            plugins: [],
            id: 'purchaseReturnedDetailGridPanel',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'stpa_Fname',
                    tooltip: 'Vendor'
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'pure_EntryOn',
                    width: 150,
                    tooltip: 'Date'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'purd_itemReturnedQty',
                    align: 'right',
                    tooltip: 'Quantity'
                }, {
                    header: 'Rate',
                    sortable: true,
                    dataIndex: 'purd_Rate',
                    align: 'right',
                    tooltip: 'Rate'
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
        return purchaseReturnedDetailGrid;
    };

    var purchaseReturnedDetailStore = function (itemId) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getpurchaseReturnedDetailStore',
            fields: ['pure_Id','purd_itemID','purd_itemReturnedQty','purd_Rate','pure_EntryOn','pure_vendorId','stpa_Fname'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        itemId: itemId
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    return{
        initPurchaseReturnedtStock: function () {
            var _purchaseReturnedPanelId = 'panelPurchaseReturned';
            var _masterPanelPurchaseReturnedtStock = Ext.getCmp(_purchaseReturnedPanelId);
            if (Ext.isEmpty(_masterPanelPurchaseReturnedtStock)) {
                _masterPanelPurchaseReturnedtStock = masterPanelforPurchaseReturnedtStockGrid(_purchaseReturnedPanelId);
                Application.UI.addTab(_masterPanelPurchaseReturnedtStock);
                _masterPanelPurchaseReturnedtStock.doLayout();
            } else {
                Application.UI.addTab(_masterPanelPurchaseReturnedtStock);
            }
        },
        initStockLost: function () {
            var _stockLostPanelId = 'panelStockLost';
            var _masterPanelStockLost = Ext.getCmp(_stockLostPanelId);
            if (Ext.isEmpty(_masterPanelStockLost)) {
                _masterPanelStockLost = masterPanelforStockLostGrid(_stockLostPanelId);
                Application.UI.addTab(_masterPanelStockLost);
                _masterPanelStockLost.doLayout();
            } else {
                Application.UI.addTab(_masterPanelStockLost);
            }
        }, purchaseReturnedDetails: function (itemId) {
            var resultWindow = new Ext.Window({
                id: "windowpurchaseReturnedDetails",
                iconCls: 'vender-items',
                shadow: false,
                height: 600,
                width: 700,
                title: 'Details',
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [createpurchaseReturnedDetailskGrid(itemId)],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowpurchaseReturnedDetails').close();
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
        }
    };
}();