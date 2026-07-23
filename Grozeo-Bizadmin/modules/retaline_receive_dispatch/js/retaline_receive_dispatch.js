Application.Branch_Receive_Dispatch = function () {
    var modURL = '?module=retaline_receive_dispatch';
    var winsize = Ext.getBody().getViewSize();
    var RECS_PER_PAGE = 12;
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var receivedispatch_array = new Array();
    var receiveorder_array = new Array();

    var receiveItemDetails = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: 'Item Details',
            height: 200,
            autoScroll: true,
            id: 'receiveStockPoItemDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Item name</th><td> {item_name} </td></tr>',
                    '<tr><th width="40%">Ordered Quantity </th><td>{fsto_ItemQty}</td></tr>',
                    '<tr><th width="40%">MRP per {ccs_package_type_name}</th><td> {itemMRP} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
    var EditReceiveDispatchWindow = function (fsto_ItemId, fsto_id, item_name, fsto_ItemQty, fsto_ItemQtyL3Received) {
        var _addnewWindow = new Ext.Window({
            id: 'windowReceiveDispatchDataviewOrderDetails',
            title: 'Receive Dispatch Details',
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
                    items: receiveItemDetails()
                }, {
                    region: 'center',
                    layout: 'fit',
                    height: 150,
                    items: ReceiveDispatchForm(fsto_ItemId, fsto_id, item_name, fsto_ItemQty, fsto_ItemQtyL3Received)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 250,
                    items: receiveItemStockGrid(fsto_ItemId, fsto_id, item_name)
                }],
            fbar: [
                /* <?php if (user_access("retaline_receive_dispatch", "ReceiveDispatchDetails")) { ?> */
                {
                    text: "Cancel",
                    cls: 'left-right-buttons',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    handler: function () {
                        _addnewWindow.close();
                    }

                }
                /* <?php } ?> */
            ], listeners: {
                afterrender: function () {
                    Application.Branch_Receive_Dispatch.itemViewMode(fsto_ItemId, fsto_id, item_name, fsto_ItemQty);
                }
            }

        });
        _addnewWindow.doLayout();
        _addnewWindow.show();
        _addnewWindow.center();
    };
    var receiveItemStockGrid = function (fsto_ItemId, fsto_id, item_name) {

        var poStockEntryGridStore = poStockEntryItemStore(fsto_ItemId, fsto_id, item_name);
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
                    header: 'Receive Date',
                    sortable: true,
                    dataIndex: 'fstro_receivedOn',
                    tooltip: 'Receive Date'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            getClass: function (v, meta, rec) {
                                if (_SESSION.br_PyramidLevel < 4) {
                                    this.items[0].tooltip = 'Print Barcode';
                                    return 'my-icon18';
                                }
                                else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                rePrintBarcode(record.get('stii_id'), fsto_id);
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
    var barcodeItemStore = function (stii_id, fsto_id) {
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
                        fsto_id: fsto_id
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
    var rePrintBarcode = function (stii_id, fsto_id) {
        var t = new Date();
        var t_stamp = t.format("YmdHis")
        var barcodeStore = barcodeItemStore(stii_id, fsto_id);
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
                frame: true,
                border: false,
                autoHeight: true,
                layout: 'column',
                bodyStyle: {"background-color": "#F1F1F1", "padding": "5px 3px 5px 8px"},
                //items: [rePrintBarcodePanel],
                items: [{
                        layout: 'form',
                        columnWidth: 0.95,
                        border: false,
                        labelAlign: 'top',
                        bodyStyle: {"background-color": "#F1F1F1"},
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
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon61',
                        handler: function () {
                            rePrintBarcodeWindow.close();
                        }
                    }, {
                        text: 'Print',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        iconCls: 'my-icon141',
                        handler: function () {
                            var params = {
                                action: 1
                            };
                            var stiid_barcode = Ext.getCmp('comboboxBarcode').getRawValue();
                            var stiid_Tobarcode = Ext.getCmp('comboboxToBarcode').getRawValue();
                            Application.Branch_Receive_Dispatch.Cache.stii_id = stii_id;
                            Ext.Ajax.request({
                                url: modURL + '&op=checkBarcode',
                                method: 'POST',
                                params: {stiid_barcode: stiid_barcode, stiid_Tobarcode: stiid_Tobarcode, stii_id: Application.Branch_Receive_Dispatch.Cache.stii_id, apikey: _SESSION.apikey, tstamp: t_stamp},
                                success: function (res) {
                                    var tmp = Ext.decode(res.responseText);
                                    if (tmp.success === true) {
                                        postToUrl(modURL + '&op=rePrintBarcodeOp', {stiid_barcode: stiid_barcode, stiid_Tobarcode: stiid_Tobarcode, stii_id: Application.Branch_Receive_Dispatch.Cache.stii_id, apikey: _SESSION.apikey, tstamp: t_stamp});
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
    var poStockEntryItemStore = function (fsto_ItemId, fsto_id, item_name) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listReceStockEntryItemStore',
            fields: ['stii_itemmastername', 'stii_batch', 'stii_qty', 'stii_expirydate', 'stii_id', 'stii_isgift', 'stii_createdon', 'fstro_receivedOn', 'fstro_receivedTime'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        fsto_ItemId: fsto_ItemId,
                        fsto_id: fsto_id,
                        item_name: item_name,
                        quor_id: Application.Branch_Receive_Dispatch.Cache.quor_id,
                        quor_TransferOrder_id: Application.Branch_Receive_Dispatch.Cache.quor_TransferOrder_id
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var ReceiveDispatchForm = function (fsto_ItemId, fsto_id, item_name, fsto_ItemQty, fsto_ItemQtyL3Received) {
        var _dispatchFormPanel = new Ext.form.FormPanel({
            id: 'formpanelReceiveDispatchDataentryReceiveDetails',
            height: 150,
            frame: true,
            border: false,
            layout: 'column',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
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

                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.35,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Item',
                            id: 'recItem',
                            name: 'recItem',
                            disabled: true,
                            value: item_name,
                            anchor: '85%'
                        }
                    ]

                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    items: [
                        {
                            xtype: 'datefield',
                            fieldLabel: 'Received Date',
                            id: 'textfieldreceiveddispatchReceiveddate',
                            name: 'textfieldreceiveddispatchReceiveddate',
                            anchor: '90%',
                            allowBlank: false,
                            tabIndex: 3,
                            value: new Date(),
                            format: "d/m/Y"
                        }
                    ]

                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    items: [
                        {
                            xtype: 'timefield',
                            fieldLabel: 'Received Time',
                            id: 'textfieldreceiveddispatchReceivedtime',
                            name: 'textfieldreceiveddispatchReceivedtime',
                            anchor: '90%',
                            //allowBlank: false,
                            tabIndex: 4,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }
                    ]

                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.10,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Quantity',
                            id: 'receiveStockItemQty',
                            name: 'receiveStockItemQty',
                            allowBlank: false,
                            hidden: true,
                            labelAlign: 'top',
                            anchor: '99%'
                        }
                    ]

                }, {
                    layout: 'form',
                    columnWidth: 0.10,
                    items: [{
                            xtype: 'button',
                            text: 'Save',
                            id: 'stockAddinPoItems',
                            anchor: '90%',
                            iconCls: 'finascop_add',
                            style: 'margin-top:17px;',
                            handler: function () {
                                if ((!Ext.isEmpty((Ext.getCmp("textfieldreceiveddispatchReceiveddate").getValue()))))
                                {

                                    Ext.Ajax.request({
                                        url: modURL + "&op=ReceiveDispatchDetailsold",
                                        method: "POST",
                                        params: {
                                            quor_id: Application.Branch_Receive_Dispatch.Cache.quor_id,
                                            quor_TransferOrder_id: Application.Branch_Receive_Dispatch.Cache.quor_TransferOrder_id,
                                            fsto_ItemQty: fsto_ItemQty,
                                            fsto_ItemQtyL3Received: fsto_ItemQtyL3Received,
                                            receive_date: Ext.getCmp("textfieldreceiveddispatchReceiveddate").getValue(),
                                            receive_time: Ext.getCmp("textfieldreceiveddispatchReceivedtime").getValue(),
                                            //receiveStockItemQty: Ext.getCmp("receiveStockItemQty").getValue(),
                                            receiveStockItemQty: fsto_ItemQty,
                                            fsto_ItemId: fsto_ItemId,
                                            fsto_id: fsto_id,
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            console.log("tmp", tmp);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg("Success", tmp.message);
                                                RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails"));
                                                receivedispatch_array = [];
                                                Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").store.reload({
                                                    params: {
                                                        start: 0,
                                                        limit: RECS_PER_PAGE
                                                    }
                                                });
                                                Ext.getCmp('windowReceiveDispatchDataviewOrderDetails').close();
                                            } else if (tmp.success === true && tmp.valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else if (tmp.success === true & tmp.img_valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else {
                                                Ext.Msg.alert("Error", tmp.message);
                                            }
                                        },
                                        failure: function (response) {
                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            Ext.MessageBox.alert("Error", tmp.message);
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert("Notification", "Please enter required data");
                                }

                            }

                        }]
                }
            ]
        });
        return _dispatchFormPanel;
    };
    var ck_selection = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true,
        checkOnly: true,
        listeners: {
            rowdeselect: function (sm, rowIndex, record) {
                console.log("Checkboxdeselection:", record);
                var ind = receivedispatch_array.indexOf(record.get('Value'));
                Application.Branch_Receive_Dispatch.Cache.Order_ID = record.data.order_id;
                console.log("de:ind", ind);
                if (ind > -1)
                    receivedispatch_array.splice(ind, 1);

                record.set('checked', 'false');
            },
            rowselect: function (sm, rowIndex, record) {
                console.log("Checkboxselection:", record);
                Application.Branch_Receive_Dispatch.Cache.Order_ID = record.data.order_id;
                var ind = receivedispatch_array.indexOf(record.get('Value'));
                console.log("se:ind", ind);
                if (ind == -1)
                    receivedispatch_array.push(record.get('Value'));

                record.set('checked', 'true');
            }
        }
    });
    var ReceiveDispatchOrdersItemsGrid = function () {
        console.log('Application.Branch_Receive_Dispatch.Cache.quor_id', Application.Branch_Receive_Dispatch.Cache.quor_id);
        var _Store = new Ext.data.GroupingStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listOrderItemData',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            },
            ['fsto_ItemId', 'item_name', 'fsto_ItemQty', 'fsto_id', 'fsto_ItemQtyL3Received', 'fstro_receivedOn', 'fstro_receivedTime', 'package_type']),
            remoteSort: true,
            autoLoad: false,
            groupField: '',
            groupDir: '',
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.quor_id = Application.Branch_Receive_Dispatch.Cache.quor_id;
                    this.baseParams.quor_TransferOrder_id = Application.Branch_Receive_Dispatch.Cache.quor_TransferOrder_id;
                }
            }
        });
        _Store.setDefaultSort('order_no', 'ASC');
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'south',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _Store,
            //iconCls: 'money',
            width: winsize.width * 0.4,
            height: winsize.height * 0.2,
            autoScroll: true,
            // height: 250,
            bodyStyle: {"background-color": "white"},
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fsto_ItemQtyL3Received') == record.get('fsto_ItemQty'))
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            id: 'gridpanelReceiveDispatchDataviewReceiveDetails',
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'Item Name',
                    dataIndex: 'item_name',
                    sortable: true,
                    tooltip: 'Item Name',
                    hideable: false
                },
                {
                    header: 'Quantity',
                    dataIndex: 'fsto_ItemQty',
                    sortable: true,
                    tooltip: 'Quantity',
                    hideable: false
                }, {
                    header: 'Quantity Received',
                    dataIndex: 'fsto_ItemQtyL3Received',
                    sortable: true,
                    tooltip: 'Quantity Received',
                    hideable: false
                }, {
                    header: 'Unit',
                    dataIndex: 'package_type',
                    sortable: true,
                    tooltip: 'Unit',
                    hideable: false
                }, {
                    header: 'Received Date',
                    dataIndex: 'fstro_receivedOn',
                    sortable: true,
                    tooltip: 'Received Date',
                    hideable: false
                }, {
                    header: 'Received Time',
                    dataIndex: 'fstro_receivedTime',
                    sortable: true,
                    tooltip: 'Received Time',
                    hideable: false
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'my-icon18',
                            tooltip: 'Add Stock',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                if (Application.Branch_Receive_Dispatch.Cache.quor_Deliverybr_id == _SESSION.finascop_current_branch_id) {
                                    EditReceiveDispatchWindow(record.get('fsto_ItemId'), record.get('fsto_id'), record.get('item_name'), record.get('fsto_ItemQty'), record.get('fsto_ItemQtyL3Received'));
                                } else {
                                    Ext.MessageBox.alert('Notification', 'Receicing branch is not matching');
                                }

                            }
                        }
                    ]

                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    _Store.load();
                    var me = this;
                    _Store.on('load', function () {
                        var data = me.getStore().data.items;
                        var recs = [];
                        Ext.each(data, function (item, index) {
                            if (item.data.checked == 1) {
                                recs.push(index);
                            }
                        });
                        me.getSelectionModel().selectRows(recs);
                    });
                }
            }
        });
        return _dispatchgridPanel;
    };
    var ReceiveDispatchOrdersGrid = function () {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listOrderData',
                method: 'post'
            }),
            fields: ['order_id', 'order_no', 'bcor_createdon', 'order_status', 'cpd_Name', 'branch_name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.quor_id = Application.Branch_Receive_Dispatch.Cache.quor_id
                }
            }
        });
        _Store.setDefaultSort('order_no', 'ASC');
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _Store,
            //iconCls: 'money',
            autoScroll: true,
            width: winsize.width * 0.4,
            height: 250,
            bodyStyle: {"background-color": "white"},
            selModel: ck_selection,
            id: 'gridpanelReceiveDispatchDataviewReceiveDetails',
            columns: [
                ck_selection,
                {
                    header: 'Order No.',
                    dataIndex: 'order_no',
                    sortable: true,
                    tooltip: 'Order No.',
                    hideable: false
                },
                {
                    header: 'Order Date',
                    dataIndex: 'bcor_createdon',
                    sortable: true,
                    tooltip: 'Order Date',
                    hideable: false
                },
                {
                    header: 'From',
                    dataIndex: 'cpd_Name',
                    sortable: true,
                    tooltip: 'From',
                    hideable: false
                },
                {
                    header: 'To',
                    dataIndex: 'branch_name',
                    sortable: true,
                    tooltip: 'To',
                    hideable: false
                },
                {
                    header: 'Order Status',
                    dataIndex: 'order_status',
                    sortable: true,
                    tooltip: 'Order Status',
                    hideable: false
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    _Store.load();
                    var me = this;
                    _Store.on('load', function () {
                        var data = me.getStore().data.items;
                        var recs = [];
                        Ext.each(data, function (item, index) {
                            if (item.data.checked == 1) {
                                recs.push(index);
                            }
                        });
                        me.getSelectionModel().selectRows(recs);
                    });
                }
            }
        });
        return _dispatchgridPanel;
    };
    var ReceiveDispatchMasterDetailsView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '';
        //var src = '?module=cpd_dispatch&op=dispatchdetailsView&quor_id=' + Application.CPD_Dispatch.Cache.quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'panelReceiveDispatchDetailsviewReceivedispatchDetails',
            region: 'center',
            width: winsize.width * 0.4,
            height: winsize.height * 0.4,
            items: [{
                    region: 'center',
                    defaults: {
                        frame: false
                    },
                    html: '<iframe id="downloadReceiveDispatchIframe" name="downloadReceiveDispatchIframe" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>'
                }
            ]
        });
        return contactsPanel;
    };
    var gridSelectionReceiveDispatchDetails = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").getSelectionModel().getSelections())) {
            var ID = Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.quor_id;
            var quor_TransferOrder_id = Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.quor_TransferOrder_id;
            var fstr_requestreceivedStatus = Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.fstr_requestreceivedStatus;
            var quor_Deliverybr_id = Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.quor_Deliverybr_id;
            var fstr_requestreceivedStatus = Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.fstr_requestreceivedStatus;
            if (fstr_requestreceivedStatus == 'Received') {
                Ext.getCmp('buttonReceiveDispatchDataeditReceivedispatchDetails').hide();
            } else {
                Ext.getCmp('buttonReceiveDispatchDataeditReceivedispatchDetails').show();
            }
            Application.Branch_Receive_Dispatch.Cache.quor_id = ID;
            Application.Branch_Receive_Dispatch.Cache.quor_TransferOrder_id = quor_TransferOrder_id;
            Application.Branch_Receive_Dispatch.ViewReceiveDispatchMode(ID);
            Application.Branch_Receive_Dispatch.Cache.quor_Deliverybr_id = quor_Deliverybr_id;
