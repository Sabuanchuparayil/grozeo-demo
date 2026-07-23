Application.PickerActivity = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var modURL = '?module=retaline_picker_activity';

    var onGridResize = function (cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    };
    function orderPickerActivitySelect() {
        return  new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
    }
    var loadMask = function () {
        return new Ext.LoadMask(Ext.getCmp('pikerActivity_main_panel').getEl(),
                {msg: "Please wait..."});
    };
    var orderPickerActivityStore = function () {

        var store = new Ext.data.JsonStore({
            url: modURL + '&op=getOrderPickerActivity',
            fields: ['rgba_id', 'rgba_Users', 'rgba_date', 'rgba_FirstAccept', 'rgba_LastAccept', 'rgba_Sessions', 'rgba_JobsToday', 'rgba_JobsCompleted', 'rgba_JobsPreviousPending', 'rgba_JobsPending', 'rgba_Duration', 'rgba_JobChecks', 'br_Name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    var dt = Ext.getCmp('pickerActivityDate').getValue();
                    if (!Ext.isEmpty(dt) && Ext.getCmp('pickerActivityDate').isValid()) {
                        this.baseParams.date = dt.format('Y-m-d');
                    }
                },
                load: function () {

                }
            }
        });
        return store;
    };
    var showData = function () {


        var str = Ext.getCmp('gridOrderPickerActivity').getStore();
        str.removeAll();

        var dt = Ext.getCmp('pickerActivityDate').getValue();
        if (!Ext.isEmpty(dt) && Ext.getCmp('pickerActivityDate').isValid()) {
            str.baseParams.date = dt.format('Y-m-d');
            str.load();
        } else {
            Ext.MessageBox.alert("Notification", "Please select valid branch and date");
        }
    };
    var orderPickerActivityGrid = function () {

        var orderPickerActivityFilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'boy_name'

                }]
        });
        var godownExecutive_store = orderPickerActivityStore();
        var godownExecutive_select = orderPickerActivitySelect();
        var godownExecutive_grid = new Ext.grid.GridPanel({
            store: godownExecutive_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                }
            },
            plugins: [orderPickerActivityFilters],
            title: 'Picker Activity',
            id: 'gridOrderPickerActivity',
            iconCls: 'trip_icon',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name'
                },
                {
                    header: 'User Count',
                    sortable: true,
                    dataIndex: 'rgba_Users',
                    align: 'right'
                },
                {
                    header: 'First Seen',
                    sortable: true,
                    dataIndex: 'rgba_FirstAccept'
                },
                {
                    header: 'Last Seen',
                    sortable: true,
                    dataIndex: 'rgba_LastAccept'
                }, {
                    header: 'Jobs Today',
                    sortable: true,
                    dataIndex: 'rgba_JobsToday',
                    align: 'right'
                }, {
                    header: 'Previous Pending',
                    sortable: true,
                    dataIndex: 'rgba_JobsPreviousPending',
                    align: 'right'
                }, {
                    header: 'Jobs Packed',
                    sortable: true,
                    dataIndex: 'rgba_JobsCompleted',
                    align: 'right'
                }, {
                    header: 'Pending Jobs',
                    sortable: true,
                    dataIndex: 'rgba_JobsPending',
                    align: 'right'
                }, {
                    header: 'Availabilty',
                    sortable: true,
                    dataIndex: 'rgba_Duration'
                }, {
                    header: 'Job Checks',
                    sortable: true,
                    dataIndex: 'rgba_JobChecks',
                    align: 'right'
                }, {
                    header: 'Sessions',
                    sortable: true,
                    dataIndex: 'rgba_Sessions',
                    align: 'right'
                },{hidden:true}
