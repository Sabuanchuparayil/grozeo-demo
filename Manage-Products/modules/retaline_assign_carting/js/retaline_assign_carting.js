Application.Retaline_Assign_Carting = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_assign_carting';
    var RECS_PER_PAGE = 12;

    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };

    var gridSelectionChangedassignCarting = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelBrmAssignCartingMainDataview').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelBrmAssignCartingMainDataview').getSelectionModel().getSelections()[0].data.order_branch_id;
            var ORDER_ID = Ext.getCmp("gridpanelBrmAssignCartingMainDataview").getSelectionModel().getSelections()[0].data.order_order_id;
            var ORDER_STATUS = Ext.getCmp("gridpanelBrmAssignCartingMainDataview").getSelectionModel().getSelections()[0].data.status;
            Application.Retaline_Assign_Carting.Cache.OrderNo = ORDER_ID;
            if (ORDER_STATUS != "Queued for manual assignment") {
                Ext.getCmp('order_save_scheduleassign_carting').hide();
            }
            else {
                Ext.getCmp('order_save_scheduleassign_carting').show();
            }
            Ext.getCmp('order_executives_gridassign_carting').getStore().load({
                params: {
                    order_branch_id: ID
                }
            });
        }
    };

    var AssignCartingMainGridStore = function () {
        var _assignCartingStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=list',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_order_id', 'order_customer_id', 'branch_name', 'status', 'order_confirm_date', 'company_name', 'order_branch_id', 'order_id']),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelBrmAssignCartingMainDataview').getView().refresh();
                    Ext.getCmp('gridpanelBrmAssignCartingMainDataview').getSelectionModel().selectRow(0);
                }
            }
        });

        return _assignCartingStore;
    };

    var masterPanelforAssignCarting = function (id, ind) {
        var _mpanelforassigncarting = new Ext.Panel({
            frame: false,
            id: id,
            border: false,
            layout: 'border',
            title: 'Assign Carting',
            //iconCls: 'overseas_report',
            autoScroll: false,
            items: [AssignCartingMainGrid(ind),
                new Ext.Panel({
                    region: 'east',
                    border: true,
                    split: true,
                    width: 600,
                    tools: [{
                            //id: 'order_maximize',
                            handler: function () {
                                // showMap(ind);
                            }
                        }],
                    layout: 'border',
                    items: [mapPanel('location_map' + ind, ind),
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
                                },
                            ],
                            bbar: [{
                                    xtype: 'hidden',
                                    id: 'order_hdnExecutiveId' + ind
                                }, {
                                    xtype: 'displayfield',
                                    id: 'order_booking_disp' + ind
                                }, {
                                    xtype: 'displayfield', value: '|', style: 'margin:0 8px 0 8px;', id: 'ExecutiveDetailsDisplayField'
                                }, {
                                    xtype: 'displayfield',
                                    id: 'order_vehicle_disp' + ind
                                }, '->', {
                                    text: 'Assign',
                                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                    iconCls: 'my-icon1',
                                    id: 'order_save_schedule' + ind,
                                    handler: function () {
                                        var executive_id = Ext.getCmp("order_executives_grid" + ind).getSelectionModel().getSelections()[0].data.id;
                                        var order_ID = Ext.getCmp("gridpanelBrmAssignCartingMainDataview").getSelectionModel().getSelections()[0].data.order_order_id;
                                        var branch_ID = Ext.getCmp("gridpanelBrmAssignCartingMainDataview").getSelectionModel().getSelections()[0].data.order_branch_id;

                                        if (executive_id != '')
                                        {

                                            Ext.Ajax.request({
                                                url: modURL + '&op=assignExecutive',
                                                method: 'POST',
                                                params: {
                                                    id: executive_id,
                                                    order_ID: order_ID,
                                                    br_ID: branch_ID

                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.status == 'ok') {
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp("order_executives_grid" + ind).getStore().load({
                                                            params: {
                                                                cpd_ID: branch_ID
                                                            }
                                                        });
                                                        Ext.getCmp("gridpanelBrmAssignCartingMainDataview").getStore().load();
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
        return _mpanelforassigncarting;
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
                    Ext.getCmp("order_executives_grid" + ind).getView().refresh();
                    Ext.getCmp("order_executives_grid" + ind).getSelectionModel().selectRow(0);

                }
            }
        });
        return store;
    };
    var executive_select = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp("order_executives_gridassign_carting").getSelectionModel().getSelections())) {
            var executive_name = Ext.getCmp("order_executives_gridassign_carting").getSelectionModel().getSelections()[0].data.name;
            Ext.getCmp('ExecutiveDetailsDisplayField').setValue("Order No:" + Application.Retaline_Assign_Carting.Cache.OrderNo + '|' + "Godown Boy:" + executive_name);
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
            id: 'order_executives_grid' + ind,
            columns: [
                {
                    header: 'Godown Executives',
                    dataIndex: 'name',
                    width: 200
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
                }
            }
        };
    };





    var AssignCartingMainGrid = function (ind) {
        var _assignCartingStore = AssignCartingMainGridStore();
        var _assigncartingGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'company_name'

                }, {
                    type: 'string',
                    dataIndex: 'branch_name'
                },
            ]
        });
        _assigncartingGridFilter.remote = true;
        _assigncartingGridFilter.autoReload = true;
        var _assignCartingmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _assignCartingStore,
            //iconCls: 'money',
            id: 'gridpanelBrmAssignCartingMainDataview',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _assigncartingGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order No.',
                    dataIndex: 'order_order_id',
                    sortable: true,
                    tooltip: 'Order No.',
                    hideable: false
                },
                {
                    header: 'Order Date',
                    dataIndex: 'order_confirm_date',
                    sortable: true,
                    tooltip: 'Order Date',
                    hideable: false
                },
                {
                    header: 'Company Name',
                    dataIndex: 'company_name',
                    sortable: true,
                    tooltip: 'Company Name',
                    hideable: false
                },
                {
                    header: 'Branch Name',
                    dataIndex: 'branch_name',
                    sortable: true,
                    tooltip: 'Branch Name',
                    hideable: false
                },
                {
                    header: 'Order Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'status'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: 'list_users',
                            tooltip: 'View Polled History',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Application.CPD.viewPolledHistory(record.get('order_order_id'), 'branch');
                }
                        },{
                            iconCls: 'my-icon96',
                            tooltip: 'View Item Details',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Application.CPD.viewItems(record.get('order_id'), 'branch');
                            }
                        }, {
                            getClass: function (v, meta, rec) {
                                if ((rec.get('status') == 'Delivery carting') || (rec.get('status') == 'Assigned godown boy')) {
                                    this.items[1].tooltip = 'Revoke';
                                    return 'now_active';
                                }
                                else {
                                    return 'hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.MessageBox.confirm('Confirm', 'Do you wish to revoke this?', function (btn, text) {
                                    if (btn == 'yes') {
                                        Application.CPD.revokeOrder(record.get('order_order_id'), 'branch')
                                    }
                                });
                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _assignCartingStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedassignCarting
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {

                },
                resize: onGridResize,
                afterrender: function () {
                    Ext.getCmp('textboxBrmAssignCartingBranch').setValue(_SESSION.current_branch);
                    if (Ext.getCmp('textboxBrmAssignCartingBranch').getValue() != '')
                    {
                        _assignCartingStore.baseParams = {
                            current_branch_id: _SESSION.finascop_current_branch_id
                        }
                        _assignCartingStore.load();
                    }
                }
            },
            tbar: [
                {
                    html: 'Branch : &nbsp;'
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmAssignCartingBranch',
                    name: 'textboxBrmAssignCartingBranch',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    disabled: true
                }
            ]
        });
        return _assignCartingmaingridPanel;
    };

    return{
        Cache: {},
        initAssignCarting: function () {
            var _assigncartingpanel = 'panelAssignCartingDataview';
            var ind = 'assign_carting';
            var _masterPanelAssignCarting = Ext.getCmp(_assigncartingpanel);
            if (Ext.isEmpty(_masterPanelAssignCarting)) {
                _masterPanelAssignCarting = masterPanelforAssignCarting(_assigncartingpanel, ind);
                Application.UI.addTab(_masterPanelAssignCarting);
                _masterPanelAssignCarting.doLayout();
            } else {
                Application.UI.addTab(_masterPanelAssignCarting);
            }
        }
    };
}();