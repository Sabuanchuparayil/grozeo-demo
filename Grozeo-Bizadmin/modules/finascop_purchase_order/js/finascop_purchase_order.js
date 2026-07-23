Application.Finascop_Purchase_Order = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 19;
    var partytype = 0;
    var modURL = '?module=finascop_purchase_order';
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    var total_mrp = 0;
    var onGridResize = function (cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    };
    var eprCalculation = function () {
        var purchaseRate = Ext.getCmp('fpot_itemoffrrate').getValue(); //offer rate
        var additionalDiscount = Ext.getCmp('fpot_itemaddidisc').getValue(); //additional discount
        var itemQuantity = Ext.getCmp('fpot_itemqty').getValue(); //product quantity
        var PdtDiscount = Ext.getCmp('fpot_itemoffrqty').getValue(); //offer quantity
        var effectRate = (parseFloat(itemQuantity) * (parseFloat(purchaseRate) - parseFloat(additionalDiscount))) / (parseFloat(itemQuantity) + parseFloat(PdtDiscount));
        if (effectRate > 0) {
            Ext.getCmp('fpot_effectiverate').setValue(effectRate);
        } else {
            Ext.Msg.alert("Notification.", 'Invalid effective rate');
        }
    }
    var vendorItemsGridStorePO = function (customerId) {
        var vendorId = arguments[0];
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=poVendorItemsGridStore',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['stit_ID', 'stit_itemName', 'stit_brand_name', 'stit_category_name', 'stit_product_variant', 'stit_quantity', 'last_mrp', 'last_sp', 'stit_SKU', 'stit_HSN_code', 'stit_GST',
                'uniqueId', 'opuniqueid', 'opitemid', 'opvendorid', 'fpot_itemmrp', 'fpot_itemoffrqty', 'fpot_itemaddidisc', 'fpot_itemqty', 'fpot_itemoffrrate', 'fpot_effectiverate',
                'fpot_giftname', 'fpot_giftqty', 'fpot_notes', 'po_item_gift', 'minQty', 'optiQty']),
            sortInfo: {
                field: 'stit_ID',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            listeners: {
                beforeload: function (store, e) {

                    this.baseParams.vendorId = vendorId;
                }, load: function () {
                    console.log('on store load');
                    //Ext.getCmp('vendorItemGridforPO').getStore().baseParams.vendorId = customerId;
                    Ext.getCmp('vendorItemGridforPO').getSelectionModel().selectRow(0);
                    var selectedData = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0].data;
                    var ID = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0].data.stit_ID;
                    var UID = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0].data.uniqueId;
                    if (!Ext.isEmpty(ID)) {
                        console.log('!Ext.isEmpty(ID)');
                        Application.Finascop_Purchase_Order.Cache.stit_ID = ID;
                        Application.Finascop_Purchase_Order.Cache.UID = UID;
                        var paneltitle = 'PO Details of ' + selectedData.stit_SKU;
                        console.log('paneltitle load', paneltitle);
                        Ext.getCmp('CategoryparentPanel').setTitle(paneltitle);
                        Ext.getCmp('CategoryparentPanel').doLayout();
                        Ext.getCmp('opitemid').setValue(ID);
                        Ext.getCmp('opuniqueid').setValue(UID);
                        Ext.getCmp('opvendorid').setValue(vendorId);
                        Ext.getCmp('fpot_itemmrp').setValue(selectedData.fpot_itemmrp);
                        Ext.getCmp('fpot_itemoffrqty').setValue(selectedData.fpot_itemoffrqty);
                        Ext.getCmp('fpot_itemaddidisc').setValue(selectedData.fpot_itemaddidisc);
                        Ext.getCmp('fpot_itemqty').setValue(selectedData.fpot_itemqty);
                        Ext.getCmp('fpot_itemoffrrate').setValue(selectedData.fpot_itemoffrrate);
                        Ext.getCmp('fpot_effectiverate').setValue(selectedData.fpot_effectiverate);
                        Ext.getCmp('fpot_giftname').setValue(selectedData.fpot_giftname);
                        Ext.getCmp('fpot_giftqty').setValue(selectedData.fpot_giftqty);
                        Ext.getCmp('fpot_notes').setValue(selectedData.fpot_notes);
                        Ext.getCmp('po_item_gift').setValue(selectedData.po_item_gift);
                        if (selectedData.fpot_effectiverate != '') {
                            Ext.getCmp('podetEditBtn').show();
                            Ext.getCmp('podetSaveBtn').hide();
                            Ext.getCmp('podetCancelBtn').hide();
                            Ext.getCmp('itemPOForm').disable();
                        } else {
                            Ext.getCmp('podetEditBtn').hide();
                            Ext.getCmp('podetSaveBtn').show();
                            Ext.getCmp('podetCancelBtn').show();
                            Ext.getCmp('itemPOForm').enable();
                        }
                    }

                }
            }
        });
        return store;
    };
    var vendItemGridforPO = function (customerId) {
        var vendorId = arguments[0];
        var spfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_category_name'
                }]
        });
        spfilters.remote = true;
        spfilters.autoReload = true;
        var vendorItemsStorePO = vendorItemsGridStorePO(vendorId);
        var grid = new Ext.grid.GridPanel({
            store: vendorItemsStorePO,
            id: 'vendorItemGridforPO',
            height: 430,
            region: 'center',
            layout: 'fit',
            autoScroll: true,
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpot_effectiverate') != '')
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
            plugins: [new Ext.ux.grid.GroupSummary(), spfilters],
            fields: ['stit_ID', 'stit_itemName', 'stit_brand_name', 'stit_category_name', 'stit_product_variant', 'stit_quantity', 'last_mrp', 'last_sp', 'uniqueId', 'opuniqueid', 'opitemid', 'opvendorid',
                'fpot_itemmrp', 'fpot_itemoffrqty', 'fpot_itemaddidisc', 'fpot_itemqty', 'fpot_itemoffrrate', 'fpot_effectiverate', 'fpot_giftname', 'fpot_giftqty', 'fpot_notes', 'po_item_gift', 'stit_SKU'],
            viewConfig: {
                forceFit: true,
                markDirty: false
            },
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            frame: true,
            border: false,
            hideBorders: true,
            columns: [new Ext.grid.RowNumberer(),
                {header: 'Items', dataIndex: 'stit_SKU', sortable: true, tooltip: 'Items', width: 100, renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.stit_SKU + '"';
                        return value;
                    }}, {header: 'HSN', dataIndex: 'stit_HSN_code', sortable: true, width: 100},
                {header: 'Tax', dataIndex: 'stit_GST', sortable: true, align: 'right', width: 50},
                {header: 'LP', dataIndex: 'last_mrp', tooltip: 'Last Purchase', align: 'right', sortable: true},
                {header: 'LPP', dataIndex: 'last_sp', tooltip: 'Lowest Purchase Price', align: 'right', sortable: true}, {header: 'Optimum Quantity', align: 'right', dataIndex: 'optiQty', sortable: true},
                {header: 'Minimum Quantity', dataIndex: 'minQty', align: 'right', sortable: true}
            ],
            autoExpandColumn: 'stit_itemName',
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('stit_ID');
                    var UID = record.get('uniqueId');
                    var paneltitle = 'PO Details of ' + record.get('stit_itemName') + '-' + record.get('stit_category_name') + '-' + record.get('stit_brand_name');
                    console.log('paneltitle', paneltitle);
                    Ext.getCmp('CategoryparentPanel').setTitle(paneltitle);
                    Ext.getCmp('CategoryparentPanel').doLayout();
                    if (!Ext.isEmpty(ID)) {
                        Application.Finascop_Purchase_Order.Cache.stit_ID = ID;
                        Application.Finascop_Purchase_Order.Cache.UID = UID;
                        Ext.getCmp('fpot_itemmrp').setValue(record.get('fpot_itemmrp'));
                        Ext.getCmp('fpot_itemoffrqty').setValue(record.get('fpot_itemoffrqty'));
                        Ext.getCmp('fpot_itemaddidisc').setValue(record.get('fpot_itemaddidisc'));
                        Ext.getCmp('fpot_itemqty').setValue(record.get('fpot_itemqty'));
                        Ext.getCmp('fpot_itemoffrrate').setValue(record.get('fpot_itemoffrrate'));
                        Ext.getCmp('fpot_itemoffrrateet').setValue(record.get('fpot_itemoffrrateet'));
                        Ext.getCmp('fpot_effectiverate').setValue(record.get('fpot_effectiverate'));
                        Ext.getCmp('fpot_giftname').setValue(record.get('fpot_giftname'));
                        Ext.getCmp('fpot_giftqty').setValue(record.get('fpot_giftqty'));
                        Ext.getCmp('fpot_notes').setValue(record.get('fpot_notes'));
                        Ext.getCmp('po_item_gift').setValue(record.get('po_item_gift'));
                        if (record.get('fpot_effectiverate') != '') {
                            Ext.getCmp('podetEditBtn').show();
                            Ext.getCmp('podetSaveBtn').hide();
                            Ext.getCmp('podetCancelBtn').hide();
                            Ext.getCmp('fpot_itemmrp').setReadOnly(true);
                            Ext.getCmp('fpot_itemoffrqty').setReadOnly(true);
                            Ext.getCmp('fpot_itemaddidisc').setReadOnly(true);
                            Ext.getCmp('fpot_itemqty').setReadOnly(true);
                            Ext.getCmp('fpot_itemoffrrate').setReadOnly(true);
                            Ext.getCmp('fpot_effectiverate').setReadOnly(true);
                            Ext.getCmp('fpot_giftname').setReadOnly(true);
                            Ext.getCmp('fpot_giftqty').setReadOnly(true);
                            Ext.getCmp('fpot_notes').setReadOnly(true);
                            Ext.getCmp('po_item_gift').setReadOnly(true);
                            Ext.getCmp('fpot_giftname').disable();
                            Ext.getCmp('fpot_giftqty').disable();
                        } else {
                            Ext.getCmp('podetEditBtn').hide();
                            Ext.getCmp('podetSaveBtn').show();
                            Ext.getCmp('podetCancelBtn').show();
                            Ext.getCmp('fpot_itemmrp').setReadOnly(false);
                            Ext.getCmp('fpot_itemoffrqty').setReadOnly(false);
                            Ext.getCmp('fpot_itemaddidisc').setReadOnly(false);
                            Ext.getCmp('fpot_itemqty').setReadOnly(false);
                            Ext.getCmp('fpot_itemoffrrate').setReadOnly(false);
                            Ext.getCmp('fpot_giftname').setReadOnly(false);
                            Ext.getCmp('fpot_giftqty').setReadOnly(false);
                            Ext.getCmp('fpot_notes').setReadOnly(false);
                            Ext.getCmp('po_item_gift').setReadOnly(false);
                        }
                    }
                },
                afterrender: function (grid, st) {
                    vendorItemsStorePO.load();
                }
            },
            tbar: []
        });
        return grid;
    };
    var vendorDetails = function () {
        var vendorData = new Ext.Panel({
            region: 'north',
            frame: false,
            id: 'VendordetailsParentPanel',
            height: 25,
            items: [new Ext.form.FormPanel({
                    frame: false,
                    hideBorders: true,
                    layout: 'column',
                    labelAlign: 'left',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    id: 'vendorPOForm',
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Number',
                                    id: 'po_number',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_number]',
                                    anchor: '80%',
                                    value: 'Not Genereated',
                                    tabIndex: 1
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Date',
                                    id: 'po_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    value: 'Not Genereated',
                                    anchor: '80%',
                                    tabIndex: 2
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ordered By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]',
                                    anchor: '80%',
                                    value: _SESSION.FirstName,
                                    tabIndex: 3
                                }
                            ]}
                    ]

                })
            ]
        });
        return vendorData;
    };
    var itemPOForms = function (customerId) {
        var itemPOFormspanel = new Ext.form.FormPanel({
            frame: false,
            border: true,
            hideBorders: true,
            fileUpload: true,
            autoScroll: true,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 5px"},
            id: 'itemPOForm',
            items: [
                {xtype: 'hidden', id: 'opuniqueid', name: 'opuniqueid'}, {xtype: 'hidden', id: 'opitemid', name: 'opitemid'}, {xtype: 'hidden', id: 'opvendorid', name: 'opvendorid'},
                {
                    xtype: 'panel',
                    layout: 'column',
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Offer Rate (Rs)',
                                    id: 'fpot_itemoffrrate',
                                    name: 'n[fpot_itemoffrrate]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    tabIndex: 501,
                                    value: 0,
                                    listeners: {
                                        change: function () {
                                            Ext.getCmp('fpot_effectiverate').reset();
                                            var taxRate = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0].data.stit_GST;
                                            var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                            var fpot_itemoffrrateet = (parseFloat(fpot_itemoffrrate) * 100) / (100 + parseFloat(taxRate));
                                            Ext.getCmp('fpot_itemoffrrateet').setValue(fpot_itemoffrrateet);
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'MRP/RRP(Inc.)',
                                    id: 'fpot_itemmrp',
                                    name: 'n[fpot_itemmrp]',
                                    anchor: '98%',
                                    //allowBlank: false,
                                    tabIndex: 500,
                                    listeners: {
                                        afterrender: function (field) {

                                        }
                                    },
                                    change: function () {
                                        Ext.getCmp('fpot_effectiverate').reset();
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Quantity (Nos)',
                                    id: 'fpot_itemqty',
                                    name: 'n[fpot_itemqty]',
                                    allowNegative: false,
                                    allowDecimals: false,
                                    anchor: '98%',
                                    allowBlank: false,
                                    tabIndex: 502,
                                    listeners: {
                                        change: function () {
                                            Ext.getCmp('fpot_effectiverate').reset();
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    //fieldLabel: 'Product Discount (Nos)',
                                    fieldLabel: 'Product Discount',
                                    id: 'fpot_itemoffrqty',
                                    name: 'n[fpot_itemoffrqty]',
                                    anchor: '98%',
                                    tabIndex: 503,
                                    value: 0,
                                    listeners: {
                                        change: function () {
                                            Ext.getCmp('fpot_effectiverate').reset();
                                        }
                                    }
                                }]
                        }
                    ]
                }, {
                    xtype: 'panel',
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Rate without tax',
                                    id: 'fpot_itemoffrrateet',
                                    name: 'n[fpot_itemoffrrateet]',
                                    anchor: '98%',
                                    readOnly: true,
                                    allowBlank: false
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Addl Discounts (Rs)',
                                    id: 'fpot_itemaddidisc',
                                    name: 'n[fpot_itemaddidisc]',
                                    anchor: '98%',
                                    value: 0,
                                    tabIndex: 504,
                                    listeners: {
                                        change: function () {
                                            Ext.getCmp('fpot_effectiverate').reset();
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Effective Rate',
                                    id: 'fpot_effectiverate',
                                    name: 'n[fpot_effectiverate]',
                                    anchor: '98%',
                                    readOnly: true,
                                    allowBlank: false
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .25,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'button',
                                    style: 'margin:15px 0 2px 0',
                                    text: 'Calculate',
                                    tabIndex: 505,
                                    handler: function () {
                                        //Effective Rate = (Quantity *(Rate-Additional discount))  / (quantity + Offer quantity)
                                        var purchaseRate = Ext.getCmp('fpot_itemoffrrate').getValue(); //offer rate
                                        var additionalDiscount = Ext.getCmp('fpot_itemaddidisc').getValue(); //additional discount
                                        var itemQuantity = Ext.getCmp('fpot_itemqty').getValue(); //product quantity
                                        var PdtDiscount = Ext.getCmp('fpot_itemoffrqty').getValue(); //offer quantity
                                        var effectRate = (parseFloat(itemQuantity) * (parseFloat(purchaseRate) - parseFloat(additionalDiscount))) / (parseFloat(itemQuantity) + parseFloat(PdtDiscount));
                                        if (effectRate > 0) {
                                            Ext.getCmp('fpot_effectiverate').setValue(effectRate);
                                        } else {
                                            Ext.Msg.alert("Notification.", 'Invalid effective rate');
                                        }

                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 1.00,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'textarea',
                                    fieldLabel: 'Notes',
                                    id: 'fpot_notes',
                                    name: 'n[fpot_notes]',
                                    anchor: '98%',
                                    tabIndex: 509
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .20,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'checkbox',
                                    id: 'po_item_gift',
                                    name: 'po_item_gift',
                                    //checked: true,
                                    inputValue: 1,
                                    tabIndex: 506,
                                    fieldLabel: 'Gift',
                                    listeners: {
                                        check: function (e, c) {
                                            if (c == true) {
                                                Ext.getCmp('fpot_giftname').enable();
                                                Ext.getCmp('fpot_giftqty').enable();
                                                Ext.getCmp('fpot_giftname').allowBlank = false;
                                                Ext.getCmp('fpot_giftqty').allowBlank = false;
                                            } else {
                                                Ext.getCmp('fpot_giftname').reset();
                                                Ext.getCmp('fpot_giftqty').reset();
                                                Ext.getCmp('fpot_giftname').disable();
                                                Ext.getCmp('fpot_giftqty').disable();
                                                Ext.getCmp('fpot_giftname').allowBlank = true;
                                                Ext.getCmp('fpot_giftqty').allowBlank = true;
                                            }
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.6,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Gift Name',
                                    id: 'fpot_giftname',
                                    name: 'n[fpot_giftname]',
                                    //disabled: true,
                                    anchor: '98%',
                                    tabIndex: 507
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.2,
                            frame: false,
                            border: false,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Gift Quantity',
                                    id: 'fpot_giftqty',
                                    name: 'n[fpot_giftqty]',
                                    //disabled: true,
                                    anchor: '98%',
                                    tabIndex: 508
                                }]
                        }
                    ]
                }],
            buttonAlign: 'right',
            buttons: [{
                    text: "Cancel",
                    tabIndex: 522,
                    cls: 'left-right-buttons',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    id: 'podetCancelBtn',
                    hidden: true,
                    handler: function () {
                        Ext.getCmp('itemPOForm').getForm().reset();
                    }
                },
                {
                    text: "Edit",
                    cls: 'left-right-buttons',
                    id: 'podetEditBtn',
                    icon: IMAGE_BASE_PATH + '/default/icons/mem_edit.png',
                    tabIndex: 521,
                    hidden: true,
                    handler: function () {
                        var ID = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0].data.stit_ID;
                        Ext.getCmp('fpot_itemmrp').setReadOnly(false);
                        Ext.getCmp('fpot_itemoffrqty').setReadOnly(false);
                        Ext.getCmp('fpot_itemaddidisc').setReadOnly(false);
                        Ext.getCmp('fpot_itemqty').setReadOnly(false);
                        Ext.getCmp('fpot_itemoffrrate').setReadOnly(false);
                        Ext.getCmp('fpot_giftname').setReadOnly(false);
                        Ext.getCmp('fpot_giftqty').setReadOnly(false);
                        Ext.getCmp('fpot_notes').setReadOnly(false);
                        Ext.getCmp('po_item_gift').setReadOnly(false);
                        Ext.getCmp('podetEditBtn').hide();
                        Ext.getCmp('podetSaveBtn').show();
                        Ext.getCmp('podetCancelBtn').show();
                    }
                }, /*     <?php if (user_access("finascop_purchase_order", "saveItemPODetails")) { ?> */ {
                    text: "Save",
                    tabIndex: 521,
                    cls: 'left-right-buttons',
                    id: 'podetSaveBtn',
                    hidden: true,
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        var itemId = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0].data.stit_ID;
                        var itemName = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0].data.stit_SKU;
                        saveItemPoDetail(customerId, itemId, itemName);
                    }
                } /*<?php } ?> */],
            listeners: {
                enable: function () {
                    if (Ext.getCmp('po_item_gift').getValue() == false)
                    {
                        Ext.getCmp('fpot_giftname').disable();
                        Ext.getCmp('fpot_giftqty').disable();
                    }
                }
            }


        });
        return itemPOFormspanel;
    };
    var saveItemPoDetail = function (customerId, itemId, itemName) {
        Application.Finascop_Purchase_Order.Cache.customerId = customerId;
        Application.Finascop_Purchase_Order.Cache.itemId = itemId;
        Application.Finascop_Purchase_Order.Cache.itemName = itemName;
        var form_data = {
            itemId: itemId,
            itemName: itemName,
            uid: Application.Finascop_Purchase_Order.Cache.UID,
            form: Ext.getCmp('itemPOForm').getForm().getValues()
        };
        var params = {
            action: 'Create PO',
            module: 'Finascop_Purchase_Order',
            op: 'saveItemPODetails',
            extrainfo: 'Save item details in PO',
            id: customerId
        };
        APICall(params, Application.Finascop_Purchase_Order.saveItemPoDetails, form_data);
    };
    var itemPodetails = function (customerId) {
        return new Ext.Panel({
            frame: false,
            border: true,
            title: 'PO detailas of item',
            height: 325,
            autoScroll: true,
            id: 'CategoryparentPanel',
            items: [itemPOForms(customerId)]
        })
    };
    var itemPaymentDetails = function (customerId) {
        return new Ext.Panel({
            region: 'south',
            frame: false,
            border: true,
            autoScroll: true,
            id: 'VendorPaymentDetailsPanel',
            height: 90,
            items: [
                {
                    layout: 'column',
                    hideBorders: true,
                    border: false,
                    style: 'border-top-color"#ffffff"',
                    defaults: {style: 'margin:10px 0 10px 0'},
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.5,
                            hideBorders: true,
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'po_payment_terms',
                                    forceSelection: true,
                                    fieldLabel: 'Payment Terms',
                                    emptyText: 'Payment Terms',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 514,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: 'Immediate', ptname: 'Immediate'}, {ptid: 'Net 7', ptname: 'Net 7'}, {ptid: 'Net 15', ptname: 'Net 15'}, {ptid: 'Net 30', ptname: 'Net 30'},
                                            {ptid: 'Net 60', ptname: 'Net 60'},
                                            {ptid: 'Custom', ptname: 'Custom'}]
                                    }),
                                    listeners: {
                                        select: function (combo, record) {
                                            var type = record.data.ptid;
                                            if (type == 'Custom') {
                                                Ext.getCmp('po_payment_value').show();
                                            } else {
                                                Ext.getCmp('po_payment_value').hide();
                                            }
                                        }

                                    }
                                }, {
                                    fieldLabel: 'Credit Days',
                                    xtype: 'numberfield',
                                    id: 'po_payment_value',
                                    name: 'po_payment_value',
                                    hidden: true,
                                    anchor: '98%',
                                    tabIndex: 515,
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.5,
                            hideBorders: true,
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'fpo_validityType',
                                    forceSelection: true,
                                    fieldLabel: 'PO Validity',
                                    emptyText: 'PO Validity',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 516,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: '15', ptname: '15 Days'},
                                            {ptid: '30', ptname: '30 Days'}, {ptid: '60', ptname: '60 Days'}, {ptid: '90', ptname: '90 Days'}]
                                    })
                                }
                            ]
                        }
                    ]
                }
            ]
        });
    };
    var PurchaseOrderEntryGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getPurchaseOrderData',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpo_id',
                root: 'data'
            }, ['fpo_id', 'fpod_itemqty', 'fpod_itemname', 'fpo_poNumber', 'fpo_poDate', 'fpo_paymentTerms', 'fpo_paymentValue', 'fpo_poDeliveryDate', 'fpo_poDeliveryType', 'UserName', 'fpo_vendorName', 'fpo_validDate', 'fpo_Active', 'fpo_potype']),
            sortInfo: {
                field: 'fpo_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'DESC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    //Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getView().refresh();
                    Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _catStore;
    };
    var PurchaseOrderAdhocGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getPurchaseOrderAdhoc',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'adhoc_uniqueid',
                root: 'data'
            }, ['adhoc_uniqueid', 'adhoc_name', 'adhoc_createdon', 'vendorName', 'fpo_potype']),
            sortInfo: {
                field: 'adhoc_createdon',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    //Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getView().refresh();
                    Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getSelectionModel().selectRow(0);
                }
            }
        });
        return _catStore;
    };
    var poItemPanel = function () {
        var _poItemPanel = new Ext.Panel({
            layout: 'fit',
            id: 'poItemViewPanel',
            border: false,
            hideBorders: true,
            autoHeight: true,
            tpl: new Ext.XTemplate(
                    '<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">PO Number </th><td> {fpo_poNumber} </td></tr>',
                    '<tr><th width="40%">PO Date </th><td> {fpo_poDate} </td></tr>',
                    '<tr><th width="40%">PO OrderBy </th><td> {UserName} </td></tr>',
                    '<tr><th width="40%">Payment Terms </th><td> {fpo_paymentTerms} </td></tr>',
                    '<tr><th width="40%">Payment Value </th><td> {fpo_paymentValue} </td></tr>',
                    '<tr><th width="40%">Delivery Date </th><td> {fpo_poDeliveryDate} </td></tr>',
                    '<tr><th width="40%">Delivery Type </th><td> {fpo_poDeliveryType} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
        return _poItemPanel;
    };
    var PurchaseOrderDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            id: 'xtemplateFinascopPurchaseOrderDataviewPoorderdetails',
            border: false,
            hideBorders: true,
            autoHeight: true,
            tpl: new Ext.XTemplate(
                    '<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tpl for=".">',
                    '<tr>',
                    '<th>Name</span></th><td>{fpod_itemname}</td>',
                    '</tr>',
                    '<tr>',
                    '<th>Quantity</th><td>{fpod_itemqty}</td>',
                    '</tr>',
                    '<tr>',
                    '<th>MRP</th><td>{fpod_itemmrp}</td>',
                    '</tr>',
                    '</tr>',
                    '<tr>',
                    '<th>Offer Qty</th><td>{fpod_itemoffrqty}</td>',
                    '</tr>',
                    '</tr>',
                    '<tr>',
                    '<th>Offer Rate</th><td>{fpod_itemoffrrate}</td>',
                    '</tr>',
                    '</tr>',
                    '<tr>',
                    '<th>Effective Rate</th><td>{fpod_effectiverate}</td>',
                    '</tr>',
                    '<tr>',
                    '<th colspan="4">',
                    '<hr></th></tr>',
                    '</tpl>',
                    '</table>',
                    '</div>'
                    )
                    /*tpl: new Ext.XTemplate(
                     '<div class="details-outer">',
                     '<table border="0" width="100%" class="details_view_table">',
                     '<tr><th>Name</span></th><th>Quantity</th><th>MRP</th><th>Offer Qty</th><th>Offer Rate</th><th>Effective Rate</th></tr>',
                     '<tpl for=".">',
                     '<tr>',
                     '<td>{fpod_itemname}</td><td>{fpod_itemqty}</td><td>{fpod_itemmrp}</td><td>{fpod_itemoffrqty}</td><td>{fpod_itemoffrrate}</td><td>{fpod_effectiverate}</td>',
                     '</tr>',
                     '<tr>',
                     '</tpl>',
                     '</table>',
                     '</div>'
                     )*/
        });
    };
    var gridSelectionChangedcat = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getSelectionModel().getSelections()[0].data.fpo_id;
            //Application.Finascop_Purchase_Order.poViewMode(ID);
        }
    };
    var gridSelectionChangedadhoc = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getSelectionModel().getSelections()[0].data.fpo_id;
        }
    }

