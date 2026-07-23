Application.Finascop_Purchase = function () {

    var modURL = '?module=finascop_purchase';
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 12;
    var chkd = true;
    var party_id;
    var party_state_id;
    var branch_state_id;
    var br_id;
    var excludeItemIds = [];
    //var dateRange = BETWEEN_DATES_TYPE_A;

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var loadPurchaseInvoice = function () {
        var purchase_grid = Ext.getCmp('pu_master_grid');
        purchase_grid.getStore().removeAll();
        purchase_grid.getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }
    var loadPurchaseReturnable = function () {
        var purchaseRetrunable_grid = Ext.getCmp('purchase_returnable_master_grid');
        purchaseRetrunable_grid.getStore().removeAll();
        purchaseRetrunable_grid.getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }

    var loadPurchaseReturn = function () {
        var purchaseRetrun_grid = Ext.getCmp('purchase_return_window_grid');
        purchaseRetrun_grid.getStore().removeAll();
        purchaseRetrun_grid.getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }

    //party Store
    var party_store = function () {
        var partyStore = new Ext.data.JsonStore({
            autoLoad: true,
            id: 'pu_party_store',
            url: modURL + '&op=getParty',
            method: 'post',
            fields: ['id', 'party', 'party_state_id', 'br_id'],
            totalProperty: 'totalCount',
            root: 'data'
        });
        return partyStore;
    }



    var itemComboStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            id: 'pu_item_store',
            url: modURL + '&op=getItems',
            method: 'post',
            fields: ['item_id', 'item_name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteFilter: true,
            listeners: {
                beforeload: function (st, opt) {
                    st.baseParams.excludeIds = Ext.encode(excludeItemIds);
                }
            }
        });
        return store;
    };
    //column model for invoiceItemGrid and invoiceTotalGrid
    var purchaseInvoiceColModel = function ()
    {
        return new Ext.grid.ColumnModel([
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'item',
                tooltip: 'Item',
                width: 130

            },
            {
                header: 'MRP',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'mrp',
                align: 'right',
                tooltip: 'MRP',
                width: 60
            },
            {
                header: 'Rate',
                sortable: true,
                dataIndex: 'rate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'RATE',
                align: 'right',
                width: 60
            },
            {
                header: 'Quantity',
                sortable: true,
                dataIndex: 'quantity',
                xtype: 'numbercolumn',
                align: 'right',
                tooltip: 'Quantity',
                width: 60
            },
            {
                header: 'HSN',
                sortable: true,
                dataIndex: 'hsn',
                align: 'right',
                tooltip: 'HSN Code',
                width: 60
            },
            {
                header: 'Tax %',
                sortable: true,
                dataIndex: 'tax_percentage',
                tooltip: 'Tax Percentage',
                align: 'right',
                //xtype: 'numbercolumn',
                width: 60
            },
            {
                header: 'IGST',
                sortable: true,
                dataIndex: 'igst',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'IGST',
                width: 60
            },
            {
                header: 'CGST',
                sortable: true,
                dataIndex: 'cgst',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'CGST',
                width: 60
            },
            {
                header: 'SGST',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                sortable: true,
                dataIndex: 'sgst',
                align: 'right',
                tooltip: 'SGST',
                width: 60
            },
            {
                header: 'Amt Bf . Tax',
                sortable: true,
                dataIndex: 'amtBfTax',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Amount Before Tax',
                width: 100
            },
            {
                header: 'Amt Af. Tax',
                sortable: true,
                dataIndex: 'amtAfTax',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Amount After Tax',
                width: 100
            },
            {
                xtype: 'actioncolumn',
                hideable: false,
                id: 'pu_removeitem',
                width: 25,
                items: [{
                        sortable: false,
                        getClass: function (v, meta, rec) {
                            if (rec.get('hideCancelbtn') == 1)
                            {
                                return 'finascop_hideicon';
                            } else
                            {
                                return 'finascop_delete';
                            }
                        },
                        tooltip: 'Remove Item',
                        handler: function (grid, rowIndex, colIndex) {
                            var record = grid.store.getAt(rowIndex);
                            removeFromItemStore(grid, rowIndex, record);
                            excludeItemIds.remove(record.get('item_id'));
                            Ext.getCmp('pu_item_field').lastQuery = null;
                            Ext.getCmp('pu_item_field').reset();
                            Ext.getCmp('pu_item_field').focus();
                        }


                    }]
            }
        ]);
    }


    var removeFromItemStore = function (grid, indx, record)
    {
        Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
            if (btn == 'yes') {

                Ext.getCmp('pu_itemgrid').getStore().removeAt(indx);
                Ext.getCmp('pu_itemgrid').getView().refresh();
            }
        });

    }

    var PurchaseEntryGrid = function () {

        var PurchaseInvoiceMasterStore = function () {
            var purchase_invoice_store = new Ext.data.JsonStore({
                method: 'post',
                id: 'pu_master_store',
                url: modURL + '&op=listPurchaseInvoices',
                fields: ['puen_Id', 'puen_InvoiceDate',
                    'puen_InvoiceNo', 'puen_Vendorname', 'puen_NetAmt', 'puen_Tax', 'puen_TotalItems',
                    'puen_TotalItemQty', 'puen_VendorPurchaseOrder', 'puen_VendorPurchaseOrderDate', 'puen_RefNo', {name: 'tax_total', type: 'float'}, {name: 'total_amount', type: 'float'}, {name: 'total_item_qty', type: 'float'}, {name: 'total_items', type: 'float'}],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                listeners: {

                    load: function (store, record, options) {
                        Ext.getCmp('pu_master_grid').getSelectionModel().selectRow(0);
                        var record = Ext.getCmp('pu_master_grid').getSelectionModel().getSelected();
                         if (record !== undefined)
                        {
                        var tax_total = record.get('tax_total');
                        var total_amount = record.get('total_amount');
                        var total_item_qty = record.get('tax_total'); 
                        var total_items = record.get('total_items');
                        var number_of_col = Ext.getCmp('pu_master_grid').getColumnModel().config.length;
                        var tooltipmaker = Ext.getCmp('pu_master_grid').getColumnModel().config;

                        for (var i = 1; i < number_of_col; i++) {
                            var column_header = tooltipmaker[i].header;
                            if (column_header == 'Amount')
                            {
                                tooltipmaker[i].tooltip = 'Total MRP : ' + total_amount;
                            }
                            if (column_header == 'Tax')
                            {
                                tooltipmaker[i].tooltip = 'Total Tax : ' + tax_total;
                            }
                            if (column_header == 'No.Of Items')
                            {
                                tooltipmaker[i].tooltip = 'Total Tax : ' + total_items;
                            }
                            if (column_header == 'Item Quantity')
                            {
                                tooltipmaker[i].tooltip = 'Total Tax : ' + total_item_qty;
                            }

                        }
                    }
                        else
                        {
                            Ext.MessageBox.alert('Alert', 'No data Found');
                        }

                    }
                }
            });
            //purchase_invoice_store.setDefaultSort('puen_Id', 'ASC');
            return purchase_invoice_store;
        };
        var purchase_invoice_master_store = PurchaseInvoiceMasterStore();
        //var action = invoiceGridAction();
        var purchase_entries_grid = new Ext.grid.GridPanel({
            store: purchase_invoice_master_store,
            layout: 'fit',
            region: 'center',
            frame: false,
            border: true,
            height: winsize.height * 0.80,
            plugins: [],
            id: 'pu_master_grid',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer({dataIndex: 'SlNo'}),
                {
                    header: 'PR No .',
                    sortable: true,
                    dataIndex: 'puen_Id',
                    tooltip: 'Purchase Register Number'
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'puen_InvoiceDate',
                    tooltip: 'Purchase Invoice Date'
                }, {
                    header: 'Invoice No.',
                    sortable: true,
                    dataIndex: 'puen_InvoiceNo',
                    tooltip: 'Purchase Invoice Number'
                }, {
                    header: 'Party',
                    sortable: true,
                    dataIndex: 'puen_Vendorname',
                    tooltip: 'Party'
                }, {
                    header: 'Amount',
                    sortable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'puen_NetAmt',
                    align: 'right'
                            //tooltip: 'Net Amount'
                }, {
                    header: 'Tax',
                    sortable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'puen_Tax',
                    align: 'right'
                            //tooltip: 'Tax'

                }, {
                    header: 'No.Of Items',
                    sortable: true,
                    dataIndex: 'puen_TotalItems',
                    align: 'right'
                            //tooltip: 'Total Items'
                }, {
                    header: 'Item Quantity',
                    dataIndex: 'puen_TotalItemQty',
                    align: 'right',
                    sortable: true
                            //tooltip: 'Total Item Quantity'
                },
                {
                    header: '',
                    hideHeaders: true,
                    dataIndex: '',
                    width: 40
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
            listeners: {
                viewready: updatePagination,
                rowclick: function (grid, rowIndex, e) {
                    var purchaseinvoiceRecord = grid.getStore().getAt(rowIndex);
                    var invItemStore = Ext.getCmp('pu_Invoice_slidergrid').getStore();
                    invItemStore.baseParams.puen_Id = purchaseinvoiceRecord.get('puen_Id');
                    invItemStore.removeAll();
                    invItemStore.load({
                        callback: function (record, options, success) {
                            var store = invItemStore;
                            var row_total = new Ext.data.Record.create([
                                {name: 'igst', type: 'float'},
                                {name: 'cgst', type: 'float'},
                                {name: 'sgst', type: 'float'},
                                {name: 'amt_bf_tax', type: 'float'},
                                {name: 'amt_af_tax', type: 'float'}

                            ]);
                            var igsttotal = Ext.util.Format.number((store.sum('igst')), "0.00");
                            var cgsttotal = Ext.util.Format.number((store.sum('cgst')), "0.00");
                            var sgsttotal = Ext.util.Format.number((store.sum('sgst')), "0.00");
                            var totalamount_btTax = Ext.util.Format.number((store.sum('amt_bf_tax')), "0.00");
                            var totalamount_atTax = Ext.util.Format.number((store.sum('amt_af_tax')), "0.00");
                            var totalsdata = new row_total({
                                igst: igsttotal,
                                cgst: cgsttotal,
                                sgst: sgsttotal,
                                amt_bf_tax: totalamount_btTax,
                                amt_af_tax: totalamount_atTax
                            });
                            Ext.getCmp('pu_sliderTotal').getStore().removeAll();
                            Ext.getCmp('pu_sliderTotal').getStore().add(totalsdata);
                        }

                    });
                    Ext.getCmp('pu_parent_sliderpanel').expand(true);
                    var visualsDescPanel = Ext.getCmp('pu_details_view_panel');
                    visualsDescPanel.update(purchaseinvoiceRecord.data);
                    Ext.getCmp('pu_editSlider_partyName').setValue(purchaseinvoiceRecord.data.puen_Vendorname);
                    Ext.getCmp('pu_editSlider_invoiceDate').setValue(purchaseinvoiceRecord.data.puen_InvoiceDate);
                    Ext.getCmp('pu_editSlider_puOrderno').setValue(purchaseinvoiceRecord.data.puen_VendorPurchaseOrder);
                    Ext.getCmp('pu_editSlider_puOrderdate').setValue(purchaseinvoiceRecord.data.puen_VendorPurchaseOrderDate);
                    Ext.getCmp('pu_editSlider_refNo').setValue(purchaseinvoiceRecord.data.puen_RefNo);
                    Ext.getCmp('puen_Id').setValue(purchaseinvoiceRecord.data.puen_Id);
                    Ext.getCmp('pu_editSlider_invNo').setValue(purchaseinvoiceRecord.data.puen_InvoiceNo);
                    Ext.getCmp('pu_invoiceeditform').setVisible(false);
                    Ext.getCmp('pu_details_view_panel').setVisible(true);
                }
            },
            tbar: ['-',
                {
                    xtype: 'button',
                    text: 'Create Purchase Invoice',
                    iconCls: 'finascop_add',
                    handler: function () {

                        addPurchaseInvoice(0);
                    }
                }, '-'
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: purchase_invoice_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
        });
        return purchase_entries_grid;
    };
    // purchase invoice add Form: -->

    var viewPurchaseInvoice_panel = function () {

        function addRawValueToStore(cbx) {
            var comboStore = cbx.getStore();
            comboStore.removeAll();
            var rec = new Ext.data.Record.create([
                {name: 'id'},
                {name: 'party'},
                {name: 'party_state_id'},
                {name: 'br_id'}
            ]);

            var data = new rec({
                id: '',
                party: cbx.getRawValue().toUpperCase(),
                party_state_id: '',
                br_id: ''
            });
            comboStore.add(data);
        }
        var PartyStore = party_store();
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'purchase_invoice_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [
                        {layout: 'form',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'checkbox',
                                    id: 'pu_cb',
                                    name: 'cb1',
                                    labelSeparator: '',
                                    hideLabel: true,
                                    boxLabel: 'Is Party',
                                    fieldLabel: 'Is Party',
                                    enableKeyEvents: true,
                                    checked: true,
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);

                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_party').focus();
                                            }
                                            if (e.getKey() == e.UP) {
                                                Ext.getCmp('pu_cb').setValue(true);
                                            }
                                            if (e.getKey() == e.DOWN) {
                                                Ext.getCmp('pu_cb').setValue(false);
                                            }
                                        },
                                        check: function (cb1, checked) {
                                            excludeItemIds = [];
                                            Ext.getCmp('pu_item_field').clearValue();
                                            Ext.getCmp('pu_rate_field').reset();
                                            Ext.getCmp('pu_quantity_field').reset();
                                            Ext.getCmp('pu_amount_field').reset();
                                            Ext.getCmp('pu_order_no').reset();
                                            Ext.getCmp('pu_order_date').reset();
                                            Ext.getCmp('pu_ref_no').reset();
                                            Ext.getCmp('pu_inv_date').reset();
                                            Ext.getCmp('pu_itemgrid').getStore().removeAll(true);
                                            Ext.getCmp('pu_itemgrid').getView().refresh();
                                            Ext.getCmp('pu_itemgridTotal').getStore().removeAll(true);
                                            Ext.getCmp('pu_itemgridTotal').getView().refresh();
                                            Ext.getCmp('pu_grossAmount').reset();
                                            chkd = checked;
                                            var partyCombo = Ext.getCmp('pu_party');
                                            if (chkd == false) {

                                                var newLabel = 'Paid To:';
                                                partyCombo.getStore().baseParams.isParty = false;
                                                partyCombo.getStore().load({
                                                    callback: function () {
                                                        partyCombo.setValue('');
                                                        partyCombo.mode = 'local';
                                                    }
                                                });
                                                partyCombo.setHideTrigger(true);
                                                if (!partyCombo.rendered)
                                                    partyCombo.fieldLabel = newLabel;
                                                else
                                                    partyCombo.label.update(newLabel);
                                            } else
                                            {
                                                var newLabel = 'Party:';
                                                partyCombo.mode = 'remote';
                                                partyCombo.getStore().baseParams.isParty = true;
                                                partyCombo.getStore().load();
                                                partyCombo.setHideTrigger(false);
                                                if (!partyCombo.rendered)
                                                    partyCombo.fieldLabel = newLabel;
                                                else
                                                    partyCombo.label.update(newLabel);
                                            }
                                            Ext.getCmp('pu_item_field').clearValue();
                                            Ext.getCmp('pu_rate_field').reset();
                                            Ext.getCmp('pu_quantity_field').reset();
                                            Ext.getCmp('pu_amount_field').reset();
                                        }

                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Party',
                                    id: 'pu_party',
                                    name: 'party',
                                    anchor: '99%',
                                    displayField: 'party',
                                    valueField: 'id',
                                    hiddenName: 'party',
                                    store: PartyStore,
                                    triggerAction: 'all',
                                    allowBlank: false,
                                    labelStyle: mandatory_label,
                                    mode: 'remote',
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    enableKeyEvents: true,
                                    autoSelect: true,
                                    maxChars: 50,
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        },
                                        select: function (combo, record, index) {
                                            excludeItemIds = [];
                                            if (record) {
                                                party_id = record.get('id');
                                                party_state_id = record.get('party_state_id');
                                                br_id = record.get('br_id');
                                                Ext.getCmp('pu_item_field').clearValue();
                                                Ext.getCmp('pu_rate_field').reset();
                                                Ext.getCmp('pu_quantity_field').reset();
                                                Ext.getCmp('pu_amount_field').reset();
                                                Ext.getCmp('pu_order_no').reset();
                                                Ext.getCmp('pu_order_date').reset();
                                                Ext.getCmp('pu_ref_no').reset();
                                                Ext.getCmp('pu_inv_date').reset();
                                                Ext.getCmp('pu_grossAmount').reset();
                                                Ext.getCmp('pu_discount').reset();
                                                Ext.getCmp('pu_netAmount').reset();
                                                Ext.getCmp('pu_signature').reset();
                                                Ext.getCmp('pu_totalItems').reset();
                                                Ext.getCmp('pu_tax').reset();
                                                Ext.getCmp('pu_totalItemQty').reset();
                                                Ext.getCmp('pu_itemgrid').getStore().removeAll(true);
                                                Ext.getCmp('pu_itemgrid').getView().refresh();
                                                Ext.getCmp('pu_itemgridTotal').getStore().removeAll(true);
                                                Ext.getCmp('pu_itemgridTotal').getView().refresh();
                                            }
                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                var chk = Ext.getCmp('pu_cb').getValue();
                                                if (chk == true) {
                                                    Ext.getCmp('pu_order_no').focus();
                                                } else {
                                                    addRawValueToStore(field);
                                                    Ext.getCmp('pu_order_no').focus();
                                                }
                                            }
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.20,
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Purchase Order No',
                                    id: 'pu_order_no',
                                    name: 'purchcase_order_no',
                                    allowBlank: true,
                                    labelAlign: 'top',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_order_date').focus();
                                            }
                                        }
                                    }
                                }
                            ]

                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'datefield',
                                    fieldLabel: 'Purchase Order Date',
                                    id: 'pu_order_date',
                                    name: 'purchase_order_date',
                                    allowBlank: true,
                                    anchor: '99%',
                                    format: 'd/m/Y',
                                    maxValue: new Date(),
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_ref_no').focus();
                                            }
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Ref. No',
                                    allowBlank: true,
                                    id: 'pu_ref_no',
                                    name: 'ref_no',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_inv_date').focus();
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
                                    fieldLabel: 'Invoice Date',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    id: 'pu_inv_date',
                                    name: 'inv_date',
                                    anchor: '99%',
                                    format: 'd/m/Y',
                                    maxValue: new Date(),
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_item_field').focus();
                                            }
                                        }
                                    }
                                }]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Invoice Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'invoice_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [invoiceItemGrid(), invoiceTotalGrid()]
                                }]
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            fieldLabel: 'Total Quantity ',
                            align: 'right',
                            id: 'pu_totalItemQty',
                            name: 'totalItemQty',
                            anchor: '99%',
                            hideLabel: true
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            fieldLabel: 'Tax ',
                            align: 'right',
                            id: 'pu_tax',
                            name: 'tax',
                            anchor: '99%',
                            hideLabel: true
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            fieldLabel: 'Total Items',
                            id: 'pu_totalItems',
                            name: 'totalItems',
                            anchor: '99%',
                            hideLabel: true
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Gross Amount',
                                    id: 'pu_grossAmount',
                                    name: 'grossAmount',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_discount').focus();
                                            }
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    fieldLabel: 'Discount',
                                    id: 'pu_discount',
                                    name: 'discount',
                                    style: {'text-align': 'right'},
                                    allowNegative: false,
                                    anchor: '99%',
                                    value: '0.00',
                                    allowDecimals: true,
                                    enableKeyEvents: true,
                                    listeners: {
                                        focus: function (txtbx) {
                                            txtbx.selectText();
                                        },
                                        keyup: function ()
                                        {
                                            var discount = Ext.getCmp('pu_discount').getValue();
                                            var grossamount = Ext.getCmp('pu_grossAmount').getValue();
                                            var netamount = (grossamount - discount);
                                            Ext.getCmp('pu_netAmount').setValue(netamount);
                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_signature').focus();
                                            }
                                        }
                                    }
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Net Amount',
                                    id: 'pu_netAmount',
                                    readOnly: true,
                                    name: 'netAmount',
                                    anchor: '99%'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Signature',
                                    id: 'pu_signature',
                                    allowBlank: false,
                                    name: 'signature',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                savePurchaseInvoices();
                                            }
                                        }

                                    }
                                }]
                        }]
                }]
        });
        return panel;
    };
    // <-- purchase invoice add Form //

    var add_PInv_Items = function () {



        if (Ext.isEmpty(Ext.getCmp('pu_item_field').getValue()))
        {
            Ext.MessageBox.alert("Notification", "Please Add Item");
            return;
        } else if (Ext.isEmpty(Ext.getCmp('pu_rate_field').getValue()))
        {
            Ext.MessageBox.alert("Notification", "Please Add Rate");
            return;
        } else if (Ext.isEmpty(Ext.getCmp('pu_quantity_field').getValue()))
        {
            Ext.MessageBox.alert("Notification", "Please Add Quantity");
            return;
        } else if (Ext.isEmpty(Ext.getCmp('pu_amount_field').getValue()))
        {
            Ext.MessageBox.alert("Notification", "Please Add Amount");
            return;
        }

        var rdata;

        var row = new Ext.data.Record.create([
            {name: 'item'},
            {name: 'item_id'},
            {name: 'mrp', type: 'float'},
            {name: 'rate', type: 'float'},
            {name: 'quantity', type: 'float'},
            {name: 'hsn'},
            {name: 'tax_percentage', type: 'float'},
            {name: 'igst', type: 'float'},
            {name: 'cgst', type: 'float'},
            {name: 'sgst', type: 'float'},
            {name: 'amtBfTax', type: 'float'},
            {name: 'amtAfTax', type: 'float'},
            {name: 'stockEnabled'}
        ]);
        var item = Ext.getCmp('pu_item_field').getRawValue();
        var item_id = Ext.getCmp('pu_item_field').getValue();
        var rate = Ext.getCmp('pu_rate_field').getRawValue();
        var quantity = Ext.getCmp('pu_quantity_field').getValue();
        var amount = Ext.getCmp('pu_amount_field').getValue();
        Ext.Ajax.request({
            waitMsg: 'Processing',
            url: modURL,
            params: {
                op: 'getInvoiceItemdetails',
                item_id: Ext.getCmp('pu_item_field').getValue(),
                party_state_id: party_state_id
                        // branch_state_id: branch_state_id
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', ACTION_FAIL);
            },
            success: function (response, options) {
                var tmp = response.responseText;
                tmp = Ext.decode(tmp);
                if (tmp.success === true) {

                    var igst = (amount * ((tmp.data.IGST) / 100));
                    var cgst = (amount * ((tmp.data.CGST) / 100));
                    var sgst = (amount * ((tmp.data.SGST) / 100));
                    rdata = new row({
                        item: item,
                        item_id: item_id,
                        mrp: tmp.data.stit_MRP,
                        rate: rate,
                        quantity: quantity,
                        hsn: tmp.data.stit_HSNCode,
                        tax_percentage: (tmp.data.stit_GST),
                        igst: igst,
                        cgst: cgst,
                        sgst: sgst,
                        amtBfTax: Number(amount),
                        amtAfTax: Number(amount) + Number(igst) + Number(cgst) + Number(sgst),
                        stockEnabled: tmp.data.stit_StockEnabled
                    });
                    excludeItemIds.push(item_id);
                    var itemfield = Ext.getCmp('pu_item_field');
                    itemfield.reset();
                    itemfield.lastQuery = null;
//                    var itemcombostore = Ext.getCmp('pu_item_field').getStore();
//                    itemcombostore.removeAll();
//                    itemcombostore.fireEvent('load', itemcombostore, [], {});

                    Ext.getCmp('pu_itemgrid').stopEditing(); //stops any acitve editing
                    Ext.getCmp('pu_itemgrid').getStore().add(rdata);
                    invoiceTotalCalculation();
                } else
                {
                    Ext.MessageBox.alert('Notification', tmp.msg);
                }

            }
        });

        Ext.getCmp('pu_item_field').clearValue();
        Ext.getCmp('pu_rate_field').reset();
        Ext.getCmp('pu_quantity_field').reset();
        Ext.getCmp('pu_amount_field').reset();
    };

    //  purchase invoice Item Grid--> //
    var invoiceItemGrid = function () {

        var PurchaseInvoiceItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                fields: [{name: 'item'},
                    {name: 'item_id'},
                    {name: 'mrp', type: 'float'},
                    {name: 'rate', type: 'float'},
                    {name: 'quantity'},
                    {name: 'hsn'},
                    {name: 'tax_percentage', type: 'float'},
                    {name: 'igst', type: 'float'},
                    {name: 'cgst', type: 'float'},
                    {name: 'sgst', type: 'float'},
                    {name: 'amtBfTax', type: 'float'},
                    {name: 'amtAfTax', type: 'float'},
                    {name: 'stockEnabled'}
                ],
                remoteSort: false,
                url: modURL + '&op=getInvoiceItems',
                method: 'post',
                totalProperty: 'totalCount',
                root: 'data',
                listeners: {
                    remove: function (obj, record, index) {
                        invoiceTotalCalculation();
                        Ext.getCmp('pu_item_field').focus();
                    }
                }
            });
            return itemStore;
        };
        //var itemStore = PurchaseInvoiceItemStore();


        var itemGridStore = PurchaseInvoiceItemStore();
        var item_store = itemComboStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'pu_itemgrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseInvoiceColModel(),
            tbar: [
                {html: '&nbsp;Item : &nbsp;'},
                {
                    xtype: 'combo',
                    anchor: '99%',
                    name: 'item_field',
                    id: 'pu_item_field',
                    width: 210,
                    store: item_store,
                    mode: 'remote',
                    hiddenName: 'item_field',
                    displayField: 'item_name',
                    valueField: 'item_id',
                    triggerAction: 'all',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    submitValue: true,
                    alloWBlank: false,
                    maxChars: 50,
                    minChars: 2,
                    style: 'margin-bottom:3px;',
                    listeners: {
                        select: function (combo, record, index)
                        {
                            if (chkd == true) {
                                var party = Ext.getCmp('pu_party').getValue();
                                if (party == '')
                                {
                                    Ext.MessageBox.alert('Alert', "Please select the Party");
                                    Ext.getCmp('pu_item_field').clearValue();

                                }
                            }

                            Ext.getCmp('pu_rate_field').focus();
                        },

                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                if (field.getValue() != '')
                                {
                                    Ext.getCmp('pu_item_field').lastQuery = null;
                                    Ext.getCmp('pu_rate_field').focus();

                                } else
                                {
                                    var gridItemCount = itemGridStore.getCount();

                                    if (gridItemCount > 0) {
                                        e.stopEvent();


                                        Ext.getCmp('pu_item_field').lastQuery = null;
                                        Ext.getCmp('pu_itemgrid').getSelectionModel().selectRow(0);
                                        Ext.getCmp('pu_itemgrid').getView().focusRow(0);

                                    } else
                                    {
                                        e.stopEvent();
                                        Ext.Msg.alert("Notification", "Please add any item", function (btn) {
                                            Ext.getCmp('pu_item_field').focus();
                                        });
                                    }


                                }
                            }
                        }
                    }
                },
                {html: '&nbsp; Rate : &nbsp;'},
                {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    id: 'pu_rate_field',
                    allowNegative: false,
                    allowDecimals: true,
                    name: 'rate_field',
                    anchor: '97%',
                    width: 100,
                    enableKeyEvents: true,
                    style: 'margin-bottom:3px;',
                    listeners: {
                        keyup: function ()
                        {
                            var qty = Ext.getCmp('pu_quantity_field').getValue();
                            var rate = Ext.getCmp('pu_rate_field').getValue();
                            var amnt = Ext.util.Format.number((qty * rate), "0.00");
                            Ext.getCmp('pu_amount_field').setValue(amnt);
                        },
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                Ext.getCmp('pu_quantity_field').focus();
                            }

                        }
                    }
                },
                {html: '&nbsp; Quantity : &nbsp;'},
                {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    id: 'pu_quantity_field',
                    name: 'quantity_field',
                    allowNegative: false,
                    allowDecimals: false,
                    anchor: '97%',
                    width: 100,
                    enableKeyEvents: true,
                    style: 'margin-bottom:3px;',
                    listeners: {
                        keyup: function ()
                        {
                            var qty = Ext.getCmp('pu_quantity_field').getValue();
                            var rate = Ext.getCmp('pu_rate_field').getValue();
                            var amnt = Ext.util.Format.number((qty * rate), "0.00");
                            Ext.getCmp('pu_amount_field').setValue(amnt);
                        },
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {

                                var item = Ext.getCmp('pu_item_field').getValue();
                                if (item != "") {
                                    add_PInv_Items();

                                    Ext.getCmp('pu_item_field').focus();

                                } else {
                                    Ext.getCmp('pu_discount').focus();
                                }
                            }
                        }
                    }


                },
                {html: '&nbsp; Amount : &nbsp;'},
                {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    id: 'pu_amount_field',
                    name: 'amount_field',
                    anchor: '97%',
                    editable: false,
                    readOnly: true,
                    width: 100,
                    style: 'margin-bottom:3px;'
                }, '-',
                {
                    xtype: 'button',
                    text: 'Add',
                    id: 'pInv_add_btn',
                    iconCls: 'finascop_add',
                    style: 'margin-bottom:3px;',
                    handler: function () {

                        add_PInv_Items();
                    }

                }, '-'


            ],
            listeners: {
                keydown: function (e)
                {
                    if ((e.getCharCode() == 99) || (e.getCharCode() == 67)) {
                        var grid = Ext.getCmp('pu_itemgrid');
                        var selected = grid.getSelectionModel().getSelected();
                        var rowIndex = grid.getStore().indexOf(selected);
                        var record = grid.getStore().getAt(rowIndex);
                        removeFromItemStore(grid, rowIndex, record);
                        excludeItemIds.remove(record.get('item_id'));
                        e.stopEvent();
                        Ext.getCmp('pu_item_field').lastQuery = null;
                        Ext.getCmp('pu_item_field').reset();
                        Ext.getCmp('pu_item_field').focus();
                    } else if (e.getCharCode() == 13) {
                        e.stopEvent();
                        Ext.getCmp('pu_discount').focus();
                    }


                }
            },
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true
        });
        return itemgrid_panel;
    };
    //  <--purchase invoice Item Grid //
    var invoiceTotalCalculation = function () {

        var grid_store = Ext.getCmp('pu_itemgrid').getStore();
        var grossAmount = Ext.util.Format.number((grid_store.sum('amtAfTax')), "0.00");
        var igsttotal = Ext.util.Format.number((grid_store.sum('igst')), "0.00");
        var cgsttotal = Ext.util.Format.number((grid_store.sum('cgst')), "0.00");
        var sgsttotal = Ext.util.Format.number((grid_store.sum('sgst')), "0.00");
        var amtBeforeTax = Ext.util.Format.number((grid_store.sum('amtBfTax')), "0.00");
        var tax = Number(igsttotal) + Number(cgsttotal) + Number(sgsttotal);
        var totalquantity = (grid_store.sum('quantity'));
        var totalitems = (grid_store.getCount());
        var row_total = new Ext.data.Record.create([
            {name: 'tax_percentage', type: 'string'},
            {name: 'igst', type: 'float'},
            {name: 'cgst', type: 'float'},
            {name: 'sgst', type: 'float'},
            {name: 'amtBfTax', type: 'float'},
            {name: 'amtAfTax', type: 'float'},
            {name: 'hideCancelbtn', type: 'number'}
        ]);
        var totalsdata = new row_total({
            tax_percentage: "Total :",
            igst: igsttotal,
            cgst: cgsttotal,
            sgst: sgsttotal,
            amtBfTax: amtBeforeTax,
            amtAfTax: grossAmount,
            hideCancelbtn: 1
        });
        Ext.getCmp('pu_itemgridTotal').getStore().removeAll(true);
        Ext.getCmp('pu_itemgridTotal').stopEditing(); //stops any acitve editing
        Ext.getCmp('pu_itemgridTotal').getStore().add(totalsdata);
        Ext.getCmp('pu_grossAmount').setValue(grossAmount);
        Ext.getCmp('pu_tax').setValue(tax);
        Ext.getCmp('pu_totalItemQty').setValue(totalquantity);
        Ext.getCmp('pu_totalItems').setValue(totalitems);
        Ext.getCmp('pu_master_grid').getStore().reload();
        var discount = Ext.getCmp('pu_discount').getValue();
        var grossamount = Ext.getCmp('pu_grossAmount').getValue();
        Ext.getCmp('pu_netAmount').setValue(grossamount - discount);
    }

    //total amount calculation-->
    var invoiceTotalGrid = function () {

        var TotalAmount_ItemStore = function () {
            var purchase_invoice_store = new Ext.data.JsonStore({
                method: 'post',
                fields: [{name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'},
                    {name: 'sgst', type: 'float'}, {name: 'amtBfTax', type: 'float'},
                    {name: 'amtAfTax', type: 'float'}, {name: 'hideCancelbtn', type: 'number'}]
            });
            return purchase_invoice_store;
        };
        var itemTotalGridStore = TotalAmount_ItemStore();
        var itemgridTotal_panel = new Ext.grid.GridPanel({
            store: itemTotalGridStore,
            layout: 'fit',
            frame: false,
            border: false,
            title: '',
            height: 40,
            id: 'pu_itemgridTotal',
            loadMask: true,
            hideHeaders: true,
            tdClass: 'true',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseInvoiceColModel(),
            stripeRows: true,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            }
        });
        return itemgridTotal_panel;
    };
    //<-- total amount calculation

    // purchase invoice add or edit window: -->//

    var addPurchaseInvoice = function (id) {
        var title = (id == 0) ? 'Add Purchase Invoice' : 'Edit Purchase Invoice';
        var purchase_invoice_form = viewPurchaseInvoice_panel();
        var addPurchaseInvoice_window = Ext.getCmp('purchaseInvoice_window');
        if (Ext.isEmpty(addPurchaseInvoice_window)) {
            var addPurchaseInvoice_window = new Ext.Window({
                id: 'purchaseInvoice_window',
                title: title,
                //iconCls: 'finascop_invoice',
                modal: true,
                layout: 'fit',
                width: 900,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [purchase_invoice_form],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            excludeItemIds = [];
                            addPurchaseInvoice_window.close();
                        }
                    },
                    {
                        text: 'Save',
                        id: 'save_PInv_btn',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            savePurchaseInvoices();
                        }
                    }
                    ]
            });
        }


        if (!Ext.isEmpty(id)) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            purchase_invoice_form.load({
                url: modURL + '&op=getPurchaseInvoice_EditData',
                params: {
                    'id': id,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (frm, action) {

                }
            });
        }

        Ext.getCmp('pu_party').getStore().baseParams.isParty = true;
        Ext.getCmp('pu_party').getStore().load();
        addPurchaseInvoice_window.show();
        addPurchaseInvoice_window.doLayout();
        addPurchaseInvoice_window.center();
    }

