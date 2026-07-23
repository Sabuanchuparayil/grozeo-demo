Application.InventoryReports = function ()
{
    var purchaseLastParameters;
    var B2CSalesLastParameters;
    var B2BSalesLastParameters;
    var recs_per_page = 25;
    var modURL = '?module=inventory_report';
    var getStockReportGrid = function () {

        var getStockReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getStockReportGridData',
                fields: ['blocked_count', 'cart_count', 'br_Name', 'stit_SKU', 'item_count', 'bom_offrPlacement', 'mrp', 'selling_price', 'optimumqty', 'minimumqty', 'maximumqty', 'stit_id', 'branchId',
                    'rack_count', 'dispatch_count', 'receive_count', 'deliver_count', 'fsbg_name', 'fsbg_id', 'purchasing_unit', 'purchasing_unitName'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        purchaseLastParameters = options.params;
                    }
                }

            });
            return SculptueReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'br_Name'
                        }, {
                            type: 'string',
                            dataIndex: 'fsbg_name'
                        }, {
                            type: 'string',
                            dataIndex: 'receive_count'
                        }, {
                            type: 'string',
                            dataIndex: 'blocked_count'
                        }, {
                            type: 'string',
                            dataIndex: 'cart_count'
                        }, {
                            type: 'string',
                            dataIndex: 'deliver_count'
                        }, {
                            type: 'string',
                            dataIndex: 'dispatch_count'
                        }, {
                            type: 'string',
                            dataIndex: 'stit_SKU'
                        }, {
                            type: 'string',
                            dataIndex: 'item_count'
                        }, {
                            type: 'string',
                            dataIndex: 'mrp'
                        }, {
                            type: 'string',
                            dataIndex: 'selling_price'
                        }, {
                            type: 'string',
                            dataIndex: 'optimumqty'
                        }, {
                            type: 'string',
                            dataIndex: 'minimumqty'
                        }, {
                            type: 'string',
                            dataIndex: 'maximumqty'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var StockReport_store = getStockReportStore();
        var grid = new Ext.grid.GridPanel({
            store: StockReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'stock_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Item SKU',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item SKU',
                    width: 200
                },
                {
                    header: 'Total Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_count',
                    tooltip: 'Total Count'
                }, {
                    header: 'Unit',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'purchasing_unitName',
                    tooltip: 'Unit'
                },
                {
                    header: MRP,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: MRP,
                },
                {
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Selling Price',
                }],
            //view: new Ext.grid.GridView(),
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: ['-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (StockReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseLastParameters);
                        for (var i = 0; i < Ext.getCmp('stock_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('stock_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('stock_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('stock_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=StockReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: StockReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getStockReportPanel = function (panelId) {
        var stockReportGrid = getStockReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Stock Report',
            items: [stockReportGrid]
        });
        return panel;
    };
    var getB2CSalesReportPanel = function (panelId) {
        var B2CSalesReportGrid = getB2CSalesReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Retail Sales Report',
            items: [B2CSalesReportGrid]
        });
        return panel;
    };
    var getB2CSalesReportGrid = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
            listeners: {
                load: function (thisstore, records, options) {
                    Ext.getCmp('search_br_Name').setValue(_SESSION.finascop_current_branch_id);
                }
            }
        });
        var getB2CSalesReportStore = function () {
            var B2CSalesReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getB2CSalesReportGridData',
                fields: ['order_payment_gateway_refid', 'order_payment_gateway_refid_crc32', 'order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', {
                        name: 'order_created_on',
                        type: 'date',
                        dateFormat: 'd-m-Y'
                    },
                    'dispatch_courier', 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'order_total_amount', 'payMode', 'payment_mode'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        B2CSalesLastParameters = options.params;
                    }
                }

            });
            return B2CSalesReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
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
                            dataIndex: 'br_Name'
                        }, {
                            type: 'list',
                            options: ORDER_STATUS_OPTIONS,
                            phpMode: true,
                            dataIndex: 'order_status'
                        }, {
                            type: 'string',
                            dataIndex: 'payMode'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var B2CSalesReport_store = getB2CSalesReportStore();
        var grid = new Ext.grid.GridPanel({
            store: B2CSalesReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'b2csales_report_grid',
            columns: [new Ext.grid.RowNumberer(), {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                }, {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 200,
                    hideable: false,
                }, {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                }, {
                    header: 'Time',
                    sortable: false,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                }, {
                    header: 'Delivery To',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Delivery To',
                    width: 200,
                }, {
                    header: 'Order Amount',
                    sortable: true,
                    dataIndex: 'order_total_amount',
                    tooltip: 'Order Amount',
                    width: 200,
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                }, {
                    header: 'Payment Mode',
                    sortable: true,
                    dataIndex: 'payMode',
                    tooltip: 'Payment Mode',
                    width: 200,
                }, {
                    header: 'PayGatewayId',
                    sortable: true,
                    dataIndex: 'order_payment_gateway_refid',
                    tooltip: 'PayGatewayId',
                    width: 200,
                }],
            //view: new Ext.grid.GridView(),
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
                    xtype: 'combo',
                    id: 'search_br_Name',
                    name: 'search_br_Name',
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
                    hiddenName: 'search_br_Name',
                },
                {html: '&nbsp; From Date : &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'search_stock_from_date',
                    name: 'search_stock_from_date',
                    format: 'd/m/Y',
                    value: new Date()
                }, {html: '&nbsp; To Date : &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'search_stock_to_date',
                    name: 'search_stock_to_date',
                    format: 'd/m/Y',
                    value: new Date()
                },
                '-',
                {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        var search_stock_from_date = Ext.getCmp('search_stock_from_date').getValue();
                        var search_stock_to_date = Ext.getCmp('search_stock_to_date').getValue();
                        Ext.getCmp('b2csales_report_grid').filters.clearFilters();
                        Ext.getCmp('b2csales_report_grid').getStore().load({
                            params: {
                                br_Name: Ext.getCmp('search_br_Name').getRawValue(),
                                search_stock_from_date: search_stock_from_date,
                                search_stock_to_date: search_stock_to_date
                            }
                        });

                        //var storefilter = Ext.getCmp('b2csales_report_grid');
                        // activateFilter(storefilter, 'br_Name', Ext.getCmp('search_br_Name').getRawValue());

                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (B2CSalesReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(B2CSalesLastParameters);
                        for (var i = 0; i < Ext.getCmp('b2csales_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('b2csales_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('b2csales_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('b2csales_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=B2CSalesReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: B2CSalesReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'cust_name_auto_exp'

        });
        return grid;
    };
    var getB2BSalesReportPanel = function (panelId) {
        var B2BSalesReportGrid = getB2BSalesReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Wholesale Report',
            items: [B2BSalesReportGrid]
        });
        return panel;
    };
    var getB2BSalesReportGrid = function () {
        var getB2BSalesReportStore = function () {
            var B2BSalesReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getB2BSalesReportGridData',
                fields: ['bbso_id', 'bbso_SONumber', 'bbso_SODate', 'b2b_Customer_ID', 'b2b_Customer_Name', 'bbso_InvoiceStatusName',
                    'bbso_SOValue', 'bbso_HandlingCharges', 'bbso_Active', 'status_id'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        B2BSalesLastParameters = options.params;
                    }
                }

            });
            return B2BSalesReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'bbso_SONumber'
                        }, {
                            type: 'date',
                            dataIndex: 'bbso_SODate'
                        }, {
                            type: 'string',
                            dataIndex: 'b2b_Customer_Name'
                        }, {
                            type: 'list',
                            value: '',
                            dataIndex: 'bbso_Active',
                            options: ['Active', 'Godown Boy Poll Rejected', 'Godown Boy Polled', 'Assigned Godown Boy', 'Queue For Manual Assignment'],
                            phpMode: true
                        }, {
                            type: 'string',
                            dataIndex: 'bbso_SOValue'
                        }, {
                            type: 'string',
                            dataIndex: 'bbso_HandlingCharges'
                        }, {
                            type: 'string',
                            dataIndex: 'bbso_InvoiceStatusName'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var B2BSalesReport_store = getB2BSalesReportStore();
        var grid = new Ext.grid.GridPanel({
            store: B2BSalesReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'b2bsales_report_grid',
            columns: [new Ext.grid.RowNumberer(), {
                    header: 'B2B SO Number',
                    sortable: true,
                    dataIndex: 'bbso_SONumber',
                    tooltip: 'B2B Sales Order Number',
                    hideable: false
                },
                {
                    header: 'B2B SO Date',
                    sortable: true,
                    dataIndex: 'bbso_SODate',
                    tooltip: 'B2B Sales Order Date'
                },
                {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'b2b_Customer_Name',
                    tooltip: 'Vendor',
                    hideable: false
                },
                {
                    header: 'B2B SO Total Value',
                    sortable: true,
                    dataIndex: 'bbso_SOValue',
                    tooltip: 'B2B Sales Order Status',
                    hideable: false
                },
                {
                    header: 'B2B SO Handling Charges',
                    sortable: true,
                    dataIndex: 'bbso_HandlingCharges',
                    tooltip: 'B2B Sales Order Status',
                    hideable: false
                },
                {
                    header: 'B2B SO Status',
                    hidden: true,
                    dataIndex: 'bbso_Active',
                    tooltip: 'B2B Sales Order Status',
                    hideable: false
                }, {
                    header: 'Invoice Status',
                    sortable: true,
                    dataIndex: 'bbso_InvoiceStatusName',
                    tooltip: 'Invoice Status',
                    hideable: false
                }],
            //view: new Ext.grid.GridView(),
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [
//                {html: '&nbsp; Date : &nbsp;'},
//                {
//                    xtype: 'datefield',
//                    width: 120,
//                    id: 'srep_filter_Date',
//                    name: 'srep_filterDate',
//                    format: 'd-M-Y',
//                    value: new Date().add(Date.DAY, -1),
//                    listeners: {
//                        select: function (datefld, date) {
//                            SculptueReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            SculptueReport_store.removeAll();
//                            Ext.getCmp('supplier_report_grid').doLayout();
//                        }
//                    }
//                }
//                , '-',
//                {
//                    xtype: 'button',
//                    text: 'Search',
//                    iconCls: 'finascop_search_btn',
//                    handler: function () {
//                        SculptueReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        SculptueReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (B2BSalesReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(B2BSalesLastParameters);
                        for (var i = 0; i < Ext.getCmp('b2bsales_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('b2bsales_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('b2bsales_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('b2bsales_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=B2BSalesReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: B2BSalesReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'cust_name_auto_exp'

        });
        return grid;
    };
    var getStockDetailsReportPanel = function (panelId) {
        var stockDetailsReportGrid = getStockDetilsReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Stock Details Report',
            items: [stockDetailsReportGrid]
        });
        return panel;
    };
    var getStockDetilsReportGrid = function () {
        var getStockDetailsReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getStockDetailsReportGridData',
                fields: ['stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'cpd_branch_id', 'stiid_leastSKUmrp'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        purchaseLastParameters = options.params;
                    }
                }

            });
            return SculptueReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'br_Name'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_vendorName'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_poNumber'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_barcode'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_itemmastername'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_batchno'
                        }, {
                            type: 'date',
                            dataIndex: 'fpo_poDate'
                        }, {
                            type: 'date',
                            dataIndex: 'stiid_expirydate'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var StockDetailsReport_store = getStockDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: StockDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'stock_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Vendor Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor Name',
                    width: 200
                },
                {
                    header: 'PO Number',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Barcode',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_barcode',
                    tooltip: 'Barcode'
                },
                {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'stiid_itemmastername',
                    tooltip: 'Item Name',
                }, {
                    header: 'Expiry Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_expirydate',
                    tooltip: 'Expiry Date'
                },
                {
                    header: 'Batch No.',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stiid_batchno',
                    tooltip: 'Batch No.',
                }, {
                    header: 'SKU '+MRP,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stiid_leastSKUmrp',
                    tooltip: 'SKU '+MRP,
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [{}, {}]

                }],
            //view: new Ext.grid.GridView(),
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [
//                {html: '&nbsp; Date : &nbsp;'},
//                {
//                    xtype: 'datefield',
//                    width: 120,
//                    id: 'srep_filter_Date',
//                    name: 'srep_filterDate',
//                    format: 'd-M-Y',
//                    value: new Date().add(Date.DAY, -1),
//                    listeners: {
//                        select: function (datefld, date) {
//                            StockReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            StockReport_store.removeAll();
//                            Ext.getCmp('purchase_report_grid').doLayout();
//                        }
//                    }
//                }
//                , '-',
//                {
//                    xtype: 'button',
//                    text: 'Search',
//                    iconCls: 'finascop_search_btn',
//                    handler: function () {
//                        StockReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        StockReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (StockDetailsReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseLastParameters);
                        for (var i = 0; i < Ext.getCmp('stock_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('stock_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('stock_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('stock_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=StockDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: StockDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getFastMovingStockDetailsReportPanel = function (panelId, type) {
        var fastMovingstockDetailsReportGrid = getFastMovingStockDetilsReportGrid(type);
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Fast Moving Items',
            items: [fastMovingstockDetailsReportGrid]
        });
        return panel;
    };
    var getFastMovingStockDetilsReportGrid = function (type) {

        var getFastMovingStockDetailsReportStore = function (type) {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getFastMovingStockDetailsReportGridData',
                fields: ['stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'cpd_branch_id'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    beforeload: function (st, opt) {
                        st.baseParams.type = type
                    },
                    load: function (a, b, options) {
                        purchaseLastParameters = options.params;
                    }
                }

            });
            return SculptueReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'br_Name'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_vendorName'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_poNumber'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_barcode'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_itemmastername'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_batchno'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var FastMovingStockDetailsReport_store = getFastMovingStockDetailsReportStore(type);
        var grid = new Ext.grid.GridPanel({
            store: FastMovingStockDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'fast_moving_stock_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Vendor Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor Name',
                    width: 200
                },
                {
                    header: 'PO Number',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Barcode',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_barcode',
                    tooltip: 'Barcode'
                },
                {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'stiid_itemmastername',
                    tooltip: 'Item Name',
                }, {
                    header: 'Expiry Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_expirydate',
                    tooltip: 'Expiry Date'
                },
                {
                    header: 'Batch No.',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stiid_batchno',
                    tooltip: 'Batch No.',
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [{}, {}]

                }],
            //view: new Ext.grid.GridView(),
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [
//                {html: '&nbsp; Date : &nbsp;'},
//                {
//                    xtype: 'datefield',
//                    width: 120,
//                    id: 'srep_filter_Date',
//                    name: 'srep_filterDate',
//                    format: 'd-M-Y',
//                    value: new Date().add(Date.DAY, -1),
//                    listeners: {
//                        select: function (datefld, date) {
//                            StockReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            StockReport_store.removeAll();
//                            Ext.getCmp('purchase_report_grid').doLayout();
//                        }
//                    }
//                }
//                , '-',
//                {
//                    xtype: 'button',
//                    text: 'Search',
//                    iconCls: 'finascop_search_btn',
//                    handler: function () {
//                        StockReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        StockReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (FastMovingStockDetailsReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseLastParameters);
                        for (var i = 0; i < Ext.getCmp('fast_moving_stock_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('fast_moving_stock_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('fast_moving_stock_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('fast_moving_stock_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=FastMovingStockDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: FastMovingStockDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getBannedStockDetailsReportPanel = function (panelId) {
        var bannedStockDetailsReportGrid = getbannedStockDetailsReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Banned Stock Report',
            items: [bannedStockDetailsReportGrid]
        });
        return panel;
    };
    var getbannedStockDetailsReportGrid = function () {

        var getBannedStockDetailsReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getBannedStockDetailsReportGridData',
                fields: ['stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'cpd_branch_id'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        purchaseLastParameters = options.params;
                    }
                }

            });
            return SculptueReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'br_Name'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_vendorName'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_poNumber'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_barcode'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_itemmastername'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_batchno'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var BannedStockDetailsReport_store = getBannedStockDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: BannedStockDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'banned_stock_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Vendor Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor Name',
                    width: 200
                },
                {
                    header: 'PO Number',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Barcode',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_barcode',
                    tooltip: 'Barcode'
                },
                {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'stiid_itemmastername',
                    tooltip: 'Item Name',
                }, {
                    header: 'Expiry Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_expirydate',
                    tooltip: 'Expiry Date'
                },
                {
                    header: 'Batch No.',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stiid_batchno',
                    tooltip: 'Batch No.',
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [{}, {}]

                }],
            //view: new Ext.grid.GridView(),
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [
//                {html: '&nbsp; Date : &nbsp;'},
//                {
//                    xtype: 'datefield',
//                    width: 120,
//                    id: 'srep_filter_Date',
//                    name: 'srep_filterDate',
//                    format: 'd-M-Y',
//                    value: new Date().add(Date.DAY, -1),
//                    listeners: {
//                        select: function (datefld, date) {
//                            StockReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            StockReport_store.removeAll();
//                            Ext.getCmp('purchase_report_grid').doLayout();
//                        }
//                    }
//                }
//                , '-',
//                {
//                    xtype: 'button',
//                    text: 'Search',
//                    iconCls: 'finascop_search_btn',
//                    handler: function () {
//                        StockReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        StockReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (BannedStockDetailsReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseLastParameters);
                        for (var i = 0; i < Ext.getCmp('banned_stock_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('banned_stock_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('banned_stock_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('banned_stock_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=bannedStockDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: BannedStockDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getB2CSalesDetailReportPanel = function (panelId) {

        var B2CSalesDetailReportGrid = getB2CSalesDetailReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Retail Sales Detail Report',
            items: [B2CSalesDetailReportGrid]
        });
        return panel;
    };
    var getB2CSalesDetailReportGrid = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
            listeners: {
                load: function (thisstore, records, options) {
                    Ext.getCmp('searchdet_br_Name').setValue(_SESSION.finascop_current_branch_id);
                }
            }
        });
        var getB2CSalesDetailsReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getB2CSalesDetailsReportGridData',
                fields: ['orderDate', 'order_order_id','br_Name','item_product_id','stit_SKU','item_igst','item_retail_price','item_sales_price','item_order_qty','item_price','payMode'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        purchaseLastParameters = options.params;
                    }
                }

            });
            return SculptueReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'order_order_id'
                        }, {
                            type: 'string',
                            dataIndex: 'orderDate'
                        }, {
                            type: 'string',
                            dataIndex: 'stit_SKU'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var B2CSalesDetailsReport_store = getB2CSalesDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: B2CSalesDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'b2csales_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order No',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order No'
                }, {
                    header: 'Order Date',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'orderDate',
                    tooltip: 'Order Date'
                },
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, 
                {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item Name',
                },  {
                    header: 'GST',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_igst',
                    tooltip: 'GST',
                }, {
                    header: MRP,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_retail_price',
                    tooltip: MRP,
                },{
                    header: 'Sales Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_sales_price',
                    tooltip: 'Sales Price',
                },{
                    header: 'Quantity',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_order_qty',
                    tooltip: 'Quantity',
                },{
                    header: 'Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_price',
                    tooltip: 'Price',
                }, {
                    header: 'Paymode',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'payMode',
                    tooltip: 'Paymode',
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [{}, {}]

                }],
            //view: new Ext.grid.GridView(),
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
                    xtype: 'combo',
                    id: 'searchdet_br_Name',
                    name: 'searchdet_br_Name',
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
                    hiddenName: 'searchdet_br_Name',
                },
                {html: '&nbsp; From Date : &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'searchdet_stock_from_date',
                    name: 'searchdet_stock_from_date',
                    format: 'd/m/Y',
                    value: new Date()
                }, {html: '&nbsp; To Date : &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'searchdet_stock_to_date',
                    name: 'searchdet_stock_to_date',
                    format: 'd/m/Y',
                    value: new Date()
                },
                '-',
                {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        var search_stock_from_date = Ext.getCmp('searchdet_stock_from_date').getValue();
                        var search_stock_to_date = Ext.getCmp('searchdet_stock_to_date').getValue();
                        Ext.getCmp('b2csales_details_report_grid').filters.clearFilters();
                        Ext.getCmp('b2csales_details_report_grid').getStore().load({
                            params: {
                                br_Name: Ext.getCmp('searchdet_br_Name').getRawValue(),
                                search_stock_from_date: search_stock_from_date,
                                search_stock_to_date: search_stock_to_date
                            }
                        });

                        //var storefilter = Ext.getCmp('b2csales_report_grid');
                        // activateFilter(storefilter, 'br_Name', Ext.getCmp('search_br_Name').getRawValue());

                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (B2CSalesDetailsReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseLastParameters);
                        for (var i = 0; i < Ext.getCmp('b2csales_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('b2csales_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('b2csales_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('b2csales_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=B2CSalesDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: B2CSalesDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getB2BSalesDetailReportPanel = function (panelId) {
        var B2BSalesDetailReportGrid = getB2BSalesDetailReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Wholesale Detail Report',
            items: [B2BSalesDetailReportGrid]
        });
        return panel;
    };
    var getB2BSalesDetailReportGrid = function () {
        var getB2BSalesDetailsReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getB2BSalesDetailsReportGridData',
                fields: ['bbso_SONumber', 'stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'bbso_SODate',
                    'cpd_branch_id', 'stiid_leastSKUmrp'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        purchaseLastParameters = options.params;
                    }
                }

            });
            return SculptueReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'bbso_SONumber'
                        }, {
                            type: 'string',
                            dataIndex: 'bbso_SODate'
                        }, {
                            type: 'string',
                            dataIndex: 'br_Name'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_vendorName'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_poNumber'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_barcode'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_itemmastername'
                        }, {
                            type: 'string',
                            dataIndex: 'stiid_batchno'
                        }, {
                            type: 'date',
                            dataIndex: 'fpo_poDate'
                        }, {
                            type: 'date',
                            dataIndex: 'stiid_expirydate'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var B2BSalesDetailsReport_store = getB2BSalesDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: B2BSalesDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'b2bsales_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'SO No',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'bbso_SONumber',
                    tooltip: 'SO No'
                }, {
                    header: 'Date',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'bbso_SODate',
                    tooltip: 'Date'
                },
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Vendor Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor Name',
                    width: 200
                },
                {
                    header: 'PO Number',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Barcode',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_barcode',
                    tooltip: 'Barcode'
                },
                {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'stiid_itemmastername',
                    tooltip: 'Item Name',
                }, {
                    header: 'Expiry Date',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stiid_expirydate',
                    tooltip: 'Expiry Date'
                },
                {
                    header: 'Batch No.',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stiid_batchno',
                    tooltip: 'Batch No.',
                }, {
                    header: 'SKU '+MRP,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stiid_leastSKUmrp',
                    tooltip: 'SKU '+MRP,
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [{}, {}]

                }],
            //view: new Ext.grid.GridView(),
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChanged
                }
            }),
            tbar: [
//                {html: '&nbsp; Date : &nbsp;'},
//                {
//                    xtype: 'datefield',
//                    width: 120,
//                    id: 'srep_filter_Date',
//                    name: 'srep_filterDate',
//                    format: 'd-M-Y',
//                    value: new Date().add(Date.DAY, -1),
//                    listeners: {
//                        select: function (datefld, date) {
//                            StockReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            StockReport_store.removeAll();
//                            Ext.getCmp('purchase_report_grid').doLayout();
//                        }
//                    }
//                }
//                , '-',
//                {
//                    xtype: 'button',
//                    text: 'Search',
//                    iconCls: 'finascop_search_btn',
//                    handler: function () {
//                        StockReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        StockReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (B2BSalesDetailsReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseLastParameters);
                        for (var i = 0; i < Ext.getCmp('b2bsales_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('b2bsales_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('b2bsales_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('b2bsales_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=B2BSalesDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: B2BSalesDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    return {
        stockReport: function ()
        {
            var panelId = 'stockReportPanelID';
            var stockReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(stockReportPanel)) {
                stockReportPanel = getStockReportPanel(panelId);
                Application.UI.addTab(stockReportPanel);
                stockReportPanel.doLayout();
            } else {
                Application.UI.addTab(stockReportPanel);
            }
        }, B2CSalesReport: function ()
        {
            var panelId = 'B2CSalesReportPanelID';
            var B2CSalesReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(B2CSalesReportPanel)) {
                B2CSalesReportPanel = getB2CSalesReportPanel(panelId);
                Application.UI.addTab(B2CSalesReportPanel);
                B2CSalesReportPanel.doLayout();
            } else {
                Application.UI.addTab(B2CSalesReportPanel);
            }
        }, B2BSalesReport: function ()
        {
            var panelId = 'B2BSalesReportPanelID';
            var B2BSalesReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(B2BSalesReportPanel)) {
                B2BSalesReportPanel = getB2BSalesReportPanel(panelId);
                Application.UI.addTab(B2BSalesReportPanel);
                B2BSalesReportPanel.doLayout();
            } else {
                Application.UI.addTab(B2BSalesReportPanel);
            }
        }, stockDetailsReport: function ()
        {
            var panelId = 'stockDetailsReportPanelID';
            var stockDetailsReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(stockDetailsReportPanel)) {
                stockDetailsReportPanel = getStockDetailsReportPanel(panelId);
                Application.UI.addTab(stockDetailsReportPanel);
                stockDetailsReportPanel.doLayout();
            } else {
                Application.UI.addTab(stockDetailsReportPanel);
            }
        }, fastMovingstockDetailsReport: function () {
            var type = 'fast';
            var panelId = 'fastMovingstockDetailsReportPanelID';
            var fastMovingstockDetailsReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(fastMovingstockDetailsReportPanel)) {
                fastMovingstockDetailsReportPanel = getFastMovingStockDetailsReportPanel(panelId, type);
                Application.UI.addTab(fastMovingstockDetailsReportPanel);
                fastMovingstockDetailsReportPanel.doLayout();
            } else {
                Application.UI.addTab(fastMovingstockDetailsReportPanel);
            }
        }, bannedStockDetailsReport: function ()
        {
            var panelId = 'bannedStockDetailsReportPanelID';
            var bannedStockDetailsReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(bannedStockDetailsReportPanel)) {
                bannedStockDetailsReportPanel = getBannedStockDetailsReportPanel(panelId);
                Application.UI.addTab(bannedStockDetailsReportPanel);
                bannedStockDetailsReportPanel.doLayout();
            } else {
                Application.UI.addTab(bannedStockDetailsReportPanel);
            }

        }, B2CSalesDetailReport: function ()
        {
            var panelId = 'B2CSalesDetailReportPanelID';
            var B2CSalesDetailReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(B2CSalesDetailReportPanel)) {
                B2CSalesDetailReportPanel = getB2CSalesDetailReportPanel(panelId);
                Application.UI.addTab(B2CSalesDetailReportPanel);
                B2CSalesDetailReportPanel.doLayout();
            } else {
                Application.UI.addTab(B2CSalesDetailReportPanel);
            }
        }, B2BSalesDetailReport: function ()
        {
            var panelId = 'B2BSalesDetailReportPanelID';
            var B2BSalesDetailReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(B2BSalesDetailReportPanel)) {
                B2BSalesDetailReportPanel = getB2BSalesDetailReportPanel(panelId);
                Application.UI.addTab(B2BSalesDetailReportPanel);
                B2BSalesDetailReportPanel.doLayout();
            } else {
                Application.UI.addTab(B2BSalesDetailReportPanel);
            }
        }
    }
}();