//                {
//                    xtype: 'actioncolumn',
//                    hideable: false,
//                    width: 16,
//                    items: [
//                        {
//                            tooltip: 'View Details',
//                            iconCls: 'perform_chem',
//                            handler: function (grid, rowIndex, colIndex) {
//                                var record = grid.store.getAt(rowIndex);
//                                var dt = Ext.getCmp('pickerActivityDate').getValue();
//                                var sedate = dt.format('Y-m-d');
//                                showActivityDetails(record.get('rgba_id'), record.get('rgba_boyid'), sedate);
//                            }
//                        },
//                    ]
//                }
            ],
            sm: godownExecutive_select,
            listeners: {
                resize: onGridResize
            },
            tbar: [{
                    html: '&nbsp; Date: &nbsp;'
                }, {
                    xtype: 'datefield',
                    id: 'pickerActivityDate',
                    allowBlank: false,
                    editable: true,
                    format: 'Y-m-d',
                    maxValue: new Date(),
                    value: new Date().add(Date.DAY, -1)
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
                store: godownExecutive_store,
                displayInfo: true,
                plugins: [orderPickerActivityFilters],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'name'

        });
        return godownExecutive_grid;
    };
    var showActivityDetails = function (rgba_id, rgba_boyid, sedate) {
        var showActivityDetailsWin = new Ext.Window({
            id: "PickerActivityDetailsWinID",
            title: 'Activity Details of Picker',
            shadow: false,
            width: 500,
            autoHeight: true,
            modal: true,
            layout: 'fit',
            resizable: true,
            closable: true,
            items: [
                {
                    xtype: 'panel',
                    anchor: '98$',
                    border: false,
                    frame: false,
                    autoHeight: true,
                    layout: 'form',
                    height: 50,
                    labelAlign: 'top',
                    items: [_activityDetailsGrid(rgba_id, rgba_boyid, sedate)]
                }
            ],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Close',
                    tabIndex: 523,
                    handler: function () {
                        Ext.getCmp('PickerActivityDetailsWinID').close();
                    }
                }]
        });

        showActivityDetailsWin.doLayout();
        showActivityDetailsWin.show();
        showActivityDetailsWin.center();
    };
    var actDetPickStore = function (rgba_id, rgba_boyid, sedate) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listHistoryofpickerActivity',
                method: 'post'
            }),
            fields: ['rgbh_id', 'login_at', 'logout_at', 'loggedout_by'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.rgba_boyid = rgba_boyid;
                    this.baseParams.date = sedate;
                }
            }
        });
        return _Store;
    };
    var _activityDetailsGrid = function (rgba_id, rgba_boyid, sedate) {

        var _InvoicegridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: actDetPickStore(rgba_id, rgba_boyid, sedate),
            iconCls: 'barcode',
            width: 500,
            height: 500,
            bodyStyle: {"background-color": "white"},
            id: 'pikrActivtyDetailsGrid',
            columns: [
                {
                    header: 'Login At',
                    dataIndex: 'login_at',
                    sortable: true,
                    tooltip: 'Login At',
                    hideable: false,
                    width: 300
                },
                {
                    header: 'Logout At',
                    dataIndex: 'logout_at',
                    sortable: true,
                    tooltip: 'Logout At',
                    hideable: false,
                    width: 300
                }, {
                    header: 'Logout By',
                    dataIndex: 'loggedout_by',
                    sortable: true,
                    tooltip: 'Logout By',
                    hideable: false,
                    width: 300
                }
            ],
            viewConfig: {
                forceFit: true,
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('pikrActivtyDetailsGrid').getStore().load({
                        params: {
                            rgba_boyid: rgba_boyid,
                            date: sedate
                        }
                    });
                },
                viewready: updatePagination
            }
        });
        return _InvoicegridPanel;
    };
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    return{
        initPickerActivity: function () {
            var panelId = 'pikerActivity_main_panel';
            var activity_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(activity_panel)) {
                activity_panel = orderPickerActivityGrid(panelId);
                Application.UI.addTab(activity_panel);
                activity_panel.doLayout();
            } else {
                Application.UI.addTab(activity_panel);
            }
            return activity_panel;
        },
    }
}();