//<-- ends//
//saving purchase invoice-->
    var WinMask;
    var savePurchaseInvoices = function () {
        WinMask = new Ext.LoadMask(Ext.getCmp('purchase_invoice_form').getEl());
        WinMask.show();
        var grid = Ext.getCmp('pu_itemgrid');
        var grid_store = grid.getStore();
        var data = Ext.pluck(grid_store.getRange(), 'data');
        var t = new Date();
        var t_stamp = t.format("YmdHis");

        if (!Ext.isEmpty(Ext.getCmp('pu_party').getRawValue()) &&
                !Ext.isEmpty(Ext.getCmp('pu_inv_date').getValue()) &&
                Ext.getCmp('pu_netAmount').getValue() > 0) {

            var form_data = {
                invoiceItem_data: data,
                party_id: Ext.getCmp('pu_party').getValue(),
                party: Ext.getCmp('pu_party').getRawValue().toUpperCase(),
                purchcase_order_no: Ext.getCmp('pu_order_no').getValue(),
                ref_no: Ext.getCmp('pu_ref_no').getValue(),
                grossAmount: Ext.getCmp('pu_grossAmount').getValue(),
                discount: Ext.getCmp('pu_discount').getValue(),
                netAmount: Ext.getCmp('pu_netAmount').getValue(),
                signature: Ext.getCmp('pu_signature').getValue(),
                totalItems: Ext.getCmp('pu_totalItems').getValue(),
                totalItemQty: Ext.getCmp('pu_totalItemQty').getValue(),
                tax: Ext.getCmp('pu_tax').getValue(),
                tstamp: t_stamp

            };
            var params = {
                action: 'Insert',
                module: 'finascop_purchase',
                op: 'savePurchaseInvoices',
                extrainfo: 'fsr',
                id: '1'
            };
            APICall(params, Application.Finascop_Purchase.savePurchaseInvoiceData, form_data);
        } else {
            Ext.MessageBox.alert('Error', 'Check the required fields');
        }

        WinMask.hide();
    };
