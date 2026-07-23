Application.RetalineReports = function () {
    var gensaleslastParameters, brnchsaleslastParameters, pendingorderlastParameters;
    var recs_per_page = 24;
    var modURL = '?module=retaline_reports';

    var salessmryReportPanel = function (panelId) {
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: panelId,
            title: 'Total Sales Summary',
            iconCls: 'finascop_bank',
            items: [
                getsalessummaryGrid()
            ]
        });
        return panel;

    };
    var getsalessummaryGrid = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

        var getdlysummryStore = function () {
            var BankTransactionReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getsalessmryGridData',
                fields: ['order_created_on', 'br_Name', 'order_order_id', 'total', 'order_paymode', 'order_total_amount', 'order_total_gst', 'comp_name', 'delivery_to', 'cust_mobile', 'order_delivery_charge',
                    'order_invoiceamt', 'order_delivered_date', 'totalGST', 'totalAmount', 'order_invoiceno', 'order_roundoff'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        gensaleslastParameters = options.params;
                    }
                }


            });
            return BankTransactionReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [
                        {
                            type: 'date',
                            dataIndex: 'order_created_on'
                        }, {
                            type: 'list',
                            dataIndex: 'br_Name',
                            options: USER_ACCESSIBLE_BRANCHES,
                            phpMode: true,
                        }, {
                            type: 'string',
                            dataIndex: 'order_order_id'
                        },
                    ]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;


        var dlysmmry_store = getdlysummryStore();
        var grid = new Ext.grid.GridPanel({
            store: dlysmmry_store,
            region: 'center',
            frame: true,
            border: false,
            plugins: [gridFilters],
            id: 'salesmmry_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date'
                }, {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch'
                }, {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID'
                }, {
                    header: 'Customer Name',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Customer Name'
                }, {
                    header: 'Customer Phone',
                    sortable: true,
                    dataIndex: 'cust_mobile',
                    tooltip: 'Customer Phone'
                }, {
                    header: 'Item value',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'order_total_amount',
                    tooltip: 'Item value'
                }, {
                    header: 'Tax',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'totalGST',
                    tooltip: 'Tax'
                }, {
                    header: 'Order Amount',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'totalAmount',
                    tooltip: 'Order Amount'
                }, {
                    header: 'Delivery Charge',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'order_delivery_charge',
                    tooltip: 'Delivery Charge'
                }, {
                    header: 'Roundoff',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'order_roundoff',
                    tooltip: 'Roundoff'
                }, {
                    header: 'Total Amount',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'total',
                    tooltip: 'Total Amount'
                }, {
                    header: 'Invoice Number',
                    sortable: true,
                    dataIndex: 'order_invoiceno',
                    tooltip: 'Invoice Number'
                }, {
                    header: 'Invoice Amount',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'order_invoiceamt',
                    tooltip: 'Invoice Amount'
                }, {
                    header: 'Payment Mode',
                    sortable: true,
                    dataIndex: 'order_paymode',
                    tooltip: 'Payment Mode'
                }, {
                    header: 'Delivered On',
                    sortable: true,
                    dataIndex: 'order_delivered_date',
                    tooltip: 'Delivered On'
                }, {
                    header: 'Company',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'comp_name',
                    tooltip: 'Company'
                },
            ],
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [{
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'lovcombo',
                    id: 'comboxbranchnameSR',
                    name: 'comboxbranchnameSR',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Store Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnameSR',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchnameSR').getValue();
                        }
                    }
                },
                {html: '&nbsp; Date From: &nbsp;'},
                {
                    editable: false,
                    allowBlank: false,
                    anchor: '70%',
                    xtype: 'datefield',
                    width: 120,
                    id: 'partner_from_Date',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    maxValue: new Date(),
                    value: new Date().add(Date.MONTH, -1),
                    listeners: {
                        select: function (datefld, date) {
                            dlysmmry_store.baseParams = {
                                park_from_Date: ''
                            };

                            dlysmmry_store.removeAll();
                            // Ext.getCmp('salesorder_report_grid').doLayout();



                            var fromdate = Ext.getCmp("partner_from_Date").getValue();
                            dlysmmry_store.removeAll();
                            var todate = new Date(fromdate).add(Date.MONTH, 1);
                            Ext.getCmp('parner_to_Date').reset();
                            Ext.getCmp('parner_to_Date').setValue(todate);
                            Ext.getCmp('parner_to_Date').setMaxValue(todate);
                            Ext.getCmp('parner_to_Date').setMinValue(fromdate);
                        }
                    }
                }
                , '-',
                {html: '&nbsp; Date To: &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'parner_to_Date',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    value: new Date(),
                    listeners: {
                        select: function (datefld, date) {
                            dlysmmry_store.baseParams = {
                                park_to_Date: ''
                            };

                            dlysmmry_store.removeAll();



                            Ext.getCmp('salesorder_report_grid').doLayout();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchnameSR').getValue();
                        dlysmmry_store.baseParams = {
                            salesrep_from_Date: Ext.util.Format.date(Ext.getCmp('partner_from_Date').getValue(), 'Y-m-d'),
                            salesrep_to_Date: Ext.util.Format.date(Ext.getCmp('parner_to_Date').getValue(), 'Y-m-d'),
                            branchName: branchName
                        }
                        dlysmmry_store.load();
                    }
                },
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'exporttoexcel',
                    handler: function () {

                        if (dlysmmry_store.getTotalCount() <= 0) {
                            Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                if (btn == 'no') {
                                    return;
                                }
                            });
                        }
                        var wisize = 'salesreport';
                        var activeTabtitle = 'Sales_Report';
                        var indexes = [];
                        var heads = [];

                        /**
                         * @modified by Aparna Ravvendran <aparna@saturn.in>
                         * @modified On 27-Mar-2013
                         *
                         * pass total no of headers of selected grid for export excel
                         */
                        var filterData = Ext.encode(gensaleslastParameters);
                        for (var i = 0; i < Ext.getCmp('salesmmry_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('salesmmry_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('salesmmry_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('salesmmry_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }
                        postToUrl(modURL + '&op=getsalesexport', {dataindexes: dataindexes, headers: headers, filterData: filterData, wisize: wisize, activeTabtitle: activeTabtitle}, 'post', 'downloadIframe');
                        //postToUrl(modURL + '&op=getsalesexport', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: dlysmmry_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });


        return grid;


    };
    var salesBranchsmryReportPanel = function (panelId) {
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: panelId,
            title: 'Branch Sales Summary',
            iconCls: 'finascop_bank',
            items: [
                getBranchsalessummaryGrid()
            ]
        });
        return panel;

    };
    var getBranchsalessummaryGrid = function () {
        var getbrncsummryStore = function () {
            var BankTransactionReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getsalesbrnchsmryGridData',
                fields: ['br_Name', 'orderCOunt', 'PoDCount', 'PaidCount', 'totalAmount', 'totalGST', 'totalAmt', 'order_confirmed_on', 'order_delivered_date', 'created_at', 'order_method', 'updated_at', 'deliveryCharge', 'orderValue','orderRoudoff'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        brnchsaleslastParameters = options.params;
                    }
                }

            });
            return BankTransactionReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [
                        {
                            type: 'date',
                            dataIndex: 'order_created_on'
                        }, {
                            type: 'list',
                            dataIndex: 'br_Name',
                            options: USER_ACCESSIBLE_BRANCHES,
                            phpMode: true,
                        }, {
                            type: 'string',
                            dataIndex: 'order_order_id'
                        },
                    ]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var saleesBrnchsmmry_store = getbrncsummryStore();
        var grid = new Ext.grid.GridPanel({
            store: saleesBrnchsmmry_store,
            region: 'center',
            frame: true,
            border: false,
            plugins: [gridFilters],
            id: 'salebrnchsmmry_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch'
                }, {
                    header: 'Total Order Count',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'orderCOunt',
                    tooltip: 'Total Order Count'
                }, {
                    header: 'PoD Count',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'PoDCount',
                    tooltip: 'PoD Count'
                }, {
                    header: 'Paid Count',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'PaidCount',
                    tooltip: 'Paid Count'
                },
                {
                    header: 'Order Value',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'orderValue',
                    tooltip: 'Order Value'
                }, {
                    header: 'Taxes',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'totalGST',
                    tooltip: 'Taxes'
                }, {
                    header: 'Delivery Charges',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'deliveryCharge',
                    tooltip: 'Delivery Charges'
                },{
                    header: 'Round Off',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'orderRoudoff',
                    tooltip: 'Round Off'
                }, {
                    header: 'Total Amount',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'totalAmt',
                    tooltip: 'Total Amount'
                },{
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 40,
                    items: [{},{}]

                }
            ],
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [
                {html: '&nbsp; Date From: &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'partner_from_Datesbs',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    value: new Date().add(Date.MONTH, -1),
                    listeners: {
                        select: function (datefld, date) {
                            saleesBrnchsmmry_store.baseParams = {
                                park_from_Date: ''
                            };

                            saleesBrnchsmmry_store.removeAll();
                            Ext.getCmp('salebrnchsmmry_report_grid').doLayout();



                            var fromdate = Ext.getCmp("partner_from_Datesbs").getValue();
                            saleesBrnchsmmry_store.removeAll();
                            var todate = new Date(fromdate).add(Date.MONTH, 1);
                            Ext.getCmp('parner_to_Datesbs').reset();
                            Ext.getCmp('parner_to_Datesbs').setValue(todate);
                            Ext.getCmp('parner_to_Datesbs').setMaxValue(todate);
                            Ext.getCmp('parner_to_Datesbs').setMinValue(fromdate);
                        }
                    }
                }
                , '-',
                {html: '&nbsp; Date To: &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'parner_to_Datesbs',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    value: new Date(),
                    listeners: {
                        select: function (datefld, date) {
                            saleesBrnchsmmry_store.baseParams = {
                                park_to_Date: ''
                            };

                            saleesBrnchsmmry_store.removeAll();



                            Ext.getCmp('salebrnchsmmry_report_grid').doLayout();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        saleesBrnchsmmry_store.baseParams = {
                            salesrep_from_Date: Ext.util.Format.date(Ext.getCmp('partner_from_Datesbs').getValue(), 'Y-m-d'),
                            salesrep_to_Date: Ext.util.Format.date(Ext.getCmp('parner_to_Datesbs').getValue(), 'Y-m-d')
                        }
                        saleesBrnchsmmry_store.load();
                    }
                },
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'exporttoexcel',
                    handler: function () {

                        if (saleesBrnchsmmry_store.getTotalCount() <= 0) {
                            Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                if (btn == 'no') {
                                    return;
                                }
                            });
                        }
                        var wisize = 'salesbranchreport';
                        var activeTabtitle = 'Sales_Branch_Report';
                        var indexes = [];
                        var heads = [];

                        /**
                         * @modified by Aparna Ravvendran <aparna@saturn.in>
                         * @modified On 27-Mar-2013
                         *
                         * pass total no of headers of selected grid for export excel
                         */
                        var filterData = Ext.encode(gensaleslastParameters);
                        for (var i = 0; i < Ext.getCmp('salebrnchsmmry_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('salebrnchsmmry_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('salebrnchsmmry_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('salebrnchsmmry_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }
                        postToUrl(modURL + '&op=getsalesbrnchexport', {dataindexes: dataindexes, headers: headers, filterData: filterData, wisize: wisize, activeTabtitle: activeTabtitle}, 'post', 'downloadIframe');
                        //postToUrl(modURL + '&op=getsalesexport', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: saleesBrnchsmmry_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });


        return grid;
    };
    var pendingOrderReportTopPanel = function (panelId) {
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: panelId,
            title: 'Pending Order Report',
            iconCls: 'finascop_bank',
            items: [getPendingOrderGrid()]
        });
        return panel;
    };
    var getPendingOrderGrid = function () {
        var getpendingorderStore = function () {
            var pendingOrdersReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPendingOrderGridData',
                fields: ['br_id','br_Name','orderCOunt','packedOrders','packingpending','orderrecived','deliveredOrders','deliverypending','totalpending','pendngOrderValue'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        pendingorderlastParameters = options.params;
                    }
                }

            });
            return pendingOrdersReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [
                    ]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
