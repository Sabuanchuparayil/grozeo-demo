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
                'fsto_destination', 'fsto_type', 'fsto_ItemWeight', 'fsto_ItemVolume', 'fsto_ordertypeName', 'isskipped', 'parentOrder','fsto_ordertype','parentOrderMain','fsto_createdOn','fsto_pickingNumber'
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
                    dataIndex: 'fsto_ordertypeName'
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
                }, {
                    header: 'Order Id',
                    dataIndex: 'parentOrder',
                    sortable: true,
                    tooltip: 'Order Id',
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
                    hideable: true
                },
                {
                    header: 'Weight',
                    dataIndex: 'fsto_ItemWeight',
                    sortable: true,
                    tooltip: 'Weight',
                    hidden: true
                },
                {
                    header: 'Volume',
                    dataIndex: 'fsto_ItemVolume',
                    sortable: true,
                    tooltip: 'Volume',
                    hidden: true
                },
                {
                    header: 'Type',
                    dataIndex: 'fsto_ordertypeName',
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
                },{
                    header: 'Picking Number',
                    dataIndex: 'fsto_pickingNumber',
                    sortable: true,
                    tooltip: 'Picking Number',
                    hideable: true
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
                            incompleteOrderActionMenu(record).showAt(e.getXY());
                        }
                    }
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('isskipped') > 0)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    }
                },
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
                    if (_SESSION.br_PyramidLevel == 1) {
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
    var incompleteOrderActionMenu = function (record) {
        var incmpleteMenu = new Ext.menu.Menu({
            items: [{
                    text: 'View & Update Orders',
                    handler: function () {
                        var fsto_id = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_id;
                        var fsto_ordertypeName = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_ordertypeName;
                        var fsto_status = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_status;
                        var fsto_ordertype = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_ordertype;
                        
                        var fsto_uid = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_uid;
                        var fsto_source = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_source;
                        var fsto_sourceName = Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getSelectionModel().getSelections()[0].data.fsto_sourceName;
                        openincompOrdersWindow(fsto_id, fsto_ordertypeName,fsto_ordertype,fsto_status,fsto_uid,fsto_source,fsto_sourceName);
                    }
                }]
        });

        return incmpleteMenu;
    };
    var openOrderGridStore = function (fsto_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getOrderItemList',
                method: 'post'
            }),
            fields: ['slNo', 'item_name', {name: 'fsto_ItemQty', type: 'float'}, {name: 'fsto_pkdQty', type: 'float'}, 'mrp', 'fsto_ItemId', 'fsto_id', 'fsto_source', 'selPrce', 'fsto_stockValue', 'stit_ConvertCalcRate', 'fstro_ItemPackedSPincTax', 'spValue_diff', 'diff_conversion'],
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
            height: 300,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelOpenOrderGrid',
            columns: [
                {
                    header: 'Sl No',
                    dataIndex: 'slNo',
                    sortable: true,
                    tooltip: 'Sl No',
                    hideable: false,
                    align: 'right',
                    width: 80
                },
                {
                    header: 'Item',
                    dataIndex: 'item_name',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true,
                    width: 200
                },
                {
                    header: 'MRP',
                    dataIndex: 'mrp',
                    sortable: true,
                    tooltip: 'MRP',
                    hideable: true,
                    hidden: true
                },
                {
                    header: 'No of Items',
                    dataIndex: 'fsto_ItemQty',
                    sortable: true,
                    tooltip: 'No of Items',
                    align: 'right',
                    hideable: true
                },{
                    header: 'Items Picked',
                    dataIndex: 'fsto_pkdQty',
                    sortable: true,
                    tooltip: 'Items Picked',
                    align: 'right',
                    hideable: true
                }, {
                    header: 'Required Qty',
                    sortable: true,
                    dataIndex: 'stit_ConvertCalcRate',
                    hideable: false,
                    tooltip: 'Required Qty',
                    align: 'right',
                    groupable: false
                }, {
                    header: 'Picked Qty',
                    sortable: true,
                    dataIndex: 'fsto_stockValue',
                    hideable: false,
                    tooltip: 'Picked Qty',
                    align: 'right',
                    groupable: false
                }, {
                    header: 'Qty Diff',
                    sortable: true,
                    dataIndex: 'diff_conversion',
                    hideable: true,
                    tooltip: 'Qty Differrence',
                    align: 'right',
                    groupable: false,
                },
                {
                    header: 'Value',
                    dataIndex: 'selPrce',
                    sortable: true,
                    tooltip: 'Price',
                    align: 'right',
                    hideable: true
                }, {
                    header: 'New Value',
                    dataIndex: 'fstro_ItemPackedSPincTax',
                    sortable: true,
                    tooltip: 'New Value',
                    align: 'right',
                    hideable: true
                }, {
                    header: 'Difference',
                    dataIndex: 'spValue_diff',
                    sortable: true,
                    tooltip: 'Difference',
                    align: 'right',
                    hideable: true
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    hidden: true,
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
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('stit_ConvertCalcRate') != record.get('fsto_stockValue')) { /* Partial entryr   */
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                }
            },
            tbar: []

        });
        return _manualPackingGridPanel;
    };

    var openincompOrdersWindow = function (fsto_id, fsto_ordertypeName,fsto_ordertype,fsto_status,fsto_uid,fsto_source,fsto_sourceName) {
        var fsto_id = fsto_id;
        var _openOrdersWindow = new Ext.Window({
            title: 'Order Details',
            id: 'incompleteOrderWindow',
            layout: 'border',
            width: winsize.width * 0.6,
            height: winsize.height * 0.7,
            resizable: false,
            draggable: true,
            modal: true,
            closable: true,
            bodyStyle: {"background-color": "white"},
            items: [{
                    region: 'north',
                    height: 250,
                    items: orderDetails()
                }, {
                    region: 'center',
                    layout: 'fit',
                    height: 300,
                    items: openOrdersGrid(fsto_id)
                }],
            buttons: [{
                    text: 'Cancel Entire Order',
                    tabIndex: 511,
                    handler: function () {
                        Ext.MessageBox.confirm('Confirm', 'You are going to Cancel the Order ?', function (btn) {
                            if (btn == 'yes') {
                                AddNewOrderpickerWindow(fsto_id, fsto_ordertypeName,fsto_uid, fsto_source, fsto_sourceName);
                            }
                        });

                    }
                }, {
                    text: 'Skip',
                    tabIndex: 511,
                    handler: function () {
                        Application.RetalineIncompleteOrder.skipOrder(fsto_id, fsto_ordertypeName);
                    }
                }, {
                    text: 'Proceed',
                    id: 'submit_button',
                    handler: function () {
                        var itemGriddata = getMigratedData('gridpanelOpenOrderGrid');
                        var fsto_id = Ext.getCmp("gridpanelOpenOrderGrid").getSelectionModel().getSelections()[0].data.fsto_id;
                        var itemStore = Ext.getCmp('gridpanelOpenOrderGrid').getStore();
                        var fsto_pkdQty = itemStore.sum('fsto_pkdQty');
                        if (fsto_pkdQty > 0) {
                            if (fsto_status == 9) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=proceedWithAvail',
                                    method: 'POST',
                                    params: {
                                        fsto_id: fsto_id,
                                        fsto_ordertypeName: fsto_ordertypeName
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp("gridpanelAddnewIncompleteOrderdata").getStore().load();
                                            _openOrdersWindow.close();
                                        } else if (tmp.success === false) {
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
                                Ext.Ajax.request({
                                    url: modURL + '&op=calculatePickedAmount',
                                    method: 'POST',
                                    params: {
                                        fsto_id: fsto_id,
                                        fsto_ordertypeName: fsto_ordertypeName
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        console.log(tmp);
                                        if (tmp.success === true) {
                                            console.log('fsto_ordertype',fsto_ordertype);
                                            if (fsto_ordertype == '1') {
                                                payCashWindow(tmp.total, tmp.newTotal, tmp.walletAmount, tmp.fsto_id, tmp.fstr_id, tmp.orderUid);
                                            }
                                        } else {
                                            Ext.Msg.alert("Error", tmp.data);
                                        }

                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.msg);
                                    }
                                });
                            }

                        } else {
                            Ext.MessageBox.alert('Notification', 'Sorry, No items picked for this order');
                        }


                    }

                }, , {
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
                                        fsto_ordertypeName: fsto_ordertypeName
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            var branchName = Ext.getCmp('comboxbranchnamess').getValue();
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
            height: 250,
            autoScroll: true,
            id: 'incompleteOrederDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table" colspan=2>',
                    '<tr><th width="20%">Order No </th><td> {paOrderNumber} </td><th width="20%">Order Type</th><td> {fsto_ordertypeName} </td></tr>',
                    '<tr><th width="20%">From </th><td>{sourcename}</td><th width="20%">To </th><td>{branch}</td></tr>',
                    '<tr><th width="20%">TO No.</th><td> {fsto_uid}</td><th width="20%">Order Date </th><td> {paOrderDate} </td></tr>',
                    '<tr><th width="20%">Customer Name </th><td> {customerName} </td><th width="20%">Customer Number </th><td> {customerNumber} </td></tr>',
                    '<tr><th width="20%">No of Items</th><td> {totalItems} </td><th width="20%">Total Quantity </th><td> {fsto_ItemQty} </td></tr>',
                    '<tr><th width="20%">Value of Order</th><td> {totalAmt} </td><th width="20%">Total items to be weighed</th><td> {fsto_pkdQty} </td></tr>',
                    '<tr><th width="20%">Updated Value of Order</th><td> {newTotal} </td><th width="20%">Total items actually weighed</th><td> {fsto_conversionpkdQty} </td></tr>',
                    '<tr><th width="20%">Balance To Pay</th><td> {balanceToPay} </td><th width="20%">Orginal Mode of Payment </th><td> {payment_modename} </td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
    var payCashWindow = function (total, newTotal, walletAmt, fstoId, OrderId, orderUid) {
        var payment_form = cashForm(total, newTotal, walletAmt, fstoId, OrderId);
        var paymentWin = new Ext.Window({
            id: 'cashPayWindow',
            title: 'Change in order - ' + orderUid,
            iconCls: 'store_search',
            modal: true,
            constrain: true,
            width: 500, //80%
            resizable: true,
            floating: true,
            shadow: false,
            items: [payment_form],
            buttons: [{
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    cls: 'left-right-buttons',
                    tabIndex: 23,
                    handler: function () {
                        paymentWin.close();
                    }
                }, {
                    text: 'Proceed To Packing',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    cls: 'left-right-buttons',
                    tabIndex: 98,
                    width: 80,
                    handler: function () {
                        var orderAmount = Ext.getCmp('order_actual_total').getValue();
                        var orderAmountToPay = Ext.getCmp('order_amt_topay').getValue();
                        var balancePaymentMode1 = Ext.getCmp('balancePaymentMode1').getValue();
                        Ext.Ajax.request({
                            url: modURL + '&op=updatePackedAmt',
                            method: 'POST',
                            params: {
                                fstoId: fstoId,
                                OrderId: OrderId,
                                total: total,
                                newTotal: newTotal,
                                orderAmount: orderAmount,
                                orderAmountToPay: orderAmountToPay,
                                balancePaymentMode1: balancePaymentMode1
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
                                    paymentWin.close();
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
                }], listeners: {
                afterrender: function () {

                    if (newTotal < total) {
                        Ext.getCmp('paymentmodefieldset').disable();
                        Ext.getCmp('order_actual_total').hide();
                        Ext.getCmp('order_actual_total').hide();
                        Ext.getCmp('order_amt_topay').hide();

                        Ext.getCmp('balanceAmtLabel').show();
                    }
                }

            }
        });
        var paymentWin = Ext.getCmp('cashPayWindow');
        paymentWin.show();
        paymentWin.doLayout();
        paymentWin.center();
    };
    var cashForm = function (total, newTotal, walletAmt, fstoId, OrderId) {
        Application.RetalineIncompleteOrder.Cache.debitamtToPay = 0;
        var form = new Ext.form.FormPanel({
            frame: false,
            border: false,
            hideBorders: true,
            id: 'cash-payment-form',
            autoScroll: true,
            labelWidth: 120,
            height: 200,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            defaults: {style: {padding: 3}},
            layout: 'column',
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.30,
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Value of Order',
                            id: 'order_total',
                            name: 'order_total',
                            anchor: '98%',
                            readOnly: true,
                            value: total,
                            tabIndex: 96
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.30,
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Updated Value of Order',
                            id: 'order_new_total',
                            minValue: 0.05,
                            name: 'order_new_total',
                            anchor: '98%',
                            readOnly: true,
                            value: newTotal,
                            tabIndex: 97,
                            listeners: {
                                afterrender: function (field) {
                                    field.focus(false, 100);
                                },
                                focus: function () {
                                    var order_actual_total = Ext.getCmp('order_actual_total').getValue();
                                    var amtToPay = (order_actual_total - total);
                                    amtToPay = Math.round(amtToPay * 100) / 100;
                                    if (amtToPay >= walletAmt) {
                                        Ext.getCmp('balancePaymentMode3').disable();
                                    }
                                    if (amtToPay > 0) {
                                        Ext.getCmp('order_amt_topay').setValue(amtToPay);
                                    } else {
                                        Ext.getCmp('paymentmodefieldset').disable();
                                        Ext.getCmp('balanceAmtLabel').show();
                                        Application.RetalineIncompleteOrder.Cache.debitamtToPay = amtToPay;
                                    }


                                }
                            }
                        }, {
                            xtype: 'numberfield',
                            fieldLabel: 'Enter Amount',
                            id: 'order_actual_total',
                            minValue: 0.05,
                            hidden: true,
                            name: 'order_actual_total',
                            anchor: '98%',
                            tabIndex: 98,
                            value: newTotal,
                            listeners: {
                                change: function () {
                                    var order_actual_total = Ext.getCmp('order_actual_total').getValue();
                                    var amtToPay = (order_actual_total - total);
                                    amtToPay = Math.round(amtToPay * 100) / 100;
                                    if (amtToPay >= walletAmt) {
                                        Ext.getCmp('balancePaymentMode3').disable();
                                    }
                                    if (amtToPay > 0) {
                                        Ext.getCmp('order_amt_topay').setValue(amtToPay);
                                    } else {
                                        Ext.getCmp('paymentmodefieldset').disable();
                                        Ext.getCmp('balanceAmtLabel').show();
                                        Application.RetalineIncompleteOrder.Cache.debitamtToPay = Math.abs(amtToPay);
                                    }


                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.30,
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Amount to Pay',
                            id: 'order_amt_topay',
                            minValue: 0.05,
                            name: 'order_amt_topay',
                            anchor: '98%',
                            readOnly: true,
                            tabIndex: 99
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 1,
                    items: [{
                            xtype: 'fieldset',
                            title: 'Payment Mode',
                            collapsible: false,
                            id: 'paymentmodefieldset',
                            items: [{
                                    xtype: 'radiogroup',
                                    anchor: '98%',
                                    hideLabel: true,
                                    mode: 'remote',
                                    id: 'balancePaymentMode1',
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    tabIndex: 100,
                                    items: [{
                                            boxLabel: 'Online',
                                            name: 'balancePaymentMode',
                                            inputValue: '2',
                                            disabled: true
                                        }, {
                                            boxLabel: 'Wallet',
                                            id: 'balancePaymentMode3',
                                            name: 'balancePaymentMode',
                                            inputValue: '3',
                                        }, {
                                            boxLabel: 'Pay On Delivery',
                                            name: 'balancePaymentMode',
                                            inputValue: '1',
                                        }]
                                }
                            ]
                        }, {
                            xtype: 'label',
                            id: 'balanceAmtLabel',
                            text: 'The balance amount will debit to wallet ' + Application.RetalineIncompleteOrder.Cache.debitamtToPay,
                            style: 'padding-top:10px;font-weight:bold;padding-bottom:10px;',
                            hidden: true
                        }]
                }
            ]
        });
        return form;
    };
    var AddNewOrderpickerWindow = function (fsto_id, fsto_ordertypeName,fsto_uid, fsto_source, fsto_sourceName) {
        var _Orderpickerform = AddNewOrderPickerForm(fsto_source);
        var _addNewWindow = new Ext.Window({
            title: 'Assign Order Picker',
            layout: 'fit',
            width: winsize.width * 0.6,
            height: winsize.height * 0.8,
            resizable: false,
            draggable: true,
            closable: true,
            modal: true,
            bodyStyle: {"background-color": "white"},
            items: [orderPickergrid(fsto_source, fsto_sourceName)],
            fbar: [{
                    xtype: 'button',
                    text: 'Replenish Manually',
                    tabIndex: 511,
                    handler: function () {

                        Ext.Ajax.request({
                            url: modURL + '&op=submitManualReplenish',
                            method: 'POST',
                            params: {
                                order_id: fsto_id,
                                fsto_uid: fsto_uid,
                            },
                            success: function (response) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.status == 'ok') {
                                    Application.example.msg('Success', tmp.msg);
                                    _addNewWindow.close();
                                    Application.RetalineIncompleteOrder.cancelOrder(fsto_id, fsto_ordertypeName);
                                } else {
                                    Ext.Msg.alert("Error", tmp.error.msg);
                                }

                            },
                            failure: function (response) {
                                var tmp = Ext.util.JSON.decode(response.responseText);
                                Ext.MessageBox.alert('Error', tmp.message);
                            }
                        });

                    }
                }, '->', {
                    xtype: 'button',
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    tabIndex: 511,
                    handler: function () {
                        _addNewWindow.close();
                    }
                }, {
                    xtype: 'button',
                    text: 'Assign',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    tabIndex: 511,
                    id: 'assign_pickr',
//                    hidden:true,
                    handler: function () {

                        var executive_id = Ext.getCmp("gridpanelAddOrderPicker").getSelectionModel().getSelections()[0].data.id;
                        var executiveStatus = Ext.getCmp("gridpanelAddOrderPicker").getSelectionModel().getSelections()[0].data.is_offline;
                        var executivename = Ext.getCmp("gridpanelAddOrderPicker").getSelectionModel().getSelections()[0].data.name;
                        var order_ID = fsto_id;
                        var order_NO = fsto_uid;
                        var branch_ID = fsto_source;
                        var type = 0;

                        if ((executive_id > 0) && (executiveStatus == 0))
                        {

                            Ext.Ajax.request({
                                url: modURL + '&op=assignOrderPicker',
                                method: 'POST',
                                params: {
                                    id: executive_id,
                                    order_ID: order_ID,
                                    br_ID: branch_ID,
                                    type: type,
                                    order_NO: order_NO

                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.status == 'ok') {
                                        Application.example.msg('Success', tmp.msg);
                                        _addNewWindow.close();
                                        Application.RetalineIncompleteOrder.cancelOrder(fsto_id, fsto_ordertypeName);
                                    } else {
                                        Ext.Msg.alert("Error", tmp.error.msg);
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
                            Ext.MessageBox.alert("Notification", 'The Order Picker ' + executivename + ' is offline');
                        }

                    }
                }]
        });
        _addNewWindow.doLayout();
        _addNewWindow.show();
        _addNewWindow.center();
    };
    var AddNewOrderPickerForm = function (fsto_source) {
        var _OrderPickerFormPanel = new Ext.form.FormPanel({
            frame: false,
            border: false,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 80,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                }, {
                    xtype: 'combo',
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    mode: 'local',
                    id: 'cpdnameid',
                    forceSelection: true,
                    fieldLabel: 'Store',
                    emptyText: 'CPD',
                    anchor: '98%',
                    typeAhead: true,
                    triggerAction: 'all',
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 102,
                    store: rtrBranchStore(fsto_source),
                    listeners: {
                        select: function () {
                            Ext.getCmp('orderPicker').getStore().load({
                                params: {
                                    pickerBranch: Ext.getCmp('cpdnameid').getValue()
                                }
                            })
                        }
                    }

                },
                {
                    xtype: 'combo',
                    displayField: 'Picker',
                    valueField: 'id',
                    mode: 'local',
                    id: 'orderPicker',
                    forceSelection: true,
                    fieldLabel: 'Picker',
                    emptyText: 'picker',
                    anchor: '98%',
                    typeAhead: true,
                    triggerAction: 'all',
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 102,
                    store: orderPickerStore()

                }
            ]
        });
        return _OrderPickerFormPanel;
    };
    var rtrBranchStore = function (fsto_source) {
        var store = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranches',
            method: 'post',
            autoLoad: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fsto_source = fsto_source;
                }
            }
        });
        return store;
    };
    var orderPickerStore = function (fsto_source) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getOrderPicker',
                method: 'post'
            }),
            fields: ['id', 'name', 'has_open_orders', 'phone', 'is_offline', 'liveStatus'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fsto_source = fsto_source;
                },
                load: function () {
                    Ext.getCmp('gridpanelAddOrderPicker').getSelectionModel().selectRow(0);
                }
            }
        });
        return _Store;
    };
    var orderPickergrid = function (fsto_source, fsto_sourceName) {
        var _orderPickerStore = orderPickerStore(fsto_source);
        var _orderPickergridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _orderPickerStore,
            //iconCls: 'money',
            autoScroll: true,
            width: 400,
            height: 250,
            bodyStyle: {"background-color": "white"},
            //selModel: ck_selection,
            id: 'gridpanelAddOrderPicker',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    dataIndex: 'name',
                    sortable: true,
                    tooltip: 'Name',
                    hideable: true
                },
                {
                    header: 'Mobile',
                    dataIndex: 'phone',
                    sortable: true,
                    tooltip: 'Mobile',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'liveStatus',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: true
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('is_offline') == 0)
                    {
                        return 'finascop_indicateColPALEGREEN';
                    }
                }
            },
            tbar: [{
                    xtype: 'combo',
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    mode: 'local',
                    id: 'cpdnameid',
                    forceSelection: true,
                    fieldLabel: 'Store',
                    emptyText: 'Select Branch',
                    anchor: '98%',
                    typeAhead: true,
                    triggerAction: 'all',
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 102,
                    store: rtrBranchStore(fsto_source),
                    value: fsto_sourceName,
                    hideTrigger: true,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelAddOrderPicker').getStore().load({
                                params: {
                                    pickerBranch: Ext.getCmp('cpdnameid').getValue()
                                }
                            })
                        }
                    }

                }
            ]

        });
        return _orderPickergridPanel;
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
        cancelOrder: function (fsto_id, fsto_ordertypeName) {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'cancelInComplOrder',
                    fsto_id: fsto_id,
                    fsto_ordertypeName: fsto_ordertypeName
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);
                    console.log('tmp', tmp);
                    if (tmp.success == true) {
                        Application.example.msg('Success', tmp.msg);
                        Ext.getCmp('incompleteOrderWindow').close();
                        var branchName = Ext.getCmp('comboxbranchnamess').getValue();
                        Ext.getCmp('gridpanelAddnewIncompleteOrderdata').getStore().load({
                            params: {
                                branchName: branchName
                            }
                        });
                        Ext.getCmp('incompleteOrderWindow').close();
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
                                labelAlign: 'top',
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
                                            fsto_ordertypeName: orderType,
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
                                    Ext.MessageBox.alert('Notification', 'Provide a reason for cancellation');
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
