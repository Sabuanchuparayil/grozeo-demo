/*
 * @author Lakshmi Jayaram <lakshmi@saturn.in>
 * @created on 07-Aug-2008
 *
 * All the components needs to create permissions
 */
Application.Access = function () {
    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Private Area------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Private Variables---------------------------//

    var perms_window;
    var modURL = '?module=access';
    var op_get_existing = "get_existing"
    var op_get_role_permission = "get_role_permission"
    var expander;
    var showSaveConfirmation = false;
    var perms_grid;
    var expandAll = false;
    var role_expandAll = false;

    //	var modules 		= {};//	=	[{"module":"recruitment","module_title":"Recruitment Module","description" : "This Module deals with posting recruitment and related process like shortlisting, etc.. ","operations" :[{"op":"default","op_title":"Post Resource Requirement","enabled":"0","capability":"post requirement"}]}];
    var system_modules = {};

    //Load the System Capabilities into a JS Object for next us
    Ext.Ajax.request({
        url: modURL,
        success: function (response, options) {
            var tmp;
            if (response.responseText != "") {
                eval('tmp=' + response.responseText);

            }
            else {
                tmp = "";
            }
            system_modules = tmp;

        }
    }) //end Ajax Load System capabilities
    var perms_store = new Ext.data.Store({
        reader: new Ext.data.JsonReader({
            id: 'module',
            fields: ['module', 'module_title', 'description', 'operations']
        })
    });

    var permissions, permissionsFor, recID, initial_perms, restrictions, currentPermissions;
//plugin used for permissionGrid
    var defineExpander = function () {
        expander = new Ext.grid.RowExpander({
            hideable: false,
            tpl: new Ext.XTemplate('<p><span style="padding-left: 5px;"><b>Description:</b> {description}</span>', '<div style="padding: 5px 5px 7px 5px;">', '<tpl for="operations">', '<div style="float: left; width:45%;">', '<div style="float:left;" onMouseOver="this.className=\'check-over\';" onMouseOut="this.className=\'\';">', '<div class="check{[(values.status>0?" check-on":"")]}" style="float: left;" onClick="Application.Access.setChecked(this, \'{capability}\')"></div>', '</div>', '&nbsp;&nbsp;{op_title}', '</div>', '</tpl>', '</div>', '</p><br>'),
            grid: perms_grid
        });
    }
    //Selection model for Permission Grid

    var grid_selection = new Ext.grid.RowSelectionModel({
        multiSelect: true,
        loadMask: true
    });


    //----------------------------Private Methods----------------------------//

    var loadGrid = function () {
        var access_filter = new Ext.ux.grid.GridFilters({
            local: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'module_title'
                }]
        });
        access_filter.remote = true;
        access_filter.autoReload = true;
        defineExpander();
//create a grid to display module
        var permissionGrid = new Ext.grid.GridPanel({
            store: perms_store,
            id: 'perms_grid',
            autoScroll: true,
            viewConfig: {
                forceFit: true
            },
            plugins: [expander, access_filter],
            border: false,
            columns: [expander, {
                    id: 'module',
                    menuDisabled: false,
                    header: "Module",
                    sortable: true,
                    hideable: true,
                    dataIndex: 'module_title'
                }],
            sm: grid_selection,
            listeners: {
                rowdblclick: function (grid, row, e) {
                    expander.toggleRow(row);
                }
            }, tools: [{
                    id: 'toggle',
                    text: 'Expand/Contract',
                    qtip: 'Expand/Contract',
                    handler: function () {
                        for (var i = 0; i <= Ext.getCmp('perms_grid').getStore().getCount(); i++) {
                            expander.toggleRow(i);
                        }
                    }
                }],
            bbar: [{
                    text: 'Apply',
                    tooltip: 'Apply Permissions',
                    icon: './resources/images/default/icons/disk.png',
                    iconCls: 'my-icon1',
                    handler: save_perms
                }],
            stripeRows: true,
            autoExpandColumn: 'module'
        });
        return permissionGrid;
    }
