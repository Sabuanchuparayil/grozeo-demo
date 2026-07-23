Application.AdminSiteAccess = function () {
    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Private Area------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Private Variables---------------------------//

    var perms_window;
    var modURL = '?module=access_admin_site';
    var op_get_existing = "get_existing"
    var op_get_role_permission = "get_role_permission"
    var expander;
    var showSaveConfirmation = false;
    var perms_grid;
    var expandAll = false;
    var role_expandAll = false;
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
    return {
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
          }
    }
}
();