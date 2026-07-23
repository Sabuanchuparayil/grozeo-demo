Application.CPD_Dispatch = function () {
	var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=cpd_dispatch';
    var RECS_PER_PAGE = 12;
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var dispatch_array = new Array();
	var CPDDispatchMasterGridStore = function () {
        var _dispatchStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listDispatchDetails',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'bcdId',
                root: 'data'
            }, ['bcdId', 'bcd_vehicleNo', 'bcd_driver', {
                    name: 'bcd_dispatchDate',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                }, 'bcd_dispatchTime','branch']),
            sortInfo: {
                field: 'bcd_dispatchDate',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelcpd_dispatchDispatchDataView').getView().refresh();
                    Ext.getCmp('gridpanelcpd_dispatchDispatchDataView').getSelectionModel().selectRow(0);
                }
            }
        });

        return _dispatchStore;
    };
	var masterPanelforDispatch = function (id) {
        var _mpanelforDispatch = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Dispatch',
            id: id,
            //iconCls: 'dispatch',
            items: [
                CPDDispatchMainGrid(),
                new Ext.Panel({
                    title: 'Dispatch Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelCPD_DispatchParent',
                    height: 350,
                    autoScroll: true,
                    items: [
                        CPDDispatchMasterDetailsView()
                    ],
                    fbar: [{
                            text: 'Print',
                            iconCls: 'my-icon141',
                            handler: function () {
                                downloadDispatchIframe.focus();
                                downloadDispatchIframe.print();
                            }
                        }]
                })
            ]
        });
        return _mpanelforDispatch;
    };

    var gridSelectionVehicleDispatchDetails = function(sm) {
        if (!Ext.isEmpty(Ext.getCmp("gridpanelcpd_dispatchDispatchDataView").getSelectionModel().getSelections())) {
            var ID = Ext.getCmp("gridpanelcpd_dispatchDispatchDataView").getSelectionModel().getSelections()[0].data.bcdId;
            Application.CPD_Dispatch.ViewDispatchMode(ID);
        }
    };
    var CPDDispatchMainGrid = function () {
        var _dispatchStore = CPDDispatchMasterGridStore();
        var _dispatchGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'bcd_vehicleNo'
                },{
                    type: 'string',
                    dataIndex: 'bcd_driver'
                }

                ]
        });
        _dispatchGridFilter.remote = true;
        _dispatchGridFilter.autoReload = true;
        var __cpddispatchmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            //iconCls: 'money',
            store:_dispatchStore,
            id: 'gridpanelcpd_dispatchDispatchDataView',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _dispatchGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Vehicle No.',
                    dataIndex: 'bcd_vehicleNo',
                    sortable: true,
                    tooltip: 'Vehicle No.',
                    hideable: false
                },
                {
                    header: 'Driver',
                    dataIndex: 'bcd_driver',
                    sortable: true,
                    tooltip: 'Driver',
                    hideable: false
                },{
                    header: 'Branch',
                    dataIndex: 'branch',
                    sortable: true,
                    tooltip: 'Branch',
                    hideable: false
                },
                {
                    header: 'Dispatched Date',
                    dataIndex: 'bcd_dispatchDate',
                    sortable: true,
                    tooltip: 'Dispatched Date',
                    hideable: false,
                     renderer: function (value, metadata, record) {
                                dateret = Ext.util.Format.date(value, 'd-m-Y');
                                return dateret;
                            }
                },
                {
                    header: 'Dispatched Time',
                    dataIndex: 'bcd_dispatchTime',
                    sortable: true,
                    tooltip: 'Dispatched Time',
                    hideable: false
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _dispatchStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionVehicleDispatchDetails
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    Application.CPD_Dispatch.ViewDispatchMode(record.data.bcdId);

                },
                resize: onGridResize,
                afterrender: function () {
                    _dispatchStore.load();

                }
            },
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                    	AddNewDispatchWindow();

                    }
                }]
        });
        return __cpddispatchmaingridPanel;
    };

     // panel for table
    var CPDDispatchMasterDetailsView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '';
        //var src = '?module=cpd_dispatch&op=dispatchdetailsView&bcd_id=' + Application.CPD_Dispatch.Cache.bcd_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'panelCPD_DispatchDetailsView',
            region: 'center',
            width: winsize.width * 0.4,
            html: '<iframe id="downloadDispatchIframe" name="downloadDispatchIframe" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>',
        });
        return contactsPanel;
    };

    var ck_selection = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true,
        checkOnly: true,
        listeners: {
            rowdeselect: function (sm, rowIndex, record) {
                console.log("Checkboxdeselection:", record);
                var ind = dispatch_array.indexOf(record.get('Value'));
                Application.CPD_Dispatch.Cache.Order_ID = record.data.quor_id;
                console.log("de:ind", ind);
                if (ind > -1)
                    dispatch_array.splice(ind, 1);

                record.set('checked', 'false');
            },
            rowselect: function (sm, rowIndex, record) {
                console.log("Checkboxselection:", record);
                Application.CPD_Dispatch.Cache.Order_ID = record.data.quor_id;
                var ind = dispatch_array.indexOf(record.get('Value'));
                console.log("se:ind", ind);
                if (ind == -1)
                    dispatch_array.push(record.get('Value'));

                record.set('checked', 'true');
            }
        }
    });

    var AddNewDispatchWindow = function () {
    	var _dispatchForm = AddNewDispatchForm();
    	var _dispatchgrid = AddNewDispatchGrid();
    	var _addnewWindow = new Ext.Window({
    		title:'Dispatch Details',
    		//iconCls: 'dispatch',
    		layout: 'fit',
    		height: 400,
    		width: 600,
            resizable: false,
    		draggable: true,
    		closable: true,
				bodyStyle: {"background-color": "white"},
    		items:[_dispatchForm,_dispatchgrid],
    		fbar:[
                /* <?php if (user_access("cpd_dispatch", "dispatchDetails")) { ?> */

    	    	{
                    text: "Dispatch",
                    cls: 'left-right-buttons',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if ((!Ext.isEmpty((Ext.getCmp("textfieldcpd_dispatchVehicleNo").getValue() &&
                            Ext.getCmp("textfieldcpd_dispatchDriver").getValue() &&
                            Ext.getCmp("textfieldcpd_dispatchDispatchdate").getValue() &&
                                Ext.getCmp("textfieldcpd_dispatchDispatchtime").getValue()))) ||
                            Ext.getCmp('gridpanelAddnewDispatchdata').getSelectionModel().getCount() != 0)
                            {
                                var store_fields = Ext.getCmp('gridpanelAddnewDispatchdata').getSelectionModel().getSelections();
                                for (var i = 0; i < store_fields.length; i++) {
                                    console.log("Dispatch");
                                dispatch_array[i] = store_fields[i].data.quor_id;
                                }
                                Ext.Ajax.request({
                                    url: modURL + "&op=dispatchDetails",
                                    method: "POST",
                                    params: {
                                        id: Ext.getCmp("textfieldcpd_dispatchid").getValue(),
                                        vehicle_no: Ext.getCmp("textfieldcpd_dispatchVehicleNo").getValue(),
                                        driver: Ext.getCmp("textfieldcpd_dispatchDriver").getValue(),
                                        dispatch_date: Ext.getCmp("textfieldcpd_dispatchDispatchdate").getValue(),
                                        dispatch_time: Ext.getCmp("textfieldcpd_dispatchDispatchtime").getValue(),
                                        dispatch_array: dispatch_array.toString(),
                                    },
                                    success: function(response) {
                                        var tmp = Ext.decode(response.responseText);
                                        console.log("tmp", tmp);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success",tmp.message);
                                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("gridpanelcpd_dispatchDispatchDataView"));
                                            dispatch_array = [];
                                            Ext.getCmp("gridpanelcpd_dispatchDispatchDataView").store.reload({
                                                params: {
                                                    start: 0,
                                                    limit: RECS_PER_PAGE
                                                }
                                            });
                                            _addnewWindow.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.",tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.",tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
                                        }
                                    },
                                    failure: function(response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert("Error", tmp.message);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert("Notification","Please fill required data");
                            }
                    }

                }
            /* <?php } ?> */
    		]

    	});
    	_addnewWindow.doLayout();
    	_addnewWindow.show();
    	_addnewWindow.center();
    };

    var AddNewDispatchForm = function () {
        var _dispatchFormPanel = new Ext.form.FormPanel({
            frame: false,
            border: false,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'left',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Vehicle Number',
                    id: 'textfieldcpd_dispatchVehicleNo',
                    name: 'textfieldcpd_dispatchVehicleNo',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 1,
                    maxValue: 100,
                    maxLength: 20,
                      listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);
                                        }
                                 }
                },
                {
                    xtype: 'textfield',
                    id: 'textfieldcpd_dispatchid',
                    name: 'textfieldcpd_dispatchid',
                    hidden: true
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Driver',
                    id: 'textfieldcpd_dispatchDriver',
                    name: 'textfieldcpd_dispatchDriver',
                    anchor: '98%',
                    allowBlank: false,
                    maxValue: 100,
                    tabIndex: 2,
                    maxLength: 20
                },
                {
                    xtype: 'datefield',
                    fieldLabel: 'Dispatch Date',
                    id: 'textfieldcpd_dispatchDispatchdate',
                    name: 'textfieldcpd_dispatchDispatchdate',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 3,
                    format: "d/m/Y",
                },
                {
                    xtype: 'timefield',
                    fieldLabel: 'Dispatch Time',
                    id: 'textfieldcpd_dispatchDispatchtime',
                    name: 'textfieldcpd_dispatchDispatchtime',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 4,
                    minValue: '9:00 AM',
                    maxValue: '6:00 PM'
                }
            ]
        });
        return _dispatchFormPanel;
    };

    var AddNewDispatchGrid = function () {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listScheduleOrderData',
                method: 'post'
            }),
            fields: ['quor_id', 'quor_RefNo', 'quor_Date', 'order_status', 'cpd_Name', 'branch_name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.cpd_id = _SESSION.finascop_current_branch_id;
                }
            }
        });
        _Store.setDefaultSort('quor_Date', 'DESC');
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _Store,
            //iconCls: 'money',
            autoScroll: true,
            width: winsize.width * 0.4,
						height: 250,
            bodyStyle: {"background-color": "white"},
            selModel: ck_selection,
            id: 'gridpanelAddnewDispatchdata',
            columns: [
                ck_selection,
                {
                    header: 'Order No.',
                    dataIndex: 'quor_RefNo',
                    sortable: true,
                    tooltip: 'Order No.',
                    hideable: false
                },
                {
                    header: 'Order Date',
                    dataIndex: 'quor_Date',
                    sortable: true,
                    tooltip: 'Order Date',
                    hideable: false
                },
                {
                    header: 'From',
                    dataIndex: 'cpd_Name',
                    sortable: true,
                    tooltip: 'From',
                    hideable: false
                },
                {
                    header: 'To',
                    dataIndex: 'branch_name',
                    sortable: true,
                    tooltip: 'To',
                    hideable: false
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
               afterrender: function () {
                    _Store.load();
                        var me = this;
                        _Store.on('load', function () {
                            var data = me.getStore().data.items;
                            var recs = [];
                            Ext.each(data, function (item, index) {
                                if (item.data.checked == 1) {
                                    recs.push(index);
                                }
                            });
                            me.getSelectionModel().selectRows(recs);
                        });
                }
            }
        });
        return _dispatchgridPanel;
    };

	return {
        Cache: {},
        initDispatch: function () {
            var _dispatchPanelId = 'panelMasterMainCPD_Dispatch';
            var _masterPanelDispatch = Ext.getCmp(_dispatchPanelId);
            if (Ext.isEmpty(_masterPanelDispatch)) {
                _masterPanelDispatch = masterPanelforDispatch(_dispatchPanelId);
                Application.UI.addTab(_masterPanelDispatch);
                _masterPanelDispatch.doLayout();
            } else {
                Application.UI.addTab(_masterPanelDispatch);
            }
        },
        ViewDispatchMode: function () {
            var bcd_id = arguments[0];
            Application.CPD_Dispatch.Cache.bcd_id = bcd_id;
            Ext.getCmp("panelCPD_DispatchDetailsView").show();
            Ext.getCmp("panelCPD_DispatchParent").doLayout();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var apikey = _SESSION.apikey;
            var modURL = '?module=cpd_dispatch';
            Ext.get('downloadDispatchIframe').dom.src = modURL + '&op=dispatchdetailsView&bcd_id=' + bcd_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        }
	};
}();
