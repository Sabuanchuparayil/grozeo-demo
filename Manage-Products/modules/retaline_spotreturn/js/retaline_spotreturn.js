Application.SpotReturn = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var modURL = '?module=retaline_spotreturn';
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

        if (!Ext.isEmpty(Ext.getCmp('manage_order_spotreturn_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_spotreturn_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.SpotReturn.Cache.orderAutoId = ID;
            Application.SpotReturn.UpdateViewMode(ID);
        }
    };

    var orderTopPanel = function (id) {

        var src = '?module=retaline_spotreturn';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Spot Return',
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
                    id: 'order_spotreturn_manage_parent_panel',
                    width: winsize.width * 0.45,
                    items: [{
                            id: 'order_spotreturn_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_spotreturn_orderdtls" name="iframe_spotreturn_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };
    var manageOrderStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getOrdersinSprtReturn',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on', 'order_qty',
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
                    var branchName = Ext.getCmp('comboxbranchOrderSpotReturn').getRawValue();
                    if (branchName != '') {
                        this.baseParams.br_Name = branchName;
                    }
                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_spotreturn_grid').getView().refresh();
                    Ext.getCmp('manage_order_spotreturn_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_spotreturn_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_spotreturn_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.SpotReturn.Cache.orderAutoId = ID;
                        Application.SpotReturn.UpdateViewMode(Application.SpotReturn.Cache.orderAutoId);
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
            id: 'manage_order_spotreturn_grid',
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
                    header: 'Delivery To',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Delivery To',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Order Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Order Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            tooltip: 'Accept Return',
                            iconCls: 'assign',
                            handler: function (grid, rowIndex, colIndex) {
                                var grecord = grid.store.getAt(rowIndex);
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
                                        Application.SpotReturn.Cache.uuid = uid;
                                        getReturnedItems(grecord.get('order_id'), grecord.get('order_order_id'), grecord.get('order_qty'));
                                    }
                                });

                            }
                        }
                    ]

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
                    id: 'textboxBrmCpdOrderSpotReturn',
                    name: 'textboxBrmCpdOrderSpotReturn',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchOrderSpotReturn',
                    name: 'comboxbranchOrderSpotReturn',
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
                    hiddenName: 'comboxbranchOrderSpotReturn',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchOrderSpotReturn').getRawValue();
                            Ext.getCmp('manage_order_spotreturn_grid').getStore().load({
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
                        Ext.getCmp("comboxbranchOrderSpotReturn").show();
                        Ext.getCmp("textboxBrmCpdOrderSpotReturn").hide();
                    }
                    else {
                        Ext.getCmp("textboxBrmCpdOrderSpotReturn").show();
                        Ext.getCmp("textboxBrmCpdOrderSpotReturn").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchOrderSpotReturn").hide();
                    }
                    if (Ext.getCmp('textboxBrmCpdOrderSpotReturn').getValue() != '')
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
    var getReturnedItems = function (order_id, order_order_id, order_qty) {
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
            items: [_returnedItemsGrid(order_id, order_order_id, order_qty)],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        _addnewItemsWindow.close();
                        Ext.getCmp('manage_order_grid').getStore().load();
                    }

                }
            ]

        });
        _addnewItemsWindow.doLayout();
        _addnewItemsWindow.show();
        _addnewItemsWindow.center();
    };
    var _returnedItemsGrid = function (order_id, order_order_id, order_qty) {

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
                            tooltip: 'Return',
                            getClass: function (v, meta, rec) {
                                console.log('returnCount', typeof (rec.get('returnCount')));
                                if ((rec.get('returnCount') == 0)) {
                                    return 'finascop_invoice';

                                } else {
                                    return 'hideicon';
                                }

                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var grecord = grid.store.getAt(rowIndex);
                                console.log('grecord', grecord);
                                updateSpotReturn(order_id, grecord.data.item_product_id, grecord.data.item_order_qty, grecord, grecord.data.itemName, grecord.data.stiid_barcode);
//                                Ext.Ajax.request({
//                                    url: modURL + '&op=moveRetunItemstoStock',
//                                    method: 'POST',
//                                    params: {
//                                        order_id: order_id,
//                                        stiid_barcode: grecord.data.stiid_barcode,
//                                        order_order_id: order_order_id,
//                                        itemId: grecord.data.itemId,
//                                        stiid_id: grecord.data.stiid_id,
//                                    },
//                                    success: function (response) {
//
//                                        var tmp = Ext.decode(response.responseText);
//                                        if (tmp.success == true) {
//                                            Application.example.msg('Success', tmp.msg);
//                                            Ext.getCmp('gridpanelforReturnredItems').store.load({
//                                                params: {
//                                                    order_id: order_id,
//                                                    order_order_id: order_order_id
//                                                }
//                                            });
//
//
//                                        } else {
//                                            Ext.MessageBox.alert("Notification", tmp.msg);
//                                        }
//
//                                    },
//                                    failure: function (response, options) {
//                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
//                                    }
//                                });

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
            fields: ['order_id', 'stiid_barcode', 'itemId', 'itemName', 'stiid_id', 'item_sales_price', 'returnCount', 'item_return_sellableqty', 'item_return_damagedqty', 'item_return_qty_requested','item_order_qty','item_product_id'],
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
    var updateSpotReturn = function (order_id, itemId, item_order_qty, record, itemName, barcodesearch_fieldrt) {
        if (Ext.isEmpty(upReturnQty_window)) {
            var upReturnQty_window = new Ext.Window({
                id: 'upReturnQty_window',
                layout: 'fit',
                width: 400,
                title: 'Accept Spot Return',
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
                            var itemReturn_type1 = Ext.getCmp('itemReturn_type1').getValue();
                            var itemReturn_type2 = Ext.getCmp('itemReturn_type2').getValue();
                            var itemReturn_type3 = Ext.getCmp('itemReturn_type3').getValue();
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
var returnCount = record.data.returnCount;
                            console.log('returnableQty', returnableQty);
                            console.log('pkdQuantity', pkdQuantity);
                            if (returnCount == 0) {

                                Ext.Ajax.request({
                                    url: modURL + '&op=saveItemtoSpotReturnOrder',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.SpotReturn.Cache.uuid,
                                        itemReturn_type1qty: itemReturn_type1qty,
                                        itemReturn_type2qty: itemReturn_type2qty,
                                        itemReturn_type3qty: itemReturn_type3qty,
                                        returnableQty: returnableQty,
                                        totalReturn: 1,
                                        returned_qty: pkdQuantity,
                                        order_id: order_id,
                                        itemId: itemId,
                                        item_order_qty: item_order_qty,
                                        barcodesearch_fieldrt: barcodesearch_fieldrt
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        console.log('tmp', tmp);
                                        if (tmp.success === true && tmp.valid === true) {

                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('gridpanelforReturnredItems').getStore().load({
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
                                Ext.Msg.alert('Notification', 'This item returned.');
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
                                    barcodesearch_fieldrt: barcodesearch_fieldrt
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        upReturnQty_window.close();

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
                    }]
            });

        }
        upReturnQty_window.doLayout();
        upReturnQty_window.show(this);
        upReturnQty_window.center();
    };
    return {
        Cache: {},
        orderSpotReturn: function () {

            var panelId = 'order_spotreturn_processing_panel';
            var SpotReturnPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(SpotReturnPanel)) {
                SpotReturnPanel = orderTopPanel(panelId);
                Application.UI.addTab(SpotReturnPanel);
                SpotReturnPanel.doLayout();
            } else {
                Application.UI.addTab(SpotReturnPanel);
            }

        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('order_spotreturn_details_view_panel').show();

            Ext.get('iframe_spotreturn_orderdtls').dom.src = modURL + '&op=order_detailsspret&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        }
    };
}();