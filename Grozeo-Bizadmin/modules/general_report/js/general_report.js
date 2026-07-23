Application.GeneralReports = function ()
{
    var supplierLastParameters;
    var customerLastParameters;
    var creditAgeLastParameters;
    var recs_per_page = 25;
    var modURL = '?module=general_report';

    var getSupplierReportGrid = function () {

        var getSculptueReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getSuppliersReportGridData',
                fields: ['customerId', 'stpa_Fname', 'stpa_Lname', 'stpa_GSTIN', 'stpa_Address', 'stpa_City', 'stpa_PINCODE', 'st_name', 'dst_Name', 'st_id', 'dst_Id', 'stpa_ContactPerson', 'stpa_MobileNo',
                    'stpa_Email', 'stpa_PanNo', 'stpa_dlno1', 'stpa_dlno2', 'stpa_fssaino'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        supplierLastParameters = options.params;

                    }
                }

            });
            return SculptueReportStore;
        };

        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'stpa_Fname'
                        },
                        {
                            type: 'string',
                            dataIndex: 'stpa_Lname'
                        },
                        {
                            type: 'string',
                            dataIndex: 'stpa_GSTIN'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_City'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_PINCODE'
                        }, {
                            type: 'string',
                            dataIndex: 'st_name'
                        }, {
                            type: 'string',
                            dataIndex: 'dst_Name'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_PanNo'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_Email'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_MobileNo'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_dlno1'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_dlno2'
                        }, {
                            type: 'string',
                            dataIndex: 'stpa_fssaino'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var SculptueReport_store = getSculptueReportStore();
        var grid = new Ext.grid.GridPanel({
            store: SculptueReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'supplier_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'stpa_Fname',
                },
                {
                    header: 'GST / VAT No ',
                    sortable: true,
                    dataIndex: 'stpa_GSTIN',
                    width: 175
                }, {
                    header: 'PAN No.',
                    sortable: true,
                    dataIndex: 'stpa_PanNo',
                }, {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'stpa_Email',
                }, {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'stpa_MobileNo',
                }, {
                    header: 'DL1',
                    sortable: true,
                    dataIndex: 'stpa_dlno1',
                }, {
                    header: 'DL2',
                    sortable: true,
                    dataIndex: 'stpa_dlno2',
                }, {
                    header: 'FSSAI / EORI No.',
                    sortable: true,
                    dataIndex: 'stpa_fssaino',
                },
                {
                    header: 'City',
                    sortable: true,
                    dataIndex: 'stpa_City',
                },
                {
                    header: 'Post Code',
                    sortable: true,
                    dataIndex: 'stpa_PINCODE',
                    width: 175
                },
                {
                    header: 'State',
                    sortable: true,
                    dataIndex: 'st_name',
                },
                {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'dst_Name',
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
                        if (SculptueReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(supplierLastParameters);
                        for (var i = 0; i < Ext.getCmp('supplier_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('supplier_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('supplier_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('supplier_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=SupplierReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: SculptueReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });

        return grid;

    };
    var getSupplierReportPanel = function (panelId) {
        var supplierReportGrid = getSupplierReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Supplier Report',
            items: [supplierReportGrid]
        });
        return panel;
    };
    var getCustomerReportPanel = function (panelId) {
        var customerReportGrid = getCustomerReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Customer Report',
            items: [customerReportGrid]
        });
        return panel;
    };
    var getCustomerReportGrid = function () {


        var getCustomerReportStore = function () {
            var SculptueReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getCustomerReportGridData',
                fields: ['cust_id', 'cust_customer_name', 'cust_mobile', 'cust_email', 'cust_walletbalance', 'cust_prom_reward_point', 'cust_status', 'cust_ref_code', 'cust_branch_id',
                    'cust_mobile', 'cust_customer_id', 'customer_pin',
                    'cust_status', 'customer_district', 'customer_state', 'enabled_delivery'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        customerLastParameters = options.params;

                    }
                }

            });
            return SculptueReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'cust_customer_name'
                        }, {
                            type: 'string',
                            dataIndex: 'cust_mobile'
                        }, {
                            type: 'string',
                            dataIndex: 'cust_email'
                        }, {
                            type: 'string',
                            dataIndex: 'cust_ref_code'
                        }, {
                            type: 'string',
                            dataIndex: 'customer_pin'
                        }, {
                            type: 'string',
                            dataIndex: 'customer_state'
                        }, {
                            type: 'string',
                            dataIndex: 'cust_walletbalance'
                        }, {
                            type: 'string',
                            dataIndex: 'cust_prom_reward_point'
                        }, {
                            type: 'string',
                            dataIndex: 'cust_status'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var CustomerReport_store = getCustomerReportStore();
        var grid = new Ext.grid.GridPanel({
            store: CustomerReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'customer_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    id: 'cust_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'cust_customer_name',
                },
                {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'cust_mobile',
                }, {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'cust_email',
                }, {
                    header: 'Customer Code',
                    sortable: true,
                    dataIndex: 'cust_ref_code',
                }, {
                    header: 'Reward Point',
                    sortable: true,
                    dataIndex: 'cust_prom_reward_point',
                }, {
                    header: 'Wallet Balance',
                    sortable: true,
                    dataIndex: 'cust_walletbalance',
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'cust_status',
                }, ],
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
                        if (CustomerReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(supplierLastParameters);
                        for (var i = 0; i < Ext.getCmp('customer_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('customer_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('customer_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('customer_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=CustomerReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: CustomerReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'cust_name_auto_exp'

        });

        return grid;
    };
    var getDeliveryResourceReportPanel = function (panelId) {
        var deliveryResourceReportGrid = getDeliveryResourceReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Delivery Resource Report',
            items: [deliveryResourceReportGrid]
        });
        return panel;
    };
    var getDeliveryResourceReportGrid = function () {



        var getDeliveryResourceReportStore = function () {
            var deliveryResourceReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getDeliveryResourceReportGridData',
                fields: ['rownum', 'd_ID', 'address', 'd_Name', 'd_Ph1', 'd_licence', 'd_licenceexpairy', 'branch', 'd_isallowAutoSchedule', 'd_isallowManualSchedule'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        customerLastParameters = options.params;

                    }
                }

            });
            return deliveryResourceReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'd_Name'

                        }, {
                            type: 'string',
                            dataIndex: 'address'

                        }, {
                            type: 'string',
                            dataIndex: 'd_Ph1'

                        }, {
                            type: 'string',
                            dataIndex: 'd_isallowAutoSchedule'

                        }, {
                            type: 'list',
                            options: ['Yes', 'No'],
                            phpMode: true,
                            dataIndex: 'd_isallowAutoSchedule'
                        }, {
                            type: 'string',
                            dataIndex: 'd_isallowManualSchedule'

                        }, {
                            type: 'list',
                            options: ['Yes', 'No'],
                            phpMode: true,
                            dataIndex: 'd_isallowManualSchedule'
                        }, {
                            type: 'string',
                            dataIndex: 'branch'

                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var DeliveryResourceReport_store = getDeliveryResourceReportStore();
        var grid = new Ext.grid.GridPanel({
            store: DeliveryResourceReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'deliveryresource_report_grid',
            columns: [new Ext.grid.RowNumberer(), {
                    header: 'Driver  ',
                    id: 'd_name',
                    sortable: true,
                    dataIndex: 'd_Name'
                }, {
                    header: 'Address ',
                    id: 'd_address',
                    sortable: true,
                    dataIndex: 'address'
                }, {
                    header: 'Phone',
                    id: 'd_phone',
                    sortable: true,
                    dataIndex: 'd_Ph1'
                }, {
                    header: 'Auto Schedule',
                    sortable: true,
                    dataIndex: 'd_isallowAutoSchedule'
                }, {
                    header: 'Manual Schedule',
                    sortable: true,
                    dataIndex: 'd_isallowManualSchedule'
                }, {
                    header: 'Branch',
                    id: 'd_branch',
                    sortable: true,
                    dataIndex: 'branch'
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
                        if (DeliveryResourceReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(supplierLastParameters);
                        for (var i = 0; i < Ext.getCmp('deliveryresource_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('deliveryresource_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('deliveryresource_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('deliveryresource_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=DeliveryResourceReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: DeliveryResourceReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'cust_name_auto_exp'

        });

        return grid;
    };
    var getCreditAgeReportPanel = function (panelId) {
        var creditAgeReportGrid = getCreditAgeReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Credit Age Report',
            items: [creditAgeReportGrid]
        });
        return panel;
    };
    var getCreditAgeReportGrid = function () {
        var getCreditAgeReportStore = function () {
            var creditAgeReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getCreditAgeReportGridData',
                fields: ['bbso_id', 'b2b_Customer_Name', 'bbso_SONumber', 'bbso_SODate', 'bbso_SOValue', 'bbso_validDate', 'bbso_InvNumber', 'bbso_InvDate', 'delayDay'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        creditAgeLastParameters = options.params;
                    }
                }
            });
            return creditAgeReportStore;
        };

        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'b2b_Customer_Name'

                        }, {
                            type: 'string',
                            dataIndex: 'bbso_InvNumber'

                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var CreditAgeReport_store = getCreditAgeReportStore();
        var grid = new Ext.grid.GridPanel({
            store: CreditAgeReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'creditage_report_grid',
            columns: [new Ext.grid.RowNumberer(), {
                    header: 'B2B Client Name  ',
                    sortable: true,
                    dataIndex: 'b2b_Customer_Name'
                }, {
                    header: 'Invoice Number ',
                    sortable: true,
                    dataIndex: 'bbso_InvNumber'
                }, {
                    header: 'Invoice Date',
                    sortable: true,
                    dataIndex: 'bbso_InvDate'
                }, {
                    header: 'Invoice Amount',
                    sortable: true,
                    dataIndex: 'bbso_SOValue'
                }, {
                    header: 'Pay By Date',
                    sortable: true,
                    dataIndex: 'bbso_validDate'
                }, {
                    header: 'Delay By',
                    sortable: true,
                    dataIndex: 'delayDay'
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
                        if (CreditAgeReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(supplierLastParameters);
                        for (var i = 0; i < Ext.getCmp('creditage_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('creditage_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('creditage_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('creditage_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=CreditAgeReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: CreditAgeReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'cust_name_auto_exp'

        });

        return grid;
    };
    var getSupplierProductReportPanel = function (panelId) {
        var supplierProductReportGrid = getSupplierProductReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'Supplier Product Report',
            items: [supplierProductReportGrid]
        });
        return panel;
    };
    var getSupplierProductReportGrid = function () {
        var getSupplierProductReportStore = function () {
            var SupplierProductReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getSupplierProductsReportGridData',
                fields: ['stpi_id', 'stit_type', 'itemType', 'itemId', 'itemName', 'supplier'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        supplierLastParameters = options.params;

                    }
                }

            });
            return SupplierProductReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'supplier'
                        }, {
                            type: 'string',
                            dataIndex: 'itemName'
                        }, {
                            type: 'string',
                            dataIndex: 'itemType'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var SupplierProductReport_store = getSupplierProductReportStore();
        var grid = new Ext.grid.GridPanel({
            store: SupplierProductReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'supplier_prooduct_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'supplier',
                },
                {
                    header: 'Product',
                    sortable: true,
                    dataIndex: 'itemName',
                    width: 175
                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'itemType',
                    width: 175
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
                        if (SupplierProductReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(supplierLastParameters);
                        for (var i = 0; i < Ext.getCmp('supplier_prooduct_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('supplier_prooduct_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('supplier_prooduct_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('supplier_prooduct_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=SupplierProductReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: SupplierProductReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'auto_exp_thesis_title'

        });

        return grid;
    };
    var getB2BCustomerReportPanel = function (panelId) {
        var customerB2BReportGrid = getB2BCustomerReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'B2B Customer Report',
            items: [customerB2BReportGrid]
        });
        return panel;
    };
    var getB2BCustomerReportGrid = function () {



        var getB2BCustomerReportStore = function () {
            var B2BCusomerReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getB2BCustomerReportGridData',
                fields: ['b2b_Customer_ID', 'b2b_Customer_Name', 'b2b_Customer_District', 'b2b_Customer_State',
                    'b2b_Customer_Address', 'b2b_Customer_Mobile', 'b2b_Customer_Email', 'b2b_Customer_Phone',
                    'b2b_Customer_Incharge', 'b2b_Customer_status', 'company', 'branch_shortname', 'comp_id',
                    'b2b_Customer_pincode', 'b2b_Customer_Lat', 'b2b_Customer_Lng', 'rbsch_name','br_Name','b2b_Customer_gst','b2b_Customer_dlno1','b2b_Customer_dlno2','b2b_Customer_fssaino'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    load: function (a, b, options) {
                        customerLastParameters = options.params;

                    }
                }

            });
            return B2BCusomerReportStore;
        };


        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'b2b_Customer_Name'
                        },{
                            type: 'string',
                            dataIndex: 'br_Name'
                        }, {
                            type: 'string',
                            dataIndex: 'branch_shortname'
                        }, {
                            type: 'string',
                            dataIndex: 'b2b_Customer_District'
                        }, {
                            type: 'string',
                            dataIndex: 'b2b_Customer_State'
                        }, {
                            type: 'string',
                            dataIndex: 'b2b_Customer_Mobile'
                        }, {
                            type: 'string',
                            dataIndex: 'b2b_Customer_Email'
                        }, {
                            type: 'string',
                            dataIndex: 'b2b_Customer_Phone'
                        }, {
                            type: 'string',
                            dataIndex: 'b2b_Customer_Incharge'
                        }, {
                            type: 'string',
                            dataIndex: 'company'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;

        var B2BCustomerReport_store = getB2BCustomerReportStore();
        var grid = new Ext.grid.GridPanel({
            store: B2BCustomerReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'b2bcustomer_report_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_Name',
                    tooltip: 'Name'
                },{
                    header: 'Branch',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch'
                },
                {
                    header: 'Contact Person',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_Incharge',
                    tooltip: 'Contact Person'
                },
                {
                    header: 'Email',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_Email',
                    tooltip: 'Email'
                },
                {
                    header: 'Contact No.',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_Phone',
                    tooltip: 'Contact No.'
                },
                {
                    header: 'Mobile',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_Mobile',
                    tooltip: 'Mobile'
                },{
                    header: 'GST / VAT No.',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_gst',
                    tooltip: 'GST / VAT No.'
                },{
                    header: 'Reg Number 1',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_dlno1',
                    tooltip: 'Reg Number 1'
                },{
                    header: 'Reg Number 2',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_dlno2',
                    tooltip: 'Reg Number 2'
                },{
                    header: 'FSSAI / EORI No.',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'b2b_Customer_fssaino',
                    tooltip: 'FSSAI / EORI No.'
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
                        if (CustomerReport_store.getTotalCount() <= 0) {
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
                        var filterData = Ext.encode(supplierLastParameters);
                        for (var i = 0; i < Ext.getCmp('b2bcustomer_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('b2bcustomer_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('b2bcustomer_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('b2bcustomer_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=B2BCustomerReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }


            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: B2BCustomerReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'cust_name_auto_exp'

        });

        return grid;
    };
    return {
        supplierReport: function ()
        {
            var panelId = 'supplierReportPanelID';
            var supplierReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(supplierReportPanel)) {
                supplierReportPanel = getSupplierReportPanel(panelId);
                Application.UI.addTab(supplierReportPanel);
                supplierReportPanel.doLayout();
            } else {
                Application.UI.addTab(supplierReportPanel);
            }
        }, customerReport: function ()
        {
            var panelId = 'customerReportPanelID';
            var customerReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(customerReportPanel)) {
                customerReportPanel = getCustomerReportPanel(panelId);
                Application.UI.addTab(customerReportPanel);
                customerReportPanel.doLayout();
            } else {
                Application.UI.addTab(customerReportPanel);
            }
        }, deliveryResourceReport: function ()
        {
            var panelId = 'deliveryResourceReportPanelID';
            var deliveryResourceReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(deliveryResourceReportPanel)) {
                deliveryResourceReportPanel = getDeliveryResourceReportPanel(panelId);
                Application.UI.addTab(deliveryResourceReportPanel);
                deliveryResourceReportPanel.doLayout();
            } else {
                Application.UI.addTab(deliveryResourceReportPanel);
            }
        }, creditAgeReport: function ()
        {
            var panelId = 'creditAgeReportPanelID';
            var creditAgeReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(creditAgeReportPanel)) {
                creditAgeReportPanel = getCreditAgeReportPanel(panelId);
                Application.UI.addTab(creditAgeReportPanel);
                creditAgeReportPanel.doLayout();
            } else {
                Application.UI.addTab(creditAgeReportPanel);
            }
        }, supplierProductReport: function ()
        {
            var panelId = 'supplierProductReportPanelID';
            var supplierProductReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(supplierProductReportPanel)) {
                supplierProductReportPanel = getSupplierProductReportPanel(panelId);
                Application.UI.addTab(supplierProductReportPanel);
                supplierProductReportPanel.doLayout();
            } else {
                Application.UI.addTab(supplierProductReportPanel);
            }
        }, B2BCustomerReport: function ()
        {
            var panelId = 'B2BCustomerReportPanelID';
            var B2BCustomerReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(B2BCustomerReportPanel)) {
                B2BCustomerReportPanel = getB2BCustomerReportPanel(panelId);
                Application.UI.addTab(B2BCustomerReportPanel);
                B2BCustomerReportPanel.doLayout();
            } else {
                Application.UI.addTab(B2BCustomerReportPanel);
            }
        }
    }
}();