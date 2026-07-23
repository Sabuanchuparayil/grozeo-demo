
Application.Driver = function () {
    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Private Area------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Private Variables---------------------------//
    //Configure the Number of records can display in Grid at a time.
    var recs_per_page = 12;
    var modURL = '?module=qugeo_driver';
//reads a data object from an Ext.data.Connection object configured to reference a certain URL.    

    var proxy = new Ext.data.HttpProxy({
        url: modURL + '&op=list',
        method: 'post'
    });
    /** 
     * var driverStore = Store for 'driver_grid'  
     **/
    var driverStore = function () {
        var driver_store = new Ext.data.JsonStore({
            proxy: proxy,
            fields: ['rownum', 'd_ID', 'address', 'd_Name', 'd_Ph1', 'd_licence', 'd_licenceexpairy', 'branch', 'd_isallowAutoSchedule', 'd_isallowManualSchedule'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'd_ID',
            // turn on remote sorting
            remoteSort: true
        });
        return driver_store;
    };

    /** 
     * var branchStore = Branch combo store
     * getBranch contains the data for branchStore 
     **/

    var branchStore = new Ext.data.JsonStore({
        fields: ['br_ID', 'br_Name'],
        url: '?module=qugeo_driver&op=getBranch',
        root: 'data',
        listeners: {
            load: function () {
                if (Ext.getCmp('d_branch').getValue() == '' || Ext.getCmp('d_branch').getValue() == '0'){
                    //Ext.getCmp('d_branch').setValue(1);
                }
 
            }
        }
    });

    var activityBranchStore = new Ext.data.JsonStore({
        fields: ['br_ID', 'br_Name'],
        url: '?module=qugeo_driver&op=getBranch',
        root: 'data'
    });

    var driverComboStore = new Ext.data.JsonStore({
        fields: ['driver_id', 'driver_name'],
        url: '?module=qugeo_driver&op=getBranchDriver',
        root: 'data'
    });







    //for selecting multi rows in a grid   
    function driverSelect() {
        return  new Ext.grid.RowSelectionModel({
            multiSelect: true
        });
    }



    //----------------------------Private Methods----------------------------//

    // Load driver
    var loadDriver = function () {
        Ext.getCmp('griddriver').getStore().setDefaultSort('d_Name', 'asc');
        Ext.getCmp('griddriver').getStore().removeAll();
        Ext.getCmp('griddriver').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    };

    /**
     * var driverAction =  List of actions in 'driver_grid'
     * Action Edit
     **/
//    var driverAction = function () {
//        var action = new Ext.ux.grid.RowActions({
//            hideMode: 'display',
//            //header: '',
//            // tooltip: '',
//            autoWidth: false,
//            width: 30,
//            actions: [/* <?php if (user_access("qugeo_driver", "saveDriver")) { ?> */{
//                    sortable: false,
//                    tooltip: 'Edit Driver  ',
//                    iconCls: 'my-icon2',
//                    callback: function (grid, rec, row, col) {
//                        Application.Driver.addDriver(rec.data.d_ID);
//                    }
//                }/* <?php } ?> */]
//        });
//        return action;
//    };
var driverActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var d_ID = Ext.getCmp('griddriver').getSelectionModel().getSelections()[0].data.d_ID;
                    Application.Driver.addDriver(d_ID);
                }
            }]
    });
    
    //for pagination  
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }
    

    var createdriverGrid = function () {
        var driver_store = driverStore();
        var driver_select = driverSelect();
        //var action = driverAction();
        /**
         * var driverfilters = Plugin used for the  Driver Grid
         **/
        var driverfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'd_Name'

                }, {
                    type: 'string',
                    dataIndex: 'address'

                },{
                    type: 'string',
                    dataIndex: 'd_Ph1'

                },{
                    type: 'string',
                    dataIndex: 'd_isallowAutoSchedule'

                },{
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'd_isallowAutoSchedule'
                },{
                    type: 'string',
                    dataIndex: 'd_isallowManualSchedule'

                },{
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'd_isallowManualSchedule'
                },{
                    type: 'string',
                    dataIndex: 'branch'

                }]
        });
        driverfilters.remote = true;
        driverfilters.autoReload = true;
        //create a grid to display all Drivers,Address,Phone,Branch       
        var driver_grid = new Ext.grid.GridPanel({
            store: driver_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [driverfilters],
            title: 'Drive Users',
            //iconCls: 'my-icon92',
            id: 'griddriver',
            columns: [{
                    header: '',
                    sortable: false,
                    dataIndex: 'rownum',
                    align: 'right',
                    width: 3
                }, {
                    header: 'Driver  ',
                    id: 'd_name',
                    sortable: true,
                    dataIndex: 'd_Name'
                }, {
                    header: 'Address ',
                    id: 'd_address',
                    sortable: true,
                    dataIndex: 'address'
                }, {
                    header: 'Phone',
                    id: 'd_phone',
                    sortable: true,
                    dataIndex: 'd_Ph1'
                }, {
                    header: 'Auto Schedule',
                    sortable: true,
                    dataIndex: 'd_isallowAutoSchedule'
                }, {
                    header: 'Manual Schedule',
                    sortable: true,
                    dataIndex: 'd_isallowManualSchedule'
                }, {
                    header: 'Branch',
                    id: 'd_branch',
                    sortable: true,
                    dataIndex: 'branch'
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
                            driverActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
            ],
            sm: driver_select,
            listeners: {
                resize: updatePagination
            },
            /* <?php if (user_access("qugeo_driver", "saveDriver")) { ?> */
            tbar: [{
                    text: 'Create Driver',
                    tooltip: 'Create Driver',
                    id: 'driver_save_button',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Driver.addDriver();
                    }
                }], /* <?php } ?> */
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: driver_store,
                displayInfo: true,
                plugins: [driverfilters],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'd_Name'

        });
        return driver_grid;
    };

    // create a form to add/edit Driver   
    var addDriverFrm = function () {
        var formDriver = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'Driver_form',
            height: 350,
            labelAlign: 'top',
            items: [{
                layout: 'column',
                items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: .5,
                            items: [{

                                    xtype: 'textfield',
                                    id: 'd_Name',
                                    name: 'd_Name',
                                    allowBlank: false,
                                    maxLength: 25,
                                    maxLengthText: 'The maximum length for this field is 25',
                                    vtype: 'resrcdataStringspec',
                                    tabIndex: 300,
                                    minLength: 3,
                                    minLengthText: 'Minimum 3 characters required',
                                    anchor: '97%',
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
                            items: [{

                                    xtype: 'textfield',
                                    id: 'l_Name',
                                    name: 'l_Name',
                                    allowBlank: false,
                                    maxLength: 25,
                                    maxLengthText: 'The maximum length for this field is 25',
                                    vtype: 'resrcdataStringspec',
                                    tabIndex: 310,
                                    minLength: 3,
                                    minLengthText: 'Minimum 3 characters required',
                                    anchor: '97%',
                                    fieldLabel: 'Last Name',
                                }]
                            }]
                    }                ]
                    
            },
            {
                layout: 'column',
                items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: .4,
                            items: [
                               {
                                    xtype: 'textfield',
                                    id: 'd_add1',
                                    name: 'd_Add1',
                                    maxLengthText: 'The maximum length for this field is 250',
                                    maxLength: 250,
                                    tabIndex: 311,
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
                                    id: 'd_add2',
                                    name: 'd_Add2',
                                    maxLength: 250,
                                    maxLengthText: 'The maximum length for this field is 250',
                                    tabIndex: 312,
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
                                    id: 'd_add3',
                                    name: 'd_Add3',
                                    tabIndex: 313,
                                    allowBlank: true,
                                    anchor: '97%',
                                    fieldLabel: 'Postcode',
                                    listeners: {
                                        focus: function () {
                                            
                                        }
                                    }
                                }
                            ]
                            }]
                    }//column outer item end
                ]
                    
            },
            {
                layout: 'column',
                items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                            {
                            layout: 'form',
                            columnWidth: .5,
                            items: [
                                new Ext.form.ComboBox({
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender:true,
                                    fieldLabel: 'Employee Type',
                                    mode: 'local',
                                    tabIndex: 350,
                                    allowBlank: false,
                                    id: 'employee_type',
                                    anchor: '99%',
                                    name: 'employee_type',
                                    store: new Ext.data.ArrayStore({
                                        fields: [
                                            'employee_type_id',
                                            'employee_type'
                                        ],
                                        data: [["OwnEmployee", 'OwnEmployee'], ['HiredEmployee', 'HiredEmployee']]
                                    }),
                                    valueField: 'employee_type_id',
                                    displayField: 'employee_type'
                                })

                            ]
                            },
                            {
                            layout: 'form',
                            columnWidth: .5,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'emp_id',
                                    name: 'emp_id',
                                    maxLength: 25,
                                    maxLengthText: 'The maximum length for this field is 25',
                                    tabIndex: 360,
                                    allowBlank: true,
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    fieldLabel: 'Employee ID'
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
                    items: [
                            {
                            layout: 'form',
                            columnWidth: .5,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'emp_ni_number',
                                    name: 'emp_ni_number',
                                    maxLength: 25,
                                    maxLengthText: 'The maximum length for this field is 25',
                                    tabIndex: 370,
                                    allowBlank: true,
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    fieldLabel: 'Employee NI Number'
                                }
                            ]
                            },
                        {
                            layout: 'form',
                            columnWidth: .5,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'emp_email_id',
                                    name: 'emp_email_id',
                                    maxLength: 50,
                                    maxLengthText: 'The maximum length for this field is 50',
                                    tabIndex: 380,
                                    hidden:false,
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
                items: [{
                    columnWidth: 1,
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: .33,
                            items: [
                                 {
                                    xtype: 'textfield',
                                    id: 'd_ph1',
                                    name: 'd_Ph1',
                                    tabIndex: 390,
                                    allowBlank: false,
                                    vtype: 'phonespec',
                                    anchor: '97%',
                                    fieldLabel: 'Phone'
                                }
                            ]
                            },
                            {
                            layout: 'form',
                            columnWidth: .33,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'd_licence',
                                    name: 'd_licence',
                                    tabIndex: 400,
                                    allowBlank: true,
                                    vtype: 'quoteRestrictedString',
                                    anchor: '97%',
                                    fieldLabel: 'License'
                                }
                            ]
                            },{
                            layout: 'form',
                            columnWidth: .34,
                            items: [
{
                                    xtype: 'datefield',
                                    allowBlank: true,
                                    fieldLabel: 'License Validity',
                                    editable: false,
                                    anchor: '97%',
                                    tabIndex: 410,
                                    id: 'd_licenceexpairy',
                                    format: 'd/m/Y',
                                    name: 'd_licenceexpairy'
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
                            columnWidth: .33,
                            items: [
                                 {
                                    xtype: 'datefield',
                                    fieldLabel: 'Date Of Birth',
                                    editable: false,
                                    anchor: '97%',
                                    allowBlank: true,
                                    maxValue: new Date(),
                                    tabIndex: 420,
                                    id: 'd_dob',
                                    format: 'd/m/Y',
                                    name: 'd_dob'
                                }
                            ]
                            },
                            {
                            layout: 'form',
                            columnWidth: .33,
                            items: [
                                {
                                    xtype: 'combo',
                                    id: 'd_branch',
                                    name: 'br_id',
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    tabIndex: 430,
                                    // vtype: 'alphaStringspec',
                                    fieldLabel: 'Branch',
                                    editable: true,
                                    allowBlank: false,
                                    anchor: '97%',
                                    store: branchStore,
                                    //emptyText:      'Select Industry Type',
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    hiddenName: 'br_id'
                                }

                            ]
                            },
                                                    {
                            layout: 'form',
                            columnWidth: .34,
                            items: [
{
                                    xtype: 'numberfield',
                                    allowDecimals: false,
                                    id: 'd_DeliveryRange',
                                    name: 'd_DeliveryRange',
                                    tabIndex: 440,
                                    maxLength: 10,
                                    allowBlank: true,
                                    anchor: '97%',
                                    fieldLabel: 'Coverage KM.',
                                    minValue: 1
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
                            columnWidth: .3,
                            items: [{
                                    border: false,
                                    id: 'd_isallowAutoSchedule',
                                    name: 'd_isallowAutoSchedule',
                                    xtype: 'checkbox',
                                    inputValue: 1,
                                    tabIndex: 450,
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
                            columnWidth: .3,
                            items: [
                                {
                                    border: false,
                                    id: 'd_isallowManualSchedule',
                                    name: 'd_isallowManualSchedule',
                                    xtype: 'checkbox',
                                    inputValue: 1,
                                    tabIndex: 460,
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
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'd_id',
                                    name: 'd_ID'
                                }

                            ]
                            }]
                    }
                ]
                    
            }
            ]//column outer end
        });
        return formDriver;
    };

    var driverActivityStore = function () {
        var driver_store = new Ext.data.JsonStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=load_activity',
                method: 'post'
            }),
            fields: ['d_ID', 'activity_date', 'TotalJobs', 'DayStart',
                'DayEnd', 'FirstJob', 'LastJob', 'TotalKm', 'JobsCompl', 'AvgLocKm', 'details', 'TotalTrips', 'WorkingHours'],
            totalProperty: 'totalCount',
            root: 'data',
            localSort: true,
            autoLoad: false
        });

        /*
         var driver_store = new Ext.data.ArrayStore({
         proxy: new Ext.data.HttpProxy({
         url: modURL + '&op=load_activity',
         method: 'post'
         }),
         fields: ['rownum', 'd_ID', 'activity_date', 'TotalJobs', 'DayStart',
         'DayEnd', 'FirstJob', 'LastJob', 'TotalKm', 'JobsCompl', 'AvgLocKm', 'details', 'TotalTrips', 'WorkingHours'],
         localSort: true,
         autoLoad: false
         });*/

        return driver_store;
    };


    var driver_activity_details_store = function () {

        var driver_store = new Ext.data.JsonStore({
            fields: ['order', 'Diff', 'Dist'],
            localSort: true,
            autoLoad: false,
            data: arguments[0]
        });
        return driver_store;
    };


    var detailsGrid = function (data) {

        var store = driver_activity_details_store(data);
        var sm = driverSelect();

        /*    var driverfilters = new Ext.ux.grid.GridFilters({
         filters: [{
         type: 'string',
         dataIndex: 'd_Name'
         
         }, {
         type: 'string',
         dataIndex: 'branch'
         
         }]
         });
         driverfilters.remote = true;
         driverfilters.autoReload = true;*/

        var driver_grid = new Ext.grid.GridPanel({
            store: store,
            layout: 'fit',
            viewConfig: {
                forceFit: true
            },
            /*   plugins: [ driverfilters],
             title: 'Driver Activity Details',*/
            id: 'details_grid',
            columns: [{
                    header: 'Booking Ref.',
                    sortable: false,
                    dataIndex: 'order',
                    flex: 1
                }, {
                    header: 'Location',
                    sortable: true,
                    dataIndex: 'Diff',
                    width: 100
                }, {
                    header: 'DistPrevJob',
                    sortable: true,
                    dataIndex: 'Dist',
                    flex: 1
                }],
            sm: sm,
            stripeRows: true
        });
        return driver_grid;
    };

    var displayDetails = function (data) {

        var driver_winId = "driver_details_window";
        var driver_details_window = Ext.getCmp(driver_winId);
        if (Ext.isEmpty(driver_details_window)) {

            driver_details_window = new Ext.Window({
                id: 'driver_details_window',
                layout: 'fit',
                width: 500,
                title: 'Activity Details',
                height: 600,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                resizable: false,
                items: detailsGrid(data),
                buttons: [{
                        text: 'Close',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            driver_details_window.close();
                        }
                    }],
                listeners: {
                    afterrender: function () {

                    }
                }
            });
        }
        driver_details_window.doLayout();
        driver_details_window.show(this);
        driver_details_window.center();
    };

    var driverActivityMAin = function (id) {

        var driver_activity_store = driverActivityStore();
        var driver_select = driverSelect();
        /*var action = new Ext.ux.grid.RowActions({
         hideMode: 'display',
         autoWidth: false,
         width: 50,
         dataIndex: 'details',
         actions: [{
         sortable: false,
         tooltip: 'Details',
         iconCls: 'my-icon57',
         callback: function (grid, rec, row, col) {
         displayDetails(rec.get('details'));
         }
         }]
         });*/
        /*['rownum', 'd_ID', 'activity_date', 'TotalJobs', 'DayStart',
         'DayEnd', 'FirstJob', 'LastJob', 'TotalKm', 'JobsCompl', 'AvgLocKm', 'details', 'TotalTrips', 'WorkingHours']*/
        var driver_activity_filters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'activity_date'
                }, {
                    type: 'string',
                    dataIndex: 'FirstJob'
                }, {
                    type: 'string',
                    dataIndex: 'LastJob'
                }, {
                    type: 'string',
                    dataIndex: 'WorkingHours'
                }, {
                    type: 'numeric',
                    dataIndex: 'TotalTrips'
                }, {
                    type: 'numeric',
                    dataIndex: 'TotalJobs'
                }, {
                    type: 'numeric',
                    dataIndex: 'JobsCompl'
                }, {
                    type: 'numeric',
                    dataIndex: 'TotalKm'
                }, {
                    type: 'numeric',
                    dataIndex: 'AvgLocKm'
                }]
        });
        driver_activity_filters.local = true;
        driver_activity_filters.autoReload = true;

        var driver_grid = new Ext.grid.GridPanel({
            store: driver_activity_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true
            },
            plugins: [/*action,*/ driver_activity_filters],
            title: 'Driver Activity',
            id: id,
            iconCls: 'driver',
            columns: [{
                    header: 'Date',
                    id: 'activity_date',
                    sortable: true,
                    dataIndex: 'activity_date'
                }, {
                    header: 'First Job',
                    sortable: true,
                    dataIndex: 'FirstJob'
                }, {
                    header: 'Last Job',
                    sortable: true,
                    dataIndex: 'LastJob'
                }, {
                    header: 'Wrk Hrs',
                    sortable: true,
                    dataIndex: 'WorkingHours'
                }, {
                    header: 'Session',
                    sortable: true,
                    dataIndex: 'TotalTrips',
                    align: 'right'
                }, {
                    header: 'Total Jobs',
                    sortable: true,
                    dataIndex: 'TotalJobs',
                    align: 'right'
                }, {
                    header: 'Compl Jobs ',
                    sortable: true,
                    dataIndex: 'JobsCompl',
                    align: 'right'
                }, {
                    header: 'Tot. Km.',
                    sortable: true,
                    dataIndex: 'TotalKm',
                    align: 'right'
                }, {
                    header: 'Avg. Loc. Var.',
                    sortable: true,
                    dataIndex: 'AvgLocKm',
                    align: 'right'
                }, {header: '', dataIndex: 'default_col', width: 20}],
            sm: driver_select,
            listeners: {
                rowdblclick: function (grid, rowIndex, e) {
                    var rec = grid.getStore().getAt(rowIndex);
                    displayDetails(rec.get('details'));
                }
            },
            tbar: [{html: '&nbsp;Branch :&nbsp;'}, {
                    xtype: 'combo',
                    id: 'activity_branch',
                    name: 'activity_branch',
                    typeAhead: true,
                    forceSelection: true,
                    tabIndex: 1,
                    editable: true,
                    anchor: '97%',
                    store: activityBranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'activity_branch',
                    listeners: {
                        select: function () {
                            var activity_driver = Ext.getCmp('activity_driver');
                            activity_driver.reset();
                            activity_driver.getStore().removeAll();
                            activity_driver.getStore().baseParams.Branch = this.getValue();
                            activity_driver.getStore().load();
                        }
                    }
                }, {html: '&nbsp;Driver :&nbsp;'}, {
                    xtype: 'combo',
                    id: 'activity_driver',
                    name: 'activity_driver',
                    typeAhead: true,
                    forceSelection: true,
                    tabIndex: 2,
                    editable: true,
                    anchor: '97%',
                    store: driverComboStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'driver_name',
                    valueField: 'driver_id',
                    hiddenName: 'activity_driver'
                }, {html: '&nbsp;Date From :&nbsp;'}, {
                    xtype: 'datefield',
                    name: 'activity_date_from',
                    id: 'activity_date_from',
                    format: 'Y-m-d',
                    editable: false,
                    maxValue: new Date(),
                    anchor: '98%',
                    listeners: {
                        select: function () {
                            Ext.getCmp('activity_date_to').reset();
                        }
                    }
                }, {html: '&nbsp;To :&nbsp;'}, {
                    xtype: 'datefield',
                    name: 'activity_date_to',
                    id: 'activity_date_to',
                    format: 'Y-m-d',
                    editable: false,
                    maxValue: new Date(),
                    anchor: '90%',
                    listeners: {
                        select: function () {
                            var frm = Ext.getCmp('activity_date_from').getValue();
                            var from = new Date(frm);
                            var todate = Ext.getCmp('activity_date_to').getValue();
                            var to = new Date(todate);

                            var diff = (to.getTime() - from.getTime()) / (1000 * 60 * 60 * 24);
                            if (diff > 31) {
                                Ext.getCmp('activity_date_to').reset();
                                Ext.MessageBox.alert('Warning', 'Date range must be in 31 days');
                            }
                            if (todate < frm) {
                                Ext.getCmp('activity_date_to').reset();
                                Ext.MessageBox.alert('Warning', 'Invalid Selection');
                            }
                        }
                    }
                }, '-', {
                    xtype: 'button',
                    text: 'Search',
                    icon: IMAGE_BASE_PATH + '/default/icons/search.png',
                    iconCls: 'update',
                    handler: function () {
                        if (Ext.isEmpty(Ext.getCmp('activity_driver').getValue()) || Ext.isEmpty(Ext.getCmp('activity_branch').getValue())) {
                            Ext.MessageBox.alert("Notification", 'Please select Branch and Driver to continue.');
                        } else {
                            var grid = Ext.getCmp('driver_activity_panel').getStore();
                            grid.removeAll();
                            grid.baseParams = {
                                activity_driver: Ext.getCmp('activity_driver').getValue(),
                                activity_date_from: Ext.getCmp('activity_date_from').getRawValue(),
                                activity_date_to: Ext.getCmp('activity_date_to').getRawValue()
                            };
                            grid.load();
                        }
                    }
                }, '-', {
                    xtype: 'button',
                    text: 'Reset',
                    icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        Ext.getCmp('activity_branch').reset();
                        Ext.getCmp('activity_driver').reset();
                        Ext.getCmp('activity_driver').getStore().removeAll();
                        Ext.getCmp('activity_date_from').reset();
                        Ext.getCmp('activity_date_to').reset();
                        Ext.getCmp('driver_activity_panel').getStore().baseParams = '';
                    }
                }, '-', '->', 
//                {
//                    xtype: 'exportbutton',
//                    component: driver_grid,
//                    text: "Download as .xls",
//                    title: 'Driver Activity',
//                    store: driver_activity_store,
//                    cls: 'driver_activity.xls'
//                }
            ],
            stripeRows: true,
            autoExpandColumn: 'activity_date'
        });
        return driver_grid;
    };
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

            var drivergrid = Ext.getCmp('griddriver');
            if (!drivergrid) {
                drivergrid = createdriverGrid();
            }
            loadDriver();
            Application.UI.addTab(drivergrid);
            drivergrid.doLayout();
        },
        //for add driver    
        addDriver: function (id) {
            var driver_winId = "driver_window";
            //create the window on the first click and reuse on subsequent clicks
            var driver_window = Ext.getCmp(driver_winId);
            if (Ext.isEmpty(driver_window)) {
                var driverfrm = addDriverFrm();
                driver_window = new Ext.Window({
                    id: 'driver_window',
                    layout: 'fit',
                    width: 500,
                    title: 'Create Driver  ',
                    autoHeight: true,
                    iconCls: (id) ? 'my-icon93' : 'my-icon94',
                    plain: true,
                    constrain: true,
                    draggable: true,
                    modal: true,
                    resizable: false,
                    items: [driverfrm],
                    //button for save
                    buttons: [{
                            text: 'Cancel',
                            id: 'btnCancel',
                            tabIndex: 500,
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                driver_window.close();
                            }
                        }, 
                        {
                            text: 'Save',
                            id: 'btnsave',
                            tabIndex: 600,
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                if (driverfrm.getForm().isValid()) {
                                    driverfrm.getForm().submit({
                                        waitMsg: 'Saving...',
                                        url: modURL,
                                        params: {
                                            op: 'saveDriver',
                                            d_ID: id,
                                            apikey: _SESSION.apikey,
                                            tstamp: t_stamp
                                        },
                                        //for submit success
                                        success: function (frm, action) {

                                            if (action && action.response.responseText) {
                                                var tmp = Ext.decode(action.response.responseText);
                                                if (tmp.success == true) {
                                                    Application.example.msg("Notification", tmp.msg); 
                                                    driver_window.close();
                                                    loadDriver();
//                                                    function (btn) {
//                                                        if (btn == 'ok') {
//                                                            driver_window.close();
//                                                            loadDriver();
//                                                        }
//                                                    });
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
                                } else {
                                    Ext.MessageBox.alert("Notification", 'Please enter valid data.');
                                }
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            if (_SESSION.typId == '3' || _SESSION.typId == '4')
                            {
                                Ext.getCmp('d_branch').store.load({
                                    callback: function () {
                                        //Ext.getCmp('d_branch').setValue(_SESSION.typdetsid);
                                    }
                                });

                            }
                        }
                    }
                });

            }
            //for load the details of selected driver    
            branchStore.load({
                callback: function () {
                    if (!Ext.isEmpty(id)) {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        driverfrm.load({
                            url: modURL + '&op=getDriver',
                            params: {
                                'd_ID': id,
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp
                            },
                            success: function (frm, action) {
                                var obj = Ext.decode(action.response.responseText);
                                Ext.getCmp('d_branch').setValue(obj.data.br_id);
                                driver_window.setTitle("Edit Driver  ");



                            }
                        });
                    }
                }
            });


            driver_window.doLayout();
            driver_window.show(this);
            driver_window.center();
        },
        driverActivity: function () {
            var panelId = 'driver_activity_panel';
            var driver_activity_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(driver_activity_panel)) {
                driver_activity_panel = driverActivityMAin(panelId);
                Application.UI.addTab(driver_activity_panel);
                driver_activity_panel.doLayout();
            } else {
                Application.UI.addTab(driver_activity_panel);
            }
        }
    };
//////////////////////////////EO Public Area///////////////////////////////
}
();
