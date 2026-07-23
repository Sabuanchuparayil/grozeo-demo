/** @author Lakshmi Jayaram <lakshmi@saturn.in>
 * @created on 28-Jul-2008
 *
 * All the components needs to be displayed at the time of 'Users' click in the menu bar is defined here for better readability.
 */

Application.Users = function () {
//
///////////////////////////////////////////////////////////////////////////
//-----------------------------------------------------------------------//
//-----------------------------Private Area------------------------------//
//-----------------------------------------------------------------------//
//---------------------------Private Variables---------------------------//
//Configure the Number of records can display in Grid at a time.
    var recs_per_page = 12;
    var user_window;
    var user_store;
    var changePassword_window;
    var uGrid;
    var redirect = "";
    var userID;
    var userLoadedForm;
    var item_count = 0;
    var img_statusOnline_path = IMAGE_BASE_PATH + "/default/icons/user_green.png";
    var img_statusOffline_path = IMAGE_BASE_PATH + "/default/icons/status_offline.png";
    var img_setPermission = IMAGE_BASE_PATH + "/default/icons/key.png";
    var img_editProfile = IMAGE_BASE_PATH + "/default/icons/page_white_edit.png";
    var modURL = '?module=user';
    var roleURL = '?module=role&op=role_combo';
    var checkpasswordURL = modURL + '&op=check-current_password';
    var userTimezoneStoreURL = modURL + "&op=getTZ";
    var totalComboCount;
    var myMask;
    
    var comboLoadComplete = function () {

        if (!Ext.isEmpty(userLoadedForm) && userLoadedForm != null) {
            userLoadedForm[0].loadRecord(Ext.decode(userLoadedForm[1]));
        }

        if (totalComboCount == 0) {
            myMask.hide();
            userLoadedForm = null;
        }

    };
    var deleteOP = "delete";
//reads a data object from an Ext.data.Connection object configured to reference a certain URL.
    var proxy = new Ext.data.HttpProxy({
        url: modURL,
        method: 'post'
    });
    // This store will hold the data for the designation_store options
    var designation_store = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            //where to retrieve data
            url: roleURL, //url to data object (server side script)
            method: 'POST'
        }),
        reader: new Ext.data.ArrayReader({}, [{
                name: 'id_admin_role'
            }, {
                name: 'admin_role_name'
            }])
    }); //end designation_store


// This store is used for Details combo
//loadDetailStore contains the data for Details combo
    var details_Store = new Ext.data.JsonStore({
        proxy: new Ext.data.HttpProxy({
            url: '?module=user&op=loadDetailStore',
            method: 'POST'
        }),
        fields: ['zon_ID', 'zon_Name'],
        autoLoad: false,
        id: 'name',
        remoteSort: true
    });
    //for selecting multi rows in a grid   
    function UserSelection() {
        return new Ext.grid.RowSelectionModel({
            multiSelect: true,
            loadMask: true,
            listeners: {
                //enable delete button based on selecting row     
                rowselect: function (sm, row, rec) {
                    if (Ext.getCmp("user_delete_button"))
                        Ext.getCmp("user_delete_button").enable();
                }

            }
        });
    }
    //----------------------------Private Methods----------------------------//
    // This store will hold the data for 'user_grid'
    var fn_user_store = function () {
        //user_store  = Ext.getCmp('uidnr_admin');
        if (Ext.isEmpty(user_store)) {
            user_store = new Ext.data.JsonStore({
                url: modURL,
                method: 'post',
                proxy: proxy,
                fields: ['rownum', 'uidnr_admin', 'admin_username', 'admin_role_name', 'admin_email', 'admin_active', 'isreport_to_user', 'admin_fullname', {
                        name: 'status_icon',
                        type: 'string'
                    }],
                totalProperty: 'totalCount',
                root: 'users',
                id: 'uidnr_admin',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    load: function () {
                        if (Ext.getCmp('userPanel'))
                            Ext.getCmp('userPanel').selModel.clearSelections();
                        if (Ext.getCmp("user_delete_button"))
                            Ext.getCmp('user_delete_button').disable();
                    }
                }
            });
        }
        user_store.setDefaultSort('admin_username', 'asc');
        return user_store;
    };
    //load user
    var loadUser = function () {
        //user_store = fn_user_store();
        user_store.removeAll();
        user_store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    };
    // Create actions on each row of the User Grid using RowActions Plugin
    //Actions Permission,Edit,Enable/Disable
    var actions = function () {
        var action = new Ext.ux.grid.RowActions({
            header: 'Actions',
            autoWidth: false,
            width: 35,
            hideMode: 'display',
            actions: [/* <?php if (user_access("access", "default")) { ?> */{
                    iconCls: 'my-iconkey',
                    tooltip: 'Set Permission',
                    text: ' ',
                    align: 'center',
                    callback: function (grid, record) {
                        Application.Access.init('user', record.data.uidnr_admin, record.data.admin_username);
                    }
                }, /* <?php } ?> */ /* <?php if (user_access("user", "save")) { ?> */ {
                    iconCls: 'my-icon24',
                    tooltip: 'Edit Profile',
                    text: ' ',
                    align: 'center',
                    callback: function (grid, record) {
                        Application.Users.loadWindow(record.data.uidnr_admin);
                        disablecompany_Store.load();
                        disablebranch_Store.load();
                    }
                }, /* <?php } ?> */ /* <?php if (user_access("user", "active")) { ?> */ {
                    iconCls: 'user_enabled',
                    iconIndex: 'status_icon',
                    tooltip: 'Enable/Disable User',
                    text: ' ',
                    callback: function (grid, record) {
                        Application.Users.change_status(record.data.admin_active, record.data.uidnr_admin, true);
                    }
                }, /* <?php } ?> */ /* <?php if (user_access("user", "active")) { ?> */ {
                    iconCls: 'user_disabled',
                    iconIndex: 'status_icon',
                    hide: true,
                    tooltip: 'Enable/Disable User',
                    text: ' ',
                    callback: function (grid, record) {
                        Application.Users.change_status(record.data.admin_active, record.data.uidnr_admin, true);
                    }
                }, /* <?php } ?> */ /* <?php if (user_access("access", "default")) { ?> */{
                    iconCls: 'my-icon56',
                    tooltip: 'Set Password',
                    text: ' ',
                    align: 'center',
                    callback: function (grid, record) {
                        //console.log(record.data.uidnr_admin +' \n '+record.data.admin_username );
                        setPassword(record.data.uidnr_admin, record.data.admin_username);
                    }
                } /* <?php } ?> */]
        });
        return action;
    };
    var userActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Set Permission",
                handler: function () {

                    var uidnr_admin = Ext.getCmp('userPanel').getSelectionModel().getSelections()[0].data.uidnr_admin;
                    Application.Access.init('user', uidnr_admin,'admin_username');
                }
            }, {
                text: "Edit Profile",
                handler: function () {

                    var uidnr_admin= Ext.getCmp('userPanel').getSelectionModel().getSelections()[0].data.uidnr_admin;
                    Application.Users.loadWindow(uidnr_admin);
                }
            },  {
                text: "Enable/Disable User",
                handler: function () {

                    var uidnr_admin= Ext.getCmp('userPanel').getSelectionModel().getSelections()[0].data.uidnr_admin;
                     Application.Users.change_status('admin_active', uidnr_admin, true);
                }
            }, {
                text: "Set Password",
                handler: function () {

                    var uidnr_admin= Ext.getCmp('userPanel').getSelectionModel().getSelections()[0].data.uidnr_admin;
                    setPassword(uidnr_admin, 'admin_username');
                }
            },{
                text: "Assign Brands",
                handler: function () {
                    var uidnr_admin= Ext.getCmp('userPanel').getSelectionModel().getSelections()[0].data.uidnr_admin;
                     Application.ProductBankMaster.assignBrands(uidnr_admin);
                }
            }]
    });
    //Function for Deleting record(s)

    function deleteRecord(btn) {

        if (btn == 'yes') {
            //returns record objects for selected rows (all info for row)
            uGrid = Ext.getCmp('userPanel');
            //returns array of selected rows ids only

            var selectedKeys = uGrid.selModel.selections.keys;
            var form_data = {
                uidnr_admin: selectedKeys
            };
            var params = {
                action: 'Update',
                module: 'user',
                op: deleteOP,
                extrainfo: 'asd',
                id: selectedKeys.toString()
            };
            APICall(params, Application.Users.DeleteUser, form_data);
        } else {
            grid_selection.clearSelections();
            Ext.getCmp("user_delete_button");
            Ext.getCmp("user_delete_button").disable();
        }
    }
    ; // end deleteRecord
    //Handler for Deleting record(s)

    function handleDelete() {

        //returns array of selected rows ids only
        uGrid = Ext.getCmp('userPanel');
        var selectedKeys = uGrid.selModel.selections.keys;
        if (selectedKeys.length > 0) {
            Ext.MessageBox.confirm('Confirm', CONFIRM_SELECT_DELETE, deleteRecord);
        }
    }
    ; // end handleDelete

