/* QUGEO  */
/*
 * @author Arun P 
 * @updated by Anjitha
 *
 */
Application.Qugeo = function () {
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
    
        var modURL = '?module=qugeo';
        var mod1URL = '?module=qugeo_job_confirmation';
    
        var loadMask = function (ind) {
            return new Ext.LoadMask(Ext.getCmp('main_panel' + ind).getEl(),
                    {msg: "Please wait..."});
        };
    
        var winsize = Ext.getBody().getViewSize();
    
        Math.degreetoradians = function (degrees) {
            return degrees * Math.PI / 180;
        };
    
        function getDegreeMatrix() {
            var mylon = parseFloat(arguments[0]);
            var mylat = parseFloat(arguments[1]);
            var dist = parseFloat(arguments[2]);
            var kmtomile = dist * 0.623;
    
            var lon1 = mylon - kmtomile / Math.abs(Math.cos(Math.degreetoradians(mylat)) * 69);
            var lon2 = mylon + kmtomile / Math.abs(Math.cos(Math.degreetoradians(mylat)) * 69);
            var lat1 = mylat - (kmtomile / 69);
            var lat2 = mylat + (kmtomile / 69);
            // array("kmtomile"=>kmtomile,"lon1"=>lon1,"lon2"=>lon2,"lat1"=>lat1,"lat2"=>lat2);
    
            return [lon1, lon2, lat1, lat2];
        }
    
        //getDegreeMatrix(19.55, 87.99, 5);
    
        var qugeoStore = function (ind) {
            var qugeo_store = new Ext.data.JsonStore({
                url: modURL + '&op=listSchedule',
                fields: ['booking_no', 'booked_at', 'customer', 'source', 'destination',
                    'type', 'quor_id', 'bk_brk_br_id', 'latitude', 'longitude', 'mapicon', 'brGodownLati', 'brGodownLong'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: false,
                autoLoad: false,
                listeners: {
                    beforeload: function () {
                        this.baseParams.ind = ind;
                        this.baseParams.br_id = Ext.getCmp('search_branch' + ind).getValue();
                    }
                }
            });
            return qugeo_store;
        };
        //for selecting single rows in a grid    
        function qugeoSelect() {
            return  new Ext.grid.RowSelectionModel({
                singleSelect: true
            });
        }
        var gridSelectionChanged = function () {
            if (!Ext.isEmpty(Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections())) {
                var ID = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_id;
                var quor_Type = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_Type;
                var quor_Status = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_Status;
                var quor_TransferOrder_Type = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
                var orderMethod = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
                var quor_DeliveryMethodsAllowed = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_DeliveryMethodsAllowed;
                defineMenuinDeliJobs(quor_Type);
    
            }
        };
        function defineMenuinDeliJobs(quor_Type) {
            if (quor_Type >= 2 && quor_Type <= 4) {
    
            }
    
        }
        ;
    
        var assignSchedule = function (ind) {
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
            if (!Ext.isEmpty(sel) && !Ext.isEmpty(Ext.getCmp('hdnVehicleId' + ind).getValue())) {
                Ext.Ajax.request({
                    url: modURL + '&op=saveQugeo',
                    method: 'POST',
                    params: {
                        qugeobk_NO: sel.get('quor_id'),
                        br_id: sel.get('bk_brk_br_id'),
                        hdnVehicleId: Ext.getCmp('hdnVehicleId' + ind).getValue(),
                        type: sel.get('type'),
                        handling_br_id: Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id
                    },
                    success: function (res) {
    
                        var tmp = Ext.decode(res.responseText);
                        if (tmp.success == true) {
                            Ext.Msg.hide();
                            Ext.MessageBox.alert('Notification', tmp.msg, function (btn) {
                                if (btn == 'ok')
                                    showData(ind);
                            });
    
                        } else {
                            Ext.Msg.hide();
                            Ext.MessageBox.alert('Notification', tmp.msg);
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', "Please select a booking and vehicle");
            }
        };
    
        var vehicleStore = function (ind) {
            var store = new Ext.data.JsonStore({
                //proxy: proxy,
                url: modURL + '&op=loadVehicle',
                fields: ['v_ID', 'v_No', 'Latitude', 'Longitude', 'LastLocationDtTm',
                    'DriverName', 'Vehicletypename', 'updated_date', 'vehicle_marker',
                    'MaxLoad', 'CurrentLoad', 'v_MapIcon'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: false,
                autoLoad: false,
                listeners: {
                    beforeload: function () {
                        if (!Ext.isEmpty(Ext.getCmp('search_branch' + ind).getValue()))
                            this.baseParams.br_id = Ext.getCmp('search_branch' + ind).getValue();
                        else
                            return false;
                    }
                }
            });
            return store;
        };
        var vehicleGrid = function (ind) {
            var store = vehicleStore(ind);
            var sm = new Ext.grid.RowSelectionModel({
                singleSelect: true
            });
            var grid = new Ext.grid.GridPanel({
                store: store,
                height: 200,
                viewConfig: {
                    forceFit: true
                },
                id: 'vehicle_grid' + ind,
                columns: [
                    {
                        header: 'V. Reg. No.',
                        dataIndex: 'v_No'
                    }, {
                        header: 'D. Name',
                        dataIndex: 'DriverName'
                    }, {
                        header: 'V. Type',
                        dataIndex: 'Vehicletypename'
                    }, {
                        header: 'Last Updation',
                        dataIndex: 'LastLocationDtTm',
                        renderer: function (val, meta, rec) {
                            var selected_date = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            var show = selected_date[1] + '-' + selected_date[2] + '-' + selected_date[3] + ' ' + selected_date[4] + ':' + selected_date[5] + ':' + selected_date[6];
                            //rec.set('updated_date', show);
                            return show;
                        }
                    }],
                sm: sm,
                listeners: {
                    cellClick: function (grid, rowIndex, columnIndex, e) {
                        var record = grid.getStore().getAt(rowIndex);
                        var vehicle_marker = record.get('vehicle_marker');
    
                        Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
                        Ext.getCmp('vehicle_details' + ind).update(record.data);
    
                        Ext.getCmp('location_map' + ind).animateMarker(Application.Qugeo.Markers[ind][vehicle_marker], 'BOUNCE', 'START');
    
    
                        setTimeout(function () {
                            Ext.getCmp('location_map' + ind).animateMarker(Application.Qugeo.Markers[ind][vehicle_marker], 'BOUNCE', 'STOP');
                        }, 2000);
    
                        setTimeout(function () {
                            Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
                            Ext.getCmp('vehicle_disp' + ind).setValue('Vehicle No. : <b>' + record.get('v_No') + '</b>');
                            Ext.getCmp('hdnVehicleId' + ind).setValue(record.get('v_ID'));
                        }, 50);
                    }
                },
                stripeRows: true
            });
            return grid;
        };
    
        var scheduleGrid = function (ind) {
            var qugeo_store = qugeoStore(ind);
            var qugeo_select = qugeoSelect(ind);
            var qugeo_grid = new Ext.grid.GridPanel({
                store: qugeo_store,
                region: 'center',
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                id: 'schedule_grid_' + ind,
                columns: [{
                        header: 'Booking No.',
                        sortable: false,
                        dataIndex: 'booking_no'
                    }, {
                        header: 'Booked Date',
                        sortable: false,
                        dataIndex: 'booked_at'
                    }, {
                        header: 'Customer',
                        sortable: false,
                        dataIndex: 'customer'
                    }, {
                        header: 'Source',
                        sortable: false,
                        dataIndex: 'source'
                    }, {
                        header: 'Destination',
                        sortable: false,
                        dataIndex: 'destination'
                    }, {
                        header: 'Action Needed',
                        sortable: false,
                        dataIndex: 'type'
                    }],
                sm: qugeo_select,
                listeners: {
                    resize: updatePagination,
                    celldblClick: function (grid, rowIndex, columnIndex, e) {
                        var record = grid.getStore().getAt(rowIndex);
                        var loadingMask = loadMask(ind);
                        loadingMask.show();
                        Application.Qugeo.Markers[ind] = [];
    
                        Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + record.get('booking_no') + '</b>');
                        Ext.getCmp('hdnVehicleId' + ind).setValue('');
                        Ext.getCmp('vehicle_disp' + ind).setValue('');
    
                        Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
    
                        var latitude = record.get('latitude');
                        var longitude = record.get('longitude');
    
                        var action = record.get('type');
                        if (record.get('type') != 'PICKUP') {
                            var title = record.get('destination');
                            var vehlatitude = record.get('brGodownLati');
                            var vehlongitude = record.get('brGodownLong');
                            Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                        } else {
                            var title = record.get('source');
                            var vehlatitude = record.get('latitude');
                            var vehlongitude = record.get('longitude');
                            Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                        }
    
                        Ext.getCmp('location_map' + ind).clearMarkers();
    
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
    
                        var vehicle_store = Ext.getCmp('vehicle_grid' + ind).getStore();
                        vehicle_store.removeAll();
                        vehicle_store.baseParams.bk_ID = record.get('bk_id');
                        vehicle_store.load({
                            params: {
                                longitude: vehlongitude,
                                latitude: vehlatitude,
                                br_id: Ext.getCmp('search_branch' + ind).getValue(),
                                action: action
                            },
                            callback: function () {
                                if (vehicle_store.getCount() > 0) {
                                    var store = vehicle_store.getRange();
                                    /*      var veh_marker = [];*/
                                    var a = 1;
                                    Ext.each(store, function (rec) {
                                        mymarker.push({
                                            lat: rec.get('Latitude'),
                                            lng: rec.get('Longitude'),
                                            marker: {
                                                title: rec.get('v_No'),
                                                draggable: false
                                            },
                                            listeners: {
                                                click: function () {
                                                    var extraInfo = this.extraInfo;
                                                    Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
                                                    Ext.getCmp('vehicle_details' + ind).update(extraInfo);
                                                }
                                            },
                                            icon: rec.get('v_MapIcon'),
                                            extraInfo: rec.data
                                        });
                                        rec.set('vehicle_marker', a);
                                        a++;
                                    });
                                }
    
                                Application.Qugeo.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true);
                                Ext.getCmp('location_map' + ind).getMap().setZoom(10);
                                loadingMask.hide();
                            }
                        });
                    }
                },
                tbar: [{html: 'Branch : &nbsp;', id: 'branch_label' + ind}, {
                        xtype: 'combo',
                        mode: 'remote',
                        typeAhead: true,
                        editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: branchstore(),
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
                        iconCls: 'show',
                        style: "padding-left: 10px;",
                        id: 'id_show' + ind,
                        handler: function () {
                            showData(ind);
                        }
                    }],
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: qugeo_store,
                    displayInfo: true,
                    displayMsg: 'Displaying pages {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                stripeRows: true
            });
            return qugeo_grid;
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
                /* markers: markers,*/
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
                        text: 'Cancel',
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
    
        var failure_reason_store = function () {
    
            var store = new Ext.data.JsonStore({
                fields: ['dls_ID', 'dls_Description'],
                url: modURL + '&op=getFailureReasons',
                method: 'post',
                autoLoad: true,
                root: 'data',
                remoteSort: true,
                listeners: {
                    beforeload: function () {
                        this.baseParams.dls_ID = Ext.getCmp('dls_id').getValue();
                    },
                    load: function (thisstore, records, options) {
                        Ext.getCmp('cb_DelFailureReason').setValue(Ext.getCmp('dls_id').getValue());
                    }
                }
            });
            return store;
        };
    
        var branchstore = function () {
            var store = new Ext.data.JsonStore({
                fields: ['br_Name', 'br_ID'],
                url: modURL + '&op=getBranch',
                method: 'post',
                autoLoad: true,
                root: 'data',
                remoteSort: true,
                listeners: {
                    load: function (thisstore, records, options) {
                        if (!Ext.isEmpty(Ext.getCmp('sj_search_branch'))) {
                            Ext.getCmp('sj_search_branch').setValue(_SESSION.finascop_current_branch_id);
                        }
                        if (!Ext.isEmpty(Ext.getCmp('lv_search_branch'))) {
                            Ext.getCmp('lv_search_branch').setValue(_SESSION.finascop_current_branch_id);
                        }
                        if (!Ext.isEmpty(Ext.getCmp('veh_history_branch'))) {
                            Ext.getCmp('veh_history_branch').setValue(_SESSION.finascop_current_branch_id);
                        }
                        loadScheduleGrid();
                    }
                }
                //totalProperty: 'totalCount' qg_search_branch
            });
            return store;
        };
    
        var getdriverstore = function () {
            var driverstore = new Ext.data.JsonStore({
                fields: ['driver_Name', 'driver_ID'],
                url: '?module=qugeo&op=getDriver',
                autoLoad: true,
                root: 'data',
                listeners: {
                    beforeload: function () {
                        this.baseParams.br_id = Ext.getCmp('dp_search_branch').getValue();
                    }
                }
            });
            return driverstore;
        };
        var showData = function (ind) {
    
            Ext.getCmp('vehicle_grid' + ind).getStore().removeAll();
            Ext.getCmp('schedule_grid_' + ind).getStore().removeAll();
    
            Ext.getCmp('location_map' + ind).clearMarkers();
    
            var branch = Ext.getCmp('search_branch' + ind).getValue();
    
            Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
            Ext.getCmp('schedule_grid_' + ind).getStore().load();
    
            Ext.getCmp('vehicle_grid' + ind).getStore().removeAll();
            Ext.getCmp('vehicle_grid' + ind).getStore().baseParams.br_id = branch;
    
            //Ext.getCmp('vehicle_grid').getStore().load();
    
            Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
            Ext.getCmp('vehicle_details' + ind).update({});
            Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
    
            Ext.getCmp('vehicle_disp' + ind).setValue('');
            Ext.getCmp('booking_disp' + ind).setValue('');
            Ext.getCmp('hdnVehicleId' + ind).setValue('');
        };
        var schedulePanel = function (id, ind, title) {
            var panel = new Ext.Panel({
                frame: true,
                id: id,
                border: false,
                layout: 'border',
                title: title,
                iconCls: (ind == 1) ? 'my-icon146' : 'scheduled',
                items: [scheduleGrid(ind),
                    new Ext.Panel({
                        region: 'east',
                        layout: 'border',
                        split: true,
                        width: 600,
                        tools: [{
                                id: 'maximize',
                                handler: function () {
                                    showMap(ind);
                                }
                            }],
                        items: [mapPanel('location_map' + ind, ind),
                            new Ext.TabPanel({
                                border: false,
                                activeTab: 0,
                                region: 'south',
                                height: 200,
                                deferredRender: false,
                                id: 'vehicle_tab' + ind,
                                items: [{
                                        title: 'Live Vehicles',
                                        layout: 'fit',
                                        items: vehicleGrid(ind)
                                    }, {
                                        title: 'Vehicle Details',
                                        layout: 'fit',
                                        items: new Ext.Panel({
                                            frame: false,
                                            id: 'vehicle_details' + ind,
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
                                        })
                                    }],
                                bbar: [{
                                        xtype: 'hidden',
                                        id: 'hdnVehicleId' + ind
                                    }, {
                                        xtype: 'displayfield',
                                        id: 'booking_disp' + ind
                                    }, {
                                        xtype: 'displayfield', value: '|', style: 'margin:0 8px 0 8px;'
                                    }, {
                                        xtype: 'displayfield',
                                        id: 'vehicle_disp' + ind
                                    }, '->', {
                                        text: 'Assign',
                                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                        iconCls: 'my-icon1',
                                        id: 'save_schedule' + ind,
                                        handler: function () {
                                            assignSchedule(ind);
                                        }
                                    }]})]
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
    
    
        var scheduledJobsStore = function () {
            var store = new Ext.data.JsonStore({
                url: modURL + '&op=listInTransitJobs',
                fields: ['booking_no', 'customer', 'source', 'destination','dest_phone',  
                    'quor_Status', 'quor_Deliverybr_id', 'quor_ItemReturned', 'IsPickup', 'sourcecontact', 'quor_Pickupbr_id','activedriveid',
                    'drivetype', 'quor_id', 'bk_brk_br_id', 'quor_PickupLat', 'quor_PickupLng', 'quor_DeliveryLat', 'quor_DeliveryLng', 'pickupmapicon', 'deliverymapicon', 'dls_DelStatus', 'dls_id', 'quor_TypeName',
                    'quor_ScheduleOpeningTime', 'quor_Type', 'quor_DeliveryName', 'quor_DeliveryPhone', 'driverid', 'session', 'DriverName', {
                        name: 'booked_at',
                        type: 'date',
                        dateFormat: 'd-m-Y'
                    }, 'orderid', 'date', 'quor_RefNo', 'From', 'To', 'NetAmt', 'Paymode', 'HasReCalculatedCharges', 'ReCalculatedCharges', 'ReCalculcationPaymentType', 'IsPickup', 'status', 'IsEditable', 'v_no', 'BkLastEditTime',
                    'status_time', 'quor_AmountCollectible', 'quor_ItemReturned'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function () {
                        this.baseParams.br_id = Ext.getCmp('sj_search_branch').getValue();
                        this.baseParams.ind = 2;
                    }
                }
            });
            return store;
        };
        //for selecting single rows in a grid    
        function sjSelect() {
            return  new Ext.grid.RowSelectionModel({
                singleSelect: true
            });
        }
        function sjSelectIn() {
            return  new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChanged
                }
            });
        }
    
        var incomeDivisionDetailsStore = function (bk_id) {
            var store = new Ext.data.JsonStore({
                url: modURL + '&op=incomeDivisionDetails',
                fields: ['indi_type', 'indi_percentage', 'indi_amount', 'indi_ispaid', 'indi_payable_to', 'indi_paid_to', 'indi_paid_on', 'indi_paid_by'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: false,
                autoLoad: false
                        // listeners: {
                        //     beforeload: function () {
                        //         this.baseParams.bk_id = bk_id;
                        //     }
                        // }
    
            });
            store.load({
                params: {
                    bk_id: bk_id
                }
            });
            return store;
        };
    
        var incomeDivisionDetailsGrid = function (bk_id) {
    
            var store = incomeDivisionDetailsStore(bk_id);
            var sm = new Ext.grid.RowSelectionModel({
                singleSelect: true,
                loadMask: true
            });
    
            var grid = new Ext.grid.GridPanel({
                store: store,
                title: 'Income Division Details',
                region: 'center',
                iconCls: '',
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                id: 'income_division_details_grid',
                columns: [{
                        header: 'Type',
                        sortable: false,
                        dataIndex: 'indi_type'
                    }, {
                        header: 'Percentage',
                        sortable: false,
                        dataIndex: 'indi_percentage'
                    }, {
                        header: 'Amount',
                        sortable: false,
                        align: 'right',
                        dataIndex: 'indi_amount'
                    }, {
                        header: 'Status',
                        sortable: false,
                        dataIndex: 'indi_ispaid'
                    }, {
                        header: 'Payable to',
                        sortable: false,
                        width: 160,
                        dataIndex: 'indi_payable_to'
                    }, {
                        header: 'Paid To',
                        sortable: false,
                        dataIndex: 'indi_paid_to'
                    }, {
                        header: 'Paid On',
                        sortable: false,
                        dataIndex: 'indi_paid_on'
                    }, {
                        header: 'Paid By',
                        sortable: false,
                        dataIndex: 'indi_paid_by'
                    }],
                sm: sm,
                listeners: {
                    resize: updatePagination,
                    afterrender: function () {
                        //store.load();
                    }
                },
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: store,
                    displayInfo: true,
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                stripeRows: true
            });
            return grid;
    
        }
    
        var showIncomeDivisionDetails = function (bk_id) {
            var incomeDivDetailsGrid = incomeDivisionDetailsGrid(bk_id);
            var panel = new Ext.Panel({
                layout: 'border',
                items: [incomeDivDetailsGrid]
            });
    
            var income_div_details_win = new Ext.Window({
                width: winsize.width * 0.6,
                height: winsize.height * 0.6,
                plain: true,
                modal: true,
                frame: true,
                constrainHeader: true,
                layout: 'fit',
                items: panel,
                buttons: [{
                        text: 'Cancel',
                        tabIndex: '10',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            income_div_details_win.close();
                        }
                    }]
            });
    
            income_div_details_win.doLayout();
            income_div_details_win.show(this);
            income_div_details_win.center();
    
        };
    
        var incomeDivisionAction = function () {
            var action = new Ext.ux.grid.RowActions({
                hideMode: 'display',
                header: 'Actions',
                // tooltip: '',
                autoWidth: false,
                width: 40,
                actions: [{
                        sortable: false,
                        tooltip: 'View',
                        iconCls: 'view',
                        callback: function (grid, rec, row, col) {
                            showIncomeDivisionDetails(rec.get('booking_id'));
    
                        }
                    }]
            });
            return action;
        }
    
        var incomeDivisionStore = function () {
            var store = new Ext.data.JsonStore({
                url: modURL + '&op=listIncomeDivBooking',
                fields: ['booking_id', 'booking_no', 'booked_at', 'calculated_on'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: false,
                autoLoad: false,
                listeners: {
                    beforeload: function () {
                        //this.baseParams.br_id = Ext.getCmp('sj_search_branch').getValue();
                        //this.baseParams.ind = 2;
                    }
                }
            });
            return store;
        };
    
        var IncomeDivisionGrid = function () {
            var store = incomeDivisionStore();
            var sm = new Ext.grid.RowSelectionModel({
                singleSelect: true
            });
            var actionColumn = incomeDivisionAction();
            var grid = new Ext.grid.GridPanel({
                store: store,
                title: 'Income Division',
                iconCls: 'incomeDiv',
                plugins: [actionColumn],
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                id: 'income_division_grid',
                columns: [{
                        header: 'Booking No.',
                        sortable: false,
                        dataIndex: 'booking_no'
                    }, {
                        header: 'Booked Date',
                        sortable: false,
                        dataIndex: 'booked_at'
                    }, {
                        header: 'Calculated On',
                        sortable: false,
                        dataIndex: 'calculated_on'
                    }, actionColumn],
                sm: sm,
                listeners: {
                    resize: updatePagination,
                    afterrender: function () {
                    }
                },
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: store,
                    displayInfo: true,
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                tbar: [{
                        html: 'Branch : &nbsp;',
                        id: 'qg_branch_label'
                    }, {
                        xtype: 'combo',
                        mode: 'remote',
                        typeAhead: true,
                        editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: branchstore(),
                        id: 'qg_search_branch',
                        triggerAction: 'all',
                        displayField: 'br_Name',
                        allowBlank: false,
                        valueField: 'br_ID',
                        hiddenName: 'br_id',
                        name: 'br_id',
                        minChars: 1
                    }, {html: '&nbsp;Date From :&nbsp;'}, {
                        xtype: 'datefield',
                        name: 'date_from',
                        id: 'date_from',
                        format: 'Y-m-d',
                        editable: false,
                        maxValue: new Date(),
                        anchor: '98%',
                        listeners: {
                            select: function () {
    
                            }
                        }
                    }, {html: '&nbsp;To :&nbsp;'}, {
                        xtype: 'datefield',
                        name: 'date_to',
                        id: 'date_to',
                        format: 'Y-m-d',
                        editable: false,
                        maxValue: new Date(),
                        anchor: '90%',
                        listeners: {
                            select: function () {
    
                            }
                        }
                    }, {
                        xtype: 'button',
                        text: 'Show',
                        iconCls: 'show',
                        style: "padding-left: 10px;",
                        id: 'qg_id_show',
                        handler: function () {
                            var updateStore = true;
                            if (Ext.isEmpty(Ext.getCmp('date_to').getValue())) {
                                Ext.Msg.confirm('Notification', 'Select \'To Date\'');
                                updateStore = false;
                            } else {
                                store.baseParams.to_date = Ext.getCmp('date_to').getValue().format('Y-m-d');
                                ;
                            }
    
                            if (Ext.isEmpty(Ext.getCmp('date_from').getValue())) {
                                Ext.Msg.confirm('Notification', 'Select \'From Date\'.');
                                updateStore = false;
                            } else {
                                store.baseParams.from_date = Ext.getCmp('date_from').getValue().format('Y-m-d');
                                ;
                            }
    
                            if (Ext.isEmpty(Ext.getCmp('qg_search_branch').getValue())) {
                                Ext.Msg.confirm('Notification', 'Select Branch.');
                                updateStore = false;
                            } else {
                                store.baseParams.br_id = Ext.getCmp('qg_search_branch').getValue();
                            }
    
                            if (updateStore) {
                                store.removeAll();
                                store.load();
                            }
    
                        }
                    }],
                stripeRows: true
            });
            return grid;
        };
    
    
        var driverPaymentStore = new Ext.data.JsonStore({
            url: modURL + '&op=listDriverPayment',
            fields: ['booking_id', 'booking_no', 'booking_date', 'net_amt', 'percentage', 'amount'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    this.baseParams.br_id = Ext.getCmp('dp_search_branch').getValue();
                }
            }
        });
    
        var DriverPaymentGrid = function () {
            var driverstore = getdriverstore();
            var sm = new Ext.grid.CheckboxSelectionModel({
                multiSelect: true,
                loadMask: true,
            });
    
            var grid = new Ext.grid.GridPanel({
                store: driverPaymentStore,
                title: 'Driver Payment',
                iconCls: 'drv_pay',
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                id: 'driver_payment_grid',
                cm: new Ext.grid.ColumnModel([sm, {
                        header: 'Booking No.',
                        sortable: false,
                        dataIndex: 'booking_no'
                    }, {
                        header: 'Booking Date',
                        sortable: false,
                        dataIndex: 'booking_date'
                    }, {
                        header: 'Net Amount',
                        sortable: false,
                        align: 'right',
                        dataIndex: 'net_amt'
                    }, {
                        header: 'Percentage',
                        sortable: false,
                        align: 'right',
                        dataIndex: 'percentage'
                    }, {
                        header: 'Amount',
                        sortable: false,
                        align: 'right',
                        dataIndex: 'amount'
                    }]),
                sm: sm,
                listeners: {
                    resize: updatePagination,
                    afterrender: function () {
                    }
                },
                bbar: [new Ext.PagingToolbar({
                        pageSize: recs_per_page,
                        store: driverPaymentStore,
                        displayInfo: true,
                        displayMsg: 'Displaying records {0} - {1} of {2}',
                        emptyMsg: "No pages to display"
                    })],
                tbar: [{
                        html: 'Branch : &nbsp;',
                        id: 'dp_branch_label'
                    }, {
                        xtype: 'combo',
                        mode: 'remote',
                        typeAhead: true,
                        editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: branchstore(),
                        id: 'dp_search_branch',
                        triggerAction: 'all',
                        displayField: 'br_Name',
                        allowBlank: false,
                        valueField: 'br_ID',
                        hiddenName: 'br_ID',
                        name: 'br_id',
                        minChars: 1,
                        listeners: {
                            select: function (combo, record, index) {
                                Ext.getCmp('dp_search_driver').setValue("");
                                driverstore.baseParams.br_id = record.get('br_ID');
                                driverstore.removeAll();
                                driverstore.load();
                            }
                        }
                    }, {
                        html: '&nbsp;Driver : &nbsp;',
                        id: 'dp_driver_label'
                    }, {
                        xtype: 'combo',
                        mode: 'remote',
                        typeAhead: true,
                        editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: driverstore,
                        id: 'dp_search_driver',
                        triggerAction: 'all',
                        displayField: 'driver_Name',
                        allowBlank: true,
                        valueField: 'driver_ID',
                        hiddenName: 'driver_id',
                        name: 'driver_id',
                        minChars: 1,
                        listeners: {
                            beforequery: function (combo) {
                                if (Ext.isEmpty(Ext.getCmp('dp_search_branch').getValue())) {
                                    Ext.Msg.alert('Notification', 'Please select a branch.', function (btn) {
                                        if (btn == 'ok') {
    
    
                                        }
                                    });
                                }
                            }
                        }
                    }, {html: '&nbsp;Date From :&nbsp;'}, {
                        xtype: 'datefield',
                        name: 'dp_date_from',
                        id: 'dp_date_from',
                        format: 'Y-m-d',
                        editable: false,
                        allowBlank: false,
                        maxValue: new Date(),
                        anchor: '98%',
                        listeners: {
                            select: function () {
    
                            }
                        }
                    }, {html: '&nbsp;To :&nbsp;'}, {
                        xtype: 'datefield',
                        name: 'dp_date_to',
                        id: 'dp_date_to',
                        format: 'Y-m-d',
                        editable: false,
                        allowBlank: false,
                        maxValue: new Date(),
                        anchor: '90%',
                        listeners: {
                            select: function () {
    
                            }
                        }
                    }, {
                        xtype: 'button',
                        text: 'Show',
                        iconCls: 'show',
                        style: "padding-left: 10px;",
                        id: 'dp_id_show',
                        handler: function () {
    
    
                        }
                    }, {
                        xtype: 'button',
                        text: 'Save',
                        iconCls: 'my-icon5',
                        style: "padding-left: 10px;",
                        id: 'dp_id_save',
                        handler: function () {
    
    
                        }
                    }],
                stripeRows: true
            });
            return grid;
        };
    
    
        var scheduledJobsGrid = function () {
            //var intransitGridActionColumnMenu = intransitGridActionMenu();
            var store = scheduledJobsStore();
            var select = sjSelectIn();
            var intransitJobsFilter = new Ext.ux.grid.GridFilters({
                filters: [{
                        type: 'string',
                        dataIndex: 'booking_no'
                    }, {
                        type: 'string',
                        dataIndex: 'quor_PickupName'
                    }, {
                        type: 'string',
                        dataIndex: 'quor_PickupPhone'
                    },
                    {
                        type: 'list',
                        options: ['PICKUP - POLLED', 'PICKUP - POLL REJECTED', 'PICKUP - POLL NO RESPONSE', 'PICKUP - HOME BRANCH FLAGGED', 'PICKUP - DIRECT DELIVERY FLAGGED', 'PICKUP - HOME BRANCH PICKED UP',
                            'PICKUP - DIRECT DELIVERY PICKED UP', 'DELIVERY - POLLED', 'DELIVERY - POLL REJECTED', 'DELIVERY - POLL NO RESPONSE', 'PICKUP FAILED - DOOR LOCKED', 'PICKUP FAILED - ADDRESS NOT FOUND',
                            'PICKUP FAILED - ITEM NOT READY', 'DELIVERY - DELIVERED BUT NOT CONFIRMED', 'DESPATCHED - VIA CROSS BOOKING'],
                        phpMode: true,
                        dataIndex: 'dls_DelStatus'
                    }]
            });
            intransitJobsFilter.remote = true;
            intransitJobsFilter.autoReload = true;
            var grid = new Ext.grid.GridPanel({
                store: store,
                title: 'In Transit',
                iconCls: 'scheduled',
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                id: 'scheduled_jobs_grid',
                plugins: [intransitJobsFilter],
                columns: [{
                        header: 'Order No.',
                        sortable: true,
                        dataIndex: 'booking_no'
                    }, {
                        header: 'Order Date',
                        sortable: true,
                        dataIndex: 'booked_at',
                        renderer: function (value, metadata, record) {
                            dateret = Ext.util.Format.date(value, 'd-m-Y');
                            return dateret;
                        }
                    }, {
                        header: 'Consignee',
                        sortable: true,
                        dataIndex: 'customer',
                        hidden: true
                    }, {
                        header: 'Consigner',
                        sortable: true,
                        dataIndex: 'source',
                        hidden: true
                    }, {
                        header: 'Vehicle No.',
                        sortable: true,
                        dataIndex: 'v_no',
                        hideable: true//Driver Name, Vehicle Number, Pay Mode, Amount Collected
                    }, {
                        header: 'Session',
                        sortable: true,
                        dataIndex: 'session'
                    }, {
                        header: 'Pay Mode',
                        sortable: true,
                        dataIndex: 'Paymode'
                    }, {
                        header: 'Amount',
                        sortable: true,
                        dataIndex: 'NetAmt'
                    },
                    {
                        header: 'Type',
                        sortable: true,
                        dataIndex: 'quor_TypeName'
                    }, {
                        header: 'Status',
                        sortable: true,
                        dataIndex: 'dls_DelStatus'
                    },{
                        header: 'Destination Name',
                        sortable: true,
                        dataIndex: 'destination'
                    },//'dest_phone'
                    {
                        header: 'Destination Phone',
                        sortable: true,
                        dataIndex: 'dest_phone'
                    },
    //                {
    //                    header: 'Contact Name',
    //                    sortable: true,
    //                    dataIndex: 'quor_PickupName'
    //                }, {
    //                    header: 'Contact NO',
    //                    sortable: true,
    //                    dataIndex: 'quor_PickupPhone'
    //                }, 
                    {
                        xtype: 'actioncolumn',
                        //header: 'Action',
                        hideable: true,
                        iconCls: 'downarrow',
                        tooltip: 'Choose Actions',
                        listeners: {
                            click: function (a, grid, rowindex, e) {
                                var record = grid.store.getAt(rowindex);
                                grid.getSelectionModel().selectRow(rowindex);
                                intransitGridActionMenu.showAt(e.getXY());
                                //action
                            }
                        }
                    }
    //                {
    //                    xtype: 'actioncolumn',
    //                    hideable: false,
    //                    //iconCls: 'downarrow',
    //                    //tooltip: 'Choose Actions',
    //                    width: 40,
    //                    items: [/* <?php if (user_access("qugeo", "getMarkers")) { ?> */{
    //                            sortable: false,
    //                            getClass: function (v, meta, rec) {
    //                                // return 'my-icon109';
    //                                return 'my-icon10';
    //                            },
    //                            tooltip: 'View Jobs and Route',
    //                            handler: function (grid, rowIndex, colIndex) {
    //                                var record = grid.store.getAt(rowIndex);
    //                                Application.Qugeo.JobsRoute(record.get('quor_id'), 'Order', 0, record.get('booking_no'), record.get('driverid'));
    //                            }
    //                        }/* <?php } ?> */, {
    //                            sortable: false,
    //                            getClass: function (v, meta, rec) {
    //                                // return 'my-icon109';
    //                                if ((rec.get('quor_Type') >= 2) && (rec.get('quor_Type') <= 4)) {
    //                                    return 'delivered';
    //                                } else {
    //                                    return 'hideicon';
    //                                }
    //
    //                            },
    //                            tooltip: 'Delivery Update',
    //                            handler: function (grid, rowIndex, colIndex) {
    //                                var record = grid.store.getAt(rowIndex);
    //                                Application.Qugeo.MarkDelivered(record.get('quor_id'), record.get('Paymode'));
    //                            }
    //                        }, {
    //                            sortable: false,
    //                            getClass: function (v, meta, rec) {
    //                                // return 'my-icon109';
    //                                if ((rec.get('quor_Type') == 1)) {
    //                                    return 'application_view_detail';
    //                                } else {
    //                                    return 'hideicon';
    //                                }
    //
    //                            },
    //                            tooltip: 'Delivery Update',
    //                            handler: function (grid, rowIndex, colIndex) {
    //                                var record = grid.store.getAt(rowIndex);
    //                                Application.Qugeo.verify_btn_window(record);
    //                            }
    //                        }]
    //
    //                }
                ],
                sm: select,
                listeners: {
                    resize: updatePagination,
                    afterrender: function () {
                        if (_SESSION.typId == 3 || _SESSION.typId == 4) {
                            Ext.getCmp('sj_search_branch').setValue(_SESSION.typdetsid);
                            Ext.getCmp('sj_search_branch').hide();
                            Ext.getCmp('sj_branch_label').hide();
                            Ext.getCmp('scheduled_jobs_grid').getStore().baseParams.br_id = _SESSION.typdetsid;
                        }
                    }
                },
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: store,
                    displayInfo: true,
                    displayMsg: 'Displaying pages {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                tbar: [{
                        html: 'Branch : &nbsp;',
                        id: 'sj_branch_label'
                    }, {
                        xtype: 'combo',
                        mode: 'remote',
                        typeAhead: true,
                        editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: branchstore(),
                        id: 'sj_search_branch',
                        triggerAction: 'all',
                        displayField: 'br_Name',
                        allowBlank: false,
                        valueField: 'br_ID',
                        hiddenName: 'br_id',
                        name: 'br_id',
                        minChars: 1
                    }, {
                        xtype: 'button',
                        text: 'Show',
                        iconCls: 'show',
                        style: "padding-left: 10px;",
                        id: 'sj_id_show',
                        handler: function () {
                            loadScheduleGrid();
                        }
                    }],
                stripeRows: true
            });
            return grid;
        };
        var intransitGridActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "View Jobs and Route",
                    handler: function () {
                        //var Order = tmp.Order;
                        var quor_id = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_id;
                        var booking_no = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.booking_no;
                        var driverid = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.driverid;
                        console.log('quor_id', quor_id);
                        console.log('booking_no', booking_no);
                        console.log('driverid', driverid);
                        if (!Ext.isEmpty(quor_id) && !Ext.isEmpty(booking_no) && !Ext.isEmpty(driverid)) {
                            Application.Qugeo.JobsRoute(quor_id, 'Order', 0, booking_no, driverid);
                        } else {
                            Ext.MessageBox.alert('Notification', 'Not able to load');
                        }
    
                    }
                }, {
                    text: "Delivery Update",
                    handler: function () {
                        var record = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0];
                        var quor_Type = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_Type;
                        var quor_id = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_id;
                        var Paymode = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.Paymode;
                        if (quor_Type == 1) {
                            Application.Qugeo.verify_btn_window(record);
                        }
                        if ((quor_Type >= 2) && (quor_Type <= 6)) {
                            Application.Qugeo.MarkDelivered(quor_id, Paymode);
                        }
    
                    }
    
                }]
        });
    
        /*var intransitGridActionMenu = new Ext.menu.Menu({
         items: [{
         text: "View Jobs & Route",
         handler: function () {
         var record = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0];
         //var quor_id = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.quor_id;
         //var booking_no = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.booking_no;
         //var driverid = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0].data.driverid;
         Application.Qugeo.JobsRoute(record.get('quor_id'), 'Order', 0, record.get('booking_no'), record.get('driverid'));
         }
         }, {
         text: "Delivery Update",
         handler: function () {
         var record = Ext.getCmp('scheduled_jobs_grid').getSelectionModel().getSelections()[0];
         Application.Qugeo.MarkDelivered(record.get('quor_id'), record.get('Paymode'));
         }
         }]
         });*/
    
        var loadScheduleGrid = function () {
            var branch;
            if (!Ext.isEmpty(Ext.getCmp('scheduled_jobs_grid'))) {
                Ext.getCmp('scheduled_jobs_grid').getStore().removeAll();
            }
            if (!Ext.isEmpty(Ext.getCmp('sj_search_branch'))) {
                branch = Ext.getCmp('sj_search_branch').getValue();
            }
            if (!Ext.isEmpty(Ext.getCmp('lv_search_branch'))) {
                branch = Ext.getCmp('lv_search_branch').getValue();
            }
            if (!Ext.isEmpty(Ext.getCmp('veh_history_branch'))) {
                branch = Ext.getCmp('veh_history_branch').getValue();
            }
            //var branch = Ext.getCmp('sj_search_branch').getValue();
            if (!Ext.isEmpty(Ext.getCmp('scheduled_jobs_grid'))) {
                Ext.getCmp('scheduled_jobs_grid').getStore().baseParams.br_id = branch;
                Ext.getCmp('scheduled_jobs_grid').getStore().load();
            }
    
        };
    
        var liveVehicleStore = function () {
            var store = new Ext.data.JsonStore({
                url: modURL + '&op=listLiveVehicles',
                fields: ['vehid', 'vehno', 'drivername', 'mobno', 'logintime', 'logintime', 'assgwt',
                    'currwt', 'assgvol', 'currvol', 'kmcovered', 'totjobs', 'jobscompleted', 'apikey', 'DriverId'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                listeners: {
                    beforeload: function () {
                        var live_vehicles_grid = Ext.getCmp('live_vehicles_grid').getStore();
                        live_vehicles_grid.removeAll();
                        var branch = Ext.getCmp('lv_search_branch').getValue();
                        var live_veh_date = Ext.getCmp('live_veh_date').getValue();
                        if (!Ext.isEmpty(live_veh_date) && !Ext.isEmpty(branch) && Ext.getCmp('lv_search_branch').isValid()
                                && Ext.getCmp('live_veh_date').isValid()) {
                            live_vehicles_grid.baseParams.br_id = branch;
                            live_vehicles_grid.baseParams.date = live_veh_date.format('Y-m-d');
                        } else {
                            return false;
                        }
                    }
                }
            });
            return store;
        };
    
    
    
        var liveVehiclesGrid = function () {
            var store = liveVehicleStore();
            var select = sjSelect();
            var grid = new Ext.grid.GridPanel({
                store: store,
                title: 'Live Vehicles',
                iconCls: 'live_vehicles',
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                id: 'live_vehicles_grid',
                columns: [{
                        header: 'Vehicle No.',
                        sortable: false,
                        dataIndex: 'vehno'
                    }, {
                        header: 'Driver',
                        sortable: true,
                        dataIndex: 'drivername'
                    }, {
                        header: 'Mobile',
                        sortable: true,
                        dataIndex: 'mobno'
                    },
                    {
                        header: 'Login Time',
                        sortable: true,
                        dataIndex: 'logintime',
                        // renderer: function (val) {
                        //     var login_time = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                        //     return login_time[1] + '-' + login_time[2] + '-' + login_time[3] + ' ' + login_time[4] + ':' + login_time[5] + ':' + login_time[6];
                        // }
                    }, {
                        header: 'Assigned Weight',
                        sortable: true,
                        dataIndex: 'assgwt',
                        align: 'right'
                    }, {
                        header: 'Current Weight',
                        sortable: true,
                        dataIndex: 'currwt',
                        align: 'right'
                    },
                    {
                        header: 'Assigned Volume',
                        sortable: true,
                        dataIndex: 'assgvol',
                        align: 'right'
                    }, {
                        header: 'Current Volume',
                        sortable: true,
                        dataIndex: 'currvol',
                        align: 'right'
                    }, {
                        header: 'KM. Covered',
                        sortable: true,
                        dataIndex: 'kmcovered',
                        align: 'right'
                    }, {
                        header: 'Total Jobs',
                        sortable: true,
                        dataIndex: 'totjobs',
                        align: 'right'
                    },
                    {
                        header: 'Jobs Completed',
                        sortable: true,
                        dataIndex: 'jobscompleted',
                        align: 'right'
                    }, {
                        xtype: 'actioncolumn',
                        hideable: false,
                        width: 80,
                        items: [/* <?php if (user_access("qugeo", "getMarkers")) { ?> */{
                                sortable: false,
                                getClass: function (v, meta, rec) {
                                    // return 'my-icon109';
                                    return 'my-icon10';
                                },
                                tooltip: 'View Jobs and Route',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    Application.Qugeo.JobsRoute(record.get('vehid'), 'Vehicle', 0, record.get('vehno'));
                                }
                            }, /* <?php } ?> */ /* <?php if (user_access("qugeo", "forceLogout")) { ?> */ {
                                sortable: false,
                                getClass: function (v, meta, rec) {
                                    return 'force_logout';
                                },
                                tooltip: 'Force Logout',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    Application.Qugeo.ForceLogout(record.get('vehid'), record.get('DriverId'));
                                }
                            }, /* <?php } ?> */ {}]
                    }],
                sm: select,
                listeners: {
                    resize: updatePagination,
                    afterrender: function () {
                        if (_SESSION.typId == 3 || _SESSION.typId == 4) {
                            Ext.getCmp('lv_search_branch').setValue(_SESSION.typdetsid);
                            Ext.getCmp('lv_search_branch').hide();
                            Ext.getCmp('lv_branch_label').hide();
                            Ext.getCmp('live_vehicles_grid').getStore().baseParams.br_id = _SESSION.typdetsid;
                        }
                    }
                },
                tbar: [{html: 'Branch : &nbsp;',
                        id: 'lv_branch_label'
                    }, {
                        xtype: 'combo',
                        mode: 'remote',
                        typeAhead: true, editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: branchstore(),
                        id: 'lv_search_branch',
                        triggerAction: 'all',
                        displayField: 'br_Name',
                        allowBlank: false,
                        valueField: 'br_ID',
                        hiddenName: 'br_ID',
                        name: 'br_id',
                        minChars: 1
                    },
                    {
                        html: '&nbsp; Date: &nbsp;'
                    }, {
                        xtype: 'datefield',
                        id: 'live_veh_date',
                        allowBlank: false,
                        editable: true,
                        format: 'Y-m-d',
                        maxValue: new Date(),
                        value: new Date()
                    },
                    {
                        xtype: 'button',
                        text: 'Show',
                        iconCls: 'show',
                        style: "padding-left: 10px;",
                        id: 'lv_id_show',
                        handler: function () {
                            loadLiveVehiclesGrid();
                        }
                    }],
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: store,
                    displayInfo: true,
                    displayMsg: 'Displaying pages {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                stripeRows: true
            });
            return grid;
        };
    
        var loadLiveVehiclesGrid = function () {
            var live_vehicles_grid = Ext.getCmp('live_vehicles_grid').getStore();
            live_vehicles_grid.removeAll();
            var branch = Ext.getCmp('lv_search_branch').getValue();
            var live_veh_date = Ext.getCmp('live_veh_date').getValue();
            if (!Ext.isEmpty(live_veh_date) && !Ext.isEmpty(branch) && Ext.getCmp('lv_search_branch').isValid()
                    && Ext.getCmp('live_veh_date').isValid()) {
                var loadingMask = new Ext.LoadMask(Ext.getCmp('live_vehicles_grid').el, {msg: "Please wait..."});
                loadingMask.show();
                live_vehicles_grid.baseParams.br_id = branch;
                live_vehicles_grid.baseParams.date = live_veh_date.format('Y-m-d');
                live_vehicles_grid.load({
                    callback: function () {
                        loadingMask.hide();
                    }
                });
            } else {
                Ext.MessageBox.alert("Notification", "Please select valid branch and date");
            }
        };
    
        var snapRoad = function () {
            var loadingMask = new Ext.LoadMask(Ext.getCmp('map_win').el, {msg: "Please wait..."});
            loadingMask.show();
            Ext.Msg.show({
                title: 'Loading!',
                msg: '',
                progressText: 'Please Wait...',
                width: 300,
                progress: true,
                closable: false,
                wait: true
            });
            Ext.Ajax.request({
                url: modURL + '&op=snap_road',
                method: 'POST',
                params: {
                    vehapikey: Application.Qugeo.apikey
                },
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        loadingMask.hide();
                        var job_route_map = Ext.getCmp('job_route_map');
    
                        var mymarker = [];
                        var bounds = {};
    
    
                        var data = tmp.polylines;
                        var marker_icon = job_route_map.getDotMarkerIcon('red');
                        Ext.each(data, function (item) {
                            var title = item.tstamp.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            var app_time = item.apptime.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            var new_title = app_time[4] + ':' + app_time[5] + ':' + app_time[6] + '  [' + title[4] + ':' + title[5] + ':' + title[6] + ']';
                            mymarker.push({
                                lat: item.Latitude,
                                lng: item.Longitude,
                                title: new_title
                            });
                        });
    
                        Application.Qugeo.SnapRoadPolyline = job_route_map.addPolyline(mymarker, {
                            strokeColor: '#0000ff',
                            strokeOpacity: 1.0,
                            strokeWeight: 2,
                            fillColor: '#0000ff',
                            fillOpacity: 1
                        }, true, bounds, null, true, marker_icon);
                    }
                }
            });
        };
    
        var drawActualRoute = function (op) {
            var loadingMask = new Ext.LoadMask(Ext.getCmp('map_win').el, {msg: "Please wait..."});
            loadingMask.show();
            Ext.Ajax.request({
                url: modURL + '&op=' + op,
                method: 'POST',
                params: {
                    vehapikey: Application.Qugeo.apikey
                },
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        loadingMask.hide();
                        var job_route_map = Ext.getCmp('job_route_map');
    
                        var mymarker = [];
                        var bounds = {};
    
    
                        var data = tmp.polylines;
                        var marker_icon = job_route_map.getDotMarkerIcon('red');
                        Ext.each(data, function (item) {
                            var title = item.tstamp.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            var app_time = item.apptime.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            var new_title = app_time[4] + ':' + app_time[5] + ':' + app_time[6] + '  [' + title[4] + ':' + title[5] + ':' + title[6] + ']';
                            mymarker.push({
                                lat: item.Latitude,
                                lng: item.Longitude,
                                title: new_title
                            });
                        });
    
                        Application.Qugeo.ActualRoutePolyline = job_route_map.addPolyline(mymarker, {
                            strokeColor: '#0000ff',
                            strokeOpacity: 1.0,
                            strokeWeight: 2,
                            fillColor: '#0000ff',
                            fillOpacity: 1
                        }, true, bounds, null, false, marker_icon);
    
                    }
                }
            });
        };
    
        var drawScheduledRoute = function (op) {
            var loadingMask = new Ext.LoadMask(Ext.getCmp('map_win').el, {msg: "Please wait..."});
            loadingMask.show();
            Ext.Ajax.request({
                url: modURL + '&op=' + op,
                method: 'POST',
                params: {
                    vehapikey: Application.Qugeo.apikey
                },
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        loadingMask.hide();
                        var points = tmp.routelocations;
                        if (!Ext.isEmpty(points)) {
                            var orgs = new google.maps.LatLng(points[0].Latitude, points[0].Longitude);
    
                            var len = points.length;
                            var dests = points[len - 1];
                            var destination = new google.maps.LatLng(dests.Latitude, dests.Longitude);
    
                            var waypts = [];
                            var index = 0;
                            Ext.each(points, function (item) {
                                if (index > 0 && index < (points.length - 1)) {
                                    waypts.push({location: new google.maps.LatLng(item.Latitude, item.Longitude)});
                                }
                                index++;
                            });
    
                            var job_route_map = Ext.getCmp('job_route_map');
                            Application.Qugeo.ScheduledRouteDirections = job_route_map.addDirections(orgs, destination, waypts);
                        }
                    }
                }
            });
    
        };
    
        function exportToExcel() {
            document.location.href = modURL + '&op=export_toexcel';
        }
    
        function downloadSnapGrid() {
            var routestore = Ext.getCmp("snap_road_grid").getStore().getCount();
            if (routestore > 0) {
                exportToExcel();
            } else {
                Ext.MessageBox.alert('Notification', "There is no Data to export", function () {
    
                });
            }
        }
    
        function downloadRouteGrid() {
    
            var routestore = Ext.getCmp("").getStore().getCount();
            if (routestore > 0) {
                exportToExcel();
            } else {
                Ext.MessageBox.alert('Notification', "There is no Data to export", function () {
    
                });
            }
    
        }
    
        var createRouteStore = function () {
            var route_store = new Ext.data.JsonStore({
                url: '?module=qugeo&op=actroute',
                fields: ['Latitude', 'Longitude', 'tstamp', 'apptime', 'disttravled', 'provider', 'version'],
                root: 'polylines',
                id: 'routestore',
                totalProperty: 'totalcount',
                localSort: true
            });
            return route_store;
        };
    
        var loadsnapRoadGrid = function () {
            Ext.getCmp('snap_road_grid').getStore().setDefaultSort('apptime', 'asc');
            Ext.getCmp('snap_road_grid').getStore().removeAll();
            Ext.getCmp('snap_road_grid').getStore().load({
                params: {
                    start: 0,
                    limit: recs_per_page,
                    vehapikey: Application.Qugeo.apikey
                }
            });
        };
    
        var loadActrouteGrid = function () {
            Ext.getCmp('grid_route').getStore().setDefaultSort('apptime', 'asc');
            Ext.getCmp('grid_route').getStore().removeAll();
            Ext.getCmp('grid_route').getStore().load({
                params: {
                    start: 0,
                    limit: recs_per_page,
                    vehapikey: Application.Qugeo.apikey
                }
            });
        };
    
        var crateSnapStore = function () {
            var store = new Ext.data.JsonStore({
                url: '?module=qugeo&op=snap_road_details',
                fields: ['Latitude', 'Longitude', 'tstamp', 'apptime', 'disttravled', 'provider', 'version'],
                root: 'polylines',
                id: 'routestore',
                totalProperty: 'totalcount',
                localSort: true
            });
            return store;
        };
    
        var createSnapGrid = function () {
            var store = crateSnapStore();
            var grid = new Ext.grid.GridPanel({
                store: store,
                layout: 'fit',
                forceFit: true,
                height: 300,
                id: 'snap_road_grid',
                columns: [{
                        header: 'Time',
                        dataIndex: 'tstamp',
                        sortable: true,
                        renderer: function (val) {
                            var tstamp = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            return tstamp[4] + ':' + tstamp[5] + ':' + tstamp[6];
                        }
                    }, {
                        header: 'Application Time',
                        dataIndex: 'apptime',
                        sortable: true,
                        renderer: function (val) {
                            var app_time = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            return app_time[4] + ':' + app_time[5] + ':' + app_time[6];
                        }
                    }, {
                        header: 'Distance',
                        dataIndex: 'disttravled',
                        sortable: false,
                        align: 'right'
                    }, {
                        header: 'Latitude',
                        dataIndex: 'Latitude',
                        id: 'latitude',
                        sortable: false,
                        align: 'right'
                    }, {
                        header: 'Longitude',
                        dataIndex: 'Longitude',
                        sortable: false,
                        align: 'right'
                    }, {
                        header: 'Provder',
                        dataIndex: 'provider',
                        sortable: false
                    }, {
                        header: 'Version',
                        dataIndex: 'version',
                        sortable: false,
                        align: 'right'
                    }], tbar: [{
                        text: 'Export To Excel',
                        tooltip: 'Generate Spreadsheet',
                        id: 'btnExcel1',
                        icon: IMAGE_BASE_PATH + "/default/icons/page_excel.png",
                        iconCls: 'my-icon1',
                        handler: function () {
                            downloadSnapGrid();
                        }
                    }],
                viewConfig: {
                    forceFit: true
                },
                stripeRows: true
            });
            return grid;
        };
    
        var createrouteGrid = function () {
            var store_route = createRouteStore();
    
            var routeGrid = new Ext.grid.GridPanel({
                store: store_route,
                layout: 'fit',
                forceFit: true,
                height: 300,
                iconCls: '',
                id: 'grid_route',
                remoteSort: false,
                columns: [{
                        header: 'Time',
                        dataIndex: 'tstamp',
                        sortable: true,
                        renderer: function (val) {
                            var tstamp = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            return tstamp[4] + ':' + tstamp[5] + ':' + tstamp[6];
                        }
                    }, {
                        header: 'Application Time',
                        dataIndex: 'apptime',
                        sortable: true,
                        renderer: function (val) {
                            var app_time = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            return app_time[4] + ':' + app_time[5] + ':' + app_time[6];
                        }
                    }, {
                        header: 'Distance',
                        dataIndex: 'disttravled',
                        sortable: false,
                        align: 'right'
                    }, {
                        header: 'Latitude',
                        dataIndex: 'Latitude',
                        id: 'latitude',
                        sortable: false,
                        align: 'right'
                    }, {
                        header: 'Longitude',
                        dataIndex: 'Longitude',
                        sortable: false,
                        align: 'right'
                    }, {
                        header: 'Provder',
                        dataIndex: 'provider',
                        sortable: false
                    }, {
                        header: 'Version',
                        dataIndex: 'version',
                        sortable: false,
                        align: 'right'
                    }], tbar: [{
                        text: 'Export To Excel',
                        tooltip: 'Generate Spreadsheet',
                        id: 'btnExcel1',
                        //iconCls : 'icon_excel',
                        icon: IMAGE_BASE_PATH + "/default/icons/page_excel.png",
                        iconCls: 'my-icon1',
                        handler: function () {
    
                            /*  var cm = Ext.getCmp('softwareUpdate_grid').getColumnModel();
                             document.location.href = '?module=logs&op=export_to_excel';*/
    
                            downloadRouteGrid();
    
                        }
                    }],
                viewConfig: {
                    forceFit: true
                },
                stripeRows: true
            });
            return routeGrid;
        };
    
    
        var routeMap = function (type_value, type, isjobconfirmation, driverid) {
    
            var centerParam = {
                lat: "12.938006",
                lng: "77.622966"
            };
            return {
                xtype: 'gmappanel',
                region: 'center',
                zoomLevel: 10,
                minGeoAccuracy: 4,
                gmapType: 'map',
                scaleControl: true,
                id: 'job_route_map',
                anchor: '99%',
                mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
                mapControls: ['GSmallMapControl', 'GMapTypeControl'],
                zoom: 20,
                mapTypeId: "roadmap",
                setCenter: centerParam,
                listeners: {
                    afterrender: function () {
                        setTimeout(function () {
                            Ext.Ajax.request({
                                url: modURL + '&op=getMarkers',
                                method: 'POST',
                                params: {
                                    type_value: type_value, type: type, isjobconfirmation: isjobconfirmation, driverid: driverid
                                },
                                success: function (res) {
                                    var tmp = Ext.decode(res.responseText);
                                    if (tmp.success === true) {
    
                                        var mymarker = [];
    
                                        var order = tmp.order;
    
                                        if (!Ext.isEmpty(order)) {
                                            Ext.each(order, function (order_item) {
    
                                                var contentString = '<div id="content">' +
                                                        '<h1 class="firstHeading">' + order_item.bkno + '</h1>' +
                                                        '<table style="font-size:10px;color:#333">' +
                                                        '<tr><td>Sequence</td><td> : ' + order_item.order + '</td></tr>' +
                                                        '<tr><td>Location </td><td> : ' + order_item.locationname + '</td></tr>' +
                                                        '</table>' +
                                                        '</div>';
    
                                                mymarker.push({
                                                    lat: order_item.Latitude,
                                                    lng: order_item.Longitude,
                                                    marker: {
                                                        title: order_item.title,
                                                        draggable: false,
                                                        infoWindow: {content: contentString}
                                                    },
                                                    icon: order_item.ordericon,
                                                    extraInfo: order_item.extraInfo
                                                });
                                            });
                                        }
                                        var vehicle = tmp.vehicle;
                                        if (!Ext.isEmpty(vehicle)) {
                                            Ext.each(vehicle, function (vehicle_item) {
                                                Application.Qugeo.apikey = vehicle_item.apikey;
                                                var contentString = '<div id="content">' +
                                                        '<h1 class="firstHeading">' + vehicle_item.v_no + '</h1>' +
                                                        '<table style="font-size:10px;color:#333">' +
                                                        '<tr><td>Type</td><td> : ' + vehicle_item.v_typename + '</td></tr>' +
                                                        '<tr><td>Driver </td><td> : ' + vehicle_item.DriverName + '</td></tr>' +
                                                        '<tr><td>Mobile </td><td> : ' + vehicle_item.mobno + '</td></tr>' +
                                                        '</table>' +
                                                        '</div>';
                                                mymarker.push({
                                                    lat: vehicle_item.Latitude,
                                                    lng: vehicle_item.Longitude,
                                                    marker: {
                                                        title: vehicle_item.title,
                                                        draggable: false,
                                                        infoWindow: {content: contentString}
                                                    },
                                                    icon: vehicle_item.vehicleicon,
                                                    extraInfo: vehicle_item.extraInfo
                                                });
                                            });
                                        }
    
                                        var qgdrvhome = tmp.qgdrvhome;
                                        if (!Ext.isEmpty(qgdrvhome)) {
                                            mymarker.push({
                                                lat: qgdrvhome.Latitude,
                                                lng: qgdrvhome.Longitude,
                                                marker: {
                                                    title: 'Branch Location',
                                                    draggable: false
                                                },
                                                icon: qgdrvhome.homeicon
                                            });
                                        }
                                        var bounds = {value: null};
    
                                        Ext.getCmp('job_route_map').addMarkers(mymarker, true, bounds);
                                        /* <?php if (user_access("qugeo", "actroute")) { ?> */
                                        Ext.getCmp('actroute_btn').enable();
                                        /* <?php } ?> */
                                        /* <?php if (user_access("qugeo", "schroute")) { ?> */
                                        Ext.getCmp('schroute_btn').enable();
                                        /* <?php } ?> */
                                        /* <?php if (user_access("qugeo", "actroutegrid")) { ?> */
                                        Ext.getCmp('actroutegrd_btn').enable();
                                        /* <?php } ?> */
                                        /* <?php if (user_access("qugeo", "snap_road_details")) { ?> */
                                        Ext.getCmp('snap_road_btn').enable();
                                        /* <?php }?> */
                                        /* <?php if (user_access("qugeo", "snap_road_details")) { ?> */
                                        Ext.getCmp('snap_road_details_btn').enable();
                                        /* <?php } ?> */
                                    }
                                    setTimeout(function () {
                                        myMask.hide();
                                    }, 2000);
                                },
                                failure: function () {
                                    myMask.hide();
                                }
                            });
    
                        }, 3000);
                    }
                }
            };
        };
    
        var reloadPolylines = function () {
    
            /* <?php if (user_access("qugeo", "snap_road")) { ?> */
    
            if (Ext.getCmp('snap_road_btn').btn_pressed == true && !Ext.isEmpty(Application.Qugeo.SnapRoadPolyline)) {
                Ext.getCmp('job_route_map').removePolyline(Application.Qugeo.SnapRoadPolyline.polyline);
                Ext.getCmp('job_route_map').removeMarkers(Application.Qugeo.SnapRoadPolyline.markers);
                snapRoad();
            }
            /* <?php } ?> */
            /* <?php if (user_access("qugeo", "actroute")) { ?> */
    
            if (Ext.getCmp('actroute_btn').btn_pressed == true && !Ext.isEmpty(Application.Qugeo.ActualRoutePolyline)) {
                Ext.getCmp('job_route_map').removePolyline(Application.Qugeo.ActualRoutePolyline.polyline);
                Ext.getCmp('job_route_map').removeMarkers(Application.Qugeo.ActualRoutePolyline.markers);
                drawActualRoute('actroute');
            }
            /* <?php } ?> */
    
            /* <?php if (user_access("qugeo", "schroute")) { ?> */
            if (Ext.getCmp('schroute_btn').btn_pressed == true && !Ext.isEmpty(Application.Qugeo.ScheduledRouteDirections)) {
                Ext.getCmp('job_route_map').removeDirections(Application.Qugeo.ScheduledRouteDirections);
                //   Ext.getCmp('job_route_map').removeMarkers(Application.Qugeo.ScheduledRouteDirections.markers);
                drawScheduledRoute('schroute');
            }
            /* <?php } ?> */
    
        };
    
        var myMask;
        var viewJobsAndRoutes = function (type_value, type, isjobconfirmation, title_string, driverid) {
    
    
            var map_win = new Ext.Window({
                width: winsize.width * 0.78, height: winsize.height * 0.8,
                resizable: false,
                plain: true,
                iconCls: 'my-icon109',
                title: 'Jobs and Routes : ' + title_string,
                modal: true,
                frame: true,
                constrainHeader: true,
                layout: 'border',
                items: routeMap(type_value, type, isjobconfirmation, driverid),
                buttonAlign: 'left',
                id: 'map_win',
                buttons: [
                    {html: "<img src='" + IMAGE_BASE_PATH + "/default/icons/queued_red.png' title='Queued'> Queued"},
                    {html: "<img src='" + IMAGE_BASE_PATH + "/default/icons/green_done.png' title='Completed'> Completed"},
                    {html: "<img src='" + IMAGE_BASE_PATH + "/default/icons/black.png' title='Terminated'> Terminated"},
                    {html: "<img src='" + IMAGE_BASE_PATH + "/default/icons/yellow.png' title='Failed'> Failed"},
                    '->',
                    /* <?php if (user_access("qugeo", "schroute") || user_access("qugeo", "snap_road") || user_access("qugeo", "actroute")) { ?> */
                    {
                        tooltip: 'Reload',
                        icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
                        iconCls: 'my-icon1',
                        tabIndex: '9',
                        handler: function () {
                            reloadPolylines();
                        }
                    }, /* <?php } ?> */
                    /* <?php if (user_access("qugeo", "schroute")) { ?> */{
                        tooltip: 'Scheduled Routes',
                        id: 'schroute_btn',
                        disabled: true,
                        tabIndex: '10',
                        enableToggle: true,
                        allowDepress: true,
                        btn_pressed: false,
                        icon: IMAGE_BASE_PATH + '/default/icons/scheduled_routes.png',
                        handler: function (btn) {
                            if (btn.btn_pressed == true)
                                btn.btn_pressed = false;
                            else
                                btn.btn_pressed = true;
    
                            if (!Ext.isEmpty(Application.Qugeo.ScheduledRouteDirections) && btn.btn_pressed == false) {
                                Ext.getCmp('job_route_map').removeDirections(Application.Qugeo.ScheduledRouteDirections);
                                //   Ext.getCmp('job_route_map').removeMarkers(Application.Qugeo.ScheduledRouteDirections.markers);
                            }
                            if (btn.btn_pressed == true) {
                                map_win.setTitle('Scheduled Route Jobs and Routes: ' + title_string);
                                drawScheduledRoute('schroute');
                            }
                        }
                    }, /* <?php } ?> */
    
                    /* <?php if (user_access("qugeo", "snap_road")) { ?> */{
                        tooltip: 'Snap to Road',
                        id: 'snap_road_btn',
                        disabled: true,
                        tabIndex: '20',
                        enableToggle: true,
                        allowDepress: true,
                        btn_pressed: false,
                        icon: IMAGE_BASE_PATH + '/default/icons/road.png',
                        handler: function (btn) {
                            if (btn.btn_pressed == true)
                                btn.btn_pressed = false;
                            else
                                btn.btn_pressed = true;
                            if (!Ext.isEmpty(Application.Qugeo.SnapRoadPolyline) && btn.btn_pressed == false) {
                                Ext.getCmp('job_route_map').removePolyline(Application.Qugeo.SnapRoadPolyline.polyline);
                                Ext.getCmp('job_route_map').removeMarkers(Application.Qugeo.SnapRoadPolyline.markers);
                            }
                            if (btn.btn_pressed == true) {
                                map_win.setTitle('Snap to Road Jobs and Routes : ' + title_string);
                                snapRoad();
                            }
                        }
                    }, /* <?php } ?> */
                    /* <?php if (user_access("qugeo", "snap_road_details")) { ?> */{
                        tooltip: 'Snap to Road Details',
                        id: 'snap_road_details_btn',
                        disabled: true,
                        tabIndex: '30',
                        icon: IMAGE_BASE_PATH + '/default/icons/road.png',
                        /*  iconCls: 'my-icon1',*/
                        handler: function () {
                            Application.Qugeo.snapRoadGrd();
                            loadsnapRoadGrid();
                        }
                    }, /* <?php } ?> */
                    /* <?php if (user_access("qugeo", "actroute")) { ?> */{
                        tooltip: 'Actual Route',
                        id: 'actroute_btn',
                        disabled: true,
                        tabIndex: '40',
                        enableToggle: true,
                        allowDepress: true,
                        icon: IMAGE_BASE_PATH + '/default/icons/actual_route.png',
                        btn_pressed: false,
                        handler: function (btn) {
                            if (btn.btn_pressed == true)
                                btn.btn_pressed = false;
                            else
                                btn.btn_pressed = true;
    
                            if (!Ext.isEmpty(Application.Qugeo.ActualRoutePolyline) && btn.btn_pressed == false) {
                                Ext.getCmp('job_route_map').removePolyline(Application.Qugeo.ActualRoutePolyline.polyline);
                                Ext.getCmp('job_route_map').removeMarkers(Application.Qugeo.ActualRoutePolyline.markers);
                            }
                            if (btn.btn_pressed == true) {
                                map_win.setTitle('Actual Route Jobs and Routes: ' + title_string);
                                drawActualRoute('actroute');
                            }
                        }
                    }, /* <?php } ?> */
    
                    /* <?php if (user_access("qugeo", "actroutegrid")) { ?> */{
                        tooltip: 'Actual Route Details',
                        id: 'actroutegrd_btn',
                        disabled: true,
                        tabIndex: '50',
                        icon: IMAGE_BASE_PATH + '/default/icons/actual_route.png',
                        /*  iconCls: 'my-icon1',*/
                        handler: function () {
                            //  drawActualRoute('actroute');
                            Application.Qugeo.ActualrouteGrd();
                            loadActrouteGrid();
                        }
                    }, /* <?php } ?> */{
                        tooltip: 'Cancel',
                        tabIndex: '60',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        /*iconCls: 'my-icon1',*/
                        handler: function () {
                            map_win.close();
                        }
                    }],
                listeners: {
                    afterrender: function () {
    
                    }
                }
            });
    
            map_win.doLayout();
            map_win.show(this);
            map_win.center();
            myMask = new Ext.LoadMask(Ext.getCmp('map_win').el, {msg: "Please wait....", maskCls: 'customLoadMask'});
            myMask.show();
        };
    
        var vehicleHistoryStore = function () {
            var store = new Ext.data.JsonStore({
                url: modURL + '&op=listVehicleHistory',
                method: 'post',
                fields: ['vehid', 'vehno', 'drivername', 'mobno', 'logintime', 'logintime', 'assgwt', 'currwt', 'assgvol', 'currvol', 'kmcovered', 'totjobs', 'jobscompleted', 'apikey', 'LoggedOutAt', 'IsCleanLogout'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false,
                listeners: {beforeload: function () {
                        if (Ext.getCmp('veh_history_branch').getValue() != '' && Ext.getCmp('veh_date_tbar').getValue() != '') {
                            this.baseParams.date = Ext.getCmp('veh_date_tbar').getValue().format('Y-m-d');
                            this.baseParamsbr_id = Ext.getCmp('veh_history_branch').getValue();
                        } else {
                            Ext.MessageBox.alert("Notification", "Select select Branch and Date.");
                            return false;
                        }
                    }
                }
            });
            return store;
        };
    
        var vehiclesHistoryGrid = function () {
            var store = vehicleHistoryStore();
            var sm = new Ext.grid.RowSelectionModel({
                singleSelect: true
            });
            var vehistfilters = new Ext.ux.grid.GridFilters({
                filters: [{
                        type: 'string',
                        dataIndex: 'vehno'
    
                    }, {
                        type: 'string',
                        dataIndex: 'drivername'
    
                    }, {
                        type: 'string',
                        dataIndex: 'mobno'
    
                    }, {
                        type: 'string',
                        dataIndex: 'logintime'
    
                    }, {
                        type: 'string',
                        dataIndex: 'LoggedOutAt'
    
                    }, {
                        type: 'string',
                        dataIndex: 'IsCleanLogout'
    
                    }, {
                        type: 'string',
                        dataIndex: 'assgwt'
    
                    }, {
                        type: 'string',
                        dataIndex: 'currwt'
    
                    }, {
                        type: 'string',
                        dataIndex: 'assgvol'
    
                    }, {
                        type: 'string',
                        dataIndex: 'currvol'
    
                    }, {
                        type: 'string',
                        dataIndex: 'kmcovered'
    
                    }, {
                        type: 'string',
                        dataIndex: 'totjobs'
    
                    }, {
                        type: 'string',
                        dataIndex: 'jobscompleted'
    
                    }]
            });
            vehistfilters.remote = true;
            vehistfilters.autoReload = true;
            var grid = new Ext.grid.GridPanel({
                store: store,
                iconCls: 'vehicle_history',
                layout: 'fit',
                frame: false,
                border: false,
                title: 'Vehicle History',
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                sm: sm,
                stripeRows: true,
                id: 'vehicles_history_grid', columns: [{
                        header: 'Vehicle No.',
                        sortable: false,
                        dataIndex: 'vehno'
                    }, {
                        header: 'Driver',
                        sortable: true,
                        dataIndex: 'drivername'
                    }, {
                        header: 'Mobile',
                        sortable: true,
                        dataIndex: 'mobno',
                        width: 70
                    },
                    {
                        header: 'Login Time',
                        sortable: true,
                        dataIndex: 'logintime',
                        renderer: function (val) {
                            var login_time = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                            return login_time[1] + '-' + login_time[2] + '-' + login_time[3] + ' ' + login_time[4] + ':' + login_time[5] + ':' + login_time[6];
                        }
                    }, {
                        header: 'Logout Time',
                        sortable: true,
                        dataIndex: 'LoggedOutAt',
                        renderer: function (val) {
                            if (val) {
                                var logout_time = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                                return logout_time[1] + '-' + logout_time[2] + '-' + logout_time[3] + ' ' + logout_time[4] + ':' + logout_time[5] + ':' + logout_time[6];
                            } else {
                                return '';
                            }
                        }
                    }, {
                        header: 'Logout Type',
                        sortable: true,
                        dataIndex: 'IsCleanLogout',
                        width: 70
                    }, {
                        header: 'Allot Wt',
                        sortable: true,
                        dataIndex: 'assgwt',
                        align: 'right',
                        width: 60
                    }, {
                        header: 'Final Wt',
                        sortable: true,
                        dataIndex: 'currwt',
                        align: 'right',
                        width: 60
                    },
                    {
                        header: 'Allot Vol',
                        sortable: true,
                        dataIndex: 'assgvol',
                        align: 'right',
                        width: 60
                    }, {
                        header: 'Final Vol',
                        sortable: true,
                        dataIndex: 'currvol',
                        align: 'right',
                        width: 60
                    }, {
                        header: 'KM. Covered',
                        sortable: true,
                        dataIndex: 'kmcovered',
                        align: 'right',
                        width: 80
                    }, {
                        header: 'Total Jobs',
                        sortable: true,
                        dataIndex: 'totjobs',
                        align: 'right',
                        width: 70
                    },
                    {
                        header: 'Jobs Completed',
                        sortable: true,
                        dataIndex: 'jobscompleted',
                        align: 'right',
                        width: 80
                    }, {
                        xtype: 'actioncolumn',
                        hideable: false,
                        width: 40,
                        items: [{
                                sortable: false,
                                getClass: function (v, meta, rec) {
                                    return 'my-icon10';
                                }, tooltip: 'View Jobs and Route', handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    Application.Qugeo.JobsRoute(record.get('vehid'), 'Vehicle', 0, record.get('vehno'));
                                }
                            }]
                    }],
                tbar: [{
                        html: '&nbsp; Branch: &nbsp;'
                    }, {
                        xtype: 'combo',
                        mode: 'remote',
                        typeAhead: true,
                        editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: branchstore(),
                        id: 'veh_history_branch',
                        triggerAction: 'all',
                        displayField: 'br_Name',
                        allowBlank: false,
                        valueField: 'br_ID',
                        hiddenName: 'br_ID',
                        name: 'veh_history_branch',
                        minChars: 1
                    },
                    {
                        html: '&nbsp; Date: &nbsp;'
                    }, {
                        xtype: 'datefield',
                        id: 'veh_date_tbar',
                        allowBlank: false,
                        editable: true,
                        format: 'Y-m-d',
                        maxValue: new Date(),
                        value: new Date()
                    },
                    {
                        html: '&nbsp; &nbsp;'
                    },
                    {
                        xtype: 'button',
                        text: 'Show',
                        iconCls: 'show',
                        style: "padding-left: 10px;",
                        id: 'vehicle_history_show',
                        handler: function () {
                            if (Ext.getCmp('veh_history_branch').getValue() != '' && Ext.getCmp('veh_date_tbar').getValue() != '') {
                                var loadingMask = new Ext.LoadMask(Ext.getCmp('vehicles_history_grid').el, {msg: "Please wait..."});
                                loadingMask.show();
    
                                Ext.getCmp('vehicles_history_grid').getStore().load({
                                    params: {
                                        date: Ext.getCmp('veh_date_tbar').getValue().format('Y-m-d'),
                                        br_id: Ext.getCmp('veh_history_branch').getValue()
                                    },
                                    callback: function () {
                                        loadingMask.hide();
                                    }
                                });
                            } else
                                Ext.MessageBox.alert("Notification", "Select select Branch and Date.");
                        }
                    }
                ], bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: store,
                    displayInfo: true,
                    displayMsg: 'Displaying pages {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                listeners: {
                    afterrender: function () {
                        if (_SESSION.typId == '3' || _SESSION.typId == '4')
                        {
                            Ext.getCmp('veh_history_branch').store.load({
                                callback: function () {
                                    Ext.getCmp('veh_history_branch').setValue(_SESSION.typdetsid);
                                }
                            });
                        }
                    }
                }
            });
    
            return grid;
        };
        var pickup_verify_btn_form = function () {
    
            var form = new Ext.FormPanel({
                id: 'verify_form_id',
                frame: true,
                border: false,
                labelAlign: 'left',
                autoHeight: true,
                layout: 'column',
                labelWidth: 105,
                items: [
                    {
                        layout: 'form',
                        columnWidth: 0.50,
                        defaults: {
                            submitValue: true,
                            anchor: '98%'},
                        items: [{
                                xtype: 'hidden',
                                id: 'pickup_del_time',
                                name: 'pickup_del_time'
                            }, {
                                xtype: 'hidden',
                                id: 'BkLastEditTime',
                                name: 'BkLastEditTime'
                            }, {
                                xtype: 'hidden',
                                id: 'status',
                                name: 'status'
                            }, {
                                xtype: 'hidden',
                                id: 'bk_brk_br_id',
                                name: 'bk_brk_br_id'
                            }, {
                                xtype: 'hidden',
                                id: 'dls_id',
                                name: 'dls_id'
                            }, {
                                xtype: 'hidden',
                                id: 'IsPickup',
                                name: 'IsPickup'
                            }, {
                                xtype: 'hidden',
                                id: 'orderid',
                                name: 'orderid'
                            }, {
                                xtype: 'hidden',
                                id: 'quor_id',
                                name: 'quor_id'
                            }, {
                                fieldLabel: 'Booking No',
                                id: 'quor_RefNo',
                                name: 'quor_RefNo',
                                xtype: 'textfield',
                                readOnly: true
                            },
                            {xtype: 'textfield',
                                readOnly: true,
                                fieldLabel: 'Vehicle No',
                                id: 'v_no',
                                name: 'v_no'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                fieldLabel: 'Source',
                                id: 'From',
                                name: 'source'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                hidden: true,
                                fieldLabel: 'Excess Amount',
                                id: 'ex_amt',
                                name: 'ex_amt'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                fieldLabel: 'Status',
                                id: 'status',
                                name: 'status'
                            }, {
                                xtype: 'datefield',
                                format: 'Y-m-d H:i:s',
                                readOnly: true,
                                fieldLabel: 'Status Time',
                                id: 'status_time',
                                name: 'status_time'
                            }]
                    },
                    {
                        layout: 'form',
                        columnWidth: 0.50,
                        defaults: {
                            anchor: '98%',
                            submitValue: true
                        },
                        items: [
                            {
                                xtype: 'hidden',
                                id: 'reason_id',
                                name: 'reason_id'
                            },
                            {xtype: 'textfield',
                                readOnly: true,
                                fieldLabel: 'Date',
                                id: 'date',
                                name: 'date'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                fieldLabel: 'Destination',
                                id: 'To',
                                name: 'destination'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                fieldLabel: 'Paymode',
                                id: 'Paymode',
                                name: 'Paymode'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                fieldLabel: 'Excess Payment',
                                id: 'ex_pay',
                                hidden: true,
                                name: 'ex_pay'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                hidden: true,
                                fieldLabel: 'Net Amount',
                                id: 'NetAmt',
                                name: 'NetAmt'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                hidden: true,
                                fieldLabel: 'Amount Collectible',
                                id: 'quor_AmountCollectible',
                                name: 'quor_AmountCollectible'
                            }, {xtype: 'textfield',
                                readOnly: true,
                                fieldStyle: 'text-align: right;',
                                fieldLabel: 'Total',
                                id: 'quor_AmountCollectible',
                                name: 'quor_AmountCollectible'
                            }, {
                                xtype: 'datefield',
                                format: 'Y-m-d H:i:s',
                                increment: 5,
                                fieldLabel: 'Confirmation Time',
                                value: new Date().format("Y-m-d H:i:s"),
                                id: 'confirmation_time',
                                name: 'confirmation_time',
                                anchor: '98%',
                                allowBlank: false
                            }]
                    }
    
                ]
            });
            return form;
        };
        var save = function (button_type) {
            Ext.Msg.show({
                title: 'Saving!',
                msg: '',
                progressText: 'Please Wait...',
                width: 300,
                progress: true,
                closable: false,
                wait: true
            });
    //
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.getCmp('verify_form_id').getForm().submit({
                url: mod1URL + '&op=save',
                waitTitle: 'Please Wait..',
                waitMsg: 'Saving data...',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    action: button_type
                },
                success: function (form, action) {
                    var result = Ext.decode(action.response.responseText);
                    if (result.success !== undefined && result.success === true) {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', result.msg, function (btn) {
                            if (btn == 'ok') {
                                Ext.getCmp('scheduled_jobs_grid').getStore().load();
                                Ext.getCmp('verify_btn_window').close();
                            }
                        });
                    }
                },
                failure: function () {
                    Ext.Msg.hide();
                    Ext.MessageBox.alert('Error', 'Error occured while uploading data.');
                }
            });
        };
        var getPickupDeliveryTime = function (type, date_only) {
            var confirmation_time = Ext.getCmp('confirmation_time').getValue().format("Y-m-d\\TH:i:sP");
    
            var time_win = new Ext.Window({
                id: 'time_win',
                title: type + ' Time',
                modal: true,
                layout: 'fit',
                width: 300,
                autoHeight: true,
                shadow: false,
                resizable: false,
                items: new Ext.FormPanel({
                    id: 'time_form',
                    frame: true,
                    border: false,
                    autoHeight: true,
                    layout: 'fit',
                    items: {
                        xtype: 'datefield',
                        format: 'Y-m-d H:i:s',
                        id: 'time_input',
                        name: 'time_input',
                        anchor: '98%',
                        allowBlank: false,
                        emptyText: 'Please enter ' + type + ' time'
                    },
                    listeners: {
                        afterrender: function () {
                            Ext.getCmp('time_input').setValue(date_only);
                        }
                    }
                }),
                buttons: [{
                        text: 'Cancel', icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            time_win.close();
                        }
                    }, {
                        text: 'Ok',
                        iconCls: 'icon_approve',
                        handler: function () {
                            if (Ext.getCmp('time_form').getForm().isValid()) {
                                var popup_time = Ext.getCmp('time_input').getValue();
                                var time = popup_time.format("Y-m-d\\TH:i:sP");
    
                                if (confirmation_time >= time) {
    
                                    if (popup_time.format("H:i:s") == '00:00:00') {
                                        Ext.MessageBox.alert("Notification", 'Please enter a valid time');
                                    } else {
                                        Ext.getCmp('pickup_del_time').setValue(popup_time.format("Y-m-d H:i:s"));
                                        time_win.close();
                                        Application.JobConfirmation.DateTime = Ext.getCmp('confirmation_time').getValue().format("Y-m-d H:i:s");
    
                                        save(type);
                                    }
                                } else {
                                    Ext.MessageBox.alert("Notification", 'Invalid date and time selection for confirmation time/' + type + ' time');
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please enter valid ' + type + ' time');
                            }
                        }
                    }]
            });
    
    
            time_win.show();
            time_win.doLayout();
            time_win.center();
        };
        var updateStatus = function (data, type, action) {
            var status_time = Ext.getCmp('status_time').getValue().format("Y-m-d\\TH:i:sP");
    
            var cnf = Ext.getCmp('confirmation_time').getValue();
    
            var confirmation_time = cnf.format("Y-m-d\\TH:i:sP");
            var date_only = cnf.format("Y-m-d H:i:s");
            if (type == 1) {
                if (data.dls_id == 28)
                    save(action);
                else {
                    if (status_time <= confirmation_time)
                        getPickupDeliveryTime(action, date_only);
                    else {
                        Ext.MessageBox.alert("Notification", "Invalid date selection.");
                    }
                }
            } else if (type == 2)
                if (data.dls_id == 38)
                    save(action);
                else {
                    if (status_time <= confirmation_time)
                        getPickupDeliveryTime(action, date_only);
                    else {
                        Ext.MessageBox.alert("Notification", "Invalid date selection.");
                    }
                }
        };
        var markDeliveryForm = function () {
            var confirmation_time = Ext.getCmp('confirmation_time').getValue().format("Y-m-d\\TH:i:sP");
    
            var time_win = new Ext.Window({
                id: 'time_win',
                title: type + ' Time',
                modal: true,
                layout: 'fit',
                width: 300,
                autoHeight: true,
                shadow: false,
                resizable: false,
                items: new Ext.FormPanel({
                    id: 'time_form',
                    frame: true,
                    border: false,
                    autoHeight: true,
                    layout: 'fit',
                    items: {
                        xtype: 'datefield',
                        format: 'Y-m-d H:i:s',
                        id: 'time_input',
                        name: 'time_input',
                        anchor: '98%',
                        allowBlank: false,
                        emptyText: 'Please enter ' + type + ' time'
                    },
                    listeners: {
                        afterrender: function () {
                            Ext.getCmp('time_input').setValue(date_only);
                        }
                    }
                }),
                buttons: [{
                        text: 'Cancel', icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            time_win.close();
                        }
                    }, {
                        text: 'Ok',
                        iconCls: 'icon_approve',
                        handler: function () {
                            if (Ext.getCmp('time_form').getForm().isValid()) {
                                var popup_time = Ext.getCmp('time_input').getValue();
                                var time = popup_time.format("Y-m-d\\TH:i:sP");
    
                                if (confirmation_time >= time) {
    
                                    if (popup_time.format("H:i:s") == '00:00:00') {
                                        Ext.MessageBox.alert("Notification", 'Please enter a valid time');
                                    } else {
                                        Ext.getCmp('pickup_del_time').setValue(popup_time.format("Y-m-d H:i:s"));
                                        time_win.close();
                                        Application.JobConfirmation.DateTime = Ext.getCmp('confirmation_time').getValue().format("Y-m-d H:i:s");
    
                                        save(type);
                                    }
                                } else {
                                    Ext.MessageBox.alert("Notification", 'Invalid date and time selection for confirmation time/' + type + ' time');
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please enter valid ' + type + ' time');
                            }
                        }
                    }]
            });
    
    
            time_win.show();
            time_win.doLayout();
            time_win.center();
        };
    
        var failureReasonWindow = function (failureid = 0) {
            var verify_reason_window = Ext.getCmp('failureReasonWindow');
            if (Ext.isEmpty(verify_reason_window)) {
                var failureReasonWindow = new Ext.Window({
                    width: 600,
                    autoHeight: true,
                    id: 'failureReasonWindow',
                    shadow: false,
                    resizable: false,
                    plain: true,
                    constrain: true,
                    draggable: true,
                    modal: true,
                    closable: false,
                    items: [
                        new Ext.form.FormPanel(
                                {
                                    id: 'formDelFailureReason',
                                    autoHeight: true,
                                    title: 'Failure reason',
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 5px"},
                                    labelAlign: 'top',
                                    items: [
                                        {
                                            layout: 'column',
                                            border: false,
                                            bodyStyle: {"background-color": "F1F1F1"},
                                            items: [{
                                                    layout: 'form',
                                                    border: false,
                                                    bodyStyle: {"background-color": "F1F1F1"},
                                                    columnWidth: 1.0,
                                                    items: [
                                                        {
                                                            layout: 'column',
                                                            bodyStyle: {"background-color": "F1F1F1"},
                                                            fieldLabel: 'Failure Reason',
                                                            xtype: 'combo',
                                                            anchor: '95%',
                                                            displayField: 'dls_Description',
                                                            valueField: 'dls_ID',
                                                            allowBlank: false,
                                                            typeAhead: true,
                                                            minChars: 1,
                                                            editable: true,
                                                            id: 'cb_DelFailureReason',
                                                            name: 'cb_DelFailureReason',
                                                            hideBorders: true,
                                                            triggerAction: 'all',
                                                            mode: 'local',
                                                            border: false,
                                                            store: failure_reason_store()
                                                        }
                                                    ]
                                                }
    
                                            ]
                                        }
                                    ]
    
                                })
                    ],
                    fbar: [
                        {
                            text: 'Cancel', icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            tabIndex: 45,
                            handler: function () {
                                failureReasonWindow.close();
                            }
                        },
                        {
                            text: 'Save',
                            anchor: '95%',
                            columnWidth: 0.1,
                            bodyStyle: {'margin': '0px 0px 0px 0px'},
                            xtype: 'button',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            tabIndex: 304,
                            handler: function () {
                                //Ext.getCmp('reason_id').setValue(0);
                                Ext.getCmp('reason_id').setValue(Ext.getCmp('cb_DelFailureReason').getValue());
                                failureReasonWindow.close();
                                save('Failed');
                            }
                        }
    
    
    
                    ]
    
                });
            } else {
                failureReasonWindow = verify_reason_window;
            }
            if (failureid > 0) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var regFee_form = Ext.getCmp('formDelFailureReason').getForm();
                regFee_form.load({
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadEditData',
                    waitMsg: 'Loading...', success: function (form, action) {
                        eval('var tmp=' + action.response.responseText);
                        var tmp = Ext.decode(action.response.responseText);
    
    
                    },
                    failure: function (form, action) {
                    }
                });
            }
    
            failureReasonWindow.doLayout();
            failureReasonWindow.show();
            failureReasonWindow.center();
            return failureReasonWindow;
        }
    
        /////////////////////////////////////////////////////////////////////////////  
    
    
    
        //
        /////////////////////////////EO Private Area///////////////////////////////
    
        ///////////////////////////////////////////////////////////////////////////
        //-----------------------------------------------------------------------//
        //------------------------Event Definition Area--------------------------//
    
        //-----------------------------------------------------------------------//
        ///////////////////////////////////////////////////////////////////////////
    
    
        ///////////////////////////////////////////////////////////////////////////
        //-----------------------------------------------------------------------//
        //-----------------------------Public Area-------------------------------//
        //-----------------------------------------------------------------------//
        //---------------------------Public Variables ---------------------------//
        //----------------------------Public Methods-----------------------------//
        return {
            addQugeo: function (ind) {
                Application.Qugeo.Markers = [];
                Application.Qugeo.Markers[ind] = [];
                var main_panel_id = 'main_panel' + ind;
                var main_panel = Ext.getCmp(main_panel_id);
                var title = (ind == 1) ? 'Manual Scheduling' : 'Scheduled Jobs';
                if (Ext.isEmpty(main_panel)) {
                    main_panel = schedulePanel(main_panel_id, ind, title);
                    Application.UI.addTab(main_panel);
                    main_panel.doLayout();
                } else {
                    Application.UI.addTab(main_panel);
                }
            },
            SceduledJobs: function () {
    
                var main_panel_id = 'scheduled_jobs_grid';
                var scheduled_jobs_main_panel = Ext.getCmp(main_panel_id);
                if (Ext.isEmpty(scheduled_jobs_main_panel)) {
                    scheduled_jobs_main_panel = scheduledJobsGrid();
                    Application.UI.addTab(scheduled_jobs_main_panel);
                    scheduled_jobs_main_panel.doLayout();
                } else {
                    Application.UI.addTab(scheduled_jobs_main_panel);
                }
            },
            JobsRoute: function () {
                Application.Qugeo.SnapRoadPolyline = null;
                Application.Qugeo.ActualRoutePolyline = null;
                Application.Qugeo.ScheduledRouteDirections = null;
                var type_value = arguments[0];
                var type = arguments[1];
                var isjobconfirmation = arguments[2];
                var title_string = arguments[3];
                var driverid = arguments[4];
                Application.Qugeo.apikey;
                viewJobsAndRoutes(type_value, type, isjobconfirmation, title_string, driverid);
            },
            ForceLogout: function () {
                var vehid = arguments[0];
                var DriverId = arguments[1];
                Ext.MessageBox.confirm('Confirm', 'Do you want to force logout?', function (btn, text) {
                    if (btn == 'yes') {
                        Ext.Ajax.request({
                            url: modURL + '&op=forceLogout',
                            method: 'POST',
                            params: {
                                vehid: vehid,
                                DriverId: DriverId
                            },
                            success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                if (tmp.success == true) {
                                    Ext.MessageBox.alert('Notification', tmp.msg, function (btn) {
                                        if (btn == 'ok')
                                            Ext.getCmp('live_vehicles_grid').getStore().reload();
                                    });
                                } else {
                                    Ext.MessageBox.alert('Notification', 'Error occured during the action.');
                                }
                            }
                        });
                    }
                });
            },
            LiveVehicles: function () {
                var panel_id = 'live_vehicles_grid';
                var live_vehicles_panel = Ext.getCmp(panel_id);
    
                if (Ext.isEmpty(live_vehicles_panel)) {
                    live_vehicles_panel = liveVehiclesGrid(panel_id);
                    Application.UI.addTab(live_vehicles_panel);
                    live_vehicles_panel.doLayout();
                } else {
                    Application.UI.addTab(live_vehicles_panel);
                }
            },
            VehicleHistory: function () {
                var panel_id = 'vehicles_history_grid';
                var vehicles_history_panel = Ext.getCmp(panel_id);
    
                if (Ext.isEmpty(vehicles_history_panel)) {
                    vehicles_history_panel = vehiclesHistoryGrid(panel_id);
                    Application.UI.addTab(vehicles_history_panel);
                    vehicles_history_panel.doLayout();
                } else {
                    Application.UI.addTab(vehicles_history_panel);
                }
            },
            snapRoadGrd: function () {
                var grid = createSnapGrid();
                var win = new Ext.Window({
                    layout: 'fit',
                    width: 800,
                    autoHeight: true,
                    modal: true,
                    constrain: true,
                    title: 'Snap to Road',
                    closable: true,
                    resizable: false,
                    plain: true,
                    border: false,
                    items: grid,
                    buttonAlign: 'left',
                    buttons: [{
                            tooltip: 'Reload',
                            icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
                            iconCls: 'my-icon1',
                            handler: function () {
                                loadsnapRoadGrid();
                            }
                        }, '->', {
                            text: 'Cancel',
                            tabIndex: 985,
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                win.close();
                            }
                        }]
                });
                win.show();
            },
            ActualrouteGrd: function () {
                var rg_Frm = createrouteGrid();
                var routegridwindow = new Ext.Window({
                    layout: 'fit',
                    width: 800,
                    //width: 450,
                    autoHeight: true,
                    modal: true,
                    //constrainHeader: true,
                    constrain: true,
                    title: 'Actual Route',
                    // iconCls: '',
                    closable: true,
                    resizable: false,
                    plain: true,
                    border: false,
                    items: [rg_Frm],
                    buttonAlign: 'left',
                    buttons: [{
                            tooltip: 'Reload',
                            icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
                            iconCls: 'my-icon1',
                            tabIndex: '9',
                            handler: function () {
                                loadActrouteGrid();
                            }
                        }, '->', {
                            text: 'Cancel',
                            tabIndex: 985,
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                routegridwindow.close();
                            }
                        }]
                });
                routegridwindow.show();
                /*loadActrouteGrid();*/
            },
            IncomeDivision: function () {
    
                var main_panel_id = 'income_division_grid';
                var income_division_main_panel = Ext.getCmp(main_panel_id);
                if (Ext.isEmpty(income_division_main_panel)) {
                    income_division_main_panel = IncomeDivisionGrid();
                    Application.UI.addTab(income_division_main_panel);
                    income_division_main_panel.doLayout();
                } else {
                    Application.UI.addTab(income_division_main_panel);
                }
            },
            DriverPayment: function () {
                var panel_id = 'driver_payment_grid';
                var driver_payment_main_panel = Ext.getCmp(panel_id);
                if (Ext.isEmpty(driver_payment_main_panel)) {
                    driver_payment_main_panel = DriverPaymentGrid();
                    Application.UI.addTab(driver_payment_main_panel);
                    driver_payment_main_panel.doLayout();
                } else {
                    Application.UI.addTab(income_division_main_panel);
                }
            },
            verify_btn_window: function () {
    
                var record = arguments[0];
                var data = record.data;
                var type, hidfrm, ItemReturned, retCount;
                if (data.quor_ItemReturned) {
                    ItemReturned = JSON.parse(data.quor_ItemReturned);
                    retCount = ItemReturned.length;
                }
    
                if (data.IsPickup != 1 && retCount > 0) {
                    hidfrm = false;
                } else {
                    hidfrm = true;
                }
                if (data.IsPickup == 1)
                {
                    type = 1;
                } else
                {
                    type = 2;
                }
    
                var verify_btn_window = Ext.getCmp('verify_btn_window');
                if (Ext.isEmpty(verify_btn_window)) {
                    var verify_btn_window = new Ext.Window({
                        id: 'verify_btn_window',
                        title: 'Details',
                        iconCls: '',
                        modal: true,
                        layout: 'fit',
                        width: 650,
                        autoHeight: true,
                        shadow: false,
                        resizable: false,
                        items: pickup_verify_btn_form(),
                        buttons: [{
                                text: 'Cancel',
                                icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    verify_btn_window.close();
                                }
                            }, {
                                text: 'View Return',
                                id: 'view_return',
                                hidden: hidfrm,
                                iconCls: 'price_list',
                                handler: function () {
                                    Application.JobConfirmation.viewBarcodes(data.quor_id);
                                }
                            }, {
                                text: 'Picked up',
                                id: 'picked_up_btn',
                                iconCls: 'pickup',
                                handler: function () {
                                    if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                        Ext.Msg.show({
                                            title: 'Confirm',
                                            msg: "Do you want to mark it as Picked up?",
                                            buttons: Ext.MessageBox.OKCANCEL,
                                            fn: function (btn) {
                                                if (btn == 'ok') {
                                                    var action = 'PickUp';
                                                    updateStatus(data, type, action);
                                                }
                                            }
                                        });
                                    } else {
                                        Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                    }
                                }
                            }, {
                                text: 'Delivered',
                                id: 'delivered_btn',
                                iconCls: 'delivered',
                                handler: function () {
                                    if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                        Ext.Msg.show({
                                            title: 'Confirm',
                                            msg: "Do you want to mark it as Delivered?",
                                            buttons: Ext.MessageBox.OKCANCEL,
                                            fn: function (btn) {
                                                if (btn == 'ok') {
                                                    var action = 'Delivery';
                                                    updateStatus(data, type, action);
                                                }
                                            }
                                        });
    
                                    } else {
                                        Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                    }
                                }
                            }, {
                                text: 'Failed',
                                id: 'failed_btn',
                                iconCls: 'icon-del-table',
                                handler: function () {
                                    if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                        if (retCount > 0) {
                                            Ext.Msg.show({
                                                title: 'Confirm',
                                                msg: "Do you want to go back and view the returned items?",
                                                buttons: Ext.MessageBox.YESNO,
                                                fn: function (btn) {
                                                    if (btn == 'no') {
                                                        Ext.Msg.show({
                                                            title: 'Confirm',
                                                            msg: "Do you want to mark it as Failed?",
                                                            buttons: Ext.MessageBox.OKCANCEL,
                                                            fn: function (btn) {
                                                                if (btn == 'ok') {
                                                                    // failed_reason_window(type);
                                                                    //Ext.getCmp('reason_id').setValue(0);
                                                                    failureReasonWindow();
    
                                                                }
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        } else {
                                            Ext.Msg.show({
                                                title: 'Confirm',
                                                msg: "Do you want to mark it as Failed?",
                                                buttons: Ext.MessageBox.OKCANCEL,
                                                fn: function (btn) {
                                                    if (btn == 'ok') {
                                                        //failed_reason_window(type
    
                                                        failureReasonWindow();
                                                    }
                                                }
                                            });
                                        }
    
                                    } else {
                                        Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                    }
                                }
                            }, {
                                text: 'Manual Schedule',
                                id: 'manual_schedule_btn',
                                iconCls: 'scheduled',
                                handler: function () {
                                    if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                        Ext.MessageBox.confirm('Confirm', 'Are you sure to push manual sheduling?', function (btn, text) {
                                            if (btn == 'yes') {
                                                save('manualschedule');
                                            }
                                        });
                                    } else {
                                        Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                    }
                                }
                            }],
                        listeners: {
                            afterrender: function () {
    
                                if (data.IsEditable == false) {
                                    Ext.getCmp('picked_up_btn').disable();
                                    Ext.getCmp('delivered_btn').disable();
                                    Ext.getCmp('failed_btn').disable();
                                    Ext.getCmp('manual_schedule_btn').disable();
                                }else{
                                    Ext.getCmp('manual_schedule_btn').enable();
                                    Ext.getCmp('delivered_btn').enable();
                                }
                                
                                if (data.quor_Status == 38) {
                                    Ext.getCmp('delivered_btn').enable();
                                }
    
                                Ext.getCmp('verify_form_id').getForm().loadRecord(record);
                                Ext.getCmp('status').setValue(record.data.dls_DelStatus);
    
                                setTimeout(function () {
                                    if (Application.JobConfirmation.DateTime != '' && Ext.isEmpty(Ext.getCmp('confirmation_time').getValue()))
                                        Ext.getCmp('confirmation_time').setValue(Application.JobConfirmation.DateTime);
                                }, 50);
                            }
                        }
                    });
                }
    
                verify_btn_window.show();
                verify_btn_window.doLayout();
                verify_btn_window.center();
    
                if (data.IsPickup == 1)
                {
    
                } else
                {
                    Ext.getCmp('picked_up_btn').hide();
                }
    
            },
            MarkDelivered: function (orderid, quor_Paymode) {
                console.log('orderid', orderid);
                console.log('quor_Paymode', quor_Paymode);
                var markDeliver_win = new Ext.Window({
                    title: 'Deliver',
                    modal: true,
                    layout: 'fit',
                    id: 'windowId',
                    frame: true,
                    border: false,
                    width: winsize.width * 0.25,
                    height: winsize.height * 0.33,
                    autoHeight: true,
                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 5px"},
                    autoScroll: true,
                    shadow: false,
                    resizable: false,
                    items: new Ext.FormPanel({
                        id: 'markDelivery_form',
                        frame: true,
                        bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 5px"},
                        labelAlign: 'top',
                        border: false,
                        autoHeight: true,
                        autoWidth: true,
                        items: [{
                                layout: 'column',
                                hidden: true,
                                items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    hidden: true,
                                    items: [{
                                            xtype: 'radiogroup',
                                            fieldLabel: '',
                                            allowBlank: true,
                                            anchor: '90%',
                                            hidden: true,
                                            id: 'deliverReturnType',
                                            items: [{
                                                    boxLabel: 'Delivered',
                                                    name: 'rb-auto',
                                                    inputValue: 'Delivered',
                                                    labelWidth: 50,
                                                    allowBlank: true,
                                                    anchor: '95%',
                                                    tabIndex: 10,
                                                    hidden: true
                                                }, {
                                                    boxLabel: 'Returned',
                                                    name: 'rb-auto',
                                                    inputValue: 'Returned',
                                                    labelWidth: 50,
                                                    allowBlank: true,
                                                    hidden: true,
                                                    anchor: '95%',
                                                    tabIndex: 15
                                                }]
                                        }]
                                }]
                            }, {
                                layout: 'column',
                                items: [{
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [{
                                            xtype: 'radiogroup',
                                            fieldLabel: '',
                                            allowBlank: true,
                                            anchor: '90%',
                                            id: 'deliveredAmtType',
                                            items: [{
                                                    boxLabel: 'Cash',
                                                    name: 'dat-auto',
                                                    inputValue: 'Cash',
                                                    labelWidth: 50,
                                                    anchor: '95%',
                                                    tabIndex: 20
                                                }, {
                                                    boxLabel: 'Bank',
                                                    name: 'dat-auto',
                                                    inputValue: 'Bank',
                                                    labelWidth: 50,
                                                    anchor: '95%',
                                                    tabIndex: 25
                                                }],
                                            listeners: {
                                                change: function (event, checked) {
                                                    var current_firstid = event.items.items[0].inputValue;
                                                    var current_secondid = event.items.items[1].inputValue;
                                                    //var current_thirdid = event.items.items[2].inputValue;
                                                    var radioid = Ext.getCmp('deliveredAmtType').getValue();
                                                    console.log('current_secondid', current_secondid);
                                                    console.log('radioid', radioid);
                                                    if (radioid == current_secondid)
                                                    {
                                                        Ext.getCmp('bankTransactionNo').show();
                                                    } else if (radioid == current_firstid)
                                                    {
                                                        Ext.getCmp('bankTransactionNo').hide();
                                                    }
                                                }
                                            }
                                        }]
                                },{
                                    layout: 'form',
                                    columnWidth: .5,
                                    items: [{
                                            fieldLabel: 'Transaction Id',
                                            id: 'bankTransactionNo',
                                            name: 'bankTransactionNo',
                                            xtype: 'textfield',
                                            labelAlign: 'top',
                                            anchor: '98%',
                                            minLength: 2,
                                            allowBlank: true,
                                            maxLength: 300,
                                            tabIndex: 30,
                                            hidden: true
                                        }]
                                }]
    
                            },{
                                layout: 'column',
                                items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [{
                                            fieldLabel: 'Remarks',
                                            id: 'deliverReturnRemarks',
                                            name: 'deliverReturnRemarks',
                                            xtype: 'textarea',
                                            labelAlign: 'top',
                                            allowBlank: false,
                                            anchor: '98%',
                                            minLength: 2,
                                            maxLength: 1400,
                                            tabIndex: 35,
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1000);
                                                },
                                            }
                                        }]
                                }]
    
                            }, {
                                layout: 'column',
                                items: [{
                                        layout: 'column',
                                        columnWidth: 1,
                                        items: [{
                                                layout: 'form',
                                                columnWidth: .5,
                                                items: [{
                                                        fieldLabel: 'Date',
                                                        labelAlign: 'left',
                                                        xtype: 'datefield',
                                                        format: 'Y-m-d H:i:s',
                                                        id: 'deliverReturnDate',
                                                        name: 'deliverReturnDate',
                                                        anchor: '98%',
                                                        tabIndex: 40,
                                                        allowBlank: false,
                                                        value: new Date().format("Y-m-d H:i:s")
                                                    }]
                                            }]
                                    }]
    
                            }],
                        listeners: {
                            afterrender: function () {
                                console.log('quor_Paymode', quor_Paymode);
                                if (quor_Paymode == 'Internal' || quor_Paymode == 'Paid Online') {
                                    Ext.getCmp('deliveredAmtType').hide();
                                    Ext.getCmp('deliveredAmtType').allowBlank = true;
                                    Ext.getCmp('bankTransactionNo').hide();
                                } else {
                                    Ext.getCmp('deliveredAmtType').show();
                                    Ext.getCmp('deliveredAmtType').allowBlank = false;
                                    //Ext.getCmp('bankTransactionNo').show();
                                }
                            }
                        }
                    }),
                    buttons: [{
                            text: 'Cancel', icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            tabIndex: 45,
                            handler: function () {
                                markDeliver_win.close();
                            }
                        }, {
                            text: 'Submit',
                            iconCls: 'icon_approve',
                            tabIndex: 50,
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            handler: function () {
                                if (Ext.getCmp('markDelivery_form').getForm().isValid()) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=markDeliverer',
                                        method: 'POST',
                                        params: {
                                            orderid: orderid,
                                            deliverReturnType: 'Delivered',
                                            deliverReturnDate: Ext.getCmp('deliverReturnDate').getValue(),
                                            deliverReturnRemarks: Ext.getCmp('deliverReturnRemarks').getValue(),
                                            deliveredAmtType: Ext.getCmp('deliveredAmtType').getValue(),
                                            bankTransactionNo: Ext.getCmp('bankTransactionNo').getValue(),
                                        },
                                        success: function (res) {
                                            var tmp = Ext.decode(res.responseText);
                                            if (tmp.success === true) {
                                                markDeliver_win.close();
                                                Ext.getCmp('scheduled_jobs_grid').getStore().load();
                                            }
                                        },
                                        failure: function (response) {
                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            Ext.MessageBox.alert("Error", tmp.message);
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                                }
    
                            }
                        }]
                });
    
                markDeliver_win.show();
                markDeliver_win.doLayout();
                markDeliver_win.center();
            }
    
        }; //////////////////////////////EO Public Area///////////////////////////////
    }
    ();
    
    