//set permission
    var addPermission = function (capability) {
        removePermission(capability);
        permissions.push(capability);
    }
//remove permission
    var removePermission = function (capability) {
        delete permissions[permissions.indexOf(capability)];
    };

//Save permission setiings
    var save_perms = function () {

        var valid_permissions = [];
        var restricted_permissions = [];

        if (!Ext.isEmpty(permissions)) {
            Ext.each(permissions, function (it) {
                if (!Ext.isEmpty(it))
                    valid_permissions.push(it);
            });
        }

        if (!Ext.isEmpty(restrictions)) {
            Ext.each(restrictions, function (i) {
                if (!Ext.isEmpty(i))
                    restricted_permissions.push(i);
            });
        }

        var form_data = {
            perms: valid_permissions/*permissions*/,
            restrict: restricted_permissions/*restrictions*/,
            id: recID,
            type: permissionsFor
        };
        var params = {
            action: 'Update',
            module: 'access',
            op: 'save',
            extrainfo: 'asd',
            id: recID
        };

        APICall(params, Application.Access.SavePermissions, form_data);
    };

    var x = 0;
    var enable_existing_capabilities = function (response, options) {
        //load the System Capabilities to a variable
        var modules = Ext.util.JSON.decode(Ext.util.JSON.encode(system_modules));

        eval('var availablePerms =' + response.responseText);

        var existing = availablePerms.existing;
        //console.log(existing);
        currentPermissions = availablePerms.roles;
        for (var i = 0; i < modules.length; i++) {
            var operations = modules[i].operations;

            for (var j = 0; j < operations.length; j++) {
                if (operations[j].status == 1 || existing.in_array(operations[j].capability) == true) {
                    addPermission(operations[j].capability);
                    operations[j].status = 1;
                }
            }
            modules[i].operations = operations;
        }
        //Finaly load the Capabilities into Grid Window.
        perms_store.loadData(modules);
        defineExpander();
    }

//For load  the existing capabilities for the existing user or role
    var loadCapabilities = function () {

        //Get the existing capabilities for the existing user or role
        Ext.Ajax.request({
            url: modURL,
            params: {
                op: op_get_existing,
                id: recID,
                type: permissionsFor,
                renderer: new Date().format("Y-m-d H:i:s")
            },
            success: enable_existing_capabilities
        });
    }
