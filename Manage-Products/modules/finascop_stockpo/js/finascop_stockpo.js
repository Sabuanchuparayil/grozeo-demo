Application.Finascop_Stockpo = function () {

    var RECS_PER_PAGE = 12;
    var modURL = '?module=finascop_stockpo';
    var winsize = Ext.getBody().getViewSize();

    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('stockPoGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('stockPoGridPanel').getSelectionModel().getSelections()[0].data.fpo_id;
            Application.Finascop_Stockpo.ViewMode(ID);
        }
    };

    var newgridSelectionChanged = function (sm) {
        Ext.getCmp('poStockItemName').reset();
        Ext.getCmp('poStockItemQty').reset();
        Application.Finascop_Stockpo.Cache.fpod_id = 0;
        Application.Finascop_Stockpo.Cache.fpod_itemid = 0;
        if (!Ext.isEmpty(Ext.getCmp('newstockPoItemGrid').getSelectionModel().getSelections())) {
            var fpod_id = Ext.getCmp('newstockPoItemGrid').getSelectionModel().getSelections()[0].data.fpod_id;
            var PoId = Ext.getCmp('newstockPoItemGrid').getSelectionModel().getSelections()[0].data.fpod_fpoId;
            var fpod_itemid = Ext.getCmp('newstockPoItemGrid').getSelectionModel().getSelections()[0].data.fpod_itemid;
            var fpod_leastSKUBalanceqty = Ext.getCmp('newstockPoItemGrid').getSelectionModel().getSelections()[0].data.fpod_leastSKUBalanceqty;
            Application.Finascop_Stockpo.Cache.fpod_id = fpod_id;
            Application.Finascop_Stockpo.Cache.fpod_itemid = fpod_itemid;
            Ext.getCmp('poStockItemQty').setValue(fpod_leastSKUBalanceqty);
            Application.Finascop_Stockpo.itemViewMode(fpod_id);
            Ext.getCmp('poStockItemUnits').getStore().load({
                params: {
                    itemId: fpod_itemid
                }
            });
            Ext.getCmp('poStockItemName').getStore().load({
                params: {
                    fpod_id: fpod_id
                }
            });
            Ext.getCmp('poStockEntryGridPanel').getStore().load({
                params: {
                    fpod_id: fpod_id,
                    fpo_id: PoId,
                    itemId: fpod_itemid
                }
            });
        }
    };
    var stockPoStore = function () {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStockPo',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpo_id',
                root: 'data'
            }, ['fpo_id', 'vendor_name', 'fpo_poNumber', 'fpo_poDate', 'fpo_paymentTerms', 'fpo_paymentValue', 'fpo_poDeliveryDate', 'fpo_poDeliveryType', 'fpo_stockVerificationStatus', 'fpo_Active']),
            sortInfo: {
                field: 'fpo_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('stockPoGridPanel').getSelectionModel().selectRow(0);

                }
            }
        });
        return _store;
    };

    var verifyPoGrid = function () {
        var _poGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fpo_poNumber'
                }, {
                    type: 'string',
                    dataIndex: 'vendor_name'
                }, {
                    type: 'string',
                    dataIndex: 'fpo_poDate'
                }, {
                    type: 'list',
                    value: ['Active'],
                    dataIndex: 'fpo_Active',
                    options: ['Active', 'Inactive'],
                    phpMode: true
                }]
        });
        _poGridFilter.remote = true;
        _poGridFilter.autoReload = true;
        var _gridStore = stockPoStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'stockPoGridPanel',
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            //iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpo_stockVerificationStatus') == 1) { /* Partial entryr   */
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpo_stockVerificationStatus') == 2) { /* No partial entries    */
                        return 'finascop_indicateColPINK';
                    } else if (record.get('fpo_stockVerificationStatus') == 3) {   /* completed */
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                }
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _poGridFilter],
            store: _gridStore,
            columns: [
                {
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number',
                    hideable: false
                },
                {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'vendor_name',
                    tooltip: 'Vendor'
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'Date',
                    hideable: false
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'fpo_Active',
                    tooltip: 'Status',
                    hideable: false
                }

            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChanged,
                    rowselect: function (sm, rowIndex, record) {
                        console.log('main', record);
                        Application.Finascop_Stockpo.Cache.PoId = record.get('fpo_id');

                    }
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('fpo_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.Finascop_Stockpo.Cache.fpo_id = ID;
                        Application.Finascop_Stockpo.ViewMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _gridStore.load();
                }
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display",
                items: [{
                        html: '<div class="finascop_color_wr">\
                    <div class="finascopstockpo_white_small"></div><div class="text_c"> Yet to Receive</div>\
                    <div class="finascopstockpo_orange_small"></div><div class="text_c"> Partially Received </div>\
                    <div class="finascopstockpo_pink_small"></div> <div class="text_c">Inward incomplete</div>\
                    <div class="finascopstockpo_green_small"></div><div class="text_c">Stock Received</div>\
                </div> '
                    }]
            }),
            stripeRows: true,
        });
        return _gridPanel;
    };
    var poDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: 'PO Details',
            height: 130,
            autoScroll: true,
            id: 'stockPoDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">PO Number </th><td> {fpo_poNumber} </td></tr>',
                    '<tr><th width="40%">PO Date </th><td> {fpo_poDate} </td></tr>',
                    '<tr><th width="40%">Vendor </th><td> {vendor_name} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
    var stockPoItemGridStorePo = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=stockpoItemStore',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpod_itemid',
                root: 'data'
            }, ['fpod_itemid', 'fpod_itemname', 'fpod_itemqty', 'item_received', 'item_balance', 'fpod_id', 'fpod_fpoId', 'fpod_totalqty', 'fpod_receivedqty', 'fpod_balanceqty', 'fpod_giftqty', 'fpod_giftrecqty',
                'fpod_giftbalqty', 'fpod_stockVerificationStatus', 'itemSku', 'fpod_itemoffrqty', 'fpod_purchasingUnit', 'unitName', 'fpod_leastSKUTotalqty', 'fpod_leastSKUBalanceqty', 'fpod_leastSKUreceivedqty', 'least_package_type_name']),
            sortInfo: {
                field: 'fpod_itemid',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fpo_id = Application.Finascop_Stockpo.Cache.fpo_id;
                }
            }
        });
        return store;
    };
    var newstockPoItemGridStorePo = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=stockpoItemStore',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpod_itemid',
                root: 'data'
            }, ['fpod_itemid', 'fpod_itemname', 'fpod_itemqty', 'item_received', 'item_balance', 'fpod_id', 'fpod_fpoId', 'fpod_totalqty', 'fpod_receivedqty', 'fpod_balanceqty', 'fpod_giftqty', 'fpod_giftrecqty',
                'fpod_giftbalqty', 'fpod_stockVerificationStatus', 'itemSku', 'fpod_itemoffrqty', 'fpod_purchasingUnit', 'unitName', 'fpod_leastSKUTotalqty', 'fpod_leastSKUBalanceqty', 'fpod_leastSKUreceivedqty', 'least_package_type_name']),
            sortInfo: {
                field: 'fpod_itemid',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fpo_id = Application.Finascop_Stockpo.Cache.fpo_id;
                },
                load: function () {
                    Ext.getCmp('newstockPoItemGrid').getSelectionModel().selectRow(0);

                }
            }
        });
        return store;
    };
    var poItemGrid = function () {

        var hfilters_contact = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'itemSku'
                }]
        });
        hfilters_contact.remote = true;
        hfilters_contact.autoReload = true;
        var stockPoItemGridStore = stockPoItemGridStorePo();
        var grid = new Ext.grid.GridPanel({
            store: stockPoItemGridStore,
            id: 'stockPoItemGrid',
            title: 'PO Items',
            autoScroll: true,
            view: new Ext.grid.GroupingView({
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_stockVerificationStatus') == 0)/*Nothing Done*/
                    {
                        return 'finascop_indicateColWHITE';
                    } else if (record.get('fpod_stockVerificationStatus') == 1) { /* Partial*/
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_stockVerificationStatus') == 2) { /* Full    */
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'

            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters_contact],
            fields: ['fpod_itemid', 'fpod_itemname', 'fpod_itemqty', 'item_received', 'item_balance', 'fpod_id', 'fpod_fpoId', 'fpod_totalqty', 'fpod_receivedqty', 'fpod_balanceqty', 'fpod_giftqty', 'fpod_giftrecqty', 'fpod_giftbalqty', 'fpod_stockVerificationStatus', 'itemSku'],
            viewConfig: {
                forceFit: true,
                markDirty: false
            },
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            frame: true,
            border: false,
            hideBorders: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'itemSku',
                    tooltip: 'Item',
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.itemSku + '"';
                        return value;
                    }
                }, {
                    header: 'Qty',
                    sortable: true,
                    dataIndex: 'fpod_totalqty',
                    align: 'right',
                    tooltip: 'Ordered Quantity'
                }, {
                    header: 'Unit',
                    sortable: true,
                    dataIndex: 'unitName',
                    align: 'right',
                    tooltip: 'Unit'
                }, {
                    header: 'SKU Qty',
                    sortable: true,
                    dataIndex: 'fpod_leastSKUTotalqty',
                    align: 'right',
                    tooltip: 'SKU Quantity'
                }, {
                    header: 'Received',
                    sortable: true,
                    dataIndex: 'fpod_leastSKUreceivedqty',
                    tooltip: 'SKU Received',
                    align: 'right',
                    width: 100,
                }, {
                    header: 'Balance',
                    sortable: true,
                    dataIndex: 'fpod_leastSKUBalanceqty',
                    align: 'right',
                    tooltip: 'Balance'
                }, {width: 20, dataIndex: ''}, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    hidden: true,
                    groupable: false,
                    width: 80,
                    items: [/* <?php if (user_access("finascop_stockpo", "addItemtoStock")) { ?> */
                        {
                            iconCls: 'hideicon',
                            tooltip: 'Add Stock',
                            hidden: true,
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                //if (_SESSION.br_PyramidLevel == 2) {
                                Application.Finascop_Stockpo.Cache.reselectPo = Ext.getCmp('stockPoGridPanel').getSelectionModel().getSelections()[0];
                                Application.Finascop_Stockpo.Cache.reselectPoindex = Ext.getCmp('stockPoGridPanel').store.indexOf(Application.Finascop_Stockpo.Cache.reselectPo);
                                addPoStock(record.get('fpod_id'), record.get('fpod_fpoId'), record.get('fpod_itemid'));
                                //} else {
                                // Ext.MessageBox.alert('Notification', 'Stock Inward is for Central Store(s)');
                                // }

                            }
                        }/*<?php } ?> */, {
                            iconCls: 'hideicon',
                            tooltip: 'Update Quantity',
                            hidden: true,
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                if (_SESSION.br_PyramidLevel == 2) {
                                    Application.Finascop_Stockpo.Cache.reselectPo = Ext.getCmp('stockPoGridPanel').getSelectionModel().getSelections()[0];
                                    Application.Finascop_Stockpo.Cache.reselectPoindex = Ext.getCmp('stockPoGridPanel').store.indexOf(Application.Finascop_Stockpo.Cache.reselectPo);
                                    addPoStockAdditionalQty(record);
                                } else {
                                    Ext.MessageBox.alert('Notification', 'Stock Inward is for CPD(s)');
                                }

                            }
                        }
                    ]

                }],
            fbar: [{
                    text: "Inward PO Items",
                    id: 'candidate_edit_btn',
                    iconCls: 'my-icon18',
                    tabIndex: 80,
                    handler: function () {
                        Application.Finascop_Stockpo.InwardPOItemsOrder(Application.Finascop_Stockpo.Cache.PoId);
                    }
                }]
        });
        return grid;
    };
    var newpoItemGrid = function () {

        var hfilters_contact = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'itemSku'
                }]
        });
        hfilters_contact.remote = true;
        hfilters_contact.autoReload = true;
        var stockPoItemGridStore = newstockPoItemGridStorePo();
        var grid = new Ext.grid.GridPanel({
            store: stockPoItemGridStore,
            id: 'newstockPoItemGrid',
            title: 'PO Items',
            region: 'center',
            autoScroll: true,
            view: new Ext.grid.GroupingView({
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_stockVerificationStatus') == 1) { /* Partial*/
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_stockVerificationStatus') == 2) { /* Full    */
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return 'finascop_indicateColWHITE';
                    }

                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'

            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters_contact],
            fields: ['fpod_itemid', 'fpod_itemname', 'fpod_itemqty', 'item_received', 'item_balance', 'fpod_id', 'fpod_fpoId', 'fpod_totalqty', 'fpod_receivedqty', 'fpod_balanceqty', 'fpod_giftqty', 'fpod_giftrecqty', 'fpod_giftbalqty', 'fpod_stockVerificationStatus', 'itemSku'],
            viewConfig: {
                forceFit: true,
                markDirty: false
            },
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: newgridSelectionChanged,
                    rowselect: function (sm, rowIndex, record) {
                        console.log(record);
                        Application.Finascop_Stockpo.Cache.fpod_id = 0;
                        Application.Finascop_Stockpo.Cache.fpod_itemid = 0;
                        Application.Finascop_Stockpo.Cache.fpod_id = record.get('fpod_id');
                        Application.Finascop_Stockpo.Cache.fpod_itemid = record.get('fpod_itemid');
                        Ext.getCmp('poStockItemQty').setValue(record.get('fpod_leastSKUBalanceqty'));
                        Ext.getCmp('poStockItemUnits').getStore().load({
                            params: {
                                itemId: record.get('fpod_itemid')
                            }
                        });
                        Ext.getCmp('poStockItemName').getStore().load({
                            params: {
                                fpod_id: record.get('fpod_id')
                            }
                        });
                        Ext.getCmp('poStockEntryGridPanel').getStore().load({
                            params: {
                                fpod_id: record.get('fpod_id'),
                                fpo_id: record.get('fpod_fpoId'),
                                itemId: record.get('fpod_itemid')
                            }
                        });

                    }
                }
            }),
            frame: true,
            border: false,
            hideBorders: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'itemSku',
                    tooltip: 'Item',
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.itemSku + '"';
                        return value;
                    }
                }, {
                    header: 'Qty',
                    sortable: true,
                    dataIndex: 'fpod_totalqty',
                    align: 'right',
                    tooltip: 'Ordered Quantity'
                }, {
                    header: 'Unit',
                    sortable: true,
                    dataIndex: 'unitName',
                    align: 'right',
                    tooltip: 'Unit'
                }, {
                    header: 'SKU Qty',
                    sortable: true,
                    dataIndex: 'fpod_leastSKUTotalqty',
                    align: 'right',
                    tooltip: 'SKU Quantity',
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.fpod_leastSKUTotalqty + ' ' + record.data.least_package_type_name + '"';
                        return value;
                    }
                }, {
                    header: 'Received',
                    sortable: true,
                    dataIndex: 'fpod_leastSKUreceivedqty',
                    tooltip: 'Received',
                    align: 'right',
                    width: 100,
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.fpod_leastSKUreceivedqty + ' ' + record.data.least_package_type_name + '"';
                        return value;
                    }
                }, {
                    header: 'Balance',
                    sortable: true,
                    dataIndex: 'fpod_leastSKUBalanceqty',
                    align: 'right',
                    tooltip: 'Balance',
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.fpod_leastSKUBalanceqty + ' ' + record.data.least_package_type_name + '"';
                        return value;
                    }
                }, {dataIndex: '', width: 20}]
        });
        return grid;
    };
    var addPoStockAdditionalQty = function (record) {
        if (Ext.isEmpty(upInvQty_window)) {
            var upInvQty_window = new Ext.Window({
                id: 'upInvQty_window',
                layout: 'fit',
                width: 430,
                title: 'Update Quantity',
                autoHeight: true,
                draggable: false,
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [new Ext.FormPanel({
                        frame: true,
                        monitorValid: true,
                        labelWidth: 150,
                        id: 'upInvQty_form',
                        height: 100,
                        items: [{
                                fieldLabel: 'Item',
                                xtype: 'displayfield',
                                id: 'itemName',
                                name: 'itemName',
                                value: record.data.itemSku
                            }, {
                                fieldLabel: 'Total Quantity Receivable',
                                xtype: 'numberfield',
                                id: 'new_totalqty',
                                width: 100,
                                name: 'new_totalqty',
                                tabIndex: 93,
                                allowBlank: false
                            }]
                    })],
                buttons: [{
                        text: 'Update',
                        id: 'btnsave',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var currentQty = record.data.fpod_totalqty;
                            var new_totalqty = Ext.getCmp('new_totalqty').getValue();
                            if (new_totalqty <= currentQty) {
                                Ext.MessageBox.alert("Notification", " Total quantity should be greater than " + currentQty);
                                Ext.getCmp('new_totalqty').reset();
                            } else {
                                Ext.Ajax.request({
                                    waitMsg: 'Processing',
                                    method: 'POST',
                                    url: modURL + '&op=updateTotalQtyinPO',
                                    params: {
                                        poId: record.data.fpod_fpoId,
                                        podId: record.data.fpod_id,
                                        itemId: record.data.fpod_itemid,
                                        fpod_itemoffrqty: record.data.fpod_itemoffrqty,
                                        currentQty: currentQty,
                                        new_totalqty: new_totalqty
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true)
                                        {
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('upInvQty_window').close();

                                            Ext.getCmp('stockPoGridPanel').getStore().load({
                                                // store loading is asynchronous, use a load listener or callback to handle results
                                                callback: function () {
                                                    Ext.getCmp('stockPoGridPanel').getSelectionModel().selectRow(Application.Finascop_Stockpo.Cache.reselectPoindex);
                                                }
                                            });
                                        } else
                                        {
                                            Ext.MessageBox.alert("Failure", "Error moving Data");
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.msg);
                                    }
                                });
                            }

                        }
                    }, {
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            upInvQty_window.close();
                        }
                    }]
            });

        }
        upInvQty_window.doLayout();
        upInvQty_window.show(this);
        upInvQty_window.center();
    };
    var poItemDetails = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: 'Item Details',
            height: 200,
            autoScroll: true,
            id: 'stockPoItemDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Item </th><td> {itemSku} </td></tr>',
                    '<tr><th width="40%">Ordered Quantity </th><td>{fpod_totalqty}{fpod_purchasingUnitName}</td></tr>',
                    '<tr><th width="40%">Total SKUs </th><td>{SKUS}</td></tr>',
                    '<tr><th width="40%">Unit </th><td>{unitName}</td></tr>',
                    '<tr><th width="40%">MRP </th><td> {fpod_itemmrp} </td></tr>',
                    '<tr><th width="40%">Offer Rate </th><td> {fpod_itemoffrrate} </td></tr>',
                    '<tr><th width="40%">Effective Purchase Rate </th><td> {fpod_effectiverate} </td></tr>',
                    '<tpl if="fpod_giftname != \'\'"><tr><th width="40%">Gift Detail </th><td> {fpod_giftname} </td></tr></tpl>',
                    '<tpl if="fpod_giftqty != \'\'"><tr><th width="40%">Gift Quantity</th><td> {fpod_giftqty} </td></tr></tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
