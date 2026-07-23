
Application.Vehicleowner = function () {
    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Private Area------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Private Variables---------------------------//
    //Configure the Number of records can display in Grid at a time.
    var recs_per_page = 12;
    var modURL = '?module=qugeo_vehicleowner';


    var proxy = new Ext.data.HttpProxy({
        url: modURL + '&op=list',
        method: 'post'
    });
    //var vehicleowner_store = Store for 'vehicleowner_grid'

    var vehicleownerStore = function () {
        var vehicleowner_store = new Ext.data.JsonStore({
            proxy: proxy,
            fields: ['rownum', 'vhow_ID', 'address', 'vhow_Name', 'vhow_Ph1', 'vhow_Active'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'vhow_ID',
            // turn on remote sorting
            remoteSort: true
        });
        return vehicleowner_store;
    }


    //for selecting multi rows in a grid    
    function vehicleownerSelect() {
        return  new Ext.grid.RowSelectionModel({
            multiSelect: true
        });
    }



    //----------------------------Private Methods----------------------------//

    // Load vehicleowner
    var loadVehicleowner = function () {
        Ext.getCmp('gridvehicleowner').getStore().setDefaultSort('vhow_Name', 'asc');
        Ext.getCmp('gridvehicleowner').getStore().removeAll();
        Ext.getCmp('gridvehicleowner').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }
    /**
     * var vehicleownerAction =  List of actions in 'vehicleowner_grid'
     * Action Edit
     **/
//    var vehicleownerAction = function () {
//        var action = new Ext.ux.grid.RowActions({
//            hideMode: 'display',
//            //header: '',
//            // tooltip: '',
//            autoWidth: false,
//            width: 30,
//            actions: [/* <?php if (user_access("qugeo_vehicleowner", "saveVehicleowner")) { ?> */{
//                    sortable: false,
//                    tooltip: 'Edit Vehicle Owner',
//                    iconCls: 'edit',
//                    callback: function (grid, rec, row, col) {
//                        Application.Vehicleowner.addVehicleowner(rec.data.vhow_ID);
//                    }
//                }/* <?php } ?> */]
//        });
//        return action;
//    }
    var vehicleownerActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var vhow_ID = Ext.getCmp('gridvehicleowner').getSelectionModel().getSelections()[0].data.vhow_ID;
                    Application.Vehicleowner.addVehicleowner(vhow_ID);
                }
            }]
    });
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }
    //create a grid to display all the vehicle owners

    var createVehicleownerGrid = function () {
        var vehicleowner_store = vehicleownerStore();
        var vehicleowner_select = vehicleownerSelect();
        //var action = vehicleownerAction();

        //var vehicleownerfilters = Plugin used for the 'vehicleowner_grid'

        var vehicleownerfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'vhow_Name'

                }, {
                    type: 'string',
                    dataIndex: 'address'

                }, {
                    type: 'string',
                    dataIndex: 'vhow_Ph1'

                }, {
                    type: 'string',
                    dataIndex: 'vhow_Active'

                },{
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'vhow_Active'
                }]
        });
        vehicleownerfilters.remote = true;
        vehicleownerfilters.autoReload = true;
        //create a grid to display all Vehicle Owner,Address,Phone,Active 

        var vehicleowner_grid = new Ext.grid.GridPanel({
            store: vehicleowner_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [vehicleownerfilters],
            title: 'Vehicle Owner',
            //iconCls: 'my-icon92',
            id: 'gridvehicleowner',
            columns: [{
                    header: '',
                    sortable: false,
                    dataIndex: 'rownum',
                    align: 'right',
                    width: 3
                }, {
                    header: 'Vehicle Owner',
                    id: 'vhow_Name',
                    sortable: true,
                    dataIndex: 'vhow_Name'


                }, {
                    header: 'Address ',
                    id: 'vhow_address',
                    sortable: true,
                    dataIndex: 'address'


                }, {
                    header: 'Phone',
                    id: 'vhow_Ph1',
                    sortable: true,
                    dataIndex: 'vhow_Ph1'


                }, {
                    header: 'Active',
                    id: 'vhow_Active',
                    sortable: true,
                    dataIndex: 'vhow_Active'


                }, {
                    xtype: 'actioncolumn',
                    //header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            vehicleownerActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }],
            sm: vehicleowner_select,
            listeners: {
                resize: updatePagination
            },
            /* <?php if (user_access("qugeo_vehicleowner", "saveVehicleowner")) { ?> */
            tbar: [{
                    text: 'Create Vehicle Owner',
                    tooltip: 'Create Vehicle Owner',
                    id: 'vehicleowner_save_button',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Vehicleowner.addVehicleowner();
                    }
                }], /* <?php } ?> */
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: vehicleowner_store,
                displayInfo: true,
                plugins: [vehicleownerfilters],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'vhow_Name'

        });
        return vehicleowner_grid;
    }
    // create a form to add/edit vehicle owner

    var addVehicleownerFrm = function () {
        var formVehicleowner = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'vehicleowner_form',
            labelAlign: 'top',
            height: 280,
            items: [{
                    xtype: 'textfield',
                    id: 'vhow_Name',
                    name: 'vhow_Name',
                    allowBlank: false,
                    tabIndex: 800,
                    vtype: 'resrcdataStringspec',
                    maxLength: 250,
                    maxLengthText: 'The maximum length for this field is 250',
                    anchor: '97%',
                    fieldLabel: 'Name '
                }, {
                    xtype: 'textfield',
                    id: 'vhow_add1',
                    name: 'vhow_Add1',
                    tabIndex: 801,
                    maxLength: 250,
                    maxLengthText: 'The maximum length for this field is 250',
                    allowBlank: false,
                    vtype: 'dataStringspec',
                    anchor: '97%',
                    fieldLabel: 'Address1'
                }, {
                    xtype: 'textfield',
                    id: 'vhow_add2',
                    name: 'vhow_Add2',
                    tabIndex: 802,
                    maxLength: 250,
                    maxLengthText: 'The maximum length for this field is 250',
                    allowBlank: false,
                    vtype: 'dataStringspec',
                    anchor: '97%',
                    fieldLabel: 'Address2'
                }, {
                    xtype: 'textfield',
                    id: 'vhow_add3',
                    tabIndex: 803,
                    name: 'vhow_Add3',
                    allowBlank: false,
                    maxLength: 250,
                    maxLengthText: 'The maximum length for this field is 250',
                    vtype: 'dataStringspec',
                    anchor: '97%',
                    fieldLabel: 'Address3'
                }, {
                    xtype: 'textfield',
                    id: 'vhow_ph1',
                    tabIndex: 804,
                    name: 'vhow_Ph1',
                    allowBlank: false,
                    vtype: 'phonespec',
                    anchor: '97%',
                    fieldLabel: 'Phone'
                }, {
                    xtype: 'checkbox',
                    id: 'vhow_Active',
                    name: 'vhow_Active',
                    tabIndex: 805,
                    checked: true,
                    boxLabel: 'Active',
                    inputValue: 1
                },
                {
                    xtype: 'hidden',
                    id: 'vhow_id',
                    name: 'vhow_ID'
                }]

        });
        return formVehicleowner;
    }

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
        init: function () {

            var vehicleownergrid = Ext.getCmp('gridvehicleowner');
            if (!vehicleownergrid) {
                vehicleownergrid = createVehicleownerGrid();
            }
            loadVehicleowner();
            Application.UI.addTab(vehicleownergrid);
            vehicleownergrid.doLayout();
        },
        //for add vehicle owner
        addVehicleowner: function (id) {
            var vehicleowner_winId = "vehicleowner_window";
            //create the window on the first click and reuse on subsequent clicks
            var vehicleowner_window = Ext.getCmp(vehicleowner_winId);
            if (Ext.isEmpty(vehicleowner_window)) {
                var vehicleownerfrm = addVehicleownerFrm();
                vehicleowner_window = new Ext.Window({
                    id: 'vehicleowner_window',
                    layout: 'fit',
                    width: 400,
                    title: 'Create Vehicle Owner  ',
                    autoHeight: true,
                    draggable: false,
                    iconCls: (id) ? 'my-icon105' : 'my-icon104',
                    plain: true,
                    constrain: true,
                    draggable: true,
                            modal: true,
                    resizable: false,
                    items: [vehicleownerfrm],
                    buttons: [{
                            text: 'Cancel',
                            id: 'btnCancel',
                            tabIndex: 807,
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                vehicleowner_window.close();
                            }
                        },
                        {
                            text: 'Save',
                            id: 'btnsave',
                            tabIndex: 806,
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            //function for form submit
                            handler: function () {
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                vehicleownerfrm.getForm().submit({
                                    waitMsg: 'Saving...',
                                    url: modURL,
                                    params: {
                                        op: 'saveVehicleowner',
                                        vhow_ID: id,
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    //for submit success
                                    success: function (frm, action) {

                                        if (action && action.response.responseText) {
                                            var tmp = Ext.decode(action.response.responseText);

                                            /*var data = [];
                                             var act;
                                             data[0]=id;
                                             
                                             if(Ext.isEmpty(id))
                                             act = 'Create   ';
                                             else
                                             act = 'Edit   ';*/

                                            if (tmp.success == true) {
                                                Application.example.msg("Notification", tmp.msg);
                                                vehicleowner_window.close();
                                                loadVehicleowner();
//                                                function (btn) {
//                                                    if (btn == 'ok') {
//                                                        //Application.ActLog.actionLog(data,act);  
//                                                        vehicleowner_window.close();
//                                                        loadVehicleowner();
//
//                                                    }
//                                                });
                                            }
                                            else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }

                                        }



                                    },
                                    //for submit failure
                                    failure: function (frm, action) {
                                        if (action.failureType == 'server') {
                                            obj = Ext.util.JSON.decode(action.response.responseText);
                                            Ext.MessageBox.show({
                                                title: 'Error!',
                                                msg: (obj.msg) ? obj.msg : obj.error,
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.ERROR,
                                                width: 325
                                            });
                                        }
                                    }
                                });
                            }
                        }]
                });

            }
            //for load the details of selected vehicle owner    
            if (!Ext.isEmpty(id)) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                vehicleownerfrm.load({
                    url: modURL + '&op=getVehicleowner',
                    params: {
                        'vhow_ID': id,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (frm, action) {
                        var obj = Ext.decode(action.response.responseText);
                        vehicleowner_window.setTitle("Edit Vehicle Owner ");



                    }
                });
            }

            vehicleowner_window.doLayout();
            vehicleowner_window.show(this);
            vehicleowner_window.center();
        }
    }
//////////////////////////////EO Public Area///////////////////////////////
}
();
