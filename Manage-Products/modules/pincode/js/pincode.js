
Application.Pincode = function () {
    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Private Area------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Private Variables---------------------------//
    //Configure the Number of records can display in Grid at a time.
    var recs_per_page = 12;
    var modURL = '?module=pincode';


    var proxy = new Ext.data.HttpProxy({
        url: modURL + '&op=list',
        method: 'post'
    });


    var branchStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=getBranch',
        method: 'post',
        fields: ['br_ID', 'br_Name'],
        totalProperty: 'totalCount',
        root: 'data'
    });




//var pincode_store = Store for 'pincode_grid'

    var pincodeStore = function () {
        var pincode_store = new Ext.data.JsonStore({
            proxy: proxy,
            fields: ['rownum', 'id', 'pincode', 'st_id', 'dt_id', 'brName', 'is_Active'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'pin_ID',
            // turn on remote sorting
            remoteSort: true
        });
        return pincode_store;
    }

    /* store for state combo    */
    var stateStore = new Ext.data.JsonStore({
        fields: ['st_ID', 'st_name'],
        url: '?module=pincode&op=getState',
        root: 'data'

    });

    /* store for district combo    */
    var districtStores = new Ext.data.JsonStore({
        fields: ['dst_Id', 'dst_Name'],
        url: '?module=pincode&op=getDistrict',
        root: 'data'

    });
    //for selecting multi rows in a grid    
    function pincodeSelect() {
        return  new Ext.grid.RowSelectionModel({
            multiSelect: true
        });
    }



    //----------------------------Private Methods----------------------------//

    // Load pincode
    var loadPincode = function () {
        Ext.getCmp('gridpincode').getStore().setDefaultSort('pincode', 'asc');
        Ext.getCmp('gridpincode').getStore().removeAll();
        Ext.getCmp('gridpincode').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }
    /**
     * var pincodeAction =  List of actions in pincode Grid
     * Action Edit,Enable/Disable Pincode
     **/
//    var pincodeAction = function () {
//        var action = new Ext.ux.grid.RowActions({
//            hideMode: 'display',
//            //header: '',
//            // tooltip: '',
//            autoWidth: false,
//            width: 30,
//            actions: [/* <?php if (user_access("pincode", "savePincode")) { ?> */{
//                    sortable: false,
//                    tooltip: 'Edit Pincode  ',
//                    iconCls: 'finascop_edit',
//                    callback: function (grid, rec, row, col) {
//                        Application.Pincode.addPincode(rec.data.pincode);
//                    }
//                }, {
//                    sortable: false,
//                    tooltip: 'Enable/Disable Pincode',
//                    iconCls: 'my-icon24',
//                    hide: false,
//                    id: 'id_disableSettings',
//                    callback: function (grid, rec, row, col) {
//                        // console.log(rec.data.is_Active,rec.data.pincode);
//                        if (rec.data.is_Active == 'Y')
//                            Application.Pincode.disablePinSettings(rec.data.pincode);
//                        if (rec.data.is_Active == 'N')
//                            Application.Pincode.enableblePinSettings(rec.data.pincode);
//
//                    }
//                }/* <?php } ?> */]
//        });
//        return action;
//    }
var pincodeActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var pincode = Ext.getCmp('gridpincode').getSelectionModel().getSelections()[0].data.pincode;
                    Application.Pincode.addPincode(pincode);
                }
            }, {
                text: "Enable/Disable",
                            handler: function () {
                            var pincode = Ext.getCmp('gridpincode').getSelectionModel().getSelections()[0].data.pincode;
                            var is_Active = Ext.getCmp('gridpincode').getSelectionModel().getSelections()[0].data.is_Active;
                            if (is_Active == 'Y')
                            Application.Pincode.disablePinSettings(pincode);
                        if (is_Active == 'N')
                            Application.Pincode.enableblePinSettings(pincode);

                            }
                        
            }]
    });

    //for pagination 
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }

    var createPincodeGrid = function () {
        var pincode_store = pincodeStore();
        var pincode_select = pincodeSelect();
        //var action = pincodeAction();
        /**
         * var pincodefilters = Plugin used for the  pincode_grid
         **/
        var pincodefilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'pincode'

                }, {
                    type: 'string',
                    dataIndex: 'st_id'

                }, {
                    type: 'string',
                    dataIndex: 'dt_id'

                }, {
                    type: 'string',
                    dataIndex: 'brName'

                }, {
                    type: 'list',
                    options: ['Y', 'N'],
                    phpMode: true,
                    dataIndex: 'is_Active'

                }]
        });
        pincodefilters.remote = true;
        pincodefilters.autoReload = true;


        var pincode_grid = new Ext.grid.GridPanel({
            store: pincode_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                //Condition checking and Assign color to row  
                //Assign color to Actve Pincode.
                getRowClass: function (record, index) {

                    if (record.data.is_Active == 'N')
                    {
                        return '';
                    } else
                        return 'indicateCOL';
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [pincodefilters],
            title: 'Post Codes',
            //iconCls: 'my-icon92',
            id: 'gridpincode',
            columns: [{
                    header: '',
                    sortable: false,
                    dataIndex: 'rownum',
                    align: 'right',
                    width: 3
                }, {
                    header: 'Post Codes',
                    id: 'pincode',
                    sortable: true,
                    dataIndex: 'pincode'


                }, {
                    header: 'State',
                    id: 'st_id',
                    sortable: true,
                    dataIndex: 'st_id'


                }, {
                    header: 'District',
                    id: 'dt_id',
                    sortable: true,
                    dataIndex: 'dt_id'


                }, {
                    header: 'Branch',
                    id: 'br_id',
                    sortable: true,
                    dataIndex: 'brName'


                }, {
                    header: 'Active',
                    id: 'is_Active',
                    sortable: true,
                    dataIndex: 'is_Active'
                },
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
                            pincodeActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }],
            sm: pincode_select,
            listeners: {
                resize: updatePagination
            },
            /* <?php if (user_access("pincode", "savePincode")) { ?> */
            tbar: [{
                    text: 'Create Post Code',
                    tooltip: 'Create Post Code',
                    id: 'pincode_save_button',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Pincode.addPincode();
                    }
                }], /* <?php } ?> */
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: pincode_store,
                displayInfo: true,
                plugins: [pincodefilters],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'pincode'

        });
        return pincode_grid;
    }
    // create a form to add/edit Collectionboy


    /* Function for creating Pincode Form    */


    var addpincodeFrm = function () {
        var formPincode = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'pincode_form',
            labelAlign: 'top',
            height: 110,
            items: [
                {
                    xtype: 'spacer',
                    height: 10
                },
                {
                    layout: 'column',
                    border: false,
                    items: [{
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                               {
                    xtype: 'numberfield',
                    id: 'pincode',
                    name: 'pincode',
                    allowBlank: false,
                    // vtype: 'phonespec',
                    minLength: 6,
                    maxLength: 6,
                    anchor: '97%',
                    fieldLabel: 'Post Codes',
                    tabIndex: 400,
                    listeners: {
                        afterrender: function (field) {
                            Ext.defer(function () {
                                field.focus(true, 100);
                            }, 1);
                        }
                    }
                },
                            ]
                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                    xtype: 'combo',
                    id: 'c_states1',
                    name: 'c_State',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    // vtype: 'alphaStringspec',
                    fieldLabel: 'State',
                    editable: true,
                    tabIndex: 401,
                    allowBlank: false,
                    anchor: '97%',
                    store: stateStore,
                    emptyText: 'Select State',
                    triggerAction: 'all',
                    minChars: 1,
                    displayField: 'st_name',
                    valueField: 'st_ID',
                    hiddenName: 'c_State',
                    listeners: {
                        change: function (cbo) {
                            var index = stateStore.find('st_ID', new RegExp("^" + cbo.getValue() + "$", "i"));
                            if (index == -1) {
                                Ext.getCmp('c_states1').markInvalid("Please check the State");
                            }
                            if (districtStores.baseParams.st_ID !== cbo.getValue()) {
                                districtStores.removeAll();
                                districtStores.baseParams.st_ID = cbo.getValue();
                                districtStores.load();
                                Ext.getCmp('c_districts1').setValue('');

                            }
                        }
                    }

                },
                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                    xtype: 'combo',
                    id: 'c_districts1',
                    name: 'c_District',
                    tabIndex: 402,
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'District',
                    editable: true,
                    allowBlank: false,
                    anchor: '97%',
                    store: districtStores,
                    emptyText: 'Select District',
                    triggerAction: 'all',
                    minChars: 1,
                    displayField: 'dst_Name',
                    valueField: 'dst_Id',
                    hiddenName: 'c_District',
                    listeners: {
                        change: function (cbo) {
                            var index = districtStores.find('dst_Id', new RegExp("^" + cbo.getValue() + "$", "i"));
                            if (index == -1) {
                                Ext.getCmp('c_districts1').markInvalid("Please check the District");

                            }
                        }
                    }
                }
                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                    xtype: 'combo',
                    id: 'db_branch',
                    name: 'br_id',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Branch',
                    editable: true,
                    allowBlank: false,
                    anchor: '97%',
                    store: branchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    tabIndex: 403,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'br_id'
                }


                            ]
                        }

                    ]

                },
                
                {
                    xtype: 'textfield',
                    id: 'bmd_id_b2b',
                    name: 'n[bmd_id]',
                    hidden: true
                }

            ]

        });
        return formPincode;
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

            var pincodegrid = Ext.getCmp('gridpincode');
            if (!pincodegrid) {
                pincodegrid = createPincodeGrid();
            }
            loadPincode();
            Application.UI.addTab(pincodegrid);
            pincodegrid.doLayout();
        },
        //for add collection boy    
        addPincode: function (pincode) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var pincode_winId = "pincode_window";
            //create the window on the first click and reuse on subsequent clicks
            var pincode_window = Ext.getCmp(pincode_winId);
            if (Ext.isEmpty(pincode_window)) {
                var pincodefrm = addpincodeFrm();
                pincode_window = new Ext.Window({
                    id: 'pincode_window',
                    layout: 'fit',
                    width: 400,
                    title: 'Create Post Code  ',
                    autoHeight: true,
                    draggable: false,
                    iconCls: (pincode) ? 'my-icon99' : 'my-icon98',
                    plain: true,
                    constrain: true,
                    modal: true,
                    resizable: false,
                    items: [pincodefrm],
                    buttons: [{
                            text: 'Cancel',
                            id: 'btnCancel',
                            tabIndex: 405,
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                pincode_window.close();
                            }
                        },
                        {
                            text: 'Save',
                            id: 'btnsave',
                            tabIndex: 404,
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                pincodefrm.getForm().submit({
                                    waitMsg: 'Saving...',
                                    url: modURL,
                                    params: {
                                        op: 'savePincode',
                                        pin_id: pincode,
                                        branch_id: db_branch,
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    //for submit success
//                                   success: function (frm, action) {
//
//                                        if (action && action.response.responseText) {
//                                            var tmp = Ext.decode(action.response.responseText);
//
//                                            /*var data = [];
//                                             var act;
//                                             data[0]=id;
//                                             
//                                             if(Ext.isEmpty(id))
//                                             act = 'Create Collection Boy  ';
//                                             else
//                                             act = 'Edit Collection Boy  ';*/
//
//                                            if (tmp.success == true) {
//                                                Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
//                                                    if (btn == 'ok') {
//                                                        //Application.ActLog.actionLog(data,act);  
//                                                        pincode_window.close();
//                                                        loadPincode();
//
//                                                    }
//                                                });
//                                            } else {
//                                                Ext.MessageBox.alert("Notification", tmp.msg);
//                                            }
//
//                                        }
//
//                                    },
                                    success: function (frm, action) {
                                        if (action && action.response.responseText) {
                                            var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success == true) {
                                            Application.example.msg('Success', tmp.msg);
                                            pincode_window.close();
                                            Ext.getCmp('pincode_window').getStore().reload();
                                        } else  {
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
            //for load the details of selected collection boy    


            /*   if(!Ext.isEmpty(pincode)){
             pincodefrm.load({
             url : modURL + '&op=getPincode',
             params : {
             'pin_ID' : pincode
             },
             success : function(frm,action){
             var obj = Ext.decode(action.response.responseText); 
             //Ext.getCmp('db_branch').setValue(obj.data.br_id);
             pincode_window.setTitle("Edit Pincode  ");
             
             
             
             }                
             });
             }  */

            stateStore.load();
            districtStores.load({
                /* function for getting selected locations  */
                callback: function () {
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    if (!Ext.isEmpty(pincode)) {
                        pincodefrm.load({
                            url: modURL + '&op=getPincode',
                            params: {
                                'pin_ID': pincode,
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp

                            },
                            success: function (frm, action) {
                                var obj = Ext.decode(action.response.responseText);

                                pincode_window.setTitle("Edit Post Code");
                                Ext.getCmp('c_states1').setRawValue(obj.data.st_name);
                                Ext.getCmp('c_states1').setValue(obj.data.st_ID);
                                Ext.getCmp('c_districts1').setValue(obj.data.dst_Id);
                                Ext.getCmp('c_districts1').setRawValue(obj.data.dst_Name);
                                Ext.getCmp('db_branch').setValue(obj.data.br_ID);
                                Ext.getCmp('db_branch').setRawValue(obj.data.brName);


                            }
                        });
                    }
                }
            });


            pincode_window.doLayout();
            pincode_window.show(this);
            pincode_window.center();
        }, disablePinSettings: function (pincode) {
            /* function for disable post codes. */
            Ext.MessageBox.confirm('Confirm', 'Are you sure to disable this settings?', function (btn) {

                if (btn == "yes") {

                    Ext.Ajax.request({
//submit to server
                        url: modURL + "&op=disableSettings",
                        method: 'POST',
                        params: {
                            'pin_ID': pincode
                        },
                        success: function (res) {

                            var tmp = Ext.decode(res.responseText);
                            if (tmp.success == true) {

                                if (tmp.error != "") {

                                    Ext.MessageBox.alert(
                                            'Warning',
                                            tmp.error
                                            );
                                } else {

                                    Ext.getCmp('gridpincode').store.reload({
                                        callback: function () {

                                            Application.example.msg(
                                                    'Success',
                                                    'Your request has been successfully processed.'
                                                    );
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            });
        }, enableblePinSettings: function (pincode) {
            /* function for enable pincode.   */
            Ext.MessageBox.confirm('Confirm', 'Are you sure to enable this settings?', function (btn) {

                if (btn == "yes") {

                    Ext.Ajax.request({
//submit to server
                        url: modURL + "&op=enableSettings",
                        method: 'POST',
                        params: {
                            'pin_ID': pincode
                        },
                        success: function (res) {

                            var tmp = Ext.decode(res.responseText);
                            if (tmp.success == true) {

                                if (tmp.error != "") {

                                    Ext.MessageBox.alert(
                                            'Warning',
                                            tmp.error
                                            );
                                } else {

                                    Ext.getCmp('gridpincode').store.reload({
                                        callback: function () {

                                            Application.example.msg(
                                                    'Success',
                                                    'Your request has been successfully processed.'
                                                    );
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            });
        }
    }
//////////////////////////////EO Public Area///////////////////////////////
}
();