//    var ItemStoreToaddStock = function (fpod_id) {
//        var partyStore = new Ext.data.JsonStore({
//            autoLoad: true,
//            url: modURL + '&op=getItemStoreforStock',
//            method: 'post',
//            fields: ['item_id', 'item_name'],
//            totalProperty: 'totalCount',
//            root: 'data',
//            remoteFilter: true,
//            listeners: {
//                beforeload: function (store, opt) {
//                    store.baseParams = {
//                        fpod_id: fpod_id
//                    };
//                }
//            }
//        });
//        return partyStore;
//    };
    var ItemStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=getItemStoreforStock',
        method: 'post',
        fields: ['item_id', 'item_name'],
        totalProperty: 'totalCount',
        root: 'data',
        remoteFilter: true,
        listeners: {
            beforeload: function (store, opt) {
                store.baseParams = {
                    fpod_id: Application.Finascop_Stockpo.Cache.fpod_id
                };
            },
            load: function (store, rec, opt) {
                if (store.totalLength > 0) { //totalLength == 1
                    Ext.getCmp('poStockItemName').setValue(store.getAt(0).get("item_id"));
                }
            }
        }
    });
    var packTypeStore = new Ext.data.JsonStore({
        url: modURL + '&op=getPTStore',
        fields: ['package_type_id', 'package_type_name'],
        totalProperty: 'totalCount',
        root: 'data',
        autoload: true,
        remoteFilter: true,
        listeners: {
            beforeload: function () {
                this.baseParams.stdpckl11 = '';
                this.baseParams.stdpckl12 = '';
                this.baseParams.stdpckl21 = '';
                this.baseParams.stdpckl31 = '';
                this.baseParams.stdpckl41 = '';
            }
        }
    });
    var ItemStoreToaddStock = function (fpod_id) {
        var ItemStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getItemStoreforStock',
            method: 'post',
            fields: ['item_id', 'item_name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteFilter: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        fpod_id: fpod_id
                    };
                },
                load: function (store, rec, opt) {
                    if (store.totalLength > 0) { //totalLength == 1
                        Ext.getCmp('poStockItemName').setValue(store.getAt(0).get("item_id"));
                    }
                }
            }
        });
        return ItemStore;
    };
    var poItemStockform = function (fpod_id, fpo_id) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var ItemStore = ItemStoreToaddStock(fpod_id);
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 100,
            frame: true,
            border: false,
            id: 'poItemStockItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.15,
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Branch',
                                    id: 'currentCPD',
                                    name: 'currentCPD',
                                    disabled: true,
                                    value: _SESSION.current_branch,
                                    anchor: '85%'
                                }
                            ]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.25,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Item/Gift',
                                    id: 'poStockItemName',
                                    name: 'poStockItemName',
                                    anchor: '99%',
                                    displayField: 'item_name',
                                    valueField: 'item_id',
                                    hiddenName: 'poStockItemName',
                                    store: ItemStore,
                                    triggerAction: 'all',
                                    allowBlank: false,
                                    labelStyle: mandatory_label,
                                    mode: 'remote',
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    enableKeyEvents: true,
                                    autoSelect: true,
                                    tabIndex: 901,
                                    listeners: {
                                        select: function () {
                                            var itemId = Ext.getCmp('poStockItemName').getValue();

                                            if (itemId > 0) {
                                                Ext.getCmp('poStockItemSP').setValue(100);
                                                Ext.getCmp('poStockItemUnits').allowBlank = false;
                                                Ext.getCmp('poStockItemUnits').getStore().load({
                                                    params: {
                                                        itemId: itemId
                                                    }
                                                });

                                            } else {
                                                Ext.getCmp('poStockItemUnits').allowBlank = true;
                                                Ext.getCmp('poStockItemUnits').disable();
                                                Ext.getCmp('poStockItemUnits').disable();

                                            }

                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Batch No',
                                    allowBlank: true,
                                    id: 'poStockItemBatch',
                                    name: 'poStockItemBatch',
                                    tabIndex: 902,
                                    anchor: '99%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Selling Price',
                                    allowBlank: true,
                                    id: 'poStockItemSP',
                                    name: 'poStockItemSP',
                                    readOnly: true,
                                    hidden: true,
                                    anchor: '99%'
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Quantity',
                                    id: 'poStockItemQty',
                                    name: 'poStockItemQty',
                                    allowBlank: false,
                                    allowNegative: false,
                                    allowDecimals: false,
                                    labelAlign: 'top',
                                    anchor: '99%',
                                    tabIndex: 903
                                }
                            ]

                        }, {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'combo',
                                    fieldLabel: 'Units',
                                    id: 'poStockItemUnits',
                                    name: 'poStockItemUnits',
                                    labelStyle: mandatory_label,
                                    allowBlank: true,
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
                                    hiddenName: 'poStockItemUnits',
                                    tabIndex: 904,
                                    listeners: {
                                        select: function () {
                                            var poStockItemUnits = Ext.getCmp('poStockItemUnits').getValue();
                                            var itemId = Ext.getCmp('poStockItemName').getValue();
                                            var poStockItemQty = Ext.getCmp('poStockItemQty').getValue();
                                            if (poStockItemUnits > 0 && itemId > 0 && poStockItemQty > 0) {
                                                Ext.Ajax.request({
                                                    url: modURL + '&op=getPackingDetails',
                                                    params: {poStockItemUnits: poStockItemUnits, itemId: itemId, poStockItemQty: poStockItemQty},
                                                    failure: function (response) {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    },
                                                    success: function (response, options) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        console.log('tmp', tmp);
                                                        if (tmp.success === true) {
                                                            console.log('here');
                                                            Ext.getCmp('leastSKUDisplay').show();
                                                            Ext.getCmp('sileast_package_type_id').setValue(tmp.least_package_type_id);
                                                            Ext.getCmp('sileast_package_type_name').setValue(tmp.least_package_type_name);
                                                            Ext.getCmp('leastSKUCount').setValue(tmp.leastSKUqty);

                                                            Ext.getCmp('leastSKUDisplay').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                        } else {
                                                            Ext.MessageBox.alert('Error', response.responseText);

                                                        }
                                                    }
                                                });
                                            } else {
                                                Ext.MessageBox.alert('Error', "Enter valid quantity.");
                                                Ext.getCmp('poStockItemUnits').reset();
                                            }

                                        }
                                    }
                                }
                            ]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'datefield',
                                    fieldLabel: 'Expiry Date',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    id: 'poStockItemExpiryDate',
                                    name: 'poStockItemExpiryDate',
                                    anchor: '99%',
                                    format: 'd-m-Y',
                                    minValue: new Date(),
                                    tabIndex: 905,
                                    value: new Date(new Date().setFullYear(new Date().getFullYear() + 1))
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'button',
                                    text: 'Save',
                                    tabIndex: 906,
                                    id: 'stockAddinPoItems',
                                    iconCls: 'finascop_add',
                                    style: 'margin-top:17px;',
                                    handler: function () {

                                        var poStockItemName = Ext.getCmp('poStockItemName').getRawValue();
                                        var poStockItemId = Ext.getCmp('poStockItemName').getValue();
                                        var poStockItemBatch = Ext.getCmp('poStockItemBatch').getValue();
                                        var poStockItemQty = Ext.getCmp('poStockItemQty').getValue();
                                        var poStockItemSP = Ext.getCmp('poStockItemSP').getValue();
                                        var poStockItemExpiryDate = Ext.getCmp('poStockItemExpiryDate').getValue().toString();
                                        var sileast_package_type_id = Ext.getCmp('sileast_package_type_id').getValue();
                                        var sileast_package_type_name = Ext.getCmp('sileast_package_type_name').getValue();
                                        var leastSKUCount = Ext.getCmp('leastSKUCount').getValue();
                                        var today = new Date();
                                        var minExpryDate = today.setMonth(today.getMonth() + 6);
                                        var expiryDate = new Date(poStockItemExpiryDate);
                                        console.log('expiryDate', expiryDate);
                                        console.log('minExpryDate', minExpryDate);
                                        Application.Finascop_Stockpo.Cache.aspfpo_id = fpo_id;
                                        Application.Finascop_Stockpo.Cache.aspfpod_id = Application.Finascop_Stockpo.Cache.fpod_id;
                                        // if (expiryDate >= minExpryDate) {
                                        if (!Ext.isEmpty(Ext.getCmp('poStockItemName').getValue() && Ext.getCmp('poStockItemExpiryDate').getValue())) {
                                            //printBarcode(poStockItemName,poStockItemId,poStockItemBatch,poStockItemQty,poStockItemExpiryDate,Application.Finascop_Stockpo.Cache.aspfpo_id,Application.Finascop_Stockpo.Cache.aspfpod_id);
                                            //postToUrl(modURL + '&op=addItemtoStock', {poStockItemName: poStockItemName, poStockItemId: poStockItemId,poStockItemBatch:poStockItemBatch,poStockItemQty:poStockItemQty,poStockItemExpiryDate:poStockItemExpiryDate,fpo_id:Application.Finascop_Stockpo.Cache.aspfpo_id,fpod_id:Application.Finascop_Stockpo.Cache.aspfpod_id,apikey:_SESSION.apikey,tstamp:t_stamp});

                                            var form_data = {
                                                poStockItemName: poStockItemName,
                                                poStockItemId: poStockItemId,
                                                poStockItemBatch: poStockItemBatch,
                                                poStockItemQty: poStockItemQty,
                                                poStockItemExpiryDate: poStockItemExpiryDate,
                                                poStockItemSP: poStockItemSP,
                                                fpod_id: Application.Finascop_Stockpo.Cache.fpod_id,
                                                fpo_id: fpo_id,
                                                sileast_package_type_id: sileast_package_type_id,
                                                sileast_package_type_name: sileast_package_type_name,
                                                leastSKUCount: leastSKUCount

                                            };
                                            var params = {
                                                action: 'Add Stock',
                                                module: 'Finascop_Stockpo',
                                                op: 'addItemtoStock',
                                                extrainfo: 'Add Stock item based on PO',
                                                id: fpod_id
                                            };
                                            APICall(params, Application.Finascop_Stockpo.addItemtoStock, form_data);

                                        } else {
                                            Ext.MessageBox.alert("Notification", 'Please fill required fields');
                                        }
//                                        } else {
//                                            Ext.MessageBox.alert("Notification", 'Expiry date of item is not valid.');
//                                        }


                                    }

                                }]
                        }]
                },
                {
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
                                    name: 'leastSKUDisplay',
                                    id: 'leastSKUDisplay',
                                    hidden: true
                                }, {
                                    xtype: 'hidden',
                                    id: 'sileast_package_type_id',
                                    name: 'sileast_package_type_id'
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'sileast_package_type_name',
                                    name: 'sileast_package_type_name'
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'leastSKUCount',
                                    name: 'leastSKUCount'
                                }]
                        }]
                }]
        });
        return poItemStockformPanel;
    };
    var newpoItemStockform = function (fpod_id, fpo_id) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        //var ItemStore = ItemStoreToaddStock(fpod_id);
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 100,
            frame: true,
            border: false,
            id: 'poItemStockItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Branch',
                                    id: 'currentCPD',
                                    name: 'currentCPD',
                                    disabled: true,
                                    value: _SESSION.current_branch,
                                    anchor: '85%'
                                }
                            ]

                        }, {
                            layout: 'form',
                            labelAlign: 'top',
							bodyStyle: {"padding-top": "15px"},
                            columnWidth: 0.05,
                            items: [
                                {
                                    xtype: 'button',
                                    text: '',
                                    tooltip: 'Enter Barcode',
                                    iconCls: 'scan',
                                    handler: function () {
                                        Application.Finascop_Stockpo.enterBarcode();
                                    }
                                }
                            ]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.25,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Item/Gift',
                                    id: 'poStockItemName',
                                    name: 'poStockItemName',
                                    anchor: '99%',
                                    displayField: 'item_name',
                                    valueField: 'item_id',
                                    hiddenName: 'poStockItemName',
                                    store: ItemStore,
                                    triggerAction: 'all',
                                    allowBlank: false,
                                    labelStyle: mandatory_label,
                                    mode: 'remote',
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    enableKeyEvents: true,
                                    autoSelect: true,
                                    tabIndex: 901,
                                    listeners: {
                                        select: function () {
                                            var itemId = Ext.getCmp('poStockItemName').getValue();

                                            if (itemId > 0) {
                                                Ext.getCmp('poStockItemSP').setValue(100);
                                                Ext.getCmp('poStockItemUnits').allowBlank = false;
                                                Ext.getCmp('poStockItemUnits').getStore().load({
                                                    params: {
                                                        itemId: itemId
                                                    }
                                                });

                                            } else {
                                                Ext.getCmp('poStockItemUnits').allowBlank = true;
                                                Ext.getCmp('poStockItemUnits').disable();
                                                Ext.getCmp('poStockItemUnits').disable();

                                            }

                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Batch No',
                                    allowBlank: true,
                                    id: 'poStockItemBatch',
                                    name: 'poStockItemBatch',
                                    tabIndex: 902,
                                    anchor: '99%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Selling Price',
                                    allowBlank: true,
                                    id: 'poStockItemSP',
                                    name: 'poStockItemSP',
                                    readOnly: true,
                                    hidden: true,
                                    anchor: '99%'
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Quantity',
                                    id: 'poStockItemQty',
                                    name: 'poStockItemQty',
                                    allowBlank: false,
                                    allowNegative: false,
                                    allowDecimals: false,
                                    labelAlign: 'top',
                                    anchor: '99%',
                                    tabIndex: 903
                                }
                            ]

                        }, {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'combo',
                                    fieldLabel: 'Units',
                                    id: 'poStockItemUnits',
                                    name: 'poStockItemUnits',
                                    labelStyle: mandatory_label,
                                    allowBlank: true,
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
                                    hiddenName: 'poStockItemUnits',
                                    tabIndex: 904,
                                    listeners: {
                                        select: function () {
                                            var poStockItemUnits = Ext.getCmp('poStockItemUnits').getValue();
                                            var itemId = Ext.getCmp('poStockItemName').getValue();
                                            var poStockItemQty = Ext.getCmp('poStockItemQty').getValue();
                                            if (poStockItemUnits > 0 && itemId > 0 && poStockItemQty > 0) {
                                                Ext.Ajax.request({
                                                    url: modURL + '&op=getPackingDetails',
                                                    params: {poStockItemUnits: poStockItemUnits, itemId: itemId, poStockItemQty: poStockItemQty},
                                                    failure: function (response) {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    },
                                                    success: function (response, options) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        console.log('tmp', tmp);
                                                        if (tmp.success === true) {
                                                            console.log('here');
                                                            Ext.getCmp('leastSKUDisplay').show();
                                                            Ext.getCmp('sileast_package_type_id').setValue(tmp.least_package_type_id);
                                                            Ext.getCmp('sileast_package_type_name').setValue(tmp.least_package_type_name);
                                                            Ext.getCmp('leastSKUCount').setValue(tmp.leastSKUqty);

                                                            Ext.getCmp('leastSKUDisplay').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                        } else {
                                                            Ext.MessageBox.alert('Error', response.responseText);

                                                        }
                                                    }
                                                });
                                            } else {
                                                Ext.MessageBox.alert('Error', "Enter valid quantity.");
                                                Ext.getCmp('poStockItemUnits').reset();
                                            }

                                        }
                                    }
                                }
                            ]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'datefield',
                                    fieldLabel: 'Expiry Date',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    id: 'poStockItemExpiryDate',
                                    name: 'poStockItemExpiryDate',
                                    anchor: '99%',
                                    format: 'd-m-Y',
                                    minValue: new Date(),
                                    tabIndex: 905,
                                    value: new Date(new Date().setFullYear(new Date().getFullYear() + 1))
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'button',
                                    text: 'Save',
                                    tabIndex: 906,
                                    id: 'stockAddinPoItems',
                                    iconCls: 'finascop_add',
                                    style: 'margin-top:17px;',
                                    handler: function () {

                                        var poStockItemName = Ext.getCmp('poStockItemName').getRawValue();
                                        var poStockItemId = Ext.getCmp('poStockItemName').getValue();
                                        var poStockItemBatch = Ext.getCmp('poStockItemBatch').getValue();
                                        var poStockItemQty = Ext.getCmp('poStockItemQty').getValue();
                                        var poStockItemSP = Ext.getCmp('poStockItemSP').getValue();
                                        var poStockItemExpiryDate = Ext.getCmp('poStockItemExpiryDate').getValue().toString();
                                        var sileast_package_type_id = Ext.getCmp('sileast_package_type_id').getValue();
                                        var sileast_package_type_name = Ext.getCmp('sileast_package_type_name').getValue();
                                        var leastSKUCount = Ext.getCmp('leastSKUCount').getValue();
                                        var today = new Date();
                                        var minExpryDate = today.setMonth(today.getMonth() + 6);
                                        var expiryDate = new Date(poStockItemExpiryDate);
                                        console.log('expiryDate', expiryDate);
                                        console.log('minExpryDate', minExpryDate);
                                        Application.Finascop_Stockpo.Cache.aspfpo_id = fpo_id;
                                        Application.Finascop_Stockpo.Cache.aspfpod_id = Application.Finascop_Stockpo.Cache.fpod_id;
                                        // if (expiryDate >= minExpryDate) {
                                        if (!Ext.isEmpty(Ext.getCmp('poStockItemName').getValue() && Ext.getCmp('poStockItemExpiryDate').getValue())) {
                                            //printBarcode(poStockItemName,poStockItemId,poStockItemBatch,poStockItemQty,poStockItemExpiryDate,Application.Finascop_Stockpo.Cache.aspfpo_id,Application.Finascop_Stockpo.Cache.aspfpod_id);
                                            //postToUrl(modURL + '&op=addItemtoStock', {poStockItemName: poStockItemName, poStockItemId: poStockItemId,poStockItemBatch:poStockItemBatch,poStockItemQty:poStockItemQty,poStockItemExpiryDate:poStockItemExpiryDate,fpo_id:Application.Finascop_Stockpo.Cache.aspfpo_id,fpod_id:Application.Finascop_Stockpo.Cache.aspfpod_id,apikey:_SESSION.apikey,tstamp:t_stamp});

                                            var form_data = {
                                                poStockItemName: poStockItemName,
                                                poStockItemId: poStockItemId,
                                                poStockItemBatch: poStockItemBatch,
                                                poStockItemQty: poStockItemQty,
                                                poStockItemExpiryDate: poStockItemExpiryDate,
                                                poStockItemSP: poStockItemSP,
                                                fpod_id: Application.Finascop_Stockpo.Cache.fpod_id,
                                                fpo_id: fpo_id,
                                                sileast_package_type_id: sileast_package_type_id,
                                                sileast_package_type_name: sileast_package_type_name,
                                                leastSKUCount: leastSKUCount

                                            };
                                            var params = {
                                                action: 'Add Stock',
                                                module: 'Finascop_Stockpo',
                                                op: 'addItemtoStock',
                                                extrainfo: 'Add Stock item based on PO',
                                                id: fpod_id
                                            };
                                            APICall(params, Application.Finascop_Stockpo.addItemtoStock, form_data);

                                        } else {
                                            Ext.MessageBox.alert("Notification", 'Please fill required fields');
                                        }
//                                        } else {
//                                            Ext.MessageBox.alert("Notification", 'Expiry date of item is not valid.');
//                                        }


                                    }

                                }]
                        }]
                },
                {
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
                                    name: 'leastSKUDisplay',
                                    id: 'leastSKUDisplay',
                                    hidden: true
                                }, {
                                    xtype: 'hidden',
                                    id: 'sileast_package_type_id',
                                    name: 'sileast_package_type_id'
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'sileast_package_type_name',
                                    name: 'sileast_package_type_name'
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'leastSKUCount',
                                    name: 'leastSKUCount'
                                }]
                        }]
                }]
        });
        return poItemStockformPanel;
    };
    function convert(str) {
        var date = new Date(str),
                mnth = ("0" + (date.getMonth() + 1)).slice(-2),
                day = ("0" + date.getDate()).slice(-2);
        return [day, mnth, date.getFullYear()].join("-");
    }

    var createprintBarcodePanel = function (poStockItemName, poStockItemId, poStockItemBatch, poStockItemQty, poStockItemExpiryDate) {
        var FPOID = Application.Finascop_Stockpo.Cache.aspfpo_id;
        var FPODID = Application.Finascop_Stockpo.Cache.aspfpod_id
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var date_frm = convert(poStockItemExpiryDate);
        var src = '?module=finascop_stockpo&op=addItemtoStock&poStockItemName=' + poStockItemName + '&poStockItemId=' + poStockItemId + '&poStockItemBatch=' + poStockItemBatch + '&poStockItemQty=' + poStockItemQty + '&poStockItemExpiryDate=' + date_frm + '&fpod_id=' + FPODID + '&fpo_id=' + FPOID + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            id: 'printOrderPanel',
            items: [{
                    region: 'center',
                    border: false,
                    html: '<iframe src="' + src +
                            '" id="iframeRequest" name="iframeRequest" ' +
                            'width="100%" height="100%" style="border:none">'
                }]
        });
        return myPanel;
    };
    var printBarcode = function (poStockItemName, poStockItemId, poStockItemBatch, poStockItemQty, poStockItemExpiryDate) {
        var printBarcodePanel = createprintBarcodePanel(poStockItemName, poStockItemId, poStockItemBatch, poStockItemQty, poStockItemExpiryDate, Application.Finascop_Stockpo.Cache.aspfpo_id, Application.Finascop_Stockpo.Cache.aspfpod_id, 'printBarcode');

        var printBarcodeWindow = Ext.getCmp('printBarcodeWindow');

        if (Ext.isEmpty(printBarcodeWindow)) {
            printBarcodeWindow = new Ext.Window({
                id: 'printBarcodeWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Barcode Details',
                width: 800,
                autoHeight: true,
                items: [printBarcodePanel],
                buttons: [{
                        text: 'Cancel',
                        iconCls: 'my-icon61',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        handler: function () {
                            printBarcodeWindow.close();
                        }
                    }, {
                        text: 'Print',
                        iconCls: 'my-icon141',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        handler: function () {
                            var params = {
                                // order_id: order_id,
                                action: 1
                            };
                            //updateOrderStatus(params);
                            iframeRequest.focus();
                            iframeRequest.print();
                            printBarcodeWindow.close();
                        }
                    }],
            });
        }
        printBarcodeWindow.doLayout();
        printBarcodeWindow.show();
        printBarcodeWindow.center();
    };

    var createrePrintBarcodePanel = function (stii_id) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var src = '?module=finascop_stockpo&op=rePrintBarcodeOp&stii_id=' + stii_id + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            id: 'printOrderPanel',
            items: [{
                    region: 'center',
                    border: false,
                    html: '<iframe src="' + src +
                            '" id="iframeRequest" name="iframeRequest" ' +
                            'width="100%" height="100%" style="border:none">'
                }]
        });
        return myPanel;
    };
    var rePrintBarcode = function (stii_id) {
        var t = new Date();
        var t_stamp = t.format("YmdHis")
        var barcodeStore = barcodeItemStore(stii_id);
        var rePrintBarcodeWindow = Ext.getCmp('printBarcodeWindow');
        if (Ext.isEmpty(rePrintBarcodeWindow)) {
            rePrintBarcodeWindow = new Ext.Window({
                id: 'rePrintBarcodeWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Barcode Details',
                width: 300,
                autoHeight: true,
                //items: [rePrintBarcodePanel],
                items: [{
                        layout: 'form',
                        columnWidth: 0.25,
                        labelAlign: 'top',
                        bodyStyle: {"background-color": "F1F1F1"},
                        items: [{
                                fieldLabel: 'From Barcode',
                                xtype: 'combo',
                                displayField: 'stiid_barcode',
                                valueField: 'stiid_barcode',
                                mode: 'local',
                                id: 'comboboxBarcode',
                                hiddenName: 'comboboxBarcode',
                                name: 'comboboxBarcode',
                                typeAhead: true,
                                minChars: 3,
                                triggerAction: 'all',
                                selectOnFocus: true,
                                lazyRender: true,
                                anchor: '98%',
                                allowBlank: false,
                                hideTrigger: true,
                                store: barcodeStore,
                                editable: true,
                                msgTarget: 'under',
                                tabIndex: 504,
                                labelStyle: mandatory_label
                            }, {
                                fieldLabel: 'To Barcode',
                                xtype: 'combo',
                                displayField: 'stiid_barcode',
                                valueField: 'stiid_barcode',
                                mode: 'local',
                                id: 'comboboxToBarcode',
                                hiddenName: 'comboboxToBarcode',
                                name: 'comboboxToBarcode',
                                typeAhead: true,
                                minChars: 3,
                                triggerAction: 'all',
                                selectOnFocus: true,
                                lazyRender: true,
                                anchor: '98%',
                                allowBlank: false,
                                hideTrigger: true,
                                store: barcodeStore,
                                editable: true,
                                msgTarget: 'under',
                                tabIndex: 504,
                                labelStyle: mandatory_label
                            }]
                    }],
                buttons: [{
                        text: 'Cancel',
                        iconCls: 'my-icon61',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        handler: function () {
                            rePrintBarcodeWindow.close();
                        }
                    }, {
                        text: 'Print',
                        iconCls: 'my-icon141',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        handler: function () {
                            var params = {
                                action: 1
                            };
                            var stiid_barcode = Ext.getCmp('comboboxBarcode').getRawValue();
                            var stiid_Tobarcode = Ext.getCmp('comboboxToBarcode').getRawValue();
                            Application.Finascop_Stockpo.Cache.stii_id = stii_id;
                            Ext.Ajax.request({
                                url: modURL + '&op=checkBarcode',
                                method: 'POST',
                                params: {stiid_barcode: stiid_barcode, stiid_Tobarcode: stiid_Tobarcode, stii_id: Application.Finascop_Stockpo.Cache.stii_id, apikey: _SESSION.apikey, tstamp: t_stamp},
                                success: function (res) {
                                    var tmp = Ext.decode(res.responseText);
                                    if (tmp.success === true) {
                                        postToUrl(modURL + '&op=rePrintBarcodeOp', {stiid_barcode: stiid_barcode, stiid_Tobarcode: stiid_Tobarcode, stii_id: Application.Finascop_Stockpo.Cache.stii_id, apikey: _SESSION.apikey, tstamp: t_stamp});
                                        rePrintBarcodeWindow.close();
                                    }
                                    else {
                                        Ext.MessageBox.alert('Error', 'Invalid barcode Entered!');
                                    }
                                },
                                failure: function () {
                                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                }
                            });

                        }
                    }],
            });
        }
        rePrintBarcodeWindow.doLayout();
        rePrintBarcodeWindow.show();
        rePrintBarcodeWindow.center();
    };
    var rePrintBarcodeDetails = function (stiid_barcode) {
        Application.Finascop_Stockpo.Cache.stii_id = stii_id;
        var form_data = {
            form: Ext.getCmp('rePrintBarcodeWindow').getForm().getValues()
        };
        var params = {
            action: 'reprint barcodes',
            module: 'finascop_stockpo',
            op: 'rePrintBarcodeOp',
            extrainfo: 'Reprint Baracode',
            id: Application.finascop_stockpo.Cache.stii_id
        };
        APICall(params, Application.Finascop_Stockpo.rePrintBarcodeDetails, form_data);
    };


    var poStockEntryItemStore = function (fpod_id, fpo_id, itemId) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listpoStockEntryItemStore',
            fields: ['stii_itemmastername', 'stii_batch', 'stii_qty', 'stii_expirydate', 'stii_id', 'stii_isgift', 'stii_createdon'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        fpod_id: fpod_id,
                        fpo_id: fpo_id,
                        itemId: itemId
                    };
                }

            }
        });
        return purchase_invoice_store;
    };

    var barcodeItemStore = function (stii_id) {
        var barcode_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=barcodeItemStore',
            fields: ['stiid_barcode', 'stii_id'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        stii_id: stii_id,
                    };
                }, load: function (store, record) {
                    //console.log(record.0.data);
                    var length = record.length;
                    var barcodeStart = record[0].data['stiid_barcode'];
                    var barcodeLast = record[length - 1].data['stiid_barcode'];
                    Ext.getCmp('comboboxBarcode').setValue(barcodeStart);
                    Ext.getCmp('comboboxBarcode').setRawValue(barcodeStart);
                    Ext.getCmp('comboboxToBarcode').setValue(barcodeLast);
                    Ext.getCmp('comboboxToBarcode').setRawValue(barcodeLast);
                }

            }
        });
        return barcode_store;
    };



    var poItemStockGrid = function (fpod_id, fpo_id, itemId) {
        var poStockEntryGridStore = poStockEntryItemStore(fpod_id, fpo_id, itemId);
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: poStockEntryGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'poStockEntryGridPanel',
            //iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'stii_itemmastername',
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'stii_qty',
                    align: 'right',
                    tooltip: 'Quantity'
                }, {
                    header: 'Batch',
                    sortable: true,
                    dataIndex: 'stii_batch',
                    tooltip: 'Batch'
                }, {
                    header: 'Inward Date',
                    sortable: true,
                    dataIndex: 'stii_createdon',
                    tooltip: 'Inward Date'
                }, {
                    header: 'Expiry Date',
                    sortable: true,
                    dataIndex: 'stii_expirydate',
                    tooltip: 'Expiry Date'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [/* <?php if (user_access("finascop_stockpo", "addItemtoStock")) { ?> */
                        {
                            iconCls: 'my-icon18',
                            tooltip: 'Print Barcode',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                //console.log(record);
                                //if (_SESSION.br_PyramidLevel == 2) {
                                rePrintBarcode(record.get('stii_id'));
                                //addPoStock(record.get('fpod_id'), record.get('fpod_fpoId'), record.get('fpod_itemid'));
                                //} else {
                                // Ext.MessageBox.alert('Notification', 'Stock Inward is for Central Store(s)');
                                //}

                            }
                        }/*<?php } ?> */, {
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _isGift = data.stii_isgift;
                                if (_isGift == 1) {
                                    this.items[1].tooltip = 'Upload Gift Image';
                                    return 'upload_image';
                                }
                                else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.store.getAt(rowIndex);
                                var stii_id = record.data.stii_id;
                                Ext.Ajax.request({
                                    url: modURL + '&op=getGiftImage',
                                    method: 'POST',
                                    params: {
                                        stii_id: stii_id
                                    },
                                    success: function (res) {
                                        var tmp = Ext.decode(res.responseText);
                                        console.log("temp is -", tmp);
                                        if (tmp.data != '')
                                        {
                                            var img_url = tmp.data.stii_imageUrl;
                                            Application.Finascop_Stockpo.uploadGiftImage(stii_id, img_url);
                                        }
                                        else {
                                            var img_url = '';
                                            Application.Finascop_Stockpo.uploadGiftImage(stii_id, img_url);

                                        }
                                    }
                                })
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
    var addPoStock = function () {
        var fpod_id = arguments[0];
        var fpo_id = arguments[1];
        var itemId = arguments[2];
        var resultWindow = new Ext.Window({
            id: "windowFinascopStockpoAddpoItems",
            //iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 800,
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'north',
                    height: 200,
                    items: poItemDetails()
                }, {
                    region: 'center',
                    layout: 'fit',
                    height: 100,
                    items: poItemStockform(fpod_id, fpo_id)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 250,
                    items: poItemStockGrid(fpod_id, fpo_id, itemId)
                }],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        Ext.getCmp('windowFinascopStockpoAddpoItems').close();
                        Ext.getCmp('stockPoGridPanel').getStore().load({
                            // store loading is asynchronous, use a load listener or callback to handle results
                            callback: function () {
                                Ext.getCmp('stockPoGridPanel').getSelectionModel().selectRow(Application.Finascop_Stockpo.Cache.reselectPoindex);
                            }
                        });
                    }

                }
            ], listeners: {
                afterrender: function () {
                    Application.Finascop_Stockpo.itemViewMode(fpod_id);
                }
            }

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var panelforpoDetails = function (id) {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Stock Inward',
            id: id,
            //iconCls: 'my-icon444',
            items: [verifyPoGrid(), new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.5,
                    autoScroll: true,
                    id: 'verifyPOparentPanel',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    items: [{
                            region: 'north',
                            height: 100,
                            items: poDetailsView()
                        }, {
                            region: 'center',
                            layout: 'fit',
                            items: poItemGrid()
                        }
                    ]
                })
            ]
        });
        return panel;
    };
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    ;
    var giftImageUploadForm = function (img) {
        return new Ext.Panel({
            height: "400",
            items: [new Ext.Panel({
                    layout: "fit",
                    id: 'gift_main_image_panel',
                    tpl: new Ext.XTemplate('<div class="details-outer">',
                            '<img width="120" id="demo123" height="100" src="{img_src}"></img>',
                            '</div>')
                }),
                {
                    xtype: 'hidden',
                    id: 'aws_file_location',
                    name: 'aws_file_location'
                }, {
                    xtype: 'hidden',
                    id: 'aws_file_bucket',
                    name: 'aws_file_bucket'
                },
                new Ext.form.FormPanel({
                    id: 'gift_image_upload',
                    layout: 'form',
                    fileUpload: true,
                    autoHeight: true,
                    frame: true,
                    items: [
                        {
                            xtype: 'hidden',
                            id: 'file_name', name: 'file_name'
                        }, {
                            xtype: 'hidden',
                            id: 'albumBucketName',
                            name: 'albumBucketName'
                        }, {
                            xtype: 'hidden',
                            id: 'accessKey',
                            name: 'accessKey'
                        }, {
                            xtype: 'hidden',
                            id: 'secretKey',
                            name: 'secretKey'
                        }, {
                            xtype: 'hidden',
                            id: 'bucketRegion',
                            name: 'bucketRegion'
                        },
                        {
                            xtype: 'hidden',
                            id: 'oncompleteurl',
                            name: 'oncompleteurl'
                        },
                        {
                            xtype: 'hidden',
                            id: 'img_path_db',
                            name: 'img_path_db'
                        },
                        {
                            xtype: 'box',
                            fieldLabel: 'Existing Image',
                            width: 120,
                            height: 100,
                            id: 'exist_img_box',
                            autoEl: {tag: 'img', src: img, width: 120, height: 100}
                        }
                    ],
                    buttons: [
                        {
                            xtype: 'fileuploadfield',
                            id: 'giftimg_file',
                            anchor: '98%',
                            fieldLabel: 'Select File',
                            name: 'file',
                            allowBlank: true,
                            buttonOnly: true,
                            // hidden: true,
                            buttonCfg: {
                                text: '',
                                iconCls: 'finascop_upload_file',
                                width: 80
                            },
                            validator: function (v) {
                                if (v != '') {
                                    v = v.toLowerCase();
                                    var exp = /^.*\.(png|jpg|gif)$/i;
                                    if (!(exp.test(v))) {
                                        Ext.Msg.alert("Notification", "Upload a valid image file");
                                        return;
                                        //return 'Upload a valid image file of format JPG.';
                                    }

                                    var giftimg_file = Ext.getCmp('giftimg_file').getValue();
                                    if (giftimg_file == '') {
                                        Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                        return;
                                    }

                                    var gift_image_upload = Ext.getCmp('gift_image_upload').getForm();
                                    if (gift_image_upload.isValid()) {
                                        Ext.getCmp('exist_img_box').hide();
                                        winLoadMask.show();
                                        addPhoto();
                                    }
                                    return true;
                                }
                            }
                        }]
                })]
        });
    };
    function addPhoto() {

        var albumBucketName = Ext.getCmp('albumBucketName').getValue();
        var bucketRegion = Ext.getCmp('bucketRegion').getValue();
        var filepath = Ext.getCmp('oncompleteurl').getValue();
        AWS.config.update({
            region: bucketRegion,
            credentials: new AWS.Credentials(
                    Ext.getCmp('accessKey').getValue(),
                    Ext.getCmp('secretKey').getValue(), null
                    )
        });
        var s3 = new AWS.S3({
            apiVersion: '2006-03-01',
            params: {Bucket: albumBucketName}
        });
        var files = document.getElementById('giftimg_file-file').files;
        if (!files.length) {
            winLoadMask.hide();
            return alert('Please choose a file to upload first.');
        }
        var file = files[0];
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split('.').pop();
        var fileName = uuidv4();
        fileName = fileName + '.' + fileExt;
        Ext.getCmp('file_name').setValue(fileName);
        // var fileName = file.name;
        // var file_Name = JSON.stringify(fileName).slice(1, -1);
        /* var albumPhotosKey = encodeURIComponent(albumName) + '//';*/

        /* var photoKey = fileName;*/

        s3.upload({
            Key: filepath + fileName, /*file_Name*/ /*from server*/
            Body: file,
            ACL: 'public-read'
        }, function (err, data) {

            if (err) {
                winLoadMask.hide();
                var img_src = Ext.BLANK_IMAGE_URL;
                Ext.getCmp('gift_main_image_panel').update({'img_src': img_src});
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {

                winLoadMask.hide();
                Ext.Msg.alert("Notification", 'File uploaded successfully.');
                Application.Finascop_Stockpo.UploadedFileLocation = data.Location;
                Application.Finascop_Stockpo.UploadedFileBucket = data.Bucket;
                Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
                Ext.getCmp('aws_file_location').setValue(data.Location);
                /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
                Ext.getCmp('gift_main_image_panel').update({'img_src': Application.Finascop_Stockpo.UploadedFileLocation});

            }
        });
    }
    ;

    return{
        Cache: {},
        init: function () {
            var _panelId = 'panelidforStockkeeping';
            var _stockRegisterPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_stockRegisterPanel)) {
                _stockRegisterPanel = panelforpoDetails(_panelId);
                Application.UI.addTab(_stockRegisterPanel);
                _stockRegisterPanel.doLayout();
            } else {
                Application.UI.addTab(_stockRegisterPanel);
            }
        }, ViewMode: function () {

            var fpo_id = arguments[0];

            Ext.Ajax.request({
                url: modURL + '&op=stockpoDetailsView',
                method: 'POST',
                params: {fpo_id: fpo_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('stockPoDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('stockPoDetailsViewPanel').doLayout();
            Ext.getCmp('stockPoItemGrid').getStore().load({
                params: {
                    fpo_id: fpo_id
                }
            });
        }, itemViewMode: function () {

            var fpod_id = arguments[0];

            Ext.Ajax.request({
                url: modURL + '&op=itemStockpoDetailsView',
                method: 'POST',
                params: {fpod_id: fpod_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('stockPoItemDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('stockPoItemDetailsViewPanel').doLayout();
        }, addItemtoStock: function () {
            var poStockItemName = Ext.getCmp('poStockItemName').getRawValue();
            var poStockItemId = Ext.getCmp('poStockItemName').getValue();
            var poStockItemBatch = Ext.getCmp('poStockItemBatch').getValue();
            var poStockItemQty = Ext.getCmp('poStockItemQty').getValue();
            var poStockItemExpiryDate = Ext.getCmp('poStockItemExpiryDate').getValue();
            var poStockItemSP = Ext.getCmp('poStockItemSP').getValue();
            var sileast_package_type_id = Ext.getCmp('sileast_package_type_id').getValue();
            var sileast_package_type_name = Ext.getCmp('sileast_package_type_name').getValue();
            var leastSKUCount = Ext.getCmp('leastSKUCount').getValue();
            var stii_enteredpT = Ext.getCmp('poStockItemUnits').getValue();
            if (!Ext.isEmpty(Ext.getCmp('poStockItemName').getValue() && Ext.getCmp('poStockItemExpiryDate').getValue())) {

                Ext.Ajax.request({
                    url: modURL + '&op=addItemtoStock',
                    method: 'POST',
                    params: {
                        poStockItemName: poStockItemName,
                        poStockItemId: poStockItemId,
                        poStockItemBatch: poStockItemBatch,
                        poStockItemQty: poStockItemQty,
                        poStockItemExpiryDate: poStockItemExpiryDate,
                        poStockItemSP: poStockItemSP,
                        fpod_id: Application.Finascop_Stockpo.Cache.aspfpod_id,
                        fpo_id: Application.Finascop_Stockpo.Cache.aspfpo_id,
                        sileast_package_type_id: sileast_package_type_id,
                        sileast_package_type_name: sileast_package_type_name,
                        leastSKUCount: leastSKUCount,
                        stii_enteredpT: stii_enteredpT
                    },
                    success: function (response) {
                        var res = Ext.decode(response.responseText);
                        if (res.success === true) {
                            Application.example.msg('Success', res.msg);
                            Ext.getCmp('poStockEntryGridPanel').getStore().load({
                                params: {
                                    fpod_id: Application.Finascop_Stockpo.Cache.aspfpod_id,
                                    fpo_id: Application.Finascop_Stockpo.Cache.aspfpo_id,
                                    itemId: Application.Finascop_Stockpo.Cache.fpod_itemid
                                }
                            });
                            Ext.getCmp('newstockPoItemGrid').getStore().load({
                                params: {
                                    fpo_id: Application.Finascop_Stockpo.Cache.aspfpo_id
                                }
                            });
                            Ext.getCmp('poItemStockItemFormPanel').getForm().reset();
                        } else if (res.error) {
                            Ext.MessageBox.alert("Error", res.error);
                        } else {
                            Ext.MessageBox.alert("Notification", res.msg, function (btn) {
                                if (btn == 'ok') {
                                    Ext.getCmp('poStockItemQty').focus(false, 100);
                                }
                            });
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert("Notification", 'Please fill all Payment Terms and Date of delivery');
            }
        }, rePrintBarcodeDetails: function () {
            var barcodeId = Ext.getCmp('comboboxBarcode').getValue();
            if (!Ext.isEmpty(Ext.getCmp('comboboxBarcode').getValue())) {
                Ext.Ajax.request({
                    url: modURL + '&op=rePrintBarcodeOp',
                    method: 'POST',
                    params: {
                        barcodeId: barcodeId,
                        stii_id: Application.Finascop_Stockpo.Cache.stii_id,
                    },
                    success: function (response) {
                        var res = Ext.decode(response.responseText);
                        if (res.success === true) {
                            Ext.MessageBox.alert("Success", res.msg);
                        } else if (res.error) {
                            Ext.MessageBox.alert("Error", res.error);
                        } else {
                            Ext.MessageBox.alert("Notification", res.msg);
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert("Notification", 'Please Enter Barcode to reprint');
            }
        }, uploadGiftImage: function (stii_id, img_url) {
            var main_img_panel = giftImageUploadForm(img_url);
            var window_id = "giftImageUploadWindow";
            var giftImageUploadWindow = new Ext.Window({
                id: window_id,
                title: 'Upload Image',
                layout: 'fit',
                width: 500,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                //iconCls: 'finascop_dataentry_receipt',
                resizable: false,
                closable: false,
                items: main_img_panel,
                listeners: {
                    afterrender: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        winLoadMask = new Ext.LoadMask(Ext.getCmp('giftImageUploadWindow').getEl());
                        winLoadMask.msg = 'Please wait...';

                        Ext.getCmp('gift_image_upload').getForm().load({
                            waitTitle: 'Please Wait',
                            waitMsg: 'Loading...',
                            url: modURL + '&op=getGiftImageS3details',
                            params: {
                                rid: stii_id,
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp
                            }
                        });
                    }
                },
                buttons: [{
                        text: 'Upload',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        iconCls: 'finascop_my-icon1',
                        id: 'saveButton',
                        handler: function () {
                            Application.Finascop_Stockpo.Cache.rid = stii_id;
                            var bucket_name = Ext.getCmp('albumBucketName').getValue();
                            var file_name = Ext.getCmp('file_name').getValue();
                            var form_data = {
                                stii_id: stii_id,
                                uploaded_file_name: file_name,
                                bucket: bucket_name,
                                filepath: Ext.getCmp('aws_file_location').getValue(),
                                bucket_path: Ext.getCmp('oncompleteurl').getValue()
                            };
                            var params = {
                                action: 'Add Gift Image',
                                module: 'Master',
                                op: 'saveGiftImage',
                                extrainfo: 'Add Gift Image',
                                id: stii_id
                            };
                            APICall(params, Application.Finascop_Stockpo.saveGiftImage, form_data);

                        }
                    }, {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        iconCls: 'finascop_my-icon1',
                        handler: function () {

                            giftImageUploadWindow.close();
                        }
                    }]
            });
            giftImageUploadWindow.doLayout();
            giftImageUploadWindow.show(this);
            giftImageUploadWindow.center();
        }, saveGiftImage: function () {
            var bucket_name = Ext.getCmp('albumBucketName').getValue();
            var file_name = Ext.getCmp('file_name').getValue();

            if (bucket_name != '' && file_name != '')
            {
                Ext.Ajax.request({
                    url: modURL + '&op=saveGiftImage',
                    method: 'POST',
                    params: {
                        stii_id: Application.Finascop_Stockpo.Cache.rid,
                        uploaded_file_name: file_name,
                        bucket: bucket_name,
                        filepath: Ext.getCmp('aws_file_location').getValue(),
                        bucket_path: Ext.getCmp('oncompleteurl').getValue()
                    },
                    success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                            Application.example.msg('Notification', 'Image saved..');
                            Ext.getCmp('giftImageUploadWindow').close();

                        } else {
                            Ext.Msg.alert('Error', "Image not saved. Try again");
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Notification', result.error);
                        } else {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.MessageBox.alert('Error', result.error);
                        }
                    }
                });
            }
        }, InwardPOItemsOrder: function (poId) {
            var win_canc_id = "newWindowForStockInward";
            var win_canc = Ext.getCmp(win_canc_id);
            if (Ext.isEmpty(win_canc)) {
                win_canc = new Ext.Window({
                    id: win_canc_id,
                    title: 'Order Item Details',
                    layout: 'border',
                    width: winsize.width * 0.85,
                    height: 600,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: [newpoItemGrid(), new Ext.Panel({
                            frame: false,
                            border: true,
                            region: 'east',
                            width: winsize.width * 0.45,
                            autoScroll: true,
                            id: 'verifyPOparentPanel',
                            height: winsize.height * 0.6,
                            layout: 'border',
                            items: [{
                                    region: 'north',
                                    height: 200,
                                    items: poItemDetails()
                                }, {
                                    region: 'center',
                                    layout: 'fit',
                                    height: 100,
                                    items: newpoItemStockform(Application.Finascop_Stockpo.Cache.fpod_id, poId)
                                }, {
                                    region: 'south',
                                    layout: 'fit',
                                    height: 250,
                                    items: poItemStockGrid(Application.Finascop_Stockpo.Cache.fpod_id, poId, Application.Finascop_Stockpo.Cache.fpod_itemid)
                                }
                            ]
                        })],
                    buttonAlign: 'right',
                    fbar: [],
                    buttons: [
                        {
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            text: 'Cancel',
                            handler: function () {
                                Ext.getCmp('newWindowForStockInward').close();
                                Ext.getCmp('stockPoGridPanel').getStore().load({
                                    // store loading is asynchronous, use a load listener or callback to handle results
                                    callback: function () {
                                        Ext.getCmp('stockPoGridPanel').getSelectionModel().selectRow(Application.Finascop_Stockpo.Cache.reselectPoindex);
                                    }
                                });
                            }

                        }
                    ],
                    listeners: {
                        afterrender: function () {
                            Ext.getCmp('newstockPoItemGrid').getStore().load({
                                params: {
                                    fpo_id: poId
                                }
                            });
                        }
                    }


                });
            }
            win_canc.doLayout();
            win_canc.show(this);
            win_canc.center();
        }, enterBarcode: function () {

            var win_canc_id = "newWindowForItemBarcode";
            var win_canc = Ext.getCmp(win_canc_id);
            if (Ext.isEmpty(win_canc)) {
                win_canc = new Ext.Window({
                    id: win_canc_id,
                    title: 'Item Barcode',
                    layout: 'fit',
                    width: 400,
                    height: 100,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: [{
                            xtype: 'textfield',
                            id: 'barcode_inItemInward',
                            name: 'barcode_inItemInward',
                            fieldLabel: 'Barcode',
                            style: {
                                'font-size': '28px'
                            },
                            emptyText: 'Enter Barcode',
                            height: 50,
                            anchor: '98%',
                            tabIndex: 102,
                            listeners: {
                                change: function () {
                                    var barcodesearch_fieldrt = Ext.getCmp('barcode_inItemInward').getValue();
                                    if (barcodesearch_fieldrt) {

                                    }
                                },
                                specialkey: function (field, e) {
                                    if (e.getKey() == e.ENTER) {
                                    }
                                }
                            }
                        }],
                    buttonAlign: 'right',
                    buttons: [
                        {
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            text: 'Cancel',
                            handler: function () {
                                Ext.getCmp('newWindowForItemBarcode').close();
                            }

                        }, {
                            icon: IMAGE_BASE_PATH + '/default/icons/save.png',
                            text: 'Save',
                            handler: function () {
                            }

                        }
                    ],
                    listeners: {
                        afterrender: function () {

                        }
                    }


                });
            }
            win_canc.doLayout();
            win_canc.show(this);
            win_canc.center();

        }
    }
}();