//confirmation for save permission
    var checkSave = function () {

        if (showSaveConfirmation == true) {
            Ext.MessageBox.confirm('Confirm', 'You have modified the Permission settings. Do you wish to save changes?', function (btn, text) {
                if (btn == 'yes') {
                    save_perms();
                }
            });
        }
    }
    
    var role_tree_panel = function (id) {

        var tree = new Ext.tree.TreePanel({
            region: 'center',
            useArrows: false,
            autoScroll: true,
            animate: true,
            enableDD: true,
            containerScroll: true,
            border: true,
            overClass: 'x-view-over',
            itemSelector: 'div.thumb-wrap',
            dataUrl: modURL + '&op=getPermissionRole' + '&role_id=' + id,
            id: 'role_permission_tree',
            mask: true,
            maskDisabled: false,
            maskConfig: {msg: "Loading items..."},
            root: {
                nodeType: 'async',
                text: '',
                draggable: false,
                id: 'role_permission_tree_root',
                cls: 'nature_group'
            },
            listeners: {
                load: function () {
                    setTimeout(function () {
                        if (WinMask)
                            WinMask.hide();
                    }, 500);
                },
                checkchange:function (node,checked){
                  Ext.Ajax.request({
                    url: modURL + '&op=saveRolePermission',
                    params: {
                        role_id: id,
                        perm_op: node.attributes['OperationId'],
                        node_checked:checked
                    },
                    success: function (response, options) {}
                  });
                }
            }
        });
        //Ext.getCmp('ldg_company').setValue(store.getAt('0').get('comp_id'));
        tree.getRootNode().expand();
        return tree;
    };
    var tree_panel = function (id) {

        var tree = new Ext.tree.TreePanel({
            region: 'center',
            useArrows: false,
            autoScroll: true,
            animate: true,
            enableDD: true,
            containerScroll: true,
            border: true,
            overClass: 'x-view-over',
            itemSelector: 'div.thumb-wrap',
            dataUrl: modURL + '&op=getPermissionUser' + '&user_id=' + id,
            id: 'user_permission_tree',
            mask: true,
            maskDisabled: false,
            maskConfig: {msg: "Loading items..."},
            root: {
                nodeType: 'async',
                text: '',
                draggable: false,
                id: 'permission_tree_root',
                cls: 'nature_group'
            },
            listeners: {
                load: function () {
                    setTimeout(function () {
                        if (WinMask)
                            WinMask.hide();
                    }, 500);
                },
                checkchange:function (node,checked){
                  Ext.Ajax.request({
                    url: modURL + '&op=savePermission',
                    params: {
                        user_id: id,
                        perm_op: node.attributes['OperationId'],
                        node_checked:checked
                    },
                    success: function (response, options) {}
                  });
                }
            }
        });
        //Ext.getCmp('ldg_company').setValue(store.getAt('0').get('comp_id'));
        tree.getRootNode().expand();
        return tree;
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
        setChecked: function (el, capability) {
            try {
                if (!showSaveConfirmation) {
                    showSaveConfirmation = true;
                }

                var obj = Ext.get(el);
                var class_exists = obj.dom.className;
                if (class_exists.trim() == "check check-on") {

                    obj.removeClass("check-on");
                    obj.removeClass("check");
                    obj.addClass("check");
                    removePermission(capability);

                    //storing unwanted role permission in an array
                    if (!Ext.isEmpty(currentPermissions))
                        if (currentPermissions.in_array(capability) == true) {
                            delete restrictions[restrictions.indexOf(capability)];
                            restrictions.push(capability);
                        }

                }
                else {
                    //if(currentPermissions.in_array(capability) == true){
                    delete restrictions[restrictions.indexOf(capability)];
                    //}

                    obj.removeClass(class_exists);
                    obj.addClass("check check-on");
                    addPermission(capability);
                }
            } catch (ex) {
                Ext.Msg.alert('Exception', 'Error : ' + ex.message);
            }
        },
        initRolePermission:function (permFor, id, titleName) {
          var win_id = "role_permission_structure";
          var win = Ext.getCmp(win_id);
          if (Ext.isEmpty(win)) {

              win = new Ext.Window({
                  id: win_id,
                  title: 'Menuwise Permission Settings for Roles ' + titleName,
                  layout: 'fit',
                  width: 700,
                  autoHeight: true,
                  plain: true,
                  constrainHeader: true,
                  modal: true,
                  frame: true,
                  resizable: false,
                  items: new Ext.Panel({
                      height: 500,
                      border: false,
                      layout: 'border',
                      items: [role_tree_panel(id)]
                  }), buttons: [
                      {
                          text: 'Expand All',
                          id:'xpandRole',
                          tabIndex: 501,
                          icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                          handler: function () {
                            if(!role_expandAll){
                              Ext.getCmp('role_permission_tree').expandAll();
                              Ext.getCmp('xpandRole').setText('Collapse All');
                              role_expandAll = true;
                            }else{
                              Ext.getCmp('role_permission_tree').collapseAll();
                              Ext.getCmp('role_permission_tree').getRootNode().expand();
                              Ext.getCmp('xpandRole').setText('Expand All');
                              role_expandAll = false;                          
                            }
                                  //Ext.getCmp('role_permission_tree').getRootNode().reload();
                          }
                      },
                    {
                          text: 'Close',
                          tabIndex: 502,
                          icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                          handler: function () {
                              win.close();
                          }
                      }],
                  listeners: {
                      afterrender: function () {
                          WinMask = new Ext.LoadMask(Ext.getCmp('role_permission_structure').getEl());
                          WinMask.show();
                      }
                  }
              });
          }

          win.doLayout();
          win.show(this);
          win.center();
        },
        init_menuwise_permission:function (permFor, id, titleName) {
          var win_id = "user_permission_structure";
          var win = Ext.getCmp(win_id);
          if (Ext.isEmpty(win)) {

              win = new Ext.Window({
                  id: win_id,
                  title: 'Menuwise Permission Settings for Users ' + titleName,
                  layout: 'fit',
                  width: 700,
                  autoHeight: true,
                  plain: true,
                  constrainHeader: true,
                  modal: true,
                  frame: true,
                  resizable: false,
                  items: new Ext.Panel({
                      height: 500,
                      border: false,
                      layout: 'border',
                      items: [tree_panel(id)]
                  }), buttons: [
                    {
                          text: 'Expand All',
                          id:'xpandUser',
                          tabIndex: 501,
                          icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                          handler: function () {
                              if(!expandAll){
                              Ext.getCmp('user_permission_tree').expandAll();
                              Ext.getCmp('xpandUser').setText('Collapse All');
                              expandAll = true;
                            }else{
                              Ext.getCmp('user_permission_tree').collapseAll();
                              Ext.getCmp('user_permission_tree').getRootNode().expand();
                              Ext.getCmp('xpandUser').setText('Expand All');
                              expandAll = false;                          
                            }
                                  //Ext.getCmp('user_permission_tree').getRootNode().reload();
                          }
                      },
                    {
                          text: 'Close',
                          tabIndex: 502,
                          icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                          handler: function () {
                              win.close();
                          }
                      }],
                  listeners: {
                      afterrender: function () {
                          WinMask = new Ext.LoadMask(Ext.getCmp('user_permission_structure').getEl());
                          WinMask.show();
                      }
                  }
              });
          }

          win.doLayout();
          win.show(this);
          win.center();
        },
        init: function (permFor, id, titleName) {
            permissions = [];
            restrictions = [];
            currentPermissions = [];
            permissionsFor = permFor;
            recID = id;

            showSaveConfirmation = false;

            //Call function to Load the Available capabilities in the system and enabled the capabilities
            //in which the user already has permissions
            loadCapabilities();

            //initial_perms = permissions;

            var win_id = "permission_window";
            //create the window on the first click and reuse on subsequent clicks
            perms_window = Ext.getCmp(win_id);

            perms_store.removeAll();
            perms_grid = loadGrid();
            if (Ext.isEmpty(perms_window)) {

                //Create a window
                perms_window = new Ext.Window({
                    id: win_id,
                    layout: 'fit',
                    //iconCls: 'my-icon35',
                    title: 'Set Permissions: ' + permFor.toUpperCase() + " : " + toTitleCase(titleName),
                    width: 650,
                    height: 400,
                    //autoHeight: true,
                    plain: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: [perms_grid]
                });
            }
            perms_window.on("beforeClose", checkSave);
            perms_window.doLayout();
            perms_window.show(this);
        },
        SavePermissions: function () {
            Ext.Ajax.request({
                url: modURL,
                params: {
                    op: 'save',
                    perms: Ext.encode(permissions),
                    restrict: Ext.encode(restrictions),
                    id: recID,
                    type: permissionsFor
                },
                success: function (response) {
                    eval('var tmp=' + response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        showSaveConfirmation = false;
                        Application.example.msg('Status', 'Permissions Saved'); 
                        perms_window.close();
//                        function (btn, text) {
//                           
//                                perms_window.close();
//                            
//                        };
                    }
                },
                failure: function (response) {
                    Ext.MessageBox.alert('Error', 'could not connect to the database. retry later');
                }
            });
        }
    };

    //
    //
    //////////////////////////////EO Public Area///////////////////////////////
}
();
