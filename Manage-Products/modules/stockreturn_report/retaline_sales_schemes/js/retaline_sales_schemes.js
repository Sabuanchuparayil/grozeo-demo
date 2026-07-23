/* global Ext, Application, _SESSION, ACTION_FAIL, FINASCOP_CURRENCY_FORMAT */

Application.RetalineSalesSchemes = function () {

    var RECS_PER_PAGE = 12;
    var modURL = '?module=retaline_sales_schemes';
    var winsize = Ext.getBody().getViewSize();
    var updatePagination = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var has_rbsch_IsGeneral = 0;

    var SalesSchemesListStore = function () {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=getSalesSchemeslist',
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            fields: ['rbsch_id', 'rbsch_name', 'rbsch_IsGeneral', {name: 'rbsch_FromMrp', type: 'float'}, {name: 'rbsch_ToMrp', type: 'float'}],
            sortInfo: {
                field: 'rbsch_name',
                direction: "DESC"
            },
            remoteSort: true,
            autoLoad: true
        });
        return store;
    };

    var createNewSalesScheme = function () {
        Ext.getCmp('schemeDetailsSlidingPanelID').expand(true);
        Ext.getCmp('schemeDetailsPanel').enable();


        var SchemeRecord = Ext.data.Record.create([// creates a subclass of Ext.data.Record
            {name: 'levelID', mapping: 'rbsch_LevelID', type: 'int', allowBlank: false},
            {name: 'LevelName', mapping: 'rbsch_Level', allowBlank: false},
            {name: 'fromQty', mapping: 'rbsch_FromQty', type: 'int', allowBlank: true},
            {name: 'toQty', mapping: 'rbsch_ToQty', type: 'int', allowBlank: true},
            {name: 'company', mapping: 'rbsch_company', type: 'int', allowBlank: true},
            {name: 'technology', mapping: 'rbsch_technology', type: 'int', allowBlank: true},
            {name: 'b2BCustomer', mapping: 'rbsch_b2BCustomer', type: 'int', allowBlank: true},
            {name: 'b2bTotal', mapping: 'rbsch_b2BTotal', type: 'int', allowBlank: true}
        ]);
        var i, levelStore = Ext.getCmp('scheme_level_details_grid').getStore();
        levelStore.removeAll();
        for (i = 1; i < 6; i++) {
            var myNewRecord = new SchemeRecord(
                    {
                        levelID: i,
                        LevelName: 'Level' + i,
                        fromQty: '',
                        toQty: '',
                        company: '',
                        technology: '',
                        b2BCustomer: '',
                        b2bTotal: ''
                    });
            levelStore.add(myNewRecord);
        }

    };

    var setAsDefaultScheme = function (rbsch_name) {
        Ext.Ajax.request({
            url: modURL + '&op=setSchemeAsDefault',
            params: {
                rbsch_name: rbsch_name
            },
            method: 'POST',
            success: function (res, opt) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                    Application.example.msg('Success', tmp.msg);
                    Ext.isEmpty(Ext.getCmp('sl_sales_schemes_grid')) ? {} : Ext.getCmp('sl_sales_schemes_grid').getStore().load();
                } else {
                    Ext.MessageBox.alert("Notification", res.responseText);
                }
            },
            failure: function (res, opt) {
                var tmp = Ext.decode(res.responseText);
                Ext.Msg.alert("Error", tmp);
            }
        });
    };
    var counterSaleItemStore = function () {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getmanualPacking',
                method: 'post'
            }),
            fields: ['slNo', 'item_name', {name: 'fsto_ItemQty', type: 'float'}, {name: 'fsto_pkdQty', type: 'float'}, 'mrp', 'fsto_ItemId', 'fsto_id', 'fsto_source'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function (itemStore, itemRecord) {

                },
                beforeload: function (store, e) {

                }
            }
        });
        return _Store;
    };
    var counterItemGrid = function () {
        var counterGrid = new Ext.grid.GridPanel({
            store: counterSaleItemStore(),
            layout: 'fit',
            id: 'counterSales_return_window_grid',
            autoScroll: true,
            frame: true,
            border: false,
            loadMask: true,
            height: 300,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Sl no',
                    sortable: true,
                    dataIndex: 'puRe_InvNo',
                    tooltip: 'Purchase Invoice Number',
                }, {
                    header: 'Item',
                    width: 200,
                    sortable: true,
                    dataIndex: 'puRe_InvDate',
                    tooltip: 'Purchase Invoice Date',
                }, {
                    header: 'Batch No',
                    sortable: true,
                    dataIndex: 'puRe_totalitems',
                    tooltip: 'Item Count',
                }, {
                    header: 'Expiry Date',
                    sortable: true,
                    dataIndex: 'have_returned',
                    tooltip: 'Have Returned',
                }, {
                    header: 'MRP',
                    sortable: true,
                    dataIndex: 'have_returned',
                    tooltip: 'Have Returned',
                }, {
                    header: 'Rate',
                    sortable: true,
                    dataIndex: 'have_returned',
                    tooltip: 'Have Returned',
                }, {
                    header: 'GST %',
                    sortable: true,
                    dataIndex: 'have_returned',
                    tooltip: 'Have Returned',
                }, {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'have_returned',
                    tooltip: 'Have Returned',
                }, {
                    header: 'Amount',
                    sortable: true,
                    dataIndex: 'have_returned',
                    tooltip: 'Have Returned',
                }

            ], tbar: [{
                    html: '&nbsp;Barcode : &nbsp;',
                    id: 'barcodeBr_label',
                }, {
                    xtype: 'textfield',
                    id: 'barcode_id',
                    name: 'barcode_id',
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

                        },
                        specialkey: function (field, e) {
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

                        }
                    }
                }
            ],
            stripeRows: true
        });
        return counterGrid;
    };
    var viewcounter_sale_panel = function () {

        var panel = new Ext.form.FormPanel({
            frame: false,
            height: winsize.height * 0.75,
            border: false,
            id: 'counter_sales_add_form_id',
            layout: 'column',
            items: [{
                    layout: 'form',
                    border: false,
                    columnWidth: 1,
                    items: [counterItemGrid()]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.30,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            style: {'text-align': 'right', 'margin-top': '4px'},
                            fieldLabel: 'Patient Name',
                            allowBlank: false,
                            id: 'pe_name_id',
                            autoStripChars: true,
                            name: 'pe_name_id',
                            minValue: 0.05,
                            allowNegative: false,
                            allowDecimals: true,
                            readOnly: true,
                            anchor: '99%'

                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: .40,
                    items: [{
                            xtype: 'label',
                            html: '&nbsp;'
                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.20,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            style: {'text-align': 'right', 'margin-top': '4px'},
                            fieldLabel: 'Sub Total',
                            allowBlank: false,
                            id: 'subTotal_id',
                            autoStripChars: true,
                            name: 'subTotal_id',
                            minValue: 0.05,
                            allowNegative: false,
                            allowDecimals: true,
                            readOnly: true,
                            anchor: '99%',
                            width: 100

                        }]
                },
                {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.30,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            style: {'text-align': 'right', 'margin-top': '4px'},
                            fieldLabel: 'Mobile No',
                            //allowBlank: false,
                            id: 'Mob_no_id',
                            autoStripChars: true,
                            name: 'Mob_no_id',
                            minValue: 0.00,
                            allowNegative: false,
                            allowDecimals: true,
                            anchor: '99%'

                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: .40,
                    items: [{
                            xtype: 'label',
                            html: '&nbsp;'
                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.20,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            style: {'text-align': 'right', 'margin-top': '4px'},
                            fieldLabel: 'GST',
                            //allowBlank: false,
                            id: 'gst_id',
                            autoStripChars: true,
                            name: 'gst_id',
                            minValue: 0.00,
                            allowNegative: false,
                            allowDecimals: true,
                            anchor: '99%',
                            width: 100

                        }]
                },
                {
                    layout: 'form',
                    border: false,
                    columnWidth: 0.30,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            style: {'text-align': 'right', 'margin-top': '4px'},
                            fieldLabel: 'Doctor Name',
                            allowBlank: false,
                            id: 'doctor_name_id',
                            autoStripChars: true,
                            name: 'doctor_name_id',
                            minValue: 0.00,
                            allowNegative: false,
                            allowDecimals: true,
                            anchor: '99%'
                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: .40,
                    items: [{
                            xtype: 'label',
                            html: '&nbsp;'
                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.20,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            style: {'text-align': 'right', 'margin-top': '4px'},
                            fieldLabel: 'KFC',
                            allowBlank: false,
                            id: 'kfc_id',
                            autoStripChars: true,
                            name: 'kfc_id',
                            minValue: 0.00,
                            allowNegative: false,
                            allowDecimals: true,
                            anchor: '99%',
                            width: 100
                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.70,
                    items: [{
                            xtype: 'label',
                            html: '&nbsp;'
                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.20,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            style: {'text-align': 'right', 'margin-top': '4px'},
                            fieldLabel: 'Round Off',
                            allowBlank: false,
                            id: 'roundoff_id',
                            autoStripChars: true,
                            name: 'roundoff_id',
                            minValue: 0.05,
                            allowNegative: false,
                            allowDecimals: true,
                            readOnly: true,
                            anchor: '99%',
                            width: 100

                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.70,
                    items: [{
                            xtype: 'label',
                            html: '&nbsp;'
                        }]
                }, {
                    border: false,
                    layout: 'form',
                    columnWidth: 0.20,
                    items: [{
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            allowBlank: false,
                            allowNegative: false,
                            allowDecimals: true,
                            style: {'text-align': 'right'},
                            fieldLabel: 'Grand Total',
                            id: 'grand_total_id',
                            name: 'grand_total_id',
                            anchor: '99%',
                            autoStripChars: true,
                            enableKeyEvents: true,
                            width: 100

                        }]
                }
            ]
        });
        return panel;
    };
    var addCounterSales = function (id) {
        var title = 'Retail Counter Sales';
        var counter_sale_add_form = viewcounter_sale_panel();
        var addCounterSale_Window = Ext.getCmp('purchaseReturn_window');
        if (Ext.isEmpty(addCounterSale_Window)) {
            var addCounterSale_Window = new Ext.Window({
                id: 'purchaseReturn_window',
                title: title,
                iconCls: 'finascop_purchase_returns',
                modal: true,
                layout: 'fit',
                width: winsize.width * 0.8,
                height: winsize.height * 0.8,
                shadow: false,
                resizable: false,
                closable: true,
                items: [counter_sale_add_form],
                buttons: [
                    {
                        tooltip: 'Cancel',
                        iconCls: 'big-text',
                        text: 'Cancel',
                        handler: function () {
                            addCounterSale_Window.close();
                        }
                    }, {
                        tooltip: 'Save',
                        text: 'Save',
                        handler: function () {
                        }
                    }]
            });
        }

        addCounterSale_Window.show();
        addCounterSale_Window.doLayout();
        addCounterSale_Window.center();
    };
    var createSalesSchemesListGrid = function (schemeID) {

        var sales_schemes_store = SalesSchemesListStore();
        var sales_schemes_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    name: 'rbsch_name'
                }, {
                    type: 'string',
                    name: 'rbsch_FromMrp'
                }, {
                    type: 'string',
                    name: 'rbsch_ToMrp'
                }]
        });
        sales_schemes_filter.remote = true;
        sales_schemes_filter.autoReload = true;
        var sales_schemes_grid = new Ext.grid.GridPanel(
                {
                    ds: sales_schemes_store,
                    enableColumnMove: false,
                    layout: 'fit',
                    anchor: '98%',
                    iconCls: 'finascop_sales_schemes',
                    region: 'center',
                    frame: false,
                    border: true,
                    id: 'sl_sales_schemes_grid',
                    viewConfig: {
                        forceFit: true,
                        deferEmptyText: false,
                        emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                    },
                    sm: new Ext.grid.RowSelectionModel({
                        singleSelect: true

                    }),
                    plugins: [sales_schemes_filter],
                    columns: [new Ext.grid.RowNumberer(),
                        {
                            header: 'Schemes Name',
                            dataIndex: 'rbsch_name',
                            tooltip: 'Sales Schemes Name',
                            sortable: true
                        },
                        {
                            header: 'Is General',
                            hidden: true,
                            dataIndex: 'rbsch_IsGeneral',
                            tooltip: 'Is General',
                            sortable: true
                        },
                        {
                            header: 'From MRP',
                            id: 'rbsch_FromMrp',
                            dataIndex: 'rbsch_FromMrp',
                            align: 'right',
                            tooltip: 'From MRP',
                            width: 100,
                            sortable: true
                        },
                        {
                            header: 'To MRP',
                            id: 'rbsch_ToMrp',
                            dataIndex: 'rbsch_ToMrp',
                            align: 'right',
                            tooltip: 'To MRP',
                            width: 100,
                            sortable: true
                        }, {
                            xtype: 'actioncolumn',
                            hideable: false,
                            stopSelection: true,
                            sortable: false,
                            items: [
                                {
                                    tooltip: 'Make It Default Scheme',
                                    getClass: function (value, metadata, record, rowIndex, colIndex, store) {
                                        var rbsch_IsGen = record.get('rbsch_IsGeneral');
                                        if (rbsch_IsGen <= 0) {
                                            return 'default_scheme';
                                        } else {
                                            has_rbsch_IsGeneral = record.get('rbsch_IsGeneral');
                                            return 'finascop_hideicon';
                                        }
                                    },
                                    handler: function (grid, rowIndex, colIndex) {
                                        var record = grid.store.getAt(rowIndex);
                                        var rbsch_name = record.get('rbsch_name');
                                        Ext.Msg.confirm('Notification', 'Do you really want set this scheme as default?', function (btn) {
                                            if (btn == 'yes') {
                                                setAsDefaultScheme(rbsch_name);
                                            }
                                        });
                                    }
                                }/*,
                                 {
                                 iconCls: 'dc_16x16',
                                 tooltip: 'Add More Scheme Details.',
                                 handler: function (grid, rowIndex, colIndex) {
                                 Ext.getCmp('schemeDetailsPanel').getForm().reset();
                                 Application.RetalineSalesSchemes.Cache.rbsch_id = '0';
                                 var record = grid.store.getAt(rowIndex);
                                 Ext.getCmp('rbsch_name').setValue(record.get('rbsch_name'));
                                 Ext.getCmp('rbsch_IsGeneral').setValue(record.get('rbsch_IsGeneral'));
                                 Ext.getCmp('rbsch_name').setReadOnly(true);
                                 Ext.getCmp('rbsch_FromMrp').setReadOnly(false);
                                 Ext.getCmp('rbsch_ToMrp').setReadOnly(false);
                                 Ext.getCmp('cmbSchmeType').setReadOnly(false);
                                 Ext.getCmp('b2bdcSubmitBtn').setVisible(true);
                                 Ext.getCmp('scheme_level_details_grid').enable();
                                 createNewSalesScheme();
                                 }
                                 }*/
                            ]
                        }

                    ],
                    tbar: [{
                            xtype: 'button',
                            text: 'Create New Sales Schemes', iconCls: 'finascop_add',
                            handler: function () {
                                Ext.getCmp('schemeDetailsPanel').getForm().reset();
                                Ext.getCmp('rbsch_name').setReadOnly(false);
                                Ext.getCmp('rbsch_name').setReadOnly(false);
                                Ext.getCmp('rbsch_IsGeneral').setValue('0');
                                Ext.getCmp('rbsch_FromMrp').setReadOnly(false);
                                Ext.getCmp('rbsch_ToMrp').setReadOnly(false);
                                Ext.getCmp('cmbSchmeType').setReadOnly(false);
                                Ext.getCmp('b2bdcSubmitBtn').setVisible(true);
                                Ext.getCmp('scheme_level_details_grid').enable();
                                Application.RetalineSalesSchemes.Cache.rbsch_id = '0';
                                createNewSalesScheme();
                            }
                        }, {
                            xtype: 'button',
                            text: 'Create Entry',
                            iconCls: 'add',
                            handler: function () {
                                addCounterSales();
                            }
                        }],
                    bbar: new Ext.PagingToolbar({
                        pageSize: RECS_PER_PAGE,
                        store: sales_schemes_store,
                        displayInfo: true,
                        displayMsg: 'Displaying items {0} - {1} of {2}',
                        emptyMsg: "No records to display"
                    }),
                    listeners: {
                        viewready: updatePagination,
                        rowclick: function (grid, rowIndex, e) {
                            var target = e.getTarget(null, null, true);
                            if (target.hasClass('x-action-col-icon')) {
                                return;
                            }
                            var record = grid.getStore().getAt(rowIndex);
                            var rbsch_id = record.get('rbsch_id');
                            if (rbsch_id > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=getB2BSchemeDetails',
                                    method: 'POST',
                                    params: {
                                        rbsch_id: rbsch_id
                                    },
                                    success: function (response) {

                                        Ext.getCmp('schemeDetailsPanel').getForm().reset();
                                        var res = Ext.decode(response.responseText);
                                        Application.RetalineSalesSchemes.Cache.rbsch_id = res.SchemeData.rbsch_id;
                                        Ext.getCmp('rbsch_name').setValue(res.SchemeData.rbsch_name);
                                        Ext.getCmp('rbsch_FromMrp').setValue(res.SchemeData.rbsch_FromMrp);
                                        Ext.getCmp('rbsch_ToMrp').setValue(res.SchemeData.rbsch_ToMrp);
                                        Ext.getCmp('cmbSchmeType').setValue('1');
                                        Ext.getCmp('scheme_level_details_grid').getStore().loadData(res);
                                        Ext.getCmp('schemeDetailsSlidingPanelID').expand(true);
                                        Ext.getCmp('rbsch_name').setReadOnly(true);
                                        Ext.getCmp('rbsch_FromMrp').setReadOnly(true);
                                        Ext.getCmp('rbsch_ToMrp').setReadOnly(true);
                                        Ext.getCmp('cmbSchmeType').setReadOnly(true);
                                        Ext.getCmp('b2bdcSubmitBtn').setVisible(false);
                                        Ext.getCmp('scheme_level_details_grid').disable();
                                    },
                                    failure: function (res, opt) {
                                        var tmp = Ext.decode(res.responseText);
                                        Ext.Msg.alert("Error", tmp);
                                    }
                                });
                            }
                        }
                    }
                }
        );
        Ext.getCmp('sl_sales_schemes_grid').getStore().load();
        return sales_schemes_grid;
    };

    var saveB2BScheme = function () {
        var schemeLevelDetails = Ext.pluck(Ext.getCmp('scheme_level_details_grid').getStore().getRange(), 'data');
        Ext.Ajax.request({
            url: modURL + '&op=saveB2BScheme',
            method: 'POST',
            params: {
                rbsch_id: Application.RetalineSalesSchemes.Cache.rbsch_id,
                rbsch_name: Ext.getCmp('rbsch_name').getValue(),
                rbsch_FromMrp: Ext.getCmp('rbsch_FromMrp').getValue(),
                rbsch_ToMrp: Ext.getCmp('rbsch_ToMrp').getValue(),
                rbsch_IsGeneral: Ext.getCmp('rbsch_IsGeneral').getValue(),
                schemeLevelDetails: Ext.encode(schemeLevelDetails),
                has_rbsch_IsGeneral: has_rbsch_IsGeneral
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true) {
                    Application.example.msg('Success', res.msg);
                    Ext.isEmpty(Ext.getCmp('sl_sales_schemes_grid')) ? {} : Ext.getCmp('sl_sales_schemes_grid').getStore().load();
                    Ext.getCmp('schemeDetailsSlidingPanelID').collapse(true);
                    Ext.getCmp('schemeDetailsPanel').getForm().reset();

                } else {
                    Ext.MessageBox.alert("Notification", response.responseText);
                }
            },
            failure: function (res, opt) {
                var tmp = Ext.decode(res.responseText);
                Ext.Msg.alert("Error", tmp);
            }
        });
    };

    var schemeDetailsPanel = function () {
        var schemeID = Application.RetalineSalesSchemes.Cache.rbsch_id;
        var schemeLevelDetailsStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getSchemeLevelDetails',
            fields: [
                {name: 'levelID', type: 'int'},
                {name: 'LevelName', type: 'string'},
                {name: 'fromQty', type: 'int'},
                {name: 'toQty', type: 'int'},
                {name: 'company', type: 'int'},
                {name: 'technology', type: 'int'},
                {name: 'b2BCustomer', type: 'int'},
                {name: 'b2bTotal', type: 'int'}

            ],
            totalProperty: 'totalCount',
            root: 'data',
            baseParams: {
                rbsch_id: schemeID
            },
            localSort: true,
            autoLoad: false
        });

        var schemeScopeStore = new Ext.data.ArrayStore({
            fields: ['SchemeScopeID', 'SchemeScopeName'],
            data: [
                ['1', 'Item'],
                ['2', 'Brand'],
                ['3', 'Department']
            ],
            autoLoad: true
        });

        var schemeTypeStore = new Ext.data.ArrayStore({
            fields: ['SchemeTypeID', 'SchemeTypeName'],
            data: [
                ['1', 'All'],
                ['2', 'Specific']
            ],
            autoLoad: true
        });

        var grid = new Ext.grid.EditorGridPanel({
            store: schemeLevelDetailsStore,
            forceFit: true,
            id: 'scheme_level_details_grid',
            height: 300,
            disabledClass: 'vel-disabled-formpanel',
            enableColumnMove: false,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [
                new Ext.grid.RowNumberer({
                    width: 30
                }),
                {
                    header: 'Level',
                    dataIndex: 'LevelName',
                    width: 50
                },
                {
                    header: 'Quantity From',
                    id: 'fromQty',
                    dataIndex: 'fromQty',
                    width: 93,
                    editable: true,
                    xtype: 'numbercolumn',
                    format: '000,00,00,000',
                    align: 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        allowDecimals: false,
                        currRec: {},
                        previousfromQty: '',
                        fromQty: 0,
                        enableKeyEvents: true,
                        validator: function (value) {
                            return !isNaN(value);
                        },
                        listeners: {
                            keyup: function (fromQtyField, e) {
                                this.fromQty = fromQtyField.getValue();
                            },
                            focus: function (fromQtyField) {
                                fromQtyField.selectText();
                                this.fromQty = fromQtyField.getValue();
                                this.currRec = grid.getSelectionModel().getSelected();
                                this.currRec.set("fromQty", "");
                            },
                            blur: function (drField) {
                                var val = this.currRec.get("fromQty");
                                if (this.fromQty != 0 && Number(val) == 0)
                                {
                                    this.currRec.set("fromQty", this.fromQty);
                                }
                            }
                        }
                    })

                },
                {
                    header: 'Quantity To',
                    id: 'toQty',
                    dataIndex: 'toQty',
                    width: 90,
                    editable: true,
                    xtype: 'numbercolumn',
                    format: '000,00,00,000',
                    align: 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        allowDecimals: false,
                        id: 'toQtyEditField',
                        currRec: {},
                        maxValue: 100,
                        previoustoQty: '',
                        toQty: 0,
                        enableKeyEvents: true,
                        validator: function (value) {
                            return !isNaN(value);
                        },
                        listeners: {
                            keyup: function (toQtyField, e) {
                                this.toQty = toQtyField.getValue();
                            },
                            focus: function (toQtyField) {
                                toQtyField.selectText();
                                this.toQty = toQtyField.getValue();
                                this.currRec = grid.getSelectionModel().getSelected();

                                this.currRec.set("toQty", "");
                            },
                            blur: function (drField) {
                                var val = this.currRec.get("toQty");
                                if (this.toQty != 0 && Number(val) == 0)
                                {
                                    this.currRec.set("toQty", this.toQty);
                                }
                            }
                        }
                    })

                },
                {
                    header: 'Company %',
                    id: 'company',
                    dataIndex: 'company',
                    width: 90,
                    editable: true,
                    xtype: 'numbercolumn',
                    format: '00',
                    align: 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        allowDecimals: false,
                        currRec: {},
                        previousCompany: '',
                        company: 0,
                        maxValue: 100,
                        enableKeyEvents: true,
                        validator: function (value) {
                            return !isNaN(value);
                        },
                        listeners: {
                            keyup: function (companyPercentage, e) {
                                this.company = companyPercentage.getValue();
                            },
                            focus: function (companyPercentage) {
                                companyPercentage.selectText();
                                this.company = companyPercentage.getValue();
                                this.currRec = grid.getSelectionModel().getSelected();
                                this.currRec.set("company", "");
                            },
                            blur: function (companyPercentage) {
                                var val = this.currRec.get("company");
                                if (this.company != 0 && Number(val) == 0)
                                {
                                    this.currRec.set("company", this.company);
                                }
                                var b2bTotal = (this.currRec.get("technology") + this.currRec.get("company") + this.currRec.get("b2BCustomer"));
                                this.currRec.set("b2bTotal", b2bTotal);
                            }
                        }
                    })

                },
                {
                    header: 'Technology %',
                    id: 'technology',
                    dataIndex: 'technology',
                    width: 90,
                    editable: true,
                    xtype: 'numbercolumn',
                    format: '00',
                    align: 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        allowDecimals: false,
                        currRec: {},
                        maxValue: 100,
                        previousTechnology: '',
                        technology: 0,
                        enableKeyEvents: true,
                        validator: function (value) {
                            return !isNaN(value);
                        },
                        listeners: {
                            keyup: function (technologyPercentage, e) {
                                this.technology = technologyPercentage.getValue();
                            },
                            focus: function (technologyPercentage) {
                                technologyPercentage.selectText();
                                this.technology = technologyPercentage.getValue();
                                this.currRec = grid.getSelectionModel().getSelected();
                                this.currRec.set("technology", "");
                            },
                            blur: function (technologyPercentage) {
                                var val = this.currRec.get("technology");
                                if (this.technology != 0 && Number(val) == 0)
                                {
                                    this.currRec.set("technology", this.technology);

                                }
                                var b2bTotal = (this.currRec.get("technology") + this.currRec.get("company") + this.currRec.get("b2BCustomer"));
                                this.currRec.set("b2bTotal", b2bTotal);
                            }
                        }
                    })

                },
                {
                    header: 'B2BCustomer %',
                    id: 'b2BCustomer',
                    dataIndex: 'b2BCustomer',
                    width: 105,
                    editable: true,
                    xtype: 'numbercolumn',
                    format: '00',
                    align: 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        allowDecimals: false,
                        currRec: [],
                        previousB2BCustomer: '',
                        b2BCustomer: 0,
                        enableKeyEvents: true,
                        validator: function (value) {
                            return !isNaN(value);
                        },
                        listeners: {
                            keyup: function (b2BCustomerPercentage, e) {
                                this.b2BCustomer = b2BCustomerPercentage.getValue();
                            },
                            focus: function (b2BCustomerPercentage) {
                                b2BCustomerPercentage.selectText();
                                this.b2BCustomer = b2BCustomerPercentage.getValue();
                                this.currRec = grid.getSelectionModel().getSelected();
                                this.currRec.set("b2BCustomer", "");
                            },
                            blur: function (b2BCustomerPercentage) {
                                var val = this.currRec.get("b2BCustomer");
                                if (this.b2BCustomer != 0 && Number(val) == 0)
                                {
                                    this.currRec.set("b2BCustomer", this.b2BCustomer);
                                }
                                var b2bTotal = (this.currRec.get("technology") + this.currRec.get("company") + this.currRec.get("b2BCustomer"));
                                this.currRec.set("b2bTotal", b2bTotal);
                            }
                        }
                    })

                }
            ]

        });

        var scheme_details_Panel = new Ext.FormPanel({
            id: 'schemeDetailsPanel',
            frame: true,
            border: true,
            region: 'center',
            anchor: '98%',
            monitorValid: true,
            layout: 'column',
            listeners: {
                afterrender: function (thisPanel) {
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    if (schemeID > 0) {
                        Ext.Ajax.request({
                            url: modURL + '&op=getSchemeDetails',
                            params: {
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp,
                                rbsch_id: schemeID

                            },
                            method: 'POST',
                            success: function (res, opt) {
                                var tmp = Ext.decode(res.responseText);
                                var uid = tmp.uid;
                            },
                            failure: function (res, opt) {
                                Ext.Msg.alert("Error", res.responseText);
                            }
                        });
                    }
                }
            },
            items: [
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.40,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'hidden',
                                    allowBlank: true,
                                    id: 'rbsch_id',
                                    name: 'rbsch_id'
                                }, {
                                    xtype: 'hidden',
                                    allowBlank: true,
                                    id: 'rbsch_IsGeneral',
                                    name: 'rbsch_IsGeneral'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Scheme Name',
                                    style: 'text-transform: uppercase',
                                    id: 'rbsch_name',
                                    name: 'rbsch_name',
                                    anchor: '99%',
                                    allowBlank: false,
                                    listeners: {
                                        change: function (field, newValue) {
                                            field.setValue(newValue.toUpperCase());
                                            var store = Ext.getCmp('sl_sales_schemes_grid').getStore();
                                            if ((store.find('rbsch_name', field.getValue())) !== -1) {
                                                Ext.MessageBox.alert('Notification', 'Scheme Already exists.Please use action button.', function (btn) {
                                                    field.selectText();
                                                    field.focus();
                                                });
                                            }
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.28,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'MRP From',
                                    id: 'rbsch_FromMrp',
                                    name: 'rbsch_FromMrp',
                                    allowBlank: false,
                                    anchor: '99%',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: 'text-align:right',
                                    allowDecimal: true
                                }]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.28,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'MRP To',
                                    id: 'rbsch_ToMrp',
                                    allowBlank: false,
                                    name: 'rbsch_ToMrp',
                                    anchor: '99%',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: 'text-align:right',
                                    allowDecimal: true
                                }]
                        }
                    ]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.49,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'combo',
                                    name: 'cmbSchmeType',
                                    fieldLabel: 'Scheme Type',
                                    id: 'cmbSchmeType',
                                    store: schemeTypeStore,
                                    mode: 'local',
                                    anchor: '99%',
                                    hiddenName: 'receipt_particular',
                                    displayField: 'SchemeTypeName',
                                    valueField: 'SchemeTypeID',
                                    triggerAction: 'all',
                                    allowBlank: false,
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    minChars: 1,
                                    listeners: {
                                        afterrender: function (field) {

                                        },
                                        specialkey: function (thisfield, e) {
                                            if (e.getKey() == e.ENTER) {

                                            }
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.49,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'combo',
                                    name: 'cmbSchmeScope',
                                    fieldLabel: 'Scheme Scope',
                                    id: 'cmbSchmeScope',
                                    store: schemeScopeStore,
                                    mode: 'local',
                                    anchor: '99%',
                                    hiddenName: 'cmbSchmeScope',
                                    displayField: 'SchemeScopeName',
                                    valueField: 'SchemeScopeID',
                                    triggerAction: 'all',
                                    allowBlank: true,
                                    hidden: true,
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    minChars: 1,
                                    listeners: {
                                        afterrender: function (field) {

                                        },
                                        specialkey: function (thisfield, e) {
                                            if (e.getKey() == e.ENTER) {

                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                },
                {
                    layout: 'anchor',
                    columnWidth: 1,
                    items: grid
                }
            ],
            buttons: [{
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Close',
                    tabIndex: 523,
                    handler: function () {
                        Ext.getCmp('schemeDetailsSlidingPanelID').collapse(true);
                        Ext.getCmp('schemeDetailsPanel').getForm().reset();
                    }
                }, {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    text: 'Submit',
                    id: 'b2bdcSubmitBtn',
                    tabIndex: 522,
                    handler: function () {
                        var b2bfromQty = grid.getStore().getRange()[0].get('fromQty');
                        var b2btoQty = grid.getStore().getRange()[0].get('toQty');
                        var b2btotalPerc = grid.getStore().getRange()[0].get('b2bTotal');
                        var b2fq = (b2bfromQty !== "" && b2bfromQty >= 0);
                        var b2bft = (b2btoQty !== "" && b2btoQty >= 0);
                        var b2btotal = (b2btotalPerc !== "" && b2btotalPerc > 0);
                        var hasItemsInGrid = b2fq && b2bft && b2btotal;
                        if (Ext.getCmp('schemeDetailsPanel').getForm().isValid() && hasItemsInGrid) {
                            saveB2BScheme();
                        } else {
                            Ext.MessageBox.alert("Notification", 'Please fill B2B Scheme details.');
                        }
                    }
                }]

        });
        return scheme_details_Panel;
    };

    var createSalesSchemesPanel = function (panelID) {
        var mid = 0;
        var panel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            hideBorders: true,
            id: panelID,
            title: 'Sales Schemes',
            iconCls: 'finascop_salescustomer',
            items: [createSalesSchemesListGrid(),
                {
                    border: false,
                    frame: false,
                    layout: 'fit',
                    id: 'schemeDetailsSlidingPanelID',
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    collapsible: true,
                    collapseMode: 'mini',
                    collapsed: true,
                    items: schemeDetailsPanel()
                }]
        });
        return panel;
    };
    return {
        Cache: {},
        initListB2BSalesSchemes: function () {
            var _rln_sales_schemes_listPanelID = 'rln_sales_schemes_listPanelID';
            var _rln_sales_schemes_listPanel = Ext.getCmp('rln_sales_schemes_listPanelID');
            if (Ext.isEmpty(_rln_sales_schemes_listPanel)) {
                _rln_sales_schemes_listPanel = createSalesSchemesPanel(_rln_sales_schemes_listPanelID);
                Application.UI.addTab(_rln_sales_schemes_listPanel);
                _rln_sales_schemes_listPanel.doLayout();
            } else {
                Application.UI.addTab(_rln_sales_schemes_listPanel);
            }
        }
    };
}();
