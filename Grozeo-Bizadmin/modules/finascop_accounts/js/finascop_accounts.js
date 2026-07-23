Application.Finascop_Accounts = function () {

    var recs_per_page = 23;
    var bankreclastParameters;
    var modURL = '?module=finascop_accounts';

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


    var loadBankReconciliation = function () {
        var bankReconciliation_grid = Ext.getCmp('bankRec_grid_id');
        bankReconciliation_grid.getStore().removeAll();
        bankReconciliation_grid.getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }

    var resultGrid = function () {
        var store = resultStore();
        var sm = new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
        var grid = new Ext.grid.GridPanel({
            store: store,
            height: 400,
            id: 'result_grid_id',
            loadMask: true,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: sm,
            columns: [
                {
                    header: 'No',
                    dataIndex: ''
                }, {
                    header: 'Name',
                    dataIndex: ''
                }, {
                    header: 'Narration',
                    dataIndex: ''
                }, {
                    header: 'Action',
                    dataIndex: ''
                }],
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);


                }
            },
            stripeRows: true
        });
        return grid;
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

    var details_grid = function () {

        var store = detailsStore();
        var sm = new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
        var grid = new Ext.grid.GridPanel({
            store: store,
            height: 130,
            loadMask: true,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            id: 'sub_grid',
            columns: [
                {
                    header: 'Sl. No.',
                    dataIndex: ''
                }, {
                    header: 'Mode',
                    dataIndex: ''
                }, {
                    header: 'Ledger',
                    dataIndex: ''
                }, {
                    header: 'Dr.',
                    dataIndex: ''
                }, {
                    header: 'Cr.',
                    dataIndex: ''
                }],
            sm: sm,
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);


                }
            },
            stripeRows: true
        });
        return grid;
    };

    var changeFields = function () {
        var type = arguments[0];
        /*['0', 'Bank Receipt'], ['1', 'HPA Payments'], ['1', 'Journal Voucher']*/
        switch (type) {
            case 0:
                Ext.getCmp('receipt_details').setTitle('Recipt Details');
                Ext.getCmp('paid_to').hide();
                Ext.getCmp('for_hpa').hide();
                Ext.getCmp('for_hpa_text').hide();
                Ext.getCmp('sel_all').hide();
                Ext.getCmp('head_office').hide();
                Ext.getCmp('branch_sel_1').hide();
                Ext.getCmp('receipt_branch_id').hide();
                Ext.getCmp('branch_sel_2').show();
                Ext.getCmp('receipt_customer_id').show();
                Ext.getCmp('received_fieldset').show();
                Ext.getCmp('received_from').show();
                Ext.getCmp('sub_grid').setHeight(125);
                Ext.getCmp('rb-search2').show();
                Ext.getCmp('ledger_fieldset').setTitle('Select Ledger');
                Ext.getCmp('customer_only').hide();
                Ext.getCmp('cust_customer_id').hide();
                break;
            case 1:
                Ext.getCmp('rb-search2').show();
                Ext.getCmp('branch_sel_1').hide();
                Ext.getCmp('receipt_branch_id').hide();
                Ext.getCmp('branch_sel_2').hide();
                Ext.getCmp('receipt_customer_id').hide();
                Ext.getCmp('received_fieldset').hide();
                Ext.getCmp('received_from').hide();
                Ext.getCmp('paid_to').show();
                Ext.getCmp('for_hpa').show();
                Ext.getCmp('for_hpa_text').show();
                Ext.getCmp('receipt_details').setTitle('Payment Details');
                Ext.getCmp('sub_grid').setHeight(207);
                Ext.getCmp('ledger_fieldset').setTitle('Select Ledger');
                Ext.getCmp('customer_only').hide();
                Ext.getCmp('cust_customer_id').hide();
                break;
            case 2:
                Ext.getCmp('for_hpa').hide();
                Ext.getCmp('for_hpa_text').hide();
                Ext.getCmp('receipt_details').setTitle('Payment Details');
                Ext.getCmp('rb-search2').hide();
                Ext.getCmp('for_journal').show();
                Ext.getCmp('received_fieldset').hide();
                Ext.getCmp('sub_grid').setHeight(200);
                Ext.getCmp('ledger_fieldset').setTitle('');
                Ext.getCmp('customer_only').show();
                Ext.getCmp('branch_sel_1').hide();
                Ext.getCmp('receipt_branch_id').hide();
                Ext.getCmp('sel_all').show();
                Ext.getCmp('head_office').show();
                Ext.getCmp('received_from').hide();
                Ext.getCmp('cust_customer_id').show();
                break;
            case 3:
                Ext.getCmp('for_hpa').hide();
                Ext.getCmp('for_hpa_text').hide();
                Ext.getCmp('receipt_details').setTitle('Receipt Details');

                Ext.getCmp('rb-search2').show();

                Ext.getCmp('for_journal').hide();
                Ext.getCmp('received_fieldset').hide();
                Ext.getCmp('sub_grid').setHeight(200);
                Ext.getCmp('ledger_fieldset').setTitle('Select Ledger');
                Ext.getCmp('customer_only').hide();
                Ext.getCmp('branch_sel_1').show();
                Ext.getCmp('receipt_branch_id').hide();
                Ext.getCmp('sel_all').hide();
                Ext.getCmp('head_office').show();
                Ext.getCmp('received_from').show();
                Ext.getCmp('cust_customer_id').hide();
                break;
            case 4:
                Ext.getCmp('for_hpa').hide();
                Ext.getCmp('for_hpa_text').hide();
                Ext.getCmp('receipt_details').setTitle('Payment Details');

                Ext.getCmp('rb-search2').show();

                Ext.getCmp('for_journal').hide();
                Ext.getCmp('received_fieldset').hide();
                Ext.getCmp('sub_grid').setHeight(200);
                Ext.getCmp('ledger_fieldset').setTitle('Select Ledger');
                Ext.getCmp('customer_only').hide();
                Ext.getCmp('branch_sel_1').show();
                Ext.getCmp('receipt_branch_id').hide();
                Ext.getCmp('sel_all').hide();
                Ext.getCmp('head_office').show();
                Ext.getCmp('received_from').hide();
                Ext.getCmp('paid_to').show();
                Ext.getCmp('cust_customer_id').hide();

                break;
        }


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


    var upload_reconciliation_window = function (bank) {

        var selectUploadedUTRs = function (selectedIds, records) {
            var check_box = Ext.getCmp('bankRec_grid_id').getSelectionModel();

            Ext.each(records, function (record, index, items) {

                var acet_NO = record.data.bankRec_refno;

                if (selectedIds.indexOf(acet_NO) > -1)
                {
                    check_box.selectRow(index, true);
                }
            });
        }
        //console.log(rec.get('bankRec_refno'));
        var item_form = viewItem_panel(bank);
        var upload_reconciliation_window = Ext.getCmp('upload_reconciliation_window');
        if (Ext.isEmpty(upload_reconciliation_window)) {
            var upload_reconciliation_window = new Ext.Window({
                id: 'upload_reconciliation_window',
                title: 'Upload Reconciliation',
                //iconCls: 'finascop_upload_file',
                modal: true,
                layout: 'fit',
                width: 360,
                autoHeight: true,
                shadow: false,
                resizable: false,
                items: [item_form
                ],
                buttons: [{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function (grid, rowIndex, colIndex) {
                            var uploadform = Ext.getCmp('itemPanel');
                            if (uploadform.getForm().isValid()) {
                                var t = new Date();
                                var selectedIds = "";
                                var t_stamp = t.format("YmdHis");
                                uploadform.getForm().submit({
                                    url: modURL + '&op=reconciliation_upload',
                                    waitMsg: 'Uploading file...',
                                    params: {
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    success: function (form, action) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success === true) {

                                            Ext.MessageBox.alert('Notification', tmp.msg, function () {
                                                selectedIds = tmp.data;
                                                var bankgridstore = Ext.getCmp('bankRec_grid_id').getStore();
                                                bankgridstore.baseParams = {
                                                    bankname: Ext.getCmp('bankRec_Acnt').getRawValue(),
                                                    ledgerId: Ext.getCmp('bankRec_Acnt').getValue(),
                                                    date: Ext.util.Format.date(Ext.getCmp('bankRec_searchDate').getValue(), 'd/m/Y')
                                                };
                                                bankgridstore.load({
                                                    callback: function (records, opt, success) {

                                                        selectUploadedUTRs(selectedIds, records);
                                                    }
                                                });

                                                upload_reconciliation_window.close();
                                            });

                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg, function () {
                                                upload_reconciliation_window.close();
                                            });

                                        }

                                    }
                                });
                            }
                        }
                    },
                    {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            upload_reconciliation_window.close();
                        }
                    }]
            });
        }

        upload_reconciliation_window.show();
        upload_reconciliation_window.doLayout();
        upload_reconciliation_window.center();
    };

    var viewItem_panel = function (bank) {


        var reconciliation_template_store = new Ext.data.JsonStore({
            method: 'post',
            fields: ["value", "text"],
            data: [["0", "Template 1"], ["1", "Template 2"]],
            //url: modURL + '&op=listreconciliation_ledger',
            //fields: ['accled_Ledger_Id', 'ledgertypename'],
            //totalProperty: 'totalCount',
            //root: 'data',
            autoLoad: true
        });

        var itemPanel = new Ext.FormPanel({
            id: 'itemPanel',
            autoHeight: true,
            fileUpload: true,
            frame: true,
            monitorValid: true,
            items: [{
                    fieldLabel: 'Bank Name',
                    labelWidth: 50,
                    xtype: 'hidden',
                    id: 'hid_bankName',
                    name: 'bank',
                    value: bank,
                    anchor: '88%',
                    readOnly: true,
                    editable: false,
                    allowBlank: false
                },
                {
                    xtype: 'combo',
                    fieldLabel: 'Template Name',
                    id: 'template_name',
                    name: 'template_name',
                    hiddenName: 'template_name',
                    anchor: '95%',
                    allowBlank: true,
                    displayField: 'ledgertypename',
                    valueField: 'accled_Ledger_Id',
                    store: reconciliation_template_store,
                    triggerAction: 'all',
                    forceSelection: true,
                    typeAhead: true
                },
                {
                    fieldLabel: 'UTR Number Column',
                    labelWidth: 75,
                    xtype: 'textfield',
                    id: 'utrNo',
                    name: 'utrNoColumn',
                    anchor: '95%',
                    allowBlank: false
                },
                {
                    xtype: 'fileuploadfield',
                    id: 'template_file',
                    anchor: '95%',
                    fieldLabel: 'Upload Template',
                    name: 'template_file',
                    allowBlank: true,
                    buttonOnly: true,
                    buttonText: 'Browse',
                    buttonCfg: {
                        text: '',
                        iconCls: 'finascop_upload_file',
                        width: 100
                    }
                }
            ]
        });
        return itemPanel;
    }


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


    var bankRec_Maingrid = function () {
        var bankStore = bankCombo_Store();

        var bankRec_grid_Store = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=listbankRecmaingrid',
                fields: ['bankRec_date', 'bankRec_No', 'bankRec_refno', 'bankRec_particulars', 'bankRec_amount',
                    'bankRec_utrRefno', 'bankRec_narration', 'updated_on', 'selectedIds'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        bankreclastParameters = options.params;

                    }
                }

            });
            return store;
        };
        var check_box = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true
        });
        var bankRec_master_store = bankRec_grid_Store();
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
        var stock_register_main_grid = new Ext.grid.GridPanel({
            store: bankRec_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [bankRec_filter],
            id: 'bankRec_grid_id',
            columns: [new Ext.grid.RowNumberer(),
                check_box,
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'bankRec_date',
                    width: 20
                },
                {
                    header: 'Ref.No',
                    sortable: true,
                    dataIndex: 'bankRec_No',
                    width: 20
                },
                {header: '',
                    hidable: false,
                    hidden: true,
                    dataIndex: 'bankRec_refno',
                },
                {
                    header: 'Particulars',
                    sortable: true,
                    dataIndex: 'bankRec_particulars',
                    width: 30
                }, {
                    header: 'Amount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'bankRec_amount',
                    width: 20
                },
                {
                    header: 'UTR Ref.No',
                    sortable: true,
                    dataIndex: 'bankRec_utrRefno',
                    width: 30
                }, {
                    header: 'Narration',
                    sortable: true,
                    dataIndex: 'bankRec_narration',
                    width: 150
                }],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            sm: check_box,
//            sm: new Ext.grid.RowSelectionModel({
//                singleSelect: true,
//                listeners: {
//                    //selectionchange: gridSelectionChanged
//                }
//            }),
            tbar: [
                {
                    html: '&nbsp;Bank Account:&nbsp;'
                }, {
                    fieldLabel: 'Bank Account',
                    xtype: 'combo',
                    store: bankStore,
                    name: 'bank',
                    id: 'bankRec_Acnt',
                    displayField: 'bankName',
                    valueField: 'bankId',
                    hiddenName: 'bank',
                    tabIndex: 9,
                    triggerAction: 'all',
                    mode: 'local',
                    minChars: 2,
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    maxChars: 50,
                    listeners: {
                        select: function () {
                            Ext.getCmp('bankRec_searchDate').reset();
                            Ext.getCmp('bankRec_grid_id').getStore().removeAll();
                            Ext.getCmp('bankRec_grid_id').getView().refresh();
                        }
                    }
                },
                {
                    html: '&nbsp;Date:&nbsp;'
                }, {
                    fieldLabel: 'Date',
                    xtype: 'datefield',
                    id: 'bankRec_searchDate',
                    name: 'searchDate',
                    anchor: '86%',
                    editable: true,
                    allowBlank: false,
                    value: new Date().add(Date.DAY, -1),
                    format: 'd/m/Y'
                },
                '-',
                {
                    xtype: 'button',
                    text: 'Filter',
                    iconCls: 'finascop_search_btn',
                    id: 'bankRec_filter_btn',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('bankRec_Acnt').getValue())) {
                            bankRec_master_store.baseParams = {
                                bankname: Ext.getCmp('bankRec_Acnt').getRawValue(),
                                ledgerId: Ext.getCmp('bankRec_Acnt').getValue(),
                                date: Ext.util.Format.date(Ext.getCmp('bankRec_searchDate').getValue(), 'Y-m-d')
                            };
                            bankRec_master_store.load();
                        } else {
                            Ext.Msg.alert("Notification", "Please select your Bank");
                        }

                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Reset',
                    iconCls: 'finascop_my-resetpass',
                    handler: function () {
                        Ext.getCmp('bankRec_Acnt').reset();
                        bankRec_master_store.removeAll();
                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Import Reconciliation', iconCls: 'finascop_import',
                    id: 'import_reconciliation',
                    hidden: true,
                    handler: function (grid, rowIndex, colIndex) {
                        Ext.getCmp('bankRec_grid_id').getStore().removeAll();
                        Ext.getCmp('bankRec_grid_id').getView().refresh();

                        var bank = Ext.getCmp('bankRec_Acnt').getValue();
                        if (!Ext.isEmpty(Ext.getCmp('bankRec_Acnt').getValue())) {
                            upload_reconciliation_window(bank);
                        } else {
                            Ext.Msg.alert("Notification", "Please select your Bank");
                        }
                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (bankRec_master_store.getTotalCount() <= 0) {
                            Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                if (btn == 'no') {
                                    return;
                                }
                            });
                        }

                        var indexes = [];
                        var heads = [];

                        /**
                         * @modified by Aparna Ravvendran <aparna@saturn.in>
                         * @modified On 27-Mar-2013
                         *
                         * pass total no of headers of selected grid for export excel
                         */
                        var filterData = Ext.encode(bankreclastParameters);
                        for (var i = 0; i < Ext.getCmp('bankRec_grid_id').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('bankRec_grid_id').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('bankRec_grid_id').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('bankRec_grid_id').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }
                        console.log('dataindexes', dataindexes);
                        postToUrl(modURL + '&op=bankreceiptexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }

            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: bankRec_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                // buttonAlign:'center',
                items: ['-', {xtype: 'hidden',
                        fieldLabel: '',
                        align: 'left',
                        id: 'bankrechidden',
                        name: 'bankrechidden',
                        anchor: '99%',
                        hideLabel: true},
                    {
                        xtype: 'button',
                        text: 'Approve Selected',
                        id: 'sel_approve_btn',
                        iconCls: 'finascop_approved',
                        align: 'center',
                        handler: function () {
                            var sm = Ext.getCmp('bankRec_grid_id').getSelectionModel().getSelections();
                            //var comma = "";
                            var selectedRecords = [];

                            if (!Ext.isEmpty(sm)) {

                                var count = sm.length;
                                var j = 0;

                                for (var i = 0; i < count; i++) {
                                    var tmp = {bankRec_refno: sm[i].get('bankRec_refno'), updated_on: sm[i].get('updated_on')};
                                    selectedRecords[i] = tmp;

                                }

                            }
                            approveSelected(selectedRecords);
                        }
                    }, '-']
            }),
            stripeRows: true
        });

        return stock_register_main_grid;
    };
    var approveSelected = function (selectedRecords) {
        var recon_maingrid = Ext.getCmp('bankRec_grid_id');
        var recon_maingridstore = recon_maingrid.getStore();
        var recon_maingridData = Ext.pluck(recon_maingridstore.getRange(), 'data');
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var form_data = {
            gridData: Ext.encode(recon_maingridData),
            selectedRecords: selectedRecords,
            apikey: _SESSION.apikey,
            tstamp: t_stamp
        };
        var params = {
            action: "Update",
            module: "approving",
            op: "approveSelected",
            id: "",
            extrainfo: "asd"
        };
        APICall(params, Application.Finascop_Accounts.approve_selectedBankRec(selectedRecords), form_data);
//        APICall(params, function () {
//            return  Application.Finascop_Accounts.approve_selectedBankRec(selectedRecords);
//        }, form_data);

    };

    var reconciliation_Masterpanel = function () {

        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: 'bankRec_MasterPanel',
            title: 'Payment Gateway',
            //iconCls: 'finascop_bank',
            items: [bankRec_Maingrid()]
        });
        return panel;
    };



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
        initBankReconciliation: function () {
            var panelId = 'bank_reconcilation_panel';
            var bankrec_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(bankrec_panel)) {
                bankrec_panel = reconciliation_Masterpanel();
                Application.UI.addTab(bankrec_panel);
                bankrec_panel.doLayout();
            } else {
                Application.UI.addTab(bankrec_panel);
                bankrec_panel.doLayout();
            }

        },
        approve_selectedBankRec: function (selectedRecords) {
            var recon_maingrid = Ext.getCmp('bankRec_grid_id');
            var recon_maingridstore = recon_maingrid.getStore();
            var recon_maingridData = Ext.pluck(recon_maingridstore.getRange(), 'data');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=approveSelected',
                method: 'POST',
                params: {
                    bankrecgridData: Ext.encode(recon_maingridData),
//                    acet_NO: selectedIds,
//                    updated_on: updated_on,
//                    reason: "approved selected bank reconciliation",
//                    type: '',
                    selectedRecords: Ext.encode(selectedRecords),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.alert("Notification", tmp.msg, function () {
                            Ext.getCmp('bankRec_grid_id').getStore().load();
                            Ext.getCmp('bankRec_grid_id').getView().refresh();
                        });

                    } else {
                        Ext.MessageBox.alert("Notification", tmp.msg, function () {
                        });
                    }
                },
                failure: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });

        }

    };
}();