Application.OrgRole = function () {

  ///////////////////////////////////////////////////////////////////////////
  //-----------------------------------------------------------------------//
  //-----------------------------Private Area------------------------------//
  //-----------------------------------------------------------------------//
  //---------------------------Private Variables---------------------------//
  //Configure the Number of records can display in Grid at a time.
      var recs_per_page = 22;
      function updatePagination(cmp) {
          recs_per_page = finascop_update_recs_per_page(cmp);
      }
  var winsize = Ext.getBody().getViewSize();
      var user_window;
      var img_manageRole = IMAGE_BASE_PATH + "/default/icons/key.png";
      var img_deleteRole = IMAGE_BASE_PATH + "/default/icons/delete.png";
      var role_grid, roleGrid;
      var modURL = '?module=manage_role';
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
          fields: ['rownum', 'id_admin_role', 'admin_role_name','deptName','typeName'],
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
                  rowselect: function (sm, row, record) {
                    var rolePermissionsTo = record.get("rolePermissionsTo");
                    switch(rolePermissionsTo){
                      case '1':
                        Ext.getCmp('roleAdminSitePermission').hide();
                        Ext.getCmp('roleMenuPermission').show();
                        Ext.getCmp('roleCapabilityPermission').show();
                        break;
                      case '2':
                        Ext.getCmp('roleMenuPermission').hide();
                        Ext.getCmp('roleCapabilityPermission').hide();
                        Ext.getCmp('roleAdminSitePermission').show();
                        break;
                      case '3':
                        Ext.getCmp('roleMenuPermission').show();
                        Ext.getCmp('roleCapabilityPermission').show();
                        Ext.getCmp('roleAdminSitePermission').show();
                        break;
                    }
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
          Application.OrgRole.admin_role_name = rec.data.admin_role_name;
          Application.OrgRole.id_admin_role = (!Ext.isEmpty(rec.data.id_admin_role)) ? rec.data.id_admin_role : 0;
          var form_data = {
              admin_role_name: Application.OrgRole.admin_role_name,
              id_admin_role: Application.OrgRole.id_admin_role
          };
          var params = {
              action: 'Insert',
              module: 'role',
              op: saveOP,
              extrainfo: 'asd',
              id: '0'
          };
          if (Application.OrgRole.id_admin_role > 0) {
              params.action = 'Update';
              params.id = Application.OrgRole.id_admin_role;
          }
  
          APICall(params, Application.OrgRole.SaveRole, form_data);
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
              APICall(params, Application.OrgRole.DeleteRole, form_data);
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
  
      var deptComboStorefn = function () {
              var store = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + "&op=getDepartment",
                method: "post",
                fields: ["id", "name"],
                root: "data",
              });
              return store;
            };
            var divTypeComboStorefn = function () {
              var store = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + "&op=getDivisionType",
                method: "post",
                fields: ["id", "name"],
                root: "data",
              });
              return store;
            };
            var divisionComboStorefn = function () {
              var store = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + "&op=getDivision",
                method: "post",
                fields: ["id", "name"],
                root: "data",
              });
              return store;
            };
            var reportingToComboStorefn = function () {
              var store = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + "&op=getRoles",
                method: "post",
                fields: ["RoleId", "RoleName"],
                root: "data",
              });
              return store;
            };
          var manageRoleForm = function (roleId) {
              var deptComboStore = deptComboStorefn();
              var divTypeComboStore = divTypeComboStorefn();
              var divisonComboStore = divisionComboStorefn();
              var reportingToComboStore = reportingToComboStorefn();
              var form = new Ext.FormPanel({
                  id: 'orgRoleForm',
                  height: winsize.height * .3,
                  autoScroll:true,
                  frame: true,
                  border: true,
                  labelAlign: 'top',
                  items: [{
                          layout: 'column',
                          items: [
                              {
                                  layout: 'form',
                                  columnWidth: .5,
                                  padding: '3px',
                                  items: [{
                                      xtype:'hidden',
                                      id:'id_admin_role',
                                      name:'id_admin_role'
                                  },{
                                      xtype: "textfield",
                                      fieldLabel: "Role",
                                      id: "txtRole",
                                      name: "txtRole",
                                      anchor: "98%",
                                      allowBlank: false,
                                      tabIndex: 1,
                                      maxLength: 100,
                                    },{
                                      xtype: "combo",
                                      store: divTypeComboStore,
                                      mode: "local",
                                      id: "divTypeId",
                                      allowBlank: false,
                                      fieldLabel: "Type",
                                      hiddenName: "divTypeId",
                                      displayField: "name",
                                      valueField: "id",
                                      typeAhead: true,
                                      forceSelection: true,
                                      editable: true,
                                      minChars: 2,
                                      anchor: "98%",
                                      triggerAction: "all",
                                      lazyRender: true,
                                      tabIndex: 3,
                                      listeners: {
                                        select: function () {
                                          var value = Ext.getCmp("divTypeId").getValue();
                                          divisonComboStore.baseParams.divTypeId = this.value;
                                          divisonComboStore.load();
                                        },
                                      },
                                    },{
                                      xtype: "combo",
                                      store: reportingToComboStore,
                                      mode: "local",
                                      id: "reprtingRoleId",
                                      allowBlank: false,
                                      fieldLabel: "Reporting To",
                                      hiddenName: "reprtingRoleId",
                                      displayField: "RoleName",
                                      valueField: "RoleId",
                                      typeAhead: true,
                                      forceSelection: true,
                                      editable: true,
                                      minChars: 2,
                                      anchor: "98%",
                                      triggerAction: "all",
                                      lazyRender: true,
                                      tabIndex: 5,
                                      listeners: {
                                        select: function () {
                                          var value = Ext.getCmp("reprtingRoleId").getValue();
                                        },
                                      },
                                    }]
                              }, {
                                  layout: 'form',
                                  columnWidth: .5,
                                  padding: '3px',
                                  items: [{
                                      xtype: "combo",
                                      store: deptComboStore,
                                      mode: "local",
                                      id: "roleDepartmentId",
                                      allowBlank: false,
                                      fieldLabel: "Department",
                                      hiddenName: "roleDepartmentId",
                                      displayField: "name",
                                      valueField: "id",
                                      typeAhead: true,
                                      forceSelection: true,
                                      editable: true,
                                      minChars: 2,
                                      anchor: "98%",
                                      triggerAction: "all",
                                      lazyRender: true,
                                      tabIndex: 2,
                                      listeners: {
                                        select: function () {
                                          
                                        },
                                      },
                                    },{
                                      xtype: 'combo',
                                      displayField: 'name',
                                      valueField: 'id',
                                      mode: 'local',
                                      fieldLabel: 'Permission On',
                                      hiddenName: 'rolePermissionsTo',
                                      id: 'rolePermissionsTo',
                                      allowBlank: false,
                                      anchor: '98%',
                                      tabIndex: 6,
                                      triggerAction: 'all',
                                      lazyRender: true,
                                      store: new Ext.data.JsonStore(
                                              {
                                                  fields: ['id', 'name'],
                                                  data: [{
                                                          id: '1',
                                                          name: 'Back Office'
                                                      },
                                                      {
                                                          id: '2',
                                                          name: 'Admin Site'
                                                      },
                                                      {
                                                          id: '3',
                                                          name: 'Both'
                                                      }
                                                  ]
  
  
                                              })
                                  }]
                              }
                              
                          ]}
                  ]
              });
              return form;
          };
          var roleDesignationGrid = function (roleId) {
              var _designationGridStore = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + "&op=getDesignations",
                method: "post",
                fields: ["id", "name", "checked"],
                remoteSort: true,
                listeners:{
                  beforeload: function (store, e) {
                      this.baseParams.roleId = roleId;
                  }
                }
              });
              var ck_selection = new Ext.grid.CheckboxSelectionModel({
                multiSelect: true,
                checkOnly: true,
                listeners: {
                  rowdeselect: function (sm, rowIndex, record) {
                    var ind = role_designation.indexOf(record.get("id"));
                    if (ind > -1) role_designation.splice(ind, 1);
                    record.set("checked", "false");
                  },
                  rowselect: function (sm, rowIndex, record) {
                    var ind = role_designation.indexOf(record.get("id"));
                    if (ind == -1)
                      role_designation.push(record.get("id"));
                    record.set("checked", "true");
                  },
                },
              });
              var __rcGridFilter = new Ext.ux.grid.GridFilters({
                filters: [
                  {
                    type: "string",
                    dataIndex: "name",
                  },
                ],
              });
              __rcGridFilter.remote = true;
              __rcGridFilter.autoReload = true;
              var _albumProjectEventGrid = new Ext.grid.GridPanel({
                id: "gridRoleDesignation",
                region: "center",
                height: 200,
                width: winsize.width * 0.78,
                frame: true,
                border: false,
                autoScroll: true,
                store: _designationGridStore,
                selModel: ck_selection,
                viewConfig: {
                  forceFit: true,
                },
                plugins: [__rcGridFilter],
                fields: ["id", "name"],
                colModel: new Ext.grid.ColumnModel({
                  columns: [
                    ck_selection,
                    {
                      header: "Designation",
                      dataIndex: "name",
                    },
                  ],
                }),
                iconCls: "icon-grid",
                listeners: {
                  afterrender: function () {
                    {
                      var me = this;
                      _designationGridStore.on("load", function () {
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
                  },
                },
              });
              return _albumProjectEventGrid;
            };
      function add_new_Role(roleId,type) {
              var form = manageRoleForm(roleId);
              var orgRoleManage_window = Ext.getCmp('orgRoleManage_window');
              if (Ext.isEmpty(orgRoleManage_window)) {
                  orgRoleManage_window = new Ext.Window({
                      id: 'orgRoleManage_window',
                      title: 'Manage Role',
                      layout: 'fit',
                      width: winsize.width * .5,
                      height: winsize.height * .45,
                      plain: true,
                      constrainHeader: true,
                      modal: true,
                      frame: true,
                      resizable: false,
                      items: [form],
                      shadow: false,
                      buttons: [{
                              text: 'Cancel',
                              tabindex: 26,
                              icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                              handler: function () {
                                  orgRoleManage_window.close();
                              }
                          }, {
                              text: 'Save',
                              icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                              tabindex: 25,
                              handler: function () {
                                  saveRoles(roleId, type);
                              }
                          }
  
                      ]
                  });
              }
              if (!Ext.isEmpty(roleId)) {
                  var t = new Date();
                  var t_stamp = t.format("YmdHis");
                  var dept_form = Ext.getCmp('orgRoleForm').getForm();
                  dept_form.load({
                      params: {
                          roleId: roleId,
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp
                      },
                      url: modURL + '&op=loadRoleData',
                      waitMsg: 'Loading...', success: function (form, action) {
                          eval('var tmp=' + action.response.responseText);
                          var tmp = Ext.decode(action.response.responseText);
                      },
                      failure: function (form, action) {
                      }
                  });
              }
              orgRoleManage_window.doLayout();
              orgRoleManage_window.show(this);
              orgRoleManage_window.center();
      }
      ; 
  var saveRoles = function () {
              
              var ptId = Ext.getCmp("id_admin_role").getValue();
              if (
                !Ext.isEmpty(Ext.getCmp("txtRole").getValue()) &&
                !Ext.isEmpty(Ext.getCmp("roleDepartmentId").getValue())
              ) {
                Ext.Ajax.request({
                  url: modURL + "&op=save",
                  method: "POST",
                  params: {
                    id: Ext.getCmp("id_admin_role").getValue(),
                    admin_role_name: Ext.getCmp("txtRole").getValue(),
                    roleDepartmentId:Ext.getCmp("roleDepartmentId").getValue(),
                    roleDepartmentName:Ext.getCmp("roleDepartmentId").getRawValue(),
                    divTypeId:Ext.getCmp("divTypeId").getValue(),
                    reprtingRoleId:Ext.getCmp("reprtingRoleId").getValue(),
                    rolePermissionsTo:Ext.getCmp("rolePermissionsTo").getValue()
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                      Ext.getCmp('orgRoleManage_window').close();
                      Application.example.msg("Success", "Role updated successfully.");
                      if (Application.OrgRole.action == "Add") {
                        RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("gridRole"));
                        Ext.getCmp("gridRole").store.reload({
                          params: {
                            start: 0,
                            limit: RECS_PER_PAGE,
                          },
                        });
                      } else {
                        Ext.getCmp("gridRole").getStore().load({
                            callback: function (record, options, success) {
                              var gridPanel = Ext.getCmp("gridRole");
                              var index = gridPanel.store.find("id_admin_role",ptId);
                              gridPanel.getSelectionModel().selectRow(index);
                            },
                          });                      
                      }
                      
                    } else if (tmp.success === true && tmp.valid === false) {
                      Ext.Msg.alert("Notification.", tmp.message);
                    } else if (tmp.success === true && tmp.img_valid === false) {
                      Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                      Ext.Msg.alert("Error", tmp.message);
                    }
                  },
                  failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert("Error", tmp.msg);
                  },
                });
              } else {
                Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
              }
            };
      
  var rolesActionMenu = new Ext.menu.Menu({
          items: [{
              text: "Edit",
              id:'roleEdit',
              handler: function () {
                  var id_admin_role = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.id_admin_role;
                  add_new_Role(id_admin_role,1);
              }
          },{
          text: "Backoffice Menuwise Permission",
          id:'roleMenuPermission',
          handler: function () {
            var id_role = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.id_admin_role;
            var admin_role_name = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.admin_role_name;
            Application.Access.initRolePermission('designation', id_role, admin_role_name);
              }
            },
            {
                  text: "Backoffice Capability Permission",
                  id:'roleCapabilityPermission',
                  handler: function () {
                      var id_admin_role = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.id_admin_role;
                      var admin_role_name = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.admin_role_name;
                      Application.Access.init('designation', id_admin_role, admin_role_name);
                  }
              },{
                text: "Admin Site Permission",
                hidden:true,
                id:'roleAdminSitePermission',
                  handler: function () {
                    var id_admin_role = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.id_admin_role;
                    var admin_role_name = Ext.getCmp('gridRole').getSelectionModel().getSelections()[0].data.admin_role_name;
                    Application.AdminSiteAccess.initRolePermission('designation', id_admin_role, admin_role_name);
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
          var role_grid = new Ext.grid.GridPanel({
              store: role_store,
              loadMask: true,
              layout: 'fit',
              forceFit: true,
              plugins: [filters],
              title: 'Manage Roles',
              id: 'gridRole',
              columns: [{
                      header: 'Role',
                      id: 'role_name',
                      flex: 1,
                      sortable: true,
                      dataIndex: 'admin_role_name'
                  },{
                      header: 'Department',
                      sortable: true,
                      dataIndex: 'deptName'
                  },{
                      header: 'Type',
                      sortable: true,
                      dataIndex: 'typeName'
                  },{
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
                      handler: function() {
                          add_new_Role();
                      }
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
                      admin_role_name: Application.OrgRole.admin_role_name,
                      id_admin_role: Application.OrgRole.id_admin_role
                  },
                  failure: function (response, options) {
                      Ext.MessageBox.alert('Notification', 'Save Failed');
                  },
                  success: function (response, options) {
                      if (response.responseText != "") {
                          eval('var tmp=' + response.responseText);
                          if (tmp.success !== undefined && tmp.success === true) {
                              var msg = 'New Role has been saved successfully';
                              if (Application.OrgRole.id_admin_role)
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