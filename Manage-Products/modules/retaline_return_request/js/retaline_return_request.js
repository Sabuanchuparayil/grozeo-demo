Application.ReturnRequest = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var transferRulesLoadedForm = null;
    var modURL = '?module=retaline_return_request';
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

    var ordergridSelectionChanged = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('manage_order_returnreq_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_returnreq_grid').getSelectionModel().getSelections()[0].data.order_id;
            var rtrqo_sourceType = Ext.getCmp('manage_order_returnreq_grid').getSelectionModel().getSelections()[0].data.rtrqo_sourceType;
            Application.ReturnRequest.Cache.orderAutoId = ID;
            Application.ReturnRequest.Cache.rtrqo_sourceType = rtrqo_sourceType;
            Application.ReturnRequest.UpdateViewMode(ID, rtrqo_sourceType);
        }
    };
    var orderTopPanel = function (id) {

        var src = '?module=retaline_return_request';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Sales Return',
            iconCls: 'overseas_report',
            items: [manageOrderGrid(),
                new Ext.Panel({
                    region: 'east',
                    frame: false,
                    border: true,
                    layout: 'fit',
                    autoScroll: false,
                    title: 'Order Details',
                    bodyStyle: {"background-color": "white"},
                    id: 'order_returnreq_manage_parent_panel',
                    width: winsize.width * 0.45,
                    items: [{
                            id: 'order_returnreq_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_returnreq_orderdtls" name="iframe_returnreq_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };
//
//
    var manageOrderStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getReturnedOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'rtrqo_id',
                root: 'data'
            }, ['rtrqo_id', 'order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on',
                'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes',
                'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'order_itemReturnRequestCount', 'order_qty', 'cust_mobile', 'rtrqo_sourceType'
            ]),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            //    remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    var branchName = Ext.getCmp('comboxbranchOrderReturnReq').getRawValue();
                    if (branchName != '') {
                        this.baseParams.br_Name = branchName;
                    }
                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_returnreq_grid').getView().refresh();
                    Ext.getCmp('manage_order_returnreq_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_returnreq_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_returnreq_grid').getSelectionModel().getSelections()[0].data.order_id;
                        var rtrqo_sourceType = Ext.getCmp('manage_order_returnreq_grid').getSelectionModel().getSelections()[0].data.rtrqo_sourceType;
                        Application.ReturnRequest.Cache.orderAutoId = ID;
                        Application.ReturnRequest.Cache.rtrqo_sourceType = rtrqo_sourceType;
                        Application.ReturnRequest.UpdateViewMode(Application.ReturnRequest.Cache.orderAutoId, Application.ReturnRequest.Cache.rtrqo_sourceType);
                    }
                }
            }
        });
        return store;
    };

    var manageOrderGrid = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: brmodURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

