/*
 * Created by Lekshmi VS
 * On feb 3, 2016
 * */

Application.OrderProcessing = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var transferRulesLoadedForm = null;
    var modURL = '?module=order_processing';
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
    var gridSelectionChanged = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('order_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('order_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.OrderProcessing.Cache.order_id = ID;
            Application.OrderProcessing.ViewMode(ID);
        }
    };

    var ordergridSelectionChanged = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.OrderProcessing.Cache.orderAutoId = ID;
            Application.OrderProcessing.UpdateViewMode(ID);
        }
    };
    var ordergridSelectionChangedb2cso = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_id;
            Application.OrderProcessing.Cache.orderAutoIdb2cso = ID;
            Application.OrderProcessing.UpdateViewModeB2CSO(ID);
        }
    };

    var DetailsViewpanel = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            hidden: true,
            id: 'details_view_panel_order',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="99%" class="details_view_table">',
                    '<tr><th>Name</th><td>  {member_name}</td></tr>',
                    '<tr><th>DOB</th><td> {member_dob}</td></tr>',
                    '<tr><th>Gender</th><td>  {member_gender}</td></tr>',
                    '<tr><th>Address Line 1</th><td>  {member_address1}</td></tr>',
                    '<tr><th>Address Line 2</th><td>  {member_address2}</td></tr>',
                    '<tr><th>Email</th><td>  {member_email}</td></tr>',
                    '<tr><th>Phone</th><td>  {member_phone}</td></tr>',
                    '<tr><th>Member Plan </th><td>  {mst_plan_name}</td></tr>',
                    '<tr><th>Plan Amount</th><td>  {online_payment_amount}</td></tr>',
                    '<tr><th>Aadhar No</th><td>  {member_aadhar}</td></tr>',
                    '<tpl if="member_passport != \'\'"><tr><th>Passport No</th><td>  {member_passport}</td></tr></tpl>',
                    '<tr><th>Nominee Name</th><td>  {member_nominee}</td></tr>',
                    '<tr><th>Nominee Relation</th><td>  {member_nominee_relation}</td></tr>',
                    '<tr><th>Payment Status</th><td>  {payment_status}</td></tr>',
                    '</table>',
                    '</div>'),
        });
    };
    var topPanel = function (id) {
        var src = '?module=order_processing&op=order_details&order_auto_id=' + Application.OrderProcessing.Cache.order_auto_id;
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Transactions',
            iconCls: 'onlineorders',
            items: [orderGrid(),
                new Ext.Panel({
                    region: 'east',
                    frame: false,
                    border: true,
                    layout: 'fit',
                    autoScroll: false,
                    title: ' Online Order Details',
                    bodyStyle: {"background-color": "white"},
                    id: 'order_parent_panel',
                    width: winsize.width * 0.45,
                    /*items: [DetailsViewpanel()
                     ]*/
                    items: [{
                            id: 'details_view_panel_order',
                            hidden: true,
                            html: '<iframe id="iframe_productdtls" name="iframe_productdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };
    var orderStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listorders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_auto_id',
                root: 'data'
            }, ['order_auto_id', 'order_generated_id', 'order_status_sort_order', 'order_user_type', 'payment_by', 'ordertime',
                'cust_mobile', 'order_total_amount', 'order_created_on', 'order_status', 'order_tax'
            ]),
            sortInfo: {
                field: 'order_auto_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (store, e) {
                    Ext.getCmp('order_grid').getView().refresh();
                    Ext.getCmp('order_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('order_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('order_grid').getSelectionModel().getSelections()[0].data.order_auto_id;
                        Application.OrderProcessing.Cache.order_auto_id = ID;
                        Application.OrderProcessing.ViewMode(Application.OrderProcessing.Cache.order_auto_id);
                    }
                }
            }
        });
        return store;
    };


    var orderGrid = function () {
        var hfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'order_generated_id'
                }, {
                    type: 'date',
                    dataIndex: 'order_created_on'
                }, {
                    type: 'string',
                    dataIndex: 'payment_by'
                }, {
                    type: 'list',
                    options: ['Customer', 'BA'],
                    phpMode: true,
                    dataIndex: 'order_user_type'
                }, {
                    type: 'numeric',
                    dataIndex: 'order_total_amount'
                }, {
                    type: 'list',
                    options: ['Order Created', 'Order Success', 'Order Failed', 'Order Processing', 'Ready to Dispatch', 'Dispatched', 'Delivered'],
                    phpMode: true,
                    dataIndex: 'order_status'
                }]
        });
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = orderStore();
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
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters],
            id: 'order_grid',
            fields: ['order_auto_id', 'order_generated_id', 'order_status_sort_order', 'order_user_type', 'cust_mobile', 'ordertime',
                'order_total_amount', 'order_created_on', 'order_status', 'order_tax'
            ],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_generated_id',
                    tooltip: 'Order ID',
                    renderer: qtipRenderer
                }, {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    renderer: qtipRenderer
                }, {
                    header: 'Time',
                    sortable: true,
                    dataIndex: 'ordertime',
                    tooltip: 'Time',
                    renderer: qtipRenderer
                }, {
                    header: 'Payment By',
                    sortable: true,
                    dataIndex: 'payment_by',
                    tooltip: 'Payment By',
                    renderer: qtipRenderer
                }, {
                    header: 'User Type',
                    sortable: true,
                    dataIndex: 'order_user_type',
                    tooltip: 'User Type',
                    renderer: qtipRenderer
                }, {
                    header: 'Amount',
                    sortable: true,
                    dataIndex: 'order_total_amount',
                    tooltip: 'Amount',
                    align: 'right',
                    renderer: function (value) {
                        return (isNaN(value)) ? value : formatNumbertofixtothreedecimal(value);

                    }
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: ' Order Status',
                    renderer: qtipRenderer
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('order_auto_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.OrderProcessing.Cache.order_auto_id = ID;
                        Application.OrderProcessing.ViewMode(Application.OrderProcessing.Cache.order_auto_id);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    order_store.load();
                }
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: order_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };

    var orderTopPanel = function (id) {

        var src = '?module=order_processing';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Retail Orders',
            items: [manageOrderGrid(),
                new Ext.Panel({
                    region: 'east',
                    frame: false,
                    border: true,
                    layout: 'fit',
                    autoScroll: false,
                    title: 'Order Details',
                    collapsible: true,
                    collapseMode: 'mini',
                    collapsed: true,
                    bodyStyle: {"background-color": "white"},
                    id: 'order_manage_parent_panel',
                    width: winsize.width * 0.4,
                    items: [{
                            id: 'order_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_orderdtls" name="iframe_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };


    var manageOrderStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_payment_gateway_refid', 'order_payment_gateway_refid_crc32', 'order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to',
                {
                    name: 'order_created_on',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                },
                'dispatch_courier', 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified'
            ]),
            sortInfo: {
                field: 'order_created_on',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    var branchName = Ext.getCmp('comboxbranchOrder').getRawValue();
                    if (branchName != '') {
                        this.baseParams.br_Name = branchName;
                    }
                    this.baseParams.isb2cso = 0;
                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_grid').getView().refresh();
                    Ext.getCmp('manage_order_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.OrderProcessing.Cache.orderAutoId = ID;
                        Application.OrderProcessing.UpdateViewMode(Application.OrderProcessing.Cache.orderAutoId);
                    }
                }
            }
        });
        return store;
    };

    var userlog_window = function () {
        var order_generated_id = arguments[0];
        var order_auto_id = arguments[1];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var src = '?module=order_processing&op=oreder_userlog_dtlsview&order_auto_id=' + order_auto_id + '&order_generated_id=' + order_generated_id + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;
        var oreder_userlog_win = Ext.getCmp('ORDER_userlog_win');
        if (Ext.isEmpty(oreder_userlog_win)) {
            var oreder_userlog_win = new Ext.Window({
                id: 'ORDER_userlog_win',
                title: 'Order History ::' + order_generated_id,
                iconCls: 'userlog',
                modal: true,
                height: 400,
                width: 700,
                shadow: false,
                resizable: false,
                items: [
                    new Ext.Panel({
                        layout: 'fit',
                        border: false,
                        width: 700,
                        autoScroll: true,
                        // id: 'oreder_userlog_dtlsview',
                        items: [{
                                region: 'center',
                                border: false,
                                html: '<iframe src="' + src +
                                        '" id="iframeRequest" name="iframeRequest" ' +
                                        'width="100%" height="100%" style="border:none">'
                            }]
                    })
                ],
                buttons: [{
                        iconCls: 'my-icon61',
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        tabIndex: 130,
                        handler: function () {
                            Ext.getCmp('ORDER_userlog_win').close();
                        }
                    }]
            });
        }

        oreder_userlog_win.doLayout();
        oreder_userlog_win.show();
        oreder_userlog_win.center();
//        if (order_auto_id > 0) {
//            Ext.getCmp('oreder_userlog_dtlsview').load({
//                url: '?module=order_processing' + '&op=oreder_userlog_dtlsview',
//                params: {
//                    order_auto_id: order_auto_id,
//                    order_generated_id: order_generated_id
//                },
//                discardUrl: false,
//                nocache: true,
//                callback: function () {
//
//                },
//                text: 'Loading...',
//                timeout: 30,
//                scripts: false
//            });
//        }
    };
    var packetsCount = function (order_id, order_order_id, bagsCount) {
        var t = new Date();
        var t_stamp = t.format("YmdHis")
        var packetsCountWindow = Ext.getCmp('packetsCountWindow');
        if (Ext.isEmpty(packetsCountWindow)) {
            packetsCountWindow = new Ext.Window({
                id: 'packetsCountWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Print Count',
                width: 250,
                autoHeight: true,
                //items: [packetsCountPanel],
                items: [{
                        layout: 'form',
                        columnWidth: 0.25,
                        labelAlign: 'top',
                        items: [{
                                fieldLabel: 'No.of prints',
                                xtype: 'textfield',
                                id: 'packetsCounts',
                                name: 'packetsCounts',
                                value: bagsCount,
                                allowBlank: false,
                                tabIndex: 504,
                            }]
                    }],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon61',
                        handler: function () {
                            packetsCountWindow.close();
                        }
                    }, {
                        text: 'Print Slip',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        iconCls: 'my-icon141',
                        handler: function () {
                            var printCount = Ext.getCmp('packetsCounts').getValue();
                            printPackingSlip(order_id, order_order_id, printCount);
                            Ext.getCmp('packetsCountWindow').close()
                        }
                    }],
            });
        }
        packetsCountWindow.doLayout();
        packetsCountWindow.show();
        packetsCountWindow.center();
    };
    var manageOrderGrid = function () {

        var actionItems = [];

//        actionItems.push({
//            tooltip: 'Print Order',
//            getClass: function (v, meta, rec) {
//                if (rec.get('status') >= 4) {
//                    return 'print_invoice';
//                } else {
//                    return 'hideicon';
//                }
//            },
//            handler: function (grid, rowIndex, colIndex) {
//                var record = grid.store.getAt(rowIndex);
//                console.log(record);
//                var order_generated_id = record.data.order_order_id;
//                printOrder(record.get('order_id'), order_generated_id);
//            }
//        });

        actionItems.push({
            tooltip: 'Print Packing Slip',
            getClass: function (v, meta, rec) {
                if (rec.get('status') >= 9) {
                    return 'my-icon40';

                } else {
                    var id = rec.get('status');
                    console.log(id);
                    return 'hideicon';
                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);

                if (record.get('status') < 4) {
                    Ext.MessageBox.alert('Notification', 'Please print the order to enable this action.');
                } else {
//                    printPackingSlip(record.get('order_id'), record.get('order_order_id'));
                    packetsCount(record.get('order_id'), record.get('order_order_id'), record.get('order_packedbags_count'));
                }
            }
        });


        actionItems.push({
            tooltip: 'Print Invoice',
            getClass: function (v, meta, rec) {
                if ((rec.get('status') >= 4)) {
                    return 'print_invoice';

                } else {
                    return 'hideicon';
                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var record = grid.store.getAt(rowIndex);
                var order_auto_id = record.data.order_id;
                var order_generated_id = record.data.order_order_id;
                printInvoice(order_auto_id, order_generated_id);
                //postToUrl(modURL + '&op=downloadInvoice' + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp, {order_auto_id: order_auto_id, order_generated_id: order_generated_id});
            }
        });
        actionItems.push({
            getClass: function (v, meta, rec) {
//                if ((rec.get('order_status_sort_order') < 8) && (rec.get('order_status_sort_order') > 6)) {
//                    //this.items[3].tooltip = 'Enter Delivery Details';
//                    //this.items[<?php echo $count; ?>].tooltip = 'Enter Delivery Details';
//                    return 'delivery';
//                } else if (rec.get('order_status_sort_order') > 7) {
//                    //this.items[3].tooltip = 'View Delivery Details';
//                    //this.items[<?php echo $count; ?>].tooltip = 'View Delivery Details';
//                    return 'delivery';
//                } else {
                return 'hideicon';
//                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                if (record.get('order_status_sort_order') < 7) {
                    Ext.MessageBox.alert('Notification', 'Please enter dispatch details to enable this action.');
                } else {
                    updateDelivery(record.get('order_id'), record.get('delivery_at_date'), record.get('delivery_at_time'), record.get('delivery_notes'));
                }
            }
        });



        actionItems.push({
            tooltip: 'Order History',
            iconCls: 'application_view_detail',
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                userlog_window(record.data.order_order_id, record.data.order_id);
            }
        });
        actionItems.push({
            tooltip: 'Delivery',
            getClass: function (v, meta, rec) {
                // this.items[<?php echo $count; ?>].tooltip = 'View Userlog';
                //console.log(rec.get('status'));
                if ((rec.get('status') == 9) || (rec.get('status') == 10) || (rec.get('status') == 11) || (rec.get('status') == 12) || (rec.get('status') == 13) || (rec.get('status') == 14)) {
                    return 'dispatch';
                } else {
                    return 'hideicon';
                }

            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                var order_id = record.get('order_id');
                Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status ?', function (btn, text) {
                    if (btn == 'yes') {
                        Ext.Ajax.request({
                            waitMsg: 'Processing',
                            url: modURL,
                            params: {
                                op: 'deliverManually',
                                order_id: order_id
                            },
                            failure: function (response, options) {
                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                            },
                            success: function (response, options) {
                                var tmp = Ext.decode(response.responseText);
                                console.log('tmp', tmp);
                                if (tmp.status == 'ok') {
                                    Application.example.msg('Success', tmp.msg);
                                    grid.getStore().load();

                                } else {
                                    Ext.Msg.alert("Error", tmp.error.msg);
                                }
                            }
                        });
                    }
                });

            }
        });
        actionItems.push({
            tooltip: 'Returned Items',
            getClass: function (v, meta, rec) {
                if ((rec.get('order_HasReturn') == 1)) {
                    //return 'return-back';
                    return 'hideicon';

                } else {
                    return 'hideicon';
                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                getReturnedItems(record.get('order_id'), record.get('order_order_id'));
            }
        });

        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: brmodURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

        var getReturnedItems = function (order_id, order_order_id) {
            var _addnewItemsWindow = new Ext.Window({
                title: 'Item Details',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 600,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_returnedItemsGrid(order_id, order_order_id)],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        handler: function () {
                            _addnewItemsWindow.close();
                            if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                                Ext.getCmp('manage_order_grid').getStore().load();
                            }
                        }

                    }
                ]

            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        };
        var check_box = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true
        });
        var _returnedItemsGrid = function (order_id, order_order_id) {

            var _dispatchgridPanel = new Ext.grid.GridPanel({
                region: 'center',
                layout: 'fit',
                frame: false,
                border: false,
                loadMask: true,
                store: _bcdItemsStore(order_id, order_order_id),
                iconCls: 'money',
                autoScroll: true,
                width: winsize.width * 0.4,
                height: 300,
                bodyStyle: {"background-color": "white"},
                id: 'gridpanelforReturnredItems',
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
                        header: 'Barcode',
                        dataIndex: 'stiid_barcode',
                        sortable: true,
                        align: 'right',
                        tooltip: 'Barcode',
                        hideable: false
                    },
                    {
                        header: 'Price',
                        dataIndex: 'item_sales_price',
                        sortable: true,
                        align: 'right',
                        tooltip: 'Price',
                        hideable: false
                    }, {
                        xtype: 'actioncolumn',
                        header: 'Actions',
                        hideable: false,
                        groupable: false,
                        width: 80,
                        items: [
                            {
                                tooltip: 'Move Item to Stock',
                                getClass: function (v, meta, rec) {
                                    console.log('returnCount', typeof (rec.get('returnCount')));
                                    if ((rec.get('returnCount') == 0)) {
                                        return 'assign';

                                    } else {
                                        return 'hideicon';
                                    }

                                },
                                handler: function (grid, rowIndex, colIndex) {
                                    var grecord = grid.store.getAt(rowIndex);
                                    console.log('grecord', grecord);
                                    Ext.Ajax.request({
                                        url: modURL + '&op=moveRetunItemstoStock',
                                        method: 'POST',
                                        params: {
                                            order_id: order_id,
                                            stiid_barcode: grecord.data.stiid_barcode,
                                            order_order_id: order_order_id,
                                            itemId: grecord.data.itemId,
                                            stiid_id: grecord.data.stiid_id,
                                        },
                                        success: function (response) {

                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success == true) {
                                                Application.example.msg('Success', tmp.msg);
                                                Ext.getCmp('gridpanelforReturnredItems').store.load({
                                                    params: {
                                                        order_id: order_id,
                                                        order_order_id: order_order_id
                                                    }
                                                });


                                            } else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }

                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        }
                                    });

                                }
                            }
                        ]

                    }
                ],
                viewConfig: {
                    forceFit: true
                }, sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true
                }),
                listeners: {
                    afterrender: function () {
                        Ext.getCmp('gridpanelforReturnredItems').getStore().load({
                            params: {
                                order_id: order_id,
                                order_order_id: order_order_id
                            }
                        });
                    }
                }
            });
            return _dispatchgridPanel;
        };
        var _bcdItemsStore = function (order_id, order_order_id) {
            var _Store = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listItemsinReturnedOrder',
                    method: 'post'
                }),
                fields: ['order_id', 'stiid_barcode', 'itemId', 'itemName', 'stiid_id', 'item_sales_price', 'returnCount'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.order_id = order_id;
                        this.baseParams.order_order_id = order_order_id;
                    }
                }
            });
            return _Store;
        };

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
                    dataIndex: 'br_Name'
                }, {
                    type: 'list',
                    options: ORDER_STATUS_OPTIONS,
                    phpMode: true,
                    dataIndex: 'order_status'
                }]
        });
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = manageOrderStore();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            region: 'center',
            frame: true,
            //width: winsize.width * 0.6,
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
            id: 'manage_order_grid',
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
                    renderer: qtipRenderer
                }, {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                }, {
                    header: 'Time',
                    sortable: false,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                    renderer: qtipRenderer
                }, {
                    header: 'Delivery To',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Delivery To',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'PayGatewayId',
                    sortable: true,
                    dataIndex: 'order_payment_gateway_refid',
                    tooltip: 'PayGatewayId',
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
                            var status = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.status;
                            var order_HasReturn = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_HasReturn;
                            onlineorderActionMenu(e, status, order_HasReturn);
                        }
                    }
                },
                /*{
                 xtype: 'actioncolumn',
                 header: 'Actions',
                 hideable: false,
                 sortable: false,
                 width: 260,
                 items: actionItems
                 }*/
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
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdOrder',
                    name: 'textboxBrmCpdOrder',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchOrder',
                    name: 'comboxbranchOrder',
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
                    hiddenName: 'comboxbranchOrder',
                    listeners: {
                        select: function () {
                            Ext.getCmp('manage_order_grid').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    id: 'showBtn',
                    style: "padding-left: 10px;",
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchOrder').getRawValue();
                        Ext.getCmp('manage_order_grid').getStore().load({
                            params: {
                                br_Name: branchName
                            }
                        });
                    }
                }],
            listeners: {
                /*  cellClick: function (grid, rowIndex, columnIndex, e) {
                 var record = grid.getStore().getAt(rowIndex);
                 var ID = record.get('order_auto_id');
                 if (!Ext.isEmpty(ID)) {
                 Application.OrderProcessing.Cache.orderAutoId = ID;
                 Application.OrderProcessing.UpdateViewMode(Application.OrderProcessing.Cache.orderAutoId);
                 }
                 },*/
                resize: onGridResize,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchOrder").show();
                        Ext.getCmp("textboxBrmCpdOrder").hide();
                    }
                    else {
                        Ext.getCmp("textboxBrmCpdOrder").show();
                        Ext.getCmp("textboxBrmCpdOrder").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchOrder").hide();
                        Ext.getCmp('showBtn').hide();
                    }
                    if (Ext.getCmp('textboxBrmCpdOrder').getValue() != '')
                    {
                        order_store.baseParams = {
                            current_branch_id: _SESSION.finascop_current_branch_id,
                            isb2cso: 0
                        }
                        order_store.load();
                    }
                }, celldblclick: function () {
                    Ext.getCmp('order_manage_parent_panel').expand(true)
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
    var onlineorderActionMenu = function (e, status, order_HasReturn) {
        var onlineorderActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Print Packing Slip",
                    hidden: !(status >= 9),
                    handler: function () {
                        var record = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data;
                        //var order_id = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_id;
                        //var order_order_id = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_order_id;
                        if (record.status < 4) {
                            Ext.MessageBox.alert('Notification', 'Please print the order to enable this action.');
                        } else {
//                    printPackingSlip(record.get('order_id'), record.get('order_order_id'));
                            packetsCount(record.get('order_id'), record.get('order_order_id'), record.get('order_packedbags_count'));
                        }
                    }
                }, {
                    text: "Print Invoice",
                    hidden: !(status >= 4),
                    handler: function () {
                        var order_id = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_id;
                        var order_order_id = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_order_id;
                        printInvoice(order_id, order_order_id);
                    }
                },
                {
                    text: "Order History",
                    handler: function () {
                        var order_order_id = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_order_id;
                        var order_id = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data.order_id;
                        userlog_window(order_order_id, order_id);
                    }
                }, {
                    text: "Delivery",
                    hidden: !((status == 9) || (status == 10) || (status == 11) || (status == 12) || (status == 13) || (status == 14)),
                    handler: function () {
                        var record = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data;
                        var order_id = record.get('order_id');
                        Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status ?', function (btn, text) {
                            if (btn == 'yes') {
                                Ext.Ajax.request({
                                    waitMsg: 'Processing',
                                    url: modURL,
                                    params: {
                                        op: 'deliverManually',
                                        order_id: order_id
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    },
                                    success: function (response, options) {
                                        var tmp = Ext.decode(response.responseText);
                                        console.log('tmp', tmp);
                                        if (tmp.status == 'ok') {
                                            Application.example.msg('Success', tmp.msg);
                                            grid.getStore().load();

                                        } else {
                                            Ext.Msg.alert("Error", tmp.error.msg);
                                        }
                                    }
                                });
                            }
                        });
                    }
                }, {
                    text: "Returned Items",
                    hidden: !(order_HasReturn == 1),
                    handler: function () {
                        var record = Ext.getCmp('manage_order_grid').getSelectionModel().getSelections()[0].data;
                        getReturnedItems(record.get('order_id'), record.get('order_order_id'));
                    }
                }]
        });
        onlineorderActionMenu.showAt(e.getXY());
    };
    var createprintInvoicePanel = function (order_id, order_order_id) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var src = '?module=order_processing&op=downloadInvoice&order_id=' + order_id + '&order_order_id=' + order_order_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            width: winsize.width * 0.9,
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
    var createprintOrderPanel = function (order_id, mode, order_order_id) {
        var t = new Date();

        var t_stamp = t.format("YmdHis");
        var src = '?module=order_processing&op=orderPrint&order_id=' + order_id + '&order_order_id=' + order_order_id + '&mode=' + mode + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
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

    var createthermalPanel = function (order_id, order_generated_id) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");

        var src = '?module=order_processing&op=thermalsticker&order_id=' + order_id + '&order_generated_id=' + order_generated_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;

        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 300,
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

    var printInvoice = function (order_auto_id, order_generated_id) {
        var printOrderPanel = createprintInvoicePanel(order_auto_id, order_generated_id);
        var printOrderWindow = Ext.getCmp('printOrderWindow');
        if (Ext.isEmpty(printOrderWindow)) {
            printOrderWindow = new Ext.Window({
                id: 'printOrderWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Print Invoice',
                width: winsize.width * 0.9,
                autoHeight: true,
                layout: 'fit',
                items: [printOrderPanel],
                buttons: [{
                        text: 'Cancel',
                        iconCls: 'my-icon61',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        handler: function () {
                            printOrderWindow.close();
                        }
                    },
                    {
                        text: 'Print',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        iconCls: 'my-icon141',
                        handler: function () {
                            var params = {
                                order_id: order_auto_id,
                                action: 1
                            };
                            updateOrderStatus(params);
                            iframeRequest.focus();
                            iframeRequest.print();
                            printOrderWindow.close();
                        }
                    }],
                listeners: {
                    close: function () {
                        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                            Ext.getCmp('manage_order_grid').getStore().load();
                        }
                    }
                }
            });
        }
        printOrderWindow.doLayout();
        printOrderWindow.show();
        printOrderWindow.center();
    };
    var printOrder = function (order_id, order_generated_id) {
        var printOrderPanel = createprintOrderPanel(order_id, 'printOrder', order_generated_id);
        var printOrderWindow = Ext.getCmp('printOrderWindow');
        if (Ext.isEmpty(printOrderWindow)) {
            printOrderWindow = new Ext.Window({
                id: 'printOrderWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Order Details',
                width: 950,
                autoHeight: true,
                items: [printOrderPanel],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon61',
                        handler: function () {
                            printOrderWindow.close();
                        }
                    }, {
                        text: 'Print',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        iconCls: 'my-icon141',
                        handler: function () {
                            var params = {
                                order_id: order_id,
                                action: 1
                            };
                            updateOrderStatus(params);
                            iframeRequest.focus();
                            iframeRequest.print();
                            printOrderWindow.close();
                        }
                    }],
                listeners: {
                    close: function () {
                        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                            Ext.getCmp('manage_order_grid').getStore().load();
                        }
                    }
                }
            });
        }
        printOrderWindow.doLayout();
        printOrderWindow.show();
        printOrderWindow.center();
    };

    // Added By Iqbal for thermal sticker

    var printThermal = function (order_id, order_generated_id) {
        var printThermalPanel = createthermalPanel(order_id, order_generated_id);
        var printThermal = Ext.getCmp('printThermal');
        if (Ext.isEmpty(printThermal)) {
            printThermal = new Ext.Window({
                id: 'printThermal',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Thermal Sticker',
                width: 950,
                autoHeight: true,
                items: [printThermalPanel],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon61',
                        handler: function () {
                            printThermal.close();
                        }
                    }, {
                        text: 'Print',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        iconCls: 'my-icon141',
                        handler: function () {
                            var params = {
                                order_id: order_id,
                                action: 1
                            };
                            //  updateOrderStatus(params);
                            iframeRequest.focus();
                            iframeRequest.print();
                            printThermal.close();
                        }
                    }],
                listeners: {
                    close: function () {
                        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                            Ext.getCmp('manage_order_grid').getStore().load();
                        }
                    }
                }
            });
        }
        printThermal.doLayout();
        printThermal.show();
        printThermal.center();
    };


    var createprintSlipPanel = function (order_id, order_generated_id, printCount) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var src = '?module=order_processing&op=orderPrintSlip&order_id=' + order_id + '&order_generated_id=' + order_generated_id + '&printCount=' + printCount + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            id: 'printSlipPanel',
            items: [{
                    region: 'center',
                    border: false,
                    html: '<iframe src="' + src +
                            '" id="iframeSlip" name="iframeSlip" ' +
                            'width="100%" height="100%" style="border:none">'
                }]
        });
        return myPanel;
    };

    var printPackingSlip = function (order_id, order_generated_id, printCount) {
        var printSlipPanel = createprintSlipPanel(order_id, order_generated_id, printCount);
        var printSlipWindow = Ext.getCmp('printSlipWindow');
        if (Ext.isEmpty(printSlipWindow)) {
            printSlipWindow = new Ext.Window({
                id: 'printOrderWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Packing Slip',
                width: 950,
                autoHeight: true,
                items: [printSlipPanel],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon61',
                        handler: function () {
                            printSlipWindow.close();
                        }
                    }, {
                        text: 'Print',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        iconCls: 'my-icon141',
                        handler: function () {
                            var params = {
                                order_id: order_id,
                                action: 2
                            };
                            updateOrderStatus(params);
                            iframeSlip.focus();
                            iframeSlip.print();
                        }
                    }],
                listeners: {
                    close: function () {
                        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                            Ext.getCmp('manage_order_grid').getStore().load();
                        }
                    }
                }
            });
        }
        printSlipWindow.doLayout();
        printSlipWindow.show();
        printSlipWindow.center();
    };

    var updateDispatch = function (order_id, courier, dispatch_consignment, cust_mobile, delivery_to, order_generated_id) {
        var disp_win = Ext.getCmp('disp_win');
        if (Ext.isEmpty(disp_win)) {
            disp_win = new Ext.Window({
                id: 'disp_win',
                title: 'Dispatch Details',
                layout: 'fit',
                autoHeight: true,
                width: 500,
                autoScroll: true,
                plain: false,
                constrain: true,
                modal: true,
                frame: true,
                resizable: false,
                shadow: false,
                items: [new Ext.FormPanel({
                        frame: false,
                        border: false,
                        bodyStyle: {"background-color": "white", "padding": "5px"},
                        hideBorders: true,
                        id: 'disp_form',
                        labelAlign: 'top',
                        height: 110,
                        items: [mkCombo({
                                "type": 'mst_courier',
                                "value": "mst_courier_id",
                                "display": "mst_courier_name",
                                "name": "order_dispatch_courier",
                                "fieldLabel": "Courier",
                                "emptyText": "Select Courier Company..",
                                "id": "order_dispatch_courier",
                                "listeners": false,
                                allowBlank: false,
                                anchor: '98%',
                                org_value: courier
                            }), {
                                xtype: 'textfield',
                                fieldLabel: 'Consignment No.',
                                id: 'order_dispatch_consignment',
                                name: 'order_dispatch_consignment',
                                allowBlank: false,
                                anchor: '98%',
                                value: dispatch_consignment
                            }]
                    })],
                buttons: [{
                        text: "Close",
                        id: 'cancel_btn_content',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        tabIndex: 140,
                        handler: function () {
                            disp_win.close();
                        }
                    }, {
                        text: "Save",
                        id: 'save_btn_content',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        handler: function () {
                            if (Ext.getCmp('disp_form').getForm().isValid()) {
                                var params = {
                                    order_id: order_id,
                                    order_dispatch_courier: Ext.getCmp('order_dispatch_courier').getValue(),
                                    order_dispatch_consignment: Ext.getCmp('order_dispatch_consignment').getValue(),
                                    action: 3,
                                    cust_mobile: cust_mobile,
                                    delivery_to: delivery_to,
                                    order_generated_id: order_generated_id
                                };
                                updateOrderStatus(params);
                            }
                        }
                    }],
                listeners: {
                    close: function () {
                        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                            Ext.getCmp('manage_order_grid').getStore().load();
                        }
                    }
                }
            });
        }
        disp_win.doLayout();
        disp_win.show();
        if (!Ext.isEmpty(courier)) {
            Ext.getCmp('save_btn_content').hide();
            Ext.getCmp('order_dispatch_courier').setReadOnly(true);
            Ext.getCmp('order_dispatch_consignment').setReadOnly(true);
        }
    };

    var updateDelivery = function (order_id, delivery_at_date, delivery_at_time, delivery_notes) {
        var del_win = Ext.getCmp('del_win');
        if (Ext.isEmpty(del_win)) {
            del_win = new Ext.Window({
                id: 'del_win',
                title: 'Delivery Details',
                layout: 'fit',
                autoHeight: true,
                width: 500,
                autoScroll: true,
                plain: false,
                constrain: true,
                modal: true,
                frame: true,
                resizable: false,
                shadow: false,
                items: [new Ext.FormPanel({
                        frame: false,
                        border: false,
                        bodyStyle: {"background-color": "white", "padding": "5px"},
                        hideBorders: true,
                        id: 'del_form',
                        labelAlign: 'top',
                        height: 150,
                        items: [{
                                fieldLabel: 'Delivered At',
                                xtype: 'compositefield',
                                labelWidth: 120,
                                items: [{
                                        xtype: 'datefield',
                                        id: 'order_delivery_at_date',
                                        allowBlank: false,
                                        minValue: (new Date()).clearTime(),
                                        name: 'order_delivery_at_date',
                                        flex: 1,
                                        format: 'd-m-Y',
                                        value: (delivery_at_date == '0000-00-00') ? new Date() : delivery_at_date
                                    },
                                    new Ext.form.TimeField({
                                        minValue: '00:00',
                                        maxValue: '23:59',
                                        id: 'order_delivery_at_time',
                                        name: 'order_delivery_at_time',
                                        increment: 10,
                                        flex: 1,
                                        allowBlank: false,
                                        format: 'H:i',
                                        value: (delivery_at_time == '00:00:00') ? '' : delivery_at_time
                                    })
                                            /*{
                                             xtype: 'textfield',
                                             fieldLabel: 'TAT',
                                             emptyText: 'HH:MM...',
                                             id: 'test_turn_around_time',
                                             name: 'n[test_turn_around_time]',
                                             msgTarget: 'under',
                                             anchor: '98%',
                                             tabIndex: 20,
                                             allowBlank: false,
                                             regex: /^([0-9]+):[0-5][0-9]?$/,
                                             validator: function (v) {
                                             return /^([0-9]+):[0-5][0-9]?$/.test(v) ? true : "Invalid Time Format";
                                             }
                                             }*/
                                ]
                            }, {
                                xtype: 'textarea',
                                fieldLabel: 'Notes',
                                id: 'order_delivery_notes',
                                name: 'order_delivery_notes',
                                allowBlank: false,
                                anchor: '99%',
                                value: delivery_notes
                            }]
                    })],
                buttons: [{
                        text: "Close",
                        id: 'cancel_btn_content',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        tabIndex: 140,
                        handler: function () {
                            del_win.close();
                        }
                    }, {
                        text: "Save",
                        id: 'save_btn_content',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        handler: function () {
                            if (Ext.getCmp('del_form').getForm().isValid()) {
                                var params = {
                                    order_id: order_id,
                                    order_delivery_at_date: Ext.getCmp('order_delivery_at_date').getValue(),
                                    order_delivery_at_time: Ext.getCmp('order_delivery_at_time').getValue(),
                                    order_delivery_notes: Ext.getCmp('order_delivery_notes').getValue(),
                                    action: 4
                                };
                                updateOrderStatus(params);
                            }
                        }
                    }],
                listeners: {
                    close: function () {
                        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                            Ext.getCmp('manage_order_grid').getStore().load();
                        }
                    }
                }
            });
        }
        del_win.doLayout();
        del_win.show();

        if (!Ext.isEmpty(delivery_at_date)) {
            Ext.getCmp('save_btn_content').hide();
            Ext.getCmp('order_delivery_at_date').setReadOnly(true);
            Ext.getCmp('order_delivery_at_time').setReadOnly(true);
            Ext.getCmp('order_delivery_notes').setReadOnly(true);
        }
    };

    var updateOrderStatus = function (params) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        Ext.Ajax.request({
            url: modURL + '&op=updateOrderStatus',
            method: 'POST',
            tstamp: t_stamp,
            apikey: _SESSION.apikey,
            params: params,
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true && tmp.valid === true) {
                    if (params.action == 3) {
                        Ext.getCmp('disp_win').close();
                        Ext.getCmp('manage_order_grid').getStore().load();
                        Ext.MessageBox.alert('Success', 'Products Dispatched');
                        sendsms(params.order_id, params.order_dispatch_courier, params.order_dispatch_consignment, params.cust_mobile, params.delivery_to, params.order_generated_id);

                    }
                    if (params.action == 4) {
                        Ext.getCmp('del_win').close();
                        Ext.MessageBox.alert('Success', 'Products Delivered Successfully.');
                    }
                    Ext.getCmp('manage_order_grid').getStore().load();
                }
            },
            failure: function () {
                Ext.MessageBox.alert('Error', 'Error occured while sending data');
            }
        });
    };

    var sendsms = function (order_id, order_dispatch_courier, order_dispatch_consignment, cust_mobile, delivery_to, order_generated_id) {
        Ext.Ajax.request({
            url: modURL + '&op=sendSMS',
            method: 'POST',
            params: {
                order_id: order_id,
                order_dispatch_courier: order_dispatch_courier,
                order_dispatch_consignment: order_dispatch_consignment,
                cust_mobile: cust_mobile,
                delivery_to: delivery_to,
                order_generated_id: order_generated_id
            },
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true && tmp.valid === true) {
                    Ext.MessageBox.alert('Success', tmp.message);
                }
            },
            failure: function () {
                Ext.MessageBox.alert('Error', 'Error occured while sending data');
            }
        });
    };

    var b2cSalesorderTopPanel = function (id) {

        var src = '?module=order_processing';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Sales Orders',
            items: [manageOrderGridb2cso(),
                new Ext.Panel({
                    region: 'east',
                    frame: false,
                    border: true,
                    layout: 'fit',
                    autoScroll: false,
                    title: 'View Sales Order Details',
                    collapsible: true,
                    collapseMode: 'mini',
                    collapsed: false,
                    bodyStyle: {"background-color": "white"},
                    id: 'order_manage_parent_panelb2cso',
                    width: winsize.width * 0.4,
                    items: [{
                            id: 'order_details_view_panel_b2cso',
                            hidden: true,
                            html: '<iframe id="iframe_orderdtlsb2cso" name="iframe_orderdtlsb2cso"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };
    
        var mapPanel = function (id,lat,lng) {
        var centerParam = {
            lat: lat,
            lng: lng
        };
        var map =  {xtype: 'gmappanel',
            region: 'center',
            zoomLevel: 10,
            gmapType: 'map',
            scaleControl: true,
            id: id,
            anchor: '99%',
            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
            mapControls: ['GSmallMapControl', 'GMapTypeControl'],
            setCenter: centerParam,
            listeners: {
                afterrender: function () {
                    setTimeout(function () {
                        Ext.getCmp(id).clearMarkers();
                        if (id == 'g_map') {
                            Ext.getCmp('g_map').addMarkers(Application.OrderProcessing.Markers, false);
                        }
                    }, 1000);
                }
            }
        };
       
      
        return map;
        
    };

    var showMap = function (lat,lng) {

        var panel = new Ext.Panel({
            layout: 'border',
            items: mapPanel('g_map',lat,lng)
        });

        var map_win = new Ext.Window({
            width: winsize.width * 0.9,
            height: winsize.height * 0.9,
            plain: true,
            modal: true,
            frame: true,
            constrainHeader: true,
            layout: 'fit',
            items: panel,
            buttons: [{
                    text: 'Close',
                    tabIndex: '10',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        map_win.close();
                    }
                }]
        });
        
        Ext.getCmp('g_map').clearMarkers();
        var mymarker = [];
        mymarker.push({
            lat: lat,
            lng: lng,
            marker: {
                title: "Order Co-ordinates",
                draggable: false
            },
            icon: IMAGE_BASE_PATH + "/default/icons/placeholder.png",/*GMAP_LOCATION_ICON*/
        });
        Application.OrderProcessing.Markers = mymarker;
        Ext.getCmp('g_map').addMarkers(mymarker, false);
        
        //Ext.getCmp('g_map').repaint(10);
        
        map_win.doLayout();
        map_win.show(this);
        map_win.center();

    };
    
    var manageOrderGridb2cso = function () {
        console.log('actionItemsb2cso');
        var actionItemsb2cso = [];

        actionItemsb2cso.push({
            tooltip: 'Print Packing Slip',
            getClass: function (v, meta, rec) {
                if (rec.get('status') >= 9) {
                    return 'price_list';

                } else {
                    var id = rec.get('status');
                    console.log(id);
                    return 'hideicon';
                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);

                if (record.get('status') < 4) {
                    Ext.MessageBox.alert('Notification', 'Please print the order to enable this action.');
                } else {
//                    printPackingSlip(record.get('order_id'), record.get('order_order_id'));
                    packetsCount(record.get('order_id'), record.get('order_order_id'), record.get('order_packedbags_count'));
                }
            }
        });


        actionItemsb2cso.push({
            tooltip: 'Print Invoice',
            getClass: function (v, meta, rec) {
                if ((rec.get('status') >= 4)) {
                    return 'print_invoice';

                } else {
                    return 'hideicon';
                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var record = grid.store.getAt(rowIndex);
                var order_auto_id = record.data.order_id;
                var order_generated_id = record.data.order_order_id;
                printInvoice(order_auto_id, order_generated_id);
                //postToUrl(modURL + '&op=downloadInvoice' + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp, {order_auto_id: order_auto_id, order_generated_id: order_generated_id});
            }
        });
        actionItemsb2cso.push({
            getClass: function (v, meta, rec) {
//                if ((rec.get('order_status_sort_order') < 8) && (rec.get('order_status_sort_order') > 6)) {
//                    //this.items[3].tooltip = 'Enter Delivery Details';
//                    //this.items[<?php echo $count; ?>].tooltip = 'Enter Delivery Details';
//                    return 'delivery';
//                } else if (rec.get('order_status_sort_order') > 7) {
//                    //this.items[3].tooltip = 'View Delivery Details';
//                    //this.items[<?php echo $count; ?>].tooltip = 'View Delivery Details';
//                    return 'delivery';
//                } else {
                return 'hideicon';
//                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                if (record.get('order_status_sort_order') < 7) {
                    Ext.MessageBox.alert('Notification', 'Please enter dispatch details to enable this action.');
                } else {
                    updateDelivery(record.get('order_id'), record.get('delivery_at_date'), record.get('delivery_at_time'), record.get('delivery_notes'));
                }
            }
        });



        actionItemsb2cso.push({
            tooltip: 'Order History',
            getClass: function (v, meta, rec) {
                // this.items[<?php echo $count; ?>].tooltip = 'View Userlog';
                return 'userlog';
            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                userlog_window(record.data.order_order_id, record.data.order_id);
            }
        });
        actionItemsb2cso.push({
            tooltip: 'Returned Items',
            getClass: function (v, meta, rec) {
                if ((rec.get('order_HasReturn') == 1)) {
                    return 'return-back';

                } else {
                    return 'hideicon';
                }
            },
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                getReturnedItems(record.get('order_id'), record.get('order_order_id'));
            }
        });
        actionItemsb2cso.push({
            tooltip: 'View Map',
            hidden: true,
            icon: IMAGE_BASE_PATH + "/default/icons/map.png",
            handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                //order_latitude,order_longitude
                //Application.DataEntry.showMap(record.get('order_latitude'), record.get('order_longitude'));
                showMap(record.get('order_latitude'), record.get('order_longitude'));
            }
        });

        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: brmodURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post'
        });

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
                    dataIndex: 'br_Name'
                }, {
                    type: 'list',
                    options: ORDER_STATUS_OPTIONS,
                    phpMode: true,
                    dataIndex: 'order_status'
                }]
        });
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = manageOrderStoreb2cso();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            region: 'center',
            frame: true,
            //width: winsize.width * 0.6,
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
            id: 'manage_order_gridb2cso',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Store',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Store',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                }, {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                }, {
                    header: 'Time',
                    sortable: false,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                    renderer: qtipRenderer
                },
                {
                    header: 'Delivery Mode',
                    sortable: true,
                    dataIndex: 'order_method',
                    tooltip: 'Order Method',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Delivery To',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Delivery To',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'cust_mobile',
                    tooltip: 'Mobile',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'PayGatewayId',
                    sortable: true,
                    dataIndex: 'order_payment_gateway_refid',
                    tooltip: 'PayGatewayId',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    xtype: 'actioncolumn',
                    //header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            var status = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.status;
                            var order_HasReturn = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_HasReturn;
                            salesorderActionMenu(e, status, order_HasReturn,grid, rowindex);
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 header: '',
                 hideable: false,
                 sortable: false,
                 width: 260,
                 items: actionItemsb2cso
                 }*/
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: ordergridSelectionChangedb2cso
                }
            }), tbar: [
                //{                   html: '&nbsp;BRANCH : &nbsp;'                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdOrder',
                    name: 'textboxBrmCpdOrder',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchOrder',
                    name: 'comboxbranchOrder',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Store Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchOrder',
                    hidden: true,
                    listeners: {
                        select: function () {
                            Ext.getCmp('manage_order_gridb2cso').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    id: 'showBtn',
                    style: "padding-left: 10px;",
                    hidden: true,
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchOrder').getRawValue();
                        Ext.getCmp('manage_order_gridb2cso').getStore().load({
                            params: {
                                br_Name: branchName
                            }
                        });
                    }
                }, {
                    xtype: 'button',
                    text: 'Create Sales Order',
                    tooltip: 'Create Sales Order',
                    iconCls: 'add',
                    hidden: true,
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
                                Application.OrderProcessing.Cache.uuid = uid;
                                createRetalineSalesOrder(0);
                            }
                        });

                    }
                }],
            listeners: {
                /*  cellClick: function (grid, rowIndex, columnIndex, e) {
                 var record = grid.getStore().getAt(rowIndex);
                 var ID = record.get('order_auto_id');
                 if (!Ext.isEmpty(ID)) {
                 Application.OrderProcessing.Cache.orderAutoId = ID;
                 Application.OrderProcessing.UpdateViewMode(Application.OrderProcessing.Cache.orderAutoId);
                 }
                 },*/
                resize: onGridResize,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchOrder").hide();
                        Ext.getCmp("textboxBrmCpdOrder").hide();
                    }
                    else {
                        Ext.getCmp("textboxBrmCpdOrder").hide();
                        Ext.getCmp("textboxBrmCpdOrder").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchOrder").hide();
                        Ext.getCmp('showBtn').hide();
                    }
                    if (Ext.getCmp('textboxBrmCpdOrder').getValue() != '')
                    {
                        order_store.baseParams = {
                            current_branch_id: _SESSION.finascop_current_branch_id,
                            isb2cso: 1
                        }
                        order_store.load();
                    }
                }, celldblclick: function () {

                    Ext.getCmp('order_manage_parent_panelb2cso').expand(true);
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
    var salesorderActionMenu = function (e, status, order_HasReturn) {
        var salesorderActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Print Packing Slip",
                    hidden: !(status >= 9),
                    handler: function () {
                        var record = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data;
                        var order_id = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_id;
                        var order_order_id = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_order_id;
                        var order_packedbags_count = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_packedbags_count;
                        if (record.status < 4) {
                            Ext.MessageBox.alert('Notification', 'Please print the order to enable this action.');
                        } else {
//                    printPackingSlip(record.get('order_id'), record.get('order_order_id'));
                            packetsCount(order_id, order_order_id, order_packedbags_count);
                        }
                    }
                }, {
                    text: "Print Invoice",
                    hidden: !(status >= 4),
                    handler: function () {
                        var order_id = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_id;
                        var order_order_id = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_order_id;
                        printInvoice(order_id, order_order_id);
                    }
                },
                {
                    text: "Order History",
                    handler: function () {
                        var order_order_id = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_order_id;
                        var order_id = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_id;
                        userlog_window(order_order_id, order_id);
                    }
                },
                {
                    text: "Returned Items",
                    hidden: !(order_HasReturn == 1),
                    handler: function () {
                        var record = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data;
                        getReturnedItems(record.get('order_id'), record.get('order_order_id'));
                    }
                },
                {
                    text: "View Map",
                    handler: function () {
                        var order_latitude = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_latitude;
                        var order_longitude = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_longitude;
                        //Application.DataEntry.showMap(order_latitude, order_longitude);
                        showMap(order_latitude, order_longitude);
                    }
                }]
        });
        salesorderActionMenu.showAt(e.getXY());
    };
    var manageOrderStoreb2cso = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_payment_gateway_refid', 'order_payment_gateway_refid_crc32', 'order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'order_method', 'delivery_to', 'cust_mobile', 'order_latitude', 'order_longitude',{
                    name: 'order_created_on',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                },
                'dispatch_courier', 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'paymentMode', 'fsto_uid'
            ]),
            sortInfo: {
                field: 'order_created_on',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    var branchName = Ext.getCmp('comboxbranchOrder').getRawValue();
                    if (branchName != '') {
                        this.baseParams.br_Name = branchName;
                    }
                    this.baseParams.isb2cso = 1;
                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_gridb2cso').getView().refresh();
                    Ext.getCmp('manage_order_gridb2cso').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_gridb2cso').getSelectionModel().getSelections()[0].data.order_id;
                        Application.OrderProcessing.Cache.orderAutoIdb2cso = ID;
                        Application.OrderProcessing.UpdateViewModeB2CSO(Application.OrderProcessing.Cache.orderAutoIdb2cso);
                    }
                }
            }
        });
        return store;
    };
    var createRetalineSalesOrder = function (fstr_id) {
        var resultWindow = new Ext.Window({
            id: "windowRetalineTransferRequest",
            iconCls: 'vender-items',
            shadow: false,
            height: 700,
            width: winsize.width * 0.8,
            title: 'Create Sales Order',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'north',
                    layout: 'fit',
                    height: 100,
                    items: createRetalineSalesOrderform(fstr_id)
                }, {
                    region: 'center',
                    layout: 'fit',
                    height: 400,
                    items: createRetalineSalesOrderItemStockGrid(fstr_id)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 200,
                    items: createRetalineSalesOrderBottomform()
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
                                    uuid: Application.OrderProcessing.Cache.uuid,
                                    fstr_id: fstr_id,
                                    fstr_source: Ext.getCmp('fstr_source').getValue(),
                                    fstr_destination: Ext.getCmp('fstr_destination').getValue(),
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        Application.example.msg('Success', tmp.msg);
                                        Application.OrderProcessing.Cache.uuid = '';
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
    var createRetalineSalesOrderBottomform = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var retalineSOFormPanel = new Ext.form.FormPanel({
            height: 200,
            frame: true,
            border: false,
            id: 'salesOrderBottomFormPanel',
            layout: 'column',
            items: [{
                    layout: 'form',
                    columnWidth: 0.80,
                    items: [{
                            xtype: 'label',
                            html: '&nbsp;'
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.15,
                    labelWidth: 42,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            fieldLabel: 'Total',
                            id: 'rso_totalValue',
                            name: 'rso_totalValue',
                            allowNegative: true,
                            allowDecimals: true,
                            tabIndex: 919,
                            readOnly: true,
                            anchor: '99%',
                            listeners: {
                                focus: function (txtbx) {

                                }
                            }
                        }]
                }]
        });
        return retalineSOFormPanel;
    };
    var createRetalineSalesOrderform = function (fstr_id) {

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var retalineSOFormPanel = new Ext.form.FormPanel({
            height: 200,
            frame: true,
            border: false,
            id: 'poItemStockItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.25,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Consultant',
                            id: 'order_consultant_name',
                            name: 'fstr_RequiredItemQty',
                            allowBlank: false,
                            anchor: '97%'
                        }
                    ]

                },
                {
                    layout: 'form',
                    columnWidth: 0.25,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Customer Mobile',
                            id: 'order_customer_mobile',
                            name: 'order_customer_mobile',
                            allowBlank: false,
                            anchor: '97%',
                            listeners: {
                                change: function () {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=getCustomerDetails',
                                        method: 'POST',
                                        params: {
                                            order_customer_mobile: Ext.getCmp('order_customer_mobile').getValue()
                                        },
                                        success: function (res) {
                                            var tmp = Ext.decode(res.responseText);
                                            if (tmp.cust_id > 0) {
                                                Ext.getCmp('order_customer_name').setValue(tmp.cust_customer_name);
                                                Ext.getCmp('order_customer_email').setValue(tmp.cust_email);
                                                Ext.getCmp('order_customer_name').setReadOnly(true);
                                                Ext.getCmp('order_customer_email').setReadOnly(true);
                                            }
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        }
                                    });
                                }
                            }
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: 0.25,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Customer Name',
                            id: 'order_customer_name',
                            name: 'order_customer_name',
                            allowBlank: false,
                            anchor: '97%'
                        }]
                },
                {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.25,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Customer Email',
                            id: 'order_customer_email',
                            name: 'order_customer_email',
                            allowBlank: false,
                            anchor: '97%'
                        }]

                }]
        });
        return retalineSOFormPanel;
    };
    var retalineTransferRequestItemStore = function (fstr_id) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listItemsForSO',
            fields: ['uuid', 'fstr_id', 'fstrd_id', 'fstr_ItemId', 'fstr_RequiredItemQty', 'fstrd_status', 'fstr_ItemName'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        uuid: Application.OrderProcessing.Cache.uuid,
                        fstr_id: fstr_id
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var createRetalineSalesOrderItemStockGrid = function (fstr_id) {

        var retalineTransferRequestItemGridStore = retalineTransferRequestItemStore(fstr_id);
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineTransferRequestItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineSOItemGridPanel',
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
                    header: 'Batch',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Batch'
                }, {
                    header: 'Expiry Date',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Expiry Date'
                }, {
                    header: 'Rate',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Rate'
                }, {
                    header: 'GST / VAT %',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'GST / VAT %'
                }, {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Quantity'
                }, {
                    header: 'Amount',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Amount'
                }, {
                    header: 'Tax',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Tax'
                }, {
                    header: 'Total',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Total'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete Item',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteItem(record.get('fstrd_id'));
                            }
                        }
                    ]

                }],
            tbar: [{
                    html: '&nbsp;Enter/Scan Barcode : &nbsp;',
                    id: 'barcodeBr_label',
                }, {
                    xtype: 'textfield',
                    id: 'barcode_idso',
                    name: 'barcode_idso',
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
                            var barcodesearch_fieldrt = Ext.getCmp('barcode_idso').getValue();
                            if (barcodesearch_fieldrt) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=barcodeCheckForso',
                                    method: 'POST',
                                    params: {
                                        barcodesearch_fieldrt: barcodesearch_fieldrt,
                                        unique_id: Application.OrderProcessing.Cache.uuid
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        var item_id = tmp.item_id;
                                        if (tmp.success === true) {

                                            var rowIndex = Ext.getCmp('retalineSOItemGridPanel').store.find('itemId', item_id);
                                            var grecord = Ext.getCmp('retalineSOItemGridPanel').store.getAt(rowIndex);
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
                                                Ext.getCmp('barcode_idso').reset();
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
//                                                        if (pkdQuantity <= (parseInt(returnableQty))) {
//
//                                                            Ext.Ajax.request({
//                                                                url: modURL + '&op=saveItemReturninOrder',
//                                                                method: 'POST',
//                                                                params: {
//                                                                    uuid: Application.ReturnRequest.Cache.uuid,
//                                                                    itemReturn_type1qty: itemReturn_type1qty,
//                                                                    itemReturn_type2qty: itemReturn_type2qty,
//                                                                    itemReturn_type3qty: itemReturn_type3qty,
//                                                                    returnableQty: returnableQty,
//                                                                    totalReturn: 1,
//                                                                    returned_qty: pkdQuantity,
//                                                                    order_id: order_id,
//                                                                    itemId: item_id,
//                                                                    item_order_qty: item_order_qty,
//                                                                    barcodesearch_fieldrt: barcodesearch_fieldrt
//                                                                },
//                                                                success: function (response) {
//                                                                    var tmp = Ext.decode(response.responseText);
//                                                                    console.log('tmp', tmp);
//                                                                    if (tmp.success === true && tmp.valid === true) {
//
//                                                                        Application.example.msg('Success', tmp.msg);
//                                                                        Ext.getCmp('retalineSOItemGridPanel').getStore().load({
//                                                                            params: {
//                                                                                order_id: order_id
//                                                                            }
//                                                                        });
//                                                                    } else if (tmp.success === true && tmp.valid === false) {
//                                                                        Ext.Msg.alert("Notification.", tmp.msg);
//                                                                    } else if (tmp.success === true && tmp.img_valid === false) {
//                                                                        Ext.Msg.alert("Notification.", tmp.msg);
//                                                                    } else {
//                                                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
//                                                                    }
//                                                                },
//                                                                failure: function (response) {
//                                                                    var tmp = Ext.util.JSON.decode(response.responseText);
//                                                                    Ext.MessageBox.alert('Error', tmp.message);
//                                                                }
//                                                            });
//                                                            var new_fsto_pkdQty = parseInt(grecord.data.item_return_qty) + 1;
//                                                            grecord.set('item_return_qty', new_fsto_pkdQty);
//                                                            if (itemReturn_type1 == true) {
//                                                                if (parseInt(grecord.data.item_return_sellableqty) > 0) {
//                                                                    var sellableqty = grecord.data.item_return_sellableqty;
//                                                                } else {
//                                                                    var sellableqty = 0;
//                                                                }
//                                                                var newitem_return_sellableqty = parseInt(sellableqty) + 1;
//                                                                grecord.set('item_return_sellableqty', newitem_return_sellableqty);
//                                                            } else {
//                                                                if (parseInt(grecord.data.item_return_damagedqty) > 0) {
//                                                                    var damagedqty = grecord.data.item_return_damagedqty;
//                                                                } else {
//                                                                    var damagedqty = 0;
//                                                                }
//                                                                var newitem_return_damagedqty = parseInt(damagedqty) + 1;
//                                                                grecord.set('item_return_damagedqty', newitem_return_damagedqty);
//                                                            }
//                                                            Ext.getCmp('barcode_idso').focus();
//                                                        } else {
//                                                            Ext.Msg.alert('Notification', 'Quantity Mismatch.');
//                                                            grecord.set('item_return_qty', '');
//                                                        }
                                                    } else {
                                                        upReturnType(order_id, item_id, item_order_qty, grecord, itemName, barcodesearch_fieldrt);
                                                    }

                                                }, 1000);

                                            }
                                            else {
                                                Ext.MessageBox.alert('Notification', 'Item Quantity limit exceeded');
                                                Ext.getCmp('wrong_id').show();
                                                Ext.getCmp('correct_id').hide();
                                                Ext.getCmp('barcode_idso').reset();
                                                setTimeout(function () {
                                                    Ext.getCmp('wrong_id').hide();
                                                }, 5000);
                                            }
                                        }
                                        else {
//                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                            Ext.getCmp('wrong_id').show();
                                            Ext.getCmp('correct_id').hide();
                                            Ext.getCmp('barcode_idso').reset();
                                            setTimeout(function () {
                                                Ext.getCmp('wrong_id').hide();
                                            }, 5000);
                                            Ext.getCmp('barcode_idso').focus();
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        Ext.getCmp('barcode_idso').focus();
                                    }
                                });
                            }
                        },
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                var barcodesearch_fieldrt = Ext.getCmp('barcode_idso').getValue();
//                                if (barcodesearch_fieldrt) {
//                                    Ext.Ajax.request({
//                                        url: modURL + '&op=returnbarcodeCheck',
//                                        method: 'POST',
//                                        params: {
//                                            barcodesearch_fieldrt: barcodesearch_fieldrt,
//                                            order_id: order_id
//                                        },
//                                        success: function (response) {
//                                            var tmp = Ext.decode(response.responseText);
//                                            if (tmp.success === true) {
//                                                var tmp = Ext.decode(response.responseText);
//                                                var item_id = tmp.item_id;
//                                                var rowIndex = Ext.getCmp('retalineSOItemGridPanel').store.find('itemId', item_id);
//                                                var grecord = Ext.getCmp('retalineSOItemGridPanel').store.getAt(rowIndex);
//                                                var item_return_qty_requested = grecord.data.item_return_qty_requested;
//                                                console.log('item_return_qty_requested', item_return_qty_requested);
//                                                var item_order_qty = grecord.data.item_order_qty;
//                                                if (item_order_qty > 0) {
//                                                    item_order_qty = item_order_qty;
//                                                } else {
//                                                    item_order_qty = 0;
//                                                }
//                                                console.log('item_order_qty', item_order_qty);
//                                                var item_return_qty = grecord.data.item_return_qty;
//                                                console.log('item_return_qty', item_return_qty);
//                                                var totalReturn = parseInt(item_return_qty) + parseInt(item_return_qty_requested);
//                                                if (totalReturn > 0) {
//                                                    totalReturn = totalReturn;
//                                                } else {
//                                                    totalReturn = 0;
//                                                }
//                                                console.log('totalReturn', totalReturn);
//                                                var itemName = grecord.data.itemName;
//
//
//                                                if (item_order_qty > totalReturn) {
//                                                    Ext.getCmp('correct_id').show();
//                                                    Ext.getCmp('wrong_id').hide();
//                                                    Ext.getCmp('barcode_idso').reset();
//                                                    setTimeout(function () {
//                                                        Ext.getCmp('correct_id').hide();
//                                                        upReturnType(order_id, item_id, item_order_qty, grecord, itemName, barcodesearch_fieldrt);
//                                                    }, 1000);
//
//                                                }
//                                                else {
//                                                    Ext.MessageBox.alert('Notification', 'Item Quantity limit exceeded');
//                                                    Ext.getCmp('wrong_id').show();
//                                                    Ext.getCmp('correct_id').hide();
//                                                    Ext.getCmp('barcode_idso').reset();
//                                                    setTimeout(function () {
//                                                        Ext.getCmp('wrong_id').hide();
//                                                    }, 5000);
//                                                }
//
//
//                                            }
//                                            else {
////                                            Ext.MessageBox.alert("Notification", tmp.msg);
//                                                Ext.getCmp('wrong_id').show();
//                                                Ext.getCmp('correct_id').hide();
//                                                Ext.getCmp('barcode_idso').reset();
//                                                setTimeout(function () {
//                                                    Ext.getCmp('wrong_id').hide();
//                                                }, 5000);
//                                                Ext.getCmp('barcode_idso').focus();
//                                            }
//                                        },
//                                        failure: function (response, options) {
//                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
//                                            Ext.getCmp('barcode_idso').focus();
//                                        }
//                                    });
//                                }
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
    return {
        Cache: {},
        init: function () {

            Application.OrderProcessing.item_add_edit = '';
            var transferRulesLoadedForm;
            var panelId = 'order_tpanel';
            var OrderPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(OrderPanel)) {
                OrderPanel = topPanel(panelId);
                Application.UI.addTab(OrderPanel);
                OrderPanel.doLayout();
            } else {
                Application.UI.addTab(OrderPanel);
            }

        },
        ViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_id = arguments[0];
            Ext.getCmp('details_view_panel_order').show();

            Ext.get('iframe_productdtls').dom.src = modURL + '&op=order_details&order_id=' + order_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
            //  Ext.get('iframe_teacherProfile').dom.src = modURL + '&op=order_details&order_auto_id=' + order_auto_id;
            /* Ext.Ajax.request({
             url: modURL + '&op=detailsView',
             method: 'POST',
             params: {order_auto_id: order_auto_id},
             success: function (res) {
             var tmp = Ext.decode(res.responseText);
             if (tmp.success === true) {
             var details_view_panel_order = Ext.getCmp('details_view_panel_order');
             details_view_panel_order.update(tmp);
             }
             },
             failure: function () {
             Ext.MessageBox.alert('Error', 'Error occured while sending data');
             }
             });*/
        },
        orderProcessing: function () {

            var panelId = 'order_processing_panel';
            var OrderProcessingPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(OrderProcessingPanel)) {
                OrderProcessingPanel = orderTopPanel(panelId);
                Application.UI.addTab(OrderProcessingPanel);
                OrderProcessingPanel.doLayout();
            } else {
                Application.UI.addTab(OrderProcessingPanel);
            }

        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('order_details_view_panel').show();

            Ext.get('iframe_orderdtls').dom.src = modURL + '&op=order_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        }, b2csalesOrder: function () {

            var panelId = 'b2csalesorder_processing_panel';
            var SalesOrderProcessingPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(SalesOrderProcessingPanel)) {
                SalesOrderProcessingPanel = b2cSalesorderTopPanel(panelId);
                Application.UI.addTab(SalesOrderProcessingPanel);
                SalesOrderProcessingPanel.doLayout();
            } else {
                Application.UI.addTab(SalesOrderProcessingPanel);
                //Ext.getCmp('order_manage_parent_panel').collapse();
            }

        }, UpdateViewModeB2CSO: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            //Ext.getCmp('order_manage_parent_panel').expand(true);
            Ext.getCmp('order_details_view_panel_b2cso').show();

            Ext.get('iframe_orderdtlsb2cso').dom.src = modURL + '&op=order_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        }
    };
}();