//br_id,br_Name,orderCOunt,packedOrders,packingpending,orderrecived,deliveredOrders,deliverypending,totalpending,pendngOrderValue
        var pendingOrdersmmry_store = getpendingorderStore();
        var grid = new Ext.grid.GridPanel({
            store: pendingOrdersmmry_store,
            region: 'center',
            frame: true,
            border: false,
            plugins: [gridFilters],
            id: 'pending_order_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch'
                }, {
                    header: 'Order Received',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'orderrecived',
                    tooltip: 'Order Received'
                },
                {
                    header: 'Orders Packed',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'packedOrders',
                    tooltip: 'Orders Packed'
                }, {
                    header: 'Packing Pending',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'packingpending',
                    tooltip: 'Packing Pending'
                }, {
                    header: 'Orders Delivered',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'deliveredOrders',
                    tooltip: 'Orders Delivered'
                }, {
                    header: 'Delivery Pending',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'deliverypending',
                    tooltip: 'Delivery Pending'
                },  {
                    header: 'Total Pending',
                    sortable: true,
                    hidden: true,
                    align: 'right',
                    dataIndex: 'totalpending',
                    tooltip: 'Total Pending'
                }, {
                    header: 'Pending Order Value',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'pendngOrderValue',
                    tooltip: 'Pending Order Value'
                },{
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 40,
                    items: [{},{}]

                }
            ],
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [
                {html: '&nbsp; Date From: &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'partner_from_Datepor',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    value: new Date().add(Date.MONTH, -1),
                    listeners: {
                        select: function (datefld, date) {
                            pendingOrdersmmry_store.baseParams = {
                                park_from_Date: ''
                            };

                            pendingOrdersmmry_store.removeAll();
                            Ext.getCmp('pending_order_report_grid').doLayout();



                            var fromdate = Ext.getCmp("partner_from_Datesbs").getValue();
                            pendingOrdersmmry_store.removeAll();
                            var todate = new Date(fromdate).add(Date.MONTH, 1);
                            Ext.getCmp('parner_to_Datepor').reset();
                            Ext.getCmp('parner_to_Datepor').setValue(todate);
                            Ext.getCmp('parner_to_Datepor').setMaxValue(todate);
                            Ext.getCmp('parner_to_Datepor').setMinValue(fromdate);
                        }
                    }
                }
                , '-',
                {html: '&nbsp; Date To: &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'parner_to_Datepor',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    value: new Date(),
                    listeners: {
                        select: function (datefld, date) {
                            pendingOrdersmmry_store.baseParams = {
                                park_to_Date: ''
                            };

                            pendingOrdersmmry_store.removeAll();



                            Ext.getCmp('pending_order_report_grid').doLayout();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        pendingOrdersmmry_store.baseParams = {
                            salesrep_from_Date: Ext.util.Format.date(Ext.getCmp('partner_from_Datepor').getValue(), 'Y-m-d'),
                            salesrep_to_Date: Ext.util.Format.date(Ext.getCmp('parner_to_Datepor').getValue(), 'Y-m-d')
                        }
                        pendingOrdersmmry_store.load();
                    }
                },
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'exporttoexcel',
                    handler: function () {

                        if (pendingOrdersmmry_store.getTotalCount() <= 0) {
                            Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                if (btn == 'no') {
                                    return;
                                }
                            });
                        }
                        var wisize = 'pendingorderreport';
                        var activeTabtitle = 'Pending_Order_Report';
                        var indexes = [];
                        var heads = [];

                        /**
                         * @modified by Aparna Ravvendran <aparna@saturn.in>
                         * @modified On 27-Mar-2013
                         *
                         * pass total no of headers of selected grid for export excel
                         */
                        var filterData = Ext.encode(gensaleslastParameters);
                        for (var i = 0; i < Ext.getCmp('pending_order_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('pending_order_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('pending_order_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('pending_order_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }
                        postToUrl(modURL + '&op=getPendingOrderexport', {dataindexes: dataindexes, headers: headers, filterData: filterData, wisize: wisize, activeTabtitle: activeTabtitle}, 'post', 'downloadIframe');
                        //postToUrl(modURL + '&op=getsalesexport', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: pendingOrdersmmry_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });


        return grid;
    };
    var mnthlysalessmryReportPanel = function (panelId) {
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: panelId,
            title: 'Monthly Sales Summary',
            iconCls: 'finascop_bank',
            items: [
                getMonthlysalessummaryGrid()
            ]
        });
        return panel;

    };
    var getMonthlysalessummaryGrid = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

        var getmnthlysummryStore = function () {
            var BankTransactionReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getsalessmryGridDatamnthly',
                fields: ['order_created_on', 'br_Name', 'order_order_id', 'total', 'order_paymode', 'order_total_amount', 'order_total_gst', 'comp_name', 'delivery_to', 'cust_mobile', 'order_delivery_charge',
                    'order_invoiceamt', 'order_delivered_date', 'totalGST', 'totalAmount', 'order_invoiceno', 'order_roundoff','order_amount_payable'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        gensaleslastParameters = options.params;
                    }
                }


            });
            return BankTransactionReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [
                        {
                            type: 'date',
                            dataIndex: 'order_created_on'
                        }, {
                            type: 'list',
                            dataIndex: 'br_Name',
                            options: USER_ACCESSIBLE_BRANCHES,
                            phpMode: true,
                        }, {
                            type: 'string',
                            dataIndex: 'order_order_id'
                        },
                    ]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;


        var dlysmmry_store = getmnthlysummryStore();
        var grid = new Ext.grid.GridPanel({
            store: dlysmmry_store,
            region: 'center',
            frame: true,
            border: false,
            plugins: [gridFilters],
            id: 'mnthlysalesmmry_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date'
                }, {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID'
                },{
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch'
                },  {
                    header: 'Customer Name',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Customer Name'
                },{
                    header: 'Gross Amount',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'order_amount_payable',
                    tooltip: 'Gross Amount'
                }, {
                    header: 'GST Collected',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'order_total_gst',
                    tooltip: 'Tax'
                }, {
                    header: 'Net Amount',
                    sortable: true,
                    dataIndex: 'order_total_amount',
                    tooltip: 'Net Amount'
                }, {
                    header: 'Company',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'comp_name',
                    tooltip: 'Company'
                },
            ],
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [{
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'lovcombo',
                    id: 'comboxbranchnameSRM',
                    name: 'comboxbranchnameSRM',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Store Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnameSRM',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchnameSRM').getValue();
                        }
                    }
                },
                {html: '&nbsp; Date From: &nbsp;'},
                {
                    editable: false,
                    allowBlank: false,
                    anchor: '70%',
                    xtype: 'datefield',
                    width: 120,
                    id: 'partner_from_DateSRM',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    maxValue: new Date(),
                    value: new Date().add(Date.MONTH, -1),
                    listeners: {
                        select: function (datefld, date) {
                            dlysmmry_store.baseParams = {
                                park_from_Date: ''
                            };

                            dlysmmry_store.removeAll();



                            var fromdate = Ext.getCmp("partner_from_DateSRM").getValue();
                            dlysmmry_store.removeAll();
                            var todate = new Date(fromdate).add(Date.MONTH, 1);
                            Ext.getCmp('parner_to_DateSRM').reset();
                            Ext.getCmp('parner_to_DateSRM').setValue(todate);
                            //Ext.getCmp('parner_to_DateSRM').setMaxValue(todate);
                            Ext.getCmp('parner_to_DateSRM').setMinValue(fromdate);
                        }
                    }
                }
                , '-',
                {html: '&nbsp; Date To: &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'parner_to_DateSRM',
                    name: 'bnktrans_filter_Date',
                    format: 'd-M-Y',
                    value: new Date(),
                    listeners: {
                        select: function (datefld, date) {
                            dlysmmry_store.baseParams = {
                                park_to_Date: ''
                            };

                            dlysmmry_store.removeAll();



                            Ext.getCmp('mnthlysalesmmry_report_grid').doLayout();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchnameSRM').getValue();
                        dlysmmry_store.baseParams = {
                            salesrep_from_Date: Ext.util.Format.date(Ext.getCmp('partner_from_DateSRM').getValue(), 'Y-m-d'),
                            salesrep_to_Date: Ext.util.Format.date(Ext.getCmp('parner_to_DateSRM').getValue(), 'Y-m-d'),
                            branchName: branchName
                        }
                        dlysmmry_store.load();
                    }
                },
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'exporttoexcel',
                    handler: function () {

                        if (dlysmmry_store.getTotalCount() <= 0) {
                            Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                if (btn == 'no') {
                                    return;
                                }
                            });
                        }
                        var wisize = 'salesreportmonthly';
                        var activeTabtitle = 'Sales_Report';
                        var indexes = [];
                        var heads = [];

                        /**
                         * @modified by Aparna Ravvendran <aparna@saturn.in>
                         * @modified On 27-Mar-2013
                         *
                         * pass total no of headers of selected grid for export excel
                         */
                        var filterData = Ext.encode(gensaleslastParameters);
                        for (var i = 0; i < Ext.getCmp('mnthlysalesmmry_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('mnthlysalesmmry_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('mnthlysalesmmry_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('mnthlysalesmmry_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }
                        postToUrl(modURL + '&op=getsalesexportmnthly', {dataindexes: dataindexes, headers: headers, filterData: filterData, wisize: wisize, activeTabtitle: activeTabtitle}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: dlysmmry_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
        });


        return grid;


    };
    return {
        salesReport: function () {
            var panelId = 'salesrprt_report';
            var salessmryPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(salessmryPanel)) {
                salessmryPanel = salessmryReportPanel(panelId);
                Application.UI.addTab(salessmryPanel);
                salessmryPanel.doLayout();
            } else {
                Application.UI.addTab(salessmryPanel);
            }
        },
        salesSummaryReport: function () {
            var panelId = 'salesBrnchSummryrprt_report';
            var saleBrnchssmryPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(saleBrnchssmryPanel)) {
                saleBrnchssmryPanel = salesBranchsmryReportPanel(panelId);
                Application.UI.addTab(saleBrnchssmryPanel);
                saleBrnchssmryPanel.doLayout();
            } else {
                Application.UI.addTab(saleBrnchssmryPanel);
            }
        },
        pendingOrderReport: function () {
            var panelId = 'pendingOrderrprt_report';
            var pendingOrderReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(pendingOrderReportPanel)) {
                pendingOrderReportPanel = pendingOrderReportTopPanel(panelId);
                Application.UI.addTab(pendingOrderReportPanel);
                pendingOrderReportPanel.doLayout();
            } else {
                Application.UI.addTab(pendingOrderReportPanel);
            }
        },mnthlysalesReport: function () {
            var panelId = 'mnthlysalesrprt_report';
            var mnthlysalessmryPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(mnthlysalessmryPanel)) {
                mnthlysalessmryPanel = mnthlysalessmryReportPanel(panelId);
                Application.UI.addTab(mnthlysalessmryPanel);
                mnthlysalessmryPanel.doLayout();
            } else {
                Application.UI.addTab(mnthlysalessmryPanel);
            }
        },

    };
}();
