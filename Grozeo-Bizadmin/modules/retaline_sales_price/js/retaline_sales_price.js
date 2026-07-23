Application.RetalineSalesPrice = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_sales_price';
    var recs_per_page = 22;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var getMigratedData = function (gridid) {
        var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), 'data');
        return Ext.encode(j);
    };
    var salesPriceStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStockSalesPrice',
                method: 'post'
            }),
            fields: ['br_Name', 'selling_price','stit_SKU', 'stit_itemName', 'product_category', 'stit_category_name', 'stit_brand_name', 'med_manufacturename', 'category_name', 'parent_category', 'item_count', 'mrp',
                'fpod_customerRateHmDel', 'fpod_customerRateCouDel', 'fpod_customerRatePikup', 'type', 'spId', 'retailPrice', 'csPrice', 'stit_quantity','discount_selling_price'],
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
                    dataIndex: 'mrp',
                    tooltip: 'MRP',
                },{
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Selling Price',
                },{
                    header: 'Discount Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'discount_selling_price',
                    tooltip: 'Discount Selling Price',
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

    var addItemStore = function () {
        var sellingPricestore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listSelectedItems',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['branch_id', 'stitId', 'stit_SKU', 'item_count', 'selling_price', 'fpod_customerRatePikup', 'fpod_skuPurchaseRange', 'fpod_skuPurchaseQty', 'fpod_skuAvgPurchaseRate', 'fpod_skuLastPurchaseRate',
                'fpod_leastSKUepr', 'fpod_effectivemargin', 'selling_price', 'lastdate', 'fpod_customerRateHmDel', 'fpod_leastSKUb2bRetailsp', 'fpod_leastSKUb2bCSsp', 'fpod_customerRateCouDel', 'currentPrice']),
            sortInfo: {
                field: 'stit_SKU',
                direction: "ASC"
            },
            remoteSort: true,
            groupField: '',
            groupDir: '',
            listeners: {
                load: function () {
                    //Ext.getCmp('nootitemtodel').focus();
                }
            }
        });
        return sellingPricestore;
    };
    var selectedPrductsGridDelivery = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSellingPriceDelivery',
            columns: [{
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'New Purchase Range',
                    dataIndex: 'fpod_skuPurchaseRange',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Purchase Qty',
                    dataIndex: 'fpod_skuPurchaseQty',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Average Purchase Rate',
                    dataIndex: 'fpod_skuAvgPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Last Purchase Rate',
                    dataIndex: 'fpod_skuLastPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'EPR',
                    dataIndex: 'fpod_leastSKUepr',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Price',
                    dataIndex: 'currentPrice',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Effective Margin',
                    dataIndex: 'fpod_effectivemargin',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Selling Price',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                }],
            tbar: [{html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'search_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_baseStation',
                    name: 'search_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSellingPriceDelivery').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSellingPriceDelivery').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'delivery'
                                },
                                callback: function () {
                                    Ext.getCmp('nootitemtodel').focus();
                                }
                            });


                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

                    }
                }, '->', {html: 'Last selling price updated on:'}, , {
                    id: 'nootitemtodel',
                    name: 'nootitemtodel',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var totalValue = 0, actualValue = 0;
                            var selpriceDate = Ext.getCmp('gridpanelUpdateSellingPriceDelivery').getStore().data.items[0].data.lastdate;
                            Ext.getCmp('nootitemtodel').setValue(selpriceDate); //store_stock

                        }
                    }
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_effectivemargin') < 0)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_effectivemargin') < -10)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
            }),
            listeners: {
                afteredit: updateMargin,
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var updateMargin = function (grid_event) {
        //var grecord = grid_event.store.getAt(rowIndex);
        var rec = grid_event.record;
        var fpod_effectivemargin = 100 - ((rec.data.fpod_skuLastPurchaseRate / rec.data.selling_price) * 100);
        fpod_effectivemargin = parseFloat(fpod_effectivemargin.toFixed(3));
        grid_event.record.set('fpod_effectivemargin', fpod_effectivemargin);

    };
    var branchstoreBaseStation = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getBaseStationBranch',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                    if (_SESSION.br_PyramidLevel > 2) {
                        Ext.getCmp('search_baseStation').setValue(_SESSION.finascop_current_branch_id);
                    }

                }
            }
        });
        return store;
    };
    var selectedPrductsGridRetail = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSellingPriceRetail',
            columns: [{
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'New Purchase Range',
                    dataIndex: 'fpod_skuPurchaseRange',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Purchase Qty',
                    dataIndex: 'fpod_skuPurchaseQty',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Average Purchase Rate',
                    dataIndex: 'fpod_skuAvgPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Last Purchase Rate',
                    dataIndex: 'fpod_skuLastPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'EPR',
                    dataIndex: 'fpod_leastSKUepr',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Price',
                    dataIndex: 'currentPrice',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Effective Margin',
                    dataIndex: 'fpod_effectivemargin',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Selling Price',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                }],
            tbar: [{html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'search_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_baseStation',
                    name: 'search_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSellingPriceRetail').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSellingPriceRetail').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'retail'
                                },
                                callback: function () {
                                    Ext.getCmp('nootitemtoret').focus();
                                }
                            });

                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

                    }
                }, '->', {html: 'Last selling price updated on:'}, , {
                    id: 'nootitemtoret',
                    name: 'nootitemtoret',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var totalValue = 0, actualValue = 0;
                            Ext.getCmp('nootitemtoret').setValue(Ext.getCmp('gridpanelUpdateSellingPriceRetail').getStore().data.items[0].data.lastdate); //store_stock

                        }
                    }
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_effectivemargin') < 0)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_effectivemargin') < -10)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
            }),
            listeners: {
                afteredit: updateMargin,
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var selectedPrductsGridBaseStation = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSellingPriceBaseStation',
            columns: [{
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'New Purchase Range',
                    dataIndex: 'fpod_skuPurchaseRange',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Purchase Qty',
                    dataIndex: 'fpod_skuPurchaseQty',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Average Purchase Rate',
                    dataIndex: 'fpod_skuAvgPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Last Purchase Rate',
                    dataIndex: 'fpod_skuLastPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'EPR',
                    dataIndex: 'fpod_leastSKUepr',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Price',
                    dataIndex: 'currentPrice',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Effective Margin',
                    dataIndex: 'fpod_effectivemargin',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Selling Price',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                }],
            tbar: [{html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'search_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_baseStation',
                    name: 'search_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSellingPriceBaseStation').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSellingPriceBaseStation').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'basestation'
                                },
                                callback: function () {
                                    Ext.getCmp('nootitemtobs').focus();
                                }
                            });

                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

                    }
                }, '->', {html: 'Last selling price updated on:'}, , {
                    id: 'nootitemtobs',
                    name: 'nootitemtobs',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var totalValue = 0, actualValue = 0;
                            Ext.getCmp('nootitemtobs').setValue(Ext.getCmp('gridpanelUpdateSellingPriceBaseStation').getStore().data.items[0].data.lastdate); //store_stock

                        }
                    }
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_effectivemargin') < 0)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_effectivemargin') < -10)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
            }),
            listeners: {
                afteredit: updateMargin,
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var selectedPrductsGridCourier = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSellingPriceCourier',
            columns: [{
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'New Purchase Range',
                    dataIndex: 'fpod_skuPurchaseRange',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Purchase Qty',
                    dataIndex: 'fpod_skuPurchaseQty',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Average Purchase Rate',
                    dataIndex: 'fpod_skuAvgPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Last Purchase Rate',
                    dataIndex: 'fpod_skuLastPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'EPR',
                    dataIndex: 'fpod_leastSKUepr',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Price',
                    dataIndex: 'currentPrice',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Effective Margin',
                    dataIndex: 'fpod_effectivemargin',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Selling Price',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                }],
            tbar: [{html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'search_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_baseStation',
                    name: 'search_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSellingPriceCourier').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSellingPriceCourier').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'courier'
                                },
                                callback: function () {
                                    Ext.getCmp('nootitemtocou').focus();
                                }
                            });

                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

                    }
                }, '->', {html: 'Last selling price updated on:'}, , {
                    id: 'nootitemtocou',
                    name: 'nootitemtocou',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var totalValue = 0, actualValue = 0;
                            Ext.getCmp('nootitemtocou').setValue(Ext.getCmp('gridpanelUpdateSellingPriceCourier').getStore().data.items[0].data.lastdate); //store_stock

                        }
                    }
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_effectivemargin') < 0)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_effectivemargin') < -10)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
            }),
            listeners: {
                afteredit: updateMargin,
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var selectedPrductsGrid = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSellingPrice',
            columns: [{
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'New Purchase Range',
                    dataIndex: 'fpod_skuPurchaseRange',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Purchase Qty',
                    dataIndex: 'fpod_skuPurchaseQty',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Average Purchase Rate',
                    dataIndex: 'fpod_skuAvgPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Last Purchase Rate',
                    dataIndex: 'fpod_skuLastPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'EPR',
                    dataIndex: 'fpod_leastSKUepr',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Price',
                    dataIndex: 'currentPrice',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Effective Margin',
                    dataIndex: 'fpod_effectivemargin',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Selling Price',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                }],
            tbar: [{html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'search_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_baseStation',
                    name: 'search_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSellingPrice').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSellingPrice').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'pickup'
                                },
                                callback: function () {
                                    Ext.getCmp('nootitemtoonl').focus();
                                }
                            });

                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

                    }
                }, '->', {html: 'Last selling price updated on:'}, , {
                    id: 'nootitemtoonl',
                    name: 'nootitemtoonl',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var totalValue = 0, actualValue = 0;
                            Ext.getCmp('nootitemtoonl').setValue(Ext.getCmp('gridpanelUpdateSellingPrice').getStore().data.items[0].data.lastdate); //store_stock

                        }
                    }
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_effectivemargin') < 0)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_effectivemargin') < -10)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
            }),
            listeners: {
                afteredit: updateMargin,
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var bankRemittanceStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStockbankRemittance',
                method: 'post'
            }),
            fields: ['rbrem_id', 'rbrem_date', 'rbrem_bank', 'rbrem_purpose', 'rbrem_amount', 'rbem_createdBranch', 'rbm_name', 'rbm_account', 'rbm_branch', 'rbm_ifsc', 'br_Name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                load: function () {
                }
            }
        });
        return store;
    };



    var masterPanelforBankRemittanceGrid = function (id) {

        var bankComboStore = new Ext.data.JsonStore({
            fields: ['rbm_id', 'rbm_name', 'rbm_account'],
            url: modURL + '&op=getBankName',
            autoLoad: true,
            method: 'post',
        });
        var bankRemittance_store = bankRemittanceStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rbrem_bank'
                }, {
                    type: 'string',
                    dataIndex: 'br_Name'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: bankRemittance_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Bank Remittance',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Fish Mart',
                    sortable: true,
                    hidden: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch'
                },
                {
                    header: 'Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rbrem_date',
                    tooltip: 'Date'
                }, {
                    header: 'Account No',
                    sortable: true,
                    hideable: false,
                    hideable: false,
                            dataIndex: 'rbm_account',
                    tooltip: 'Account No',
                }, {
                    header: 'Branch',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rbm_branch',
                    tooltip: 'Branch',
                },
                {
                    header: 'Bank',
                    sortable: true,
                    hideable: false,
                    id: 'bnkrem_auto_exp',
                    dataIndex: 'rbm_name',
                    tooltip: 'Bank'
                },
                {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rbrem_amount',
                    tooltip: 'Amount',
                }, {
                    header: 'IFSC',
                    hidden: true,
                    hideable: false,
                    dataIndex: 'rbm_ifsc',
                    tooltip: 'IFSC',
                }, {
                    header: 'Remarks',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rbrem_purpose',
                    tooltip: 'Remarks',
                    width: 300
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    width: 25,
                    items: [/*<?php if (user_access("retaline_sales_price", "deleteBankRemittanceEntry")) { ?> */{
                            iconCls: 'finascop_delete',
                            tooltip: 'Remove Item',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteBankRemittance(record.get('rbrem_id'));
                            }
                        }/*<?php } ?> */]
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
            tbar: [{html: '&nbsp;Date : &nbsp;'}, {
                    xtype: 'datefield',
                    fieldLabel: 'Date',
                    id: 'bankRemittance_date',
                    name: 'bankRemittance_date',
                    anchor: '90%',
                    allowBlank: false,
                    tabIndex: 3,
                    value: new Date(),
                    format: "d/m/Y"
                }, {html: '&nbsp;Bank : &nbsp;'}, {
                    xtype: 'combo',
                    store: bankComboStore,
                    mode: 'local',
                    id: 'bankRemittance_bank',
                    allowBlank: true,
                    fieldLabel: 'Bank',
                    displayField: 'rbm_name',
                    valueField: 'rbm_id',
                    typeAhead: true,
                    forceSelection: true,
                    editable: true,
                    minChars: 2,
                    anchor: '98%',
                    triggerAction: 'all',
                    lazyRender: true,
                    tabIndex: 34,
                    listeners: {
                    }
                }, {html: '&nbsp;Amount : &nbsp;'}, {
                    xtype: 'numberfield',
                    fieldLabel: 'Amount',
                    id: 'bankRemittance_amount',
                    name: 'bankRemittance_amount',
                    anchor: '98%',
                    allowBlank: false,
                    width: 100,
                    tabIndex: 503,
                    maxLength: 300
                }, {html: '&nbsp;Remarks : &nbsp;'}, {
                    xtype: 'textfield',
                    fieldLabel: 'Remarks',
                    id: 'bankRemittance_purpose',
                    name: 'bankRemittance_purpose',
                    anchor: '98%',
                    width: 300,
                    tabIndex: 503,
                    maxLength: 300
                }, {
                    xtype: 'button',
                    text: 'Add',
                    iconCls: 'add',
                    tabIndex: 504,
                    handler: function () {

                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
                        if (!Ext.isEmpty(Ext.getCmp('bankRemittance_amount').getValue()) && !Ext.isEmpty(Ext.getCmp('bankRemittance_date').getValue()) && !Ext.isEmpty(Ext.getCmp('bankRemittance_bank').getValue())) {
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL + '&op=saveBankRemittance',
                                method: 'POST',
                                params: {
                                    bankRemittance_amount: Ext.getCmp('bankRemittance_amount').getValue(),
                                    bankRemittance_date: Ext.getCmp('bankRemittance_date').getValue(),
                                    bankRemittance_bank: Ext.getCmp('bankRemittance_bank').getValue(),
                                    bankRemittance_purpose: Ext.getCmp('bankRemittance_purpose').getValue()

                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true)
                                    {
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp(id).getStore().load();
                                        Ext.getCmp('bankRemittance_amount').reset();
                                        Ext.getCmp('bankRemittance_bank').reset();
                                        Ext.getCmp('bankRemittance_purpose').reset();
                                    } else
                                    {
                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                    }
                                    Ext.getBody().unmask();

                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }

                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: bankRemittance_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'bnkrem_auto_exp'
        });
        return grid_panel;
    };
    var ExpenseStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listDailyStockExpense',
                method: 'post'
            }),
            fields: ['rde_id', 'rde_date', 'rde_purpose', 'rde_amount', 'br_Name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                load: function () {
                }
            }
        });
        return store;
    };



    var masterPanelforExpenseGrid = function (id) {

        var Expense_store = ExpenseStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: Expense_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Expense',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Fish Mart',
                    sortable: true,
                    hideable: true,
                    hidden: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                },
                {
                    header: 'Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rde_date',
                    tooltip: 'Date',
                },
                {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rde_amount',
                    tooltip: 'Amount',
                }, {
                    header: 'Purpose',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rde_purpose',
                    tooltip: 'Purpose',
                    width: 300
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    width: 25,
                    items: [/*<?php if (user_access("retaline_sales_price", "deleteExpenseEntry")) { ?> */{
                            iconCls: 'finascop_delete',
                            tooltip: 'Remove Item',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteExpenxeEntry(record.get('rde_id'));
                            }
                        }/*<?php } ?> */]
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
            tbar: [{html: '&nbsp;Date : &nbsp;'}, {
                    xtype: 'datefield',
                    fieldLabel: 'Date',
                    id: 'rde_date',
                    name: 'rde_date',
                    anchor: '90%',
                    allowBlank: false,
                    tabIndex: 300,
                    value: new Date(),
                    format: "d-m-Y"
                }, {html: '&nbsp;Purpose : &nbsp;'}, {
                    xtype: 'textfield',
                    fieldLabel: 'Purpose',
                    id: 'rde_purpose',
                    name: 'rde_purpose',
                    anchor: '98%',
                    width: 350,
                    tabIndex: 301,
                    maxLength: 500
                }, {html: '&nbsp;Amount : &nbsp;'}, {
                    xtype: 'numberfield',
                    fieldLabel: 'Amount',
                    id: 'rde_amount',
                    name: 'rde_amount',
                    anchor: '98%',
                    allowBlank: false,
                    width: 100,
                    tabIndex: 302,
                    maxLength: 300
                }, {
                    xtype: 'button',
                    text: 'Add',
                    iconCls: 'add',
                    tabIndex: 303,
                    handler: function () {


                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
                        if (!Ext.isEmpty(Ext.getCmp('rde_amount').getValue()) && !Ext.isEmpty(Ext.getCmp('rde_date').getValue())) {
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL + '&op=saveDailyExpense',
                                method: 'POST',
                                params: {
                                    rde_date: Ext.getCmp('rde_date').getValue(),
                                    rde_purpose: Ext.getCmp('rde_purpose').getValue(),
                                    rde_amount: Ext.getCmp('rde_amount').getValue()
                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true)
                                    {
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp(id).getStore().load();
                                        Ext.getCmp('rde_purpose').reset();
                                        Ext.getCmp('rde_amount').reset();
                                    } else
                                    {
                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                    }
                                    Ext.getBody().unmask();
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: Expense_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'bnkrem_auto_exp'
        });
        return grid_panel;
    };
    var selectedPrductsGridRaw = function () {
        var addItemStorenew = rawItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSellingPriceRaw',
            columns: [{
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Purchase Range',
                    dataIndex: 'fpod_skuPurchaseRange',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Fishmart Price',
                    dataIndex: 'fishmart_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Online Selling Price',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Offline Selling Price [Fishmart]',
                    dataIndex: 'offline_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Institutional Price',
                    dataIndex: 'institutional_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Fishmart Price',
                    dataIndex: 'newfishmart_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3,
                        allowBlank: false
                    }
                }, {
                    header: 'Online Selling Price',
                    dataIndex: 'newselling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3,
                        allowBlank: false
                    }
                }, {
                    header: 'Offline Selling Price [Fishmart]',
                    dataIndex: 'newoffline_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3,
                        allowBlank: false
                    }
                }, {
                    header: 'Institutional Price',
                    dataIndex: 'newinstitutional_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3,
                        allowBlank: false
                    }
                }],
            tbar: [{html: '&nbsp;Date : &nbsp;'}, {
                    xtype: 'datefield',
                    fieldLabel: 'Date',
                    id: 'update_date',
                    name: 'update_date',
                    anchor: '90%',
                    allowBlank: false,
                    tabIndex: 3,
                    value: new Date(),
                    format: "d-m-Y"
                }, {html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'search_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_baseStation',
                    name: 'search_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSellingPriceRaw').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSellingPriceRaw').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    created_date: Ext.getCmp('update_date').getValue(),
                                    type: 'raw'
                                },
                                callback: function () {
                                    Ext.getCmp('nootitemtocou').focus();
                                }
                            });

                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

                    }
                }, '->', {html: 'Last selling price updated on:'}, , {
                    id: 'nootitemtocou',
                    name: 'nootitemtocou',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var totalValue = 0, actualValue = 0;
                            Ext.getCmp('nootitemtocou').setValue(Ext.getCmp('gridpanelUpdateSellingPriceRaw').getStore().data.items[0].data.lastdate); //store_stock

                        }
                    }
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
            }),
            listeners: {
                afteredit: '',
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var rawItemStore = function () {
        var sellingPricestore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRawItems',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['lastdate', 'branch_id', 'stitId', 'stit_SKU', 'fishmart_price', 'selling_price', 'fpod_skuPurchaseRange', 'offline_price', 'institutional_price']),
            sortInfo: {
                field: 'stit_SKU',
                direction: "ASC"
            },
            remoteSort: true,
            groupField: '',
            groupDir: '',
            listeners: {
                load: function () {
                    //Ext.getCmp('nootitemtodel').focus();
                }
            }
        });
        return sellingPricestore;
    };
    var deleteExpenxeEntry = function (rde_id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this entry?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteExpenseEntry',
                    params: {
                        rde_id: rde_id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed entry.');
                            Ext.getCmp('panelExpense').getStore().load();
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
    var deleteBankRemittance = function (rbrem_id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this entry?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteBankRemittanceEntry',
                    params: {
                        rbrem_id: rbrem_id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed entry.');
                            Ext.getCmp('panelBankRemittance').getStore().load();
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
    var rawSalesPriceStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRawStockSalesPrice',
                method: 'post'
            }),
            fields: ['lastdate', 'branch_id', 'stitId', 'stit_SKU', 'fishmart_price', 'selling_price', 'fpod_skuPurchaseRange', 'offline_price', 'institutional_price'],
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
    var masterPanelforRawSalesPriceGrid = function (id) {
        var salesPrice_store = rawSalesPriceStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: salesPrice_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Raw Product Pricing',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Fishmart Price',
                    dataIndex: 'fishmart_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Selling Price [Online]',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Selling Price [Fishmart]',
                    dataIndex: 'offline_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Institutional Price',
                    dataIndex: 'institutional_price',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                },
                {dataIndex: ' ', }
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
            tbar: [{html: '&nbsp;Date : &nbsp;'}, {
                    xtype: 'datefield',
                    fieldLabel: 'Date',
                    id: 'lrupdate_date',
                    name: 'lrupdate_date',
                    anchor: '90%',
                    allowBlank: false,
                    tabIndex: 3,
                    value: new Date(),
                    format: "d-m-Y"
                }, {html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'lrsearch_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'lrsearch_baseStation',
                    name: 'lrsearch_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelListSellingPriceRaw').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('lrsearch_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelListSellingPriceRaw').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('lrsearch_baseStation').getValue(),
                                    created_date: Ext.getCmp('lrupdate_date').getValue(),
                                    type: 'raw'
                                },
                                callback: function () {
                                    Ext.getCmp('nootitemtocou').focus();
                                }
                            });

                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

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
        }, updateSellingPriceDelivery: function () {

            var resultWindow = new Ext.Window({
                id: "windowDeliveySellingPriceUpdation",
                title: 'Online Selling Price',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [selectedPrductsGridDelivery()],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowDeliveySellingPriceUpdation').close();

        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        text: 'Save',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSellingPriceDelivery');
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateSellingPrice',
                                    uuid: Application.RetalineSalesPrice.Cache.deliUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'delivery'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        console.log('updateItemStock');
                                        Ext.Ajax.request({
                                            url: modURL,
                                            params: {
                                                op: 'updateItemStock',
                                                uuid: Application.RetalineSalesPrice.Cache.deliUUID,
                                                spUpdatedItems: spUpdatedItems,
                                                parentItems: tmp.parentItems,
                                                search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                                type: 'delivery'
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                                    Ext.getCmp('windowDeliveySellingPriceUpdation').close();
                                                    Application.RetalineSalesPrice.Cache.deliUUID = '';
                                                    Ext.getBody().unmask();
    }
                                            }
                                        });


                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineSalesPrice.Cache.deliUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        },
        updateSellingPriceRetail: function () {

            var resultWindow = new Ext.Window({
                id: "windowRetailSellingPriceUpdation",
                title: 'Institutional Selling Price',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [selectedPrductsGridRetail()],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowRetailSellingPriceUpdation').close();

                        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        text: 'Save',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSellingPriceRetail');
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateSellingPrice',
                                    uuid: Application.RetalineSalesPrice.Cache.retailUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'retail'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {

                                        console.log('updateItemStock');
                                        Ext.Ajax.request({
                                            url: modURL,
                                            params: {
                                                op: 'updateItemStock',
                                                uuid: Application.RetalineSalesPrice.Cache.retailUUID,
                                                spUpdatedItems: spUpdatedItems,
                                                parentItems: tmp.parentItems,
                                                search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                                type: 'delivery'
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                                    Ext.getCmp('windowRetailSellingPriceUpdation').close();
                                                    Application.RetalineSalesPrice.Cache.retailUUID = '';
                                                    Ext.getBody().unmask();
                                                }
                                            }
                                        });

                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineSalesPrice.Cache.retailUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        },
        updateSellingPriceBaseStation: function () {

            var resultWindow = new Ext.Window({
                id: "windowBaseStationSellingPriceUpdation",
                title: 'Wholesale Selling Price',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [selectedPrductsGridBaseStation()],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowBaseStationSellingPriceUpdation').close();

                        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        text: 'Save',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSellingPriceBaseStation');
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateSellingPrice',
                                    uuid: Application.RetalineSalesPrice.Cache.basestationUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'basestation'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {

                                        console.log('updateItemStock');
                                        Ext.Ajax.request({
                                            url: modURL,
                                            params: {
                                                op: 'updateItemStock',
                                                uuid: Application.RetalineSalesPrice.Cache.basestationUUID,
                                                spUpdatedItems: spUpdatedItems,
                                                parentItems: tmp.parentItems,
                                                search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                                type: 'delivery'
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                                    Ext.getCmp('windowBaseStationSellingPriceUpdation').close();
                                                    Application.RetalineSalesPrice.Cache.basestationUUID = '';
                                                    Ext.getBody().unmask();
                                                }
                                            }
                                        });

                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineSalesPrice.Cache.basestationUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        },
        updateSellingPriceCourier: function () {

            var resultWindow = new Ext.Window({
                id: "windowCourierilSellingPriceUpdation",
                title: 'Courier Selling Price',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [selectedPrductsGridCourier()],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowCourierilSellingPriceUpdation').close();

                        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        text: 'Save',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSellingPriceCourier');
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateSellingPrice',
                                    uuid: Application.RetalineSalesPrice.Cache.courierUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'courier'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        Ext.Ajax.request({
                                            url: modURL,
                                            params: {
                                                op: 'updateItemStock',
                                                uuid: Application.RetalineSalesPrice.Cache.courierUUID,
                                                spUpdatedItems: spUpdatedItems,
                                                parentItems: tmp.parentItems,
                                                search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                                type: 'delivery'
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                                    Ext.getCmp('windowCourierilSellingPriceUpdation').close();
                                                    Application.RetalineSalesPrice.Cache.courierUUID = '';
                                                    Ext.getBody().unmask();
                                                }
                                            }
                                        });

                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineSalesPrice.Cache.courierUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        }, updateSellingPrice: function () {

            var resultWindow = new Ext.Window({
                id: "windowFinascopStockAddvenderitemCreatevendoritem",
                title: 'Fish Mart Selling Price',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [selectedPrductsGrid()],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();

                        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        text: 'Save',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSellingPrice');
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateSellingPrice',
                                    uuid: Application.RetalineGoodPurchaseNote.Cache.updateUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    type: 'pickup'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        Ext.Ajax.request({
                                            url: modURL,
                                            params: {
                                                op: 'updateItemStock',
                                                uuid: Application.RetalineSalesPrice.Cache.updateUUID,
                                                spUpdatedItems: spUpdatedItems,
                                                parentItems: tmp.parentItems,
                                                search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                                type: 'delivery'
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                                    Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
                                                    Application.RetalineSalesPrice.Cache.updateUUID = '';
                                                    Ext.getBody().unmask();
                                                }
                                            }
                                        });

                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineGoodPurchaseNote.Cache.updateUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        }, initBankRemittance: function () {
            var _bankRemitttancePanelId = 'panelBankRemittance';
            var _masterPanelBankRemittance = Ext.getCmp(_bankRemitttancePanelId);
            if (Ext.isEmpty(_masterPanelBankRemittance)) {
                _masterPanelBankRemittance = masterPanelforBankRemittanceGrid(_bankRemitttancePanelId);
                Application.UI.addTab(_masterPanelBankRemittance);
                _masterPanelBankRemittance.doLayout();
            } else {
                Application.UI.addTab(_masterPanelBankRemittance);
            }
        }, initExpense: function () {
            var _expensePanelId = 'panelExpense';
            var _masterPanelExpense = Ext.getCmp(_expensePanelId);
            if (Ext.isEmpty(_masterPanelExpense)) {
                _masterPanelExpense = masterPanelforExpenseGrid(_expensePanelId);
                Application.UI.addTab(_masterPanelExpense);
                _masterPanelExpense.doLayout();
            } else {
                Application.UI.addTab(_masterPanelExpense);
            }
        }, updateSellingPriceRaw: function () {

            var resultWindow = new Ext.Window({
                id: "windowRawSellingPriceUpdation",
                title: 'Billing Price',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [selectedPrductsGridRaw()],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowRawSellingPriceUpdation').close();

                        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        text: 'Save',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSellingPriceRaw');
                            Ext.getBody().mask('Loading...');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateRawSellingPrice',
                                    uuid: Application.RetalineSalesPrice.Cache.rawUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_baseStation').getValue(),
                                    created_date: Ext.getCmp('update_date').getValue(),
                                    type: 'raw'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        Ext.getCmp('windowRawSellingPriceUpdation').close();
                                        Application.RetalineSalesPrice.Cache.rawUUID = '';
                                        Ext.getBody().unmask();

                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineSalesPrice.Cache.courierUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        }, initRawPrice: function () {
            var _rawPricePanelId = 'gridpanelListSellingPriceRaw';
            var _masterPanelRawPrice = Ext.getCmp(_rawPricePanelId);
            if (Ext.isEmpty(_masterPanelRawPrice)) {
                _masterPanelRawPrice = masterPanelforRawSalesPriceGrid(_rawPricePanelId);
                Application.UI.addTab(_masterPanelRawPrice);
                _masterPanelRawPrice.doLayout();
            } else {
                Application.UI.addTab(_masterPanelRawPrice);
            }
        }
    }
}();