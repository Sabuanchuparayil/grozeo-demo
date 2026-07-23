Application.PurchaseReports = function ()
{
    var purchaseLastParameters;
    var purchaseReturnLastParameters;
    var recs_per_page = 25;
    var modURL = '?module=purchase_report';

    var getPurchaseReportGrid = function () {

        var getPurchaseReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseReportGridData',
                fields: ['fpe_id', 'fpe_vendorName', 'fpe_fpoPoNumber', 'fpe_fpoPODate',
                    'fpe_invoiceNumber', 'fpe_invoiceDate', {name: 'fpe_grossAmt', type: 'float'},
                    {name: 'fpe_discount', type: 'float'}, {name: 'fpe_netQty', type: 'int'}, {name: 'fpe_netItems', type: 'int'},
                    {name: 'fpe_netTax', type: 'float'}, {name: 'fpe_netAmount', type: 'float'},
                    {name: 'fpe_netIgst', type: 'float'}, {name: 'fpe_netCgst', type: 'float'},
                    {name: 'fpe_netSgst', type: 'float'}],
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
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var PurchaseReport_store = getPurchaseReportStore();
        var grid = new Ext.grid.GridPanel({
            store: PurchaseReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'purchase_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Vendor',
                    id: 'vendor_name_auto_exp',
                    sortable: true,
                    dataIndex: 'fpe_vendorName',
                    tooltip: 'Vendor Name'
                }, {
                    header: 'PO No:',
                    sortable: true,
                    dataIndex: 'fpe_fpoPoNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'Invoice No:',
                    sortable: true,
                    dataIndex: 'fpe_invoiceNumber',
                    tooltip: 'Invoice Number'
                }, {
                    header: 'Invoice Date',
                    sortable: true,
                    dataIndex: 'fpe_invoiceDate',
                    tooltip: 'Invoice Date'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    dataIndex: 'fpe_fpoPODate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Net Amt.',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpe_netAmount',
                    tooltip: 'Net Amt.'
                }, {
                    header: 'IGST',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpe_netIgst',
                    tooltip: 'IGST'
                }, {
                    header: 'CGST',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpe_netCgst',
                    tooltip: 'CGST'
                }, {
                    header: 'SGST',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpe_netSgst',
                    tooltip: 'SGST'
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
                        if (PurchaseReport_store.getTotalCount() <= 0) {
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
                        for (var i = 0; i < Ext.getCmp('purchase_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('purchase_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('purchase_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('purchase_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=PurchaseReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: PurchaseReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;

    };
    var getPurchaseReportPanel = function (panelId) {
        var purchaseReportGrid = getPurchaseReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Purchase Report',
            items: [purchaseReportGrid]
        });
        return panel;
    };
    var getPurchaseReturnReportPanel = function (panelId) {
        var purchaseReturnReportGrid = getPurchaseReturnReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Purchase Return Report',
            items: [purchaseReturnReportGrid]
        });
        return panel;

    };

    var getPurchaseReturnReportGrid = function () {
        var getPurchaseReturnReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseReturnReportGridData',
                fields: ['stit_category_name', 'blocked_count', 'cart_count', 'br_Name', 'stit_SKU', 'item_count', 'bom_offrPlacement', 'mrp', 'selling_price', 'optimumqty', 'minimumqty', 'maximumqty', 'stit_id', 'frbi_epr', 'frbi_id',
                    'branchId', 'rack_count', 'dispatch_count', 'receive_count', 'deliver_count', 'fsbg_name', 'fsbg_id', 'stit_quantity', 'stit_brand_name', 'stit_product_variant', 'frbi_leastSKUepr', 'frbi_statusName', 'frbi_status', 'frbi_leastSKUmrp'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        purchaseReturnLastParameters = options.params;

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
                        }, {
                            type: 'string',
                            dataIndex: 'stit_quantity'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var PurchaseReturnReport_store = getPurchaseReturnReportStore();
        var grid = new Ext.grid.GridPanel({
            store: PurchaseReturnReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'purchase_return_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
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
                    header: 'Total Count',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'item_count',
                    tooltip: 'Total Count'
                },
                {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: 'MRP'
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
                        if (PurchaseReturnReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseReturnLastParameters);
                        for (var i = 0; i < Ext.getCmp('purchase_return_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('purchase_return_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('purchase_return_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('purchase_return_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=PurchaseReturnReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: PurchaseReturnReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getPurchaseReturnedReportPanel = function (panelId) {
        var purchaseReturnedReportGrid = getPurchaseReturnedReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Purchase Returned Report',
            items: [purchaseReturnedReportGrid]
        });
        return panel;
    };
    var getPurchaseReturnedReportGrid = function () {
        var getPurchaseReturnedReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseReturnedReportGridData',
                fields: ['stit_category_name', 'br_Name', 'stit_SKU', 'item_count', 'item_returned', 'mrp', 'selling_price', 'stit_id', 'frbi_epr', 'frbi_id',
                    'branchId', 'fsbg_name', 'fsbg_id', 'stit_quantity', 'stit_brand_name', 'stit_product_variant', 'frbi_statusName', 'frbi_status'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        purchaseReturnLastParameters = options.params;

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
                        }, {
                            type: 'string',
                            dataIndex: 'stit_quantity'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var PurchaseReturnedReport_store = getPurchaseReturnedReportStore();
        var grid = new Ext.grid.GridPanel({
            store: PurchaseReturnedReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'purchase_returnable_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
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
                    dataIndex: 'item_returned',
                    tooltip: 'Returned Count'
                },
                {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: 'MRP'
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
                        if (PurchaseReturnedReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(purchaseReturnLastParameters);
                        for (var i = 0; i < Ext.getCmp('purchase_returnable_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('purchase_returnable_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('purchase_returnable_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('purchase_returnable_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=PurchaseReturnedReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: PurchaseReturnedReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getPurchaseDetailsReportPanel = function (panelId) {
        var purchaseDetailsReportGrid = getPurchaseDetailsReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Purchase Details Report',
            items: [purchaseDetailsReportGrid]
        });
        return panel;
    };
    var getPurchaseDetailsReportGrid = function () {

        var getPurchaseDetailsReportStore = function () {
            var PurchaseDetailsReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseDetailsReportGridData',
                fields: ['fpod_id', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'fpod_itemname', 'fpod_itemqty', 'fpod_totalqty', 'fpod_giftname', 'fpod_giftqty', 'fpod_itemSmallStockUnit', 'fpod_leastSKUqty', 'fpod_leastSKUmrp',
                    'fpod_customerRateHmDel', 'fpod_itemaddidisc', 'itemDisc', 'package_type_name', 'fpod_leastSKUepr', 'fpod_itempoqty'],
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
            return PurchaseDetailsReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'fpo_vendorName'
                        }, {
                            type: 'string',
                            dataIndex: 'fpo_poNumber'
                        }, {
                            type: 'date',
                            dataIndex: 'fpo_poDate'
                        }, {
                            type: 'string',
                            dataIndex: 'fpod_itemname'
                        }, {
                            type: 'string',
                            dataIndex: 'fpod_itempoqty'
                        }, {
                            type: 'string',
                            dataIndex: 'package_type_name'
                        }, {
                            type: 'string',
                            dataIndex: 'fpod_leastSKUepr'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var PurchaseDetailsReport_store = getPurchaseDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: PurchaseDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'purchase_details_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Vendor',
                    id: 'fpo_vendorName_exp',
                    sortable: true,
                    dataIndex: 'fpo_vendorName',
                    tooltip: 'Vendor Name'
                }, {
                    header: 'PO No:',
                    sortable: true,
                    dataIndex: 'fpo_poNumber',
                    tooltip: 'PO Number'
                }, {
                    header: 'PO Date',
                    sortable: true,
                    dataIndex: 'fpo_poDate',
                    tooltip: 'PO Date'
                }, {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'fpod_itemname',
                    tooltip: 'Item Name'
                }, {
                    header: 'PO Qty',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpod_itempoqty',
                    tooltip: 'PO Qty'
                }, {
                    header: 'Unit',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'package_type_name',
                    tooltip: 'Unit'
                }, {
                    header: 'Gift Name',
                    sortable: true,
                    dataIndex: 'fpod_giftname',
                    tooltip: 'Gift Name'
                }, {
                    header: 'Gift Qty',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpod_giftqty',
                    tooltip: 'Gift Qty'
                }, {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpod_leastSKUmrp',
                    tooltip: 'Gift Qty'
                }, {
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpod_customerRateHmDel',
                    tooltip: 'Selling Price'
                }, {
                    header: 'EPR in SKU',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'fpod_leastSKUepr',
                    tooltip: 'EPR in SKU'
                }, {
                    header: 'Discount',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'itemDisc',
                    tooltip: 'Discount'
                }, {
                    xtype: 'actioncolumn',
                    header: '',
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
                        if (PurchaseDetailsReport_store.getTotalCount() <= 0) {
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
                        for (var i = 0; i < Ext.getCmp('purchase_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('purchase_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('purchase_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('purchase_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=PurchaseDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: PurchaseDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getPurchaseReturnableDetailsReportPanel = function (panelId) {
        var purchaseReturnableDetailsReportGrid = getPurchaseReturnableDetailsReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Purchase Returnable Details Report',
            items: [purchaseReturnableDetailsReportGrid]
        });
        return panel;
    };
    var getPurchaseReturnableDetailsReportGrid = function () {


        var getPurchaseReturnableDetailsReportStore = function () {
            var PurchaseReturnableDetailsReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseReturnableDetailsReportGridData',
                fields: ['stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'cpd_branch_id', 'stiid_description'],
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
            return PurchaseReturnableDetailsReportStore;
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

        var PurchaseReturnableDetailsReport_store = getPurchaseReturnableDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: PurchaseReturnableDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'purchase_returnable_details_report_grid',
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
                        if (PurchaseReturnableDetailsReport_store.getTotalCount() <= 0) {
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
                        for (var i = 0; i < Ext.getCmp('purchase_returnable_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('purchase_returnable_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('purchase_returnable_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('purchase_returnable_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=PurchaseReturnableDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: PurchaseReturnableDetailsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });
        return grid;
    };
    var getPurchaseReturnedDetailsReportPanel = function (panelId) {
        var purchaseReturnedDetailsReportGrid = getPurchaseReturnedDetailsReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Purchase Returned Detail Report',
            items: [purchaseReturnedDetailsReportGrid]
        });
        return panel;
    };
    var getPurchaseReturnedDetailsReportGrid = function () {



        var getPurchaseReturnedDetailsReportStore = function () {
            var PurchaseReturnedDetailsReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getPurchaseReturnedDetailsReportGridData',
                fields: ['stiid_id', 'br_Name', 'fpo_id', 'fpo_vendorName', 'fpo_poNumber', 'fpo_poDate', 'stiid_barcode', 'stiid_itemmastername', 'stiid_expirydate', 'stiid_batchno', 'cpd_branch_id', 'stiid_description'],
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
            return PurchaseReturnedDetailsReportStore;
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

        var PurchaseReturnedDetailsReport_store = getPurchaseReturnedDetailsReportStore();
        var grid = new Ext.grid.GridPanel({
            store: PurchaseReturnedDetailsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'purchase_returned_details_report_grid',
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
                        if (PurchaseReturnedDetailsReport_store.getTotalCount() <= 0) {
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
                        for (var i = 0; i < Ext.getCmp('purchase_returned_details_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('purchase_returned_details_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('purchase_returned_details_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('purchase_returned_details_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=PurchaseReturnedDetailsReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: PurchaseReturnedDetailsReport_store,
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
        purchaseReport: function ()
        {
            var panelId = 'purchaseReportPanelID';
            var purchaseReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchaseReportPanel)) {
                purchaseReportPanel = getPurchaseReportPanel(panelId);
                Application.UI.addTab(purchaseReportPanel);
                purchaseReportPanel.doLayout();
            } else {
                Application.UI.addTab(purchaseReportPanel);
            }
        },
        purchaseReturnReport: function ()
        {
            var panelId = 'purchaseReturnReportPanelID';
            var purchaseReturnReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchaseReturnReportPanel)) {
                purchaseReturnReportPanel = getPurchaseReturnReportPanel(panelId);
                Application.UI.addTab(purchaseReturnReportPanel);
                purchaseReturnReportPanel.doLayout();
            } else {
                Application.UI.addTab(purchaseReturnReportPanel);
            }
        }, purchaseReturnedReport: function ()
        {
            var panelId = 'purchaseReturnedReportPanelID';
            var purchaseReturnedReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchaseReturnedReportPanel)) {
                purchaseReturnedReportPanel = getPurchaseReturnedReportPanel(panelId);
                Application.UI.addTab(purchaseReturnedReportPanel);
                purchaseReturnedReportPanel.doLayout();
            } else {
                Application.UI.addTab(purchaseReturnedReportPanel);
            }
        }, purchaseDetailsReport: function () {
            var panelId = 'purchaseDetailsReportPanelID';
            var purchaseDetailsReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchaseDetailsReportPanel)) {
                purchaseDetailsReportPanel = getPurchaseDetailsReportPanel(panelId);
                Application.UI.addTab(purchaseDetailsReportPanel);
                purchaseDetailsReportPanel.doLayout();
            } else {
                Application.UI.addTab(purchaseDetailsReportPanel);
            }
        }, purchaseReturnableDetailsReport: function () {
            var panelId = 'purchaseReturnableDetailsReportPanelID';
            var purchaseReturnableDetailsReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchaseReturnableDetailsReportPanel)) {
                purchaseReturnableDetailsReportPanel = getPurchaseReturnableDetailsReportPanel(panelId);
                Application.UI.addTab(purchaseReturnableDetailsReportPanel);
                purchaseReturnableDetailsReportPanel.doLayout();
            } else {
                Application.UI.addTab(purchaseReturnableDetailsReportPanel);
            }
        }, purchaseReturnedDetailsReport: function () {
            var panelId = 'purchaseReturnedDetailsReportPanelID';
            var purchaseReturnedDetailsReportPanelID = Ext.getCmp(panelId);
            if (Ext.isEmpty(purchaseReturnedDetailsReportPanelID)) {
                purchaseReturnedDetailsReportPanelID = getPurchaseReturnedDetailsReportPanel(panelId);
                Application.UI.addTab(purchaseReturnedDetailsReportPanelID);
                purchaseReturnedDetailsReportPanelID.doLayout();
            } else {
                Application.UI.addTab(purchaseReturnedDetailsReportPanelID);
            }
        }
    }
}();