//<-- saving purchase invoice ends

    /* store for slider grid */

    var purchaseMasterPanel = function (id) {

        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white", "padding": "5px 3px"},
            border: false,
            id: id,
            title: 'Purchase Invoice',
            //iconCls: 'finascop_invoice',
            items: [PurchaseEntryGrid(), invoiceTab()]
        });
        return panel;
    };
    //Left side panel contents
    var invoiceTab = function () {
        var invheader = InvoiceDetailsViewpanel();
        var edit_item = editItem_panel();
        var tab = new Ext.Panel({
            frame: false,
            border: true,
            layout: 'border',
            region: 'east',
            title: 'Edit Purchase Invoice',
            width: winsize.width * 0.7,
            id: 'pu_parent_sliderpanel',
            height: winsize.height * 0.6,
            collapsible: true,
            collapseMode: 'mini',
            collapsed: true,
            cls: 'left_side_panel',
            items: [

                new Ext.Panel({
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
                    items: [

                        sliderGrid(),
                        {
                            height: 25,
                            items: [sliderTotalGrid()]
                        }]
                }),
                {
                    layout: 'fit',
                    height: 200,
                    minHeight: 200,
                    region: 'north',
                    items: [invheader, edit_item]
                }]

        });
        return tab;
    };
    var editItem_panel = function () {
        var party_Store = party_store();
        var itemPanel = new Ext.FormPanel({
            id: 'pu_invoiceeditform',
            height: 200,
            border: false,
            hideBorders: true,
            layout: 'column',
            hidden: true,
            style: 'margin-left:2px;',
            items: [{layout: 'form',
                    columnWidth: 0.40,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'puen_Id',
                            labelStyle: 'width:150px',
                            id: 'puen_Id',
                            name: 'puen_Id',
                            anchor: '85%',
                            allowBlank: false,
                            hidden: true
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'invoiceno',
                            labelStyle: 'width:150px',
                            id: 'pu_editSlider_invNo',
                            name: 'invoice_no',
                            anchor: '85%',
                            allowBlank: false,
                            hidden: true

                        }, {
                            xtype: 'datefield',
                            fieldLabel: 'Invoice Date',
                            labelStyle: 'width:150px',
                            id: 'pu_editSlider_invoiceDate',
                            name: 'invoice_Date',
                            anchor: '85%',
                            allowBlank: false,
                            format: 'd/m/Y',
                            maxValue: new Date()
                        }, {
                            xtype: 'combo',
                            fieldLabel: 'Party',
                            labelStyle: 'width:150px',
                            name: 'party_Name',
                            id: 'pu_editSlider_partyName',
                            store: party_Store,
                            anchor: '85%',
                            displayField: 'party',
                            valueField: 'id',
                            mode: 'local',
                            editable: false

                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Purchase Order No',
                            labelStyle: 'width:150px',
                            id: 'pu_editSlider_puOrderno',
                            name: 'purchase_Orderno',
                            anchor: '85%',
                            allowBlank: true
                        }, {
                            xtype: 'datefield',
                            fieldLabel: 'Purchase Order Date',
                            labelStyle: 'width:150px',
                            id: 'pu_editSlider_puOrderdate',
                            name: 'purchase_Orderdate',
                            anchor: '85%',
                            allowBlank: false,
                            format: 'd/m/Y',
                            maxValue: new Date()

                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Reference No',
                            labelStyle: 'width:150px',
                            id: 'pu_editSlider_refNo',
                            name: 'reference_no',
                            anchor: '85%',
                            allowBlank: true
                        }]
                }],
            buttons: [{
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    handler: function () {
                        saveInvoiceEdits();
                    }
                },
                {
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    handler: function () {
                        Ext.getCmp('pu_invoiceeditform').setVisible(false);
                        Ext.getCmp('pu_details_view_panel').setVisible(true);
                    }
                }]
        });
        return itemPanel;
    }

    var saveInvoiceEdits = function () {
        WinMask = new Ext.LoadMask(Ext.getCmp('pu_invoiceeditform').getEl());
        WinMask.show();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(Ext.getCmp('puen_Id').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('pu_editSlider_partyName').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('pu_editSlider_invoiceDate').getValue())) {
            var form_data = {
                puen_Id: Ext.getCmp('puen_Id').getValue(),
                party: Ext.getCmp('pu_editSlider_partyName').getRawValue(),
                purchase_orderno: Ext.getCmp('pu_editSlider_puOrderno').getValue(),
                purchase_orderdate: Ext.util.Format.date(Ext.getCmp('pu_editSlider_puOrderdate').getValue(), 'd/m/Y'),
                invoice_date: Ext.util.Format.date(Ext.getCmp('pu_editSlider_invoiceDate').getValue(), 'd/m/Y'),
                referenceno: Ext.getCmp('pu_editSlider_refNo').getValue(),
                invoiceno: Ext.getCmp('pu_editSlider_invNo').getValue(),
                tstamp: t_stamp
            };
            var params = {
                action: 'Update',
                module: 'Finascop_Purchase',
                op: 'saveEditedData',
                id: Ext.getCmp('puen_Id').getValue(),
                extrainfo: 'fse'
            };
            APICall(params, Application.Finascop_Purchase.savePurchaseInvoiceEditedData, form_data);
        } else {
            Ext.MessageBox.alert('Error', 'Check the required fields');
        }
        WinMask.hide();
    };
    //Left side panel contents ends here

    var InvoiceDetailsViewpanel = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: 'Purchase Invoice Details',
            height: 200,
            autoScroll: true,
            id: 'pu_details_view_panel',
            tpl: new Ext.XTemplate('<div class="finascop_details-outer">',
                    '<table border="0" width="99%" class="finascop_details-slider_font" style="margin-left:3px;">',
                    '<tr><th width=300px>Invoice No:</th><td class="finascop_details-slider_table"> {puen_InvoiceNo} </td></tr>',
                    '<tr><th width=300px>Invoice Date :</th> <td> {puen_InvoiceDate} <td> </tr>',
                    '<tr><th width=300px>Party :</th> <td> {puen_Vendorname}</td></tr>',
                    '<tr><th width=300px>Purchase Order No :</th> <td>  {puen_VendorPurchaseOrder} </td> </tr>',
                    '<tr><th width=300px>Purchse Order Date :</th> <td> {puen_VendorPurchaseOrderDate} </td> </tr>',
                    '<tr><th width=300px>Reference No :</th><td>{puen_RefNo}</td></tr>',
                    '</table>',
                    '</div>'),
            buttons: [
                /* <?php if (user_access("finascop_purchase", "listPurchaseInvoices")) { ?> */
                {
                    text: "Edit",
                    id: 'invoice_edit_btn',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_edit.png',
                    handler: function () {

                        Ext.getCmp('pu_invoiceeditform').setVisible(true);
                        Ext.getCmp('pu_details_view_panel').setVisible(false);
                        // var ID = Ext.getCmp('testGrid').getSelectionModel().getSelections()[0].data.test_id;
                        // Application.Test_Management.EditView(ID);
                    }
                }
                /*<?php  } ?>*/
            ]
        });
    };
    /* Creating Slider Grid   */
    var sliderGrid = function () {

        var invoiceItemStore = new Ext.data.JsonStore({
            fields: ['item', 'item_id', 'hsncode', 'mrp', 'rate', 'qty', {name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'},
                {name: 'sgst', type: 'float'}, {name: 'amt_bf_tax', type: 'float'}, {name: 'amt_af_tax', type: 'float'}],
            url: modURL + '&op=getInvoiceSliderItems',
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        var slider_grid = new Ext.grid.GridPanel({
            store: invoiceItemStore,
            frame: false,
            border: false,
            title: '',
            flex: 2,
            autoScroll: true,
            id: 'pu_Invoice_slidergrid',
            loadMask: true,
            //plugins: [item_action],
            cm: sidepanel_ColModel,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            stripeRows: true
        });
        return slider_grid;
    }; // slider grid ends here

    var sliderTotalGrid = function () {

        var sliderTotalGridStore = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                fields: [{name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'}, {name: 'sgst', type: 'float'},
                    {name: 'amt_bf_tax', type: 'float'}, {name: 'amt_af_tax', type: 'float'}]
            });
            return store;
        };
        var slidertotalGridStore = sliderTotalGridStore();
        var slider_total_panel = new Ext.grid.GridPanel({
            store: slidertotalGridStore,
            layout: 'fit',
            frame: false,
            border: false,
            title: '',
            height: 25,
            style: 'margin-top:2px;',
            id: 'pu_sliderTotal',
            loadMask: true,
            autoScroll: true,
            hideHeaders: true,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: sidepanel_ColModel,
            stripeRows: true
        });
        return slider_total_panel;
    };

    var sidepanel_ColModel = new Ext.grid.ColumnModel([
        {
            header: 'Item',
            sortable: true,
            hideable: false,
            dataIndex: 'item',
            tooltip: 'ITEM',
            width: 140
        },
        {
            header: 'MRP',
            sortable: true,
            dataIndex: 'mrp',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            align: 'right',
            tooltip: 'MRP',
            width: 85
        },
        {
            header: 'HSN Code',
            sortable: true,
            align: 'right',
            dataIndex: 'hsncode',
            tooltip: 'HSN Code',
            width: 80
        },
        {
            header: 'Rate',
            sortable: true,
            dataIndex: 'rate',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            tooltip: 'Rate',
            align: 'right',
            width: 85
        },
        {
            header: 'Quantity',
            sortable: true,
            dataIndex: 'qty',
            align: 'right',
            tooltip: 'Quantity',
            width: 85
        },
        {
            header: 'IGST',
            sortable: true,
            dataIndex: 'igst',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            align: 'right',
            tooltip: 'IGST',
            width: 85
        },
        {
            header: 'CGST',
            sortable: true,
            dataIndex: 'cgst',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            tooltip: 'CGST',
            align: 'right',
            width: 85
        },
        {
            header: 'SGST',
            sortable: true,
            dataIndex: 'sgst',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            tooltip: 'SGST',
            align: 'right',
            width: 85
        },
        {
            header: 'Amt Bf . Tax',
            sortable: true,
            dataIndex: 'amt_bf_tax',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            align: 'right',
            tooltip: 'Amount Before Tax',
            width: 95
        },
        {
            header: 'Amt Af. Tax',
            sortable: true,
            dataIndex: 'amt_af_tax',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            align: 'right',
            tooltip: 'Amount After Tax',
            width: 95
        }


    ]);
    //purchase returnable menu starts//
    var PurchaseReturnableGrid = function () {

        var PurchaseReturnableMasterStore = function () {
            var purchase_returnable_store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=listPurchaseReturnable',
                fields: ['prab_Id', 'prab_RecordDate', 'stit_ID', 'prab_Qty', 'RefInvNo', 'prab_RefIsPurchaseReturn',
                    'prab_EntryBy', 'prab_IsActive', 'prab_updated_on'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false
            });
            return purchase_returnable_store;
        };
        var purchase_returnable_master_store = PurchaseReturnableMasterStore();
        var item_store = itemComboStore();
        var purchase_returnable_entries_grid = new Ext.grid.GridPanel({
            store: purchase_returnable_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [],
            id: 'purchase_returnable_master_grid',
            title: 'Purchase Returnable',
            //iconCls: 'finascop_purchase_return',
            style: 'margin-bottom:3px;',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Returnable Register Number',
                    sortable: true,
                    dataIndex: 'prab_Id',
                    tooltip: 'Returnable Register Number'
                },
                {
                    header: 'Record Date',
                    sortable: true,
                    dataIndex: 'prab_RecordDate',
                    tooltip: 'Record Date'
                }, {
                    header: 'Item ',
                    sortable: true,
                    dataIndex: 'stit_ID',
                    tooltip: 'Item'
                }, {
                    header: 'Quantity',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'prab_Qty',
                    tooltip: 'Quantity'
                }, {
                    header: 'Reference Invoice Number',
                    sortable: true,
                    dataIndex: 'RefInvNo',
                    tooltip: 'Reference Invoice Number'

                }, {
                    header: 'Type ',
                    sortable: true,
                    dataIndex: 'prab_RefIsPurchaseReturn',
                    tooltip: 'Type '
                }, {
                    header: 'Entry By',
                    dataIndex: 'prab_EntryBy',
                    tooltip: 'Entry By'
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    items: [{
                            sortable: false,
                            getClass: function (v, meta, rec) {
                                if (rec.get('prab_IsActive') == 0) {
                                    return 'finascop_hideicon'
                                } else {
                                    return 'finascop_purchase_cancel';
                                }
                            },
                            tooltip: 'Cancel Purchase Returnable',
                            handler: function (grid, rowIndex, colIndex) {
                                Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
                                    if (btn == 'yes') {
                                        var rec = purchase_returnable_master_store.getAt(rowIndex);
                                        grid.store.removeAt(rowIndex);
                                        grid.getView().refresh();
                                        Ext.Ajax.request({
                                            url: modURL + '&op=removePurchaseReturnable',
                                            method: 'POST',
                                            params: {
                                                remove_PRid: rec.get('prab_Id'),
                                                prev_key: rec.get('prab_updated_on')
                                            },
                                            success: function (response) {
                                                //submit success
                                                var tmp = Ext.util.JSON.decode(response.responseText);
                                                if (tmp.success !== undefined && tmp.success === true) {
                                                    Ext.MessageBox.alert('status', tmp.msg);
                                                }
                                            },
                                            //submit failure
                                            failure: function (response) {
                                                var tmp = Ext.util.JSON.decode(response.responseText);
                                                Ext.MessageBox.alert('Error', tmp.msg);
                                            }
                                        });
                                    }
                                });
                            }
                        }]
                }],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            listeners: {
                viewready: updatePagination
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            tbar: ['-',
                {
                    xtype: 'button',
                    text: 'Create Purchase Returnable',
                    tooltip: 'Create Purchase Returnable',
                    iconCls: 'finascop_add',
                    style: 'margin-top:2px; margin-bottom:3px;',
                    handler: function () {
                        addPurchaseReturnable(0);
                    }
                }, '-', {html: '&nbsp;Item : &nbsp;'},
                {
                    xtype: 'combo',
                    text: 'Item',
                    store: item_store,
                    id: 'filter_item',
                    name: 'filter_item',
                    mode: 'local',
                    hiddenName: 'filter_item',
                    displayField: 'item_name',
                    valueField: 'item_id',
                    triggerAction: 'all',
                    forceSelection: false,
                    editable: true,
                    typeAhead: false,
                    enableKeyEvents: true,
                    autoSelect: false,
                    width: 150
                }, {html: '&nbsp; Purchase/Sales Invoice No. : &nbsp;'},
                {
                    xtype: 'textfield',
                    width: 100,
                    text: 'Purchase/Sales Invoice No.',
                    id: 'filter_InvNumber',
                    name: 'filter_InvNumber',
                }
                , {html: '&nbsp; Show Returned : &nbsp;'},
                {
                    xtype: 'checkbox',
                    id: 'filter_ShowReturned',
                    name: 'filter_ShowReturned',
                    checked: false,
                }
                , {html: '&nbsp; From Date : &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 80,
                    text: 'From Date',
                    id: 'filter_FromDate',
                    name: 'filter_FromDate',
                    enableKeyEvents: true
//                    listeners: {
//                        blur: function () {
//                            var fromdate = Ext.getCmp('filter_FromDate').getValue();
//                            var todate = Ext.getCmp('filter_ToDate').getValue();
//                            if (fromdate != '' || todate == '') {
//                                Ext.MessageBox.alert("Notification", "To date must be required");
//                            }
//                        }
//                    }
                }
                , {html: '&nbsp; To Date : &nbsp;'},
                {
                    xtype: 'datefield',
                    text: 'To Date',
                    width: 80,
                    id: 'filter_ToDate',
                    name: 'filter_ToDate',
                    enableKeyEvents: true
//                    listeners: {
//                        blur: function () {
//                            var fromdate = Ext.getCmp('filter_FromDate').getValue();
//                            var todate = Ext.getCmp('filter_ToDate').getValue();
//                            if (fromdate == '' || todate != '') {
//                                Ext.MessageBox.alert("Notification", "To date must be required");
//                        }
//                    }
                }
                , '-', {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {

                        var params_to_store = function () {
                            purchase_returnable_master_store.baseParams = {
                                filter_item: Ext.getCmp('filter_item').getRawValue(),
                                filter_itemId: Ext.getCmp('filter_item').getValue(),
                                filter_InvNumber: Ext.getCmp('filter_InvNumber').getValue(),
                                filter_ShowReturned: Ext.getCmp('filter_ShowReturned').getValue(),
                                filter_FromDate: Ext.util.Format.date(Ext.getCmp('filter_FromDate').getValue(), 'd/m/Y'),
                                filter_ToDate: Ext.util.Format.date(Ext.getCmp('filter_ToDate').getValue(), 'd/m/Y'),
                            };
                            purchase_returnable_master_store.load();
                        }

                        var fromdate = Ext.getCmp('filter_FromDate').getValue();
                        var todate = Ext.getCmp('filter_ToDate').getValue();
                        if (fromdate != '' && todate == '') {
                            //Ext.MessageBox.alert("Notification", "FromDate and ToDate must be required");
                            Ext.MessageBox.alert('Notification', 'To Date is required');
                        } else if (fromdate == '' && todate != '') {
                            Ext.MessageBox.alert('Notification', 'From Date is required');
                        } else {
                            params_to_store();
                        }

                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Reset',
                    iconCls: 'finascop_my-resetpass',
                    handler: function () {

                        Ext.getCmp('filter_item').reset();
                        Ext.getCmp('filter_item').clearValue();
                        Ext.getCmp('filter_InvNumber').reset();
                        Ext.getCmp('filter_ShowReturned').reset();
                        Ext.getCmp('filter_FromDate').reset();
                        Ext.getCmp('filter_ToDate').reset();
                        purchase_returnable_master_store.removeAll();
                    }
                }, '-'
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: purchase_returnable_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });
        return purchase_returnable_entries_grid;
    };
    // purchase returnable add Form: -->
    var viewPurchaseReturnable_panel = function () {

        var PurchaseReturnableGridStore = function () {
            var purchase_returnable_grid_store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseReturnableGridData',
                fields: ['itemname', 'stit_ID', 'puen_InvNo', 'puen_InvDate', 'paii_itemQty', 'prab_Qty',
                    'item_avialability', 'refId', 'puen_updated_on'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true
            });
            return purchase_returnable_grid_store;
        };
        var purchase_returnable_Gridstore = PurchaseReturnableGridStore();
        var item_store = itemComboStore();
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'purchase_returnable_add_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.35,
                            border: false,
                            labelWidth: 35,
                            style: 'margin-top:10px;',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Item',
                                    store: item_store,
                                    id: 'window_filter_item',
                                    name: 'window_filter_item',
                                    mode: 'local',
                                    hiddenName: 'window_filter_item',
                                    displayField: 'item_name',
                                    valueField: 'item_id',
                                    triggerAction: 'all',
                                    forceSelection: false,
                                    editable: true,
                                    typeAhead: false,
                                    enableKeyEvents: true,
                                    autoSelect: false,
                                    anchor: '85%',
                                    allowBlank: false,
                                    listeners:{
                                       afterrender:function(field){
                                       Ext.defer(function(){
                                       field.focus(true,100);
                                       },1);
                                     }
                                   }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.25,
                            border: false,
                            labelWidth: 35,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'From',
                                    xtype: 'datefield',
                                    id: 'item_search_from',
                                    name: 'n[search_from]',
                                    anchor: '88%',
                                    editable: true,
                                    allowBlank: false,
                                    format: 'd/m/Y'

                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.25,
                            border: false,
                            labelWidth: 25,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'To',
                                    xtype: 'datefield',
                                    id: 'item_search_to',
                                    name: 'n[search_to]',
                                    anchor: '86%',
                                    editable: true,
                                    allowBlank: false,
                                    value: (new Date()),
                                    format: 'd/m/Y'

                                }]
                        },
                        {
                            xtype: 'button',
                            text: 'Search',
                            iconCls: 'finascop_search_btn',
                            id: 'ch_filter_button',
                            style: 'margin-left:10px;margin-top:10px;',
                            handler: function () {
                                var fdate = Ext.getCmp('item_search_from').getValue();
                                var item = Ext.getCmp('window_filter_item').getValue();
                                var tdate = Ext.getCmp('item_search_to').getValue();
                                if (item != "" && fdate == "") {
                                    Ext.MessageBox.alert("Notification", "From Date is required");
                                } else if (item == "" && fdate != "")
                                {

                                    Ext.MessageBox.alert("Notification", "Choose any Item");
                                } else if (item == "" && fdate == "")
                                {
                                    Ext.MessageBox.alert("Notification", "Item Name and From Date are required");
                                } else if (fdate > tdate) {
                                    Ext.MessageBox.alert("Notification", "From Date cannot be greater than To Date");
                                } else {
                                    purchase_returnable_Gridstore.baseParams = {
                                        itemName: Ext.getCmp('window_filter_item').getRawValue(),
                                        itemId: Ext.getCmp('window_filter_item').getValue(),
                                        item_search_from: Ext.util.Format.date(Ext.getCmp('item_search_from').getValue(), 'd/m/Y'),
                                        item_search_to: Ext.util.Format.date(Ext.getCmp('item_search_to').getValue(), 'd/m/Y'),
                                    };
                                    purchase_returnable_Gridstore.load();
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'Reset',
                            iconCls: 'finascop_my-resetpass',
                            style: 'margin-left:15px;margin-top:10px;',
                            handler: function () {

                                Ext.getCmp('window_filter_item').reset();
                                Ext.getCmp('item_search_from').reset();
                                Ext.getCmp('item_search_to').reset();
                                purchase_returnable_Gridstore.removeAll();
                            }
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    title: 'Purchase Returnable Items',
                    columnWidth: 1,
                    id: 'purchase_returnable_gridItems',
                    items: new Ext.grid.GridPanel({
                        store: purchase_returnable_Gridstore,
                        layout: 'fit',
                        id: 'purchase_returnable_window_grid',
                        height: 380,
                        columns: [new Ext.grid.RowNumberer(),
                            {
                                header: 'Item',
                                sortable: true,
                                dataIndex: 'itemname',
                                tooltip: 'Item'
                            },
                            {
                                header: 'Purchase Invoice Number',
                                sortable: true,
                                dataIndex: 'puen_InvNo',
                                tooltip: 'Purchase Invoice Number',
                                width: 150
                            }, {
                                header: 'Date',
                                sortable: true,
                                dataIndex: 'puen_InvDate',
                                tooltip: 'Purchase Invoice Date'
                            }, {
                                header: 'Item Count',
                                sortable: true,
                                align: 'right',
                                dataIndex: 'paii_itemQty',
                                tooltip: 'Item Count'
                            }, {
                                header: 'Returnable Marked Count',
                                sortable: true,
                                align: 'right',
                                dataIndex: 'prab_Qty',
                                tooltip: 'Returnable Marked Count',
                                width: 150
                            }, {
                                header: 'Marked as Returnable',
                                sortable: true,
                                dataIndex: 'item_avialability',
                                tooltip: 'Marked as Returnable',
                                width: 150

                            },
                            {
                                xtype: 'actioncolumn',
                                width: 67,
                                hideable: false,
                                items: [{
                                        sortable: false,
                                        getClass: function (v, meta, rec) {
                                            var show = 1;
                                            var returnable_Qty = Number(rec.get('paii_itemQty')) - Number(rec.get('prab_Qty'));
                                            if (returnable_Qty == 0) {
                                                /* <?php if (user_access("finascop_purchase", "savePurchaseReturnable_ModifyData")) { ?> */
                                                show = 0;
                                                /* <?php } ?> */
                                            }
                                            if (show == 1) {
                                                this.items[0].tooltip = 'Modify Purchase Return';
                                                return 'finascop_modify';
                                            } else {
                                                return 'finascop_hideicon';
                                            }
                                        },
                                        handler: function (grid, rowIndex, colIndex) {
                                            var rec = purchase_returnable_Gridstore.getAt(rowIndex);
                                            modifyAddPurchaseReturnable(rec.get('stit_ID'), rec.get('refId'),
                                                    rec.get('prab_Qty'), rec.get('paii_itemQty'),
                                                    rec.get('itemname'), rec.get('puen_InvDate'), rec.get('puen_updated_on'),
                                                    rec.get('puen_InvNo'));
                                        }
                                    }]
                            }
                        ],
                        bbar: new Ext.PagingToolbar({
                            pageSize: recs_per_page,
                            store: purchase_returnable_Gridstore,
                            displayInfo: true,
                            displayMsg: 'Displaying items {0} - {1} of {2}',
                            emptyMsg: "No records to display"
                        }),
                        stripeRows: true

                    })
                }]

        });
        return panel;
    };
    // <-- purchase returnable add Form //

    //modify purchase returnable: -->//
    var viewPurchaseReturnable_Modifypanel = function (itemname, puen_InvDate, paii_itemQty, prab_Qty, refId, stit_ID, puen_updated_on, puen_InvNo) {

        var returnable_Qty = Number(paii_itemQty) - Number(prab_Qty);
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'purchase_returnable_modify_form',
            items: [
                {
                    fieldLabel: 'Ref Id',
                    labelWidth: 50,
                    xtype: 'hidden',
                    id: 'refId',
                    name: 'refId',
                    value: refId,
                    anchor: '88%',
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    fieldLabel: 'invoice number',
                    labelWidth: 50,
                    xtype: 'hidden',
                    id: 'inv_number',
                    name: 'inv_number',
                    value: puen_InvNo,
                    anchor: '88%',
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    fieldLabel: 'update Id',
                    labelWidth: 50,
                    xtype: 'hidden',
                    id: 'update_id',
                    name: 'update_id',
                    value: puen_updated_on,
                    anchor: '88%',
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    fieldLabel: 'itemId',
                    labelWidth: 50,
                    xtype: 'hidden',
                    id: 'itemId',
                    name: 'itemId',
                    value: stit_ID,
                    anchor: '88%',
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    fieldLabel: 'Date',
                    labelWidth: 50,
                    xtype: 'textfield',
                    id: 'modify_datefield',
                    name: 'modify_datefield',
                    anchor: '88%',
                    value: puen_InvDate,
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    fieldLabel: 'Item Name',
                    labelWidth: 50,
                    xtype: 'textfield',
                    id: 'modify_itemName',
                    name: 'modify_itemName',
                    anchor: '88%',
                    value: itemname,
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    fieldLabel: 'Returnable Quantity',
                    labelWidth: 50,
                    xtype: 'numberfield',
                    id: 'modify_ReturnableQty',
                    name: 'modify_ReturnableQty',
                    value: returnable_Qty,
                    anchor: '88%',
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    fieldLabel: 'To Return',
                    labelWidth: 0,
                    xtype: 'numberfield',
                    id: 'modify_toReturn',
                    name: 'modify_toReturn',
                    anchor: '88%',
                    readOnly: false,
                    editable: true,
                    allowBlank: false

                }
            ]

        });
        return panel;
    };
    var modifyAddPurchaseReturnable = function (stit_ID, refId, prab_Qty, paii_itemQty, itemname, puen_InvDate, puen_updated_on, puen_InvNo) {

        var purchase_returnable_modify_form = viewPurchaseReturnable_Modifypanel(itemname, puen_InvDate, paii_itemQty, prab_Qty, refId, stit_ID, puen_updated_on, puen_InvNo);
        var modifyPurchaseReturnable_Window = Ext.getCmp('purchaseReturnableModify_window');
        if (Ext.isEmpty(modifyPurchaseReturnable_Window)) {
            var modifyPurchaseReturnable_Window = new Ext.Window({
                id: 'purchaseReturnableModify_window',
                title: 'Modify Purchase Returnable',
                //iconCls: 'finascop_modify',
                modal: true,
                layout: 'fit',
                width: 350,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [purchase_returnable_modify_form],
                buttons: [{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            var returnableQty = Number(Ext.getCmp('modify_ReturnableQty').getValue());
                            var returnQty = Number(Ext.getCmp('modify_toReturn').getValue());
                            if (returnableQty == 0) {
                                Ext.MessageBox.alert('Error', 'Can not modify this item. No item available for return');
                            } else if (returnQty <= returnableQty) {

                                savePurchaseReturnable_ModifyData();
                            } else {
                                Ext.MessageBox.alert('Error', 'Can not return this item');
                            }
                        }
                    },
                    {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {

                            modifyPurchaseReturnable_Window.close();
                        }
                    }]
            });
        }