//            Ext.getCmp('gridpanelReceiveDispatchDataviewReceiveDetails').getStore().load({
//                params: {
//                    quor_id: Application.Branch_Receive_Dispatch.Cache.quor_id,
//                    quor_TransferOrder_id: Application.Branch_Receive_Dispatch.Cache.quor_TransferOrder_id
//                }
//            });
        }
    };
    var ReceiveDispatchMasterGridStore = function () {

        var _dispatchStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listReceiveDispatchDetails',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['quor_id', 'quor_RefNo', 'quor_Pickupbr_id', 'quor_Type', 'quor_TransferOrder_id', 'quor_Deliverybr_id', 'fstr_requestreceived', 'fstr_requestreceivedStatus', 'quor_Deliverybr_name', 'quor_Pickupbr_name',
                'quor_TypeName', 'fstro_receivedOn', 'fstro_receivedTime']),
            sortInfo: {
                field: 'quor_id',
                direction: "DESC"
            },
            // groupField: 'dispatchStatus',
            remoteSort: true,
            groupField: '',
            groupDir: '',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelReceiveDispatchDataviewDispatchDetails').getView().refresh();
                    Ext.getCmp('gridpanelReceiveDispatchDataviewDispatchDetails').getSelectionModel().selectRow(0);
                }
            }
        });

        return _dispatchStore;
    };
    var ReceiveDispatchMainGrid = function () {
        var _receivedispatchStore = ReceiveDispatchMasterGridStore();
        var _receivedispatchGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'bcd_driver'
                }, {
                    type: 'string',
                    dataIndex: 'quor_RefNo'
                }, {
                    type: 'string',
                    dataIndex: 'quor_Pickupbr_name'
                }, {
                    type: 'string',
                    dataIndex: 'quor_Deliverybr_name'
                }, {
                    type: 'string',
                    dataIndex: 'quor_TypeName'
                }, {
                    type: 'string',
                    dataIndex: 'fstr_requestreceivedStatus'
                }

            ]
        });
        _receivedispatchGridFilter.remote = true;
        _receivedispatchGridFilter.autoReload = true;
        var __cpddispatchmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            //iconCls: 'money',
            store: _receivedispatchStore,
            id: 'gridpanelReceiveDispatchDataviewDispatchDetails',
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fstr_requestreceivedStatus') == 'Received')
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _receivedispatchGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order No.',
                    dataIndex: 'quor_RefNo',
                    sortable: true,
                    tooltip: 'Order No.',
                    hideable: false, groupable: false

                },
                {
                    header: 'Source',
                    dataIndex: 'quor_Pickupbr_name',
                    sortable: true,
                    tooltip: 'Source',
                    hideable: false, groupable: false
                },
                {
                    header: 'Destination',
                    dataIndex: 'quor_Deliverybr_name',
                    sortable: true,
                    tooltip: 'Destination',
                    hideable: false, groupable: false
                }, {
                    header: 'Type',
                    dataIndex: 'quor_TypeName',
                    sortable: true,
                    tooltip: 'Type',
                    hideable: false, groupable: false

                }, {
                    header: 'Status',
                    dataIndex: 'fstr_requestreceivedStatus',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false, groupable: false

                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _receivedispatchStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionReceiveDispatchDetails
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    Application.Branch_Receive_Dispatch.ViewReceiveDispatchMode(record.data.quor_id);

                },
                resize: onGridResize,
                afterrender: function () {
                    _receivedispatchStore.load();

                }
            }
        });
        return __cpddispatchmaingridPanel;
    };
    var masterPanelforReceiveDispatch = function (id) {
        var _mpanelforReceiveDispatch = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Receive Dispatch',
            id: id,
            //iconCls: 'dispatch',
            buttonAlign: "right",
            items: [
                ReceiveDispatchMainGrid(),
                new Ext.Panel({
                    title: 'Receive Dispatch Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelReceiveDispatchDataviewReceivedispatchDetails',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    items: [
                        ReceiveDispatchMasterDetailsView()
                                //, ReceiveDispatchOrdersItemsGrid()
                    ],
                    fbar: [{
                            text: "Receive",
                            cls: "left-right-buttons",
                            id: "buttonReceiveDispatchDataeditReceivedispatchDetails",
                            handler: function () {
                                var record = Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").getSelectionModel().getSelections()[0];
                                var quor_TransferOrder_id = record.data.quor_TransferOrder_id;
                                var quor_id = record.data.quor_id;
                                var quor_RefNo = record.data.quor_RefNo;
                                var fstro_receivedOn = record.data.fstro_receivedOn;
                                var fstro_receivedTime = record.data.fstro_receivedTime;
                                NewReceiveDispatchWindow(quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime);
                            }
                        }, {
                            text: 'Print',
                            iconCls: 'my-icon141',
                            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                            handler: function () {
                                downloadReceiveDispatchIframe.focus();
                                downloadReceiveDispatchIframe.print();
                            }
                        }
                    ]
                })
            ],
        });
        return _mpanelforReceiveDispatch;
    };
    var NewReceiveDispatchWindow = function (quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime) {
        var _addnewWindow = new Ext.Window({
            id: 'windowReceiveDispatchDataviewOrderDetails',
            title: 'Receive Dispatch Details',
            shadow: false,
            height: 650,
            width: 800,
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 200,
                    items: ReceiveDispatchFormNEw(quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 450,
                    items: receiveItemStockGridNew(quor_id, quor_TransferOrder_id, quor_RefNo)
                }],
            fbar: [
                {
                    xtype: 'datefield',
                    fieldLabel: 'Received Date',
                    id: 'textfieldreceiveddispatchReceiveddate',
                    name: 'textfieldreceiveddispatchReceiveddate',
                    anchor: '90%',
                    allowBlank: false,
                    tabIndex: 3,
                    value: new Date(),
                    format: "d/m/Y"
                }, {
                    xtype: 'timefield',
                    fieldLabel: 'Received Time',
                    id: 'textfieldreceiveddispatchReceivedtime',
                    name: 'textfieldreceiveddispatchReceivedtime',
                    anchor: '90%',
                    tabIndex: 4,
                    minValue: '9:00 AM',
                    maxValue: '6:00 PM',
                    value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                }/* <?php if (user_access("retaline_receive_dispatch", "ReceiveDispatchDetails")) { ?> */, {
                    xtype: 'button',
                    text: 'Save',
                    id: 'stockAddinPoItems',
                    anchor: '90%',
                    iconCls: 'finascop_add',
                    handler: function () {
                        if ((!Ext.isEmpty((Ext.getCmp("textfieldreceiveddispatchReceiveddate").getValue()))))
                        {
                            var receiveItems = getMigratedData('poStockEntryGridPanel');
                            Ext.Ajax.request({
                                url: modURL + "&op=ReceiveDispatchDetails",
                                method: "POST",
                                params: {
                                    quor_id: quor_id,
                                    quor_TransferOrder_id: quor_TransferOrder_id,
                                    receive_date: Ext.getCmp("textfieldreceiveddispatchReceiveddate").getValue(),
                                    receive_time: Ext.getCmp("textfieldreceiveddispatchReceivedtime").getValue(),
                                    receiveItems: receiveItems
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    console.log("tmp", tmp);
                                    if (tmp.success === true && tmp.valid === true) {

                                        console.log('updateItemStock');
                                        Ext.Ajax.request({
                                            url: modURL,
                                            params: {
                                                op: 'updateItemStock',
                                                quor_id: quor_id,
                                                quor_TransferOrder_id: quor_TransferOrder_id,
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                        Application.example.msg("Success", tmp.message);
                                        RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails"));
                                        receivedispatch_array = [];
                                        Ext.getCmp("gridpanelReceiveDispatchDataviewDispatchDetails").store.reload({
                                            params: {
                                                start: 0,
                                                limit: RECS_PER_PAGE
                                            }
                                        });
                                        Ext.getCmp('windowReceiveDispatchDataviewOrderDetails').close();
                                                }
                                            }
                                        });

                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true & tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", tmp.message);
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert("Error", tmp.message);
                                }
                            });
                        } else {
                            Ext.MessageBox.alert("Notification", "Please enter required data");
                        }

                    }

                }
                /* <?php } ?> */
            ], listeners: {
                afterrender: function () {
                }
            }

        });
        _addnewWindow.doLayout();
        _addnewWindow.show();
        _addnewWindow.center();
    };
    var getMigratedData = function (gridid) {
        var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), 'data');
        return Ext.encode(j);
    };
    var ReceiveDispatchFormNEw = function (quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime) {
        var _dispatchFormPanel = new Ext.form.FormPanel({
            id: 'formpanelReceiveDispatchDataentryReceiveDetails',
            height: 200,
            frame: true,
            border: false,
            layout: 'column',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Branch',
                            id: 'currentCPD',
                            name: 'currentCPD',
                            disabled: true,
                            value: _SESSION.current_branch,
                            anchor: '85%'
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Receive Date',
                            id: 'recDate',
                            name: 'recDate',
                            disabled: true,
                            value: fstro_receivedOn,
                            anchor: '85%'
                        }
                    ]

                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Order No.',
                            id: 'recItem',
                            name: 'recItem',
                            disabled: true,
                            value: quor_RefNo,
                            anchor: '85%'
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Receive Time',
                            id: 'recTime',
                            name: 'recTime',
                            disabled: true,
                            value: fstro_receivedTime,
                            anchor: '85%'
                        }
                    ]

                }
            ]
        });
        return _dispatchFormPanel;
    };
    var receiveItemStockGridNew = function (quor_id, quor_TransferOrder_id, quor_RefNo) {

        var poStockEntryGridStore = listReceiveItemsStore(quor_id, quor_TransferOrder_id, quor_RefNo);
        var poStockEntryGrid = new Ext.grid.EditorGridPanel({
            store: poStockEntryGridStore,
            frame: false,
            border: true,
            height: 450,
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
                    header: 'Packed Qty',
                    sortable: true,
                    dataIndex: 'stii_packedQty',
                    align: 'right',
                    tooltip: 'Packed Qty'
                }, {
                    header: 'Received Qty',
                    sortable: true,
                    dataIndex: 'fsto_ItemQtyL3Received',
                    align: 'right',
                    tooltip: 'Received Qty'
                },
                {
                    header: 'To Receive Qty',
                    sortable: true,
                    dataIndex: 'stii_toReceiveqty',
                    xtype: 'numbercolumn',
                    align: 'right',
                    tooltip: 'Received Qty',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3
                    }
                }, {
                    header: 'Receive Date',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'fstro_receivedOn',
                    tooltip: 'Receive Date'
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
    var listReceiveItemsStore = function (quor_id, quor_TransferOrder_id, quor_RefNo) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listReceiveItemsStore',
            fields: ['stii_itemmastername', 'stii_batch', 'stii_qty', 'stii_expirydate', 'stii_id', 'stii_isgift', 'stii_createdon', 'fstro_receivedOn', 'fstro_receivedTime', 'stii_packedQty', 'fstod_id',
                'fsto_ItemId', 'fsto_pkdQty', 'fsto_ItemQty', 'fsto_ItemQtyL3Received','stii_toReceiveqty'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        quor_RefNo: quor_RefNo,
                        quor_id: quor_id,
                        quor_TransferOrder_id: quor_TransferOrder_id
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    return{
        Cache: {},
        initReceiveDispatch: function () {
            var _receivedispatchPanelId = 'panelMasterReceive_Dispatch';
            var _masterPanelReceiveDispatch = Ext.getCmp(_receivedispatchPanelId);
            if (Ext.isEmpty(_masterPanelReceiveDispatch)) {
                _masterPanelReceiveDispatch = masterPanelforReceiveDispatch(_receivedispatchPanelId);
                Application.UI.addTab(_masterPanelReceiveDispatch);
                _masterPanelReceiveDispatch.doLayout();
            } else {
                Application.UI.addTab(_masterPanelReceiveDispatch);
            }
        },
        ViewReceiveDispatchMode: function () {
            var quor_id = arguments[0];
            //Application.Branch_Receive_Dispatch.Cache.quor_id = quor_id;
            Ext.getCmp("panelReceiveDispatchDetailsviewReceivedispatchDetails").show();
            Ext.getCmp("panelReceiveDispatchDataviewReceivedispatchDetails").doLayout();

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var apikey = _SESSION.apikey;
            var modURL = '?module=retaline_receive_dispatch';
            Ext.get('downloadReceiveDispatchIframe').dom.src = modURL + '&op=dispatchdetailsView&quor_id=' + quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        }, itemViewMode: function (fsto_ItemId, fsto_id, item_name, fsto_ItemQty) {

            var fpod_id = arguments[0];

            Ext.Ajax.request({
                url: modURL + '&op=itemStockReceiveDetailsView',
                method: 'POST',
                params: {
                    fsto_ItemId: fsto_ItemId,
                    fsto_id: fsto_id,
                    item_name: item_name,
                    fsto_ItemQty: fsto_ItemQty
                },
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('receiveStockPoItemDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('receiveStockPoItemDetailsViewPanel').doLayout();
        }
    };
}();
