Application.Finascop_AccountsBkRec = function () {

    var recs_per_page = 23;
    var backreclastParameters;
    var modURL = '?module=finascop_back_reconcilation';

    var winsize = Ext.getBody().getViewSize();
    /*** Branch combo store**/

    var branch_store = new Ext.data.JsonStore({
        url: modURL + '&op=getbranch',
        method: 'post',
        fields: ['br_Name', 'br_ID'],
        totalProperty: 'totalCount',
        root: 'data',
        autoLoad: false

    });
    /*Ledger combo store*/
    var ledger_store = new Ext.data.JsonStore({
        autoLoad: false,
        url: modURL + '&op=ledger_store',
        method: 'post',
        fields: ['Ledger', 'accled_Ledger_Id'],
        totalProperty: 'totalCount',
        root: 'data'
    });

    /*result grid store*/
    var resultStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: false,
            url: modURL + '&op=resultGridlist',
            method: 'post',
            fields: ['No', 'name', 'narration', 'action'],
            totalProperty: 'totalCount',
            root: 'data'


        });
        return store;
    };

    var company_store = new Ext.data.JsonStore({
        autoLoad: false,
        url: modURL + '&op=getCompany',
        method: 'post',
        fields: ['comp_id', 'comp_name'],
        totalProperty: 'totalCount',
        root: 'data',
        listeners: {
            load: function (store, rec, opt) {
                if (store.totalLength == 1) {
                    Ext.getCmp('receipt_company_id').setValue(store.getAt(0).get("comp_id"));
                }
            }
        }
    });

    var detailsStore = function () {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=getDetails',
            fields: [],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: false,
            listeners: {
            }
        });
        return store;
    };

    var ledger_balance_view = function () {

        var panel = new Ext.Panel({
            height: 400,
            frame: false,
            border: false,
            hideBorders: true,
            bodyStyle: {"padding": "5px 5px 5px 5px"},
            items: [
                {
                    layout: 'column',
                    hideBorders: true,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.50,
                            labelWidth: 60,
                            bodyStyle: {"padding": "5px 5px 5px 5px"},
                            items: [
                                {
                                    fieldLabel: 'Company',
                                    xtype: 'combo',
                                    displayField: 'comp_name',
                                    valueField: 'comp_id',
                                    mode: 'remote',
                                    id: 'ledger_company_id',
                                    emptyText: 'Select company..',
                                    hiddenName: 'n[ledger_company_id]',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    tabIndex: 1,
                                    anchor: '98%', allowBlank: false,
                                    store: company_store,
                                    editable: false,
                                    listeners: {
                                        select: function () {
                                            Ext.getCmp('ledger_balance_id').reset();
                                            Ext.getCmp('ledger_balance_id').getStore().removeAll();
                                        }
                                    }


                                },
                                {layout: 'column',
                                    hideBorders: true,
                                    border: false,
                                    items: [
                                        {
                                            layout: 'form',
                                            columnWidth: 0.60,
                                            hideBorders: true,
                                            labelWidth: 60,
                                            items: [
                                                {
                                                    fieldLabel: 'Ledger',
                                                    xtype: 'combo',
                                                    displayField: 'Ledger',
                                                    valueField: 'accled_Ledger_Id',
                                                    mode: 'remote',
                                                    id: 'ledger_balance_id',
                                                    hiddenName: 'n[ledger_balance_id]',
                                                    typeAhead: true,
                                                    minChars: 3,
                                                    triggerAction: 'all', selectOnFocus: true,
                                                    lazyRender: true,
                                                    anchor: '100%',
                                                    allowBlank: false,
                                                    hideTrigger: true,
                                                    store: ledger_store,
                                                    editable: true,
                                                    msgTarget: 'under',
                                                    tabIndex: 5
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: 0.40,
                                            labelWidth: 40,
                                            items: [
                                                {
                                                    xtype: 'checkbox',
                                                    id: 'chk_customer',
                                                    name: 'check_customer',
                                                    tabIndex: 3,
                                                    boxLabel: 'Customer Only',
                                                    listeners: {
                                                        'check': function () {
                                                            var chk_customer = Ext.getCmp('chk_customer').getValue();
                                                            console.log(chk_customer);
                                                            Ext.getCmp('ledger_balance_id').reset();
                                                            Ext.getCmp('ledger_balance_id').getStore().removeAll();
                                                            var ledger = Ext.getCmp('ledger_balance_id').getStore();
                                                            ledger.baseParams.chk_customer = chk_customer;
                                                        }
                                                    }
                                                }]
                                        }]
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.50,
                            labelWidth: 50,
                            hideBorders: true,
                            bodyStyle: {"padding": "5px 5px 5px 5px"},
                            items: [{
                                    fieldLabel: 'Branch',
                                    xtype: 'combo',
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    mode: 'remote',
                                    id: 'ledger_branch_id',
                                    hiddenName: 'n[ledger_branch_id]',
                                    typeAhead: true,
                                    minChars: 1,
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    lazyRender: true,
                                    anchor: '95%',
                                    allowBlank: false,
                                    hideTrigger: true,
                                    store: branch_store,
                                    editable: true,
                                    msgTarget: 'under',
                                    tabIndex: 2,
                                    listeners: {
                                        select: function (cmp) {
                                            Ext.getCmp('ledger_balance_id').reset();
                                            Ext.getCmp('ledger_balance_id').getStore().removeAll();
                                            var ledger = Ext.getCmp('ledger_balance_id').getStore();
                                            ledger.baseParams.branch_id = cmp.getValue();
                                        }
                                    }
                                }, {layout: 'column',
                                    hideBorders: true,
                                    items: [
                                        {
                                            layout: 'form',
                                            columnWidth: 0.42,
                                            hideBorders: true,
                                            labelWidth: 50,
                                            items: [{
                                                    xtype: 'datefield',
                                                    format: 'Y-m-d',
                                                    fieldLabel: 'From',
                                                    id: 'ledger_from_date',
                                                    name: 'n[ledger_from_date]',
                                                    anchor: '95%',
                                                    tabIndex: 6
                                                }]
                                        },
                                        {
                                            layout: 'form',
                                            columnWidth: 0.42,
                                            hideBorders: true,
                                            labelWidth: 50,
                                            items: [{
                                                    xtype: 'datefield',
                                                    format: 'Y-m-d',
                                                    fieldLabel: 'To',
                                                    id: 'ledger_to_date',
                                                    name: 'n[ledger_to_date]',
                                                    anchor: '95%',
                                                    tabIndex: 7
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: 0.16,
                                            hideBorders: true,
                                            labelWidth: 50,
                                            items: [{
                                                    xtype: 'button',
                                                    id: 'go_btn',
                                                    text: 'Go',
                                                    width: 50,
                                                    tabIndex: 8,
                                                    handler: function () {
                                                        Ext.getCmp('ledger_balance_grid_id').getStore().removeAll();

                                                        var load_params = {
                                                            company_id: Ext.getCmp('ledger_company_id').getValue(),
                                                            ledger_id: Ext.getCmp('ledger_balance_id').getValue(),
                                                            br_id: Ext.getCmp('ledger_branch_id').getValue(),
                                                            from_date: Ext.getCmp('ledger_from_date').getValue().format('Y-m-d'),
                                                            to_date: Ext.getCmp('ledger_to_date').getValue().format('Y-m-d'),
                                                            checked: Ext.getCmp('chk_customer').getValue()
                                                        };

                                                        Ext.Ajax.request({
                                                            url: modURL + '&op=getOpeningBalance',
                                                            method: 'POST',
                                                            params: load_params,
                                                            success: function (res) {

                                                                var tmp = Ext.decode(res.responseText);
                                                                if (tmp.success == true) {
                                                                    var data = tmp.data;
                                                                    Ext.getCmp('dr_tbar').setValue(data.FinalDrAmts);
                                                                    Ext.getCmp('cr_tbar').setValue(data.FinalCrAmts);
                                                                }
                                                            }
                                                        });

                                                        Ext.getCmp('ledger_balance_grid_id').getStore().load({
                                                            params: load_params
                                                        });
                                                    }
                                                }]
                                        }]
                                }]
                        }]
                }, ledger_balance_grid()
            ]
        });
        return panel;
    };
    /* End of ledger balance form*/
    /*Vehicle Receipt*/
    /*
     Trip combo store
     */
    var trip_store = new Ext.data.JsonStore({
        autoLoad: false,
        url: modURL + '&op=getTrip',
        method: 'post',
        fields: ['Trip', 'TripDate', 'trip_ID'],
        totalProperty: 'totalCount',
        root: 'data'
    });
    var vehicle_reciept_view = function () {

        var panel = new Ext.Panel({
            height: 250,
            frame: false,
            border: false,
            bodyStyle: {"padding": "5px 5px 5px 5px"},
            items: [
                {
                    hideBorders: true,
                    border: false,
                    html: '<b>Please select the trip that reached your branch and provide the time details</b>',
                    bodyStyle: {"padding": "15px 15px 15px 15px"}
                },
                {
                    xtype: 'fieldset',
                    title: 'Select Trip Name',
                    collapsible: false,
                    bodyStyle: {"padding": "5px 5px 5px 5px"},
                    items: [{
                            fieldLabel: 'Select Trip',
                            xtype: 'combo',
                            displayField: 'Trip',
                            valueField: 'trip_ID',
                            mode: 'remote',
                            id: 'vehicle_receipt_trip_id',
                            hiddenName: 'n[vehicle_receipt_trip_id]',
                            typeAhead: true,
                            minChars: 1,
                            triggerAction: 'all',
                            selectOnFocus: true,
                            lazyRender: true,
                            anchor: '98%',
                            allowBlank: false,
                            hideTrigger: true,
                            store: trip_store,
                            editable: true,
                            msgTarget: 'under',
                            tabIndex: 1

                        }

                    ]


                }, {
                    xtype: 'checkbox',
                    name: 'chk_truct',
                    boxLabel: 'This truck skipped the branch',
                    inputValue: ''


                }, {
                    layout: 'column',
                    hideBorders: true,
                    border: false,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            bodyStyle: {"padding": "5px 5px 5px 5px"},
                            items: [{xtype: 'fieldset',
                                    title: 'Time and date of truck arrival',
                                    collapsible: false,
                                    items: [
                                        {
                                            xtype: 'compositefield',
                                            hideLabel: true,
                                            defaults: {
                                                flex: 1
                                            },
                                            items: [{
                                                    xtype: 'timefield',
                                                    format: 'H:i',
                                                    id: 'vehicle_receipt_time',
                                                    name: 'n[vehicle_receipt_time]',
                                                    anchor: '55%'
                                                }, {
                                                    xtype: 'datefield',
                                                    format: 'Y/m/d ',
                                                    id: 'vehicle_receipt_date',
                                                    name: 'n[vehicle_receipt_date]',
                                                    anchor: '50%'
                                                }]
                                        }]
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.40,
                            bodyStyle: {"padding": "5px 5px 5px 5px"},
                            items: [
                                {xtype: 'fieldset',
                                    title: 'Odometer Reading',
                                    collapsible: false,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'cust_read',
                                            anchor: '95%',
                                            hideLabel: true
                                        }]
                                }]
                        }]
                }]
        });
        return panel;
    };
    var ledger_balance_gridStore = function () {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=ledgerBalanceGrid',
            method: 'post',
            fields: [''],
            totalProperty: 'totalCount',
            root: 'data'
        });
        return store;
    };

    var ledger_balance_grid = function () {


        var ledger_balance_grid_filter = new Ext.ux.grid.GridFilters({
            filters: []
        });

        ledger_balance_grid_filter.remote = true;
        ledger_balance_grid_filter.autoReload = true;
        var ledger_balance_grid_store = ledger_balance_gridStore();

        var grid = new Ext.grid.GridPanel({
            store: ledger_balance_grid_store,
            plugins: [new Ext.ux.grid.GroupSummary(), ledger_balance_grid_filter],
            id: 'ledger_balance_grid_id',
            autoScroll: true,
            height: 330,
            loadMask: true,
            viewConfig: {
                forceFit: true,
                markDirty: false,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true,
            frame: true,
            border: false, hideBorders: true,
            columns: [
                {
                    header: 'Sl. No',
                    sortable: true,
                    dataIndex: 'sl_no',
                    width: 50,
                    align: 'center'
                },
                {
                    header: 'Payment No.',
                    sortable: true,
                    dataIndex: 'voucher_no'
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'date'
                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'type'
                },
                {
                    header: 'Dr',
                    sortable: true,
                    dataIndex: 'dr',
                    align: 'center'
                }, {
                    header: 'Cr',
                    sortable: true,
                    dataIndex: 'cr',
                    align: 'center'
                },
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'name'
                }, {
                    header: 'Narration',
                    sortable: true,
                    dataIndex: 'narration'
                }],
            tbar: [
                '->', {
                    html: '&nbsp;Dr:&nbsp;'
                }, {
                    fieldLabel: 'Dr',
                    xtype: 'textfield',
                    id: 'dr_tbar',
                    tabIndex: 9,
                    allowBlank: false
                }, {xtype: 'tbseparator'},
                {
                    html: '&nbsp;Cr:&nbsp;'
                },
                {
                    fieldLabel: 'Cr',
                    xtype: 'textfield',
                    id: 'cr_tbar',
                    tabIndex: 10,
                    allowBlank: false
                }
            ]
        });
        return grid;
    };


    var bankCombo_Store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getbankDetails',
            fields: ['bankName', 'bankId'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true

        });
        return store;
    };
    var voucherStore = new Ext.data.ArrayStore({
        fields: ['id', 'name'],
        data: [
            ['1', 'Receipt'], ['2', 'Payment'], ['3', 'Journal Voucher'], ['4', 'Contra Entry']
        ],
        autoLoad: true
    });

    var backRec_grid_Store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listbankRecmaingrid',
                method: 'post'
            }),
            fields: ['bankRec_date', 'bankRec_No', 'bankRec_refno', 'bankRec_particulars', 'bankRec_amount', 'type_name', 'bankCreditRec_amount', 'bankInstrument_date',
                'bankRec_utrRefno', 'bankRec_narration', 'updated_on', 'selectedIds', 'acet_BankDate', 'cr', 'dr', 'type'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function (a, b, options) {

                }
            }

        });
        return store;
    };
    var backRec_Maingrid = function () {
        var backStore = bankCombo_Store();

        var backRec_master_store = backRec_grid_Store();
        var bankRec_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'date',
                    dataIndex: 'bankRec_date'
                }, {
                    type: 'string',
                    dataIndex: 'bankRec_No'
                }, {
                    type: 'string',
                    dataIndex: 'bankRec_particulars'
                }, {
                    type: 'string',
                    dataIndex: 'bankRec_amount'
                }, {
                    type: 'string',
                    dataIndex: 'bankRec_utrRefno'
                }, {
                    type: 'string',
                    dataIndex: 'bankRec_narration'
                }]
        });
        bankRec_filter.remote = true;
        bankRec_filter.autoReload = true;
        var back_reconcile_main_grid = new Ext.grid.GridPanel({
            store: backRec_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [bankRec_filter],
            id: 'backRec_grid_id',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'bankRec_date',
                    width: 20
                }, {
                    header: 'Particulars',
                    sortable: true,
                    dataIndex: 'bankRec_particulars'
                }, {
                    header: 'Voucher Type',
                    hidable: false,
                    dataIndex: 'type_name',
                }, {
                    header: 'Trans. Type',
                    hidable: false,
                    dataIndex: 'bankRec_refno',
                },
                {
                    header: 'Instrument Date',
                    sortable: true,
                    dataIndex: 'bankInstrument_date',
                    width: 20
                }, {
                    header: 'Bank Date',
                    sortable: true,
                    dataIndex: 'acet_BankDate',
                    width: 20
                },
                {
                    header: 'Debit',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'dr',
                    width: 20
                }, {
                    header: 'Credit',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'cr',
                    width: 20
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 20,
                    items: [
                        {
                            iconCls: 'my-icon2',
                            tooltip: 'Update Bank Date',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                updateBankDate(record);
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
            tbar: [
                {
                    html: '&nbsp;Bank Account:&nbsp;'
                }, {
                    fieldLabel: 'Bank Account',
                    xtype: 'combo',
                    store: backStore,
                    name: 'bank',
                    id: 'backRec_Acnt',
                    displayField: 'bankName',
                    valueField: 'bankId',
                    hiddenName: 'bank',
                    tabIndex: 200,
                    triggerAction: 'all',
                    mode: 'remote',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    maxChars: 50,
                    listeners: {
                        select: function () {
                            Ext.getCmp('backRec_searchDate').reset();
                            Ext.getCmp('backRec_searchToDate').reset();
                            Ext.getCmp('backRec_grid_id').getStore().removeAll();
                            Ext.getCmp('backRec_grid_id').getView().refresh();
                        }
                    }
                },
                {
                    html: '&nbsp;From:&nbsp;'
                }, {
                    fieldLabel: 'Date',
                    xtype: 'datefield',
                    id: 'backRec_searchDate',
                    name: 'searchDate',
                    tabIndex: 201,
                    anchor: '86%',
                    editable: true,
                    allowBlank: false,
                    value: new Date().add(Date.MONTH, -1),
                    format: 'd/m/Y'
                }, {
                    html: '&nbsp;To:&nbsp;'
                }, {
                    fieldLabel: 'Date',
                    xtype: 'datefield',
                    id: 'backRec_searchToDate',
                    name: 'searchDate',
                    anchor: '86%',
                    tabIndex: 202,
                    editable: true,
                    allowBlank: false,
                    value: new Date(),
                    format: 'd/m/Y'
                },
                '-',
                {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    tabIndex: 203,
                    id: 'backRec_filter_btn',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('backRec_Acnt').getValue())) {
                            var bank = Ext.getCmp('backRec_Acnt').getRawValue();
                            var dateFrom = Ext.getCmp('backRec_searchDate').getRawValue();
                            var dateTo = Ext.getCmp('backRec_searchToDate').getRawValue();

                            Application.Finascop_AccountsBkRec.showBankFunc(bank, dateFrom, dateTo);

                        } else {
                            Ext.Msg.alert("Notification", "Please select your Bank");
                        }

                    }
                }, '-', {
                    html: '&nbsp;Add:&nbsp;'
                }, {
                    fieldLabel: 'Voucher',
                    xtype: 'combo',
                    store: voucherStore,
                    name: 'bank',
                    id: 'backVoucher_Acnt',
                    displayField: 'name',
                    valueField: 'id',
                    hiddenName: 'backVoucher_Acnt',
                    //tabIndex: 9,
                    width: 100,
                    triggerAction: 'all',
                    mode: 'local',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    maxChars: 50,
                    listeners: {
                        select: function (combo, record) {
                            var type = record.data.name;
                            console.log('type', type);
                            if (_SESSION.AssignedBranchCount == 0)
                            {
                                Ext.Msg.alert('Notification', 'You do not have any company/branch assigned');
                            } else if (_SESSION.finascop_current_company_isactive == '0' || _SESSION.finascop_current_branch_isactive == '0')
                            {
                                var strg = (_SESSION.finascop_current_company_isactive == '0' ? 'Your selected company ' + _SESSION.finascop_current_company : 'Your selected branch ' + _SESSION.current_branch) + ' is disabled';
                                Ext.Msg.alert('Notification', strg);
                            } else
                            {
                                Application.Finascop_DataEntry.receipt_voucher(0, type);
                            }

                        }
                    }
                }, '-', {
                    html: '&nbsp;Opening Balance as per record:&nbsp;'
                }, {
                    fieldLabel: '',
                    align: 'left',
                    xtype: 'numberfield',
                    width: 100,
                    tabIndex: 205,
                    id: 'bankopeninigBalrecor',
                    name: 'bankopeninigBalrecor',
                    anchor: '99%',
                }, {
                    html: '&nbsp;Opening Balance as per bank:&nbsp;'
                }, {
                    fieldLabel: '',
                    align: 'left',
                    xtype: 'numberfield',
                    tabIndex: 206,
                    width: 100,
                    id: 'bankopeninigBalBank',
                    name: 'bankopeninigBalBank',
                    anchor: '99%',
                }, {
                    html: '&nbsp;Difference:&nbsp;'
                }, {
                    fieldLabel: '',
                    align: 'left',
                    xtype: 'numberfield',
                    width: 100,
                    id: 'bankopeningBalDiff',
                    name: 'bankopeningBalDiff',
                    anchor: '99%',
                }

            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: backRec_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                items: ['-', {
                        html: '&nbsp;Closing Balance as per record:&nbsp;'
                    }, {
                        fieldLabel: '',
                        align: 'left',
                        xtype: 'numberfield',
                        id: 'bankclosingBalrecor',
                        tabIndex: 208,
                        name: 'bankclosingBalrecor',
                        anchor: '99%',
                    }, {
                        html: '&nbsp;Closing Balance as per bank:&nbsp;'
                    }, {
                        fieldLabel: '',
                        align: 'left',
                        xtype: 'numberfield',
                        id: 'bankclosingBalBank',
                        tabIndex: 209,
                        name: 'bankclosingBalBank',
                        anchor: '99%',
                    }, {
                        html: '&nbsp;Difference:&nbsp;'
                    }, {
                        fieldLabel: '',
                        align: 'left',
                        xtype: 'numberfield',
                        id: 'bankclosingBalDiff',
                        name: 'bankclosingBalDiff',
                        anchor: '99%',
                    }
                ]
            }),
            stripeRows: true
        });

        return back_reconcile_main_grid;
    };

    var updateBankDate = function (record) {
        if (Ext.isEmpty(upInvQty_window)) {
            var upInvQty_window = new Ext.Window({
                id: 'upBankDate_window',
                layout: 'fit',
                width: 250,
                title: 'Update Bank Date',
                autoHeight: true,
                draggable: false,
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [new Ext.FormPanel({
                        frame: true,
                        monitorValid: true,
                        labelWidth: 100,
                        height: 100,
                        items: [{
                                fieldLabel: 'Bank Date',
                                xtype: 'datefield',
                                format: 'Y-m-d',
                                id: 'bankDate',
                                width: 100,
                                name: 'bankDate',
                                tabIndex: 93,
                                allowBlank: false
                            }]
                    })],
                buttons: [{
                        text: 'Update',
                        id: 'btnsave',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var bankRec_refno = record.get('bankRec_refno');
                            var bankDate = Ext.getCmp('bankDate').getValue();
                            Ext.Ajax.request({
                                url: modURL + '&op=updateBankDate',
                                method: 'POST',
                                params: {
                                    bankRec_refno: bankRec_refno,
                                    bankDate: bankDate
                                },
                                success: function (response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success === true)
                                    {
                                        Ext.getCmp('backRec_grid_id').getStore().load({
                                            params: {
                                                bankname: Ext.getCmp('backRec_Acnt').getRawValue(),
                                                ledgerId: Ext.getCmp('backRec_Acnt').getValue(),
                                                date: Ext.util.Format.date(Ext.getCmp('backRec_searchDate').getValue(), 'Y-m-d'),
                                                dateto: Ext.util.Format.date(Ext.getCmp('backRec_searchToDate').getValue(), 'Y-m-d')
                                            }, callback: function () {
                                                Ext.getCmp('upBankDate_window').close();
                                            }
                                        });
                                    } else
                                    {
                                        Ext.MessageBox.alert('Failed');
                                    }
                                }
                            });
                        }
                    }, {
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            upInvQty_window.close();
                        }
                    }]
            });

        }
        upInvQty_window.doLayout();
        upInvQty_window.show(this);
        upInvQty_window.center();
    };

    var reconciliation_Masterpanel = function (panelId) {

        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: panelId,
            title: 'Bank Account',
            items: [backRec_Maingrid()]
        });
        return panel;
    };
    var collectBankDetailsForm = function (dateFrom, dateTo) {
        return new Ext.form.FormPanel({
            height: 150,
            frame: true,
            border: false,
            id: 'bankBackReconcFormPanel',
            items: [{
                    html: '&nbsp;<p align="center"><b>Provide the opening & closing balance </br> of the Bank on these dates you choose.</b></p></br> &nbsp;'
                }, {
                    xtype: 'compositefield',
                    hideLabel: true,
                    border: false,
                    defaults: {
                        flex: 1
                    },
                    items: [{
                            html: '&nbsp;Opening Balance &nbsp;'
                        }, {
                            xtype: 'textfield',
                            labelWidth: 200,
                            fieldLabel: 'Bank Opening Balance',
                            id: 'bkrec_bankOpeningBalance',
                            name: 'bkrec_bankOpeningBalance',
                            allowBlank: false,
                            anchor: '99%'
                        }, {
                            html: '&nbsp;on ' + dateFrom + '.&nbsp;'
                        }]
                },
                {
                    xtype: 'compositefield',
                    hideLabel: true,
                    border: false,
                    defaults: {
                        flex: 1
                    },
                    items: [{
                            html: '&nbsp;Closing Balance &nbsp;'
                        }, {
                            xtype: 'textfield',
                            labelWidth: 200,
                            fieldLabel: 'Bank Closing Balance',
                            id: 'bkrec_bankCloseingBalance',
                            name: 'bkrec_bankCloseingBalance',
                            allowBlank: false,
                            anchor: '99%'
                        }, {
                            html: '&nbsp;on ' + dateTo + '.&nbsp;'
                        }]
                }
            ]
        });
    };

    function diff(num1, num2) {
        if (num1 > num2) {
            return num1 - num2
        } else {
            return num2 - num1
        }
    }
    return {
        ledger_balance: function () {

            var ledger_balance_window = Ext.getCmp('ledger_balance_window');
            if (Ext.isEmpty(ledger_balance_window)) {
                var ledger_balance_window = new Ext.Window({
                    id: 'ledger_balance_window',
                    title: 'Ledger Balance',
                    //iconCls: '',
                    modal: true,
                    layout: 'fit',
                    width: 850,
                    height: 400,
                    shadow: false,
                    resizable: false,
                    items: ledger_balance_view(),
                    buttons: [{
                            text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            handler: function () {

                            }
                        }, {
                            text: 'Close',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            handler: function () {
                                ledger_balance_window.close();
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            if (_SESSION.typId == '3' || _SESSION.typId == '4')
                            {
                                Ext.getCmp('ledger_branch_id').store.load({
                                    callback: function () {

                                        Ext.getCmp('ledger_branch_id').setValue(_SESSION.typdetsid);

                                    }
                                });

                            }
                        }
                    }
                });
            }
            ledger_balance_window.show();
            ledger_balance_window.doLayout();
            ledger_balance_window.center();

        },
        vehicle_reciept: function () {

            var vehicle_reciept_window = Ext.getCmp('vehicle_reciept_window');
            if (Ext.isEmpty(vehicle_reciept_window)) {
                var vehicle_reciept_window = new Ext.Window({
                    id: 'vehicle_reciept_window',
                    title: 'Vehicle Receipt',
                    //iconCls: '',
                    modal: true,
                    layout: 'fit',
                    width: 450,
                    height: 300,
                    shadow: false,
                    resizable: false,
                    items: vehicle_reciept_view(),
                    buttons: [{
                            text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            handler: function () {

                            }
                        }, {
                            text: 'Close',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            handler: function () {
                                vehicle_reciept_window.close();
                            }
                        }]
                });
            }
            vehicle_reciept_window.show();
            vehicle_reciept_window.doLayout();
            vehicle_reciept_window.center();
        },
        initBackReconciliation: function () {
            var panelId = 'back_reconcilation_panel';
            var backrec_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(backrec_panel)) {
                backrec_panel = reconciliation_Masterpanel(panelId);
                Application.UI.addTab(backrec_panel);
                backrec_panel.doLayout();
            } else {
                Application.UI.addTab(backrec_panel);
                backrec_panel.doLayout();
            }

        }, showBankFunc: function (title, dateFrom, dateTo) {
            var showBankinBackReconcWindow = new Ext.Window({
                id: "windowBackReconcBank",
                shadow: false,
                height: 200,
                width: 350,
                title: title,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [collectBankDetailsForm(dateFrom, dateTo)],
                buttons: [
                    {
                        text: 'Submit',
                        'align': 'center',
                        handler: function () {
                            //bkrec_bankOpeningBalance,bkrec_bankCloseingBalance
                            Ext.Ajax.request({
                                waitTitle: 'Please Wait',
                                waitMsg: 'Sending...',
                                url: modURL + '&op=getLedgerBalaces',
                                params: {
                                    ledger: Ext.getCmp('backRec_Acnt').getValue(),
                                    date_from: Ext.util.Format.date(Ext.getCmp('backRec_searchDate').getValue(), 'Y-m-d'),
                                    date_to: Ext.util.Format.date(Ext.getCmp('backRec_searchToDate').getValue(), 'Y-m-d'),
                                    //audit_status: auidt_status

                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success !== undefined && tmp.success === true) {
                                        Ext.getCmp('bankopeninigBalrecor').setValue(Ext.util.Format.number(tmp.openingBal, FINASCOP_CURRENCY_FORMAT));
                                        Ext.getCmp('bankclosingBalrecor').setValue(Ext.util.Format.number(tmp.closingBal, FINASCOP_CURRENCY_FORMAT));


                                        var bkrec_bankOpeningBalance = Ext.getCmp('bkrec_bankOpeningBalance').getValue();
                                        var bkrec_bankCloseingBalance = Ext.getCmp('bkrec_bankCloseingBalance').getValue();
                                        Ext.getCmp('bankopeninigBalBank').setValue(bkrec_bankOpeningBalance);
                                        Ext.getCmp('bankclosingBalBank').setValue(bkrec_bankCloseingBalance);

                                        var bankopeningBalDiff = diff(tmp.openingBal, bkrec_bankOpeningBalance);
                                        var bankclosingBalDiff = diff(tmp.closingBal, bkrec_bankCloseingBalance);
                                        Ext.getCmp('bankopeningBalDiff').setValue(Ext.util.Format.number(bankopeningBalDiff, FINASCOP_CURRENCY_FORMAT));
                                        Ext.getCmp('bankclosingBalDiff').setValue(Ext.util.Format.number(bankclosingBalDiff, FINASCOP_CURRENCY_FORMAT));


                                        Ext.getCmp('backRec_grid_id').getStore().load({
                                            params: {
                                                bankname: Ext.getCmp('backRec_Acnt').getRawValue(),
                                                ledgerId: Ext.getCmp('backRec_Acnt').getValue(),
                                                date: Ext.util.Format.date(Ext.getCmp('backRec_searchDate').getValue(), 'Y-m-d'),
                                                dateto: Ext.util.Format.date(Ext.getCmp('backRec_searchToDate').getValue(), 'Y-m-d')
                                            }, callback: function () {
                                                Ext.getCmp('windowBackReconcBank').close();
                                            }
                                        });
                                    }
                                }
                            });

                        }
                    }
                ], listeners: {
                    afterrender: function () {

                    }
                }

            });

            showBankinBackReconcWindow.doLayout();
            showBankinBackReconcWindow.show();
            showBankinBackReconcWindow.center();
        },
    };
}();