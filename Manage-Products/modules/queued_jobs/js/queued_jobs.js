Application.QueuedJobs = function () {
///////////////////////////////////////////////////////////////////////////
//-----------------------------------------------------------------------//
//-----------------------------Private Area------------------------------//
//-----------------------------------------------------------------------//
//---------------------------Private Variables---------------------------//
//Configure the Number of records can display in Grid at a time.

    var recs_per_page = 16;
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }
    var modURL = '?module=queued_jobs';


    var loadMask = function () {
        return new Ext.LoadMask(Ext.getCmp('queued_jobs_panel').getEl(),
                {msg: "Please wait..."});
    };


    var queued_jobsStore = function (ind) {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=queued_jobs_store',
            fields: ['booking_no', 'booked_at', 'scheduled_time', 'source', 'destination',
                'type', 'bk_id', 'bk_brk_br_id', 'latitude', 'longitude', 'mapicon', 'brGodownLati', 'brGodownLong'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    var branch = Ext.getCmp('queued_job_search_branch').getValue();
                    var dt = Ext.getCmp('queued_date').getValue();
                    if (!Ext.isEmpty(branch) && !Ext.isEmpty(dt) && Ext.getCmp('queued_date').isValid()) {
                        this.baseParams.br_id = branch;
                        this.baseParams.date = dt.format('Y-m-d');
                    } else {
                        var loadingMask = loadMask();
                        loadingMask.hide();
                        return false;
                    }
                },
                load: function () {
                    var loadingMask = loadMask();
                    loadingMask.hide();
                }
            }
        });
        return store;
    };

    function queued_jobsSelect() {
        return  new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
    }


    var branchstore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: '?module=qugeo&op=getBranch',
            autoLoad: true,
            root: 'data',
             listeners: {
                load: function (thisstore, records, options) {
                    Ext.getCmp('queued_job_search_branch').setValue(_SESSION.finascop_current_branch_id);
                }
            }
        });
        return store;
    };

    var showData = function () {
        var loadingMask = loadMask();
        loadingMask.show();

        var str = Ext.getCmp('queued_jobs_grid').getStore();
        str.removeAll();

        Ext.getCmp('queued_location_map').clearMarkers();
        var branch = Ext.getCmp('queued_job_search_branch').getValue();
        var dt = Ext.getCmp('queued_date').getValue();
        if (!Ext.isEmpty(branch) && !Ext.isEmpty(dt) && Ext.getCmp('queued_date').isValid()) {
            str.baseParams.br_id = branch;
            str.baseParams.date = dt.format('Y-m-d');
            str.load();
        } else {
            Ext.MessageBox.alert("Notification", "Please select valid branch and date");
        }
    };

    var queuedJobsGrid = function () {
        var queued_jobs_store = queued_jobsStore();
        var queued_jobs_select = queued_jobsSelect();
        var queued_jobs_grid = new Ext.grid.GridPanel({
            store: queued_jobs_store,
            region: 'center',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            id: 'queued_jobs_grid',
            columns: [{
                    header: 'Booking No.',
                    sortable: false,
                    dataIndex: 'booking_no'
                }, {
                    header: 'Date',
                    sortable: false,
                    dataIndex: 'booked_at'
                }, {
                    header: 'Scheduled Time',
                    sortable: false,
                    dataIndex: 'scheduled_time'
                }, {
                    header: 'Source',
                    sortable: false,
                    dataIndex: 'source'
                }, {
                    header: 'Destination',
                    sortable: false,
                    dataIndex: 'destination'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [{
                            iconCls: 'icon-add-table',
                            tooltip: 'Show Location',
                            handler: function (grid, rowIndex, colIndex) {

                                var record = grid.getStore().getAt(rowIndex);
                                var loadingMask = loadMask();
                                loadingMask.show();
                                Application.QueuedJobs.Markers = [];


                                var latitude = record.get('latitude');
                                var longitude = record.get('longitude');

                                var action = record.get('type');
                                if (record.get('type') != 'PICKUP') {
                                    var title = record.get('destination');
                                    var vehlatitude = record.get('brGodownLati');
                                    var vehlongitude = record.get('brGodownLong');
                                }
                                else {
                                    var title = record.get('source');
                                    var vehlatitude = record.get('latitude');
                                    var vehlongitude = record.get('longitude');
                                }
                                Ext.getCmp('queued_location_map').clearMarkers();
                                var mymarker = [];
                                mymarker.push({
                                    lat: latitude,
                                    lng: longitude,
                                    marker: {
                                        title: title,
                                        draggable: false
                                    },
                                    icon: record.get('mapicon')/*GMAP_LOCATION_ICON*/
                                });
                                Application.QueuedJobs.Markers = Ext.getCmp('queued_location_map').addMarkers(mymarker, true);
                                Ext.getCmp('queued_location_map').getMap().setZoom(10);

                                Ext.getCmp('queued_vehicle_details').update({});

                                loadingMask.hide();

                            }
                        }
                    ]

                }],
            sm: queued_jobs_select,
            listeners: {
                resize: updatePagination,
                celldblClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var loadingMask = loadMask();
                    loadingMask.show();
                    Application.QueuedJobs.Markers = [];


                    var latitude = record.get('latitude');
                    var longitude = record.get('longitude');

                    var action = record.get('type');
                    if (record.get('type') != 'PICKUP') {
                        var title = record.get('destination');
                        var vehlatitude = record.get('brGodownLati');
                        var vehlongitude = record.get('brGodownLong');
                    }
                    else {
                        var title = record.get('source');
                        var vehlatitude = record.get('latitude');
                        var vehlongitude = record.get('longitude');
                    }
                    Ext.getCmp('queued_location_map').clearMarkers();
                    var mymarker = [];
                    mymarker.push({
                        lat: latitude,
                        lng: longitude,
                        marker: {
                            title: title,
                            draggable: false
                        },
                        icon: record.get('mapicon')/*GMAP_LOCATION_ICON*/
                    });
                    Application.QueuedJobs.Markers = Ext.getCmp('queued_location_map').addMarkers(mymarker, true);
                    Ext.getCmp('queued_location_map').getMap().setZoom(10);

                    Ext.getCmp('queued_vehicle_details').update({});

                    loadingMask.hide();
                }
            },
            tbar: [{html: 'Branch : &nbsp;', id: 'queued_job_branch'}, {
                    xtype: 'combo',
                    mode: 'remote',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstore(),
                    id: 'queued_job_search_branch',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'br_id',
                    name: 'br_id',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('queued_jobs_grid').getStore().removeAll();
                            Ext.getCmp('queued_vehicle_details').update({});
                        }
                    }
                }, {
                    html: '&nbsp; Date: &nbsp;'
                }, {
                    xtype: 'datefield',
                    id: 'queued_date',
                    allowBlank: false,
                    editable: true,
                    format: 'Y-m-d',
                    maxValue: new Date(),
                    value: new Date()
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        showData();
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: queued_jobs_store,
                displayInfo: true,
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true
        });
        return queued_jobs_grid;
    };

    var mapPanel = function (id) {
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
                            Ext.getCmp('max_map').addMarkers(Application.QueuedJobs.Markers, true);
                        }
                    }, 1000);
                }
            }
        };
    };

    var showMap = function (ind) {

        var panel = new Ext.Panel({
            layout: 'border',
            items: mapPanel('max_map')
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

    var queuedPanel = function (id) {
        var panel = new Ext.Panel({
            frame: true,
            id: id,
            border: false,
            layout: 'border',
            title: 'Live Polls',
            iconCls: 'queued_jobs',
            items: [queuedJobsGrid(),
                new Ext.Panel({
                    region: 'east',
                    layout: 'border',
                    split: true,
                    width: 600,
                    tools: [{
                            id: 'maximize',
                            handler: function () {
                                showMap();
                            }
                        }],
                    items: [mapPanel('queued_location_map'),
                        new Ext.Panel({
                            title: 'Vehicle Details',
                            bodyStyle: 'background-color:white;',
                            plane: true,
                            border: false,
                            region: 'south',
                            height: 200,
                            id: 'queued_vehicle_details',
                            tpl: new Ext.XTemplate('<div class="details-outer">',
                                    '<table border="0" width="100%" class="details_view_table" >',
                                    '<tbody><tr><td width="17%">Vehicle No.</td><td> : {v_No}</td>',
                                    '<td width="17%">Vehicle Type</td><td> : {Vehicletypename}</td></tr>',
                                    '<tr><td>Driver Name</td><td> : {DriverName}</td>',
                                    '<td>Latitude</td><td> : {Latitude}</td></tr>',
                                    '<tr><td>Longitude</td><td> : {Longitude}</td>',
                                    '<td>Max. Load</td><td> : {MaxLoad}</td></tr>',
                                    '<tr><td>Current Load</td><td> : {CurrentLoad}</td></tr>',
                                    '</tbody>',
                                    '</table>',
                                    '</div>')
                        })]
                })],
            listeners: {
                afterrender: function () {
                    if (_SESSION.typId == 3 || _SESSION.typId == 4) {
                        Ext.getCmp('queued_job_search_branch').setValue(_SESSION.typdetsid);
                        Ext.getCmp('queued_job_search_branch').hide();
                        Ext.getCmp('queued_job_branch').hide();
                        Ext.getCmp('queued_jobs_grid').getStore().baseParams.br_id = _SESSION.typdetsid;
                    }                    
                }
            }
        });
        return panel;
    };




    return {
        viewQueued: function () {
            Application.QueuedJobs.Markers = [];
            var main_panel_id = 'queued_jobs_panel';
            var main_panel = Ext.getCmp(main_panel_id);
            if (Ext.isEmpty(main_panel)) {
                main_panel = queuedPanel(main_panel_id);
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
                Ext.getCmp('queued_vehicle_details').update({});
            } else {
                Application.UI.addTab(main_panel);
            }
        }
    };
}();