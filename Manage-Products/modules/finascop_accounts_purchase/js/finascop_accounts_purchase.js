/* global Application, Ext */

Application.Finascop_Accounts_Purchase = function () {

    var modURL = '?module=finascop_accounts_purchase';
    var pomodURL = '?module=finascop_purchase_order';
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 12;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    ;


    var purchaseEntryStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPurchaseEntries',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpe_id',
                root: 'data'
            }, ['fpe_id', 'fpe_vendorName', 'fpe_fpoPoNumber', 'fpe_fpoPODate',
                'fpe_invoiceNumber', 'fpe_invoiceDate', {name: 'fpe_grossAmt', type: 'float'},
                {name: 'fpe_discount', type: 'float'}, {name: 'fpe_netQty', type: 'int'}, {name: 'fpe_netItems', type: 'int'},
                {name: 'fpe_netTax', type: 'float'}, {name: 'fpe_netAmount', type: 'float'},
                {name: 'fpe_netIgst', type: 'float'}, {name: 'fpe_netCgst', type: 'float'},
                {name: 'fpe_netSgst', type: 'float'}]),
            sortInfo: {
                field: 'fpe_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: true,
            root: 'data',
            remoteSort: false
        });
        store.setDefaultSort('vendor_name', 'ASC');
        return store;
    };
    var PurchaseEntryGrid = function (id) {

        var centre_store = purchaseEntryStore();
        var centre_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fpe_vendorName'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_fpoPoNumber'
                }, {
                    type: 'date',
                    dataIndex: 'fpe_invoiceDate'
                }, {
                    type: 'date',
                    dataIndex: 'fpe_fpoPODate'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_netAmount'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_netIgst'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_netCgst'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_netSgst'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_invoiceNumber'
                }]
        });
        centre_filter.remote = true;
        centre_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: centre_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            title: '',
            iconCls: 'finascop_approval',
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), centre_filter],
            id: 'purchaseentriGridPanel',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Vendor',
                    id: 'vendor_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpe_vendorName',
                    tooltip: 'Vendor Name'
                }, {
                    header: 'PO No:',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpe_fpoPoNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'Invoice No:',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpe_invoiceNumber',
                    tooltip: 'Invoice Number'
                }, {
                    header: 'Invoice Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpe_invoiceDate',
                    tooltip: 'Invoice Date'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpe_fpoPODate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Net Amt.',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'fpe_netAmount',
                    tooltip: 'Net Amt.'
                }, {
                    header: 'IGST',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'fpe_netIgst',
                    tooltip: 'IGST'
                }, {
                    header: 'CGST',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'fpe_netCgst',
                    tooltip: 'CGST'
                }, {
                    header: 'SGST',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'fpe_netSgst',
                    tooltip: 'SGST'
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
                            purchaseentryActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 header: 'Actions',
                 hideable: false,
                 groupable: false,
                 width: 80,
                 items: [
                 {
                 iconCls: 'my-icon18',
                 tooltip: 'View Purchase Entry',
                 handler: function (grid, rowIndex, colIndex) {
                 var grecord = grid.store.getAt(rowIndex);
                 console.log('grecord', grecord);
                 viewPurchaseInvoice(grecord.data.fpe_id);
                 
                 }
                 }
                 ]
                 
                 }*/
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Purchase Entry',
                    tooltip: 'Create Purchase Entry',
                    iconCls: 'add',
                    handler: function () {
                        addPurchaseInvoice(0);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: centre_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'vendor_name_auto_exp'
        });
        return grid_panel;
    };
    var purchaseentryActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View",
                handler: function () {
                    var fpe_id = Ext.getCmp('purchaseentriGridPanel').getSelectionModel().getSelections()[0].data.fpe_id;
                    viewPurchaseInvoice(fpe_id);
                }
            }]
    });

    var viewPurchaseInvoice_panel = function () {
        Application.Finascop_Accounts_Purchase.Cache.poMsg = '';
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'purchase_entry_form',
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
                                    id: 'pe_cb',
                                    name: 'cb1',
                                    labelSeparator: '',
                                    hidden: true,
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
                                                Ext.getCmp('pe_party').focus();
                                            }
                                            if (e.getKey() == e.UP) {
                                                Ext.getCmp('pe_cb').setValue(true);
                                            }
                                            if (e.getKey() == e.DOWN) {
                                                Ext.getCmp('pe_cb').setValue(false);
                                            }
                                        },
                                        check: function (cb1, checked) {
                                            excludeItemIds = [];
                                            Ext.getCmp('pe_order_no').reset();
                                            Ext.getCmp('pe_order_date').reset();
                                            Ext.getCmp('pe_ref_no').reset();
                                            Ext.getCmp('pe_inv_date').reset();
                                            Ext.getCmp('pe_itemgrid').getStore().removeAll(true);
                                            Ext.getCmp('pe_itemgrid').getView().refresh();
                                            Ext.getCmp('pe_itemgridTotal').getStore().removeAll(true);
                                            Ext.getCmp('pe_itemgridTotal').getView().refresh();
                                            Ext.getCmp('pe_grossAmount').reset();
                                            chkd = checked;
                                            if (chkd == false) {
                                                var newLabel = 'Paid To:';
                                                Ext.getCmp('pe_party').setValue('');
                                                if (!Ext.getCmp('pe_party').rendered)
                                                    Ext.getCmp('pe_party').fieldLabel = newLabel;
                                                else
                                                    Ext.getCmp('pe_party').label.update(newLabel);
                                            } else
                                            {
                                                var newLabel = 'Party:';
                                                if (!Ext.getCmp('pe_party').rendered)
                                                    Ext.getCmp('pe_party').fieldLabel = newLabel;
                                                else
                                                    Ext.getCmp('pe_party').label.update(newLabel);
                                            }
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [{xtype: 'hidden', id: 'pe_PO_ID', name: 'pe_PO_ID'}, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Party',
                                    id: 'pe_party',
                                    name: 'pe_party',
                                    labelStyle: mandatory_label,
                                    allowBlank: true,
                                    labelAlign: 'top',
                                    anchor: '99%',
                                    readOnly: true,
                                    listeners: {
                                        focus: function (qw) {
                                            VendorSelecttionWindow('Vendor PO', 'VendorPOForPI');
                                        },
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);
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
                                    id: 'pe_order_no',
                                    name: 'purchcase_order_no',
                                    allowBlank: false,
                                    readOnly: true,
                                    tabIndex: 89,
                                    labelAlign: 'top',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pe_order_date').focus();
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
                                    readOnly: true,
                                    id: 'pe_order_date',
                                    name: 'purchase_order_date',
                                    allowBlank: false,
                                    tabIndex: 90,
                                    anchor: '99%',
                                    format: 'd-m-Y',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pe_ref_no').focus();
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
                                    fieldLabel: 'Invoice No',
                                    allowBlank: false,
                                    id: 'pe_ref_no',
                                    name: 'ref_no',
                                    anchor: '99%',
                                    tabIndex: 91
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
                                    id: 'pe_inv_date',
                                    name: 'inv_date',
                                    anchor: '99%',
                                    format: 'd-m-Y',
                                    maxValue: new Date(),
                                    tabIndex: 92
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
                                    items: [invoiceItemGrid()]
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
                            id: 'pe_totalItemQty',
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
                            id: 'pe_tax',
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
                            id: 'pe_totalItems',
                            name: 'totalItems',
                            anchor: '99%',
                            hideLabel: true
                        }, {
                            xtype: 'hidden',
                            fieldLabel: 'Total IGST',
                            id: 'pe_taxIgst',
                            name: 'taxIgst',
                            anchor: '99%',
                            hideLabel: true
                        }, {
                            xtype: 'hidden',
                            fieldLabel: 'Total CGST',
                            id: 'pe_taxCgst',
                            name: 'taxCgst',
                            anchor: '99%',
                            hideLabel: true
                        }, {
                            xtype: 'hidden',
                            fieldLabel: 'Total SGST',
                            id: 'pe_taxSgst',
                            name: 'taxSgst',
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
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Sub Total',
                                    allowBlank: false,
                                    id: 'pe_subTotal',
                                    autoStripChars: true,
                                    name: 'pe_subTotal',
                                    minValue: 0.05,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {
                                            var itemStore = Ext.getCmp('pe_itemgrid').getStore();
                                            var grossAmt = itemStore.sum('amtAfTax');
                                            //var amtBfTax = itemStore.sum('amtBfTax');
                                            //var genDiscount = amtBfTax * Application.Finascop_Accounts_Purchase.Cache.gendiscount / 100;
                                            //genDiscount = Math.round((genDiscount + Number.EPSILON) * 100) / 100;


                                            var totalIgst = itemStore.sum('itemIgst');
                                            var totalCgst = itemStore.sum('itemCgst');
                                            var totalSgst = itemStore.sum('itemSgst');
                                            var totalTax = totalIgst + totalCgst + totalSgst;
                                            var totaQty = itemStore.sum('fpod_invoiceqty');
                                            var totalItems = itemStore.getCount();
                                            Ext.getCmp('pe_subTotal').setValue(grossAmt);
                                            Ext.getCmp('pe_tax').setValue(totalTax);
                                            Ext.getCmp('pe_taxIgst').setValue(totalIgst);
                                            Ext.getCmp('pe_taxCgst').setValue(totalCgst);
                                            Ext.getCmp('pe_taxSgst').setValue(totalSgst);
                                            Ext.getCmp('pe_totalItemQty').setValue(totaQty);
                                            Ext.getCmp('pe_totalItems').setValue(totalItems);

                                            Ext.getCmp('pe_genDiscount').setValue(0);
                                            Ext.Ajax.request({
                                                url: modURL + '&op=purchaseEntryAdded',
                                                method: 'POST',
                                                params: {
                                                    poid: Application.Finascop_Accounts_Purchase.Cache.POID

                                                },
                                                success: function (response) {
                                                    eval("var tmp=" + response.responseText);
                                                    if (tmp.success === true) {
                                                        var inclHC = parseFloat(Application.Finascop_Accounts_Purchase.Cache.shipping) - parseFloat(Application.Finascop_Accounts_Purchase.Cache.handlinchgGstVal);
                                                        var maxHC = parseFloat(inclHC) - parseFloat(tmp.data);
                                                        var maxHCgst = parseFloat(Application.Finascop_Accounts_Purchase.Cache.handlinchgGstVal) - parseFloat(tmp.fpe_handlingChargeGst);
                                                        Ext.getCmp('pe_handlingCharge').setValue(maxHC);
                                                        Ext.getCmp('pe_handlingChargeGst').setValue(maxHCgst);
                                                        Application.Finascop_Accounts_Purchase.Cache.enteredGrossAmount = tmp.groamt;
                                                        Ext.getCmp('pe_netAmount').focus();
                                                    }
                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });

                                            txtbx.selectText();
                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {

                                                Ext.getCmp('pe_genDiscount').focus();
                                            }
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Addi. Disc.',
                                    //allowBlank: false,
                                    id: 'pe_genDiscount',
                                    autoStripChars: true,
                                    name: 'pe_genDiscount',
                                    minValue: 0.00,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    hidden: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {

                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Handling Charges',
                                    allowBlank: false,
                                    id: 'pe_handlingCharge',
                                    autoStripChars: true,
                                    name: 'pe_handlingCharge',
                                    minValue: 0.00,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    anchor: '99%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Handling Charge GST / VAT',
                                    allowBlank: false,
                                    id: 'pe_handlingChargeGst',
                                    autoStripChars: true,
                                    name: 'pe_handlingChargeGst',
                                    minValue: 0.00,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    anchor: '99%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Amount',
                                    allowBlank: false,
                                    id: 'pe_grossAmount',
                                    autoStripChars: true,
                                    name: 'grossAmount',
                                    minValue: 0.05,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {

                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pe_netAmount').focus();
                                            }
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    allowBlank: false,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Invoice Amount',
                                    id: 'pe_netAmount',
                                    name: 'netAmount',
                                    anchor: '99%',
                                    autoStripChars: true,
                                    enableKeyEvents: true,
                                    listeners: {
                                        focus: function (txtbx) {
                                            var subtotal = Ext.getCmp('pe_subTotal').getValue();
                                            var hchg = Ext.getCmp('pe_handlingCharge').getValue();
                                            var hchgGST = Ext.getCmp('pe_handlingChargeGst').getValue();
                                            var gendis = Ext.getCmp('pe_genDiscount').getValue();
                                            //var grossAmt = parseFloat(subtotal) + parseFloat(hchg) + parseFloat(hchgGST) - parseFloat(gendis);
                                            var grossAmt = parseFloat(subtotal) + parseFloat(hchg) + parseFloat(hchgGST);

                                            var maxGrossAmt = parseFloat(Application.Finascop_Accounts_Purchase.Cache.fpo_poValue) - parseFloat(Application.Finascop_Accounts_Purchase.Cache.enteredGrossAmount);
                                            if (parseFloat(grossAmt) > parseFloat(maxGrossAmt)) {
                                                var changedGenDis = (parseFloat(subtotal) + parseFloat(hchg) + parseFloat(hchgGST)) - parseFloat(maxGrossAmt);
                                                console.log('changedGenDis', changedGenDis);
                                                Ext.getCmp('pe_genDiscount').setValue(changedGenDis);

                                                var grossAmt = parseFloat(subtotal) + parseFloat(hchg) + parseFloat(hchgGST) - parseFloat(changedGenDis);
                                                //Ext.Msg.alert('Notification', 'Amount should not be greater than PO Value..');
                                                Application.Finascop_Accounts_Purchase.Cache.poMsg = 'Amount should not be greater than PO Value';
                                            } else {
                                                console.log('smaller');
                                                Application.Finascop_Accounts_Purchase.Cache.poMsg = '';
                                            }
                                            Ext.getCmp('pe_grossAmount').setValue(grossAmt);
                                            Ext.getCmp('pe_netAmount').setValue(grossAmt);
                                            txtbx.selectText();
                                        },
                                        change: function ()
                                        {
                                            var grossamount = Ext.getCmp('pe_grossAmount').getValue();
                                            var invAmount = Ext.getCmp('pe_netAmount').getValue();
                                            var discount = (invAmount - grossamount);
                                            Ext.getCmp('pe_discount').setValue(discount);
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    fieldLabel: 'Round Off',
                                    autoStripChars: true,
                                    id: 'pe_discount',
                                    name: 'discount',
                                    style: {'text-align': 'right'},
                                    //allowNegative: false,
                                    anchor: '99%',
                                    value: '0.00',
                                    readOnly: true,
                                    tabIndex: 94,
                                    allowDecimals: true,
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            xtype: 'label',
                            value: '&nbsp;' + Application.Finascop_Accounts_Purchase.Cache.poMsg
                        }
                    ]
                }]
        });
        return panel;
    };
    var viewPurchaseEntry_panel = function (id) {

        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'purchase_view_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Party',
                                    name: 'fpe_vendorName',
                                    labelAlign: 'top',
                                    anchor: '99%',
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.20,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Purchase Order No',
                                    name: 'fpe_fpoPoNumber',
                                    labelAlign: 'top',
                                    anchor: '99%'
                                }
                            ]

                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Purchase Order Date',
                                    name: 'fpe_fpoPODate',
                                    anchor: '99%',
                                    format: 'd-m-Y'
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Invoice No',
                                    allowBlank: false,
                                    name: 'fpe_invoiceNumber',
                                    anchor: '99%'
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Invoice Date',
                                    name: 'fpe_invoiceDate',
                                    format: 'd-m-Y'
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
                                    items: [invoiceItemViewGrid(id)]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Sub Total',
                                    autoStripChars: true,
                                    name: 'fpe_subTotal',
                                    minValue: 0.05,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    readOnly: true,
                                    anchor: '99%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Addi. Disc.',
                                    autoStripChars: true,
                                    name: 'fpe_genDiscount',
                                    minValue: 0.00,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    anchor: '99%',
                                    hidden: true
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Handling Charges',
                                    autoStripChars: true,
                                    name: 'fpe_handlingCharge',
                                    minValue: 0.00,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    anchor: '99%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Amount',
                                    autoStripChars: true,
                                    name: 'fpe_grossAmt',
                                    minValue: 0.05,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    readOnly: true,
                                    anchor: '99%',
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    allowBlank: false,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Invoice Amount',
                                    name: 'fpe_netAmount',
                                    anchor: '99%',
                                    autoStripChars: true,
                                    enableKeyEvents: true,
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.70,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    fieldLabel: 'Round Off',
                                    autoStripChars: true,
                                    name: 'fpe_discount',
                                    style: {'text-align': 'right'},
                                    anchor: '99%',
                                    value: '0.00',
                                    readOnly: true,
                                    tabIndex: 94,
                                    allowDecimals: true,
                                }]
                        }]
                }]
        });
        return panel;
    };
    var viewPurchaseInvoice = function (id) {
        var title = 'View Purchase Entry';
        var purchase_entry_form = viewPurchaseEntry_panel(id);
        var viewPurchaseInvoice_window = Ext.getCmp('purchaseEntry_window');
        if (Ext.isEmpty(viewPurchaseInvoice_window)) {
            var viewPurchaseInvoice_window = new Ext.Window({
                id: 'viewpurchaseEntry_window',
                title: title,
                iconCls: 'finascop_invoice',
                modal: true,
                layout: 'fit',
                width: 1100,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [purchase_entry_form],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        tabIndex: 96,
                        handler: function () {
                            excludeItemIds = [];
                            viewPurchaseInvoice_window.close();
                        }
                    }]
            });
        }


        if (!Ext.isEmpty(id)) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            purchase_entry_form.load({
                url: modURL + '&op=viewPurchaseEntryData',
                params: {
                    'fpe_id': id,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (frm, action) {
                    Ext.getCmp('pe_invoiceItemViewGrid').getStore().load({
                        params: {
                            fped_fpeId: id
                        }
                    });
                }
            });
        }

        viewPurchaseInvoice_window.show();
        viewPurchaseInvoice_window.doLayout();
        viewPurchaseInvoice_window.center();
    };
    var addPurchaseInvoice = function (id) {
        var title = (id == 0) ? 'Create Purchase Entry' : 'Edit Purchase Entry';
        var purchase_entry_form = viewPurchaseInvoice_panel();
        var addPurchaseInvoice_window = Ext.getCmp('purchaseEntry_window');
        if (Ext.isEmpty(addPurchaseInvoice_window)) {
            var addPurchaseInvoice_window = new Ext.Window({
                id: 'purchaseEntry_window',
                title: title,
                //iconCls: 'finascop_invoice',
                modal: true,
                layout: 'fit',
                width: 1100,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [purchase_entry_form],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        tabIndex: 97,
                        handler: function () {
                            excludeItemIds = [];
                            addPurchaseInvoice_window.close();
                        }
                    }, {
                        text: 'Save',
                        id: 'save_PInv_btn',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        tabIndex: 96,
                        handler: function () {
                            var grossAmt = Ext.getCmp('pe_grossAmount').getValue();
                            var maxGrossAmt = parseFloat(Application.Finascop_Accounts_Purchase.Cache.fpo_poValue) - parseFloat(Application.Finascop_Accounts_Purchase.Cache.enteredGrossAmount);
                            if (parseFloat(grossAmt) > parseFloat(maxGrossAmt)) {
                                Ext.Msg.alert('Notification', 'Amount should not be greater than PO Value..');
                            } else {
                                savePurchaseentries();
                            }

                        }
                    }]
            });
        }


        if (!Ext.isEmpty(id)) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            purchase_entry_form.load({
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

        addPurchaseInvoice_window.show();
        addPurchaseInvoice_window.doLayout();
        addPurchaseInvoice_window.center();
    };
    var savePurchaseentries = function () {
        var itemGriddata = getMigratedData('pe_itemgrid');
        var purchaseEntryForm = Ext.getCmp('purchase_entry_form').getForm();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (purchaseEntryForm.isValid()) {
            var form_data = {
                peItemSGriddata: JSON.parse(itemGriddata),
                form: Ext.getCmp('purchase_entry_form').getForm().getValues(),
                PoId: Application.Finascop_Accounts_Purchase.Cache.POID,
                VendorId: Application.Finascop_Accounts_Purchase.Cache.vendorId
            };
            var params = {
                action: 'Insert purchase entry',
                module: 'finascop_accounts_purchase',
                op: 'savePurchaseEntry',
                extrainfo: 'Save Purchase entries',
                id: Application.Finascop_Accounts_Purchase.Cache.POID
            };
            APICall(params, Application.Finascop_Accounts_Purchase.savePurchaseentries, form_data);
        } else {
            Application.example.msg('Error', 'Check the required fields');
        }
    };

    var purchaseEntryPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: id,
            title: 'Purchase Entry',
            //iconCls: 'finascop_itemmaster',
            items: [PurchaseEntryGrid()]
        });
        return panel;
    };

    var VendorSelecttionWindow = function () {
        var type = arguments[0];
        var unType = arguments[1];
        var dashboard_filter_form = dashboardFilterForm(type);
        var dashboardFilterWindow = new Ext.Window({
            id: 'dashboardFilterWindow',
            modal: true,
            constrain: true,
            width: 500,
            resizable: false,
            floating: true,
            title: type + ' Search',
            shadow: false,
            items: [dashboard_filter_form],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    tabIndex: 32,
                    handler: function () {
                        dashboardFilterWindow.close();
                    }
                },
                {
                    text: 'Select',
                    iconCls: 'add_visual',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                    tabIndex: 31,
                    width: 80,
                    handler: function () {

                        var data = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections();
                        if (data != '') {
                            var poId = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_id;
                            var vendorName = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_name;
                            var poNumber = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_poNumber;
                            var poDate = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_poDate;


                            Application.Finascop_Accounts_Purchase.Cache.POID = poId;
                            Application.Finascop_Accounts_Purchase.Cache.vendorId = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_vendorId;
                            Application.Finascop_Accounts_Purchase.Cache.shipping = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_shippingcharge;
                            Application.Finascop_Accounts_Purchase.Cache.handlinchgGstVal = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.handlinchgGstVal;
                            Application.Finascop_Accounts_Purchase.Cache.gendiscount = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_gdiscpercent;
                            Application.Finascop_Accounts_Purchase.Cache.fpo_poValue = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_poFinalValue;
                            Ext.getCmp('pe_PO_ID').setValue(poId);
                            Ext.getCmp('pe_party').setValue(vendorName);
                            Ext.getCmp('pe_order_no').setValue(poNumber);
                            Ext.getCmp('pe_order_date').setValue(poDate);

                            Ext.getCmp('dashboardFilterWindow').close();
                            Ext.getCmp('pe_itemgrid').getStore().load({
                                params: {
                                    poId: Application.Finascop_Accounts_Purchase.Cache.POID,
                                    vendorId: Application.Finascop_Accounts_Purchase.Cache.vendorId
                                }
                            })
                        }
                    }
                }
            ]

        });
        Ext.getCmp('search_field').focus(false, 100);
        var dashboardFilterWindow = Ext.getCmp('dashboardFilterWindow');
        dashboardFilterWindow.show();
        dashboardFilterWindow.doLayout();
        dashboardFilterWindow.center();
    };

    function dashboardFilterForm() {
        var type = arguments[0];
        var form = new Ext.form.FormPanel({
            labelWidth: 80,
            labelAlign: 'left',
            frame: false,
            border: false,
            hideBorders: true,
            modal: true,
            autoScroll: true,
            layout: 'column',
            items: [{
                    columnWidth: .9,
                    layout: 'form',
                    style: 'margin-top:15px',
                    bodyStyle: {"background-color": "white", "padding": "0px 25px"},
                    items: [
                        {
                            xtype: 'textfield',
                            //fieldLabel: 'Search',
                            id: 'search_field',
                            emptyText: 'Search Vendor',
                            name: 'n[search_field]',
                            hideLabel: true,
                            allowBlank: false,
                            anchor: '95%',
                            tabIndex: 6,
                            listeners: {
                                change: function () {
                                    var search_field = Ext.getCmp('search_field').getValue();

                                    Ext.getCmp('dash_search_grid').getStore().load({
                                        params: {
                                            search_field: search_field,
                                            type: type
                                        }
                                    });
                                }
                            }
                        }
                    ]}, {
                    columnWidth: .1,
                    layout: 'form',
                    style: 'margin-top:15px;margin-left:-5px;',
                    items: [{
                            xtype: 'button',
                            //style: 'margin-left:10px;',
                            text: 'Submit',
                            tabIndex: 8,
                            handler: function () {
                                var search_field = Ext.getCmp('search_field').getValue();

                                Ext.getCmp('dash_search_grid').getStore().load({
                                    params: {
                                        search_field: search_field,
                                        type: type
                                    }
                                });
                            }
                        }
                    ]
                },
                {
                    columnWidth: 1,
                    height: 300,
                    title: type + ' Data',
                    items: [dashboardSearchGrid(type)]
                }
            ]
        });
        return form;
    }
    ;
    var dashboardSearchGridStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=vendorPOSearchGridStore',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'search_id',
                root: 'data'
            }, ['search_name', 'search_id', 'fpo_poNumber', 'fpo_poDate', 'fpo_vendorId', 'fpo_gdiscpercent', 'fpo_shippingcharge', 'fpo_poValue', 'fpo_isInvoicedStatus', 'stockVerificationStatus', 'handlinchgGstVal', 'fpo_poFinalValue']),
            sortInfo: {
                field: 'search_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
        });
        return store;
    };
    var dashboardSearchGrid = function () {
        var type = arguments[0];
        var dashboardSearchStore = dashboardSearchGridStore();

        var grid = new Ext.grid.GridPanel({
            id: 'dash_search_grid',
            autoScroll: true,
            animCollapse: false,
            frame: false,
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpo_isInvoicedStatus') == 0)/*Nothing done */
                    {
                        return 'finascop_indicateColWHITE';
                    } else if (record.get('fpo_isInvoicedStatus') == 1) { /* Partial entryr   */
                        return 'finascop_indicateColPINK';
                    } else if (record.get('fpo_isInvoicedStatus') == 2) { /* completed    */
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            viewConfig: {
                forceFit: true, markDirty: false
            },
            stripeRows: true, layout: 'fit',
            border: false,
            hideBorders: true,
            store: dashboardSearchStore,
            height: 250,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'search_name',
                    tooltip: 'Vendor',
                    hideable: false
                }, {
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number',
                    hideable: false
                }, {
                    header: 'PO Date',
                    sortable: true,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date',
                    hideable: false
                }, {
                    header: 'Stock Status',
                    sortable: true,
                    dataIndex: 'stockVerificationStatus',
                    tooltip: 'Stock Status',
                    hideable: false
                }], listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex); // Get the Record
                },
                celldblclick: function () {
                    var data = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections();
                    if (data != '') {
                        var poId = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_id;
                        var vendorName = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_name;
                        var poNumber = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_poNumber;
                        var poDate = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_poDate;


                        Application.Finascop_Accounts_Purchase.Cache.POID = poId;
                        Application.Finascop_Accounts_Purchase.Cache.vendorId = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_vendorId;
                        Application.Finascop_Accounts_Purchase.Cache.shipping = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_shippingcharge;
                        Application.Finascop_Accounts_Purchase.Cache.handlinchgGstVal = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.handlinchgGstVal;
                        Application.Finascop_Accounts_Purchase.Cache.gendiscount = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_gdiscpercent;
                        Application.Finascop_Accounts_Purchase.Cache.fpo_poValue = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.fpo_poFinalValue;
                        Ext.getCmp('pe_PO_ID').setValue(poId);
                        Ext.getCmp('pe_party').setValue(vendorName);
                        Ext.getCmp('pe_order_no').setValue(poNumber);
                        Ext.getCmp('pe_order_date').setValue(poDate);

                        Ext.getCmp('dashboardFilterWindow').close();
                        Ext.getCmp('pe_itemgrid').getStore().load({
                            params: {
                                poId: Application.Finascop_Accounts_Purchase.Cache.POID,
                                vendorId: Application.Finascop_Accounts_Purchase.Cache.vendorId
                            }
                        })
                    }
                }
            }
        });
        return grid;
    };

    var upInvQty = function (record) {
        if (Ext.isEmpty(upInvQty_window)) {
            var upInvQty_window = new Ext.Window({
                id: 'upInvQty_window',
                layout: 'fit',
                width: 400,
                title: 'Update Quantity',
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
                        id: 'upInvQty_form',
                        height: 50,
                        items: [{
                                fieldLabel: 'Invoiced Quantity',
                                xtype: 'numberfield',
                                minValue: 0,
                                id: 'invoice_qty',
                                name: 'invoice_qty',
                                allowNegative: false,
                                allowDecimals: false,
                                tabIndex: 93,
                                allowBlank: false
                            }]
                    })],
                buttons: [{
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            upInvQty_window.close();
                        }
                    }, {
                        text: 'Update',
                        id: 'btnsave',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var ft = Ext.getCmp('invoice_qty').getValue();
                            var invQuantity = parseInt(ft);
                            if (ft <= (parseInt(record.data.fpod_itemqty) - parseInt(record.data.fpod_invoicedqty))) {
                                //var offrRate = record.data.fpod_itemoffrrate; changed on 02-01-2020
                                //var offrRate = record.data.fpod_itemoffrrateet;changed on 01-05-2020
                                var offrrateetch;
                                var gstTax = record.data.itemGST;
                                var igstTax = record.data.igst;
                                var sgstTax = record.data.sgst;
                                var cgstTax = record.data.cgst;
                                var itemoffrratech = record.data.fpod_itemoffrratech;
                                console.log('itemoffrratech', itemoffrratech);
                                if (itemoffrratech > 0) {
                                    offrrateetch = itemoffrratech * 100 / (100 + gstTax);
                                } else {
                                    itemoffrratech = record.data.fpod_netamount / record.data.fpod_itemqty;
                                    console.log('itemoffrratech else', itemoffrratech);
                                    offrrateetch = itemoffrratech * 100 / (100 + gstTax);
                                }
                                offrrateetch = Math.round((offrrateetch + Number.EPSILON) * 100) / 100;
                                var itemoffrrateetch = Math.round(record.data.fpod_itemoffrrateetch);
                                console.log('itemoffrrateetch', itemoffrrateetch);
                                console.log('gstTax', gstTax);
                                console.log('offrrateetch', offrrateetch);
                                var amtBfrTax = invQuantity * offrrateetch;
                                //var genDiscount = amtBfrTax * Application.Finascop_Accounts_Purchase.Cache.gendiscount / 100;
                                //genDiscount = Math.round((genDiscount + Number.EPSILON) * 100) / 100;
                                //amtBfrTax = amtBfrTax - genDiscount;
                                amtBfrTax = Math.round((amtBfrTax + Number.EPSILON) * 100) / 100;
                                var itemCgst = amtBfrTax * cgstTax / 100;
                                itemCgst = Math.round((itemCgst + Number.EPSILON) * 100) / 100;
                                var itemSgst = amtBfrTax * sgstTax / 100;
                                itemSgst = Math.round((itemSgst + Number.EPSILON) * 100) / 100;
                                var itemIgst = amtBfrTax * igstTax / 100;
                                itemIgst = Math.round((itemIgst + Number.EPSILON) * 100) / 100;
                                console.log('amtBfrTax', amtBfrTax);
                                console.log('itemCgst', itemCgst);
                                var amtAfrTax = amtBfrTax + itemIgst + itemSgst + itemCgst;
                                record.set('fpod_invoiceqty', ft);
                                record.set('amtBfTax', amtBfrTax);
                                record.set('amtAfTax', amtAfrTax);
                                record.set('itemCgst', itemCgst);
                                record.set('itemSgst', itemSgst);
                                record.set('itemIgst', itemIgst);
                                upInvQty_window.close();
                                Ext.getCmp('pe_subTotal').focus(0, 100);
                            } else {
                                Ext.Msg.alert('Notification', 'The selected item is already invoiced or partly invoiced.');
                                record.set('fpod_invoiceqty', '');
                            }

                        }
                    }]
            });

        }
        upInvQty_window.doLayout();
        upInvQty_window.show(this);
        upInvQty_window.center();
    };

    var purchaseInvoiceColModel = function ()
    {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'fpod_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'MRP',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fpod_itemmrp',
                align: 'right',
                tooltip: 'MRP',
                width: 60
            }, {
                header: 'Rate',
                sortable: true,
                dataIndex: 'fpod_itemoffrrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                align: 'right',
                width: 60
            }, {
                header: 'Rate ET',
                sortable: true,
                dataIndex: 'fpod_itemoffrrateetch',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate Excluding Tax',
                align: 'right',
                width: 60
            }, {
                header: 'Qty. Ordered',
                sortable: true,
                xtype: 'numbercolumn',
                format: '0,00,00,000',
                hideable: false,
                dataIndex: 'fpod_itemqty',
                tooltip: 'Qty. Ordered',
                align: 'right',
                width: 60
            }, {
                header: 'Qty. Receivable',
                sortable: true,
                xtype: 'numbercolumn',
                format: '0,00,00,000',
                hideable: false,
                dataIndex: 'fpod_totalqty',
                tooltip: 'Qty. Receivable',
                align: 'right',
                width: 60
            }, {
                header: 'Qty. Invoiced',
                sortable: true,
                xtype: 'numbercolumn',
                format: '0,00,00,000',
                hideable: false,
                dataIndex: 'fpod_invoicedqty',
                tooltip: 'Qty. Invoiced',
                align: 'right',
                width: 60
            }, {
                header: 'Invoice Qty',
                sortable: true,
                xtype: 'numbercolumn',
                format: '0,00,00,000',
                hideable: false,
                dataIndex: 'fpod_invoiceqty',
                tooltip: 'Qty. to be Invoiced',
                align: 'right',
                width: 100
            }, {
                header: 'Unit',
                hideable: false,
                dataIndex: 'itemUnit',
                tooltip: 'Unit',
                width: 100
            },
            {
                header: 'HSN',
                sortable: true,
                dataIndex: 'itemhsn',
                align: 'right',
                tooltip: 'HSN Code',
                width: 60
            },
            {
                header: 'Tax %',
                sortable: true,
                dataIndex: 'itemGST',
                tooltip: 'Tax Percentage',
                xtype: 'numbercolumn',
                align: 'right',
                width: 60
            },
            {
                header: 'IGST',
                sortable: true,
                dataIndex: 'itemIgst',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'IGST',
                width: 60
            },
            {
                header: 'CGST',
                sortable: true,
                dataIndex: 'itemCgst',
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
                dataIndex: 'itemSgst',
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
                header: 'Actions',
                hideable: false,
                groupable: false,
                width: 80,
                items: [
                    {
                        iconCls: '',
                        getClass: function (v, meta, rec) {
                            var data = rec.data;
                            var fpod_itemqty = data.fpod_itemqty;
                            var fpod_invoicedqty = data.fpod_invoicedqty;
                            if (fpod_invoicedqty < fpod_itemqty) {
                                this.items[0].tooltip = 'Update Quantity';
                                return 'my-icon18';
                            } else {
                                return 'finascop_hideicon';
                            }
                        },
                        handler: function (grid, rowIndex, colIndex) {
                            var grecord = grid.store.getAt(rowIndex);
                            console.log('grecord', grecord);
                            upInvQty(grecord);
                            //var record = Ext.getCmp('pe_itemgrid').getSelectionModel().getSelected();

                        }
                    }
                ]

            }
        ]);
    };

    var invoiceItemGrid = function () {
        var PurchaseInvoiceItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listPodetailsStore',
                    method: 'post'
                }),
                fields: ['fpod_id', 'fpod_itemid', 'fpod_itemname', {name: 'fpod_itemqty', type: 'int'},
                    {name: 'fpod_itemoffrrate', type: 'float'}, {name: 'fpod_itemoffrrateet', type: 'float'}, {name: 'fpod_netamount', type: 'float'},
                    {name: 'fpod_itemoffrratech', type: 'float'}, {name: 'fpod_itemoffrrateetch', type: 'float'},
                    {name: 'fpod_itemmrp', type: 'float'}, {name: 'fpod_itemoffrqty', type: 'int'}, {name: 'fpod_itemoffrrate', type: 'float'},
                    {name: 'fpod_effectiverate', type: 'float'}, {name: 'fpod_totalqty', type: 'int'},
                    {name: 'fpod_invoicedqty', type: 'int'}, {name: 'fpod_receivedqty', type: 'int'}, 'itemhsn', 'itemUnit',
                    {name: 'itemGST', type: 'float'}, {name: 'amtAfTax', type: 'float'}, {name: 'amtBfTax', type: 'float'},
                    {name: 'igst', type: 'float'}, {name: 'sgst', type: 'float'}, {name: 'cgst', type: 'float'},
                    {name: 'itemIgst', type: 'float'}, {name: 'itemCgst', type: 'float'}, {name: 'itemSgst', type: 'float'},
                    {name: 'fpod_poLandingCost', type: 'float'}, {name: 'fpod_pogstAmt', type: 'float'}, {name: 'fpod_shippingchargegst', type: 'float'}],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.poId = Application.Finascop_Accounts_Purchase.Cache.POID;
                    }
                }

            });
            return itemStore;
        };
        var itemGridStore = PurchaseInvoiceItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'pe_itemgrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseInvoiceColModel(),
            tbar: [],
            listeners: {
                keydown: function (e)
                {
                    if ((e.getCharCode() == 99) || (e.getCharCode() == 67)) {
                        var grid = Ext.getCmp('pe_itemgrid');
                        var selected = grid.getSelectionModel().getSelected();
                        var rowIndex = grid.getStore().indexOf(selected);
                        var record = grid.getStore().getAt(rowIndex);
                        removeFromItemStore(grid, rowIndex, record);
                        excludeItemIds.remove(record.get('item_id'));
                        e.stopEvent();
                    } else if (e.getCharCode() == 13) {
                        e.stopEvent();
                        Ext.getCmp('pe_discount').focus();
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
    var peViewColModel = function ()
    {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'fped_fpodItemName',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'Rate',
                sortable: true,
                xtype: 'numbercolumn',
                hideable: false,
                dataIndex: 'fped_amtBfTax',
                tooltip: 'Rate', //Amount Before Tax
                width: 130

            }, {
                header: 'Qty Invoiced',
                sortable: true,
                hideable: false,
                dataIndex: 'fped_qtyInvoiced',
                tooltip: 'Qty Invoiced',
                width: 130

            }, {
                header: 'IGST',
                sortable: true,
                xtype: 'numbercolumn',
                hideable: false,
                dataIndex: 'fped_igstAmt',
                tooltip: 'IGST',
                width: 130

            }, {
                header: 'CGST',
                sortable: true,
                xtype: 'numbercolumn',
                hideable: false,
                dataIndex: 'fped_cgstAmt',
                tooltip: 'CGST',
                width: 130

            }, {
                header: 'SGST',
                sortable: true,
                xtype: 'numbercolumn',
                hideable: false,
                dataIndex: 'fped_sgst',
                tooltip: 'SGST',
                width: 130

            }, {
                header: 'Amount',
                sortable: true,
                xtype: 'numbercolumn',
                hideable: false,
                dataIndex: 'fped_amtAfTax',
                tooltip: 'Amount', //After tax
                width: 130

            }
        ]);
    };
    var invoiceItemViewGrid = function (fped_fpeId) {
        var PurchaseInvoiceItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listPEdetailsStore',
                    method: 'post'
                }),
                fields: ['fped_fpodItemName', 'fped_qtyInvoiced', 'fped_igstAmt', 'fped_cgstAmt', 'fped_sgst', 'fped_amtBfTax', 'fped_amtAfTax'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.fped_fpeId = fped_fpeId;
                    }
                }

            });
            return itemStore;
        };
        var itemGridStore = PurchaseInvoiceItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'pe_invoiceItemViewGrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: peViewColModel(),
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true
        });
        return itemgrid_panel;
    };
    var getMigratedData = function (gridid) {
        var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), 'data');
        return Ext.encode(j);
    };
    var vendorSearchGridStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getVendorDetails',
            fields: ['customerId', 'stpa_Fname', 'stpa_Lname', 'stpa_GSTIN', 'stpa_Address', 'stpa_City', 'stpa_PINCODE', 'st_name', 'dst_Name', 'st_id', 'dst_Id'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: false
        });
        return store;
    };
    var vendorSearchGrid = function () {
        var vendorSearchStore = vendorSearchGridStore();
        var grid = new Ext.grid.GridPanel({
            id: 'vendor_search_grid',
            autoScroll: true,
            animCollapse: false,
            frame: false,
            viewConfig: {
                forceFit: true, markDirty: false},
            stripeRows: true, layout: 'fit',
            border: false,
            hideBorders: true,
            store: vendorSearchStore,
            height: 250,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'stpa_Fname',
                    width: 175
                },
                {
                    header: 'GST / VAT ',
                    sortable: true,
                    dataIndex: 'stpa_GSTIN',
                    width: 175
                },
                {
                    header: 'City',
                    sortable: true,
                    dataIndex: 'stpa_City',
                    width: 175
                },
                {
                    header: 'Pincode',
                    sortable: true,
                    dataIndex: 'stpa_PINCODE',
                    width: 175
                },
                {
                    header: 'State',
                    sortable: true,
                    dataIndex: 'st_name',
                    width: 175
                },
                {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'dst_Name',
                    width: 175
                }], listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex); // Get the Record
                },
                celldblclick: function () {
                    var data = Ext.getCmp('vendor_search_grid').getSelectionModel().getSelections();
                    if (data != '') {
                        var vendorId = Ext.getCmp('vendor_search_grid').getSelectionModel().getSelections()[0].data.customerId;
                        var vendorName = Ext.getCmp('vendor_search_grid').getSelectionModel().getSelections()[0].data.stpa_Fname;

                        Application.Finascop_Accounts_Purchase.Cache.ppVendorId = vendorId;
                        Application.Finascop_Accounts_Purchase.Cache.ppVendorName = vendorName;
                        Ext.getCmp('vendorFilterWindow').close();
                        Application.Finascop_Accounts_Purchase.initPaymentProcess(Application.Finascop_Accounts_Purchase.Cache.ppVendorId, Application.Finascop_Accounts_Purchase.Cache.ppVendorName);
                    }
                }
            }
        });
        return grid;
    };
    var vendorFilterForm = function () {
        var form = new Ext.form.FormPanel({
            labelWidth: 80,
            labelAlign: 'left',
            frame: false,
            border: false,
            hideBorders: true,
            modal: true,
            autoScroll: true,
            layout: 'column',
            items: [
                {
                    columnWidth: .7,
                    layout: 'form',
                    style: 'margin-top:15px',
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Search',
                            id: 'search_field',
                            emptyText: 'Enter search value',
                            name: 'n[search_field]',
                            allowBlank: false,
                            anchor: '95%',
                            tabIndex: 6,
                            listeners: {
                                change: function () {
                                    var search_field = Ext.getCmp('search_field').getValue();

                                    Ext.getCmp('vendor_search_grid').getStore().load({
                                        params: {
                                            search_field: search_field
                                        }
                                    });
                                }
                            }
                        }
                    ]
                }, {
                    columnWidth: .3,
                    layout: 'form',
                    style: 'margin-top:15px;margin-left:-17px;',
                    items: [{
                            xtype: 'button',
                            style: 'margin-left:50px;',
                            text: 'Submit',
                            tabIndex: 8,
                            handler: function () {
                                var search_field = Ext.getCmp('search_field').getValue();

                                Ext.getCmp('vendor_search_grid').getStore().load({
                                    params: {
                                        search_field: search_field
                                    }
                                });
                            }
                        }
                    ]
                },
                {
                    columnWidth: 1,
                    height: 300,
                    title: 'Search Results',
                    items: [vendorSearchGrid()]
                }
            ]
        });
        return form;
    };
    var VendorSearchWindow = function () {
        var vendor_filter_form = vendorFilterForm();
        var vendorFilterWindow = new Ext.Window({
            id: 'vendorFilterWindow',
            modal: true,
            constrain: true,
            width: 500,
            resizable: false,
            floating: true,
            title: 'Vendor Search',
            shadow: false,
            items: [vendor_filter_form],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    tabIndex: 32,
                    handler: function () {
                        vendorFilterWindow.close();
                    }
                },
                {
                    text: 'Select',
                    iconCls: 'add_visual',
                    icon: IMAGE_BASE_PATH + '/default/icons/enable.png',
                    tabIndex: 31,
                    width: 80,
                    handler: function () {
                        var data = Ext.getCmp('vendor_search_grid').getSelectionModel().getSelections();
                        if (data != '') {

                            var vendorId = Ext.getCmp('vendor_search_grid').getSelectionModel().getSelections()[0].data.customerId;
                            var vendorName = Ext.getCmp('vendor_search_grid').getSelectionModel().getSelections()[0].data.stpa_Fname;

                            Application.Finascop_Accounts_Purchase.Cache.ppVendorId = vendorId;
                            Application.Finascop_Accounts_Purchase.Cache.ppVendorName = vendorName;
                            Ext.getCmp('vendorFilterWindow').close();
                            Application.Finascop_Accounts_Purchase.initPaymentProcess(Application.Finascop_Accounts_Purchase.Cache.ppVendorId, Application.Finascop_Accounts_Purchase.Cache.ppVendorName);


                        }
                    }
                },
                //{
                //    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                //    iconCls: 'my-icon1',
                //    text: 'Close',
                //    tabIndex: 32,
                //    handler: function () {
                //       vendorFilterWindow.close();
                //   }
                //}
            ]

        });
        Ext.getCmp('search_field').focus(false, 100);
        var vendorFilterWindow = Ext.getCmp('vendorFilterWindow');
        vendorFilterWindow.show();
        vendorFilterWindow.doLayout();
        vendorFilterWindow.center();

    };
    var purchasePaymentStore = function (vid) {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPurchaseEntryForPayProce',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpe_id',
                root: 'data'
            }, ['fpe_id', 'fpe_vendorName', 'fpe_fpoPoNumber', 'fpe_fpoPODate', 'fpe_invoiceNumber', 'fpe_invoiceDate', 'fpe_grossAmt', 'fpe_discount', 'fpe_netQty', 'fpe_netItems',
                'fpe_netTax', 'fpe_netAmount', 'fpe_netIgst', 'fpe_netCgst', 'fpe_netSgst', 'paymentStatus']),
            sortInfo: {
                field: 'fpe_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.vendorId = vid;
                }
            }
        });
        store.setDefaultSort('vendor_name', 'ASC');
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
                    Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vid);
                },
                rowselect: function (sm, rowIndex, record) {
                    record.set('checked', 'true');
                    Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vid);
                }
            }
        });
    };
    var PurchasePaymentGrid = function (vid, vname) {
        var chk_model = check_model(vid);
        var centre_store = purchasePaymentStore(vid);
        var centre_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fpe_vendorName'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_fpoPoNumber'
                }, {
                    type: 'string',
                    dataIndex: 'fpe_invoiceNumber'
                }]
        });
        centre_filter.remote = true;
        centre_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: centre_store,
            layout: 'fit',
            region: 'center',
            frame: false,
            border: false,
            loadMask: true,
            title: '',
            iconCls: 'finascop_approval',
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('paymentStatus') == 'Paid')/*Nothing done */
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
            plugins: [new Ext.ux.grid.GroupSummary(), centre_filter],
            id: 'purchasepaymentGridPanel',
            columns: [chk_model,
                {
                    header: 'Invoice No:',
                    sortable: true,
                    hideable: false,
                    id: 'invnum_auto_exppp',
                    dataIndex: 'fpe_invoiceNumber',
                    tooltip: 'Invoice Number'
                }, {
                    header: 'Invoice Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpe_invoiceDate',
                    tooltip: 'Invoice Date'
                }, {
                    header: 'Net Amt.',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'fpe_netAmount',
                    tooltip: 'Net Amt.'
                },
                {
                    header: 'PO No:',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpe_fpoPoNumber',
                    tooltip: 'PO Number'
                }],
            viewConfig: {
                forceFit: true
            },
            sm: chk_model,
            listeners: {
                beforeselect: function (sm, rowIndex, record) {
                    if (record.get('paymentStatus') == 'Paid')/*Nothing done */
                    {
                        console.log('on gid');
                        return false;
                    }
                },
                viewready: updatePagination
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: centre_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
            }),
            stripeRows: true,
            autoExpandColumn: 'invnum_auto_exppp'
        });
        return grid_panel;
    };

    var purchasePaymentPanel = function (id, vendorId, vendorName) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white", "padding": "5px 3px"},
            border: false,
            id: id,
            title: 'Payment Process for ' + vendorName,
            iconCls: 'finascop_itemmaster',
            items: [PurchasePaymentGrid(vendorId, vendorName), invoiceDetail(vendorId, vendorName)]
        });
        return panel;
    };
    var chequeForm = function (netAmount, dateOfInstrument, peids, vendorId, vendorName, mode, invoices, reqID) {

        var bankLedgerStore = new Ext.data.JsonStore({
            autoLoad: true,
            id: 'cq_bank_store',
            url: modURL + '&op=getBankLedgers',
            totalProperty: 'totalCount',
            method: 'post',
            fields: ['name', 'id'],
            root: 'data'
        });

        var form = new Ext.form.FormPanel({
            frame: false,
            border: false,
            hideBorders: true,
            labelWidth: 100,
            id: 'cheque-payment-form',
            autoScroll: true,
            height: 200,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            labelAlign: 'left',
            defaults: {style: {padding: 10}},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Branch',
                    id: 'chequeBranch',
                    name: 'chequeBranch',
                    anchor: '98%',
                    readOnly: true,
                    value: _SESSION.current_branch,
                    tabIndex: 51
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Payment Mode',
                    id: 'paymentChequeMode',
                    name: 'paymentMode',
                    anchor: '98%',
                    value: mode,
                    readOnly: true,
                    tabIndex: 52
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Pay To',
                    id: 'chequeVendor',
                    name: 'chequeVendor',
                    anchor: '98%',
                    value: vendorName,
                    readOnly: true,
                    tabIndex: 53
                }, {
                    xtype: 'hidden',
                    fieldLabel: 'peids',
                    id: 'chequePEIds',
                    name: 'chequePEIds',
                    readOnly: true,
                    anchor: '98%',
                    value: peids
                }, {
                    xtype: 'hidden',
                    fieldLabel: 'Pay To Vendor ID',
                    id: 'chequeVendorID',
                    name: 'chequeVendorID',
                    readOnly: true,
                    anchor: '98%',
                    value: vendorId
                }, {
                    xtype: 'hidden',
                    fieldLabel: 'Pay To Invioces',
                    id: 'chequeVendorInvDet',
                    name: 'chequeVendorInvDet',
                    readOnly: true,
                    anchor: '98%',
                    value: invoices
                },
                {
                    xtype: 'hidden',
                    fieldLabel: 'ReqID',
                    id: 'chequeReqID',
                    name: 'chequeReqID',
                    readOnly: true,
                    anchor: '98%',
                    value: reqID
                }, {
                    xtype: 'combo',
                    fieldLabel: 'Bank',
                    id: 'chequeBank',
                    name: 'chequeBank',
                    hiddenName: 'chequeBankAPIKey',
                    anchor: '98%',
                    tabIndex: 54,
                    store: bankLedgerStore,
                    displayField: 'name',
                    valueField: 'id',
                    mode: 'local',
                    editable: false

                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Cheque No:',
                    id: 'chequeNumber',
                    name: 'chequeNumber',
                    anchor: '98%',
                    tabIndex: 55
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Amount',
                    minValue: 0.05,
                    id: 'chequeAmt',
                    name: 'chequeAmt',
                    anchor: '98%',
                    readOnly: true,
                    value: netAmount,
                    tabIndex: 56
                }]
        });
        return form;
    };
    var cashForm = function (netAmount, dateOfInstrument, peids, vendorId, vendorName, mode, invoices, reqID) {
        var form = new Ext.form.FormPanel({
            frame: false,
            border: false,
            hideBorders: true,
            labelWidth: 100,
            id: 'cash-payment-form',
            autoScroll: true,
            height: 200,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            labelAlign: 'left',
            defaults: {style: {padding: 10}},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Branch',
                    id: 'cashBranch',
                    name: 'cashBranch',
                    readOnly: true,
                    anchor: '98%',
                    value: _SESSION.current_branch,
                    tabIndex: 95
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Payment Mode',
                    id: 'cashMode',
                    name: 'cashMode',
                    anchor: '98%',
                    readOnly: true,
                    value: mode,
                    tabIndex: 96
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Pay To',
                    id: 'cashVendor',
                    name: 'cashVendor',
                    readOnly: true,
                    anchor: '98%',
                    value: vendorName,
                    tabIndex: 97
                }, {
                    xtype: 'hidden',
                    fieldLabel: 'peids',
                    id: 'cashPEids',
                    name: 'cashPEIds',
                    readOnly: true,
                    anchor: '98%',
                    value: peids
                }, {
                    xtype: 'hidden',
                    fieldLabel: 'Pay To Vendor ID',
                    id: 'cashVendorID',
                    name: 'cashVendorID',
                    readOnly: true,
                    anchor: '98%',
                    value: vendorId
                }, {
                    xtype: 'hidden',
                    fieldLabel: 'Pay To Invioces',
                    id: 'cashVendorInvDet',
                    name: 'cashVendorInvDet',
                    readOnly: true,
                    anchor: '98%',
                    value: invoices
                },
                {
                    xtype: 'hidden',
                    fieldLabel: 'ReqID',
                    id: 'cashReqID',
                    name: 'cashReqID',
                    readOnly: true,
                    anchor: '98%',
                    value: reqID
                },
                {
                    xtype: 'numberfield',
                    fieldLabel: 'Amount',
                    id: 'cashAmt',
                    minValue: 0.05,
                    name: 'cashAmt',
                    anchor: '98%',
                    readOnly: true,
                    value: netAmount,
                    tabIndex: 98
                }
            ]
        });
        return form;
    };
    var payCashWindow = function (netAmount, dateOfInstrument, peids, vendorId, vendorName, mode, invoices, reqID) {
        var payment_form;
        if (mode === 'Cash') {
            payment_form = cashForm(netAmount, dateOfInstrument, peids, vendorId, vendorName, mode, invoices, reqID);
        } else if (mode === 'Cheque') {
            payment_form = chequeForm(netAmount, dateOfInstrument, peids, vendorId, vendorName, mode, invoices, reqID);
        }

        var paymentWin = new Ext.Window({
            id: 'cashPayWindow',
            title: mode + ' Payment',
            iconCls: 'store_search',
            modal: true,
            constrain: true,
            width: 400, //80%
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
                    text: 'Submit',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_approve.png',
                    cls: 'left-right-buttons',
                    tabIndex: 98,
                    width: 80,
                    handler: function () {
                        if (mode === 'Cash') {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            var form = Ext.getCmp('cash-payment-form').getForm();
                            if (form.isValid()) {
                                form.submit({
                                    url: modURL + '&op=processCashPayment',
                                    waitMsg: 'Saving Details....',
                                    waitTitle: 'Please Wait...',
                                    params: {
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    success: function (response, action) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success === true) {
                                            Application.example.msg('Success', tmp.msg);
                                        } else if (tmp.success === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                        } else {
                                            Ext.Msg.alert("Error", tmp);
                                        }
                                        Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                        Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                        paymentWin.close();
                                    },
                                    failure: function (form, action) {
                                        switch (action.failureType) {
                                            case Ext.form.Action.CLIENT_INVALID:
                                                Ext.Msg.alert('Failure', 'Form fields may be submitted with invalid values',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                paymentWin.close();
                                                                Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                                                Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.CONNECT_FAILURE:
                                                Ext.Msg.alert('Failure', 'Ajax communication failed',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                paymentWin.close();
                                                                Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                                                Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.SERVER_INVALID:
                                                Ext.Msg.alert('Failure', action.response.responseText,
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                paymentWin.close();
                                                                Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                                                Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                                            }
                                                        });
                                        }

                                    }
                                });
                            } else {
                                Ext.MessageBox.alert('Error', 'Check the required fields');
                            }
                        } else if (mode === 'Cheque') {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            var form = Ext.getCmp('cheque-payment-form').getForm();
                            if (form.isValid()) {
                                form.submit({
                                    url: modURL + '&op=processChequePayment',
                                    waitMsg: 'Saving Details....',
                                    waitTitle: 'Please Wait...',
                                    params: {
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    success: function (response, action) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success === true) {
                                            Application.example.msg('Success', tmp.msg);
                                        } else if (tmp.success === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                        } else {
                                            Ext.Msg.alert("Error", tmp);
                                        }
                                        paymentWin.close();
                                        Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                        Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                    },
                                    failure: function (form, action) {
                                        switch (action.failureType) {
                                            case Ext.form.Action.CLIENT_INVALID:
                                                Ext.Msg.alert('Failure', 'Form fields may be submitted with invalid values',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                paymentWin.close();
                                                                Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                                                Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.CONNECT_FAILURE:
                                                Ext.Msg.alert('Failure', 'Ajax communication failed',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                paymentWin.close();
                                                                Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                                                Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.SERVER_INVALID:
                                                Ext.Msg.alert('Failure', action.response.responseText,
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                paymentWin.close();
                                                                Application.Finascop_Accounts_Purchase.ViewInvoiceDetails(vendorId);
                                                                Ext.getCmp('purchasepaymentGridPanel').getStore().reload();
                                                            }
                                                        });
                                        }

                                    }
                                });
                            } else {
                                Ext.MessageBox.alert('Error', 'Check the required fields');
                            }
                        }


                    }
                }]
        });
        var paymentWin = Ext.getCmp('cashPayWindow');
        paymentWin.show();
        paymentWin.doLayout();
        paymentWin.center();
    };
    var invoiceDetail = function (vendorId, vendorName) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var src = '?module=finascop_accounts_purchase&op=invoice_profile_view&vendorid=' + vendorId + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp + "&fpeIds=" + Application.Finascop_Accounts_Purchase.Cache.fpeIds;
        return new Ext.Panel({
            frame: false,
            border: true,
            layout: 'border',
            region: 'east',
            title: 'Details of Invoice',
            width: winsize.width * 0.4,
            id: 'pu_parent_sliderpanel',
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
                            html: '<iframe id="iframe_invoiceDetails" name="iframe_invoiceDetails" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' + src + '"; ></iframe>',
                            fbar: [
                                {
                                    text: "Pay Cash",
                                    id: 'candidate_edit_btn',
                                    tabIndex: 80,
                                    handler: function () {
                                        var netAmount = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('netamt').value;
                                        var dateOfInstrument = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('datofinstrument').value;
                                        var peids = Application.Finascop_Accounts_Purchase.Cache.fpeIds;
                                        var invoices = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('invoices').value;
                                        var reqID = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('reqID').value;
                                        payCashWindow(netAmount, dateOfInstrument, peids, vendorId, vendorName, 'Cash', invoices, reqID);

                                    }
                                }, {
                                    text: "Issue Cheque",
                                    id: 'candidate_cnvrtTeachr_btn',
                                    tabIndex: 81,
                                    handler: function () {
                                        var netAmount = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('netamt').value;
                                        var dateOfInstrument = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('datofinstrument').value;
                                        var peids = Application.Finascop_Accounts_Purchase.Cache.fpeIds;
                                        var invoices = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('invoices').value;
                                        var reqID = Ext.getDom('iframe_invoiceDetails').contentWindow.document.getElementById('reqID').value;
                                        payCashWindow(netAmount, dateOfInstrument, peids, vendorId, vendorName, 'Cheque', invoices, reqID);
                                    }
                                }, {
                                    text: "Print Cheque",
                                    id: 'candidate_saveandexit_btn',
                                    tabIndex: 82,
                                    handler: function () {
                                        Ext.MessageBox.alert("Notification", 'Integration is going on.');
                                    }
                                }, {
                                    text: "Pay Online",
                                    id: 'candidate_archive_btn',
                                    tabIndex: 83,
                                    handler: function () {
                                        Ext.MessageBox.alert("Notification", 'Integration is going on.');
                                    }
                                }]
                        }]
                })]
        });
    };
    return{
        Cache: {},
        initPurchaseEntry: function () {
            var panelId = 'purchase_entryaccount_panel';
            var purchase_entryaccount_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchase_entryaccount_panel)) {
                purchase_entryaccount_panel = purchaseEntryPanel(panelId);
                Application.UI.addTab(purchase_entryaccount_panel);
                purchase_entryaccount_panel.doLayout();
            } else {
                Application.UI.addTab(purchase_entryaccount_panel);
                purchase_entryaccount_panel.doLayout();
            }

        }, chooseVendor: function () {
            VendorSearchWindow();
        }, initPaymentProcess: function (vendorId, vendorName) {
            var panelId = 'purchase_paymentaccount_panel';
            var purchase_paymentaccount_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchase_paymentaccount_panel)) {
                purchase_paymentaccount_panel = purchasePaymentPanel(panelId, vendorId, vendorName);
                Application.UI.addTab(purchase_paymentaccount_panel);
                purchase_paymentaccount_panel.doLayout();
            } else {
                Application.UI.addTab(purchase_paymentaccount_panel);
                purchase_paymentaccount_panel.doLayout();
                Application.Finascop_Accounts_Purchase.Cache.fpeIds = 0;
                Ext.getCmp('pu_parent_sliderpanel').collapse();
                Ext.getCmp('purchasepaymentGridPanel').getStore().load({
                    params: {
                        vendorId: vendorId
                    }
                });
            }
        }, savePurchaseentries: function () {
            var itemGriddata = getMigratedData('pe_itemgrid');
            var purchaseEntryForm = Ext.getCmp('purchase_entry_form').getForm();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (purchaseEntryForm.isValid()) {
                purchaseEntryForm.submit({
                    url: modURL + '&op=savePurchaseEntry',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        PoId: Application.Finascop_Accounts_Purchase.Cache.POID,
                        VendorId: Application.Finascop_Accounts_Purchase.Cache.vendorId,
                        peItemSGriddata: itemGriddata,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.msg);
                            Ext.getCmp('purchaseEntry_window').close();
                            Ext.getCmp('purchaseentriGridPanel').getStore().load();
                        } else if (tmp.success === true && tmp.valid === false) {
                            Application.example.msg('Error', tmp.msg);
                        } else {
                            Application.example.msg('Error', tmp.msg);
                        }
                    },
                    failure: function (form, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        switch (action.failureType) {
                            case Ext.form.Action.CLIENT_INVALID:
                                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
                                break;
                            case Ext.form.Action.CONNECT_FAILURE:
                                Ext.Msg.alert('Failure', 'Ajax communication failed');
                                break;
                            case Ext.form.Action.SERVER_INVALID:
                                Ext.Msg.alert('Failure', tmp.msg);
                        }
                    }
                });
            } else {
                Application.example.msg('Error', 'Check the required fields');
            }
        },
        ViewInvoiceDetails: function (vendorId) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var gr = Ext.getCmp('purchasepaymentGridPanel');
            var str = gr.getStore().getRange();
            var fpeIds = [];
            Ext.each(str, function (r) {
                if (r.get('checked') == 'true' && r.get('paymentStatus') != 'Paid') {
                    fpeIds.push(r.get('fpe_id'));
                }
            });
            var peIds = Ext.encode(fpeIds);
            Application.Finascop_Accounts_Purchase.Cache.fpeIds = peIds;
            Ext.getCmp('pu_parent_sliderpanel').expand(true);
            Ext.get('iframe_invoiceDetails').dom.src = modURL + '&op=invoice_profile_view&vendorid=' + vendorId + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp + "&fpeIds=" + peIds;
        }
    };

}();
