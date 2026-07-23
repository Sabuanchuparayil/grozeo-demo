Application.ScheduledJobs = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 16;
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }
    var modURL = '?module=retaline_scheduledjobs';

    var loadMask = function (ind) {
        return new Ext.LoadMask(Ext.getCmp('main_panelScheduledJobs').getEl(),
                {msg: "Please wait..."});
    };
    var diffArray = function (a, b) {
        var seen = [], diff = [];
        for (var i = 0; i < b.length; i++)
            seen[b[i]] = true;
        for (var i = 0; i < a.length; i++)
            if (!seen[a[i]])
                diff.push(a[i]);
        return diff;
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
    var scheduledJobsPanel = function (id, ind, title) {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: title,
            id: id,
            iconCls: 'scheduled',
            buttonAlign: "right",
            items: [scheduledJobsGrid(ind),
                new Ext.Panel({
                    title: 'Order Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelScheduledJobsViewDetails',
                    height: winsize.height * 0.6,
                    items: [
                        ScheduledJobsDetailsView()
                    ],
                    fbar: [{
                            text: 'View Delivery Challan',
                            iconCls: 'finascop_viewfile',
                            tooltip: 'View Delivery Challan',
                            id: 'schJobviewDeliveryChallan',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("schedule_grid_schJob").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.ScheduledJobs.viewDeliveryChallan(quor_id);
                            }
                        }, {
                            text: 'View Drive Polls',
                            iconCls: 'list_users',
                            tooltip: 'View Drive Polls',
                            id: 'schJobviewPolledHistory',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("schedule_grid_schJob").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.ScheduledJobs.viewPolledHistory(quor_id);
                            }
                        }

                    ]
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
    var scheduledJobsGrid = function (ind) {
        var chk_model = check_model();
        var qugeo_store = scheduledJobsStore(ind);
        var qugeo_select = qugeoSelect();
        var scheduledJobsFilter = new Ext.ux.grid.GridFilters({
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
                    type: 'string',
                    dataIndex: 'quor_TypeName'
                },
                {
                    type: 'string',
                    dataIndex: 'booked_at'
                },
                {
                    type: 'list',
                    options: ['WAITING FOR DESPATCH', 'DESPATCHED - IN TRANSIT', 'DAMAGED WHILE TRANSIT', 'AWAITING SALES TAX CLEARANCE', 'INCOMPLETE RECEIPT', 'ITEM NOT RECEIVED', 'ITEM PARTLY RECEIVED', 'FAILED-IN TRANSIT',
                        'RECEIVED IN GODOWN', 'SEND FOR DOOR DELIVERY', 'DELIVERY FAILED - DOOR LOCKED', 'DELIVERY FAILED - REFUSED', 'DELIVERY FAILED - ADDRESS NOT FOUND', 'INCOMPLETE DELIVERY', 'DELIVERY FAILED - DAMAGED',
                        'DELIVERY COMPLETED', 'RE-ROUTED', 'RE-BOOKED', 'ARBITRATED', 'PARTIAL DISPATCH', 'PARTIAL RECEIVE', 'PARTIAL DELIVERY', 'PICKUP - AT ORIGIN', 'PICKUP - POLLED', 'PICKUP - POLL REJECTED',
                        'PICKUP - POLL NO RESPONSE', 'PICKUP - HOME BRANCH FLAGGED', 'PICKUP - DIRECT DELIVERY FLAGGED', 'PICKUP - HOME BRANCH PICKED UP', 'PICKUP - DIRECT DELIVERY PICKED UP', 'PICKUP - BOOKING CANCELLED',
                        'PICKED UP - WAITING FOR ASSIGNMENT', 'DELIVERY - POLLED', 'DELIVERY - POLL REJECTED', 'DELIVERY - POLL NO RESPONSE', 'PICKUP FAILED - DOOR LOCKED', 'PICKUP FAILED - ADDRESS NOT FOUND',
                        'PICKUP FAILED - ITEM NOT READY', 'DELIVERY - DELIVERED BUT NOT CONFIRMED', 'DESPATCHED - VIA CROSS BOOKING', 'CANCELLED'],
                    phpMode: true,
                    dataIndex: 'dls_DelStatus'
                }]
        });
        scheduledJobsFilter.remote = true;
        scheduledJobsFilter.autoReload = true;
        var qugeo_grid = new Ext.grid.GridPanel({
            store: qugeo_store,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [new Ext.ux.grid.GroupSummary(), scheduledJobsFilter],
            id: 'schedule_grid_' + ind,
            columns: [chk_model, {
                    header: 'Order No.',
                    sortable: true,
                    dataIndex: 'booking_no',
                    width: 80
                }, {
                    header: 'Order Date',
                    sortable: true,
                    width: 80,
                    dataIndex: 'orgOrderDate'
                }, {
                    header: 'Created Date',
                    sortable: true,
                    hidden: true,
                    width: 80,
                    dataIndex: 'booked_at',
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                }, {
                    header: 'Delivery Date',
                    sortable: true,
                    width: 80,
                    dataIndex: 'quor_slot_date'
                }, {
                    header: 'Delivery Slot',
                    sortable: true,
                    dataIndex: 'quor_slot_delivery',
                    width: 80
                }, {
                    header: 'Location',
                    sortable: true,
                    dataIndex: 'quor_DeliveryAddress',
                    hidden: true
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
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'quor_TypeName',
                    width: 80,
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'dls_DelStatus',
                    width: 120
                }, {
                    header: 'STH Opening Time',
                    sortable: true,
                    dataIndex: 'quor_ScheduleOpeningTime',
                    tooltip: 'Scheduled Opening Time',
                    hidden: true
                }, {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'quor_DeliveryName',
                }, {
                    header: 'Customer Contact',
                    sortable: true,
                    dataIndex: 'quor_DeliveryPhone',
                }, {
                    hidden: true,
                    header: 'Contact Name',
                    sortable: true,
                    dataIndex: 'quor_PickupName'
                }, {
                    header: 'Contact NO',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'quor_PickupPhone'
                }, {
                    header: 'Location',
                    sortable: true,
                    dataIndex: 'destination'
                }
            ],
            sm: chk_model,
            listeners: {
                resize: updatePagination,
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
                        Ext.getCmp('schJobManualSchedule').show();
                        Ext.getCmp('schJobManualDeliver').show();
                    }
                }, {
                    xtype: 'button',
                    text: 'Show Pending',
                    style: "padding-left: 10px;",
                    id: 'pendingid_show' + ind,
                    handler: function () {
                        var branch = Ext.getCmp('search_branch' + ind).getValue();

                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.pending = 1;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.delivered = 0;
                        Ext.getCmp('schedule_grid_' + ind).getStore().load();
                        Ext.getCmp('schJobManualSchedule').show();
                        Ext.getCmp('schJobManualDeliver').show();
                    }
                }, {
                    xtype: 'button',
                    text: 'Show Delivered',
                    style: "padding-left: 10px;",
                    handler: function () {
                        var branch = Ext.getCmp('search_branch' + ind).getValue();

                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.delivered = 1;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.pending = 0;
                        Ext.getCmp('schedule_grid_' + ind).getStore().load();

                        Ext.getCmp('schJobManualSchedule').hide();
                        Ext.getCmp('schJobManualDeliver').hide();
                    }
                }], bbar: [{
                    text: "Assign To Drive",
                    id: "schJobManualSchedule",
                    iconCls: 'list_users',
                    handler: function () {
                        var selectitem = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var record = [];
                        for (var i = 0; i < selectedcount; i++)
                        {
                            record.push(selectitem[i].data);
                        }
                        Application.ScheduledJobs.pushToDrive(record, 'schJob');
                    }
                }, {
                    text: "Manual Delivery",
                    id: "schJobManualDeliver",
                    iconCls: 'list_users',
                    handler: function () {
                        var selectitem = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var record = [];
                        if (selectedcount > 0) {
                            for (var i = 0; i < selectedcount; i++)
                            {
                                record.push(selectitem[i].data);
                            }
                            Application.RetalinProcurement.manualDelivery(record, 'schJob');
                        }

                    }
                }],
            stripeRows: true
        });
        return qugeo_grid;
    };
    var ScheduledJobsDetailsView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '';
        //var src = '?module=cpd_dispatch&op=dispatchdetailsView&quor_id=' + Application.CPD_Dispatch.Cache.quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'details_view_panel_scheduledJobs',
            region: 'center',
            width: winsize.width * 0.4,
            items: [{
                    region: 'center',
                    defaults: {
                        frame: false
                    },
                    html: '<iframe id="iframe_order_schejobdtls" name="iframe_order_schejobdtls" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>'
                }
            ]
        });
        return contactsPanel;
    };

    var scheduledJobsStore = function (ind) {
        var qugeo_store = new Ext.data.JsonStore({
            url: modURL + '&op=listScheduledJobs',
            fields: ['booking_no', 'customer', 'source', 'destination', 'quor_PickupName', 'quor_PickupPhone', 'quor_Status', 'quor_Deliverybr_id', 'quor_ItemReturned', 'IsPickup', 'sourcecontact', 'quor_Pickupbr_id',
                'drivetype', 'quor_id', 'bk_brk_br_id', 'quor_PickupLat', 'quor_PickupLng', 'quor_DeliveryLat', 'quor_DeliveryLng', 'pickupmapicon', 'deliverymapicon', 'dls_DelStatus', 'quor_TypeName', 'br_Name', 'quor_DeliveryAddress',
                'quor_ScheduleOpeningTime', 'quor_Type', 'quor_DeliveryName', 'quor_DeliveryPhone', 'quor_TransferOrder_Type', 'orgOrderDate', 'orderMethod', 'quor_DeliveryMethodsAllowed', 'quor_slot_date', 'quor_slot_delivery', 'deliveryLocation',
                {
                    name: 'booked_at',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                }],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp('schedule_grid_schJob').getView().refresh();
                    Ext.getCmp('schedule_grid_schJob').getSelectionModel().selectRow(0);
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
        if (!Ext.isEmpty(Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_id;
            var quor_Type = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_Type;
            var quor_Status = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_Status;
            var quor_TransferOrder_Type = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var orderMethod = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var quor_DeliveryMethodsAllowed = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_DeliveryMethodsAllowed;
            Application.ScheduledJobs.Cache.quor_id = ID;
            Application.ScheduledJobs.ViewMode(ID);
            console.log('Application.ScheduledJobs.Cache.vphbutton', Application.ScheduledJobs.Cache.vphbutton);
            if (Application.ScheduledJobs.Cache.vphbutton == 1) {
                Ext.getCmp('schJobviewPolledHistory').show();
            } else {
                Ext.getCmp('schJobviewPolledHistory').hide();
            }
        }
    };
    var branchstore = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getBranch',
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
                        var ID = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_id;
                        var quor_Type = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_Type;
                        var quor_Status = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_Status;
                        var quor_TransferOrder_Type = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
                        var orderMethod = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
                        var quor_DeliveryMethodsAllowed = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_DeliveryMethodsAllowed;
                        var quor_Type = Ext.getCmp('schedule_grid_schJob').getSelectionModel().getSelections()[0].data.quor_Type;
                        Application.ScheduledJobs.Cache.quor_id = ID;
                        Application.ScheduledJobs.ViewMode(ID);
                        console.log('Application.ScheduledJobs.Cache.vphbutton', Application.ScheduledJobs.Cache.vphbutton);
                        var taskStatus = Ext.unique(Ext.pluck(st, 'quor_Status'));
                        var deliveryStatus = ['15', '38'];
                        var assignNomatches = checkInArray(taskStatus, deliveryStatus);
                        console.log(taskStatus);
                        console.log(assignNomatches);

//                        if (taskStatus.length > 1 || assignNomatches == true) {
//                            console.log(taskStatus.length);
//                            Ext.getCmp('schJobManualSchedule').hide();
//                        } else {
//                            Ext.getCmp('schJobManualSchedule').show();
//                        }
                        if (Application.ScheduledJobs.Cache.vphbutton == 1) {
                            Ext.getCmp('schJobviewPolledHistory').show();
                        } else {
                            Ext.getCmp('schJobviewPolledHistory').hide();
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
    var vehicleGrid = function (jobrecord, ind) {
        var store = vehicleStore(jobrecord, ind);
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
                    console.log('record', record);
                    var vehicle_store = grid.getStore();
                    if (vehicle_store.getCount() > 0) {
                        var store = vehicle_store.getRange();
                        /*      var veh_marker = [];*/
                        var a = 1;
                        //Ext.each(store, function (rec) {
                        // console.log('vrec', rec);
                        var mymarker = [];
                        mymarker.push({
                            lat: record.get('Latitude'),
                            lng: record.get('Longitude'),
                            marker: {
                                title: record.get('v_No'),
                                draggable: false
                            },
                            listeners: {
                                click: function () {
                                    var extraInfo = this.extraInfo;
                                    //Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
                                }
                            },
                            icon: record.get('v_MapIcon'),
                            extraInfo: record.data
                        });
                        record.set('vehicle_marker', a);
                        a++;
                        console.log('vehicle_marker', a);
                        Ext.getCmp('location_map' + ind).clearMarkers();
                        console.log('vehicle_marker1', mymarker);
                        loadMarker(jobrecord, ind, mymarker);
                        console.log('vehicle_marker', mymarker);
                        Application.ScheduledJobs.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true);
                        Ext.getCmp('location_map' + ind).animateMarker(Application.ScheduledJobs.Markers[ind][0], 'BOUNCE', 'START');
                        Ext.getCmp('location_map' + ind).getMap().setZoom(10);

                        // });
                    }
                    setTimeout(function () {
                        Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
                        Ext.getCmp('vehicle_disp' + ind).setValue('Vehicle No. : <b>' + record.get('v_No') + '</b>');
                        Ext.getCmp('hdnVehicleId' + ind).setValue(record.get('v_ID'));
                    }, 50);
                    setTimeout(function () {
                        Ext.getCmp('location_map' + ind).animateMarker(Application.ScheduledJobs.Markers[ind][0], 'BOUNCE', 'STOP');
                    }, 2000);


                }
            },
            stripeRows: true
        });
        return grid;
    };
    var vehicleStore = function (jobrecord, ind) {
        var store = new Ext.data.JsonStore({
            //proxy: proxy,
            url: modURL + '&op=loadVehicle',
            fields: ['v_ID', 'v_No', 'Latitude', 'Longitude', 'LastLocationDtTm',
                'DriverName', 'Vehicletypename', 'updated_date', 'vehicle_marker',
                'MaxLoad', 'CurrentLoad', 'v_MapIcon'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    console.log('jobrecord', jobrecord);
                    console.log('jobrecord521', jobrecord[0]['booking_no']);

                    Application.ScheduledJobs.Markers[ind] = [];

                    Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord[0]['booking_no'] + '</b>');
                    Ext.getCmp('hdnVehicleId' + ind).setValue('');
                    Ext.getCmp('vehicle_disp' + ind).setValue('');

                    Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                    var action = jobrecord[0]['drivetype'];
                    if (jobrecord[0]['drivetype'] != 'PICKUP') {
                        var title = jobrecord[0]['quor_DeliveryName'] + ', ' + jobrecord[0]['quor_DeliveryPhone'];
                        var vehlatitude = jobrecord[0]['quor_DeliveryLat'];
                        var vehlongitude = jobrecord[0]['quor_DeliveryLng'];
                        Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                    } else {
                        var title = jobrecord[0]['quor_PickupName'] + ', ' + jobrecord[0]['quor_PickupPhone'];
                        var vehlatitude = jobrecord[0]['quor_PickupLat'];
                        var vehlongitude = jobrecord[0]['quor_PickupLng'];
                        Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                    }
                    Ext.getCmp('location_map' + ind).clearMarkers();

                    if (!Ext.isEmpty(jobrecord[0]['quor_Pickupbr_id'])) {
                        this.baseParams.br_id = jobrecord[0]['quor_Pickupbr_id'];
                        this.baseParams.longitude = vehlongitude;
                        this.baseParams.latitude = vehlatitude;
                        this.baseParams.action = action;
                        this.baseParams.bk_ID = jobrecord[0]['quor_id'];
                    } else
                        return false;


                },
                load: function (vehicle_store, e) {
                    // loadMarker(jobrecord, ind);
                }
            }
        });
        return store;
    };
    var mapPanel = function (id, ind, jobrecord) {
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
            mapControls: ['GSmallMapControl', 'GMapTypeControl', 'NonExistantControl'],
            setCenter: centerParam,
            /* markers: markers,*/
            listeners: {
                afterrender: function () {
                    setTimeout(function () {
                        Ext.getCmp(id).clearMarkers();
                        if (jobrecord) {
                            console.log('jobrecord', jobrecord);
                            console.log('jobrecord588', jobrecord[0]);

                            Application.ScheduledJobs.Markers[ind] = [];

                            Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord[0]['booking_no'] + '</b>');
                            Ext.getCmp('hdnVehicleId' + ind).setValue('');
                            Ext.getCmp('vehicle_disp' + ind).setValue('');

                            Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                            var action = jobrecord[0]['drivetype'];
                            if (jobrecord[0]['drivetype'] != 'PICKUP') {
                                var title = jobrecord[0]['quor_DeliveryName'] + ', ' + jobrecord[0]['quor_DeliveryPhone'];
                                var vehlatitude = jobrecord[0]['quor_DeliveryLat'];
                                var vehlongitude = jobrecord[0]['quor_DeliveryLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                            } else {
                                var title = jobrecord[0]['quor_PickupName'] + ', ' + jobrecord[0]['quor_PickupPhone'];
                                var vehlatitude = jobrecord[0]['quor_PickupLat'];
                                var vehlongitude = jobrecord[0]['quor_PickupLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                            }
                            var mymarker = [];

                            //Ext.getCmp('location_map' + ind).clearMarkers();
                            var bounds = {value: null};
                            loadMarker(jobrecord, ind, mymarker);

                            console.log('vehicle_marker', mymarker);
                            Application.ScheduledJobs.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true, bounds);
                            Ext.getCmp('location_map' + ind).animateMarker(Application.ScheduledJobs.Markers[ind][0], 'BOUNCE', 'START');
                            Ext.getCmp('location_map' + ind).getMap().setZoom(10);

                        }



                        if (id == 'max_map') {
                            Ext.getCmp('max_map').addMarkers(Application.ScheduledJobs.Markers[ind], true);
                        }
                    }, 1000);
                }
            }
        };
    };
    var showMap = function (ind) {

        var panel = new Ext.Panel({
            layout: 'border',
            items: mapPanel('max_map', ind, '')
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
    var loadMarker = function (jobrecord, ind, parkmymarker) {
        var loadingMask = loadMask(ind);
        loadingMask.show();


        var action = jobrecord[0]['drivetype'];
        for (var i = 0; i < jobrecord.length; i++) {
            var title = jobrecord[i]['quor_DeliveryName'] + ', ' + jobrecord[i]['quor_DeliveryPhone'];
            parkmymarker.push({
                lat: jobrecord[i]['quor_DeliveryLat'],
                lng: jobrecord[i]['quor_DeliveryLng'],
                marker: {
                    title: title,
                    draggable: false
                },
                icon: jobrecord[i]['deliverymapicon']/*GMAP_LOCATION_ICON*/
            });
        }


        if (jobrecord[0]['drivetype'] == 'PICKUP') {
            title = '';
            title = jobrecord[0]['quor_PickupName'] + ', ' + jobrecord[0]['quor_PickupPhone'];
            Ext.getCmp('save_schedule' + ind).setText('To Pickup');
            parkmymarker.push({
                lat: jobrecord[0]['quor_PickupLat'],
                lng: jobrecord[0]['quor_PickupLng'],
                marker: {
                    title: title,
                    draggable: false
                },
                icon: jobrecord[0]['pickupmapicon']/*GMAP_LOCATION_ICON*/
            });
        } else {
            Ext.getCmp('save_schedule' + ind).setText('To Deliver');
        }
        loadingMask.hide();
        // Application.DeliveryJobs.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(parkmymarker, true);
        // Ext.getCmp('location_map' + ind).getMap().setZoom(10);
    };
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                    v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
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
        var selectitem = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections();
        var selectedcount = selectitem.length;
        var quor_idarr = [];
        for (var i = 0; i < selectedcount; i++)
        {
            quor_idarr.push(selectitem[i].data.quor_id);
        }

        if (!Ext.isEmpty(sel) && !Ext.isEmpty(Ext.getCmp('hdnVehicleId' + ind).getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveQugeo',
                method: 'POST',
                params: {
                    quorIds: Ext.encode(quor_idarr),
                    qugeobk_NO: sel.get('quor_id'), //quor_Deliverybr_id,quor_Pickupbr_id
                    br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id')),
                    hdnVehicleId: Ext.getCmp('hdnVehicleId' + ind).getValue(),
                    type: sel.get('drivetype'),
                    handling_br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id')),
                    uniqueId: uuidv4()
                },
                success: function (res) {

                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success == true) {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', tmp.msg, function (btn) {
                            if (btn == 'ok')
                                Ext.getCmp('pushToDriveWindow').close();
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
    return {
        Cache: {},
        addScheduledJobs: function () {
            Application.ScheduledJobs.Markers = [];
            Application.ScheduledJobs.Markers['schJob'] = [];
            var main_panel_id = 'main_panelScheduledJobs';
            var main_panel = Ext.getCmp(main_panel_id);
            var title = 'Scheduled Delivery';
            if (Ext.isEmpty(main_panel)) {
                main_panel = scheduledJobsPanel(main_panel_id, 'schJob', title);
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
            } else {
                Application.UI.addTab(main_panel);
            }
        }, ViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var quor_id = arguments[0];
            Ext.getCmp('details_view_panel_scheduledJobs').show();
            Ext.getCmp("panelScheduledJobsViewDetails").doLayout();
            console.log('ViewMode');
            Ext.get('iframe_order_schejobdtls').dom.src = modURL + '&op=order_details_viewschJob&quor_id=' + quor_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
        }, pushToDrive: function (jobrecord, ind) {
            var _addNewWindow = new Ext.Window({
                title: 'Push to Drive',
                layout: 'border',
                width: winsize.width * 0.4,
                height: winsize.height * 0.9,
                resizable: false,
                id: 'pushToDriveWindow',
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [new Ext.TabPanel({
                        border: false,
                        activeTab: 0,
                        region: 'north',
                        height: winsize.height * 0.3,
                        deferredRender: false,
                        id: 'vehicle_tab' + ind,
                        items: [{
                                title: 'Live Vehicles',
                                layout: 'fit',
                                items: vehicleGrid(jobrecord, ind)
                            }],
                    }), mapPanel('location_map' + ind, ind, jobrecord)],
                tools: [{
                        id: 'maximize',
                        handler: function () {
                            showMap(ind);
                        }
                    }],
                tbar: ['->', {
                        text: 'Refresh',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var action = jobrecord[0]['drivetype'];
                            if (jobrecord[0]['drivetype'] != 'PICKUP') {
                                var title = jobrecord[0]['destination'];
                                var vehlatitude = jobrecord[0]['quor_DeliveryLat'];
                                var vehlongitude = jobrecord[0]['quor_DeliveryLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                            } else {
                                var title = jobrecord[0]['source'];
                                var vehlatitude = jobrecord[0]['quor_PickupLat'];
                                var vehlongitude = jobrecord[0]['quor_PickupLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                            }
                            Ext.getCmp('vehicle_griddj').getStore().load({
                                params: {
                                    br_id: jobrecord[0]['quor_Pickupbr_id'],
                                    longitude: vehlongitude,
                                    latitude: vehlatitude,
                                    action: action,
                                    bk_ID: jobrecord[0]['quor_id'],
                                    radiogroupdriverType_type: Ext.getCmp('radiogroupdriverType_type').getValue()
                                }
                            });
                        }
                    }],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 511,
                        handler: function () {
                            _addNewWindow.close();
                        }
                    }, {
                        text: 'Assign',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        id: 'save_schedule' + ind,
                        handler: function () {
                            assignSchedule(ind);
                        }
                    }], bbar: [{
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
                    }],
                listeners: {
                    afterrender: function () {
                    }
                }
            });
            _addNewWindow.doLayout();
            _addNewWindow.show();
            _addNewWindow.center();
        }
    }
}();
