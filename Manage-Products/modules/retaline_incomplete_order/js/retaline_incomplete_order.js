Application.RetalineIncompleteOrder = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var modURL = '?module=retaline_incomplete_order';
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var getMigratedData = function (gridid) {
        var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), 'data');
        return Ext.encode(j);
    };
    var gridSelectionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_id;
            // var fsto_status = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_status;
            // Application.RetalineIncompleteOrder.ViewMode(ID);

        }
    };
    /*  var retalineIncompleteOrderPanel = function (id) {
     var panel = new Ext.Panel({
     frame: false,
     hideBorders: true,
     layout: 'border',
     border: false,
     title: 'Incomplete Order',
     id: id,
     items: [retalineIncompleteOrderGrid()]
     });
     return panel;
     }; */
    var incompleteOrderStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listIncompleteOrderData',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fsto_id',
                root: 'data'
            }, ['fsto_id', 'fsto_uid', 'fstr_id', 'fsto_status', 'fsto_source', 'fsto_sourceName', 'fsto_sourcetype', 'fsto_destination', 'fsto_destinationtype', 'fsto_source', 'fsto_destinationName', 'fsto_statusName',
                'fsto_destination', 'fsto_type', 'fsto_ItemWeight', 'fsto_ItemVolume', 'fsto_ordertype', {
                    name: 'fsto_createdOn',
                    type: 'date',
                    dateFormat: 'd-m-y'
                }
            ]),
            sortInfo: {
                field: 'fsto_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (store, e) {
                    Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getView().refresh();
                    Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_id;
                        Application.RetalineIncompleteOrder.Cache.fsto_id = ID;
                        //Application.RetalineIncompleteOrder.ViewMode(Application.RetalineIncompleteOrder.Cache.fsto_id);
                    }
                }
            }
        });
        return store;
    };
    var retalineIncompleteOrderGrid = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

        var _Store = incompleteOrderStore();
        var incomplete_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fsto_uid'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_createdOn'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_sourceName'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_ItemWeight'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_ItemVolume'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_destinationName'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_ordertype'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_statusName'
                }]
        });
        incomplete_filter.remote = true;
        incomplete_filter.autoReload = true;
        var _IncompleteOrdergridPanel = new Ext.grid.GridPanel({
            store: _Store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Incomplete Orders',
            iconCls: 'branch',
            autoScroll: true,
            plugins: [incomplete_filter],
//            id: id,
            loadMask: true,
            height: 530,
            bodyStyle: {"background-color": "white"},
            //selModel: ck_selection,
            id: 'gridpanelAddnewIncompleteOrderdata',
            columns: [
                {
                    header: 'TO No.',
                    dataIndex: 'fsto_uid',
                    sortable: true,
                    tooltip: 'TO No.',
                    hideable: true
                },
                {
                    header: 'From',
                    dataIndex: 'fsto_sourceName',
                    sortable: true,
                    tooltip: 'From',
                    hideable: true
                },
                {
                    header: 'To',
                    dataIndex: 'fsto_destinationName',
                    sortable: true,
                    tooltip: 'To',
                    hideable: true
                },
                {
                    header: 'Date',
                    dataIndex: 'fsto_createdOn',
                    sortable: true,
                    tooltip: 'Date',
                    hideable: true,
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-y');
                        return dateret;
                    }
                },
                {
                    header: 'Weight',
                    dataIndex: 'fsto_ItemWeight',
                    sortable: true,
                    tooltip: 'Weight',
                    hideable: true
                },
                {
                    header: 'Volume',
                    dataIndex: 'fsto_ItemVolume',
                    sortable: true,
                    tooltip: 'Volume',
                    hideable: true
                },
                {
                    header: 'Type',
                    dataIndex: 'fsto_ordertype',
                    sortable: true,
                    tooltip: 'Type',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'fsto_statusName',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: true
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            iconCls: 'my-icon24',
                            tooltip: 'Open Orders',
                            id: 'call_cust',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                openincompOrdersWindow(record.get('fsto_id'), record.get('fsto_ordertype'));
                            }
                        }
                    ]
                }
            ],
            viewConfig: {
                forceFit: true,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChanged
                }
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchnamess").show();
                        Ext.getCmp("textboxBrmCpdDataeditCSSS").hide();
                    }
                    else {
                        Ext.getCmp("textboxBrmCpdDataeditCSSS").show();
                        Ext.getCmp("textboxBrmCpdDataeditCSSS").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchnamess").hide();
                    }
                }
            },
            tbar: [{
                    html: '&nbsp;BRANCH : &nbsp;',
                    id: 'branch_labels',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditCSSS',
                    name: 'textboxBrmCpdDataeditCSSS',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                }, {
                    xtype: 'combo',
                    id: 'comboxbranchnamess',
                    name: 'comboxbranchnamess',
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
                    hiddenName: 'comboxbranchnamess',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchnamess').getValue();
                            Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getStore().load({
                                params: {
                                    branchName: branchName
                                }
                            });
                        }
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _Store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [incomplete_filter]
            })
        });
        return _IncompleteOrdergridPanel;
    };
    var openOrderGridStore = function (fsto_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getOrderItemList',
                method: 'post'
            }),
            fields: ['slNo', 'item_name', {name: 'fsto_ItemQty', type: 'float'}, {name: 'fsto_pkdQty', type: 'float'}, 'mrp', 'fsto_ItemId', 'fsto_id', 'fsto_source', 'selPrce'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function (itemStore, itemRecord) {
                    Ext.getCmp('gridpanelOpenOrderGrid').getSelectionModel().selectRow(0);
                },
                beforeload: function (store, e) {
                    this.baseParams.fsto_id = fsto_id;
                }
            }
        });
        return _Store;
    };
    var openOrdersGrid = function (fsto_id) {

        var _openOrderGridStore = openOrderGridStore(fsto_id);
        var _manualPackingGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _openOrderGridStore,
            iconCls: 'money',
            autoScroll: true,
            width: 600,
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelOpenOrderGrid',
            columns: [
                {
                    header: 'Sl No',
                    dataIndex: 'slNo',
                    sortable: true,
                    tooltip: 'Sl No',
                    hideable: true
                },
                {
                    header: 'Item',
                    dataIndex: 'item_name',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                },
                {
                    header: 'MRP',
                    dataIndex: 'mrp',
                    sortable: true,
                    tooltip: 'MRP',
                    hideable: true
                },
                {
                    header: 'Order Qty',
                    dataIndex: 'fsto_ItemQty',
                    sortable: true,
                    tooltip: 'Order Qty',
                    hideable: true
                },
                {
                    header: 'Packed Qty',
                    sortable: true,
                    dataIndex: 'fsto_pkdQty',
                    hideable: false,
                    tooltip: 'Packed Qty',
                    groupable: false
                }, {
                    header: 'SP',
                    dataIndex: 'selPrce',
                    sortable: true,
                    tooltip: 'Selling Price',
                    hideable: true
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: 'application_view_detail',
                            tooltip: 'View',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                var fsto_ItemId = Ext.getCmp('gridpanelOpenOrderGrid').getSelectionModel().getSelections()[0].data.fsto_ItemId;
                                Application.RetalineTransferOrder.viewBarcodeWindow(fsto_id, fsto_ItemId);
                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            tbar: []

        });
        return _manualPackingGridPanel;
    };

    var openincompOrdersWindow = function (fsto_id, fsto_ordertype) {
        var fsto_id = fsto_id;
        var _openOrdersWindow = new Ext.Window({
            title: 'Order Details',
            id: 'incompleteOrderWindow',
            layout: 'border',
            width: winsize.width * 0.6,
            height: winsize.height * 0.6,
            resizable: false,
            draggable: true,
            modal: true,
            closable: true,
            bodyStyle: {"background-color": "white"},
            items: [{
                    region: 'north',
                    height: 200,
                    items: orderDetails()
                }, {
                    region: 'center',
                    layout: 'fit',
                    height: 150,
                    items: openOrdersGrid(fsto_id)
                }],
            buttons: [{
                    text: 'Cancel Entire Order',
                    tabIndex: 511,
                    handler: function () {
                        Application.RetalineIncompleteOrder.cancelOrder(fsto_id, fsto_ordertype);
                    }
                }, {
                    text: 'Skip Order',
                    tabIndex: 511,
                    handler: function () {
                        Application.RetalineIncompleteOrder.skipOrder(fsto_id, fsto_ordertype);
                    }
                }, {
                    text: 'Proceed with Available Quantity',
                    id: 'submit_button',
                    handler: function () {
                        var itemGriddata = getMigratedData('gridpanelOpenOrderGrid');
                        var fsto_id = Ext.getCmp("gridpanelOpenOrderGrid").getSelectionModel().getSelections()[0].data.fsto_id;
                        var itemStore = Ext.getCmp('gridpanelOpenOrderGrid').getStore();
                        var fsto_pkdQty = itemStore.sum('fsto_pkdQty');
                        if (fsto_pkdQty > 0) {
                            Ext.Ajax.request({
                                url: modURL + '&op=proceedWithAvail',
                                method: 'POST',
                                params: {
                                    fsto_id: fsto_id,
                                    fsto_ordertype: fsto_ordertype
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.status === 'ok') {
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp("gridpanelAddnewIncompleteOrderdata").getStore().load();
                                        _openOrdersWindow.close();
                                    } else if (tmp.status === 'error') {
                                        Ext.Msg.alert("Error", tmp.error.msg);
                                    } else {
                                        Ext.Msg.alert("Error", tmp.data);
                                    }

                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.msg);
                                }
                            });
                        } else {
                            Ext.MessageBox.alert('Notification', 'There should be atleast one packed item');
                        }


                    }

                },, {
                    text: 'Revert',
                    tabIndex: 511,
                    handler: function () {

                        var itemGriddata = getMigratedData('gridpanelOpenOrderGrid');
                        var fsto_id = Ext.getCmp("gridpanelOpenOrderGrid").getSelectionModel().getSelections()[0].data.fsto_id;
                        var itemStore = Ext.getCmp('gridpanelOpenOrderGrid').getStore();
                        var fsto_pkdQty = itemStore.sum('fsto_pkdQty');
                        Ext.MessageBox.confirm('Confirm', 'You are going to change the order back to Pending Orders ?', function (btn) {
                            if (btn == 'yes') {
                                Ext.Ajax.request({
                                    url: modURL + '&op=revertIncompleteOrder',
                                    method: 'POST',
                                    params: {
                                        fsto_id: fsto_id,
                                        fsto_ordertype: fsto_ordertype
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            var branchName = Ext.getCmp('comboxbranchnamessinco').getValue();
                                            Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getStore().load({
                                                params: {
                                                    branchName: branchName
                                                }
                                            });
                                            _openOrdersWindow.close();
                                        } else if (tmp.status === 'error') {
                                            Ext.Msg.alert("Error", tmp.error.msg);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.msg);
                                        }

                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.msg);
                                    }
                                });
                            }
                        });


                    }
                }],
            listeners: {
                afterrender: function () {
                    Application.RetalineIncompleteOrder.orderViewMode(fsto_id);
                }

            }


        });
        _openOrdersWindow.doLayout();
        _openOrdersWindow.show();
        _openOrdersWindow.center();
    };
    var orderDetails = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: '',
            height: 200,
            autoScroll: true,
            id: 'incompleteOrederDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table" colspan=2>',
                    '<tr><th width="20%">TO No.</th><td> {fsto_uid}</td><th width="20%">Order Type</th><td> {fsto_ordertypeName} </td></tr>',
                    '<tr><th width="20%">From </th><td>{sourcename}</td><th width="20%">To </th><td>{branch}</td></tr>',
                    '<tr><th width="20%">Order No </th><td> {paOrderNumber} </td><th width="20%">Order Date </th><td> {paOrderDate} </td></tr>',
                    '<tr><th width="20%">Customer Name </th><td> {customerName} </td><th width="20%">Customer Number </th><td> {customerNumber} </td></tr>',
                    '<tr><th width="20%">Order Value </th><td> {totalAmt} </td><th width="20%">Total Items </th><td> {totalItems} </td></tr>',
                    '<tr><th width="20%">Total Quantity </th><td> {fsto_ItemQty} </td><th width="20%">Total Packed </th><td> {fsto_pkdQty} </td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
    return{
        Cache: {},
        initIncompleteOrder: function () {
            var panelid = 'retalineIncompleteOrder_main_panel';
            var order_panel = Ext.getCmp(panelid);
            if (Ext.isEmpty(order_panel)) {
                order_panel = retalineIncompleteOrderGrid(panelid);
                Application.UI.addTab(order_panel);
                order_panel.doLayout();
            } else {
                Application.UI.addTab(order_panel);
            }
            return order_panel;
        },
        cancelOrder: function (fsto_id, fsto_ordertype) {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'cancelInComplOrder',
                    fsto_id: fsto_id,
                    fsto_ordertype: fsto_ordertype
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);
                    console.log('tmp', tmp);
                    if (tmp.status == 'true') {
                        Application.example.msg('Success', tmp.msg);
                        Ext.getCmp('incompleteOrderWindow').close();
                        var branchName = Ext.getCmp('comboxbranchnamess').getValue();
                        Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getStore().load({
                            params: {
                                branchName: branchName
                            }
                        });
                    } else {
                        Ext.Msg.alert("Error", tmp.msg);
                    }

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.message);
                }
            });
        }, orderViewMode: function () {

            var fsto_id = arguments[0];
            Ext.Ajax.request({
                url: modURL + '&op=incompOrderDetailsView',
                method: 'POST',
                params: {fsto_id: fsto_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('incompleteOrederDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('incompleteOrederDetailsViewPanel').doLayout();
        }, skipOrder: function (order_id, orderType) {
            var win_canc_id = "windowSkipOrder";
            var win_canc = Ext.getCmp(win_canc_id);
            if (Ext.isEmpty(win_canc)) {
                win_canc = new Ext.Window({
                    id: win_canc_id,
                    title: 'Reason for skipping',
                    layout: 'fit',
                    width: 400,
                    height: 150,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    bodyStyle: {"background-color": "white"},
                    items: new Ext.Panel({
                        autoHeight: true,
                        border: false,
                        layout: 'column',
                        bodyStyle: {"background-color": "white", "padding": "5px"},
                        items: [{
                                layout: 'form',
                                columnWidth: 1,
                                border: false,
                                hideLabel: true,
                                hideBorders: true,
                                // bodyStyle: {"background-color": "white"},
                                items: [{
                                        fieldLabel: 'Reason',
                                        xtype: 'textarea',
                                        id: 'skipReason',
                                        name: 'skipReason',
                                        allowBlank: false,
                                        anchor: '99%'
                                    }]
                            }]
                    }),
                    buttonAlign: 'right',
                    fbar: [{
                            text: 'Save',
                            xtype: 'button',
//                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            width: 80,
                            handler: function () {

                                var reason = Ext.getCmp('skipReason').getValue();
                                if (reason != '') {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=skipincompleteOrder',
                                        method: 'POST',
                                        params: {
                                            fstoId: order_id,
                                            fsto_ordertype: orderType,
                                            reason: reason
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                var tmp = Ext.decode(response.responseText);
                                                Application.example.msg('Success', tmp.msg);
                                                var branchName = Ext.getCmp('comboxbranchnamess').getValue();
                                                Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getStore().load({
                                                    params: {
                                                        branchName: branchName
                                                    }
                                                });
                                                Ext.getCmp('incompleteOrderWindow').close();
                                                win_canc.close();
                                            }
                                            else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        }
                                    });
                                }
                                else {
                                    Ext.MessageBox.alert('Notification', 'Please select a reason for cancellation');
                                }


                            }
                        }]


                });
            }
            win_canc.doLayout();
            win_canc.show(this);
            win_canc.center();
        }
    }
}();