// Store 
    var spGridStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getPOdetailsData',
            fields: ['customerId', 'stpa_Fname', 'stpa_Lname', 'stpa_GSTIN', 'stpa_Address', 'stpa_City', 'stpa_PINCODE', 'st_name', 'dst_Name', 'st_id', 'dst_Id'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: false

        })
        return store;
    };
    // Panel inside ResultWindow
    var AddPOPanel = function () {

        var store = spGridStore();
        var panel = new Ext.form.FormPanel({
            id: 'formpanelFinascopPurchaseOrderSearchpo',
            autoHeight: true,
            frame: true,
            border: false,
            layout: 'column',
            items: [
                {
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
                                    fieldLabel: 'Vendor Name',
                                    xtype: 'textfield',
                                    id: 'textfieldFinascopPurchaseOrderDataentryPoname',
                                    labelStyle: 'width:125px',
                                    tabIndex: 600,
                                    anchor: '88%',
                                    editable: true,
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);
                                        }
                                    }
                                }]
                        },
                        {
                            xtype: 'button',
                            text: 'Search',
                            tabIndex: 601,
                            iconCls: 'finascop_search_btn',
                            style: 'margin-left:10px;margin-top:10px;',
                            handler: function () {
                                var i = Ext.getCmp('textfieldFinascopPurchaseOrderDataentryPoname').getValue();
                                if (i != '') {
                                    store.baseParams = {
                                        POname: i
                                    };
                                    store.load();
                                }
                            }
                        }
                    ]

                },
                {
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    items: [
                        {
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: new Ext.grid.GridPanel({
                                        store: store,
                                        id: 'gridpanelFinascopPurchaseOrderDataviewPodetails',
                                        region: 'center',
                                        frame: true,
                                        border: false,
                                        layout: 'fit',
                                        height: winsize.height * 0.6,
                                        loadMask: true,
                                        columns: [
                                            {
                                                header: 'Name',
                                                sortable: true,
                                                dataIndex: 'stpa_Fname',
                                                width: 175
                                            },
                                            {
                                                header: 'GST No ',
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
                                                header: 'State/Province',
                                                sortable: true,
                                                dataIndex: 'st_name',
                                                width: 175
                                            },
                                            {
                                                header: 'District',
                                                sortable: true,
                                                dataIndex: 'dst_Name',
                                                width: 175
                                            }, {
                                                xtype: 'actioncolumn',
                                                hideable: false,
                                                sortable: false,
                                                items: [{
                                                        iconCls: 'my-icon96',
                                                        tooltip: 'Create PO',
                                                        handler: function (grid, rowIndex, colIndex) {
                                                            var record = grid.store.getAt(rowIndex);
                                                            Application.Finascop_Purchase_Order.createPOforVendor(record.get('customerId'), record.get('stpa_Fname'));
                                                        }
                                                    }]
                                            }],
                                        viewConfig: {
                                            forceFit: true
                                        },
                                        bbar: new Ext.PagingToolbar({
                                            pageSize: recs_per_page,
                                            store: store,
                                            displayInfo: true,
                                            //plugins: [customerGrid_filter],
                                            displayMsg: 'Displaying items {0} - {1} of {2}',
                                            emptyMsg: "No records to display"
                                        }),
                                        stripeRows: true,
                                        sm: new Ext.grid.RowSelectionModel({
                                            singleSelected: true
                                        })
                                    })

                                }]
                        }
                    ]
                }

            ]
        });
        return panel;
    };
    // Result Window


    var VendorOperationsWindow = function () {
        var po_add_form = AddPOPanel();
        var resultWindow = new Ext.Window({
            id: 'windowFinascopPurchaseOrderDataentryAddpo',
            title: 'Search Vendor',
            iconCls: 'vender-items',
            shadow: false,
            height: 400,
            width: 800,
            layout: 'fit',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [po_add_form],
            buttons: [
                {
                    text: 'Close',
                    tabIndex: 602,
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    handler: function () {
                        Ext.getCmp('windowFinascopPurchaseOrderDataentryAddpo').close();
                        Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getStore().reload();
                    }
                }
            ]

        });
        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var PurchaseOrderEntryGrid = function () {
        var _PoGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fpo_poNumber'
                }, {
                    type: 'string',
                    dataIndex: 'fpo_poDate'
                }, {
                    type: 'string',
                    dataIndex: 'fpo_validDate'
                }, {
                    type: 'string',
                    dataIndex: 'fpo_vendorName'
                }, {
                    type: 'list',
                    value: ['Active'],
                    dataIndex: 'fpo_Active',
                    options: ['Active', 'Inactive'],
                    phpMode: true
                }]
        });
        _PoGridFilter.remote = true;
        _PoGridFilter.autoReload = true;
        var _gridStore = PurchaseOrderEntryGridStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata',
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            iconCls: 'my-icon444',
            store: _gridStore,
            loadMask: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            tbar: [{
                    text: 'Add PO',
                    tooltip: 'Add PO',
                    hidden: true,
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        //VendorOperationsWindow()
                        Application.Finascop_Purchase_Order.newPOWindowForVendor();
                    }
                }],
            plugins: [new Ext.ux.grid.GroupSummary(), _PoGridFilter],
            columns: [
                {
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number',
                    hideable: false
                },
                {
                    header: 'PO Date',
                    sortable: true,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor',
                    hideable: false
                },
                {
                    header: 'PO Valid',
                    sortable: true,
                    dataIndex: 'fpo_validDate',
                    tooltip: 'PO Valid till',
                    hideable: false
                },
                {
                    header: 'PO Status',
                    sortable: true,
                    dataIndex: 'fpo_Active',
                    tooltip: 'PO Status',
                    hideable: false
                }, {
                    header: 'PO Type',
                    sortable: true,
                    dataIndex: 'fpo_potype',
                    tooltip: 'PO Type',
                    hideable: false
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
                            purchaseorderActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 hideable: false,
                 sortable: false,
                 items: [{
                 iconCls: 'viewfile',
                 tooltip: 'View',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Application.Finascop_Purchase_Order.viewPODetails(record.get('fpo_id'));
                 }
                 }, {
                 iconCls: "left-right-buttons",
                 icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Application.Finascop_Purchase_Order.printPO(record.get('fpo_id'));
                 }
                 }]
                 }*/
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedcat
                }
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('fpo_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.Finascop_Purchase_Order.Cache.fpo_id = ID;
                        Application.Finascop_Purchase_Order.poViewMode(ID);
                    }
                },
                resize: onGridResize
            }

        });
        return _gridPanel;
    };
    var purchaseorderActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View",
                handler: function () {
                    var fpo_id = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getSelectionModel().getSelections()[0].data.fpo_id;
                    Application.Finascop_Purchase_Order.viewPODetails(fpo_id);
                }
            }, {
                text: "Print",
                handler: function () {
                    var fpo_id = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getSelectionModel().getSelections()[0].data.fpo_id;
                    Application.Finascop_Purchase_Order.printPO(fpo_id);
                }
            }]
    });
    var PurchaseOrderAdhocGrid = function () {

        var _PoGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'adhoc_name'
                }, {
                    type: 'string',
                    dataIndex: 'adhoc_createdon'
                }, {
                    type: 'string',
                    dataIndex: 'vendorName'
                }]
        });
        _PoGridFilter.remote = true;
        _PoGridFilter.autoReload = true;
        var _gridStore = PurchaseOrderAdhocGridStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc',
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            iconCls: 'my-icon444',
            store: _gridStore,
            loadMask: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            tbar: [{
                    text: 'Create Provisional PO',
                    tooltip: 'Create Provisional PO',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        //VendorOperationsWindow()
                        Application.Finascop_Purchase_Order.newPOWindowForVendor();
                    }
                }],
            plugins: [new Ext.ux.grid.GroupSummary(), _PoGridFilter],
            columns: [
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'adhoc_name',
                    tooltip: 'Name',
                    hideable: false
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'adhoc_createdon',
                    tooltip: 'PO Date'
                }, {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'vendorName',
                    tooltip: 'Vendor',
                    hideable: false
                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'fpo_potype',
                    tooltip: 'Type',
                    hideable: false
                },
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
                            provisionalpoActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 hideable: false,
                 sortable: false,
                 items: [{
                 iconCls: 'approve-reject',
                 tooltip: 'Create PO',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var t = new Date();
                 var t_stamp = t.format("YmdHis");
                 Application.Finascop_Purchase_Order.editPOWindowForVendor(record.get('adhoc_uniqueid'));
                 //                                Ext.Ajax.request({
                 //                                    url: modURL + '&op=checkAssignPO',
                 //                                    params: {
                 //                                        apikey: _SESSION.apikey,
                 //                                        tstamp: t_stamp,
                 //                                        uniqueid: record.get('fpot_uniqueid')
                 //                                    },
                 //                                    method: 'POST',
                 //                                    success: function (res) {
                 //                                        var tmp = Ext.decode(res.responseText);
                 //                                        if (tmp.assigneddby == _SESSION.UserId) {
                 //                                            
                 //                                        } else {
                 //                                            Ext.MessageBox.alert('Error', 'You are not allowed to edit this PO.');
                 //                                        }
                 //
                 //                                    }
                 //                                });
                 }
                 }, {
                 tooltip: 'DELETE',
                 iconCls: 'wrong',
                 handler: function () {
                 var adhoc_uniqueid = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getSelectionModel().getSelections()[0].data.adhoc_uniqueid;
                 Application.Finascop_Purchase_Order.deleteFeedback(adhoc_uniqueid);
                 }
                 }
                 ]
                 }*/
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedadhoc
                }
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true
        });
        return _gridPanel;
    };
    var provisionalpoActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Create",
                handler: function () {
                    var adhoc_uniqueid = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getSelectionModel().getSelections()[0].data.adhoc_uniqueid;
                    Application.Finascop_Purchase_Order.editPOWindowForVendor(adhoc_uniqueid);
                }
            }, {
                text: 'Delete',
                handler: function () {
                    var adhoc_uniqueid = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getSelectionModel().getSelections()[0].data.adhoc_uniqueid;
                    Application.Finascop_Purchase_Order.deleteFeedback(adhoc_uniqueid);
                }
            }]
    });
    var adpurchaseOrderPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Provisional PO',
            iconCls: 'finascop_invoice',
            items: [PurchaseOrderAdhocGrid()]
        });
        return panel;
    };
    var purchaseMasterOrderPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Purchase Orders',
            //iconCls: 'finascop_invoice',
            items: [PurchaseOrderEntryGrid()/*, new Ext.Panel({
             title: 'Purchase Item Details',
             frame: false,
             border: true,
             region: 'east',
             cls: 'left_side_panel',
             width: winsize.width * 0.4,
             autoScroll: true,
             id: 'panelFinascopPurchaseOrderDataviewPoorderdetails',
             height: winsize.height * 0.6,
             items: [PurchaseOrderDetailsView()]
             })*/]
        });
        return panel;
    };
    var vendorStore = new Ext.data.JsonStore({
        fields: ['stpa_id', 'stpa_Fname'],
        url: modURL + '&op=getVendorName',
        autoLoad: true,
        method: 'post',
    });
    var centralStore = new Ext.data.JsonStore({
        fields: ['br_ID', 'br_Name'],
        url: modURL + '&op=getCentralStores',
        autoLoad: true,
        method: 'post',
    });
    var vendorItemStore = new Ext.data.JsonStore({
        autoLoad: false,
        url: modURL + '&op=vendorItemStore',
        method: 'post',
        fields: ['stit_ID', 'stit_SKU']
    });
    var pmtTermsStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=getPaymentTerms',
        method: 'post',
        fields: ['ptc_id', 'ptc_name'],
        root: 'data'
    });
    var packTypeStore = new Ext.data.JsonStore({
        url: modURL + '&op=getPTStore',
        fields: ['package_type_id', 'package_type_name'],
        totalProperty: 'totalCount',
        root: 'data',
        autoload: true,
        remoteFilter: true,
        listeners: {
            beforeload: function () {
                this.baseParams.stdpckl11 = '';
                this.baseParams.stdpckl12 = '';
                this.baseParams.stdpckl21 = '';
                this.baseParams.stdpckl31 = '';
                this.baseParams.stdpckl41 = '';
            }
        }
    });
    var vendornewPOCreateForm = function () {
        var purUni = '';
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'newPOFormForVendor',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            xtype: 'hidden', id: 'uniqueId', name: 'uniqueId'
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Name',
                                    hideLabel: true,
                                    emptyText: 'Name',
                                    id: 'fpot_adhocname',
                                    name: 'fpot_adhocname',
                                    labelAlign: 'left',
                                    anchor: '97%',
                                    hidden: true,
                                    tabIndex: 190
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'left',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Vendor',
                                    hideLabel: true,
                                    emptyText: 'Choose Vendor',
                                    id: 'pe_party',
                                    name: 'pe_party',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'remote',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '97%',
                                    store: vendorStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'stpa_Fname',
                                    valueField: 'stpa_id',
                                    hiddenName: 'pe_party',
                                    tabIndex: 200,
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1000);
                                        },
                                        select: function () {
                                            var pe_party = Ext.getCmp('pe_party').getValue();
                                            Ext.getCmp('pe_partyItems').getStore().clearData();
                                            Ext.getCmp('pe_partyItems').reset();
                                            Ext.getCmp('pe_partyItems').getStore().load({
                                                params: {
                                                    stpa_id: pe_party
                                                }
                                            });
                                            Ext.getCmp('po_payment_terms').getStore().load({
                                                params: {
                                                    stpa_id: pe_party
                                                }
                                            });
                                            Ext.Ajax.request({
                                                url: modURL + '&op=vendorDetails',
                                                method: 'POST',
                                                params: {pe_party: pe_party},
                                                success: function (res) {
                                                    var tmp = Ext.decode(res.responseText);
                                                    Ext.getCmp('vendorDetails').show();
                                                    Ext.getCmp('vendorCity').setValue(tmp.stpa_City);
                                                    Ext.getCmp('vendorPinc').setValue(tmp.stpa_PINCODE);
                                                    Ext.getCmp('vendorMobile').setValue(tmp.stpa_MobileNo);
                                                    Ext.getCmp('vendorGST').setValue(tmp.stpa_GSTIN);
                                                    Ext.getCmp('vendorVisfreq').setValue(tmp.stpa_visitFrequency);
                                                    Ext.getCmp('vendorOncall').setValue(tmp.stpa_oncallDelivery);
                                                },
                                                failure: function () {
                                                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                }
                                            });
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Number',
                                    id: 'po_number',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_number]',
                                    anchor: '97%',
                                    value: 'Not Genereated',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Date',
                                    id: 'po_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    value: 'Not Genereated',
                                    anchor: '97%',
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ordered By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]',
                                    anchor: '97%',
                                    value: _SESSION.FirstName,
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Ship To',
                                    emptyText: 'Ship To',
                                    id: 'po_billing_to',
                                    name: 'po_billing_to',
                                    labelStyle: mandatory_label,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '99%',
                                    store: centralStore,
                                    allowBlank: false,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    hiddenName: 'po_billing_to',
                                    tabIndex: 210
                                }]
                        }]
                }, {
                    xtype: 'fieldset',
                    title: 'Vendor Details',
                    collapsible: true,
                    columnWidth: 1,
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    id: 'vendorDetails',
                    hidden: true,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;,margin-left: 15px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'City',
                                    id: 'vendorCity',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Pincode',
                                    id: 'vendorPinc',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Mobile',
                                    id: 'vendorMobile',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Tax',
                                    id: 'vendorGST',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Visit Frequency',
                                    id: 'vendorVisfreq',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'On Call Delivery',
                                    id: 'vendorOncall',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }]
                }, {
                    xtype: 'fieldset',
                    layout: 'column',
                    collapsible: true,
                    title: 'Item Details',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'itemHistory',
                    hidden: true,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;,margin-left: 15px;',
                            columnWidth: .30,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Unit',
                                    id: 'itemUnitFormdet',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;,margin-left: 15px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'HSN',
                                    id: 'itemHSN',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Tax',
                                    id: 'itemGST',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Last Purchase',
                                    id: 'itemLP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'LP Price',
                                    tooltip: 'Lowest Purchase Price',
                                    id: 'itemLPP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Optimum Qty',
                                    id: 'itemOptimumQty',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Min Qty',
                                    id: 'itemMinQty',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Item Count',
                                    id: 'itemCSCount',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }]
                }, {
                    layout: 'column',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: '',
                                    id: 'itemSmaalStockUnit',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .30,
                                    labelAlign: 'top',
                                    hideLabel: 'true',
                                    hidden: true,
                                    items: [{
                                            xtype: 'radio',
                                            name: 'isStandardPacking',
                                            id: 'isStandardPacking',
                                            inputValue: 1,
                                            boxLabel: 'Apply Standard Packing',
                                            listeners: {
                                                check: function (field, new_value) {
                                                    if (new_value == true) {
                                                        Ext.getCmp('pe_partyItems').reset();
                                                        Ext.getCmp('fpot_itemmrp').reset();
                                                        Ext.getCmp('fpot_itemqty').reset();
                                                        Ext.getCmp('fpot_itemoffrqty').reset();
                                                        Ext.getCmp('fpot_itemoffrrate').reset();
                                                        Ext.getCmp('fpot_itemaddidisc').reset();
                                                        Ext.getCmp('fpot_amount').reset();
                                                        Ext.getCmp('fpot_netamount').reset();
                                                        Ext.getCmp('po_idiscountcalculs').reset();
                                                        Ext.getCmp('fpot_giftqty').reset();
                                                        Ext.getCmp('fpot_giftname').reset();
                                                        Ext.getCmp('fpot_notes').reset();
                                                        Ext.getCmp('po_gstType').reset();
                                                        Ext.getCmp('fpot_itemoffrrateExcT').reset();
                                                        Ext.getCmp('itemSmaalStockUnit').reset();
                                                        Application.Finascop_Purchase_Order.Cache.isStandardPacking = 1;
                                                        Ext.getCmp('isNotStandardPacking').reset();
                                                    }
                                                }
                                            }

                                        }]
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .35,
                                    labelAlign: 'top',
                                    hideLabel: 'true',
                                    items: [
                                    ]
                                }, {
                                    hidden: true,
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .30,
                                    labelAlign: 'top',
                                    hideLabel: 'true',
                                    items: [{
                                            xtype: 'radio',
                                            name: 'isNotStandardPacking',
                                            id: 'isNotStandardPacking',
                                            inputValue: 2,
                                            boxLabel: 'Do Not Apply Standard Packing',
                                            listeners: {
                                                check: function (field, new_value) {
                                                    if (new_value == true) {
                                                        Ext.getCmp('pe_partyItems').reset();
                                                        Ext.getCmp('fpot_itemmrp').reset();
                                                        Ext.getCmp('fpot_itemqty').reset();
                                                        Ext.getCmp('fpot_itemoffrqty').reset();
                                                        Ext.getCmp('fpot_itemoffrrate').reset();
                                                        Ext.getCmp('fpot_itemaddidisc').reset();
                                                        Ext.getCmp('fpot_amount').reset();
                                                        Ext.getCmp('fpot_netamount').reset();
                                                        Ext.getCmp('po_idiscountcalculs').reset();
                                                        Ext.getCmp('fpot_giftqty').reset();
                                                        Ext.getCmp('fpot_giftname').reset();
                                                        Ext.getCmp('fpot_notes').reset();
                                                        Ext.getCmp('po_gstType').reset();
                                                        Ext.getCmp('fpot_itemoffrrateExcT').reset();
                                                        Ext.getCmp('itemSmaalStockUnit').reset();
                                                        Application.Finascop_Purchase_Order.Cache.isStandardPacking = 2;
                                                        Ext.getCmp('isStandardPacking').reset();
                                                    }
                                                }
                                            }

                                        }]
                                }]

                        }, {
                            layout: 'column',
                            style: 'margin-bottom:3px;',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.25,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Items',
                                            id: 'pe_partyItems',
                                            name: 'pe_partyItems',
                                            labelStyle: mandatory_label,
                                            allowBlank: true,
                                            labelAlign: 'top',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: vendorItemStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'stit_SKU',
                                            valueField: 'stit_ID',
                                            hiddenName: 'pe_partyItems',
                                            tabIndex: 220,
                                            listeners: {
                                                select: function () {
                                                    Ext.getCmp('fpot_poValue').focus(false, 100);
                                                    Ext.getCmp('fpot_itemmrp').reset();
                                                    Ext.getCmp('fpot_itemqty').reset();
                                                    Ext.getCmp('fpot_itemoffrqty').reset();
                                                    Ext.getCmp('fpot_itemoffrrate').reset();
                                                    Ext.getCmp('fpot_itemaddidisc').reset();
                                                    Ext.getCmp('fpot_amount').reset();
                                                    Ext.getCmp('fpot_netamount').reset();
                                                    Ext.getCmp('po_idiscountcalculs').reset();
                                                    Ext.getCmp('fpot_giftqty').reset();
                                                    Ext.getCmp('fpot_giftname').reset();
                                                    Ext.getCmp('fpot_notes').reset();
                                                    Ext.getCmp('po_gstType').reset();
                                                    Ext.getCmp('fpot_itemoffrrateExcT').reset();
                                                    Ext.getCmp('itemUnitForm').getStore().clearData();
                                                    var pe_party = Ext.getCmp('pe_party').getValue();
                                                    var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
                                                    var po_billing_to = Ext.getCmp('po_billing_to').getValue();
                                                    //if (po_billing_to > 0 && Application.Finascop_Purchase_Order.Cache.isStandardPacking > 0) {
                                                    if (po_billing_to > 0 && pe_party > 0) {
                                                        Ext.Ajax.request({
                                                            url: modURL + '&op=itemHistory',
                                                            method: 'POST',
                                                            params: {pe_partyItems: pe_partyItems, po_billing_to: po_billing_to, isStandardPacking: Application.Finascop_Purchase_Order.Cache.isStandardPacking},
                                                            success: function (res) {
                                                                var tmp = Ext.decode(res.responseText);
                                                                if (tmp.csb_package_type_name != '') {
                                                                    purUni = ' - ' + tmp.csb_package_type_name;
                                                                } else {
                                                                    purUni = '';
                                                                }
                                                                Ext.getCmp('itemHistory').show();
                                                                Ext.getCmp('itemHSN').setValue(tmp.stit_HSN_code);
                                                                Ext.getCmp('itemLP').setValue(tmp.last_mrp);
                                                                Ext.getCmp('itemGST').setValue(tmp.stit_GST);
                                                                Application.Finascop_Purchase_Order.Cache.stit_GST = tmp.stit_GST;
                                                                Application.Finascop_Purchase_Order.Cache.ds_nos_item = tmp.ds_nos;
                                                                Application.Finascop_Purchase_Order.Cache.cs_nos_item = tmp.cs_nos;
                                                                Application.Finascop_Purchase_Order.Cache.billingToPramidLevel = tmp.billingToPramidLevel;
                                                                Ext.getCmp('itemLPP').setValue(tmp.last_sp);
                                                                Ext.getCmp('itemOptimumQty').setValue(tmp.optiQty);
                                                                Ext.getCmp('itemCSCount').setValue(tmp.itemCSCount);
                                                                Ext.getCmp('itemMinQty').setValue(tmp.minQty);
                                                                Ext.getCmp('isRRPApplicable').setValue(tmp.isRRPApplicable);
                                                                //Ext.getCmp('itemUnit').setValue(tmp.csb_package_type_name);
                                                                //Ext.getCmp('itemUnitForm').setValue(tmp.itemUnitForm);
                                                                Ext.getCmp('itemUnitFormdet').setValue(tmp.itemUnitForm);
                                                                Ext.getCmp('itemSmaalStockUnit').setValue(tmp.itemSmaalStockUnit);
                                                                Application.Finascop_Purchase_Order.Cache.stit_fixedB2BRates = tmp.stit_fixedB2BRates;
                                                                Ext.getCmp('itemUnitForm').getStore().load({
                                                                    params: {
                                                                        isMedicine: tmp.isMedicine,
                                                                        stdpckl11: tmp.stdpckl11_package_type_id,
                                                                        stdpckl12: tmp.stdpckl12_package_type_id,
                                                                        stdpckl21: tmp.stdpckl21_package_type_id,
                                                                        stdpckl31: tmp.stdpckl31_package_type_id,
                                                                        stdpckl41: tmp.stdpckl41_package_type_id
                                                                    }
                                                                });
                                                                //Ext.getCmp('fpot_itemoffrrate').focus(true, 100);
                                                            },
                                                            failure: function () {
                                                                Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                            }
                                                        });
                                                    } else {
                                                        Ext.MessageBox.alert('Notification', 'Choose a Vendor and Ship To');
                                                        Ext.getCmp('pe_partyItems').getStore().clearData();
                                                    }

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Offr.Rate',
                                            align: 'right',
                                            id: 'fpot_itemoffrrate',
                                            name: 'n[fpot_itemoffrrate]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 230,
                                            value: 0,
                                            listeners: {
                                                change: function (e) {
                                                    var itemmrp = Ext.getCmp('fpot_itemmrp').getValue();
                                                    var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.09,
                                    labelAlign: 'top',
                                    items: [{
                                            id: 'isRRPApplicable',
                                            xtype: 'hidden',
                                            name: 'isRRPApplicable'
                                        }, {
                                            xtype: 'numberfield',
                                            fieldLabel: 'MRP/RRP(Inc.)',
                                            id: 'fpot_itemmrp',
                                            align: 'right',
                                            name: 'n[fpot_itemmrp]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 240,
                                            listeners: {
                                                change: function (e) {
                                                    var itemmrp = Ext.getCmp('fpot_itemmrp').getValue();
                                                    var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    if (itemmrp > 0) {
                                                        if (fpot_itemoffrrate < itemmrp) {
                                                            var fpot_itemqty = Ext.getCmp('fpot_itemqty').getValue();
                                                            var amount = fpot_itemqty * fpot_itemoffrrate;
                                                            Ext.getCmp('fpot_amount').setValue(amount);
                                                            console.log('e', e);
                                                        } else {
                                                            Ext.getCmp('fpot_itemoffrrate').reset();
                                                            Ext.MessageBox.alert("Notification", "Offer Rate should be less than MRP");
                                                        }
                                                    }


                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Units',
                                            id: 'itemUnitForm',
                                            name: 'itemUnitForm',
                                            labelStyle: mandatory_label,
                                            allowBlank: true,
                                            labelAlign: 'top',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: packTypeStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'package_type_name',
                                            valueField: 'package_type_id',
                                            hiddenName: 'itemUnitForm',
                                            tabIndex: 250, listeners: {
                                                select: function () {
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            mode: 'local',
                                            id: 'po_gstType',
                                            forceSelection: true,
                                            fieldLabel: 'Tax',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 260,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'Inclusive', name: 'Incl.'}, {id: 'Exclusive', name: 'Excl.'}]
                                            }), listeners: {
                                                select: function (combo, record) {
                                                    var offerRateET, offerRateIT;
                                                    var offerRate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    if (record.data.id == 'Inclusive') {
                                                        offerRateIT = offerRate;
                                                        offerRateET = (offerRate * 100) / (100 + parseFloat(Application.Finascop_Purchase_Order.Cache.stit_GST));
                                                    } else {
                                                        offerRateET = offerRate;
                                                        offerRateIT = offerRate + (offerRate * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);
                                                    }
                                                    offerRateET = Math.round((offerRateET + Number.EPSILON) * 100) / 100;
                                                    offerRateIT = Math.round((offerRateIT + Number.EPSILON) * 100) / 100;
                                                    Ext.getCmp('fpot_itemoffrrateExcT').setValue(offerRateET);
                                                    var fpot_itemoffrrate = offerRateIT;
                                                    var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
                                                    if (_SESSION.DEFAULT_RRP > 0) {

                                                    }
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.08,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Rate',
                                            align: 'right',
                                            id: 'fpot_itemoffrrateExcT',
                                            name: 'n[fpot_itemoffrrateExcT]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 270,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Qty',
                                            align: 'right',
                                            id: 'fpot_itemqty',
                                            name: 'n[fpot_itemqty]',
                                            allowNegative: false,
                                            allowDecimals: false,
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 280,
                                            listeners: {
                                                change: function () {
                                                    var fpot_itemqty = Ext.getCmp('fpot_itemqty').getValue();
                                                    if (Application.Finascop_Purchase_Order.Cache.billingToPramidLevel == 3 && Application.Finascop_Purchase_Order.Cache.isStandardPacking == 2) {
                                                        var isInteger = parseInt(fpot_itemqty) % parseInt(Application.Finascop_Purchase_Order.Cache.ds_nos_item);
                                                    } else if (Application.Finascop_Purchase_Order.Cache.billingToPramidLevel == 2 && Application.Finascop_Purchase_Order.Cache.isStandardPacking == 2) {
                                                        var isInteger = parseInt(fpot_itemqty) % parseInt(Application.Finascop_Purchase_Order.Cache.ds_nos_item * Application.Finascop_Purchase_Order.Cache.cs_nos_item);
                                                    } else {
                                                        var isInteger = parseInt(fpot_itemqty) % 1;
                                                    }
                                                    console.log('isInteger', isInteger);
                                                    if (isInteger == 0) {
                                                        var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                        var amount = fpot_itemqty * fpot_itemoffrrateExcT;
                                                        Ext.getCmp('fpot_amount').setValue(amount);
                                                    } else {
                                                        Ext.MessageBox.alert('Error', 'Enter a valid quantity.');
                                                    }

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.08,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Amount',
                                            align: 'right',
                                            id: 'fpot_amount',
                                            name: 'n[fpot_amount]',
                                            tabIndex: 290,
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.08,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            //fieldLabel: 'Product Discount (Nos)',
                                            fieldLabel: 'Product Discount',
                                            align: 'right',
                                            id: 'fpot_itemoffrqty',
                                            name: 'n[fpot_itemoffrqty]',
                                            anchor: '98%',
                                            tabIndex: 300,
                                            value: 0,
                                            listeners: {
                                                change: function () {
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.07,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            align: 'right',
                                            //fieldLabel: 'Addl Discounts (Rs)',
                                            fieldLabel: 'Discounts',
                                            id: 'fpot_itemaddidisc',
                                            name: 'n[fpot_itemaddidisc]',
                                            anchor: '98%',
                                            allowNegative: false,
                                            allowDecimals: false,
                                            value: 0,
                                            tabIndex: 310,
                                            listeners: {
                                                change: function () {
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            mode: 'local',
                                            id: 'po_idiscountcalculs',
                                            forceSelection: true,
                                            fieldLabel: 'Amt /Percent',
                                            emptyText: 'Amount / Percentage',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 320,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'Amount', name: 'Amount'}, {id: 'Percentage', name: 'Percentage'}]
                                            }), listeners: {
                                                select: function () {
                                                    var fpot_netamount, fpot_netRate, fpot_gstValue, fpot_itemoffrrateExcT, fpot_itemoffrrateIncTax;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    //var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var po_gstType = Ext.getCmp('po_gstType').getValue();
                                                    if (po_gstType == 'Inclusive') {
                                                        fpot_itemoffrrateIncTax = Ext.getCmp('fpot_itemoffrrate').getValue()
                                                        fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    } else {
                                                        fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                        fpot_itemoffrrateIncTax = fpot_itemoffrrateExcT * (100 + parseFloat(Application.Finascop_Purchase_Order.Cache.stit_GST)) / 100;
                                                        fpot_itemoffrrateIncTax = Math.round((fpot_itemoffrrateIncTax + Number.EPSILON) * 100) / 100;
                                                    }

                                                    var fpot_itemqty = Ext.getCmp('fpot_itemqty').getValue();
                                                    var amount = fpot_itemoffrrateIncTax * fpot_itemqty;
                                                    amount = Math.round((amount + Number.EPSILON) * 100) / 100;
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        //fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                        fpot_netamount = amount - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        //fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                        fpot_netamount = parseFloat(amount) - (parseFloat(amount) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_gstValue = (fpot_netamount * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);
                                                    console.log('fpot_gstValue', fpot_gstValue);
                                                    Application.Finascop_Purchase_Order.Cache.fpot_gstValue = fpot_gstValue;
                                                    /*fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                     console.log('fpot_netRate', fpot_netRate);
                                                     fpot_netamount = fpot_netRate * fpot_itemoffrrateIncTax;
                                                     console.log('fpot_netamount1', fpot_netamount);
                                                     fpot_gstValue = (fpot_netamount * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);
                                                     console.log('fpot_gstValue', fpot_gstValue);
                                                     Application.Finascop_Purchase_Order.Cache.fpot_gstValue = fpot_gstValue;
                                                     fpot_netamount = fpot_netamount + (fpot_netamount * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);*/
                                                    console.log('fpot_netamount2', fpot_netamount);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount3', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Net Amt',
                                            id: 'fpot_netamount',
                                            align: 'right',
                                            name: 'n[fpot_netamount]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 330,
                                            readOnly: true,
                                            listeners: {
                                                focus: function () {
                                                    var fpot_netamount, fpot_netRate, fpot_gstValue, fpot_itemoffrrateExcT, fpot_itemoffrrateIncTax;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    //var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var po_gstType = Ext.getCmp('po_gstType').getValue();
                                                    if (po_gstType == 'Inclusive') {
                                                        fpot_itemoffrrateIncTax = Ext.getCmp('fpot_itemoffrrate').getValue()
                                                        fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    } else {
                                                        fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                        fpot_itemoffrrateIncTax = fpot_itemoffrrateExcT * (100 + parseFloat(Application.Finascop_Purchase_Order.Cache.stit_GST)) / 100;
                                                        fpot_itemoffrrateIncTax = Math.round((fpot_itemoffrrateIncTax + Number.EPSILON) * 100) / 100;
                                                    }

                                                    var fpot_itemqty = Ext.getCmp('fpot_itemqty').getValue();
                                                    var amount = fpot_itemoffrrateIncTax * fpot_itemqty;
                                                    amount = Math.round((amount + Number.EPSILON) * 100) / 100;
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        //fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                        fpot_netamount = amount - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        //fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                        fpot_netamount = parseFloat(amount) - (parseFloat(amount) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_gstValue = (fpot_netamount * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);
                                                    console.log('fpot_gstValue', fpot_gstValue);
                                                    Application.Finascop_Purchase_Order.Cache.fpot_gstValue = fpot_gstValue;
                                                    /*fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                     console.log('fpot_netRate', fpot_netRate);
                                                     fpot_netamount = fpot_netRate * fpot_itemoffrrateIncTax;
                                                     console.log('fpot_netamount1', fpot_netamount);
                                                     fpot_gstValue = (fpot_netamount * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);
                                                     console.log('fpot_gstValue', fpot_gstValue);
                                                     Application.Finascop_Purchase_Order.Cache.fpot_gstValue = fpot_gstValue;
                                                     fpot_netamount = fpot_netamount + (fpot_netamount * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);*/
                                                    console.log('fpot_netamount2', fpot_netamount);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount3', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);
                                                }
                                            }
                                        }]
                                }]
                        }, {
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .25,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Gift Name',
                                            id: 'fpot_giftname',
                                            name: 'n[fpot_giftname]',
                                            anchor: '98%',
                                            tabIndex: 340
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            align: 'right',
                                            fieldLabel: 'Gift Quantity',
                                            id: 'fpot_giftqty',
                                            name: 'n[fpot_giftqty]',
                                            anchor: '98%',
                                            tabIndex: 350
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .40,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Notes',
                                            id: 'fpot_notes',
                                            name: 'n[fpot_notes]',
                                            anchor: '98%',
                                            tabIndex: 360
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Add',
                                            tabIndex: 370,
                                            id: 'addItemsToPO',
                                            iconCls: 'finascop_add',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                if (Application.Finascop_Purchase_Order.Cache.stit_fixedB2BRates == 1) {
                                                    var stGroupWindow = Ext.getCmp('stGroup_window');
                                                    if (Ext.isEmpty(stGroupWindow)) {
                                                        var stGroupWindow = new Ext.Window({
                                                            id: 'stGroup_window',
                                                            title: 'Fixed Price',
                                                            iconCls: 'finascop_additem',
                                                            modal: true,
                                                            layout: 'fit',
                                                            width: 360,
                                                            autoHeight: true,
                                                            shadow: false,
                                                            resizable: false,
                                                            items: [new Ext.FormPanel({
                                                                    autoHeight: true,
                                                                    labelAlign: 'top',
                                                                    frame: true,
                                                                    border: false,
                                                                    items: [{
                                                                            xtype: 'textfield',
                                                                            fieldLabel: 'PTS excl. Tax',
                                                                            labelStyle: mandatory_label,
                                                                            id: 'fpot_pts',
                                                                            name: 'fpot_pts',
                                                                            anchor: '99%'
                                                                        }, {
                                                                            xtype: 'textfield',
                                                                            fieldLabel: 'PTR excl. Tax',
                                                                            labelStyle: mandatory_label,
                                                                            id: 'fpot_ptr',
                                                                            name: 'fpot_ptr',
                                                                            anchor: '99%'
                                                                        }]
                                                                })
                                                            ],
                                                            buttons: [{
                                                                    text: 'Save',
                                                                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                                                                    handler: function () {
                                                                        var fpot_pts = Ext.getCmp('fpot_pts').getValue();
                                                                        var fpot_ptr = Ext.getCmp('fpot_ptr').getValue();
                                                                        if (fpot_pts > 0 && fpot_ptr > 0) {
                                                                            var isRRPApplicable = Ext.getCmp('isRRPApplicable').getValue();
                                                                            if ((isRRPApplicable == 0) && (Ext.isEmpty(Ext.getCmp('fpot_itemmrp').getValue()))) {
                                                                                Ext.MessageBox.alert('Notification', 'Enter MRP for the item');
                                                                            } else {
                                                                                if (isRRPApplicable == 1) {
                                                                                    //if (Ext.isEmpty(Ext.getCmp('fpot_itemmrp').getValue())) {
                                                                                    Application.Finascop_Purchase_Order.Cache.isRRP = 1;
                                                                                    setItemMRPRRP(fpot_pts, fpot_ptr);
                                                                                } else {
                                                                                    Application.Finascop_Purchase_Order.Cache.isRRP = 0;
                                                                                    addItemtoPO(fpot_pts, fpot_ptr);
                                                                                }

                                                                                stGroupWindow.close();
                                                                            }

                                                                        } else {
                                                                            Ext.MessageBox.alert('Notification', 'Enter PTS and PTR for the item.');
                                                                        }

                                                                    }
                                                                },
                                                                {
                                                                    text: 'Close',
                                                                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                                                                    handler: function () {
                                                                        stGroupWindow.close();
                                                                    }
                                                                }]
                                                        });
                                                    }

                                                    stGroupWindow.show();
                                                    stGroupWindow.doLayout();
                                                    stGroupWindow.center();
                                                } else {
                                                    var isRRPApplicable = Ext.getCmp('isRRPApplicable').getValue();
                                                    if ((isRRPApplicable == 0) && (Ext.isEmpty(Ext.getCmp('fpot_itemmrp').getValue()))) {
                                                        Ext.MessageBox.alert('Notification', 'Enter MRP for the item');
                                                    } else {
                                                        if (isRRPApplicable == 1) {
                                                            //if (Ext.isEmpty(Ext.getCmp('fpot_itemmrp').getValue())) {
                                                            Application.Finascop_Purchase_Order.Cache.isRRP = 1;
                                                            setItemMRPRRP();
                                                        } else {
                                                            Application.Finascop_Purchase_Order.Cache.isRRP = 0;
                                                            addItemtoPO(0, 0);
                                                        }
                                                    }


                                                }


                                            }
                                        }]
                                }, {layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Clear',
                                            tabIndex: 380,
                                            iconCls: 'finascop_my-resetpass',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                Ext.getCmp('fpot_poValue').focus(false, 100);
                                                Ext.getCmp('pe_partyItems').reset();
                                                Ext.getCmp('fpot_itemmrp').reset();
                                                Ext.getCmp('fpot_itemqty').reset();
                                                Ext.getCmp('fpot_itemoffrqty').reset();
                                                Ext.getCmp('fpot_itemoffrrate').reset();
                                                Ext.getCmp('fpot_itemaddidisc').reset();
                                                Ext.getCmp('fpot_amount').reset();
                                                Ext.getCmp('fpot_netamount').reset();
                                                Ext.getCmp('po_idiscountcalculs').reset();
                                                Ext.getCmp('fpot_giftqty').reset();
                                                Ext.getCmp('fpot_giftname').reset();
                                                Ext.getCmp('fpot_notes').reset();
                                                Ext.getCmp('po_gstType').reset();
                                                Ext.getCmp('itemUnitForm').reset();
                                                Ext.getCmp('fpot_itemoffrrateExcT').reset();
                                                Ext.getCmp('itemSmaalStockUnit').reset();
                                            }
                                        }]

                                }]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Vendor Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'po_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        newPOItemGrid()
                                    ]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.75,
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
                                    id: 'fpot_poValue',
                                    name: 'fpot_poValue',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 390,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {
                                            var itemStore = Ext.getCmp('newPOItemGrid').getStore();
                                            var fpot_netamount = itemStore.sum('fpot_netamount');
                                            var fpot_netamountet = itemStore.sum('fpot_netamountet');
                                            var fpot_pogstAmt = itemStore.sum('fpot_pogstAmt');
                                            var total_initialnetamount = itemStore.sum('fpot_initialnetamount');
                                            var total_initialofferamount = itemStore.sum('fpot_amount');
                                            Application.Finascop_Purchase_Order.Cache.total_initialnetamount = total_initialnetamount;
                                            Application.Finascop_Purchase_Order.Cache.total_initialofferamount = total_initialofferamount;
                                            var totalItems = itemStore.getCount();
                                            if (parseFloat(Application.Finascop_Purchase_Order.Cache.totalhcg) > 0) {
                                                Application.Finascop_Purchase_Order.Cache.totalhcg = Application.Finascop_Purchase_Order.Cache.totalhcg;
                                            } else {
                                                Application.Finascop_Purchase_Order.Cache.totalhcg = 0;
                                            }
                                            //fpot_netamount = fpot_netamount + Application.Finascop_Purchase_Order.Cache.totalhcg;
                                            fpot_netamount = fpot_netamountet + fpot_pogstAmt;
                                            console.log('fpot_netamountpovalue2', fpot_netamount);
                                            Ext.getCmp('fpot_poValue').setValue(fpot_netamount);
                                            Ext.getCmp('fpot_poFinalValue').setValue(fpot_netamount);
                                            var fpot_poFinalValue = Ext.getCmp('fpot_poFinalValue').getValue();
                                            var fpot_poValue = Ext.getCmp('fpot_poValue').getValue();
                                            var discount = (fpot_poFinalValue - fpot_poValue);
                                            Ext.getCmp('fpot_poValueDiff').setValue(discount);
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.75,
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
                                    fieldLabel: 'Final',
                                    id: 'fpot_poFinalValue',
                                    name: 'fpot_poFinalValue',
                                    allowBlank: false,
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 400,
                                    anchor: '99%',
                                    listeners: {
                                        change: function (txtbx) {
                                            var fpot_poFinalValue = Ext.getCmp('fpot_poFinalValue').getValue();
                                            var fpot_poValue = Ext.getCmp('fpot_poValue').getValue();
                                            var discount = (fpot_poFinalValue - fpot_poValue);
                                            Ext.getCmp('fpot_poValueDiff').setValue(discount);
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.75,
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
                                    fieldLabel: 'Round off',
                                    id: 'fpot_poValueDiff',
                                    name: 'fpot_poValueDiff',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 410,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {

                                        }
                                    }
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptc_name',
                                    valueField: 'ptc_id',
                                    mode: 'local',
                                    id: 'po_payment_terms',
                                    forceSelection: true,
                                    fieldLabel: 'Payment Terms',
                                    emptyText: 'Payment Terms',
                                    anchor: '98%',
                                    allowBlank: false,
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 420,
                                    store: pmtTermsStore,
                                    listeners: {
                                        select: function (combo, record) {
                                            var type = record.data.ptc_name;
                                            if (type == 'Custom') {
                                                Ext.getCmp('po_payment_value').show();
                                                Ext.getCmp('po_payment_value').allowBlank = true;
                                            } else {
                                                Ext.getCmp('po_payment_value').hide();
                                                Ext.getCmp('po_payment_value').allowBlank = false;
                                            }
                                        }

                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    fieldLabel: 'Credit Days',
                                    xtype: 'numberfield',
                                    id: 'po_payment_value',
                                    labelAlign: 'top',
                                    allowBlank: false,
                                    name: 'po_payment_value',
                                    hidden: true,
                                    anchor: '98%',
                                    tabIndex: 100
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'fpo_validityType',
                                    forceSelection: true,
                                    fieldLabel: 'PO Validity',
                                    emptyText: 'PO Validity',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 430,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: '15', ptname: '15 Days'},
                                            {ptid: '30', ptname: '30 Days'}, {ptid: '60', ptname: '60 Days'}, {ptid: '90', ptname: '90 Days'}]
                                    })
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Shipping/Handling Charge',
                                    id: 'fpot_shippingcharge',
                                    name: 'n[fpot_shippingcharge]',
                                    allowNegative: false,
                                    allowDecimals: false,
                                    anchor: '98%',
                                    value: 0,
                                    tabIndex: 440,
                                    listeners: {
                                        change: function () {
                                            var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
                                            var fpot_hshippingcharge = parseFloat(fpot_shippingcharge) * 100 / parseFloat(Application.Finascop_Purchase_Order.Cache.total_initialnetamount);
                                            Application.Finascop_Purchase_Order.Cache.fpot_hshippingcharge = fpot_hshippingcharge;
                                            Application.Finascop_Purchase_Order.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'General Discounts',
                                    id: 'fpot_generalDiscount',
                                    name: 'n[fpot_generalDiscount]',
                                    allowNegative: false,
                                    allowDecimals: false,
                                    anchor: '98%',
                                    value: 0,
                                    tabIndex: 450,
                                    listeners: {
                                        change: function () {
                                            Application.Finascop_Purchase_Order.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }, {
                                    html: 'Percentage',
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    hidden: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    mode: 'local',
                                    id: 'po_gdiscountcalculs',
                                    forceSelection: true,
                                    fieldLabel: 'Percent',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: false,
                                    minChars: 2,
                                    tabIndex: 110,
                                    store: new Ext.data.JsonStore({
                                        fields: ['id', 'name'],
                                        data: [{id: 'Percentage', name: 'Percentage'}]
                                    }), listeners: {
                                        afterrender: function () {
                                            Ext.getCmp('po_gdiscountcalculs').setValue('Percentage');
                                            Ext.getCmp('po_gdiscountcalculs').setRawValue('Percentage');
                                            Ext.getCmp('po_gdiscountcalculs').setHideTrigger(true);
                                        },
                                        select: function () {
                                            justCaculatehcgd();
                                            Application.Finascop_Purchase_Order.Cache.isDiscountApplied = 0;
                                        }
                                    },
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'button',
                                    text: 'Apply',
                                    tabIndex: 460,
                                    id: 'applyGeneralDiscount',
                                    style: 'margin-top:17px;',
                                    handler: function () {
                                        if ((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0)) {
                                            justCaculatehcgd();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=getGenDiscounCalculate',
                                                method: 'POST',
                                                params: {
                                                    fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid,
                                                    total_initialnetamount: Application.Finascop_Purchase_Order.Cache.total_initialnetamount,
                                                    total_initialofferamount: Application.Finascop_Purchase_Order.Cache.total_initialofferamount,
                                                    fpot_gdiscpercent: Application.Finascop_Purchase_Order.Cache.fpot_gdiscpercent,
                                                    fpot_hshippingcharge: Application.Finascop_Purchase_Order.Cache.fpot_hshippingcharge,
                                                    adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                    adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                    adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                    adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                    adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                    adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                                                    itemGST: Application.Finascop_Purchase_Order.Cache.stit_GST

                                                },
                                                success: function (response) {

                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true) {
                                                        Ext.getCmp('newPOItemGrid').store.load({
                                                            params: {
                                                                fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid
                                                            },
                                                            callback: function () {
                                                                Application.Finascop_Purchase_Order.Cache.totalhcg = tmp.hcg;
                                                                console.log('focus2');
                                                                Ext.getCmp('fpot_poValue').focus(false, 100);
                                                                Application.Finascop_Purchase_Order.Cache.fpot_gdiscpercent = 0;
                                                                Application.Finascop_Purchase_Order.Cache.fpot_hshippingcharge = 0;
                                                                Application.Finascop_Purchase_Order.Cache.isDiscountApplied = 1;
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
                                        } else {
                                            Ext.MessageBox.alert("Notification", 'Please add the general discount / handling charge to proceed');
                                        }

                                    }
                                }]
                        }
                    ]
                }]
        });
        return panel;
    };
    function justCaculatehcgd() {
        var fpot_gdiscpercent, fpot_hshippingcharge;
        var po_gdiscountcalculs = Ext.getCmp('po_gdiscountcalculs').getRawValue();
        var fpot_generalDiscount = Ext.getCmp('fpot_generalDiscount').getValue();
        var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
        if (po_gdiscountcalculs == 'Amount') {
            fpot_gdiscpercent = parseFloat(fpot_generalDiscount) * 100 / parseFloat(Application.Finascop_Purchase_Order.Cache.total_initialnetamount);
        } else {
            if (fpot_generalDiscount <= 100) {
                fpot_gdiscpercent = parseInt(fpot_generalDiscount);
            } else {
                Ext.MessageBox.alert("Notification", "Percentage value should be between 1 nd 100");
            }

        }
        fpot_hshippingcharge = parseFloat(fpot_shippingcharge) * 100 / parseFloat(Application.Finascop_Purchase_Order.Cache.total_initialnetamount);
        Application.Finascop_Purchase_Order.Cache.fpot_gdiscpercent = fpot_gdiscpercent;
        Application.Finascop_Purchase_Order.Cache.fpot_hshippingcharge = fpot_hshippingcharge;
    }
    var setItemMRPRRP = function (fpot_pts, fpot_ptr) {
        if (Ext.isEmpty(fpot_pts)) {
            fpot_pts = 0;
        }
        if (Ext.isEmpty(fpot_ptr)) {
            fpot_ptr = 0;
        }

        var offerRateET, offerRateIT;
        var offerRate = Ext.getCmp('fpot_itemoffrrate').getValue();
        var po_gstType = Ext.getCmp('po_gstType').getValue();
        var mrpEntered = Ext.getCmp('fpot_itemmrp').getValue();
        if (po_gstType == 'Inclusive') {
            offerRateIT = offerRate;
            offerRateET = (offerRate * 100) / (100 + parseFloat(Application.Finascop_Purchase_Order.Cache.stit_GST));
        } else {
            offerRateET = offerRate;
            offerRateIT = offerRate + (offerRate * Application.Finascop_Purchase_Order.Cache.stit_GST / 100);
        }
        var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
        if (pe_partyItems > 0 && offerRateIT > 0) {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                method: 'POST',
                url: modURL + '&op=updateRRPValue',
                params: {
                    fpot_itemoffrrate: offerRateIT,
                    pe_partyItems: pe_partyItems,
                    mrpEntered: mrpEntered
                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        if (tmp.rrpCount == 1) {
                            Ext.MessageBox.confirm('Confirm', 'Calculated RRP is ' + tmp.rrpValue + ',Do you want to proceed ?', function (btn) {
                                if (btn == 'yes') {
                                    Ext.getCmp('fpot_itemmrp').setValue(tmp.rrpValue);
                                    addItemtoPO(fpot_pts, fpot_ptr);
                                }
                            });
                        } else {

                            upRRPValue(fpot_pts, fpot_ptr, tmp.rrpValue);
                        }

                    } else {
                        Ext.MessageBox.alert('Error', tmp.msg);
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert('Error', "Check the data entered");
        }

    };
    var upRRPValue = function (fpot_pts, fpot_ptr, rrpValue) {
        if (Ext.isEmpty(upRRPValue_window)) {
            var upRRPValue_window = new Ext.Window({
                id: 'setRRRP_window',
                layout: 'fit',
                width: 370,
                title: 'Existing RRP Values are' + rrpValue,
                autoHeight: true,
                iconCls: 'my-icon53',
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [{
                        xtype: 'numberfield',
                        id: 'confirmedRRP',
                        name: 'confirmedRRP',
                    }],
                buttons: [{
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            Ext.getCmp('setRRRP_window').close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        id: 'btnSetPassword',
                        handler: function () {
                            Ext.getCmp('fpot_itemmrp').setValue(Ext.getCmp('confirmedRRP').getValue());
                            Ext.getCmp('setRRRP_window').close();
                            addItemtoPO(fpot_pts, fpot_ptr);
                        }

                    }]
            });
            ;

        }
        upRRPValue_window.doLayout();
        upRRPValue_window.show(this);
        upRRPValue_window.center();
    };
    var addItemtoPO = function (fpot_pts, fpot_ptr) {
        var itemmrp = parseFloat(Ext.getCmp('fpot_itemmrp').getValue());
        var offerRateIncTax = parseFloat(Ext.getCmp('fpot_itemoffrrate').getValue());
        var offrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
        if (offerRateIncTax < itemmrp) {
            if (Ext.getCmp('fpot_netamount').getValue() > 0) {

                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=saveItemPODetails',
                    params: {
                        itemUnitForm: Ext.getCmp('itemUnitForm').getValue(),
                        fpot_itempts: fpot_pts,
                        fpot_itemptr: fpot_ptr,
                        isStandardPacking: Application.Finascop_Purchase_Order.Cache.isStandardPacking,
                        fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid,
                        fpot_adhocname: Ext.getCmp('fpot_adhocname').getValue(),
                        fpot_vendorid: Ext.getCmp('pe_party').getValue(),
                        fpot_itemid: Ext.getCmp('pe_partyItems').getValue(),
                        fpot_itemname: Ext.getCmp('pe_partyItems').getRawValue(),
                        fpot_itemmrp: Ext.getCmp('fpot_itemmrp').getValue(),
                        fpot_itemqty: Ext.getCmp('fpot_itemqty').getValue(),
                        fpot_itemoffrqty: Ext.getCmp('fpot_itemoffrqty').getValue(),
                        fpot_itemoffrrate: Ext.getCmp('fpot_itemoffrrate').getValue(),
                        //fpot_itemoffrrateet: Ext.getCmp('fpot_itemoffrrateet').getValue(),
                        fpot_itemaddidisc: Ext.getCmp('fpot_itemaddidisc').getValue(),
                        //fpot_effectiverate: Ext.getCmp('fpot_effectiverate').getValue(),
                        fpot_amount: Ext.getCmp('fpot_amount').getValue(),
                        fpot_netamount: Ext.getCmp('fpot_netamount').getValue(),
                        fpot_idiscountcalculs: Ext.getCmp('po_idiscountcalculs').getValue(),
                        fpot_giftqty: Ext.getCmp('fpot_giftqty').getValue(),
                        fpot_giftname: Ext.getCmp('fpot_giftname').getValue(),
                        fpot_notes: Ext.getCmp('fpot_notes').getValue(),
                        poadhoc_updatedon: Application.Finascop_Purchase_Order.Cache.poadhoc_updatedon,
                        adhoc_poValue: Ext.getCmp('fpot_poValue').getValue(),
                        adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                        adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                        adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                        adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                        adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                        adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                        po_gstType: Ext.getCmp('po_gstType').getValue(),
                        fpot_itemoffrrateExcT: Ext.getCmp('fpot_itemoffrrateExcT').getValue(),
                        fpot_gstValue: Application.Finascop_Purchase_Order.Cache.fpot_gstValue,
                        fpot_isRRP: Application.Finascop_Purchase_Order.Cache.isRRP
                    },
                    success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true)
                        {
                            Application.example.msg('Success', tmp.msg);
                            Application.Finascop_Purchase_Order.Cache.poadhoc_updatedon = tmp.date;
                            Ext.getCmp('itemHistory').hide();
                            Ext.getCmp('newPOItemGrid').store.load({
                                params: {
                                    fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid
                                },
                                callback: function () {
                                    console.log('focus1');
                                    Ext.getCmp('fpot_poValue').focus(false, 100);
                                    Ext.getCmp('pe_partyItems').reset();
                                    Ext.getCmp('fpot_itemmrp').reset();
                                    Ext.getCmp('fpot_itemqty').reset();
                                    Ext.getCmp('fpot_itemoffrqty').reset();
                                    Ext.getCmp('fpot_itemoffrrate').reset();
                                    Ext.getCmp('fpot_itemaddidisc').reset();
                                    Ext.getCmp('fpot_amount').reset();
                                    Ext.getCmp('fpot_netamount').reset();
                                    Ext.getCmp('po_idiscountcalculs').reset();
                                    Ext.getCmp('fpot_giftqty').reset();
                                    Ext.getCmp('fpot_giftname').reset();
                                    Ext.getCmp('fpot_notes').reset();
                                    Ext.getCmp('po_gstType').reset();
                                    Ext.getCmp('itemUnitForm').reset();
                                    Ext.getCmp('fpot_itemoffrrateExcT').reset();
                                    Ext.getCmp('itemSmaalStockUnit').reset();
                                }
                            });
                            Ext.getCmp('newPOItemGrid').getStore().load({
                                params: {
                                    fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid
                                }
                            });
                        } else if (tmp.valid === false) {
                            Ext.MessageBox.alert("Invalid!!", tmp.msg);
                        } else
                        {
                            Ext.MessageBox.alert("Failure", "Error moving Data, Kindly reload.");
                            Ext.getCmp('windowFinascopStocknewPOCreate').close();
                        }
                    },
                    failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert('Error', tmp.msg);
                    }
                });
            } else {
                Ext.Msg.alert("Notification.", 'Please fill the item details.');
            }
        } else {
            Ext.Msg.alert("Notification.", 'Offer Rate entered is not valid.');
        }

    };
    var purchaseItemOrderColModel = function ()
    {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'fpot_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'fpot_totalqty',
                tooltip: 'Qty',
                align: 'right',
                width: 50,
                renderer: function (value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.data.fpot_totalqty + ' ' + record.data.unitName + '"';
                    return value;
                }
            }, {
                header: 'SKUs',
                sortable: true,
                hideable: false,
                dataIndex: 'fpot_leastSKUTotalqty',
                tooltip: 'SKUs',
                align: 'right',
                width: 50,
                renderer: function (value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.data.fpot_leastSKUTotalqty + ' ' + record.data.leastSKU + '"';
                    return value;
                }
            }, {
                header: 'MRP/RRP(Inc)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fpot_itemmrp',
                align: 'right',
                hidden: true,
                tooltip: 'MRP/RRP(Inc)',
                width: 60
            }, {
                header: 'MRP/RRP(Inc)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fpot_leastSKUmrp',
                align: 'right',
                tooltip: 'MRP/RRP for SKU',
                width: 60
            }, {
                header: 'Rate',
                sortable: true,
                dataIndex: 'fpot_itemoffrrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                align: 'right',
                hidden: true,
                width: 60
            }, {
                header: 'Rate(Excl. of GST)',
                sortable: true,
                dataIndex: 'fpot_itemoffrrateet',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: '(Excl. of GST)',
                align: 'right',
                hidden: true,
                width: 60
            }, {
                header: 'Rate',
                sortable: true,
                dataIndex: 'fpot_leastSKUoffrrateet',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: '(Excl. of GST) Rate for SKU',
                align: 'right',
                width: 60
            }, {
                header: 'Amount',
                sortable: true,
                hideable: false,
                xtype: 'finascopcurrency',
                dataIndex: 'fpot_amount',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount',
                align: 'right',
                width: 60
            }, {
                header: 'Product Dis.',
                sortable: true,
                hideable: false,
                dataIndex: 'fpot_itemoffrqty',
                tooltip: 'Product Discount',
                align: 'right',
                width: 50
            },
            {
                header: 'Discount',
                sortable: true,
                dataIndex: 'itemDisc',
                align: 'right',
                tooltip: 'Discount',
                width: 50
            },
            {
                header: 'Handling Chrg.',
                sortable: true,
                dataIndex: 'fpot_shippingcharge',
                tooltip: 'Shipping / Handling Charge',
                align: 'right',
                width: 50
            }, {
                header: 'General Dis.',
                sortable: true,
                dataIndex: 'fpot_gendiscount',
                tooltip: 'General Discount',
                align: 'right',
                width: 50
            },
            {
                header: 'Tax',
                sortable: true,
                dataIndex: 'fpot_pogstAmt',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Tax',
                width: 60
            }, {
                header: 'Net Amt',
                sortable: true,
                dataIndex: 'fpot_netamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Net Amt',
                width: 60
            }, {
                header: 'Initial Net Amt',
                hidden: true,
                dataIndex: 'fpot_initialnetamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Initial Net Amt',
                width: 60
            }, {
                header: 'Total Net Amt',
                hidden: true,
                dataIndex: 'fpot_netamountTotal',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Total Net Amt',
                width: 60
            },
            {
                header: 'EPR',
                sortable: true,
                dataIndex: 'fpot_effectiverate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'EPR',
                hidden: true,
                width: 60
            }, {
                header: 'EPR',
                sortable: true,
                dataIndex: 'fpot_leastSKUepr',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'SKU EPR',
                width: 60
            }, {
                xtype: 'actioncolumn',
                hideable: false,
                width: 25,
                items: [{
                        tooltip: 'Remove Item',
                        iconCls: 'finascop_delete',
                        handler: function (grid, rowIndex, colIndex) {
                            var record = grid.store.getAt(rowIndex);
                            Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
                                if (btn == 'yes') {
                                    removeFromItemStore(grid, rowIndex, record);
                                }
                            });
                        }
                    }]
            }
        ]);
    };
    var removeFromItemStore = function (grid, indx, record)
    {

        Ext.Ajax.request({
            url: modURL + '&op=deleteVendorItemFromPO',
            method: 'POST',
            params: {
                itemid: record.get('fpot_itemid'),
                uid: Application.Finascop_Purchase_Order.Cache.Pouid
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true)
                {
                    Ext.getCmp('newPOItemGrid').getStore().load({
                        params: {
                            fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid
                        },
                        callback: function () {
                            Ext.getCmp('fpot_poValue').focus();
                        }
                    });
                } else
                {
                    Ext.MessageBox.alert('Failed');
                }
            }
        });
    };
    var newPOItemGrid = function () {

        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listPodetailsStore',
                    method: 'post'
                }),
                fields: ['fpot_itemid', 'fpot_itemname', 'fpot_itemmrp', 'fpot_itemqty', 'fpot_itemoffrqty', 'fpot_totalqty', 'fpot_balanceqty', 'fpot_itemoffrrate', 'fpot_itemoffrrateet', 'itemDisc', 'fpot_pogstAmt', 'fpot_netamountTotal', 'fpot_netamountTotal',
                    'fpot_itemaddidisc', 'fpot_effectiverate', 'fpot_idiscountcalculs', {name: 'fpot_amount', type: 'float'}, {name: 'fpot_netamount', type: 'float'}, {name: 'fpot_netamountet', type: 'float'}, {name: 'fpot_pogstAmt', type: 'float'},
                    {name: 'fpot_initialnetamount', type: 'float'}, 'fpot_gendiscount', 'fpot_shippingcharge', 'fpot_leastSKUepr', 'fpot_leastSKUmrp', 'fpot_leastSKUoffrrateet', 'fpot_leastSKUqty', 'unitName', 'leastSKU', 'fpot_leastSKUTotalqty'],
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
        var itemGridStore = PurchaseOrderItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'newPOItemGrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseItemOrderColModel(),
            tbar: [],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true
        });
        return itemgrid_panel;
    };
    var vendornewPOViewForm = function (poId) {

        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'poViewForm',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 42,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Vendor',
                                    id: 'vendor_name',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 1
                                }]
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Number',
                                    id: 'vpo_number',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 1
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Date',
                                    id: 'vpo_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    anchor: '98%',
                                    tabIndex: 2
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 53,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Value',
                                    id: 'poValue',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 53,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ship To',
                                    id: 'centralStore',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }
                            ]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Vendor Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        viewPOItemGrid(poId)
                                    ]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .65,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .20,
                            labelWidth: 85,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Total',
                                    id: 'po_poValue',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 511,
                                    readOnly: true,
                                    anchor: '80%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .65,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .20,
                            labelWidth: 85,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Final Value',
                                    id: 'fpo_poFinalValue',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 511,
                                    readOnly: true,
                                    anchor: '80%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .65,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .20,
                            labelWidth: 85,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Round off',
                                    id: 'fpo_poValueDiff',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 511,
                                    readOnly: true,
                                    anchor: '80%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.15,
                            hideBorders: true,
                            labelAlign: 'left',
                            labelWidth: 90,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Payment Terms',
                                    id: 'po_payment_terms',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            labelWidth: 60,
                            items: [{
                                    fieldLabel: 'Credit Days',
                                    xtype: 'displayfield',
                                    id: 'payment_value',
                                    labelAlign: 'left',
                                    hidden: true,
                                    anchor: '98%',
                                    tabIndex: 513
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            hideBorders: true,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Validity',
                                    id: 'povalidityType',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            hideBorders: true,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'General Discounts',
                                    id: 'pogeneralDiscount',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            hideBorders: true,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Handling Charges',
                                    id: 'poShippingCharge',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }]
                        }
                    ]
                }]
        });
        return panel;
    };
    var viewPOItemGrid = function (poId) {

        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=viewListPodetailsStore',
                    method: 'post'
                }),
                fields: ['fpod_itemid', 'fpod_itemname', 'fpod_itemmrp', 'fpod_itemqty', 'fpod_itemoffrqty', 'fpod_totalqty', 'fpod_balanceqty', 'fpod_itemoffrrate', 'fpod_itemoffrrateet', 'fpod_itemaddidisc', 'fpod_effectiverate',
                    'fpod_idiscountcalculus', 'fpod_amount', 'fpod_giftname', 'fpod_netamount', 'fpod_giftqty', 'fpod_notes', 'itemDisc', 'unitName'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.poId = poId;
                    }
                }

            });
            return itemStore;
        };
        var itemGridStore = PurchaseOrderItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'newPOItemGrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: viewOrderColModel(),
            tbar: [],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true
        });
        return itemgrid_panel;
    };
    var viewOrderColModel = function ()
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
                header: 'MRP/RRP(Inc.)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fpod_itemmrp',
                align: 'right',
                tooltip: 'MRP/RRP(Inc.)',
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
                header: 'Rate (Excl. of GST)',
                sortable: true,
                dataIndex: 'fpod_itemoffrrateet',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                align: 'right',
                width: 60
            }, {
                header: 'PO Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'fpod_itemqty',
                tooltip: 'PO Qty',
                align: 'right',
                width: 60
            }, {
                header: 'Total Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'fpod_totalqty',
                tooltip: 'Total Qty',
                align: 'right',
                width: 60
            }, {
                header: 'Unit',
                sortable: true,
                hideable: false,
                dataIndex: 'unitName',
                tooltip: 'Unit',
                width: 60
            }, {
                header: 'Amount',
                sortable: true,
                hideable: false,
                dataIndex: 'fpod_amount',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount',
                align: 'right',
                width: 60
            }, {
                header: 'Product Dis',
                sortable: true,
                hideable: false,
                dataIndex: 'fpod_itemoffrqty',
                tooltip: 'Product Discount',
                align: 'right',
                width: 100
            },
            {
                header: 'Discount',
                sortable: true,
                dataIndex: 'itemDisc',
                align: 'right',
                tooltip: 'Discount',
                width: 60
            },
            {
                header: 'Net Amt',
                sortable: true,
                dataIndex: 'fpod_netamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Net Amt',
                width: 60
            },
            {
                header: 'EPR',
                sortable: true,
                dataIndex: 'fpod_effectiverate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'EPR',
                width: 60
            }, {
                header: 'Gift Name',
                sortable: true,
                dataIndex: 'fpod_giftname',
                tooltip: 'Gift Name',
                width: 60
            }, {
                header: 'Gift Qty',
                sortable: true,
                dataIndex: 'fpod_giftqty',
                align: 'right',
                tooltip: 'Gift Qty',
                width: 60
            }, {
                header: 'Notes',
                sortable: true,
                dataIndex: 'fpod_notes',
                tooltip: 'Notes',
                width: 60,
                renderer: function (value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.data.fpod_notes + '"';
                    return value;
                }
            }
        ]);
    };
    return{
        Cache: {},
        editPOWindowForVendor: function (uniqueid) {
            var vendornewPOForm = vendornewPOCreateForm(uniqueid);
            var vendorPonewWindow = new Ext.Window({
                id: "windowFinascopStocknewPOCreate",
                title: 'Create Purchase Order',
                iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1300,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [vendornewPOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save & Close',
                        tabIndex: 523,
                        handler: function () {
                            if (Ext.getCmp('newPOItemGrid').getStore().getCount() > 0) {
                                if (((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0) && Application.Finascop_Purchase_Order.Cache.isDiscountApplied == 1) || ((Ext.getCmp('fpot_generalDiscount').getValue() == 0) || (Ext.getCmp('fpot_shippingcharge').getValue() == 0) && Application.Finascop_Purchase_Order.Cache.isDiscountApplied == 0)) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=adhocSaveClose',
                                        method: 'POST',
                                        params: {
                                            fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid,
                                            adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                            adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                            adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                            adhoc_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                            adhoc_poFinalValue: Ext.getCmp('fpot_poFinalValue').getValue(),
                                            adhoc_poValueDiff: Ext.getCmp('fpot_poValueDiff').getValue()
                                        },
                                        success: function (response) {

                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success == true) {

                                                Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                                Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getStore().load();
                                            } else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }

                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        }
                                    });
                                    Application.Finascop_Purchase_Order.Cache.totalhcg = 0;
                                } else {
                                    Ext.MessageBox.alert("Notification", "Mistake in Discount applied.");
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", "Items are not added");
                            }




                        }
                    } /*     <?php if (user_access("finascop_purchase_order", "savePO")) { ?> */,
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/arrow_right.png',
                        text: 'Convert to PO',
                        tabIndex: 522,
                        handler: function () {
                            var po_payment_terms = Ext.getCmp('po_payment_terms').getValue();
                            var po_payment_value = Ext.getCmp('po_payment_value').getValue();
                            var fpo_validityType = Ext.getCmp('fpo_validityType').getValue();
                            if (Ext.getCmp('newPOItemGrid').getStore().getCount() > 0) {
                                if (((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0) && Application.Finascop_Purchase_Order.Cache.isDiscountApplied == 1) || ((Ext.getCmp('fpot_generalDiscount').getValue() == 0) || (Ext.getCmp('fpot_shippingcharge').getValue() == 0) && Application.Finascop_Purchase_Order.Cache.isDiscountApplied == 0)) {
                                    if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue()) && Ext.getCmp('fpot_poFinalValue').getValue() > 0) {
                                        Application.Finascop_Purchase_Order.savenewPO();
                                    } else {
                                        Ext.MessageBox.alert("Notification", 'Please check the values of Payment Terms, PO Validity , Ship To..');
                                    }
                                } else {
                                    Ext.MessageBox.alert("Notification", "Mistake in Discount applied.");
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", "Items are not added");
                            }

                            Application.Finascop_Purchase_Order.Cache.totalhcg = 0;
                        }
                    } /*<?php } ?> */
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=loadAdhocPO',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    uniqueid: uniqueid

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    Ext.getCmp('itemHistory').hide();
                    Ext.getCmp('newPOItemGrid').store.load({
                        params: {
                            fpot_uniqueid: uniqueid
                        },
                        callback: function () {
                            Application.Finascop_Purchase_Order.Cache.poadhoc_updatedon = tmp.adhoc_updatedon;
                            console.log('focus3');
                            Ext.getCmp('fpot_poValue').focus(false, 100);
                            Ext.getCmp('pe_partyItems').reset();
                            Ext.getCmp('fpot_itemmrp').reset();
                            Ext.getCmp('fpot_itemqty').reset();
                            Ext.getCmp('fpot_itemoffrqty').reset();
                            Ext.getCmp('fpot_itemoffrrate').reset();
                            Ext.getCmp('fpot_itemaddidisc').reset();
                            Ext.getCmp('fpot_amount').reset();
                            Ext.getCmp('fpot_netamount').reset();
                            Ext.getCmp('po_idiscountcalculs').reset();
                            Ext.getCmp('fpot_giftqty').reset();
                            Ext.getCmp('fpot_giftname').reset();
                            Ext.getCmp('fpot_notes').reset();
                            Ext.getCmp('po_gstType').reset();
                            Ext.getCmp('fpot_itemoffrrateExcT').reset();
                            Ext.getCmp('itemSmaalStockUnit').reset();
                            Application.Finascop_Purchase_Order.Cache.Pouid = uniqueid;
                            Ext.getCmp('uniqueId').setValue(uniqueid);
                            Ext.getCmp('fpot_adhocname').setReadOnly(true);
                            Ext.getCmp('fpot_adhocname').setValue(tmp.adhoc_name);
                            Ext.getCmp('pe_party').setReadOnly(true);
                            Ext.getCmp('pe_party').setHideTrigger(true);
                            Ext.getCmp('pe_party').setValue(tmp.adhoc_vendor);
                            Ext.getCmp('po_payment_terms').setValue(tmp.adhoc_paymentTerms);
                            Ext.getCmp('po_payment_value').setValue(tmp.adhoc_paymentValue);
                            Ext.getCmp('fpo_validityType').setValue(tmp.adhoc_validityType);
                            Ext.getCmp('fpot_shippingcharge').setValue(tmp.adhoc_shippingcharge);
                            Ext.getCmp('fpot_generalDiscount').setValue(tmp.adhoc_gdiscount);
                            Ext.getCmp('po_gdiscountcalculs').setValue(tmp.adhoc_gdiscounttype);
                            Ext.getCmp('po_billing_to').setValue(tmp.adhoc_billingTo);
                            Ext.getCmp('fpot_poFinalValue').setValue(tmp.adhoc_poFinalValue);
                            Ext.getCmp('fpot_poValueDiff').setValue(tmp.adhoc_poValueDifference);
//                            Ext.getCmp('fpo_validityType').getStore().load();
//                            Ext.getCmp('po_gdiscountcalculs').getStore().load();
                            Ext.getCmp('pe_partyItems').getStore().load({
                                params: {
                                    stpa_id: tmp.adhoc_vendor
                                }
                            });
                            Ext.getCmp('po_payment_terms').getStore().load({
                                params: {
                                    stpa_id: tmp.adhoc_vendor
                                }
                            });
                        }
                    });
                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        },
        deleteFeedback: function () {
            var t = new Date();
            var adhoc_uniqueid = arguments[0];
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=deleteAdhocDetails',
                method: 'POST',
                params: {adhoc_uniqueid: adhoc_uniqueid},
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc'));
                        Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').store.reload({
                            params: {
                                start: 0,
                                limit: recs_per_page
                            }
                        });
                    } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else if (tmp.success === true && tmp.img_valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                        Ext.Msg.alert("Error", tmp.message);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
        },
        newPOWindowForVendor: function () {
            Application.Finascop_Purchase_Order.Cache.isStandardPacking = 0;
            var vendornewPOForm = vendornewPOCreateForm();
            Application.Finascop_Purchase_Order.Cache.poadhoc_updatedon = '';
            var vendorPonewWindow = new Ext.Window({
                id: "windowFinascopStocknewPOCreate",
                title: 'Create Provisional PO',
                //iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1180,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [vendornewPOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save & Close',
                        tabIndex: 470,
                        handler: function () {
                            if (Ext.getCmp('newPOItemGrid').getStore().getCount() > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=adhocSaveClose',
                                    method: 'POST',
                                    params: {
                                        fpot_uniqueid: Application.Finascop_Purchase_Order.Cache.Pouid,
                                        adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                        adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                        adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                        adhoc_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                        adhoc_poFinalValue: Ext.getCmp('fpot_poFinalValue').getValue(),
                                        adhoc_poValueDiff: Ext.getCmp('fpot_poValueDiff').getValue()
                                    },
                                    success: function (response) {

                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == true) {

                                            Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                            Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getStore().load();
                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }

                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please add the items to proceed');
                            }

                            Application.Finascop_Purchase_Order.Cache.totalhcg = 0;
                        }
                    } /*     <?php if (user_access("finascop_purchase_order", "savePO")) { ?> */,
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/arrow_right.png',
                        text: 'Convert to PO',
                        tabIndex: 480,
                        handler: function () {
                            var po_payment_terms = Ext.getCmp('po_payment_terms').getValue();
                            var po_payment_value = Ext.getCmp('po_payment_value').getValue();
                            var fpo_validityType = Ext.getCmp('fpo_validityType').getValue();
                            //if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue())) {
                            if (Ext.getCmp('newPOItemGrid').getStore().getCount() > 0) {
                                if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue()) && (Ext.getCmp('fpot_poValue').getValue() > 0) && (Ext.getCmp('po_billing_to').getValue() > 0)) {
                                    Application.Finascop_Purchase_Order.savenewPO();
                                } else {
                                    Ext.MessageBox.alert("Notification", 'Please check the values of Payment Terms, PO Validity , Ship To..');
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please add the items to proceed');
                            }

                            Application.Finascop_Purchase_Order.Cache.totalhcg = 0;
                        }
                    } /*<?php } ?> */
                ]
            });
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
                    Application.Finascop_Purchase_Order.Cache.Pouid = uid;
                    Ext.getCmp('pe_partyItems').getStore().clearData();
                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        },
        createPOforVendor: function (customerId, customerName) {

            var vendorPoWindow = new Ext.Window({
                id: "windowFinascopStockPOCreate",
                title: 'Purchase order for ' + customerName,
                iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1200,
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                layout: 'border',
                items: [vendorDetails(), vendItemGridforPO(customerId), {
                        xtype: 'panel',
                        region: 'east',
                        width: 500,
                        items: [
                            itemPodetails(customerId)
                        ]
                    }, itemPaymentDetails(customerId)],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 519,
                        handler: function () {
                            Ext.getCmp('windowFinascopStockPOCreate').close();
                        }
                    } /*     <?php if (user_access("finascop_purchase_order", "savePO")) { ?> */,
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save',
                        tabIndex: 518,
                        handler: function () {
                            Application.Finascop_Purchase_Order.Cache.customerName = customerName;
                            var po_payment_terms = Ext.getCmp('po_payment_terms').getValue();
                            var po_payment_value = Ext.getCmp('po_payment_value').getValue();
                            var po_delivery_datetype = Ext.getCmp('po_delivery_datetype').getValue();
                            var fpo_validityType = Ext.getCmp('fpo_validityType').getValue();
                            var po_delivery_date = Ext.getCmp('po_delivery_date').getValue();
                            if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue())) {
                                Application.Finascop_Purchase_Order.savePO();
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please fill all Payment Terms and Date of delivery');
                            }
                        }
                    } /*<?php } ?> */
                ]
            });
            vendorPoWindow.doLayout();
            vendorPoWindow.show();
            vendorPoWindow.center();
        },
        initPurchaseOrder: function () {
            var _panelId = 'purchase_order_panel';
            var _purchaseOrderPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_purchaseOrderPanel)) {
                _purchaseOrderPanel = purchaseMasterOrderPanel(_panelId);
                Application.UI.addTab(_purchaseOrderPanel);
                _purchaseOrderPanel.doLayout();
            } else {
                Application.UI.addTab(_purchaseOrderPanel);
                _purchaseOrderPanel.doLayout();
            }
        },
        initadhocPurchaseOrder: function () {
            var _panelId = 'purchase_order_panel_adhoc';
            var _adpurchaseOrderPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_adpurchaseOrderPanel)) {
                _adpurchaseOrderPanel = adpurchaseOrderPanel(_panelId);
                Application.UI.addTab(_adpurchaseOrderPanel);
                _adpurchaseOrderPanel.doLayout();
            } else {
                Application.UI.addTab(_adpurchaseOrderPanel);
                _adpurchaseOrderPanel.doLayout();
            }
        },
        poViewMode: function () {
            var fpo_id = arguments[0];
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=getOrderDetails',
                method: 'POST',
                params: {
                    fpo_id: fpo_id,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    var propertygridPanel = Ext.getCmp('xtemplateFinascopPurchaseOrderDataviewPoorderdetails');
                    //propertygridPanel.update(tmp.data);

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
            //Ext.getCmp('xtemplateFinascopPurchaseOrderDataviewPoorderdetails').doLayout();
        }, saveItemPoDetails: function () {
            var customerId = Application.Finascop_Purchase_Order.Cache.customerId;
            var itemId = Application.Finascop_Purchase_Order.Cache.itemId;
            var itemName = Application.Finascop_Purchase_Order.Cache.itemName;
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('itemPOForm').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL,
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        op: 'saveItemPODetails',
                        vendorId: customerId,
                        itemId: itemId,
                        itemName: itemName,
                        uid: Application.Finascop_Purchase_Order.Cache.UID,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', tmp.msg);
                            var selectedRecord = Ext.getCmp('vendorItemGridforPO').getSelectionModel().getSelections()[0];
                            var row = Ext.getCmp('vendorItemGridforPO').store.indexOf(selectedRecord);
                            var currentRecord = Ext.getCmp('vendorItemGridforPO').store.getAt(row);
                            currentRecord.set('opuniqueid', Ext.getCmp('opuniqueid').getValue());
                            currentRecord.set('opitemid', Ext.getCmp('opitemid').getValue());
                            currentRecord.set('opvendorid', Ext.getCmp('opvendorid').getValue());
                            currentRecord.set('fpot_itemmrp', Ext.getCmp('fpot_itemmrp').getValue());
                            currentRecord.set('fpot_itemoffrqty', Ext.getCmp('fpot_itemoffrqty').getValue());
                            currentRecord.set('fpot_itemaddidisc', Ext.getCmp('fpot_itemaddidisc').getValue());
                            currentRecord.set('fpot_itemqty', Ext.getCmp('fpot_itemqty').getValue());
                            currentRecord.set('fpot_itemoffrrate', Ext.getCmp('fpot_itemoffrrate').getValue());
                            currentRecord.set('fpot_itemoffrrateet', Ext.getCmp('fpot_itemoffrrateet').getValue());
                            currentRecord.set('fpot_effectiverate', Ext.getCmp('fpot_effectiverate').getValue());
                            currentRecord.set('fpot_giftname', Ext.getCmp('fpot_giftname').getValue());
                            currentRecord.set('fpot_giftqty', Ext.getCmp('fpot_giftqty').getValue());
                            currentRecord.set('fpot_notes', Ext.getCmp('fpot_notes').getValue());
                            currentRecord.set('po_item_gift', Ext.getCmp('po_item_gift').getValue());
                            currentRecord.commit();
                            Ext.getCmp('vendorItemGridforPO').getView().refresh();
                            Ext.getCmp('podetEditBtn').show();
                            Ext.getCmp('podetSaveBtn').hide();
                            Ext.getCmp('podetCancelBtn').hide();
                        } else if (tmp.success === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                            Ext.getCmp('windowFinascopStockPOCreate').close();
                        } else {
                            Ext.Msg.alert("Error", tmp.msg);
                            Ext.getCmp('windowFinascopStockPOCreate').close();
                        }
                    },
                    failure: function (elm, conf, action) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            console.log('result', result);
                            Ext.Msg.alert('Error', result.error);
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Error', 'Check the required fields');
            }
        }, savePO: function () {
            var po_payment_terms = Ext.getCmp('po_payment_terms').getValue();
            var po_payment_value = Ext.getCmp('po_payment_value').getValue();
            var po_delivery_datetype = Ext.getCmp('po_delivery_datetype').getValue();
            var fpo_validityType = Ext.getCmp('fpo_validityType').getValue();
            var po_delivery_date = Ext.getCmp('po_delivery_date').getValue();
            if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue())) {

                Ext.Ajax.request({
                    url: modURL + '&op=savePO',
                    method: 'POST',
                    params: {
                        po_payment_terms: po_payment_terms,
                        po_payment_value: po_payment_value,
                        po_delivery_datetype: po_delivery_datetype,
                        po_delivery_date: po_delivery_date,
                        fpo_validityType: fpo_validityType,
                        customerId: Application.Finascop_Purchase_Order.Cache.customerId,
                        customerName: Application.Finascop_Purchase_Order.Cache.customerName,
                        uid: Application.Finascop_Purchase_Order.Cache.UID
                    },
                    success: function (response) {
                        var res = Ext.decode(response.responseText);
                        if (res.success === true) {
                            Application.example.msg('Success', res.msg);
                            Ext.getCmp('windowFinascopStockPOCreate').close();
                        } else {
                            Ext.MessageBox.alert("Notification", res.msg);
                            Ext.getCmp('windowFinascopStockPOCreate').close();
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert("Notification", 'Please fill all Payment Terms and Date of delivery');
            }
        }, savenewPO: function () {
            var po_payment_terms = Ext.getCmp('po_payment_terms').getValue();
            var po_payment_value = Ext.getCmp('po_payment_value').getValue();
            var fpo_validityType = Ext.getCmp('fpo_validityType').getValue();
            var customerId = Ext.getCmp('pe_party').getValue();
            var customerName = Ext.getCmp('pe_party').getRawValue();
            var po_billing_to = Ext.getCmp('po_billing_to').getValue();
            if (Application.Finascop_Purchase_Order.Cache.isDiscountApplied == 1) {
                var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
                var fpot_generalDiscount = Ext.getCmp('fpot_generalDiscount').getValue();
                var po_gdiscountcalculs = Ext.getCmp('po_gdiscountcalculs').getValue();
            } else {
                Ext.getCmp('fpot_shippingcharge').setValue(0);
                Ext.getCmp('fpot_generalDiscount').setValue(0);
                Ext.getCmp('po_gdiscountcalculs').setValue('');
                var fpot_shippingcharge = 0
                var fpot_generalDiscount = 0;
                var po_gdiscountcalculs = 0;
            }
            if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue()) && (Ext.getCmp('fpot_poValue').getValue() > 0) && (Ext.getCmp('po_billing_to').getValue() > 0) && Ext.getCmp('fpot_poFinalValue').getValue() > 0) {

                Ext.Ajax.request({
                    url: modURL + '&op=savePO',
                    method: 'POST',
                    params: {
                        po_payment_terms: po_payment_terms,
                        po_payment_value: po_payment_value,
                        fpo_validityType: fpo_validityType,
                        po_billing_to: po_billing_to,
                        customerId: customerId,
                        customerName: customerName,
                        uid: Application.Finascop_Purchase_Order.Cache.Pouid,
                        poValue: Ext.getCmp('fpot_poValue').getValue(),
                        fpot_gdiscpercent: Application.Finascop_Purchase_Order.Cache.fpot_gdiscpercent,
                        fpot_hshippingcharge: Application.Finascop_Purchase_Order.Cache.fpot_hshippingcharge,
                        fpot_shippingcharge: fpot_shippingcharge,
                        fpot_generalDiscount: fpot_generalDiscount,
                        po_gdiscountcalculs: po_gdiscountcalculs,
                        total_initialnetamount: Application.Finascop_Purchase_Order.Cache.total_initialnetamount,
                        total_initialofferamount: Application.Finascop_Purchase_Order.Cache.total_initialofferamount,
                        fpo_poFinalValue: Ext.getCmp('fpot_poFinalValue').getValue(),
                        fpo_poValueDiff: Ext.getCmp('fpot_poValueDiff').getValue()
                    },
                    success: function (response) {
                        var res = Ext.decode(response.responseText);
                        if (res.success === true) {
                            Application.example.msg('Success', res.msg);
                            Ext.getCmp('windowFinascopStocknewPOCreate').close();
                            Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataadhoc').getStore().load();
                        } else {
                            Ext.MessageBox.alert("Notification", response.responseText);
                            // Ext.getCmp('windowFinascopStocknewPOCreate').close();
                        }
                    },
                    failure: function (response) {
                        Ext.MessageBox.alert('Error', response.responseText);
                    }
                });
            } else {
                Ext.MessageBox.alert("Notification", 'Please fill all Payment Terms , Date of delivery, Final Value & Ship To');
            }
        }, viewPODetails: function (poId) {
            var vendornewPOForm = vendornewPOViewForm(poId);
            var poViewWindow = new Ext.Window({
                id: "windowFinascopStocknewPoView",
                title: 'View Purchase order',
                //iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1200,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: false,
                closable: false,
                items: [vendornewPOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 519,
                        handler: function () {
                            Ext.getCmp('windowFinascopStocknewPoView').close();
                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=getPODetails',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    poId: poId

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    console.log(tmp);
                    Ext.getCmp('vendor_name').setValue(tmp.fpo_vendorName);
                    Ext.getCmp('centralStore').setValue(tmp.centralStore);
                    Ext.getCmp('vpo_number').setValue(tmp.fpo_poNumber);
                    Ext.getCmp('vpo_date').setValue(tmp.fpo_poDate);
                    Ext.getCmp('poValue').setValue(tmp.fpo_poValue);
                    Ext.getCmp('po_poValue').setValue(tmp.fpo_poValue);
                    Ext.getCmp('po_payment_terms').setValue(tmp.fpo_paymentTerms);
                    Ext.getCmp('payment_value').setValue(tmp.fpo_paymentValue);
                    Ext.getCmp('povalidityType').setValue(tmp.fpo_validityType);
                    Ext.getCmp('pogeneralDiscount').setValue(tmp.fpo_gdiscpercent);
                    Ext.getCmp('poShippingCharge').setValue(tmp.fpo_shippingcharge);
                    Ext.getCmp('fpo_poFinalValue').setValue(tmp.fpo_poFinalValue);
                    Ext.getCmp('fpo_poValueDiff').setValue(tmp.fpo_poValueDiff);
                    Ext.getCmp('newPOItemGrid').getStore().load({
                        params: {
                            poId: poId
                        }
                    });
                }
            });
            poViewWindow.doLayout();
            poViewWindow.show();
            poViewWindow.center();
        }, printPO: function (PoId) {
            var printLabResultsPanel = Application.Finascop_Purchase_Order.createprintPOPanel(PoId);
            if (Ext.isEmpty(printRetalinPODetaislWindow)) {
                var printRetalinPODetaislWindow = new Ext.Window({
                    id: 'printRetalinPODetaislWindow',
                    plain: true,
                    modal: true,
                    constrain: true,
                    resizable: false,
                    title: 'Print Purchase Order',
                    width: winsize.width * 0.7,
                    autoHeight: true,
                    items: [printLabResultsPanel],
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            handler: function () {
                                printRetalinPODetaislWindow.close();
                            }
                        }, {
                            text: 'Print',
                            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                            handler: function () {
                                var params = {
                                    // order_id: order_id,
                                    action: 1
                                };
                                //updateOrderStatus(params);
                                iframeRequest.focus();
                                iframeRequest.print();
                                printRetalinPODetaislWindow.close();
                            }
                        }],
                });
            }
            printRetalinPODetaislWindow.doLayout();
            printRetalinPODetaislWindow.show();
            printRetalinPODetaislWindow.center();
        }, createprintPOPanel: function (PoId) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var src = '?module=finascop_purchase_order&op=printPODetails&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '&PoId=' + PoId;
            var myPanel = new Ext.Panel({
                layout: 'border',
                height: 500,
                id: 'printRetalinPOPanel',
                items: [{
                        region: 'center',
                        border: false,
                        html: '<iframe src="' + src +
                                '" id="iframeRequest" name="iframeRequest" ' +
                                'width="100%" height="100%" style="border:none">'
                    }]
            });
            return myPanel;
        }
    }
}();
