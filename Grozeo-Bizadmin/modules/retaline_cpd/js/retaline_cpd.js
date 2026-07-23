Application.CPD = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var modURL = '?module=retaline_cpd';
    var my_marker;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var totalComboCount;
    var myMask, branchLoadedForm;
    var userID;
    /*************MASTER DATA****************/
    var comboLoadComplete = function () {

        if (!Ext.isEmpty(branchLoadedForm) && branchLoadedForm != null) {
            branchLoadedForm[0].loadRecord(Ext.decode(branchLoadedForm[1]));
        }

        if (totalComboCount == 0) {
            myMask.hide();
            branchLoadedForm = null;
        }
    };
    var WinMask;
    var branchStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCpd',
                method: 'post'
            }),
            fields: ['cpd_id', 'cpd_Name', 'cpd_District', 'cpd_State', 'cpd_Address', 'cpd_Phone', 'cpd_Incharge', 'cpd_status', 'cpd_pincode', 'cpd_Lat', 'cpd_Lng'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('cpd_Name', 'ASC');
        return store;
    };

    var cpdGrid = function (id) {
        var branch_store = branchStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    name: 'cpd_Name'
                }, {
                    type: 'string',
                    name: 'cpd_District'
                }, {
                    type: 'string',
                    name: 'cpd_State'
                }, {
                    type: 'string',
                    name: 'cpd_Incharge'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'CPD',
            //iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'CPD Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'cpd_Name',
                    tooltip: 'CPD Name'
                },
                {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'cpd_District',
                    tooltip: 'District'
                },
                {
                    header: 'State',
                    sortable: true,
                    dataIndex: 'cpd_State',
                    tooltip: 'State'
                },
                {
                    header: 'Contact No.',
                    sortable: true,
                    dataIndex: 'cpd_Phone',
                    tooltip: 'Contact No.'
                },
                {
                    header: 'Incharge',
                    sortable: true,
                    dataIndex: 'cpd_Incharge',
                    tooltip: 'Incharge'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            iconCls: 'edit',
                            tooltip: 'Edit CPD Details',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var map_Lat = record.get('cpd_Lat');
                                var map_Long = record.get('cpd_Lng');
                                cpdDetails(record.get('cpd_id'), map_Lat, map_Long);
                            }
                        },
                        {
                            getClass: function (v, meta, rec) {
                                if (rec.get('cpd_status') == 'Active') {
                                    this.items[1].tooltip = 'Deactivate CPD';
                                    return 'now_active';
                                }
                                else {
                                    this.items[1].tooltip = 'Activate CPD';
                                    return 'now_inactive';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                //console.log (record.get('comp_id'));
                                Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this cpd?', function (btn, text) {
                                    if (btn == 'yes') {
                                        changeCPDStatus(record.get('cpd_id'), record.get('cpd_status'), record.get('comp_id'));
                                    }
                                });
                            }
                        }]
                }],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create New CPD', iconCls: 'add',
                    handler: function () {
                        cpdDetails(0, 0, 0);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branch_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exp'
        });
        return grid_panel;
    };
    var brComboStore = function (ind) {
        return  new Ext.data.ArrayStore({
            url: modURL + '&op=getComboStore&ind=' + ind,
            method: 'POST',
            autoLoad: false,
            fields: ['id', 'name']
        });
    };
    var changeCPDStatus = function () {

        Application.CPD.cpd_id = arguments[0];
        Application.CPD.cpd_status = arguments[1];
        var form_data = {
            cpd_id: Application.CPD.cpd_id,
            cpd_status: Application.CPD.cpd_status
        };
        var params = {
            action: 'Update',
            module: 'retaline_cpd',
            op: 'changeStatus',
            extrainfo: 'asd',
            id: Application.CPD.cpd_id

        };

        APICall(params, Application.CPD.changeStatus, form_data);
    };
    var branchForm = function (map_lat, map_long) {
        my_marker = [{
                lat: map_lat,
                lng: map_long,
                marker: {
                    title: "you are here",
                    draggable: false
                },
                listeners: {
                    "onFailure": function () {
                        Ext.MessageBox.alert('Failed locating city ');
                    },
                    "onSuccess": function (point) {
                        console.log(point.lat);
                        console.log(point.lng);
                    },
                    "dragend": function (markerAt) {
                        Ext.getCmp('cpd_Lat').setValue(markerAt.latLng.lat());
                        Ext.getCmp('cpd_Lng').setValue(markerAt.latLng.lng());
                    }
                }
            }];

        var form = new Ext.FormPanel({
            labelAlign: 'top',
            autoHeight: true,
            width: 850,
            url: modURL + "&op=saveCPD",
            frame: true,
            id: 'cpd_form',
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: 0.4,
                            hideBorders: true,
                            //xtype: 'fieldset',
                            //autoHeight: true,
                            defaults: {
                                xtype: 'textfield',
                                anchor: '95%',
                                allowBlank: false,
                                //style: 'margin:10px 0 10px 0',
                                hideBorders: true
                            },
                            items: [{
                                    xtype: 'hidden',
                                    id: 'cpd_id',
                                    name: 'cpd_id'
                                }, {
                                    fieldLabel: 'CPD Name',
                                    id: 'cpd_Name',
                                    name: 'cpd_Name',
                                    maxLength: 250,
                                    tabIndex: 2
                                },
                                {
                                    fieldLabel: 'Address',
                                    id: 'cpd_Address',
                                    name: 'cpd_Address',
                                    maxLength: 20,
                                    height: 40,
                                    xtype: 'textarea',
                                    tabIndex: 4
                                },
                                {
                                    xtype: 'combo',
                                    store: brComboStore(1),
                                    mode: 'remote',
                                    id: 'cpd_State',
                                    fieldLabel: 'State',
                                    hiddenName: 'cpd_State',
                                    displayField: 'name',
                                    valueField: 'id',
                                    editable: false,
                                    selectOnFocus: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    tabIndex: 5,
                                    listeners: {
                                        select: function () {
                                            var dist = Ext.getCmp('cpd_District');
                                            var dist_str = dist.getStore();
                                            dist.reset();
                                            dist_str.removeAll();
                                            dist_str.baseParams = {
                                                ind: 2,
                                                state: this.getValue()
                                            };
                                            dist_str.load();
                                        }
                                    }
                                },
                                {
                                    xtype: 'combo',
                                    store: brComboStore(2),
                                    mode: 'remote',
                                    id: 'cpd_District',
                                    fieldLabel: 'District',
                                    hiddenName: 'cpd_District',
                                    displayField: 'name',
                                    valueField: 'id',
                                    editable: false,
                                    selectOnFocus: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    tabIndex: 6
                                }, {
                                    fieldLabel: 'Post code',
                                    id: 'cpd_pincode',
                                    name: 'cpd_pincode',
                                    vtype: 'phonespec',
                                    tabIndex: 7,
                                    listeners: {
                                        change: function () {
                                            if (!Ext.isEmpty(Ext.getCmp('cpd_pincode').getValue())) {
                                                Ext.getCmp('map_button1').enable();
                                            }
                                        }
                                    }
                                },
                                {
                                    id: 'cpd_Incharge',
                                    fieldLabel: 'Contact Person',
                                    maxLength: 250,
                                    name: 'cpd_Incharge',
                                    tabIndex: 8
                                },
                                {
                                    fieldLabel: 'Telephone',
                                    id: 'cpd_Phone',
                                    name: 'cpd_Phone',
                                    vtype: 'phonespec',
                                    tabIndex: 9
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.6,
                            title: 'MAP',
                            region: 'center',
                            items: [{
                                    xtype: 'gmappanel',
                                    gmapType: 'map',
                                    id: 'branchgooglemap',
                                    zoomLevel: 14,
                                    height: 380,
                                    minGeoAccuracy: 4,
                                    scaleControl: true,
                                    mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
                                    mapControls: ['GSmallMapControl', 'GMapTypeControl'],
                                    setCenter: {
                                        lat: map_lat,
                                        lng: map_long
                                    },
                                    repaint: function (zoomlevel) {
                                        var gmappanel = Ext.getCmp('branchgooglemap');
                                        if (zoomlevel) {
                                            gmappanel.zoomLevel = zoomlevel;
                                            gmappanel.getMap().setZoom(zoomlevel);
                                        }
                                        gmappanel.onMapReady();

                                    },
                                    markers: my_marker
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .5,
                                    //style: 'margin-left:5px;',
                                    items: [{
                                            xtype: 'fieldset',
                                            title: 'Map Coordinates',
                                            autoHeight: true,
                                            items: [{
                                                    layout: 'column',
                                                    items: [{
                                                            layout: 'form',
                                                            columnWidth: .3,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    fieldLabel: 'Coordinates',
                                                                    tooltip: 'Locate latitude and longitude',
                                                                    icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                                                                    iconCls: 'my-icon1',
                                                                    id: 'map_button1',
                                                                    name: 'map_button1',
                                                                    disabled: true,
                                                                    text: 'Find Coordinates',
                                                                    tabIndex: 13,
                                                                    handler: function () {
                                                                        Ext.getCmp('branchgooglemap').clearMarkers();
                                                                        my_marker = [];
                                                                        //Ext.getCmp('branchgooglemap').clearMarkers();
                                                                        my_marker.push({
                                                                            geoCodeAddr: Ext.getCmp("cpd_pincode").getValue(),
                                                                            setCenter: true,
                                                                            marker: {
                                                                                title: "Click and Drag to Move Around",
                                                                                draggable: true
                                                                            },
                                                                            listeners: {
                                                                                onFailure: function () {

                                                                                },
                                                                                "tilesloaded": function (markerAt) {
                                                                                    Ext.getCmp('cpd_Lat').setValue(markerAt.latLng.lat());
                                                                                    Ext.getCmp('cpd_Lng').setValue(markerAt.latLng.lng());
                                                                                },
                                                                                onSuccess: function (point) {
                                                                                    Ext.getCmp('cpd_Lat').setValue(point.latLng.lat());
                                                                                    Ext.getCmp('cpd_Lng').setValue(point.latLng.lng());
                                                                                },
                                                                                "dragend": function (markerAt) {
                                                                                    Ext.getCmp('cpd_Lat').setValue(markerAt.latLng.lat());
                                                                                    Ext.getCmp('cpd_Lng').setValue(markerAt.latLng.lng());
                                                                                }
                                                                            },
                                                                            icon: null
                                                                        });
                                                                        Ext.getCmp('branchgooglemap').addScaleControl();
                                                                        Ext.getCmp('branchgooglemap').zoomLevel = 15;
                                                                        Ext.getCmp('branchgooglemap').clearMarkers();
                                                                        Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                        Ext.defer(function () {
                                                                            var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                            Ext.getCmp('cpd_Lat').setValue(point.lat);
                                                                            Ext.getCmp('cpd_Lng').setValue(point.lng);
                                                                            Ext.getCmp('branchgooglemap').clearMarkers();
                                                                            Ext.getCmp('branchgooglemap').repaint(13);
                                                                            Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                        }, 1200);
                                                                    }
                                                                }]
                                                        }, {
                                                            layout: 'form',
                                                            columnWidth: .35,
                                                            items: [{
                                                                    xtype: 'textfield',
                                                                    fieldLabel: 'Latitude',
                                                                    //style: 'padding-left:5px;',
                                                                    id: 'cpd_Lat',
                                                                    name: 'cpd_Lat',
                                                                    anchor: '95%'
                                                                }]
                                                        }, {
                                                            layout: 'form',
                                                            columnWidth: .35,
                                                            items: [{
                                                                    xtype: 'textfield',
                                                                    fieldLabel: 'Longitude',
                                                                    id: 'cpd_Lng',
                                                                    name: 'cpd_Lng',
                                                                    anchor: '95%'
                                                                }]
                                                        }]
                                                }]
                                        }]
                                }
                            ]
                        }]
                }]
        });
        return form;
    };
    var cpdDetails = function () {

        var cpd_id = arguments[0];
        var map_lat = arguments[1];
        var map_long = arguments[2];
        var win_id = "cpd_details_window";

        var cpd_details_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(cpd_details_window)) {
            var cpd_form = branchForm(map_lat, map_long);
            cpd_details_window = new Ext.Window({
                id: win_id,
                title: 'Create New CPD',
                layout: 'fit',
                width: 900,
                height: 600,
                autoScroll: true,
                plain: true,
                //constrainHeader: true,
                modal: true,
                frame: true,
                iconCls: '',
                resizable: false,
                items: cpd_form,
                buttons: [{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (cpd_form.getForm().isValid()) {

                                var form_data = cpd_form.getForm().getValues();
                                var params = {
                                    action: 'Insert',
                                    module: 'finascop_branch',
                                    op: 'saveCPD',
                                    id: '0',
                                    extrainfo: 'asd'
                                };
                                if (cpd_id > 0) {
                                    params.action = 'Update';
                                    params.id = cpd_id;
                                }
                                APICall(params, Application.CPD.saveCPD, form_data);

                            } else {
                                Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                            }
                        }
                    }, {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            cpd_details_window.close();
                        }
                    }],
                listeners: {
                    afterrender: function () {
                        if (!Ext.isEmpty(cpd_id) && cpd_id > 0) {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");

                            myMask = new Ext.LoadMask(Ext.getCmp('cpd_details_window').getEl(), {msg: "Please wait..."});
                            myMask.show();

                            cpd_form.load({
                                url: modURL + '&op=getCpdDetails',
                                params: {
                                    'id': cpd_id,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                },
                                success: function (frm, action) {

                                    eval('var tmp=' + action.response.responseText);
                                    branchLoadedForm = [frm, action.response.responseText];
                                    totalComboCount = 2;


                                    cpd_details_window.setTitle("Edit Details : " + Ext.getCmp("cpd_Name").getValue());
                                    var cpd_State = Ext.getCmp('cpd_State');


                                    cpd_State.getStore().load({
                                        params: {
                                            ind: 1
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });

                                    var cpd_District = Ext.getCmp('cpd_District');
                                    cpd_District.getStore().baseParams.state = cpd_State.getValue();

                                    cpd_District.getStore().load({
                                        params: {
                                            ind: 2
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });
                                }
                            });
                        }
                    }
                }
            });
        }

        cpd_details_window.doLayout();
        cpd_details_window.show(this);
        cpd_details_window.center();
    };

    var godownExecutiveStore = function () {
        var proxy = new Ext.data.HttpProxy({
            url: modURL + '&op=getGoDownExecutive',
            method: 'post'
        });
        var godownBoy_store = new Ext.data.JsonStore({
            proxy: proxy,
            fields: ['id', 'name', 'phone', 'cpd_name', 'status', 'is_cpd', 'is_offline', 'latlng_updated_at', 'is_allowManualSchedule', 'is_allowAutoSchedule','status','statusName'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'id',
            remoteSort: false,
            autoLoad: false
        });
        //godownBoy_store.setDefaultSort('name', 'ASC');
        return godownBoy_store;
    };
    /*var godownExecutiveStore = function () {
     var _godownExecutiveStore = new Ext.data.GroupingStore({
     proxy: new Ext.data.HttpProxy({
     url: modURL + '&op=getGoDownExecutive',
     method: 'post'
     }),
     reader: new Ext.data.JsonReader({
     totalProperty: 'totalCount',
     idProperty: 'business_type_id',
     root: 'data'
     }, ['id', 'name', 'phone', 'cpd_name', 'status', 'is_cpd', 'is_offline', 'latlng_updated_at', 'is_allowManualSchedule', 'is_allowAutoSchedule']),
     sortInfo: {
     field: 'name',
     direction: "DESC"
     },
     groupField: '',
     groupDir: 'ASC',
     remoteSort: true,
     autoLoad: false,
     root: 'data',
     listeners: {
     load: function () {
     Ext.getCmp('gridgodownExecutive').getView().refresh();
     Ext.getCmp('gridgodownExecutive').getSelectionModel().selectRow(0);
     }
     }
     });
     return _godownExecutiveStore;
     };*/

    function godownExecutiveSelect() {
        return  new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
    }

    var godownExecutiveAction = function () {
        var action = new Ext.ux.grid.RowActions({
            autoWidth: false,
            width: 100,
            actions: [{
                    tooltip: 'Edit Order Picker',
                    iconCls: 'my-icon24',
                    align: 'center',
                    callback: function (grid, rec, row, col) {
                        addPackingExecutive(rec.data.id);
                    }
                }, {
                    tooltip: 'Force Logout',
                    iconCls: 'exit',
//                    getClass: function (v, meta, rec) {
//                        console.log("rec.get('is_offline')", rec.get('is_offline'));
//                        if (rec.get('is_offline') == 'No') {
//                            return 'exit';
//                        } else {
//                            return 'hideicon';
//                        }
//                    },
                    callback: function (grid, rec, row, col) {
                        //var record = grid.store.getAt(rowIndex);
                        if (rec.data.is_offline == 'Online') {
                            Ext.Ajax.request({
                                url: modURL + '&op=logoutGodownExecutive',
                                method: 'POST',
                                params: {
                                    id: rec.data.id,
                                    phone: rec.data.phone
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        Application.example.msg('Success', tmp.message);
                                        Ext.getCmp('gridgodownExecutive').getStore().reload();
                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        } else {
                            Ext.Msg.alert("Notification.", 'Order picker is offline.');
                        }


                    }
                }
            ]
        });
        return action;
    }

    var godownExecutiveGrid = function () {
        var action = packingGridAction();
        var godownExecutivefilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'name'

                }, {
                    type: 'string',
                    dataIndex: 'phone'

                }, {
                    type: 'string',
                    dataIndex: 'is_offline'

                }, {
                    type: 'string',
                    dataIndex: 'is_allowManualSchedule'

                }, {
                    type: 'string',
                    dataIndex: 'is_allowAutoSchedule'

                }, {
                    type: 'string',
                    dataIndex: 'latlng_updated_at'

                }, {
                    type: 'string',
                    dataIndex: 'cpd_name'

                }, {
                    type: 'string',
                    dataIndex: 'statusName'

                }]
        });
        var godownExecutive_store = godownExecutiveStore();
        var godownExecutive_select = godownExecutiveSelect();
        var action = godownExecutiveAction();
        var godownExecutive_grid = new Ext.grid.GridPanel({
            store: godownExecutive_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('is_offline') == 'Online')
                    {
                        return 'finascop_indicateColPALEGREEN';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [action, godownExecutivefilters],
            title: 'Order Pickers',
            id: 'gridgodownExecutive',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order Picker',
                    id: 'name',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'name'
                }, {
                    header: 'Phone',
                    id: 'phone',
                    hideable: true,
                    sortable: true,
                    dataIndex: 'phone'
                },
                {
                    header: 'Central Store/Distributor/Retailer Name',
                    id: 'cpd_name',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'cpd_name'
                }, {
                    header: 'Online Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'is_offline'
                }, {
                    header: 'Manual Schedule',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'is_allowManualSchedule'
                }, {
                    header: 'Auto Schedule',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'is_allowAutoSchedule'
                }, {
                    header: 'Location Updated At',
                    id: 'latlng_updated_at',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'latlng_updated_at'
                },{
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'statusName'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            var is_offline = Ext.getCmp('gridgodownExecutive').getSelectionModel().getSelections()[0].data.is_offline;
                            packingExeActionMenu(e, is_offline);
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 header: 'Action',
                 sortable: false,
                 items: [{
                 tooltip: 'Edit Packing Executive',
                 iconCls: 'my-icon24',
                 align: 'center',
                 handler: function (grid, rowIndex, colIndex) {
                 //callback: function (grid, rec, row, col) {
                 var rec = grid.store.getAt(rowIndex);
                 addPackingExecutive(rec.data.id);
                 }
                 }, {
                 tooltip: 'Force Logout',
                 iconCls: 'exit',
                 getClass: function (v, meta, rec) {
                 console.log("rec.get('is_offline')", rec.get('is_offline'));
                 if (rec.get('is_offline') == 'Online') {
                 return 'exit';
                 } else {
                 return 'hideicon';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 //callback: function (grid, rec, row, col) {
                 var record = grid.store.getAt(rowIndex);
                 if (record.data.is_offline == 'Online') {
                 Ext.Ajax.request({
                 url: modURL + '&op=logoutGodownExecutive',
                 method: 'POST',
                 params: {
                 id: record.data.id,
                 phone: record.data.phone
                 },
                 success: function (response) {
                 var tmp = Ext.decode(response.responseText);
                 if (tmp.success === true && tmp.valid === true) {
                 Application.example.msg('Success', tmp.message);
                 Ext.getCmp('gridgodownExecutive').getStore().reload();
                 } else if (tmp.success === true && tmp.valid === false) {
                 Ext.Msg.alert("Notification.", tmp.message);
                 } else if (tmp.success === true && tmp.img_valid === false) {
                 Ext.Msg.alert("Notification.", tmp.message);
                 } else {
                 if (tmp.message) {
                 Ext.Msg.alert("Error", 'Entered data is not valid.');
                 } else {
                 Ext.Msg.alert("Error", tmp.error.msg);
                 }
                 
                 }
                 },
                 failure: function (response) {
                 var tmp = Ext.util.JSON.decode(response.responseText);
                 Ext.MessageBox.alert('Error', tmp.message);
                 }
                 });
                 } else {
                 Ext.Msg.alert("Notification.", 'Order picker is offline.');
                 }
                 
                 
                 }
                 }]
                 }*/
            ],
            sm: godownExecutive_select,
            listeners: {
                resize: updatePagination
            },
            /* <? if (user_access("godownBoy", "savegodownBoy")) { ?> */
            tbar: [{
                    text: 'Create Order Picker',
                    tooltip: 'Create Order Picker',
                    id: 'godownExecutive_save_button',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        addPackingExecutive();
                    }
                }], /* <? } ?> */
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: godownExecutive_store,
                displayInfo: true,
                plugins: [godownExecutivefilters],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'name'

        });
        return godownExecutive_grid;
    }
    var packingGridAction = function () {
        var action = new Ext.ux.grid.RowActions({
            autoWidth: false,
            hideMode: 'display',
            width: 150,
            actions: [{align: 'center',
                    handler: function (grid, rowIndex, colIndex) {
                        //callback: function (grid, rec, row, col) {
                        var rec = grid.store.getAt(rowIndex);
                        addPackingExecutive(rec.data.id);
                    }
                }]
        });
        return action;
    };
    var packingExeActionMenu = function (e, is_offline) {
        var packingExeActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Edit",
                    handler: function () {
                        var id = Ext.getCmp('gridgodownExecutive').getSelectionModel().getSelections()[0].data.id;
                        addPackingExecutive(id);
                    }
                },{
                    text: "Change Status",
                    handler: function () {
                        var id = Ext.getCmp('gridgodownExecutive').getSelectionModel().getSelections()[0].data.id;
                        var status = Ext.getCmp('gridgodownExecutive').getSelectionModel().getSelections()[0].data.status;
                            Ext.Ajax.request({
                                url: modURL + '&op=chStatusGodownExecutive',
                                method: 'POST',
                                params: {
                                    id: id,
                                    status:status
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        Application.example.msg('Success', tmp.message);
                                        Ext.getCmp('gridgodownExecutive').getStore().reload();
                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        if (tmp.message) {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        } else {
                                            Ext.Msg.alert("Error", tmp.error.msg);
                                        }

                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                    }
                }, {
                    text: "Force Logout",
                    hidden: !(is_offline == 'Online'),
                    handler: function () {
                        //var record = grid.store.getAt(rowIndex);
                        var is_offline = Ext.getCmp('gridgodownExecutive').getSelectionModel().getSelections()[0].data.is_offline;
                        if (is_offline == 'Online') {
                            Ext.Ajax.request({
                                url: modURL + '&op=logoutGodownExecutive',
                                method: 'POST',
                                params: {
                                    id: Ext.getCmp('gridgodownExecutive').getSelectionModel().getSelections()[0].data.id,
                                    phone: Ext.getCmp('gridgodownExecutive').getSelectionModel().getSelections()[0].data.phone
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        Application.example.msg('Success', tmp.message);
                                        Ext.getCmp('gridgodownExecutive').getStore().reload();
                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        if (tmp.message) {
                                            Ext.Msg.alert("Error", 'Entered data is not valid.');
                                        } else {
                                            Ext.Msg.alert("Error", tmp.error.msg);
                                        }

                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        } else {
                            Ext.Msg.alert("Notification.", 'Order picker is offline.');
                        }
                    }
                }]
        });
        packingExeActionMenu.showAt(e.getXY());
    };
    var CPDStore = new Ext.data.JsonStore({
        fields: ['cpd_id', 'cpd_Name'],
        url: modURL + '&op=getCPDName',
        autoLoad: true,
        method: 'post',
    });
    var vendorStore = new Ext.data.JsonStore({
        fields: ['stpa_id', 'stpa_Fname'],
        url: modURL + '&op=getVendorName',
        autoLoad: true,
        method: 'post',
    });
    var BranchStore = new Ext.data.JsonStore({
        fields: ['br_ID', 'br_Name'],
        url: modURL + '&op=getBranchName',
        autoLoad: true,
        method: 'post',
    });
    var addPackingExecutiveFrm = function () {
        var formPackingExecutive = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'godownexecutive_form',
            height: 250,
            labelAlign: 'top',
            items: [
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'name',
                                            name: 'name',
                                            allowBlank: false,
                                            vtype: 'dataStringspec',
                                            maxLength: 250,
                                            maxLengthText: 'The maximum length for this field is 250',
                                            anchor: '97%',
                                            tabIndex: 501,
                                            fieldLabel: 'First Name',
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'lname',
                                            name: 'lname',
                                            allowBlank: false,
                                            maxLength: 250,
                                            maxLengthText: 'The maximum length for this field is 250',
                                            anchor: '97%',
                                            tabIndex: 505,
                                            fieldLabel: 'Last Name',
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }
                                    ]
                                }]
                        }]

                },
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [
                                {
                                    layout: 'form',
                                    columnWidth: .33,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'emp_id',
                                            name: 'emp_id',
                                            maxLength: 25,
                                            maxLengthText: 'The maximum length for this field is 25',
                                            tabIndex: 510,
                                            allowBlank: true,
                                            vtype: 'dataStringspec',
                                            anchor: '97%',
                                            fieldLabel: 'Employee ID'
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .33,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'emp_ni_number',
                                            name: 'emp_ni_number',
                                            maxLength: 25,
                                            maxLengthText: 'The maximum length for this field is 25',
                                            tabIndex: 515,
                                            allowBlank: true,
                                            vtype: 'dataStringspec',
                                            anchor: '97%',
                                            fieldLabel: 'Employee NI Number'
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .34,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'emp_email_id',
                                            name: 'emp_email_id',
                                            maxLength: 50,
                                            maxLengthText: 'The maximum length for this field is 50',
                                            tabIndex: 520,
                                            hidden: false,
                                            allowBlank: true,
                                            vtype: 'email',
                                            anchor: '97%',
                                            fieldLabel: 'E-mail ID'
                                        }
                                    ]
                                }]
                        }
                    ]

                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: .4,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'emp_add1',
                                    name: 'emp_Add1',
                                    maxLength: 250,
                                    maxLengthText: 'The maximum length for this field is 250',
                                    tabIndex: 525,
                                    allowBlank: true,
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    fieldLabel: 'Address1'
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: .4,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'emp_add2',
                                    name: 'emp_Add2',
                                    maxLength: 250,
                                    maxLengthText: 'The maximum length for this field is 250',
                                    tabIndex: 530,
                                    allowBlank: true,
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    fieldLabel: 'Address2'
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: .2,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'emp_pincode',
                                    name: 'emp_pincode',
                                    tabIndex: 535,
                                    allowBlank: true,
                                    anchor: '97%',
                                    fieldLabel: 'Post code',
                                    listeners: {
                                        focus: function () {

                                        }
                                    }
                                }
                            ]
                        }]
                },
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .2,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'phone',
                                            name: 'phone',
                                            allowBlank: false,
                                            vtype: 'phonespec',
                                            anchor: '97%',
                                            tabIndex: 540,
                                            fieldLabel: 'Phone',
                                            listeners: {
                                                change: function (field) {
                                                    if (field.getValue().charAt(0) == '0') {
                                                        field.setValue(field.getValue().slice(1));
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .4,
                                    items: [
                                        {
                                            xtype: 'combo',
                                            id: 'comboxbranchname',
                                            name: 'comboxbranchname',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            fieldLabel: 'CS / Distributor Name / Retailer',
                                            editable: true,
                                            allowBlank: true,
                                            anchor: '97%',
                                            store: BranchStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'br_Name',
                                            valueField: 'br_ID',
                                            tabIndex: 550,
                                            hiddenName: 'comboxbranchname',
                                            listeners: {
                                                select: function (combo, record) {
                                                    var br_ID = record.data.br_ID;
                                                    if (br_ID > 0) {
                                                        Ext.getCmp('gdwn_party').reset();
                                                        Ext.getCmp('gdwn_party').disable();
                                                        Ext.getCmp('asctedbrach_stdDelivery').allowBlank = true;
                                                    }

                                                }
                                            }
                                        }

                                    ]
                                }, {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Vendor',
                                            emptyText: 'Choose Vendor',
                                            id: 'gdwn_party',
                                            name: 'gdwn_party',
                                            labelStyle: mandatory_label,
                                            allowBlank: true,
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: vendorStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'stpa_Fname',
                                            valueField: 'stpa_id',
                                            hiddenName: 'gdwn_party',
                                            tabIndex: 200,
                                            listeners: {
                                                select: function (combo, record) {
                                                    var stpa_id = record.data.stpa_id;
                                                    if (stpa_id > 0) {
                                                        Ext.getCmp('comboxbranchname').reset();
                                                        Ext.getCmp('comboxbranchname').disable();
                                                        Ext.getCmp('comboxbranchname').allowBlank = true;
                                                    }

                                                }
                                            }
                                        }
                                    ]
                                }]
                        }
                    ]

                },
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            xtype: 'checkbox',
                                            fieldLabel: 'Is CPD',
                                            id: 'godown_checkbox',
                                            hidden: true,
                                            name: 'godown_checkbox',
                                            tabIndex: 560,
                                            checked: true,
                                            listeners: {
                                                'check': function () {
                                                    if (Ext.getCmp('godown_checkbox').checked == false)
                                                    {
                                                        Ext.getCmp('branch_id_hidden').hide();
                                                        Ext.getCmp('branch_id_hidden').setValue('');
                                                        Ext.getCmp('comboxbranchname').show();
                                                    }
                                                    else
                                                    {
                                                        Ext.getCmp('comboxbranchname').setValue('');
                                                        Ext.getCmp('comboxbranchname').hide();
                                                        Ext.getCmp('branch_id_hidden').show();
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            xtype: 'combo',
                                            id: 'branch_id_hidden',
                                            name: 'branch_id_hidden',
                                            mode: 'local',
                                            hidden: true,
                                            typeAhead: true,
                                            forceSelection: true,
                                            fieldLabel: 'CPD Name',
                                            editable: true,
                                            allowBlank: true,
                                            anchor: '97%',
                                            store: CPDStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'cpd_Name',
                                            valueField: 'cpd_id',
                                            tabIndex: 570,
                                            hiddenName: 'branch_id_hidden'
                                        }
                                    ]
                                }]
                        }]

                },
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            border: false,
                                            id: 'is_allowAutoSchedule',
                                            name: 'is_allowAutoSchedule',
                                            xtype: 'checkbox',
                                            inputValue: 1,
                                            tabIndex: 580,
                                            anchor: '98%',
                                            hideLabel: true,
                                            labelAlign: 'right',
                                            boxLabel: '&nbspAllow auto schedule',
                                            listeners: {
                                                check: function (cb1, checked) {
                                                    if (checked == true) {
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            border: false,
                                            id: 'is_allowManualSchedule',
                                            name: 'is_allowManualSchedule',
                                            xtype: 'checkbox',
                                            inputValue: 1,
                                            tabIndex: 580,
                                            anchor: '98%',
                                            hideLabel: true,
                                            labelAlign: 'right',
                                            boxLabel: '&nbspAllow manual schedule',
                                            listeners: {
                                                check: function (cb1, checked) {
                                                    if (checked == true) {
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                }]
                        }]

                },{
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            border: false,
                                            id: 'allowStoreClose',
                                            name: 'allowStoreClose',
                                            xtype: 'checkbox',
                                            inputValue: 1,
                                            tabIndex: 580,
                                            anchor: '98%',
                                            hideLabel: true,
                                            labelAlign: 'right',
                                            boxLabel: '&nbspAllow Store Close',
                                            listeners: {
                                                check: function (cb1, checked) {
                                                    if (checked == true) {
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [
                                        {
                                            border: false,
                                            id: 'allowInventoryControl',
                                            name: 'allowInventoryControl',
                                            xtype: 'checkbox',
                                            inputValue: 1,
                                            tabIndex: 580,
                                            anchor: '98%',
                                            hideLabel: true,
                                            labelAlign: 'right',
                                            boxLabel: '&nbspAllow Inventory Control',
                                            listeners: {
                                                check: function (cb1, checked) {
                                                    if (checked == true) {
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                }]
                        }]

                },
                {
                    xtype: 'hidden',
                    id: 'id',
                    name: 'id'
                }
            ]

        });
        return formPackingExecutive;
    };
    var addPackingExecutive = function (id) {
        var godownBoy_winId = "godownBoy_window";
        var godownExecutive_window = Ext.getCmp(godownBoy_winId);
        if (Ext.isEmpty(godownExecutive_window)) {
            var godownexecutivefrm = addPackingExecutiveFrm();
            godownExecutive_window = new Ext.Window({
                id: 'godownExecutive_window',
                layout: 'fit',
                width: 500,
                title: (id) ? 'Edit Order Picker' : 'Create Order Picker',
                autoHeight: true,
                //iconCls: (id) ? 'my-icon24' : 'my-icon26',
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                resizable: false,
                items: [godownexecutivefrm],
                buttons: [{
                        text: 'Cancel',
                        id: 'btnCancel',
                        tabIndex: 507,
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            godownExecutive_window.close();
                        }
                    },
                    {
                        text: 'Save',
                        id: 'btnsave',
                        tabIndex: 506,
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var is_allowManualSchedule, is_allowAutoSchedule,allowStoreClose,allowInventoryControl;
                            if (Ext.getCmp('branch_id_hidden').getValue() == '')
                            {
                                var checkboxvalue = 0;
                            }
                            if (Ext.getCmp('comboxbranchname').getValue() == '')
                            {
                                var checkboxvalue = 1;
                            }
                            if (Ext.getCmp('is_allowManualSchedule').getValue() == true) {
                                is_allowManualSchedule = 1;
                            } else {
                                is_allowManualSchedule = 0;
                            }

                            if (Ext.getCmp('is_allowAutoSchedule').getValue() == true) {
                                is_allowAutoSchedule = 1;
                            } else {
                                is_allowAutoSchedule = 0;
                            }
                            if (Ext.getCmp('allowStoreClose').getValue() == true) {
                                allowStoreClose = 1;
                            } else {
                                allowStoreClose = 0;
                            }
                            if (Ext.getCmp('allowInventoryControl').getValue() == true) {
                                allowInventoryControl = 1;
                            } else {
                                allowInventoryControl = 0;
                            }
                            if (Ext.getCmp('godownexecutive_form').getForm().isValid()) {
                                if (!Ext.isEmpty(Ext.getCmp('name').getValue()) &&
                                        !Ext.isEmpty(Ext.getCmp('phone').getValue())) {

                                    Ext.Ajax.request({
                                        url: modURL + '&op=savegodownExecutive',
                                        method: 'POST',
                                        params: {
                                            id: id,
                                            name: Ext.getCmp('name').getValue(),
                                            lname: Ext.getCmp('lname').getValue(),
                                            emp_id: Ext.getCmp('emp_id').getValue(),
                                            emp_ni_number: Ext.getCmp('emp_ni_number').getValue(),
                                            emp_email_id: Ext.getCmp('emp_email_id').getValue(),
                                            emp_add1: Ext.getCmp('emp_add1').getValue(),
                                            emp_add2: Ext.getCmp('emp_add2').getValue(),
                                            emp_pincode: Ext.getCmp('emp_pincode').getValue(),
                                            phone: Ext.getCmp('phone').getValue(),
                                            cpdname: Ext.getCmp('branch_id_hidden').getValue(),
                                            branch_name: Ext.getCmp('comboxbranchname').getValue(),
                                            branch_id: checkboxvalue,
                                            is_allowManualSchedule: is_allowManualSchedule,
                                            is_allowAutoSchedule: is_allowAutoSchedule,
                                            gdwn_party: Ext.getCmp('gdwn_party').getValue(),
                                            allowStoreClose: allowStoreClose,
                                            allowInventoryControl: allowInventoryControl,
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', tmp.message);
                                                godownExecutive_window.close();
                                                Ext.getCmp('gridgodownExecutive').getStore().reload();
                                            } else if (tmp.success === true && tmp.valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else if (tmp.success === true && tmp.img_valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else {
                                                Ext.Msg.alert("Error", 'Entered data is not valid.');
                                            }
                                        },
                                        failure: function (response) {
                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            Ext.MessageBox.alert('Error', tmp.message);
                                        }
                                    });
                                }
                            }
                            else
                            {
                                Ext.MessageBox.alert("Notification", 'Please fill all mandatory fields');
                            }
                        }
                    }]
            });

        }
        ;
        BranchStore.load({
            callback: function () {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                if (!Ext.isEmpty(id)) {
                    godownexecutivefrm.load({
                        url: modURL + '&op=loadgodownExecutive',
                        params: {
                            'id': id,
                            apikey: _SESSION.apikey,
                            tstamp: t_stamp
                        },
                        success: function (frm, action) {
                            var obj = Ext.decode(action.response.responseText);
                            godownExecutive_window.setTitle("Edit Order Picker");


                            if (obj.data.branch_type_id == 1) {
                                Ext.getCmp('gdwn_party').disable();
                                if (obj.data.is_cpd == 1)
                                {
                                    Ext.getCmp('comboxbranchname').hide();
                                    Ext.getCmp('branch_id_hidden').setRawValue(obj.data.cpd_branch);
                                    Ext.getCmp('branch_id_hidden').setValue(obj.data.branch_id);
                                    Ext.getCmp('godown_checkbox').setValue(true);
                                }
                                else
                                {
                                    Ext.getCmp('branch_id_hidden').hide();
                                    Ext.getCmp('godown_checkbox').setValue(false);
                                    Ext.getCmp('comboxbranchname').setValue(obj.data.branch_name);
                                    Ext.getCmp('comboxbranchname').setRawValue(obj.data.cpd_branch);

                                }
                                Ext.getCmp('comboxbranchname').enable();
                            } else {
                                Ext.getCmp('comboxbranchname').disable();
                                Ext.getCmp('gdwn_party').setRawValue(obj.data.gdwn_partyname);
                                Ext.getCmp('gdwn_party').setValue(obj.data.branch_name);
                                Ext.getCmp('gdwn_party').enable();
                            }


                        }
                    });
                }
            }
        });
        godownExecutive_window.doLayout();
        godownExecutive_window.show(this);
        godownExecutive_window.center();
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
                            id: 'order_maximize',
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
                                }
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
                                }, '->',
                                {
                                    text: 'Assign',
                                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                    iconCls: 'my-icon1',
                                    id: 'order_save_schedule' + ind,
                                    handler: function () {
                                        var executive_id = Ext.getCmp("order_executives_grid").getSelectionModel().getSelections()[0].data.id;
                                        var order_no = Ext.getCmp("outward_schedule_grid").getSelectionModel().getSelections()[0].data.order_no;
                                        var branch_ID = Ext.getCmp("outward_schedule_grid").getSelectionModel().getSelections()[0].data.cpd_id;

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
                                                        Ext.getCmp("order_executives_grid").getStore().load({
                                                            params: {
                                                                cpd_ID: branch_ID
                                                            }
                                                        });
                                                        Ext.getCmp("outward_schedule_grid").getStore().load();
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
                    Ext.getCmp("order_executives_grid").getView().refresh();
                    Ext.getCmp("order_executives_grid").getSelectionModel().selectRow(0);

                }
            }
        });
        return store;
    };
    var executive_select = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp("order_executives_grid").getSelectionModel().getSelections())) {
            var executive_name = Ext.getCmp("order_executives_grid").getSelectionModel().getSelections()[0].data.name;
            Ext.getCmp('ExecutiveDetailsDisplayField').setValue("Order No:" + Application.CPD.Cache.Order_ID + '|' + "Packing Boy:" + executive_name);
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
            id: 'order_executives_grid',
            columns: [
                {
                    header: 'Order Pickers',
                    dataIndex: 'name',
                    width: 100
                },
                {
                    header: 'Mobile No.',
                    dataIndex: 'phone'
                }
                //  {
                //     header: 'V. Type',
                //     dataIndex: 'Executivetypename'
                // }, 
                // {
                //     header: 'Last Updation',
                //     dataIndex: 'LastLocationDtTm',
                //     renderer: function (val, meta, rec) {
                //         var selected_date = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                //         var show = selected_date[1] + '-' + selected_date[2] + '-' + selected_date[3] + ' ' + selected_date[4] + ':' + selected_date[5] + ':' + selected_date[6];
                //         //rec.set('updated_date', show);
                //         return show;
                //     }
                // }
            ],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: executive_select
                }
            }),
            listeners: {
                // cellClick: function (grid, rowIndex, columnIndex, e) {
                //     var record = grid.getStore().getAt(rowIndex);
                //     var vehicle_marker = record.get('vehicle_marker');

                //     Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
                //     Ext.getCmp('vehicle_details' + ind).update(record.data);

                //     Ext.getCmp('location_map' + ind).animateMarker(Application.Qugeo.Markers[ind][vehicle_marker], 'BOUNCE', 'START');


                //     setTimeout(function () {
                //         Ext.getCmp('location_map' + ind).animateMarker(Application.Qugeo.Markers[ind][vehicle_marker], 'BOUNCE', 'STOP');
                //     }, 2000);

                //     setTimeout(function () {
                //         Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
                //         Ext.getCmp('vehicle_disp' + ind).setValue('Executive No. : <b>' + record.get('v_No') + '</b>');
                //         Ext.getCmp('hdnExecutiveId' + ind).setValue(record.get('v_ID'));
                //     }, 50);
                // }
            },
            stripeRows: true
        });
        return grid;
    };

    // Grouping store for Main Grid
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
                "branch_name", "createddatetime", "updatedatetime"
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
                    Ext.getCmp("outward_schedule_grid").getView().refresh();
                    Ext.getCmp("outward_schedule_grid").getSelectionModel().selectRow(0);
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
    // var qugeoStore = function (ind) {
    //     var qugeo_store = new Ext.data.JsonStore({
    //         url: modURL + '&op=listSchedule',
    //         fields: ['booking_no', 'booked_at', 'customer', 'source', 'destination',
    //             'type', 'bk_id', 'bk_brk_br_id', 'latitude', 'longitude', 'mapicon', 'brPackingLati', 'brPackingLong'],
    //         totalProperty: 'totalCount',
    //         root: 'data',
    //         remoteSort: false,
    //         autoLoad: false,
    //         listeners: {
    //             beforeload: function () {
    //                 this.baseParams.ind = ind;
    //                 this.baseParams.br_id = Ext.getCmp('order_search_branch' + ind).getValue();
    //             }
    //         }
    //     });
    //     return qugeo_store;
    // };
    var qugeo_select = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp("outward_schedule_grid").getSelectionModel().getSelections())) {
            var cpd_ID = Ext.getCmp("outward_schedule_grid").getSelectionModel().getSelections()[0].data.cpd_id;
            var ORDER_ID = Ext.getCmp("outward_schedule_grid").getSelectionModel().getSelections()[0].data.order_no;
            var ORDER_STATUS = Ext.getCmp("outward_schedule_grid").getSelectionModel().getSelections()[0].data.order_status;
            Application.CPD.Cache.Order_ID = ORDER_ID;
            if (ORDER_STATUS != "Manual Queued") {
                Ext.getCmp('order_save_schedule1').hide();
            }
            else {
                Ext.getCmp('order_save_schedule1').show();
            }
            //var branch_ID = Ext.getCmp("outward_schedule_grid").getSelectionModel().getSelections()[0].data.branch_id;
            Ext.getCmp('order_executives_grid').getStore().load({
                params: {
                    cpd_ID: cpd_ID,
                    //branch_ID:branch_ID
                }
            });
            //Application.Idrl_Stories.ViewMode(ID);
        }
    };

    var branchstore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getBranch',
            method: 'post',
            autoLoad: false,
            root: 'data',
            remoteSort: true
        });
        return store;
    };
    var scheduleGrid = function (ind) {
        var transferFrom, transferTo;
        switch (_SESSION['br_PyramidLevel']) {
            case '1':
                transferFrom = 'CPD';
                transferTo = 'Central Store';
                break;
            case '2':
                transferFrom = 'Central Store';
                transferTo = 'Distributor';
                break;
            case '3':
                transferFrom = 'Distributor';
                transferTo = 'Retailer';
                break;
            case '4':
                transferFrom = 'Retailer';
                transferTo = 'Customer';
                break;
        }
        var _scheduleGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'list',
                    options: ['Created', 'Manual Queued', 'Polled', 'Assigned', 'Scanning Started', 'Incomplete Order', 'Order Completed', 'Cancelled', 'Expired', 'Dispatched', 'Partly Received', 'Received'],
                    phpMode: true,
                    dataIndex: 'order_status'
                },
                {
                    type: "string",
                    dataIndex: "order_no"
                }, {
                    type: "string",
                    dataIndex: "bcor_createdon"
                }, {
                    type: "string",
                    dataIndex: "cpd_Name"
                }, {
                    type: "string",
                    dataIndex: "branch_name"
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
            id: 'outward_schedule_grid',
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
                    sortable: true,
                    dataIndex: 'order_no'
                }, {
                    header: 'Order Date',
                    sortable: true,
                    dataIndex: 'bcor_createdon'
                }, {
                    header: transferFrom,
                    sortable: true,
                    dataIndex: 'cpd_Name'
                },
                {
                    header: transferTo,
                    sortable: true,
                    dataIndex: 'branch_name'
                },
                {
                    header: 'Order Status',
                    sortable: true,
                    dataIndex: 'order_status'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: 'list_users',
                            tooltip: 'View Polled History',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);

                                Application.CPD.viewPolledHistory(record.get('order_no'), 'cpd');
                            }
                        }, {
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
                    id: 'textboxBrmCpdDataeditBranch',
                    name: 'textboxBrmCpdDataeditBranch',
                    anchor: '98%',
                    hidden: true,
                    tabIndex: 1,
                    maxLength: 20,
                    disabled: true
                },
                {
                    html: '&nbsp;' + transferFrom + ' : &nbsp;',
                    //id: 'order_branch_label' + ind
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditCPD',
                    name: 'textboxBrmCpdDataeditCPD',
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
                    id: 'invokeTransferinCPD',
                    handler: function () {
                        setTimeout(function () {
                            Ext.Ajax.request({
                                waitMsg: 'Processing',
                                url: modURL,
                                params: {
                                    op: 'schedulerinvoke'
                                },
                                failure: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.status == 'error') {
                                        Ext.Msg.alert("Error", tmp.error.msg);
                                    } else {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }

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
//                        Ext.getCmp('textboxBrmCpdDataeditCPD').setValue(_SESSION.current_branch);
//                    } else {
                    //Ext.getCmp('textboxBrmCpdDataeditCPD').setValue(_SESSION.current_branch_cpd);
                    Ext.getCmp('textboxBrmCpdDataeditCPD').setValue(_SESSION.current_branch);
//                    }

                    Ext.getCmp('textboxBrmCpdDataeditBranch').setValue(_SESSION.current_branch);
                    if (Ext.getCmp('textboxBrmCpdDataeditBranch').getValue() != '' && Ext.getCmp('textboxBrmCpdDataeditCPD') != '')
                    {
                        qugeo_store.baseParams = {
                            cpd: Ext.getCmp('textboxBrmCpdDataeditCPD').getValue(),
                            br_id: Ext.getCmp('textboxBrmCpdDataeditBranch').getValue(),
                            cpd_id: _SESSION.current_branch_cpdId,
                            current_branch_id: _SESSION.finascop_current_branch_id
                        }
                        qugeo_store.load();
                    }
                },
                resize: onGridResize,
            }
        });
        return qugeo_grid;
    };
    var showMap = function (ind) {

        var panel = new Ext.Panel({
            layout: 'border',
            items: mapPanel('max_map', ind)
        });

        var map_win = new Ext.Window({
            width: winsize.width * 0.9,
            height: winsize.height * 0.9,
            plain: true,
            modal: true,
            frame: true,
            constrainHeader: true,
            layout: 'fit',
            items: panel,
            buttons: [{
                    text: 'Close',
                    tabIndex: '10',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        map_win.close();
                    }
                }]
        });

        map_win.doLayout();
        map_win.show(this);
        map_win.center();

    };
    var _bcdItemsStore = function (orderId, orderType) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listItemsinOutwardOrder',
                method: 'post'
            }),
            fields: ['bcod_id', 'bcor_id', 'stit_ID', 'bcod_Count', 'stitSKU', 'bcod_scannedcount'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.orderId = orderId;
                    this.baseParams.orderType = orderType;
                }
            }
        });
        return _Store;
    };
    var _bcdItemsGrid = function (orderId, orderType) {
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _bcdItemsStore(orderId, orderType),
            iconCls: 'money',
            autoScroll: true,
            width: winsize.width * 0.4,
            height: 300,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforOuteardItems',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item Name',
                    dataIndex: 'stitSKU',
                    sortable: true,
                    tooltip: 'Item Name',
                    hideable: false,
                    width: 200
                },
                {
                    header: 'Quantity',
                    dataIndex: 'bcod_Count',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Quantity',
                    hideable: false
                },
                {
                    header: 'Scanned Qty',
                    dataIndex: 'bcod_scannedcount',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Scanned Qty',
                    hideable: false
                }, {width: 20, dataIndex: ' '}
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforOuteardItems').getStore().load({
                        params: {
                            orderId: orderId,
                            orderType: orderType
                        }
                    });
                },
                rowdblclick: function (grid, rowIndex, e) {
                    var rec = grid.getStore().getAt(rowIndex);
                    var bcod_scannedcount = rec.get('bcod_scannedcount');
                    if (bcod_scannedcount > 0) {
                        Application.CPD.scannedBarcodeDetails(orderId, orderType, rec.get('bcod_id'));
                    }

                }
            }
        });
        return _dispatchgridPanel;
    };
    var _barcodeScannedDetailsStore = function (bcod_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listScannedBarcodes',
                method: 'post'
            }),
            fields: ['stiid_barcode', 'boib_id'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.bcod_id = bcod_id;
                }
            }
        });
        return _Store;
    };
    var _barcodeScannnedDetailsGrid = function (orderId, orderType, bcod_id) {
        var _barcodeScannngridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _barcodeScannedDetailsStore(bcod_id),
            iconCls: 'money',
            width: winsize.width * 0.5,
            height: 500,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelfor_barcodeScanDetails',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Barcode',
                    dataIndex: 'stiid_barcode',
                    sortable: true,
                    tooltip: 'Barcode',
                    hideable: false,
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelfor_barcodeScanDetails').getStore().load({
                        params: {
                            bcod_id: bcod_id
                        }
                    });
                }, rowdblclick: function (grid, rowIndex, e) {
                    var rec = grid.getStore().getAt(rowIndex);
                    Application.RetalineCurrentStock.displayBarcodeDetails(rec.get('stiid_barcode'));
                }
            }
        });
        return _barcodeScannngridPanel;
    };
    var _gbHistoryStore = function (orderId, orderType) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPolledHistory',
                method: 'post'
            }),
            fields: ['boy_name', 'ordersreqtatus', 'created_at', 'updated_at', 'accepted_time', 'scan_start_time', 'last_scan_time', 'completed_time'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.orderId = orderId;
                    this.baseParams.orderType = orderType;
                    this.baseParams.current_branch_id = _SESSION.finascop_current_branch_id;
                }
            }
        });
        return _Store;
    };
    var _gbHistoryGrid = function (orderId, orderType) {
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _gbHistoryStore(orderId, orderType),
            iconCls: 'money',
            autoScroll: true,
            width: winsize.width * 0.4,
            height: 300,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforGBHistory',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Boy',
                    dataIndex: 'boy_name',
                    sortable: true,
                    tooltip: 'Boy',
                    hideable: false,
                    width: 150
                },
                {
                    header: 'Status',
                    dataIndex: 'ordersreqtatus',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false
                },
                {
                    header: 'Created At',
                    dataIndex: 'created_at',
                    sortable: true,
                    tooltip: 'Created At',
                    hideable: false
                },
                {
                    header: 'Updated At',
                    dataIndex: 'updated_at',
                    sortable: true,
                    tooltip: 'Updated At',
                    hideable: false
                },
                {
                    header: 'Accepted On',
                    dataIndex: 'accepted_time',
                    sortable: true,
                    tooltip: 'Accepted On',
                    hideable: false
                }/*,
                 {
                 header: 'Scaned On',
                 dataIndex: 'scan_start_time',
                 sortable: true,
                 tooltip: 'Scaned On',
                 hideable: false
                 },
                 {
                 header: 'Last Scaned On',
                 dataIndex: 'last_scan_time',
                 sortable: true,
                 tooltip: 'Last Scaned On',
                 hideable: false
                 },
                 {
                 header: 'Completed On',
                 dataIndex: 'completed_time',
                 sortable: true,
                 tooltip: 'Completed On',
                 hideable: false
                 }*/
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforGBHistory').getStore().load({
                        params: {
                            orderId: orderId,
                            orderType: orderType,
                            current_branch_id: _SESSION.finascop_current_branch_id
                        }
                    });
                }
            }
        });
        return _dispatchgridPanel;
    };
    return{
        Cache: {},
        initCPD: function () {
            var panelId = 'brmcpd_main_panel';
            var branch_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(branch_panel)) {
                branch_panel = cpdGrid(panelId);
                Application.UI.addTab(branch_panel);
                branch_panel.doLayout();
            } else {
                Application.UI.addTab(branch_panel);
            }
            return branch_panel;
        }, saveCPD: function () {

            var cpd_form = Ext.getCmp('cpd_form');

            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.MessageBox.show({
                msg: 'Saving your data, please wait...',
                title: 'Wait...',
                progressText: 'Saving...',
                width: 300,
                wait: true
            });

            cpd_form.getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (cpd_form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true) {
                        Ext.getCmp('cpd_details_window').close();
                        Ext.MessageBox.alert("Success", tmp.msg, function (btn) {
                            Ext.getCmp('brmcpd_main_panel').getStore().reload();
                        });
                    }
                },
                failure: function (cpd_form, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: (obj.error) ? obj.error : obj.errors.reason, buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    } else {
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: MAN_FIELD,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    }
                }
            });
        }, changeStatus: function () {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'changeStatus',
                    cpd_id: Application.CPD.cpd_id,
                    cpd_status: Application.CPD.cpd_status
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.alert("Success", "CPD status changed successfully", function (btn) {

                            Ext.getCmp('brmcpd_main_panel').getStore().reload();
                        });
                    }
                }
            });
        },
        initgodownExecutive: function () {
            var panelId = 'godownExecutive_main_panel';
            var branch_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(branch_panel)) {
                branch_panel = godownExecutiveGrid(panelId);
                Application.UI.addTab(branch_panel);
                branch_panel.doLayout();
            } else {
                Application.UI.addTab(branch_panel);
            }
            return branch_panel;
        },
        initOrder: function (ind) {
            Application.CPD.Markers = [];
            Application.CPD.Markers[ind] = [];
            var main_panel_id = 'main_panel' + ind;
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
        },
        viewItems: function (orderId, orderType) {

            var _addnewItemsWindow = new Ext.Window({
                title: 'Item Details',
                //iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 600,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_bcdItemsGrid(orderId, orderType)],
                fbar: []

            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        }, revokeOrder: function (orderId, orderType) {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'revokeOrder',
                    orderId: orderId,
                    orderType: orderType
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);
                    console.log('tmp', tmp);
                    if (tmp.status == 'ok') {
                        Application.example.msg('Success', tmp.msg);
                        if (orderType == 'cpd') {
                            Ext.getCmp("outward_schedule_grid").getStore().load();
                        } else {
                            Ext.getCmp("gridpanelBrmAssignCartingMainDataview").getStore().load();
                        }

                    } else {
                        Ext.Msg.alert("Error", tmp.error.msg);
                    }

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.message);
                }
            });
        }, scannedBarcodeDetails: function (orderId, orderType, bcod_id) {
            var _addnewItemsWindow = new Ext.Window({
                title: 'Barcode Details',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 600,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_barcodeScannnedDetailsGrid(orderId, orderType, bcod_id)]
            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        }, viewPolledHistory: function (orderId, orderType) {

            var _polledItemsWindow = new Ext.Window({
                title: 'Polled Status',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 900,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_gbHistoryGrid(orderId, orderType)],
                fbar: []

            });
            _polledItemsWindow.doLayout();
            _polledItemsWindow.show();
            _polledItemsWindow.center();
        }
    };
}();