//for pagination  
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
//create a grid
    var createGrid = function () {

        user_store = fn_user_store();
        var action = actions();
        //var filters = Plugin used for the  User Grid
        var filters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'admin_username'
                },{
                    type: 'string',
                    dataIndex: 'admin_fullname'
                },{
                    type: 'string',
                    dataIndex: 'admin_role_name'
                },{
                    type: 'string',
                    dataIndex: 'admin_active'
                }, {
                    type: 'string',
                    dataIndex: 'admin_email'
                }]
        });
        filters.remote = true;
        filters.autoReload = true;
        var grid_selection = UserSelection();
        //create a grid to display Login Name,Role,Email Id     
        var user_grid = new Ext.grid.GridPanel({
            store: user_store,
            id: 'userPanel',
            title: 'Users',
            //iconCls: 'icon_users',
            plugins: [action, filters],
            loadMask: true,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            border: false,
            layout: 'fit',
            columns: [{
                    header: "",
                    sortable: false,
                    width: 8,
                    align: 'right',
                    dataIndex: 'rownum'
                }, {
                    id: 'user_name',
                    header: "Login Name",
                    sortable: true,
                    dataIndex: 'admin_username'
                }, {
                    id: 'full_name',
                    header: "User Name",
                    width: 90,
                    sortable: true,
                    dataIndex: 'admin_fullname'
                }, {
                    id: 'user_role',
                    header: "Role",
                    width: 90,
                    sortable: true,
                    dataIndex: 'admin_role_name'
                }, {
                    id: 'user_stat',
                    header: "Active User",
                    width: 90,
                    sortable: true,
                    dataIndex: 'admin_active'
                },
                /*{
                 id: 'user_emailId',
                 header: "Email Id",
                 width: 120,
                 sortable: true,
                 dataIndex: 'admin_email'
                 },*/
                 // row_actions
                  {
                            xtype: 'actioncolumn',
                            header: 'Action',
                            hideable: true,
                            iconCls: 'downarrow',
                            tooltip: 'Choose Actions',
                            listeners: {
                                click: function (a, grid, rowindex, e) {
                                    var record = grid.store.getAt(rowindex);
                                    grid.getSelectionModel().selectRow(rowindex);
                                    userActionMenu.showAt(e.getXY());
                                    //action
                                }
                            }
                        }
                 ],
            sm: grid_selection,
            listeners: {
                dblclick: function () {
                    if (uGrid.selModel.selections.length != 0) {
                        var userID = uGrid.selModel.getSelected().data.uidnr_admin;
                        Application.Users.loadWindow(userID);
                        grid_selection.clearSelections();
                    }
                },
                viewready: function (grid) {
                    updatePagination(grid)
                }
            },
            tbar: [{
                    text: 'Create User',
                    tooltip: 'Create User',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    /* <?php if (!user_access("user", "save")) { ?> */
                    hidden: true,
                    /* <?php } ?> */
                    handler: function () {
                        //Application.Users.editUser();
                        Application.Users.loadWindow();
                        disablecompany_Store.load();
                        disablebranch_Store.load();
                    }
                }, ' ', '-', ' ', {
                    id: 'user_delete_button',
                    text: 'Delete',
                    tooltip: 'Delete Selected Member',
                    icon: './resources/images/default/icons/delete.png',
                    iconCls: 'my-icon1',
                    /* <?php if (!user_access("user", "delete")) { ?> */
                    hidden: true,
                    /* <?php } ?> */
                    disabled: true,
                    handler: handleDelete
                }
                ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: user_store,
                displayInfo: true,
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'user_name'
        });
        //Ext.util.Observable.capture(user_grid, function(evname) {console.log(evname, arguments);})
        return user_grid;
    };
    var disablecompany_Store = new Ext.data.JsonStore({
        proxy: new Ext.data.HttpProxy({
            url: '?module=user&op=loaddisablecompanyStore',
            method: 'POST'
        }),
        fields: ['dcid', 'dcname'],
        autoLoad: false,
        // id: 'name',
        remoteSort: true
    });
    var disablebranch_Store = new Ext.data.JsonStore({
        proxy: new Ext.data.HttpProxy({
            url: '?module=user&op=loaddisablebranchStore',
            method: 'POST'
        }),
        fields: ['dbid', 'dbname'],
        autoLoad: false,
        // id: 'name',
        remoteSort: true
    });
    var setPasswordForm = function (user_id, user_name) {
        var pswdForm = new Ext.FormPanel({
            labelWidth: 105,
            id: 'setPassword_Form',
            url: "?module=user&op=setUserPassword",
            frame: true,
            border: true,
            width: 360,
            labelAlign: 'top',
            autoHeight: true,
            defaults: {
                width: 200
            },
            defaultType: 'textfield',
            items: [{
                    xtype: 'hidden',
                    id: 'uid',
                    name: 'user_id',
                    value: user_id
                }, {
                    xtype: 'hidden',
                    id: 'uname',
                    name: 'user_name',
                    value: user_name
                },
                new Ext.ux.PasswordField({
                    fieldLabel: 'New Password',
                    inputType: 'password',
                    showCapsWarning: true,
                    showStrengthMeter: true,
                    minLength: 4,
                    tabIndex: '2',
                    id: 'newPassword',
                    allowBlank: false,
                    strength: 0,
                    listeners: {
                        change: function (pwfield, newValue, oldValue) {
                            pwfield.strength = pwfield.calcStrength(newValue);
                        }
                    }
                }), {
                    fieldLabel: 'Confirm Password',
                    inputType: 'password',
                    id: 'confirmPassword',
                    tabIndex: '3',
                    vtype: 'password',
                    initialPassField: 'newPassword',
                    allowBlank: false
                }]

        });
        return pswdForm;
    };

    var comboConfStore = function (ind, id) {
        var store = new Ext.data.ArrayStore({
            url: modURL + '&op=getComboData&ind=' + ind,
            method: 'POST',
            autoLoad: false,
            fields: ['id', 'name'],
            listeners: {
                beforeload: function () {
                    if (ind == 2) {
                        store.baseParams.comp_id = Ext.getCmp('reporting_company').getValue();
                    }
                    if (ind == 4) {
                        store.baseParams.comp_id = Ext.getCmp('active_company').getValue();
                    }
                    if (ind == 6) {
                        store.baseParams.comp_id = Ext.getCmp('auditor_company').getValue();
                    }
                    if (ind == 7) {
                        var user_type = Ext.getCmp('user_type').getValue();
                        store.baseParams = {
                            user_id: Ext.getCmp('uid').getValue(),
                            user_type: user_type
                        }



                    }
                },
                load: function () {
                    if (!Ext.isEmpty(Ext.getCmp(id))) {
                        Ext.getCmp(id).setValue(Ext.getCmp(id).getValue());
                    }
                }
            }
        });
        return store;
    };
    var comboConf = function (ind, label, id, type) {

        /* var anchor = (ind === 7) ? '50%' : '99%';*/

        var allow_blank = (ind <= 7 && ind > 2) ? true : false;
        var xtype = (type === 'single') ? 'combo' : 'lovcombo';
        var editable = (ind <= 7 && ind > 2) ? true : false;
        return {
            xtype: xtype,
            store: comboConfStore(ind, id),
            mode: 'remote',
            id: id,
            fieldLabel: label,
            hiddenName: 'cmp_br[' + id + ']',
            /* name: 'cmp_br[' + id + ']',*/
            displayField: 'name',
            valueField: 'id',
            editable: editable,
            anchor: '99%',
            allowBlank: allow_blank,
            typeAhead: true,
            selectOnFocus: true,
            triggerAction: 'all',
            lazyRender: true,
            listeners: {
                beforequery: function (qe) {
                    /*  if (ind == 2 || ind == 4 || ind == 6)
                     delete qe.combo.lastQuery;*/
                },
                select: function () {
                    userLoadedForm = null;
                    var next_combo;
                    var next_combo_str;
                    if (ind == 1) {
                        next_combo = Ext.getCmp('reporting_branch');
                    }
                    if (ind == 3) {
                        next_combo = Ext.getCmp('active_branch');
                    }

                    if (!Ext.isEmpty(next_combo)) {
                        next_combo_str = next_combo.getStore();
                        next_combo.reset();
                        next_combo_str.removeAll();
                        next_combo_str.load();
                        var value = this.getValue();
                        //console.log(value);
                        var rvalue = this.getRawValue();
                    }
                }
            }
        };
    };


    //create form to Add/Edit member
    var loadForm = function (value) {
        var userID = arguments[1];
        var form = new Ext.FormPanel({
            id: 'user_form',
            labelAlign: 'top',
            autoHeight: true,
            url: "?module=user&op=save",
            frame: true,
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: 0.5,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'First Name',
                                    labelStyle: mandatory_label,
                                    id: 'txtFName',
                                    allowBlank: false,
                                    name: 'profile[admin_fname]',
                                    anchor: '95%',
                                    listeners:{
                                       afterrender:function(field){
                                       Ext.defer(function(){
                                       field.focus(true,100);
                                       },1);
                                     }
                                   }
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Last Name',
                                    labelStyle: mandatory_label,
                                    id: 'txtLName',
                                    allowBlank: false,
                                    name: 'profile[admin_lname]',
                                    anchor: '95%'
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Email',
                                    name: 'user[admin_email]',
                                    allowBlank: false,
                                    vtype: 'email',
                                    anchor: '95%',
                                    id: 'admin_email',
                                    labelStyle: mandatory_label,
                                    listeners: {
                                        change: function () {
                                            var mails = this.getValue();
                                            Ext.getCmp('admin_username').setValue(mails);
                                        }
                                    }
                                },
                                new Ext.ux.PasswordField({
                                    fieldLabel: 'Password',
                                    id: 'txtPassword',
                                    name: 'user[admin_password]',
                                    anchor: '95%',
                                    minLength: 4,
                                    allowBlank: value,
                                    labelStyle: mandatory_label,
                                    showStrengthMeter: true


                                }),
                                /*{
                                 xtype: 'textfield',
                                 inputType: 'password',
                                 fieldLabel: 'Password',
                                 labelStyle: mandatory_label,
                                 id: 'txtPassword',
                                 allowBlank: value,
                                 //blankText:'password field is mandatory',
                                 minLength: 4,
                                 minLengthText: 'The minimum length for this field is 4',
                                 
                                 name: 'user[admin_password]',
                                 anchor: '95%', */
                                //},
                                {
                                    xtype: 'hidden',
                                    id: 'uid',
                                    name: 'user[uidnr_admin]'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.5,
                            items: [new Ext.form.ComboBox(
                                        {typeAhead: true,
                                            id: 'designation',
                                            editable: true,
                                            fieldLabel: 'Role',
                                            labelStyle: mandatory_label,
                                            allowBlank: false,
                                            anchor: '98%',
                                            triggerAction: 'all',
                                            emptyText: 'Select a role',
                                            lazyRender: true,
                                            store: designation_store,
                                            displayField: 'admin_role_name',
                                            valueField: 'id_admin_role',
                                            hiddenName: 'user[id_admin_role]',
                                            listClass: 'x-combo-list-small',
                                            mode: 'local'
                                        }),
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Mobile',
                                    labelStyle: mandatory_label,
                                    vtype: 'phonespec',
                                    allowBlank: false,
                                    name: 'profile[admin_telephone]',
                                    minLength: 10,
                                    minLengthText: 'The minimum length for this field is 10',
                                    maxLength: 10,
                                    //maxLengthText: 'The minimum length for this field is 10',

                                    anchor: '98%'
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Login Name',
                                    id: 'admin_username',
                                    name: 'user[admin_username]',
                                    readOnly: true,
                                    minLength: 4,
                                    anchor: '98%',
                                    labelStyle: mandatory_label
                                },
                                {
                                    xtype: 'combo',
                                    displayField: 'name',
                                    valueField: 'id',
                                    mode: 'local',
                                    fieldLabel: 'Type',
                                    editable: true,
                                    labelStyle: mandatory_label,
                                    emptyText: 'Select ...',
                                    hiddenName: 'profile[user_type]',
                                    id: 'user_type',
                                    allowBlank: true,
                                    anchor: '98%',
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    store: new Ext.data.JsonStore({
                                        fields: ['id', 'name'],
                                        data: [{id: '1', name: 'Admin'},
                                            {id: '2', name: 'Corporate'},
                                            //{id: '3', name: 'Branch'},
                                            //{id: '4', name: 'Auditor'}
                                        ]
                                    }),
                                    listeners: {
                                        select: function (cbo) {
                                            var val = cbo.getValue();
                                            var auditor_company = Ext.getCmp('auditor_company');
                                            var auditor_branch = Ext.getCmp('auditor_branch');
                                            auditor_branch.allowBlank = true;
                                            auditor_company.allowBlank = true;
                                            auditor_branch.reset();
                                            auditor_company.reset();
                                            var auditor_panel = Ext.getCmp('auditor_panel');
                                            var active_company = Ext.getCmp('active_company');
                                            var active_branch = Ext.getCmp('active_branch');
                                            var active_panel = Ext.getCmp('active_panel');
                                            active_company.allowBlank = true;
                                            active_branch.allowBlank = true;
                                            active_company.reset();
                                            active_branch.reset();
                                            var autoapproval = Ext.getCmp('e_autoapproval');
                                            var reporting_company = Ext.getCmp('reporting_company');
                                            var reporting_branch = Ext.getCmp('reporting_branch');
                                            var reporting_panel = Ext.getCmp('reporting_panel');
                                            reporting_company.allowBlank = true;
                                            reporting_branch.allowBlank = true;
                                            reporting_company.reset();
                                            reporting_branch.reset();
                                            //reporting_panel.hide();
                                            //reporting_panel.doLayout();
                                            if (val == 1) {/*Admin - access all company and branch*/

                                                auditor_panel.hide();
                                                active_panel.hide();
                                                reporting_company.hide();
                                                reporting_branch.hide();
                                                //autoapproval.show();
                                            } else if (val == 2) {/*Corporate - access to selected companies and all branches in that*/

                                                auditor_panel.hide();
                                                active_panel.show();
                                                active_company.show();
                                                active_company.allowBlank = false;
                                                active_branch.hide();
                                                active_panel.doLayout();
                                                reporting_company.hide();
                                                reporting_branch.hide();
                                                //autoapproval.show();
                                            } else if (val == 3) {/*Branch - access to selected and selected branches*/

                                                auditor_panel.hide();
                                                active_panel.show();
                                                active_panel.doLayout();
                                                active_branch.show();
                                                active_company.show();
                                                active_company.allowBlank = false;
                                                active_branch.allowBlank = false;
                                                reporting_company.hide();
                                                reporting_branch.hide();
                                                //autoapproval.show();
                                            } else if (val == 4) {/*Auditor - auditor of the current auditing company*/
                                                auditor_panel.show();
                                                auditor_panel.doLayout();
                                                auditor_company.show();
                                                auditor_company.allowBlank = false;
                                                auditor_branch.allowBlank = false;
                                                active_panel.hide();
                                                reporting_company.show();
                                                reporting_company.allowBlank = false;
                                                reporting_branch.hide();
                                                //autoapproval.hide();
                                                var reporting_company_store = reporting_company.getStore();
                                                reporting_company_store.reload({
                                                    callback: function () {
                                                        if (reporting_company_store.getCount() == 1) {
                                                            var rec = reporting_company_store.getAt(0);
                                                            reporting_company.setValue(rec.get('id'));
                                                            reporting_company.editable = false;
                                                            reporting_company.setReadOnly(true);
                                                        }
                                                    }
                                                });
                                            } else {

                                            }


                                        }
                                    }
                                }]
                        }]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.50,
                            items: [{
                                    xtype: 'checkbox',
                                    id: 'e_autoapproval',
                                    name: 'profile[e_autoapproval]',
                                    tabIndex: 1024,
                                    boxLabel: 'Enable Auto Approval',
                                    hidden:true,
                                    //inputValue: 1,
                                    //uncheckedValue: 0,
                                    listeners: {
                                        check: function (checkbox, checked) {

                                            if (checked == true) {

                                                Ext.getCmp('autoapproval').setValue('1');
                                            } else {
                                                Ext.getCmp('autoapproval').setValue('0');
                                            }
                                        }
                                    }
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'autoapproval',
                                    name: 'profile[enable_autoapproval]',
                                    value: 0
                                }]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.50,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'checkbox',
                                    id: 'e_approver',
                                    hidden:true,
                                    name: 'profile[e_approver]',
                                    tabIndex: 1024,
                                    boxLabel: 'Approver',
                                    //inputValue: 1,
                                    //uncheckedValue: 0,
                                    listeners: {
                                        check: function (checkbox, checked) {

                                            if (checked == true) {

                                                Ext.getCmp('isanapprover').setValue('1');
                                            } else {
                                                Ext.getCmp('isanapprover').setValue('0');
                                            }
                                        }
                                    }
                                },
                                {
                                    xtype: 'hidden',
                                    id: 'isanapprover',
                                    name: 'profile[isanapprover]',
                                    value: 0
                                }]

                        }]
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'Address',
                    labelStyle: mandatory_label,
                    allowBlank: false,
                    maxLength: 256,
                    name: 'profile[admin_address]',
                    height: 50,
                    anchor: '99%'
                }, {
                    xtype: 'fieldset',
                    title: "Reporting",
                    hidden:true,
                    layout: "column",
                    id: 'reporting_panel',
                    defaults: {
                        xtype: "panel",
                        layout: 'form',
                        width: "50%"
                    },
                    items: [{
                            items: [comboConf(1, 'Company', 'reporting_company', 'single'),
                                comboConf(2, 'Branch', 'reporting_branch', 'single')]
                        }, {
                            items: comboConf(7, 'To', 'report_to_user', 'single')
                        }]
                }, {
                    xtype: 'fieldset',
                    title: 'Active In',
                    layout: "column",
                    id: 'active_panel',
                    hidden: true,
                    defaults: {
                        xtype: "panel",
                        layout: 'form',
                        width: "50%"
                    },
                    items: [{
                            items: comboConf(3, 'Company', 'active_company')
                        }, {
                            items: comboConf(4, 'Branch', 'active_branch')
                        }]
                }, {
                    xtype: 'fieldset',
                    title: 'Auditor of',
                    id: 'auditor_panel',
                    hidden: true,
                    layout: "column",
                    defaults: {
                        xtype: "panel",
                        layout: 'form',
                        width: "50%"
                    },
                    items: [{
                            items: comboConf(5, 'Company', 'auditor_company')
                        }, , {
                            items: comboConf(6, 'Branch', 'auditor_branch')
                        }]
                }],
            keys: [{
                    key: Ext.EventObject.ENTER,
                    fn: function () {
                        //submit to server  
                        Ext.getCmp('user_form').getForm().submit({
                            waitTitle: 'Please Wait!',
                            waitMsg: 'Saving data...',
                            success: function (userForm, action) {
                                eval('var tmp=' + action.response.responseText);
                                if (tmp.success == true) {

                                    Ext.getCmp('userPanel').store.reload();
                                    Ext.getCmp('user_window').close();
                                }

                            }
                        });
                    }
                }]
        });
        return form;
    };
    Ext.apply(Ext.form.VTypes, {
        password: function (val, field) {
            if (field.initialPassField) {
                var pwd = Ext.getCmp(field.initialPassField);
                return (val == pwd.getValue());
            }
            return true;
        },
        //validating password
        check_validation: function (val, field) {
            //check_space = /^\s+|\s$/;
            var new_val = Ext.util.Format.trim(val);
            //var reg_exp=/\w+|\s+/;
            var reg_exp = /^\w+$|^\w+\s+\w+$/;
            if (!val.match(reg_exp)) {
                return false;
            } else {
                return true;
            }
        },
        passwordText: 'New and Confirm do not match'
    });
    // Create a form to allow users to change password

    var changePasswordForm = function () {
        var pswdForm = new Ext.FormPanel({
            labelWidth: 105,
            id: 'changePassword_Form',
            url: "?module=user&op=changePassword",
            frame: true,
            border: true,
            width: 360,
            autoHeight: true,
            defaults: {
                width: 200
            },
            defaultType: 'textfield',
            items: [

                {
                    fieldLabel: 'Current Password',
                    //vtype: 'alphanum',
                    inputType: 'password',
                    allowBlank: false,
                    tabIndex: '1',
                    id: 'current_password',
                    plugins: [Ext.ux.plugins.RemoteValidator],
                    rvOptions: {
                        url: checkpasswordURL,
                        callback: function (option, success, response) {
                            eval('var tmp=' + response.responseText);
                            if (tmp.valid == true) {
                                Ext.getCmp('newPassword').enable();
                                Ext.getCmp('newPassword').focus();
                                Ext.getCmp('confirmPassword').enable();
                            } else {
                                Ext.getCmp('newPassword').reset();
                                Ext.getCmp('confirmPassword').reset();
                                Ext.getCmp('newPassword').disable();
                                Ext.getCmp('confirmPassword').disable();
                            }
                        }
                    },
                    id: 'currentPassword'
                },

                new Ext.ux.PasswordField({
                    fieldLabel: 'New Password',
                    id: 'newPassword',
                    disabled: true,
                    minLength: 4,
                    tabIndex: '2',
                    allowBlank: false,
                    showStrengthMeter: true,
                    showCapsWarning: true,
                    strength: 0,
                    listeners: {
                        change: function (pwfield, newValue, oldValue) {
                            pwfield.strength = pwfield.calcStrength(newValue);
                        }
                    }
                }),

                /*  {
                 fieldLabel: 'New Password',
                 inputType: 'password',
                 showCapsWarning: true,
                 showStrengthMeter: true,
                 minLength: 4,
                 tabIndex: '2',
                 id: 'newPassword',
                 allowBlank: false,
                 disabled: true
                 
                 },*/ {
                    fieldLabel: 'Confirm Password',
                    inputType: 'password',
                    id: 'confirmPassword',
                    tabIndex: '3',
                    vtype: 'password',
                    initialPassField: 'newPassword',
                    allowBlank: false,
                    disabled: true
                }]

        });
        return pswdForm;
    };
    //log out
    var logout_function = function (btn) {
        if (btn != 'no') {
            if (Ext.isDefined(Application.checkoutList) && Application.checkoutList.length > 0) {
                if (!confirm('Are you sure you want to navigate away from this page?\n\nFollowing jobs are not checked in:' + Application.checkoutList.join(', ') + '\n\nPress  OK to continue, or Cancel to stay on the current page.'))
                    return;
                else
                    Application.checkoutList = [];
            }
            Ext.Msg.show({
                title: 'Please Wait!',
                progressText: 'Logging off...',
                width: 300,
                progress: true,
                closable: false,
                wait: true
            });
            Ext.Ajax.request({
                url: modURL,
                params: {
                    'op': 'logout'
                },
                failure: function (response, options) {
                    Ext.Msg.hide();
                },
                success: function (response, options) {
                    Ext.Msg.hide();
                    window.location = redirect;
                }
            });
        }
    };
    var setPassword = function (user_id, user_name) {
        var changePswd_id = "setPassword_window";
        //create the window on the first click and reuse on subsequent clicks
        var setPassword_window = Ext.getCmp(changePswd_id);
        if (Ext.isEmpty(setPassword_window)) {
            var form = setPasswordForm(user_id, user_name);
            setPassword_window = new Ext.Window({
                id: 'setPassword_window',
                layout: 'fit',
                width: 370,
                title: 'Set Password',
                autoHeight: true,
                iconCls: 'my-icon53',
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [form],
                buttons: [{
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            setPassword_window.close();
                        }
                    },{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        id: 'btnSetPassword',
                        handler: function () {
                            var newPassword = Ext.getCmp('newPassword');
                            var passwordStrength = newPassword.strength;
                            if (passwordStrength < MINIMUM_PASSWORD_STRENGTH) {
                                Ext.Msg.alert('ERROR', "Your password is too weak, Kindly include Symbols ( e.g. @#$% ) and Numbers ( e.g. 123456 ) to make your password strong");
                                return;
                            }

                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            if (form.getForm().isValid()) {
                                form.getForm().submit({
                                    params: {
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp,
                                        profile_update: 0
                                    },
                                    waitMsg: 'Submitting your data...',
                                    success: function (form, action) {
                                        // server responded with success = true
                                        eval('var tmp=' + action.response.responseText);
                                        if (tmp.success !== undefined && tmp.success === true) {
                                            Application.example.msg("Notification", "Password has been changed successfully.");
                                            setPassword_window.close();
                                        }
                                    },
                                    failure: function (form, action) {

                                    }
                                });
                            }

                        }

                    }]
            });
        }
        setPassword_window.doLayout();
        setPassword_window.show(this);
        setPassword_window.center();
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
        loadWindow: function (id) {
            var window_title = 'Create User';
            //Preload Data in the form for Edit User functionaity
            userID = arguments[0];
            var win_id = "user_window";
            //create the window on the first click and reuse on subsequent clicks
            var user_window = Ext.getCmp(win_id);
            if (Ext.isEmpty(user_window)) {
                var uForm;
                if (Ext.isEmpty(userID))
                    uForm = loadForm(false);
                else
                    uForm = loadForm(true, userID);
                user_window = new Ext.Window({
                    id: win_id,
                    layout: 'fit',
                    width: 600,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    //iconCls: 'my-icon9',
                    resizable: false,
                    shadow: false,
                    floating: true,
                    items: [uForm],
                    buttons: [
                           {
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                user_window.close();
                            }
                        },

                    {
                            text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                /*var activeCmp = Ext.getCmp('active_company').getValue();
                                 //activeCmp.toString();
                                 //var activeCmpArray = [];                                
                                 console.log( activeCmp);
                                 activeCmpArray = activeCmp.split(',');
                                 console.log(activeCmpArray);
                                 Ext.each(activeCmpArray, function (key,value)
                                 {
                                 console.log(value);
                                 var index=disablecompany_Store.find('dcid',value);
                                 
                                 
                                 console.log('index'+index);
                                 
                                 });
                                 return;*/
                                Ext.Ajax.request({
                                    waitMsg: 'Checking Username',
                                    url: modURL,
                                    params: {
                                        op: 'check_username_duplication',
                                        'id': userID
                                    },
                                    failure: function (response, options) {

                                    },
                                    success: function (response, options) {

                                        eval("var tmp=" + response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            if (uForm.getForm().isValid()) {

                                                var form_data = uForm.getForm().getValues();
                                                var params = {
                                                    action: 'Insert',
                                                    module: 'user',
                                                    op: 'save',
                                                    id: '0',
                                                    extrainfo: 'asd'
                                                };
                                                if (userID > 0) {
                                                    params.action = 'Update';
                                                    params.id = userID;
                                                }
                                                APICall(params, Application.Users.saveUserData, form_data);
                                            } else {
                                                Ext.MessageBox.alert("Notification", "Please enter all required fields");
                                            }
                                        } else {
                                            Ext.getCmp('admin_username').markInvalid("Server not yet validated the username");
                                        }
                                    }
                                });
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            //for load the details of selected user   

                            designation_store.load();
                            if (!Ext.isEmpty(userID)) {
                                myMask = new Ext.LoadMask(Ext.getCmp('user_window').getEl(), {msg: "Please wait..."});
                                myMask.show();
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                uForm.load({
                                    url: modURL,
                                    params: {
                                        'op': 'getDetails',
                                        'id': userID,
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    success: function (form, action) {

                                        eval('var tmp=' + action.response.responseText);
                                        userLoadedForm = [form, action.response.responseText];
                                        var tmp = Ext.decode(action.response.responseText);
                                        var isreport_to_user = tmp.data.isreport_to_user;
                                        if (isreport_to_user == 'true') {
                                            Ext.getCmp('user_type').setReadOnly(true);
                                        }

                                        user_window.setTitle("Edit User: " + Ext.getCmp("admin_username").getValue());
                                        user_window.setIconClass('my-icon24');
                                        //PasswordFieldVisibility = false;
                                        Ext.getCmp('txtPassword').hide();
                                        var myCombo = Ext.getCmp('user_type');
                                        myCombo.fireEvent('select', myCombo);
                                        totalComboCount = 8;
                                        //var c =Ext.getCmp('user_type').getValue(); 
                                        //console.log(c)
                                        //Load Designations List
                                        designation_store.load({
                                            callback: function () {
                                                totalComboCount--;
                                                comboLoadComplete();
                                            }
                                        });
                                        Ext.getCmp('active_company').getStore().load({
                                            callback: function () {

                                                // var cmp = tmp.data.cmp_br[active_company];

                                                // Ext.getCmp('active_company').setValue(cmp.toString());

                                                totalComboCount--;
                                                comboLoadComplete();
                                                Ext.getCmp('active_branch').getStore().load({
                                                    params: {
                                                        comp_id: Ext.getCmp('active_company').getValue()
                                                    },
                                                    callback: function () {
                                                        totalComboCount--;
                                                        comboLoadComplete();
                                                    }
                                                });
                                            }
                                        });
                                        Ext.getCmp('reporting_company').getStore().load({
                                            callback: function () {
                                                totalComboCount--;
                                                comboLoadComplete();
                                                Ext.getCmp('reporting_branch').getStore().load({
                                                    params: {
                                                        comp_id: Ext.getCmp('reporting_company').getValue()
                                                    },
                                                    callback: function () {
                                                        totalComboCount--;
                                                        comboLoadComplete();
                                                    }
                                                });
                                            }
                                        });
                                        Ext.getCmp('auditor_company').getStore().load({
                                            callback: function () {
                                                totalComboCount--;
                                                comboLoadComplete();
                                            }
                                        });
                                        Ext.getCmp('report_to_user').getStore().load({
                                            callback: function () {
                                                totalComboCount--;
                                                comboLoadComplete();
                                            }
                                        });
                                        Ext.getCmp('auditor_branch').getStore().load({
                                            callback: function () {
                                                totalComboCount--;
                                                comboLoadComplete();
                                            }
                                        });
                                        myMask.hide();
                                    },
                                    failure: function () {
                                        myMask.hide();
                                    }
                                });
                            } else {
                                user_window.setTitle(window_title);
                                user_window.setIconClass('my-icon9');
                            }
                        }
                    }
                });
            }



            user_window.doLayout();
            user_window.show(this);
            user_window.center();
        },
        init: function () {
            uGrid = Ext.getCmp("userPanel");
            if (Ext.isEmpty(uGrid)) {
                uGrid = createGrid();
            }
            Application.UI.addTab(uGrid);
            uGrid.doLayout();
            loadUser();
        },
        //change password
        changePassword: function () {
            var changePswd_id = "changePassword_window";
            //create the window on the first click and reuse on subsequent clicks
            var changePassword_window = Ext.getCmp(changePswd_id);
            if (Ext.isEmpty(changePassword_window)) {
                var form1 = changePasswordForm();
                changePassword_window = new Ext.Window({
                    id: 'changePassword_window',
                    layout: 'fit',
                    width: 370,
                    title: 'Change Password',
                    autoHeight: true,
                    //iconCls: 'my-icon53',
                    plain: true,
                    constrain: true,
                    modal: true,
                    resizable: false,
                    items: [form1],
                    buttons: [{
                            text: 'Cancel',
                            id: 'btnCancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                changePassword_window.close();
                            }
                        },{
                            text: 'Change Password',
                            icon: IMAGE_BASE_PATH + '/default/icons/lock_edit.png',
                            iconCls: 'my-icon1',
                            id: 'btnChangePassword',
                            handler: function () {
                                var newPassword = Ext.getCmp('newPassword');
                                var passwordStrength = newPassword.strength;
                                console.log(passwordStrength);
                                if (passwordStrength < MINIMUM_PASSWORD_STRENGTH) {
                                    Ext.Msg.alert('ERROR', "Your password is too weak, Kindly include Symbols ( e.g. @#$% ) and Numbers ( e.g. 123456 ) to make your password strong");
                                    return;
                                }

                                var form_data = form1.getForm().getValues();
                                var params = {
                                    action: 'Update',
                                    module: 'user',
                                    op: 'changePassword',
                                    id: _SESSION.UserId,
                                    extrainfo: 'asd'
                                };
                                APICall(params, Application.Users.ChangeUserPassword, form_data);

                            }

                        }]
                });
            }
            changePassword_window.doLayout();
            changePassword_window.show(this);
            changePassword_window.center();
        },
        logout: function () {
            var show_confirm = arguments[0];
            if (show_confirm != false) {
                Ext.Msg.show({
                    title: 'Confirm',
                    msg: WISH_LOGOUT,
                    buttons: Ext.MessageBox.YESNO,
                    fn: logout_function
                });
            } else {
                logout_function();
            }
        },
        //chinging status
        change_status: function () {
            Application.Users.user_active = arguments[0];
            Application.Users.uidnr_admin = arguments[1];
            var form_data = {
                user_active: Application.Users.user_active,
                uidnr_admin: Application.Users.uidnr_admin
            };
            var params = {
                action: 'Update',
                module: 'user',
                op: 'active',
                extrainfo: 'asd',
                id: Application.Users.uidnr_admin
            };
            APICall(params, Application.Users.StatusChange, form_data);
        },
        updateProfile: function (prof) {
            var window_title = 'Update Profile';
            var window_id = "user_edit_window";
            var userID = _SESSION.UserId;
            //create the window on the first click and reuse on subsequent clicks
            var user_edit_window = Ext.getCmp(window_id);
            if (Ext.isEmpty(user_edit_window)) {
                userID = _SESSION.UserId;
                var uForm = loadForm(true, _SESSION.UserId, true); //Third argument added to disable site panel

                user_edit_window = new Ext.Window({
                    id: window_id,
                    layout: 'fit',
                    width: 600,
                    autoHeight: true,
                    //iconCls: (prof) ? 'edit_profile' : 'my-icon24',
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: [uForm],
                    shadow: false,
                    buttons: [{text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                if (uForm.getForm().isValid()) {
                                    uForm.getForm().submit({
                                        params: {
                                            apikey: _SESSION.apikey,
                                            tstamp: t_stamp,
                                            profile_update: 1
                                        },
                                        waitTitle: 'Please Wait!',
                                        waitMsg: 'Saving data...',
                                        success: function (uForm, action) {
                                            //alert(action.response.responseText);
                                            eval('var tmp=' + action.response.responseText);
                                            if (tmp.success !== undefined && tmp.success === true) {
                                                user_edit_window.close();
                                                Ext.MessageBox.alert("Success", "Profile has been updated successfully!..");
                                                loadUser();
                                                if (!Ext.isEmpty(Ext.getCmp('userPanel'))) {
                                                    grid_selection.clearSelections();
                                                }
                                                if (Ext.getCmp('txtFName'))
                                                    _SESSION.FirstName = Ext.getCmp('txtFName').getValue();
                                                if (Ext.getCmp('txtLName'))
                                                    _SESSION.LastName = Ext.getCmp('txtLName').getValue();
                                            }
                                        },
                                        failure: function (uForm, action) {
                                            if (action.failureType == 'server') {
                                                obj = Ext.util.JSON.decode(action.response.responseText);
                                                Ext.MessageBox.show({
                                                    title: 'Error!',
                                                    msg: (obj.error) ? obj.error : obj.errors.reason,
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.ERROR,
                                                    width: 325
                                                });
                                            } else {
                                                Ext.MessageBox.show({
                                                    title: 'Error!',
                                                    msg: 'Error occured while saving data.'/*MAN_FIELD*/,
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.ERROR,
                                                    width: 325
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.show({
                                        title: 'Error!',
                                        msg: MAN_FIELD,
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.ERROR,
                                        width: 325
                                    });
                                }
                                return;
                            }
                        }, {
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                user_edit_window.close();
                            }
                        }],
                    listeners: {
                        afterrender: function () {
//                            Ext.getCmp('reporting_company').allowBlank = true;
//                            Ext.getCmp('reporting_branch').allowBlank = true;
//
//                            Ext.getCmp('reporting_panel').hide();
//                            Ext.getCmp('active_panel').hide();
//                            Ext.getCmp('auditor_panel').hide();
                            myMask = new Ext.LoadMask(Ext.getCmp('user_edit_window').getEl(), {msg: "Please wait..."});
                            myMask.show();
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            uForm.load({
                                url: modURL,
                                params: {
                                    'op': 'getUserProfile',
                                    'id': userID,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                },
                                success: function (frm, action) {
                                    var tmp = Ext.util.JSON.decode(action.response.responseText);
                                    userLoadedForm = [frm, action.response.responseText];
                                    if (!Ext.isEmpty(prof))
                                        user_edit_window.setTitle("Update My Profile");
                                    else
                                        user_edit_window.setTitle("Update User: " + Ext.getCmp("admin_username").getValue());
                                    var myCombo = Ext.getCmp('user_type');
                                    myCombo.fireEvent('select', myCombo);
                                    totalComboCount = 1;
                                    //Load Designations List
                                    designation_store.load({
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });
                                    Ext.getCmp('user_type').store.load();
                                    // console.log('here ' + Ext.getCmp('active_company').getValue());

                                    Ext.getCmp('active_company').getStore().load({
                                        callback: function () {

                                            totalComboCount--;
                                            comboLoadComplete();
                                            // console.log(Ext.getCmp('active_company').getValue());
                                            // Ext.getCmp('active_company').setValue(Ext.getCmp('active_company').getValue());

                                            Ext.getCmp('active_branch').getStore().load({
                                                params: {
                                                    comp_id: Ext.getCmp('active_company').getValue()
                                                },
                                                callback: function () {
                                                    totalComboCount--;
                                                    comboLoadComplete();
                                                }
                                            });
                                        }
                                    });
                                    /*Ext.getCmp('reporting_company').getStore().load({
                                     callback: function () {
                                     totalComboCount--;
                                     comboLoadComplete();
                                     Ext.getCmp('reporting_branch').getStore().load({
                                     params: {
                                     comp_id: Ext.getCmp('reporting_company').getValue()
                                     },
                                     callback: function () {
                                     totalComboCount--;
                                     comboLoadComplete();
                                     }
                                     });
                                     }
                                     });
                                     
                                     
                                     Ext.getCmp('auditor_company').getStore().load({
                                     callback: function () {
                                     totalComboCount--;
                                     comboLoadComplete();
                                     }
                                     });
                                     
                                     Ext.getCmp('report_to_user').getStore().load({
                                     callback: function () {
                                     totalComboCount--;
                                     comboLoadComplete();
                                     }
                                     });
                                     Ext.getCmp('auditor_branch').getStore().load({
                                     callback: function () {
                                     totalComboCount--;
                                     comboLoadComplete();
                                     }
                                     });*/
                                    //user_edit_window.doLayout();

                                    myMask.hide();
                                },
                                failure: function () {
                                    myMask.hide()
                                    Ext.Msg.alert('ERROR', USR_PERM);
                                    user_edit_window.close();
                                }
                            });
                            userID = Ext.getCmp('uid').getValue();
                            Ext.getCmp('txtPassword').getEl().up('.x-form-item').setDisplayed(false);
                            Ext.getCmp('user_type').setReadOnly(true);
                            Ext.getCmp('designation').setReadOnly(true);
                        }
                    }
                });
            }

            user_edit_window.doLayout();
            user_edit_window.show(this);
            user_edit_window.center();
        },
        saveUserData: function () {
            var uForm = Ext.getCmp('user_form');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (uForm.getForm().isValid()) {
                uForm.getForm().submit({
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        profile_update: 0
                    },
                    waitTitle: 'Please Wait!',
                    waitMsg: 'Saving data...',
                    success: function (uForm, action) {
                        eval('var tmp=' + action.response.responseText);
                        if (tmp.success !== undefined && tmp.success === true) {
                            Ext.getCmp('user_window').close();
                            if (Ext.isEmpty(userID))
                                Ext.MessageBox.alert("Success", "New member has been created successfully!..", function (btn) {

                                    Ext.getCmp('userPanel').store.reload();

                                });
                            else
                                Application.example.msg("Success", "Member profile has been updated successfully!..");
                                loadUser();
//                            function (btn) {
//
//                                    loadUser();
//
//                                });
                        }
                    },
                    failure: function (uForm, action) {
                        if (action.failureType == 'server') {
                            obj = Ext.util.JSON.decode(action.response.responseText);
                            Ext.MessageBox.show({
                                title: 'Error!',
                                msg: (obj.error) ? obj.error : obj.errors.reason,
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR,
                                width: 325
                            });
                        } else {
                            Ext.MessageBox.show({
                                title: 'Error!',
                                msg: 'Error occured while saving data.',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR,
                                width: 325
                            });
                        }
                    }
                });
            } else {
                Ext.MessageBox.show({
                    title: 'Error!',
                    msg: MAN_FIELD,
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                    width: 325
                });
            }
        },
        StatusChange: function () {
            Ext.Ajax.request({waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'active',
                    admin_active: Application.Users.user_active,
                    uidnr_admin: Application.Users.uidnr_admin
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {

                        Ext.getCmp('userPanel').getStore().reload();
                    }
                }
            });
        },
        DeleteUser: function () {
            uGrid = Ext.getCmp('userPanel');
            var selectedKeys = uGrid.selModel.selections.keys;
            var encoded_keys = Ext.encode(selectedKeys);
            Ext.Ajax.request({
                waitMsg: 'Deleting...',
                url: modURL,
                params: {
                    'op': deleteOP,
                    uidnr_admin: encoded_keys
                },
                callback: function (options, success, response) {
                    if (success) {
                        Ext.MessageBox.alert("Notification", "User has been deleted successfully", function (btn) {

                            loadUser();
                            Ext.getCmp('userPanel').getStore().reload();
                        });
                    } else {
                        Ext.MessageBox.alert(TRY_AGAIN);
                    }
                }
            }); // end Ajax request initialization
        },
        ChangeUserPassword: function () {
            var form1 = Ext.getCmp('changePassword_Form');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            form1.getForm().submit({
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (form1, action) {
                    if (!Ext.isEmpty(action.response))
                        if (action.response.responseText != "") {
                            eval('var tmp=' + action.response.responseText);
                            if (tmp.success !== undefined && tmp.success === true) {
                                Ext.MessageBox.alert('Notification', "Password has been changed successfully", function (btn) {

                                    Ext.getCmp('changePassword_window').close();
                                    Application.Users.logout(false);
                                });


                                
                            }
                        }
                },
                failure: function (form1, action) {
                    if (!Ext.isEmpty(action.response))
                        if (action.response.responseText != "") {
                            eval('var tmp=' + action.response.responseText);
                            Ext.MessageBox.alert('Notification', tmp.errors.reason);
                            Ext.getCmp('currentPassword').reset();
                            Ext.getCmp('currentPassword').focus();
                        }
                }
            });
        },
        UserMainPanel: function () {
            return [{
                    region: 'center',
                    border: false,
                    layout: 'fit',
                    items: Application.Users.init()
                }];
        }
    };
//
//
//////////////////////////////EO Public Area///////////////////////////////
}();

