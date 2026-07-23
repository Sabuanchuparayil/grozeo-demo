Application.DistributerTransferFiles = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 18;
    var modURL = '?module=mypha_distributor_transferlist';
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };


    var mapPanel = function (id, ind) {
        var centerParam = {
            lat: "8.52414",
            lng: "76.93664"
        };
        return {xtype: 'gmappanel',
            region: 'center',
            zoomLevel: 10,
            minGeoAccuracy: 4,
            gmapType: 'map',
            scaleControl: true,
            id: id,
            anchor: '99%',
            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
            mapControls: ['GSmallMapControl', 'GMapTypeControl'],
            setCenter: centerParam,
            /*  markers: markers,*/
            listeners: {
                afterrender: function () {
                    setTimeout(function () {
                        Ext.getCmp(id).clearMarkers();
                        if (id == 'max_map') {
                            Ext.getCmp('max_map').addMarkers(Application.Qugeo.Markers[ind], true);
                        }
                    }, 1000);
                }
            }
        };
    };
    var executiveStore = function (ind) {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=loadExecutive',
            fields: ['id', 'name', 'phone'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp("order_executives_gridDTF").getView().refresh();
                    Ext.getCmp("order_executives_gridDTF").getSelectionModel().selectRow(0);

                }
            }
        });
        return store;
    };
    var executive_select = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp("order_executives_gridDTF").getSelectionModel().getSelections())) {
            var executive_name = Ext.getCmp("order_executives_gridDTF").getSelectionModel().getSelections()[0].data.name;
            Ext.getCmp('ExecutiveDetailsDisplayFieldDTF').setValue("Order No:" + Application.DistributerTransferFiles.Cache.Order_ID + '|' + "Packing Boy:" + executive_name);
        }
    };
    var ExecutiveGrid = function (ind) {
        var store = executiveStore(ind);
        // var sm = new Ext.grid.RowSelectionModel({
        //     singleSelect: true
        // });
        var grid = new Ext.grid.GridPanel({
            store: store,
            height: 200,
            viewConfig: {
                forceFit: true
            },
            id: 'order_executives_gridDTF',
            columns: [
                {
                    header: 'Packing Executives',
                    dataIndex: 'name',
                    width: 100
                },
                {
                    header: 'Mobile No.',
                    dataIndex: 'phone'
                }

            ],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: executive_select
                }
            }),
            listeners: {
            },
            stripeRows: true
        });
        return grid;
    };
    var qugeoStore = function (ind) {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + "&op=listScheduleOrder",
                method: "post"
            }),
            reader: new Ext.data.JsonReader(
                    {
                        totalProperty: "totalCount",
                        idProperty: "order_id",
                        root: "data"
                    },
            [
                "order_id",
                "order_no",
                "bcor_createdon",
                "cpd_id",
                "branch_id",
                "order_status",
                "order_no_last_id",
                "cpd_Name",
                "branch_name"
            ]
                    ),
            sortInfo: {
                field: "order_id",
                direction: "DESC"
            },
            groupField: "",
            groupDir: "ASC",
            remoteSort: true,
            autoLoad: false,
            root: "data",
            listeners: {
                load: function () {
                    Ext.getCmp("outward_schedule_gridDTF").getView().refresh();
                    Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().selectRow(0);
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });

        return _store;
    };
    var scheduleGrid = function (ind) {
        var _scheduleGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'list',
                    options: ['Created', 'Manual Queued', 'Polled', 'Assigned', 'Scanning Started', 'Incomplete Order', 'Order Completed', 'Cancelled', 'Expired', 'Dispatched', 'Partly Received', 'Received'],
                    phpMode: true,
                    dataIndex: 'order_status'
                }
            ]
        });
        _scheduleGridFilter.remote = true;
        _scheduleGridFilter.autoReload = true;
        //var qugeo_selection = qugeo_select(ind);
        var qugeo_store = qugeoStore(ind);
        var qugeo_grid = new Ext.grid.GridPanel({
            store: qugeo_store,
            region: 'center',
            height: winsize.height * 0.6,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            id: 'outward_schedule_gridDTF',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText:
                        '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl:
                        '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _scheduleGridFilter],
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'Order No.',
                    sortable: false,
                    dataIndex: 'order_no'
                }, {
                    header: 'Order Date',
                    sortable: false,
                    dataIndex: 'bcor_createdon'
                }, {
                    header: 'Distributor',
                    sortable: false,
                    dataIndex: 'cpd_Name'
                },
                {
                    header: 'Retailer Name',
                    sortable: false,
                    dataIndex: 'branch_name'
                },
                {
                    header: 'Order Status',
                    sortable: false,
                    dataIndex: 'order_status'
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: 'my-icon96',
                            tooltip: 'View Item Details',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Application.CPD.viewItems(record.get('order_id'));
                            }
                        }, {
                            getClass: function (v, meta, rec) {
                                if ((rec.get('order_status') == 'Assigned') || (rec.get('order_status') == 'Scanning Started') || (rec.get('order_status') == 'Incomplete Order')) {
                                    this.items[1].tooltip = 'Revoke';
                                    return 'now_active';
                                }
                                else {
                                    return 'revoke';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.MessageBox.confirm('Confirm', 'Do you wish to revoke this?', function (btn, text) {
                                    if (btn == 'yes') {
                                        Application.CPD.revokeOrder(record.get('order_no'))
                                    }
                                });
                            }
                        }]
                }
            ],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: qugeo_select
                }
            }),
            tbar: [
                // {html: 'Branch : &nbsp;' },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditBranchDTF',
                    name: 'textboxBrmCpdDataeditBranchDTF',
                    anchor: '98%',
                    hidden: true,
                    tabIndex: 1,
                    maxLength: 20,
                    disabled: true
                },
                {
                    html: '&nbsp;Distributor : &nbsp;',
                    //id: 'order_branch_label' + ind
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditCPDDTF',
                    name: 'textboxBrmCpdDataeditCPDDTF',
                    tabIndex: 1,
                    maxLength: 20,
                    disabled: true
                },
                {
                    html: '&nbsp;&nbsp;&nbsp;&nbsp;',
                    //id: 'order_branch_label' + ind
                },
                {
                    xtype: 'button',
                    text: 'Invoke Transfer',
                    anchor: '95%',
                    iconCls: 'show',
                    style: "background-color: lightgrey; border-radius: 4px; padding: 1px 1px 1px 1px;",
                    id: 'invokeTransferinCPDDTF',
                    handler: function () {
                        setTimeout(function () {
                            Ext.Ajax.request({
                                waitMsg: 'Processing',
                                url: modURL,
                                params: {
                                    op: 'schedulerinvoke'
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    console.log('tmp', tmp);
                                    if (tmp.status == 'ok') {
                                        Application.example.msg('Success', tmp.msg);
                                    } else {
                                        Ext.Msg.alert("Error", tmp.error.msg);
                                    }
                                }
                            });
                        }, 100);

                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: qugeo_store,
                displayInfo: true,
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: "pdt_name_col",
            listeners: {
                afterrender: function () {
//                    if (_SESSION.current_branch_iscpd == 1) {
//                        Ext.getCmp('textboxBrmCpdDataeditCPDDTF').setValue(_SESSION.current_branch);
//                    } else {
                    //Ext.getCmp('textboxBrmCpdDataeditCPDDTF').setValue(_SESSION.current_branch_cpd);
                    Ext.getCmp('textboxBrmCpdDataeditCPDDTF').setValue(_SESSION.current_branch);
//                    }

                    Ext.getCmp('textboxBrmCpdDataeditBranchDTF').setValue(_SESSION.current_branch);
                    if (Ext.getCmp('textboxBrmCpdDataeditBranchDTF').getValue() != '' && Ext.getCmp('textboxBrmCpdDataeditCPDDTF') != '')
                    {
                        qugeo_store.baseParams = {
                            cpd: Ext.getCmp('textboxBrmCpdDataeditCPDDTF').getValue(),
                            br_id: Ext.getCmp('textboxBrmCpdDataeditBranchDTF').getValue(),
                            cpd_id: _SESSION.current_branch_cpdId,
                            current_branch_id: _SESSION.finascop_current_branch_id
                        }
                        qugeo_store.load();
                    }
                },
                resize: onGridResize
            }
        });
        return qugeo_grid;
    };
    var schedulePanel = function (id, ind, title) {
        //console.log("ind",ind);
        var panel = new Ext.Panel({
            frame: false,
            id: id,
            border: false,
            layout: 'border',
            title: title,
            //iconCls: 'my-icon146',
            autoScroll: false,
            items: [scheduleGrid(ind),
                new Ext.Panel({
                    region: 'east',
                    border: true,
                    split: true,
                    width: 600,
                    tools: [{
                            id: 'order_maximizeDTF',
                            handler: function () {
                                // showMap(ind);
                            }
                        }],
                    layout: 'border',
                    items: [mapPanel('location_mapDTF' + ind, ind),
                        new Ext.TabPanel({
                            border: false,
                            activeTab: 0,
                            region: 'south',
                            height: 200,
                            deferredRender: false,
                            id: 'order_vehicle_tab' + ind,
                            items: [{
                                    title: 'Live Executives',
                                    layout: 'fit',
                                    items: ExecutiveGrid(ind)
                                }
                            ],
                            bbar: [{
                                    xtype: 'hidden',
                                    id: 'order_hdnExecutiveIdDTF' + ind
                                }, {
                                    xtype: 'displayfield',
                                    id: 'order_booking_dispDTF' + ind
                                }, {
                                    xtype: 'displayfield', value: '|', style: 'margin:0 8px 0 8px;', id: 'ExecutiveDetailsDisplayFieldDTF'
                                }, {
                                    xtype: 'displayfield',
                                    id: 'order_vehicle_dispDTF' + ind
                                }, '->',
                                {
                                    text: 'Assign',
                                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                    iconCls: 'my-icon1',
                                    id: 'order_save_scheduleDTF',
                                    handler: function () {
                                        var executive_id = Ext.getCmp("order_executives_gridDTF").getSelectionModel().getSelections()[0].data.id;
                                        var order_no = Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().getSelections()[0].data.order_no;
                                        var branch_ID = Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().getSelections()[0].data.cpd_id;

                                        if (executive_id != '')
                                        {
                                            Ext.Ajax.request({
                                                url: modURL + '&op=assignExecutive',
                                                method: 'POST',
                                                params: {
                                                    id: executive_id,
                                                    order_ID: order_no,
                                                    br_ID: branch_ID

                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    console.log('tmp', tmp);
                                                    if (tmp.status == 'ok') {
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp("order_executives_gridDTF").getStore().load({
                                                            params: {
                                                                cpd_ID: branch_ID
                                                            }
                                                        });
                                                        Ext.getCmp("outward_schedule_gridDTF").getStore().load();
                                                    } else {
                                                        Ext.Msg.alert("Error", tmp.error.msg);
                                                    }

                                                },
                                                failure: function (response) {
                                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                                    Ext.MessageBox.alert('Error', tmp.message);
                                                }
                                            });
                                        }
                                        else
                                        {
                                            Ext.MessageBox.alert("Notification", 'Nobody to Assign!!!');
                                        }

                                    }
                                }
                            ]
                        })
                    ]
                })
            ],
            listeners: {
            }
        });
        return panel;
    };
    var qugeo_select = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().getSelections())) {
            var cpd_ID = Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().getSelections()[0].data.cpd_id;
            var ORDER_ID = Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().getSelections()[0].data.order_no;
            var ORDER_STATUS = Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().getSelections()[0].data.order_status;
            Application.DistributerTransferFiles.Cache.Order_ID = ORDER_ID;
            if (ORDER_STATUS != "Manual Queued") {
                Ext.getCmp('order_save_scheduleDTF').hide();
            }
            else {
                Ext.getCmp('order_save_scheduleDTF').show();
            }
            //var branch_ID = Ext.getCmp("outward_schedule_gridDTF").getSelectionModel().getSelections()[0].data.branch_id;
            Ext.getCmp('order_executives_gridDTF').getStore().load({
                params: {
                    cpd_ID: cpd_ID,
                    //branch_ID:branch_ID
                }
            });
            //Application.Idrl_Stories.ViewMode(ID);
        }
    };

    var distributerOrderListexecutiveStore = function (ind) {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=loadExecutive',
            fields: ['id', 'name', 'phone'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp("order_executives_gridDOL" + ind).getView().refresh();
                    Ext.getCmp("order_executives_gridDOL" + ind).getSelectionModel().selectRow(0);

                }
            }
        });
        return store;
    };
    var distributerOrderListexecutive_select = function (sm) {
        var ind = Application.DistributerTransferFiles.Cache.ind;
        if (!Ext.isEmpty(Ext.getCmp("order_executives_gridDOL" + ind).getSelectionModel().getSelections())) {
            var executive_name = Ext.getCmp("order_executives_gridDOL" + ind).getSelectionModel().getSelections()[0].data.name;
            Ext.getCmp('ExecutiveDetailsDisplayFieldDOL' + ind).setValue("Order No:" + Application.DistributerTransferFiles.Cache.Order_ID + '|' + "Packing Boy:" + executive_name);
        }
    };
    var distributerOrderListExecutiveGrid = function (ind) {
        var store = distributerOrderListexecutiveStore(ind);
        var grid = new Ext.grid.GridPanel({
            store: store,
            height: 200,
            viewConfig: {
                forceFit: true
            },
            id: 'order_executives_gridDOL' + ind,
            columns: [
                {
                    header: 'Packing Executives',
                    dataIndex: 'name',
                    width: 100
                },
                {
                    header: 'Mobile No.',
                    dataIndex: 'phone'
                }

            ],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: distributerOrderListexecutive_select
                }
            }),
            listeners: {
            },
            stripeRows: true
        });
        return grid;
    };
    var distributerOrderListGridStore = function (ind) {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + "&op=listScheduleOrder",
                method: "post"
            }),
            reader: new Ext.data.JsonReader(
                    {
                        totalProperty: "totalCount",
                        idProperty: "order_id",
                        root: "data"
                    },
            [
                "order_id",
                "order_no",
                "bcor_createdon",
                "cpd_id",
                "branch_id",
                "order_status",
                "order_no_last_id",
                "cpd_Name",
                "branch_name"
            ]
                    ),
            sortInfo: {
                field: "order_id",
                direction: "DESC"
            },
            groupField: "",
            groupDir: "ASC",
            remoteSort: true,
            autoLoad: false,
            root: "data",
            listeners: {
                load: function () {
                    Ext.getCmp("outward_schedule_gridDOL" + ind).getView().refresh();
                    Ext.getCmp("outward_schedule_gridDOL" + ind).getSelectionModel().selectRow(0);
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });

        return _store;
    };
    var distributerOrderListGrid = function (ind) {
        var headrf, headrs;
        Application.DistributerTransferFiles.Cache.ind = ind;
        if (ind == 'CS') {
            headrf = 'Cental Store';
            headrs = 'Distributer';
        } else {
            headrf = 'Distributer';
            headrs = 'Retailer';
        }
        var _distributerOrderListGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'list',
                    options: ['Created', 'Manual Queued', 'Polled', 'Assigned', 'Scanning Started', 'Incomplete Order', 'Order Completed', 'Cancelled', 'Expired', 'Dispatched', 'Partly Received', 'Received'],
                    phpMode: true,
                    dataIndex: 'order_status'
                }
            ]
        });
        _distributerOrderListGridFilter.remote = true;
        _distributerOrderListGridFilter.autoReload = true;
        var qugeo_store = distributerOrderListGridStore(ind);
        var qugeo_grid = new Ext.grid.GridPanel({
            store: qugeo_store,
            region: 'center',
            height: winsize.height * 0.6,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            id: 'outward_schedule_gridDOL' + ind,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText:
                        '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl:
                        '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _distributerOrderListGridFilter],
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'Order No.',
                    sortable: false,
                    dataIndex: 'order_no'
                }, {
                    header: 'Order Date',
                    sortable: false,
                    dataIndex: 'bcor_createdon'
                }, {
                    header: headrf,
                    sortable: false,
                    dataIndex: 'cpd_Name'
                },
                {
                    header: headrs,
                    sortable: false,
                    dataIndex: 'branch_name'
                },
                {
                    header: 'Order Status',
                    sortable: false,
                    dataIndex: 'order_status'
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: 'my-icon96',
                            tooltip: 'View Item Details',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Application.CPD.viewItems(record.get('order_id'));
                            }
                        }, {
                            getClass: function (v, meta, rec) {
                                if ((rec.get('order_status') == 'Assigned') || (rec.get('order_status') == 'Scanning Started') || (rec.get('order_status') == 'Incomplete Order')) {
                                    this.items[1].tooltip = 'Revoke';
                                    return 'now_active';
                                }
                                else {
                                    return 'revoke';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.MessageBox.confirm('Confirm', 'Do you wish to revoke this?', function (btn, text) {
                                    if (btn == 'yes') {
                                        Application.DistributerTransferFiles.revokeOrder(record.get('order_no'))
                                    }
                                });
                            }
                        }]
                }
            ],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: orderListDistributerSelect
                }
            }),
            tbar: [
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditBranchDOL' + ind,
                    name: 'textboxBrmCpdDataeditBranchDOL',
                    anchor: '98%',
                    hidden: true,
                    tabIndex: 1,
                    maxLength: 20,
                    disabled: true
                },
                {
                    html: '&nbsp;' + headrf + ' : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditCPDDOL' + ind,
                    name: 'textboxBrmCpdDataeditCPDDOL',
                    tabIndex: 1,
                    maxLength: 20,
                    disabled: true
                },
                {
                    html: '&nbsp;&nbsp;&nbsp;&nbsp;',
                },
                {
                    xtype: 'button',
                    text: 'Create Orders',
                    anchor: '95%',
                    style: "background-color: lightgrey; border-radius: 4px; padding: 1px 1px 1px 1px;",
                    handler: function () {
                        Application.DistributerTransferFiles.addPurchaseInvoice(0);

                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: qugeo_store,
                displayInfo: true,
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: "pdt_name_col",
            listeners: {
                afterrender: function () {
//                    if (_SESSION.current_branch_iscpd == 1) {
//                        Ext.getCmp('textboxBrmCpdDataeditCPDDOL').setValue(_SESSION.current_branch);
//                    } else {
                    //Ext.getCmp('textboxBrmCpdDataeditCPDDOL').setValue(_SESSION.current_branch_cpd);
                    Ext.getCmp('textboxBrmCpdDataeditCPDDOL' + ind).setValue(_SESSION.current_branch);
//                    }

                    Ext.getCmp('textboxBrmCpdDataeditBranchDOL' + ind).setValue(_SESSION.current_branch);
                    if (Ext.getCmp('textboxBrmCpdDataeditBranchDOL' + ind).getValue() != '' && Ext.getCmp('textboxBrmCpdDataeditCPDDOL' + ind) != '')
                    {
                        qugeo_store.baseParams = {
                            cpd: Ext.getCmp('textboxBrmCpdDataeditCPDDOL' + ind).getValue(),
                            br_id: Ext.getCmp('textboxBrmCpdDataeditBranchDOL' + ind).getValue(),
                            cpd_id: _SESSION.current_branch_cpdId,
                            current_branch_id: _SESSION.finascop_current_branch_id
                        }
                        qugeo_store.load();
                    }
                },
                resize: onGridResize
            }
        });
        return qugeo_grid;
    };
    var distributerOrderListPanel = function (id, ind, title) {
        //console.log("ind",ind);
        var panel = new Ext.Panel({
            frame: false,
            id: id,
            border: false,
            layout: 'border',
            title: title,
            autoScroll: false,
            items: [distributerOrderListGrid(ind),
                new Ext.Panel({
                    region: 'east',
                    border: true,
                    split: true,
                    width: 600,
                    tools: [{
                            id: 'order_maximizeDOL' + ind,
                            handler: function () {
                                // showMap(ind);
                            }
                        }],
                    layout: 'border',
                    items: [mapPanel('location_mapDOL', ind),
                        new Ext.TabPanel({
                            border: false,
                            activeTab: 0,
                            region: 'south',
                            height: 200,
                            deferredRender: false,
                            id: 'order_vehicle_tabDOL',
                            items: [{
                                    title: 'Live Executives',
                                    layout: 'fit',
                                    items: distributerOrderListExecutiveGrid(ind)
                                }
                            ],
                            bbar: [{
                                    xtype: 'hidden',
                                    id: 'order_hdnExecutiveIdDOL' + ind
                                }, {
                                    xtype: 'displayfield',
                                    id: 'order_booking_dispDOL' + ind
                                }, {
                                    xtype: 'displayfield', value: '|', style: 'margin:0 8px 0 8px;', id: 'ExecutiveDetailsDisplayFieldDOL' + ind
                                }, {
                                    xtype: 'displayfield',
                                    id: 'order_vehicle_dispDOL'
                                }, '->',
                                {
                                    text: 'Assign',
                                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                    iconCls: 'my-icon1',
                                    id: 'order_save_scheduleDOL' + ind,
                                    handler: function () {
                                        var executive_id = Ext.getCmp("order_executives_gridDOL" + ind).getSelectionModel().getSelections()[0].data.id;
                                        var order_no = Ext.getCmp("outward_schedule_gridDOL" + ind).getSelectionModel().getSelections()[0].data.order_no;
                                        var branch_ID = Ext.getCmp("outward_schedule_gridDOL" + ind).getSelectionModel().getSelections()[0].data.cpd_id;

                                        if (executive_id != '')
                                        {
                                            Ext.MessageBox.alert("Notification", 'Packing Boy Assigned');
                                        }
                                        else
                                        {
                                            Ext.MessageBox.alert("Notification", 'Nobody to Assign!!!');
                                        }

                                    }
                                }
                            ]
                        })
                    ]
                })
            ],
            listeners: {
            }
        });
        return panel;
    };
    var orderListDistributerSelect = function (sm) {
        var ind = Application.DistributerTransferFiles.Cache.ind;
        if (!Ext.isEmpty(Ext.getCmp("outward_schedule_gridDOL" + ind).getSelectionModel().getSelections())) {
            var cpd_ID = Ext.getCmp("outward_schedule_gridDOL" + ind).getSelectionModel().getSelections()[0].data.cpd_id;
            var ORDER_ID = Ext.getCmp("outward_schedule_gridDOL" + ind).getSelectionModel().getSelections()[0].data.order_no;
            var ORDER_STATUS = Ext.getCmp("outward_schedule_gridDOL" + ind).getSelectionModel().getSelections()[0].data.order_status;
            Application.DistributerTransferFiles.Cache.Order_ID = ORDER_ID;
            if (ORDER_STATUS != "Manual Queued") {
                Ext.getCmp('order_save_scheduleDOL' + ind).hide();
            }
            else {
                Ext.getCmp('order_save_scheduleDOL' + ind).show();
            }
            Ext.getCmp('order_executives_gridDOL' + ind).getStore().load({
                params: {
                    cpd_ID: cpd_ID,
                }
            });
        }
    };

    var party_store = function () {
        var partyStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getPyramids',
            method: 'post',
            fields: ['br_ID', 'br_Name', 'br_PyramidLevel', 'br_id'],
            totalProperty: 'totalCount',
            autoLoad: true,
                    root: 'data'
        });
        return partyStore;
    };
    var viewPurchaseInvoice_panel = function () {
        var PartyStore = party_store();
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'purchase_invoice_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Distributor',
                                    id: 'pu_party',
                                    name: 'party',
                                    anchor: '99%',
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    hiddenName: 'br_Name',
                                    store: PartyStore,
                                    triggerAction: 'all',
                                    allowBlank: false,
                                    minChars: 1,
                                    lazyRender: true,
                                    labelStyle: mandatory_label,
                                    mode: 'local',
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        },
                                        select: function (combo, record, index) {
                                            excludeItemIds = [];
                                            if (record) {
                                                party_id = record.get('id');
                                                //party_state_id = record.get('party_state_id');
                                                //br_id = record.get('br_id');
                                                Ext.getCmp('pu_item_field').clearValue();
                                                Ext.getCmp('pu_rate_field').reset();
                                                Ext.getCmp('pu_quantity_field').reset();
                                                Ext.getCmp('pu_amount_field').reset();
                                                Ext.getCmp('pu_order_no').reset();
                                                Ext.getCmp('pu_order_date').reset();
                                                Ext.getCmp('pu_ref_no').reset();
                                                Ext.getCmp('pu_inv_date').reset();
                                                Ext.getCmp('pu_grossAmount').reset();
                                                //Ext.getCmp('pu_discount').reset();
                                                Ext.getCmp('pu_netAmount').reset();
                                                Ext.getCmp('pu_signature').reset();
                                                Ext.getCmp('pu_totalItems').reset();
                                                Ext.getCmp('pu_tax').reset();
                                                Ext.getCmp('pu_totalItemQty').reset();
                                                Ext.getCmp('pu_itemgrid').getStore().removeAll(true);
                                                Ext.getCmp('pu_itemgrid').getView().refresh();
                                                Ext.getCmp('pu_itemgridTotal').getStore().removeAll(true);
                                                Ext.getCmp('pu_itemgridTotal').getView().refresh();
                                            }
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.20,
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Order No',
                                    id: 'pu_order_no',
                                    name: 'purchcase_order_no',
                                    allowBlank: true,
                                    labelAlign: 'top',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_order_date').focus();
                                            }
                                        }
                                    }
                                }
                            ]

                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'datefield',
                                    fieldLabel: 'Order Date',
                                    id: 'pu_order_date',
                                    name: 'purchase_order_date',
                                    allowBlank: true,
                                    anchor: '99%',
                                    format: 'd/m/Y',
                                    maxValue: new Date(),
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_ref_no').focus();
                                            }
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Ref. No',
                                    allowBlank: true,
                                    id: 'pu_ref_no',
                                    name: 'ref_no',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_inv_date').focus();
                                            }
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'datefield',
                                    fieldLabel: ' Date',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    id: 'pu_inv_date',
                                    name: 'inv_date',
                                    anchor: '99%',
                                    format: 'd/m/Y',
                                    maxValue: new Date(),
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('pu_item_field').focus();
                                            }
                                        }
                                    }
                                }]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'invoice_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [invoiceItemGrid(), invoiceTotalGrid()]
                                }]
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            fieldLabel: 'Total Quantity ',
                            align: 'right',
                            id: 'pu_totalItemQty',
                            name: 'totalItemQty',
                            anchor: '99%',
                            hideLabel: true
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            fieldLabel: 'Tax ',
                            align: 'right',
                            id: 'pu_tax',
                            name: 'tax',
                            anchor: '99%',
                            hideLabel: true
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            fieldLabel: 'Total Items',
                            id: 'pu_totalItems',
                            name: 'totalItems',
                            anchor: '99%',
                            hideLabel: true
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right', 'margin-top': '4px'},
                                    fieldLabel: 'Gross Amount',
                                    id: 'pu_grossAmount',
                                    name: 'grossAmount',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    readOnly: true,
                                    anchor: '99%'
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    allowNegative: false,
                                    allowDecimals: true,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Net Amount',
                                    id: 'pu_netAmount',
                                    readOnly: true,
                                    name: 'netAmount',
                                    anchor: '99%'
                                }]
                        }]
                }]
        });
        return panel;
    };
    var invoiceItemGrid = function () {

        var PurchaseInvoiceItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                fields: [{name: 'item'},
                    {name: 'item_id'},
                    {name: 'mrp', type: 'float'},
                    {name: 'rate', type: 'float'},
                    {name: 'quantity'},
                    {name: 'hsn'},
                    {name: 'tax_percentage', type: 'float'},
                    {name: 'igst', type: 'float'},
                    {name: 'cgst', type: 'float'},
                    {name: 'sgst', type: 'float'},
                    {name: 'amtBfTax', type: 'float'},
                    {name: 'amtAfTax', type: 'float'},
                    {name: 'stockEnabled'}
                ],
                remoteSort: false,
                url: modURL + '&op=getInvoiceItems',
                method: 'post',
                totalProperty: 'totalCount',
                root: 'data',
                listeners: {
                    remove: function (obj, record, index) {
                        invoiceTotalCalculation();
                        Ext.getCmp('pu_item_field').focus();
                    }
                }
            });
            return itemStore;
        };
        //var itemStore = PurchaseInvoiceItemStore();


        var itemGridStore = PurchaseInvoiceItemStore();
        var item_store = itemComboStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'pu_itemgrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseInvoiceColModel(),
            tbar: [{
                    width: 170,
                    xtype: 'radiogroup',
                    id: 'radiobuttonFinascopStockId',
                    items: [
                        {boxLabel: 'Medicine', name: 'rb-auto', inputValue: 1, labelWidth: 120},
                        {boxLabel: 'Product', name: 'rb-auto', inputValue: 2, labelWidth: 120, checked: true}

                    ],
                    listeners: {
                        change: function (event, checked)
                        {
                            var current_firstid = event.items.items[0].inputValue;
                            var current_secondid = event.items.items[1].inputValue;
                            //var current_thirdid = event.items.items[2].inputValue;
                            var radioid = Ext.getCmp('radiobuttonFinascopStockId').getValue();

                            if (radioid == current_secondid)
                            {
                                var item_name = '';
                            } else if (radioid == current_firstid)
                            {
                                var item_name = '';
                            }
                            Ext.getCmp('pu_item_field').getStore().load({
                                params: {
                                    type: radioid
                                }
                            });

                        }
                    }
                },
                {html: '&nbsp;Item : &nbsp;'},
                {
                    xtype: 'combo',
                    anchor: '99%',
                    name: 'item_field',
                    id: 'pu_item_field',
                    width: 170,
                    store: item_store,
                    mode: 'remote',
                    hiddenName: 'item_field',
                    displayField: 'item_name',
                    valueField: 'item_id',
                    triggerAction: 'all',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    enableKeyEvents: true,
                    autoSelect: true,
                    submitValue: true,
                    alloWBlank: false,
                    maxChars: 50,
                    minChars: 2,
                    style: 'margin-bottom:3px;',
                    listeners: {
                        select: function (combo, record, index)
                        {
                            if (chkd == true) {
                                var party = Ext.getCmp('pu_party').getValue();
                                if (party == '')
                                {
                                    Ext.MessageBox.alert('Alert', "Please select the Party");
                                    Ext.getCmp('pu_item_field').clearValue();

                                }
                            }

                            Ext.getCmp('pu_rate_field').focus();
                        },
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                if (field.getValue() != '')
                                {
                                    Ext.getCmp('pu_item_field').lastQuery = null;
                                    Ext.getCmp('pu_rate_field').focus();

                                } else
                                {
                                    var gridItemCount = itemGridStore.getCount();

                                    if (gridItemCount > 0) {
                                        e.stopEvent();


                                        Ext.getCmp('pu_item_field').lastQuery = null;
                                        Ext.getCmp('pu_itemgrid').getSelectionModel().selectRow(0);
                                        Ext.getCmp('pu_itemgrid').getView().focusRow(0);

                                    } else
                                    {
                                        e.stopEvent();
                                        Ext.Msg.alert("Notification", "Please add any item", function (btn) {
                                            Ext.getCmp('pu_item_field').focus();
                                        });
                                    }


                                }
                            }
                        }
                    }
                },
                {html: '&nbsp; Quantity : &nbsp;'},
                {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    id: 'pu_quantity_field',
                    name: 'quantity_field',
                    allowNegative: false,
                    allowDecimals: false,
                    anchor: '97%',
                    width: 50,
                    enableKeyEvents: true,
                    style: 'margin-bottom:3px;',
                    listeners: {
                        keyup: function ()
                        {
                            var qty = Ext.getCmp('pu_quantity_field').getValue();
                            var rate = Ext.getCmp('pu_rate_field').getValue();
                            var amnt = Ext.util.Format.number((qty * rate), "0.00");
                            Ext.getCmp('pu_amount_field').setValue(amnt);
                        },
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {

                                var item = Ext.getCmp('pu_item_field').getValue();
                                if (item != "") {
                                    add_PInv_Items();

                                    Ext.getCmp('pu_item_field').focus();

                                } else {
                                    Ext.getCmp('pu_discount').focus();
                                }
                            }
                        }
                    }


                },
                {html: '&nbsp; Amount : &nbsp;'},
                {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    id: 'pu_amount_field',
                    name: 'amount_field',
                    anchor: '97%',
                    editable: false,
                    readOnly: true,
                    width: 50,
                    style: 'margin-bottom:3px;'
                }, '-',
                {
                    xtype: 'button',
                    text: 'Add',
                    id: 'pInv_add_btn',
                    iconCls: 'finascop_add',
                    style: 'margin-bottom:3px;',
                    handler: function () {

                        add_PInv_Items();
                    }

                }, '-'
            ],
            listeners: {
                keydown: function (e)
                {
                    if ((e.getCharCode() == 99) || (e.getCharCode() == 67)) {
                        var grid = Ext.getCmp('pu_itemgrid');
                        var selected = grid.getSelectionModel().getSelected();
                        var rowIndex = grid.getStore().indexOf(selected);
                        var record = grid.getStore().getAt(rowIndex);
                        removeFromItemStore(grid, rowIndex, record);
                        excludeItemIds.remove(record.get('item_id'));
                        e.stopEvent();
                        Ext.getCmp('pu_item_field').lastQuery = null;
                        Ext.getCmp('pu_item_field').reset();
                        Ext.getCmp('pu_item_field').focus();
                    } else if (e.getCharCode() == 13) {
                        e.stopEvent();
                        Ext.getCmp('pu_discount').focus();
                    }


                }
            },
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true
        });
        return itemgrid_panel;
    };
    var invoiceTotalGrid = function () {

        var TotalAmount_ItemStore = function () {
            var purchase_invoice_store = new Ext.data.JsonStore({
                method: 'post',
                fields: [{name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'},
                    {name: 'sgst', type: 'float'}, {name: 'amtBfTax', type: 'float'},
                    {name: 'amtAfTax', type: 'float'}, {name: 'hideCancelbtn', type: 'number'}]
            });
            return purchase_invoice_store;
        };
        var itemTotalGridStore = TotalAmount_ItemStore();
        var itemgridTotal_panel = new Ext.grid.GridPanel({
            store: itemTotalGridStore,
            layout: 'fit',
            frame: false,
            border: false,
            title: '',
            height: 40,
            id: 'pu_itemgridTotal',
            loadMask: true,
            hideHeaders: true,
            tdClass: 'true',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseInvoiceColModel(),
            stripeRows: true,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            }
        });
        return itemgridTotal_panel;
    };
    var itemComboStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            id: 'pu_item_store',
            url: modURL + '&op=getItems',
            method: 'post',
            fields: ['item_id', 'item_name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteFilter: true,
            listeners: {
                beforeload: function (st, opt) {
                    st.baseParams.type = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                }
            }
        });
        return store;
    };

    var purchaseInvoiceColModel = function ()
    {
        return new Ext.grid.ColumnModel([
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'item',
                tooltip: 'Item',
                width: 130

            },
            {
                header: 'MRP',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'mrp',
                align: 'right',
                tooltip: 'MRP',
                width: 60
            },
            {
                header: 'Quantity',
                sortable: true,
                dataIndex: 'quantity',
                xtype: 'numbercolumn',
                align: 'right',
                tooltip: 'Quantity',
                width: 60
            },
            {
                header: 'HSN',
                sortable: true,
                dataIndex: 'hsn',
                align: 'right',
                tooltip: 'HSN Code',
                width: 60
            },
            {
                header: 'Tax %',
                sortable: true,
                dataIndex: 'tax_percentage',
                tooltip: 'Tax Percentage',
                align: 'right',
                //xtype: 'numbercolumn',
                width: 60
            },
            {
                header: 'IGST',
                sortable: true,
                dataIndex: 'igst',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'IGST',
                width: 60
            },
            {
                header: 'CGST',
                sortable: true,
                dataIndex: 'cgst',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'CGST',
                width: 60
            },
            {
                header: 'SGST',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                sortable: true,
                dataIndex: 'sgst',
                align: 'right',
                tooltip: 'SGST',
                width: 60
            },
            {
                header: 'Amt Bf . Tax',
                sortable: true,
                dataIndex: 'amtBfTax',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Amount Before Tax',
                width: 100
            },
            {
                header: 'Amt Af. Tax',
                sortable: true,
                dataIndex: 'amtAfTax',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Amount After Tax',
                width: 100
            },
            {
                xtype: 'actioncolumn',
                hideable: false,
                id: 'pu_removeitem',
                width: 25,
                items: [{
                        sortable: false,
                        getClass: function (v, meta, rec) {
                            if (rec.get('hideCancelbtn') == 1)
                            {
                                return 'finascop_hideicon';
                            } else
                            {
                                return 'finascop_delete';
                            }
                        },
                        tooltip: 'Remove Item',
                        handler: function (grid, rowIndex, colIndex) {
                            var record = grid.store.getAt(rowIndex);
                            removeFromItemStore(grid, rowIndex, record);
                            excludeItemIds.remove(record.get('item_id'));
                            Ext.getCmp('pu_item_field').lastQuery = null;
                            Ext.getCmp('pu_item_field').reset();
                            Ext.getCmp('pu_item_field').focus();
                        }


                    }]
            }
        ]);
    }

    return{
        Cache: {},
        initTransfer: function (ind) {
            Application.DistributerTransferFiles.Markers = [];
            Application.DistributerTransferFiles.Markers[ind] = [];
            var main_panel_id = 'main_panelDTF' + ind;
            var main_panel = Ext.getCmp(main_panel_id);
            //var title = 'Assign Carting';
            var title = 'Transfer List';
            if (Ext.isEmpty(main_panel)) {
                main_panel = schedulePanel(main_panel_id, ind, title);
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
            } else {
                Application.UI.addTab(main_panel);
            }
        }, initOrder: function (ind) {
            Application.DistributerTransferFiles.Markers = [];
            Application.DistributerTransferFiles.Markers[ind] = [];
            var main_panel_id = 'main_panelDTF' + ind;
            var main_panel = Ext.getCmp(main_panel_id);
            var title = 'Order List';
            if (Ext.isEmpty(main_panel)) {
                main_panel = distributerOrderListPanel(main_panel_id, ind, title);
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
            } else {
                Application.UI.addTab(main_panel);
            }
        }, addPurchaseInvoice: function (id) {
            var title = (id == 0) ? 'Create Orders' : 'Edit Orders';
            var purchase_invoice_form = viewPurchaseInvoice_panel();
            var addPurchaseInvoice_window = Ext.getCmp('purchaseInvoice_window');
            if (Ext.isEmpty(addPurchaseInvoice_window)) {
                var addPurchaseInvoice_window = new Ext.Window({
                    id: 'purchaseInvoice_window',
                    title: title,
                    //iconCls: 'finascop_invoice',
                    modal: true,
                    layout: 'fit',
                    width: 900,
                    autoHeight: true,
                    shadow: false,
                    resizable: false,
                    closable: false,
                    items: [purchase_invoice_form],
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            handler: function () {
                                excludeItemIds = [];
                                addPurchaseInvoice_window.close();
                            }
                        },
                        {
                            text: 'Save',
                            id: 'save_PInv_btn',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                            handler: function () {
                                savePurchaseInvoices();
                            }
                        }
                    ]
                });
            }


            if (!Ext.isEmpty(id)) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                purchase_invoice_form.load({
                    url: modURL + '&op=getPurchaseInvoice_EditData',
                    params: {
                        'id': id,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (frm, action) {

                    }
                });
            }

            Ext.getCmp('pu_party').getStore().baseParams.isParty = true;
            Ext.getCmp('pu_party').getStore().load();
            addPurchaseInvoice_window.show();
            addPurchaseInvoice_window.doLayout();
            addPurchaseInvoice_window.center();
        }
    }
}();