//        var check_box = new Ext.grid.CheckboxSelectionModel({
//            multiSelect: true
//        });

        var hfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'order_generated_id'
                }, {
                    type: 'string',
                    dataIndex: 'order_order_id'
                }, {
                    type: 'date',
                    dataIndex: 'order_created_on'
                }, {
                    type: 'string',
                    dataIndex: 'delivery_to'
                }, {
                    type: 'string',
                    dataIndex: 'district'
                }, {
                    type: 'string',
                    dataIndex: 'pin'
                }, {
                    type: 'string',
                    dataIndex: 'cust_mobile'
                }, {
                    type: 'list',
                    options: ['Customer', 'BA'],
                    phpMode: true,
                    dataIndex: 'order_user_type'
                }, {
                    type: 'numeric',
                    dataIndex: 'order_total_amount'
                }, {
                    type: 'string',
                    dataIndex: 'order_status'
                }, {
                    type: 'string',
                    dataIndex: 'br_Name'
                }]
        });
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = manageOrderStore();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            region: 'center',
            frame: true,
            layout: 'fit',
            width: winsize.width * 0.6,
            border: false, //order_ReturnVerified
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
                getRowClass: function (record, index, params, store) {
                    if ((record.get('order_ReturnVerified') == 0) && (record.get('order_HasReturn') == 1))
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                }
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters],
            id: 'manage_order_returnreq_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 200,
                    hideable: false,
                    hidden: true,
                    renderer: qtipRenderer
                }, {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: qtipRenderer
                }, {
                    header: 'Time',
                    sortable: true,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                    renderer: qtipRenderer
                },
                {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Customer',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Mobile',
                    dataIndex: 'cust_mobile',
                    sortable: true,
                    tooltip: 'Mobile',
                    hideable: false
                }, {
                    header: 'Total Qty',
                    dataIndex: 'order_qty',
                    sortable: true,
                    tooltip: 'Total Qty',
                    hideable: false
                }, {
                    header: 'Return Qty',
                    dataIndex: 'order_itemReturnRequestCount',
                    sortable: true,
                    tooltip: 'Return Qty',
                    hideable: false
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                    renderer: qtipRenderer
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: ordergridSelectionChanged
                }
            }), tbar: [{
                    text: 'Create Sales Return',
                    tooltip: 'Create Sales Return',
                    icon: './resources/images/submenuicons/add.png',
//                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        Application.ReturnRequest.addNewOrderToRequestReturn();

                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdOrderReturnReq',
                    name: 'textboxBrmCpdOrderReturnReq',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchOrderReturnReq',
                    name: 'comboxbranchOrderReturnReq',
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
                    hiddenName: 'comboxbranchOrderReturnReq',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchOrderReturnReq').getRawValue();
                            Ext.getCmp('manage_order_returnreq_grid').getStore().load({
                                params: {
                                    br_Name: branchName
                                }
                            });
                        }
                    }
                }],
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchOrderReturnReq").show();
                        Ext.getCmp("textboxBrmCpdOrderReturnReq").hide();
                    } else {
                        Ext.getCmp("textboxBrmCpdOrderReturnReq").show();
                        Ext.getCmp("textboxBrmCpdOrderReturnReq").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchOrderReturnReq").hide();
                    }
                    if (Ext.getCmp('textboxBrmCpdOrderReturnReq').getValue() != '')
                    {
                        order_store.baseParams = {
                            current_branch_id: _SESSION.finascop_current_branch_id
                        }
                        order_store.load();
                    }
                }
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: order_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true
        });
        return grid;
    };
    var order_no = new Array();
    var cust_id = new Array();
    var ck_selection = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true,
        checkOnly: true,
        listeners: {
            rowdeselect: function (sm, rowIndex, record) {
                var ind = order_no.indexOf(record.get('order_id'));
                //if (ind > -1)
                //  rtrqod_itemId.splice(ind, 1);
                record.set('checked', 'false');
            },
            rowselect: function (sm, rowIndex, record) {
                console.log(record);
                var ind = order_no.indexOf(record.get('order_id'));
                //if (ind == -1)
                //rtrqod_itemId.push(record.get('rtrqod_itemId'));
                record.set('checked', 'true');
            }
        }
    });
    var _Storetr = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getOrdersDetails',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_mrp', 'order_packedbags_count', 'cust_mobile', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on',
                'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status',
                'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'updated_at'
            ]),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            //    remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function () {

                    this.baseParams.customer_mobileretreq = "";

                }
            }
        });
        return store;
    };
    var OrderDetailsGrid = function (fstr_id) {
        var branchD_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'order_order_id'
                }, {
                    type: 'string',
                    dataIndex: 'delivery_to'
                },
                {
                    type: 'string',
                    dataIndex: 'cust_mobile'
                },
                {
                    type: 'string',
                    dataIndex: 'ordertime'
                },
                {
                    type: 'string',
                    dataIndex: 'order_mrp'
                },
                {
                    type: 'string',
                    dataIndex: 'order_status'
                },
                {
                    type: 'string',
                    dataIndex: 'reason'
                }, ]
        });
        branchD_filter.remote = true;
        branchD_filter.autoReload = true;
        var _OrdergridPanel = new Ext.grid.EditorGridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _Storetr(),
            autoScroll: true,
            width: winsize.width * 0.9,
            plugins: [branchD_filter],
            height: 450,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelOrderReturnReq',
            columns: [{
                    header: 'Order No:',
                    dataIndex: 'order_order_id',
                    sortable: true,
                    tooltip: 'Order No',
                    hideable: true
                },
                {
                    header: 'Customer Name',
                    dataIndex: 'delivery_to',
                    sortable: true,
                    tooltip: 'Customer Name',
                    hideable: true
                },
                {
                    header: 'Mobile',
                    dataIndex: 'cust_mobile',
                    sortable: true,
                    tooltip: 'Mobile',
                    hideable: false
                },
                {
                    header: 'Date & Time',
                    dataIndex: 'ordertime',
                    sortable: true,
                    tooltip: 'Date & Time',
                    hideable: false
                },
                {
                    header: 'Order Value',
                    dataIndex: 'order_mrp',
                    sortable: true,
                    tooltip: 'Order Value',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'order_status',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false
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
                            returnOrderMenu(e);
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
                            iconCls: 'cancel_order',
                            tooltip: 'Return Order',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
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
                                        Application.ReturnRequest.Cache.uuid = uid;
                                        Application.ReturnRequest.returnOrder(record.get('order_id'), record.get('order_customer_id'), record.get('updated_at'), record.get('status_id'), Application.ReturnRequest.Cache.ordertype);
                                    }
                                });

                            }
                        }
                    ]
                }*/
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                
            })
        });
        return _OrdergridPanel;
    };
    var returnOrderMenu = function (e) {
    var returnOrderMenu = new Ext.menu.Menu({
        items: [{
                text: "Return Order",
                handler: function () {
                    //var record = Ext.getCmp('gridpanelOrderReturnReq').getSelectionModel().getSelections()[0].data;
                    var order_id = Ext.getCmp('gridpanelOrderReturnReq').getSelectionModel().getSelections()[0].data.order_id;
                    var order_customer_id = Ext.getCmp('gridpanelOrderReturnReq').getSelectionModel().getSelections()[0].data.order_customer_id;
                    var updated_at = Ext.getCmp('gridpanelOrderReturnReq').getSelectionModel().getSelections()[0].data.updated_at;
                    var status_id = Ext.getCmp('gridpanelOrderReturnReq').getSelectionModel().getSelections()[0].data.status_id;
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
                            Application.ReturnRequest.Cache.uuid = uid;
                            Application.ReturnRequest.returnOrder(order_id, order_customer_id, updated_at, status_id, Application.ReturnRequest.Cache.ordertype);
                        }
                    });
                }
            }]
    });
    returnOrderMenu.showAt(e.getXY());
    };
    var orderGridFn = function (order_id, ordertype) {
        var _retReqgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _orderItemStore(order_id, ordertype),
            iconCls: 'money',
            autoScroll: true,
            width: 400,
            height: 350,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforReturnRequstItems',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item Name',
                    dataIndex: 'itemName',
                    sortable: true,
                    tooltip: 'Item Name',
                    hideable: false,
                    width: 200
                },
                {
                    header: 'MRP',
                    dataIndex: 'item_retail_price',
                    sortable: true,
                    align: 'right',
                    tooltip: 'MRP',
                    hideable: false
                },
                {
                    header: 'Rate',
                    dataIndex: 'item_sales_price',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Rate',
                    hideable: false
                }, {
                    header: 'Quantity',
                    dataIndex: 'item_order_qty',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Quantity',
                    hideable: false
                }, {
                    header: 'Returned Qty',
                    dataIndex: 'item_return_qty_requested',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Returned Quantity',
                    hideable: false
                }, {
                    header: 'Sellable',
                    dataIndex: 'item_return_sellableqty',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Sellable',
                    hideable: false
                }, {
                    header: 'Damaged',
                    dataIndex: 'item_return_damagedqty',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Damaged',
                    hideable: false
                }, {
                    header: 'To Return',
                    dataIndex: 'item_return_qty',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Returned',
                    hideable: false
                }, {
                    header: 'Amount',
                    dataIndex: 'item_amount',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Amount',
                    hideable: false
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            getClass: function (v, meta, rec) {
                                if ((_SESSION.IS_RETALINE_LITE != 1) && (rec.get('isReturn') == 1)) {
                                    this.items[0].tooltip = 'View Barcode';
                                    return 'barcode_view';
                                } else {
                                    return 'hideicon';
                                }
                            },
                            //id: 'view_barcode',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                var order_id = Ext.getCmp('gridpanelforReturnRequstItems').getSelectionModel().getSelections()[0].data.order_id;
                                var itemId = Ext.getCmp('gridpanelforReturnRequstItems').getSelectionModel().getSelections()[0].data.itemId;
                                Application.ReturnRequest.viewBarcodeWindow(order_id, itemId);
                            }
                        }, {
                            //id: 'marked_packed',
                            getClass: function (v, meta, rec) {
                                console.log('item_order_qty', rec.get('item_order_qty'));
                                console.log('fsto_ItemQty', rec.get('fsto_ItemQty'))
                                if ((_SESSION.IS_RETALINE_LITE == '1') && (rec.get('isReturn') == 1) && (rec.get('item_return_qty') < rec.get('item_order_qty')) && (rec.get('item_return_qty_requested') < rec.get('item_order_qty'))) {
                                    this.items[1].tooltip = 'Mark As Returned';
                                    return 'packing-add';
                                } else {
                                    return 'hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                var order_id = record.data.order_id;
                                var itemId = record.data.itemId;
                                var item_order_qty = record.data.item_order_qty;
                                var itemName = record.data.itemName;
                                upReturnQty(order_id, itemId, item_order_qty, record, itemName);

                            }
                        }
                    ]

                }
            ],
            viewConfig: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }), view: new Ext.grid.GroupingView({
                forceFit: true,
                showGroupName: false,
                enableNoGroups: false,
                enableGroupingMenu: false,
                hideGroupedColumn: false,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    switch (record.get('isReturn')) {
                        case 0:
                            return 'finascop_indicateColPINK';
                            break;
                        case 1:
                            return 'finascop_indicateColPALEGREEN';
                            break;
                    }

                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            }), sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            tbar: [{
                    html: '&nbsp;Enter/Scan Barcode : &nbsp;',
                    id: 'barcodeBr_label',
                }, {
                    xtype: 'textfield',
                    id: 'barcode_idret',
                    name: 'barcode_idret',
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
                            var barcodesearch_fieldrt = Ext.getCmp('barcode_idret').getValue();
                            if (barcodesearch_fieldrt) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=returnbarcodeCheck',
                                    method: 'POST',
                                    params: {
                                        barcodesearch_fieldrt: barcodesearch_fieldrt,
                                        order_id: order_id,
                                        ordertype: ordertype
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        var item_id = tmp.item_id;
                                        if (tmp.success === true) {

                                            var rowIndex = Ext.getCmp('gridpanelforReturnRequstItems').store.find('itemId', item_id);
                                            var grecord = Ext.getCmp('gridpanelforReturnRequstItems').store.getAt(rowIndex);
                                            var item_return_qty_requested = grecord.data.item_return_qty_requested;
                                            console.log('item_return_qty_requested', item_return_qty_requested);
                                            var item_order_qty = grecord.data.item_order_qty;
                                            if (item_order_qty > 0) {
                                                item_order_qty = item_order_qty;
                                            } else {
                                                item_order_qty = 0;
                                            }
                                            console.log('item_order_qty', item_order_qty);
                                            var item_return_qty = grecord.data.item_return_qty;
                                            console.log('item_return_qty', item_return_qty);
                                            var totalReturn = parseInt(item_return_qty) + parseInt(item_return_qty_requested);
                                            if (totalReturn > 0) {
                                                totalReturn = totalReturn;
                                            } else {
                                                totalReturn = 0;
                                            }
                                            console.log('totalReturn', totalReturn);
                                            var itemName = grecord.data.itemName;


                                            if (item_order_qty > totalReturn) {
                                                Ext.getCmp('correct_id').show();
                                                Ext.getCmp('wrong_id').hide();
                                                Ext.getCmp('barcode_idret').reset();
                                                setTimeout(function () {
                                                    Ext.getCmp('correct_id').hide();
                                                    if (tmp.banned === true) {
                                                        var returned_qty = 1;
                                                        var returnableQty = parseInt(item_order_qty) - parseInt(grecord.data.item_return_qty_requested);
                                                        var pkdQuantity = parseInt(returned_qty);
                                                        var itemReturn_type1 = false;//Sellable
                                                        var itemReturn_type2 = true;//Damaged
                                                        var itemReturn_type3 = false;//Damaged In Transit
                                                        var itemReturn_type1qty, itemReturn_type2qty, itemReturn_type3qty;
                                                        if (itemReturn_type1 == true) {
                                                            itemReturn_type1qty = 1;
                                                        } else {
                                                            itemReturn_type1qty = 0;
                                                        }
                                                        if (itemReturn_type2 == true) {
                                                            itemReturn_type2qty = 1;
                                                        } else {
                                                            itemReturn_type2qty = 0;
                                                        }
                                                        if (itemReturn_type3 == true) {
                                                            itemReturn_type3qty = 1;
                                                        } else {
                                                            itemReturn_type3qty = 0;
                                                        }

                                                        console.log('returnableQty', returnableQty);
                                                        console.log('pkdQuantity', pkdQuantity);
                                                        if (pkdQuantity <= (parseInt(returnableQty))) {

                                                            Ext.Ajax.request({
                                                                url: modURL + '&op=saveItemReturninOrder',
                                                                method: 'POST',
                                                                params: {
                                                                    uuid: Application.ReturnRequest.Cache.uuid,
                                                                    itemReturn_type1qty: itemReturn_type1qty,
                                                                    itemReturn_type2qty: itemReturn_type2qty,
                                                                    itemReturn_type3qty: itemReturn_type3qty,
                                                                    returnableQty: returnableQty,
                                                                    totalReturn: 1,
                                                                    returned_qty: pkdQuantity,
                                                                    order_id: order_id,
                                                                    itemId: item_id,
                                                                    item_order_qty: item_order_qty,
                                                                    barcodesearch_fieldrt: barcodesearch_fieldrt,
                                                                    ordertype: ordertype
                                                                },
                                                                success: function (response) {
                                                                    var tmp = Ext.decode(response.responseText);
                                                                    console.log('tmp', tmp);
                                                                    if (tmp.success === true && tmp.valid === true) {

                                                                        Application.example.msg('Success', tmp.msg);
                                                                        Ext.getCmp('gridpanelforReturnRequstItems').getStore().load({
                                                                            params: {
                                                                                order_id: order_id
                                                                            }
                                                                        });
                                                                    } else if (tmp.success === true && tmp.valid === false) {
                                                                        Ext.Msg.alert("Notification.", tmp.msg);
                                                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                                                        Ext.Msg.alert("Notification.", tmp.msg);
                                                                    } else {
                                                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                                                    }
                                                                },
                                                                failure: function (response) {
                                                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                                                    Ext.MessageBox.alert('Error', tmp.message);
                                                                }
                                                            });
                                                            var new_fsto_pkdQty = parseInt(grecord.data.item_return_qty) + 1;
                                                            grecord.set('item_return_qty', new_fsto_pkdQty);
                                                            if (itemReturn_type1 == true) {
                                                                if (parseInt(grecord.data.item_return_sellableqty) > 0) {
                                                                    var sellableqty = grecord.data.item_return_sellableqty;
                                                                } else {
                                                                    var sellableqty = 0;
                                                                }
                                                                var newitem_return_sellableqty = parseInt(sellableqty) + 1;
                                                                grecord.set('item_return_sellableqty', newitem_return_sellableqty);
                                                            } else {
                                                                if (parseInt(grecord.data.item_return_damagedqty) > 0) {
                                                                    var damagedqty = grecord.data.item_return_damagedqty;
                                                                } else {
                                                                    var damagedqty = 0;
                                                                }
                                                                var newitem_return_damagedqty = parseInt(damagedqty) + 1;
                                                                grecord.set('item_return_damagedqty', newitem_return_damagedqty);
                                                            }
                                                            Ext.getCmp('barcode_idret').focus();
                                                        } else {
                                                            Ext.Msg.alert('Notification', 'Quantity Mismatch.');
                                                            grecord.set('item_return_qty', '');
                                                        }
                                                    } else {
                                                        upReturnType(order_id, item_id, item_order_qty, grecord, itemName, barcodesearch_fieldrt, ordertype);
                                                    }

                                                }, 1000);

                                            } else {
                                                Ext.MessageBox.alert('Notification', 'Item Quantity limit exceeded');
                                                Ext.getCmp('wrong_id').show();
                                                Ext.getCmp('correct_id').hide();
                                                Ext.getCmp('barcode_idret').reset();
                                                setTimeout(function () {
                                                    Ext.getCmp('wrong_id').hide();
                                                }, 5000);
                                            }
                                        } else {
//                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                            Ext.getCmp('wrong_id').show();
                                            Ext.getCmp('correct_id').hide();
                                            Ext.getCmp('barcode_idret').reset();
                                            setTimeout(function () {
                                                Ext.getCmp('wrong_id').hide();
                                            }, 5000);
                                            Ext.getCmp('barcode_idret').focus();
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        Ext.getCmp('barcode_idret').focus();
                                    }
                                });
                            }
                        },
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                var barcodesearch_fieldrt = Ext.getCmp('barcode_idret').getValue();
                                if (barcodesearch_fieldrt) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=returnbarcodeCheck',
                                        method: 'POST',
                                        params: {
                                            barcodesearch_fieldrt: barcodesearch_fieldrt,
                                            order_id: order_id
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                var tmp = Ext.decode(response.responseText);
                                                var item_id = tmp.item_id;
                                                var rowIndex = Ext.getCmp('gridpanelforReturnRequstItems').store.find('itemId', item_id);
                                                var grecord = Ext.getCmp('gridpanelforReturnRequstItems').store.getAt(rowIndex);
                                                var item_return_qty_requested = grecord.data.item_return_qty_requested;
                                                console.log('item_return_qty_requested', item_return_qty_requested);
                                                var item_order_qty = grecord.data.item_order_qty;
                                                if (item_order_qty > 0) {
                                                    item_order_qty = item_order_qty;
                                                } else {
                                                    item_order_qty = 0;
                                                }
                                                console.log('item_order_qty', item_order_qty);
                                                var item_return_qty = grecord.data.item_return_qty;
                                                console.log('item_return_qty', item_return_qty);
                                                var totalReturn = parseInt(item_return_qty) + parseInt(item_return_qty_requested);
                                                if (totalReturn > 0) {
                                                    totalReturn = totalReturn;
                                                } else {
                                                    totalReturn = 0;
                                                }
                                                console.log('totalReturn', totalReturn);
                                                var itemName = grecord.data.itemName;


                                                if (item_order_qty > totalReturn) {
                                                    Ext.getCmp('correct_id').show();
                                                    Ext.getCmp('wrong_id').hide();
                                                    Ext.getCmp('barcode_idret').reset();
                                                    setTimeout(function () {
                                                        Ext.getCmp('correct_id').hide();
                                                        upReturnType(order_id, item_id, item_order_qty, grecord, itemName, barcodesearch_fieldrt, ordertype);
                                                    }, 1000);

                                                } else {
                                                    Ext.MessageBox.alert('Notification', 'Item Quantity limit exceeded');
                                                    Ext.getCmp('wrong_id').show();
                                                    Ext.getCmp('correct_id').hide();
                                                    Ext.getCmp('barcode_idret').reset();
                                                    setTimeout(function () {
                                                        Ext.getCmp('wrong_id').hide();
                                                    }, 5000);
                                                }


                                            } else {
//                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                                Ext.getCmp('wrong_id').show();
                                                Ext.getCmp('correct_id').hide();
                                                Ext.getCmp('barcode_idret').reset();
                                                setTimeout(function () {
                                                    Ext.getCmp('wrong_id').hide();
                                                }, 5000);
                                                Ext.getCmp('barcode_idret').focus();
                                            }
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            Ext.getCmp('barcode_idret').focus();
                                        }
                                    });
                                }
                            }
                        }
                    }
                },
                {
                    iconCls: 'correct',
                    id: 'correct_id',
                    tabIndex: 511,
                    hidden: true,
                    listeners: {
                        change: function () {
                            setTimeout(function () {
                                onGridResize(true);
                            }, 10);
                        }
                    }
                },
                {
                    iconCls: 'wrong',
                    id: 'wrong_id',
                    tabIndex: 511,
                    hidden: true,
                    listeners: {
                        change: function () {
                            setTimeout(function () {
                                onGridResize(true);
                            }, 10);
                        }
                    }
                }
            ],
            listeners: {
                afterrender: function () {
                    if (_SESSION.IS_RETALINE_LITE == '1') {
                        Ext.getCmp('barcode_idret').disable();
                    }
                    Ext.getCmp('gridpanelforReturnRequstItems').getStore().load({
                        params: {
                            order_id: order_id
                        }
                    });
                }
            }
        });
        return _retReqgridPanel;
    };
    var _orderItemStore = function (order_id, order_order_id) {

        var _Store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listItemsinCustomerOrder',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['order_id', 'stiid_barcode', 'itemId', 'itemName', 'stiid_id', 'item_sales_price', 'item_return', 'isReturn', 'item_retail_price', 'item_order_qty', 'item_amount', 'item_return_qty_requested',
                'item_return_qty', 'item_returnDate', 'item_deliveredDate', 'item_return_damagedqty', 'item_return_sellableqty']),
            sortInfo: {
                field: 'stiid_barcode',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: false,
            root: 'data',
            remoteSort: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.order_id = order_id;
                    this.baseParams.order_order_id = order_order_id;
                }
            }
        });

        return _Store;
    };
    var upReturnQty = function (order_id, itemId, item_order_qty, record, itemName) {

        if (Ext.isEmpty(upReturnQty_window)) {
            var upReturnQty_window = new Ext.Window({
                id: 'upReturnQty_window',
                layout: 'fit',
                width: 400,
                title: 'Accept Sales Return',
                //autoHeight: true,
                iconCls: 'my-icon98',
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [new Ext.FormPanel({
                        frame: true,
                        monitorValid: true,
                        id: 'upReturnQty_form',
                        height: 150,
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                labelAlign: 'left',
                                columnWidth: 1,
                                items: [{
                                        fieldLabel: '',
                                        xtype: 'displayfield',
                                        minValue: 0,
                                        id: 'returned_Item',
                                        name: 'returned_Item',
                                        tabIndex: 93,
                                        value: itemName,
                                        allowBlank: false
                                    }]
                            }, {
                                layout: 'form',
                                labelAlign: 'left',
                                columnWidth: 0.5,
                                items: [{
                                        fieldLabel: 'Sold Quantity',
                                        xtype: 'displayfield',
                                        id: 'sold_qty',
                                        name: 'sold_qty',
                                        allowNegative: false,
                                        allowDecimals: false,
                                        tabIndex: 93,
                                        value: item_order_qty,
                                        allowBlank: false
                                    }]
                            }, {
                                layout: 'form',
                                labelAlign: 'left',
                                columnWidth: 0.5,
                                items: [{
                                        fieldLabel: 'Return Requested',
                                        xtype: 'numberfield',
                                        minValue: 0,
                                        id: 'returned_qty',
                                        name: 'returned_qty',
                                        allowNegative: false,
                                        allowDecimals: false,
                                        tabIndex: 93,
                                        width: 80,
                                        value: 0,
                                        allowBlank: false
                                    }]
                            }, {
                                layout: 'form',
                                labelAlign: 'left',
                                columnWidth: 1,
                                labelWidth: 120,
                                items: [{
                                        fieldLabel: 'Sellable In Store',
                                        xtype: 'numberfield',
                                        id: 'itemReturn_type1qty',
                                        name: 'itemReturn_type1qty',
                                        allowBlank: false,
                                        value: 0,
                                        tabIndex: 94,
                                    }, {
                                        fieldLabel: 'Purchase Returnable',
                                        xtype: 'numberfield',
                                        id: 'itemReturn_type2qty',
                                        name: 'itemReturn_type2qty',
                                        allowBlank: false,
                                        tabIndex: 95,
                                        value: 0,
                                    }, {
                                        fieldLabel: 'Damaged in Transit',
                                        xtype: 'numberfield',
                                        id: 'itemReturn_type3qty',
                                        name: 'itemReturn_type3qty',
                                        allowBlank: false,
                                        tabIndex: 96,
                                        value: 0,
                                    }, {
                                        hideLabel: true,
                                        xtype: 'radiogroup',
                                        id: 'radiogroupItemReturnType',
                                        items: [
                                            {boxLabel: 'Sellable', name: 'itemReturn_type', inputValue: 1, id: 'itemReturn_type1', checked: true, hidden: true,
                                                listeners: {
                                                    'check': function (checkbox, checked) {
                                                        if (checked) {
                                                        }
                                                    }
                                                }
                                            },
                                            {
                                                boxLabel: 'Damaged', name: 'itemReturn_type', inputValue: 2, id: 'itemReturn_type2', hidden: true,
                                                listeners: {
                                                    'check': function (checkbox, checked) {
                                                        if (checked) {
                                                        }
                                                    }
                                                }
                                            }
                                        ]
                                    }]
                            }
                        ]
                    })],
                buttons: [{
                        text: 'Cancel',
                        id: 'btnCancel',
                        tabIndex: 97,
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            upReturnQty_window.close();
                        }
                    }, {
                        text: 'Submit',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        tabIndex: 96,
                        handler: function () {
                            console.log('record.data.item_return_qty_requested', record.data.item_return_qty_requested);
                            var returned_qty = Ext.getCmp('returned_qty').getValue();
                            var itemReturn_type1 = Ext.getCmp('itemReturn_type1').getValue();
                            var itemReturn_type2 = Ext.getCmp('itemReturn_type2').getValue();
                            var itemReturn_type1qty = Ext.getCmp('itemReturn_type1qty').getValue();
                            var itemReturn_type2qty = Ext.getCmp('itemReturn_type2qty').getValue();
                            var itemReturn_type3qty = Ext.getCmp('itemReturn_type3qty').getValue();
                            var returnableQty = parseInt(item_order_qty) - parseInt(record.data.item_return_qty_requested);
                            var totalReturn = parseInt(itemReturn_type1qty) + parseInt(itemReturn_type2qty) + parseInt(itemReturn_type3qty);
                            var pkdQuantity = parseInt(returned_qty);
                            if (pkdQuantity <= (parseInt(returnableQty)) && (pkdQuantity == totalReturn)) {
                                //saveItemReturninOrder

                                Ext.Ajax.request({
                                    url: modURL + '&op=saveItemReturninOrder',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.ReturnRequest.Cache.uuid,
                                        itemReturn_type1qty: itemReturn_type1qty,
                                        itemReturn_type2qty: itemReturn_type2qty,
                                        itemReturn_type3qty: itemReturn_type3qty,
                                        returnableQty: returnableQty,
                                        totalReturn: totalReturn,
                                        returned_qty: pkdQuantity,
                                        order_id: order_id,
                                        itemId: itemId,
                                        item_order_qty: item_order_qty,
                                        ordertype: ordertype
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        console.log('tmp', tmp);
                                        if (tmp.success === true && tmp.valid === true) {

                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('gridpanelforReturnRequstItems').getStore().load({
                                                params: {
                                                    order_id: order_id
                                                }
                                            });
                                            upReturnQty_window.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                        } else if (tmp.success === true && tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                        } else {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.message);
                                    }
                                });
//                                record.set('item_return_qty', pkdQuantity);
//
//                                //if (itemReturn_type1 == true) {
//                                if (parseInt(record.data.item_return_sellableqty) > 0) {
//                                    var sellableqty = record.data.item_return_sellableqty;
//                                } else {
//                                    var sellableqty = 0;
//                                }
//                                var newitem_return_sellableqty = parseInt(sellableqty) + parseInt(itemReturn_type1qty);
//                                record.set('item_return_sellableqty', newitem_return_sellableqty);
//                                // } else {
//                                if (parseInt(record.data.item_return_damagedqty) > 0) {
//                                    var damagedqty = record.data.item_return_damagedqty;
//                                } else {
//                                    var damagedqty = 0;
//                                }
//                                var newitem_return_damagedqty = parseInt(damagedqty) + parseInt(itemReturn_type2qty);
//                                record.set('item_return_damagedqty', newitem_return_damagedqty);
//                                //}
//                                upReturnQty_window.close();

                            } else {
                                Ext.Msg.alert('Notification', 'Quantity Mismatch.');
                                record.set('item_return_qty', '');
                            }

                        }
                    }]
            });

        }
        upReturnQty_window.doLayout();
        upReturnQty_window.show(this);
        upReturnQty_window.center();
    };
    var getMigratedData = function (gridid) {
        var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), 'data');
        return Ext.encode(j);
    };
    var returnOrderDetails = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: ' ',
            height: 150,
            autoScroll: true,
            id: 'returnOrderDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer"  colspan=3>',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="20%">Order No.</th><td> {order_order_id} </td><th width="20%">Date</th><td> {orderdate}</td><th width="20%">Time</th><td> {ordertime}</td></tr>',
                    '<tr><th width="20%">Delivery No </th><td>{fpod_totalqty}</td><th width="20%">Date</th><td> {deliveryDate}</td></tr>',
                    '<tr><th width="20%">Name </th><td> {cust_customer_name} </td><th width="20%">Contact Us </th><td> {cust_mobile} </td></tr>',
                    '<tr><th width="20%">Total Orders </th><td> {fpod_itemoffrrate} </td><th width="20%">Previous Return </th><td> {fpod_itemoffrrate} </td><th width="20%">Member Since </th><td> {fpod_itemoffrrate} </td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
    var upReturnType = function (order_id, itemId, item_order_qty, record, itemName, barcodesearch_fieldrt, ordertype) {
        if (Ext.isEmpty(upReturnQty_window)) {
            var upReturnQty_window = new Ext.Window({
                id: 'upReturnQty_window',
                layout: 'fit',
                width: 400,
                title: 'Accept Sales Return',
                autoHeight: true,
                draggable: false,
                iconCls: 'my-icon98',
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [new Ext.FormPanel({
                        frame: true,
                        monitorValid: true,
                        id: 'upReturnQtyType_form',
                        height: 100,
                        items: [{
                                fieldLabel: 'Return Quantity',
                                xtype: 'numberfield',
                                minValue: 0,
                                id: 'returned_qty',
                                name: 'returned_qty',
                                allowNegative: false,
                                allowDecimals: false,
                                tabIndex: 93,
                                value: 1,
                                allowBlank: false
                            }, {
                                hideLabel: true,
                                xtype: 'radiogroup',
                                id: 'radiogroupItemReturnType',
                                items: [
                                    {boxLabel: 'Sellable', name: 'itemReturn_type', inputValue: 1, id: 'itemReturn_type1', checked: true,
                                        listeners: {
                                            'check': function (checkbox, checked) {
                                                if (checked) {
                                                }
                                            }
                                        }
                                    },
                                    {
                                        boxLabel: 'Damaged', name: 'itemReturn_type', inputValue: 2, id: 'itemReturn_type2',
                                        listeners: {
                                            'check': function (checkbox, checked) {
                                                if (checked) {
                                                }
                                            }
                                        }
                                    },
                                    {
                                        boxLabel: 'Damaged In Transit', name: 'itemReturn_type', inputValue: 3, id: 'itemReturn_type3',
                                        listeners: {
                                            'check': function (checkbox, checked) {
                                                if (checked) {
                                                }
                                            }
                                        }
                                    }
                                ]
                            }]
                    })],
                buttons: [{
                        text: 'Update',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            console.log('record.data.item_return_qty_requested', record.data.item_return_qty_requested);
                            var returned_qty = Ext.getCmp('returned_qty').getValue();
                            var returnableQty = parseInt(item_order_qty) - parseInt(record.data.item_return_qty_requested);
                            var pkdQuantity = parseInt(returned_qty);
                            var itemReturn_type1 = Ext.getCmp('itemReturn_type1').getValue();//Sellable
                            var itemReturn_type2 = Ext.getCmp('itemReturn_type2').getValue();//Damaged
                            var itemReturn_type3 = Ext.getCmp('itemReturn_type3').getValue();//Damaged In Transit
                            var itemReturn_type1qty, itemReturn_type2qty, itemReturn_type3qty;
                            if (itemReturn_type1 == true) {
                                itemReturn_type1qty = 1;
                            } else {
                                itemReturn_type1qty = 0;
                            }
                            if (itemReturn_type2 == true) {
                                itemReturn_type2qty = 1;
                            } else {
                                itemReturn_type2qty = 0;
                            }
                            if (itemReturn_type3 == true) {
                                itemReturn_type3qty = 1;
                            } else {
                                itemReturn_type3qty = 0;
                            }

                            console.log('returnableQty', returnableQty);
                            console.log('pkdQuantity', pkdQuantity);
                            if (pkdQuantity <= (parseInt(returnableQty))) {

                                Ext.Ajax.request({
                                    url: modURL + '&op=saveItemReturninOrder',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.ReturnRequest.Cache.uuid,
                                        itemReturn_type1qty: itemReturn_type1qty,
                                        itemReturn_type2qty: itemReturn_type2qty,
                                        itemReturn_type3qty: itemReturn_type3qty,
                                        returnableQty: returnableQty,
                                        totalReturn: 1,
                                        returned_qty: pkdQuantity,
                                        order_id: order_id,
                                        itemId: itemId,
                                        item_order_qty: item_order_qty,
                                        barcodesearch_fieldrt: barcodesearch_fieldrt,
                                        ordertype: ordertype
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        console.log('tmp', tmp);
                                        if (tmp.success === true && tmp.valid === true) {

                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('gridpanelforReturnRequstItems').getStore().load({
                                                params: {
                                                    order_id: order_id
                                                }
                                            });
                                            upReturnQty_window.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                        } else if (tmp.success === true && tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                        } else {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.message);
                                    }
                                });
                                var new_fsto_pkdQty = parseInt(record.data.item_return_qty) + 1;
                                record.set('item_return_qty', new_fsto_pkdQty);
                                if (itemReturn_type1 == true) {
                                    if (parseInt(record.data.item_return_sellableqty) > 0) {
                                        var sellableqty = record.data.item_return_sellableqty;
                                    } else {
                                        var sellableqty = 0;
                                    }
                                    var newitem_return_sellableqty = parseInt(sellableqty) + 1;
                                    record.set('item_return_sellableqty', newitem_return_sellableqty);
                                } else {
                                    if (parseInt(record.data.item_return_damagedqty) > 0) {
                                        var damagedqty = record.data.item_return_damagedqty;
                                    } else {
                                        var damagedqty = 0;
                                    }
                                    var newitem_return_damagedqty = parseInt(damagedqty) + 1;
                                    record.set('item_return_damagedqty', newitem_return_damagedqty);
                                }
                                Ext.getCmp('barcode_idret').focus();
                                upReturnQty_window.close();

                            } else {
                                Ext.Msg.alert('Notification', 'Quantity Mismatch.');
                                record.set('item_return_qty', '');
                            }

                        }
                    }, {
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            Ext.Ajax.request({
                                url: modURL + '&op=retbarcodedelete',
                                method: 'POST',
                                params: {
                                    order_id: order_id,
                                    barcodesearch_fieldrt: barcodesearch_fieldrt,
                                    ordertype: ordertype
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        upReturnQty_window.close();

                                    } else {
                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                    }
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });

                        }
                    }]
            });

        }
        upReturnQty_window.doLayout();
        upReturnQty_window.show(this);
        upReturnQty_window.center();
    };
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var returnDamageGridstore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listReturnDamage',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['rtrqod_item_id', 'stit_SKU', 'item_count', 'returnOrders', 'rtrqo_type', 'rtrqo_typeName', 'rtrqoId']),
            sortInfo: {
                field: 'stit_SKU',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: true,
            root: 'data',
            remoteSort: true
        });
        store.setDefaultSort('stit_SKU', 'ASC');
        return store;
    };
    var check_model = function (vid) {
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            dataIndex: 'checked',
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    record.set('checked', 'false');
                    Application.ReturnRequest.viewItemOrderDetails(vid);
                },
                rowselect: function (sm, rowIndex, record) {
                    record.set('checked', 'true');
                    Application.ReturnRequest.viewItemOrderDetails(vid);
                }
            }
        });
    };
    var purchasePaymentPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white", "padding": "5px 3px"},
            border: false,
            id: id,
            title: 'Stock Return of ' + _SESSION.current_branch,
            iconCls: 'package',
            items: [masterPanelforReturnDamageGrid(), invoiceDetail()]
        });
        return panel;
    };
    var invoiceDetail = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var src = "?module=retaline_return_request&op=return_order_view&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp + "&itemIds=" + Application.ReturnRequest.Cache.itemIds + "&returnOrders=" + Application.ReturnRequest.Cache.returnOrders;
        return new Ext.Panel({
            frame: false,
            border: true,
            layout: 'border',
            region: 'east',
            title: 'Item Details',
            width: winsize.width * 0.4,
            id: 'damret_parent_sliderpanel',
            collapsible: true,
            collapseMode: 'mini',
            collapsed: true,
            cls: 'left_side_panel',
            items: [new Ext.Panel({
                    frame: false,
                    border: true,
                    autoheight: true,
                    region: 'center',
                    layout: 'vbox',
                    cls: 'left_side_panel',
                    layoutConfig: {
                        align: 'stretch',
                        pack: 'start'
                    },
                    items: [{
                            region: 'center',
                            height: winsize.height * 0.75,
                            defaults: {
                                frame: false
                            },
                            html: '<iframe id="iframe_retOrderDetails" name="iframe_retOrderDetails" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' + src + '"; ></iframe>',
                            fbar: [
                                {
                                    text: "Create Packing Order",
                                    id: 'candidate_edit_btn',
                                    tabIndex: 80,
                                    handler: function () {
                                        if (Application.ReturnRequest.Cache.rqotype == true) {
                                            Ext.Ajax.request({
                                                url: modURL + '&op=createPackingOrderfromReturns',
                                                method: 'POST',
                                                params: {
                                                    itemIds: Application.ReturnRequest.Cache.itemIds,
                                                    returnOrders: Application.ReturnRequest.Cache.returnOrders,
                                                    itemRecords: Application.ReturnRequest.Cache.itemRecords,
                                                    rqotypeNAme: Application.ReturnRequest.Cache.rqotypeNAme

                                                },
                                                success: function (response) {
                                                    eval("var tmp=" + response.responseText);
                                                    if (tmp.success === true) {
                                                        Application.ReturnRequest.initReturnDamaged();
                                                    }
                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert('Notification', "Return Type should be same to create Packing Order");
                                        }

                                    }
                                }]
                        }]
                })]
        });
    };
    var masterPanelforReturnDamageGrid = function () {
        var chk_model = check_model();
        var returnDamageGrid_store = returnDamageGridstore();
        var returnDamage_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'item_count'
                }]
        });
        returnDamage_filter.remote = true;
        returnDamage_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: returnDamageGrid_store,
            layout: 'fit',
            region: 'center',
            frame: false,
            border: false,
            loadMask: true,
            height: winsize.height * 0.8,
            title: 'Items',
            //iconCls: 'finascop_approval',
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), returnDamage_filter],
            id: 'salesReturnDamageGrid',
            columns: [chk_model,
                {
                    hidden: true,
                    header: 'Item id',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rtrqod_item_id',
                    tooltip: 'Item id',
                },
                {
                    header: 'Item Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item Name',
                    width: 150
                }, {
                    header: 'Return Type',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rtrqo_typeName',
                    tooltip: 'Return Type'
                },
                {
                    header: 'Damage Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_count',
                    tooltip: 'Damage Count'
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    width: 25,
                    items: [{
                            hidden: true
                        }, {
                            hidden: true
                        }, {
                            hidden: true
                        }]
                }],
            viewConfig: {
                forceFit: true
            },
            sm: chk_model,
            listeners: {
                viewready: updatePagination
            },
            tbar: [{
                    text: 'Create Stock Return',
                    tooltip: 'Create Stock Return',
                    icon: './resources/images/submenuicons/add.png',
//                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        if ((_SESSION.br_PyramidLevel == 4) || (_SESSION.br_PyramidLevel == 3)) {
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
                                    Application.ReturnRequest.Cache.rsuuid = uid;
                                    Application.ReturnRequest.addNewStockReturn();
                                }
                            });
                        } else {
                            Ext.MessageBox.alert("Notification", "Adding Stock Return is not possible here.");
                        }
                    }
                }, {
                    text: 'Return Banned Batches',
                    tooltip: 'Return Banned Batches',
//                    iconCls: 'finascop_search_btn',
                    iconCls: 'return-back',
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
                                Application.ReturnRequest.Cache.banneduuid = uid;
                                Application.ReturnRequest.addBannedStock();
                            }
                        });
                    }

                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: returnDamageGrid_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
            }),
            stripeRows: true
        });
        return grid_panel;
    };
    var ReturnStockStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listReturnedToDamage',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'stit_id',
                root: 'data'
            }, ['stit_category_name', 'blocked_count', 'cart_count', 'br_Name', 'stit_SKU', 'item_count', 'bom_offrPlacement', 'mrp', 'selling_price', 'optimumqty', 'minimumqty', 'maximumqty', 'stit_id', 'frbi_epr', 'frbi_id', 'actualValue',
                'branchId', 'rack_count', 'dispatch_count', 'receive_count', 'deliver_count', 'fsbg_name', 'fsbg_id', 'stit_quantity', 'stit_brand_name', 'stit_product_variant', 'frbi_leastSKUepr',
                'frbi_statusName', 'frbi_status', 'frbi_leastSKUmrp', 'qtyDistribution'
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
                    this.baseParams.branchName = Ext.getCmp('comboxbranchnameRetStock').getValue();
                }
            }
        });
        //store.setDefaultSort('stit_id', 'ASC');
        return store;
    };
    var check_modelRS = function (vid) {
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            dataIndex: 'checked',
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    record.set('checked', 'false');
                    if (sm.selections.length > 0) {
                        Ext.getCmp('returnStockOrder').enable();
                        Ext.getCmp('returnStockLoss').enable();
                    } else {
                        Ext.getCmp('returnStockOrder').disable();
                        Ext.getCmp('returnStockLoss').disable();
                    }
                },
                rowselect: function (sm, rowIndex, record) {
                    record.set('checked', 'true');
                    if (sm.selections.length > 0) {
                        Ext.getCmp('returnStockOrder').enable();
                        Ext.getCmp('returnStockLoss').enable();
                    } else {
                        Ext.getCmp('returnStockOrder').disable();
                        Ext.getCmp('returnStockLoss').disable();
                    }
                }
            }
        });
    };
    var masterPanelforReturnedtStockGrid = function (id) {
        var chk_model = check_modelRS();
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
            title: 'Purchase Returnable',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [chk_model, new Ext.grid.RowNumberer(),
                {
                    header: 'Branch',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                },
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
                }, {
                    header: 'Quantity Units',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'qtyDistribution',
                    tooltip: 'Quantity Units',
                },
                {
                    header: 'Total Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_count',
                    tooltip: 'Total Count'
                },
                {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: 'MRP'
                }, {
                    header: 'SKU MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'frbi_leastSKUmrp',
                    tooltip: 'SKU MRP'
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
                    header: 'SKU EPR',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'frbi_leastSKUepr',
                    tooltip: 'SKU EPR',
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
            sm: chk_model,
            listeners: {
                resize: updatePagination,
                //viewready: updatePagination,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchnameRetStock").show();
                        Ext.getCmp("textboxBrmCpdDataeditRetSt").hide();
                    } else {
                        Ext.getCmp("textboxBrmCpdDataeditRetSt").show();
                        Ext.getCmp("textboxBrmCpdDataeditRetSt").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchnameRetStock").hide();
                    }
                }
            },
            tbar: [{
                    text: 'Create Purchase Returnable',
                    tooltip: 'Create Purchase Returnable',
                    icon: './resources/images/submenuicons/add.png',
//                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        if (_SESSION.br_PyramidLevel == 2) {
                            Application.ReturnRequest.addNewPurchaseReturn();
                        } else {
                            Ext.MessageBox.alert("Notification", "Adding Purchase Return is not possible here.");
                        }



                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditRetSt',
                    name: 'textboxBrmCpdDataeditRetSt',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchnameRetStock',
                    name: 'comboxbranchnameRetStock',
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
                    hiddenName: 'comboxbranchnameRetStock',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchnameRetStock').getValue();
                            Ext.getCmp('panelMasterReturnedtStock').getStore().load({
                                params: {
                                    branchName: branchName
                                }
                            });
                        }
                    }
                }, '->', {
                    text: 'Create Return Order',
                    tooltip: 'Create Return Order',
                    disabled: true,
                    id: 'returnStockOrder',
//                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        if (_SESSION.br_PyramidLevel == 2 || _SESSION.br_PyramidLevel == 1) {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            var selectitem = Ext.getCmp('panelMasterReturnedtStock').getSelectionModel().getSelections();
                            var selectedcount = selectitem.length;
                            var itemarr = [];
                            var itemReturnCount = 0;
                            for (var i = 0; i < selectedcount; i++)
                            {
                                itemarr.push(selectitem[i].data.stit_id);
                                itemReturnCount += selectitem[i].data.item_count;
                            }
                            if (itemReturnCount > 0) {
                                Application.ReturnRequest.createReturnOrder(itemarr);
                            } else {
                                Ext.MessageBox.alert("Notification", "There is no items for return order.");
                            }

                        }
                    }
                }, {
                    text: 'Create Stock Loss',
                    tooltip: 'Create Stock Loss',
                    disabled: true,
                    id: 'returnStockLoss',
//                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        if (_SESSION.br_PyramidLevel == 2 || _SESSION.br_PyramidLevel == 1) {
                            var selectitem = Ext.getCmp('panelMasterReturnedtStock').getSelectionModel().getSelections();
                            var selectedcount = selectitem.length;
                            var itemarr = [];
                            var itemReturnCount = 0, itemLossValue = 0;
                            for (var i = 0; i < selectedcount; i++)
                            {
                                itemarr.push(selectitem[i].data.stit_id);
                                itemReturnCount += selectitem[i].data.item_count;
                                //itemLossValue += selectitem[i].data.item_count * selectitem[i].data.frbi_leastSKUepr;
                                itemLossValue += selectitem[i].data.actualValue;
                            }
                            console.log('itemLossValue', itemLossValue);
                            console.log('itemarr', itemarr);
                            if (itemReturnCount > 0) {
                                Application.ReturnRequest.createStockLoss(itemarr, itemLossValue);
                            } else {
                                Ext.MessageBox.alert("Notification", "There is no items for stock loss.");
                            }

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
    var rtItemStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU', 'item_count'],
            url: modURL + '&op=getItemName',
            autoLoad: true,
            method: 'post'
        });
        return store;
    };
    var createRetalineStockReturnform = function () {
        var itemsStore = rtItemStore();
        return new Ext.form.FormPanel({
            height: 100,
            frame: true,
            border: false,
            id: 'stockReturnItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    bodyStyle: {"padding": "5px 3px 5px 3px"},
                    items: [{
                            layout: 'form',
                            columnWidth: 0.80,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Items',
                                    id: 'rtrqod_itemId',
                                    name: 'rtrqod_itemId',
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
                                    hiddenName: 'rtrqod_itemId',
                                    tabIndex: 600,
                                    listeners: {
                                    }
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
                                    id: 'rtrqod_return_damaged',
                                    name: 'rtrqod_return_damaged',
                                    allowBlank: false,
                                    labelAlign: 'top',
                                    anchor: '90%'
                                }
                            ]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.09,
                            items: [{
                                    xtype: 'button',
                                    text: 'Add',
                                    anchor: '96%',
                                    iconCls: 'finascop_add',
                                    style: 'margin-top:17px;',
                                    handler: function () {

                                        if (!Ext.isEmpty(Ext.getCmp('rtrqod_itemId').getValue()) &&
                                                !Ext.isEmpty(Ext.getCmp('rtrqod_return_damaged').getValue())) {

                                            Ext.Ajax.request({
                                                url: modURL + '&op=addItemstoStockReturn',
                                                method: 'POST',
                                                params: {
                                                    uuid: Application.ReturnRequest.Cache.rsuuid,
                                                    rtrqod_itemId: Ext.getCmp('rtrqod_itemId').getValue(),
                                                    rtrqod_return_damaged: Ext.getCmp('rtrqod_return_damaged').getValue(),
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true && tmp.valid === true) {
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineStockReturnItemGridPanel').getStore().load({
                                                            params: {
                                                                uuid: Application.ReturnRequest.Cache.rsuuid
                                                            }
                                                        });
                                                        Ext.getCmp('rtrqod_itemId').reset();
                                                        Ext.getCmp('rtrqod_return_damaged').reset();
                                                    } else if (tmp.success === true && tmp.valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.msg);
                                                    } else if (tmp.success === false && tmp.valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.msg);
                                                    } else {
                                                        Ext.Msg.alert("Error", 'Incorrect input data.');
                                                    }
                                                },
                                                failure: function (response) {
                                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                                    Ext.MessageBox.alert('Error', tmp.message);
                                                }
                                            });
                                        } else
                                        {
                                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                                        }
                                    }
                                }]
                        }]
                }]
        });
    };
    var createRetalineStockReturnItemGrid = function () {


        var retalineTransferRequestItemGridStore = retalineTransferRequestItemStore();
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineTransferRequestItemGridStore,
            frame: false,
            border: true,
            height: 300,
            autoScroll: true,
            plugins: [],
            id: 'retalineStockReturnItemGridPanel',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'rtrqod_ItemName',
                    width: 150,
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'rtrqod_return_damaged',
                    align: 'right',
                    tooltip: 'Quantity'
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
                                deleteItem(record.get('rtrqod_id'));
                            }
                        }
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
    var retalineTransferRequestItemStore = function () {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listStockReturnItems',
            fields: ['rtrqo_uid', 'fstr_id', 'rtrqod_id', 'rtrqod_itemId', 'rtrqod_return_damaged', 'rtrqod_ItemName'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        uuid: Application.ReturnRequest.Cache.rsuuid
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var createRetalinePurchaseReturnableform = function () {
        var itemsStore = rtItemStore();
        return new Ext.form.FormPanel({
            height: 150,
            frame: true,
            border: false,
            id: 'purchaseReturnableItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    bodyStyle: {"padding": "5px 3px 5px 3px"},
                    items: [{
                            layout: 'form',
                            columnWidth: 0.80,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Items',
                                    id: 'rtrqod_itemId',
                                    name: 'rtrqod_itemId',
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
                                    hiddenName: 'rtrqod_itemId',
                                    tabIndex: 600,
                                    listeners: {
                                        select: function () {
                                            var rtrqod_itemId = Ext.getCmp('rtrqod_itemId').getValue();
                                            Ext.getCmp('gridpanelforbarcodeDetails').getStore().load({
                                                params: {
                                                    stit_id: rtrqod_itemId
                                                }
                                            });
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
                                    xtype: 'textfield',
                                    fieldLabel: 'Quantity',
                                    id: 'rtrqod_return_damaged',
                                    name: 'rtrqod_return_damaged',
                                    allowBlank: false,
                                    readOnly: true,
                                    labelAlign: 'top',
                                    anchor: '90%'
                                }
                            ]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.09,
                            items: [{
                                    xtype: 'button',
                                    text: 'Add',
                                    anchor: '96%',
                                    iconCls: 'finascop_add',
                                    style: 'margin-top:17px;',
                                    handler: function () {

                                        if (!Ext.isEmpty(Ext.getCmp('rtrqod_itemId').getValue()) &&
                                                !Ext.isEmpty(Ext.getCmp('rtrqod_return_damaged').getValue())) {
                                            var store_fields = Ext.getCmp('gridpanelforbarcodeDetails').getSelectionModel().getSelections();
                                            if (store_fields.length > 0) {
                                                var stiid_id = [];
                                                for (var i = 0; i < store_fields.length; i++) {
                                                    stiid_id[i] = store_fields[i].data.stiid_id;
                                                }
                                                Ext.Ajax.request({
                                                    url: modURL + '&op=addItemstoPurchaseReturn',
                                                    method: 'POST',
                                                    params: {
                                                        stiid_id: Ext.encode(stiid_id),
                                                        rtrqod_itemId: Ext.getCmp('rtrqod_itemId').getValue(),
                                                        rtrqod_return_damaged: Ext.getCmp('rtrqod_return_damaged').getValue(),
                                                    },
                                                    success: function (response) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        if (tmp.success === true && tmp.valid === true) {
                                                            Application.example.msg('Success', tmp.msg);
                                                            var branchName = Ext.getCmp('comboxbranchnameRetStock').getValue();
                                                            Ext.getCmp('panelMasterReturnedtStock').getStore().load({
                                                                params: {
                                                                    branchName: branchName
                                                                }
                                                            });
                                                            Ext.getCmp('rtrqod_itemId').reset();
                                                            Ext.getCmp('rtrqod_return_damaged').reset();
                                                            Ext.getCmp('gridpanelforbarcodeDetails').getStore().load({
                                                                params: {
                                                                    stit_id: 0
                                                                }
                                                            });
                                                        } else if (tmp.success === true && tmp.valid === false) {
                                                            Ext.Msg.alert("Notification.", tmp.msg);
                                                        } else if (tmp.success === false && tmp.valid === false) {
                                                            Ext.Msg.alert("Notification.", tmp.msg);
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

                                        } else
                                        {
                                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                                        }
                                    }
                                }]
                        }]
                }]
        });
    };

    var vendorStore = function (itemarr) {
        var store = new Ext.data.JsonStore({
            fields: ['stpa_id', 'stpa_Fname'],
            url: modURL + '&op=getVendorName',
            autoLoad: true,
            method: 'post',
            listeners: {
                beforeLoad: function () {
                    this.baseParams.itemarr = Ext.encode(itemarr);
                }
            }
        });
        return store;
    };
    var createRetalineReturnOrderform = function (itemarr) {
        var retvnedorStore = vendorStore(itemarr);
        return new Ext.form.FormPanel({
            height: winsize.height * 0.2,
            frame: true,
            border: false,
            region: 'south',
            id: 'returnOrderFormPanel',
            layout: 'column',
            items: [{
                    layout: 'form',
                    columnWidth: 0.30,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Vendor',
                            id: 'rtvendorId',
                            name: 'rtvendorId',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            editable: true,
                            anchor: '80%',
                            store: retvnedorStore,
                            triggerAction: 'all',
                            //hideTrigger: true,
                            minChars: 1,
                            selectOnFocus: false,
                            displayField: 'stpa_Fname',
                            valueField: 'stpa_id',
                            hiddenName: 'rtvendorId',
                            tabIndex: 600,
                            listeners: {
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.30,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Actual Value',
                            id: 'prtoValue',
                            name: 'prtoValue',
                            allowBlank: false,
                            readOnly: true,
                            anchor: '80%'
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.30,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Amount',
                            id: 'prtoAmount',
                            name: 'prtoAmount',
                            allowBlank: false,
                            anchor: '80%',
                            listeners: {
                                focus: function () {
                                    var totalValue = 0, actualValue = 0;
                                    var itemInROStore = Ext.getCmp('gridForReturnOrder').getStore().getRange();
                                    Ext.each(itemInROStore, function (rec) {
                                        //console.log('itemReturnable', rec.get('itemReturnable'));
                                        console.log('frbi_epr', rec.get('frbi_epr'));
                                        //actualValue = parseFloat(rec.get('itemReturnable')) * parseFloat(rec.get('frbi_leastSKUepr'));
                                        actualValue = rec.get('actualValue');
                                        console.log('actualValue', actualValue);
                                        totalValue = parseFloat(totalValue) + parseFloat(actualValue);
                                        console.log('totalValue', totalValue);
                                    });
                                    //Ext.getCmp('prtoValue').setValue(totalValue);
                                    Ext.getCmp('prtoValue').setValue(totalValue);

                                }
                            }
                        }]
                }
            ]
        });
    };
    var ReturnStockStoreConfirm = function (itemarr) {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=confirmedReturnedToDamage',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'stit_id',
                root: 'data'
            }, ['stit_category_name', 'blocked_count', 'cart_count', 'br_Name', 'stit_SKU', 'item_count', 'bom_offrPlacement', 'mrp', 'selling_price', 'optimumqty', 'minimumqty', 'maximumqty', 'stit_id', 'frbi_id', 'frbi_leastSKUepr',
                'branchId', 'rack_count', 'dispatch_count', 'receive_count', 'deliver_count', 'fsbg_name', 'fsbg_id', 'stit_quantity', 'stit_brand_name', 'stit_product_variant', 'itemReturnable', 'frbi_epr',
                'frbi_leastSKUmrp', 'actualValue', 'totalSKUs'
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
                beforeload: function () {
                    this.baseParams.itemarr = Ext.encode(itemarr);
                },
                load: function () {
                    Ext.getCmp('prtoAmount').focus();
                }
            }
        });

        return store;
    };
    var retalineReturnOrderGrid = function (itemarr) {
        var ReturnStock_store = ReturnStockStoreConfirm(itemarr);
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

        var grid_panel = new Ext.grid.EditorGridPanel({
            store: ReturnStock_store,
            layout: 'fit',
            frame: false,
            border: false,
            height: winsize.height * 0.6,
            autoScroll: true,
            title: '',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: 'gridForReturnOrder',
            region: 'center',
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
                }, {
                    header: 'SKU Count',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'totalSKUs',
                    tooltip: 'SKU Count',
                }, {
                    header: 'SKU EPR',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'frbi_leastSKUepr',
                    tooltip: 'SKU EPR',
                }, {
                    header: 'EPR',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'frbi_epr',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'EPR',
                },
                {
                    header: 'Returnable',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_count',
                    tooltip: 'Returnable'
                }, {
                    header: 'Returned',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'itemReturnable',
                    tooltip: 'Returned',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                },
                {
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
                afterrender: function () {
                    Ext.getCmp('prtoAmount').focus();
                },
                afteredit: function (grid_event) {
                    Ext.getCmp('prtoAmount').focus();
                }
            },
            tbar: [],
            stripeRows: true
        });
        return grid_panel;
    };
    var viewBarcodeGrid = function (fsto_id, fsto_itemId) {
        var _viewBarcodeStore = viewBarcodeStore(fsto_id, fsto_itemId);
        var _viewBarcodeGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _viewBarcodeStore,
            iconCls: 'money',
            autoScroll: true,
            width: 300,
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelviewBarcodeGrid',
            columns: [
                {
                    header: 'Barcodes',
                    dataIndex: 'rtrqb_barcode',
                    sortable: true,
                    tooltip: 'Barcodes',
                    hideable: true
                }
            ],
            viewConfig: {
                forceFit: true,
            }

        });
        return _viewBarcodeGridPanel;
    };
    var viewBarcodeStore = function (fsto_id, fsto_itemId) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getItemBarcodes',
                method: 'post'
            }),
            fields: ['rtrqb_barcode', 'rtrqb_itemId', 'rtrqb_id'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelviewBarcodeGrid').getSelectionModel().selectRow(0);
                },
                beforeload: function (store, e) {
                    //var fsto_source = Ext.getCmp('gridpanelAddnewTransferOrderdata').getSelectionModel().getSelections()[0].data.fsto_source;
                    this.baseParams.fsto_id = fsto_id;
                    this.baseParams.fsto_itemId = fsto_itemId;
                    //this.baseParams.fsto_source = fsto_source;
                }
            }
        });
        return _Store;
    };
    var chk_bannedModelfn = function () {
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            dataIndex: 'checked',
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    record.set('checked', 'false');
                    Application.ReturnRequest.chooseBannedItems();
                },
                rowselect: function (sm, rowIndex, record) {
                    record.set('checked', 'true');
                    Application.ReturnRequest.chooseBannedItems();
                }
            }
        });
    };
    var createRetalineBannedStockGrid = function () {
        var chk_bannedModel = chk_bannedModelfn();
        var retalineBannedStockGridStore = retalineBannedStockStore();
        var bannedStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineBannedStockGridStore,
            frame: false,
            border: true,
            height: 300,
            autoScroll: true,
            plugins: [],
            id: 'retalineStockBannedGridPanel',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [chk_bannedModel,
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'stiid_itemmastername',
                    tooltip: 'Item'
                },
                {
                    header: 'Batch',
                    sortable: true,
                    dataIndex: 'fsibb_batch',
                    width: 150,
                    tooltip: 'Batch'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'fsibb_quantity',
                    align: 'right',
                    tooltip: 'Quantity'
                }],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            sm: chk_bannedModel,
            listeners: {},
            stripeRows: true,
        });
        return bannedStockEntryGrid;
    };
    var retalineBannedStockStore = function () {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getRetalineBannedStock',
            fields: ['fsibb_id', 'stiid_itemmasterid', 'fsibb_batch', 'fsibb_quantity', 'stiid_itemmastername'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        uuid: Application.ReturnRequest.Cache.rsuuid
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    function allEqual(arr) {
        return new Set(arr).size == 1;
    }
    function onlyUnique(value, index, self) {
        return self.indexOf(value) === index;
    }
    var ckSelection = function () {
        var ApprovedItemQty = 0;
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    console.log("Checkboxde:", record);
                    ApprovedItemQty -= 1;
                    console.log('rowdeselect', ApprovedItemQty);
                    Ext.getCmp('rtrqod_return_damaged').setValue(ApprovedItemQty);
                    record.set('checked', 'false');
                },
                rowselect: function (sm, rowIndex, record) {
                    console.log("Checkboxde:", record);
                    ApprovedItemQty += 1;
                    console.log('rowselect', ApprovedItemQty);
                    Ext.getCmp('rtrqod_return_damaged').setValue(ApprovedItemQty);
                    record.set('checked', 'true');
                }
            }
        });
    };
    var _barcodeDetailsGrid = function (stit_id) {
        var ck_selection = ckSelection();
        var spfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stiid_barcode'
                }]
        });
        spfilters.remote = true;
        spfilters.autoReload = true;
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _barcodeDetailsStore(stit_id),
            iconCls: 'money',
            width: winsize.width * 0.8,
            height: 500,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforbarcodeDetails',
            sm: ck_selection,
            plugins: [spfilters],
            columns: [ck_selection,
                {
                    header: 'Barcode',
                    dataIndex: 'stiid_barcode',
                    sortable: true,
                    tooltip: 'Barcode',
                    hideable: false,
                    width: 100
                }, {
                    header: 'Batch',
                    dataIndex: 'stiid_batchno',
                    sortable: true,
                    tooltip: 'Batch',
                    hideable: false,
                    width: 100
                }, {
                    header: 'Expiry Date',
                    dataIndex: 'stiid_expirydate',
                    sortable: true,
                    tooltip: 'Expiry Date',
                    hideable: false
                }, {
                    header: 'Created On',
                    dataIndex: 'stiid_createdon',
                    sortable: true,
                    tooltip: 'Created On',
                    hideable: false
                }, {
                    header: 'Updated On',
                    dataIndex: 'stiid_updatedon',
                    sortable: true,
                    tooltip: 'Created On',
                    hideable: false
                }, {
                    header: 'Status',
                    dataIndex: 'stiid_statusStat',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false,
                    width: 150,
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.stiid_statusStat + '"';
                        return value;
                    }
                }, {
                    header: ' ',
                    width: 20,
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    switch (record.get('stiid_statusStat')) {
                        case 'Godown and available for transfer':
                            return '';
                            break;
                        case 'Godown and marked for outward':
                            return 'finascop_indicateColCASHMERE';
                            break;
                        case 'Dispatched and in transit to branch':
                            return 'finascop_indicateColLIGHTORANGE';
                            break;
                        case 'Received at branch and ready for sale':
                            return 'finascop_indicateColPINK';
                            break;
                        case 'Item in Delivery Cart':
                            return 'finascop_indicateColLIGHTYELLOW';
                            break;
                        case 'Item Out For Delivery':
                            return 'finascop_indicateColPALEGREEN';
                            break;
                        case 'Item Delivered':
                            return 'finascop_indicateColGUMLEAFGREEN';
                            break;

                    }

                }
            },
            listeners: {
                afterrender: function () {

                }, rowdblclick: function (grid, rowIndex, e) {
                    var rec = grid.getStore().getAt(rowIndex);
                }
            }
        });
        return _dispatchgridPanel;
    };
    var _barcodeDetailsStore = function (stit_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listbarcodesinCurrentStock',
                method: 'post'
            }),
            fields: ['stiid_id', 'stiid_barcode', 'stiid_createdon', 'stiid_updatedon', 'stiid_statusStat', 'stiid_customerRateHmDel', 'stiid_customerRateCouDel', 'stiid_customerRatePikup', 'stiid_expirydate', 'stiid_leastSKUmrp', 'stiid_itemleastSKUpts', 'stiid_itemleastSKUptr', 'stiid_batchno'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
            }
        });
        return _Store;
    };
    return {
        Cache: {},
        orderReturnRequest: function () {

            var panelId = 'order_returnreq_processing_panel';
            var ReturnRequestPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(ReturnRequestPanel)) {
                ReturnRequestPanel = orderTopPanel(panelId);
                Application.UI.addTab(ReturnRequestPanel);
                ReturnRequestPanel.doLayout();
            } else {
                Application.UI.addTab(ReturnRequestPanel);
            }

        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            var rtrqo_sourceType = arguments[1];
            Ext.getCmp('order_returnreq_details_view_panel').show();

            Ext.get('iframe_returnreq_orderdtls').dom.src = modURL + '&op=order_details&order_auto_id=' + order_auto_id + '&rtrqo_sourceType=' + rtrqo_sourceType + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        },
        addNewOrderToRequestReturn: function () {
            var win_id = "windowAddNewOrderToReturnReq";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {
                win = new Ext.Window({
                    id: win_id,
                    title: 'Order Details',
                    layout: 'fit',
                    width: winsize.width * 0.85,
                    height: 500,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: new Ext.Panel({
                        autoHeight: true,
                        border: false,
                        layout: 'column',
                        bodyStyle: {"background-color": "F1F1F1", "padding": "5px"},
                        items: [{
                                layout: 'form',
                                columnWidth: .60,
                                border: false,
                                hideLabel: true,
                                hideBorders: true,
                                bodyStyle: {"background-color": "F1F1F1"},
                                items: [{
                                        hideLabel: true,
                                        xtype: 'radiogroup',
                                        //anchor: '90%',
                                        id: 'radiogroupOrderSearchReturnReq',
                                        items: [{
                                                boxLabel: 'Order Number',
                                                name: 'search_type',
                                                inputValue: 'orderNumber',
                                                anchor: '80%',
                                                id: 'search_type1',
                                                listeners: {
                                                    'check': function (checkbox, checked) {
                                                        if (checked) {
                                                            Ext.getCmp('order_numberretreq').show();
                                                            Ext.getCmp('customer_mobileretreq').hide();
                                                        }
                                                    }
                                                }},
                                            {
                                                boxLabel: 'Customer Mobile',
                                                name: 'search_type',
                                                anchor: '85%',
                                                inputValue: 'customerMobile',
                                                id: 'search_type2',
                                                checked: true,
                                                listeners: {
                                                    'check': function (checkbox, checked) {
                                                        if (checked) {
                                                            Ext.getCmp('order_numberretreq').hide();
                                                            Ext.getCmp('customer_mobileretreq').show();
                                                        }
                                                    }
                                                }},
                                            {
                                                hideLabel: true,
                                                xtype: 'combo',
                                                displayField: 'name',
                                                valueField: 'id',
                                                mode: 'local',
                                                id: 'ordertype',
                                                forceSelection: true,
                                                //fieldLabel: 'Order Type',
                                                emptyText: 'Order Type',
                                                anchor: '90%',
                                                typeAhead: true,
                                                triggerAction: 'all',
                                                lazyRender: true,
                                                editable: true,
                                                minChars: 2,
                                                tabIndex: 913,
                                                store: new Ext.data.JsonStore({
                                                    fields: ['id', 'name'],
                                                    data: [{id: 'B2B', name: 'B2B'}, {id: 'B2C', name: 'B2C'}]
                                                })
                                            },
                                            {
                                                hideLabel: true,
                                                xtype: 'textfield',
                                                id: 'customer_mobileretreq',
                                                name: 'n[customer_mobileretreq]',
                                                emptyText: 'Enter Mobile',
                                                allowBlank: false,
                                                anchor: '90%'
                                            },
                                            {
                                                hideLabel: true,
                                                xtype: 'textfield',
                                                id: 'order_numberretreq',
                                                name: 'n[order_numberretreq]',
                                                emptyText: 'Enter Order Number',
                                                allowBlank: false,
                                                hidden: true,
                                                anchor: '90%'
                                            }, {
                                                xtype: 'button',
                                                text: 'Search',
                                                anchor: '50%',
                                                iconCls: 'finascop_search_btn',
                                                handler: function () {
                                                    var ordertype = Ext.getCmp('ordertype').getValue();
                                                    Application.ReturnRequest.Cache.ordertype = ordertype;
                                                    var searchType = Ext.getCmp('radiogroupOrderSearchReturnReq').getValue();
                                                    var customer_mobileretreq = Ext.getCmp('customer_mobileretreq').getValue();
                                                    var order_numberretreq = Ext.getCmp('order_numberretreq').getValue();
                                                    switch (searchType) {
                                                        case 'orderNumber':
                                                            var type = 'order';
                                                            Ext.getCmp('gridpanelOrderReturnReq').getStore().load({
                                                                params: {
                                                                    customer_mobileretreq: order_numberretreq,
                                                                    type: type,
                                                                    ordertype: ordertype
                                                                }
                                                            });
                                                            break;
                                                        case 'customerMobile':
                                                            var type = 'mobile';
                                                            Ext.getCmp('gridpanelOrderReturnReq').getStore().load({
                                                                params: {
                                                                    customer_mobileretreq: customer_mobileretreq,
                                                                    type: type,
                                                                }
                                                            });
                                                            break;
                                                    }
//                                           
                                                }
                                            }]
                                    }]
                            },
//                            {
//                                layout: 'form',
//                                columnWidth: 0.10,
//                                labelAlign: 'left',
//                                border: false,
//                                hideBorders: true,
//                                bodyStyle: {"background-color": "F1F1F1"},
//                                items: [
//                                    {
//                                        xtype: 'button',
//                                        text: 'Search',
//                                        iconCls: 'finascop_search_btn',
//                                        handler: function () {
//                                            var ordertype = Ext.getCmp('ordertype').getValue();
//                                            Application.ReturnRequest.Cache.ordertype = ordertype;
//                                            var searchType = Ext.getCmp('radiogroupOrderSearchReturnReq').getValue();
//                                            var customer_mobileretreq = Ext.getCmp('customer_mobileretreq').getValue();
//                                            var order_numberretreq = Ext.getCmp('order_numberretreq').getValue();
//                                            switch (searchType) {
//                                                case 'orderNumber':
//                                                    var type = 'order';
//                                                    Ext.getCmp('gridpanelOrderReturnReq').getStore().load({
//                                                        params: {
//                                                            customer_mobileretreq: order_numberretreq,
//                                                            type: type,
//                                                            ordertype: ordertype
//                                                        }
//                                                    });
//                                                    break;
//                                                case 'customerMobile':
//                                                    var type = 'mobile';
//                                                    Ext.getCmp('gridpanelOrderReturnReq').getStore().load({
//                                                        params: {
//                                                            customer_mobileretreq: customer_mobileretreq,
//                                                            type: type,
//                                                        }
//                                                    });
//                                                    break;
//                                            }
////                                           
//                                        }
//                                    }]
//                            },
                            {
                                columnWidth: 1,
                                height: 490,
                                bodyStyle: {"background-color": "white"},
                                //bodyStyle: {"background-color": "white", "padding": "5px"},
                                items: OrderDetailsGrid()
                            }]
                    }),
                    listeners: {
                        afterrender: function () {
                        }
                    }
                });
            }
            win.doLayout();
            win.show(this);
            win.center();
        },
        returnOrder: function (order_id, cust_id, updated_at, status_id, ordertype) {
            var win_canc_id = "windowReturnRequestOrder";
            var win_canc = Ext.getCmp(win_canc_id);
            if (Ext.isEmpty(win_canc)) {
                win_canc = new Ext.Window({
                    id: win_canc_id,
                    title: 'Order Item Details',
                    layout: 'border',
                    width: winsize.width * 0.7,
                    height: 600,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: [{
                            region: 'north',
                            height: 150,
                            items: returnOrderDetails()
                        }, {
                            region: 'center',
                            layout: 'fit',
                            height: 450,
                            items: orderGridFn(order_id, ordertype)
                        }],
                    buttonAlign: 'right',
                    fbar: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            tabIndex: 130,
                            iconCls: 'my-icon1',
                            handler: function () {
                                win_canc.close();
                            }
                        }, {
                            hidden: true,
                            text: 'Save Return',
                            xtype: 'button',
//                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            width: 80,
                            handler: function () {
                                //gridpanelOrderReturnReq
                                var itemRecord = Ext.getCmp('gridpanelforReturnRequstItems').getStore();
                                var item_return_qty = itemRecord.sum('item_return_qty');
                                if (item_return_qty > 0) {
                                    var itemGriddata = getMigratedData('gridpanelforReturnRequstItems');
                                    console.log('itemGriddata', itemGriddata);
                                    Ext.Ajax.request({
                                        url: modURL + '&op=saveRequestinOrder',
                                        method: 'POST',
                                        params: {
                                            order_no: order_id,
                                            cust_id: cust_id,
                                            itemGriddata: itemGriddata,
                                            updated_at: updated_at,
                                            status_id: status_id,
                                            item_return_qty: item_return_qty
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', 'Return Saved.');
                                                Ext.getCmp('manage_order_returnreq_grid').getStore().load();
                                                Ext.getCmp('windowAddNewOrderToReturnReq').close();
                                                win_canc.close();
                                                Application.ReturnRequest.Cache.uuid = '';
                                            } else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert("Notification", "There should be atleast 1 item to return.");
                                }
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            Application.ReturnRequest.orderViewMode(order_id, cust_id, ordertype);
                        }
                    }


                });
            }
            win_canc.doLayout();
            win_canc.show(this);
            win_canc.center();
        }, orderViewMode: function (order_id, cust_id, ordertype) {


            Ext.Ajax.request({
                url: modURL + '&op=returnOrderDetailsView',
                method: 'POST',
                params: {order_id: order_id, cust_id: cust_id, ordertype: ordertype},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('returnOrderDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('returnOrderDetailsViewPanel').doLayout();
        },
        initReturnDamagedold: function () {
            var returnDamagePanelId = 'panelMasterReturnDamage';
            var _masterPanelReturnDamage = Ext.getCmp(returnDamagePanelId);
            if (Ext.isEmpty(_masterPanelReturnDamage)) {
                _masterPanelReturnDamage = masterPanelforReturnDamageGrid(returnDamagePanelId);
                Application.UI.addTab(_masterPanelReturnDamage);
                _masterPanelReturnDamage.doLayout();
            } else {
                Application.UI.addTab(_masterPanelReturnDamage);
            }
        }, initReturnDamaged: function () {
            var panelId = 'panelMasterReturnDamage';
            var purchase_paymentaccount_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchase_paymentaccount_panel)) {
                purchase_paymentaccount_panel = purchasePaymentPanel(panelId);
                Application.UI.addTab(purchase_paymentaccount_panel);
                purchase_paymentaccount_panel.doLayout();
            } else {
                Application.UI.addTab(purchase_paymentaccount_panel);
                purchase_paymentaccount_panel.doLayout();
                Application.ReturnRequest.Cache.itemIds = 0;
                Application.ReturnRequest.Cache.returnOrders = 0;
                Ext.getCmp('damret_parent_sliderpanel').collapse();
                Ext.getCmp('salesReturnDamageGrid').getStore().load({
                    params: {
                        branchId: _SESSION.finascop_current_branch_id
                    }
                });
            }
        }, viewItemOrderDetails: function (vendorId) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var gr = Ext.getCmp('salesReturnDamageGrid');
            var str = gr.getStore().getRange();
            var fpeIds = [];
            var rqotype = [];
            var itemRecords = [];
            var returnOrders = [];
            Ext.each(str, function (r) {
                if (r.get('checked') == 'true') {
                    itemRecords.push(r.data);
                    console.log('r', r.data);
                    fpeIds.push(r.get('rtrqod_item_id'));
                    rqotype.push(r.data.rtrqo_typeName);
                    returnOrders.push(r.data.rtrqoId);
                }
            });
            var rqotypeNAme = rqotype.filter(onlyUnique);
            var returnOrders = returnOrders.filter(onlyUnique);
            Application.ReturnRequest.Cache.rqotype = allEqual(rqotype);
            console.log('Application.ReturnRequest.Cache.returnOrders', returnOrders);
            var peIds = Ext.encode(fpeIds);
            var itemRecords = Ext.encode(itemRecords);
            var rqotypeNAme = Ext.encode(rqotypeNAme);
            var returnOrders = Ext.encode(returnOrders);
            Application.ReturnRequest.Cache.rqotypeNAme = rqotypeNAme;
            Application.ReturnRequest.Cache.itemIds = peIds;
            Application.ReturnRequest.Cache.returnOrders = returnOrders;
            Application.ReturnRequest.Cache.itemRecords = itemRecords;
            Ext.getCmp('damret_parent_sliderpanel').expand(true);
            Ext.get('iframe_retOrderDetails').dom.src = modURL + "&op=return_order_view&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp + "&itemIds=" + peIds + "&returnOrders=" + returnOrders;
        },
        initReturnedtStock: function () {
            var _currentStockPanelId = 'panelMasterReturnedtStock';
            var _masterPanelReturnedtStock = Ext.getCmp(_currentStockPanelId);
            if (Ext.isEmpty(_masterPanelReturnedtStock)) {
                _masterPanelReturnedtStock = masterPanelforReturnedtStockGrid(_currentStockPanelId);
                Application.UI.addTab(_masterPanelReturnedtStock);
                _masterPanelReturnedtStock.doLayout();
            } else {
                Application.UI.addTab(_masterPanelReturnedtStock);
            }
        },
        addNewStockReturn: function () {

            var resultWindow = new Ext.Window({
                id: "windowRetalineStockReturn",
                iconCls: 'vender-items',
                shadow: false,
                height: 600,
                width: 700,
                title: 'Create Stock Return',
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [{
                        region: 'center',
                        layout: 'fit',
                        height: 150,
                        items: createRetalineStockReturnform()
                    }, {
                        region: 'south',
                        layout: 'fit',
                        height: 450,
                        items: createRetalineStockReturnItemGrid()
                    }],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        handler: function () {
                            Ext.getCmp('windowRetalineStockReturn').close();
                            Ext.getCmp('retalineTransferRequest_main_panel').getStore().load();
                        }

                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        handler: function () {
                            if (Ext.getCmp('retalineStockReturnItemGridPanel').getStore().getCount() > 0) {

                                Ext.Ajax.request({
                                    url: modURL + '&op=saveStockReturn',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.ReturnRequest.Cache.rsuuid
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            Application.ReturnRequest.Cache.rsuuid = '';
                                            Ext.getCmp('salesReturnDamageGrid').getStore().load({
                                                params: {
                                                    branchId: _SESSION.finascop_current_branch_id
                                                }
                                            });
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
        },
        addNewPurchaseReturn: function () {
            var addNewPurchaseReturnWindow = new Ext.Window({
                id: "windowRetalinePurchaseReturn",
                iconCls: 'vender-items',
                shadow: false,
                height: 600,
                width: 800,
                title: 'Create Purchase Return',
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [{
                        region: 'center',
                        layout: 'fit',
                        height: 150,
                        items: createRetalinePurchaseReturnableform()
                    }, {
                        region: 'south',
                        layout: 'fit',
                        height: 400,
                        items: _barcodeDetailsGrid()
                    }],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        handler: function () {
                            Ext.getCmp('windowRetalinePurchaseReturn').close();
                            var branchName = Ext.getCmp('comboxbranchnameRetStock').getValue();
                            Ext.getCmp('panelMasterReturnedtStock').getStore().load({
                                params: {
                                    branchName: branchName
                                }
                            });
                        }

                    }
                ], listeners: {
                    afterrender: function () {

                    }
                }

            });

            addNewPurchaseReturnWindow.doLayout();
            addNewPurchaseReturnWindow.show();
            addNewPurchaseReturnWindow.center();
        },
        createReturnOrder: function (itemarr) {
            var addNewReturnOrderWindow = new Ext.Window({
                id: "windowRetalineReturnOrder",
                iconCls: 'vender-items',
                shadow: false,
                width: winsize.width * 0.8,
                height: winsize.height * 0.8,
                title: 'Return Order',
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [retalineReturnOrderGrid(itemarr), createRetalineReturnOrderform(itemarr)],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowRetalineReturnOrder').close();
                        }

                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_save.png',
                        text: 'Save',
                        handler: function () {
                            var selectitem = Ext.getCmp('panelMasterReturnedtStock').getSelectionModel().getSelections();
                            var selectedcount = selectitem.length;
                            var itemarr = [];
                            var itemReturnCount = 0, itemReturnableCount = 0;
                            for (var i = 0; i < selectedcount; i++)
                            {
                                itemarr.push(selectitem[i].data.stit_id);
                                itemReturnCount += selectitem[i].data.item_count;
                            }
                            var itemInROStore = Ext.getCmp('gridForReturnOrder').getStore().getRange();
                            Ext.each(itemInROStore, function (rec) {
                                itemReturnableCount += parseInt(rec.get('itemReturnable'));
                            });
                            //itemReturnableCount = Ext.getCmp('gridForReturnOrder').getStore().sum('itemReturnable');
                            console.log('itemReturnableCount', itemReturnableCount);
                            if (itemReturnableCount <= itemReturnCount) {
                                var form_data = {
                                    form: Ext.getCmp('returnOrderFormPanel').getForm().getValues(),
                                    itemarr: itemarr
                                };
                                Application.ReturnRequest.Cache.ROItems = itemarr;
                                var params = {
                                    action: 'Create Return Order',
                                    module: 'retaline_return_request',
                                    op: 'saveReturnOrder',
                                    extrainfo: 'Save Return Order',
                                    id: '-'
                                };
                                APICall(params, Application.ReturnRequest.saveReturnOrder, form_data);
                            } else {
                                Ext.MessageBox.alert("Notification", "Return Quantity exceeds.");
                            }

                        }

                    }
                ], listeners: {
                    load: function () {
                        Ext.getCmp('prtoAmount').focus();
                    }
                }

            });

            addNewReturnOrderWindow.doLayout();
            addNewReturnOrderWindow.show();
            addNewReturnOrderWindow.center();
        },
        createStockLoss: function (atemArr, itemLossValue) {
            var addNewStockLossWindow = new Ext.Window({
                id: "windowRetalineStockLoss",
                iconCls: 'vender-items',
                shadow: false,
                height: 150,
                width: 500,
                title: 'Create Stock Loss',
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [new Ext.form.FormPanel({
                        height: 120,
                        frame: true,
                        border: false,
                        id: 'stockLossFormPanel',
                        items: [{
                                xtype: 'numberfield',
                                fieldLabel: 'Actual Value',
                                id: 'prtoLossValue',
                                name: 'prtoLossValue',
                                allowBlank: false,
                                labelAlign: 'top',
                                readOnly: true,
                                anchor: '99%',
                                value: itemLossValue,
                                listeners: {
                                }
                            }
                        ]
                    })],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowRetalineStockLoss').close();
                        }

                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_save.png',
                        text: 'Save',
                        handler: function () {
                            var form_data = {
                                form: Ext.getCmp('stockLossFormPanel').getForm().getValues()
                            };
                            var params = {
                                action: 'Create Stock Loss',
                                module: 'retaline_return_request',
                                op: 'saveStockLoss',
                                extrainfo: 'Save Stock Loss',
                                id: '-'
                            };
                            APICall(params, Application.ReturnRequest.saveStockLoss, form_data);
                        }

                    }
                ], listeners: {
                    load: function () {
                        Ext.getCmp('prtoLossValue').focus();
                    }
                }

            });

            addNewStockLossWindow.doLayout();
            addNewStockLossWindow.show();
            addNewStockLossWindow.center();
        },
        saveStockLoss: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('stockLossFormPanel').getForm();
            var selectitem = Ext.getCmp('panelMasterReturnedtStock').getSelectionModel().getSelections();
            var selectedcount = selectitem.length;
            var stockLossItems = [];
            for (var i = 0; i < selectedcount; i++)
            {
                stockLossItems.push(selectitem[i].data);
            }
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL,
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        op: 'saveStockLoss',
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        stockLossItems: Ext.encode(stockLossItems)
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true) {
                            Ext.getCmp('windowRetalineStockLoss').close();
                            Application.example.msg('Success', tmp.msg);
                            var branchName = Ext.getCmp('textboxBrmCpdDataeditRetSt').getRawValue();
                            Ext.getCmp('panelMasterReturnedtStock').getStore().load({
                                params: {
                                    br_Name: branchName
                                }
                            });

                        } else if (tmp.success === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                            Ext.getCmp('windowRetalineStockLoss').close();
                        } else {
                            Ext.Msg.alert("Error", tmp.msg);
                            Ext.getCmp('windowRetalineStockLoss').close();

                        }
                    },
                    failure: function (elm, conf, action) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            console.log('result', result);
                            Ext.Msg.alert('Error', result.error);
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Error', 'Check the required fields');
            }
        },
        saveReturnOrder: function () {
            var iteminReturnedStock = getMigratedData('gridForReturnOrder');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('returnOrderFormPanel').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL,
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        op: 'saveReturnOrder',
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        itemArray: Ext.encode(Application.ReturnRequest.Cache.ROItems),
                        iteminReturnedStock: iteminReturnedStock
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true) {
                            Ext.getCmp('windowRetalineReturnOrder').close();
                            Application.example.msg('Success', tmp.msg);
                            var branchName = Ext.getCmp('textboxBrmCpdDataeditRetSt').getRawValue();
                            Ext.getCmp('panelMasterReturnedtStock').getStore().load({
                                params: {
                                    br_Name: branchName
                                }
                            });
                        } else if (tmp.success === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                            Ext.getCmp('windowRetalineReturnOrder').close();
                        } else {
                            Ext.Msg.alert("Error", tmp.msg);
                            Ext.getCmp('windowRetalineReturnOrder').close();

                        }
                    },
                    failure: function (elm, conf, action) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            console.log('result', result);
                            Ext.Msg.alert('Error', result.error);
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Error', 'Check the required fields');
            }
        }, viewBarcodeWindow: function (fsto_id, fsto_itemId) {
            var fsto_id = fsto_id;
            var fsto_itemId = fsto_itemId;
            var _viewBarcodeWindow = new Ext.Window({
                title: 'Barcodes',
                layout: 'fit',
                height: 300,
                width: 300,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [viewBarcodeGrid(fsto_id, fsto_itemId)],
                buttons: [{
                        text: 'Close',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 511,
                        handler: function () {
                            _viewBarcodeWindow.close();
                        }
                    }, ]
            });
            _viewBarcodeWindow.doLayout();
            _viewBarcodeWindow.show();
            _viewBarcodeWindow.center();
        },
        addBannedStock: function () {
            var resultWindow = new Ext.Window({
                id: "windowRetalineStockBanned",
                iconCls: 'vender-items',
                shadow: false,
                height: 600,
                width: 700,
                title: 'Return Banned Batches',
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [createRetalineBannedStockGrid()],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        handler: function () {
                            Ext.getCmp('windowRetalineStockBanned').close();
                        }

                    }, {
                        text: 'Add',
                        iconCls: 'finascop_add',
                        handler: function () {
                            var mainLength = Ext.getCmp('retalineStockBannedGridPanel').getSelectionModel().getSelections().length;
                            if (mainLength > 0) {
                                if ((_SESSION.br_PyramidLevel == 4) || (_SESSION.br_PyramidLevel == 3)) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=addBannedItemtoStockReturn',
                                        method: 'POST',
                                        params: {
                                            uuid: Application.ReturnRequest.Cache.banneduuid,
                                            bannedItemIds: Application.ReturnRequest.Cache.bannedItemIds,
                                            bannedItemRecords: Application.ReturnRequest.Cache.bannedItemRecords
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', tmp.msg);
                                                Application.ReturnRequest.Cache.rsuuid = '';
                                                Ext.getCmp('salesReturnDamageGrid').getStore().load({
                                                    params: {
                                                        branchId: _SESSION.finascop_current_branch_id
                                                    }
                                                });
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
                                } else {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=addBannedItemtoStockReturninCS',
                                        method: 'POST',
                                        params: {
                                            uuid: Application.ReturnRequest.Cache.banneduuid,
                                            bannedItemIds: Application.ReturnRequest.Cache.bannedItemIds,
                                            bannedItemRecords: Application.ReturnRequest.Cache.bannedItemRecords
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', tmp.msg);
                                                Application.ReturnRequest.Cache.rsuuid = '';
                                                Ext.getCmp('salesReturnDamageGrid').getStore().load({
                                                    params: {
                                                        branchId: _SESSION.finascop_current_branch_id
                                                    }
                                                });
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

                            } else
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
        }, chooseBannedItems: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var gr = Ext.getCmp('retalineStockBannedGridPanel');
            var str = gr.getStore().getRange();
            var bannedItemIds = [];
            var bannedItemRecords = [];
            Ext.each(str, function (r) {
                if (r.get('checked') == 'true') {
                    bannedItemRecords.push(r.data);
                    bannedItemIds.push(r.get('stiid_itemmasterid'));
                }
            });
            var bannedItemIds = Ext.encode(bannedItemIds);
            var bannedItemRecords = Ext.encode(bannedItemRecords);
            Application.ReturnRequest.Cache.bannedItemIds = bannedItemIds;
            Application.ReturnRequest.Cache.bannedItemRecords = bannedItemRecords;
        }
    };
}();