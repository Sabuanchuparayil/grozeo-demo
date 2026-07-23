Application.DeliveryJobs = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 16;
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }
    var modURL = '?module=retaline_deliveryjobs';

    var loadMask = function (ind) {
        return new Ext.LoadMask(Ext.getCmp('main_panelDeliveryJobs').getEl(),
                {msg: "Please wait..."});
    };
    
    function defineMenuinDeliJobs(statusSum) {
        var arr = [];
        arr[0] = 0;//'NoDelivery';
        arr[1] = 1;//'Drive';
        arr[2] = 2;//'Hired';
        arr[3] = 4;//'CustomerPickup';
        arr[4] = 8//'Courier';
        arr[5] = 16//'DriverPickup';
        arr[6] = 32//'ManualDelivery';
        Ext.getCmp('djManualSchedule').hide();
        Ext.getCmp('djHire').hide();
        Ext.getCmp('djCustomerPickUp').hide();
        Ext.getCmp('djCourier').hide();
        Ext.getCmp('djDriverPickUp').hide();
        Ext.getCmp('djManualDeliver').hide();
        arr.forEach(function (entry) {
            if ((statusSum & entry) == entry) {
                console.log('entry', entry);
                switch (entry) {
                    case 1:
                        console.log('1Manual');
                        Ext.getCmp('djManualSchedule').show();
                        break;
                    case 2:
                        console.log('2Hire');
                        Ext.getCmp('djHire').show();
                        break;
                    case 4:
                        console.log('4Customer');
                        Ext.getCmp('djCustomerPickUp').show();
                        break;
                    case 8:
                        console.log('8Courier');
                        Ext.getCmp('djCourier').show();
                        break;
                    case 16:
                        console.log('16driver');
                        Ext.getCmp('djDriverPickUp').show();
                        break;
                    case 32:
                        console.log('32deliver');
                        Ext.getCmp('djManualDeliver').show();
                        break;
                }
            }
        });
    }
    ;
    
    var gridSelectionChanged = function () {
        if (!Ext.isEmpty(Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0].data.quor_id;
            var quor_Type = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0].data.quor_Type;
            var quor_Status = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0].data.quor_Status;
            var quor_TransferOrder_Type = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var orderMethod = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var quor_DeliveryMethodsAllowed = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0].data.quor_DeliveryMethodsAllowed;
            defineMenuinDeliJobs(quor_DeliveryMethodsAllowed);
            Application.DeliveryJobs.Cache.quor_id = ID;
            Application.DeliveryJobs.ViewMode(ID);
