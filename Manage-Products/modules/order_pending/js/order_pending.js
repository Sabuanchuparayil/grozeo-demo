Application.OrderPending = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var transferRulesLoadedForm = null;
    var modURL = '?module=order_pending';
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

        if (!Ext.isEmpty(Ext.getCmp('manage_order_pending_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_pending_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.OrderPending.Cache.orderAutoId = ID;
            Application.OrderPending.UpdateViewMode(ID);
        }
    };

    var orderTopPanel = function (id) {

        var src = '?module=order_pending';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Pending Orders',
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
                    id: 'order_pending_manage_parent_panel',
                    width: winsize.width * 0.45,
                    items: [{
                            id: 'order_pending_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_pending_orderdtls" name="iframe_pending_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
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
                url: modURL + '&op=getPendingOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on', 'order_total_amount', 'cust_mobile',
                'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status',
                'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'order_convertedBy', 'order_isConverted', 'order_convertedByName'
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
                    var branchName = Ext.getCmp('comboxbranchOrderPending').getRawValue();
                    if (branchName != '') {
                        this.baseParams.br_Name = branchName;
                    }
                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_pending_grid').getView().refresh();
                    Ext.getCmp('manage_order_pending_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_pending_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_pending_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.OrderPending.Cache.orderAutoId = ID;
                        Application.OrderPending.UpdateViewMode(Application.OrderPending.Cache.orderAutoId);
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
                    type: 'numeric',
                    dataIndex: 'order_total_amount'
                }, {
                    type: 'string',
                    dataIndex: 'order_status'
                }, {
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'list',
                    options: ['Converted', 'Not Converted'],
                    phpMode: true,
                    value: 'Not Converted',
                    dataIndex: 'order_isConvertedType'
                }, ]
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
            id: 'manage_order_pending_grid',
            columns: [new Ext.grid.RowNumberer(),
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
                },
                {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Transaction Amount',
                    sortable: true,
                    dataIndex: 'order_total_amount',
                    tooltip: 'Transaction Amount',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 200,
                    hidden: true,
                    renderer: qtipRenderer
                },
                {
                    header: 'Customer Name',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Customer Name',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Contact No',
                    sortable: true,
                    dataIndex: 'cust_mobile',
                    tooltip: 'Contact No',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                    hidden: true,
                    renderer: qtipRenderer
                },
                {
                    header: 'Converted By',
                    sortable: true,
                    dataIndex: 'order_convertedByName',
                    tooltip: 'Converted By',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    items: [{
                            iconCls: 'my-icon18',
                            tooltip: 'Convert Order',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                covertToSuccess(record.get('order_id'));
                            }
                        }]
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
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdOrderPending',
                    name: 'textboxBrmCpdOrderPending',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchOrderPending',
                    name: 'comboxbranchOrderPending',
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
                    hiddenName: 'comboxbranchOrderPending',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchOrderPending').getRawValue();
                            Ext.getCmp('manage_order_pending_grid').getStore().load({
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
                        Ext.getCmp("comboxbranchOrderPending").show();
                        Ext.getCmp("textboxBrmCpdOrderPending").hide();
                    }
                    else {
                        Ext.getCmp("textboxBrmCpdOrderPending").show();
                        Ext.getCmp("textboxBrmCpdOrderPending").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchOrderPending").hide();
                    }
                    if (Ext.getCmp('textboxBrmCpdOrderPending').getValue() != '')
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
    var covertToSuccess = function (orderId) {
        if (Ext.isEmpty(convertOrderToSuccess_window)) {
            var convertOrderToSuccess_window = new Ext.Window({
                id: 'convertOrderToSuccess_window',
                layout: 'fit',
                width: 430,
                title: 'Convert Payment',
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
                        height: 80,
                        labelAlign: 'top',
                        items: [{
                                fieldLabel: 'Bank Transaction Number',
                                xtype: 'textfield',
                                id: 'orderTransactionNumber',
                                name: 'orderTransactionNumber',
                                tabIndex: 93,
                                allowBlank: false
                            }]
                    })],
                buttons: [{
                        text: 'Convert',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var orderTransactionNumber = Ext.getCmp('orderTransactionNumber').getValue();

                            Ext.Ajax.request({
                                waitMsg: 'Processing',
                                method: 'POST',
                                url: modURL + '&op=convertOrderToSuccess',
                                params: {
                                    orderId: orderId,
                                    orderTransactionNumber: orderTransactionNumber
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    console.log('tmp', tmp);
                                    if (tmp.status == 'ok') {
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp('convertOrderToSuccess_window').close();
                                        var branchName = Ext.getCmp('comboxbranchOrderPending').getRawValue();
                                        Ext.getCmp('manage_order_pending_grid').getStore().load({
                                            params: {
                                                br_Name: branchName
                                            }
                                        });

                                    } else {
                                        Ext.Msg.alert("Error", tmp.error.msg);
                                    }

                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.msg);
                                }
                            });

                        }
                    }, {
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            convertOrderToSuccess_window.close();
                        }
                    }]
            });

        }
        convertOrderToSuccess_window.doLayout();
        convertOrderToSuccess_window.show(this);
        convertOrderToSuccess_window.center();
    };


    return {
        Cache: {},
        pendingOrders: function () {

            var panelId = 'order_pending_processing_panel';
            var OrderPendingPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(OrderPendingPanel)) {
                OrderPendingPanel = orderTopPanel(panelId);
                Application.UI.addTab(OrderPendingPanel);
                OrderPendingPanel.doLayout();
            } else {
                Application.UI.addTab(OrderPendingPanel);
            }

        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('order_pending_details_view_panel').show();

            Ext.get('iframe_pending_orderdtls').dom.src = modURL + '&op=order_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        }
    };
}();