Application.StockReturnReports = function ()
{
    var stockRerturnLastParameters, SalesReturnLastParameters;
    var retDetLastParameters, stockLostParameters;
    var recs_per_page = 25;
    var modURL = '?module=stockreturn_report';

    var getStockReturnReportGrid = function () {

        var getStockReturnReportStore = function () {
            var StockReturnReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getStockReturnReportGridData',
                fields: ['stit_category_name', 'br_Name', 'stit_SKU', 'item_count', 'item_returned', 'mrp', 'selling_price', 'stit_id', 'frbi_epr', 'frbi_id',
                    'branchId', 'fsbg_name', 'fsbg_id', 'stit_quantity', 'stit_brand_name', 'stit_product_variant', 'frbi_statusName', 'frbi_status', 'item_lossCount'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        stockRerturnLastParameters = options.params;

                    }
                }

            });
            return StockReturnReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'br_Name'
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
                            dataIndex: 'stit_category_name'
                        }, {
                            type: 'string',
                            dataIndex: 'stit_brand_name'
                        }, {
                            type: 'string',
                            dataIndex: 'stit_product_variant'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var StockReturnReport_store = getStockReturnReportStore();
        var grid = new Ext.grid.GridPanel({
            store: StockReturnReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'stock_return_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item SKU',
                    sortable: true,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item SKU',
                }, {
                    header: 'Category',
                    sortable: true,
                    dataIndex: 'stit_category_name',
                    tooltip: 'Category',
                }, {
                    header: 'Brand',
                    sortable: true,
                    dataIndex: 'stit_brand_name',
                    tooltip: 'Brand',
                }, {
                    header: 'Variant',
                    sortable: true,
                    dataIndex: 'stit_product_variant',
                    tooltip: 'Variant',
                }, {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'stit_quantity',
                    tooltip: 'Quantity',
                },
                {
                    header: 'Count',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_lossCount',
                    tooltip: 'Lost Count'
                },
                {
                    header: MRP,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: MRP
                },
                {
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Selling Price',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT
                }, {
                    header: 'EPR',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'frbi_epr',
                    tooltip: 'EPR',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'frbi_statusName',
                    tooltip: 'Status'
                }, {
                    hidden: true
                }, {
                    hidden: true
                }, {
                    hidden: true
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
//                            StockReturnReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            StockReturnReport_store.removeAll();
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
//                        StockReturnReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        StockReturnReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (StockReturnReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(stockRerturnLastParameters);
                        for (var i = 0; i < Ext.getCmp('stock_return_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('stock_return_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('stock_return_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('stock_return_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=StockReturnReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: StockReturnReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;

    };
    var getStockReturnReportPanel = function (panelId) {
        var stockReturnReportGrid = getStockReturnReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Stock Return Report',
            items: [stockReturnReportGrid]
        });
        return panel;
    };

    var getStockLostReportGrid = function () {
        var getStockLostReportStore = function () {
            var StockLostReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getStockLostReportGridData',
                fields: ['rtrqod_item_id', 'stit_SKU', 'item_count', 'returnOrders', 'item_lossCount', 'br_Name'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        stockRerturnLastParameters = options.params;

                    }
                }

            });
            return StockLostReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'stit_SKU'
                        }, {
                            type: 'string',
                            dataIndex: 'item_count'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var StockLostReport_store = getStockLostReportStore();
        var grid = new Ext.grid.GridPanel({
            store: StockLostReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'stock_lost_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 150
                },
                {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item Name',
                    width: 150
                },
                {
                    header: 'Lost Count',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_lossCount',
                    tooltip: 'Lost Count'
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    width: 25,
                    items: [{
                            hidden: true
                        }, {
                            hidden: true
                        }, {
                            hidden: true
                        }]
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
//                            StockReturnReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            StockReturnReport_store.removeAll();
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
//                        StockReturnReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        StockReturnReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (StockLostReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(stockRerturnLastParameters);
                        for (var i = 0; i < Ext.getCmp('stock_lost_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('stock_lost_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('stock_lost_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('stock_lost_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=StockLostReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: StockLostReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getStockLostReportPanel = function (panelId) {
        var stockLostReportGrid = getStockLostReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Stock Lost Report',
            items: [stockLostReportGrid]
        });
        return panel;
    };
    var getReturnDetailsReportPanel = function (panelId) {
        var returnDetailsReportGrid = getReturnDetailsReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Return Details Report',
            items: [returnDetailsReportGrid]
        });
        return panel;
    };
    var getReturnDetailsReportGrid = function () {
        var getReturnDetailsReportStore = function () {
            var ReturnDetailsReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getReturnDetailsReportGridData',
                fields: ['stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'cpd_branch_id', 'stiid_description'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        retDetLastParameters = options.params;

                    }
                }

            });
            return ReturnDetailsReportStore;
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
                            type: 'string',
                            dataIndex: 'stiid_description'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var ReturnDetailsReport_store = getReturnDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: ReturnDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'return_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Vendor Name',
                    sortable: true,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor Name',
                    width: 200
                },
                {
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Barcode',
                    sortable: true,
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
                    dataIndex: 'stiid_expirydate',
                    tooltip: 'Expiry Date'
                },
                {
                    header: 'Batch No.',
                    sortable: true,
                    dataIndex: 'stiid_batchno',
                    tooltip: 'Batch No.',
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'stiid_description',
                    tooltip: 'Status',
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
//                            PurchaseReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            PurchaseReport_store.removeAll();
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
//                        PurchaseReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        PurchaseReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (ReturnDetailsReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(retDetLastParameters);
                        for (var i = 0; i < Ext.getCmp('return_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('return_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('return_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('return_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=ReturnDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: ReturnDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getSalesReturnReportPanelReportPanel = function (panelId) {
        var SalesReturnReportGrid = getSalesReturnReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Sales Return Report',
            items: [SalesReturnReportGrid]
        });
        return panel;
    };
    var getSalesReturnReportGrid = function () {

        var getB2CSalesReportStore = function () {
            var B2CSalesReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getSalesReturnReportGridData',
                fields: ['rtrqo_id', 'order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on',
                    'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes',
                    'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'order_itemReturnRequestCount', 'order_qty', 'cust_mobile'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        SalesReturnLastParameters = options.params;
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
                            dataIndex: 'order_status'
                        }, {
                            type: 'string',
                            dataIndex: 'br_Name'
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
            id: 'sales_return_report_grid',
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
                    hidden: true,
                }, {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                }, {
                    header: 'Time',
                    sortable: true,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                },
                {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Customer',
                    width: 200,
                }, {
                    header: 'Mobile',
                    dataIndex: 'cust_mobile',
                    sortable: true,
                    tooltip: 'Mobile',
                }, {
                    header: 'Total Qty',
                    dataIndex: 'order_qty',
                    sortable: true,
                    tooltip: 'Total Qty',
                }, {
                    header: 'Return Qty',
                    dataIndex: 'order_itemReturnRequestCount',
                    sortable: true,
                    tooltip: 'Return Qty',
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
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
                        var filterData = Ext.encode(SalesReturnLastParameters);
                        for (var i = 0; i < Ext.getCmp('sales_return_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('sales_return_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('sales_return_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('sales_return_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=SalesReturnReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
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
    var getStockLostDetailReportPanel = function (panelId) {
        var stockLostDetailsReportGrid = getStockLostDetailsReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Stock Lost Detail Report',
            items: [stockLostDetailsReportGrid]
        });
        return panel;
    };
    var getStockLostDetailsReportGrid = function () {




        var getStockLostDetailsReportStore = function () {
            var StockLostDetailsReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getStockLostDetailsReportGridData',
                fields: ['stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'cpd_branch_id', 'stiid_description'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        stockLostParameters = options.params;

                    }
                }

            });
            return StockLostDetailsReportStore;
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
                            type: 'string',
                            dataIndex: 'stiid_description'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var StockLostDetailsReport_store = getStockLostDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: StockLostDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'stock_lost_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Vendor Name',
                    sortable: true,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor Name',
                    width: 200
                },
                {
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Barcode',
                    sortable: true,
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
                    dataIndex: 'stiid_expirydate',
                    tooltip: 'Expiry Date'
                },
                {
                    header: 'Batch No.',
                    sortable: true,
                    dataIndex: 'stiid_batchno',
                    tooltip: 'Batch No.',
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'stiid_description',
                    tooltip: 'Status',
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
//                            PurchaseReport_store.baseParams = {
//                                date_of_sale: ''
//                            };
//
//                            PurchaseReport_store.removeAll();
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
//                        PurchaseReport_store.baseParams = {
//                            date_of_sale: Ext.util.Format.date(Ext.getCmp('srep_filter_Date').getValue(), 'Y-m-d')
//                        }
//
//                        PurchaseReport_store.load();
//
//                    }
//                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (StockLostDetailsReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(stockLostParameters);
                        for (var i = 0; i < Ext.getCmp('stock_lost_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('stock_lost_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('stock_lost_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('stock_lost_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=StockLostDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: StockLostDetailsReport_store,
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
        stockReturnReport: function ()
        {
            var panelId = 'stockReturnReportPanelID';
            var stockReturnReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(stockReturnReportPanel)) {
                stockReturnReportPanel = getStockReturnReportPanel(panelId);
                Application.UI.addTab(stockReturnReportPanel);
                stockReturnReportPanel.doLayout();
            } else {
                Application.UI.addTab(stockReturnReportPanel);
            }
        }, stockLostReport: function ()
        {
            var panelId = 'stockLostReportPanelID';
            var stockLostReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(stockLostReportPanel)) {
                stockLostReportPanel = getStockLostReportPanel(panelId);
                Application.UI.addTab(stockLostReportPanel);
                stockLostReportPanel.doLayout();
            } else {
                Application.UI.addTab(stockLostReportPanel);
            }
        }, returnDetailReport: function () {
            var panelId = 'returnDetailsReportPanelID';
            var returnDetailsReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(returnDetailsReportPanel)) {
                returnDetailsReportPanel = getReturnDetailsReportPanel(panelId);
                Application.UI.addTab(returnDetailsReportPanel);
                returnDetailsReportPanel.doLayout();
            } else {
                Application.UI.addTab(returnDetailsReportPanel);
            }
        }, salesReturnReport: function () {
            var panelId = 'salesReturnReportPanelID';
            var salesReturnReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(salesReturnReportPanel)) {
                salesReturnReportPanel = getSalesReturnReportPanelReportPanel(panelId);
                Application.UI.addTab(salesReturnReportPanel);
                salesReturnReportPanel.doLayout();
            } else {
                Application.UI.addTab(salesReturnReportPanel);
            }
        }, stockLostDetailReport: function ()
        {
            var panelId = 'stockLostDetailReportPanelID';
            var stockLostDetailReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(stockLostDetailReportPanel)) {
                stockLostDetailReportPanel = getStockLostDetailReportPanel(panelId);
                Application.UI.addTab(stockLostDetailReportPanel);
                stockLostDetailReportPanel.doLayout();
            } else {
                Application.UI.addTab(stockLostDetailReportPanel);
            }
        }
    }
}();