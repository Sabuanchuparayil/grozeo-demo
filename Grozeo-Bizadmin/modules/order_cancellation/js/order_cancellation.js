/*
 * Created by Lekshmi VS
 * On feb 3, 2016
 * */

Application.OrderCancellation = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var transferRulesLoadedForm = null;
    var modURL = '?module=order_cancellation';
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

        if (!Ext.isEmpty(Ext.getCmp('manage_order_canc_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_canc_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.OrderCancellation.Cache.orderAutoId = ID;
            Application.OrderCancellation.UpdateViewMode(ID);
        }
    };

    var orderTopPanel = function (id) {

        var src = '?module=order_cancellation';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Cancelled Orders',
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
                    id: 'order_canc_manage_parent_panel',
                    width: winsize.width * 0.45,
                    items: [{
                            id: 'order_canc_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_canc_orderdtls" name="iframe_canc_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
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
                url: modURL + '&op=getOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on',
                'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified'
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
                    var branchName = Ext.getCmp('comboxbranchOrderCanc').getRawValue();
                    if (branchName != '') {
                        this.baseParams.br_Name = branchName;
                    }
                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_canc_grid').getView().refresh();
                    Ext.getCmp('manage_order_canc_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_canc_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_canc_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.OrderCancellation.Cache.orderAutoId = ID;
                        Application.OrderCancellation.UpdateViewMode(Application.OrderCancellation.Cache.orderAutoId);
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

        var check_box = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true
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
            id: 'manage_order_canc_grid',
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
                    header: 'Delivery To',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Delivery To',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Reason',
                    sortable: true,
                    dataIndex: 'reason',
                    tooltip: 'Reason',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Cancelled Type',
                    sortable: true,
                    dataIndex: 'cancelled_by_type_name',
                    tooltip: 'Cancelled Type',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Cancelled By',
                    sortable: true,
                    dataIndex: 'cancelled_by_name',
                    tooltip: 'Cancelled By',
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
                    text: 'Create Order To Cancel',
                    tooltip: 'Create Order To Cancel',
                    icon: './resources/images/submenuicons/add.png',
//                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        Application.OrderCancellation.addNewOrderToCancel();

                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdOrderCanc',
                    name: 'textboxBrmCpdOrderCanc',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchOrderCanc',
                    name: 'comboxbranchOrderCanc',
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
                    hiddenName: 'comboxbranchOrderCanc',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchOrderCanc').getRawValue();
                            Ext.getCmp('manage_order_canc_grid').getStore().load({
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
                    if (_SESSION.br_PyramidLevel == 1) {
                        Ext.getCmp("comboxbranchOrderCanc").show();
                        Ext.getCmp("textboxBrmCpdOrderCanc").hide();
                    } else {
                        Ext.getCmp("textboxBrmCpdOrderCanc").show();
                        Ext.getCmp("textboxBrmCpdOrderCanc").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchOrderCanc").hide();
                    }
                    if (Ext.getCmp('textboxBrmCpdOrderCanc').getValue() != '')
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
                //  fstr_ItemId.splice(ind, 1);
                record.set('checked', 'false');
            },
            rowselect: function (sm, rowIndex, record) {
                console.log(record);
                var ind = order_no.indexOf(record.get('order_id'));
                //if (ind == -1)
                //fstr_ItemId.push(record.get('fstr_ItemId'));
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
            }, ['order_id', 'order_order_id', 'order_mrp', 'order_customer_id', 'order_packedbags_count', 'cust_mobile', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on',
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

                    this.baseParams.customer_mobile = "";

                }
//                load: function (store, e) {
//                    Ext.getCmp('manage_order_canc_grid').getView().refresh();
//                    Ext.getCmp('manage_order_canc_grid').getSelectionModel().selectRow(0);
//                    if (!Ext.isEmpty(Ext.getCmp('manage_order_canc_grid').getSelectionModel().getSelections())) {
//                        var ID = Ext.getCmp('manage_order_canc_grid').getSelectionModel().getSelections()[0].data.order_id;
//                        Application.OrderCancellation.Cache.orderAutoId = ID;
//                        Application.OrderCancellation.UpdateViewMode(Application.OrderCancellation.Cache.orderAutoId);
//                    }
//                }
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
//                {
//                    type: 'list',
//                    dataIndex: 'status_name',
////                    options: ['Requested', 'Ordered', 'Deleted'],
////                    value: ['Requested'],
//                    phpMode: true
//                },
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
//            selModel: ck_selection,
            id: 'gridpanelOrderdata',
            columns: [
//                ck_selection,
                {
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
                }, {
                    header: 'Date',
                    dataIndex: 'order_created_on',
                    sortable: true,
                    tooltip: 'Date',
                    hideable: false
                },
                {
                    header: 'Time',
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
//                {
//                    header: 'Reson',
//                    dataIndex: 'reason',
//                    sortable: true,
//                    tooltip: 'Reason',
//                    hideable: false
//                },
//                {
//                    header: 'Cancelled Type',
//                    dataIndex: 'cancelled_by_type_name',
//                    sortable: true,
//                    tooltip: 'Cancelled Type',
//                    hideable: false
//                },
//                {
//                    header: 'Cancelled By',
//                    dataIndex: 'cancelled_by_name',
//                    sortable: true,
//                    tooltip: 'Cancelled By',
//                    hideable: false
//                },
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
                            ordercancelMenu(e);
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
                 tooltip: 'Cancel Order',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.getStore().getAt(rowIndex);
                 Application.OrderCancellation.cancelOrder(record.get('order_id'), record.get('order_customer_id'), record.get('updated_at'), record.get('status'));
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
//            listeners: {
//                afterrender: function () {
//                    Ext.getCmp('gridpanelOrderdata').getStore().load({
//                        params: {
//                            fstr_id: fstr_id
//
//                        }
//                    });
//                }
//            }
        });
        return _OrdergridPanel;
    };
    var ordercancelMenu = function (e) {
        var ordercancelMenu = new Ext.menu.Menu({
            items: [{
                    text: "Cancel Order",
                    handler: function () {
                        var order_id = Ext.getCmp('gridpanelOrderdata').getSelectionModel().getSelections()[0].data.order_id;
                        var order_customer_id = Ext.getCmp('gridpanelOrderdata').getSelectionModel().getSelections()[0].data.order_customer_id;
                        var updated_at = Ext.getCmp('gridpanelOrderdata').getSelectionModel().getSelections()[0].data.updated_at;
                        var status = Ext.getCmp('gridpanelOrderdata').getSelectionModel().getSelections()[0].data.status;
                        Application.OrderCancellation.cancelOrder(order_id, order_customer_id, updated_at, status);
                    }
                }]
        });
        ordercancelMenu.showAt(e.getXY());
    };

    return {
        Cache: {},
        orderCancellation: function () {

            var panelId = 'order_canc_processing_panel';
            var OrderCancellationPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(OrderCancellationPanel)) {
                OrderCancellationPanel = orderTopPanel(panelId);
                Application.UI.addTab(OrderCancellationPanel);
                OrderCancellationPanel.doLayout();
            } else {
                Application.UI.addTab(OrderCancellationPanel);
            }

        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('order_canc_details_view_panel').show();

            Ext.get('iframe_canc_orderdtls').dom.src = modURL + '&op=order_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        },
        addNewOrderToCancel: function () {
            var win_id = "windowAddNewOrderToCancel";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {
                win = new Ext.Window({
                    id: win_id,
                    title: 'Order Details',
//                    iconCls: 'finascop_balance',
                    layout: 'fit',
                    width: winsize.width * 0.85,
//                    autoHeight: true,
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
                                columnWidth: 0.60,
                                border: false,
                                hideLabel: true,
                                hideBorders: true,
                                bodyStyle: {"background-color": "F1F1F1"},
                                items: [{
                                        hideLabel: true,
                                        xtype: 'radiogroup',
                                        id: 'radiogroupOrderSearch',
                                        items: [{
                                                boxLabel: 'Order Number',
                                                name: 'search_type',
                                                anchor: '60%',
                                                inputValue: 'orderNumber',
                                                id: 'search_type1',
                                                listeners: {
                                                    'check': function (checkbox, checked) {
                                                        if (checked) {
                                                            Ext.getCmp('order_number').show();
                                                            Ext.getCmp('customer_mobile').hide();
                                                        }
                                                    }
                                                }},
                                            {
                                                boxLabel: 'Customer Mobile',
                                                name: 'search_type',
                                                inputValue: 'customerMobile',
                                                anchor: '80%',
                                                id: 'search_type2',
                                                checked: true,
                                                listeners: {
                                                    'check': function (checkbox, checked) {
                                                        if (checked) {
                                                            Ext.getCmp('order_number').hide();
                                                            Ext.getCmp('customer_mobile').show();
                                                        }
                                                    }
                                                }},
                                            {
                                                hideLabel: true,
                                                xtype: 'textfield',
                                                id: 'customer_mobile',
                                                name: 'n[customer_mobile]',
                                                emptyText: 'Enter Mobile',
                                                allowBlank: false,
                                                anchor: '90%'
                                            },
                                            {
                                                hideLabel: true,
                                                xtype: 'textfield',
                                                id: 'order_number',
                                                name: 'n[order_number]',
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
                                                    var searchType = Ext.getCmp('radiogroupOrderSearch').getValue();
                                                    var customer_mobile = Ext.getCmp('customer_mobile').getValue();
                                                    var order_number = Ext.getCmp('order_number').getValue();
                                                    switch (searchType) {
                                                        case 'orderNumber':
                                                            var type = 'order';
                                                            Ext.getCmp('gridpanelOrderdata').getStore().load({
                                                                params: {
                                                                    customer_mobile: order_number,
                                                                    type: type
                                                                }
                                                            });
                                                            break;
                                                        case 'customerMobile':
                                                            var type = 'mobile';
                                                            Ext.getCmp('gridpanelOrderdata').getStore().load({
                                                                params: {
                                                                    customer_mobile: customer_mobile,
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
                            /*{
                             layout: 'form',
                             columnWidth: 0.10,
                             labelAlign: 'left',
                             border: false,
                             hideBorders: true,
                             bodyStyle: {"background-color": "white"},
                             items: [
                             {
                             xtype: 'button',
                             text: 'Search',
                             anchor: '50%',
                             iconCls: 'finascop_search_btn',
                             handler: function () {
                             var searchType = Ext.getCmp('radiogroupOrderSearch').getValue();
                             var customer_mobile = Ext.getCmp('customer_mobile').getValue();
                             var order_number = Ext.getCmp('order_number').getValue();
                             switch (searchType) {
                             case 'orderNumber':
                             var type = 'order';
                             Ext.getCmp('gridpanelOrderdata').getStore().load({
                             params: {
                             customer_mobile: order_number,
                             type: type
                             }
                             });
                             break;
                             case 'customerMobile':
                             var type = 'mobile';
                             Ext.getCmp('gridpanelOrderdata').getStore().load({
                             params: {
                             customer_mobile: customer_mobile,
                             type: type,
                             }
                             });
                             break;
                             }
                             //                                           
                             }
                             }]
                             },*/
                            {
                                columnWidth: 1,
                                height: 490,
                                bodyStyle: {"background-color": "white"},
                                // bodyStyle: {"background-color": "white", "padding": "5px"},
                                items: OrderDetailsGrid()
                            }]
                    }),
                    listeners: {
                        afterrender: function () {

                        }
                    },
//                    buttonAlign: 'right',
//                    fbar: [
//                        {
//                            xtype: 'combo',
//                            id: 'comboxreason',
//                            name: 'comboxreason',
//                            mode: 'local',
//                            typeAhead: true,
//                            forceSelection: true,
//                            emptyText: 'Select Reason',
//                            fieldLabel: 'Reason',
//                            editable: true,
//                            anchor: '97%',
//                            store: new Ext.data.ArrayStore({
//                                fields: [
//                                    'reason_id',
//                                    'reason_name'
//                                ],
//                                data: [['Requested by customer', 'Requested by customer'],
//                                    ['Order picker not available', 'Order picker not available'],
//                                    ['Deliver boy not available', 'Deliver boy not available'],
//                                    ['Ordered items not available', 'Ordered items not available'],
//                                    ['Delivery area not reachable', 'Delivery area not reachable'],
//                                    ['Unforeseen Reason', 'Unforeseen Reason']
//                                ]
//                            }),
//                            triggerAction: 'all',
//                            minChars: 2,
//                            displayField: 'reason_id',
//                            valueField: 'reason_name',
//                            hiddenName: 'comboxreason'
//                        },
//                        {
//                            text: 'Cancel Order',
//                            xtype: 'button',
////                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
//                            width: 80,
//                            handler: function () {
//                                var store_fields = Ext.getCmp('gridpanelOrderdata').getSelectionModel().getSelections();
//                                if (store_fields.length > 0) {
//                                    var reason = Ext.getCmp('comboxreason').getValue();
//                                    if (reason != '') {
//                                        order_no = [];
//                                        cust_id = [];
//                                        for (var i = 0; i < store_fields.length; i++) {
//                                            order_no[i] = store_fields[i].data.order_id;
//                                            cust_id[i] = store_fields[i].data.order_customer_id;
//                                        }
//                                        Ext.Ajax.request({
//                                            url: modURL + '&op=cancelNewOrder',
//                                            method: 'POST',
//                                            params: {
//                                                order_no: Ext.encode(order_no),
//                                                cust_id: Ext.encode(cust_id),
//                                                reason: reason,
//                                            },
//                                            success: function (response) {
//                                                var tmp = Ext.decode(response.responseText);
//                                                if (tmp.success === true) {
//                                                    var tmp = Ext.decode(response.responseText);
//                                                    Application.example.msg('Success', tmp.msg);
//                                                    Ext.getCmp('manage_order_canc_grid').getStore().load();
//                                                    win.close();
//                                                }
//                                                else {
//                                                    Ext.MessageBox.alert("Notification", tmp.msg);
//                                                }
//                                            },
//                                            failure: function (response, options) {
//                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
//                                            }
//                                        });
//                                    }
//                                    else {
//                                        Ext.MessageBox.alert('Notification', 'Please select a reason for cancellation');
//                                    }
//
//                                }
//                                else {
//                                    Ext.MessageBox.alert('Notification', 'Please select Items');
//                                }
//                            }
//                        }
//                    ]


                });
            }
            win.doLayout();
            win.show(this);
            win.center();
        },
        cancelOrder: function (order_id, cust_id, updated_at, status_id) {
            var win_canc_id = "windowCancelOrder";
            var win_canc = Ext.getCmp(win_canc_id);
            if (Ext.isEmpty(win_canc)) {
                win_canc = new Ext.Window({
                    id: win_canc_id,
                    title: 'Reason for cancellation',
//                    iconCls: 'finascop_balance',
                    layout: 'fit',
                    width: 400,
//                    autoHeight: true,
                    height: 100,
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
                                columnWidth: 1,
                                border: false,
                                hideLabel: true,
                                labelWidth: 40,
                                hideBorders: true,
                                bodyStyle: {"background-color": "F1F1F1"},
                                items: [{
                                        xtype: 'combo',
                                        id: 'comboxreason',
                                        name: 'comboxreason',
                                        mode: 'local',
                                        typeAhead: true,
                                        forceSelection: true,
                                        emptyText: 'Select Reason',
                                        fieldLabel: 'Reason',
                                        editable: true,
                                        anchor: '97%',
                                        store: new Ext.data.ArrayStore({
                                            fields: [
                                                'reason_id',
                                                'reason_name'
                                            ],
                                            data: [['Requested by customer', 'Requested by customer'],
                                                ['Order picker not available', 'Order picker not available'],
                                                ['Deliver boy not available', 'Deliver boy not available'],
                                                ['Ordered items not available', 'Ordered items not available'],
                                                ['Delivery area not reachable', 'Delivery area not reachable'],
                                                ['Unforeseen Reason', 'Unforeseen Reason']
                                            ]
                                        }),
                                        triggerAction: 'all',
                                        minChars: 2,
                                        displayField: 'reason_id',
                                        valueField: 'reason_name',
                                        hiddenName: 'comboxreason'
                                    }]
                            }]
                    }),
                    buttonAlign: 'right',
                    fbar: [{
                            text: 'Cancel Order',
                            xtype: 'button',
//                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            width: 80,
                            handler: function () {

                                var reason = Ext.getCmp('comboxreason').getValue();
                                Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
                                    if (btn == 'yes') {
                                        if (reason != '') {
                                            Ext.Ajax.request({
                                                url: modURL + '&op=cancelNewOrder',
                                                method: 'POST',
                                                params: {
                                                    order_no: order_id,
                                                    cust_id: cust_id,
                                                    reason: reason,
                                                    updated_at: updated_at,
                                                    status_id: status_id
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('manage_order_canc_grid').getStore().load();
                                                        Ext.getCmp('windowAddNewOrderToCancel').close();
                                                        win_canc.close();
                                                    } else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }
                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert('Notification', 'Please select a reason for cancellation');
                                        }
                                    }
                                });



                            }
                        }]


                });
            }
            win_canc.doLayout();
            win_canc.show(this);
            win_canc.center();
        }
    };
}();