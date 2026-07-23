Application.GSTReports = function ()
{
    var recs_per_page = 25;
    var modURL = '?module=gst_reports';
    var gstReportMasterPanel = function (panelId) {
        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: panelId,
            title: 'GST Reports',
            items: [gstreport_Maingrid()]
        });
        return panel;
    };
    var gstReport_grid_store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listGSTReportsGrid',
                method: 'post'
            }),
            fields: ['acet_NO', 'actr_Date', 'type_name', 'actr_Date', 'acet_BankDate', 'cr', 'dr', 'type', 'bankRec_particulars', 'drcr_amount'],
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
    var gstCombo_Store = new Ext.data.ArrayStore({
        fields: ['id', 'name'],
        data: [
            ['1', 'IGST'], ['2', 'CGST'], ['3', 'SGST'], ['4', 'KFC'], ['5', 'TCS']
        ],
        autoLoad: true
    });
    var gstreport_Maingrid = function () {


        var gstrepo_master_store = gstReport_grid_store();
        var gstReport_filter = new Ext.ux.grid.GridFilters({
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
        gstReport_filter.remote = true;
        gstReport_filter.autoReload = true;
        var gst_report_main_grid = new Ext.grid.GridPanel({
            store: gstrepo_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [gstReport_filter],
            id: 'gst_report_grid_id',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'actr_Date',
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
                    header: 'Voucher No',
                    hidable: false,
                    dataIndex: 'acet_NO',
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
                    html: '&nbsp;Tax:&nbsp;'
                }, {
                    fieldLabel: 'Tax',
                    xtype: 'combo',
                    store: gstCombo_Store,
                    name: 'gstReportTax',
                    id: 'gstReportTax',
                    displayField: 'name',
                    valueField: 'id',
                    hiddenName: 'gstReportTax',
                    tabIndex: 9,
                    triggerAction: 'all',
                    mode: 'local',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    maxChars: 50,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gstrep_searchDate').reset();
                            Ext.getCmp('gstrep_searchToDate').reset();
                            Ext.getCmp('gst_report_grid_id').getStore().removeAll();
                            Ext.getCmp('gst_report_grid_id').getView().refresh();
                        }
                    }
                },'-',
                {
                    html: '&nbsp;From:&nbsp;'
                }, {
                    fieldLabel: 'Date',
                    xtype: 'datefield',
                    id: 'gstrep_searchDate',
                    name: 'searchDate',
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
                    id: 'gstrep_searchToDate',
                    name: 'searchDate',
                    anchor: '86%',
                    editable: true,
                    allowBlank: false,
                    value: new Date(),
                    format: 'd/m/Y'
                },
                '-',
                {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    id: 'backRec_filter_btn',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('gstReportTax').getValue())) {
                            var gstReportTax = Ext.getCmp('gstReportTax').getValue();
                            var dateFrom = Ext.getCmp('gstrep_searchDate').getValue();
                            var dateTo = Ext.getCmp('gstrep_searchToDate').getValue();

                            Ext.getCmp('gst_report_grid_id').getStore().load({
                                params: {
                                    gstReportTax: gstReportTax,
                                    date: Ext.util.Format.date(dateFrom, 'Y-m-d'),
                                    dateto: Ext.util.Format.date(dateTo, 'Y-m-d')
                                }, callback: function () {
                                }
                            });

                        } else {
                            Ext.Msg.alert("Notification", "Please select your Bank");
                        }

                    }
                }, '->', , {
                    xtype: 'button',
                    text: 'Export',
                    //iconCls: 'exporttoexcel',
                    iconCls: 'icon_excel',
                    style: "padding-left: 10px;",
                    handler: function () {
                        postToUrl(modURL + '&op=getreportrexport', '', 'post', 'downloadIframe');
                    }
                }

            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: gstrepo_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });

        return gst_report_main_grid;
    };

    var debitNoteMasterPanel = function (panelId) {
        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: panelId,
            title: 'Debit Note Reports',
            items: [debitNotereports_Maingrid()]
        });
        return panel;
    };
    var debitNote_grid_store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listLedgerReportsGrid',
                method: 'post'
            }),
            fields: ['acet_NO', 'account', 'ledg_Id', 'actr_amount', 'narration', 'particular', 'actr_Date', 'type_name', 'debit', 'credit', 'drcr_amount'],
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
    var debitNotereports_Maingrid = function () {
        var ledgerDeditCombo_Store = new Ext.data.JsonStore({
            fields: ['id', 'name'],
            url: modURL + '&op=getledgerDeditCombo',
            autoLoad: true,
            method: 'post',
        });
        var debitNoterepo_master_store = debitNote_grid_store();
        var debitNoteReport_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'acet_NO'
                }, {
                    type: 'string',
                    dataIndex: 'account'
                }, {
                    type: 'string',
                    dataIndex: 'actr_amount'
                }, {
                    type: 'string',
                    dataIndex: 'narration'
                }, {
                    type: 'string',
                    dataIndex: 'particular'
                }]
        });
        debitNoteReport_filter.remote = true;
        debitNoteReport_filter.autoReload = true;
        var gst_report_main_grid = new Ext.grid.GridPanel({
            store: debitNoterepo_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [debitNoteReport_filter],
            id: 'debit_note_report_grid_id',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'actr_Date'
                },
                {
                    header: 'Particulars',
                    sortable: true,
                    dataIndex: 'particular'
                }, {
                    header: 'Voucher Type',
                    sortable: true,
                    dataIndex: 'type_name',
                }, {
                    header: 'Voucher No',
                    sortable: true,
                    dataIndex: 'acet_NO',
                    width: 20
                },
                {
                    header: 'Narration',
                    sortable: true,
                    dataIndex: 'narration'
                }, {
                    header: 'Debit',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'debit',
                    width: 20
                }, {
                    header: 'Credit',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'credit',
                    width: 20
                }, {
                    width: 10,
                    hidden: true
                }, {
                    width: 10,
                    hidden: true
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
            tbar: [{
                    html: '&nbsp;Ledger:&nbsp;'
                }, {
                    fieldLabel: 'Ledger',
                    xtype: 'combo',
                    store: ledgerDeditCombo_Store,
                    name: 'ledgerDeditCombo',
                    id: 'ledgerDeditCombo',
                    displayField: 'name',
                    valueField: 'id',
                    hiddenName: 'ledgerDeditCombo',
                    tabIndex: 9,
                    triggerAction: 'all',
                    mode: 'local',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    maxChars: 50,
                    listeners: {
                        select: function () {
                            Ext.getCmp('ledgerrep_searchDate').reset();
                            Ext.getCmp('ledgerrep_searchToDate').reset();
                            Ext.getCmp('debit_note_report_grid_id').getStore().removeAll();
                            Ext.getCmp('debit_note_report_grid_id').getView().refresh();
                        }
                    }
                },'-',
                {
                    html: '&nbsp;From:&nbsp;'
                }, {
                    fieldLabel: 'Date',
                    xtype: 'datefield',
                    id: 'ledgerrep_searchDate',
                    name: 'searchDate',
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
                    id: 'ledgerrep_searchToDate',
                    name: 'searchDate',
                    anchor: '86%',
                    editable: true,
                    allowBlank: false,
                    value: new Date(),
                    format: 'd/m/Y'
                },
                '-',
                {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    id: 'backRec_filter_btn',
                    handler: function () {
                        var dateFrom = Ext.getCmp('ledgerrep_searchDate').getValue();
                        var dateTo = Ext.getCmp('ledgerrep_searchToDate').getValue();

                        Ext.getCmp('debit_note_report_grid_id').getStore().load({
                            params: {
                                date: Ext.util.Format.date(dateFrom, 'Y-m-d'),
                                dateto: Ext.util.Format.date(dateTo, 'Y-m-d'),
                                ledgerDeditCombo: Ext.getCmp('ledgerDeditCombo').getValue()
                            }, callback: function () {
                            }
                        });


                    }
                }, '->', , {
                    xtype: 'button',
                    text: 'Export',
                    //iconCls: 'exporttoexcel',
                    iconCls: 'icon_excel',
                    style: "padding-left: 10px;",
                    handler: function () {
                        postToUrl(modURL + '&op=debitNotereportsrexport', '', 'post', 'downloadIframe');
                    }
                }

            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: debitNoterepo_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });

        return gst_report_main_grid;
    };
    var creditNoteMasterPanel = function (panelId) {
        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: panelId,
            title: 'Credit Note Reports',
            items: [ceditNotereports_Maingrid()]
        });
        return panel;
    };
    var creditNote_grid_store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCreditReportsGrid',
                method: 'post'
            }),
            fields: ['acet_NO', 'account', 'ledg_Id', 'actr_amount', 'narration', 'particular', 'actr_Date', 'type_name', 'debit', 'credit', 'drcr_amount'],
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
    var ceditNotereports_Maingrid = function () {

        var ledgerCreditCombo_Store = new Ext.data.JsonStore({
            fields: ['id', 'name'],
            url: modURL + '&op=getledgerCreditCombo',
            autoLoad: true,
            method: 'post',
        });
        var creditNoterepo_master_store = creditNote_grid_store();
        var creditNoteReport_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'acet_NO'
                }, {
                    type: 'string',
                    dataIndex: 'account'
                }, {
                    type: 'string',
                    dataIndex: 'actr_amount'
                }, {
                    type: 'string',
                    dataIndex: 'narration'
                }, {
                    type: 'string',
                    dataIndex: 'particular'
                }]
        });
        creditNoteReport_filter.remote = true;
        creditNoteReport_filter.autoReload = true;
        var creditNote_report_main_grid = new Ext.grid.GridPanel({
            store: creditNoterepo_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [creditNoteReport_filter],
            id: 'credit_note_report_grid_id',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'actr_Date'
                },
                {
                    header: 'Particulars',
                    sortable: true,
                    dataIndex: 'particular'
                }, {
                    header: 'Voucher Type',
                    sortable: true,
                    dataIndex: 'type_name',
                }, {
                    header: 'Voucher No',
                    sortable: true,
                    dataIndex: 'acet_NO',
                    width: 20
                },
                {
                    header: 'Narration',
                    sortable: true,
                    dataIndex: 'narration'
                }, {
                    header: 'Debit',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'debit',
                    width: 20
                }, {
                    header: 'Credit',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'credit',
                    width: 20
                }, {
                    width: 10,
                    hidden: true
                }, {
                    width: 10,
                    hidden: true
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
            tbar: [{
                    html: '&nbsp;Ledger:&nbsp;'
                }, {
                    fieldLabel: 'Ledger',
                    xtype: 'combo',
                    store: ledgerCreditCombo_Store,
                    name: 'ledgerCreditCombo',
                    id: 'ledgerCreditCombo',
                    displayField: 'name',
                    valueField: 'id',
                    hiddenName: 'ledgerCreditCombo',
                    tabIndex: 9,
                    triggerAction: 'all',
                    mode: 'local',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    maxChars: 50,
                    listeners: {
                        select: function () {
                            Ext.getCmp('creditNoterep_searchDate').reset();
                            Ext.getCmp('creditNoterep_searchToDate').reset();
                            Ext.getCmp('credit_note_report_grid_id').getStore().removeAll();
                            Ext.getCmp('credit_note_report_grid_id').getView().refresh();
                        }
                    }
                },'-',
                {
                    html: '&nbsp;From:&nbsp;'
                }, {
                    fieldLabel: 'Date',
                    xtype: 'datefield',
                    id: 'creditNoterep_searchDate',
                    name: 'creditNoterep_searchDate',
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
                    id: 'creditNoterep_searchToDate',
                    name: 'creditNoterep_searchToDate',
                    anchor: '86%',
                    editable: true,
                    allowBlank: false,
                    value: new Date(),
                    format: 'd/m/Y'
                },
                '-',
                {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        var dateFrom = Ext.getCmp('creditNoterep_searchDate').getValue();
                        var dateTo = Ext.getCmp('creditNoterep_searchToDate').getValue();

                        Ext.getCmp('credit_note_report_grid_id').getStore().load({
                            params: {
                                date: Ext.util.Format.date(dateFrom, 'Y-m-d'),
                                dateto: Ext.util.Format.date(dateTo, 'Y-m-d'),
                                ledgerDeditCombo: Ext.getCmp('ledgerCreditCombo').getValue()
                            }, callback: function () {
                            }
                        });


                    }
                }, '->', , {
                    xtype: 'button',
                    text: 'Export',
                    //iconCls: 'exporttoexcel',
                    iconCls: 'icon_excel',
                    style: "padding-left: 10px;",
                    handler: function () {
                        postToUrl(modURL + '&op=creditNotereportsrexport', '', 'post', 'downloadIframe');
                    }
                }

            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: creditNoterepo_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });

        return creditNote_report_main_grid;
    };
    var HSNMasterPanel = function (panelId) {
        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: panelId,
            title: 'HSN / GST Reports',
            items: [HSNreports_Maingrid()]
        });
        return panel;
    };
    var HSN_grid_store = function () {

        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listHSNReportsGrid',
                method: 'post'
            }),
            fields: ['total_sgst', 'total_cgst', 'total_gst', 'total_sales', 'invoice_count', 'hsn_gst', 'hsn_code'],
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
    var HSNreports_Maingrid = function () {
        var typeCombo_Store = new Ext.data.ArrayStore({
            fields: ['id', 'name'],
            data: [
                ['1', 'HSN'], ['2', 'GST']
            ],
            autoLoad: true
        });
        var HSNStore = new Ext.data.JsonStore({
            fields: ['hsn_id', 'hsn_code'],
            url: modURL + '&op=getHSNs',
            autoLoad: true,
            method: 'post',
        });
        var GSTStore = new Ext.data.JsonStore({
            fields: ['hsn_id', 'gst_percent'],
            url: modURL + '&op=getGSTs',
            autoLoad: true,
            method: 'post',
        });
        var HSNrepo_master_store = HSN_grid_store();
        var creditNoteReport_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'total_gst'
                }]
        });
        creditNoteReport_filter.remote = true;
        creditNoteReport_filter.autoReload = true;
        var creditNote_report_main_grid = new Ext.grid.GridPanel({
            store: HSNrepo_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [creditNoteReport_filter],
            id: 'hsn_report_grid_id',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'HSN / GST',
                    sortable: true,
                    dataIndex: 'hsn_code'
                },
                {
                    header: 'Invoice Count',
                    sortable: true,
                    dataIndex: 'invoice_count',
                }, {
                    header: 'Total Sales',
                    sortable: true,
                    dataIndex: 'total_sales',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                },
                {
                    header: 'Total GST',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    dataIndex: 'total_gst'
                }, {
                    header: 'CGST',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'total_cgst',
                }, {
                    header: 'SGST',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'total_sgst',
                }, {
                    width: 10,
                    hidden: true
                }, {
                    width: 10,
                    hidden: true
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
            tbar: [{
                    html: '&nbsp;HSN / GST : &nbsp;',
                }, {
                    fieldLabel: 'HSN / GST',
                    xtype: 'combo',
                    store: typeCombo_Store,
                    name: 'gstReportType',
                    id: 'gstReportType',
                    displayField: 'name',
                    valueField: 'id',
                    hiddenName: 'gstReportType',
                    tabIndex: 9,
                    triggerAction: 'all',
                    mode: 'local',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    maxChars: 50,
                    listeners: {
                        select: function () {
                            var gstReportType = Ext.getCmp('gstReportType').getValue();
                            if (gstReportType == 1) {
                                Ext.getCmp('search_hsnCode').show();
                                Ext.getCmp('search_hsngstCode').hide();
                                Ext.getCmp('search_hsngstCode').reset();
                            } else {
                                Ext.getCmp('search_hsngstCode').show();
                                Ext.getCmp('search_hsnCode').hide();
                                Ext.getCmp('search_hsnCode').reset();
                            }

                        }
                    }
                },'-',
                {
                    xtype: 'combo',
                    id: 'search_hsnCode',
                    name: 'search_hsnCode',
                    mode: 'local',
                    hidden: true,
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'HSN',
                    editable: true,
                    anchor: '97%',
                    store: HSNStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'hsn_code',
                    valueField: 'hsn_id',
                    hiddenName: 'search_hsnCode',
                }, {
                    xtype: 'combo',
                    id: 'search_hsngstCode',
                    name: 'search_hsngstCode',
                    mode: 'local',
                    hidden: true,
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'GST',
                    editable: true,
                    anchor: '97%',
                    store: GSTStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'gst_percent',
                    valueField: 'hsn_id',
                    hiddenName: 'search_hsngstCode',
                },
                {
                    html: '&nbsp;From:&nbsp;'
                }, {
                    fieldLabel: 'Date',
                    xtype: 'datefield',
                    id: 'hsnrep_searchDate',
                    name: 'hsnrep_searchDate',
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
                    id: 'hsnrep_searchToDate',
                    name: 'hsnrep_searchToDate',
                    anchor: '86%',
                    editable: true,
                    allowBlank: false,
                    value: new Date(),
                    format: 'd/m/Y'
                },
                '-',
                {
                    xtype: 'button',
                    iconCls: 'finascop_search_btn',
                    text: 'Search',
                    handler: function () {
                        var dateFrom = Ext.getCmp('hsnrep_searchDate').getValue();
                        var dateTo = Ext.getCmp('hsnrep_searchToDate').getValue();

                        Ext.getCmp('hsn_report_grid_id').getStore().load({
                            params: {
                                date: Ext.util.Format.date(dateFrom, 'Y-m-d'),
                                dateto: Ext.util.Format.date(dateTo, 'Y-m-d'),
                                search_hsnCode: Ext.getCmp('search_hsnCode').getValue(),
                                search_hsnCodeVal: Ext.getCmp('search_hsnCode').getRawValue(),
                                search_hsngstCode: Ext.getCmp('search_hsngstCode').getRawValue()
                            }, callback: function () {
                            }
                        });


                    }
                }, '->', , {
                    xtype: 'button',
                    text: 'Export',
                    //iconCls: 'exporttoexcel',
                    iconCls: 'icon_excel',
                    style: "padding-left: 10px;",
                    handler: function () {
                        postToUrl(modURL + '&op=HSNreportsrexport', '', 'post', 'downloadIframe');
                    }
                }

            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: HSNrepo_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });

        return creditNote_report_main_grid;
    };
    return{
        initGSTReports: function () {
            var panelId = 'gst_reports_panel';
            var gstreport_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(gstreport_panel)) {
                gstreport_panel = gstReportMasterPanel(panelId);
                Application.UI.addTab(gstreport_panel);
                gstreport_panel.doLayout();
            } else {
                Application.UI.addTab(gstreport_panel);
                gstreport_panel.doLayout();
            }

        }, initDebitNoteReports: function () {
            var panelId = 'debitNote_reports_panel';
            var debitNotereports_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(debitNotereports_panel)) {
                debitNotereports_panel = debitNoteMasterPanel(panelId);
                Application.UI.addTab(debitNotereports_panel);
                debitNotereports_panel.doLayout();
            } else {
                Application.UI.addTab(debitNotereports_panel);
                debitNotereports_panel.doLayout();
            }

        }, initCreditNoteReports: function () {
            var panelId = 'creditNote_reports_panel';
            var creditNotereports_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(creditNotereports_panel)) {
                creditNotereports_panel = creditNoteMasterPanel(panelId);
                Application.UI.addTab(creditNotereports_panel);
                creditNotereports_panel.doLayout();
            } else {
                Application.UI.addTab(creditNotereports_panel);
                creditNotereports_panel.doLayout();
            }

        }, initHSNReports: function () {
            var panelId = 'HSNNote_reports_panel';
            var hsnreports_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(hsnreports_panel)) {
                hsnreports_panel = HSNMasterPanel(panelId);
                Application.UI.addTab(hsnreports_panel);
                hsnreports_panel.doLayout();
            } else {
                Application.UI.addTab(hsnreports_panel);
                hsnreports_panel.doLayout();
            }

        }
    }
}();