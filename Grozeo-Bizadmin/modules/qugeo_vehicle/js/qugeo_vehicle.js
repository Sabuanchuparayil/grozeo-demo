
var Vehicle = function () {
    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Private Area------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Private Variables---------------------------//
    //Configure the Number of records can display in Grid at a time.
    var recs_per_page = 12;
    var modURL = '?module=qugeo_vehicle';


    var proxy = new Ext.data.HttpProxy({
        url: modURL + '&op=list',
        method: 'post'
    });
    /** 
     * var vehicle_store = Store for 'vehicle_grid'  
     **/
    var vehicleStore = function () {
        var vehicle_store = new Ext.data.JsonStore({
            proxy: proxy,
            fields: ['rownum', 'v_ID', 'v_No', 'vhty_name', 'v_manufacturor', 'v_EntryBy', 'v_Active', 'v_Hired', 'brName'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'v_ID',
            // turn on remote sorting
            remoteSort: true
        });
        return vehicle_store;
    }
    /** 
     * var vehicleTypeStore = Vehicle Type combo store  
     **/
    var vehicleTypeStore = new Ext.data.JsonStore({
        fields: ['vhty_id', 'vhty_name'],
        url: '?module=qugeo_vehicle&op=getVehicleType',
        root: 'data'

    });
    /** 
     * var companyStore = Company combo store  
     **/
    var companyStore = new Ext.data.JsonStore({
        fields: ['comp_id', 'comp_name'],
        url: '?module=qugeo_vehicle&op=getCompany',
        root: 'data'

    });
    /** 
     * var hireownerStore = HVO combo store  
     **/
    var hireownerStore = new Ext.data.JsonStore({
        fields: ['vhow_ID', 'vhow_Name'],
        url: '?module=qugeo_vehicle&op=getHireOwner',
        root: 'data'

    });
    /** 
     * var hpaStore = Nature combo store  
     **/
    var hpaStore = new Ext.data.JsonStore({
        fields: ['hpa_id', 'hpa_status'],
        url: '?module=qugeo_vehicle&op=gethpastatus',
        root: 'data'

    });
    /** 
     * var branchStore = Branch combo store  
     **/
    var branchStore = new Ext.data.JsonStore({
        fields: ['br_ID', 'br_Name'],
        url: '?module=qugeo_vehicle&op=getBranch',
        root: 'data',
        listeners: {
            load: function () {
                if (Ext.getCmp('v_branch').getValue() == '' || Ext.getCmp('v_branch').getValue() == '0')
                    Ext.getCmp('v_branch').setValue(1);
            }
        }

    });
    //for selecting multi rows in a grid 
    function vehicleSelect() {
        return  new Ext.grid.RowSelectionModel({
            multiSelect: true
        });
    }



    //----------------------------Private Methods----------------------------//

    // Load vehicle
    var loadVehicle = function () {
        Ext.getCmp('gridvehicle').getStore().setDefaultSort('v_No', 'asc');
        Ext.getCmp('gridvehicle').getStore().removeAll();
        Ext.getCmp('gridvehicle').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }

    /**
     * var vehicleAction =  List of actions in 'vehicle_grid'
     * Actions Edit,Delete
     **/

    var vehicleAction = function () {
        var action = new Ext.ux.grid.RowActions({
            hideMode: 'display',
            //header: '',
            // tooltip: '',
            autoWidth: false,
            width: 30,
            actions: [/* <?php if (user_access("qugeo_vehicle", "saveVehicle")) { ?> */{
                    sortable: false,
                    tooltip: 'Edit Vehicle  ',
                    iconCls: 'finascop_edit',
                    callback: function (grid, rec, row, col) {
                        Application.Vehicle.addVehicle(rec.data.v_ID);
                    }
                }, {
                    sortable: false,
                    tooltip: 'Disable/Enable Vehicle',
                    iconCls: 'my-icon24',
                    hide: false,
                    id: 'id_disableSettings',
                    callback: function (grid, rec, row, col) {

                        if (rec.data.v_Active == 'Y')
                            Application.Vehicle.disableVehicleSettings(rec.data.v_ID);
                        if (rec.data.v_Active == 'N')
                            Application.Vehicle.enablebleVehicleSettings(rec.data.v_ID);

                    }
                }/* <?php } ?> */]
        });
        return action;
    }
    //for pagination 
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }

    var createVehicleGrid = function () {
        var vehicle_store = vehicleStore();
        var vehicle_select = vehicleSelect();
        var action = vehicleAction();
        /**
         * var vehiclefilters = Plugin used for the 'vehicle_grid' 
         **/
        var vehiclefilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'v_No'

                }, {
                    type: 'string',
                    dataIndex: 'vhty_name'

                }, {
                    type: 'string',
                    dataIndex: 'v_manufacturor'

                }, {
                    type: 'string',
                    dataIndex: 'v_EntryBy'

                }, {
                    type: 'list',
                    options: ['Y', 'N'],
                    phpMode: true,
                    dataIndex: 'v_Hired'

                }, {
                    type: 'string',
                    dataIndex: 'brName'

                }, {
                    type: 'list',
                    options: ['Y', 'N'],
                    phpMode: true,
                    dataIndex: 'v_Active'

                }]
        });
        vehiclefilters.remote = true;
        vehiclefilters.autoReload = true;
        /**
         * var vehicle_grid = creating a Grid to display All Vehicle Reg No,Vehicle Type,Manufacturer,EntryBy,Active
         **/

        var vehicle_grid = new Ext.grid.GridPanel({
            store: vehicle_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                //Condition checking and Assign color to row  
                //checking 'v_Active' 
                getRowClass: function (record, index) {

                    if (record.data.v_Active == 'Y')
                    {
                        return '';
                    } else
                        return 'indicateCOL';
                }
            },
            plugins: [vehiclefilters],
            title: 'Vehicle',
            //iconCls: 'my-icon92',
            id: 'gridvehicle',
            columns: [{
                    header: '',
                    sortable: false,
                    dataIndex: 'rownum',
                    align: 'right',
                    width: 3
                }, {
                    header: 'Vehicle Reg No  ',
                    id: 'v_regno',
                    sortable: true,
                    dataIndex: 'v_No'


                }, {
                    header: 'Vehicle Type',
                    id: 'veh_type',
                    sortable: true,
                    dataIndex: 'vhty_name'


                }, {
                    header: 'Branch',
                    id: 'brName',
                    sortable: true,
                    dataIndex: 'brName'


                }, {
                    header: 'Manufacturer',
                    id: 'v_manufr',
                    sortable: true,
                    dataIndex: 'v_manufacturor'


                }, {
                    header: 'EntryBy',
                    id: 'v_EntryBy',
                    sortable: true,
                    dataIndex: 'v_EntryBy',
                    width: 60

                }, {
                    header: 'Hired',
                    id: 'v_Hired',
                    sortable: true,
                    dataIndex: 'v_Hired',
                    width: 20


                }, {
                    header: 'Active',
                    id: 'v_Active',
                    sortable: true,
                    dataIndex: 'v_Active',
                    width: 20


                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            vehicleActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
//                {
//                    xtype: 'actioncolumn',
//                    header: 'Action',
//                    hideable: false,
//                    sortable: false,
//                    groupable: false,
//                    tooltip: 'Action',
//                    items: [{
//                            iconCls: 'edit',
//                            tooltip: 'Edit Branch Details',
//                            handler: function (grid, rowIndex, colIndex) {
//                                var record = grid.store.getAt(rowIndex);
//                                Application.Vehicle.addVehicle(record.get('v_ID'));
//                            }
//                        }, {
//                            tooltip: 'Disable/Enable Vehicle',
//                            iconCls: 'my-icon24',
//                            handler: function (grid, rowIndex, colIndex) {
//                                var record = grid.store.getAt(rowIndex);
//                                var status = record.get('v_Active');
//                                if (status == 'Y')
//                                    Application.Vehicle.disableVehicleSettings(record.get('v_ID'));
//                                if (status == 'N')
//                                    Application.Vehicle.enablebleVehicleSettings(record.get('v_ID'));
//                            }
//                        }]
//                }//,action
            ],
            sm: vehicle_select,
            listeners: {
                resize: updatePagination
            },
            /* <?php if (user_access("qugeo_vehicle", "saveVehicle")) { ?> */
            tbar: [{
                    text: 'Create Vehicle',
                    tooltip: 'Create Vehicle',
                    id: 'vehicle_save_button',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Vehicle.addVehicle();
                    }
                }], /* <?php } ?> */
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: vehicle_store,
                displayInfo: true,
                plugins: [vehiclefilters],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'v_Name'

        });
        return vehicle_grid;
    }
    var vehicleActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var v_ID = Ext.getCmp('gridvehicle').getSelectionModel().getSelections()[0].data.v_ID;
                    Application.Vehicle.addVehicle(v_ID);
                }
            }, {
                text: "Enable/Disable",
                handler: function () {
                    var v_ID = Ext.getCmp('gridvehicle').getSelectionModel().getSelections()[0].data.v_ID;
                    var v_Active = Ext.getCmp('gridvehicle').getSelectionModel().getSelections()[0].data.v_Active;
                    if (v_Active == 'Y')
                        Application.Vehicle.disableVehicleSettings(v_ID);
                    if (v_Active == 'N')
                        Application.Vehicle.enablebleVehicleSettings(v_ID);
                }
            }]
    });

    /** 
     * var formVehicle = Form Panel for Add/Edit Vehicle 
     **/
    var addVehicleFrm = function () {
        var formVehicle = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'vehicle_form',
            height: 380,
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: .5,
                            LabelWidth: 180,
                            items: [{
                                    xtype: 'textfield',
                                    id: 'v_No',
                                    name: 'v_No',
                                    allowBlank: false,
                                    //vtype: 'dataStringspec',
                                    vtype: 'vehicledataStringspec',
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    anchor: '97%',
                                    tabIndex: 400,
                                    minLength: 3,
                                    minLengthText: 'Minimum 3 characters required',
                                    fieldLabel: 'Vehicle Reg No ',
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);
                                        }
                                    }
                                }, {
                                    xtype: 'textfield',
                                    id: 'v_manufacturer',
                                    name: 'v_manufacturor',
                                    allowBlank: false,
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 402,
                                    fieldLabel: 'Manufacturer'
                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Date Of Manf.',
                                    allowBlank: false,
                                    editable: false,
                                    maxValue: new Date(),
                                    anchor: '97%',
                                    id: 'dt_manufacture',
                                    maxLength: 50,
                                    tabIndex: 404,
                                    maxLengthText: 'The maximum length for this field is 50',
                                    format: 'd/m/Y',
                                    name: 'dt_manufacture'

                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Fitness Certificate',
                                    editable: false,
                                    allowBlank: false,
                                    anchor: '97%',
                                    maxLength: 50,
                                    tabIndex: 406,
                                    maxLengthText: 'The maximum length for this field is 50',
                                    id: 'dt_fitness',
                                    format: 'd/m/Y',
                                    name: 'dt_fitness'

                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Insurance',
                                    editable: false,
                                    allowBlank: false,
                                    anchor: '97%',
                                    id: 'dt_insurance',
                                    maxLength: 50,
                                    tabIndex: 408,
                                    maxLengthText: 'The maximum length for this field is 50',
                                    format: 'd/m/Y',
                                    name: 'dt_insurance'

                                }, {
                                    xtype: 'numberfield',
                                    id: 'hr_ir',
                                    name: 'hr_ir',
                                    //allowBlank: false,
                                    vtype: 'dataStringspec',
                                    maxLength: 13,
                                    maxLengthText: 'The maximum length for this field is 13',
                                    anchor: '97%',
                                    tabIndex: 410,
                                    qtip: 'Hire Purchase Interest Rate',
                                    fieldLabel: 'HPIR'
                                }, {
                                    xtype: 'textfield',
                                    id: 'v_chasisno',
                                    name: 'v_chasisno',
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 412,
                                    fieldLabel: 'Chasis No'
                                }, {
                                    xtype: 'textfield',
                                    id: 'v_engineno',
                                    name: 'v_Engineno',
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 414,
                                    fieldLabel: 'Engine No'
                                }, {
                                    xtype: 'textfield',
                                    id: 'v_make',
                                    name: 'v_Make',
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 416,
                                    fieldLabel: 'Make'
                                }, {
                                    xtype: 'fieldset',
                                    title: 'Truck Capacity',
                                    autoHeight: true,
                                    anchor: '97%',
                                    items: [{
                                            layout: 'column',
                                            items: [{
                                                    layout: 'form',
                                                    columnWidth: .5,
                                                    labelAlign: 'top',
                                                    items: [{
                                                            xtype: 'numberfield',
                                                            id: 'tonnage',
                                                            LabelWidth: 50,
                                                            readOnly: true,
                                                            name: 'tonnage',
                                                            style: 'background: #ccc;',
                                                            //allowBlank: false,
                                                            anchor: '97%',
                                                            tabIndex: 418,
                                                            fieldLabel: 'Tonnage'
                                                        }]
                                                }, {
                                                    layout: 'form',
                                                    columnWidth: .5,
                                                    labelAlign: 'top',
                                                    items: [{
                                                            xtype: 'textfield',
                                                            id: 'volume',
                                                            name: 'volume',
                                                            LabelWidth: 50,
                                                            style: 'background: #ccc;',
                                                            readOnly: true,
                                                            tabIndex: 419,
                                                            //allowBlank: false,                                    
                                                            anchor: '97%',
                                                            fieldLabel: 'Volume (LxBxH)'
                                                        }]
                                                }]
                                        }]
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .5,
                            LabelWidth: 180,
                            items: [{
                                    xtype: 'combo',
                                    id: 'v_typeid',
                                    name: 'v_Type',
                                    mode: 'local',
                                    //typeAhead:       true,
                                    forceSelection: true,
                                    // vtype: 'alphaStringspec',
                                    fieldLabel: 'Vehicle Type',
                                    editable: true,
                                    allowBlank: false,
                                    anchor: '97%',
                                    tabIndex: 401,
                                    store: vehicleTypeStore,
                                    emptyText: 'Select Vehicle Type',
                                    triggerAction: 'all',
                                    minChars: 2,
                                    typeAhead: true,
                                    displayField: 'vhty_name',
                                    valueField: 'vhty_id',
                                    hiddenName: 'v_Type',
                                    listeners: {
                                        select: function (cbo) {
                                            Ext.Ajax.request({
                                                //submit to server  
                                                waitMsg: '...',
                                                url: modURL,
                                                params: {
                                                    'op': 'getVolume',
                                                    'v_Type': cbo.getValue()
                                                },
                                                callback: function (options, success, response) {
                                                    if (success) {
                                                        if (response && response.responseText) {
                                                            var tmp = Ext.decode(response.responseText);
                                                            Ext.getCmp('volume').setValue(tmp.data.volume);
                                                            Ext.getCmp('tonnage').setValue(tmp.data.tonnage);
                                                        }

                                                    }

                                                }
                                            } //end Ajax request config
                                            );// end Ajax request initialization 
                                        }
                                    }
                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Date Of Purchase',
                                    editable: false,
                                    maxValue: new Date(),
                                    allowBlank: false,
                                    anchor: '97%',
                                    tabIndex: 403,
                                    maxLength: 50,
                                    maxLengthText: 'The maximum length for this field is 50',
                                    id: 'dt_purchase',
                                    format: 'd/m/Y',
                                    name: 'dt_purchase'

                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Valid Permit Date',
                                    editable: false,
                                    anchor: '97%',
                                    allowBlank: false,
                                    tabIndex: 405,
                                    maxLength: 50,
                                    maxLengthText: 'The maximum length for this field is 50',
                                    id: 'dt_permit',
                                    format: 'd/m/Y',
                                    name: 'dt_permit'

                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'POC',
                                    editable: false,
                                    allowBlank: false,
                                    qtip: 'Pollution Certificate',
                                    anchor: '97%',
                                    tabIndex: 407,
                                    maxLength: 50,
                                    maxLengthText: 'The maximum length for this field is 50',
                                    id: 'dt_pollution',
                                    format: 'd/m/Y',
                                    name: 'dt_pollution'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'hr_inst',
                                    name: 'hr_inst',
                                    maxLength: 13,
                                    maxLengthText: 'The maximum length for this field is 13',
                                    // allowBlank: false,
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 409,
                                    qtip: 'Hire Purchase Instalments',
                                    fieldLabel: 'HPI'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'v_cost',
                                    name: 'v_cost',
                                    // allowBlank: false,
                                    maxLength: 13,
                                    allowBlank: false,
                                    maxLengthText: 'The maximum length for this field is 13',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 411,
                                    fieldLabel: 'Rate/KM'
                                }, {
                                    xtype: 'textfield',
                                    id: 'v_permit',
                                    name: 'v_permit',
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 413,
                                    fieldLabel: 'Permit No'
                                }, {
                                    xtype: 'textfield',
                                    id: 'v_model',
                                    name: 'v_Model',
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 415,
                                    fieldLabel: 'Model',
                                    style: "padding-bottom:2px;"
                                }, {
                                    xtype: 'textfield',
                                    id: 'v_color',
                                    name: 'v_color',
                                    maxLength: 255,
                                    maxLengthText: 'The maximum length for this field is 255',
                                    vtype: 'dataStringspec',
                                    anchor: '97%',
                                    tabIndex: 417,
                                    fieldLabel: 'Color'
                                }, {
                                    xtype: 'fieldset',
                                    id: 'cmpfieldset',
                                    anchor: '97%',
                                    items: [{
                                            layout: 'column',
                                            items: [{
                                                    layout: 'form',
                                                    columnWidth: 1,
                                                    tabIndex: 420,
                                                    style: "padding-bottom:6px;",
                                                    items: [{
                                                            xtype: 'radiogroup',
                                                            id: 'vradio',
                                                            fieldLabel: '',
                                                            vertical: false,
                                                            items: [
                                                                {
                                                                    id: 'ofd',
                                                                    name: 'v_isCmp',
                                                                    boxLabel: 'OFD',
                                                                    checked: true,
                                                                    inputValue: 1,
                                                                    listeners: {
                                                                        focus: function () {

                                                                            Ext.getCmp('cmbcomp_ID').show();
                                                                            Ext.getCmp('hireId').setValue('');
                                                                            Ext.getCmp('hireId').hide();
                                                                            hpaStore.removeAll();
                                                                            hpaStore.baseParams.type = 'ofd';
                                                                            hpaStore.load();
                                                                            Ext.getCmp('hpa_status').setValue('');
                                                                        }
                                                                    }
                                                                },
                                                                {
                                                                    id: 'hire',
                                                                    name: 'v_isCmp',
                                                                    boxLabel: 'Hire',
                                                                    inputValue: 2,
                                                                    listeners: {
                                                                        focus: function () {

                                                                            Ext.getCmp('hireId').show();
                                                                            Ext.getCmp('cmbcomp_ID').setValue('');
                                                                            Ext.getCmp('cmbcomp_ID').hide();
                                                                            hpaStore.removeAll();
                                                                            hpaStore.baseParams.type = 'hire';
                                                                            hpaStore.load();
                                                                            Ext.getCmp('hpa_status').setValue('');
                                                                        }
                                                                    }
                                                                }

                                                            ]

                                                        }]
                                                }]
                                        }, {
                                            xtype: 'combo',
                                            id: 'hireId',
                                            name: 'vhow_ID',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            // vtype: 'alphaStringspec',
                                            fieldLabel: 'HVO',
                                            tabIndex: 418,
                                            qtip: 'Hired Vehicle Owner',
                                            editable: true,
                                            //allowBlank: false,
                                            hidden: true,
                                            anchor: '97%',
                                            store: hireownerStore,
                                            emptyText: 'Select Owner',
                                            triggerAction: 'all',
                                            minChars: 4,
                                            displayField: 'vhow_Name',
                                            valueField: 'vhow_ID',
                                            hiddenName: 'vhow_ID'
                                        }, {
                                            xtype: 'combo',
                                            id: 'cmbcomp_ID',
                                            name: 'comp_ID',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            // vtype: 'alphaStringspec',
                                            fieldLabel: 'Company',
                                            tabIndex: 421,
                                            editable: true,
                                            // allowBlank: false,
                                            //hidden:true,
                                            anchor: '97%',
                                            store: companyStore,
                                            emptyText: 'Select Company',
                                            triggerAction: 'all',
                                            minChars: 4,
                                            displayField: 'comp_name',
                                            valueField: 'comp_id',
                                            hiddenName: 'comp_ID'
                                        }, {
                                            xtype: 'combo',
                                            id: 'hpa_status',
                                            name: 'hpa_status',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            // vtype: 'alphaStringspec',
                                            fieldLabel: 'Nature',
                                            tabIndex: 422,
                                            editable: true,
                                            allowBlank: false,
                                            //hidden:true,
                                            anchor: '97%',
                                            store: hpaStore,
                                            emptyText: 'Select Nature',
                                            triggerAction: 'all',
                                            minChars: 4,
                                            displayField: 'hpa_status',
                                            valueField: 'hpa_id',
                                            hiddenName: 'hpa_id'
                                        }, {
                                            xtype: 'combo',
                                            id: 'v_branch',
                                            name: 'br_id',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            // vtype: 'alphaStringspec',
                                            fieldLabel: 'Branch',
                                            tabIndex: 423,
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
                                        }]
                                }, {
                                    xtype: 'hidden',
                                    id: 'v_id',
                                    name: 'v_ID'
                                }]
                        }]



                }]
        });
        return formVehicle;
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

            var vehiclegrid = Ext.getCmp('gridvehicle');
            if (!vehiclegrid) {
                vehiclegrid = createVehicleGrid();
            }
            loadVehicle();
            Application.UI.addTab(vehiclegrid);
            vehiclegrid.doLayout();
        },
        //add vehicle
        addVehicle: function (id) {
            var vehicle_winId = "vehicle_window";
            //create the window on the first click and reuse on subsequent clicks
            var vehicle_window = Ext.getCmp(vehicle_winId);
            if (Ext.isEmpty(vehicle_window)) {
                var vehiclefrm = addVehicleFrm();
                vehicle_window = new Ext.Window({
                    id: 'vehicle_window',
                    layout: 'fit',
                    width: 700,
                    title: 'Create Vehicle  ',
                    autoHeight: true,
                    draggable: false,
                    iconCls: (id) ? 'my-icon101' : 'my-icon100',
                    plain: true,
                    constrain: true,
                    draggable: true,
                    modal: true,
                    resizable: false,
                    items: [vehiclefrm],
                    //button for save
                    buttons: [{
                            text: 'Cancel',
                            tabIndex: 422,
                            id: 'btnCancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                vehicle_window.close();
                            }
                        },
                        {
                            text: 'Save',
                            tabIndex: 421,
                            id: 'btnsave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                //submit to server
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                if (!vehiclefrm.getForm().isValid()) {
                                    console.log("Please enter mandatory fields to continue.");
                                    Ext.MessageBox.show({
                                        title: 'Error!',
                                        msg: "Please enter mandatory fields to continue.",
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.ERROR,
                                        width: 325
                                    });
                                } else {
                                    vehiclefrm.getForm().submit({
                                        waitMsg: 'Saving...',
                                        url: modURL,
                                        params: {
                                            op: 'saveVehicle',
                                            v_ID: id,
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
                                                 act = 'Create Vehicle  ';
                                                 else
                                                 act = 'Edit Vehicle  ';*/

                                                if (tmp.success == true) {
                                                    Application.example.msg("Notification", tmp.msg);
                                                    vehicle_window.close();
                                                    loadVehicle();
                                                    //                                                function (btn) {
                                                    ////                                                    if (btn == 'ok') {
                                                    ////                                                        // Application.ActLog.actionLog(data,act);  
                                                    ////                                                        vehicle_window.close();
                                                    ////                                                        loadVehicle();
                                                    ////
                                                    ////                                                    }
                                                    ////                                                });
                                                } else {
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
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            if (_SESSION.typId == '3' || _SESSION.typId == '4')
                            {
                                Ext.getCmp('v_branch').store.load({
                                    callback: function () {

                                        Ext.getCmp('v_branch').setValue(_SESSION.typdetsid);

                                    }
                                });
                            }
                        }
                    }
                });
            }


            vehicleTypeStore.load();
            companyStore.baseParams.id = id;
            companyStore.load();
            hireownerStore.baseParams.id = id;
            hireownerStore.load();
            hpaStore.baseParams.type = 'ofd';
            hpaStore.load();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            branchStore.load({
                //for load the details of slected vehicle    
                callback: function () {
                    if (!Ext.isEmpty(id)) {

                        vehiclefrm.load({
                            url: modURL + '&op=getVehicle',
                            params: {
                                'v_ID': id,
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp
                            },
                            success: function (frm, action) {
                                var obj = Ext.decode(action.response.responseText);
                                Ext.getCmp('v_branch').setValue(obj.data.br_id);
                                Ext.getCmp('hireId').getStore().load({
                                    callback: function () {
                                        Ext.getCmp('hireId').setValue(Ext.getCmp('hireId').getValue());
                                    }
                                });
                                if (obj.data.v_isCmp == 2) {
                                    Ext.getCmp('hire').checked = true;
                                    Ext.getCmp('ofd').checked = false;
                                    Ext.getCmp('hireId').show();
                                    Ext.getCmp('cmbcomp_ID').setValue('');
                                    Ext.getCmp('cmbcomp_ID').hide();
                                    hpaStore.removeAll();
                                    hpaStore.baseParams.type = 'hire';
                                    hpaStore.load({
                                        callback: function () {
                                            Ext.getCmp('hpa_status').setValue(obj.data.hpa_id);
                                        }
                                    });
                                }
                                if (obj.data.v_isCmp == 1) {
                                    Ext.getCmp('ofd').checked = true;
                                    Ext.getCmp('hire').checked = false;
                                    Ext.getCmp('cmbcomp_ID').show();
                                    Ext.getCmp('hireId').setValue('');
                                    Ext.getCmp('hireId').hide();
                                    hpaStore.removeAll();
                                    hpaStore.baseParams.type = 'ofd';
                                    hpaStore.load({
                                        callback: function () {
                                            Ext.getCmp('hpa_status').setValue(obj.data.hpa_id);
                                        }
                                    });
                                }
                                vehicle_window.setTitle("Edit Vehicle  ");
                            }
                        });

                    }
                }
            });


            vehicle_window.doLayout();
            vehicle_window.show(this);
            vehicle_window.center();
        },
//disable vehicle settings
        disableVehicleSettings: function (v_ID) {

            Ext.MessageBox.confirm('Confirm', 'Are you sure to disable this settings?', function (btn) {

                if (btn == "yes") {

                    Ext.Ajax.request({
//submit to server
                        url: modURL + "&op=disableSettings",
                        method: 'POST',
                        params: {
                            VehicleId: v_ID
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

                                    Ext.getCmp('gridvehicle').store.reload({
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
        },
//Enable vehicle settings
        enablebleVehicleSettings: function (v_ID) {

            Ext.MessageBox.confirm('Confirm', 'Are you sure to enable this settings?', function (btn) {

                if (btn == "yes") {

                    Ext.Ajax.request({
//submit to server
                        url: modURL + "&op=enableSettings",
                        method: 'POST',
                        params: {
                            VehicleId: v_ID
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

                                    Ext.getCmp('gridvehicle').store.reload({
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
};
Application.Vehicle = new Vehicle();