//        
        modifyPurchaseReturnable_Window.show();
        modifyPurchaseReturnable_Window.doLayout();
        modifyPurchaseReturnable_Window.center();
    }
    //saving purchase returnable modify data

    var savePurchaseReturnable_ModifyData = function () {
        WinMask = new Ext.LoadMask(Ext.getCmp('purchase_returnable_modify_form').getEl());
        WinMask.show();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(Ext.getCmp('modify_datefield').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('modify_itemName').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('modify_ReturnableQty').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('modify_toReturn').getValue())) {

            var form_data = {
                mod_Date: Ext.getCmp('modify_datefield').getValue(),
                mod_ItemName: Ext.getCmp('modify_itemName').getValue(),
                mod_ReturnableQty: Ext.getCmp('modify_ReturnableQty').getValue(),
                mod_ToReturn: Ext.getCmp('modify_toReturn').getValue(),
                refId: Ext.getCmp('refId').getValue(),
                itemId: Ext.getCmp('itemId').getValue(),
                previous_key: Ext.getCmp('update_id').getValue(),
                invoice_no: Ext.getCmp('inv_number').getValue(),
                tstamp: t_stamp
            };
            var params = {
                action: 'Insert',
                module: 'finascop_purchase',
                op: 'savePurchaseReturnable_ModifyData',
                extrainfo: 'fsr',
                id: '1'
            };
            APICall(params, Application.Finascop_Purchase.saveModifyData, form_data);
        } else {
            Ext.MessageBox.alert('Error', 'Check the required fields');
        }

        WinMask.hide();
    };
    // purchase returnable add or edit window: -->//

    var addPurchaseReturnable = function (id) {
        var title = (id == 0) ? 'New Purchase Returnable' : 'Edit Purchase Returnable';
        var purchase_returnable_add_form = viewPurchaseReturnable_panel();
        var addPurchaseReturnable_Window = Ext.getCmp('purchaseReturnable_window');
        if (Ext.isEmpty(addPurchaseReturnable_Window)) {
            var addPurchaseReturnable_Window = new Ext.Window({
                id: 'purchaseReturnable_window',
                title: title,
                //iconCls: 'finascop_purchase_returns',
                modal: true,
                layout: 'fit',
                width: 900,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [purchase_returnable_add_form],
                buttons: [
                    {
                        text: 'OK',
                    //    iconCls: 'finascop_ok',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        handler: function () {

                            Ext.getCmp('purchase_returnable_window_grid').getStore().removeAll(true);
                            Ext.getCmp('purchase_returnable_window_grid').getView().refresh();
                            addPurchaseReturnable_Window.close();
                        }
                    }]
            });
        }

        addPurchaseReturnable_Window.show();
        addPurchaseReturnable_Window.doLayout();
        addPurchaseReturnable_Window.center();
    }

