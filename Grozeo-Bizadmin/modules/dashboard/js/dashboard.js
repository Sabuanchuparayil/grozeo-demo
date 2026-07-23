Application.Dashboard = function () {
    var recs_per_page = 20
    var winsize = Ext.getBody().getViewSize()
    var modURL = '?module=dashboard';
    // var approvalStore="approvalStore"
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var CmpRendered = false;
    var loadingMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Please wait...Building components of Dashboard...'})
    var creatMainPanel = function (id) {
        return new Ext.Panel({
            border: false,
            region: 'center',
            hideBorders: true,
            iconCls: 'project_dashboard',
            title: 'Scheduler Details',
            layout: 'fit',
            autoScroll: true,
            bodyStyle: 'background-color:white;',
            // items: dashboard_items(),
            items: [schedulerGrid()],
            id: id,
            listeners: {
                "afterrender": function () {
                    setTimeout(function () {
                    }, 200);

                }
            }
        });
    };
    var schedulerStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            waitMsg: 'Please wait...',
            url: modURL + '&op=schedulerGridStore',
            method: 'post',
            fields: ['prlk_name', 'prlk_status', 'prlk_updtime', 'prlk_isenabled', 'prlk_email', 'prlk_Description', 'prlk_interval', 'minuteDiff'],
            totalProperty: 'totalCount',
            root: 'data',
            //remoteSort: true
        });
        return store;
    };
    var schedulerGrid = function () {
        var store = schedulerStore();
        var grid_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'prlk_name'
                }]
        });
        grid_filter.remote = true;
        grid_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: store,
            loadMask: true,
            region: 'center',
            frame: false,
            border: true,
            title: '',
            height: 200,
            autoScroll: true,
            stripeRows: true,
            plugins: [grid_filter],
            style: 'margin:5px',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('prlk_status') == 1 && record.get('minuteDiff') > 30)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('prlk_status') == 0 && record.get('minuteDiff') > 30)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            id: 'runningEventsForDashboard',
            columns: [
                {
                    header: 'Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_name',
                    tooltip: 'Name',
                }, {
                    header: 'Email',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_email',
                    tooltip: 'Email',
                    width: 100
                }, {
                    header: 'Description',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_Description',
                    tooltip: 'Description',
                    width: 100
                }, {
                    header: 'Updated Time',
                    sortable: true,
                    dataIndex: 'prlk_updtime',
                    tooltip: 'Updated Time',
                    width: 100
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: '',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var prlk_status = data.prlk_status;
                                var minuteDiff = data.minuteDiff;
                                if ((prlk_status == 1) && (minuteDiff > 30)) {
                                    this.items[0].tooltip = 'Reset';
                                    return 'assign';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var prlk_name = record.data.prlk_name;
                                Ext.Ajax.request({
                                    url: modURL + '&op=changeStatus',
                                    method: 'POST',
                                    params: {
                                        prlk_name: prlk_name
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            var tmp = Ext.decode(response.responseText);
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('runningEventsForDashboard').getStore().load();
                                        }
                                        else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            }
                        }]
                }],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            })
        });
        return grid_panel;
    };
    var currentStockStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=dlistCurrentStock',
                method: 'post'
            }),
            fields: ['blocked_count', 'cart_count', 'br_Name', 'stit_SKU', 'item_count', 'bom_offrPlacement', 'mrp', 'selling_price', 'optimumqty', 'minimumqty', 'maximumqty', 'stit_id', 'branchId',
                'rack_count', 'dispatch_count', 'receive_count', 'deliver_count', 'fsbg_name', 'fsbg_id', 'purchasing_unit', 'purchasing_unitname', 'least_package_type_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            sortInfo: {
                field: 'stit_id',
                direction: "ASC"
            },
            listeners: {
                load: function () {
                }
            }
        });
        return store;
    };
    var currentStock = function () {
        var current_stock_store = currentStockStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
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
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: current_stock_store,
            layout: 'fit',
            height: 400,
            autoScroll: true,
            frame: false,
            border: false,
            title: 'Todays Stock',
            plugins: [branch_filter],
            id: 'dashboard_current_stock',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    hidden: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Item SKU',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item SKU',
                    width: 200
                }, {
                    header: 'Receivable',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'receive_count',
                    tooltip: 'Receivable'
                }, {
                    header: 'Blocked',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'blocked_count',
                    tooltip: 'Customer checked out but not ordered'
                }, {
                    header: 'To Cart',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'cart_count',
                    tooltip: 'Customer orderd to be packed'
                }, {
                    header: 'To Deliver',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'deliver_count',
                    tooltip: 'To Deliver'
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
                    dataIndex: 'least_package_type_name',
                    tooltip: 'Unit'
                }, {
                    header: 'Rack Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'rack_count',
                    tooltip: 'Rack Count',
                    hidden: true
                }, {
                    header: 'To Dispatch',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'dispatch_count',
                    tooltip: 'To Dispatch'
                },
                {
                    header: 'MRP',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: 'MRP',
                    hidden: true
                },
                {
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Selling Price',
                    hidden: true
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                resize: updatePagination,
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: current_stock_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exp'
        });
        return grid_panel;
    };
    var transferRequestStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=dlistRetalineTransferRequest',
                method: 'post'
            }),
            fields: ['fsto_id', 'fsto_uid', 'fsto_source', 'fsto_destination', 'fsto_status', 'fsto_createdOn', 'sourcename', 'branch', 'status_name', 'fsto_type', 'initiatedBranch', 'fsto_initiatedBy'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                }
            }
        });
        store.setDefaultSort('fsto_createdOn', 'DESC');
        return store;
    };
    var branchTransfers = function () {
        var branch_store = transferRequestStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fsto_uid'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_createdOn'
                },
                {
                    type: 'string',
                    dataIndex: 'status_name'
                },
                {
                    type: 'string',
                    dataIndex: 'sourcename'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_type'
                }, {
                    type: 'string',
                    dataIndex: 'branch'
                }
            ]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            height: 400,
            autoScroll: true,
            frame: false,
            border: false,
            title: 'Pending Orders',
            plugins: [branch_filter],
            id: 'todaysBranchTransfer',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order No',
                    id: 'branch_name_auto_exptr',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fsto_uid',
                    tooltip: 'Order No'
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'fsto_createdOn',
                    tooltip: 'Created Date'
                },
                {
                    header: 'Source',
                    sortable: true,
                    dataIndex: 'sourcename',
                    tooltip: 'Source'
                },
                {
                    header: 'Destination',
                    sortable: true,
                    dataIndex: 'branch',
                    tooltip: 'Destination'
                }, {
                    header: 'Initiated Branch',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'initiatedBranch',
                    tooltip: 'Initiated Branch'
                },
                {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'fsto_type',
                    hidden: true,
                    tooltip: 'Type'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'status_name',
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {
                }
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branch_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exptr'
        });
        return grid_panel;
    };
    var transferReceivalStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=dlistRetalineTransferReceivals',
                method: 'post'
            }),
            fields: ['fsto_id', 'fsto_uid', 'fsto_source', 'fsto_destination', 'fsto_status', 'fsto_createdOn', 'sourcename', 'branch', 'status_name', 'fsto_type', 'initiatedBranch', 'fsto_initiatedBy'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                }
            }
        });
        store.setDefaultSort('fsto_createdOn', 'DESC');
        return store;
    };
    var branchReceivals = function () {

        var branch_store = transferReceivalStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fsto_uid'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_createdOn'
                },
                {
                    type: 'string',
                    dataIndex: 'status_name'
                },
                {
                    type: 'string',
                    dataIndex: 'sourcename'
                }, {
                    type: 'string',
                    dataIndex: 'fsto_type'
                }, {
                    type: 'string',
                    dataIndex: 'branch'
                }
            ]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            height: 400,
            autoScroll: true,
            frame: false,
            border: false,
            title: 'Todays Receivals',
            plugins: [branch_filter],
            id: 'todaysBranchReceivals',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order No',
                    id: 'branch_name_auto_exptr',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'fsto_uid',
                    tooltip: 'Order No'
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'fsto_createdOn',
                    tooltip: 'Created Date'
                },
                {
                    header: 'Source',
                    sortable: true,
                    dataIndex: 'sourcename',
                    tooltip: 'Source'
                },
                {
                    header: 'Destination',
                    sortable: true,
                    dataIndex: 'branch',
                    tooltip: 'Destination'
                }, {
                    header: 'Initiated Branch',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'initiatedBranch',
                    tooltip: 'Initiated Branch'
                },
                {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'fsto_type',
                    hidden: true,
                    tooltip: 'Type'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'status_name',
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {
                }
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branch_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exptr'
        });
        return grid_panel;

    };
    var PurchaseOrderGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=dgetPurchaseOrderData',
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
                }
            }
        });
        return _catStore;
    };
    var purchaseOrders = function () {
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
        var _gridStore = PurchaseOrderGridStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'dashboardPurchaseOrders',
            layout: 'fit',
            height: 400,
            autoScroll: true,
            frame: false,
            border: false,
            title: 'Todays Purchase',
            store: _gridStore,
            loadMask: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            tbar: [],
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
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
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
                },
                resize: updatePagination
            }

        });
        return _gridPanel;
    };
    var schedulerStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            waitMsg: 'Please wait...',
            url: modURL + '&op=schedulerGridStore',
            method: 'post',
            fields: ['prlk_name', 'prlk_status', 'prlk_updtime', 'prlk_isenabled', 'prlk_email', 'prlk_Description', 'prlk_interval', 'minuteDiff'],
            totalProperty: 'totalCount',
            root: 'data',
            //remoteSort: true
        });
        return store;
    };
    var schedulerGrid = function () {
        var store = schedulerStore();
        var grid_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'prlk_name'
                }]
        });
        grid_filter.remote = true;
        grid_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: store,
            loadMask: true,
            region: 'center',
            frame: false,
            border: true,
            title: 'Running Schedulers',
            height: 400,
            autoScroll: true,
            stripeRows: true,
            plugins: [grid_filter],
            style: 'margin:5px',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('prlk_status') == 1 && record.get('minuteDiff') > 30)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('prlk_status') == 0 && record.get('minuteDiff') > 30)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            id: 'runningEventsForDashboard',
            columns: [
                {
                    header: 'Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_name',
                    tooltip: 'Name',
                }, {
                    header: 'Email',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_email',
                    tooltip: 'Email',
                    width: 100
                }, {
                    header: 'Description',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_Description',
                    tooltip: 'Description',
                    width: 100
                }, {
                    header: 'Updated Time',
                    sortable: true,
                    dataIndex: 'prlk_updtime',
                    tooltip: 'Updated Time',
                    width: 100
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: '',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var prlk_status = data.prlk_status;
                                var minuteDiff = data.minuteDiff;
                                if ((prlk_status == 1) && (minuteDiff > 30)) {
                                    this.items[0].tooltip = 'Reset';
                                    return 'assign';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var prlk_name = record.data.prlk_name;
                                Ext.Ajax.request({
                                    url: modURL + '&op=changeStatus',
                                    method: 'POST',
                                    params: {
                                        prlk_name: prlk_name
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            var tmp = Ext.decode(response.responseText);
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('runningEventsForDashboard').getStore().load();
                                        }
                                        else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            }
                        }]
                }],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            })
        });
        return grid_panel;
    };

    return {
        loadCharts: function () {
            Ext.get('chartCombination').dom.src = modURL + '&op=chartCombination';

            Application.Dashboard.ViewMode();
        },
        ViewMode: function () {
            
            
                    },
        init: function () {
            Application.UI.switchMainView(new Ext.Panel({
                xtype: 'panel',
                border: false,
                hideBorders: true,
                iconCls: 'project_dashboard',
                title: 'Dashboard',
                layout: 'border',
                autoScroll: true,
                bodyStyle: 'background-color:white;',
                items: [{
                        region: 'center',
                        layout: 'column',
                        autoHeight: true,
                        border: false,
                        items: [/* <?php if (user_access("dashboard", "chartCombination")) { ?> */{
                                layout: 'form',
                                columnWidth: 1,
                                hideBorders: true,
                                items: [{
                                        height: 1300,
                                        border: false,
                                        collapseFirst: false,
                                        html: '<iframe style="border:none" scrolling="no" id="chartCombination" width="100%" height="100%" ></iframe>'
                                    }]
                            }, /* <?php } ?> */ /* <?php if (user_access("dashboard", "dlistCurrentStock")) { ?> */{
                                layout: 'form',
                                columnWidth: 0.5,
                                style: "padding: 10px;",
                                hideBorders: true,
                                items: currentStock()
                            }, /* <?php } ?> */
                            /* <?php if (user_access("dashboard", "dgetPurchaseOrderData")) { ?> */{
                                layout: 'form',
                                columnWidth: 0.5,
                                hidden:true,
                                style: "padding: 10px;",
                                hideBorders: true,
                                items: purchaseOrders()
                            }, /* <?php } ?> */
                             /* <?php if (user_access("dashboard", "dlistRetalineTransferRequest")) { ?> */{
                                layout: 'form',
                                columnWidth: 0.5,
                                style: "padding: 10px;",
                                hideBorders: true,
                                items: branchTransfers()
                            }, /* <?php } ?> */
                             /* <?php if (user_access("dashboard", "dlistRetalineTransferReceivals")) { ?> */ {
                                layout: 'form',
                                columnWidth: 0.5,
                                hideBorders: true,
                                hidden:true,
                                style: "padding: 10px;",
                                items: branchReceivals()
                            }, /* <?php } ?> */ /* <?php if (user_access("dashboard", "schedulerGridStore")) { ?> */ {
                                layout: 'form',
                                columnWidth: 1,
                                hideBorders: true,
                                items: schedulerGrid()
                            }, /* <?php } ?> */{
                                layout: 'form',
                                columnWidth: 1,
                                hideBorders: true,
                                items: [{
                                        layout: 'column',
                                        hideBorders: true,
                                        items: [
                                            {
                                                layout: 'form',
                                                columnWidth: 0.30,
                                                hideBorders: true,
                                                items: [{
                                                        height: 200,
                                                        border: false,
                                                        collapseFirst: false,
                                                        html: '<iframe style="border:none" scrolling="no" id="lineChartMonthlySales" width="100%" height="100%" ></iframe>'
                                                    }]
                                            }, {
                                                layout: 'form',
                                                columnWidth: 0.30,
                                                hideBorders: true,
                                                items: [{
                                                        height: 200,
                                                        border: false,
                                                        collapseFirst: false,
                                                        html: '<iframe style="border:none" scrolling="no" id="pieChartDailySales" width="100%" height="100%" ></iframe>'
                                                    }]
                                            }, {
                                                layout: 'form',
                                                columnWidth: 0.30,
                                                hideBorders: true,
                                                items: [{
                                                        height: 200,
                                                        border: false,
                                                        collapseFirst: false,
                                                        html: '<iframe style="border:none" scrolling="no" id="barGraphBranchSales" width="100%" height="100%" ></iframe>'
                                                    }]
                                            }, {}]
                                    }]
                            }]
                    }],
                id: 'chartsPanel',
                listeners: {
                    "afterrender": function () {
                        setTimeout(function () {
                            Application.Dashboard.loadCharts();
                        }, 200);

                    }
                }
            }));

        },
        CRMainPanel: function () {
            Application.UI.switchMainView(new Ext.Panel({
                id: 'dashboard-panel',
                title: 'Dashboard',
                xtype: 'portal',
                region: 'center',
                closable: true,
                border: false,
                forceFit: true,
                autoScroll: true,
                layout: 'column',
                items: [{
                        columnWidth: .5,
                        border: false,
                        style: 'margin:5px',
                        items: [creatMainPanel('proceeLockPanel')]
                    }],
                listeners: {
                    afterrender: function () {
                        setTimeout(function () {

                        }, 7000);
                    }
                }
            }));
        },
        proceessLock: function () {
            var panelId = 'proceeLockPanel';
            var panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(panel)) {
                panel = creatMainPanel(panelId);
                Application.UI.addTab(panel);
                //panel.doLayout();
            } else {
                Application.UI.addTab(panel);
                //panel.doLayout();
            }
            return panel;
        }, activeWidgets: {
            "widget_0": "on",
            "widget_1": "on",
            "widget_2": "on"

        },
        initGA: function () {
            var winHeight = winsize.height * .5;
            Ext.Ajax.request({
                waitMsg: 'Dashboard is loading..',
                url: modURL + '&op=generateToken',
                success: function (response, options) {
                    var result = Ext.decode(response.responseText);
                    console.log('result');
                    console.log(result);

                    if (result) {

                        (function (w, d, s, g, js, fs) {
                            g = w.gapi || (w.gapi = {});
                            g.analytics = {q: [], ready: function (f) {
                                    this.q.push(f);
                                }};
                            js = d.createElement(s);
                            fs = d.getElementsByTagName(s)[0];
                            js.src = 'https://apis.google.com/js/platform.js';
                            fs.parentNode.insertBefore(js, fs);
                            js.onload = function () {
                                g.load('analytics');
                            };
                        }(window, document, 'script'));

                        Application.UI.switchMainView(new Ext.Panel({
                            id: 'dashboard-panel',
                            title: 'Google Analytics Chart',
                            xtype: 'portal',
                            region: 'center',
                            collapsible: true,
                            border: false,
                            forceFit: true,
                            autoScroll: true,
                            layout: 'column',
                            items: [
                                {
                                    columnWidth: .33,
                                    border: false,
                                    style: 'margin:5px',
                                    items: [{
                                            title: "Visitors",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-1-container"></div>'
                                        }, {
                                            title: "Pageviews",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-2-container"></div>'
                                        }]
                                }, {
                                    columnWidth: .33,
                                    border: false,
                                    style: 'margin:5px',
                                    items: [{
                                            title: "Geolocations",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-3-container"></div>'
                                        }, {
                                            title: "Top Devices",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-5-container"></div>'
                                        }]
                                }, {
                                    columnWidth: .33,
                                    border: false,
                                    style: 'margin:5px',
                                    items: [{
                                            title: "Top Countries",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-4-container"></div>'
                                        }, {
                                            title: "Visitors",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-6-container"></div>'
                                        }]
                                }],
                            listeners: {
                                afterrender: function () {
                                    setTimeout(function () {
                                        if (Application.UI.loadingMaskDash())
                                            Application.UI.loadingMaskDash().hide();
                                    }, 7000);
                                }
                            }
                        }));

                        var tokenacces = result.access_token;
                        gapi.analytics.ready(function () {

                            gapi.analytics.auth.authorize({
                                'serverAuth': {
                                    'access_token': tokenacces

                                }
                            });



                            /**
                             * Authorize the user immediately if the user has already granted access.
                             * If no access has been created, render an authorize button inside the
                             * element with the ID "embed-api-auth-container".
                             */
                            /*gapi.analytics.auth.authorize({
                             container: 'embed-api-auth-container',
                             clientid: clientId
                             });*/


                            /**
                             * Create a new ViewSelector instance to be rendered inside of an
                             * element with the id "view-selector-container".
                             */
                            var dataChart1 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'start-date': '30daysAgo',
                                    'end-date': 'today',
                                    'metrics': 'ga:visitors',
                                    'dimensions': 'ga:date'
                                },
                                chart: {
                                    'container': 'chart-1-container',
                                    'type': 'LINE',
                                    hAxis: {title: 'Date'},
                                    vAxis: {title: 'Visits'},
                                    'options': {
                                        'width': '100%',
                                        'fontSize': 12,
                                        'title': 'Visitors in site'
                                    }
                                }
                            });
                            dataChart1.execute();


                            /**
                             * Creates a new DataChart instance showing top 5 most popular demos/tools
                             * amongst returning users only.
                             * It will be rendered inside an element with the id "chart-3-container".
                             */
                            var dataChart2 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'start-date': '30daysAgo',
                                    'end-date': 'today',
                                    'metrics': 'ga:pageviews',
                                    'dimensions': 'ga:pagePathLevel1',
                                    'sort': '-ga:pageviews',
                                    'filters': 'ga:pagePathLevel1!=/',
                                    'max-results': 20
                                },
                                chart: {
                                    'container': 'chart-2-container',
                                    'type': 'PIE',
                                    'title': 'Pageviews in site',
                                    'options': {
                                        'width': '100%',
                                        'pieHole': 4 / 9,
                                    }
                                }
                            });
                            dataChart2.execute();

                            /**/
                            var dataChart3 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'start-date': '30daysAgo',
                                    'end-date': 'today',
                                    'metrics': 'ga:visitors',
                                    'dimensions': 'ga:country'
                                },
                                chart: {
                                    'container': 'chart-3-container',
                                    'type': 'GEO',
                                    'title': 'Visitors in site',
                                    'options': {
                                        'width': '100%'
                                    }
                                }
                            });
                            dataChart3.execute();

                            /**/
                            var dataChart4 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'dimensions': 'ga:country',
                                    'metrics': 'ga:sessions',
                                    'sort': '-ga:sessions',
                                    'max-results': 5
                                },
                                chart: {
                                    'container': 'chart-4-container',
                                    'type': 'PIE',
                                    'title': 'Top Browsers',
                                    'options': {
                                        'width': '100%',
                                        'pieHole': 4 / 9
                                    }
                                }
                            });
                            dataChart4.execute();

                            /**/
                            var dataChart5 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'dimensions': 'ga:deviceCategory',
                                    'metrics': 'ga:sessions',
                                    'sort': '-ga:sessions',
                                    'max-results': 5
                                },
                                chart: {
                                    'container': 'chart-5-container',
                                    'type': 'BAR',
                                    'title': 'Top Devices',
                                    'options': {
                                        'width': '100%'
                                    }
                                }
                            });
                            dataChart5.execute();

                            /**/
                            var dataChart6 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'dimensions': 'ga:userType',
                                    'metrics': 'ga:users',
                                    'sort': '-ga:users',
                                    'max-results': 5
                                },
                                chart: {
                                    'container': 'chart-6-container',
                                    'type': 'PIE',
                                    'title': 'Top Devices',
                                    'options': {
                                        'width': '100%',
                                        'pieHole': 4 / 9
                                    }
                                }
                            });
                            dataChart6.execute();

                            var actuser = new gapi.client.analytics.data.realtime.get({
                                'ids': 'ga:183384251',
                                metrics: 'rt:activeUsers',
                                container: 'chart-7-container',
                                pollingInterval: 5
                            });
                            actuser.execute();
                        });
                    } else {
                        Ext.MessageBox.alert("Notification", "Token generation failed");
                    }
                }
            });
            // blocking for a production release


        }

    }
}();