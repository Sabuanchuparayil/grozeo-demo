Application.RetalineHoldOrders = function () {
    var winLoadMask;
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 18;
    var modURL = '?module=retaline_hold_orders';
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var holdPrescriptionPanel = function (id, _type) {
        var src = '?module=retaline_hold_orders';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Orders on Hold',
            items: [manageHoldOrderGrid(),
                new Ext.Panel({
                    region: 'east',
                    frame: false,
                    border: true,
                    layout: 'fit',
                    autoScroll: false,
                    title: 'Order Details',
                    bodyStyle: {"background-color": "white"},
                    id: 'retholdorders_manage_parent_panel',
                    width: winsize.width * 0.45,
                    items: [{
                            id: 'retalineorder_hold_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_retalinehold_orderdtls" name="iframe_retalinehold_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };
    var manageHoldOrderGrid = function () {

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
        var order_store = manageHoldOrderStore();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            region: 'center',
            frame: true,
            layout: 'fit',
            width: winsize.width * 0.6,
            border: false,
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
            id: 'manage_order_retalinehold_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
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
                }, {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Vendor',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                },
                {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                }, {
                    header: 'Total Items',
                    sortable: true,
                    dataIndex: 'itemCount',
                    tooltip: 'Total Items',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Items Short',
                    sortable: true,
                    dataIndex: 'itemShortCount',
                    tooltip: 'Items Short',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Order Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'poNumber',
                    tooltip: 'PO Number',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            productActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
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
            }), tbar: [],
            listeners: {
                resize: onGridResize,
                afterrender: function () {
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
    var ordergridSelectionChanged = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.RetalineHoldOrders.Cache.orderAutoId = ID;
            Application.RetalineHoldOrders.UpdateViewMode(ID);
        }
    };
    var qtipRenderer = function (value, metadata, record, rowIndex, colIndex, store) {
        metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
        return value;
    };
    var manageHoldOrderStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listHoldOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on', 'itemCount', 'order_methodName',
                'quor_Type', 'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time',
                'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'quor_DeliveryMethodsAllowed', 'order_method', 'prescStatusId', 'poNumber', 'itemShortCount'
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

                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_retalinehold_grid').getView().refresh();
                    Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.RetalineHoldOrders.Cache.orderAutoId = ID;
                        Application.RetalineHoldOrders.UpdateViewMode(Application.RetalineHoldOrders.Cache.orderAutoId);
                    }
                }
            }
        });
        return store;
    };
    var productActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Remove Hold",
                handler: function () {
                    var order_id = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
                    Ext.Ajax.request({
                        url: modURL + '&op=removeHold',
                        method: 'POST',
                        params: {order_id: order_id},
                        success: function (res) {
                            var res = Ext.decode(response.responseText);
                            if (res.success === true)
                            {
                                Ext.MessageBox.alert('Success', res.msg, function (btn) {
                                    Ext.getCmp('manage_order_retalinehold_grid').getStore().load();
                                });
                            } else
                            {
                                Ext.MessageBox.alert('Failed');
                            }
                        },
                        failure: function () {
                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                        }
                    });
                }
            }, {
                text: "Manually Initiate",
                handler: function () {
                    var order_id = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
                    Ext.Ajax.request({
                        url: modURL + '&op=mauallyInitiatePO',
                        method: 'POST',
                        params: {order_id: order_id},
                        success: function (res) {
                            var res = Ext.decode(response.responseText);
                            if (res.success === true)
                            {
                                Ext.MessageBox.alert('Success', res.msg, function (btn) {
                                    Ext.getCmp('manage_order_retalinehold_grid').getStore().load();
                                });
                            } else
                            {
                                Ext.MessageBox.alert('Failed');
                            }
                        },
                        failure: function () {
                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                        }
                    });
                }
            }]
    });
    return{
        Cache: {},
        initHoldOrders: function () {
            var _panelId = 'retaline_holdorder_panel';
            var _type = 'Hold';
            var _prescriptionHoldPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prescriptionHoldPanel)) {
                _prescriptionHoldPanel = holdPrescriptionPanel(_panelId, _type);
                Application.UI.addTab(_prescriptionHoldPanel);
                _prescriptionHoldPanel.doLayout();
            } else {
                Application.UI.addTab(_prescriptionHoldPanel);
                _prescriptionHoldPanel.doLayout();
            }
        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('retalineorder_hold_details_view_panel').show();

            Ext.get('iframe_retalinehold_orderdtls').dom.src = modURL + '&op=holdorder_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        }
    }
}();