//<-- ends//

//purchase returnable menu ends//

//purchase return menu starts//
    var PurchaseReturnGrid = function () {

        var PurchaseReturnMasterStore = function () {
            var purchase_return_store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=listPurchaseReturn',
                fields: ['pure_id', 'pure_InvoiceNo', 'pure_InvoiceDate', 'pure_TotalItems',
                    'pure_TotalItemsQty', 'pure_NetAmt', 'pure_EntryOn', 'pure_EntryBy', 'puen_Id', 'have_returned', 'invID', 'pure_updated_on', {name: 'returned_item_total', type: 'float'}, {name: 'total_items_qty', type: 'float'}, {name: 'totalamount', type: 'float'}],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                listeners: {

                    load: function (store, record, options) {
                        if(record.length > 0 )
                        {
                            Ext.getCmp('purchase_return_master_grid').getSelectionModel().selectRow(0);
                            var record = Ext.getCmp('purchase_return_master_grid').getSelectionModel().getSelected();
                            var returned_item_total = record.get('returned_item_total');
                            var total_items_qty = record.get('total_items_qty');
                            var totalamount = record.get('totalamount');
                            var number_of_col = Ext.getCmp('purchase_return_master_grid').getColumnModel().config.length;
                            var tooltipmaker = Ext.getCmp('purchase_return_master_grid').getColumnModel().config;
    
                            for (var i = 1; i < number_of_col; i++) {
                                var column_header = tooltipmaker[i].header;
                                if (column_header == 'Returned Items Count')
                                {
                                    tooltipmaker[i].tooltip = 'Total Returned Item Count : ' + returned_item_total;
                                }
                                if (column_header == 'Quantity')
                                {
                                    tooltipmaker[i].tooltip = 'Total Qty : ' + total_items_qty;
                                }
    
                                if (column_header == 'Amount')
                                {
                                    tooltipmaker[i].tooltip = 'Total Amount : ' + totalamount;
                                }
    
                            }
                        }
                      


                    }
                }
            });
            return purchase_return_store;
        };
        var purchase_return_master_store = PurchaseReturnMasterStore();
        var purchase_return_entries_grid = new Ext.grid.GridPanel({
            store: purchase_return_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [],
            id: 'purchase_return_master_grid',
            title: 'Purchase Return',
            //iconCls: 'finascop_purchase_returns',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Purchase Return Invoice No',
                    sortable: true,
                    dataIndex: 'pure_InvoiceNo',
                    tooltip: 'Purchase Return Invoice No'
                },
                {
                    header: 'Invoice date',
                    sortable: true,
                    dataIndex: 'pure_InvoiceDate',
                    tooltip: 'Invoice date'
                }, {
                    header: 'Returned Items Count',
                    align: 'right',
                    sortable: true,
                    dataIndex: 'pure_TotalItems',
                    tooltip: 'Returned Items Count'
                }, {
                    header: 'Quantity',
                    align: 'right',
                    sortable: true,
                    dataIndex: 'pure_TotalItemsQty',
                    tooltip: 'Quantity'
                }, {
                    header: 'Amount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    sortable: true,
                    dataIndex: 'pure_NetAmt',
                    tooltip: 'Amount'

                }, {
                    header: 'Entry By',
                    dataIndex: 'pure_EntryBy',
                    tooltip: 'Entry By'
                },
                {
                    header: 'Entry On',
                    sortable: true,
                    dataIndex: 'pure_EntryOn',
                    tooltip: 'Entry On'
                },
                {
                    xtype: 'actioncolumn',
                    //header: 'Actions',
                    hideable: false,
                    items: [{
                            sortable: false,
                            getClass: function (v, meta, rec) {

                                return 'finascop_PurchaseReturn_View';
                            },
                            tooltip: 'View Purchase Return',
                            handler: function (grid, rowIndex, colIndex) {
                                grid.getSelectionModel().selectRow(rowIndex);
                                var rec = purchase_return_master_store.getAt(rowIndex);
                                var id = 1;
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                Ext.Ajax.request({
                                    url: modURL + '&op=getPurchaseReturn_details',
                                    method: 'POST',
                                    params: {
                                        invID: rec.get('invID'),
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp

                                    },
                                    success: function (response) {

                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            var pure_invNo = tmp.data.pure_invNo;
                                            addOrViewpurchaseReturn(id, rec, pure_invNo);
                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }

                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            }
                        }, {
                            sortable: false,
                            getClass: function (v, meta, rec) {
                                return 'finascop_purchase_cancel';
                            },
                            tooltip: 'Cancel Purchase Return',
                            handler: function (grid, rowIndex, colIndex) {
                                Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
                                    if (btn == 'yes') {
                                        var rec = purchase_return_master_store.getAt(rowIndex);
                                        Ext.Ajax.request({
                                            url: modURL + '&op=removePurchaseReturn',
                                            method: 'POST',
                                            params: {
                                                remove_PRid: rec.get('pure_id'),
                                                puen_Id: rec.get('puen_Id'),
                                                prev_key: rec.get('pure_updated_on')
                                            }, 
                                            success: function (response) {
                                                //submit success
                                                var tmp = Ext.util.JSON.decode(response.responseText);
                                                if (tmp.success !== undefined && tmp.success === true) {
                                                    Ext.MessageBox.alert('status', tmp.msg);
                                                }
                                            },
                                            //submit failure
                                            failure: function (response) {
                                                var tmp = Ext.util.JSON.decode(response.responseText);
                                                Ext.MessageBox.alert('Error', tmp.msg);
                                            }
                                        });
                                        grid.store.removeAt(rowIndex);
                                        grid.getView().refresh();
                                    }
                                });
                            }


                        }]
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
            listeners: {
                viewready: updatePagination
            },
            tbar: ['-',
                {
                    xtype: 'button',
                    text: 'Create/View Purchase Return',
                    tooltip: 'Create/View Purchase Return',
                    iconCls: 'finascop_add',
                    handler: function (grid, rowIndex, colIndex) {

                        addPurchaseReturn(0);
                    }
                }, '-'
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: purchase_return_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });
        return purchase_return_entries_grid;
    };
