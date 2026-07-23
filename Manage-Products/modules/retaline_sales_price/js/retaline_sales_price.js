Application.RetalineSalesPrice = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_sales_price';
    var recs_per_page = 22;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var salesPriceStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStockSalesPrice',
                method: 'post'
            }),
            fields: ['br_Name', 'stit_SKU', 'stit_itemName', 'product_category', 'stit_category_name', 'stit_brand_name', 'med_manufacturename', 'category_name', 'parent_category', 'item_count', 'fpod_leastSKUmrp',
                'fpod_customerRateHmDel', 'fpod_customerRateCouDel', 'fpod_customerRatePikup', 'type', 'spId','retailPrice','csPrice','stit_quantity'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
//            sortInfo: {
//                field: 'stit_id',
//                direction: "ASC"
//            },
            listeners: {
                load: function () {
                }
            }
        });
        return store;
    };



    var masterPanelforSalesPriceGrid = function (id) {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: false,
            method: 'post',
        });
        var salesPrice_store = salesPriceStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'stit_itemName'
                }, {
                    type: 'string',
                    dataIndex: 'product_category'
                }, {
                    type: 'string',
                    dataIndex: 'stit_category_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'med_manufacturename'
                }, {
                    type: 'string',
                    dataIndex: 'category_name'
                }, {
                    type: 'string',
                    dataIndex: 'parent_category'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: salesPrice_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Product Pricing',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Source',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Source'
                },
                {
                    header: 'SKU',
                    id: 'sp_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'SKU',
                    width: 200
                }, {
                    header: 'Brand',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_brand_name',
                    tooltip: 'Brand',
                    width: 200
                }, {
                    header: 'Manufacturer',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'med_manufacturename',
                    tooltip: 'Manufacturer',
                    width: 200
                }, {
                    header: 'Parent Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'parent_category',
                    tooltip: 'Parent Category'
                }, {
                    header: 'Category',
                    dataIndex: 'category_name',
                    sortable: true,
                    hideable: false,
                    tooltip: 'Category',
                    hidden: true
                }, {
                    header: 'Sub Category',
                    dataIndex: 'stit_category_name',
                    sortable: true,
                    hideable: false,
                    tooltip: 'Sub Category',
                    hidden: true
                }, {
                    header: 'Item Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_itemName',
                    tooltip: 'Item Name'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'stit_quantity',
                    tooltip: 'Quantity'
                }, {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpod_leastSKUmrp',
                    tooltip: 'MRP',
                },{
                    header: 'Courier',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'fpod_customerRateCouDel',
                    tooltip: 'Courier'
                }, {
                    header: 'Manual',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'fpod_customerRateHmDel',
                    tooltip: 'Manual',
                }, {
                    header: 'Pick up',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'fpod_customerRatePikup',
                    tooltip: 'Pick up'
                },{
                    header: 'Distributor',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'csPrice',
                    tooltip: 'Distributor'
                },{
                    header: 'Retailor',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'retailPrice',
                    tooltip: 'Retailor'
                },
                 {dataIndex: ' ',},
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    hidden: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            currentstockActionMenu.showAt(e.getXY());
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
                resize: updatePagination,
                afterrender: function () {
                }
            },
            tbar: [{
                    html: '&nbsp;Type : &nbsp;',
                }, {
                    xtype: 'combo',
                    fieldLabel: 'Type',
                    emptyText: 'Choose Type',
                    id: 'sp_type',
                    name: 'sp_type',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    editable: true,
                    anchor: '97%',
                    store: new Ext.data.JsonStore({
                        fields: ['id', 'name'],
                        data: [{id: 'Branch', name: 'Branch'}, {id: 'Vendor', name: 'Vendor'}]
                    }),
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'name',
                    valueField: 'id',
                    hiddenName: 'sp_type',
                    tabIndex: 1,
                    listeners: {
                        select: function () {
                            var type = Ext.getCmp('sp_type').getValue();
                            Ext.getCmp('comboxbranchnamesp').getStore().load({
                            params: {
                                type:type
                            }
                        });
                        }
                    }
                }, {
                    html: '&nbsp;Source : &nbsp;',
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchnamesp',
                    name: 'comboxbranchnamesp',
                    mode: 'local',
                    emptyText: 'Choose',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnamesp',
                    listeners: {
                        select: function () {

                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Go',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        var type = Ext.getCmp('sp_type').getValue();
                        var branchName = Ext.getCmp('comboxbranchnamesp').getRawValue();
                        Ext.getCmp('panelMasterSalesPrice').getStore().load({
                            params: {
                                branchName: branchName,
                                type:type
                            }
                        });
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: salesPrice_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'sp_auto_exp'
        });
        return grid_panel;
    };
    var currentstockActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Details",
                handler: function () {
                    var stit_id = Ext.getCmp('panelMasterSalesPrice').getSelectionModel().getSelections()[0].data.stit_id;
                    var branchId = Ext.getCmp('panelMasterSalesPrice').getSelectionModel().getSelections()[0].data.branchId;
                    var fsbg_id = Ext.getCmp('panelMasterSalesPrice').getSelectionModel().getSelections()[0].data.fsbg_id;
                }
            }]
    });



    return {
        Cache: {},
        initSalesPrice: function () {
            var _salePricePanelId = 'panelMasterSalesPrice';
            var _masterPanelSalesPrice = Ext.getCmp(_salePricePanelId);
            if (Ext.isEmpty(_masterPanelSalesPrice)) {
                _masterPanelSalesPrice = masterPanelforSalesPriceGrid(_salePricePanelId);
                Application.UI.addTab(_masterPanelSalesPrice);
                _masterPanelSalesPrice.doLayout();
            } else {
                Application.UI.addTab(_masterPanelSalesPrice);
            }
        }
    }
}();