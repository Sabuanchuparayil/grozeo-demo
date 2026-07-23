Application.RetalineHoldOrders = function () {
    var winLoadMask;
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 18;
    var modURL = '?module=retaline_hold_orders';
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var holdPrescriptionPanel = function (id, _type) {
        var src = '?module=retaline_hold_orders';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Orders on Hold',
            items: [manageHoldOrderGrid(),
                new Ext.Panel({
                    region: 'east',
                    frame: false,
                    border: true,
                    layout: 'fit',
                    autoScroll: false,
                    title: 'Order Details',
                    bodyStyle: {"background-color": "white"},
                    id: 'retholdorders_manage_parent_panel',
                    width: winsize.width * 0.45,
                    items: [{
                            id: 'retalineorder_hold_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_retalinehold_orderdtls" name="iframe_retalinehold_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };
    var manageHoldOrderGrid = function () {

        var hfilters = new Ext.ux.grid.GridFilters({
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
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = manageHoldOrderStore();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            region: 'center',
            frame: true,
            layout: 'fit',
            width: winsize.width * 0.6,
            border: false,
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
                getRowClass: function (record, index, params, store) {
                    if ((record.get('order_ReturnVerified') == 0) && (record.get('order_HasReturn') == 1))
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                }
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters],
            id: 'manage_order_retalinehold_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: qtipRenderer
                }, {
                    header: 'Time',
                    sortable: true,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                    renderer: qtipRenderer
                }, {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Vendor',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                },
                {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                }, {
                    header: 'Total Items',
                    sortable: true,
                    dataIndex: 'itemCount',
                    tooltip: 'Total Items',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Items Short',
                    sortable: true,
                    dataIndex: 'itemShortCount',
                    tooltip: 'Items Short',
                    hidden: true,
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Order Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'poNumber',
                    tooltip: 'PO Number',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            productActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: ordergridSelectionChanged
                }
            }), tbar: [],
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                }
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: order_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true
        });
        return grid;
    };
    var ordergridSelectionChanged = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.RetalineHoldOrders.Cache.orderAutoId = ID;
            Application.RetalineHoldOrders.UpdateViewMode(ID);
        }
    };
    var qtipRenderer = function (value, metadata, record, rowIndex, colIndex, store) {
        metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
        return value;
    };
    var manageHoldOrderStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listHoldOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on', 'itemCount', 'order_methodName',
                'quor_Type', 'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time',
                'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'quor_DeliveryMethodsAllowed', 'order_method', 'prescStatusId', 'poNumber', 'itemShortCount'
            ]),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            //    remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function () {

                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_retalinehold_grid').getView().refresh();
                    Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.RetalineHoldOrders.Cache.orderAutoId = ID;
                        Application.RetalineHoldOrders.UpdateViewMode(Application.RetalineHoldOrders.Cache.orderAutoId);
                    }
                }
            }
        });
        return store;
    };
    var productActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Remove Hold",
                handler: function () {
                    var order_id = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
                    Ext.Ajax.request({
                        url: modURL + '&op=removeHold',
                        method: 'POST',
                        params: {order_id: order_id},
                        success: function (res) {
                            var res = Ext.decode(response.responseText);
                            if (res.success === true)
                            {
                                Ext.MessageBox.alert('Success', res.msg, function (btn) {
                                    Ext.getCmp('manage_order_retalinehold_grid').getStore().load();
                                });
                            } else
                            {
                                Ext.MessageBox.alert('Failed');
                            }
                        },
                        failure: function () {
                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                        }
                    });
                }
            }, {
                text: "Manually Initiate",
                handler: function () {
                    var order_id = Ext.getCmp('manage_order_retalinehold_grid').getSelectionModel().getSelections()[0].data.order_id;
                    Ext.Ajax.request({
                        url: modURL + '&op=mauallyInitiatePO',
                        method: 'POST',
                        params: {order_id: order_id},
                        success: function (res) {
                            var res = Ext.decode(response.responseText);
                            if (res.success === true)
                            {
                                Ext.MessageBox.alert('Success', res.msg, function (btn) {
                                    Ext.getCmp('manage_order_retalinehold_grid').getStore().load();
                                });
                            } else
                            {
                                Ext.MessageBox.alert('Failed');
                            }
                        },
                        failure: function () {
                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                        }
                    });
                }
            }]
    });
    var scheduledPackPanel = function (id, ind, title) {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: title,
            id: id,
            iconCls: 'scheduled',
            buttonAlign: "right",
            items: [scheduledPackGrid(ind),
                new Ext.Panel({
                    title: 'Order Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelScheduledPackViewDetails',
                    height: winsize.height * 0.6,
                    items: [
                        ScheduledPackDetailsView()
                    ],
                    fbar: []
                })],
            listeners: {
                afterrender: function () {

                    if (_SESSION.typId == 3 || _SESSION.typId == 4) {
                        Ext.getCmp('search_branch' + ind).setValue(_SESSION.typdetsid);
                        Ext.getCmp('search_branch' + ind).hide();
                        Ext.getCmp('branch_label' + ind).hide();
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = _SESSION.typdetsid;
                    }

                    if (ind == 2) {
                        Ext.getCmp('save_schedule' + ind).hide();
                    }
                }
            }
        });
        return panel;
    };

    var scheduledPackGrid = function (ind) {
        var chk_model = check_model();
        var qugeo_store = scheduledPackStore(ind);
        var scheduledPackFilter = new Ext.ux.grid.GridFilters({
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
        scheduledPackFilter.remote = true;
        scheduledPackFilter.autoReload = true;
        var qugeo_grid = new Ext.grid.GridPanel({
            store: qugeo_store,
            region: 'center',
            height: winsize.height * 0.6,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('br_parentPacking') == 1)/*Nothing done */
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [new Ext.ux.grid.GroupSummary(), scheduledPackFilter],
            id: 'schedule_grid_' + ind,
            columns: [chk_model, {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: qtipRenderer
                }, {
                    header: 'Time',
                    sortable: true,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                    renderer: qtipRenderer
                }, {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Vendor',
                    width: 200,
                    hideable: false,
                    hidden: true,
                    renderer: qtipRenderer
                },
                {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 200,
                    hideable: false,
                    hidden: true,
                    renderer: qtipRenderer
                }, {
                    header: 'Total Items',
                    sortable: true,
                    dataIndex: 'itemCount',
                    tooltip: 'Total Items',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Items Short',
                    sortable: true,
                    dataIndex: 'itemShortCount',
                    tooltip: 'Items Short',
                    width: 200,
                    hidden: true,
                    renderer: qtipRenderer
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Order Status',
                    width: 200,
                    renderer: qtipRenderer
                }
            ],
            sm: chk_model,
            listeners: {
                beforeselect: function (sm, rowIndex, record) {
                    if (record.get('br_parentPacking') == 1)/*Nothing done */
                    {
                        return false;
                    }
                },
                //resize: updatePagination,
                celldblClick: function (grid, rowIndex, columnIndex, e) {
                }
            },
            tbar: [{html: 'Branch : &nbsp;', id: 'branch_label' + ind}, {
                    xtype: 'combo',
                    mode: 'remote',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstore(ind),
                    id: 'search_branch' + ind,
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'br_id',
                    name: 'br_id',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('schedule_grid_' + ind).getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    id: 'id_show' + ind,
                    handler: function () {
                        showData(ind);
                    }
                }], bbar: [{
                    text: "Create Stock Request",
                    id: "schPackStockRequest",
                    iconCls: 'list_users',
                    handler: function () {
                        var selectitem = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var record = [];
                        for (var i = 0; i < selectedcount; i++)
                        {
                            record.push(selectitem[i].data);
                        }
                        Application.RetalineHoldOrders.addToStockRequest(record, ind);
                    }
                }],
            stripeRows: true
        });
        return qugeo_grid;
    };
    var ScheduledPackDetailsView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '';
        //var src = '?module=cpd_dispatch&op=dispatchdetailsView&quor_id=' + Application.CPD_Dispatch.Cache.quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'details_view_panel_scheduledPack',
            region: 'center',
            width: winsize.width * 0.4,
            items: [{
                    region: 'center',
                    defaults: {
                        frame: false
                    },
                    html: '<iframe id="iframe_order_schpackdtls" name="iframe_order_schpackdtls" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>'
                }
            ]
        });
        return contactsPanel;
    };

    var scheduledPackStore = function (ind) {
        var qugeo_store = new Ext.data.JsonStore({
            url: modURL + '&op=listDarkStoreJobs',
            fields: ['order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on', 'itemCount', 'order_methodName',
                'quor_Type', 'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time',
                'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'quor_DeliveryMethodsAllowed', 'order_method', 'prescStatusId', 'poNumber', 'itemShortCount', 'br_parentPacking'
            ],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp('schedule_grid_schPack').getView().refresh();
                    Ext.getCmp('schedule_grid_schPack').getSelectionModel().selectRow(0);
                },
                beforeload: function () {
                    this.baseParams.ind = ind;
                    this.baseParams.br_id = Ext.getCmp('search_branch' + ind).getValue();
                }
            }
        });
        return qugeo_store;
    };
    function qugeoSelect() {
        return  new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
                selectionchange: gridSelectionChanged
            }
        });
    }
    var gridSelectionChanged = function () {
        if (!Ext.isEmpty(Ext.getCmp('schedule_grid_schPack').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('schedule_grid_schPack').getSelectionModel().getSelections()[0].data.order_id;
            Application.RetalineHoldOrders.Cache.quor_id = ID;
            Application.RetalineHoldOrders.ViewMode(ID);

        }
    };
    var branchstore = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getSatelliteBranch',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                    Ext.getCmp('search_branch' + ind).setValue(_SESSION.finascop_current_branch_id);
                    showData(ind);
                }
            }
            //totalProperty: 'totalCount'
        });
        return store;
    };
    var showData = function (ind) {
        var branch = Ext.getCmp('search_branch' + ind).getValue();

        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.pending = 0;
        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.delivered = 0;
        Ext.getCmp('schedule_grid_' + ind).getStore().load();

    };
    var checkInArray = function (arr, check) {

        var found = false;
        for (var i = 0; i < check.length; i++) {
            if (arr.indexOf(check[i]) > -1) {
                found = true;
                break;
            }
        }
        return found;
    };
    var check_model = function (vid) {
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            dataIndex: 'checked',
            checkOnly: true,
            listeners: {
                selectionchange: function (selModel) {
                    var sel_data = selModel.getSelections();
                    var st = Ext.pluck(sel_data, 'data');
                    if (st.length > 0) {
                        var ID = Ext.getCmp('schedule_grid_schPack').getSelectionModel().getSelections()[0].data.order_id;
                        var status = Ext.getCmp('schedule_grid_schPack').getSelectionModel().getSelections()[0].data.status;

                        Application.RetalineHoldOrders.Cache.quor_id = ID;
                        Application.RetalineHoldOrders.ViewMode(ID);
                        console.log('Application.RetalineHoldOrders.Cache.vphbutton', Application.RetalineHoldOrders.Cache.vphbutton);
                        var taskStatus = Ext.unique(Ext.pluck(st, 'status'));
                        var isParentPack = Ext.unique(Ext.pluck(st, 'br_parentPacking'));
                        var toreqStatus = ['40'];
                        var noParentPack = ['0'];
                        var assignNomatches = checkInArray(taskStatus, toreqStatus);
                        var assignNoParentPack = checkInArray(isParentPack, noParentPack);
                        console.log(assignNoParentPack);
                        console.log(assignNomatches);
                        if ((taskStatus.length == 1) && (assignNomatches == true) && (assignNoParentPack == true)) {//|| assignNomatches == true
                            console.log(taskStatus.length);
                            Ext.getCmp('schPackStockRequest').show();
                        } else {
                            Ext.getCmp('schPackStockRequest').hide();
                        }

                    }
                },
                rowdeselect: function (sm, rowIndex, record) {
                    record.set('checked', 'false');
                },
                rowselect: function (sm, rowIndex, record) {
                    record.set('checked', 'true');
                }
            }
        });
    };
    return{
        Cache: {},
        initHoldOrders: function () {
            var _panelId = 'retaline_holdorder_panel';
            var _type = 'Hold';
            var _prescriptionHoldPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prescriptionHoldPanel)) {
                _prescriptionHoldPanel = holdPrescriptionPanel(_panelId, _type);
                Application.UI.addTab(_prescriptionHoldPanel);
                _prescriptionHoldPanel.doLayout();
            } else {
                Application.UI.addTab(_prescriptionHoldPanel);
                _prescriptionHoldPanel.doLayout();
            }
        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('retalineorder_hold_details_view_panel').show();

            Ext.get('iframe_retalinehold_orderdtls').dom.src = modURL + '&op=holdorder_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;

        }, addScheduledPack: function () {
            var main_panel_id = 'main_panelScheduledPack';
            var main_panel = Ext.getCmp(main_panel_id);
            var title = 'Packing On Hold';
            if (Ext.isEmpty(main_panel)) {
                main_panel = scheduledPackPanel(main_panel_id, 'schPack', title);
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
            } else {
                Application.UI.addTab(main_panel);
            }
        }, ViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('details_view_panel_scheduledPack').show();
            Ext.getCmp("panelScheduledPackViewDetails").doLayout();
            console.log('ViewMode');
            Ext.get('iframe_order_schpackdtls').dom.src = modURL + '&op=holdorder_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
        }, addToStockRequest: function (record, ind) {

            Ext.Msg.show({
                title: 'Saving!',
                msg: '',
                progressText: 'Please Wait...',
                width: 300,
                progress: true,
                closable: false,
                wait: true
            });
            var sel = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections()[0];
            var selectitem = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections();
            var selectedcount = selectitem.length;
            var gridData = [];
            for (var i = 0; i < selectedcount; i++)
            {
                gridData.push(selectitem[i].data);
        }

            if (!Ext.isEmpty(sel)) {
                Ext.Ajax.request({
                    url: modURL + '&op=pushToStockRequest',
                    method: 'POST',
                    params: {
                        gridData: Ext.encode(gridData),
                        satelliteBranch: Ext.getCmp('search_branch' + ind).getValue()

                    },
                    success: function (res) {

                        var tmp = Ext.decode(res.responseText);
                        if (tmp.success == true) {
                            Ext.Msg.hide();

                            var branch = Ext.getCmp('search_branch' + ind).getValue();

                            Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
                            Ext.getCmp('schedule_grid_' + ind).getStore().load();

                        } else {
                            Ext.Msg.hide();
                            Ext.MessageBox.alert('Notification', tmp.msg);
    }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', "Please select a booking and vehicle");
            }
        }
    }
}();