//<-- ends//
// purchase return add or edit window: -->//

    var addPurchaseReturn = function (id) {
        var title = (id == 0) ? 'Purchase Invoices' : '';
        var purchase_return_add_form = viewPurchaseReturn_panel();
        var addPurchaseReturn_Window = Ext.getCmp('purchaseReturn_window');
        if (Ext.isEmpty(addPurchaseReturn_Window)) {
            var addPurchaseReturn_Window = new Ext.Window({
                id: 'purchaseReturn_window',
                title: title,
                //iconCls: 'finascop_purchase_returns',
                modal: true,
                layout: 'fit',
                width: 810,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [purchase_return_add_form],
                buttons: [
                    {
                        text: 'OK',
                    //    iconCls: 'finascop_ok',
                    tabIndex:504,
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                        handler: function () {

                            Ext.getCmp('purchase_return_window_grid').getStore().removeAll();
                            addPurchaseReturn_Window.close();
                        }
                    }]
            });
        }

        addPurchaseReturn_Window.show();
        addPurchaseReturn_Window.doLayout();
        addPurchaseReturn_Window.center();
    }

//<-- ends//


    // purchase return add Form: -->
    var viewPurchaseReturn_panel = function () {

        var PurchaseReturnAddGridStore = function () {
            var purchase_return_grid_store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseReturnAddData',
                fields: ['invID', 'puRe_InvNo', 'puRe_InvDate', 'puRe_totalitems', 'have_returned', 'br_id',
                    {name: 'new_purchaseReturn', type: 'number'}, {name: 'view_purchaseReturn', type: 'number'}, 'pure_InvoiceNo', 'puen_updated_on'
                ],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false
            });
            return purchase_return_grid_store;
        };
        var purchase_return_addGridstore = PurchaseReturnAddGridStore();
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'purchase_return_add_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:5px;',
                    columnWidth: 1,
                    items: [

                        {
                            layout: 'form',
                            columnWidth: 0.5,
                            border: false,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'Purchase Invoice No',
                                    xtype: 'textfield',
                                    id: 'filter_invNo',
                                    labelStyle: 'width:125px',
                                    name: 'filter_invNo',
                                    anchor: '88%',
                                    tabIndex:501,
                                    editable: true,
                                    allowBlank: false,
                                    listeners:{
                                       afterrender:function(field){
                                       Ext.defer(function(){
                                       field.focus(true,100);
                                       },1);
                                     }
                                   }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.23,
                            border: false,
                            labelWidth: 30,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'From',
                                    xtype: 'datefield',
                                    id: 'invNo_search_from',
                                    name: 'invNo_search_from',
                                    anchor: '88%',
                                    tabIndex:502,
                                    editable: true,
                                    allowBlank: false,
                                    format: 'd/m/Y'

                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.21,
                            border: false,
                            labelWidth: 18,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'To',
                                    xtype: 'datefield',
                                    id: 'invNo_search_to',
                                    name: 'invNo_search_to',
                                    anchor: '86%',
                                    tabIndex:503,
                                    editable: true,
                                    allowBlank: false,
                                    value: (new Date()),
                                    format: 'd/m/Y'

                                }]
                        },
                        {
                            xtype: 'button',
                            text: 'Find Invoice',
                            iconCls: 'finascop_search_btn',
                            id: 'purchaseReturns_filter_button',
                            style: 'margin-left:10px;margin-top:10px;',
                            handler: function () {
                                var fdate = Ext.getCmp('invNo_search_from').getValue();
                                var tdate = Ext.getCmp('invNo_search_to').getValue();
                                if (fdate >= tdate) {
                                    Ext.MessageBox.alert("Notification", "From Date cannot be greater than To Date");
                                } else {

                                    purchase_return_addGridstore.baseParams = {
                                        filter_invNo: Ext.getCmp('filter_invNo').getRawValue(),
                                        invNo_search_from: Ext.util.Format.date(Ext.getCmp('invNo_search_from').getValue(), 'd/m/Y'),
                                        invNo_search_to: Ext.util.Format.date(Ext.getCmp('invNo_search_to').getValue(), 'd/m/Y'),
                                    };
                                    purchase_return_addGridstore.load();
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'Reset',
                            iconCls: 'finascop_my-resetpass',
                            style: 'margin-left:15px;margin-top:10px;',
                            handler: function () {

                                Ext.getCmp('filter_invNo').reset();
                                Ext.getCmp('invNo_search_from').reset();
                                Ext.getCmp('invNo_search_to').reset();
                                purchase_return_addGridstore.removeAll();
                            }
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'purchase_return_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: new Ext.grid.GridPanel({
                                        store: purchase_return_addGridstore,
                                        layout: 'fit',
                                        id: 'purchase_return_window_grid',
                                        height: 360,
                                        columns: [new Ext.grid.RowNumberer(),
                                            {
                                                header: 'Purchase Invoice Number',
                                                sortable: true,
                                                dataIndex: 'puRe_InvNo',
                                                tooltip: 'Purchase Invoice Number',
                                                width: 190
                                            }, {
                                                header: 'Date',
                                                sortable: true,
                                                dataIndex: 'puRe_InvDate',
                                                tooltip: 'Purchase Invoice Date',
                                                width: 165
                                            }, {
                                                header: 'Item Count',
                                                sortable: true,
                                                dataIndex: 'puRe_totalitems',
                                                tooltip: 'Item Count',
                                                width: 165
                                            }, {
                                                header: 'Have Returned',
                                                sortable: true,
                                                dataIndex: 'have_returned',
                                                tooltip: 'Have Returned',
                                                width: 170

                                            },
                                            {
                                                xtype: 'actioncolumn',
                                                width: 60,
                                                hideable: false,
                                                id: 'purchase_return_action',
                                                items: [{
                                                        sortable: false,
                                                        getClass: function (v, meta, rec) {
                                                            if (rec.get('have_returned') == 'YES')
                                                            {
                                                                this.items[0].tooltip = 'View Purchase Return';
                                                                return 'finascop_PurchaseReturn_View';
                                                            } else {
                                                                this.items[0].tooltip = 'New Purchase Return';
                                                                return 'finascop_purchase_return_action';
                                                            }

                                                        },
                                                        handler: function (grid, rowIndex, colIndex) {

                                                            var rec = purchase_return_addGridstore.getAt(rowIndex);
                                                            var id = 0;
                                                            var pure_invNo = "";
                                                            var pure_returnedQty = 0;
                                                            if (rec.get('have_returned') == 'YES') {
                                                                var id = 1;
                                                                var t = new Date();
                                                                var t_stamp = t.format("YmdHis");
                                                                Ext.Ajax.request({
                                                                    url: modURL + '&op=getPurchaseReturn_details',
                                                                    method: 'POST',
                                                                    params: {
                                                                        invID: rec.get('invID'),
                                                                        apikey: _SESSION.apikey,
                                                                        tstamp: t_stamp

                                                                    },
                                                                    success: function (response) {

                                                                        var tmp = Ext.decode(response.responseText);
                                                                        if (tmp.success === true) {
                                                                            pure_invNo = tmp.data.pure_invNo;
                                                                            addOrViewpurchaseReturn(id, rec, pure_invNo);
                                                                        } else {
                                                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                                                        }

                                                                    },
                                                                    failure: function (response, options) {
                                                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                                    }
                                                                });
                                                            } else {
                                                                addOrViewpurchaseReturn(id, rec, pure_invNo);
                                                            }


                                                        }
                                                    }]
                                            }
                                        ],
                                        bbar: new Ext.PagingToolbar({
                                            pageSize: recs_per_page,
                                            store: purchase_return_addGridstore,
                                            displayInfo: true,
                                            displayMsg: 'Displaying items {0} - {1} of {2}',
                                            emptyMsg: "No records to display"
                                        }),
                                        listeners: {
                                            viewready: updatePagination
                                        },
                                        stripeRows: true
                                    })
                                }]
                        }]
                }
            ]
        });
        return panel;
    };
    // <-- purchase returnable add Form //

    var addOrViewpurchaseReturn = function (id, rec, pure_invNo) {

        var pure_invNo = pure_invNo;
        var title = (id == 0) ? 'Add Purchase Return' : 'View Purchase Return ' + pure_invNo;
        if (rec.get('have_returned') == 'YES') {
            var show = false;
        } else {
            show = true;
        }

        var addOrViewpurchaseReturnWindow = Ext.getCmp('addOrViewPurchaseReturn_window');
        if (Ext.isEmpty(addOrViewpurchaseReturnWindow)) {
            var addOrViewpurchaseReturnWindow = new Ext.Window({
                id: 'addOrViewPurchaseReturn_window',
                title: title,
                //iconCls: 'finascop_purchase_return_action',
                modal: true,
                layout: 'fit',
                width: 795,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [viewPurchaseReturngrid(show), purchaseReturnTotalGrid(), purchaseReturn_amount_panel()],
                buttons: [{
                        text: 'PURCHASE RETURNED',
                        id: 'btn_PurchaseReturned',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        disabled: button_enable,
                        handler: function () {

                            savePurchaseReturnData(rec);
                        }
                    },
                    {
                        text: 'CANCEL',
                        id: 'btn_PRcancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            Ext.getCmp('purchase_return_grid').getStore().removeAll();
                            Ext.getCmp('purchasereturnTotal').getStore().removeAll();
                            addOrViewpurchaseReturnWindow.close();
                        }
                    }]
            });
        }

        if (show == false) {
            Ext.getCmp('btn_PurchaseReturned').setVisible(false)
            Ext.getCmp('btn_PRcancel').setText('CLOSE');
            var button_enable = true;
            var view = 1;
        } else {
            view = 0;
            button_enable = false;
        }

        var purchase_return_addStore = Ext.getCmp('purchase_return_grid').getStore();
        purchase_return_addStore.baseParams = {
            invoice_no: rec.get('puRe_InvNo'),
            invID: rec.get('invID'),
            view: view
        };
        purchase_return_addStore.load({
            callback: function () {
                purchaseReturnTotalGridCalculation();
            }
        });
        addOrViewpurchaseReturnWindow.show();
        addOrViewpurchaseReturnWindow.doLayout();
        addOrViewpurchaseReturnWindow.center();
    }

    // <-- purchase return action grid
    var purchaseReturnGridViewStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listpurchasereturnviewstore',
                method: 'post'
            }),
            fields: ['itemId', 'prab_IsActive', 'partyId', 'state_id', 'purchaseId', 'addPR_Item', 'addPR_ItemQty',
                {name: 'addPR_ReturnQty', type: 'number'}, {name: 'addPR_Rate', type: 'float'},
                {name: 'addPR_IGST', type: 'float'}, {name: 'addPR_CGST', type: 'float'},
                {name: 'addPR_SGST', type: 'float'}, {name: 'addPR_Amount', type: 'float'},
                'checkedIds', 'stock_enabled'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false

        });
        return store;
    };
    var viewPurchaseReturngrid = function (show) {


        var PurchaseReturnAddGridStore = function () {
            var purchase_return_grid_store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=listpurchasegridstore',
                fields: ['itemId', 'prab_IsActive', 'partyId', 'state_id', 'purchaseId', 'addPR_Item', 'addPR_ItemQty',
                    {name: 'addPR_ReturnQty', type: 'number'}, {name: 'addPR_Rate', type: 'float'},
                    {name: 'addPR_IGST', type: 'float'}, {name: 'addPR_CGST', type: 'float'},
                    {name: 'addPR_SGST', type: 'float'}, {name: 'addPR_Amount', type: 'float'}

                ],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false
            });
            return purchase_return_grid_store;
        };
        if (show == false)
        {
            var purchase_return_addStore = purchaseReturnGridViewStore();
        } else
        {
            purchase_return_addStore = PurchaseReturnAddGridStore();
        }

        var grid = new Ext.grid.EditorGridPanel({
            store: purchase_return_addStore,
            layout: 'fit',
            id: 'purchase_return_grid',
            height: 30,
            loadMask: true,
            cm: colsModel_maingrid(show),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            })
        });
        var view = grid.getView();
        view.addListener('rowupdated', function (view, firstRow, rec) {
            var returnQty = rec.get('addPR_ReturnQty');
            add_purchasereturn(returnQty, rec);
        });
        return grid;
    };
    var purchaseReturnTotalGridCalculation = function () {

        var grid = Ext.getCmp('purchase_return_grid');
        grid.getStore().commitChanges();
        var store = grid.getStore();
        var igsttotal = Ext.util.Format.round((store.sum('addPR_IGST')), 2);
        var cgsttotal = Ext.util.Format.round((store.sum('addPR_CGST')), 2);
        var sgsttotal = Ext.util.Format.round((store.sum('addPR_SGST')), 2);
        var totalamount = Ext.util.Format.round((store.sum('addPR_Amount')), 2);
        var return_qty = store.sum('addPR_ReturnQty');
        var row_total = new Ext.data.Record.create([
            {name: 'addPR_IGST', type: 'float'},
            {name: 'addPR_CGST', type: 'float'},
            {name: 'addPR_SGST', type: 'float'},
            {name: 'addPR_Amount', type: 'float'},
            {name: 'addPR_ReturnQty', type: 'number'},
            {name: 'itemId'}

        ]);
        var totalsdata = new row_total({
            itemId: '0',
            addPR_IGST: igsttotal,
            addPR_CGST: cgsttotal,
            addPR_SGST: sgsttotal,
            addPR_Amount: totalamount,
            addPR_ReturnQty: return_qty

        });
        Ext.getCmp('purchaseReturn_netAmount').setValue(totalamount);
        Ext.getCmp('purchasereturnTotal').getStore().removeAll();
        Ext.getCmp('purchasereturnTotal').getStore().add(totalsdata);
    }
    var colsModel_maingrid = function (show) {

        var colsModel_maingrid = new Ext.grid.ColumnModel([
            {
                header: 'Item',
                sortable: true,
                dataIndex: 'addPR_Item',
                tooltip: 'Item Name',
                width: 100
            }, {
                header: 'Purchased Item Qty',
                sortable: true,
                dataIndex: 'addPR_ItemQty',
                tooltip: 'Purchased Item Quantity',
                width: 115,
                editable: false,
                align: 'right'
                        // xtype: 'numbercolumn'
            }, {
                header: 'Return Quantity',
                id: 'returnedQty',
                editable: show,
                dataIndex: 'addPR_ReturnQty',
                tooltip: 'Return Quantity',
                width: 100,
                editor: new Ext.form.NumberField({
                    xtype: 'numberfield',
                    id: 'returnedQty',
                    allowBlank: false,
                    disabled: false,
                    enableKeyEvents: true,
                    validator: function (value) {
                        return !isNaN(value)
                    },
                    listeners: {
                        focus: function (txtbx) {
                            txtbx.selectText();
                        },
                        keyup: function (numfield, event) {
                            var returnedQty = numfield.getValue();
                            var rec = Ext.getCmp('purchase_return_grid').getSelectionModel().getSelected();
                            rec.set('addPR_ReturnQty', returnedQty);
                        }
                    }
                }),
                align: 'right'
                        //xtype: 'numbercolumn'
            }, {
                header: 'Rate',
                sortable: true,
                dataIndex: 'addPR_Rate',
                tooltip: 'Rate',
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                width: 75

            }, {
                header: 'IGST',
                sortable: true,
                dataIndex: 'addPR_IGST',
                tooltip: 'IGST',
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                width: 75

            }, {
                header: 'CGST',
                sortable: true,
                dataIndex: 'addPR_CGST',
                tooltip: 'CGST',
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                width: 75

            }, {
                header: 'SGST',
                sortable: true,
                dataIndex: 'addPR_SGST',
                tooltip: 'SGST',
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                width: 75

            }, {
                header: 'Amount',
                sortable: true,
                dataIndex: 'addPR_Amount',
                tooltip: 'Amount',
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                width: 75

            },
            {
                xtype: 'actioncolumn',
                width: 50,
                hideable: false,
                id: 'purchase_show_action',
                items: [{
                        sortable: false,
                        getClass: function (v, meta, rec) {

                            if (rec.get('itemId') != 0 && rec.get('prab_IsActive') > 0) {
                                this.items[0].tooltip = 'Show Associcated Purchase Returnables';
                                return 'finascop_purchase_show';
                            } else {
                                return 'finascop_hideicon';
                            }
                        },
                        handler: function (grid, rowIndex, colIndex) {

                            var rec = grid.getStore().getAt(rowIndex);
                            var isActive = rec.get('prab_IsActive');
                            var itemId = rec.get('itemId');
                            var purchaseId = rec.get('purchaseId');
                            var checkedIds = rec.get('checkedIds');
                            if (isActive > 0) {
                                showPurchaseReturnable(itemId, purchaseId, rec, show, checkedIds);
                            } else {
                                Ext.MessageBox.alert("Notification", "No Active Purchase Returnables");
                            }
                        }
                    }]
            }
        ]);
        return colsModel_maingrid;
    }

    var add_purchasereturn = function (returnedQty, rec) {

        var itemQty = rec.get('addPR_ItemQty');
        var rate = rec.get('addPR_Rate');
        if (returnedQty <= itemQty) {

            var amnt = Number(returnedQty) * Number(rate);
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'getPurchaseReturnItemdetails',
                    item_id: rec.get('itemId'),
                    partyId: rec.get('partyId'),
                    party_state_id: rec.get('state_id')
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    var tmp = response.responseText;
                    tmp = Ext.decode(tmp);
                    if (tmp.success === true) {

                        var igst = Ext.util.Format.round((amnt * (tmp.data.IGST)), 2);
                        var cgst = Ext.util.Format.round((amnt * (tmp.data.CGST)), 2);
                        var sgst = Ext.util.Format.round((amnt * (tmp.data.SGST)), 2);
                        var amount = Ext.util.Format.round((amnt + igst + cgst + sgst), 2);
                        rec.set("addPR_IGST", igst);
                        rec.set("addPR_CGST", cgst);
                        rec.set("addPR_SGST", sgst);
                        rec.set("addPR_Amount", amount);
                        purchaseReturnTotalGridCalculation();
                    } else
                    {
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                }
            });
        } else {
            Ext.MessageBox.alert("Notification", "Can not return this much item.");
            Ext.getCmp('purchase_return_grid').getStore().rejectChanges();
            //Ext.getCmp('returnedQty').clearValue();
        }
        return true;
    }

    var colsModel_totalgrid = function () {
        var colsModel_totalgrid = new Ext.grid.ColumnModel([
            {
                header: 'Item',
                sortable: true,
                dataIndex: 'addPR_Item',
                tooltip: 'Item Name',
                width: 100
            }, {
                header: 'Purchased Item Qty',
                sortable: true,
                align: 'right',
                dataIndex: 'addPR_ItemQty',
                tooltip: 'Purchased Item Quantity',
                width: 115
            }, {
                header: 'Return Quantity',
                sortable: true,
                editable: false,
                dataIndex: 'addPR_ReturnQty',
                tooltip: 'Return Quantity',
                align: 'right',
                width: 100
            }, {
                header: 'Rate',
                sortable: true,
                align: 'right',
                dataIndex: 'addPR_Rate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                width: 75

            },
            {
                header: 'IGST',
                sortable: true,
                dataIndex: 'addPR_IGST',
                tooltip: 'IGST',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                width: 75

            }, {
                header: 'CGST',
                sortable: true,
                dataIndex: 'addPR_CGST',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'CGST',
                align: 'right',
                width: 75

            }, {
                header: 'SGST',
                sortable: true,
                dataIndex: 'addPR_SGST',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'SGST',
                align: 'right',
                width: 75

            }, {
                header: 'Amount',
                sortable: true,
                dataIndex: 'addPR_Amount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount',
                align: 'right',
                width: 75

            }, {
                header: 'action',
                sortable: false,
                dataIndex: 'noaction',
                tooltip: '',
                width: 50

            }
        ]);
        return colsModel_totalgrid;
    }



    var showPurchaseReturnable = function (itemId, purchaseId, rec, show, checkedIds) {

        var purchaseReturnableWindow = Ext.getCmp('purchaseReturnShow_window');
        if (Ext.isEmpty(purchaseReturnableWindow)) {
            var purchaseReturnableWindow = new Ext.Window({
                id: 'purchaseReturnShow_window',
                title: 'Associated Purchase Returnables',
                //iconCls: 'finascop_purchase_show',
                modal: true,
                layout: 'fit',
                width: 500,
                autoHeight: true,
                closable: false,
                shadow: false,
                resizable: false,
                items: [assocPurchaseReturnableGrid(itemId, purchaseId, show, checkedIds)],
                buttons: [{
                        text: 'ADD',
                        id: 'btn_addPurchaseReutrnable',
                        iconCls: 'finascop_add',
                        disabled: btn_disable,
                        handler: function () {

                            var pr = Ext.getCmp('purchaseReturnShow_grid').getSelectionModel().getSelections();
                            var returnedQty = 0;
                            var comma = "";
                            var purchaseReturnableId = "";
                            if (!Ext.isEmpty(pr)) {

                                var count = pr.length;
                                for (var i = 0; i < count; i++) {

                                    returnedQty += pr[i].get('quantityReturned');
                                    purchaseReturnableId += (comma + pr[i].get('purchaseReturnableId'));
                                    comma = ',';
                                }

                                rec.set('addPR_ReturnQty', returnedQty);
                                rec.set('checkedIds', purchaseReturnableId);
                                Ext.getCmp('purchase_return_grid').getStore().commitChanges();
                            }

                            purchaseReturnableWindow.close();
                        }

                    },
                    {
                        text: 'CANCEL',
                        id: 'close_addPurchaseReutrnable',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {

                            purchaseReturnableWindow.close();
                        }
                    }]
            });
        }
        if (show === false) {
            Ext.getCmp('btn_addPurchaseReutrnable').setVisible(false);
            var btn_disable = true;
            Ext.getCmp('close_addPurchaseReutrnable').setText('CLOSE');
        } else {
            btn_disable = false;
        }


        purchaseReturnableWindow.show();
        purchaseReturnableWindow.doLayout();
        purchaseReturnableWindow.center();
    }

    var assocPurchaseReturnableGrid = function (itemId, purchaseId, show, checkedIds) {

        if (show == false) {
            var view = 1;
        } else {
            view = 0;
        }

        var purchaseReturnable_Store = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=listpurchaseReturnablegrid',
                fields: ['itemId', 'refid', 'purchaseReturnableId', 'purchaseReturnableNo',
                    {name: 'quantityReturned', type: 'number'}, 'purchaseReturnableItem', 'purchaseType'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                listeners: {
                    beforeload: function (store, opt) {
                        store.baseParams = {
                            itemID: itemId,
                            purchaseId: purchaseId,
                            view: view
                        };
                    }
                }
            });
            return store;
        };
        var showGridStore = purchaseReturnable_Store();
        var check_box = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true
        });
        var purchaseReturn_show_panel = new Ext.grid.GridPanel({
            store: showGridStore,
            layout: 'fit',
            frame: false,
            border: false,
            title: '',
            autoHeight: true,
            style: 'margin-top:2px;',
            id: 'purchaseReturnShow_grid',
            loadMask: true,
            autoScroll: true,
            hideHeaders: false,
            sm: check_box,
            columns: [
                check_box,
                {
                    header: 'PR No.',
                    sortable: true,
                    dataIndex: 'purchaseReturnableNo',
                    tooltip: 'Purchase Returnable Number',
                    width: 80

                }, {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'purchaseReturnableItem',
                    tooltip: 'Item',
                    width: 135

                }, {
                    header: 'Quantity Returned',
                    sortable: true,
                    dataIndex: 'quantityReturned',
                    tooltip: 'Quantity Returned',
                    width: 99

                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'purchaseType',
                    tooltip: 'Type',
                    width: 130
                }
            ],
            stripeRows: true
//            
        });
        showGridStore.load({
            callback: function (records, options, success) {

                Ext.each(records, function (record, index, items) {

                    var purchaseReturnableId = record.data.purchaseReturnableId;
                    if (checkedIds.indexOf(purchaseReturnableId) > -1)
                    {
                        check_box.selectRow(index, true);
                    }
                });
                if (show == false) {
                    check_box.lock();
                }

            }
        });
        return purchaseReturn_show_panel;
    };
    //total amount calculation for purchase return-->
    var purchaseReturnTotalGrid = function () {

        var TotalAmount_PurchaseReturn_Store = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                fields: ['itemId', {name: 'addPR_ReturnQty', type: 'number'}, {name: 'addPR_IGST', type: 'float'},
                    {name: 'addPR_CGST', type: 'float'},
                    {name: 'addPR_SGST', type: 'float'},
                    {name: 'addPR_Amount', type: 'float'}]
            });
            return store;
        };
        var totalGridStore = TotalAmount_PurchaseReturn_Store();
        var purchaseReturn_total_panel = new Ext.grid.GridPanel({
            store: totalGridStore,
            layout: 'fit',
            forceFit: true,
            title: '',
            style: 'margin-top:2px;',
            id: 'purchasereturnTotal',
            loadMask: true,
            autoScroll: true,
            hideHeaders: true,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            },
            cm: colsModel_totalgrid(),
            stripeRows: true
        });
        return purchaseReturn_total_panel;
    };
    var purchaseReturn_amount_panel = function () {
        var panel = new Ext.form.FormPanel({
            //autoHeight: true,
            height: 60,
            frame: true,
            border: false,
            id: 'purchase_return_amount_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.6,
                            border: false,
                            labelWidth: 75,
                            style: 'margin-top:5px;',
                            items: [{
                                    xtype: 'hidden',
                                    fieldLabel: 'netamount',
                                    id: 'netamount',
                                    name: 'netamount',
                                    editable: true,
                                    anchor: '90%'

                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.4,
                            border: false,
                            labelWidth: 75,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'Net Amount',
                                    style: {'text-align': 'right'},
                                    xtype: 'numberfield',
                                    id: 'purchaseReturn_netAmount',
                                    name: 'purchaseReturn_netAmount',
                                    anchor: '88%',
                                    readOnly: 'true',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    editable: false,
                                    //align: 'right',
                                    allowBlank: false
                                }]
                        }
                    ]
                }]
        })
        return panel;
    }



    var savePurchaseReturnData = function (rec) {
        var purchase_returngrid = Ext.getCmp('purchase_return_grid');
        var purchase_return_total_grid = Ext.getCmp('purchasereturnTotal');
        var purchase_returngrid_store = purchase_returngrid.getStore();
        var purchase_returngrid_total_gridstore = purchase_return_total_grid.getStore();
        var purchase_return_gridData = Ext.pluck(purchase_returngrid_store.getRange(), 'data');
        var purchase_return_total_gridData = Ext.pluck(purchase_returngrid_total_gridstore.getRange(), 'data');
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(Ext.getCmp('purchaseReturn_netAmount').getValue())) {

            var form_data = {
                invoice_no: rec.get('puRe_InvNo'),
                puen_updated_on: rec.get('puen_updated_on'),
                invoice_date: rec.get('puRe_InvDate'),
                puen_Id: rec.get('invID'),
                net_amount: Ext.getCmp('purchaseReturn_netAmount').getValue(),
                br_id: rec.get('br_id'),
                purchase_return_gridData: Ext.encode(purchase_return_gridData),
                purchase_return_total_gridData: Ext.encode(purchase_return_total_gridData),
                apikey: _SESSION.apikey,
                tstamp: t_stamp
            };
            var params = {
                action: 'Insert',
                module: 'finascop_purchase',
                op: 'savePurchaseReturned',
                extrainfo: 'fsr',
                id: '1'
            };
            APICall(params, function () {
                return  Application.Finascop_Purchase.savePurchaseReturned(rec);
            }, form_data);
        } else {
            Ext.MessageBox.alert('Error', 'Check the required fields');
        }
    };
   
    

    return{
        
        initPurchase: function () {
            var panelId = 'purchase_entries_panel';
            var purchase_entries_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchase_entries_panel)) {
                purchase_entries_panel = purchaseMasterPanel(panelId);
                Application.UI.addTab(purchase_entries_panel);
                purchase_entries_panel.doLayout();
            } else {
                Application.UI.addTab(purchase_entries_panel);
                purchase_entries_panel.doLayout();
            }

        },

        //save purchase invoice data//
        savePurchaseInvoiceData: function () {
            var purchaseInvoice_window = Ext.getCmp('purchaseInvoice_window');
            var grid = Ext.getCmp('pu_itemgrid');
            var grid_store = grid.getStore();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var data = Ext.pluck(grid_store.getRange(), 'data');
            //invoice_form.getForm().submit({
            Ext.Ajax.request({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                url: modURL,
                params: {
                    op: 'savePurchaseInvoices',
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    invoiceItem_data: Ext.encode(data),
                    party_id: Ext.getCmp('pu_party').getValue(),
                    party: Ext.getCmp('pu_party').getRawValue().toUpperCase(),
                    purchcase_order_no: Ext.getCmp('pu_order_no').getValue(),
                    ref_no: Ext.getCmp('pu_ref_no').getValue(),
                    grossAmount: Ext.getCmp('pu_grossAmount').getValue(),
                    purchaseOrderDate: Ext.util.Format.date(Ext.getCmp('pu_order_date').getValue(), 'd/m/Y'),
                    invoiceDate: Ext.util.Format.date(Ext.getCmp('pu_inv_date').getValue(), 'd/m/Y'),
                    discount: Ext.getCmp('pu_discount').getValue(),
                    netAmount: Ext.getCmp('pu_netAmount').getValue(),
                    signature: Ext.getCmp('pu_signature').getValue(),
                    totalItems: Ext.getCmp('pu_totalItems').getValue(),
                    totalItemQty: Ext.getCmp('pu_totalItemQty').getValue(),
                    tax: Ext.getCmp('pu_tax').getValue()

                },
                success: function (response) {


                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        excludeItemIds = [];
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp('pu_master_grid').getStore().reload();
                        purchaseInvoice_window.close();
                        loadPurchaseInvoice();
                    } else {
                        Ext.MessageBox.alert("Notification", tmp.error);
                        Ext.getCmp('pu_master_grid').getStore().reload();
                        purchaseInvoice_window.close();
                    }

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        },
        //save purchase invoice data ends//

        //purchase invoice edit data
        savePurchaseInvoiceEditedData: function () {
            var invoiceeditform = Ext.getCmp('pu_invoiceeditform');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=saveEditedData',
                method: 'POST',
                params: {
                    puen_Id: Ext.getCmp('puen_Id').getValue(),
                    party: Ext.getCmp('pu_editSlider_partyName').getRawValue().toUpperCase(),
                    purchase_orderno: Ext.getCmp('pu_editSlider_puOrderno').getValue(),
                    purchase_orderdate: Ext.util.Format.date(Ext.getCmp('pu_editSlider_puOrderdate').getValue(), 'd/m/Y'),
                    invoice_date: Ext.util.Format.date(Ext.getCmp('pu_editSlider_invoiceDate').getValue(), 'd/m/Y'),
                    referenceno: Ext.getCmp('pu_editSlider_refNo').getValue(),
                    invoiceno: Ext.getCmp('pu_editSlider_invNo').getValue(),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp

                },
                success: function (response) {


                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp('pu_invoiceeditform').setVisible(false);
                        Ext.getCmp('pu_details_view_panel').setVisible(true);
                        Ext.getCmp('pu_master_grid').getStore().reload();
                        var visualsDescPanel = Ext.getCmp('pu_details_view_panel');
                        visualsDescPanel.update(tmp.data);
                    } else {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                    }

                },
//               

                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        },

        //purchase returnable starts
        initPurchaseReturnable: function () {
            var panelId = 'purchase_returnable_panel';
            var purchase_returnable_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchase_returnable_panel)) {
                purchase_returnable_panel = PurchaseReturnableGrid(panelId);
                Application.UI.addTab(purchase_returnable_panel);
                purchase_returnable_panel.doLayout();
            } else {
                Application.UI.addTab(purchase_returnable_panel);
                purchase_returnable_panel.doLayout();
            }
        },

        //save purchase returnable modify data//
        saveModifyData: function () {
            var purchaseReturnable_Modifywindow = Ext.getCmp('purchaseReturnableModify_window');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                url: modURL,
                params: {
                    op: 'savePurchaseReturnable_ModifyData',
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    mod_Date: Ext.getCmp('modify_datefield').getValue(),
                    mod_ItemName: Ext.getCmp('modify_itemName').getValue(),
                    mod_ReturnableQty: Ext.getCmp('modify_ReturnableQty').getValue(),
                    mod_ToReturn: Ext.getCmp('modify_toReturn').getValue(),
                    refId: Ext.getCmp('refId').getValue(),
                    previous_key: Ext.getCmp('update_id').getValue(),
                    invoice_no: Ext.getCmp('inv_number').getValue(),
                    itemId: Ext.getCmp('itemId').getValue(),
                },
                success: function (response) {


                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp('purchase_returnable_window_grid').getStore().reload();
                        Ext.getCmp('purchase_returnable_master_grid').getStore().reload();
                        purchaseReturnable_Modifywindow.close();
                        loadPurchaseReturnable();
                    } else {
                        Ext.MessageBox.alert("Notification", tmp.errors, function () {
                            purchaseReturnable_Modifywindow.close();
                        });

                    }

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.errors);
                }
            });
        },
        //save purchase returnable modify data ends//

        //purchase return starts
        initPurchaseReturn: function () {
            var panelId = 'purchase_return_panel';
            var purchase_return_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchase_return_panel)) {
                purchase_return_panel = PurchaseReturnGrid(panelId);
                Application.UI.addTab(purchase_return_panel);
                purchase_return_panel.doLayout();
            } else {
                Application.UI.addTab(purchase_return_panel);
                purchase_return_panel.doLayout();
            }
            //getBranchStateId();
        },
        savePurchaseReturned: function (rec) {

            var purchase_returngrid = Ext.getCmp('purchase_return_grid');
            var purchase_return_total_grid = Ext.getCmp('purchasereturnTotal');
            var purchase_returngrid_store = purchase_returngrid.getStore();
            var purchase_returngrid_total_gridstore = purchase_return_total_grid.getStore();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var purchase_return_gridData = Ext.pluck(purchase_returngrid_store.getRange(), 'data');
            var purchase_return_total_gridData = Ext.pluck(purchase_returngrid_total_gridstore.getRange(), 'data');
            Ext.Ajax.request({
                url: modURL + '&op=savePurchaseReturned',
                method: 'POST',
                params: {
                    invoice_no: rec.get('puRe_InvNo'),
                    puen_updated_on: rec.get('puen_updated_on'),
                    invoice_date: rec.get('puRe_InvDate'),
                    puen_Id: rec.get('invID'),
                    net_amount: Ext.getCmp('purchaseReturn_netAmount').getValue(),
                    br_id: rec.get('br_id'),
                    purchase_return_gridData: Ext.encode(purchase_return_gridData),
                    purchase_return_total_gridData: Ext.encode(purchase_return_total_gridData),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp

                },
                success: function (response) {


                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp('purchase_return_master_grid').getStore().load();
                        Ext.getCmp('purchase_return_window_grid').getStore().load();
                        Ext.getCmp('purchase_return_window_grid').getView().refresh();
                        Ext.getCmp('addOrViewPurchaseReturn_window').close();
                        loadPurchaseReturn();
                    } else {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                    }

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        }


    };
}();
