/*
 * @author Lakshmi Jayaram <lakshmi@saturn.in>
 * @created on 03-Jul-2008
 *
 * All the components needs to be displayed at the time of 'Roles' click in the menu bar is defined here for better readability.
 */
Application.Role = function () {

///////////////////////////////////////////////////////////////////////////
//-----------------------------------------------------------------------//
//-----------------------------Private Area------------------------------//
//-----------------------------------------------------------------------//
//---------------------------Private Variables---------------------------//
//Configure the Number of records can display in Grid at a time.
    var recs_per_page = 12;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var user_window;
    var img_manageRole = IMAGE_BASE_PATH + "/default/icons/key.png";
    var img_deleteRole = IMAGE_BASE_PATH + "/default/icons/delete.png";
    var role_grid, roleGrid;
    var modURL = '?module=role';
    var saveOP = "save";
    var opKey = "op";
    var deleteOP = "delete";
    //reads a data object from an Ext.data.Connection object configured to reference a certain URL. 
    var proxy = new Ext.data.HttpProxy({
        url: modURL,
        method: 'post'
    });
    /** 
     * var role_store = Store for 'role_grid'  
     **/
    var role_store = new Ext.data.JsonStore({
        url: modURL,
        method: 'post',
        proxy: proxy,
        fields: ['rownum', 'id_admin_role', 'admin_role_name'],
        totalProperty: 'totalCount',
        root: 'roles',
        id: 'id_admin_role',
        // turn on remote sorting
        remoteSort: true,
        listeners: {
            load: function () {
                Ext.getCmp('gridRole').selModel.clearSelections();
                if (Ext.getCmp("role_delete_button"))
                    Ext.getCmp('role_delete_button').disable();
            }
        }
    });
    role_store.setDefaultSort('admin_role_name', 'asc');
//for selecting multi rows in a grid    
    function designationSelect() {
        return  new Ext.grid.RowSelectionModel({
            multiSelect: true,
            listeners: {
                //enable delete button while selecting row     
                rowselect: function (sm, row, rec) {
                    if (Ext.getCmp("role_delete_button")) {
                        Ext.getCmp("role_delete_button").enable();
                    }
                }

            }
        });
    }



//----------------------------Private Methods----------------------------//


// Render function to display set permission link in the user_grid

    /* <?php if (user_access("access", "default")) { ?> */
    var manageRole = function (value, p, record) {
        return '<div><input type="image" title="Set Permission" src="' + img_manageRole + '" onClick="Application.Access.init(\'designation\',\'' + value + '\',\'' + record.data.admin_role_name + '\');"/></div>'
    };
    /* <?php } ?> */


    // Function to update role on add new role click and editing the grid
    function updateRole(grid_event) {

        var rec = grid_event.record;
        Application.Role.admin_role_name = rec.data.admin_role_name;
        Application.Role.id_admin_role = (!Ext.isEmpty(rec.data.id_admin_role)) ? rec.data.id_admin_role : 0;
        var form_data = {
            admin_role_name: Application.Role.admin_role_name,
            id_admin_role: Application.Role.id_admin_role
        };
        var params = {
            action: 'Insert',
            module: 'role',
            op: saveOP,
            extrainfo: 'asd',
            id: '0'
        };
        if (Application.Role.id_admin_role > 0) {
            params.action = 'Update';
            params.id = Application.Role.id_admin_role;
        }

        APICall(params, Application.Role.SaveRole, form_data);
    }
    ;
    function deleteRecord(btn) {

        if (btn == 'yes') {
            roleGrid = Ext.getCmp('gridRole');
            var selectedKeys = roleGrid.selModel.selections.keys;
            var form_data = {
                uidnr_admin: selectedKeys
            };
            var params = {
                action: 'Update',
                module: 'role',
                op: deleteOP,
                extrainfo: 'asd',
                id: selectedKeys.toString()
            };
            APICall(params, Application.Role.DeleteRole, form_data);
        }
    }
    ; // end deleteRecord
    //Handler for Deleting record(s)

    function handleDelete() {
        //returns array of selected rows ids only

        roleGrid = Ext.getCmp('gridRole');
        var selectedKeys = roleGrid.selModel.selections.keys;
        if (selectedKeys.length > 0) {
            Ext.MessageBox.confirm('Confirm', 'Do you really want to delete selection?', deleteRecord);
        }
    }
    ; // end handleDelete

    // function to add new record in role grid
    function add_new_Role() {
        Ext.getCmp('txtRole').focus();
        var row = new Ext.data.Record.create({
            name: 'admin_role_name'
        });
        var r = new row({
            admin_role_name: ''

        });
        Ext.getCmp('gridRole').stopEditing(); //stops any acitve editing
        //the insertion point
        role_store.insert(0, r); //1st arg is index,2nd arg is Ext.data.Record[] records
        //start editing the specified rowIndex, colIndex
        Ext.getCmp('gridRole').startEditing(0, 0);
    }
    ; // EO add_new_Role
    //var action= List of actions in role_grid,Action permission
//    var action = new Ext.ux.grid.RowActions({
//        hideMode: 'display',
//        header: 'Action',
//        // tooltip: '',
//        autoWidth: false,
//        width: 30,
//        actions: [{width: 20,
//                sortable: false,
//                tooltip: 'Set Permission',
//                iconCls: 'my-icon35',
//                callback: function (grid, rec, row, col) {
//                    Application.Access.init('designation', rec.data.id_admin_role, rec.data.admin_role_name);
//                }}]});
var rolesActionMenu = new Ext.menu.Menu({
        items: [      {
        text: "Set Menuwise Permission to Roles",
        handler: function () {
          var id_role = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.id_admin_role;
          var admin_role_name = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.admin_role_name;
          Application.Access.initRolePermission('designation', id_role, admin_role_name);
            }
          },
          {
                text: "Set Permission",
                handler: function () {
                    var id_admin_role = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.id_admin_role;
                    var admin_role_name = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.admin_role_name;
                    Application.Access.init('designation', id_admin_role, admin_role_name);
                }
            }]
    });
    
//creating a grid to display All Designation 
    var createGrid = function () {
        var designation_select = designationSelect();
        var filters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'admin_role_name'
                }]
        });
        filters.remote = true;
        filters.autoReload = true;
        var role_grid = new Ext.grid.EditorGridPanel({
            store: role_store,
            loadMask: true,
            layout: 'fit',
            forceFit: true,
            plugins: [filters],
            title: 'Manage Roles',
            //iconCls: 'icon_role',
            id: 'gridRole',
            clicksToEdit: '2',
            columns: [{
                    header: 'Designation',
                    id: 'role_name',
                    flex: 1,
                    sortable: true,
                    dataIndex: 'admin_role_name',
                    editor: new Ext.form.TextField({
                        id: 'txtRole',
                        allowBlank: false,
                        maxLength: 90
                    })
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
                            rolesActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                } 
                /* <?php if (user_access("access", "default")) { ?> */ , /* <?php } ?> */],
            sm: designation_select,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            tbar: [/* <?php if (user_access("role", "save")) { ?> */{
                    text: 'Create Roles',
                    id: 'role_add_button',
                    tooltip: 'Create Roles',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: add_new_Role
                }, '-', /* <?php } ?> */ /* <?php if (user_access("role", "delete")) { ?> */ {
                    text: 'Delete',
                    id: 'role_delete_button',
                    tooltip: 'Delete Selected Roles',
                    icon: IMAGE_BASE_PATH + "/default/icons/delete.png",
                    iconCls: 'my-icon1',
                    disabled: true,
                    handler: handleDelete
                } /* <?php } ?> */],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: role_store,
                displayInfo: true,
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            listeners: {
                viewready: updatePagination
                        /* <?php if (user_access("role", "default")) { ?> */
                ,afteredit: updateRole
                        /* <?php } ?> */
            },
            stripeRows: true,
            autoExpandColumn: 'role_name'

        });
        return role_grid;
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
            //loadRole();
            var rolegrid = Ext.getCmp('gridRole');
            if (!rolegrid) {
                rolegrid = createGrid();
            }

            Application.UI.addTab(rolegrid);
            rolegrid.doLayout();
        },
        SaveRole: function () {
            Ext.Ajax.request({
                waitMsg: 'Please wait...',
                url: modURL,
                params: {
                    op: saveOP,
                    admin_role_name: Application.Role.admin_role_name,
                    id_admin_role: Application.Role.id_admin_role
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', 'Save Failed');
                },
                success: function (response, options) {
                    if (response.responseText != "") {
                        eval('var tmp=' + response.responseText);
                        if (tmp.success !== undefined && tmp.success === true) {
                            var msg = 'New Role has been saved successfully';
                            if (Application.Role.id_admin_role)
                                msg = 'Role has been updated successfully';
                            Ext.MessageBox.alert('Notification', msg, function (btn) {
                               
                                    role_store.reload();
                               
                            });
                        }
                        else
                        if (tmp.success === false) {
                            Ext.MessageBox.alert('Notification', tmp.errors.reason);
                        }
                    }
                }
            });
        },
        DeleteRole: function () {
            roleGrid = Ext.getCmp('gridRole');
            var selectedKeys = roleGrid.selModel.selections.keys;
            var encoded_keys = Ext.encode(selectedKeys);
            Ext.Ajax.request({
                waitMsg: 'Deleting...',
                url: modURL,
                params: {
                    op: deleteOP,
                    id_admin_role: encoded_keys
                },
                callback: function (options, success, response) {
                    if (success) {
                        if (response && response.responseText) {
                            Application.example.msg("Notification", "Role(s) has been deleted successfully");
                            role_store.removeAll();
                            role_store.reload();
//                            function (btn) {
//                                
//                                    role_store.removeAll();
//                                    role_store.reload();
//                                
//                            });
                        }
                    }
                    else {
                        Ext.MessageBox.alert('Sorry, please try again. ');
                    }
                }
            });
        }
    };
    //////////////////////////////EO Public Area///////////////////////////////
}
();