//            if ((quor_Status == 22) && (quor_Type == 1)) {
//                Ext.getCmp('djpushToDrive').show();
//            }
//
//            if ((quor_Status == 22) && (quor_Type == 2)) {
//                Ext.getCmp('djDispatch').show();
//            }
//            if (quor_TransferOrder_Type == 3) {
//                Ext.getCmp('djCustomerPickUp').hide();
//                Ext.getCmp('djDriverPickUp').show();
//            } else {
//                Ext.getCmp('djCustomerPickUp').show();
//                Ext.getCmp('djDriverPickUp').hide();
//            }
//            if ((quor_Type == 1) || (quor_Type == 2) || (quor_Status != 40)) {
//                Ext.getCmp('djviewDeliveryChallan').show();
//            }
//            switch (orderMethod) {
//                case 1://Home Deliver
//                    break;
//                case 2://Collectfromstore
//                    Ext.getCmp('djCustomerPickUp').show();
//                    Ext.getCmp('djHire').hide();
//                    Ext.getCmp('djCourier').hide();
//                    Ext.getCmp('djManualSchedule').hide();
//                    Ext.getCmp('djManualDeliver').hide();
//
//                    break;
//                case 3://courier
//                    Ext.getCmp('djCustomerPickUp').hide();
//                    Ext.getCmp('djHire').hide();
//                    Ext.getCmp('djCourier').show();
//                    Ext.getCmp('djManualSchedule').hide();
//                    Ext.getCmp('djManualDeliver').hide();
//                    break;
//            }
            console.log('Application.DeliveryJobs.Cache.vphbutton', Application.DeliveryJobs.Cache.vphbutton);
            if (Application.DeliveryJobs.Cache.vphbutton == 1) {
                Ext.getCmp('djviewPolledHistory').show();
            } else {
                Ext.getCmp('djviewPolledHistory').hide();
            }



        }
    };
    var deliveryJobsStore = function (ind) {
        var qugeo_store = new Ext.data.JsonStore({
            url: modURL + '&op=listDeliveryJobs',
            fields: ['booking_no', 'customer', 'source', 'destination', 'quor_PickupName', 'quor_PickupPhone', 'quor_Status', 'quor_Deliverybr_id', 'quor_ItemReturned', 'IsPickup', 'sourcecontact', 'quor_Pickupbr_id',
                'drivetype', 'quor_id', 'bk_brk_br_id', 'quor_PickupLat', 'quor_PickupLng', 'quor_DeliveryLat', 'quor_DeliveryLng', 'pickupmapicon', 'deliverymapicon', 'dls_DelStatus', 'quor_TypeName', 'br_Name',
                'quor_ScheduleOpeningTime', 'quor_Type', 'quor_DeliveryName', 'quor_DeliveryPhone', 'quor_TransferOrder_Type', 'orgOrderDate', 'orderMethod', 'quor_DeliveryMethodsAllowed', {
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
                    Ext.getCmp('schedule_grid_dj').getView().refresh();
                    Ext.getCmp('schedule_grid_dj').getSelectionModel().selectRow(0);
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
    var djGridActionMenu = function () {
        return new Ext.menu.Menu({
            items: [{
                    text: "Dispatch via Hired Vehicle",
                    id: "djHire",
                    hidden: true,
                    handler: function () {
                        var record = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0];
                        var quor_id = record.data.quor_id;
                        var quor_Type = record.data.quor_Type;
                        var quor_Status = record.data.quor_Status;
                        Application.DeliveryJobs.orderDispatch(quor_id, quor_Type, quor_Status);
                    }
                }, {
                    text: "Customer Pickup",
                    id: "djCustomerPickUp",
                    hidden: true,
                    handler: function () {
                        var record = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0];
                        var quor_id = record.data.quor_id;
                        var quor_Type = record.data.quor_Type;
                        var quor_Status = record.data.quor_Status;
                        Application.DeliveryJobs.orderCustomerPickUp(quor_id, quor_Type, quor_Status);
                    }
                }, {
                    text: "Driver Pickup",
                    id: "djDriverPickUp",
                    hidden: true,
                    handler: function () {
                        var record = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0];
                        var quor_id = record.data.quor_id;
                        var quor_Type = record.data.quor_Type;
                        var quor_Status = record.data.quor_Status;
                        Application.DeliveryJobs.orderDriverPickUp(quor_id, quor_Type, quor_Status);
                    }
                }, {
                    text: "Dispatch via Courier / Cargo",
                    id: "djCourier",
                    hidden: true,
                    handler: function () {
                        var record = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0];
                        var quor_id = record.data.quor_id;
                        var quor_Type = record.data.quor_Type;
                        var quor_Status = record.data.quor_Status;
                        Application.DeliveryJobs.orderbyCourierCargo(quor_id, quor_Type, quor_Status);

                    }
                }, {
                    text: "Assign To Drive",
                    id: "djManualSchedule",
                    handler: function () {
                        var record = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0];
                        var quor_id = record.data.quor_id;
                        var quor_Type = record.data.quor_Type;
                        var quor_Status = record.data.quor_Status;
                        Application.DeliveryJobs.pushToDrive(record, 'dj');
                    }
                }, {
                    text: "Manually Delivered",
                    id: "djManualDeliver",
                    handler: function () {
                        var record = Ext.getCmp('schedule_grid_dj').getSelectionModel().getSelections()[0];
                        var quor_id = record.data.quor_id;
                        var quor_Type = record.data.quor_Type;
                        var quor_Status = record.data.quor_Status;
                        Application.DeliveryJobs.manualDelivery(quor_id, quor_Type, quor_Status);
                    }
                }]
        });
    };
    var deliveryJobsGrid = function (ind) {
        var djGridActionColumn = djGridActionMenu();
        var qugeo_store = deliveryJobsStore(ind);
        var qugeo_select = qugeoSelect();
        var deliveryJobsFilter = new Ext.ux.grid.GridFilters({
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
        deliveryJobsFilter.remote = true;
        deliveryJobsFilter.autoReload = true;
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
            plugins: [new Ext.ux.grid.GroupSummary(), deliveryJobsFilter],
            id: 'schedule_grid_' + ind,
            columns: [{
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
                    width: 80,
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
                    dataIndex: 'quor_PickupPhone'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    // icon: './resources/images/submenuicons/action.png',
                    items: [
                        {
                            getClass: function (v, meta, rec) {
                                if (((rec.get('quor_Status') == 22) || (rec.get('quor_Status') == 31)) && (rec.get('quor_Type') == 0)) {
                                    this.items[0].tooltip = 'Choose Actions';
                                    return 'actioncol';
                                } else {
                                    return 'hideicon';
                                }
                            }
                        },
                        {
                            getClass: function (v, meta, rec) {
                                if ((((rec.get('quor_Status') >= 22) && (rec.get('quor_Status') <= 29)) || ((rec.get('quor_Status') >= 32) && (rec.get('quor_Status') <= 37))) && (rec.get('quor_Type') == 1)) {
                                    return 'color-trigger';
                                } else {
                                    return 'hideicon';
                                }
                            }
                        }
                    ], listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            if (((record.data.quor_Status == 22) || (record.data.quor_Status == 31)) && (record.data.quor_Type == 0)) {
                                djGridActionColumn.showAt(e.getXY());
                            }

                        }
                    }
//                    ,

                }
            ],
            sm: qugeo_select,
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
    var showData = function (ind) {
        var branch = Ext.getCmp('search_branch' + ind).getValue();

        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
        Ext.getCmp('schedule_grid_' + ind).getStore().load();

    };
    var DeliveryJobsDetailsView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '';
        //var src = '?module=cpd_dispatch&op=dispatchdetailsView&quor_id=' + Application.CPD_Dispatch.Cache.quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'details_view_panel_deliveryjobs',
            region: 'center',
            width: winsize.width * 0.4,
            items: [{
                    region: 'center',
                    defaults: {
                        frame: false
                    },
                    html: '<iframe id="iframe_order_deljobdtls" name="iframe_order_deljobdtls" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>'
                }
            ]
        });
        return contactsPanel;
    };
    var deliveryJobsPanel = function (id, ind, title) {
        //var src = '?module=retaline_deliveryjobs&op=order_details_viewdj&quor_id=' + Application.DeliveryJobs.Cache.quor_id;
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: title,
            id: id,
            iconCls: 'scheduled',
            buttonAlign: "right",
            items: [deliveryJobsGrid(ind),
                new Ext.Panel({
                    title: 'Order Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelDeliveryJobsViewDetails',
                    height: winsize.height * 0.6,
                    items: [
                        DeliveryJobsDetailsView()
                    ],
                    fbar: [{
                            text: 'View Delivery Challan',
                            iconCls: 'finascop_viewfile',
                            tooltip: 'View Delivery Challan',
                            id: 'djviewDeliveryChallan',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("schedule_grid_dj").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.DeliveryJobs.viewDeliveryChallan(quor_id);
                            }
                        }, {
                            text: 'View Drive Polls',
                            iconCls: 'list_users',
                            tooltip: 'View Drive Polls',
                            id: 'djviewPolledHistory',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("schedule_grid_dj").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.DeliveryJobs.viewPolledHistory(quor_id);
                            }
                        }
//                        {
//                            getClass: function (v, meta, rec) {
//                                if ((rec.get('quor_Status') > 22) && (rec.get('quor_Status') < 38)) {
//                                    this.items[1].tooltip = 'View Jobs and Route';
//                                    return 'my-icon10';
//                                }
//                                else {
//                                    return 'hideicon';
//                                }
//                            },
//                            handler: function (grid, rowIndex, colIndex) {
//                                var record = grid.store.getAt(rowIndex);
//                                Application.Qugeo.JobsRoute(record.get('quor_id'), 'Order', 1);
//                            }
//                        },
//                        {
//                            text: 'Dispatch',
//                            iconCls: 'dispatch',
//                            tooltip: 'Dispatch',
//                            id: 'djDispatch',
//                            hidden: true,
//                            getClass: function (v, meta, rec) {
//                                if ((rec.get('quor_Status') >= 23) && (rec.get('quor_Status') <= 38)) {
//                                    this.items[0].tooltip = 'Delivery Update';
//                                    return 'application_view_detail';
//                                }
//                                else {
//                                    return 'hideicon';
//                                }
//                            },
//                            handler: function (grid, rowIndex, colIndex) {
//                                var record = grid.store.getAt(rowIndex);
//                                Application.DeliveryJobs.verify_btn_window(record);
//                            }
//                        }
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

                            Application.DeliveryJobs.Markers[ind] = [];

                            Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord.get('booking_no') + '</b>');
                            Ext.getCmp('hdnVehicleId' + ind).setValue('');
                            Ext.getCmp('vehicle_disp' + ind).setValue('');

                            Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                            var action = jobrecord.get('drivetype');
                            if (jobrecord.get('drivetype') != 'PICKUP') {
                                var title = jobrecord.get('quor_DeliveryName') + ', ' + jobrecord.get('quor_DeliveryPhone');
                                var vehlatitude = jobrecord.get('quor_DeliveryLat');
                                var vehlongitude = jobrecord.get('quor_DeliveryLng');
                                Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                            } else {
                                var title = jobrecord.get('quor_PickupName') + ', ' + jobrecord.get('quor_PickupPhone');
                                var vehlatitude = jobrecord.get('quor_PickupLat');
                                var vehlongitude = jobrecord.get('quor_PickupLng');
                                Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                            }
                            var mymarker = [];

                            //Ext.getCmp('location_map' + ind).clearMarkers();
                            var bounds = {value: null};
                            loadMarker(jobrecord, ind, mymarker);

                            console.log('vehicle_marker', mymarker);
                            Application.DeliveryJobs.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true, bounds);
                            Ext.getCmp('location_map' + ind).animateMarker(Application.DeliveryJobs.Markers[ind][0], 'BOUNCE', 'START');
                            Ext.getCmp('location_map' + ind).getMap().setZoom(10);

//                        if (!Ext.isEmpty(jobrecord.get('quor_Pickupbr_id'))) {
//                            this.baseParams.br_id = jobrecord.get('quor_Pickupbr_id');
//                            this.baseParams.longitude = vehlongitude;
//                            this.baseParams.latitude = vehlatitude;
//                            this.baseParams.action = action;
//                            this.baseParams.bk_ID = jobrecord.get('quor_id');
//                        } else
//                            return false;

                        }



                        if (id == 'max_map') {
                            Ext.getCmp('max_map').addMarkers(Application.DeliveryJobs.Markers[ind], true);
                        }
                    }, 1000);
                }
            }
        };
    };

    var hiredVehicleStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['v_ID', 'v_No'],
            url: modURL + '&op=gethiredVehicleStore',
            method: 'post',
            autoLoad: true,
        });
        return store;
    };
    var courierCargoStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['mst_courier_id', 'mst_courier_name'],
            url: modURL + '&op=getcourierCargoStore',
            method: 'post',
            autoLoad: true,
        });
        return store;
    };
    var customerPickupForm = function () {
        var _customerPickupFormPanel = new Ext.form.FormPanel({
            width: winsize.width * 0.4,
            height: winsize.height * 0.5,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            layout: 'column',
            //bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.34,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Pickup By',
                            id: 'textfieldDispatchPickup',
                            name: 'textfieldDispatchPickup',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 800,
                            maxLength: 150,
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1000);
                                },
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Mobile Number',
                            id: 'textfieldDispatchPickupMobile',
                            name: 'textfieldDispatchPickupMobile',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 810,
                            maxLength: 10
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'OTP',
                            id: 'textfieldDispatchPickupOtp',
                            name: 'textfieldDispatchPickupOtp',
                            anchor: '95%',
                            allowBlank: true,
                            tabIndex: 820,
                            maxLength: 4
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            id: 'textfieldcpd_pickudate',
                            name: 'textfieldcpd_pickudate',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 830,
                            format: "d/m/Y",
                            maxValue: new Date(),
                            value: new Date()
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            id: 'textfieldcpd_pickuptime',
                            name: 'textfieldcpd_pickuptime',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 840,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }]
                }]
        });
        return _customerPickupFormPanel;
    };
    var discourierCargoForm = function () {
        var _discourierCargoFormFormPanel = new Ext.form.FormPanel({
            width: winsize.width * 0.4,
            height: winsize.height * 0.5,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            layout: 'column',
            //bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'combo',
                            displayField: 'mst_courier_name',
                            valueField: 'mst_courier_id',
                            mode: 'local',
                            id: 'dispatchCourier',
                            forceSelection: true,
                            fieldLabel: 'Cargo / Courier',
                            emptyText: 'Select',
                            anchor: '95%',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            editable: true,
                            allowBlank: false,
                            minChars: 2,
                            tabIndex: 300,
                            store: courierCargoStore(),
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1000);
                                },
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'QCN Number',
                            id: 'textfieldQCNnumber',
                            name: 'textfieldQCNnumber',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 310,
                            maxLength: 20
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            id: 'textfieldcpd_couriredate',
                            name: 'textfieldcpd_couriredate',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 320,
                            format: "d/m/Y",
                            maxValue: new Date(),
                            value: new Date()
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            id: 'textfieldcpd_couriretime',
                            name: 'textfieldcpd_couriretime',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 330,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }]
                }]
        });
        return _discourierCargoFormFormPanel;
    };
    var AddNewDispatchForm = function () {
        var _dispatchFormPanel = new Ext.form.FormPanel({
            width: 100,
            height: 100,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'radiogroup',
                                            labelWidth: 100,
                                            fieldLabel: 'Vehicle Type',
                                            id: 'radiobuttonVehicleType',
                                            items: [{
                                                    boxLabel: 'Leased',
                                                    name: 'rb-auto',
                                                    inputValue: 'Leased',
                                                    labelWidth: 100,
                                                    tabIndex: 500,
                                                    listeners: {
                                                        afterrender: function (field) {
                                                            Ext.defer(function () {
                                                                field.focus(true, 100);
                                                            }, 1000);
                                                        }
                                                    }
                                                },
                                                {
                                                    boxLabel: 'Hired',
                                                    name: 'rb-auto',
                                                    inputValue: 'Hired',
                                                    labelWidth: 100,
                                                    tabIndex: 510,
                                                },
                                            ],
                                            listeners: {
                                                change: function (event, checked)
                                                {
                                                    var radioid = Ext.getCmp('radiobuttonVehicleType').getValue();

                                                    switch (radioid) {
                                                        case 'Leased':
                                                            Ext.getCmp('hiredVehicle').hide();
                                                            Ext.getCmp('hiredVehicle').allowBlank = true;
                                                            break;
                                                        case 'Hired':
                                                            Ext.getCmp('hiredVehicle').show();
                                                            Ext.getCmp('hiredVehicle').allowBlank = false;
                                                            break;
                                                    }
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDriver').reset();
                                                    Ext.getCmp('textfieldDispatchDriverContact').reset();
                                                    Ext.getCmp('textfieldDispatchLrgcn').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDispatchdate').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDispatchtime').reset();
                                                }
                                            }
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'v_No',
                                            valueField: 'v_ID',
                                            mode: 'local',
                                            id: 'hiredVehicle',
                                            forceSelection: true,
                                            fieldLabel: 'Vehicle',
                                            emptyText: 'Select Vehicle',
                                            anchor: '95%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            hidden: true,
                                            tabIndex: 520,
                                            store: hiredVehicleStore(),
                                            listeners: {
                                                select: function (e, b) {
                                                    //var hiredVehicle = Ext.getCmp('hiredVehicle').getRawValue();
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').setValue(Ext.getCmp('hiredVehicle').getRawValue());
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').setReadOnly(true);
                                                }
                                            }
                                        }]
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
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Vehicle Number',
                                            id: 'textfieldcpd_dispatchVehicleNo',
                                            name: 'textfieldcpd_dispatchVehicleNo',
                                            anchor: '95%',
                                            allowBlank: false,
                                            tabIndex: 530,
                                            maxValue: 100,
                                            maxLength: 20,
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Driver',
                                            id: 'textfieldcpd_dispatchDriver',
                                            name: 'textfieldcpd_dispatchDriver',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 540,
                                            maxLength: 100
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Contact No',
                                            id: 'textfieldDispatchDriverContact',
                                            name: 'textfieldDispatchDriverContact',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 550,
                                            maxLength: 20
                                        }]
                                }]
                        }]

                }, {
                    layout: 'column',
                    items: [{
                            columnWidth: 1,
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'LR / GCN Number',
                                            id: 'textfieldDispatchLrgcn',
                                            name: 'textfieldDispatchLrgcn',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 560,
                                            maxLength: 20
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'datefield',
                                            fieldLabel: 'Dispatch Date',
                                            id: 'textfieldcpd_dispatchDispatchdate',
                                            name: 'textfieldcpd_dispatchDispatchdate',
                                            anchor: '95%',
                                            allowBlank: false,
                                            tabIndex: 570,
                                            format: "d/m/Y",
                                            maxValue: new Date(),
                                            value: new Date()
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'timefield',
                                            fieldLabel: 'Dispatch Time',
                                            id: 'textfieldcpd_dispatchDispatchtime',
                                            name: 'textfieldcpd_dispatchDispatchtime',
                                            anchor: '97%',
                                            allowBlank: false,
                                            tabIndex: 580,
                                            minValue: '9:00 AM',
                                            maxValue: '6:00 PM',
                                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                                        }]
                                }]
                        }]

                }]
        });
        return _dispatchFormPanel;
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
                    qugeobk_NO: sel.get('quor_id'), //quor_Deliverybr_id,quor_Pickupbr_id
                    br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id')),
                    hdnVehicleId: Ext.getCmp('hdnVehicleId' + ind).getValue(),
                    type: sel.get('drivetype'),
                    handling_br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id'))
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

                    Application.DeliveryJobs.Markers[ind] = [];

                    Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord.get('booking_no') + '</b>');
                    Ext.getCmp('hdnVehicleId' + ind).setValue('');
                    Ext.getCmp('vehicle_disp' + ind).setValue('');

                    Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                    var action = jobrecord.get('drivetype');
                    if (jobrecord.get('drivetype') != 'PICKUP') {
                        var title = jobrecord.get('quor_DeliveryName') + ', ' + jobrecord.get('quor_DeliveryPhone');
                        var vehlatitude = jobrecord.get('quor_DeliveryLat');
                        var vehlongitude = jobrecord.get('quor_DeliveryLng');
                        Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                    } else {
                        var title = jobrecord.get('quor_PickupName') + ', ' + jobrecord.get('quor_PickupPhone');
                        var vehlatitude = jobrecord.get('quor_PickupLat');
                        var vehlongitude = jobrecord.get('quor_PickupLng');
                        Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                    }
                    Ext.getCmp('location_map' + ind).clearMarkers();

                    if (!Ext.isEmpty(jobrecord.get('quor_Pickupbr_id'))) {
                        this.baseParams.br_id = jobrecord.get('quor_Pickupbr_id');
                        this.baseParams.longitude = vehlongitude;
                        this.baseParams.latitude = vehlatitude;
                        this.baseParams.action = action;
                        this.baseParams.bk_ID = jobrecord.get('quor_id');
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
    var loadMarker = function (jobrecord, ind, parkmymarker) {
        var loadingMask = loadMask(ind);
        loadingMask.show();


        var action = jobrecord.get('drivetype');

        var title = jobrecord.get('quor_DeliveryName') + ', ' + jobrecord.get('quor_DeliveryPhone');
        parkmymarker.push({
            lat: jobrecord.get('quor_DeliveryLat'),
            lng: jobrecord.get('quor_DeliveryLng'),
            marker: {
                title: title,
                draggable: false
            },
            icon: jobrecord.get('deliverymapicon')/*GMAP_LOCATION_ICON*/
        });

        if (jobrecord.get('drivetype') == 'PICKUP') {
            title = '';
            title = jobrecord.get('quor_PickupName') + ', ' + jobrecord.get('quor_PickupPhone');
            Ext.getCmp('save_schedule' + ind).setText('To Pickup');
            parkmymarker.push({
                lat: jobrecord.get('quor_PickupLat'),
                lng: jobrecord.get('quor_PickupLng'),
                marker: {
                    title: title,
                    draggable: false
                },
                icon: jobrecord.get('pickupmapicon')/*GMAP_LOCATION_ICON*/
            });
        } else {
            Ext.getCmp('save_schedule' + ind).setText('To Deliver');
        }



        //var parkmymarker = [];


        loadingMask.hide();
        // Application.DeliveryJobs.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(parkmymarker, true);
        // Ext.getCmp('location_map' + ind).getMap().setZoom(10);
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
                        Application.DeliveryJobs.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true);
                        Ext.getCmp('location_map' + ind).animateMarker(Application.DeliveryJobs.Markers[ind][0], 'BOUNCE', 'START');
                        Ext.getCmp('location_map' + ind).getMap().setZoom(10);

                        // });
                    }
                    setTimeout(function () {
                        Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
                        Ext.getCmp('vehicle_disp' + ind).setValue('Vehicle No. : <b>' + record.get('v_No') + '</b>');
                        Ext.getCmp('hdnVehicleId' + ind).setValue(record.get('v_ID'));
                    }, 50);
                    // Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);



                    setTimeout(function () {
                        Ext.getCmp('location_map' + ind).animateMarker(Application.DeliveryJobs.Markers[ind][0], 'BOUNCE', 'STOP');
                    }, 2000);


                }
            },
            stripeRows: true
        });
        return grid;
    };
    var _gbHistoryStore = function (orderId, orderType, type) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPolledHistoryDj',
                method: 'post'
            }),
            fields: ['boy_name', 'ordersreqtatus', 'created_at', 'updated_at', 'accepted_time', 'scan_start_time', 'last_scan_time', 'completed_time', 'isopenPoll'],
            totalProperty: 'totalCount',
            root: 'data',
            // remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.orderId = orderId;
                }
            }
        });
        return _Store;
    };
    var _gbHistoryGrid = function (orderId) {
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _gbHistoryStore(orderId),
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
                }, {
                    header: 'Is Open Poll',
                    dataIndex: 'isopenPoll',
                    sortable: true,
                    tooltip: 'Is Open Poll',
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
                    header: 'Last Updated At',
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
                    hidden: true,
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforGBHistory').getStore().load({
                        params: {
                            orderId: orderId
                        }
                    });
                }
            }
        });
        return _dispatchgridPanel;
    };
    var driverPickupForm = function () {
        var _customerPickupFormPanel = new Ext.form.FormPanel({
            width: winsize.width * 0.4,
            height: winsize.height * 0.5,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            layout: 'column',
            labelAlign: 'top',
            //bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.34,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Pickup By',
                            id: 'textfieldDispatchPickup',
                            name: 'textfieldDispatchPickup',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 400,
                            maxLength: 150,
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1000);
                                },
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Mobile Number',
                            id: 'textfieldDispatchPickupMobile',
                            name: 'textfieldDispatchPickupMobile',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 410,
                            maxLength: 10,
                            minLength: 10,
                            listeners: {
//                        focusOut: function () {
//                            Ext.Ajax.request({
//                                url: modURL + '&op=sendOtpToCustomer',
//                                method: 'POST',
//                                params: {
//                                    mobile: Ext.getCmp('textfieldDispatchPickupMobile').getValue()
//                                },
//                                success: function (response) {
//                                    var res = Ext.decode(response.responseText);
//                                    if (res.success === true) {
//                                        Ext.MessageBox.alert('Success', 'Removed item from vendor', function (btn) {
//                                            Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
//                                        });
//                                    } else
//                                    {
//                                        Ext.MessageBox.alert('Failed');
//                                    }
//                                }
//                            });
//                        }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'OTP',
                            id: 'textfieldDispatchPickupOtp',
                            name: 'textfieldDispatchPickupOtp',
                            anchor: '95%',
                            allowBlank: true,
                            tabIndex: 420,
                            maxLength: 4
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            id: 'textfieldcpd_pickudate',
                            name: 'textfieldcpd_pickudate',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 430,
                            format: "d/m/Y",
                            maxValue: new Date(),
                            value: new Date()
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            id: 'textfieldcpd_pickuptime',
                            name: 'textfieldcpd_pickuptime',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 440,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }]
                }]
        });
        return _customerPickupFormPanel;
    };

    var manualDeliveryForm = function () {
        var _manualDeliveryFormPanel = new Ext.form.FormPanel({
//            width: winsize.width * 0.4,
//            height: winsize.height * 0.3,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                            layout: 'form',
                            columnWidth: .33,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'datefield',
                                    fieldLabel: 'Date',
                                    id: 'textfieldManualDeliveredDate',
                                    name: 'textfieldManualDeliveredDate',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 10,
                                    format: "d/m/Y",
                                    maxValue: new Date(),
                                    value: new Date(),
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1000);
                                        },
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .33,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'timefield',
                                    fieldLabel: 'Time',
                                    id: 'textfieldManualDeliveredtime',
                                    name: 'textfieldManualDeliveredtime',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 20,
                                    minValue: '9:00 AM',
                                    maxValue: '6:00 PM',
                                    value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .34,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Delivered By',
                                    id: 'textfieldManualDelivered',
                                    name: 'textfieldManualDelivered',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 30,
                                    maxLength: 150
                                }]
                        }]
                }]
        });
        return _manualDeliveryFormPanel;
    };
    return {
        Cache: {},
        addDeliveryJobs: function () {
            Application.DeliveryJobs.Markers = [];
            Application.DeliveryJobs.Markers['dj'] = [];
            var main_panel_id = 'main_panelDeliveryJobs';
            var main_panel = Ext.getCmp(main_panel_id);
            var title = 'Delivery Jobs';
            if (Ext.isEmpty(main_panel)) {
                main_panel = deliveryJobsPanel(main_panel_id, 'dj', title);
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
            } else {
                Application.UI.addTab(main_panel);
            }
        },
        ViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var quor_id = arguments[0];
            Ext.getCmp('details_view_panel_deliveryjobs').show();
            Ext.getCmp("panelDeliveryJobsViewDetails").doLayout();

            Ext.get('iframe_order_deljobdtls').dom.src = modURL + '&op=order_details_viewdj&quor_id=' + quor_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
        },
        pushToDrive: function (jobrecord, ind) {
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
                            var action = jobrecord.get('drivetype');
                            if (jobrecord.get('drivetype') != 'PICKUP') {
                                var title = jobrecord.get('destination');
                                var vehlatitude = jobrecord.get('quor_DeliveryLat');
                                var vehlongitude = jobrecord.get('quor_DeliveryLng');
                                Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                            } else {
                                var title = jobrecord.get('source');
                                var vehlatitude = jobrecord.get('quor_PickupLat');
                                var vehlongitude = jobrecord.get('quor_PickupLng');
                                Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                            }
                            Ext.getCmp('vehicle_griddj').getStore().load({
                                params: {
                                    br_id: jobrecord.get('quor_Pickupbr_id'),
                                    longitude: vehlongitude,
                                    latitude: vehlatitude,
                                    action: action,
                                    bk_ID: jobrecord.get('quor_id'),
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
        }, convertToType: function (quor_id, quor_Type, quor_Status) {
            var _typeConverterWindow = new Ext.Window({
                title: 'Type Converter',
                layout: 'fit',
                width: winsize.width * 0.4,
                height: 150,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white", "padding": "5px"},
                items: [new Ext.Panel({
                        frame: false,
                        hideBorders: true,
                        border: false,
                        labelAlign: 'top',
                        items: [{
                                xtype: 'radiogroup',
                                labelWidth: 100,
                                fieldLabel: 'Type',
                                id: 'radiobuttonQuorType',
                                items: [
                                    {boxLabel: 'Drive', name: 'rb-auto', id: 'quarType1', inputValue: 1, labelWidth: 100},
                                    {boxLabel: 'Hired', name: 'rb-auto', id: 'quarType2', inputValue: 2, labelWidth: 100},
                                    {boxLabel: 'CustomerPickup', name: 'rb-auto', id: 'quarType3', inputValue: 3, labelWidth: 100},
                                    {boxLabel: 'Courier', name: 'rb-auto', id: 'quarType4', inputValue: 4, labelWidth: 100}
                                ]
                            }]
                    })],
                listeners: {
                    afterrender: function () {
                        Ext.getCmp('quarType' + quor_Type).hide();
                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 511,
                        handler: function () {
                            _typeConverterWindow.close();
                        }
                    }, {
                        text: 'Convert',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 511,
                        id: 'assign_pickr',
                        handler: function () {
                            Ext.Ajax.request({
                                url: modURL + '&op=typeConvereter',
                                method: 'POST',
                                params: {
                                    quor_id: quor_id,
                                    quor_Type: quor_Type,
                                    quor_Status: quor_Status,
                                    radiobuttonQuorType: Ext.getCmp('radiobuttonQuorType').getValue()
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true && tmp.valid == true) {
                                        Application.example.msg('Success', tmp.msg);
                                        _typeConverterWindow.close();
                                        showData('dj');
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
                    }]


            });
            _typeConverterWindow.doLayout();
            _typeConverterWindow.show();
            _typeConverterWindow.center();
        }, orderDispatch: function (quor_id, quor_Type, quor_Status) {
            var _dispatchForm = AddNewDispatchForm();
            var _dispatchWindow = new Ext.Window({
                title: 'Dispatch',
                layout: 'fit',
                width: winsize.width * 0.4,
                height: winsize.height * 0.35,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_dispatchForm],
                listeners: {
                    afterrender: function () {
                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 590,
                        handler: function () {
                            _dispatchWindow.close();
                        }
                    }, {
                        text: 'Dispatch',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 600,
                        id: 'assign_pickr',
                        handler: function () {
                            if (_dispatchForm.getForm().isValid()) {
                                Ext.Ajax.request({
                                    url: modURL + "&op=dispatchEntry",
                                    method: "POST",
                                    params: {
                                        vehicle_no: Ext.getCmp("textfieldcpd_dispatchVehicleNo").getValue(),
                                        driver: Ext.getCmp("textfieldcpd_dispatchDriver").getValue(),
                                        driverContact: Ext.getCmp("textfieldDispatchDriverContact").getValue(),
                                        driverLrgcn: Ext.getCmp("textfieldDispatchLrgcn").getValue(),
                                        dispatch_date: Ext.getCmp("textfieldcpd_dispatchDispatchdate").getValue(),
                                        dispatch_time: Ext.getCmp("textfieldcpd_dispatchDispatchtime").getValue(),
                                        quor_id: quor_id
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success", tmp.message);
                                            _dispatchWindow.close();
                                            showData('dj');
                                            _dispatchWindow.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
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
            _dispatchWindow.doLayout();
            _dispatchWindow.show();
            _dispatchWindow.center();
        },
        viewPolledHistory: function (quor_id) {
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
                items: [_gbHistoryGrid(quor_id)],
                fbar: []

            });
            _polledItemsWindow.doLayout();
            _polledItemsWindow.show();
            _polledItemsWindow.center();
        },
        viewDeliveryChallan: function (quor_id) {
            var delveryChallanPanel = Application.DeliveryJobs.createprintDeliveryChallan(quor_id);
            var _viewDeliveryChallanWindow = new Ext.Window({
                title: 'Delivery Challan',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 900,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [delveryChallanPanel],
                buttons: [{
                        text: 'Cancel',
                        iconCls: 'my-icon61',
                        handler: function () {
                            _viewDeliveryChallanWindow.close();
                        }
                    }, {
                        text: 'Print',
                        iconCls: 'my-icon141',
                        handler: function () {
                            var params = {
                                // order_id: order_id,
                                action: 1
                            };
                            //updateOrderStatus(params);
                            iframeRequest.focus();
                            iframeRequest.print();
                            _viewDeliveryChallanWindow.close();
                        }
                    }]

            });
            _viewDeliveryChallanWindow.doLayout();
            _viewDeliveryChallanWindow.show();
            _viewDeliveryChallanWindow.center();
        }, createprintDeliveryChallan: function (quor_id) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var src = '?module=retaline_deliveryjobs&op=deliveryChallanView&quor_id=' + quor_id + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;
            var myPanel = new Ext.Panel({
                layout: 'border',
                height: 500,
                id: 'printLabResultPanel',
                items: [{
                        region: 'center',
                        border: false,
                        html: '<iframe src="' + src +
                                '" id="iframeRequest" name="iframeRequest" ' +
                                'width="100%" height="100%" style="border:none">'
                    }]
            });
            return myPanel;
        },
        orderCustomerPickUp: function (quor_id, quor_Type, quor_Status) {

            var _custPickupForm = customerPickupForm();
            var _custPickupWindow = new Ext.Window({
                title: 'Customer Pickup',
                layout: 'fit',
                width: winsize.width * 0.3,
                height: winsize.height * 0.25,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_custPickupForm],
                listeners: {
                    afterrender: function () {
                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 850,
                        handler: function () {
                            _custPickupWindow.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 860,
                        handler: function () {
                            if (_custPickupForm.getForm().isValid()) {
                                Ext.Ajax.request({
                                    url: modURL + "&op=saveCustomerPickup",
                                    method: "POST",
                                    params: {
                                        picked_by: Ext.getCmp("textfieldDispatchPickup").getValue(),
                                        pickup_mobile: Ext.getCmp("textfieldDispatchPickupMobile").getValue(),
                                        pickup_otp: Ext.getCmp("textfieldDispatchPickupOtp").getValue(),
                                        pickup_date: Ext.getCmp("textfieldcpd_pickudate").getValue(),
                                        pickup_time: Ext.getCmp("textfieldcpd_pickuptime").getValue(),
                                        quor_id: quor_id,
                                        isDriver: 0
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success", tmp.message);
                                            _custPickupWindow.close();
                                            showData('dj');
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
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
            _custPickupWindow.doLayout();
            _custPickupWindow.show();
            _custPickupWindow.center();
        },
        orderbyCourierCargo: function (quor_id, quor_Type, quor_Status) {

            var _courierCargoForm = discourierCargoForm();
            var _courierCargoWindow = new Ext.Window({
                title: 'Courier / Cargo',
                layout: 'fit',
                width: winsize.width * 0.3,
                height: winsize.height * 0.25,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_courierCargoForm],
                listeners: {
                    afterrender: function () {
                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 340,
                        handler: function () {
                            _courierCargoWindow.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 350,
                        handler: function () {
                            if (_courierCargoForm.getForm().isValid()) {
                                Ext.Ajax.request({
                                    url: modURL + "&op=saveByCourier",
                                    method: "POST",
                                    params: {
                                        qoc_courier: Ext.getCmp("dispatchCourier").getValue(),
                                        qoc_qcn: Ext.getCmp("textfieldQCNnumber").getValue(),
                                        qoc_date: Ext.getCmp("textfieldcpd_couriredate").getValue(),
                                        qoc_time: Ext.getCmp("textfieldcpd_couriretime").getValue(),
                                        quor_id: quor_id
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success", tmp.message);
                                            _courierCargoWindow.close();
                                            showData('dj');
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
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
            _courierCargoWindow.doLayout();
            _courierCargoWindow.show();
            _courierCargoWindow.center();
        }, orderDriverPickUp: function (quor_id, quor_Type, quor_Status) {

            var _custPickupForm = driverPickupForm();
            var _custPickupWindow = new Ext.Window({
                title: 'Driver Pickup',
                layout: 'fit',
                width: winsize.width * 0.3,
                height: winsize.height * 0.25,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_custPickupForm],
                listeners: {
                    afterrender: function () {
                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 511,
                        handler: function () {
                            _custPickupWindow.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 511,
                        handler: function () {
                            if (_custPickupForm.getForm().isValid()) {
                                Ext.Ajax.request({
                                    url: modURL + "&op=saveCustomerPickup",
                                    method: "POST",
                                    params: {
                                        picked_by: Ext.getCmp("textfieldDispatchPickup").getValue(),
                                        pickup_mobile: Ext.getCmp("textfieldDispatchPickupMobile").getValue(),
                                        pickup_otp: Ext.getCmp("textfieldDispatchPickupOtp").getValue(),
                                        pickup_date: Ext.getCmp("textfieldcpd_pickudate").getValue(),
                                        pickup_time: Ext.getCmp("textfieldcpd_pickuptime").getValue(),
                                        quor_id: quor_id,
                                        isDriver: 1
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success", tmp.message);
                                            _custPickupWindow.close();
                                            showData('dj');
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
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
            _custPickupWindow.doLayout();
            _custPickupWindow.show();
            _custPickupWindow.center();
        }, manualDelivery: function (quor_id, quor_Type, quor_Status) {

            var _custPickupForm = manualDeliveryForm();
            var _custPickupWindow = new Ext.Window({
                title: 'Manual Delivery',
                layout: 'fit',
                width: winsize.width * 0.3,
                height: winsize.height * 0.22,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_custPickupForm],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 40,
                        handler: function () {
                            _custPickupWindow.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 50,
                        handler: function () {
                            if (_custPickupForm.getForm().isValid()) {
                                Ext.Ajax.request({
                                    url: modURL + "&op=saveManualDelivery",
                                    method: "POST",
                                    params: {//textfieldManualDeliveredDate,textfieldManualDeliveredtime,textfieldManualDelivered
                                        delivered_by: Ext.getCmp("textfieldManualDelivered").getValue(),
                                        delivered_date: Ext.getCmp("textfieldManualDeliveredDate").getValue(),
                                        delivered_time: Ext.getCmp("textfieldManualDeliveredtime").getValue(),
                                        quor_id: quor_id
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success", tmp.message);
                                            _custPickupWindow.close();
                                            showData('dj');
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
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
            _custPickupWindow.doLayout();
            _custPickupWindow.show();
            _custPickupWindow.center();
        },
    }
}
();